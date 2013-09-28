<?php

class My_Series_Updater {

	public function __construct() {
		//$this->check_empty_streamallthis();
	}

	public function check_empty_streamallthis() {
		$args = array(
			'posts_per_page' => -1,
			'post_type'      => 'episode',
			'meta_query'     => array(
				array(
					'key' => 'streamallthis',
					'compare'  => 'NOT EXISTS'
				)
			)
		);
		$episodes = get_posts( $args );

		foreach( $episodes as $episode ) {
			$this->check_streamallthis( $episode );
		}
	}

	public function check_streamallthis( $episode ) {
		$serie = get_post( $episode->post_parent );

		if( ! $serie )
			return;

		$streamallthis = get_post_meta( $serie->ID, 'streamallthis_name', true );
		$season        = get_post_meta( $episode->ID, 'season', true );
		$episode_nr    = get_post_meta( $episode->ID, 'episode', true );

		if( $streamallthis ) {
			$code     = sprintf( 's%02de%02d', $season, $episode_nr );
			$url      = 'http://streamallthis.me/watch/' . $streamallthis . '/' . $code . '.html';
			$response = wp_remote_head( $url );

			if( ! is_wp_error( $response ) && 200 == wp_remote_retrieve_response_code( $response )  )
				update_post_meta( $episode->ID, 'streamallthis', $url );
		}
	}
}
