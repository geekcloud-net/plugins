<?php
/**
 * Template Name: Transparent Header
 *
 * Page Template to display a Page with a Transparent Header
 *
 * @package Page Builder Framework
 */
 
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Body Class for transparent header
function wpbf_transparent_header_body_class( $classes ) {
	$classes[] = 'wpbf-transparent-header';
	return $classes;
}
add_filter( 'body_class', 'wpbf_transparent_header_body_class' );

get_header(); ?>

		<div id="content">
			
			<?php wpbf_inner_content(); ?>

				<main id="main" class="wpbf-main" role="main">

					<?php wpbf_title(); ?>
					<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
					<?php the_content(); ?>
					<?php endwhile; endif; ?>

				</main>

			<?php wpbf_inner_content_close(); ?>
			
		</div>
	    
<?php get_footer(); ?>