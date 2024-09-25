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
class Bt_Ipay_Card_Storage {

	private const TO_ENCRYPT = [
		'expiration', 'cardholderName', 'pan'
	];

	protected $fields = array( 'expiration', 'cardholderName', 'pan', 'ipay_id', 'customer_id' );
	/**
	 * Create new database payment record
	 *
	 * @return void
	 */
	public function create( array $data ) {
		global $wpdb;

		if ( array_key_exists( 'approvalCode', $data ) ) {
			unset( $data['approvalCode'] ); // remove not need approval code
		}

		if ( count( array_diff( $this->fields, array_keys( $data ) ) ) > 0 ) {
			throw new Bt_Ipay_Storage_Exception( 'Invalid data for card storage' );
		}

		$data = $this->encrypt( $data );

		$this->delete_with_same_pan( $data );

		//phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->replace(
			self::table_name(),
			array_merge(
				$data,
				array(
					'status'     => Bt_Ipay_Card_State_Processor::ENABLED,
					'created_at' => ( new \DateTime() )->format( 'Y-m-d H:i:s' ),
				)
			)
		);
	}

	private function encrypt( array $data ) {
		foreach ( $data as $key => $value ) {
			if ( in_array( $key, self::TO_ENCRYPT ) ) {
				$data[$key] = Bt_Ipay_Card_Secure::encrypt($value);
			}
		}
		return $data;
	}

	public function decrypt( array $data ) {
		foreach ( $data as $key => $value ) {
			if ( in_array( $key, self::TO_ENCRYPT ) ) {
				$data[$key] = Bt_Ipay_Card_Secure::decrypt($value);
			}
		}
		return $data;
	}

	private function delete_with_same_pan( array $data ) {
		global $wpdb;
		if ( isset( $data['pan'] ) && isset( $data['customer_id'] ) ) {
			//phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			return $wpdb->delete(
				self::table_name(),
				array(
					'pan'         => $data['pan'],
					'customer_id' => $data['customer_id'],
				),
				array(
					'%s',
					'%d',
				)
			);
		}
	}

	public function delete_by_id( int $id ) {
		global $wpdb;
		
		//phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->delete(
			self::table_name(),
			array(
				'id' => $id,
			),
			array(
				'%d',
			)
		);
	}

	public function update_status( string $status, string $ipay_card_id ) {
		global $wpdb;

		//phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->update(
			self::table_name(),
			array(
				'status' => $status,
			),
			array(
				'ipay_id' => $ipay_card_id,
			),
			array(
				'%s',
			),
			array(
				'%s',
			)
		);
	}

	/**
	 * Find data by id
	 *
	 * @param string $payment_engine_id
	 *
	 * @return array|null
	 */
	public function find_by_id( string $id ) {
		global $wpdb;

		$sql = $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}bt_ipay_cards WHERE `id` = %s LIMIT 1",
			array( $id )
		);
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching 
		$results = $wpdb->get_results( $sql, ARRAY_A );
		if ( count( $results ) ) {
			return $results[0];
		}

		return null;
	}

	/**
	 * Find data by payment engine id
	 *
	 * @param string $payment_engine_id
	 *
	 * @return array|null
	 */
	public function find_by_payment_engine_id( string $payment_engine_id ) {
		global $wpdb;

		$sql = $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}bt_ipay_cards WHERE `ipay_id` = %s LIMIT 1",
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
	 * Find latest by order id
	 *
	 * @param int $customer_id
	 *
	 * @return array|null
	 */
	public function find_by_customer_id( int $customer_id ) {
		global $wpdb;

		$sql = $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}bt_ipay_cards WHERE `customer_id` = %s ORDER BY created_at DESC",
			array( $customer_id )
		);
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching 
		return $wpdb->get_results( $sql, ARRAY_A );
	}


	/**
	 * Find latest by order id
	 *
	 * @param int $customer_id
	 *
	 * @return array|null
	 */
	public function find_enabled_by_customer_id( int $customer_id ) {
		global $wpdb;

		$sql = $wpdb->prepare(
			"SELECT `id`, `cardholderName`, `pan` FROM {$wpdb->prefix}bt_ipay_cards WHERE `customer_id` = %s AND `status` = %s ORDER BY created_at DESC",
			array( $customer_id, Bt_Ipay_Card_State_Processor::ENABLED )
		);
		return  array_map(function( $row ) {
			return $this->decrypt( $row );
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching 
		}, $wpdb->get_results( $sql, ARRAY_A ));
	}

	/**
	 * Get card ids for customer id
	 *
	 * @param int $customer_id
	 *
	 * @return array
	 */
	public function get_ipay_ids_by_customer_id( int $customer_id ) {
		global $wpdb;

		$ids = array();
		$sql = $wpdb->prepare(
			"SELECT `ipay_id` FROM {$wpdb->prefix}bt_ipay_cards WHERE `customer_id` = %s",
			array( $customer_id )
		);
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching 
		$results = $wpdb->get_results( $sql, ARRAY_A );
		if ( is_array( $results ) ) {
			foreach ( $results as $result ) {
				$ids[] = $result['ipay_id'];
			}
		}
		return $ids;
	}



	public static function table_name() {
		global $wpdb;
		return $wpdb->prefix . 'bt_ipay_cards';
	}
}
