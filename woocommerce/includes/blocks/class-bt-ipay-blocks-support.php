<?php

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

/**
 * Server-side registration of the BT iPay gateway for the WooCommerce
 * Checkout/Cart blocks (Store API).
 *
 * WooCommerce Blocks only treats a payment method as a valid, submittable
 * method when it is registered here as well as client-side. Without this the
 * Checkout block omits `payment_method` from the Store API request and the
 * order is rejected with "No payment method provided.".
 *
 * The class extends a WooCommerce Blocks base class that is only available
 * after Blocks has loaded, so it must be required lazily from inside the
 * `woocommerce_blocks_payment_method_type_registration` callback.
 */
final class Bt_Ipay_Blocks_Support extends AbstractPaymentMethodType {

	/**
	 * Payment method id. Must match the client-side registration name and the
	 * gateway id so the Store API accepts the submitted `payment_method`.
	 *
	 * @var string
	 */
	protected $name = 'bt-ipay';

	public function initialize() {
		// Settings are read through the gateway; nothing to initialize here.
	}

	public function is_active() {
		$gateway = $this->get_payment_gateway();

		return $gateway !== null && $gateway->is_available();
	}

	public function get_payment_method_script_handles() {
		$handle     = 'bt-ipay-blocks';
		$asset_path = BT_IPAY_PLUGIN_PATH . 'public/js/dist/blocks.asset.php';
		$asset      = file_exists( $asset_path )
			? require $asset_path
			: array(
				'dependencies' => array(),
				'version'      => BT_IPAY_VERSION,
			);

		wp_register_script(
			$handle,
			BT_IPAY_PLUGIN_URL . 'public/js/dist/blocks.js',
			$asset['dependencies'],
			$asset['version'],
			true
		);

		return array( $handle );
	}

	public function get_payment_method_script_handles_for_admin() {
		return $this->get_payment_method_script_handles();
	}

	/**
	 * Data exposed to the client script via `wc.wcSettings.getSetting( 'bt-ipay_data' )`.
	 *
	 * @return array
	 */
	public function get_payment_method_data() {
		$gateway = $this->get_payment_gateway();

		if ( $gateway === null ) {
			return array();
		}

		$notices = array();

		if (
			class_exists( 'WC' ) &&
			function_exists( 'wc_get_notices' ) &&
			WC()->session !== null &&
			wc_notice_count( 'error' ) > 0
		) {
			$notices = wc_get_notices( 'error' );
			wc_clear_notices();
		}

		return array(
			'paymentMethodId'    => $gateway->id,
			'title'              => $gateway->get_title(),
			'description'        => $gateway->get_description(),
			'icon'               => $gateway->icon,
			'canShowCardsOnFile' => $gateway->can_show_cards_on_file(),
			'cards'              => $gateway->can_show_cards_on_file() ? $gateway->get_user_saved_card() : array(),
			'saveCardLabel'      => esc_html__( 'Save my card for future uses', 'bt-ipay-payments' ),
			'newCardLabel'       => esc_html__( 'I want to pay with a new card', 'bt-ipay-payments' ),
			'selectLabel'        => esc_html__( 'Select saved card', 'bt-ipay-payments' ),
			'notices'            => $notices,
		);
	}

	/**
	 * Get our payment gateway.
	 *
	 * @return Bt_Ipay_Gateway|null
	 */
	private function get_payment_gateway() {
		if ( ! function_exists( 'WC' ) ) {
			return null;
		}

		$gateways = WC()->payment_gateways->payment_gateways();

		foreach ( $gateways as $gateway ) {
			if ( $gateway->id === 'bt-ipay' ) {
				return $gateway;
			}
		}

		return null;
	}
}
