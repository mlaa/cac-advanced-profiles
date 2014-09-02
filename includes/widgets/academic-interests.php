<?php

class CACAP_Widget_Academic_Interests extends CACAP_Widget {
	public function __construct() {
		parent::init( array(
			'name' => __( 'Academic Interests', 'cacap' ),
			'slug' => 'academic-interests',
			'content_type' => 'interests',
		) );
	}

	// linkify comma-separated interests
	public function display_content_markup( $value ) {
		return xprofile_filter_link_profile_data( $value ); 
	} 
}
