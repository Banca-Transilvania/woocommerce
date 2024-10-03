<?php

/**
 *
 * @link       https://btepos.ro/module-ecommerce
 * @since      1.0.0
 *
 * @package    Bt_Ipay
 * @subpackage Bt_Ipay
 */

use BTransilvania\Api\Logger\LogLevel;
use BTransilvania\Api\Logger\LoggerInterface;

/**
 *
 * @since      1.0.0
 * @package    Bt_Ipay
 * @subpackage Bt_Ipay
 * @author     Banca Transilvania <no-reply@btepos.ro>
 */
class Bt_Ipay_Logger_Sdk implements LoggerInterface {

    private $can_log = false;
    public function __construct() {
        $this->can_log = (new Bt_Ipay_Config())->can_log_payload();
    }

	public function emergency($message, array $context = [])
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    public function log($level, $message, array $context = [])
    {
		if ( function_exists( 'wc_get_logger' ) && $this->can_log ) {
			wc_get_logger()->log( $level, $message, array( 'source' => 'bt-ipay' ) );
			return;
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
