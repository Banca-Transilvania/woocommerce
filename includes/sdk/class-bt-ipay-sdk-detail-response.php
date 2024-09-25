<?php

use BTransilvania\Api\Model\Response\GetOrderStatusResponseModel;


/**
 * @link       https://btepos.ro/module-ecommerce
 * @since      1.0.0
 *
 * @package    Bt_Ipay
 * @subpackage Bt_Ipay/includes/sdk
 */

/**
 * @since      1.0.0
 * @package    Bt_Ipay
 * @subpackage Bt_Ipay/includes/sdk
 * @author     Banca Transilvania <no-reply@btepos.ro>
 */
class Bt_Ipay_Sdk_Detail_Response {

	private GetOrderStatusResponseModel $response;

	public function __construct(
		GetOrderStatusResponseModel $response
	) {
		$this->response = $response;
	}

	public function is_successful(): bool {
		return $this->response->isSuccess();
	}

	public function get_error_message(): ?string {
		return $this->response->getErrorMessage();
	}


	public function payment_is_accepted() {
		return in_array(
			$this->get_status(),
			array(
				Bt_Ipay_Payment_Storage::STATUS_DEPOSITED,
				Bt_Ipay_Payment_Storage::STATUS_APPROVED,
			)
		);
	}

	public function get_status(): string {
		$info = $this->response->paymentAmountInfo;
		if ( $info !== null && property_exists( $info, 'paymentState' ) ) {
			return $info->paymentState; //phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		}
		return 'UNKNOWN';
	}

	public function can_save_card(): bool {
		return $this->get_status() === Bt_Ipay_Payment_Storage::STATUS_VALIDATION_FINISHED;
	}

	public function is_authorized(): bool {
		return $this->get_status() === Bt_Ipay_Payment_Storage::STATUS_APPROVED;
	}

	/**
	 * Can refund order
	 *
	 * @return boolean
	 */
	public function can_refund(): bool {
		return in_array(
			$this->get_status(),
			array(
				Bt_Ipay_Payment_Storage::STATUS_DEPOSITED,
				Bt_Ipay_Payment_Storage::STATUS_PARTIALLY_REFUNDED,
			)
		);
	}

	/**
	 * Get total available for refund
	 *
	 * @return float
	 */
	public function get_total_available(): float {
		$info = $this->response->paymentAmountInfo;
		if (
			$info !== null &&
			property_exists( $info, 'depositedAmount' ) &&
			is_scalar( $info->depositedAmount ) //phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		) {
			return ( (int) $info->depositedAmount ) / 100; //phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		}
		return 0.0;
	}

	public function get_amount(): float {
		if ( is_int( $this->response->amount ) ) {
			return $this->response->amount / 100;
		}
		return 0.0;
	}


	/**
	 * Get total refunded for refund
	 *
	 * @return float
	 */
	public function get_total_refunded(): float {
		$info = $this->response->paymentAmountInfo;
		if (
			$info !== null &&
			property_exists( $info, 'refundedAmount' ) &&
			is_scalar( $info->refundedAmount ) //phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		) {
			return ( (int) $info->refundedAmount ) / 100; //phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		}
		return 0.0;
	}

	/**
	 * Get loy amount for combined payment(loy+currency)
	 *
	 * @return float
	 */
	public function get_loy_amount(): float {
		if ( is_array( $this->response->merchantOrderParams ) ) {//phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			foreach ( $this->response->merchantOrderParams as $param ) {//phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				if ( $param instanceof \stdClass ) {
					if ( $param->name === 'loyaltyAmount' && is_scalar( $param->value ) ) {
						return floatval( $param->value ) / 100;
					}
				}
			}
			return 0.0;
		}
		return 0.0;
	}

	/**
	 * Get loy id for combined payment(loy+currency)
	 *
	 * @return string|null
	 */
	public function get_loy_id(): ?string {
		if ( is_array( $this->response->attributes ) ) {
			foreach ( $this->response->attributes as $attribute ) {
				if ( $attribute instanceof \stdClass ) {
					if ( $attribute->name === 'loyalties' && is_string( $attribute->value ) ) {
						$loy = explode( ',', $attribute->value );
						$loy = explode( ':', $loy[0] );

						if ( isset( $loy[1] ) && is_string( $loy[1] ) ) {
							return $loy[1];
						}
					}
				}
			}
			return null;
		}
		return null;
	}

	public function get_code(): ?string {
		return $this->response->getActionCode();
	}


	public function to_array(): array {
		return $this->response->toArray();
	}

	public function get_error(): ?string {
		return $this->response->getCustomerError();
	}

	public function get_card_info(): ?array {
		$card_info = $this->response->cardAuthInfo;

		$card_ids = $this->get_card_ids();
		if (
			! $card_info instanceof \stdClass ||
			! is_array( $card_ids )
		) {
			return null;
		}
		$card = (array) $card_info;
		return array_merge( $card, $card_ids );
	}

	public function get_card_ids(): ?array {
		$binding = $this->response->bindingInfo;
		if ( ! $binding instanceof \stdClass ) {
			return null;
		}

		if (
			is_string( $binding->bindingId ) && is_string( $binding->clientId ) //phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		) {
			return array(
				'ipay_id'     => $binding->bindingId, //phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				'customer_id' => $binding->clientId, //phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			);
		}
		return null;
	}
}
