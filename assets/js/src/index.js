/**
 * Entry Point
 *
 * @since 0.1.0
 */

// Import styles.
import '../../css/src/styles.scss';

jQuery( document ).ready( function() {

	/**
	 * Site events switch handler.
	 */
	jQuery( '.mwp-ssas' ).on( 'change', function() {
		const value = jQuery( this ).val();
		jQuery( '#mwpal-site-id' ).val( value );
		jQuery( '#audit-log-viewer' ).submit();
	});

	/**
	 * Number of events switch handler.
	 */
	jQuery( '.mwp-ipps' ).on( 'change', function() {
		const value = jQuery( this ).val();
		jQuery( this ).attr( 'disabled', true );
		jQuery.post( scriptData.ajaxURL, {
			action: 'set_per_page_events',
			count: value,
			nonce: scriptData.scriptNonce
		}, function() {
			location.reload();
		});
	});

	// Remove active tab class.
	if ( 'settings' === scriptData.currentTab ) {
		jQuery( '#mainwp-tabs a:nth-child(2)' ).removeClass( 'nav-tab-active' );
	}

	jQuery( '#mwpal-wsal-child-sites' ).select2({
		placeholder: scriptData.selectSites,
		width: '500px'
	});
});
