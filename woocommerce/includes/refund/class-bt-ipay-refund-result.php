<?php

/**
 *
 * @link       https://btepos.ro/module-ecommerce
 * @since      1.0.0
 *
 * @package    Bt_Ipay
 * @subpackage Bt_Ipay/includes/refund
 */

/**
 *
 * @since      1.0.0
 * @package    Bt_Ipay
 * @subpackage Bt_Ipay/includes/refund
 * @author     Banca Transilvania <no-reply@btepos.ro>
 */
class Bt_Ipay_Refund_Result {

	protected ?string $error_message = null;

	protected bool $has_internal_error = false;

	protected $loy = false;

	protected $payment = false;

	protected $partial_loy = false;

	protected $partial_payment = false;

	public function set_error_message( ?string $error_message ) {
		$this->error_message = $error_message;
	}

	public function has_error(): bool {
		return ! is_null( $this->error_message );
	}

	public function is_loy() {
		$this->loy = true;
	}

	public function is_payment() {
		$this->payment = true;
	}

	public function has_loy(): bool {
		return $this->loy;
	}

	public function has_payment(): bool {
		return $this->payment;
	}

	public function is_partial(): bool {
		return $this->has_loy() && ( $this->has_error_message() || $this->has_internal_error() );
	}

	public function internal_error() {
		$this->has_internal_error = true;
	}

	public function has_error_message(): bool {
		return ! is_null( $this->error_message );
	}

	public function has_internal_error(): bool {
		return $this->has_internal_error;
	}

	public function get_error_message(): string {
		if ( $this->has_internal_error() ) {
			return esc_html__( 'Could not process request, check woocommerce logs for errors', 'bt-ipay-payments' );
		}
		return $this->error_message ?? '';
	}


	public function is_partial_payment( bool $status ) {
		$this->partial_payment = $status;
	}

	public function is_partial_loy( bool $status ) {
		$this->partial_loy = $status;
	}

	public function is_loy_partial(): bool {
		return $this->partial_loy;
	}
	public function is_payment_partial(): bool {
		return $this->partial_payment;
	}
}
