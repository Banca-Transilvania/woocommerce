<?php

namespace BTransilvania\Api\HttpAdapter;

interface HttpClientInterface
{
    /**
     * Sends an HTTP request to the specified iPAY API URL.
     *
     * @param string $httpMethod The HTTP method (e.g., 'GET', 'POST').
     * @param string $url The URL to send the request to.
     * @param array|string $headers The request headers.
     * @param array|string $httpBody The request body as a string|array.
     * @return \stdClass|null The response as a standard class object, or null in case of an error.
     * @throws \BTransilvania\Api\Exception\HttpException When the request fails.
     */
    public function send(string $httpMethod, string $url, $headers, $httpBody): ?\stdClass;

    /**
     * Retrieves the version of the underlying HTTP client.
     *
     * @return string The client version as a string.
     */
    public function getClientVersion(): string;
}
