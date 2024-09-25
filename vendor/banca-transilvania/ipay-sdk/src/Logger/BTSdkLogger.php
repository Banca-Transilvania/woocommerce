<?php

namespace BTransilvania\Api\Logger;

use BTransilvania\Api\Logger\SdkLogger;
use BTransilvania\Api\Logger\LogLevel;
use InvalidArgumentException;

class BTSdkLogger extends SdkLogger
{
    private $logFilePath;

    public function __construct()
    {
        $this->logFilePath = __DIR__ . '/../../logs/btsdk.log';
        $this->ensureDirectoryExists();
    }

    private function ensureDirectoryExists()
    {
        $directory = dirname($this->logFilePath);
        if (!file_exists($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $directory));
        }
    }

    public function emergency($message, array $context = [])
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    public function log($level, $message, array $context = [])
    {
        if (!defined(LogLevel::class . '::' . strtoupper($level))) {
            throw new InvalidArgumentException('Invalid log level: ' . $level);
        }

        $contextString = empty($context) ? '[]' : json_encode($context);
        $logEntry = sprintf('[%s] %s: %s | %s%s', date('Y-m-d H:i:s'), strtoupper($level), $message,
            $contextString, PHP_EOL);

        if (false === file_put_contents($this->logFilePath, $logEntry, FILE_APPEND)) {
            throw new \RuntimeException('Failed to write to log file: ' . $this->logFilePath);
        }
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
