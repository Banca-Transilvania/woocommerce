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
				'title'   => __( 'Enable', 'bt-ipay-payments' ),
				'label'   => __( 'Enable BT iPay', 'bt-ipay-payments' ),
				'type'    => 'checkbox',
				'default' => 'no',
			),
			'title'            => array(
				'title'       => __( 'Title', 'bt-ipay-payments' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'bt-ipay-payments' ),
				'default'     => __( 'BT iPay', 'bt-ipay-payments' ),
			),
			'description'      => array(
				'title'       => __( 'Description', 'bt-ipay-payments' ),
				'type'        => 'textarea',
				'description' => __( 'This controls the description which the user sees during checkout.', 'bt-ipay-payments' ),
				'default'     => __( 'Pay with Transilvania Bank', 'bt-ipay-payments' ),
			),
			'paymentDescription'            => array(
				'title'       => __( 'Payment Description', 'bt-ipay-payments' ),
				'type'        => 'text',
				'description' => __( 'This controls the description which the user sees during payment process.', 'bt-ipay-payments' ),
				'default'     => __( 'Order: {order_number} - {shop_name} ', 'bt-ipay-payments' ),
			),
			'webhookUrl'       => array(
				'title'             => __( 'Callback url', 'bt-ipay-payments' ),
				'type'              => 'text',
				'description'       => __( 'The url required in order use the callback functionality', 'bt-ipay-payments' ),
				'default'           => rest_url( 'bt-ipay/v1/webhook' ),
				'custom_attributes' => array( 'readonly' => 'readonly' ),
			),
			'webhookKey'       => array(
				'title'       => __( 'Callback key', 'bt-ipay-payments' ),
				'type'        => 'password',
				'description' => __( 'The key required in order to verify the callback response', 'bt-ipay-payments' ),
				'placeholder' => self::valueExists( 'webhookKey', $settings ) ? self::PASSWORD_MASK : ''
			),
			'paymentFlow'      => array(
				'title'       => __( 'Payment Flow', 'bt-ipay-payments' ),
				'type'        => 'select',
				'description' => __( 'Select the payment flow that you desire', 'bt-ipay-payments' ),
				'default'     => self::FLOW_PAY,
				'options'     => array(
					self::FLOW_PAY       => __( 'Pay', 'bt-ipay-payments' ),
					self::FLOW_AUTHORIZE => __( 'Authorize', 'bt-ipay-payments' ),
				),
			),
			'enabledCardSave'  => array(
				'title'   => __( 'Enable Card on File', 'bt-ipay-payments' ),
				'type'    => 'checkbox',
				'default' => 'no',
			),
			'testMode'         => array(
				'title'       => __( 'Test mode', 'bt-ipay-payments' ),
				'label'       => __( 'Enable Test Mode', 'bt-ipay-payments' ),
				'type'        => 'checkbox',
				'description' => __( 'Place the payment gateway in test mode using test API keys.', 'bt-ipay-payments' ),
				'default'     => 'yes',
			),
			'authKey'          => array(
				'title'       => __( 'Username', 'bt-ipay-payments' ),
				'type'        => 'text',
				'description' => __( 'The username required for authentication with the payment engine', 'bt-ipay-payments' ),
			),
			'authPassword'     => array(
				'title'       => __( 'Password', 'bt-ipay-payments' ),
				'type'        => 'password',
				'description' => __( 'The password required for authentication with the payment engine', 'bt-ipay-payments' ),
				'placeholder' => self::valueExists( 'authPassword', $settings ) ? self::PASSWORD_MASK : ''
			),
			'testAuthKey'      => array(
				'title'       => __( 'Test Username', 'bt-ipay-payments' ),
				'type'        => 'text',
				'description' => __( 'The test username required for authentication with the payment engine', 'bt-ipay-payments' ),
			),
			'testAuthPassword' => array(
				'title'       => __( 'Test Password', 'bt-ipay-payments' ),
				'type'        => 'password',
				'description' => __( 'The test password required for authentication with the payment engine', 'bt-ipay-payments' ),
				'placeholder' => self::valueExists( 'testAuthPassword', $settings ) ? self::PASSWORD_MASK : ''
			),
			'logPayload'  => array(
				'title'   => __( 'Log payload data into woocommerce', 'bt-ipay-payments' ),
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
