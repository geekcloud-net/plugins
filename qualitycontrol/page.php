<div id="main" role="main">

	<?php appthemes_before_page_loop(); ?>

	<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

		<div id="ticket-manager-<?php the_ID(); ?>" <?php post_class( 'tabber' ); ?>>

			<?php appthemes_before_page(); ?>

			<?php get_template_part( 'templates/navigation', 'page' ); ?>

			<div class="panel">

				<div class="entry inner">

					<?php appthemes_before_page_title(); ?>

						<h1><?php the_title(); ?></h1>

					<?php appthemes_after_page_title(); ?>


					<?php appthemes_before_page_content(); ?>

						<?php the_content(); ?>

					<?php appthemes_after_page_content(); ?>

				</div>

			</div>

			<?php appthemes_after_page(); ?>

		</div><!-- #post -->

	<?php endwhile; ?>

		<?php appthemes_after_page_endwhile(); ?>

	<?php else: ?>

		<?php appthemes_page_loop_else(); ?>

	<?php endif; ?>

	<?php appthemes_after_page_loop(); ?>

</div><!-- End #main -->
