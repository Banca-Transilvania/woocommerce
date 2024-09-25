<?php

namespace BTransilvania\Api\Model\Response;

class RegisterResponseModel extends ResponseModel
{
    /**
     * Checks if the response includes a redirect URL.
     *
     * @return bool True if there is a redirect URL, False otherwise.
     */
    public function hasRedirect(): bool
    {
        return !is_null($this->getRedirectUrl());
    }

    /**
     * Retrieves the redirect URL, if any, from the response.
     *
     * @return string|null The redirect URL or null if not applicable.
     */
    public function getRedirectUrl(): ?string
    {
        return $this->response->formUrl ?? null;
    }

    public function getOrderId()
    {
        return $this->response->orderId ?? null;
    }
}
