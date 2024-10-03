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
class Bt_Ipay_Admin_Meta_Box {

	private Bt_Ipay_Post_Request $request;

	private Bt_Ipay_Logger $logger;

	private Bt_Ipay_Payment_Storage $storage;

	public function __construct( Bt_Ipay_Post_Request $request ) {
		$this->request = $request;
		$this->logger  = new Bt_Ipay_Logger();
		$this->storage = new Bt_Ipay_Payment_Storage();
	}
	public function add_order_meta_box( $post ) {
		if ( $post === 'shop_order' ) {
			add_meta_box(
				'bt-ipay',
				esc_html__( 'BT Ipay', 'bt-ipay-payments' ),
				array( $this, 'render_order_meta_box' ),
				'shop_order',
				'normal',
				'high'
			);
		}
	}
	public function render_order_meta_box( $post ) { //phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
		include_once BT_IPAY_PLUGIN_PATH . 'includes/admin/bt-ipay-order-form-template.php';
	}

	/**
	 * Ajax request that captures the payment
	 *
	 * @return void
	 */
	public function ajax_capture_payment() {
		check_ajax_referer( 'bt_ipay_nonce' );
		$this->check_permission();
		$this->validate_capture_request();

		$payment_data = $this->get_payment_data( (int) $this->request->get( 'order_id' ) );

		if ( $payment_data === null || ! array_key_exists( 'ipay_id', $payment_data ) ) {
			$this->error( esc_html__( 'No payment found for this order', 'bt-ipay-payments' ) );
		}

		try {
			$result = new Bt_Ipay_Capture_Result();
			( new Bt_Ipay_Capture_Service( $result, $this->logger ) )->capture(
				$payment_data['ipay_id'],
				(float) $this->request->get( 'amount' )
			);

			$this->update_storage_status_and_amount(
				$result,
				Bt_Ipay_Payment_Storage::STATUS_DEPOSITED,
				$payment_data['ipay_id']
			);

			if ( $result->is_partial() ) {
				$this->add_order_message(
					sprintf(
					/* translators: %s: captured amount */
						esc_html__( 'Partially captured amount %s', 'bt-ipay-payments' ),
						wc_price( $result->get_total() )
					)
				);
				$this->error( $result->get_error_message() );
			}

			if ( $result->has_internal_error() || $result->has_error_message() ) {
				$this->error( $result->get_error_message() );
			}

			$message = sprintf(
				/* translators: %s: captured amount */
				esc_html__( 'Successfully captured amount %s', 'bt-ipay-payments' ),
				wc_price( $this->request->get( 'amount' ) )
			);
			$this->update_order_status( $message, $result->has_payment() || $result->has_loy() );
			$this->deduct_not_captured( $result->get_total_captured() );
			$this->notice( $message );

		} catch ( Bt_Ipay_Capture_Exception $th ) {
			$this->error(
				sprintf(
					/* translators: %s: error message */
					esc_html__( 'Could not capture payment: %s', 'bt-ipay-payments' ),
					esc_html( $th->getMessage() )
				)
			);
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
	public function deduct_not_captured( float $total_captured ) {
		$order_service    = $this->get_order_service();
		$missing_captured = ( $order_service->get_total() / 100 ) - $total_captured;
		if ( $missing_captured < 0.01 ) {
			return;
		}

		$order_service->deduct_not_captured( $missing_captured );
	}
	/**
	 * Ajax request that cancels the payment
	 *
	 * @return void
	 */
	public function ajax_cancel_payment() {
		check_ajax_referer( 'bt_ipay_nonce' );
		$this->check_permission();
		$this->validate_request_order_number();

		$payment_data = $this->get_payment_data( (int) $this->request->get( 'order_id' ) );

		if ( $payment_data === null || ! array_key_exists( 'ipay_id', $payment_data ) ) {
			$this->error( esc_html__( 'No payment found for this order', 'bt-ipay-payments' ) );
		}

		try {
			$result = new Bt_Ipay_Cancel_Result();
			( new Bt_Ipay_Cancel_Service( $result, $this->logger ) )->cancel(
				$payment_data['ipay_id']
			);

			$this->update_storage_status(
				$result,
				Bt_Ipay_Payment_Storage::STATUS_REVERSED,
				$payment_data['ipay_id']
			);

			if ( $result->is_partial() ) {
				$this->add_order_message(
					sprintf(
						/* translators: %s: error message */
						esc_html__( 'Successfully cancel authorization %s', 'bt-ipay-payments' ),
						esc_html( $payment_data['loy_id'] )
					)
				);
				$this->error( $result->get_error_message() );
			}

			if ( $result->has_internal_error() || $result->has_error_message() ) {
				$this->error( $result->get_error_message() );
			}

			$message = esc_html__( 'Successfully cancel authorization/s', 'bt-ipay-payments' );

			$this->update_order_status( $message, $result->has_payment(), 'cancelled' );
			$this->notice( $message );
		} catch ( Bt_Ipay_Cancel_Exception $th ) {
			$this->error(
				sprintf(
					/* translators: %s: error message */
					esc_html__( 'Could not capture payment: %s', 'bt-ipay-payments' ),
					esc_html( $th->getMessage() )
				)
			);
		}
	}

	private function check_permission() {
		$current_user = wp_get_current_user();
		if ( ! in_array( 'administrator', $current_user->roles ) ) {
			$this->error( esc_html__( 'Only admin user can execute this action', 'bt-ipay-payments' ) );
		}
	}

	private function validate_capture_request() {
		$this->validate_request_order_number();

		$amount = $this->request->get( 'amount' );
		if ( ! is_scalar( $amount ) && (float) $amount <= 0 ) {
			$this->error( esc_html__( 'Invalid form: A valid capture amount is required', 'bt-ipay-payments' ) );
		}
	}

	private function validate_request_order_number() {
		if ( ! is_scalar( $this->request->get( 'order_id' ) ) ) {
			$this->error( esc_html__( 'Invalid form: Order ID is required', 'bt-ipay-payments' ) );
		}
	}

	/**
	 * Exit with error message
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	private function error( string $message ) {
		$this->notice( $message, 'error' );
	}

	private function notice( string $message, string $type = 'success' ) {
		$response = array(
			'type'    => $type,
			'message' => $message,
		);
		set_transient(
			Bt_Ipay_Admin::get_admin_notice_key(),
			$response
		);
		wp_send_json( $response );
	}

	private function update_order_status( string $message, bool $has_payment, string $status = 'processing' ) {
		$order_service = $this->get_order_service();
		if ( $order_service->get_status() === 'on-hold' && $has_payment ) {
			$order_service->update_status( $status, $message );
			return;
		}
		$order_service->update_message( $message );
	}


	private function add_order_message( string $message ) {
		$this->get_order_service()->update_message( $message );
	}

	/**
	 * Get sdk client
	 *
	 * @return Bt_Ipay_Sdk_Client
	 */
	private function get_client(): Bt_Ipay_Sdk_Client {
		return new Bt_Ipay_Sdk_Client( new Bt_Ipay_Config() );
	}

	/**
	 * Get order service
	 *
	 * @return Bt_Ipay_Order
	 */
	private function get_order_service(): Bt_Ipay_Order {
		return new Bt_Ipay_Order(
			new WC_Order( (int) $this->request->get( 'order_id' ) )
		);
	}

	/**
	 * Get latest payment data for order
	 *
	 * @param integer $order_id
	 *
	 * @return array|null
	 */
	private function get_payment_data( int $order_id ) {
		return $this->storage->find_first_by_order_id( $order_id );
	}

	/**
	 * Get all payment request for this order
	 *
	 * @param integer $order_id
	 *
	 * @return array
	 */
	protected function get_all_payments( int $order_id ): array {
		return $this->storage->all_by_order_id( $order_id );
	}

	/**
	 * Update status with amount
	 *
	 * @param Bt_Ipay_Capture_Result $result
	 * @param string $status
	 * @param string $payment_engine_id
	 *
	 * @return void
	 */
	protected function update_storage_status_and_amount(
		Bt_Ipay_Capture_Result $result,
		string $status,
		string $payment_engine_id
	) {
		if ( $result->has_payment() ) {
			$this->storage->update_status_and_amount( $payment_engine_id, $status, $result->get_pay_amount() );
		}

		if ( $result->is_payment_cancelled() ) {
			$this->storage->update_status( $payment_engine_id, Bt_Ipay_Payment_Storage::STATUS_REVERSED );
		}

		if ( $result->has_loy() ) {
			$this->storage->update_loy_status_and_amount( $payment_engine_id, $status, $result->get_loy_amount() );
		}
	}

	/**
	 * Update status of the payments
	 *
	 * @param Bt_Ipay_Action_Result $result
	 * @param string $status
	 * @param string $payment_engine_id
	 *
	 * @return void
	 */
	protected function update_storage_status(
		Bt_Ipay_Action_Result $result,
		string $status,
		string $payment_engine_id
	) {
		if ( $result->has_payment() ) {
			$this->storage->update_status( $payment_engine_id, $status );
		}

		if ( $result->has_loy() ) {
			$this->storage->update_loy_status( $payment_engine_id, $status );
		}
	}

	protected function can_show_form( ?array $payment_data ) {
		if ( ! is_array( $payment_data ) || ! array_key_exists( 'status', $payment_data ) ) {
			return false;
		}
		return $payment_data['status'] === Bt_Ipay_Payment_Storage::STATUS_APPROVED;
	}

	protected function get_approved_amount( ?array $payment_data ): float {
		$total = 0.0;
		if ( isset( $payment_data['amount'] ) ) {
			$total += floatval( $payment_data['amount'] );
		}

		if (
			isset( $payment_data['loy_amount'], $payment_data['loy_status'] ) &&
			$payment_data['loy_status'] === Bt_Ipay_Payment_Storage::STATUS_APPROVED
		) {
			$total += floatval( $payment_data['loy_amount'] );
		}

		return $total;
	}
}
