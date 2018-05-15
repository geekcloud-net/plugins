<?php
/**
 * Template Name: Password Recovery
 */
?>

<div id="main" role="main">

	<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

		<div id="ticket-manager" class="tabber">

			<?php get_template_part( 'templates/navigation', 'password-recovery' ); ?>

			<div class="panel">

				<?php do_action( 'appthemes_notices' ); ?>

				<?php require APP_FRAMEWORK_DIR . '/templates/form-password-recovery.php'; ?>

			</div>

		</div><!-- End #ticket-manager -->

	<?php endwhile; endif; ?>

</div><!-- End #main -->
