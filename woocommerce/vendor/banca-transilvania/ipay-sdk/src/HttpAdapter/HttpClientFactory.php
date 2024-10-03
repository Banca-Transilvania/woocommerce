<?php

namespace BTransilvania\Api\HttpAdapter;

use BTransilvania\Api\Exception\UnrecognizedClientException;

class HttpClientFactory implements HttpClientFactoryInterface
{
    /**
     * Creates an instance of HttpClientInterface based on the provided client or environment conditions.
     *
     * @param \GuzzleHttp\ClientInterface|\BTransilvania\Api\HttpAdapter\HttpClientInterface|null $httpClient Optional. A specific HTTP client instance to use. If null, the factory decides which client to instantiate based on the environment.
     * @return \BTransilvania\Api\HttpAdapter\HttpClientInterface An instance of a class implementing HttpClientInterface.
     * @throws \BTransilvania\Api\Exception\UnrecognizedClientException When the provided client is not recognized.
     */
    public function createHttpClient($httpClient = null): HttpClientInterface
    {
        if ($httpClient instanceof HttpClientInterface) {
            return $httpClient;
        } elseif ($httpClient instanceof \GuzzleHttp\ClientInterface) {
            return new GuzzleHttpAdapter($httpClient);
        } elseif ($httpClient === null) {
            if ($this->guzzleIsAvailable()) {
                return new GuzzleHttpAdapter();
            }
            return new CurlHttpAdapter(); // Fallback to CurlHttpAdapter
        }

        throw new UnrecognizedClientException('The provided HTTP client or adapter was not recognized.');
    }

    private function guzzleIsAvailable(): bool
    {
        return $this->guzzleIsDetected() && $this->isSupportedGuzzleVersion();
    }

    /**
     * @return bool
     */
    private function guzzleIsDetected(): bool
    {
        return interface_exists('\\' . \GuzzleHttp\ClientInterface::class);
    }

    /**
     * @return int|null
     */
    private function isSupportedGuzzleVersion(): ?int
    {
        $majorVersion = null;

        // For Guzzle 7+
        if (defined('\GuzzleHttp\ClientInterface::MAJOR_VERSION')) {
            $majorVersion = (int) \GuzzleHttp\ClientInterface::MAJOR_VERSION;
        }
        // Fallback for Guzzle 6 and below
        elseif (defined('\GuzzleHttp\ClientInterface::VERSION')) {
            $versionParts = explode('.', \GuzzleHttp\ClientInterface::VERSION);
            $majorVersion = (int) $versionParts[0];
        }

        return in_array($majorVersion, [6, 7]);
    }
}
