<?php
// Copyright (c) 2011, Neuman Vong

// All rights reserved.

// Redistribution and use in source and binary forms, with or without
// modification, are permitted provided that the following conditions are met:

//     * Redistributions of source code must retain the above copyright
//       notice, this list of conditions and the following disclaimer.

//     * Redistributions in binary form must reproduce the above
//       copyright notice, this list of conditions and the following
//       disclaimer in the documentation and/or other materials provided
//       with the distribution.

//     * Neither the name of Neuman Vong nor the names of other
//       contributors may be used to endorse or promote products derived
//       from this software without specific prior written permission.

// THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
// "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
// LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
// A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
// OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
// SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
// LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
// DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
// THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
// (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
// OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
/**
 * JSON Web Token implementation
 *
 * Minimum implementation used by Realtime auth, based on this spec:
 * http://self-issued.info/docs/draft-jones-json-web-token-01.html.
 *
 * @author Neuman Vong <neuman@twilio.com>
 */
class Bt_Ipay_Jwt {

	/**
	 * @param string      $jwt    The JWT
	 * @param string|null $key    The secret key
	 * @param bool        $verify Don't skip verification process
	 *
	 * @return object The JWT's payload as a PHP object
	 */
	public static function decode( $jwt, $key = null, $verify = true ) {
		$tks = explode( '.', $jwt );
		if ( count( $tks ) !== 3 ) {
			throw new UnexpectedValueException( 'Wrong number of segments' );
		}
		list($headb64, $payloadb64, $cryptob64) = $tks;
		$header                                 = self::json_decode( self::urlsafe_b64_decode( $headb64 ) );
		if ( null === $header ) {
			throw new UnexpectedValueException( 'Invalid segment encoding' );
		}
		$payload = self::json_decode( self::urlsafe_b64_decode( $payloadb64 ) );
		if ( null === $payload ) {
			throw new UnexpectedValueException( 'Invalid segment encoding' );
		}
		$sig = self::urlsafe_b64_decode( $cryptob64 );
		if ( $verify ) {
			if ( empty( $header->alg ) ) {
				throw new DomainException( 'Empty algorithm' );
			}
			if ( ! hash_equals( self::sign( "$headb64.$payloadb64", $key, $header->alg ), $sig ) ) {
				throw new UnexpectedValueException( 'Signature verification failed' );
			}
		}
		return $payload;
	}

	/**
	 * @param object|array $payload PHP object or array
	 * @param string       $key     The secret key
	 * @param string       $algo    The signing algorithm
	 *
	 * @return string A JWT
	 */
	public static function encode( $payload, $key, $algo = 'HS256' ) {
		$header = array(
			'typ' => 'JWT',
			'alg' => $algo,
		);

		$segments      = array();
		$segments[]    = self::urlsafe_b64_encode( self::json_encode( $header ) );
		$segments[]    = self::urlsafe_b64_encode( self::json_encode( $payload ) );
		$signing_input = implode( '.', $segments );

		$signature  = self::sign( $signing_input, $key, $algo );
		$segments[] = self::urlsafe_b64_encode( $signature );

		return implode( '.', $segments );
	}

	/**
	 * @param string $msg    The message to sign
	 * @param string $key    The secret key
	 * @param string $method The signing algorithm
	 *
	 * @return string An encrypted message
	 */
	public static function sign( $msg, $key, $method = 'HS256' ) {
		$methods = array(
			'HS256' => 'sha256',
			'HS384' => 'sha384',
			'HS512' => 'sha512',
		);
		if ( empty( $methods[ $method ] ) ) {
			throw new DomainException( 'Algorithm not supported' );
		}
		return hash_hmac( $methods[ $method ], $msg, $key, true );
	}

	/**
	 * @param string $input JSON string
	 *
	 * @return object Object representation of JSON string
	 */
	public static function json_decode( $input ) {
		$obj = json_decode( $input );
		if ( self::json_error_exists() ) {
			self::handle_json_error( json_last_error() );
		} elseif ( $obj === null && $input !== 'null' ) {
			throw new DomainException( 'Null result with non-null input' );
		}
		return $obj;
	}

	/**
	 * @param object|array $input A PHP object or array
	 *
	 * @return string JSON representation of the PHP object or array
	 */
	public static function json_encode( $input ) {
		$json = json_encode( $input ); //phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
		if ( self::json_error_exists() ) {
			self::handle_json_error( json_last_error() );
		} elseif ( $json === 'null' && $input !== null ) {
			throw new DomainException( 'Null result with non-null input' );
		}
		return $json;
	}

	private static function json_error_exists() {
		if ( ! function_exists( 'json_last_error' ) ) {
			return false;
		}
		return json_last_error() !== JSON_ERROR_NONE;
	}

	/**
	 * @param string $input A base64 encoded string
	 *
	 * @return string A decoded string
	 */
	public static function urlsafe_b64_decode( $input ) {
		$remainder = strlen( $input ) % 4;
		if ( $remainder ) {
			$padlen = 4 - $remainder;
			$input .= str_repeat( '=', $padlen );
		}
		return base64_decode( strtr( $input, '-_', '+/' ) );//phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
	}

	/**
	 * @param string $input Anything really
	 *
	 * @return string The base64 encode of what you passed in
	 */
	public static function urlsafe_b64_encode( $input ) {
		return str_replace( '=', '', strtr( base64_encode( $input ), '+/', '-_' ) ); //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
	}

	/**
	 * @param int $errno An error number from json_last_error()
	 *
	 * @return void
	 */
	private static function handle_json_error( $errno ) {
		$messages = array(
			JSON_ERROR_DEPTH     => 'Maximum stack depth exceeded',
			JSON_ERROR_CTRL_CHAR => 'Unexpected control character found',
			JSON_ERROR_SYNTAX    => 'Syntax error, malformed JSON',
		);
		throw new DomainException(
			isset( $messages[ $errno ] )
			? $messages[ $errno ] //phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
			: 'Unknown JSON error: ' . $errno //phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
		);
	}
}
