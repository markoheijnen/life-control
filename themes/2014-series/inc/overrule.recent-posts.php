<?php

new Rockstars_Widget_Recent_Post();

class Rockstars_Widget_Recent_Post {
	private $post_type;

	function __construct() {
		add_action( 'in_widget_form', array( &$this, 'extend_recent_posts_form' ), 10, 3 );
		add_filter( 'widget_update_callback', array( &$this, 'extend_recent_posts_update' ), 10, 4 );
		add_filter( 'widget_title', array( &$this, 'extend_recent_posts_init_query_filter' ), 10, 3 );
		add_filter( 'widget_posts_args', array( &$this, 'extend_recent_posts_query' ) );
	}

	function extend_recent_posts_form( $widget, $return, $instance ) {	
		if( ! is_a( $widget, 'WP_Widget_Recent_Posts' ) ) 
			return;

		echo '<select id="' . $widget->get_field_id('posttype') . '" name="' . $widget->get_field_name('posttype') . '" >';

		$post_types = get_post_types( array( 'public' => true ) ); 
		foreach ( $post_types as $post_type ) {
			if( $post_type == $instance['post_type'] ) {
				echo '<option selected="selected">' . $post_type . '</option>';
			}
			else {
				echo '<option>' . $post_type . '</option>';
			}
		}

		echo '</select>';
	}

	function extend_recent_posts_update( $instance, $new_instance, $old_instance, $widget ) {
		if( ! is_a( $widget, 'WP_Widget_Recent_Posts' ) ) 
			return $instance;

		$post_type = strip_tags( $new_instance['posttype'] );

		if( post_type_exists( $post_type ) ) {
			$instance['post_type'] = $post_type;
		}

		return $instance;
	}

	// $title, $instance, $id_base
	function extend_recent_posts_init_query_filter( $title ) {
		if( func_num_args() >= 3 ) {
			$instance = func_get_arg( 1 );
			$id_base  = func_get_arg( 2 );

			if( isset( $instance['post_type'] ) && 'recent-posts' == $id_base ) {
				$this->post_type = $instance['post_type'];
			}
		}

		return $title;
	}

	function extend_recent_posts_query( $vars ) {
		if( ! empty( $this->post_type ) ) {
			$vars['post_type'] = $this->post_type;
			$this->post_type = '';
		}

		return $vars;
	}
}