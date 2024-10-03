<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div id="bt-ipay-cof-page">
<div class="bt-ipay-add-card-wrap">
	<button type="button" class="button bt-ipay-add-card">
		<?php echo esc_html_e( 'Add new card', 'bt-ipay-payments' ); ?>
	</button>
	<small>
		<?php
		echo esc_html_e(
			'A simple card verification is required for adding a new card, no amount will be deducted',
			'bt-ipay-payments'
		);
		?>
	</small>
</div>
<?php

if ( ! is_array( $cards ) || count( $cards ) === 0 ) {
	?>
	<div class="woocommerce-info">
		<?php echo esc_html_e( 'No saved cards available yet', 'bt-ipay-payments' ); ?>
	</div>
<?php } else { ?>
	<table class="woocommerce-orders-table shop_table shop_table_responsive account-orders-table">
		<thead>
			<tr>
				<th scope="col">
					<?php esc_html_e( 'Card Holder', 'bt-ipay-payments' ); ?>
				</th>
				<th scope="col">
					<?php esc_html_e( 'Card Number', 'bt-ipay-payments' ); ?>
				</th>
				<th scope="col">
					<?php esc_html_e( 'Expiration', 'bt-ipay-payments' ); ?>
				</th>
				<th scope="col" class="bt-ipay-card-actions">
					<?php esc_html_e( 'Actions', 'bt-ipay-payments' ); ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php
			foreach ( $cards as $card ) {
				$card = $this->decrypt( $card );
				$is_active = ( $card['status'] ?? Bt_Ipay_Card_State_Processor::DISABLED ) === Bt_Ipay_Card_State_Processor::ENABLED;
				?>
				<tr>
					<td>
						<?php echo esc_html( $card['cardholderName'] ?? '' ); ?>
					</td>
					<td>
						<?php echo esc_html( $card['pan'] ?? '' ); ?>
					</td>
					<td>
						<?php
						$exp_date = '';
						if ( isset( $card['expiration'] ) ) {
							$exp_date = substr( $card['expiration'], 4, 2 ) . '/' . substr( $card['expiration'], 0, 4 );
						}
						echo esc_html( $exp_date );
						?>
					</td>
					<td>
						<div class="bt-ipay-actions-wrap">
							<?php if ( $is_active ) { ?>
								<a href="#" class="toggle-ipay-card disable-ipay-card" data-id="<?php echo esc_attr( $card['id'] ); ?>">
									<?php esc_html_e( 'Disable', 'bt-ipay-payments' ); ?>
								</a>
							<?php } else { ?>
								<a href="#" class="toggle-ipay-card enable-ipay-card" data-id="<?php echo esc_attr( $card['id'] ); ?>">
									<?php esc_html_e( 'Enable', 'bt-ipay-payments' ); ?>
								</a>
							<?php } ?>
							<a href="#" class="delete-ipay-card" data-id="<?php echo esc_attr( $card['id'] ); ?>">
								<?php esc_html_e( 'Delete', 'bt-ipay-payments' ); ?>
							</a>
						</div>
					</td>
				</tr>
			<?php } ?>
		</tbody>
	</table>
	<?php
} //end else
?>
</div>