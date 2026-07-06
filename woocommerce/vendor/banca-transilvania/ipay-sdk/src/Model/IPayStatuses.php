<?php

namespace BTransilvania\Api\Model;

class IPayStatuses
{
    public const STATUS_CREATED             = 'CREATED';
    public const STATUS_PENDING             = 'PENDING';
    public const STATUS_APPROVED            = 'APPROVED';
    public const STATUS_DECLINED            = 'DECLINED';
    public const STATUS_REVERSED            = 'REVERSED';
    public const STATUS_DEPOSITED           = 'DEPOSITED';
    public const STATUS_PARTIALLY_REFUNDED  = 'PARTIALLY_REFUNDED';
    public const STATUS_REFUNDED            = 'REFUNDED';
    public const STATUS_VALIDATION_FINISHED = 'VALIDATION_FINISHED';

    public static function getCombinedStatus($paymentStatus, $loyStatus)
    {
        if ($paymentStatus === null || $loyStatus === null) {
            return $paymentStatus ?? $loyStatus;
        }

        if ($paymentStatus === self::STATUS_PARTIALLY_REFUNDED || $loyStatus === self::STATUS_PARTIALLY_REFUNDED ||
            ($paymentStatus === self::STATUS_APPROVED && $loyStatus === self::STATUS_REFUNDED) ||
            ($loyStatus === self::STATUS_APPROVED && $paymentStatus === self::STATUS_REFUNDED) ||
            ($paymentStatus === self::STATUS_DEPOSITED && $loyStatus === self::STATUS_REFUNDED) ||
            ($loyStatus === self::STATUS_DEPOSITED && $paymentStatus === self::STATUS_REFUNDED)) {
            return self::STATUS_PARTIALLY_REFUNDED;
        }

        if ($paymentStatus === self::STATUS_APPROVED || $loyStatus === self::STATUS_APPROVED) {
            return self::STATUS_APPROVED;
        }

        if ($paymentStatus === self::STATUS_DEPOSITED || $loyStatus === self::STATUS_DEPOSITED) {
            return self::STATUS_DEPOSITED;
        }

        if ($paymentStatus === self::STATUS_REFUNDED || $loyStatus === self::STATUS_REFUNDED) {
            return self::STATUS_REFUNDED;
        }

        if ($paymentStatus === self::STATUS_REVERSED || $loyStatus === self::STATUS_REVERSED) {
            return self::STATUS_REVERSED;
        }

        return $paymentStatus ?: $loyStatus;
    }
}