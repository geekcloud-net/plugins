<div id="main" role="main">

	<?php appthemes_before_loop(); ?>

	<?php if ( have_posts() ): ?>
		<?php while ( have_posts() ) : the_post(); ?>

			<div id="ticket-manager-<?php the_ID(); ?>" <?php post_class( 'tabber' ); ?>>

				<?php appthemes_before_post(); ?>

				<?php get_template_part( 'templates/navigation', 'single' ); ?>

				<div class="panel">
					<ol class="ticket-list">
						<li id="single-ticket" class="ticket">
							<a class="ticket-meta-toggle" href="#">&nbsp;</a>

							<?php qc_status_label(); ?>

							<h1 class="ticket-title">
								<?php appthemes_before_post_title(); ?>
								<a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', APP_TD ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a>
								<?php appthemes_after_post_title(); ?>
							</h1>

							<p class="ticket-author">
								<?php printf( __( 'by <strong>%1$s</strong> on <em>%2$s</em>', APP_TD ), get_the_author(), get_the_date() ); ?>

								<?php if ( current_user_can( 'delete_post', $post->ID ) ) : ?>
									&mdash;
									<a href="<?php echo qc_get_delete_ticket_link( $post->ID ); ?>"><?php _e( 'Delete Ticket', APP_TD ); ?></a>
								<?php endif; ?>
							</p>

							<ul class="ticket-meta single">
								<?php get_template_part( 'templates/ticket-meta', 'single' ); ?>
							</ul>

							<div class="entry single-ticket">
								<?php appthemes_before_post_content(); ?>
									<?php the_content(); ?>
								<?php appthemes_after_post_content(); ?>

								<?php wp_link_pages(); ?>
							</div>

							<ol class="update-list">
<?php
							$attachments = get_posts( array(
								'post_type' => 'attachment',
								'numberposts' => -1,
								'no_found_rows' => true,
								'post_status' => null,
								'post_parent' => $post->ID
							) );

							if ( $attachments ) :
?>
								<li><strong class="title"><?php _e( 'Attachments:', APP_TD ); ?></strong>
									<ul>
									<?php foreach ( $attachments as $post ) : setup_postdata( $post ); ?>
										<li id="attachment-<?php echo $post->ID; ?>"><?php echo qc_get_attachment_link( $post->ID ); ?> <?php printf( __( 'by %1$s on %2$s', APP_TD ), get_the_author(), get_the_date() ); ?></li>
									<?php endforeach; ?>
									</ul>
								</li>

								<?php endif; wp_reset_query(); ?>
							</ol>
						</li>

						<?php comments_template( '/comments-ticket.php' ); ?>

					</ol>
				</div><!-- .panel -->

				<?php appthemes_after_post(); ?>

			</div><!-- #post -->

		<?php endwhile; ?>

		<?php appthemes_after_endwhile(); ?>

	<?php else: ?>

		<?php appthemes_loop_else(); ?>

	<?php endif; ?>

	<?php appthemes_after_loop(); ?>

</div><!-- End #main -->
