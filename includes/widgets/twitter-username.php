<?php

class CACAP_Widget_Twitter_Username extends CACAP_Widget {
	public function __construct() {
		parent::init( array(
			'name' => __( '<em>Twitter</em> User Name', 'cacap' ),
			'slug' => 'twitter-username',
			'content_type' => 'twitter_username',
			'placeholder' => 'Your twitter username, without the @ symbol, e.g., &quot;wfaulkner&quot;', 
		) );
	}

	// Italicize Twitter.
	public function display_title_markup( $value ) {
		return wp_kses( $this->name );
	}

	public function display_content_markup( $value ) {
		return '<a href="http://www.twitter.com/' . $value . '">' . $value . '</a>'; 
	} 
}
