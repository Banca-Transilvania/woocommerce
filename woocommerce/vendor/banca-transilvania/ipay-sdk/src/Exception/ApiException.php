<?php

namespace BTransilvania\Api\Exception;

class ApiException extends \Exception
{
    /**
     * @var string
     */
    protected string $plainMessage;

    /**
     * @var \Psr\Http\Message\RequestInterface|null
     */
    protected $request = null;

    /**
     * @var \Psr\Http\Message\ResponseInterface|null
     */
    protected $response = null;

    /**
     * ISO8601 representation of the moment this exception was thrown
     *
     * @var \DateTimeImmutable
     */
    protected \DateTimeImmutable $raisedAt;

    /**
     * @var array
     */
    protected array $links = [];

    /**
     * Initializes ApiException with message, code, and optional context.
     *
     * @param string $message
     * @param int $code
     * @param \Psr\Http\Message\RequestInterface|null $request
     * @param \Psr\Http\Message\ResponseInterface|null $response
     * @param \Throwable|null $previous
     * @throws \BTransilvania\Api\Exception\ApiException
     */
    public function __construct(
        string $message = "",
        int $code = 0,
        $request = null,
        $response = null,
        ?\Throwable $previous = null
    ) {
        $this->plainMessage = $message;
        $this->request = $request;
        $this->response = $response;
        $this->raisedAt = new \DateTimeImmutable();

        $formattedMessage = $this->formatExceptionMessage();

        parent::__construct($formattedMessage, $code, $previous);
    }

    /**
     * Formats the exception message with additional context.
     *
     * @return string
     * @throws ApiException
     */
    private function formatExceptionMessage(): string
    {
        $message = "[{$this->raisedAt->format(\DateTimeImmutable::ATOM)}] {$this->plainMessage}";

        $this->extractLinksFromResponse();

        if ($this->hasLink('documentation')) {
            $message .= ". Documentation: {$this->getDocumentationUrl()}";
        }

        if ($this->request) {
            $requestBody = $this->request->getBody()->__toString();
            if (!empty($requestBody)) {
                $message .= ". Request body: {$requestBody}";
            }
        }

        return $message;
    }

    /**
     * Extracts and stores links from the response body if available.
     *
     * @throws ApiException
     */
    private function extractLinksFromResponse(): void
    {
        if ($this->response) {
            $object = static::parseResponseBody($this->response);
            if (isset($object->_links)) {
                foreach ($object->_links as $key => $value) {
                    $this->links[$key] = $value;
                }
            }
        }
    }

    /**
     * Parses the body of a response into a stdClass object.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return \stdClass
     * @throws \BTransilvania\Api\Exception\ApiException
     */
    protected static function parseResponseBody($response): \stdClass
    {
        $body = (string)$response->getBody();

        $object = @json_decode($body);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new self("Unable to decode JSON response: '{$body}'.");
        }

        return $object;
    }

    /**
     * Checks if a specific link key exists.
     *
     * @param string $key
     * @return bool
     */
    public function hasLink(string $key): bool
    {
        return array_key_exists($key, $this->links);
    }

    /**
     * Retrieves the documentation URL if available.
     *
     * @return string|null
     */
    public function getDocumentationUrl(): ?string
    {
        return $this->getUrl('documentation');
    }

    /**
     * Retrieves a URL by key if available.
     *
     * @param string $key
     * @return null
     */
    public function getUrl(string $key)
    {
        if ($this->hasLink($key)) {
            return $this->getLink($key)->href;
        }

        return null;
    }

    /**
     * Retrieves a link by key if available.
     *
     * @param string $key
     * @return mixed|null
     */
    public function getLink(string $key)
    {
        if ($this->hasLink($key)) {
            return $this->links[$key];
        }

        return null;
    }

    /**
     * Creates an ApiException from a response, request, and previous exception.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param \Psr\Http\Message\RequestInterface $request
     * @param \Throwable|null $previous
     * @return \BTransilvania\Api\Exception\ApiException
     * @throws \BTransilvania\Api\Exception\ApiException
     */
    public static function createFromResponse($response, $request = null, $previous = null): ApiException
    {
        $object = static::parseResponseBody($response);

        $statusCode = $response->getStatusCode();
        $errorCode = property_exists($object,'errorCode') ? $object->errorCode : $statusCode;
        $errorMessage = property_exists($object,'errorMessage') ? $object->errorMessage : '';

        $message = "Status $statusCode: Error executing API call. Error code: ({$errorCode}): {$errorMessage}";

        return new self(
            $message,
            $statusCode,
            $request,
            $response,
            $previous
        );
    }

    /**
     * Retrieves the API field associated with the exception if available.
     *
     * @return string|null
     */
    public function getField(): ?string
    {
        return $this->field;
    }

    /**
     * Retrieves the dashboard URL if available.
     *
     * @return string|null
     */
    public function getDashboardUrl(): ?string
    {
        return $this->getUrl('dashboard');
    }

    /**
     * Retrieves the associated response if available.
     *
     * @return \Psr\Http\Message\ResponseInterface|null
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Checks if a response is associated with the exception.
     *
     * @return bool
     */
    public function hasResponse(): bool
    {
        return $this->response !== null;
    }

    /**
     * Retrieves the associated request if available.
     *
     * @return \Psr\Http\Message\RequestInterface|null
     */
    public function getRequest(): ?\Psr\Http\Message\RequestInterface
    {
        return $this->request;
    }

    /**
     * Retrieves the timestamp of when the exception was raised.
     *
     * @return \DateTimeImmutable
     */
    public function getRaisedAt(): \DateTimeImmutable
    {
        return $this->raisedAt;
    }

    /**
     * Retrieves the original exception message without additional context.
     *
     * @return string
     */
    public function getPlainMessage(): string
    {
        return $this->plainMessage;
    }

}
