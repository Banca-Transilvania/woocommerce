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
class Bt_Ipay_Refund_Processor {

	private $order_id;

	private $amount;

	private Bt_Ipay_Payment_Storage $storage;

	public function __construct( int $order_id, float $amount ) {
		$this->order_id = $order_id;
		$this->amount   = $amount;
		$this->storage  = new Bt_Ipay_Payment_Storage();
	}

	public function process() {
		try {
			$payment_engine_id = $this->get_payment_id( $this->get_payment() );
			if ( $payment_engine_id === null ) {
				return new WP_Error(
					'error',
					esc_html__( 'Cannot process refund: bt ipay id', 'bt-ipay-payments' )
				);
			}

			$result         = new Bt_Ipay_Refund_Result();
			$refund_service = new Bt_Ipay_Refund(
				$payment_engine_id,
				number_format( floatval( $this->amount ), 2 ),
				$result
			);

			$refund_service->execute();
			$this->update_payment_status( $result, $payment_engine_id );
			$this->update_order_status( $result );
			return true;
		} catch ( Bt_Ipay_Refund_Exception $th ) {
			return new WP_Error( 'error', $th->getMessage() );
		} catch ( \Throwable $th ) {
			( new Bt_Ipay_Logger() )->error( (string) $th );
			return new WP_Error(
				'error',
				esc_html__( 'Could not create refund, check the woocommerce logs', 'bt-ipay-payments' )
			);
		}
	}

	private function update_payment_status( Bt_Ipay_Refund_Result $result, string $payment_engine_id ) {
		if ( $result->has_payment() ) {
			$this->storage->update_status(
				$payment_engine_id,
				$result->is_payment_partial() ? Bt_Ipay_Payment_Storage::STATUS_PARTIALLY_REFUNDED : Bt_Ipay_Payment_Storage::STATUS_REFUNDED
			);
		}

		if ( $result->has_loy() ) {
			$this->storage->update_loy_status(
				$payment_engine_id,
				$result->is_loy_partial() ? Bt_Ipay_Payment_Storage::STATUS_PARTIALLY_REFUNDED : Bt_Ipay_Payment_Storage::STATUS_REFUNDED
			);
		}
	}

	private function update_order_status( Bt_Ipay_Refund_Result $result ) {
		/* translators: %s: payment amount */
		$message       = sprintf( esc_html__( 'Successfully refunded amount %s', 'bt-ipay-payments' ), wc_price( $this->amount ) );
		$order_service = new Bt_Ipay_Order( new WC_Order( $this->order_id ) );
		if ( $result->has_payment() && ! $result->is_payment_partial() ) {
			$order_service->update_status( 'refunded', $message );
			return;
		}
		$order_service->update_message( $message );
	}

	private function get_payment(): array {
		$payment = $this->storage->find_first_by_order_id( (int) $this->order_id );

		if ( $payment === null ) {
			throw new Bt_Ipay_Refund_Exception(
				esc_html__( 'No payment was found for this order', 'bt-ipay-payments' )
			);
		}

		return $payment;
	}

	private function get_payment_id( $payment ): ?string {
		return $payment['ipay_id'] ?? null;
	}
}
