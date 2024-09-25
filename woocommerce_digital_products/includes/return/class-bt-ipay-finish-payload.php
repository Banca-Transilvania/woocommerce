<?php

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
class Bt_Ipay_Finish_Payload {

	private string $payment_engine_id;
	private string $token;

	public function __construct(
		string $payment_engine_id,
		string $token
	) {
		$this->payment_engine_id = $payment_engine_id;
		$this->token             = $token;
	}


	public function to_array(): array {
		return array(
			'orderId' => $this->payment_engine_id,
			'token'   => $this->token,
		);
	}

	public function get_payment_engine_id() {
		return $this->payment_engine_id;
	}
}
