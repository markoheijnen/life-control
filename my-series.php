<?php
/*
	Plugin Name: My Series
	Plugin URI: 
	Description: 
	Author: Marko Heijnen
	Version: 1.0
	Author URI: http://markoheijnen.com
	Text Domain: my-series
	Domain Path: /language
 */

include 'posttypes/episodes.php';
include 'posttypes/series.php';
include 'inc/watched.php';

class My_Series {
	private $episodes;
	private $series;

	public function __construct() {
		register_activation_hook( __FILE__, array( $this, 'activation' ) );

		add_action( 'widgets_init', array( $this, 'register_widgets' ) );

		$this->series   = new My_Series_Series;
		$this->episodes = new My_Series_Episodes;

		new My_Series_Watched;

		if( is_admin() )
			$this->update();
	}


	public function activation() {
		flush_rewrite_rules();

		$this->episodes->register_post_type();
		$this->series->register_post_type();
	}


	public function register_widgets() {
		include 'widgets/upcoming.php';
		register_widget( 'Widget_Upcoming' );
	}


	public function update() {
		include 'updater.php';

		$updater = new My_Series_Updater;
	}
}

new My_Series();