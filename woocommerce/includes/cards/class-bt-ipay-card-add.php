<?php

use BTransilvania\Api\Model\Response\RegisterResponseModel;

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
class Bt_Ipay_Card_Add {

	protected Bt_Ipay_Card_Storage $storage;

	public function __construct( Bt_Ipay_Card_Storage $storage ) {
		$this->storage = $storage;
	}

	public function start() {
		$user = get_current_user_id();
		if ( $user === 0 ) {
			return $this->return( esc_html__( 'Could not save card', 'bt-ipay-payments' ) );
		}

		$payload = array(
			'orderNumber' => preg_replace( '/\s+/', '_',  'CARD' . microtime( false ) ),
			'amount'      => 0,
			'currency'    => 'RON',
			'returnUrl'   => add_query_arg( 'wc-api', 'bt_card_return', home_url( '/' ) ),
			'clientId'    => $user,
			'description' => esc_html__( 'Save card for later use', 'bt-ipay-payments' ),
		);

		return $this->process(
			( new Bt_Ipay_Sdk_Client( new Bt_Ipay_Config() ) )->save_card( $payload )
		);
	}

	/**
	 * Process card save return
	 *
	 * @return void
	 */
	public function process_return( string $payment_engine_id ) {

		$user = get_current_user_id();
		if ( $user === 0 ) {
			return $this->flash( esc_html__( 'Could not save card', 'bt-ipay-payments' ), true );
		}

		$client = new Bt_Ipay_Sdk_Client(
			new Bt_Ipay_Config()
		);

		$response = $client->payment_details( new Bt_Ipay_Sdk_Common_Payload( $payment_engine_id ) );

		if ( $response->is_successful() ) {
			if ( ! $response->can_save_card() ) {
				return $this->flash( 
					sprintf(
						/* translators: %s: captured amount */
						esc_html__( 'Could not save card, invalid data provided - %s', 'bt-ipay-payments' ),
						esc_html( $response->get_error() )
					)
					, true );
			}
			$card_data = $this->get_card_data( $response );
			if ( $card_data !== null ) {
				$this->storage->create( $card_data );
				return $this->flash( esc_html__( 'Card saved successfully', 'bt-ipay-payments' ) );
			}
			return $this->flash( esc_html__( 'This card is already registered', 'bt-ipay-payments' ), true );
		}
		( new Bt_Ipay_Logger() )->error( esc_html( $response->get_error_message() ?? '' ) );
		return $this->flash( esc_html__( 'Could not save card', 'bt-ipay-payments' ), true );
	}

	/**
	 * Flash message
	 *
	 * @param string $message
	 * @param boolean $error
	 *
	 * @return void
	 */
	private function flash( string $message, bool $error = false ) {
		wc_add_notice( $message, $error ? 'error' : 'success' );
	}

	/**
	 * Get any card data that is not in saved
	 *
	 * @param Bt_Ipay_Sdk_Detail_Response $response
	 *
	 * @return array|null
	 */
	private function get_card_data( Bt_Ipay_Sdk_Detail_Response $response ): ?array {
		$saved_card_ids = $this->storage->get_ipay_ids_by_customer_id( get_current_user_id() );
		$card_data      = $response->get_card_info();
		if ( isset( $card_data['ipay_id'] ) && ! in_array( $card_data['ipay_id'], $saved_card_ids ) ) {
			return $card_data;
		}
		return null;
	}

	protected function process( RegisterResponseModel $response ): string {
		if ( ! $response->isSuccess() ) {
			( new Bt_Ipay_Logger() )->error( $response->getErrorMessage() ?? '' );
			return $this->return( esc_html__( 'Could not save card', 'bt-ipay-payments' ) );
		}

		if ( $response->getRedirectUrl() !== null ) {
			return $response->getRedirectUrl();
		}
		( new Bt_Ipay_Logger() )->error( esc_html( $response->getErrorMessage() ?? '' ) );
		return $this->return( esc_html__( 'Could not save card', 'bt-ipay-payments' ) );
	}

	private function return( string $message ): string {
		wc_add_notice( $message, 'error' );
		return wc_get_account_endpoint_url( 'bt-ipay-cards' );
	}
}
