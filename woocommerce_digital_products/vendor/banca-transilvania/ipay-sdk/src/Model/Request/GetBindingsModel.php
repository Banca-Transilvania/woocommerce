<?php

namespace BTransilvania\Api\Model\Request;

class GetBindingsModel extends RequestModel
{
    public string $clientId;
    public ?bool $showExpired = true;
}