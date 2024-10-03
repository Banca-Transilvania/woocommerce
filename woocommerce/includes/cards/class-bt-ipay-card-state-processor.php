<?php

use BTransilvania\Api\Model\Response\BindCardResponseModel;

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
class Bt_Ipay_Card_State_Processor {

	public const ENABLED  = 'enabled';
	public const DISABLED = 'disabled';

	protected string $ipay_card_id;

	protected Bt_Ipay_Card_Storage $storage;

	protected bool $enable;

	public function __construct( Bt_Ipay_Card_Storage $storage, string $ipay_card_id, bool $enable ) {
		$this->ipay_card_id = $ipay_card_id;
		$this->storage      = $storage;
		$this->enable       = $enable;
	}

	public function process( BindCardResponseModel $response, bool $show_notice = true ) {
		if ( ! $response->isSuccess() ) {
			wc_add_notice(
				__( 'Could not change card status', 'bt-ipay-payments' ),
				'error'
			);
			( new Bt_Ipay_Logger() )->error( $response->getErrorMessage() );
			return;
		}

		$this->storage->update_status(
			$this->enable ? self::ENABLED : self::DISABLED,
			$this->ipay_card_id
		);

		if ( $show_notice ) {
			wc_add_notice(
				__( 'Status changed successfully', 'bt-ipay-payments' )
			);
        }
	}
}
