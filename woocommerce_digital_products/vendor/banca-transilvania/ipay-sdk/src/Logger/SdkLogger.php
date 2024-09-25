<?php

namespace BTransilvania\Api\Logger;

use BTransilvania\Api\Logger\LoggerInterface;
use BTransilvania\Api\Logger\LogLevel;

abstract class SdkLogger implements LoggerInterface
{

    public function emergency($message, array $context = [])
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    public function log($level, $message, array $context = [])
    {
        echo $message;
    }

    public function alert($message, array $context = [])
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    public function critical($message, array $context = [])
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    public function error($message, array $context = [])
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    public function warning($message, array $context = [])
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    public function notice($message, array $context = [])
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    public function info($message, array $context = [])
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    public function debug($message, array $context = [])
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }
}
