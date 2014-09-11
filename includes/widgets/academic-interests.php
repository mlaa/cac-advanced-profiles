<?php

class CACAP_Widget_Academic_Interests extends CACAP_Widget {
	public function __construct() {
		parent::init( array(
			'name' => __( 'Academic Interests', 'cacap' ),
			'slug' => 'academic-interests',
			'content_type' => 'interests',
			'placeholder' => 'A comma-separated list of your academic interests, e.g., &quot;critical theory, William Blake, Renaissance literature&quot;.',
		) );
	}

	// linkify comma-separated interests
	public function display_content_markup( $value ) {
		return xprofile_filter_link_profile_data( $value ); 
	} 
}
