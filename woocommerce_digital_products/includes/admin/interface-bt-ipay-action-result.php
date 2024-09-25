<?php

/**
 *
 * @link       https://btepos.ro/module-ecommerce
 * @since      1.0.0
 *
 * @package    Bt_Ipay
 * @subpackage Bt_Ipay/includes/admin
 */

/**
 *
 * @since      1.0.0
 * @package    Bt_Ipay
 * @subpackage Bt_Ipay/includes/admin
 * @author     Banca Transilvania <no-reply@btepos.ro>
 */
interface Bt_Ipay_Action_Result {
	public function has_loy(): bool;

	public function has_payment(): bool;
}
