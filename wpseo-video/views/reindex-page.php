<?php
/**
 * @package Yoast\VideoSEO
 */

?><h2><?php esc_html_e( 'Re-indexation', 'yoast-video-seo' ); ?></h2>

<p><?php esc_html_e( 'Your site is being indexed at the moment, so don\'t close this window. This process may take a few minutes to complete.', 'yoast-video-seo' ); ?></p>

<input type="hidden" name="video_seo_percentage" id="video_seo_percentage_hidden" value="0" />
<?php
wp_nonce_field( 'videoseo-ajax-nonce-for-reindex', 'videoseo-nonce-ajax' );
if ( isset( $_POST['force'] ) && $_POST['force'] === 'on' ) :
?>
<input type="hidden" name="video_seo_force_reindex" id="video_seo_force_reindex" value="on" />
<?php endif; ?>

<div id="video_seo_progressbar">
	<div class="bar">
		<p><span class="bar_status">&nbsp;</span></p>
	</div>
</div>

<p>
	<strong><?php esc_html_e( 'Estimated time to go:', 'yoast-video-seo' ); ?> <span class="video_seo_timetogo" id="video_seo_total_time">-- : --</span></strong><br />
	<strong><?php esc_html_e( 'Posts to go:', 'yoast-video-seo' ); ?> <span class="video_seo_timetogo" id="video_seo_posts_to_go">--</span></strong><br />
	<strong><?php esc_html_e( 'Total posts:', 'yoast-video-seo' ); ?> <span class="video_seo_timetogo" id="video_seo_total_posts">--</span></strong><br />
</p>
<p id="video_seo_done">
<?php
printf(
	/* translators: %1$s expands to a link start tag to the plugin settings page, %2$s is the link closing tag. */
	esc_html__( '%1$sDone! Go back to the Video SEO settings%2$s', 'yoast-video-seo' ),
	'<a href="' . esc_url( admin_url( 'admin.php?page=wpseo_video' ) ) . '" class="button button-primary">',
	'</a>'
);
?>
</p>
