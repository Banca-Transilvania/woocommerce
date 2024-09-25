<?php

/**
 *
 * @link       https://btepos.ro/module-ecommerce
 * @since      1.0.0
 *
 * @package    Bt_Ipay
 * @subpackage Bt_Ipay/includes/payment
 */

/**
 *
 * @since      1.0.0
 * @package    Bt_Ipay
 * @subpackage Bt_Ipay/includes/payment
 * @author     Banca Transilvania <no-reply@btepos.ro>
 */
class Bt_Ipay_Configuration {

	private const PASSWORD_MASK = "••••••••••••";
	public const FLOW_PAY       = 'pay';
	public const FLOW_AUTHORIZE = 'authorize';
	public static function get( $settings ): array {
		return array(
			'enabled'          => array(
				'title'   => __( 'Enable', 'bt-iPay' ),
				'label'   => __( 'Enable BT iPay', 'bt-iPay' ),
				'type'    => 'checkbox',
				'default' => 'no',
			),
			'title'            => array(
				'title'       => __( 'Title', 'bt-iPay' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'bt-iPay' ),
				'default'     => __( 'BT iPay', 'bt-iPay' ),
			),
			'description'      => array(
				'title'       => __( 'Description', 'bt-iPay' ),
				'type'        => 'textarea',
				'description' => __( 'This controls the description which the user sees during checkout.', 'bt-iPay' ),
				'default'     => __( 'Pay with Transilvania Bank', 'bt-iPay' ),
			),
			'paymentDescription'            => array(
				'title'       => __( 'Payment Description', 'bt-iPay' ),
				'type'        => 'text',
				'description' => __( 'This controls the description which the user sees during payment process.', 'bt-iPay' ),
				'default'     => __( 'Order: {order_number} - {shop_name} ', 'bt-iPay' ),
			),
			'webhookUrl'       => array(
				'title'             => __( 'Callback url', 'bt-iPay' ),
				'type'              => 'text',
				'description'       => __( 'The url required in order use the callback functionality', 'bt-iPay' ),
				'default'           => rest_url( 'bt-ipay/v1/webhook' ),
				'custom_attributes' => array( 'readonly' => 'readonly' ),
			),
			'webhookKey'       => array(
				'title'       => __( 'Callback key', 'bt-iPay' ),
				'type'        => 'password',
				'description' => __( 'The key required in order to verify the callback response', 'bt-iPay' ),
				'placeholder' => self::valueExists( 'webhookKey', $settings ) ? self::PASSWORD_MASK : ''
			),
			'paymentFlow'      => array(
				'title'       => __( 'Payment Flow', 'bt-iPay' ),
				'type'        => 'select',
				'description' => __( 'Select the payment flow that you desire', 'bt-iPay' ),
				'default'     => self::FLOW_PAY,
				'options'     => array(
					self::FLOW_PAY       => __( 'Pay', 'bt-iPay' ),
					self::FLOW_AUTHORIZE => __( 'Authorize', 'bt-iPay' ),
				),
			),
			'enabledCardSave'  => array(
				'title'   => __( 'Enable Card on File', 'bt-iPay' ),
				'type'    => 'checkbox',
				'default' => 'no',
			),
			'testMode'         => array(
				'title'       => __( 'Test mode', 'bt-iPay' ),
				'label'       => __( 'Enable Test Mode', 'bt-iPay' ),
				'type'        => 'checkbox',
				'description' => __( 'Place the payment gateway in test mode using test API keys.', 'bt-iPay' ),
				'default'     => 'yes',
			),
			'authKey'          => array(
				'title'       => __( 'Username', 'bt-iPay' ),
				'type'        => 'text',
				'description' => __( 'The username required for authentication with the payment engine', 'bt-iPay' ),
			),
			'authPassword'     => array(
				'title'       => __( 'Password', 'bt-iPay' ),
				'type'        => 'password',
				'description' => __( 'The password required for authentication with the payment engine', 'bt-iPay' ),
				'placeholder' => self::valueExists( 'authPassword', $settings ) ? self::PASSWORD_MASK : ''
			),
			'testAuthKey'      => array(
				'title'       => __( 'Test Username', 'bt-iPay' ),
				'type'        => 'text',
				'description' => __( 'The test username required for authentication with the payment engine', 'bt-iPay' ),
			),
			'testAuthPassword' => array(
				'title'       => __( 'Test Password', 'bt-iPay' ),
				'type'        => 'password',
				'description' => __( 'The test password required for authentication with the payment engine', 'bt-iPay' ),
				'placeholder' => self::valueExists( 'testAuthPassword', $settings ) ? self::PASSWORD_MASK : ''
			),
			'logPayload'  => array(
				'title'   => __( 'Log payload data into woocommerce', 'bt-iPay' ),
				'type'    => 'checkbox',
				'default' => 'no',
			),
		);
	}

	private static function valueExists( $field, $settings )
	{
		return isset( $settings[$field] ) && strlen(  $settings[$field] ) > 0;
	}
}
