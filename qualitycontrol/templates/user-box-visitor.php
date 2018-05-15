<?php echo get_avatar( 0, 30 ); ?>

<div id="current-user-name">
	<?php _e( 'Visitor', APP_TD ); ?>
</div>

<div id="current-user-links">
	<?php wp_loginout( get_bloginfo('url') ); ?>
</div>
