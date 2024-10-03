<?php

use BTransilvania\Api\Model\Response\RegisterResponseModel;


/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://btepos.ro/module-ecommerce
 * @since      1.0.0
 *
 * @package    Bt_Ipay
 * @subpackage Bt_Ipay/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Bt_Ipay
 * @subpackage Bt_Ipay/includes
 * @author     Banca Transilvania <no-reply@btepos.ro>
 */
class Bt_Ipay_Sdk_Pay_Response {


	private RegisterResponseModel $response;
	private Bt_Ipay_Order $order_service;
	private bool $is_authorize;
	private Bt_Ipay_Payment_Storage $storage_service;

	public function __construct(
		RegisterResponseModel $response,
		Bt_Ipay_Order $order_service,
		bool $is_authorize
	) {
		$this->response        = $response;
		$this->order_service   = $order_service;
		$this->is_authorize    = $is_authorize;
		$this->storage_service = new Bt_Ipay_Payment_Storage();
	}

	public function get_result() {
		if ( $this->response->isSuccess() ) {
			$this->store_payment();
			return array(
				'result'   => 'success',
				'redirect' => $this->response->getRedirectUrl(),
			);
		} else {
			$error = $this->format_error_message( $this->response->getErrorMessage() );
			wc_add_notice( $error, 'error' );
			return array(
				'result'  => 'failure',
				'message' => $error,
			);
		}
	}

	private function format_error_message( ?string $message ): string {
		if ( $message === null ) {
			return esc_html__( 'Unknown error occurred', 'bt-ipay-payments' );
		}

		if ( strpos( $message, 'orderBundle' ) !== false ) {
			$fields = explode( '.', $message );
			$fields = array_reverse( $fields );
			if ( isset( $fields[0] ) ) {
				$value = explode( ']', $fields[0] )[0];
				if ( strpos( $message, 'deliveryInfo' ) !== false ) {
					/* translators: %s: form field */
					return sprintf( esc_html__( 'Invalid shipping address field: %s', 'bt-ipay-payments' ), esc_attr( $value ) );
				}

				if ( strpos( $message, 'billingInfo' ) !== false ) {
					/* translators: %s: form field */
					return sprintf( esc_html__( 'Invalid billing address field: %s', 'bt-ipay-payments' ), esc_attr( $value ) );
				}
			}
		}
		return $message;
	}

	private function store_payment() {
		$payment_engine_id = $this->response->orderId;
		if ( is_string( $payment_engine_id ) ) {
			$this->storage_service->create(
				$this->order_service->get_id(),
				$payment_engine_id
			);
		}
	}
}
