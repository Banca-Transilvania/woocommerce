<?php

namespace BTransilvania\Api\Client;

interface ClientInterface
{
    public function sendRequest(string $action, array $data): \stdClass;
}
