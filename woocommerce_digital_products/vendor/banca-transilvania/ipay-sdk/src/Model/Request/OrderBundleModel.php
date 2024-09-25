<?php

namespace BTransilvania\Api\Model\Request;

class OrderBundleModel extends RequestModel
{
    public string $orderCreationDate;
    public CustomerDetailsModel $customerDetails;

    public function buildRequest()
    {
        $request = parent::buildRequest();

        $jsonRequest = json_encode($request);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('JSON encoding failed: ' . json_last_error_msg());
        }

        return $jsonRequest;
    }
}
