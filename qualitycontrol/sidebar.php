<?php
/**
 * The Sidebar containing the primary and secondary widget areas.
 *
 * @package Quality_Control
 * @since Quality Control 0.1
 */

?>

<div id="sidebar" class="widget-area" role="complementary">

	<ul class="submenu">

	<?php
		if ( is_home() || is_singular( 'post' ) || is_tag() || is_category() ) {

			dynamic_sidebar( 'sidebar_blog' );

		} else if ( is_page() && ! is_page_template( 'tickets-home.php' ) && ! is_page_template( 'create-ticket.php' ) ) {

			dynamic_sidebar( 'sidebar_page' );

		} else {

			dynamic_sidebar( 'primary-widget-area' );

		}

	?>

	</ul>

</div><!-- End #sidebar -->
