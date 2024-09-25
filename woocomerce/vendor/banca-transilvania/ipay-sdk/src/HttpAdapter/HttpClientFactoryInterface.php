<?php

namespace BTransilvania\Api\HttpAdapter;

interface HttpClientFactoryInterface
{
    /**
     * Creates an instance of HttpClientInterface based on the provided client or environment conditions.
     *
     * @param \GuzzleHttp\ClientInterface|\BTransilvania\Api\HttpAdapter\HttpClientInterface|null $httpClient
     *
     * @return \BTransilvania\Api\HttpAdapter\HttpClientInterface An instance of a class implementing HttpClientInterface.
     */
    public function createHttpClient($httpClient = null): HttpClientInterface;
}
