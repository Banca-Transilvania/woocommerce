<?php

use BTransilvania\Api\Model\Response\RefundResponse;


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
class Bt_Ipay_Sdk_Refund_Response {

	private RefundResponse $response;

	public function __construct( RefundResponse $response ) {
		$this->response = $response;
	}

	public function is_successful(): bool {
		return $this->response->isSuccess() === true;
	}

	public function get_error_message(): string {
		return $this->response->getErrorMessage() ?? '';
	}
}
