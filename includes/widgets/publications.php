<?php

class CACAP_Widget_Publications extends CACAP_Widget {
	public function __construct() {
		parent::init( array(
			'name' => __( 'Publications', 'cacap' ),
			'slug' => 'publications',
			'placeholder' => 'A list of your publications.', 
		) );
	}
}
