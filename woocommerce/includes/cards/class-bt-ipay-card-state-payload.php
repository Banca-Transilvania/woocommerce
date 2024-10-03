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
class Bt_Ipay_Card_State_Payload {

	protected $ipay_card_id;

	public function __construct( string $ipay_card_id ) {
		$this->ipay_card_id = $ipay_card_id;
	}

	public function to_array(): array {
		return array(
			'bindingId' => $this->ipay_card_id,
		);
	}
}
