<div id="branding_container" class="container">
	<div class="row">
		<div class="ninecol">
			<div id="branding" role="banner">
				<a class="brand_bug">&nbsp;</a>
				<?php add_filter( 'wp_title', 'qc_filter_page_title', 10, 2 ); ?>
				<?php $heading_tag = ( is_home() || is_front_page() ) ? 'h1' : 'div'; ?>
				<<?php echo $heading_tag; ?> id="site-title">
					<a href="<?php echo home_url( '/' ); ?>"><?php bloginfo( 'name' ); ?></a> <?php wp_title( '&rarr;', true, 'left' ); ?>
				</<?php echo $heading_tag; ?>>
				<div class="tagline"><?php bloginfo( 'description' ); ?></div>
			</div><!-- #branding -->
		</div>

		<div id="current-user-box" class="threecol last">
			<?php get_template_part( 'templates/user-box', is_user_logged_in() ? 'logged-in' : 'visitor' ); ?>
		</div>
	</div>
</div>
