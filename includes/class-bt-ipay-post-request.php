<?php

/**
 *
 * @link       https://btepos.ro/module-ecommerce
 * @since      1.0.0
 *
 * @package    Bt_Ipay
 * @subpackage Bt_Ipay/includes
 */

/**
 *
 * @since      1.0.0
 * @package    Bt_Ipay
 * @subpackage Bt_Ipay/includes
 * @author     Banca Transilvania <no-reply@btepos.ro>
 */
class Bt_Ipay_Post_Request {

	public function get( string $key, $default_value = null ) {

		//phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! isset( $_POST[ $key ] ) ) {
			return $default_value;
		}

		//phpcs:ignore WordPress.Security.NonceVerification.Missing
		$value = map_deep( wp_unslash( $_POST[ $key ] ), 'sanitize_text_field' );
		if ( is_string( $value ) && strlen( $value ) === 0 ) {
			return $default_value;
		}
		return $value;
	}

	public function query( string $key, $default_value = null ) {

		//phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $_GET[ $key ] ) ) {
			return $default_value;
		}

		//phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.Recommended
		$value = map_deep( wp_unslash( $_GET[ $key ] ), 'sanitize_text_field' );
		if ( is_string( $value ) && strlen( $value ) === 0 ) {
			return $default_value;
		}
		return $value;
	}
}
