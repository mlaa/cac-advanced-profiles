<?php

class CACAP_Widget_Blog extends CACAP_Widget {
	public function __construct() {
		parent::init( array(
			'name' => __( 'Blog', 'cacap' ),
			'slug' => 'blog',
		) );
	}
}
