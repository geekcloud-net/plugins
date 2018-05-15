<?php
/**
 * Template Name: Create Ticket
 */
?>

<div id="main" role="main">

	<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

		<div id="ticket-manager" class="tabber">

			<?php get_template_part( 'templates/navigation', 'create-ticket' ); ?>

			<div class="panel">
<?php
			if ( qc_can_create_ticket() )
				get_template_part( 'templates/form', 'create-ticket' );
			else
				echo html( 'p class="no-cap"', __( 'You do not have permission to create tickets.', APP_TD ) );
?>
			</div>

		</div><!-- End #ticket-manager -->

	<?php endwhile; endif; ?>

</div><!-- End #main -->
