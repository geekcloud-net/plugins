<div id="main" role="main">
	<div id="ticket-manager" class="tabber">
		<?php get_template_part( 'templates/navigation', 'dashboard' ); ?>

		<div id="recent-tickets" class="panel">
			<?php do_action( 'appthemes_notices' ); ?>
			<?php appthemes_before_loop(); ?>
			<?php get_template_part( 'templates/loop', QC_TICKET_PTYPE ); ?>
			<?php appthemes_after_loop(); ?>
		</div>
	</div><!-- End #ticket-manager -->
</div><!-- End #main -->
