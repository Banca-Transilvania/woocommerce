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
class Bt_Ipay_Address {

	private WC_Order $order;

	private string $type;

	public function __construct( WC_Order $order, string $type = 'billing' ) {
		$this->order = $order;

		if ( in_array( $type, array( 'billing', 'shipping' ) ) ) {
			$type = 'billing';
		}
		$this->type = $type;
	}

	public function get_country(): string {
		return $this->get_string( 'country' );
	}

	public function get_city(): string {
		return $this->get_string( 'city' );
	}

	public function get_postal_code(): string {
		return $this->get_string( 'postcode' );
	}

	public function get_full_address(): string {
		return $this->get_address_1() . ' ' . $this->get_address_2();
	}

	public function get_address_1(): string {
		return $this->get_string( 'address_1' );
	}

	public function get_address_2(): string {
		return $this->get_string( 'address_2' );
	}

	private function get_string( $field ): string {
		$value = $this->get( $field );
		if ( is_scalar( $value ) ) {
			return Bt_Ipay_Formatter::format( (string) $value );
		}
		return '';
	}

	private function get( $field, $default_value = '' ) {
		$method = 'get_' . $this->type . '_' . $field;
		if ( method_exists( $this->order, $method ) ) {
			return $this->order->{$method}();
		}
		return $default_value;
	}
}
