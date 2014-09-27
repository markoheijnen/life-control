<?php

class Life_Control_Watched {

	public function __construct() {
		add_action( 'wp_ajax_watched_episode', array( $this, 'watched_episode' ) );
	}

	public function watched_episode() {
		if ( ! isset( $_POST['episode_id'] ) ) {
			wp_send_json_error( __( 'No episode ID provided.', 'life-control' ) );
		}

		$episode = get_post( $_POST['episode_id'] );

		if ( ! $episode || 'episode' != $episode->post_type ) {
			wp_send_json_error( __( "Provided ID isn't a episode.", 'life-control' ) );
		}

		$watched = get_post_meta( $episode->ID, 'user_' . get_current_user_id() . '_watched', true  );

		if ( $watched ) {
			wp_send_json_error( sprinf( __( 'You already watched %s', 'life-control' ), $episode->post_title ) );
		}

		update_post_meta( $episode->ID, 'user_' . get_current_user_id() . '_watched', time() );

		wp_send_json_success( __( 'You mark this episode as watched.', 'life-control' ) );
	}

}