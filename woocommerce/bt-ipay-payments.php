<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://btepos.ro/module-ecommerce
 * @since             1.0.0
 * @package           Bt_Ipay
 *
 * @wordpress-plugin
 * Plugin Name:       BT iPay Payments
 * Plugin URI:        https://btepos.ro/module-ecommerce
 * Description:       Extinde WooCommerce cu plata prin <strong>iPay BT</strong>. Pentru conectare aveti nevoie de credentiale API de la Banca Transilvania. Pentru detalii aplicatiiecommerce@btrl.ro
 * Version:           1.0.0
 * Author:            Banca Transilvania
 * Author URI:        https://btepos.ro/module-ecommerce/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       bt-ipay-payments
 * Domain Path:       /languages
 * Requires Plugins:  woocommerce
 * WC requires at least: 7.0
 * WC tested up to: 8.7
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'BT_IPAY_VERSION', '1.0.0' );

define( 'BT_IPAY_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

define( 'BT_IPAY_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-bt-ipay-activator.php
 */
function bt_ipay_activate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-bt-ipay-activator.php';
	Bt_Ipay_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-bt-ipay-deactivator.php
 */
function bt_ipay_deactivate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-bt-ipay-deactivator.php';
	Bt_Ipay_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'bt_ipay_activate' );
register_deactivation_hook( __FILE__, 'bt_ipay_deactivate' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-bt-ipay.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function bt_ipay_run() {

	$plugin = new Bt_Ipay();
	$plugin->run();
}
bt_ipay_run();

add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );