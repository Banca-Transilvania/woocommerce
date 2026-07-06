<?php

namespace BTransilvania\Api\Action;

use BTransilvania\Api\Client\ClientInterface;
use BTransilvania\Api\Exception\ApiException;
use BTransilvania\Api\Model\Request\RequestModelInterface;
use BTransilvania\Api\Model\Response\ResponseModelInterface;
use BTransilvania\Api\Logger\LoggerInterface;

class ActionFacade
{
    private string $endpoint;
    private ClientInterface $client;
    private RequestModelInterface $requestModel;
    private ResponseModelInterface $responseModel;
    private LoggerInterface $logger;

    public function __construct(
        string $endpoint,
        ClientInterface $client,
        RequestModelInterface $requestModel,
        ResponseModelInterface $responseModel,
        LoggerInterface $logger
    ) {
        $this->endpoint = $endpoint;
        $this->client = $client;
        $this->requestModel = $requestModel;
        $this->responseModel = $responseModel;
        $this->logger = $logger;
    }

    /**
     * @throws ApiException
     */
    public function execute(array $data): ResponseModelInterface
    {
        try {
            $request = $this->requestModel->buildRequest();
            $this->logger->debug("BT-SDK: Endpoint = " . $this->endpoint);
            $this->logger->debug("BT-SDK: Request = " . \json_encode($request, JSON_PRETTY_PRINT));
            $response = $this->client->sendRequest($this->endpoint, $request);
            $this->responseModel->setResponse($response);
            $this->logger->debug("BT-SDK: Response = " . \json_encode($this->responseModel->toArray(), JSON_PRETTY_PRINT));
            return $this->responseModel;
        } catch (ApiException $e) {
            $this->logger->error("BT-SDK: Failed to execute API call: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("BT-SDK: Failed to execute API call: " . $e->getMessage());
            throw new ApiException("Failed to execute API call: " . $e->getMessage(), 0);
        }
    }
}
