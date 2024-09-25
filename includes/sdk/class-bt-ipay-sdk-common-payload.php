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
class Bt_Ipay_Sdk_Common_Payload {

	private string $payment_engine_id;

	public function __construct(
		string $payment_engine_id
	) {
		$this->payment_engine_id = $payment_engine_id;
	}

	public function to_array(): array {
		return array(
			'orderId' => $this->payment_engine_id,
		);
	}
}
