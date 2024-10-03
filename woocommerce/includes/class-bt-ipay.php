<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://btepos.ro/module-ecommerce
 * @since      1.0.0
 *
 * @package    Bt_Ipay
 * @subpackage Bt_Ipay/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Bt_Ipay
 * @subpackage Bt_Ipay/includes
 * @author     Banca Transilvania <no-reply@btepos.ro>
 */
class Bt_Ipay {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Bt_Ipay_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'BT_IPAY_VERSION' ) ) {
			$this->version = BT_IPAY_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'bt-ipay';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->on_plugin_loaded();
		$this->hook_into_woocommerce();
		$this->add_blocks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Bt_Ipay_Loader. Orchestrates the hooks of the plugin.
	 * - Bt_Ipay_i18n. Defines internationalization functionality.
	 * - Bt_Ipay_Admin. Defines all hooks for the admin area.
	 * - Bt_Ipay_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-bt-ipay-logger.php';
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-bt-ipay-loader.php';
		require_once plugin_dir_path( __DIR__ ) . 'vendor/autoload.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-bt-ipay-i18n.php';

		require_once plugin_dir_path( __DIR__ ) . 'includes/class-bt-ipay-post-request.php';
		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'admin/class-bt-ipay-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'public/class-bt-ipay-public.php';
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-bt-ipay-woo.php';
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-bt-ipay-config.php';
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-bt-ipay-logger-sdk.php';
		require_once plugin_dir_path( __DIR__ ) . 'includes/db/index.php';
		require_once plugin_dir_path( __DIR__ ) . 'includes/cards/index.php';
		require_once plugin_dir_path( __DIR__ ) . 'includes/webhook/index.php';
		require_once plugin_dir_path( __DIR__ ) . 'includes/sdk/index.php';
		require_once plugin_dir_path( __DIR__ ) . 'includes/refund/index.php';
		require_once plugin_dir_path( __DIR__ ) . 'includes/admin/index.php';
		require_once plugin_dir_path( __DIR__ ) . 'includes/order/index.php';
		require_once plugin_dir_path( __DIR__ ) . 'includes/return/index.php';

		$this->loader = new Bt_Ipay_Loader();
	}

	public function load_payment_class() {
		if ( class_exists( 'WC_Payment_Gateways' ) ) {
			require_once plugin_dir_path( __DIR__ ) . 'includes/payment/index.php';
		}
	}

	private function hook_into_woocommerce() {
		$woo = new Bt_Ipay_Woo();
		$this->loader->add_filter( 'woocommerce_payment_gateways', $woo, 'register_payment_gateway' );
	}

	private function on_plugin_loaded() {
		$this->loader->add_action( 'plugins_loaded', $this, 'load_payment_class' );
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Bt_Ipay_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Bt_Ipay_I18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$request      = new Bt_Ipay_Post_Request();
		$plugin_admin = new Bt_Ipay_Admin( $this->get_plugin_name(), $this->get_version() );
		$capture_form = new Bt_Ipay_Admin_Meta_Box( $request );
		$return_page  = new Bt_Ipay_Return( $request );

		$cof_enabled = ( new Bt_Ipay_Config() )->cof_enabled();

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'check_for_dependencies' );
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'add_admin_notices' );

		$this->loader->add_action( 'add_meta_boxes', $capture_form, 'add_order_meta_box' );
		$this->loader->add_action( 'wp_ajax_bt_ipay_capture', $capture_form, 'ajax_capture_payment' );
		$this->loader->add_action( 'wp_ajax_bt_ipay_cancel', $capture_form, 'ajax_cancel_payment' );
		$this->loader->add_action( 'woocommerce_rest_api_get_rest_namespaces', $this, 'register_wc_webhook' );

		$this->loader->add_action( 'woocommerce_api_bt_ipay_return', $return_page, 'process' );

		if ( $cof_enabled ) {
			$cards_page = new Bt_Ipay_Card_List_Page( $request );
			$this->loader->add_filter( 'woocommerce_account_menu_items', $cards_page, 'add_menu_item' );
			$this->loader->add_action( 'init', $cards_page, 'init_page' );
			$this->loader->add_action( 'woocommerce_account_bt-ipay-cards_endpoint', $cards_page, 'page_content' );
			$this->loader->add_action( 'woocommerce_api_bt_card_return', $cards_page, 'card_save_return' );
			$this->loader->add_action( 'wp_ajax_bt_ipay_toggle_card_state', $cards_page, 'toggle_card_state' );
			$this->loader->add_action( 'wp_ajax_bt_ipay_delete_card', $cards_page, 'delete_card' );
			$this->loader->add_action( 'wp_ajax_bt_ipay_save_card', $cards_page, 'save_card' );
		}
	}

	public function register_wc_webhook( $controllers ) {
		$controllers['bt-ipay/v1']['webhook'] = 'Bt_Ipay_Webhook';
		return $controllers;
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
		$plugin_public = new Bt_Ipay_Public( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Bt_Ipay_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	private function add_blocks() {
		$this->loader->add_action( 'enqueue_block_assets', $this, 'add_blocks_assets' );
	}

	public function add_blocks_assets() {

		$gateway = $this->get_payment_gateway();

		if ( $gateway === null ) {
			return;
		}
		wp_enqueue_script(
			$this->plugin_name . '_blocks',
			BT_IPAY_PLUGIN_URL . 'public/js/dist/blocks.js',
			array( 'wc-blocks-registry' ),
			$this->version,
			true
		);
		wp_localize_script( $this->plugin_name . '_blocks', 'bt_ipay_gateway', $this->get_gateway_data( $gateway ) );
	}

	private function get_gateway_data( $gateway ) {

		$notices = array();

		if (
			class_exists('WC') &&
			function_exists("wc_get_notices") &&
			WC()->session !== null &&
			wc_notice_count('error') > 0
		) {
			$notices = wc_get_notices('error');
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
	 * Get our payment gateway
	 *
	 * @return Bt_Ipay_Gateway|null
	 */
	private function get_payment_gateway() {
		if ( ! function_exists( 'WC' ) ) {
			return;
		}
		$gateways = WC()->payment_gateways->payment_gateways();

		foreach ( $gateways as $gateway ) {
			if ( $gateway->id === 'bt-ipay' ) {
				return $gateway;
			}
		}
	}
}
