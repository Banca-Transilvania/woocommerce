<?php

namespace BTransilvania\Api\Model\Response;

use BTransilvania\Api\Model\IPayStatuses;

class GetOrderStatusResponseModel extends ExtendedResponseModel
{
    public function paymentIsAccepted(): bool
    {
        return in_array(
            $this->getStatus(),
            [
                IPayStatuses::STATUS_DEPOSITED,
                IPayStatuses::STATUS_APPROVED,
            ]
        );
    }

    public function getStatus(): string
    {
        $info = $this->paymentAmountInfo;
        if ($info !== null && property_exists($info, 'paymentState')) {
            return $info->paymentState;
        }
        return 'UNKNOWN';
    }

    public function canSaveCard(): bool
    {
        return $this->getStatus() === IPayStatuses::STATUS_VALIDATION_FINISHED;
    }

    public function isAuthorized(): bool
    {
        return $this->getStatus() === IPayStatuses::STATUS_APPROVED;
    }

    public function canRefund(): bool
    {
        return in_array(
            $this->getStatus(),
            [
                IPayStatuses::STATUS_DEPOSITED,
                IPayStatuses::STATUS_PARTIALLY_REFUNDED,
            ]
        );
    }

    public function getTotalAvailableForRefund(): float
    {
        $info = $this->paymentAmountInfo;
        if ($info !== null && property_exists($info, 'depositedAmount') && is_scalar($info->depositedAmount)) {
            return ((int) $info->depositedAmount) / 100;
        }
        return 0.0;
    }

    public function getTotalAvailableForCancel(): float
    {
        $info = $this->paymentAmountInfo;
        if ($info !== null && property_exists($info, 'approvedAmount') && is_scalar($info->approvedAmount)) {
            return ((int) $info->approvedAmount) / 100;
        }
        return 0.0;
    }

    public function getAmount(): float
    {
        if (is_int($this->amount)) {
            return $this->amount / 100;
        }
        return 0.0;
    }

    public function getTotalRefunded(): float
    {
        $info = $this->paymentAmountInfo;
        if ($info !== null && property_exists($info, 'refundedAmount') && is_scalar($info->refundedAmount)) {
            return ((int) $info->refundedAmount) / 100;
        }
        return 0.0;
    }

    public function getLoyAmount(): float
    {
        if (is_array($this->merchantOrderParams)) {
            foreach ($this->merchantOrderParams as $param) {
                if ($param instanceof \stdClass && $param->name === 'loyaltyAmount' && is_scalar($param->value)) {
                    return floatval($param->value) / 100;
                }
            }
        }
        return 0.0;
    }

    public function getLoyId(): ?string
    {
        if (is_array($this->attributes)) {
            foreach ($this->attributes as $attribute) {
                if ($attribute instanceof \stdClass && $attribute->name === 'loyalties' && is_string($attribute->value)) {
                    $loy = explode(',', $attribute->value);
                    $loy = explode(':', $loy[0]);
                    if (isset($loy[1]) && is_string($loy[1])) {
                        return $loy[1];
                    }
                }
            }
        }
        return null;
    }

    public function getCardInfo(): ?array
    {
        $card_info = $this->cardAuthInfo;
        $card_ids = $this->getCardIds();
        if (!$card_info instanceof \stdClass || !is_array($card_ids)) {
            return null;
        }
        $card = (array) $card_info;
        return array_merge($card, $card_ids);
    }

    public function getCardIds(): ?array
    {
        $binding = $this->bindingInfo;
        if (!$binding instanceof \stdClass) {
            return null;
        }
        if (is_string($binding->bindingId) && is_string($binding->clientId)) {
            return [
                'ipay_id'     => $binding->bindingId,
                'customer_id' => $binding->clientId,
            ];
        }
        return null;
    }
}
