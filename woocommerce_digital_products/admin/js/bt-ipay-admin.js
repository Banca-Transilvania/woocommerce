(function ($) {
	'use strict';
	const orderPage = {
		init() {
			this.listenToEvents();
		},
		lockBox() {
			$( '#bt-ipay' ).block(
				{
					message: null,
					overlayCSS: {
						background: '#fff',
						opacity: 0.6
					}
				}
			);
		},
		listenToEvents() {
			$( '.bt-ipay-ajax-capture' ).on( 'click', this.capturePayment.bind( this ) );
			$( '.bt-ipay-ajax-cancel' ).on( 'click', this.cancelPayment.bind( this ) );
		},
		capturePayment() {
			if (confirm( bt_ipay_vars.confirm_capture ) !== true) {
				return;
			}

			this.lockBox();
			const data = {
				action: 'bt_ipay_capture',
				_ajax_nonce: bt_ipay_vars.nonce,
				order_id: $( '#bt-ipay-order-id' ).val(),
				amount: $( '#bt-ipay-capture-amount' ).val()
			}
			$.post(
				ajaxurl,
				data,
				function () {
					window.location.reload();
				}
			);
		},
		cancelPayment() {
			if (confirm( bt_ipay_vars.confirm_cancel ) !== true) {
				return;
			}
			this.lockBox();
			const data = {
				action: 'bt_ipay_cancel',
				_ajax_nonce: bt_ipay_vars.nonce,
				order_id: $( '#bt-ipay-order-id' ).val()
			}
			$.post(
				ajaxurl,
				data,
				function () {
					window.location.reload();
				}
			);
		}
	}
	const btIpayAdmin  = {
		init() {
			this.toggleTestModeFields( $( '#woocommerce_bt-ipay_testMode' ).is( ':checked' ) )
			this.listenToEvents();
		},
		listenToEvents() {
			let self   = this;
			$( '#woocommerce_bt-ipay_testMode' ).on(
				'change',
				function () {
					self.toggleTestModeFields(
						$( this ).is( ':checked' )
					)
				}
			)
		},
		toggleTestModeFields( isTestMode ) {
			$( '#woocommerce_bt-ipay_authKey, #woocommerce_bt-ipay_authPassword' ).closest( 'tr' ).toggle( ! isTestMode );
			$( '#woocommerce_bt-ipay_testAuthKey, #woocommerce_bt-ipay_testAuthPassword' ).closest( 'tr' ).toggle( isTestMode );
		}

	}

	$(
		function () {
			orderPage.init();
			btIpayAdmin.init();
		}
	);

})( jQuery );
