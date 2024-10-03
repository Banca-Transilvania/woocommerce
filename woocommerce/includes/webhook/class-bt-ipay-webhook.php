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
class Bt_Ipay_Webhook {


	private $logger;

	public function __construct() {
		$this->logger = new Bt_Ipay_Logger();
	}
	public function register_routes() {
		register_rest_route(
			'bt-ipay/v1',
			'/webhook',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'process' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	public function process() {
		try {
			$jwt = $this->decode();
			( new Bt_Ipay_Webhook_Processor( $jwt ) )->process();
		} catch ( \Throwable $th ) {
			$this->logger->error( 'Callback error: ' . $th->getMessage() . ' token:' . wp_kses_post(file_get_contents( 'php://input' )) );
			wp_die( '', '', 400 );
		}
	}

	private function decode(): \stdClass {
		return Bt_Ipay_Jwt::decode(
			(string) file_get_contents( 'php://input' ),
			Bt_Ipay_Jwt::urlsafe_b64_decode(
				( new Bt_Ipay_Config() )->get_callback_key()
			)
		);
	}
}
