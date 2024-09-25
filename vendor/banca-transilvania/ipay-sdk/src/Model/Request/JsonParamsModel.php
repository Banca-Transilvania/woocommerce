<?php

namespace BTransilvania\Api\Model\Request;

class JsonParamsModel extends RequestModel
{
    public string $requestedScaExemptionInd;
    public bool $force3DS2 = false;
    public bool $isSubscription = false;

    public function buildRequest()
    {
        $request = parent::buildRequest();
        if ($this->force3DS2) {
            unset($request['force3DS2']);
            $request['FORCE_3DS2'] = true;
        }

        if ($this->isSubscription) {
            $request['requestedScaExemptionInd'] = 'MIT';
        }

        $jsonRequest = json_encode($request);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('JSON encoding failed: ' . json_last_error_msg());
        }

        return $jsonRequest;
    }
}
