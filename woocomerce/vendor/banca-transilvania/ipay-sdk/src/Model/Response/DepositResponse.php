<?php

namespace BTransilvania\Api\Model\Response;

class DepositResponse extends ResponseModel
{
    public function getActionCode(): ?string
    {
        return $this->response->actionCode ?? null;
    }

    public function getActionCodeDescription()
    {
        return $this->response->actionCodeDescription ?? null;
    }
}
