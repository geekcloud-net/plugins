<?php do_action( 'qc_above_navigation' ); ?>

<div class="tabber-navigation top">

	<?php if ( $menu = wp_nav_menu( array( 'theme_location' => 'header', 'container' => false, 'menu_id' => 'header-nav-menu', 'echo' => false, 'fallback_cb' => false ) ) ) : ?>

		<?php echo $menu; ?>

	<?php elseif ( ! qc_can_view_all_tickets() && ! is_user_logged_in() ) : ?>

	<?php else : ?>
		<ul>
			<?php do_action( 'qc_navigation_before' ); ?>

			<?php if ( qc_can_view_all_tickets() ) : ?>
				<li <?php if ( qc_is_home() && !get_query_var( 'assigned' ) ) : ?>class="current-tab"<?php endif; ?>>
					<a href="<?php echo qc_home_url(); ?>"><?php _e( 'All Tickets', APP_TD ); ?></a>
				</li>
			<?php endif; ?>

			<?php if ( is_user_logged_in() ) : $current_user = wp_get_current_user(); ?>

				<li <?php if ( ( qc_is_home() && !qc_can_view_all_tickets() ) || is_author( $current_user->ID ) || qc_is_home_assigned() ) : ?>class="current-tab"<?php endif; ?>>
					<a href="#"><?php _e( 'My Tickets', APP_TD ); ?></a>

					<ul class="second-level children">
						<li><a href="<?php echo get_author_posts_url( $current_user->ID, $current_user->user_nicename ); ?>"><?php _e( 'Tickets Started', APP_TD ); ?></a></li>

						<?php if ( current_theme_supports( 'ticket-assignment' ) ) : ?>

							<li><a href="<?php echo qc_get_assigned_to_url( $current_user->user_login, home_url( '/' ) ); ?>"><?php _e( 'Assigned Tickets', APP_TD ); ?></a></li>

						<?php endif; ?>
					</ul>
				</li>

			<?php endif; ?>

			<?php do_action( 'qc_navigation_after' ); ?>

			<?php $page_id = QC_Ticket_Create::get_id(); if ( $page_id && !is_page( $page_id ) && qc_can_create_ticket() ) : ?>

				<li class="alignright">
					<a href="<?php echo get_permalink( $page_id ); ?>"><?php echo get_the_title( $page_id ); ?></a>
				</li>

			<?php endif; ?>

		</ul>

	<?php endif; ?>

</div>
