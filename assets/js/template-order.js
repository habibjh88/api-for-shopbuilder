/**
 * Inline "Order" editor for the ShopBuilder template list.
 *
 * @package RT_SB_API
 */

/* global jQuery, rtsbApiTemplateOrder */
( function ( $, settings ) {
	'use strict';

	if ( ! settings ) {
		return;
	}

	var TemplateOrder = {
		/**
		 * Init.
		 */
		init: function () {
			$( document )
				.on( 'focus', '.rtsb-api-template-order', this.rememberValue )
				.on( 'change', '.rtsb-api-template-order', this.onChange );

			$( '.rtsb-api-template-order' ).each( function () {
				$( this ).data( 'saved', $( this ).val() );
			} );
		},

		/**
		 * Store the last saved value.
		 */
		rememberValue: function () {
			var $input = $( this );

			if ( undefined === $input.data( 'saved' ) ) {
				$input.data( 'saved', $input.val() );
			}
		},

		/**
		 * Save the new order.
		 */
		onChange: function () {
			var $input = $( this ),
				value = parseInt( $input.val(), 10 ),
				saved = String( $input.data( 'saved' ) );

			if ( $input.data( 'busy' ) ) {
				return;
			}

			if ( isNaN( value ) || value < 0 ) {
				$input.val( saved );
				TemplateOrder.feedback( $input, 'error', '✕' );
				return;
			}

			if ( String( value ) === saved ) {
				$input.val( saved );
				return;
			}

			$input.data( 'busy', true ).prop( 'disabled', true );
			TemplateOrder.feedback( $input, 'saving', '…' );

			$.post( settings.ajaxUrl, {
				action: settings.action,
				nonce: settings.nonce,
				post_id: $input.data( 'id' ),
				order: value
			} )
				.done( function ( response ) {
					if ( response && response.success ) {
						$input.val( response.data.order ).data( 'saved', String( response.data.order ) );
						TemplateOrder.feedback( $input, 'saved', '✓' );
					} else {
						$input.val( saved );
						TemplateOrder.feedback( $input, 'error', '✕' );
					}
				} )
				.fail( function () {
					$input.val( saved );
					TemplateOrder.feedback( $input, 'error', '✕' );
				} )
				.always( function () {
					$input.data( 'busy', false ).prop( 'disabled', false );
				} );
		},

		/**
		 * Show short-lived status feedback.
		 *
		 * @param {jQuery} $input Input element.
		 * @param {string} state  saving|saved|error.
		 * @param {string} text   Status text.
		 */
		feedback: function ( $input, state, text ) {
			var colors = { saving: '#dba617', saved: '#00a32a', error: '#d63638' },
				$status = $input.next( '.rtsb-api-template-order-status' );

			if ( ! $status.length ) {
				$status = $( '<span class="rtsb-api-template-order-status" aria-live="polite"></span>' )
					.css( { marginLeft: '6px', fontWeight: 600 } )
					.insertAfter( $input );
			}

			clearTimeout( $input.data( 'timer' ) );
			$input.css( 'border-color', colors[ state ] );
			$status.text( text ).css( 'color', colors[ state ] ).show();

			if ( 'saving' !== state ) {
				$input.data(
					'timer',
					setTimeout( function () {
						$input.css( 'border-color', '' );
						$status.fadeOut( 200 );
					}, 2000 )
				);
			}
		}
	};

	$( TemplateOrder.init.bind( TemplateOrder ) );
}( jQuery, window.rtsbApiTemplateOrder ) );
