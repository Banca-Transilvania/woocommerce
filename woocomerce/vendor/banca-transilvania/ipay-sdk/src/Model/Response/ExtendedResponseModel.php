<?php

namespace BTransilvania\Api\Model\Response;

class ExtendedResponseModel extends ResponseModel
{
    protected ?string $actionCode = null;
    protected ?string $actionCodeDescription = null;
    protected ?string $customerError = null;
    private ErrorMessageMapper $errorMessageMapper;

    public function __construct()
    {
        $this->errorMessageMapper = new ErrorMessageMapper();
    }

    public function setResponse(\StdClass $response)
    {
        parent::setResponse($response);
        $this->actionCode = $response->actionCode ?? null;
        $this->actionCodeDescription = $response->actionCodeDescription ?? null;
        $this->setErrorMessageMapper();
    }

    public function getActionCode(): ?string
    {
        return $this->actionCode ?? null;
    }

    public function getActionCodeDescription(): ?string
    {
        return $this->actionCodeDescription ?? null;
    }

    public function getCustomerError(): ?string
    {
        return $this->customerError;
    }

    private function setErrorMessageMapper()
    {
        $language = $this->getLanguage() ?? 'en';
        if($this->getActionCode()) {
            $this->customerError = $this->errorMessageMapper->getMessage(
                $this->getActionCode(),
                $language
            );
        }
    }
}
