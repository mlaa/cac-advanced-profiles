<?php

function cacap_includes_dir() {
	$includes_dir = '';

	if ( isset( buddypress()->cacap->includes_dir ) ) {
		$includes_dir = buddypress()->cacap->includes_dir;
	}

	return $includes_dir;
}

function cacap_assets_url() {
	return CACAP_PLUGIN_URL . '/assets/';
}

function cacap_user_widget_instances( $args = array() ) {
	// @todo abstract
	$user_id = bp_displayed_user_id();

	$user = buddypress()->cacap->get_user( $user_id );
	return $user->get_widget_instances( $args );
}

function cacap_widget_types( $args = array() ) {
	$r = wp_parse_args( $args, array(
		'context' => 'body',
	) );

	// hardcoding for now
	$types = array(
		'text'               => 'CACAP_Widget_Text',
		'academic-interests' => 'CACAP_Widget_Academic_Interests',
		'education'          => 'CACAP_Widget_Education',
		'positions'          => 'CACAP_Widget_Positions',
		'publications'       => 'CACAP_Widget_Publications',
		//'rss'                => 'CACAP_Widget_RSS',
		'college'            => 'CACAP_Widget_College',
		'titlewidget'        => 'CACAP_Widget_Title',
		//'twitter'            => 'CACAP_Widget_Twitter',
		'blog'               => 'CACAP_Widget_Blog',
		'twitter-username'   => 'CACAP_Widget_Twitter_Username', 
	);

	$types = apply_filters( 'cacap_widget_types', $types, $r );

	$widgets = array();
	foreach ( $types as $type => $class ) {
		if ( ! class_exists( $class ) ) {
			continue;
		}

		$widgets[ $type ] = new $class;
	}

	// Filter for 'context'
	foreach ( $widgets as $widget_key => $widget ) {
		if ( $r['context'] !== $widget->context ) {
			unset( $widgets[ $widget_key ] );
		}
	}

	return $widgets;
}

function cacap_html_gen() {
	static $wpsdl;

	if ( empty( $wpsdl ) ) {
		require_once trailingslashit( CACAP_PLUGIN_DIR ) . 'lib/wp-sdl/wp-sdl.php';
		$wpsdl = WP_SDL::support( '1.0' );
	}

	return $wpsdl->html();
}

function cacap_widget_order() {
	$wis = cacap_user_widget_instances();
	$ids = array();
	foreach ( $wis as $wi ) {
		$ids[] = 'cacap-widget-' . $wi->css_id;
	}
	return esc_attr( implode( ',', $ids ) );
}

function cacap_widget_type_is_disabled_for_user( $widget_type ) {
	$disabled = false;

	$wis = cacap_user_widget_instances();
	foreach ( $wis as $wi ) {
		if ( $widget_type->slug === $wi->widget_type->slug && ! $widget_type->allow_multiple ) {
			$disabled = true;
			break;
		}
	}

	return $disabled;
}

function cacap_field_is_visible_for_user( $field_id = 0, $displayed_user_id = 0, $current_user_id = 0 ) {
	if ( ! is_numeric( $field_id ) ) {
		$field_id = xprofile_get_field_id_from_name( $field_id );
	}

	if ( ! $field_id ) {
		return true;
	}

	$hidden_fields_for_user = bp_xprofile_get_hidden_fields_for_user( $displayed_user_id, $current_user_id );

	return ! in_array( $field_id, $hidden_fields_for_user );
}

/**
 * Determines whether a user can view a widget. 
 * Accepts visibility from the widget instance, 
 * i.e. $widget_instance->visibility
 *
 * @param string visibility
 */ 
function cacap_user_can_view_widget( $visibility ) {   
	$current_user = bp_loggedin_user_id();   
	$displayed_user = bp_displayed_user_id();  
	$is_friend = friends_check_friendship( $current_user, $displayed_user ); 
	
	if ( $current_user ) { 
		// User is logged in. 
		if ( $current_user == $displayed_user ) { 
			// User is looking at own profile. 
			// Everything's cool. 
			return true; 
		} else { 
			// User is looking at someone else's profile. 
			// Friend or foe? 
			if ( $is_friend ) { 
				return ( in_array( $visibility, array( 'public', 'loggedin', 'friends' ) ) ) ? true : false; 
			} else { 
				return ( in_array( $visibility, array( 'public', 'loggedin' ) ) )  ? true : false; 
			} 
		} 

	} else { 
		// User isn't logged in, only show public widgets
		return ( 'public' == $visibility ) ? true : false; 
	} 
} 

function cacap_sanitize_content( $content ) {
	return wp_kses( $content, array(
		'a' => array(
			'href' => array(),
			'rel' => array(),
		),
		'b' => array(),
		'br' => array(),
		'div' => array(
			'align' => array(),
		),
		'h1' => array(),
		'h2' => array(),
		'h3' => array(),
		'i' => array(),
		'li' => array(),
		'p' => array(),
		'ol' => array(),
		'ul' => array(),
		'strong' => array(),
		'em' => array(),
	) );
}

function cacap_is_commons_profile() {
	$retval = false;

	if ( bp_is_user() ) {
		if ( ! empty( $_GET['commons-profile'] ) && 1 == $_GET['commons-profile'] ) {
			$retval = true;
		}

		if ( ! bp_is_profile_component() ) {
			$retval = true;
		}
	}

	return apply_filters( 'cacap_is_commons_profile', $retval );

	return bp_is_user() && ( empty( $_GET['commons-profile'] ) || 1 != $_GET['commons-profile'] || ! bp_is_profile_component()) ;
}

/**
 * URL for "public portfolio"
 */
function cacap_get_public_portfolio_url( $user_id ) {
	$url = trailingslashit( bp_core_get_user_domain( $user_id ) . buddypress()->profile->slug );
	return apply_filters( 'cacap_get_public_portfolio_url', $url, $user_id );
}

/**
 * URL for "commons portfolio"
 */
function cacap_get_commons_profile_url( $user_id ) {
	$url = trailingslashit( bp_core_get_user_domain( $user_id ) . buddypress()->profile->slug );
	$url = add_query_arg( 'commons-profile', '1', $url );
	return apply_filters( 'cacap_get_commons_profile_url', $url, $user_id );
}

/**
 * Get the header fields, as stored in the DB.
 */
function cacap_get_header_fields( $type = 'public' ) {
	$fields = bp_get_option( 'cacap_header_fields' );

	if ( 'edit' === $type ) {
		$edit_fields = array(
			'left' => array(),
			'right' => array(),
		);

		$edit_order = bp_get_option( 'cacap_header_fields_edit' );

		if ( empty( $edit_order ) ) {
			$edit_order = array(
				'left' => array(),
				'right' => array(),
			);
		}

		// Make sure that they match (all edit fields are in fact in
		// the saved fields, and all saved fields are in the edit order
		$flat_saved = array_merge(
			array( $fields['brief_descriptor'] ),
			array( $fields['about_you'] ),
			$fields['vitals']
		);

		// We always have to include field 1, the Full Name field
		if ( ! in_array( '1', $flat_saved ) ) {
			$flat_saved[] = 1;
		}

		$flat_order = array_merge(
			$edit_order['left'],
			$edit_order['right']
		);

		// Any fields not explicitly saved in the order must be added
		// to the end of one or other column
		$missing_from_order = array_diff( $flat_saved, $flat_order );
		foreach ( $missing_from_order as $mfo ) {
			if ( count( $edit_order['left'] ) <= count( $edit_order['right'] ) ) {
				$side = 'left';
			} else {
				$side = 'right';
			}

			$edit_order[ $side ][] = $mfo;
		}

		// Remove items that are in the edit order but not in the saved
		// field for some reason (shouldn't happen)
		$missing_from_saved = array_diff( $flat_order, $flat_saved );
		foreach ( $missing_from_saved as $mfs ) {
			foreach ( array( 'left', 'right' ) as $side ) {
				$mfs_key = array_search( $mfs, $edit_order[ $side ] );
				if ( false !== $mfs_key ) {
					unset( $edit_order[ $side ][ $mfs_key ] );
					$edit_order[ $side ] = array_values( $edit_order[ $side ] );
				}
			}
		}

		$fields = $edit_order;
	}

	return $fields;
}

/**
 * Get the profile field ID corresponding to the Brief Descriptor area.
 */
function cacap_get_brief_descriptor_field() {
	$fields = cacap_get_header_fields();

	$bd_field = isset( $fields['brief_descriptor'] ) ? intval( $fields['brief_descriptor'] ) : 0;
	return $bd_field;
}

/**
 * Get the profile field ID corresponding to the About You area.
 */
function cacap_get_about_you_field() {
	$fields = cacap_get_header_fields();

	$ay_field = isset( $fields['about_you'] ) ? intval( $fields['about_you'] ) : 0;
	return $ay_field;
}

function mla_check_member_database_for_updates() { 
	/*
	 *_log( 'Displayed user is: ' ); 
	 *_log( bp_displayed_user_id() ); 
	 *_log( 'user meta: ' ); 
	 *_log( get_user_meta( bp_displayed_user_id() ) ); 
	 */
	$max_updated_interval = 3600; //update every hour
	$displayed_user_id = bp_displayed_user_id(); 
	$last_updated = get_user_meta( $displayed_user_id, 'last_updated');   
	$last_updated = (integer) $last_updated[0];  //don't know why this is getting stored as an array
	//_log( 'last_updated:' ); 
	//_log( $last_updated ); 
	if ( ! $last_updated ) { 
		$too_old = true; 
		_log( 'No last_updated time. Updating.' ); 
	} else { 
		$too_old = ( ( time() - $last_updated ) > $max_updated_interval ) ? true : false ; 
	} 
	//$too_old = true; // disable the check, forcing to update for debug reasons
	$too_old = false; // disable updating for now if ( $too_old ) { 
		$mla_member = new MLAMember(); 
		$mla_member->user_id = $displayed_user_id; 
		if ( $mla_member->sync() ) { 
			_log( 'Success! Member data synced.' ); 
			update_user_meta( $displayed_user_id, 'last_updated', time() ); // it has just been updated. Update the updated time.  
		} else { 
			_log( 'Something went wrong while trying to update member info from the member database.' ); 
		} 
	} else { 
		// user meta has been updated from the member
		// database recently. Nothing to see here. 
		_log( 'User meta has been updated recently. Nothing to see here.' ); 
	} 
	//_log( 'user meta: ' ); 
	//_log( get_user_meta( bp_displayed_user_id() ) ); 
	//_log( 'xprofile fullname:' ); 
	//_log( bp_xprofile_fullname_field_name() ); 
	//_log( 'xprofile field data:' ); 
	//_log( xprofile_get_field_data( bp_xprofile_fullname_field_id(), $displayed_user_id ) ); 
} 
add_action( 'cacap_before_content', 'mla_check_member_database_for_updates' );  
