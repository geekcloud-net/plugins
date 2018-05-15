<?php
/**
Plugin Name: Yoast SEO: Video
Version: 7.4
Plugin URI: https://yoast.com/wordpress/plugins/video-seo/
Description: The Yoast Video SEO plugin makes sure your videos are recognized by search engines and social platforms, so they look good when found on these social platforms and in the search results.
Author: Team Yoast
Author URI: https://yoast.com
Depends: Yoast SEO
Text Domain: yoast-video-seo
Domain Path: /languages/

Copyright 2012-2016 Yoast BV

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

 * @package Yoast\VideoSEO
 **/

if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload_52.php' ) ) {
	require dirname( __FILE__ ) . '/vendor/autoload_52.php';
}

define( 'WPSEO_VIDEO_VERSION', '7.4' );
define( 'WPSEO_VIDEO_FILE', __FILE__ );

include_once dirname( __FILE__ ) . '/video-seo-api.php';

if ( ! wp_installing() ) {
	add_action( 'plugins_loaded', 'yoast_wpseo_video_seo_init', 5 );
}

register_activation_hook( __FILE__, 'yoast_wpseo_video_activate' );

register_deactivation_hook( __FILE__, 'yoast_wpseo_video_deactivate' );
