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
}