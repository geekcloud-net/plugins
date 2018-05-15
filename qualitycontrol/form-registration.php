<?php
/**
 * Template Name: Register
 */
?>

<div id="main" role="main">

	<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

		<div id="ticket-manager" class="tabber">

			<?php get_template_part( 'templates/navigation', 'register' ); ?>

			<div class="panel">

				<?php do_action( 'appthemes_notices' ); ?>

				<?php get_template_part('form-registration-fields'); ?>

			</div>

		</div><!-- End #ticket-manager -->

	<?php endwhile; endif; ?>

</div><!-- End #main -->
