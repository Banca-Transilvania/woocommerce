<?php

namespace BTransilvania\Api\Model\Response;

class GetBindingsResponseModel extends ResponseModel
{
    /**
     * Get Saved Cards
     *
     * @return array|null
     */
    public function getSavedCards(): ?array
    {
        return $this->response->bindings ?? null;
    }
}