<?php

/**
 *
 * @link       https://btepos.ro/module-ecommerce
 * @since      1.0.0
 *
 * @package    Bt_Ipay
 * @subpackage Bt_Ipay/includes/capture
 */

use BTransilvania\Api\Config\Config;

/**
 *
 * @since      1.0.0
 * @package    Bt_Ipay
 * @subpackage Bt_Ipay/includes/capture
 * @author     Banca Transilvania <no-reply@btepos.ro>
 */
class Bt_Ipay_Customer {

	private WC_Order $order;

	public function __construct( WC_Order $order ) {
		$this->order = $order;
	}

	public function get_email(): string {
		return $this->order->get_billing_email();
	}

	public function get_phone(): string {
		$phone = $this->clean_phone( $this->order->get_billing_phone() );
		if ( substr( $phone, 0, 2 ) === '07' ) {
			$phone = '4' . $phone;
		}
		return $phone;
	}

	public function get_full_name() {
		return substr(
			Bt_Ipay_Formatter::format(
				$this->order->get_billing_first_name() . ' ' . $this->order->get_billing_last_name()
			),
			0,
			40
		);
	}

	private function clean_phone( $phone ): string {
		if ( ! is_scalar( $phone ) ) {
			return '';
		}
		return trim( preg_replace( '/[^0-9]/', '', (string) $phone ) );
	}
}
