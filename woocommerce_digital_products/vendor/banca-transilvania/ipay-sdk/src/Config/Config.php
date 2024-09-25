<?php

namespace BTransilvania\Api\Config;

class Config
{
    public const PROD_MODE = 'prod';
    public const TEST_MODE = 'test';

    public const EUR_CURRENCY = 978;
    public const RON_CURRENCY = 946;
    public const USD_CURRENCY = 840;

    public const ROMANIAN_LANGUAGE = 'ro';
    public const ENGLISH_LANGUAGE  = 'en';

    private string $user = '';
    private string $password = '';
    private string $environment = self::TEST_MODE;
    private int $currency = self::EUR_CURRENCY;
    private string $platformName = 'iPay Client PHP SDK';
    private string $language = self::ROMANIAN_LANGUAGE;

    /**
     * Construct configuration with optional attributes
     *
     * @param array $attribs Attributes to initialize the configuration
     */
    public function __construct($attribs = [])
    {
        if (empty($attribs['user']) && empty($this->user)) {
            throw new \InvalidArgumentException("User is required and cannot be empty.");
        }

        if (empty($attribs['password']) && empty($this->password)) {
            throw new \InvalidArgumentException("Password is required and cannot be empty.");
        }

        if (!empty($attribs)) {
            foreach ($attribs as $key => $value) {
                $methodName = $key;
                if (method_exists($this, $methodName)) {
                    $this->$methodName($value);
                }
            }
        }
    }

    /**
     * Combined getter/setter for the user.
     *
     * @param string|null $value If provided, sets the user
     * @return mixed The current user value if no argument is provided, or $this for chaining if a value is set
     */
    public function user(string $value = null)
    {
        if ($value === null) {
            return $this->user;
        }

        $this->user = $value;
        return $this;
    }

    /**
     * Combined getter/setter for the password.
     *
     * @param string|null $value If provided, sets the password
     * @return string|self The current password value if no argument is provided, or $this for chaining if a value is set
     */
    public function password(string $value = null)
    {
        if ($value === null) {
            return $this->password;
        }

        $this->password = $value;
        return $this;
    }

    /**
     * Combined getter/setter for the environment.
     *
     * @param string|null $value If provided, sets the environment
     * @return string|self The current environment value if no argument is provided, or $this for chaining if a value is set
     */
    public function environment(string $value = null)
    {
        if ($value === null) {
            return $this->environment;
        }

        if (!in_array($value, [self::PROD_MODE, self::TEST_MODE], true)) {
            throw new \InvalidArgumentException("Invalid environment value: '$value'.");
        }

        $this->environment = $value;
        return $this;
    }

    /**
     * Combined getter/setter for the currency.
     *
     * @param int|null $value If provided, sets the currency
     * @return int|self The current currency value if no argument is provided, or $this for chaining if a value is set
     */
    public function currency(int $value = null)
    {
        if ($value === null) {
            return $this->currency;
        }

        $validCurrencies = [self::EUR_CURRENCY, self::RON_CURRENCY, self::USD_CURRENCY];
        if (!in_array($value, $validCurrencies, true)) {
            throw new \InvalidArgumentException("Invalid currency value: '$value'.");
        }

        $this->currency = $value;
        return $this;
    }

    /**
     * Combined getter/setter for the return URL.
     *
     * @param string|null $value If provided, sets the returnURL
     * @return string|self The current returnURL value if no argument is provided, or $this for chaining if a value is set
     */
    public function returnURL(string $value = null)
    {
        if ($value === null) {
            return $this->returnURL;
        }

        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException("Invalid return URL: '$value'.");
        }

        $this->returnURL = $value;
        return $this;
    }

    /**
     * Combined getter/setter for the platform name.
     *
     * @param string|null $value If provided, sets the platformName
     * @return string|self The current platformName value if no argument is provided, or $this for chaining if a value is set
     */
    public function platformName(string $value = null)
    {
        if ($value === null) {
            return $this->platformName;
        }

        $this->platformName = $value;
        return $this;
    }

    /**
     * Combined getter/setter for the language.
     *
     * @param string|null $value If provided, sets the language
     * @return string|self The current language value if no argument is provided, or $this for chaining if a value is set
     */
    public function language(string $value = null)
    {
        if ($value === null) {
            return $this->language;
        }

        $validLanguages = [self::ROMANIAN_LANGUAGE, self::ENGLISH_LANGUAGE];
        if (!in_array($value, $validLanguages, true)) {
            throw new \InvalidArgumentException("Invalid language value: '$value'.");
        }

        $this->language = $value;
        return $this;
    }
}
