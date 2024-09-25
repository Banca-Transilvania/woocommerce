<?php

namespace BTransilvania\Api\Model\Response;

class PaymentOrderBindingResponseModel extends ExtendedResponseModel
{
    /**
     * Retrieves the redirect URL, if any, from the response.
     *
     * @return string|null The redirect URL or null if not applicable.
     */
    public function getRedirectUrl(): ?string
    {
        return $this->response->redirect ?? null;
    }
}