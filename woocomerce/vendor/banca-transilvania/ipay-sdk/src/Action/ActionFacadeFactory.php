<?php

namespace BTransilvania\Api\Action;

use BTransilvania\Api\Client\ClientInterface;
use BTransilvania\Api\Logger\LoggerInterface;

class ActionFacadeFactory
{
    public static function createActionFacade(
        string $action,
        ClientInterface $client,
        array $data,
        LoggerInterface $logger
    ): ActionFacade {
        switch ($action) {
            case 'register':
            $endpoint = 'register.do';
                $requestModel = new \BTransilvania\Api\Model\Request\RegisterRequestModel($data);
                $responseModel = new \BTransilvania\Api\Model\Response\RegisterResponseModel();
                break;
            case 'registerPreAuth':
                $endpoint = 'registerPreAuth.do';
                $requestModel = new \BTransilvania\Api\Model\Request\RegisterRequestModel($data);
                $responseModel = new \BTransilvania\Api\Model\Response\RegisterResponseModel();
                break;
            case 'deposit':
                $endpoint = 'deposit.do';
                $requestModel = new  \BTransilvania\Api\Model\Request\DepositModel($data);
                $responseModel = new \BTransilvania\Api\Model\Response\DepositResponse();
                break;
            case 'reverse':
                $endpoint = 'reverse.do';
                $requestModel = new \BTransilvania\Api\Model\Request\ReverseModel($data);
                $responseModel = new \BTransilvania\Api\Model\Response\RefundResponse();
                break;
            case 'refund':
                $endpoint = 'refund.do';
                $requestModel = new \BTransilvania\Api\Model\Request\RefundModel($data);
                $responseModel = new \BTransilvania\Api\Model\Response\RefundResponse();
                break;
            case 'getOrderStatusExtended':
                $endpoint = 'getOrderStatusExtended.do';
                $requestModel = new \BTransilvania\Api\Model\Request\GerOrderStatusModel($data);
                $responseModel = new \BTransilvania\Api\Model\Response\GetOrderStatusResponseModel();
                break;
            case 'getFinishedPaymentInfo':
                $endpoint = 'getFinishedPaymentInfo.do';
                $requestModel = new \BTransilvania\Api\Model\Request\GetFinishedPaymentModel($data);
                $responseModel = new \BTransilvania\Api\Model\Response\GetFinishedPaymentResponseModel();
                break;
            case 'paymentOrderBinding':
                $endpoint = 'paymentOrderBinding.do';
                $requestModel = new \BTransilvania\Api\Model\Request\PaymentOrderBindingModel($data);
                $responseModel = new \BTransilvania\Api\Model\Response\PaymentOrderBindingResponseModel();
                break;
            case 'getBindings':
                $endpoint = 'getBindings.do';
                $requestModel = new \BTransilvania\Api\Model\Request\GetBindingsModel($data);
                $responseModel = new \BTransilvania\Api\Model\Response\GetBindingsResponseModel();
                break;
            case 'unBindCard':
                $endpoint = 'unBindCard.do';
                $requestModel = new \BTransilvania\Api\Model\Request\BindCardModel($data);
                $responseModel = new \BTransilvania\Api\Model\Response\BindCardResponseModel();
                break;
            case 'bindCard':
                $endpoint = 'bindCard.do';
                $requestModel = new \BTransilvania\Api\Model\Request\BindCardModel($data);
                $responseModel = new \BTransilvania\Api\Model\Response\BindCardResponseModel();
                break;
            default:
                throw new \InvalidArgumentException("Unknown action: $action");
        }

        return new ActionFacade($endpoint, $client, $requestModel, $responseModel, $logger);
    }
}
