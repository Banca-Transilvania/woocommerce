<?php

use Automattic\WooCommerce\Admin\Overrides\Order;

/**
 *
 * @link       https://btepos.ro/module-ecommerce
 * @since      1.0.0
 *
 * @package    Bt_Ipay
 * @subpackage Bt_Ipay/includes/webhook
 */

/**
 *
 * @since      1.0.0
 * @package    Bt_Ipay
 * @subpackage Bt_Ipay/includes/webhook
 * @author     Banca Transilvania <no-reply@btepos.ro>
 */
class Bt_Ipay_Webhook_Processor {

	private \stdClass $payload;

	private Bt_Ipay_Payment_Storage $payment_storage;

	public function __construct( \stdClass $jwt ) {
		$this->payload         = $this->get_payload( $jwt );
		$this->payment_storage = new Bt_Ipay_Payment_Storage();
	}


	public function process() {

		$payment_engine_id = $this->get_payment_engine_id();
		if ( $payment_engine_id === null ) {
			throw new \Exception( 'Cannot find payment id' );
		}

		$payment_status = $this->get_payment_status();
		if ( $payment_status === null ) {
			throw new \Exception( 'Cannot find payment status' );
		}

		$payment_data = $this->get_payment_data();
		$is_loy       = false;

		if ( $payment_data === null ) {
			$payment_data = $this->get_payment_data_by_loy();
			$is_loy       = true;
		}

		if ( $payment_data === null ) {
			throw new \Exception( 'Cannot find payment in WordPress' );
		}

		$order_id = $this->get_order_id( $payment_data );
		if ( $order_id === null ) {
			throw new \Exception( 'Cannot find order' );
		}

		$order_service = new Bt_Ipay_Order( new WC_Order( $order_id ) );

		$payment_id = $payment_data['ipay_id'];
		if ( $is_loy ) {
			$payment_id = $payment_data['loy_id'];
		}

		if ( $payment_status === Bt_Ipay_Payment_Storage::STATUS_REFUNDED ) {
			$is_full_refund = $this->add_refund( $payment_id, $order_service );
			if ( ! $is_full_refund ) {
				$payment_status = Bt_Ipay_Payment_Storage::STATUS_PARTIALLY_REFUNDED;
			}
		}

		if ( $payment_status === Bt_Ipay_Payment_Storage::STATUS_DEPOSITED && ! $this->has_failed() ) {
			$this->capture( $payment_data, $order_service, $is_loy );
		}

		if ( $is_loy ) {
			return $this->update_loy_data( $order_service, $payment_data, $payment_status );
		}

		$this->update_payment_status( $payment_engine_id, $payment_status );
		$this->update_order_status( $order_service, $payment_status );
	}

	private function update_loy_data( Bt_Ipay_Order $order_service, array $payment_data, string $payment_status ) {
		if (
			in_array(
				$payment_status,
				array(
					Bt_Ipay_Payment_Storage::STATUS_DEPOSITED,
					Bt_Ipay_Payment_Storage::STATUS_APPROVED,
				)
			) &&
			$this->has_failed()
		) {
			$payment_status = Bt_Ipay_Payment_Storage::STATUS_DECLINED;
		}

		$order_service->update_message(
			/* translators: %s: payment status */
			sprintf( esc_html__( 'Received loy status: `%s` via callback', 'bt-ipay-payments' ), esc_attr( $payment_status ) )
		);
		$this->payment_storage->update_loy_status( $payment_data['ipay_id'], $payment_status );
	}

	private function get_payload( \stdClass $jwt ) {
		if (
			property_exists( $jwt, 'payload' ) &&
			$jwt->payload instanceof \stdClass
		) {
			return $jwt->payload;
		}
		throw new \Exception( 'Cannot find jwt payload' );
	}

	private function update_order_status( Bt_Ipay_Order $order_service, string $payment_status ) {

		$new_order_status = $this->get_new_order_status( $payment_status );
		/* translators: %s: payment status */
		$message = sprintf( esc_html__( 'Received payment status: `%s` via callback', 'bt-ipay-payments' ), esc_attr( $payment_status ) );
		if (
			$new_order_status !== null &&
			$this->can_update_order_status( $order_service->get_status(), $new_order_status )
		) {
			$order_service->update_status( $new_order_status, $message );
			return;
		}
		$order_service->update_message( $message );
	}

	private function get_new_order_status( string $payment_status ): ?string {
		$mapping = array(
			Bt_Ipay_Payment_Storage::STATUS_DEPOSITED => 'processing',
			Bt_Ipay_Payment_Storage::STATUS_REVERSED  => 'cancelled',
			Bt_Ipay_Payment_Storage::STATUS_APPROVED  => 'on-hold',
			Bt_Ipay_Payment_Storage::STATUS_REFUNDED  => 'refunded',
			Bt_Ipay_Payment_Storage::STATUS_DECLINED  => 'failed',
		);

		if (
			in_array(
				$payment_status,
				array(
					Bt_Ipay_Payment_Storage::STATUS_DEPOSITED,
					Bt_Ipay_Payment_Storage::STATUS_APPROVED,
				)
			) &&
			$this->has_failed()
		) {
			return 'failed';
		}

		return $mapping[ $payment_status ] ?? null;
	}

	private function capture( array $payment_data, Bt_Ipay_Order $order_service, bool $is_loy ) {
		$client          = new Bt_Ipay_Sdk_Client( new Bt_Ipay_Config() );
		$payment_details = $client->payment_details( new Bt_Ipay_Sdk_Common_Payload( $payment_data['ipay_id'] ) );

		$total_captured = $payment_details->get_total_available();
		if ( $total_captured > 0 ) {
			if ( $is_loy ) {
				$this->payment_storage->update_loy_status_and_amount(
					$payment_data['loy_id'],
					Bt_Ipay_Payment_Storage::STATUS_APPROVED,
					$total_captured
				);
			} else {
				$this->payment_storage->update_status_and_amount(
					$payment_data['ipay_id'],
					Bt_Ipay_Payment_Storage::STATUS_APPROVED,
					$total_captured
				);

				if (
					isset( $payment_data['loy_status'], $payment_data['loy_amount'] ) &&
					$payment_data['loy_status'] === Bt_Ipay_Payment_Storage::STATUS_DEPOSITED
				) {//add the loy captured amount
					$total_captured += floatval( $payment_data['loy_amount'] );
				}
				$this->deduct_not_captured( $total_captured, $order_service );
			}
		}
	}

	/**
	 * To correctly display the total amount in woocommerce
	 * we need to refund any differences between the total woocommerce order and the total captured amount
	 *
	 * @param float $total_captured
	 *
	 * @return void
	 */
	public function deduct_not_captured( float $total_captured, Bt_Ipay_Order $order_service ) {
		$missing_captured = ( $order_service->get_total() / 100 ) - $total_captured;
		if ( $missing_captured < 0.01 ) {
			return;
		}

		$order_service->deduct_not_captured( $missing_captured );
	}

	/**
	 * Refund any missing amount, returns true if full refund
	 *
	 * @param string $payment_id
	 * @param Bt_Ipay_Order $order_service
	 *
	 * @return boolean
	 */
	private function add_refund( string $payment_id, Bt_Ipay_Order $order_service ): bool {
		$client          = new Bt_Ipay_Sdk_Client( new Bt_Ipay_Config() );
		$payment_details = $client->payment_details( new Bt_Ipay_Sdk_Common_Payload( $payment_id ) );

		$payment_total_refund = $payment_details->get_total_refunded();

		$available_amount = $payment_details->get_total_available();

		$refund_amount = $payment_total_refund - $order_service->get_total_refunded();
		if ( $refund_amount > 0 ) {
			wc_create_refund(
				array(
					'amount'   => $refund_amount,
					'order_id' => $order_service->get_id(),
				)
			);

			$order_service->update_message(
				/* translators: %s: payment amount */
				sprintf( esc_html__( 'Successfully refunded amount %s', 'bt-ipay-payments' ), wc_price( $refund_amount ) )
			);
		}

		return abs( $available_amount ) < 0.001;
	}

	private function can_update_order_status( string $current_status, string $new_status ): bool {
		if ( in_array( $new_status, array( 'cancelled', 'failed', 'processing' ) ) ) {
			return ! in_array( $current_status, array( 'refunded', 'complete' ) );
		}

		return true;
	}

	/**
	 * Update payment status
	 *
	 * @param Bt_Ipay_Sdk_Detail_Response $response
	 *
	 * @return void
	 */
	private function update_payment_status( string $payment_engine_id, string $payment_status ) {
		if (
			in_array(
				$payment_status,
				array(
					Bt_Ipay_Payment_Storage::STATUS_DEPOSITED,
					Bt_Ipay_Payment_Storage::STATUS_APPROVED,
				)
			) &&
			$this->has_failed()
		) {
			$payment_status = Bt_Ipay_Payment_Storage::STATUS_DECLINED;
		}

		$this->payment_storage->update_status(
			$payment_engine_id,
			$payment_status
		);
	}

	/**
	 * Get payment id from the jwt
	 *
	 * @return string|null
	 */
	private function get_payment_engine_id(): ?string {
		if (
			property_exists( $this->payload, 'mdOrder' ) &&
			is_string( $this->payload->mdOrder )
		) {
			return $this->payload->mdOrder;
		}
		return null;
	}

	/**
	 * Payment/Authorization request has failed
	 *
	 * @return bool
	 */
	private function has_failed(): bool {
		if ( property_exists( $this->payload, 'status' ) && is_scalar( $this->payload->status ) ) {
			return (int) $this->payload->status !== 1;
		}
		return false;
	}


	/**
	 * Get payment status from the jwt
	 *
	 * @return string|null
	 */
	private function get_payment_status(): ?string {
		if (
			property_exists( $this->payload, 'operation' ) &&
			is_string( $this->payload->operation )
		) {
			return strtoupper( $this->payload->operation );
		}
		return null;
	}

	private function get_order_id( array $payment_data ): ?int {
		if ( array_key_exists( 'order_id', $payment_data ) ) {
			return (int) $payment_data['order_id'];
		}
		return null;
	}

	private function get_payment_data(): ?array {
		return $this->payment_storage->find_by_payment_engine_id( $this->get_payment_engine_id() );
	}

	private function get_payment_data_by_loy(): ?array {
		return $this->payment_storage->find_by_payment_loy_id( $this->get_payment_engine_id() );
	}
}
