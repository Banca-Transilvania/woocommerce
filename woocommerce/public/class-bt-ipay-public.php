<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://btepos.ro/module-ecommerce
 * @since      1.0.0
 *
 * @package    Bt_Ipay
 * @subpackage Bt_Ipay/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Bt_Ipay
 * @subpackage Bt_Ipay/public
 * @author     Banca Transilvania <no-reply@btepos.ro>
 */
class Bt_Ipay_Public {


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
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		if ( function_exists( "is_cart" ) && ( is_cart() || is_checkout() || $this->is_card_management_page() ) ) {
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/bt-ipay-public.css', array(), $this->version, 'all' );
		}
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		global $wp;
		$current_url = home_url( add_query_arg( array() , $wp->request ) );

		if ( function_exists( "is_cart" ) && ( is_cart() || is_checkout() || $this->is_card_management_page() ) ) {
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/bt-ipay-public.js', array( 'jquery' ), $this->version, false );
			wp_localize_script(
				$this->plugin_name,
				'bt_ipay_vars',
				array(
					'nonce'               => wp_create_nonce( 'bt_ipay_nonce' ),
					'ajaxurl'             => admin_url( 'admin-ajax.php' ),
					'confirm_card_delete' => esc_html__( 'Are you sure you want to delete this card?', 'bt-ipay-payments' ),
				)
			);
		}
	}

	private function is_card_management_page()
	{
		global $wp;
        return home_url( add_query_arg( array() , $wp->request ) );
		return strpos( $current_url, "bt-ipay-cards" ) !== false;
	}
}
