<?php
class Widget_Upcoming extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'life_control_upcoming', // Base ID
			'Widget_Upcoming', // Name
			array( 'description' => __( 'Shows latest and upcoming episode', 'my-series' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $args['before_widget'];

		if ( ! empty( $title ) )
			echo $args['before_title'] . $title . $args['after_title'];

		$query_args = array(
			'post_type'      => 'serie',
			'posts_per_page' => -1
		);
		$series = get_posts( $query_args );

		foreach( $series as $serie ) {
			echo '<h2 style="font-size: 12px">' . $serie->post_title . '</h2>';

			$tvrage_id    = get_post_meta( $serie->ID, 'tvrage_id', true );
			$episode_data = $this->load_episodeinfo( $tvrage_id );

			if( $episode_data ) {
				$exploded = explode( 'x', $episode_data['latest']['number'] );
				$query_args = array(
					'post_type'      => 'episode',
					'post_parent'    => $serie->ID,
					'posts_per_page' => 1,
					'meta_query'     => array(
						array(
							'key'   => 'season',
							'value' => (int) $exploded[0]
						),
						array(
							'key'   => 'episode',
							'value' => (int) $exploded[1]
						)
					)
				);
				$latest_episode = get_posts( $query_args );

				echo '<p>';

				if( isset( $latest_episode[0] ) )
					echo '<a href="' . get_permalink( $latest_episode[0]->ID ) . '">';

				echo $episode_data['latest']['title'] . '(' . $episode_data['latest']['number'] . ')<br/>';
				echo $episode_data['latest']['date'];


				if( isset( $latest_episode[0] ) )
					echo '</a>';

				echo '</p>';

				if( isset( $episode_data['next'] ) ) {
					echo '<p>';
					echo $episode_data['next']['title'] . '(' . $episode_data['next']['number'] . ')<br/>';
					echo $episode_data['next']['date'];
					echo '</p>';
				}
				else {
					echo '<p>' . $episode_data['status'] . '</p>';
				}
			}
			else {
				echo '<p>No data</p>';
			}
		}

		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'New title', 'my-series' );
		}
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'my-series' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<?php 
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}


	public function load_episodeinfo( $tvrage_id ) {
		if ( false === ( $return = get_transient( 'myseries_episodeinfo_' . $tvrage_id ) ) ) {
			$url     = 'http://services.tvrage.com/feeds/episodeinfo.php?sid=' . $tvrage_id;
			$request = wp_remote_get( $url );
			$body    = wp_remote_retrieve_body( $request );

			if( $body ) {
				$data   = simplexml_load_string( $body );
				$return = array();

				if( $data ) {
					$return = array(
						'status' => (string) $data->status,
						'latest' => array(
							'number' => (string) $data->latestepisode->number,
							'title'  => (string) $data->latestepisode->title,
							'date'   => (string) $data->latestepisode->airdate,
						)
					);

					if( isset( $data->nextepisode ) ) {
						$timestamp = $data->nextepisode->xpath('airtime[@format="GMT+0 NODST"]');

						$return['next'] = array(
							'number'    => (string) $data->nextepisode->number,
							'title'     => (string) $data->nextepisode->title,
							'date'      => (string) $data->nextepisode->airdate,
							'timestamp' => (int)    $timestamp[0],
						);
					}

					set_transient( 'myseries_episodeinfo_' . $tvrage_id, $return, DAY_IN_SECONDS );
				}
			}
		}

		return $return;
	}

}