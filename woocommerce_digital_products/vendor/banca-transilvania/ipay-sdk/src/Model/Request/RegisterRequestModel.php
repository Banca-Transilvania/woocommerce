<?php

namespace BTransilvania\Api\Model\Request;

use BTransilvania\Api\Model\Request\RequestModel;

class RegisterRequestModel extends RequestModel
{
    public string $orderNumber;
    public float $amount;
    /**
     * @var int|string
     */
    public $currency;
    public string $returnUrl;
    public OrderBundleModel $orderBundle;
    public ?string $description = null;
    public ?string $language = null;
    public ?string $pageView = null;
    public ?string $email = null;
    public ?string $childId = null;
    public ?string $clientId = null;
    public ?string $bindingId = null;
    public ?int $sessionTimeoutSecs = null;
    public ?string $expirationDate = null;
    public ?string $jsonParams = null;

    public function buildRequest()
    {
        $this->currency = Currency::getCurrency($this->currency);
        return parent::buildRequest();
    }
}
