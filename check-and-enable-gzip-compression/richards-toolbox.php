<?php
/*
Plugin Name: Check and Enable GZIP compression
Plugin URI: http://checkgzipcompression.com
Description: This handy tool checks if you have GZIP compression enabled, and makes it possible to turn on GZIP compression. Every time you run this check, your domain name will be sent to http://checkgzipcompression.com. We won't sent any other private information.
Author: Richard's Toolbox
Text Domain: richards-toolbox
Version: 1.1.11
Author URI: http://richardstoolbox.com
*/

/*
 *  Now we set that function up to execute when the admin_notices action is called
 */
add_action( 'admin_notices', 'richard_toolbox_notices' );
function richard_toolbox_notices() {
	global $current_user;
	$user_id = $current_user->ID;
	//$hideError = false;
	//$hideSuccess = false;
	$hideError = get_user_meta($user_id, 'rt_gzip_error_ignore', true);
	$hideSuccess = get_user_meta($user_id, 'rt_gzip_success_ignore', true);
	if(!$hideError || !$hideSuccess) {
		require_once(dirname(__FILE__) . '/class.richards-toolbox.php');
		$tb = new Richards_Toolbox();
		$siteUrl = get_home_url();
		$gzipCheck = $tb->checkGZIPCompression($siteUrl);
		if($gzipCheck !== false) {
			if(!$gzipCheck->result->gzipenabled && !$gzipCheck->error && !$hideError) {
				$canEnableCheck = $tb->checkGZIPCompression(urlencode(add_query_arg('preview-gzip', '1', $siteUrl)));
				$canEnableMessage = '';
				if($canEnableCheck->result->gzipenabled == 1) {
					$canEnableMessage = 'Please go to the <a href="'.admin_url('tools.php?page=richards-toolbox-gzip').'">plugin admin page</a> to enable GZIP compression and make your site '.$gzipCheck->result->percentagesaved.'% smaller!';
				} else {
					$canEnableMessage = 'Please go to the <a href="'.admin_url('tools.php?page=richards-toolbox-gzip').'">plugin admin page</a> to view the instructions to make your site '.$gzipCheck->result->percentagesaved.'% smaller!';
				}
				echo '<div class="error"><p><strong>GZIP compression is disabled. '.$canEnableMessage.'</strong> | <a href="'.admin_url('tools.php?page=richards-toolbox-gzip&rt_gzip_error_ignore').'">Remove alert</a></p></div>';
			} elseif($gzipCheck->result->gzipenabled && !$gzipCheck->error && !$hideSuccess) {
				echo '<div class="updated"><p><strong>GZIP compression is enabled. Good job!</strong> | <a href="'.$siteUrl.'/wp-admin/tools.php?page=richards-toolbox-gzip&rt_gzip_success_ignore">Remove alert</a></p></div>';
			}
		}
	}
}

add_action('admin_init', 'rt_gzip_ignore');
function rt_gzip_ignore() {
	global $current_user;
	$user_id = $current_user->ID;
	/* If user clicks to ignore the notice, add that to their user meta */
	if ( isset($_GET['rt_gzip_success_ignore']) ) {
	     add_user_meta($user_id, 'rt_gzip_success_ignore', 'true', true);
	}

	if ( isset($_GET['rt_gzip_error_ignore']) ) {
	     add_user_meta($user_id, 'rt_gzip_error_ignore', 'true', true);
	}
}




// Add extra page in tools
/** Step 2 (from text above). */
add_action( 'admin_menu', 'toolbox_plugin_menu' );

/** Step 1. */
function toolbox_plugin_menu() {
	add_management_page( __('GZIP Compression','richards-toolbox'), __('GZIP Compression','richards-toolbox'), 'manage_options', 'richards-toolbox-gzip', 'gzip_compression_page' );
}

/** Step 3. */
function gzip_compression_page() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	require_once(dirname(__FILE__) . '/class.richards-toolbox.php');
	$tb = new Richards_Toolbox();
	$siteUrl = get_home_url();
	$gzipCheck = $tb->checkGZIPCompression($siteUrl);
	$canEnableCheck = $tb->checkGZIPCompression(add_query_arg('preview-gzip', '1', $siteUrl));
	require(dirname(__FILE__) . '/gzip-page.php');
}

//check gzip compression
add_action( 'wp_loaded', 'check_richards_toolbox_htaccess');
function check_richards_toolbox_htaccess() {
     if(get_option('richards-toolbox-htaccess-enabled')) {
	add_filter('mod_rewrite_rules', 'richards_toolbox_addHtaccessContent');
     }
}

function richards_toolbox_addHtaccessContent($rules) {
	$my_content = '
# BEGIN GZIP COMPRESSION BY RICHARD\'S TOOLBOX
<IfModule mod_deflate.c>
	<IfModule mod_filter.c>
			<IfModule mod_version.c>
				# Declare a "gzip" filter, it should run after all internal filters like PHP or SSI
				FilterDeclare  gzip CONTENT_SET

				# Enable "gzip" filter if "Content-Type" contains "text/html", "text/css" etc.
				<IfVersion < 2.4.4>
					<IfModule filter_module>
						FilterDeclare   COMPRESS
						FilterProvider  COMPRESS  DEFLATE resp=Content-Type $text/html
						FilterProvider  COMPRESS  DEFLATE resp=Content-Type $text/css
						FilterProvider  COMPRESS  DEFLATE resp=Content-Type $text/plain
						FilterProvider  COMPRESS  DEFLATE resp=Content-Type $text/xml
						FilterProvider  COMPRESS  DEFLATE resp=Content-Type $text/x-component
						FilterProvider  COMPRESS  DEFLATE resp=Content-Type $application/javascript
						FilterProvider  COMPRESS  DEFLATE resp=Content-Type $application/json
						FilterProvider  COMPRESS  DEFLATE resp=Content-Type $application/xml
						FilterProvider  COMPRESS  DEFLATE resp=Content-Type $application/xhtml+xml
						FilterProvider  COMPRESS  DEFLATE resp=Content-Type $application/rss+xml
						FilterProvider  COMPRESS  DEFLATE resp=Content-Type $application/atom+xml
						FilterProvider  COMPRESS  DEFLATE resp=Content-Type $application/vnd.ms-fontobject
						FilterProvider  COMPRESS  DEFLATE resp=Content-Type $image/svg+xml
						FilterProvider  COMPRESS  DEFLATE resp=Content-Type $application/x-font-ttf
						FilterProvider  COMPRESS  DEFLATE resp=Content-Type $font/opentype
						FilterChain     COMPRESS
						FilterProtocol  COMPRESS  DEFLATE change=yes;byteranges=no
					</IfModule>
				</IfVersion>

				<IfVersion >= 2.4.4>
					<IfModule filter_module>
						FilterDeclare   COMPRESS
						FilterProvider  COMPRESS  DEFLATE "%{Content_Type} = \'text/html\'"
						FilterProvider  COMPRESS  DEFLATE "%{Content_Type} = \'text/css\'"
						FilterProvider  COMPRESS  DEFLATE "%{Content_Type} = \'text/plain\'"
						FilterProvider  COMPRESS  DEFLATE "%{Content_Type} = \'text/xml\'"
						FilterProvider  COMPRESS  DEFLATE "%{Content_Type} = \'text/x-component\'"
						FilterProvider  COMPRESS  DEFLATE "%{Content_Type} = \'application/javascript\'"
						FilterProvider  COMPRESS  DEFLATE "%{Content_Type} = \'application/json\'"
						FilterProvider  COMPRESS  DEFLATE "%{Content_Type} = \'application/xml\'"
						FilterProvider  COMPRESS  DEFLATE "%{Content_Type} = \'application/xhtml+xml\'"
						FilterProvider  COMPRESS  DEFLATE "%{Content_Type} = \'application/rss+xml\'"
						FilterProvider  COMPRESS  DEFLATE "%{Content_Type} = \'application/atom+xml\'"
						FilterProvider  COMPRESS  DEFLATE "%{Content_Type} = \'application/vnd.ms-fontobject\'"
						FilterProvider  COMPRESS  DEFLATE "%{Content_Type} = \'image/svg+xml\'"
						FilterProvider  COMPRESS  DEFLATE "%{Content_Type} = \'image/x-icon\'"
						FilterProvider  COMPRESS  DEFLATE "%{Content_Type} = \'application/x-font-ttf\'"
						FilterProvider  COMPRESS  DEFLATE "%{Content_Type} = \'font/opentype\'"
						FilterChain     COMPRESS
						FilterProtocol  COMPRESS  DEFLATE change=yes;byteranges=no
					</IfModule>
				</IfVersion>
		</IfModule>
	</IfModule>

  <IfModule !mod_filter.c>
	 #add content typing
	AddType application/x-gzip .gz .tgz
	AddEncoding x-gzip .gz .tgz

	# Insert filters
	AddOutputFilterByType DEFLATE text/plain
	AddOutputFilterByType DEFLATE text/html
	AddOutputFilterByType DEFLATE text/xml
	AddOutputFilterByType DEFLATE text/css
	AddOutputFilterByType DEFLATE application/xml
	AddOutputFilterByType DEFLATE application/xhtml+xml
	AddOutputFilterByType DEFLATE application/rss+xml
	AddOutputFilterByType DEFLATE application/javascript
	AddOutputFilterByType DEFLATE application/x-javascript
	AddOutputFilterByType DEFLATE application/x-httpd-php
	AddOutputFilterByType DEFLATE application/x-httpd-fastphp
	AddOutputFilterByType DEFLATE image/svg+xml

	# Drop problematic browsers
	BrowserMatch ^Mozilla/4 gzip-only-text/html
	BrowserMatch ^Mozilla/4\.0[678] no-gzip
	BrowserMatch \bMSI[E] !no-gzip !gzip-only-text/html

	# Make sure proxies don\'t deliver the wrong content
	Header append Vary User-Agent env=!dont-vary
  </IfModule>
</IfModule>

<IfModule !mod_deflate.c>
    #Apache deflate module is not defined, active the page compression through PHP ob_gzhandler
    php_flag output_buffering On
    php_value output_handler ob_gzhandler
</IfModule>
# END GZIP COMPRESSION
';
	return $my_content . $rules;
}

add_action( 'after_setup_theme', 'check_richards_toolbox_gzip' );
function check_richards_toolbox_gzip() {
     global $wp_customize;
     if(!isset( $wp_customize ) && (!get_option('richards-toolbox-htaccess-enabled') && get_option('richards-toolbox-gzip-enabled') || isset($_GET['preview-gzip'])) && !is_admin() ) {
          if (!in_array('ob_gzhandler', ob_list_handlers())) {
		ob_start('ob_gzhandler');
	    } else {
		ob_start();
	    }
     }
}
