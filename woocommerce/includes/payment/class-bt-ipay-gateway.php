<?php

use BTransilvania\Api\Exception\ApiException;

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
class Bt_Ipay_Gateway extends WC_Payment_Gateway {

	private array $db_settings;

	public function __construct() {

		$this->id           = 'bt-ipay';
		$this->method_title = __( 'BT iPay', 'bt-ipay-payments' );
		$this->supports     = array( 'products', 'refunds' );

		$this->init_settings();
		$this->init_form_fields();
		$this->set_config_values();
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	public function get_field_value( $key, $field, $post_data = array() ) {
		$value = parent::get_field_value( $key, $field, $post_data );
		if (
			in_array( $key, [ 'testAuthPassword', 'authPassword', 'webhookKey']) &&
			strlen($value) === 0
		)
		{
			$value = '';
			if (isset($this->db_settings[$key]))
			{
				$value = $this->db_settings[$key];
			}
		}

		return $value;
	}

	public function init_settings() {

		parent::init_settings();

		$this->db_settings = $this->settings;
		$this->settings = array_merge(
			$this->settings,
			[
				'testAuthPassword' => '',
				'authPassword' => '',
				'webhookKey' => ''
			]
		);
	}


	public function init_form_fields() {
		$this->form_fields = Bt_Ipay_Configuration::get($this->db_settings);
	}

	public function process_payment( $order_id ) {
		try {
			$request       = new Bt_Ipay_Post_Request();
			$order_service = new Bt_Ipay_Order( new WC_Order( (int) $order_id ) );
			$client        = new Bt_Ipay_Sdk_Client( new Bt_Ipay_Config() );

			$response = $client->start_payment(
				new Bt_Ipay_Pay_Payload(
					$order_service,
					$this->can_do_request_with_cards( $request ),
					$this->get_bt_pay_card_id( $request ),
					$this->get_option( 'paymentDescription' )
				)
			);
			return $response->get_result();
		} catch ( ApiException $th ) {
			( new Bt_Ipay_Logger() )->error( (string) $th );
			throw new \Exception( esc_html__( 'Could not perform action, contact merchant for additional info', 'bt-ipay-payments' ) );
		} catch ( \Throwable $th ) {
			( new Bt_Ipay_Logger() )->error( (string) $th );
			throw $th;
		}
	}

	/**
	 * Can do request with saved creditcard
	 *
	 * @param Bt_Ipay_Post_Request $request
	 *
	 * @return boolean
	 */
	public function can_do_request_with_cards( Bt_Ipay_Post_Request $request ): bool {
		return $this->can_show_cards_on_file() && $request->get( 'bt_ipay_save_cards' ) !== null;
	}

	/**
	 * Can show cards on file functionality
	 *
	 * @return boolean
	 */
	public function can_show_cards_on_file(): bool {
		return $this->get_option( 'enabledCardSave', 'no' ) === 'yes' && get_current_user_id() !== 0;
	}

	/**
	 * Get card id if users wants to do a saved card payment
	 *
	 * @param Bt_Ipay_Post_Request $request
	 *
	 * @return string|null
	 */
	private function get_bt_pay_card_id( Bt_Ipay_Post_Request $request ): ?string {
		if ( ! is_scalar( $request->get( 'bt_ipay_card_id' ) ) ) {
			return null;
		}

		$card = ( new Bt_Ipay_Card_Storage() )->find_by_id( (int) $request->get( 'bt_ipay_card_id' ) );

		if (
			! isset( $card['customer_id'] ) ||
			get_current_user_id() !== (int) $card['customer_id'] ||
			$request->get( 'bt_ipay_use_new_card' ) !== null ||
			! isset( $card['ipay_id'] )
		) {
			return null;
		}

		return $card['ipay_id'];
	}

	public function payment_fields() {
		include_once BT_IPAY_PLUGIN_PATH . 'includes/payment/bt-ipay-payment-template.php';
	}

	public function get_user_saved_card(): ?array {
		$card_storage = new Bt_Ipay_Card_Storage();
		return $card_storage->find_enabled_by_customer_id( get_current_user_id() );
	}

	/** @return bool|WP_Error */
	public function process_refund(
		$order_id,
		$amount = null,
		$reason = ''
	) {
		if ( ! is_scalar( $order_id ) ) {
			return new WP_Error( 'error', __( 'Invalid order id', 'bt-ipay-payments' ) );
		}

		if ( ! is_scalar( $amount ) ) {
			return new WP_Error( 'error', __( 'Invalid refund amount', 'bt-ipay-payments' ) );
		}
		return (
			new Bt_Ipay_Refund_Processor(
				(int) $order_id,
				number_format( floatval( $amount ), 2 )
			)
		)->process();
	}


	private function set_config_values() {
		$this->title       = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );
		$this->icon        = BT_IPAY_PLUGIN_URL . 'public/img/bt.png';
	}
}
