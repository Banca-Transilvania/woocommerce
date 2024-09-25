<?php

namespace BTransilvania\Api\Model\Response;

class ErrorMessageMapper
{
    private const DEFAULT_MESSAGE = [
        'en' => 'Transaction refused, please come back ...',
        'ro' => 'Tranzacția a fost refuzată, vă rugăm reveniți ...',
    ];
    private array $errorMessages = [];

    /**
     * Retrieves the error message for a given action code and language.
     *
     * @param string $actionCode The action code to get the message for.
     * @param string $language The language of the message ('en' for English, 'ro' for Romanian).
     * @return string The error message corresponding to the action code and language.
     */
    public function getMessage(string $actionCode, string $language = 'en'): string
    {
        $language = $language ?? 'en';

        if (!isset($this->errorMessages[$language])) {
            $this->loadLanguage($language);
        }

        return $this->errorMessages[$language][$actionCode]
            ?? self::DEFAULT_MESSAGE[$language]
            ?? self::DEFAULT_MESSAGE['en'];
    }

    /**
     * Loads the language file into the errorMessages array.
     *
     * @param string $language The language to load.
     */
    private function loadLanguage(string $language): void
    {
        $path = __DIR__ . "/../../i18n/{$language}.php";
        if (file_exists($path)) {
            $this->errorMessages[$language] = require $path;
        } else {
            error_log("Language file {$language}.php not found. Using default messages.");
            $this->errorMessages[$language] = [];
        }
    }
}
