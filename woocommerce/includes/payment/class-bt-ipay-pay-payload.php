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
class Bt_Ipay_Pay_Payload {

	private Bt_Ipay_Order $order_service;

	private bool $is_cof_request;

	private ?string $bt_ipay_card_id;

	private ?string $description;

	public function __construct( Bt_Ipay_Order $order_service, bool $is_cof_request, ?string $bt_ipay_card_id = null, ?string $description = null ) {
		$this->order_service   = $order_service;
		$this->is_cof_request  = $is_cof_request;
		$this->bt_ipay_card_id = $bt_ipay_card_id;
		$this->description = $description;
	}

	public function get_order_service(): Bt_Ipay_Order {
		return $this->order_service;
	}

	public function to_array(): array {
		$order_number = preg_replace( '/\s+/', '_', $this->order_service->get_order_number() );
		$customer     = $this->order_service->get_customer();

		$payload = array(
			'orderNumber' => $order_number,
			'amount'      => $this->order_service->get_total(),
			'currency'    => $this->order_service->get_currency(),
			'email'        => $customer->get_email(),
			'description' => $this->get_description($order_number),
			'returnUrl'   => $this->get_redirect_url(),
			'orderBundle' => array(
				'orderCreationDate' => ( new \DateTime( 'now', new \DateTimeZone( 'Europe/Bucharest' ) ) )->format( 'Y-m-d' ),
				'customerDetails'   => array(
					'email'        => $customer->get_email(),
					'phone'        => $customer->get_phone(),
					'contact'      => $customer->get_full_name(),
					'deliveryInfo' => $this->get_delivery_info(),
					'billingInfo'  => $this->get_billing_info(),
				),
			),
		);

		if (
			$this->is_cof_request === true ||
			$this->bt_ipay_card_id !== null
		) {
			$user = get_current_user_id();
			if ( $user !== 0 ) {
				$payload['clientId'] = $user;
			}

			if ( $this->bt_ipay_card_id !== null ) {
				$payload['bindingId'] = $this->bt_ipay_card_id;
			}
		}

		return $payload;
	}

	private function get_description(string $order_number)
    {
		/* translators: %1$s: order number, %2$s: blog name*/
		$default = sprintf( __( 'Order: %1$s - %2$s ', 'bt-ipay-payments' ), $order_number, get_bloginfo( 'name' ) );
        if (!is_string($this->description) || strlen($this->description) === 0) {
            return $default;
        }

        $description = $this->description;
        $description = preg_replace('/\{order_number\}/', $order_number, $description);
        $description = preg_replace('/\{shop_name\}/', get_bloginfo( 'name' ), (string)$description);

        if (!is_string($this->description) || strlen($this->description) === 0) {
            return $default;
        }
        
        return $description;
    }


	private function get_delivery_info(): array {
		$shipping = $this->order_service->get_address( 'shipping' );
		$data     = array(
			'deliveryType' => $this->order_service->get_shipping_method(),
			'country'      => $shipping->get_country(),
			'city'         => substr( $shipping->get_city(), 0, 40 ),
			'postalCode'   => $shipping->get_postal_code(),
		);
		return array_merge( $data, $this->get_address_chunks( $shipping ) );
	}

	private function get_billing_info(): array {
		$billing = $this->order_service->get_address( 'billing' );
		$data    = array(
			'deliveryType' => $this->order_service->get_shipping_method(),
			'country'      => $billing->get_country(),
			'city'         => substr( $billing->get_city(), 0, 40 ),
			'postAddress'  => $billing->get_address_1(),
			'postalCode'   => $billing->get_postal_code(),
		);

		if ( strlen( $billing->get_address_2() ) ) {
			$data['postAddress2'] = $billing->get_address_2();
		}
		return array_merge( $data, $this->get_address_chunks( $billing ) );
	}

	private function get_address_chunks( $address ): array {
		$parts = str_split( $address->get_full_address(), 50 );
		$parts = array_slice( $parts, 0, 3 );

		$data = array();
		foreach ( $parts as $index => $part ) {
			$ending = $index + 1;
			if ( $ending === 1 ) {
				$ending = '';
			}
			$data[ 'postAddress' . $ending ] = $part;
		}
		return $data;
	}

	/**
	 * Get return url
	 *
	 * @return string
	 */
	private function get_redirect_url(): string {
		return add_query_arg( 'wc-api', 'Bt_Ipay_Return', home_url( '/' ) );
	}
}
