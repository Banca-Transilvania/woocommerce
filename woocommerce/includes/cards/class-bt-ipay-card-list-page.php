<?php

/**
 *
 * @link       https://btepos.ro/module-ecommerce
 * @since      1.0.0
 *
 * @package    Bt_Ipay
 * @subpackage Bt_Ipay/includes/cards
 */

/**
 *
 * @since      1.0.0
 * @package    Bt_Ipay
 * @subpackage Bt_Ipay/includes/cards
 * @author     Banca Transilvania <no-reply@btepos.ro>
 */
class Bt_Ipay_Card_List_Page {

	private Bt_Ipay_Post_Request $request;

	private Bt_Ipay_Card_Storage $card_storage;

	public function __construct( Bt_Ipay_Post_Request $request ) {
		$this->request      = $request;
		$this->card_storage = new Bt_Ipay_Card_Storage();
	}

	/**
	 * Handle return from save card process
	 *
	 * @return void
	 */
	public function card_save_return() {
		if ( ! is_string( $this->request->query( 'orderId' ) ) ) {
			wc_add_notice( esc_html__( 'Could not perform action', 'bt-ipay-payments' ), 'error' );
		}
		try {
			( new Bt_Ipay_Card_Add( $this->card_storage ) )->process_return( (string) $this->request->query( 'orderId' ) );
		} catch ( \Throwable $th ) {
			wc_add_notice( esc_html__( 'Could not perform action', 'bt-ipay-payments' ), 'error' );
			( new Bt_Ipay_Logger() )->error( (string) $th );
		}
		wp_safe_redirect( wc_get_account_endpoint_url( 'bt-ipay-cards' ) );
	}

	/**
	 * Do request to save card
	 *
	 * @return void
	 */
	public function save_card() {
		check_ajax_referer( 'bt_ipay_nonce' );
		try {
			wp_send_json(
				array( 'redirect' => ( new Bt_Ipay_Card_Add( $this->card_storage ) )->start() )
			);
		} catch ( \Throwable $th ) {
			wc_add_notice( esc_html__( 'Could not perform action', 'bt-ipay-payments' ), 'error' );
			( new Bt_Ipay_Logger() )->error( (string) $th );
			wp_send_json(
				array( 'redirect' => wc_get_account_endpoint_url( 'bt-ipay-cards' ) )
			);
		}
	}

	public function decrypt(array $data)
	{
		return $this->card_storage->decrypt($data);
	}

	/**
	 * Delete card from list
	 *
	 * @return void
	 */
	public function delete_card() {
		check_ajax_referer( 'bt_ipay_nonce' );

		$card_id = $this->request->get( 'card_id' );
		if ( ! is_scalar( $card_id ) ) {
			$this->failed( esc_html__( 'Could not find card id', 'bt-ipay-payments' ) );
		}

		$card = $this->get_card();
		if (
			! is_array( $card ) ||
			! $this->can_change_card( $card )
			) {
			$this->failed( esc_html__( 'Could not perform action', 'bt-ipay-payments' ) );
		}


		if ( $card === null || ! isset( $card['ipay_id'] ) ) {
			$this->failed( __( 'Cannot find card', 'bt-ipay-payments' ) );
		}

		try {
			$this->toggle_status($card['ipay_id'], false, false);
		} catch (\Throwable $th) { // phpcs:ignore
		}
		$this->card_storage->delete_by_id( (int) $card_id );
		wc_add_notice( esc_html__( 'Card deleted successfully', 'bt-ipay-payments' ) );
	}


	/**
	 * Add menu to account page
	 *
	 * @param array $menu_links
	 *
	 * @return void
	 */
	public function add_menu_item( $menu_links ) {
		$menu_links['bt-ipay-cards'] = 'Bt Ipay Saved Cards';
		return $menu_links;
	}

	public function init_page() {
		add_rewrite_endpoint( 'bt-ipay-cards', EP_PAGES );
	}

	/**
	 * Show the card page
	 *
	 * @return void
	 */
	public function page_content() {
		$cards = $this->get_card_list();
		include_once BT_IPAY_PLUGIN_PATH . 'includes/cards/bt-ipay-card-list-template.php';
	}

	/**
	 * Enable or disable cards
	 *
	 * @return void
	 */
	public function toggle_card_state() {
		check_ajax_referer( 'bt_ipay_nonce' );

		$card = $this->get_card();

		if ( $card === null || ! isset( $card['ipay_id'] ) ) {
			$this->failed( __( 'Cannot find card', 'bt-ipay-payments' ) );
		}

		if ( ! $this->can_change_card( $card ) ) {
			$this->failed( __( 'Cannot update card', 'bt-ipay-payments' ) );
		}
		try {
			$ipay_card_id = $card['ipay_id'];
			$enable       = $this->request->get( 'enable' ) === 'true';
			$this->toggle_status($ipay_card_id, $enable);
		} catch ( \Throwable $th ) {
			$this->failed( __( 'Could not process action', 'bt-ipay-payments' ) );
			( new Bt_Ipay_Logger() )->error( (string) $th );
		}
	}

	private function toggle_status( string $ipay_card_id, bool $enable, bool $show_notice = true)
	{
		$client = new Bt_Ipay_Sdk_Client(
			new Bt_Ipay_Config()
		);

		( new Bt_Ipay_Card_State_Processor(
			$this->card_storage,
			$ipay_card_id,
			$enable
		) )->process(
			$client->toggle_card_status(
				new Bt_Ipay_Card_State_Payload( $ipay_card_id ),
				$enable
			),
			$show_notice
		);
	}

	/**
	 * Flash error message and exit
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	protected function failed( string $message ) {
		wc_add_notice( $message, 'error' );
		wp_die();
	}

	/**
	 * Check if card belongs to current customer
	 *
	 * @param array $card
	 *
	 * @return boolean
	 */
	private function can_change_card( array $card ): bool {
		return isset( $card['customer_id'] ) &&
		is_scalar( $card['customer_id'] ) &&
		(int) $card['customer_id'] === get_current_user_id();
	}

	/**
	 * Get card by id
	 *
	 * @return array
	 */
	private function get_card(): array {
		return $this->card_storage->find_by_id( (int) $this->request->get( 'card_id' ) );
	}

	/**
	 * Get cards for this customer
	 *
	 * @return array|null
	 */
	private function get_card_list(): ?array {
		return $this->card_storage->find_by_customer_id(
			get_current_user_id()
		);
	}
}
