<?php

class My_Series_Series {
	private $extra_meta_data;

	public function __construct() {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_filter( 'post_updated_messages', array( $this, 'post_updated_messages' ) );

		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'wp_insert_post_data', array( $this, 'wp_insert_post_data' ), 10, 2 );
		add_action( 'save_post', array( $this, 'save_meta' ), 10, 2 );
	}

	public function register_post_type() {
		$labels = array(
			'name' => _x( 'Series', 'post type general name', 'my-series' ),
			'singular_name' => _x( 'Serie', 'post type singular name', 'my-series' ),
			'add_new' => _x( 'Add new', 'add new serie', 'my-series' ),
			'add_new_item' => __( 'Add new serie', 'my-series' ),
			'edit_item' => __( 'Edit serie', 'my-series' ),
			'new_item' => __( 'New serie', 'my-series' ),
			'all_items' => __( 'All series', 'my-series' ),
			'view_item' => __( 'View serie', 'my-series' ),
			'search_items' => __( 'Search series', 'my-series' ),
			'not_found' => __( 'No series found', 'my-series' ),
			'not_found_in_trash' => __( 'No series found in trash', 'my-series' ), 
			'parent_item_colon' => '',
			'menu_name' => __( 'Series', 'my-series' )
		);

		$args = array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true, 
			'show_in_menu' => true, 
			'query_var' => true,
			'rewrite' => array( 'slug' => 'series' ),
			'has_archive' => true, 
			'hierarchical' => false,
			'supports' => array( 'title', 'editor', 'thumbnail' )
		); 

		register_post_type( 'serie', $args );
	}

	function post_updated_messages( $messages ) {
		global $post, $post_ID;

		$messages['serie'] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => sprintf( __( 'Serie updated. <a href="%s">View serie</a>', 'my-series' ), esc_url( get_permalink( $post_ID ) ) ),
			2 => __( 'Custom field updated.', 'my-series' ),
			3 => __( 'Custom field deleted.', 'my-series' ),
			4 => __( 'Serie updated.', 'my-series' ),
			/* translators: %s: date and time of the revision */
			5 => isset( $_GET['revision'] ) ? sprintf( __('Serie restored to revision from %s', 'my-series' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => sprintf( __( 'Serie published. <a href="%s">View serie</a>', 'my-series' ), esc_url( get_permalink( $post_ID ) ) ),
			7 => __( 'Serie saved.', 'my-series' ),
			8 => sprintf( __( 'Serie submitted. <a target="_blank" href="%s">Preview serie</a>', 'my-series'), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
			9 => sprintf( __( 'Serie scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview serie</a>', 'my-series' ),
				// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ),
			10 => sprintf( __( 'Serie draft updated. <a target="_blank" href="%s">Preview serie</a>', 'my-series' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
		);

		return $messages;
	}


	public function add_meta_boxes() {
		add_meta_box(
			'series_info',
			__( 'My Post Section Title', 'my-series' ),
			array( $this, 'metabox_info' ),
			'serie'
		);
	}

	public function metabox_info( $post ) {
		wp_nonce_field( plugin_basename( __FILE__ ), 'my-series-series-nonce' );

		$imdb_id       = get_post_meta( $post->ID, 'imdb_id', true );
		$tvrage_id     = get_post_meta( $post->ID, 'tvrage_id', true );
		$streamallthis = get_post_meta( $post->ID, 'streamallthis_name', true );

		echo '<p><label for="imdb_id">';
			_e( 'IMDB ID', 'my-series' );
		echo '</label> ';
		echo '<input type="text" id="imdb_id" class="regular-text" name="imdb_id" value="' . esc_attr( $imdb_id ) . '" /></p>';

		echo '<p><label for="tvrage_id">';
			_e( 'TVrage ID', 'my-series' );
		echo '</label> ';
		echo '<input type="text" id="tvrage_id" class="regular-text" name="tvrage_id" value="' . esc_attr( $tvrage_id ) . '" /></p>';

		echo '<p><label for="streamallthis">';
			_e( 'Streamallthis url name', 'my-series' );
		echo '</label> ';
		echo '<input type="text" id="streamallthis" class="regular-text" name="streamallthis" value="' . esc_attr( $streamallthis ) . '" /></p>';
	}

	public function wp_insert_post_data( $data, $postarr ) {
		if( ! $data['post_title'] && isset( $_POST['imdb_id'] ) ) {
			$serie_data = $this->load_serie( $_POST['imdb_id'] );

			if( $serie_data ) {
				$data['post_title'] = $serie_data->Title;
				$data['post_name']  = sanitize_title( $data['post_title'] );

				if( ! $data['post_content'] )
					$data['post_content'] = $serie_data->Plot;

				$this->extra_meta_data = array(
					'released'  => $serie_data->Released,
					'genres'    => explode( ',', $serie_data->Genre ),
					'Genre'     => $serie_data->Genre,
					'thumbnail' => $serie_data->Poster
				);
			}
		}

		return $data;
	}

	public function save_meta( $post_id, $post ) {
		if ( ! isset( $_POST['my-series-series-nonce'] ) || ! wp_verify_nonce( $_POST['my-series-series-nonce'], plugin_basename( __FILE__ ) ) )
			return;

		if ( ! current_user_can( 'edit_post', $post_id ) )
			return;

		if( 'serie' != $post->post_type )
			return;


		$imdb_id       = sanitize_text_field( $_POST['imdb_id'] );
		$tvrage_id     = absint( $_POST['tvrage_id'] );
		$streamallthis = sanitize_text_field( $_POST['streamallthis'] );

		update_post_meta( $post_id, 'imdb_id', $imdb_id );
		update_post_meta( $post_id, 'tvrage_id', $tvrage_id );
		update_post_meta( $post_id, 'streamallthis_name', $streamallthis );

		if( $this->extra_meta_data ) {
			foreach( $this->extra_meta_data as $key => $value ) {
				if( 'thumbnail' ) {
					$attachment_id = $this->load_thumbnail( $post_id, $value, $post->post_title );

					if( $attachment_id )
            			set_post_thumbnail( $post_id, $attachment_id );
				}
				else {
					update_post_meta( $post_id, $key, $value );
				}
			}
		}

		if( $tvrage_id ) {
			$args = array(
				'post_type'      => 'episode',
				'post_parent'    => $post_id,
				'posts_per_page' => 1
			);
			$episodes = get_posts( $args );

			if( ! $episodes ) {
				$episodes = $this->load_episodes( $tvrage_id );

				if( $episodes ) {
					foreach( $episodes as $episode ) {
						$args = array(
							'post_title'    => $post->post_title . ': ' . $episode['title'],
							'post_content'  => '',
							'post_status'   => 'publish',
							'post_parent'   => $post_id,
							'post_type'     => 'episode',
							'post_date'     => $episode['date']
						);
						$episode_id = wp_insert_post( $args );

						if( ! is_wp_error( $episode_id ) ) {
							update_post_meta( $episode_id, 'season', $episode['season'] );
							update_post_meta( $episode_id, 'episode', $episode['episode'] );

							if( $streamallthis ) {
								$code     = sprintf( 's%02de%02d', $episode['season'] , $episode['episode'] );
								$url      = 'http://streamallthis.me/watch/' . $streamallthis . '/' . $code . '.html';
								$response = wp_remote_head( $url );

								if( ! is_wp_error( $response ) && 200 == wp_remote_retrieve_response_code( $response )  )
									update_post_meta( $episode_id, 'streamallthis', $url );
							}
						}
					}
				}
			}
		}
	}



	public function load_serie( $imdb_id ) {
		$url     = 'http://www.omdbapi.com/?plot=full&i=' . urlencode( $imdb_id );
		$request = wp_remote_get( $url );
		$body    = wp_remote_retrieve_body( $request );

		if( $body ) {
			$data = json_decode( $body );

			if( $data ) {
				return $data;
			}
		}

		return false;
	}

	public function load_episodes( $tvrage_id ) {
		$url     = 'http://services.tvrage.com/feeds/episode_list.php?sid=' . $tvrage_id;
		$request = wp_remote_get( $url );
		$body    = wp_remote_retrieve_body( $request );

		if( $body ) {
			$data = simplexml_load_string( $body );

			if( $data ) {
				$episodes = array();

				foreach( $data->Episodelist->Season as $season ) {
					foreach( $season->episode as $episode ) {
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
			if( isset( $matches[0] ) )
				$file_array['name']     = basename( $matches[0] );

			$file_array['tmp_name'] = $tmp;

			// If error storing temporarily, unlink
			if ( is_wp_error( $tmp ) ) {
				@unlink( $file_array['tmp_name'] );
				$file_array['tmp_name'] = '';
			}

			// do the validation and storage stuff
			$id = media_handle_sideload( $file_array, $post_id, $desc );

			if ( ! is_wp_error( $id ) )
				return $id;

			// If error storing permanently, unlink
			@unlink( $file_array['tmp_name'] );
		}

		return false;
	}

}