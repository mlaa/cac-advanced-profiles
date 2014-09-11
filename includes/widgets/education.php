<?php

class CACAP_Widget_Education extends CACAP_Widget {
	public function __construct() {
		parent::init( array(
			'name' => __( 'Education', 'cacap' ),
			'slug' => 'education',
			'placeholder' => 'e.g., &quot;MA, English Literature, College of Yoknapatawpha, 1995&quot;', 
		) );
	}
}
