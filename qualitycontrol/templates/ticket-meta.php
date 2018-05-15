<?php do_action( 'qc_ticket_fields_before' ); ?>

<li>
	<small><?php _e( 'Ticket', APP_TD ); ?></small>
	<a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', APP_TD ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark">#<?php the_ID(); ?></a>
</li>

<?php do_action( 'qc_ticket_fields_between' ); ?>

<?php if ( current_theme_supports( 'ticket-assignment' ) ) : ?>

	<li>
		<small><?php _e( 'Assigned to', APP_TD ); ?></small>
		<?php echo qc_assigned_to_linked(); ?>
	</li>

<?php endif; ?>

	<li>
		<small><?php _e( 'Last Updated', APP_TD ); ?></small>
		<abbr class="last-updated" title="<?php echo get_the_modified_time( DATE_ATOM ); ?>"><?php echo get_the_modified_time( 'g:i a' ); ?></abbr>
	</li>

	<li>
		<small><?php _e( 'Modified by', APP_TD ); ?></small>
		<?php if ( get_the_modified_author() ) : ?>
			<?php echo get_the_modified_author(); ?>
		<?php else : ?>
			&mdash;
		<?php endif; ?>
	</li>

<?php do_action( 'qc_ticket_fields_after' ); ?>

