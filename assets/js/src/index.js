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

	/**
	 * Refresh WSAL Child Sites.
	 */
	jQuery( '#mwpal-wsal-sites-refresh' ).click( function() {
		const refreshBtn = jQuery( this );
		refreshBtn.attr( 'disabled', true );
		refreshBtn.val( scriptData.refreshing );

		jQuery.post( scriptData.ajaxURL, {
			action: 'refresh_child_sites',
			nonce: scriptData.scriptNonce
		}, function() {
			location.reload();
		});
	});

	/**
	 * Add Sites to Active Activity Log.
	 */
	jQuery( '#mwpal-wcs-add-btn' ).click( function( e ) {
		e.preventDefault();
		const addSites = jQuery( '#mwpal-wcs input[type=checkbox]' ); // Get checkboxes.
		transferSites( 'mwpal-wcs', 'mwpal-wcs-al', addSites, 'add-sites' );
	});

	/**
	 * Remove Sites from Active Activity Log.
	 */
	jQuery( '#mwpal-wcs-remove-btn' ).click( function( e ) {
		e.preventDefault();
		const removeSites = jQuery( '#mwpal-wcs-al input[type=checkbox]' ); // Get checkboxes.
		transferSites( 'mwpal-wcs-al', 'mwpal-wcs', removeSites, 'remove-sites' );
	});

	/**
	 * Transfer sites in and out of active activity log.
	 *
	 * @param {string} fromClass     – From HTML class.
	 * @param {string} toClass       – To HTML class.
	 * @param {array} containerSites – Sites to add/remove.
	 * @param {string} action        – Type of action to perform.
	 */
	function transferSites( fromClass, toClass, containerSites, action ) {
		let selectedSites = [];
		const container = jQuery( `#${toClass} .sites-container` );
		const activeWSALSites = jQuery( '#mwpal-wsal-child-sites' );

		for ( let index = 0; index < containerSites.length; index++ ) {
			if ( jQuery( containerSites[ index ]).is( ':checked' ) ) {
				selectedSites.push( jQuery( containerSites[ index ]).val() );
			}
		}

		jQuery.ajax({
			type: 'POST',
			url: scriptData.ajaxURL,
			async: true,
			dataType: 'json',
			data: {
				action: 'update_active_wsal_sites',
				nonce: scriptData.scriptNonce,
				transferAction: action,
				activeSites: activeWSALSites.val(),
				requestSites: selectedSites.toString()
			},
			success: function( data ) {
				if ( data.success && selectedSites.length ) {
					for ( let index = 0; index < selectedSites.length; index++ ) {
						let spanElement = jQuery( '<span></span>' );
						let inputElement = jQuery( '<input />' );
						inputElement.attr( 'type', 'checkbox' );
						let labelElement = jQuery( '<label></label>' );
						let tempElement = jQuery( `#${fromClass}-site-${selectedSites[index]}` );

						// Prepare input element.
						inputElement.attr( 'name', `${toClass}[]` );
						inputElement.attr( 'id', `${toClass}-site-${selectedSites[index]}` );
						inputElement.attr( 'value', tempElement.val() );

						// Prepare label element.
						labelElement.attr( 'for', `${toClass}-site-${selectedSites[index]}` );
						labelElement.html( tempElement.parent().find( 'label' ).text() );

						// Append the elements together.
						spanElement.append( inputElement );
						spanElement.append( labelElement );
						container.append( spanElement );

						// Remove the temp element.
						tempElement.parent().remove();
					}
					activeWSALSites.val( data.activeSites );
				} else {
					console.log( data.message );
				}
			},
			error: function( xhr, textStatus, error ) {
				console.log( xhr.statusText );
				console.log( textStatus );
				console.log( error );
			}
		});
	}
});
