<?php

namespace BTransilvania\Api\Model\Request;

class Currency extends RequestModel
{
    public const EUR_NUMERIC_CODE = 978;
    public const RON_NUMERIC_CODE = 946;
    public const USD_NUMERIC_CODE = 840;
    public const LOY_NUMERIC_CODE = 777;

    public const CURRENCIES = [
        'EUR' => self::EUR_NUMERIC_CODE,
        'RON' => self::RON_NUMERIC_CODE,
        'USD' => self::USD_NUMERIC_CODE,
        'LOY' => self::LOY_NUMERIC_CODE
    ];

    public const CURRENCIES_CODE = [
        self::EUR_NUMERIC_CODE => 'EUR',
        self::RON_NUMERIC_CODE => 'RON',
        self::USD_NUMERIC_CODE => 'USD',
        self::LOY_NUMERIC_CODE => 'LOY'
    ];

    /**
     * Get the currency using either the alphabetic code or the numeric code.
     *
     * @param string|int $currency The currency code to set.
     */
    public static function getCurrency($currency): int
    {
        if (is_numeric($currency)) {
            $numericCurrency = (int)$currency;
            if (in_array($numericCurrency, self::CURRENCIES, true)) {
                return $numericCurrency;
            } else {
                throw new \InvalidArgumentException("Invalid numeric currency code: {$currency}");
            }
        } elseif (is_string($currency) && isset(self::CURRENCIES[strtoupper($currency)])) {
            return self::CURRENCIES[strtoupper($currency)];
        } else {
            throw new \InvalidArgumentException("Invalid currency code: {$currency}");
        }
    }
}
