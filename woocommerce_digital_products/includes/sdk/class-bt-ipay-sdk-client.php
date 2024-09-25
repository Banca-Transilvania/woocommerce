<?php

use BTransilvania\Api\IPayClient;
use BTransilvania\Api\Model\Response\RegisterResponseModel;
use BTransilvania\Api\Model\Response\GetBindingsResponseModel;

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
class Bt_Ipay_Sdk_Client {


	private Bt_Ipay_Config $config;

	public function __construct( Bt_Ipay_Config $config ) {
		$this->config = $config;
	}

	/**
	 * Start a payment
	 *
	 * @param Bt_Ipay_Pay_Payload $payload
	 *
	 * @return Bt_Ipay_Sdk_Pay_Response
	 */
	public function start_payment( Bt_Ipay_Pay_Payload $payload ): Bt_Ipay_Sdk_Pay_Response {
		$client = $this->create_client();

		$is_authorize = $this->is_authorize();
		if ( $is_authorize ) {
			return new Bt_Ipay_Sdk_Pay_Response( $client->registerPreAuth( $payload->to_array() ), $payload->get_order_service(), $is_authorize );
		}
		return new Bt_Ipay_Sdk_Pay_Response( $client->register( $payload->to_array() ), $payload->get_order_service(), $is_authorize );
	}

	/**
	 * Capture payment
	 *
	 * @param Bt_Ipay_Capture_Payload $payload
	 *
	 * @return Bt_Ipay_Sdk_Capture_Response
	 */
	public function capture( Bt_Ipay_Capture_Payload $payload ): Bt_Ipay_Sdk_Capture_Response {
		return new Bt_Ipay_Sdk_Capture_Response(
			$this->create_client()->deposit( $payload->to_array() ),
		);
	}

	/**
	 * Cancel payment
	 *
	 * @param Bt_Ipay_Cancel_Payload $payload
	 *
	 * @return Bt_Ipay_Sdk_Cancel_Response
	 */
	public function cancel( Bt_Ipay_Cancel_Payload $payload ): Bt_Ipay_Sdk_Cancel_Response {
		return new Bt_Ipay_Sdk_Cancel_Response(
			$this->create_client()->reverse( $payload->to_array() ),
		);
	}

	/**
	 * Get payment details
	 *
	 * @param Bt_Ipay_Sdk_Common_Payload $payload
	 *
	 * @return Bt_Ipay_Sdk_Detail_Response
	 */
	public function payment_details( Bt_Ipay_Sdk_Common_Payload $payload ): Bt_Ipay_Sdk_Detail_Response {
		return new Bt_Ipay_Sdk_Detail_Response(
			$this->create_client()->getOrderStatusExtended( $payload->to_array() )
		);
	}

	public function toggle_card_status( Bt_Ipay_Card_State_Payload $payload, bool $enable ) {
		$client = $this->create_client();
		if ( $enable ) {
			return $client->bindCard( $payload->to_array() );
		}
		return $client->unBindCard( $payload->to_array() );
	}


	/**
	 * Get cards for specific client
	 *
	 * @param string $client_id
	 *
	 * @return GetBindingsResponseModel
	 */
	public function get_cards( string $client_id ): GetBindingsResponseModel {
		$client = $this->create_client();
		return $client->getBindings( array( 'clientId' => $client_id ) );
	}


	/**
	 * Start request to save card data
	 *
	 * @param array $payload
	 *
	 * @return RegisterResponseModel
	 */
	public function save_card( array $payload ): RegisterResponseModel {
		return $this->create_client()->register( $payload );
	}


	/**
	 * Refund payment
	 *
	 * @param Bt_Ipay_Refund_Payload $payload
	 *
	 * @return Bt_Ipay_Sdk_Refund_Response
	 */
	public function refund( Bt_Ipay_Refund_Payload $payload ): Bt_Ipay_Sdk_Refund_Response {
		$client = $this->create_client();
		return new Bt_Ipay_Sdk_Refund_Response(
			$client->refund( $payload->to_array() ),
		);
	}

	private function create_client(): IPayClient {
		$sdk_config = ( new Bt_Ipay_Sdk_Auth( $this->config ) )->get();
		return new IPayClient( $sdk_config, null, new Bt_Ipay_Logger_Sdk() );
	}

	/**
	 * Can start a authorize payment request
	 *
	 * @return boolean
	 */
	private function is_authorize() {
		return $this->config->get_payment_flow() === Bt_Ipay_Configuration::FLOW_AUTHORIZE;
	}
}
