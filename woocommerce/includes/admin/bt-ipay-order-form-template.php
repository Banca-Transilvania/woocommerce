<?php

if ( ! defined( 'ABSPATH' ) ) exit;
/** @var Bt_Ipay_Admin_Meta_Box $this */
$payment_data = $this->get_payment_data( $post->ID );
$payments     = $this->get_all_payments( $post->ID );

if ( $this->can_show_form( $payment_data ) ) {
	?>
	<h4><?php echo esc_html__( 'Capture or cancel payment:' , 'bt-ipay-payments' ); ?></h4>
	<input type="hidden" name="bt-ipay-order-id" id="bt-ipay-order-id" value="<?php echo esc_attr( $post->ID ); ?>">
	<div>
		<label for="bt-ipay-capture-amount">
			<?php echo esc_html__( 'Capture amount:' , 'bt-ipay-payments' ); ?>
		</label>
	</div>
	<input type="number" step="0.01" max="<?php echo esc_attr( $this->get_approved_amount( $payment_data ) ); ?>" value="<?php echo esc_attr( $this->get_approved_amount( $payment_data ) ); ?>" name="bt-ipay-capture-amount" id="bt-ipay-capture-amount" />
	<button type="button" class="button button-primary bt-ipay-ajax-capture" type="button">
		<?php echo esc_html__( 'Capture', 'bt-ipay-payments' ); ?>
	</button>
	<button type="button" class="button button-primary bt-ipay-ajax-cancel" type="button">
		<?php echo esc_html__( 'Cancel', 'bt-ipay-payments' ); ?>
	</button>
	<hr style="margin-top:20px;">
	<?php
}
?>
<h4><?php echo esc_html__( 'Payment history:', 'bt-ipay-payments' ); ?></h4>
<table class="wp-list-table widefat fixed table-view-list">
	<thead>
		<tr>
			<th scope="col">
				<?php esc_html_e( 'Payment id', 'bt-ipay-payments' ); ?>
			</th>
			
			<th scope="col" style="text-align:right;">
				<?php esc_html_e( 'Amount', 'bt-ipay-payments' ); ?>
			</th>

			<th scope="col" style="text-align:right;">
				<?php esc_html_e( 'Status', 'bt-ipay-payments' ); ?>
			</th>
		</tr>
	</thead>
	<tbody>
		<?php
		foreach ( $payments as $payment ) {
			?>
			<tr>
				<td>
					<?php echo esc_html( $payment['ipay_id'] ?? '' ); ?>
				</td>

				<td style="text-align:right;">
					<?php echo esc_html( $payment['amount'] ?? 0 ); ?>
				</td>

				<td style="text-align:right;">
					<?php echo esc_html( $payment['status'] ?? '' ); ?>
				</td>
			</tr>
			<?php
			if ( isset( $payment['loy_id'] ) && strlen( (string) $payment['loy_id'] ) > 0 ) {
				?>
				<tr>
					<td>
						<?php echo esc_html__( 'LOY: ', 'bt-ipay-payments' ); ?>
						<?php echo esc_html( $payment['loy_id'] ?? '' ); ?>
					</td>

					<td style="text-align:right;">
						<?php echo esc_html( $payment['loy_amount'] ?? 0 ); ?>
					</td>

					<td style="text-align:right;">
						<?php echo esc_html( $payment['loy_status'] ?? '' ); ?>
					</td>
				</tr>
				<?php
			}
		}
		?>
	</tbody>
</table>