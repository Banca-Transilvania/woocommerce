<?php

namespace BTransilvania\Api\HttpAdapter;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions as GuzzleRequestOptions;
use BTransilvania\Api\Exception\ApiException;
use Psr\Http\Message\ResponseInterface;

final class GuzzleHttpAdapter implements HttpClientInterface
{
    /** Default response timeout in seconds. */
    public const DEFAULT_TIMEOUT = 10;

    /** Default connect timeout in seconds. */
    public const DEFAULT_CONNECT_TIMEOUT = 2;

    /** HTTP status code for an empty ok response. */
    public const HTTP_NO_CONTENT = 204;

    /**
     * @var \GuzzleHttp\ClientInterface
     */
    protected $httpClient;

    /**
     * Indicates if debugging is enabled. When true, ApiException includes the request.
     * Debugging is off by default to protect sensitive data.
     *
     * @var bool
     */
    protected bool $debugging = false;

    /**
     * Initializes a new instance of the GuzzleHttpAdapter class.
     *
     * @param ClientInterface|null $httpClient
     */
    public function __construct(ClientInterface $httpClient = null)
    {
        $this->httpClient = $httpClient ?: $this->createDefaultClient();
    }

    /**
     * Creates a default \GuzzleHttp\ClientInterface with standard configurations for handling retries and timeouts.
     *
     * @return ClientInterface
     */
    private function createDefaultClient(): ClientInterface
    {
        $handlerStack = HandlerStack::create();
        $handlerStack->push((new GuzzleRetryMiddlewareFactory)->retry());

        return new Client([
            GuzzleRequestOptions::TIMEOUT => self::DEFAULT_TIMEOUT,
            GuzzleRequestOptions::CONNECT_TIMEOUT => self::DEFAULT_CONNECT_TIMEOUT,
            'handler' => $handlerStack,
        ]);
    }

    /**
     * Sends an HTTP request using Guzzle and returns the response as a stdClass object.
     *
     * @param string $httpMethod
     * @param string $url
     * @param array|string $headers
     * @param array|string $httpBody
     * @return \stdClass|null
     * @throws ApiException
     */
    public function send(string $httpMethod, string $url, $headers, $httpBody): ?\stdClass
    {
        $request = new Request($httpMethod, $url, $headers, $httpBody);

        try {
            $response = $this->httpClient->send($request, ['http_errors' => false]);
        } catch (GuzzleException $e) {
            $this->handleException($e, $request);
        }

        return $this->parseResponse($response);
    }

    /**
     * Processes the HTTP response and extracts the body. Converts the body from JSON to a stdClass object.
     *
     * @param ResponseInterface $response
     * @return \stdClass|null
     * @throws ApiException
     */
    private function parseResponse(ResponseInterface $response): ?\stdClass
    {
        $body = (string)$response->getBody();
        if (empty($body)) {
            if ($response->getStatusCode() === self::HTTP_NO_CONTENT) {
                return null;
            }

            throw new ApiException("No response body found.");
        }

        $object = @json_decode($body);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ApiException("Unable to decode JSON response: '{$body}'.");
        }

        if ($response->getStatusCode() >= 400) {
            throw ApiException::createFromResponse($response, null);
        }

        return $object;
    }

    /**
     * Indicates if the HTTP adapter supports enabling debugging. When debugging is enabled,
     * detailed request information is included in ApiException.
     *
     * @return bool
     */
    public function supportsDebugging(): bool
    {
        return true;
    }

    /**
     * Checks if debugging mode is currently enabled. When enabled, detailed request information
     * will be included in ApiException.
     *
     * @return bool
     */
    public function debugging()
    {
        return $this->debugging;
    }

    /**
     * Enables debugging mode. When enabled, ApiException includes detailed request information,
     * aiding in troubleshooting. Intended for use in development environments to avoid leaking sensitive information.
     */
    public function enableDebugging()
    {
        $this->debugging = true;
    }

    /**
     * Disables debugging mode. When disabled, ApiException will not include detailed request information,
     * preventing sensitive data exposure in production environments.
     */
    public function disableDebugging()
    {
        $this->debugging = false;
    }

    /**
     * Retrieves the version of the underlying HTTP client.
     *
     * @return string The client version as a string.
     */
    public function getClientVersion(): string
    {
        if (defined('\GuzzleHttp\ClientInterface::MAJOR_VERSION')) { // Guzzle 7
            return "Guzzle/" . ClientInterface::MAJOR_VERSION;
        } elseif (defined('\GuzzleHttp\ClientInterface::VERSION')) { // Before Guzzle 7
            return "Guzzle/" . ClientInterface::VERSION;
        }

        return '';
    }

    /**
     * Handles exceptions thrown during the request process. Constructs an ApiException from the thrown exception,
     * optionally including the request data if debugging is enabled.
     *
     * @param \Exception $e
     * @param Request $request
     * @throws ApiException
     */
    private function handleException(\Exception $e, Request $request)
    {
        // Prevent sensitive request data from ending up in exception logs unintended
        if (!$this->debugging) {
            $request = null;
        }

        if (method_exists($e, 'hasResponse') && method_exists($e, 'getResponse')) {
            if ($e->hasResponse()) {
                throw ApiException::createFromResponse($e->getResponse(), $request);
            }
        }

        throw new ApiException($e->getMessage(), $e->getCode(), $request, null);
    }
}
