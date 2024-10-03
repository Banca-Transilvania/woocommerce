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
class Bt_Ipay_Refund {

	private float $amount;

	private string $payment_engine_id;

	private Bt_Ipay_Refund_Result $result;

	public function __construct( string $payment_engine_id, float $amount, Bt_Ipay_Refund_Result $result ) {
		$this->amount            = $amount;
		$this->payment_engine_id = $payment_engine_id;
		$this->result            = $result;
	}

	public function execute() {
		$client  = $this->get_sdk_client();
		$payment = $this->get_payment_details( $client );

		$max_refunded = $this->amount;

		$payment_total = $payment->get_total_available();

		$refund_both = false;
		if ( $payment->get_loy_amount() > 0 ) {

			$loy = $this->get_loy_details( $client, $payment->get_loy_id() );

			if ( $loy->can_refund() && $loy->get_total_available() > 0 ) {
				$payment_total += $loy->get_total_available();
				$refund_both    = true;
			}
		}

		$this->validate_amount( $payment_total );

		if ( $refund_both === true ) {
			$loy_refunded = $this->refund_loy(
				$client,
				$payment->get_loy_id(),
				$loy->get_total_available()
			);
			$this->result->is_loy();

			$max_refunded -= $loy_refunded;

			$this->result->is_partial_loy(
				$loy->get_total_available() - $loy_refunded > 0.001
			);
		}

		if ( $max_refunded <= 0 ) {
			return;
		}

		$this->refund_payment( $client, $max_refunded );

		$this->result->is_partial_payment(
			$payment->get_total_available() - $max_refunded > 0.001
		);
	}

	private function validate_amount( float $payment_total ) {
		if ( $payment_total === 0.0 ) {
			throw new Bt_Ipay_Refund_Exception(
				esc_html__(
					'Cannot process refund, no available amount found for refund',
					'bt-ipay-payments'
				)
			);
		}

		if ( $this->amount - $payment_total > 0.001 ) {
			throw new Bt_Ipay_Refund_Exception(
				sprintf(
					/* translators: %s: amount */
					esc_html__( 'Cannot process refund, a maximum of %s can be refunded', 'bt-ipay-payments' ),
					html_entity_decode( wp_strip_all_tags( wc_price( $payment_total ) ) )//phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
				)
			);
		}
	}

	private function refund_payment( Bt_Ipay_Sdk_Client $client, float $max_refunded ) {
		$response = $client->refund(
			new Bt_Ipay_Refund_Payload(
				$this->payment_engine_id,
				$max_refunded
			)
		);

		if ( ! $response->is_successful() ) {
			throw new Bt_Ipay_Refund_Exception(
				/* translators: %s: error message */
				sprintf( esc_html__( 'Cannot process refund: %s', 'bt-ipay-payments' ), esc_html( $response->get_error_message() ) )
			);
		}

		$this->result->is_payment();
	}

	private function get_payment_details( Bt_Ipay_Sdk_Client $client ): Bt_Ipay_Sdk_Detail_Response {
		return $client->payment_details(
			new Bt_Ipay_Sdk_Common_Payload( $this->payment_engine_id )
		);
	}

	private function refund_loy( Bt_Ipay_Sdk_Client $client, string $loy_id, float $amount ): float {
		$loy_amount = $this->determine_amount( $amount );

		$response = $client->refund(
			new Bt_Ipay_Refund_Payload(
				$loy_id,
				$loy_amount
			)
		);

		if ( ! $response->is_successful() ) {
			throw new Bt_Ipay_Refund_Exception(
				/* translators: %s: error message */
				sprintf( esc_html__( 'Cannot process refund: %s', 'bt-ipay-payments' ), esc_html( $response->get_error_message() ) )
			);
		}
		return $loy_amount;
	}

	private function get_loy_details( Bt_Ipay_Sdk_Client $client, string $loy_id ): Bt_Ipay_Sdk_Detail_Response {
		return $client->payment_details(
			new Bt_Ipay_Sdk_Common_Payload( $loy_id )
		);
	}

	private function determine_amount( float $max_amount ): float {
		if ( $this->amount > $max_amount ) {
			return $max_amount;
		}

		return $this->amount;
	}

	private function get_sdk_client(): Bt_Ipay_Sdk_Client {
		return new Bt_Ipay_Sdk_Client( new Bt_Ipay_Config() );
	}
}
