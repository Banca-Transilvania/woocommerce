<?php

/**
 *
 * @link       https://btepos.ro/module-ecommerce
 * @since      1.0.0
 *
 * @package    Bt_Ipay
 * @subpackage Bt_Ipay/includes/payment
 */

/**
 *
 * @since      1.0.0
 * @package    Bt_Ipay
 * @subpackage Bt_Ipay/includes/payment
 * @author     Banca Transilvania <no-reply@btepos.ro>
 */
class Bt_Ipay_Config {

	private array $configuration;

	public function __construct() {
		$this->configuration = get_option( 'woocommerce_bt-ipay_settings', array() );
	}

	public function is_test_mode(): bool {
		return $this->get_value( 'testMode', 'yes' ) === 'yes';
	}

	public function get_auth_key(): ?string {
		$value = $this->get_value( 'authKey' );
		if ( $this->is_test_mode() ) {
			$value = $this->get_value( 'testAuthKey' );
		}

		if ( ! is_string( $value ) || empty( $value ) ) {
			return null;
		}
		return $value;
	}

	public function get_callback_key(): string {
		$value = $this->get_value( 'webhookKey' );

		if ( ! is_string( $value ) || empty( $value ) ) {
			return '';
		}
		return $value;
	}


	public function get_auth_password(): ?string {
		$value = $this->get_value( 'authPassword' );
		if ( $this->is_test_mode() ) {
			$value = $this->get_value( 'testAuthPassword' );
		}

		if ( ! is_string( $value ) || empty( $value ) ) {
			return null;
		}
		return $value;
	}

	public function can_log_payload(): bool {
        return $this->get_value( 'logPayload', 'no' ) === 'yes';
    }

	public function cof_enabled(): bool {
		return $this->get_value( 'enabledCardSave', 'no' ) === 'yes';
	}

	public function can_call_sdk(): bool {
		return $this->get_auth_key() !== null && $this->get_auth_password() !== null;
	}

	public function get_payment_flow(): string {
		$flow = $this->get_value( 'paymentFlow', Bt_Ipay_Configuration::FLOW_PAY );
		if ( ! in_array( $flow, array( Bt_Ipay_Configuration::FLOW_PAY, Bt_Ipay_Configuration::FLOW_AUTHORIZE ), true ) ) {
			$flow = Bt_Ipay_Configuration::FLOW_PAY;
		}
		return $flow;
	}

	private function get_value( string $key, $default_value = null ) {
		return $this->configuration[ $key ] ?? $default_value;
	}
}
