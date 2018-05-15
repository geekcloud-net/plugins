<div id="main" role="main">

	<?php appthemes_before_blog_loop(); ?>

	<?php if ( have_posts() ): ?>
		<?php while ( have_posts() ) : the_post(); ?>

			<div id="ticket-manager-<?php the_ID(); ?>" <?php post_class( 'tabber' ); ?>>

				<?php appthemes_before_blog_post(); ?>

				<?php get_template_part( 'templates/navigation', 'post' ); ?>

				<div class="panel">
					<ol class="ticket-list">
						<li id="single-ticket" class="ticket">

							<h1 class="ticket-title">
								<?php appthemes_before_blog_post_title(); ?>
								<a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', APP_TD ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a>
								<?php appthemes_after_blog_post_title(); ?>
							</h1>

							<div class="entry single-ticket">
								<?php appthemes_before_blog_post_content(); ?>
									<?php the_content(); ?>
								<?php appthemes_after_blog_post_content(); ?>
							</div>

							<div class="ticket-meta single">
								<?php qc_post_entry_meta(); ?>
								<?php edit_post_link( __( 'Edit', APP_TD ), '<span>|</span> <span class="edit-link">', '</span>' ); ?>
							</div>

						</li>

						<?php comments_template(); ?>

					</ol>
				</div><!-- .panel -->

				<?php appthemes_after_blog_post(); ?>

			</div><!-- #post -->

		<?php endwhile; ?>

		<?php appthemes_after_blog_endwhile(); ?>

	<?php else: ?>

		<?php appthemes_blog_loop_else(); ?>

	<?php endif; ?>

	<?php appthemes_after_blog_loop(); ?>

</div><!-- End #main -->
