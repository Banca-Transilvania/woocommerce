<?php

if ( ! defined( 'ABSPATH' ) ) exit;

$cards = $this->get_user_saved_card();
/** @var Bt_Ipay_Gateway $this  */

$have_cards = is_array( $cards ) && count( $cards );

echo wp_kses_post( wpautop( wptexturize( $this->description ) ) );
if ( $this->can_show_cards_on_file() ) {
	if ( $have_cards ) {
		?>
		<label for="bt_ipay_use_new_card" class="bt-ipay-use-new-card">
			<input type="checkbox" name="bt_ipay_use_new_card" id="bt_ipay_use_new_card" value="yes">
			<?php echo esc_html__( 'I want to pay with a new card', 'bt-ipay-payments' ); ?>
		</label>
		<label for="bt-card_id" class="bt-ipay-card-list">
			<?php echo esc_html__( 'Select saved card', 'bt-ipay-payments' ); ?>
			<select name="bt_ipay_card_id" id="bt-card_id" class="bt-ipay-card-select">
				<?php
				foreach ( $cards as $card ) {
					?>
					<option value="<?php echo esc_attr( $card['id'] ); ?>"><?php echo esc_html( $card['pan'] . ' - ' . $card['cardholderName'] ); ?></option>
					<?php
				}
				?>
			</select>
		</label>

		<?php
	}
	?>
	<label for="bt_ipay_save_cards" class="bt-save-card-radio" 
	<?php
	if ( $have_cards ) {
		echo 'style="display:none"';}
	?>
	>
		<input type="checkbox" name="bt_ipay_save_cards" id="bt_ipay_save_cards" value="save">
		<?php echo esc_html__( 'Save my card for future uses', 'bt-ipay-payments' ); ?>
	</label>
	<?php
}
?>