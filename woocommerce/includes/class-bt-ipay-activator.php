<?php

/**
 * Fired during plugin activation
 *
 * @link       https://btepos.ro/module-ecommerce
 * @since      1.0.0
 *
 * @package    Bt_Ipay
 * @subpackage Bt_Ipay/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Bt_Ipay
 * @subpackage Bt_Ipay/includes
 * @author     Banca Transilvania <no-reply@btepos.ro>
 */
class Bt_Ipay_Activator {


	/**
	 * @since    1.0.0
	 */
	public static function activate() {
		delete_transient(Bt_Ipay_Admin::get_admin_notice_key());
		if (version_compare(PHP_VERSION, '8.1', '<'))
		{
			set_transient(
				Bt_Ipay_Admin::get_admin_notice_key(),
				array(
					'message' => __( "BT iPay: Invalid php version, at least php 8.1 is required", 'bt-ipay-payments' ),
					'type'    => 'error',
					'clear'   => false
				)
			);
		}
		self::create_payment_state_table();
		self::create_cart_storage_table();
	}

	private static function create_payment_state_table() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'bt_ipay_payments';

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			    `id` INT NOT NULL AUTO_INCREMENT ,
				`order_id` BIGINT NOT NULL ,
				`ipay_id` VARCHAR(255) NOT NULL ,
				`amount` DECIMAL(15,2) NOT NULL,
				`status` VARCHAR(255) NOT NULL ,
				`loy_id` VARCHAR(255) DEFAULT NULL,
				`loy_amount` DECIMAL(15,2) NOT NULL,
				`loy_status` VARCHAR(255) NOT NULL ,
				`data` TEXT DEFAULT NULL ,
				`created_at` TIMESTAMP NOT NULL ,
				PRIMARY KEY `id` (`id`),
				INDEX `order_id` (`order_id`),
				INDEX `loy_id` (`loy_id`),
				UNIQUE KEY `ipay_id` (`ipay_id`)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}


	private static function create_cart_storage_table() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'bt_ipay_cards';

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			    `id` INT NOT NULL AUTO_INCREMENT ,
				`customer_id` BIGINT NOT NULL ,
				`ipay_id` VARCHAR(255) NOT NULL ,
				`expiration` VARCHAR(255) NOT NULL ,
				`cardholderName` VARCHAR(255) NOT NULL ,
				`pan` VARCHAR(255) NOT NULL ,
				`status` VARCHAR(255) NOT NULL ,
				`created_at` TIMESTAMP NOT NULL ,
				PRIMARY KEY (`id`),
				INDEX (`customer_id`),
				INDEX (`customer_id`, `pan`),
				UNIQUE KEY (`ipay_id`)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}
}
