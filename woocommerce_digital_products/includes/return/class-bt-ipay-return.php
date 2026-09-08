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

	private Bt_Ipay_Logger $logger;
	private Bt_Ipay_Payment_Storage $storage_service;
	private Bt_Ipay_Post_Request $request;
	private ?Bt_Ipay_Order $order_service = null;

	public function __construct( Bt_Ipay_Post_Request $request ) {
		$this->logger          = new Bt_Ipay_Logger();
		$this->storage_service = new Bt_Ipay_Payment_Storage();
		$this->request         = $request;
	}

	public function process() {
		try {
			$this->validate();

			$this->order_service = $this->get_order_service( $this->request->query( 'orderId' ) );
			$client        = new Bt_Ipay_Sdk_Client( new Bt_Ipay_Config() );
			( new Bt_Ipay_Finish_Processor(
				$client->payment_details(
					new Bt_Ipay_Sdk_Common_Payload(
						$this->request->query( 'orderId' )
					)
				),
				$this->order_service,
				$this->request->query( 'orderId' )
			) )->process();

			$this->redirect_to_success( $this->order_service->get_order() );
        } catch ( Bt_Ipay_Storage_Exception $e ) {
            $this->handle_failure( $e, __('Cannot process payment data.', 'bt-ipay-payments'));
        } catch ( \Throwable $th ) {
            $this->handle_failure( $th, $th->getMessage());
		}
	}

	private function validate() {
		if ( ! is_string( $this->request->query( 'orderId' ) ) ) {
			throw new \InvalidArgumentException( 'Invalid return `orderId`', 1 );
		}

		if ( ! is_string( $this->request->query( 'token' ) ) ) {
			throw new \InvalidArgumentException( 'Invalid return `token`', 1 );
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
    private function redirect_to_failure($failedRedirectUrl = null)
    {
        if ($failedRedirectUrl) {
            wp_safe_redirect($failedRedirectUrl);
        } else {
            wp_safe_redirect(wc_get_checkout_url());
        }
        exit;
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

    /**
     * Handles order retrieval and redirects to the failure page if needed.
     *
     * @param \Throwable $exception
     * @param string $userMessage
     * @return void
     */
    private function handle_failure(\Throwable $exception, string $userMessage) {
        try {
            $order = $this->order_service ? $this->order_service->get_order() : $this->get_order_service($this->request->query('orderId'))->get_order();
            $failedRedirectUrl = $order->get_checkout_payment_url(false);
        } catch (\Throwable $e) {
            // Fallback if order retrieval fails
            $this->logger->error('Order retrieval failed: ' . (string) $e);
            $failedRedirectUrl = wc_get_checkout_url();
        }

        $this->logger->error((string) $exception);
        wc_add_notice($userMessage, 'error');
        $this->redirect_to_failure($failedRedirectUrl);
    }
}
