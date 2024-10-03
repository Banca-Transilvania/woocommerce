(function ($) {
	'use strict';
	$(
		function () {
			$( '.toggle-ipay-card' ).on(
				'click',
				function (e) {
					e.preventDefault();
					const id     = $( this ).attr( 'data-id' );
					const enable = $( this ).hasClass( 'enable-ipay-card' );
					const data   = {
						action: 'bt_ipay_toggle_card_state',
						_ajax_nonce: bt_ipay_vars.nonce,
						card_id: id,
						enable
					}
					$.post(
						bt_ipay_vars.ajaxurl,
						data,
						function () {
							window.location.reload();
						}
					);
				}
			)

			$( '.delete-ipay-card' ).on(
				'click',
				function (e) {
					e.preventDefault();
					if (confirm( bt_ipay_vars.confirm_card_delete )) {
						const id   = $( this ).attr( 'data-id' );
						const data = {
							action: 'bt_ipay_delete_card',
							_ajax_nonce: bt_ipay_vars.nonce,
							card_id: id,
						}
						$.post(
							bt_ipay_vars.ajaxurl,
							data,
							function () {
								window.location.reload();
							}
						);
					}
				}
			)

			$( '.bt-ipay-add-card' ).on(
				'click',
				function (e) {
					e.preventDefault();
					const data = {
						action: 'bt_ipay_save_card',
						_ajax_nonce: bt_ipay_vars.nonce,
					}
					$.post(
						bt_ipay_vars.ajaxurl,
						data,
						function (resp) {
							if (resp.redirect) {
								window.location.href = resp.redirect;
								return;
							}
							window.location.reload();
						}
					);
				}
			)

			$( 'body' ).on(
				'change',
				'#bt_ipay_use_new_card',
				function () {
					const checked = $( this ).is( ':checked' );
					$( '.bt-ipay-card-list' ).toggle( ! checked );
					$( '.bt-save-card-radio' ).toggle( checked );
				}
			)
		}
	);
})( jQuery );
