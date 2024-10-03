<?php

namespace BTransilvania\Api\Model\Request;

use BTransilvania\Api\Model\IPayFormatter;

class AddressInfoModel extends RequestModel
{
    public string $deliveryType;
    /**
     * @var int|string
     */
    public $country;
    public string $city;
    public string $postAddress;
    public ?string $postAddress2 = null;
    public ?string $postAddress3 = null;
    public ?string $postalCode = null;
    public ?string $state = null;

    public function buildRequest()
    {
        $this->deliveryType = $this->getDeliveryType();
        $this->country = Country::getCountryNumericCode($this->country);
        $this->city = $this->getFormattedString($this->city);
        $this->postAddress = $this->getFormattedString($this->postAddress);
        $this->postAddress2 = $this->getFormattedString($this->postAddress2);
        $this->postAddress3 = $this->getFormattedString($this->postAddress3);
        return parent::buildRequest();
    }

    private function getDeliveryType(): string
    {
        return substr(
            $this->getFormattedString($this->deliveryType),
            0,
            20
        );
    }

    public function filterData(array $data): array
    {
        foreach (['postAddress2', 'postAddress3', 'postalCode', 'state'] as $nullable) {
            if (array_key_exists($nullable, $data) && $data[$nullable] == null) {
                unset($data[$nullable]);
            }
        }
        return $data;
    }
}
