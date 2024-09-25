<?php

/**
 *
 * @link       https://btepos.ro/module-ecommerce
 * @since      1.0.0
 *
 * @package    Bt_Ipay
 * @subpackage Bt_Ipay/includes/refund
 */

/**
 *
 * @since      1.0.0
 * @package    Bt_Ipay
 * @subpackage Bt_Ipay/includes/refund
 * @author     Banca Transilvania <no-reply@btepos.ro>
 */
class Bt_Ipay_Refund_Payload {

	private string $payment_engine_id;
	private float $amount;

	public function __construct(
		string $payment_engine_id,
		float $amount
	) {
		$this->payment_engine_id = $payment_engine_id;
		$this->amount            = $amount;
	}

	public function to_array(): array {
		return array(
			'orderId' => $this->payment_engine_id,
			'amount'  => intval( $this->amount * 100 ),
		);
	}
}
