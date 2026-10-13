<?php

/**
 * Per-order mutual exclusion between the buyer return and the BT callback,
 * backed by a MySQL named lock.
 *
 * @since      1.0.9
 * @package    Bt_Ipay
 * @subpackage Bt_Ipay/includes/order
 */
class Bt_Ipay_Order_Lock {

	private const TIMEOUT = 10;

	private string $name;

	public function __construct( int $order_id ) {
		global $wpdb;
		$this->name = $wpdb->prefix . 'bt_ipay_order_' . $order_id;
	}

	/**
	 * Wait for the lock up to TIMEOUT seconds. Returns false on timeout.
	 */
	public function acquire(): bool {
		global $wpdb;
		//phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		return '1' === (string) $wpdb->get_var( $wpdb->prepare( 'SELECT GET_LOCK(%s, %d)', $this->name, self::TIMEOUT ) );
	}

	public function release(): void {
		global $wpdb;
		//phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $this->name ) );
	}
}
