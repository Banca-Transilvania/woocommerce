<?php

namespace BTransilvania\Api\Model\Request;

class GetFinishedPaymentModel extends RequestModel
{
    public string $token;
    public string $orderId;
    public ?string $language = null;
}
