<?php

class Life_Control_Episodes {

	public function __construct() {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_filter( 'post_updated_messages', array( $this, 'post_updated_messages' ) );
	}

	public function register_post_type() {
		$labels = array(
			'name'               => _x( 'Episodes', 'post type general name', 'life-control' ),
			'singular_name'      => _x( 'Episode', 'post type singular name', 'life-control' ),
			'add_new'            => _x( 'Add new', 'add new episode', 'life-control' ),
			'add_new_item'       => __( 'Add new episode', 'life-control' ),
			'edit_item'          => __( 'Edit episode', 'life-control' ),
			'new_item'           => __( 'New episode', 'life-control' ),
			'all_items'          => __( 'All episodes', 'life-control' ),
			'view_item'          => __( 'View episode', 'life-control' ),
			'search_items'       => __( 'Search episodes', 'life-control' ),
			'not_found'          => __( 'No episodes found', 'life-control' ),
			'not_found_in_trash' => __( 'No episodes found in trash', 'life-control' ), 
			'parent_item_colon'  => '',
			'menu_name'          => __( 'Episodes', 'life-control' )
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
			1 => sprintf( __( 'Episode updated. <a href="%s">View episode</a>', 'life-control' ), esc_url( get_permalink( $post_ID ) ) ),
			2 => __( 'Custom field updated.', 'life-control' ),
			3 => __( 'Custom field deleted.', 'life-control' ),
			4 => __( 'Episode updated.', 'life-control' ),
			/* translators: %s: date and time of the revision */
			5 => isset( $_GET['revision'] ) ? sprintf( __('Episode restored to revision from %s', 'life-control' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => sprintf( __( 'Episode published. <a href="%s">View episode</a>', 'life-control' ), esc_url( get_permalink( $post_ID ) ) ),
			7 => __( 'Episode saved.', 'life-control' ),
			8 => sprintf( __( 'Episode submitted. <a target="_blank" href="%s">Preview episode</a>', 'life-control'), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
			9 => sprintf( __( 'Episode scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview episode</a>', 'life-control' ),
				// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ),
			10 => sprintf( __( 'Episode draft updated. <a target="_blank" href="%s">Preview episode</a>', 'life-control' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
		);

		return $messages;
	}

}