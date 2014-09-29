<?php

class Life_Control_Updater {


	public function load_serie( $imdb_id ) {
		$url     = 'http://www.omdbapi.com/?plot=full&i=' . urlencode( $imdb_id );
		$request = wp_remote_get( $url );
		$body    = wp_remote_retrieve_body( $request );

		if ( $body ) {
			$data = json_decode( $body );

			if ( $data ) {
				return $data;
			}
		}

		return false;
	}

	public function load_episodes( $tvrage_id ) {
		$url     = 'http://services.tvrage.com/feeds/episode_list.php?sid=' . $tvrage_id;
		$request = wp_remote_get( $url );
		$body    = wp_remote_retrieve_body( $request );

		if ( $body ) {
			$data = simplexml_load_string( $body );

			if ( $data ) {
				$episodes = array();

				foreach ( $data->Episodelist->Season as $season ) {
					foreach ( $season->episode as $episode ) {
						$episodes[] = array(
							'season'    => (int)    $season['no'],
							'episode'   => (int)    $episode->seasonnum,
							'title'     => (string) $episode->title,
							'date'      => (string) $episode->airdate,
							'timestamp' => strtotime( $episode->airdate )
						);
					}
				}

				return $episodes;
			}
				
		}

		return false;
	}


	public function load_thumbnail( $post_id, $url, $desc = null ) {
		if ( ! empty( $url ) ) {
			// Download file to temp location
			$tmp = download_url( $url );

			// Set variables for storage
			// fix file filename for query strings
			preg_match('/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/', $url, $matches);

			if( isset( $matches[0] ) ) {
				$file_array['name']     = basename( $matches[0] );
			}

			$file_array['tmp_name'] = $tmp;

			// If error storing temporarily, unlink
			if ( is_wp_error( $tmp ) ) {
				@unlink( $file_array['tmp_name'] );
				$file_array['tmp_name'] = '';
			}

			// do the validation and storage stuff
			$id = media_handle_sideload( $file_array, $post_id, $desc );

			if ( ! is_wp_error( $id ) ) {
				return $id;
			}

			// If error storing permanently, unlink
			@unlink( $file_array['tmp_name'] );
		}

		return false;
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

		foreach ( $episodes as $episode ) {
			$this->check_streamallthis( $episode );
		}
	}

	public function check_streamallthis( $episode ) {
		$serie = get_post( $episode->post_parent );

		if ( ! $serie ) {
			return;
		}

		$streamallthis = get_post_meta( $serie->ID, 'streamallthis_name', true );
		$season        = get_post_meta( $episode->ID, 'season', true );
		$episode_nr    = get_post_meta( $episode->ID, 'episode', true );

		if ( $streamallthis ) {
			$code     = sprintf( 's%02de%02d', $season, $episode_nr );
			$url      = 'http://streamallthis.me/watch/' . $streamallthis . '/' . $code . '.html';
			$response = wp_remote_head( $url );

			if ( ! is_wp_error( $response ) && 200 == wp_remote_retrieve_response_code( $response )  ) {
				update_post_meta( $episode->ID, 'streamallthis', $url );
			}
		}
	}
}
