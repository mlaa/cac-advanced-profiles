( function( $ ){
	var $available_fields,
		$cacap_vitals,
		$drop_target,
		$processing_fields,
		$removed_field,
		cloned_available_field,
		cloned_available_field_id,
		cloned_edit_field,
		cloned_edit_field_id,
		col_from,
		col_to,
		edit_uis = [],
		saved_values = {};

	$( document ).ready( function() {
		/* Set up the Profile Header (Public) section */
		$available_fields = $( '#available-fields' );

		$( '#available-fields li' ).draggable( {
			revert: 'invalid',
			snap: '#cacap-vitals',
			snapMode: 'inner'
		} );

		$cacap_vitals = $( '#cacap-vitals' );

		$( '.cacap-droppable' ).droppable( {
			accept: '#available-fields li',
			drop: function( event, ui ) {
				process_vital_add( event, ui );
			},
			hoverClass: 'drop-hover'
		} );

		$( '#cacap-form-cacap-profile-header-public' ).submit( function( e ) {
			process_form_submit_header_public( e );
		} );

		// Bind the x-to-remove click
		$( '.remove-vital' ).on( 'click', function( e ) {
			process_vital_remove( e );
		} );

		/* Set up the Profile Header (Edit) section */
		edit_ui_init();
	} );

	function edit_ui_init() {
		// Sort within columns
		$( '.cacap-profile-edit-columns > div > ul' ).sortable( {
			connectWith: '.cacap-profile-edit-columns > div > ul'
		} );
	}

	function process_vital_add( event, ui ) {
		$drop_target = $( event.target );

		cloned_available_field = ui.draggable.clone();
		cloned_available_field.removeAttr( 'style' ).removeAttr( 'class' );
		cloned_available_field_id = cloned_available_field.attr( 'id' ).replace( 'available', 'vital' );
		cloned_available_field.attr( 'id', cloned_available_field_id );
		cloned_available_field.prepend( '<a href="#" class="remove-vital">x</a>' );

		// Add the field and make sortable
		$drop_target.append( cloned_available_field );

		$cacap_vitals = $( '#cacap-vitals' );
		$cacap_vitals.sortable( {
			items: 'li:not(.cacap-inner-label)'
		} );

		// Hide the original
		ui.draggable.hide();

		// Bind the remove click
		$drop_target.find( '.remove-vital' ).on( 'click', function( e ) {
			process_vital_remove( e );
		} );

		// Hide the inner-label
		$drop_target.addClass( 'not-empty' ).removeClass( 'empty' );

		// Only vitals can have multiples
		if ( $drop_target.attr( 'id' ) !== 'cacap-vitals' ) {
			if ( 1 < $drop_target.find( 'li' ).length ) {
				$removed_field = $( '#' + cloned_available_field_id );
				console.log($removed_field);
				revert_available_field();
			}
		}

		return false;
	}

	function process_vital_remove( e ) {
		e.preventDefault();

		$removed_field = $( e.target ).closest( '.cacap-droppable li' );

		// If this is the last item removed, restore the inner-label
		if ( 1 === $removed_field.closest( '.cacap-droppable' ).find( 'li' ).length ) {
			$removed_field.closest( '.cacap-droppable' ).addClass( 'empty' ).removeClass( 'not-empty' );
		}

		// Not sure why this failsafe is necessary, but sometimes the
		// event double-fires
		if ( $removed_field.length ) {
			revert_available_field();
		}

		return false;
	}

	function revert_available_field() {
		// Unhide the original
		$( '#' + $removed_field.attr( 'id' ).replace( 'vital', 'available' ) ).show().removeAttr( 'style' ).css( 'position', 'relative' );

		$removed_field.remove();
	}

	/**
	 * Process Submit for Header tab.
	 */
	function process_form_submit_header_public( event ) {
		// Brief Descriptor
		saved_values.brief_descriptor = '';
		$processing_fields = $( '#cacap-brief-descriptor' ).children( 'li' );
		if ( $processing_fields.length ) {
			saved_values.brief_descriptor = $processing_fields.data( 'field-id' )
		}

		// About You
		saved_values.about_you = '';
		$processing_fields = $( '#cacap-about-you' ).children( 'li' );
		if ( $processing_fields.length ) {
			saved_values.about_you = $processing_fields.data( 'field-id' )
		}

		// Vitals
		saved_values.vitals = [];
		$processing_fields = $( '#cacap-vitals' ).children( 'li' );
		if ( $processing_fields.length ) {
			$processing_fields.each( function( k, v ) {
				saved_values.vitals.push( $( v ).data( 'field-id' ) );
			} );
		}

		// Convert to json and send along with the payload
		$( event.target ).append( '<input type="hidden" name="cacap-saved-values-header-public" id="cacap-saved-values" value="" />' );
		$( '#cacap-saved-values' ).val( JSON.stringify( saved_values ) );
	}
})( jQuery );
