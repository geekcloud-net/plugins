<?php
/**
 * Template Name: Blog
 */
?>

<div id="main" role="main">
	<div id="ticket-manager" class="tabber">
		<?php get_template_part( 'templates/navigation', 'post' ); ?>

		<div id="recent-tickets" class="panel">
			<?php do_action( 'appthemes_notices' ); ?>
			<?php appthemes_before_blog_loop(); ?>
			<?php get_template_part( 'templates/loop', 'post' ); ?>
			<?php appthemes_after_blog_loop(); ?>
		</div>
	</div><!-- End #ticket-manager -->
</div><!-- End #main -->
