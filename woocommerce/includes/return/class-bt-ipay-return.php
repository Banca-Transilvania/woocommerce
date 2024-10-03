<?php

use Automattic\WooCommerce\Admin\Overrides\Order;

/**
 *
 * @link       https://btepos.ro/module-ecommerce
 * @since      1.0.0
 *
 * @package    Bt_Ipay
 * @subpackage Bt_Ipay/includes/return
 */

/**
 *
 * @since      1.0.0
 * @package    Bt_Ipay
 * @subpackage Bt_Ipay/includes/return
 * @author     Banca Transilvania <no-reply@btepos.ro>
 */
class Bt_Ipay_Return {


	private $logger;

	private Bt_Ipay_Payment_Storage $storage_service;

	private Bt_Ipay_Post_Request $request;

	public function __construct( Bt_Ipay_Post_Request $request ) {
		$this->logger          = new Bt_Ipay_Logger();
		$this->storage_service = new Bt_Ipay_Payment_Storage();
		$this->request         = $request;
	}

	public function process() {
		try {
			$this->validate();

			$order_service = $this->get_order_service( $this->request->query( 'orderId' ) );
			$client        = new Bt_Ipay_Sdk_Client( new Bt_Ipay_Config() );
			( new Bt_Ipay_Finish_Processor(
				$client->payment_details(
					new Bt_Ipay_Sdk_Common_Payload(
						$this->request->query( 'orderId' )
					)
				),
				$order_service,
				$this->request->query( 'orderId' )
			) )->process();

			$this->redirect_to_success( $order_service->get_order() );
		} catch ( Bt_Ipay_Storage_Exception $th ) {
			$this->logger->error( (string) $th );
			wc_add_notice( __( 'Cannot not process payment data.', 'bt-ipay-payments' ), 'error' );
			$this->redirect_to_failure();
		} catch ( \Throwable $th ) {
			wc_add_notice( $th->getMessage(), 'error' );
			$this->logger->error( (string) $th );
			$this->redirect_to_failure();
		}
	}

	private function validate() {
		if ( ! is_string( $this->request->query( 'orderId' ) ) ) {
			throw new \Exception( 'Invalid return `orderId`', 1 );
		}

		if ( ! is_string( $this->request->query( 'token' ) ) ) {
			throw new \Exception( 'Invalid return `token`', 1 );
		}
	}

	/**
	 * Redirect to order success page
	 *
	 * @return void
	 */
	private function redirect_to_success( WC_Order $order ) {
		/** @var WC_Payment_Gateway */
		$gateway = $this->get_payment_gateway();

		if ( $gateway !== null ) {
			wp_safe_redirect( $gateway->get_return_url( $order ) );
			exit;
		}
		wc_add_notice( __( 'Cannot find bt ipay gateway.', 'bt-ipay-payments' ) );
		$this->redirect_to_failure();
	}

	/**
	 * Redirect user back to checkout page
	 *
	 * @return void
	 */
	private function redirect_to_failure() {
		wp_safe_redirect( wc_get_checkout_url() );
	}


	/**
	 * Get our payment gateway
	 *
	 * @return Bt_Ipay_Gateway|null
	 */
	private function get_payment_gateway() {
		if ( ! function_exists( 'WC' ) ) {
			return;
		}
		$gateways = WC()->payment_gateways->payment_gateways();

		foreach ( $gateways as $gateway ) {
			if ( $gateway->id === 'bt-ipay' ) {
				return $gateway;
			}
		}
	}

	private function get_order_service( string $payment_engine_id ): Bt_Ipay_Order {
		$storage = $this->storage_service->find_by_payment_engine_id( $payment_engine_id );
		if ( ! is_array( $storage ) || ! array_key_exists( 'order_id', $storage ) ) {
			throw new \Exception( 'Could not find order data' );
		}
		$order = new WC_Order( $storage['order_id'] );
		return new Bt_Ipay_Order( $order );
	}
}
