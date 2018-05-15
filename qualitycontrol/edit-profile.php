<?php
/**
 * Template Name: Edit Profile
 */
?>

<div id="main" role="main">

	<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

		<div id="ticket-manager" class="tabber">

			<?php get_template_part( 'templates/navigation', 'edit-profile' ); ?>

			<div id="edit-profile" class="panel">
				<?php get_template_part( 'templates/form', 'edit-profile' ); ?>
			</div>

		</div><!-- End #ticket-manager -->

	<?php endwhile; endif; ?>

</div><!-- End #main -->
