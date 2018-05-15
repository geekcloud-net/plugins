<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />

	<title><?php wp_title(''); ?></title>

	<link rel="profile" href="http://gmpg.org/xfn/11" />
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />

	<meta name="viewport" content="width=device-width, initial-scale=1.0" />

	<!--[if lte IE 9]><link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/styles/ie.css" type="text/css" media="screen" /><![endif]-->
	<link rel="stylesheet" type="text/css" media="screen" href="<?php echo get_template_directory_uri(); ?>/styles/1140.css" />
	<link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo( 'stylesheet_url' ); ?>" />
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

	<?php appthemes_before(); ?>

	<?php appthemes_before_header(); ?>
	<?php get_header( app_template_base() ); ?>
	<?php appthemes_after_header(); ?>

	<div id="content" class="container">
		<div class="row">
			<div class="ninecol">
				<?php load_template( app_template_path() ); ?>
			</div><!--.col-->

			<div class="threecol last">
				<?php get_sidebar( app_template_base() ); ?>
			</div><!--.col-->
		</div><!--.row-->
		<div class="push"></div>
	</div><!-- .container -->

	<?php appthemes_before_footer(); ?>
	<?php get_footer( app_template_base() ); ?>
	<?php appthemes_after_footer(); ?>

	<?php appthemes_after(); ?>

	<?php wp_footer();?>
</body>
</html>
