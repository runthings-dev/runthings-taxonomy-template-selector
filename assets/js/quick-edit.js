/**
 * Quick edit functionality for taxonomy template selector
 *
 * @package Runthings_Taxonomy_Template_Selector
 */

( function( $ ) {
	'use strict';

	if ( typeof inlineEditTax === 'undefined' ) {
		return;
	}

	var origEdit = inlineEditTax.edit;

	inlineEditTax.edit = function( id ) {
		origEdit.apply( this, arguments );

		if ( typeof id === 'object' ) {
			id = this.getId( id );
		}

		var row = $( '#tag-' + id );
		var template = row.find( '.column-taxonomy_template' ).text().trim();
		var editRow = $( '#edit-' + id );
		var select = editRow.find( '#runthings_taxonomy_template_selector_quick_edit' );

		// Reset to default, then try to match by display name.
		select.val( 'default' );
		if ( template && template !== '—' ) {
			select.find( 'option' ).each( function() {
				if ( $( this ).text() === template ) {
					select.val( $( this ).val() );
					return false;
				}
			} );
		}
	};
} )( jQuery );

