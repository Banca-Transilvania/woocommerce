<?php

use BTransilvania\Api\Config\Config;

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
class Bt_Ipay_Sdk_Auth {


	private Bt_Ipay_Config $config;

	public function __construct( Bt_Ipay_Config $config ) {
		$this->config = $config;
	}
	public function get(): array {
		return array(
			'user'         => $this->config->get_auth_key(),
			'password'     => $this->config->get_auth_password(),
			'environment'  => $this->config->is_test_mode() ? Config::TEST_MODE : Config::PROD_MODE,
			'platformName' => 'Woocommerce',
			'language'     => $this->get_lang(),
		);
	}

	private function get_lang(): string {
		$lang = get_locale();
		if ( ! is_string( $lang ) ) {
			return 'en';
		}
		return substr( $lang, 0, 2 );
	}
}
