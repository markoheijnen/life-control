<?php
/*
	Plugin Name: Life Control
	Plugin URI: 
	Description: 
	Author: Marko Heijnen
	Version: 1.0
	Author URI: http://markoheijnen.com
	Text Domain: life-control
	Domain Path: /language
 */

include 'posttypes/episodes.php';
include 'posttypes/series.php';
include 'inc/watched.php';

class Life_Control {
	public static $episodes;
	public static $series;
	public static $updater;

	public function __construct() {
		register_activation_hook( __FILE__, array( $this, 'activation' ) );

		add_action( 'widgets_init', array( $this, 'register_widgets' ) );

		self::$series   = new Life_Control_Series;
		self::$episodes = new Life_Control_Episodes;

		new Life_Control_Watched;

		if ( is_admin() ) {
			$this->update();
		}
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
		include 'inc/updater.php';

		self::$updater = new Life_Control_Updater;
	}

}

$GLOBALS['life_control'] = new Life_Control();