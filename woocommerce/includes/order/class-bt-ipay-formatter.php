<?php

/**
 *
 * @link       https://btepos.ro/module-ecommerce
 * @since      1.0.0
 *
 * @package    Bt_Ipay
 * @subpackage Bt_Ipay/order
 */


/**
 *
 * @since      1.0.0
 * @package    Bt_Ipay
 * @subpackage Bt_Ipay/order
 * @author     Banca Transilvania <no-reply@btepos.ro>
 */
class Bt_Ipay_Formatter {

	public static function format( string $text ): string {
		$normalize_chars = array(
			'&icirc;' => 'i',
			'&Icirc;' => 'I',
			'&acirc;' => 'a',
			'&Acirc;' => 'A',
			'Š'       => 'S',
			'š'       => 's',
			'Ð'       => 'Dj',
			'Ž'       => 'Z',
			'ž'       => 'z',
			'À'       => 'A',
			'Á'       => 'A',
			'Â'       => 'A',
			'Ã'       => 'A',
			'Ä'       => 'A',
			'Å'       => 'A',
			'Æ'       => 'A',
			'Ç'       => 'C',
			'È'       => 'E',
			'É'       => 'E',
			'Ê'       => 'E',
			'Ë'       => 'E',
			'Ì'       => 'I',
			'Í'       => 'I',
			'Î'       => 'I',
			'Ï'       => 'I',
			'Ñ'       => 'N',
			'Ò'       => 'O',
			'Ó'       => 'O',
			'Ô'       => 'O',
			'Õ'       => 'O',
			'Ö'       => 'O',
			'Ø'       => 'O',
			'Ù'       => 'U',
			'Ú'       => 'U',
			'Û'       => 'U',
			'Ü'       => 'U',
			'Ý'       => 'Y',
			'Þ'       => 'B',
			'ß'       => 'Ss',
			'à'       => 'a',
			'á'       => 'a',
			'â'       => 'a',
			'ã'       => 'a',
			'ä'       => 'a',
			'å'       => 'a',
			'æ'       => 'a',
			'ç'       => 'c',
			'è'       => 'e',
			'é'       => 'e',
			'ê'       => 'e',
			'ë'       => 'e',
			'ì'       => 'i',
			'í'       => 'i',
			'î'       => 'i',
			'ï'       => 'i',
			'ð'       => 'o',
			'ñ'       => 'n',
			'ò'       => 'o',
			'ó'       => 'o',
			'ô'       => 'o',
			'õ'       => 'o',
			'ö'       => 'o',
			'ø'       => 'o',
			'ù'       => 'u',
			'ú'       => 'u',
			'û'       => 'u',
			'ý'       => 'y',
			'þ'       => 'b',
			'ÿ'       => 'y',
			'ƒ'       => 'f',
			'ü'       => 'u',
			'ţ'       => 't',
			'Ţ'       => 'T',
			'ă'       => 'a',
			'Ă'       => 'A',
			'ş'       => 's',
			'Ş'       => 'S',
			'ț'       => 't',
			'ș'       => 's',
			'Ș'       => 's',
			'Ț'       => 'T',
		);

		foreach ( $normalize_chars as $ch1 => $ch2 ) {
			$text = preg_replace( '/' . $ch1 . '/i', $ch2, $text );
		}

		return (string) preg_replace( '/[^-a-zA-Z0-9  .:;()]/', '', $text );
	}
}
