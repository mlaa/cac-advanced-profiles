<?php

class CACAP_Widget_Blog extends CACAP_Widget {
	public function __construct() {
		parent::init( array(
			'name' => __( 'Blog', 'cacap' ),
			'slug' => 'blog',
			'content_type' => 'url',
		) );
	}

	public function display_content_markup( $value ) {
		return "<a href=\"$value\">$value</a>"; 
	} 
}
