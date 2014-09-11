<?php

class CACAP_Widget_Blog extends CACAP_Widget {
	public function __construct() {
		parent::init( array(
			'name' => __( 'Blog', 'cacap' ),
			'slug' => 'blog',
			'content_type' => 'url',
			'placeholder' => 'e.g., &quot;http://my-blog.com&quot;', 
		) );
	}

	public function display_content_markup( $value ) {
		return filter_var( trim( $value ), FILTER_VALIDATE_URL )  ? "<a href=\"$value\">$value</a>" : $value; 
	} 
}
