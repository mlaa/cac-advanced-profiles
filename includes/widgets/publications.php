<?php

class CACAP_Widget_Publications extends CACAP_Widget {
	public function __construct() {
		parent::init( array(
			'name' => __( 'Publications', 'cacap' ),
			'slug' => 'publications',
			'placeholder' => 'e.g. &quot;Rethinking the Print Object: Geothe and the Book of Everything.&quot; PMLA 121.1 (2006): 124-38. Print.', 
		) );
	}
}
