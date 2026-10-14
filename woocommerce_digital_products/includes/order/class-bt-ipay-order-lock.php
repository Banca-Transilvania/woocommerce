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

	/**
	 * Run $callback while holding the lock for $order_id. Throws when the lock cannot be taken.
	 */
	public static function with( int $order_id, callable $callback ) {
		$lock = new self( $order_id );
		$lock->acquire();
		try {
			return $callback();
		} finally {
			$lock->release();
		}
	}

	private function __construct( int $order_id ) {
		global $wpdb;
		$this->name = $wpdb->prefix . 'bt_ipay_order_' . $order_id;
	}

	/**
	 * Wait for the lock up to TIMEOUT seconds. Throws on timeout or database error.
	 */
	private function acquire(): void {
		global $wpdb;
		//phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->get_var( $wpdb->prepare( 'SELECT GET_LOCK(%s, %d)', $this->name, self::TIMEOUT ) );
		if ( '1' !== (string) $result ) {
			throw new \RuntimeException( esc_html__( 'The order is still being updated, please try again in a moment.', 'bt-ipay-payments' ) );
		}
	}

	private function release(): void {
		global $wpdb;
		//phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $this->name ) );
	}
}
