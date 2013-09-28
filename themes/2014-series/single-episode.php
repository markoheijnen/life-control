<?php
/**
 * The Template for displaying all single posts.
 *
 * @package WordPress
 * @subpackage Twenty_Fourteen
 */

get_header(); ?>

<div id="primary" class="content-area">
	<div id="content" class="site-content" role="main">
		<?php
			while ( have_posts() ) :
				the_post();
			?>

		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<a href="<?php the_permalink(); ?>" title="<?php echo esc_attr( sprintf( __( 'Permalink to %s', 'twentyfourteen' ), the_title_attribute( 'echo=0' ) ) ); ?>" rel="<?php the_ID(); ?>" class="attachment-featured-thumbnail">

				<?php
				$streamallthis = get_post_meta( get_the_ID(), 'streamallthis', true );

				if( $streamallthis ) {
					/*$serie              = get_post( $post->post_parent );
					$streamallthis_name = get_post_meta( $serie->ID, 'streamallthis_name', true );
					$season             = get_post_meta( get_the_ID(), 'season', true );
					$episode            = get_post_meta( get_the_ID(), 'episode', true );

					$code = sprintf( 's%02de%02d', $season , $episode );
					$url  = 'http://streamallthis.me/watch/' . $streamallthis_name . '/if/v/' . $code . '.html'; */

					echo '<iframe src="' . $streamallthis . '" width="100%" height="360" frameborder="0" seamless></iframe>';
				}
				else {
					the_post_thumbnail( 'featured-thumbnail-large' );
				}
				?>
				
			</a>

			<header class="entry-header">
				<?php if ( in_array( 'category', get_object_taxonomies( get_post_type() ) ) && twentyfourteen_categorized_blog() ) : ?>
				<div class="entry-meta">
					<span class="cat-links"><?php echo get_the_category_list( _x( ', ', 'Used between list items, there is a space after the comma.', 'twentyfourteen' ) ); ?></span>
				</div>
				<?php endif; ?>

				<?php the_title( '<h1 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h1>' ); ?>

				<div class="entry-meta">
					<?php
						if ( 'post' == get_post_type() )
							twentyfourteen_posted_on();

						if ( ! post_password_required() && ( comments_open() || get_comments_number() ) ) :
					?>
					<span class="comments-link"><?php comments_popup_link( __( 'Leave a comment', 'twentyfourteen' ), __( '1 Comment', 'twentyfourteen' ), __( '% Comments', 'twentyfourteen' ) ); ?></span>
					<?php
						endif;

						edit_post_link( __( 'Edit', 'twentyfourteen' ), '<span class="edit-link">', '</span>' );
					?>
				</div><!-- .entry-meta -->
			</header><!-- .entry-header -->

			<?php if ( is_search() ) : ?>
			<div class="entry-summary">
				<?php the_excerpt(); ?>
			</div><!-- .entry-summary -->
			<?php else : ?>
			<div class="entry-content">
				<?php
					the_content( __( 'Continue reading <span class="meta-nav">&rarr;</span>', 'twentyfourteen' ) );
					wp_link_pages( array(
						'before'      => '<div class="page-links"><span class="page-links-title">' . __( 'Pages:', 'twentyfourteen' ) . '</span>',
						'after'       => '</div>',
						'link_before' => '<span>',
						'link_after'  => '</span>',
					) );
				?>
			</div><!-- .entry-content -->
			<?php endif; ?>

			<?php if ( has_tag() ) : ?>
			<footer class="entry-meta">
				<span class="tag-links"><?php echo get_the_tag_list(); ?></span>
			</footer><!-- .entry-meta -->
			<?php endif; ?>
		</article><!-- #post-## -->

		<?php
				twentyfourteen_post_nav();

				// If comments are open or we have at least one comment, load up the comment template.
				if ( comments_open() || get_comments_number() )
					comments_template();
			endwhile;
		?>
	</div><!-- #content -->
</div><!-- #primary -->

<?php
get_sidebar( 'content' );
get_sidebar();
get_footer();
