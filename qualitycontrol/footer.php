<div id="footer" class="container">
	<div class="row">
		<div class="ninecol">
			<div id="footer-wrap">
				<?php wp_nav_menu( array( 'theme_location' => 'footer', 'container' => false, 'menu_id' => 'footer-nav-menu', 'depth' => 1, 'fallback_cb' => false ) ); ?>
				<ul>
					<li><a href="https://www.appthemes.com/themes/qualitycontrol/" target="_blank" rel="nofollow">Quality Control Theme</a> - <?php _e( 'Powered by', APP_TD ); ?> <a href="https://www.wordpress.org" target="_blank" rel="nofollow">WordPress</a></li>
					<li class="alignright"><?php printf( __( '%1$d queries. %2$s seconds.', APP_TD ), get_num_queries(), timer_stop() ); ?></li>
				</ul>
			</div>
		</div><!--.col-->
		<div class="threecol last">
		</div><!--.col-->
	</div><!--.row-->
</div><!--#footer .container-->
