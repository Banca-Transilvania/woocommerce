<?php

namespace BTransilvania\Api\Logger;

use BTransilvania\Api\Logger\LoggerInterface;
use BTransilvania\Api\Logger\PsrLogger;

class LoggerFactory
{
    public static function createLogger(?LoggerInterface $existingLogger = null): LoggerInterface
    {
        if ($existingLogger !== null) {
            return $existingLogger;
        }

        if (class_exists('\Monolog\Logger')) {
            $logger = new \Monolog\Logger('btipay');
            $logger->pushHandler(new \Monolog\Handler\StreamHandler('var/logs/btsdk.log', \Monolog\Logger::DEBUG));
            return new PsrLogger($logger);
        }

        // Fallback to simple logger if Monolog is not available
        return new \BTransilvania\Api\Logger\BTSdkLogger();
    }
}