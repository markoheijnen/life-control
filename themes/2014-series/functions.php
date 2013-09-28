<?php

include 'inc/overrule.recent-posts.php';

class My_Life_Theme {

	public function __construct() {
		add_action( 'after_setup_theme', array( $this, 'after_setup_theme' ), 11 );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );

		add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );

		add_filter( 'post_thumbnail_html', array( $this, 'post_thumbnail_html' ), 10, 5 );
	}

	public function after_setup_theme() {
		add_theme_support( 'post-thumbnails' );
	}

	public function enqueue_styles() {
		wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
	}

	public function pre_get_posts( $query ) {
		if( $query->is_main_query() && $query->is_front_page() ) {
			$query->set( 'post_type', 'episode' );

			if( is_user_logged_in() ) {
				$meta_query = array(
					array(
						'key' => 'user_' . get_current_user_id() . '_watched',
						'compare'  => 'NOT EXISTS'
					)
				);

				$query->set( 'meta_query', $meta_query );
			}
		}
	}


	public function post_thumbnail_html( $html, $post_id, $post_thumbnail_id, $size, $attr ) {
		global $post;

		if( ! $html && $post->ID == $post_id && $post->post_parent ) {
			return get_the_post_thumbnail( $post->post_parent, $size, $attr );
		}

		return $html;
	}

}

new My_Life_Theme;