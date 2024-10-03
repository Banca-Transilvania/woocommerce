<?php

/**
 *
 * @link       https://btepos.ro/module-ecommerce
 * @since      1.0.0
 *
 * @package    Bt_Ipay
 * @subpackage Bt_Ipay/includes/admin
 */

/**
 *
 * @since      1.0.0
 * @package    Bt_Ipay
 * @subpackage Bt_Ipay/includes/admin
 * @author     Banca Transilvania <no-reply@btepos.ro>
 */
class Bt_Ipay_Capture_Result implements Bt_Ipay_Action_Result {


	protected float $loy_amount          = 0.0;
	protected float $pay_amount          = 0.0;
	protected float $previously_captured = 0.0;

	protected ?string $error_message = null;

	protected bool $has_internal_error = false;

	protected bool $payment_was_cancelled = false;

	public function set_loy_amount( float $loy_amount ) {
		$this->loy_amount = $loy_amount;
	}

	public function set_pay_amount( float $pay_amount ) {
		$this->pay_amount = $pay_amount;
	}

	public function get_loy_amount(): float {
		return $this->loy_amount;
	}

	public function get_pay_amount(): float {
		return $this->pay_amount;
	}

	public function set_error_message( ?string $error_message ) {
		$this->error_message = $error_message;
	}

	public function has_error(): bool {
		return ! is_null( $this->error_message );
	}

	public function has_loy(): bool {
		return $this->loy_amount > 0;
	}

	public function has_payment(): bool {
		return $this->pay_amount > 0;
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

	/**
	 * Get total amount captured in this request
	 *
	 * @return float
	 */
	public function get_total(): float {
		return $this->pay_amount + $this->loy_amount;
	}

	public function get_error_message(): string {
		if ( $this->has_internal_error() ) {
			return esc_html__( 'Could not process request, check woocommerce logs for errors', 'bt-ipay-payments' );
		}
		return $this->error_message ?? '';
	}

	public function add_previously_captured( float $amount ) {
		$this->previously_captured += $amount;
	}

	/**
	 * Get total captured in this request and previous requests
	 *
	 * @return float
	 */
	public function get_total_captured(): float {
		return $this->get_total() + $this->previously_captured;
	}

	public function payment_was_cancelled() {
		$this->payment_was_cancelled = true;
	}
	public function is_payment_cancelled(): bool {
		return $this->payment_was_cancelled;
	}
}
