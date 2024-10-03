<?php


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
class Bt_Ipay_Finish_Processor {

	public const ORDER_CANCEL_CODE = '341018';

	private Bt_Ipay_Sdk_Detail_Response $response;
	private Bt_Ipay_Payment_Storage $payment_storage;
	private Bt_Ipay_Card_Storage $card_storage;
	private Bt_Ipay_Order $order_service;
	private string $payment_engine_id;

	public function __construct(
		Bt_Ipay_Sdk_Detail_Response $response,
		Bt_Ipay_Order $order_service,
		string $payment_engine_id
	) {
		$this->response          = $response;
		$this->order_service     = $order_service;
		$this->payment_storage   = new Bt_Ipay_Payment_Storage();
		$this->card_storage      = new Bt_Ipay_Card_Storage();
		$this->payment_engine_id = $payment_engine_id;
	}

	public function process() {

		if ( $this->store_data() === false ) {
			throw new \Exception( esc_html__( 'Could not save finish response', 'bt-ipay-payments' ) );
		}
		if ( $this->response->payment_is_accepted() ) {
			$this->save_card_data();
			$this->order_service->update_status(
				$this->response->is_authorized() ? 'on-hold' : ($this->order_service->needs_processing() ? 'processing': 'completed'),
				/* translators: %s: payment id */
				sprintf( esc_html__( 'Created payment transaction %s', 'bt-ipay-payments' ), $this->payment_engine_id )
			);
		} else {
			$this->order_service->update_status(
				'failed',
				$this->get_status_message()
			);
			throw new \Exception(
				sprintf(
					/* translators: %s: payment error message */
					esc_html__( 'Could not process payment:  %s', 'bt-ipay-payments' ),
					esc_html( $this->get_consumer_message() )
				)
			);
		}
	}

	private function get_consumer_message() {
		//if insufficient loy get correct message
		if (
			$this->response->get_status() === Bt_Ipay_Payment_Storage::STATUS_REVERSED &&
			$this->response->get_loy_id() !== null
		) {
			return esc_html__( 'Insufficient funds.', 'bt-ipay-payments' );
		}

		return $this->response->get_error();
	}


	private function store_data() {
		return $this->payment_storage->update_data(
			$this->response->get_status(),
			$this->response->get_amount(),
			$this->response->get_loy_id(),
			$this->response->get_loy_amount(),
			$this->payment_engine_id
		);
	}

	private function get_status_message() {

		$error_message = $this->response->get_error();

		if ( $error_message === null ) {
			$error_message = 'Unknown error';
		}
		/* translators: %s: error message */
		$message = sprintf( esc_html__( 'Could not create payment: %s', 'bt-ipay-payments' ), $error_message );

		$messages = array(
			self::ORDER_CANCEL_CODE => esc_html__( 'Order has been canceled by the user', 'bt-ipay-payments' ),
		);

		return $messages[ $this->response->get_code() ] ?? $message;
	}

	private function save_card_data() {
		$card_data = $this->response->get_card_info();
		if ( $card_data === null ) {
			return;
		}
		return $this->card_storage->create( $card_data );
	}
}
