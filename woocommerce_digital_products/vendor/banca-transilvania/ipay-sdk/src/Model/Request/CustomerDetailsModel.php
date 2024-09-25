<?php

namespace BTransilvania\Api\Model\Request;

use BTransilvania\Api\Model\IPayFormatter;

class CustomerDetailsModel extends RequestModel
{
    public string $email;
    public string $phone;
    public ?string $contact = null;
    public DeliveryInfoModel $deliveryInfo;
    public BillingInfoModel $billingInfo;

    public function buildRequest(): array
    {
        $this->contact = $this->getContact();
        $this->phone = $this->getPhone();
        return parent::buildRequest();
    }

    private function getContact(): string
    {
        return substr($this->getFormattedString($this->contact), 0, 40);
    }

    private function getPhone(): string
    {
        $phone = $this->cleanPhone($this->phone);
        if (substr($phone, 0, 2) === '07') {
            $phone = '4' . $phone;
        }
        return $phone;
    }

    private function cleanPhone($phone): string
    {
        if (!is_scalar($phone)) {
            return '';
        }
        return trim(preg_replace('/[^0-9]/', '', (string)$phone));
    }

    public function filterData(array $data):array
    {
        if (array_key_exists('contact', $data) && $data['contact'] === null) {
            unset($data['contact']);
        }
        return $data;
    }
}