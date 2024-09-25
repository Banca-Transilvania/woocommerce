<?php

namespace BTransilvania\Api\Model\Request;

class DepositModel extends RequestModel
{
    public string $orderId;
    public float $amount;
}
