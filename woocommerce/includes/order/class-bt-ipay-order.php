<?php

/**
 *
 * @link       https://btepos.ro/module-ecommerce
 * @since      1.0.0
 *
 * @package    Bt_Ipay
 * @subpackage Bt_Ipay/includes/capture
 */

use BTransilvania\Api\Config\Config;

/**
 *
 * @since      1.0.0
 * @package    Bt_Ipay
 * @subpackage Bt_Ipay/includes/capture
 * @author     Banca Transilvania <no-reply@btepos.ro>
 */
class Bt_Ipay_Order {

	private WC_Order $order;

	private Bt_Ipay_Customer $customer;

	public function __construct( WC_Order $order ) {
		$this->order    = $order;
		$this->customer = new Bt_Ipay_Customer( $order );
	}

	/**
	 * Get order id
	 *
	 * @return int
	 */
	public function get_id(): int {
		return $this->order->get_id();
	}

	/**
	 * Update order status with message
	 *
	 * @param string $status
	 * @param string $message
	 *
	 * @return void
	 */
	public function update_status( string $status, string $message = '' ) {
		$this->order->update_status( $status, $message );
	}


	public function needs_processing() {
		$items = $this->order->get_items();
		foreach ($items as $product) {
			if ( ! is_a( $product, 'WC_Product' ) ) continue;
			if ( !$product->is_virtual() ) {
				return true;  
			}
		}

		return false;
	}


	public function get_status(): string {
		return $this->order->get_status( 'edit' );
	}

	/**
	 * Add order note
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	public function update_message( string $message ) {
		if ( strlen( trim( $message ) ) ) {
			$this->order->add_order_note( $message );
		}
	}

	public function get_total_refunded(): float {
		return floatval( $this->order->get_total_refunded() );
	}

	/**
	 * Add page notice
	 *
	 * @param string $message
	 * @param string $type
	 *
	 * @return void
	 */
	public function add_page_notice( string $message, string $type = 'error' ) {
		if ( strlen( trim( $message ) ) ) {
			wc_add_notice( $message, $type );
		}
	}

	public function get_order_number(): string {
		return $this->order->get_order_number() . '_' . microtime( false );
	}

	public function get_total(): int {
		$total = $this->order->get_total( 'edit' );
		if ( ! is_scalar( $total ) ) {
			/* translators: %s: order total */
			throw new Bt_Ipay_Order_Exception( sprintf( esc_html__( 'Invalid order amount: %s', 'bt-ipay-payments' ), esc_attr( $total ) ) );
		}
		return intval( floatval( $total ) * 100 );
	}

	public function get_currency(): string {
		return $this->order->get_currency();
	}

	public function get_customer(): Bt_Ipay_Customer {
		return $this->customer;
	}

	public function get_shipping_method(): string {
		return substr(
			Bt_Ipay_Formatter::format( (string) $this->order->get_shipping_method() ),
			0,
			20
		);
	}

	public function get_address( string $type ): Bt_Ipay_Address {
		return new Bt_Ipay_Address( $this->order, $type );
	}

	public function get_order(): WC_Order {
		return $this->order;
	}


	public function deduct_not_captured( float $amount ) {
		$formatted_amount = wc_price( -$amount, array( 'currency' => $this->order->get_currency() ) );
		$fee              = new WC_Order_Item_Fee();
		$fee->set_amount( -$amount );
		$fee->set_total( -$amount );
		/* translators: %s fee amount */
		$fee->set_name( sprintf( __( '%s (not captured)', 'bt-ipay-payments' ), wc_clean( $formatted_amount ) ) );

		$this->order->add_item( $fee );
		$this->order->calculate_totals();
		$this->order->save();
	}
}
