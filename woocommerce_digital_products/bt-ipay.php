<?php

/**
 * Legacy entry point, kept without a plugin header so WordPress no longer lists it.
 * Sites that activated this file are moved over to bt-ipay-payments.php.
 *
 * @package Bt_Ipay
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

$bt_ipay_legacy = plugin_basename( __FILE__ );
$bt_ipay_main   = dirname( $bt_ipay_legacy ) . '/bt-ipay-payments.php';

$bt_ipay_active = (array) get_option( 'active_plugins', array() );
if ( in_array( $bt_ipay_legacy, $bt_ipay_active, true ) ) {
	$bt_ipay_active = array_diff( $bt_ipay_active, array( $bt_ipay_legacy, $bt_ipay_main ) );
	$bt_ipay_active[] = $bt_ipay_main;
	sort( $bt_ipay_active );
	update_option( 'active_plugins', $bt_ipay_active );
}

if ( is_multisite() ) {
	$bt_ipay_network = (array) get_site_option( 'active_sitewide_plugins', array() );
	if ( isset( $bt_ipay_network[ $bt_ipay_legacy ] ) ) {
		$bt_ipay_network[ $bt_ipay_main ] = $bt_ipay_network[ $bt_ipay_legacy ];
		unset( $bt_ipay_network[ $bt_ipay_legacy ] );
		update_site_option( 'active_sitewide_plugins', $bt_ipay_network );
	}
}

unset( $bt_ipay_legacy, $bt_ipay_main, $bt_ipay_active, $bt_ipay_network );

require_once __DIR__ . '/bt-ipay-payments.php';
