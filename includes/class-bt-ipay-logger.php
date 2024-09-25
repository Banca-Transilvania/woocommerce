<?php

/**
 *
 * @link       https://btepos.ro/module-ecommerce
 * @since      1.0.0
 *
 * @package    Bt_Ipay
 * @subpackage Bt_Ipay
 */

/**
 *
 * @since      1.0.0
 * @package    Bt_Ipay
 * @subpackage Bt_Ipay
 * @author     Banca Transilvania <no-reply@btepos.ro>
 */
class Bt_Ipay_Logger {


	public function log( string $message, string $level = 'info' ) {
		if ( function_exists( 'wc_get_logger' ) ) {
			wc_get_logger()->log( $level, $message, array( 'source' => 'bt-ipay' ) );
			return;
		}
	}

	public function error( string $message ) {
		$this->log( $message, 'error' );
	}
}
