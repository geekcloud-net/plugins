<?php
/**
 * The loop that displays posts.
 *
 * The loop displays the posts and the post content. See
 * http://codex.wordpress.org/The_Loop to understand it and
 * http://codex.wordpress.org/Template_Tags to understand
 * the tags used in it.
 *
 * This can be overridden in child themes with loop.php or
 * loop-template.php, where 'template' is the loop context
 * requested by a template. For example, loop-index.php would
 * be used if it exists and we ask for the loop with:
 * <code>get_template_part( 'loop', 'index' );</code>
 *
 * @package Quality_Control
 * @since Quality Control 0.1
 */

if ( have_posts() ) : $i = 0; ?>

	<ol class="ticket-list">

	<?php while ( have_posts() ) : the_post(); $i++; ?>

		<li id="ticket-<?php the_ID(); ?>" <?php post_class( 'ticket ' . ( $i % 2 ? '' : 'alt ' ) ); ?>>

			<?php appthemes_before_blog_post(); ?>

			<p class="ticket-author">
				<?php printf( __( 'by <strong>%1$s</strong> on <em>%2$s</em>', APP_TD ), get_the_author(), get_the_date() ); ?>
			</p>

			<h2 class="ticket-title">
				<?php appthemes_before_blog_post_title(); ?>
				<a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', APP_TD ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a>
				<?php appthemes_after_blog_post_title(); ?>
			</h2>

			<div class="entry single-ticket">
				<?php appthemes_before_blog_post_content(); ?>
				<?php the_content(); ?>
				<?php appthemes_after_blog_post_content(); ?>
			</div>

			<?php appthemes_after_blog_post(); ?>

		</li>

	<?php endwhile; ?>

		<?php appthemes_after_blog_endwhile(); ?>

	</ol>

	<?php if ( qc_show_pagination() ) : ?>

		<div class="tabber-navigation bottom"><?php
			appthemes_pagenavi();
		?></div><!-- #nav-above -->

	<?php endif; ?>

<?php else : ?>

	<ol class="ticket-list">
		<li class="ticket no-results">
			<?php _e( 'No posts found.', APP_TD ); ?>
		</li>
	</ol>

<?php endif; ?>
