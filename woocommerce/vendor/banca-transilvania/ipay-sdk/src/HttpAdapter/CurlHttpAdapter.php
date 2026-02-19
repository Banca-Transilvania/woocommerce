<?php

namespace BTransilvania\Api\HttpAdapter;

use BTransilvania\Api\Exception\ApiException;
use BTransilvania\Api\Exception\CurlConnectTimeoutException;

final class CurlHttpAdapter implements HttpClientInterface
{
    /** Default response timeout in seconds. */
    public const DEFAULT_TIMEOUT = 10;

    /** Default connect timeout in seconds. */
    public const DEFAULT_CONNECT_TIMEOUT = 2;

    /** HTTP status code for an empty ok response. */
    public const HTTP_NO_CONTENT = 204;

    /** Maximum retry attempts. */
    public const MAX_RETRIES = 5;

    /** Milliseconds to increase delay per retry. */
    public const DELAY_INCREASE_MS = 1000;

    /**
     * Sends an HTTP request with retries on timeout exceptions.
     *
     * @param string $httpMethod
     * @param string $url
     * @param array|string $headers
     * @param array|string $httpBody
     * @return \stdClass|null
     * @throws ApiException
     * @throws CurlConnectTimeoutException
     */
    public function send(string $httpMethod, string $url, $headers, $httpBody): ?\stdClass
    {
        for ($attempt = 0; $attempt <= self::MAX_RETRIES; $attempt++) {
            if ($attempt > 0) {
                usleep($attempt * self::DELAY_INCREASE_MS);
            }

            try {
                return $this->attemptRequest($httpMethod, $url, $headers, $httpBody);
            } catch (CurlConnectTimeoutException $e) {
                // Nothing
            }
        }

        throw new CurlConnectTimeoutException(
            "Unable to connect to iPay. Maximum number of retries (" . self::MAX_RETRIES . ") reached."
        );
    }

    /**
     * Attempts to make an HTTP request and handle the response.
     *
     * @param string $httpMethod
     * @param string $url
     * @param array $headers
     * @param array|string $httpBody
     * @return \stdClass|void|null
     * @throws ApiException
     */
    protected function attemptRequest(string $httpMethod, string $url, array $headers, $httpBody)
    {
        $curl = curl_init($url);

        $this->setCurlOptions($curl, $httpMethod, $headers, $httpBody);

        $startTime = microtime(true);
        $response = curl_exec($curl);
        $endTime = microtime(true);

        if ($response === false) {
            $executionTime = $endTime - $startTime;
            $this->handleCurlError($curl, $executionTime);
        }

        $statusCode = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        curl_close($curl);

        return $this->parseResponse($response, $statusCode, $httpBody);
    }

    /**
     * Sets cURL options based on request parameters.
     *
     * @param resource $curl
     * @param string $httpMethod
     * @param array|string $headers
     * @param array|string $httpBody
     * @return void
     */
    private function setCurlOptions($curl, string $httpMethod, $headers, $httpBody): void
    {
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $this->formatHeaders($headers),
            CURLOPT_CONNECTTIMEOUT => self::DEFAULT_CONNECT_TIMEOUT,
            CURLOPT_TIMEOUT        => self::DEFAULT_TIMEOUT,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
        ]);

        $this->setRequestMethod($curl, $httpMethod, $httpBody);
    }

    /**
     * Formats request headers for cURL.
     *
     * @param array $headers
     * @return array
     */
    private function formatHeaders(array $headers): array
    {
        return array_map(function ($key, $value) {
            return "$key: $value";
        }, array_keys($headers), $headers);
    }

    /**
     * Sets the HTTP method for a cURL request.
     *
     * @param resource $curl
     * @param string $httpMethod
     * @param array|string $httpBody
     * @return void
     * @throws \InvalidArgumentException
     */
    private function setRequestMethod($curl, string $httpMethod, $httpBody): void
    {
        switch ($httpMethod) {
            case 'POST':
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $httpBody);
                break;
            case 'GET':
                // GET is default, do nothing
                break;
            case 'PATCH':
            case 'DELETE':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $httpMethod);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $httpBody);
                break;
            default:
                throw new \InvalidArgumentException("Invalid HTTP method: $httpMethod");
        }
    }

    /**
     * Throws an exception if a cURL error occurs, considering connection timeout scenarios.
     *
     * @param resource $curl
     * @param float $executionTime
     * @return void
     * @throws CurlConnectTimeoutException
     * @throws ApiException
     */
    private function handleCurlError($curl, float $executionTime): void
    {
        $errorNumber = curl_errno($curl);
        $errorMessage = curl_error($curl);

        if ($this->isConnectionTimeoutError($errorNumber, $executionTime)) {
            throw new CurlConnectTimeoutException("CURL Error: $errorMessage");
        }

        throw new ApiException("CURL Error: $errorMessage");
    }

    /**
     * Determines if a cURL error is related to connection timeout.
     *
     * @param int $curlErrorNumber
     * @param float $executionTime
     * @return bool
     */
    protected function isConnectionTimeoutError(int $curlErrorNumber, float $executionTime): bool
    {
        $connectErrors = [
            \CURLE_COULDNT_RESOLVE_HOST => true,
            \CURLE_COULDNT_CONNECT      => true,
            \CURLE_SSL_CONNECT_ERROR    => true,
            \CURLE_GOT_NOTHING          => true,
        ];

        if (isset($connectErrors[$curlErrorNumber])) {
            return true;
        }

        if ($curlErrorNumber === \CURLE_OPERATION_TIMEOUTED) {
            if ($executionTime > self::DEFAULT_TIMEOUT) {
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * Parses the HTTP response, handling errors and decoding JSON.
     *
     * @param string $response
     * @param int $statusCode
     * @param string $httpBody
     * @return \stdClass|null
     * @throws ApiException
     */
    protected function parseResponse(string $response, int $statusCode, string $httpBody): ?\stdClass
    {
        if (empty($response)) {
            if ($statusCode === self::HTTP_NO_CONTENT) {
                return null;
            }

            throw new ApiException("No response body found.");
        }

        $body = @json_decode($response);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ApiException("Unable to decode JSON response: '{$response}'.");
        }

        if ($statusCode >= 400) {
            $errorCode = property_exists($body,'errorCode') ? $body->errorCode : $statusCode;
            $errorMessage = property_exists($body,'errorMessage') ? $body->errorMessage : '';

            $message = "Status $statusCode: Error executing API call. Error code: ({$errorCode}): {$errorMessage}";

            if ($httpBody) {
                $message .= ". Request body: | {$httpBody} |";
            }

            throw new ApiException($message, $statusCode);
        }

        return $body;
    }

    /**
     * Returns the client version information.
     *
     * @return string
     */
    public function getClientVersion(): string
    {
        return 'Curl/*';
    }

    /**
     * Indicates whether debugging support is available.
     *
     * @return bool
     */
    public function supportsDebugging(): bool
    {
        return false;
    }
}
