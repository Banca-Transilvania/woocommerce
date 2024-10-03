<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://btepos.ro/module-ecommerce
 * @since      1.0.0
 *
 * @package    Bt_Ipay
 * @subpackage Bt_Ipay/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Bt_Ipay
 * @subpackage Bt_Ipay/admin
 * @author     Banca Transilvania <no-reply@btepos.ro>
 */
class Bt_Ipay_Admin {


	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Bt_Ipay_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Bt_Ipay_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/bt-ipay-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Bt_Ipay_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Bt_Ipay_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/bt-ipay-admin.js', array( 'jquery' ), $this->version, false );
		wp_localize_script(
			$this->plugin_name,
			'bt_ipay_vars',
			array(
				'confirm_capture' => esc_html__( 'Are you sure you wish to process this capture? This action cannot be undone', 'bt-ipay-payments' ),
				'confirm_cancel'  => esc_html__( 'Are you sure you wish to process this cancel? This action cannot be undone', 'bt-ipay-payments' ),
				'nonce'           => wp_create_nonce( 'bt_ipay_nonce' ),
			)
		);
	}

	/**
	 * Check for dependencies
	 *
	 * @return void
	 */
	public function check_for_dependencies() {
		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			set_transient(
				self::get_admin_notice_key(),
				array(
					'message' => __( 'BT iPay requires the WooCommerce plugin to be installed and active', 'bt-ipay-payments' ),
					'type'    => 'error',
				)
			);
		}
	}

	/**
	 * Get admin notice key
	 *
	 * @return string
	 */
	public static function get_admin_notice_key(): string {
		return get_current_user_id() . 'bt-pay-admin-notice';
	}

	/**
	 * Display admin notices
	 *
	 * @return void
	 */
	public function add_admin_notices() {
		$type         = 'success';
		$allowed_html = array(
			'span' => array( 'class' ),
			'bdi'  => array(),
		);

		$message = get_transient( self::get_admin_notice_key() );

		$clear = false;
		if ( is_array( $message ) && isset( $message['message'] ) ) {
			$clear = !isset( $message['clear'] ) || $message['clear'] === true;

			if ( isset( $message['type'] ) ) {
				$type = $message['type'];
			}
			$message = $message['message'];
		}
		if ( is_string( $message ) ) {
			echo '<div class="notice notice-' . esc_attr( $type ) . '"><p>' . wp_kses( $message, $allowed_html ) . '</p></div>';
		}

		
		if ( $clear ) {
			delete_transient( self::get_admin_notice_key() );
		}
	}
}
