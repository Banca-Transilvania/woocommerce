<?php

namespace BTransilvania\Api\Client;

use BTransilvania\Api\Config\Config;
use BTransilvania\Api\Exception\ApiException;
use BTransilvania\Api\HttpAdapter\HttpClientInterface;

class Client implements ClientInterface
{
    public const IPAY_PROD_URL = 'https://ecclients.btrl.ro/payment/rest/';
    public const IPAY_TEST_URL = 'https://ecclients-sandbox.btrl.ro/payment/rest/';

    private Config $config;
    private HttpClientInterface $httpClient;

    public function __construct(Config $config, HttpClientInterface $httpClient)
    {
        $this->config = $config;
        $this->httpClient = $httpClient;
    }

    public function sendRequest(string $action, array $data): \stdClass
    {
        $url = $this->baseUrl() . $action;

        $requestData = array_merge([
            'userName'  => $this->config->user(),
            'password'  => $this->config->password()
        ], $data);

        $headers = ['Content-Type' => 'application/x-www-form-urlencoded'];
        $formattedData = http_build_query($requestData);

        try {
            $response = $this->httpClient->send('POST', $url, $headers, $formattedData);
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new ApiException("Failed to send request: " . $e->getMessage(), 0);
        }

        return $response;
    }

    private function baseUrl(): string
    {
        return $this->config->environment() === Config::PROD_MODE ?
            self::IPAY_PROD_URL :
            self::IPAY_TEST_URL;
    }
}
