<?php

/**
 *
 * @link       https://btepos.ro/module-ecommerce
 * @since      1.0.0
 *
 * @package    Bt_Ipay
 * @subpackage Bt_Ipay/includes/db
 */

/**
 *
 * @since      1.0.0
 * @package    Bt_Ipay
 * @subpackage Bt_Ipay/includes/db
 * @author     Banca Transilvania <no-reply@btepos.ro>
 */
class Bt_Ipay_Payment_Storage {


	public const STATUS_DEPOSITED           = 'DEPOSITED';
	public const STATUS_APPROVED            = 'APPROVED';
	public const STATUS_DECLINED            = 'DECLINED';
	public const STATUS_REVERSED            = 'REVERSED';
	public const STATUS_PARTIALLY_REFUNDED  = 'PARTIALLY_REFUNDED';
	public const STATUS_REFUNDED            = 'REFUNDED';
	public const STATUS_VALIDATION_FINISHED = 'VALIDATION_FINISHED';
	/**
	 * Create new database payment record
	 *
	 * @return void
	 */
	public function create(
		int $order_id,
		string $payment_engine_id
	) {
		global $wpdb;

		//phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->replace(
			self::table_name(),
			array(
				'order_id'          => $order_id,
				'ipay_id'           => $payment_engine_id,
				'status'            => 'CREATED',
				'created_at'        => ( new \DateTime() )->format( 'Y-m-d H:i:s' ),
			)
		);
	}

	public function update_status( string $payment_engine_id, string $status ) {
		global $wpdb;

		//phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->update(
			self::table_name(),
			array(
				'status' => $status,
			),
			array(
				'ipay_id' => $payment_engine_id,
			),
			array(
				'%s',
			),
			array(
				'%s',
			)
		);
	}


	public function update_status_and_amount( string $payment_engine_id, string $status, float $amount ) {
		global $wpdb;

		//phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->update(
			self::table_name(),
			array(
				'status' => $status,
				'amount' => $amount,
			),
			array(
				'ipay_id' => $payment_engine_id,
			),
			array(
				'%s',
			),
			array(
				'%s',
			)
		);
	}

	public function update_loy_status( string $payment_engine_id, string $status ) {
		global $wpdb;

		//phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->update(
			self::table_name(),
			array(
				'loy_status' => $status,
			),
			array(
				'ipay_id' => $payment_engine_id,
			),
			array(
				'%s',
			),
			array(
				'%s',
			)
		);
	}

	public function update_loy_status_and_amount( string $payment_engine_id, string $status, float $amount ) {
		global $wpdb;

		//phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->update(
			self::table_name(),
			array(
				'loy_status' => $status,
				'loy_amount' => $amount,
			),
			array(
				'ipay_id' => $payment_engine_id,
			),
			array(
				'%s',
				'%f',
			),
			array(
				'%s',
			)
		);
	}


	/**
	 * Update order data
	 *
	 * @return void
	 */
	public function update_data(
		string $status,
		float $amount,
		?string $loy_id,
		float $loy_amount,
		string $payment_engine_id
	) {
		global $wpdb;

		//phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->update(
			self::table_name(),
			array(
				'status'     => $status,
				'amount'     => $amount,
				'loy_id'     => $loy_id,
				'loy_amount' => $loy_amount,
				'loy_status' => $status === self::STATUS_REVERSED ? self::STATUS_DECLINED : $status,
			),
			array(
				'ipay_id' => $payment_engine_id,
			),
			array(
				'%s',
				'%f',
				'%s',
				'%f',
				'%s',
			),
			array(
				'%s',
			)
		);
	}

	/**
	 * Find data by payment engine id
	 *
	 * @param string $payment_engine_id
	 *
	 * @return array|null
	 */
	public function find_by_payment_engine_id( string $payment_engine_id ): ?array {
		global $wpdb;

		$sql = $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}bt_ipay_payments WHERE `ipay_id` = %s ORDER BY created_at DESC LIMIT 1",
			array( $payment_engine_id )
		);
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$results = $wpdb->get_results( $sql, ARRAY_A );
		if ( count( $results ) ) {
			return $results[0];
		}

		return null;
	}

	/**
	 * Find data by loy engine id
	 *
	 * @param string $loy_id
	 *
	 * @return array|null
	 */
	public function find_by_payment_loy_id( string $loy_id ): ?array {
		global $wpdb;

		$sql = $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}bt_ipay_payments WHERE `loy_id` = %s ORDER BY created_at DESC LIMIT 1",
			array( $loy_id )
		);
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$results = $wpdb->get_results( $sql, ARRAY_A );
		if ( count( $results ) ) {
			return $results[0];
		}

		return null;
	}


	/**
	 * Find latest by order id
	 *
	 * @param int $order_id
	 *
	 * @return array|null
	 */
	public function find_first_by_order_id( int $order_id ) {
		global $wpdb;

		$sql = $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}bt_ipay_payments WHERE `order_id` = %s ORDER BY created_at DESC LIMIT 1",
			array( $order_id )
		);
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$results = $wpdb->get_results( $sql, ARRAY_A );
		if ( count( $results ) ) {
			return $results[0];
		}

		return null;
	}

	/**
	 * Find latest by order id
	 *
	 * @param int $order_id
	 *
	 * @return array|null
	 */
	public function all_by_order_id( int $order_id ) {
		global $wpdb;

		$sql = $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}bt_ipay_payments WHERE `order_id` = %s ORDER BY created_at DESC",
			array( $order_id )
		);
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_results( $sql, ARRAY_A );
	}



	public static function table_name() {
		global $wpdb;
		return $wpdb->prefix . 'bt_ipay_payments';
	}
}
