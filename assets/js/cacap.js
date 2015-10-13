// Ensure the global `wp` object exists.
window.wp = window.wp || {};

(function($){
	var cacap = {
		self: this,
		about_you_max_length: 350,
		class_to_add: '',
		currently_editing: '',
		currently_editing_position: '',
		exit_confirm: '',
		field_char_count: '',
		hallo_top: '',
		jcw_id: '',
		jcw_target_is_button: '',
		keypress_code: '',
		message: '', 
		new_widget_count: '',
		new_widget_prototype: '',
		ok_or_cancel: '',
		position_id: '',
		positions: '',
		positions_count: '',
		positions_static_text: '',
		this_position_static_text: '',
		wid: '',
		widget_order: '',
		widget_value_cache: {},
		window_height: '',
		wtype: '',
		$about_you: '',
		$about_you_gloss: '',
		$current_position: '',
		$current_field: '',
		$currently_editing: '',
		$field_to_clear: '',
		$hallo_toolbar: '',
		$jcw_half: '', // "just clicked widget"
		$jcw_target: '',
		$new_widget_button: '',
		$position_delete_button: '',
		$position_field: '',
		$position_label: '',
		$positions_fields: '',
		$positions_static_text_field: '',
		$positions_widget: '',
		$positions_widget_inputs: '',
		$positions_widget_static_text: '',
		$w: '',
		$widget_list: '',
		$widget_order: '',
		$wtitle: '',

		/**
		 * Set up the 'js' body class.
		 *
		 * BuddyPress should do this, but just in case
		 */
		init_bodyclass: function() {
			if ( $('body').hasClass('no-js') ) { 
				document.body.className = document.body.className.replace( /no-js/, 'js' );
			} else { 
				$('body').addClass('js'); 
			} 
		},

		/**
		 * Set up the sticky header.
		 */
		init_stickyheader: function() {
/*
 *                        if ( $( 'body' ).hasClass( 'short-header' ) ) {
 *                                return;
 *                        }
 *
 *                        window_height = "innerHeight" in window ? window.innerHeight : document.documentElement.offsetHeight;
 *
 *                        if ( window_height < $(document).height() - 200 ) {
 *                                $('.cacap-hero-row').waypoint('sticky', {
 *                                        offset: 10,
 *                                        wrapper: '<div class="cacap-hero-row-sticky" />'
 *                                } );
 *                        }
 */
		},

		/** 
		 * Make a pop-up bubble first first-timers
		 * to alert them of the changes. 
		 */ 
		init_bubble: function() { 
			if ($.cookie('MLAPortfolios') == null ){ 
				// me want cookie, but no have cookie. 
				$.cookie('MLAPortfolios', 1); // so me CREATE cookie. 
				var modal_inst = $.remodal.lookup[$('[data-remodal-id=modal]').data('remodal')];

				// open a modal
				if ( modal_inst ) { 
					modal_inst.open(); 
				} 
			} 
		},
		/**
		 * Initialize the sortable widgets.
		 */
		init_sortable_widgets: function() {
			self.$widget_order = $( '#cacap-widget-order' );

			self.$widget_list.sortable({
				placeholder: 'ui-state-highlight',
				containment: $( '.cacap-widgets-edit' ),
				handle: '.cacap-drag-handle',
				stop: function( event, ui ) {
					self.$widget_order.val($(this).sortable( 'toArray' ));
				}
			});
		},

		/**
		 * Initialize editable widgets.
		 *
		 * These are the widgets that use contentEditable.
		 */
		init_editable_widgets: function() {
			$('article.richtext').each( function() {
				$( this ).hallo( {
					toolbar: 'halloToolbarFixed',
					toolbarOptions: {
						parentElement: $(this).closest( '.cacap-click-to-edit' )
					},
					plugins: {
						'halloformat': {},
						'hallolink': {},
						'hallojustify': {},
						'hallolists': {},
						'halloheadings': {}
					}
				} );
			});
		},

		/**
		 * Set up initial positions widgets.
		 */
		init_positions_widgets: function() {
			self.$positions_widget = $('.cacap-widget-positions');
			var $positions_widget_inputs = self.$positions_widget.find( '.cacap-positions-positions' );
			var $positions_widget_static_text = self.$positions_widget.find( '.cacap-positions-static-text' );
			if ( self.$positions_widget.length ) {
				self.transition_positions_to_static_text();

				// Fix prototype classes
				self.$positions_widget.html( self.$positions_widget.html().replace( /\bnewwidgetkey\b/g, 'cacap_positions' ) );

				// Initialize autocomplete for existing widget
				self.positions_autocomplete_setup( self.$positions_widget );

				self.positions_sortable_setup( self.$positions_widget );
			}

			self.$widget_list.on( 'click', '.cacap-add-position', function() {
				self.$w = $( this ).closest( '.cacap-widget-positions' );
				self.clone_add_new_position_fields();
				return false;
			} );

			// Delete a position
			self.$widget_list.on( 'click', '.cacap-delete-position', function(e) {
				e.preventDefault(); 
				$( this ).parent( 'li' ).remove();
			} );


			// Don't allow user to delete first position (there must 
			// be at least one) but leave delete buttons on subsequent
			// positions. 
			$('#cacap-delete-position-1').remove(); 

		},

		/**
		 * Set up the New Widget buttons
		 */
		init_new_widget_buttons: function() {
			new_widget_count = 0;

			$( '#cacap-new-widget-types li' ).on( 'click', function( e ) {
				e.preventDefault();
				self.$new_widget_button = $( this );
				self.add_new_widget();
			} );
		},

		/**
		 * Set up the "are you sure you want to leave?" warning
		 */
		init_exit_confirm: function() {
			exit_confirm = false;

			$( '#cacap-edit-form input:not(:submit), #cacap-edit-form textarea, #cacap-edit-form select' ).change( function() {
				exit_confirm = true;
			} );

			$( '#cacap-edit-form input:submit' ).on( 'click', function() {
				exit_confirm = false;
			} );

			window.onbeforeunload = function() {
				if ( exit_confirm ) {
					return 'Are you sure you want to leave?';
				}
			};
		},

		/**
		 * [ESC] and [ENTER] have special meaning on widget edit inputs
		 */
		init_widget_specialkeys: function() {
			$( '#cacap-edit-form' ).on( 'keydown', 'input:not(:submit), textarea', function(e){
				keypress_code = ( e.keyCode ? e.keyCode : e.which );
				$current_field = $( this );

				// ESC
				if ( keypress_code === 27 ) {
					$current_field.closest( '.cacap-show-on-edit' ).find( '.cacap-cancel' ).trigger( 'click' );
					return false;
				}

				// ENTER
				// We want to preserve Enter behavior in textareas and autocomplete
				if ( keypress_code === 13 && 'textarea' !== this.type && ! $current_field.hasClass( 'ui-autocomplete-input' ) ) {
					$current_field.closest( '.cacap-show-on-edit' ).find( '.cacap-ok' ).trigger( 'click' );
					return false;
				}
			});
		},

		/**
		 * Set up character counter for About You field
		 */
		init_about_you_character_count: function() {
			$about_you = $( 'div.field_about-you textarea' );
			if ( $about_you.length !== 0 ) {

				$about_you.after('<div class="cacap-char-count-gloss">Using <span class="cacap-char-count">0</span> of ' + self.about_you_max_length + ' characters<span class="cacap-char-count-warning"> (additional characters will be trimmed)</span></div>');

				$about_you_gloss = $( '.cacap-char-count-gloss' );

				self.update_character_count_for_field( $about_you );
				$about_you.on( 'keyup', function() { update_character_count_for_field( $about_you ); } );
			}
		},

		/**
		 * Set up clear formatting buttons
		 */
		init_clear_formatting: function() {
			// Delegated
			$( '.cacap-widgets-edit' ).on( 'click', 'a.cacap-clear-formatting', function( e ) {
				if ( confirm( CACAP_Strings.clear_formatting_confirm ) ) {
					$field_to_clear = $( e.target ).closest( '.cacap-widget-section-editable' ).find( '.editable-content' );
					var field_html = $field_to_clear.html().replace( /<br>/g, "__CACAP__BR__" );
					var new_div = document.createElement( "div" );
					new_div.innerHTML = field_html;
					var cleaned_text = new_div.textContent || new_div.innerText || "";
					$field_to_clear.html( cleaned_text.replace( /__CACAP__BR__/g, "<br>" ) );
				}

				return false;
			} );
		},

		/** 
		 * Check whether URL is a valid URL. 
		 * Returns true if valid, false if invalid. 
		 * Regex via http://stackoverflow.com/a/6930641/584121  
		 */ 
		is_valid_URL: function(url) { 
			if( /^(http|https|ftp):\/\/[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/i.test( url ) ){
				return true; 
			} else {
				return false; 
			} 
		},

		/** Checks whether twitter username is valid.
		 */ 
		is_valid_twitter_username: function(username) { 
			return /^\w{1,32}$/.test(username) ? true : false; 
		},
		/**
		 * Validate data like URLs. 
		 * First, checks whether object has a content type of 'url',
		 * 'twitter_username', etc, and validates it as such. 
		 * Returns true if valid, false if invalid. 
		 * Defaults to true. 
		 */ 
		is_valid: function(obj) { 
			var content = obj.html(); 
			if ( obj.hasClass( 'url' ) ) { 

				// strip HTML tags and extra whitespace
				obj.html( obj.text().trim() );  

				//validate URL
				if ( self.is_valid_URL(content) ) { 
					return true; 
				} else { 
					message = 'Please enter a valid URL.'; 
					self.warn_invalid_data(obj, message);
					return false; 
				} 
			} else if (obj.hasClass( 'twitter_username' ) ) { 

				// strip HTML tags and extra whitespace
				obj.html( obj.text().trim() );  

				if ( self.is_valid_twitter_username(content) ) { 
					return true; 
				} else { 
					message = 'Please enter a valid twitter username, without spaces or the "@" prefix.'; 
					self.warn_invalid_data(obj, message);
					return false; 
				} 
			} else { 
				//assume content doesn't need validation, is valid
				return true; 
			} 
		},

		/** 
		 * Process clicking away from an editing area. 
		 * This should basically do the same thing as pressing "ok." 
		 */ 
		process_clickaway: function(e) { 
			var $target = $( '#' + self.currently_editing );
			var $target_editor = $target.find( '.editable-content' );

			$w = $target.parent();
			self.wid = $w.attr( 'id' );

			wtype = self.get_widget_type_from_class( $w.attr( 'class' ) );

			switch ( wtype ) {

				case 'positions' :
					self.transition_positions_to_static_text();
					break;

				case 'rss' :
				case 'twitter' :
					self.process_rss($target);
					break;

				default :
					// first remove <br/>s from single-line fields
					if ( wtype == 'blog' || wtype == 'twitter-username' ) { 
						$target_editor.html($target_editor.html().replace(/<br>/g, '')); 
					} 
					if ( self.is_valid( $target_editor ) ) { 
						$target.find( '.editable-content-stash' ).val( self.remove_unwanted_html_tags( $target_editor.html() ) ); //Copy new content to hidden input 
					} else { 
						e.stopPropagation();
						return;  
					} 

					break;
			}

			// Remove currently_editing toggle
			self.unmark_currently_editing();

			// Remove editing class
			$target.removeClass('editing');

		}, 
		/**
		 * Process the click of an OK or Cancel button.
		 */
		process_okcancel: function( ok_or_cancel ) {
			if ( 'ok' === ok_or_cancel ) {
				// Copy new content to hidden input
				self.$jcw_half.find( '.editable-content-stash' ).val( self.$jcw_half.find( '.editable-content' ).html() );
			} else {
				// Replace the edited content with the cached value
				self.$jcw_half.find( '.editable-content' ).html( self.widget_value_cache[ self.wid ] );
			}

			// Remove editing class
			self.$jcw_half.removeClass( 'editing' );

			// Remove currently_editing toggle
			self.unmark_currently_editing();
		},

		/**
		 * Process an RSS widget
		 */
		process_rss: function($target) {
			// The title side is a normal editable field
			if ( $target.closest( '.cacap-widget-section-editable' ).hasClass( 'cacap-widget-title' ) ) {
				if ( 'ok' === ok_or_cancel ) {
					// Copy new content to hidden input
					$target.find( '.editable-content-stash' ).val( $target.find( '.editable-content' ).html() );
				} else {
					// Replace the edited content with the cached value
					$target.find( '.editable-content' ).html( widget_value_cache[ wid ] );
				}

			// The content side is an input field
			} else {
				if ( 'ok' === ok_or_cancel ) {
					// nothing to do?
				} else {
					$target.find( 'input.cacap-edit-input' ).val( widget_value_cache[ wid ] );
				}
			}

		},

		/**
		 * Remove unwanted HTML tags from widget input. 
		 * This allows users to paste rich text from word processors.
		 */ 
		remove_unwanted_html_tags: function(raw_html) { 
			var $wrapped_html = $('<div>' + raw_html + '</div>'); // jquery needs these wrapped up
			$wrapped_html.find('style, meta, head').remove(); //remove tags
			$wrapped_html.find('p, div').prepend('<br/>').contents().unwrap(); //replace <p> tags with <br> tags 

			var result = $wrapped_html.html().trim().replace(/(<br>){3,}/gi, '<br><br>').replace(/\n/g, " "); 
			return result; 
		},

		/**
		 * Process the click of an OK or Cancel button.
		 */
		process_okcancel: function( ok_or_cancel ) {
			if ( 'ok' === ok_or_cancel ) {
				// Copy new content to hidden input
				self.$jcw_half.find( '.editable-content-stash' ).val( self.$jcw_half.find( '.editable-content' ).html() );
			} else {
				// Replace the edited content with the cached value
				self.$jcw_half.find( '.editable-content' ).html( self.widget_value_cache[ self.wid ] );
			}

			// Remove editing class
			self.$jcw_half.removeClass( 'editing' );

			// Remove currently_editing toggle
			self.unmark_currently_editing();
		},

		/**
		 * Process the click of an OK or Cancel button on an RSS widget
		 */
		process_okcancel_rss: function( ok_or_cancel ) {
			// The title side is a normal editable field
			if ( self.$jcw_target.closest( '.cacap-widget-section-editable' ).hasClass( 'cacap-widget-title' ) ) {
				if ( 'ok' === ok_or_cancel ) {
					// Copy new content to hidden input
					$jcw_half.find( '.editable-content-stash' ).val( $jcw_half.find( '.editable-content' ).html() );
				} else {
					// Replace the edited content with the cached value
					$jcw_half.find( '.editable-content' ).html( widget_value_cache[ self.wid ] );
				}

			// The content side is an input field
			} else {
				if ( 'ok' === ok_or_cancel ) {
					// nothing to do?
				} else {
					$jcw_half.find( 'input.cacap-edit-input' ).val( widget_value_cache[ self.wid ] );
				}
			}

			// Remove editing class
			$jcw_half.removeClass( 'editing' );

			// Remove currently_editing toggle
			self.unmark_currently_editing();
		},

		/**
		 * Process the click of an OK or Cancel button in a Positions widget.
		 */
		process_okcancel_positions: function( ok_or_cancel ) {
			if ( 'ok' === ok_or_cancel ) {
				self.transition_positions_to_static_text();
			} else {

			}

			// Remove editing class
			self.$jcw_half.removeClass( 'editing' );

			// Remove currently_editing toggle
			self.unmark_currently_editing();
		},

		/**
		 * Toggle editable widget areas (when clicked).
		 */
		toggle_editable: function() {
			// Cache the current value of the widget, in case of Cancel
			self.widget_value_cache[ self.wid ] = self.$jcw_target.html();

			// Add the 'editing' class
			self.$jcw_half.addClass( 'editing' );
		},

		/**
		 * Toggle editable widget RSS areas (when clicked).
		 */
		toggle_editable_rss: function() {
			// Cache the current value of the widget, in case of Cancel
			widget_value_cache[ self.wid ] = $jcw_half.find( 'input.cacap-edit-input' ).val();

			// Add the 'editing' class
			$jcw_half.addClass( 'editing' );
		},

		/**
		 * Toggle editable positions widget area (when clicked).
		 */
		toggle_editable_positions: function() {
			// Add the 'editing' class
			self.$jcw_half.addClass( 'editing' );
		},

		/**
		 * Get a canonical widget_type from a widget classname.
		 */
		get_widget_type_from_class: function( classname ) {
			var widget_type_regex = /cacap\-widget\-([a-zA-Z0-9\-]+)/;
			return classname.match(widget_type_regex).pop();
		},

		/**
		 * Transition a Positions inputs widget to static text.
		 */
		transition_positions_to_static_text: function() {
			positions = [];

			$positions_widget = $('.cacap-widget-positions');
			var $positions_widget_inputs = $positions_widget.find( '.cacap-positions-positions' );
			$positions_widget_inputs.find( '.cacap-position' ).each( function( index ) {
				$current_position = $( this );

				if ( 'new' !== $current_position.attr( 'id' ).split( '-' ).pop() ) {
					positions.push({
						'college': $current_position.find( '.cacap-position-field-college' ).val(),
						'department': $current_position.find( '.cacap-position-field-department' ).val(),
						'title': $current_position.find( '.cacap-position-field-title' ).val()
					});
				}
			} );

			positions_static_text = '';

			for ( var i = 0; i < positions.length; i++ ) {
				this_position_static_text = '';
				if ( positions[i].title ) { 
					this_position_static_text += '<span class="cacap-positions-title">' + positions[i].title + '</span> ';
				} 
				if ( positions[i].department ) { 
					this_position_static_text += '<span class="cacap-positions-department">' + positions[i].department + '</span>';
				} 
				if ( positions[i].college ) { 
					this_position_static_text += '<span class="cacap-positions-college">' + positions[i].college + '</span>';
				} 

				if ( this_position_static_text.length ) {
					positions_static_text += '<li>' + this_position_static_text + '</li>';
				}
			}

			$positions_static_text_field = $positions_widget.find( '.cacap-positions-static-text' );
			if ( ! $positions_static_text_field.length ) {
				$positions_widget.find( '.cacap-widget-content' ).append( '<div class="cacap-positions-static-text"></div>' );
				$positions_static_text_field = $positions_widget.find( '.cacap-positions-static-text' );
			}

			if ( positions_static_text.length ) {
				positions_static_text = '<ul class="cacap-positions-list">' + positions_static_text + '</ul>';
			}

			$positions_static_text_field.html( positions_static_text );
		},

		/**
		 * Clone 'new position' fields for a new position
		 */
		clone_add_new_position_fields: function() {
			// Find and unhide
			var $positions_fields = self.$w.find( '.cacap-position-new' ).children( 'li' ).clone();

			// Swap 'new' with proper iterator
			// Subtract 1 for the prototype, but readd for new field
			positions_count = self.$w.find( '.cacap-position' ).length;
			position_id = 'cacap-position-' + positions_count;

			// Swap 'id' attr
			$positions_fields.find('.cacap-position').removeAttr( 'id' ).attr( 'id', position_id );

			// Swap 'for' attr
			$positions_fields.find( 'label' ).each( function() {
				$position_label = $( this );
				$position_label.attr( 'for', $position_label.attr( 'for' ).replace( 'cacap-position-new', position_id ) );
			} );

			// Swap 'id' and 'name' for input and select fields
			$positions_fields.find( 'input,select' ).each( function() {
				$position_field = $( this );
				$position_field.attr( 'id', $position_field.attr( 'id' ).replace( 'cacap-position-new', position_id ) );
				$position_field.attr( 'name', $position_field.attr( 'name' ).replace( /\bnew\b/, positions_count ) );
			} );

			// Don't allow user to delete first position (there must 
			// be at least one) but leave delete buttons on subsequent
			// positions. 
			if ( 1 == positions_count ) { 
				$positions_fields.find( '.cacap-delete-position' ).remove();
			} 

			// Add to the DOM
			$( '.cacap-positions-positions' ).prepend( $positions_fields.wrap( '<li></li>' ) );

			// Init autocomplete and sortable
			self.positions_autocomplete_setup( self.$w );
			self.positions_sortable_setup( self.$w );
			self.reindex_positions_fields();
		},

		/**
		 * Add a new widget
		 */
		add_new_widget: function() {
			// Do nothing if the max has been met for this widget type
			if ( self.$new_widget_button.hasClass( 'cacap-has-max' ) ) {
				return false;
			}

			// Tick the counter (used to construct unique IDs)
			new_widget_count++;

			wtype = self.$new_widget_button.attr( 'id' ).slice( 17 );

			// Get the prototype and swap with the autoincrement
			new_widget_prototype = $( '#cacap-widget-prototype-' + wtype ).html();
			new_widget_prototype = new_widget_prototype.replace( /newwidgetkey/g, 'newwidget' + new_widget_count );

			self.wid = 'cacap-widget-newwidget' + new_widget_count;

			var nwp = '<li id="' + self.wid + '" class="cacap-widget-' + wtype + '">' + new_widget_prototype + '</li>'; 
			self.$widget_list.append( '<li id="' + self.wid + '" class="cacap-widget-' + wtype + '">' + new_widget_prototype + '</li>' );

			// Update the widget order input value
			self.init_widget_order();
			self.widget_order.push( self.wid );
			self.$widget_order.val( self.widget_order );

			self.$w = $( '#' + self.wid );

			// Add the type class
			self.$w.addClass( 'cacap-widget-' + wtype );

			// If this widget doesn't allow multiple types, disable the
			// button
			if ( self.$new_widget_button.hasClass( 'disable-multiple' ) ) {
				self.$new_widget_button.addClass( 'cacap-has-max' );
			}

			// Activate editable fields
			self.$w.find( 'article.editable-content' ).css( 'min-height', '2em' ).attr( 'contenteditable', 'true' );

			// Add section IDs
			self.$w.find( '.cacap-widget-title' ).attr( 'id', self.wid + '-title' );
			self.$w.find( '.cacap-widget-content' ).attr( 'id', self.wid + '-content' );

			// If it's a positions field, set it up
			if ( 'positions' == wtype ) {
				self.clone_add_new_position_fields( self.$w );
			}

			self.init_editable_widgets();

			// Offset for the header
			$.scrollTo( ( self.$w.offset().top - 230 ) + 'px', 500 );
		},

		/* Warn the user of invalid data by making the input box red for a moment. 
		 */ 
		warn_invalid_data: function($currently_editing, message) { 

			// Why do we have to scroll back to this spot? 
			self.currently_editing_position = $currently_editing.offset();
			$.scrollTo( (self.currently_editing_position.top - 230) + 'px', 500 );

			$currently_editing = $currently_editing.parent(); 
			$currently_editing.addClass( 'warn' );
			setTimeout( function() {
				$currently_editing.removeClass( 'warn' );
			}, 800 );

			message = message || ""; //default message is blank
			$currently_editing.find('.cacap-error').html( message ); 
		},

		/* Undo the warning message from warn_invalid_data()
		 */
		unwarn: function(obj) {
			obj.find('.cacap-error').html('');
		},
	
		bind_body_clicks: function() {

			$( 'body' ).on( 'mousedown', function( e ) {
				self.$jcw_target = $( e.target );

				self.$jcw_half = self.$jcw_target.closest( '.cacap-click-to-edit' );

				if ( self.$jcw_half.length ) {
					jcw_id = self.$jcw_half.attr( 'id' );
				} else {
					jcw_id = '';
				}

				// handle clicking "Change" on visibility toggle links
				// for new widgets. '0' below is the ID for new widgets. 
				if ( ( self.$jcw_target.hasClass('visibility-toggle-link') ) && ('field-visibility-settings-toggle-0' == self.$jcw_target.parent().attr('id') ) ) { 

					// we have to rebind these, because otherwise the default behavior 
					// (following the link to "#") will happen. 
					self.$jcw_target.on('click', function(e) { 
						// normal behavior for this button stolen from bp-default/_inc/global.js
						var toggle_div = self.$jcw_target.parent();

						$(toggle_div).fadeOut( 600, function(){
							$(toggle_div).siblings('.field-visibility-settings').slideDown(400);
						});

						return false;
					}); 
				} 	      

				// handle clicking "Close" on visibility toggle links
				// for new widgets. 
				if ( ( self.$jcw_target.hasClass('field-visibility-settings-close') ) ) { 
					self.$jcw_target.on('click', function(e) { 
						var settings_div = self.$jcw_target.parent();

						$(settings_div).slideUp( 400, function(){
							$(settings_div).siblings('.field-visibility-settings-toggle').fadeIn(800);
						});
						return false;
					}); 
				} 

				// now handle visibility radio buttons for new widgets. 
				// redundant, I know. 
				$('.field-visibility-settings input[type=radio]').click(function(){ 
					$(this).parents('.field-visibility-settings').siblings('.field-visibility-settings-toggle').children('.current-visibility-level').html($(this).parent().find('span').text()); 
				}); 

				if ( self.currently_editing.length && jcw_id !== self.currently_editing && ! self.click_target_is_whitelisted_from_bounce( e.target ) ) {
					var $currently_editing = $( '#' + self.currently_editing );

					// Scroll to the bottomish of the element
					// Offset for the header
					self.currently_editing_position = $currently_editing.offset();
					$.scrollTo( (self.currently_editing_position.top + $currently_editing.height() - 230) + 'px', 500 );
				}

				if ( self.currently_editing.length && jcw_id !== self.currently_editing && ! self.$jcw_target.closest( '.ui-autocomplete' ).length && ! self.$jcw_target.closest( '.hallolink-dialog' ).length ) {
					self.process_clickaway(e); 
				} else {
					self.unwarn(self.$jcw_half); 
				}

				// This is not a widget click, so we can bail
				if ( ! jcw_id.length ) {
					return;
				}

				if ( ! self.currently_editing.length && jcw_id.length ) {
					self.mark_currently_editing( jcw_id );
				}

				// If the widget section is not marked 'editable', nothing to do
				if ( ! self.$jcw_half.hasClass( 'cacap-widget-section-editable' ) ) {
					return;
				}

				var jcw_target_is_button = self.$jcw_target.hasClass( 'button' );
				var ok_or_cancel = '';
				if ( jcw_target_is_button ) {
					ok_or_cancel = self.$jcw_target.hasClass( 'cacap-ok' ) ? 'ok' : 'cancel';
				}

				self.$w = self.$jcw_half.closest( 'ul#cacap-widget-list li' );
				self.wid = self.$w.attr( 'id' );

				wtype = self.get_widget_type_from_class( self.$w.attr( 'class' ) );

				switch ( wtype ) {

					case 'positions' :
						if ( jcw_target_is_button ) {
							self.process_okcancel_positions( ok_or_cancel );
						} else {
							self.toggle_editable_positions( ok_or_cancel );
						}

						break;

					case 'rss' :
					case 'twitter' :
						if ( jcw_target_is_button ) {
							self.process_okcancel_rss( ok_or_cancel );
						} else {
							self.toggle_editable_rss( ok_or_cancel );
						}
						break;

					default :
						if ( jcw_target_is_button ) {
							self.process_okcancel( ok_or_cancel );
						} else if ( self.$jcw_target.closest( 'article' ).hasClass( 'editable-content' ) ) {
							self.toggle_editable();
						}

						break;
				}
			} );
		},

		bind_widget_clicks_delete: function() {
			self.$widget_list.on( 'click', '.cacap-widget-remove', function() {
				self.$w = $( this ).closest( '#cacap-widget-list li' );
				self.delete_widget();
				return false;
			} );
		},

		/**
		 * Is the just-clicked element whitelisted from the illegal click protection?
		 *
		 * When editing a field, clicking outside of the edit field will turn the field red and bounce you
		 * back to the field, unless you've clicked OK or Cancel, or unless your click target meets one of the
		 * criteria laid out in this method. Override this to add further conditions.
		 *
		 * @todo Make this more easily extensible.
		 */
		click_target_is_whitelisted_from_bounce: function( target ) {
			self.$jcw_target = $( target );

			return self.$jcw_target.closest( '.ui-autocomplete' ).length || self.$jcw_target.closest( '.hallolink-dialog' ).length;
		},

		bind_submit_clicks: function() {
			$( '#cacap-edit-form' ).submit( function( e ) {
				if ( self.currently_editing.length ) {
					e.preventDefault();
				}
			} );
		},

		bind_enterkey: function() { 
			$(document).keypress(function(e) { 
				if(e.which == 13) { // enter has been pressed
					var this_thing = e.currentTarget.activeElement; 
					if ( this_thing.classList.contains('url') || this_thing.classList.contains('twitter_username') ) { 

						e.preventDefault(); 
						e.currentTarget.activeElement.blur(); 
						self.process_clickaway(); 
					} 
				} 
			}); 
		},

		/**
		 * Mark a widget as "currently editing"
		 */
		mark_currently_editing: function( jcw_id ) {
			self.currently_editing = jcw_id;

			// Remove other contentEditables
			self.$widget_list.find('.cacap-click-to-edit').each( function() {
				if ( self.currently_editing === this.id ) {
					$(this).addClass('editing');
					$( this ).find( 'article.editable-content' ).attr( 'contenteditable', true );
				} else {
					$( this ).find( 'article.editable-content' ).attr( 'contenteditable', false );
				}
			} );

			// Mark that editing is in process (for widget styling)
			self.$widget_list.addClass( 'currently-editing' );
		},

		/**
		 * Unmark as "currently editing"
		 */
		unmark_currently_editing: function() {
			self.currently_editing = '';
			self.$widget_list.find( 'article.editable-content' ).attr( 'contenteditable', true );
			self.$widget_list.removeClass( 'currently-editing' );
		},

		/**
		 * Delete just-clicked widget
		 */
		delete_widget: function() {
			self.init_widget_order();
			self.wid = self.$w.attr( 'id' );

			// Remove the widget from the widget order
			self.widget_order.splice( $.inArray( self.wid, self.widget_order ), 1 );
			self.$widget_order.val( self.widget_order );

			// If the new widget button for this type is disabled
			// due to a max number of widgets, remove that restriction
			wtype = self.get_widget_type_from_class( self.$w.attr( 'class' ) );
			self.$new_widget_button = $( '#cacap-new-widget-' + wtype );
			if ( self.$new_widget_button.hasClass( 'disable-multiple' ) ) {
				self.$new_widget_button.removeClass( 'cacap-has-max' );
			}

			// Remove the widget
			self.$w.remove();
		},

		/**
		 * Init the widget order
		 */
		init_widget_order: function() {
			self.widget_order = self.$widget_order.val().split( ',' );
		},

		/**
		 * Set up autocomplete for Positions widget
		 */
		positions_autocomplete_setup: function( $widget ) {
			if ( $widget ) {
				var autocomplete_ep = ajaxurl + '?action=cacap_position_suggest';
				$widget.find('.cacap-position-field-autocomplete').each( function() {
					$(this).autocomplete({
						source: autocomplete_ep + '&field=' + $(this).data( 'field-type' ),
						minLength: 2
					});
				});

				// @todo Title?
			}
		},

		/**
		 * Set up sortable for Positions widget
		 */
		positions_sortable_setup: function( $widget ) {
			if ( $widget ) {
				$widget.find( '.cacap-positions-positions' ).sortable({
					placeholder: 'ui-state-highlight',
					containment: $widget,
					axis: 'y',
					handle: '.cacap-position-drag-handle',
					stop: function( event, ui ) {
						self.reindex_positions_fields();
					}
				});
			}
		},

		/**
		 * Re-index positions fields
		 */
		reindex_positions_fields: function() {
			var c = 1;
			self.$positions_widget.find( '.cacap-position' ).each( function() {
				if ( 'cacap-position-add-new' !== this.id ) {
					// Swap id for table and delete button
					// Not really necessary, but just for consistency
					this.id = 'cacap-position-' + c;
					$( this ).siblings( '.cacap-delete-position').attr( 'id', 'cacap-delete-position-' + c );

					// Swap out names - this is the part that's required
					// to make the form work
					$( this ).find( 'input,select' ).each( function() {
						//console.log( $( this ).attr( 'name' ).replace( /(\[content\]\[)([0-9]+)\]/, '$1' + c + ']' ) );
						$( this ).attr( 'name', $( this ).attr( 'name' ).replace( /(\[content\]\[)([0-9]+)\]/, '$1' + c + ']' ) );
					} );

					c++;
				}

			} );
		},

		/**
		 * Update character count for the passed field
		 */
		update_character_count_for_field: function( $field ) {
			field_char_count = $field.val().length;
			$about_you_gloss.find( 'span.cacap-char-count' ).html( field_char_count );

			if ( field_char_count > self.about_you_max_length ) {
				class_to_add = 'cacap-length-red';
			} else if ( field_char_count > self.about_you_max_length - 40 ) {
				class_to_add = 'cacap-length-yellow';
			} else {
				class_to_add = 'cacap-length-green';
			}

			$about_you_gloss.removeClass( 'cacap-length-red cacap-length-yellow cacap-length-green' );
			$about_you_gloss.addClass( class_to_add );
		},

		update_visibility_fields: function() { 
			/* jQuery hack to make visibility label change
			 * when the user changes it */ 

			$('.field-visibility-settings input[type=radio]').click(function(){ 
				$(this).parents('.field-visibility-settings').siblings('.field-visibility-settings-toggle').children('.current-visibility-level').html($(this).parent().find('span').text()); 
			}); 
		},


		// Init methods to run after document is ready
		init: function() {
			self = this;
			self.init_bodyclass();
			self.init_stickyheader();
			self.init_bubble(); 

			self.$widget_list = $( '#cacap-widget-list' );

			if ( $( 'body' ).hasClass( 'profile-edit' ) ) {
				self.init_sortable_widgets();
				self.init_editable_widgets();
				self.init_positions_widgets();
				self.init_new_widget_buttons();
				self.init_exit_confirm();
				self.init_widget_specialkeys();
				self.init_about_you_character_count();
				self.bind_body_clicks();
				self.bind_widget_clicks_delete();
				self.bind_submit_clicks();
				self.bind_enterkey(); 
				self.update_visibility_fields(); 
			}

			// Don't show blinking cursor on readonly input fields. 
			$('input[readonly]').focus(function(){
				this.blur();
			}); 
		}
	}

	wp.cacap = cacap;

	$( document ).ready( function() {
		wp.cacap.init();
	} );
}(jQuery));
