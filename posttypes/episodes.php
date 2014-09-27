<?php

class Life_Control_Episodes {

	public function __construct() {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_filter( 'post_updated_messages', array( $this, 'post_updated_messages' ) );
	}

	public function register_post_type() {
		$labels = array(
			'name'               => _x( 'Episodes', 'post type general name', 'my-series' ),
			'singular_name'      => _x( 'Episode', 'post type singular name', 'my-series' ),
			'add_new'            => _x( 'Add new', 'add new episode', 'my-series' ),
			'add_new_item'       => __( 'Add new episode', 'my-series' ),
			'edit_item'          => __( 'Edit episode', 'my-series' ),
			'new_item'           => __( 'New episode', 'my-series' ),
			'all_items'          => __( 'All episodes', 'my-series' ),
			'view_item'          => __( 'View episode', 'my-series' ),
			'search_items'       => __( 'Search episodes', 'my-series' ),
			'not_found'          => __( 'No episodes found', 'my-series' ),
			'not_found_in_trash' => __( 'No episodes found in trash', 'my-series' ), 
			'parent_item_colon'  => '',
			'menu_name'          => __( 'Episodes', 'my-series' )
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true, 
			'show_in_menu'       => true, 
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'episodes' ),
			'has_archive'        => true, 
			'hierarchical'       => false,
			'supports'           => array( 'title', 'editor', 'thumbnail', 'custom-fields' )
		); 

		register_post_type( 'episode', $args );
	}

	function post_updated_messages( $messages ) {
		global $post, $post_ID;

		$messages['episode'] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => sprintf( __( 'Episode updated. <a href="%s">View episode</a>', 'my-series' ), esc_url( get_permalink( $post_ID ) ) ),
			2 => __( 'Custom field updated.', 'my-series' ),
			3 => __( 'Custom field deleted.', 'my-series' ),
			4 => __( 'Episode updated.', 'my-series' ),
			/* translators: %s: date and time of the revision */
			5 => isset( $_GET['revision'] ) ? sprintf( __('Episode restored to revision from %s', 'my-series' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => sprintf( __( 'Episode published. <a href="%s">View episode</a>', 'my-series' ), esc_url( get_permalink( $post_ID ) ) ),
			7 => __( 'Episode saved.', 'my-series' ),
			8 => sprintf( __( 'Episode submitted. <a target="_blank" href="%s">Preview episode</a>', 'my-series'), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
			9 => sprintf( __( 'Episode scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview episode</a>', 'my-series' ),
				// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ),
			10 => sprintf( __( 'Episode draft updated. <a target="_blank" href="%s">Preview episode</a>', 'my-series' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
		);

		return $messages;
	}

}