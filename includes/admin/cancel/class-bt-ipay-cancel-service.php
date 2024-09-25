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
class Bt_Ipay_Cancel_Service {

	protected Bt_Ipay_Cancel_Result $result;

	protected Bt_Ipay_Logger $logger;


	public function __construct( Bt_Ipay_Cancel_Result $result, Bt_Ipay_Logger $logger ) {
		$this->result = $result;
		$this->logger = $logger;
	}

	public function cancel(
		string $payment_engine_id
	) {
		$payment = $this->get_payment_details( $payment_engine_id );
		$loy_id  = $payment->get_loy_id();

		try {
			if ( is_string( $loy_id ) ) {
				$this->cancel_loy( $payment );
			}

			if ( ! $payment->is_authorized() ) {
				return $this->result->set_error_message( esc_html( 'Invalid payment status' ) );
			}
			$response = $this->cancel_part( $payment_engine_id );
			if ( ! $response->is_successful() ) {
				return $this->result->set_error_message( esc_html( $response->get_error_message() ?? '' ) );
			}
			$this->result->is_payment();
		} catch ( \Throwable $th ) {
			$this->logger->error( (string) $th );
			$this->result->internal_error();
		}
	}


	private function cancel_loy(
		Bt_Ipay_Sdk_Detail_Response $payment
	) {
		$loy = $this->get_payment_details( $payment->get_loy_id() );
		if ( $loy->is_authorized() ) {
			$response = $this->cancel_part( $payment->get_loy_id() );

			if ( ! $response->is_successful() ) {
				return $this->result->set_error_message( esc_html( $response->get_error_message() ?? '' ) );
			}
			$this->result->is_loy();
		}
	}


	private function cancel_part(
		string $payment_engine_id
	) {
		return $this->get_client()->cancel(
			new Bt_Ipay_Cancel_Payload( $payment_engine_id )
		);
	}

	private function get_payment_details( string $payment_engine_id ): Bt_Ipay_Sdk_Detail_Response {
		$details = $this->get_client()->payment_details( new Bt_Ipay_Sdk_Common_Payload( $payment_engine_id ) );
		if ( ! $details->is_successful() ) {
			throw new Bt_Ipay_Cancel_Exception( esc_html( $details->get_error_message() ?? '' ) );
		}
		return $details;
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
