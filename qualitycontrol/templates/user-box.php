<?php echo get_avatar( wp_get_current_user()->user_email, 30 ); ?>

<div id="current-user-name">
<?php echo wp_get_current_user()->display_name; ?>
</div>

<div id="current-user-links">
<?php echo html_link( appthemes_get_edit_profile_url(), __( 'Edit profile', APP_TD ) ); ?> | <?php wp_loginout( get_bloginfo('url') ); ?>
</div>
