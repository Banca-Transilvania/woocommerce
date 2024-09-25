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
class Bt_Ipay_Capture_Service {

	protected Bt_Ipay_Capture_Result $result;

	protected Bt_Ipay_Logger $logger;


	public function __construct( Bt_Ipay_Capture_Result $result, Bt_Ipay_Logger $logger ) {
		$this->result = $result;
		$this->logger = $logger;
	}
	public function capture(
		string $payment_engine_id,
		float $amount
	) {

		$payment    = $this->get_payment_details( $payment_engine_id );
		$loy_amount = $payment->get_loy_amount();
		$this->result->add_previously_captured( $payment->get_total_available() );
		try {
			$loy_amount_captured = 0;
			if ( $loy_amount > 0 ) {
				$loy_amount_captured = $this->capture_loy(
					$payment,
					$amount,
					$loy_amount
				);
				$this->result->set_loy_amount( $loy_amount_captured );
			}

			$amount -= $loy_amount_captured;

			if ( $amount <= 0 ) {
				$this->cancel_payment( $payment_engine_id, $payment->is_authorized() );
				return;
			}

			if ( ! $payment->is_authorized() ) {
				return $this->result->set_error_message( esc_html( 'Invalid payment status' ) );
			}

			$response = $this->capture_part( $payment_engine_id, $amount );
			if ( ! $response->is_successful() ) {
				return $this->result->set_error_message( esc_html( $response->get_error_message() ?? '' ) );
			}
			$this->result->set_pay_amount( $amount );
		} catch ( \Throwable $th ) {
			$this->logger->error( (string) $th );
			$this->result->internal_error();
		}
	}


	private function capture_loy(
		Bt_Ipay_Sdk_Detail_Response $payment,
		float $amount,
		float $loy_amount
	): float {
		$loy = $this->get_payment_details( $payment->get_loy_id() );
		$this->result->add_previously_captured( $loy->get_total_available() );
		if ( $loy->is_authorized() ) {
			$total_loy = $this->determine_amount( $amount, $loy_amount );
			$response  = $this->capture_part( $payment->get_loy_id(), $total_loy );

			if ( ! $response->is_successful() ) {
				$this->result->set_error_message( esc_html( $response->get_error_message() ?? '' ) );
				return 0;
			}

			return $total_loy;
		}
		return 0.0;
	}

	private function cancel_payment( string $payment_engine_id, bool $is_authorized ) {
		if ( ! $is_authorized ) {
			return;
		}
		$response = $this->get_client()->cancel(
			new Bt_Ipay_Cancel_Payload(
				$payment_engine_id
			)
		);

		if ( ! $response->is_successful() ) {
			return $this->result->set_error_message( esc_html( $response->get_error_message() ?? '' ) );
		}
		$this->result->payment_was_cancelled();
	}


	private function capture_part(
		string $payment_engine_id,
		float $amount
	) {
		return $this->get_client()->capture(
			new Bt_Ipay_Capture_Payload(
				$payment_engine_id,
				$amount * 100
			)
		);
	}

	private function get_payment_details( string $payment_engine_id ): Bt_Ipay_Sdk_Detail_Response {
		$details = $this->get_client()->payment_details( new Bt_Ipay_Sdk_Common_Payload( $payment_engine_id ) );
		if ( ! $details->is_successful() ) {
			throw new Bt_Ipay_Capture_Exception( esc_html( $details->get_error_message() ?? '' ) );
		}
		return $details;
	}

	/**
	 * Determine amount to be captured for loy
	 *
	 * @param float $total
	 * @param float $max_amount
	 *
	 * @return float
	 */
	private function determine_amount( float $total, float $max_amount ): float {
		if ( $total > $max_amount ) {
			return $max_amount;
		}

		return $total;
	}

	/**
	 * Get sdk client
	 *
	 * @return Bt_Ipay_Sdk_Client
	 */
	private function get_client(): Bt_Ipay_Sdk_Client {
		return new Bt_Ipay_Sdk_Client( new Bt_Ipay_Config() );
	}
}
