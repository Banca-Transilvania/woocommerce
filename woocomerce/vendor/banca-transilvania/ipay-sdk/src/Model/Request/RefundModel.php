<?php

namespace BTransilvania\Api\Model\Request;

class RefundModel extends RequestModel
{
    public string $orderId;
    public int $amount;
}