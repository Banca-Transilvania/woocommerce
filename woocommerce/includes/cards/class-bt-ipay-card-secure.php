<?php

use BTransilvania\Api\Model\Response\RegisterResponseModel;

/**
 *
 * @link       https://btepos.ro/module-ecommerce
 * @since      1.0.0
 *
 * @package    Bt_Ipay
 * @subpackage Bt_Ipay/includes/cards
 */

/**
 *
 * @since      1.0.0
 * @package    Bt_Ipay
 * @subpackage Bt_Ipay/includes/cards
 * @author     Banca Transilvania <no-reply@btepos.ro>
 */
class Bt_Ipay_Card_Secure {

	public const ALG = "aes-256-gcm";

	public static function encrypt($data)
	{
        $encryptionKey = base64_decode(SECURE_AUTH_KEY);
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(self::ALG));
        $encrypted = openssl_encrypt($data, self::ALG, $encryptionKey, 0, $iv, $tag);
        if ($encrypted === false) {
            throw new \Exception(esc_html(openssl_error_string()));
        }
        return base64_encode($encrypted . '::' . $iv . '::' . $tag);
	}

	public static function decrypt($data)
	{
        if (!is_string($data)) {
            return '';
        }
        $encryptionKey = base64_decode(SECURE_AUTH_KEY);
        $parts = explode('::', base64_decode($data), 3);
        if (count($parts) < 3) {
            return '';
        }
        list($encryptedData, $iv, $tag) =$parts;
        $decrypted = openssl_decrypt($encryptedData, self::ALG, $encryptionKey, 0, $iv, $tag);
        if ($decrypted === false) {
            throw new \Exception(esc_html(openssl_error_string()));
        }
        return $decrypted;
	}
}
