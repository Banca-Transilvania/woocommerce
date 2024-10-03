<?php

use BTransilvania\Api\Model\Response\RefundResponse;


/**
 * @link       https://btepos.ro/module-ecommerce
 * @since      1.0.0
 *
 * @package    Bt_Ipay
 * @subpackage Bt_Ipay/includes/sdk
 */

/**
 * @since      1.0.0
 * @package    Bt_Ipay
 * @subpackage Bt_Ipay/includes/sdk
 * @author     Banca Transilvania <no-reply@btepos.ro>
 */
class Bt_Ipay_Sdk_Cancel_Response {

	private RefundResponse $response;

	public function __construct(
		RefundResponse $response
	) {
		$this->response = $response;
	}

	public function is_successful(): bool {
		return $this->response->isSuccess() === true;
	}

	public function get_error_message(): ?string {
		return $this->response->getErrorMessage();
	}
}
