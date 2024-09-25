<?php

use BTransilvania\Api\Model\Response\DepositResponse;


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
class Bt_Ipay_Sdk_Capture_Response {

	private DepositResponse $response;

	public function __construct(
		DepositResponse $response
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
