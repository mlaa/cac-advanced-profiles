<?php

class CACAP_Widget_Twitter_Username extends CACAP_Widget {
	public function __construct() {
		parent::init( array(
			'name' => __( 'Twitter User Name', 'cacap' ),
			'slug' => 'twitter-username',
			'content_type' => 'twitter_username',
		) );
	}

	public function display_content_markup( $value ) {
		return '<a href="http://www.twitter.com/' . $value . '">' . $value . '</a>'; 
	} 
}
