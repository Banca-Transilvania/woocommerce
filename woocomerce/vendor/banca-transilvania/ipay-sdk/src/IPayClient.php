<?php

namespace BTransilvania\Api;

use BTransilvania\Api\Action\ActionFacadeFactory;
use BTransilvania\Api\Client\ClientInterface;
use BTransilvania\Api\Config\Config;
use BTransilvania\Api\Client\Client;
use BTransilvania\Api\Exception\ApiException;
use BTransilvania\Api\HttpAdapter\HttpClientFactory;
use BTransilvania\Api\HttpAdapter\HttpClientInterface;
use BTransilvania\Api\Logger\LoggerFactory;
use BTransilvania\Api\Model\Response\ResponseModelInterface;
use BTransilvania\Api\Logger\LoggerInterface;

class IPayClient
{
    /**
     * Version of the remote API.
     */
    public const API_VERSION = "1.0.0";

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var ClientInterface|null
     */
    private ?ClientInterface $client = null;

    /**
     * @var HttpClientInterface|null
     */
    private ?HttpClientInterface $httpClient;

    /**
     * @var LoggerInterface|null
     */
    private ?LoggerInterface $logger;

    public function __construct(
        array $config = [],
        ?HttpClientInterface $httpClient = null,
        ?LoggerInterface $logger = null
    ) {
        $this->config = new Config($config);
        $this->httpClient = $httpClient;
        $this->logger = $logger;
    }

    private function ensureHttpClientIsInitialized()
    {
        if ($this->client === null) {
            $httpAdapterPicker = new HttpClientFactory();
            $this->httpClient = $httpAdapterPicker->createHttpClient($this->httpClient);
            $this->client = new Client($this->config, $this->httpClient);
        }
    }

    private function ensureLoggerIsInitialized()
    {
        if ($this->logger === null) {
            $loggerPicker = new LoggerFactory();
            $this->logger = $loggerPicker->createLogger($this->logger);
        }
    }

    /**
     * Get the HTTP client instance.
     *
     * @return HttpClientInterface|null
     */
    public function getHttpClient(): ?HttpClientInterface
    {
        return $this->httpClient;
    }

    public function setUser(string $user)
    {
        $this->config->user($user);
    }

    public function setPassword(string $password)
    {
        $this->config->password($password);
    }

    public function setEnvironment(string $environment)
    {
        $this->config->environment($environment);
    }

    public function setLanguage(string $language)
    {
        $this->config->language($language);
    }

    public function setCurrency(string $language)
    {
        $this->config->currency($language);
    }

    public function setPlatform(string $platform)
    {
        $this->config->platformName($platform);
    }

    /**
     * @throws ApiException
     */
    private function executeAction(string $action, array $data)
    {
        $this->ensureLoggerIsInitialized();
        $this->ensureHttpClientIsInitialized();

        $actionFacade = ActionFacadeFactory::createActionFacade($action, $this->client, $data, $this->logger);
        return $actionFacade->execute($data);
    }

    public function register(array $data): ResponseModelInterface
    {
        return $this->executeAction('register', $data);
    }

    public function registerPreAuth(array $data): ResponseModelInterface
    {
        return $this->executeAction('registerPreAuth', $data);
    }

    public function deposit(array $data): ResponseModelInterface
    {
        return $this->executeAction('deposit', $data);
    }

    public function reverse(array $data): ResponseModelInterface
    {
        return $this->executeAction('reverse', $data);
    }

    public function refund(array $data): ResponseModelInterface
    {
        return $this->executeAction('refund', $data);
    }

    public function getOrderStatusExtended(array $data): ResponseModelInterface
    {
        return $this->executeAction('getOrderStatusExtended', $data);
    }

    public function getFinishedPaymentInfo(array $data): ResponseModelInterface
    {
        return $this->executeAction('getFinishedPaymentInfo', $data);
    }

    public function paymentOrderBinding(array $data): ResponseModelInterface
    {
        return $this->executeAction('paymentOrderBinding', $data);
    }

    public function getBindings(array $data): ResponseModelInterface
    {
        return $this->executeAction('getBindings', $data);
    }

    public function unBindCard(array $data): ResponseModelInterface
    {
        return $this->executeAction('unBindCard', $data);
    }

    public function bindCard(array $data): ResponseModelInterface
    {
        return $this->executeAction('bindCard', $data);
    }
}