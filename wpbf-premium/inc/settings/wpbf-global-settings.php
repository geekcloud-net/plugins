<?php
/**
 * Settings
 *
 * @package Ultimate Dashboard PRO
 */
 
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Settings
add_action( 'admin_init', 'wpbf_premium' );

function wpbf_premium() {

	// Register Settings
	register_setting( 'wpbf-premium-group', 'wpbf_settings' );

	// Settings Sections
	add_settings_section( 'wpbf-global-tempalte-settings', __( 'Global Template Settings <a href="https://wp-pagebuilderframework.com/docs/theme-settings/" target="_blank" title="Documentation" class="dashicons dashicons-editor-help"></a>', 'wpbfpremium' ), '', 'wpbf-premium-settings' );
	add_settings_section( 'wpbf-performance-settings', __( 'Performance Settings <a href="https://wp-pagebuilderframework.com/docs/theme-settings/#performance" target="_blank" title="Documentation" class="dashicons dashicons-editor-help"></a>', 'wpbfpremium' ), '', 'wpbf-premium-settings' );
	add_settings_section( 'wpbf-responsive-breakpoints-settings', __( 'Responsive Breakpoints', 'wpbfpremium' ), '', 'wpbf-premium-settings' );
	add_settings_section( 'wpbf-white-label-settings', __( 'White Label <a href="https://wp-pagebuilderframework.com/docs/theme-settings/#whitelabel" target="_blank" title="Documentation" class="dashicons dashicons-editor-help"></a> <p class="description">For a white labeled child theme, check out the <a href="https://wp-pagebuilderframework.com/child-theme-generator/" target="_blank">Child Theme Generator</a>.</p>', 'wpbfpremium' ), '', 'wpbf-premium-settings' );

	// Settings Fields
	add_settings_field( 'wpbf_fullwidth_global', __( 'Full Width', 'wpbfpremium' ), 'wpbf_fullwidth_global_callback', 'wpbf-premium-settings', 'wpbf-global-tempalte-settings' );
	add_settings_field( 'wpbf_removetitle_global', __( 'Remove Title', 'wpbfpremium' ), 'wpbf_removetitle_global_callback', 'wpbf-premium-settings', 'wpbf-global-tempalte-settings' );
	add_settings_field( 'wpbf_clean_head', __( 'Performance Settings', 'wpbfpremium' ), 'wpbf_performance_callback', 'wpbf-premium-settings', 'wpbf-performance-settings' );
	add_settings_field( 'wpbf_breakpoint_medium', __( 'Medium Devices', 'wpbfpremium' ), 'wpbf_breakpoint_medium_callback', 'wpbf-premium-settings', 'wpbf-responsive-breakpoints-settings' );
	add_settings_field( 'wpbf_breakpoint_desktop', __( 'Desktop', 'wpbfpremium' ), 'wpbf_breakpoint_desktop_callback', 'wpbf-premium-settings', 'wpbf-responsive-breakpoints-settings' );

	add_settings_field( 'wpbf_theme_name', __( 'Theme Name', 'wpbfpremium' ), 'wpbf_theme_name_callback', 'wpbf-premium-settings', 'wpbf-white-label-settings' );
	add_settings_field( 'wpbf_theme_description', __( 'Theme Description', 'wpbfpremium' ), 'wpbf_theme_description_callback', 'wpbf-premium-settings', 'wpbf-white-label-settings' );
	add_settings_field( 'wpbf_theme_tags', __( 'Tags', 'wpbfpremium' ), 'wpbf_theme_tags_callback', 'wpbf-premium-settings', 'wpbf-white-label-settings' );
	add_settings_field( 'wpbf_theme_company_name', __( 'Company Name', 'wpbfpremium' ), 'wpbf_theme_company_name_callback', 'wpbf-premium-settings', 'wpbf-white-label-settings' );
	add_settings_field( 'wpbf_theme_company_url', __( 'Company URL', 'wpbfpremium' ), 'wpbf_theme_company_url_callback', 'wpbf-premium-settings', 'wpbf-white-label-settings' );
	add_settings_field( 'wpbf_theme_screenshot', __( 'Screenshot', 'wpbfpremium' ), 'wpbf_theme_screenshot_callback', 'wpbf-premium-settings', 'wpbf-white-label-settings' );

}

// Helpers
function wpbf_remove_array_item( $array, $item ) {
	$index = array_search( $item, $array );
	if ( $index !== false ) {
		unset( $array[$index] );
	}

	return $array;
}

// Full Width Callback
function wpbf_fullwidth_global_callback() {

	// Full Width cpts
	$post_types = get_post_types( array( 'public' => true ) );

	// remove unnecessary cpt's
	$post_types = wpbf_remove_array_item( $post_types, 'attachment' );
	$post_types = wpbf_remove_array_item( $post_types, 'elementor_library' );

	$wpbf_settings = get_option( 'wpbf_settings' );

	// loop through public post types
	foreach( $post_types as $post_type ) {

		// if array is empty, stop here
		if (!isset( $wpbf_settings['wpbf_fullwidth_global'] ) ) {

			$full_width_global = false;

		// otherwise check if post type is in array and proceed
		} else {

			if ( in_array( $post_type, $wpbf_settings['wpbf_fullwidth_global'] ) ) {

			    $full_width_global = $post_type;

			} else {

				$full_width_global = false;

			}

		}

		echo '<label><input type="checkbox" name="wpbf_settings[wpbf_fullwidth_global][]" value="'.$post_type.'" '. checked( $full_width_global, $post_type, false ) .' />'. ucfirst( $post_type ) .'</label>';
		echo '&nbsp;&nbsp;';

	}

}

// Remove Title Callback
function wpbf_removetitle_global_callback() {

	// Full Width cpts
	$post_types = get_post_types( array( 'public' => true, ) );

	// remove unnecessary cpt's
	$post_types = wpbf_remove_array_item( $post_types, 'attachment' );
	$post_types = wpbf_remove_array_item( $post_types, 'elementor_library' );

	$wpbf_settings = get_option( 'wpbf_settings' );

	// loop through public post types
	foreach( $post_types as $post_type ) {

		// if array is empty, stop here
		if (!isset( $wpbf_settings['wpbf_removetitle_global'] ) ) {

			$full_width_global = false;

		// otherwise check if post type is in array and proceed
		} else {

			if ( in_array( $post_type, $wpbf_settings['wpbf_removetitle_global'] ) ) {

			    $full_width_global = $post_type;

			} else {

				$full_width_global = false;
				
			}

		}

		echo '<label><input type="checkbox" name="wpbf_settings[wpbf_removetitle_global][]" value="'.$post_type.'" '. checked( $full_width_global, $post_type, false ) .' />'. ucfirst( $post_type ) .'</label>';
		echo '&nbsp;&nbsp;';

	}

}

// Performance Callback

function wpbf_performance_callback() {

	$wpbf_settings = get_option( 'wpbf_settings' );

	if (!isset( $wpbf_settings['wpbf_clean_head'] ) ) {

		$remove_feed = $remove_rsd = $remove_wlwmanifest = $remove_generator = $remove_shortlink = $disable_emojis = $disable_embeds = $remove_jquery_migrate = $disable_rss_feed = $google_fonts_async = false;

	} else {

		$remove_feed = in_array( 'remove_feed', $wpbf_settings['wpbf_clean_head'] ) ? 1 : false;
		$remove_rsd = in_array( 'remove_rsd', $wpbf_settings['wpbf_clean_head'] ) ? 1 : false;
		$remove_wlwmanifest = in_array( 'remove_wlwmanifest', $wpbf_settings['wpbf_clean_head'] ) ? 1 : false;
		$remove_generator = in_array( 'remove_generator', $wpbf_settings['wpbf_clean_head'] ) ? 1 : false;
		$remove_shortlink = in_array( 'remove_shortlink', $wpbf_settings['wpbf_clean_head'] ) ? 1 : false;
		$disable_emojis = in_array( 'disable_emojis', $wpbf_settings['wpbf_clean_head'] ) ? 1 : false;
		$disable_embeds = in_array( 'disable_embeds', $wpbf_settings['wpbf_clean_head'] ) ? 1 : false;
		$remove_jquery_migrate = in_array( 'remove_jquery_migrate', $wpbf_settings['wpbf_clean_head'] ) ? 1 : false;
		$disable_rss_feed = in_array( 'disable_rss_feed', $wpbf_settings['wpbf_clean_head'] ) ? 1 : false;
		$google_fonts_async = in_array( 'google_fonts_async', $wpbf_settings['wpbf_clean_head'] ) ? 1 : false;

	}

	echo '<label><input type="checkbox" name="wpbf_settings[wpbf_clean_head][]" value="remove_feed" '. checked( $remove_feed, 1, false ) .' />'. __( 'Remove Feed Links', 'wpbfpremium' ) .'</label>';

	echo '<br>';

	echo '<label><input type="checkbox" name="wpbf_settings[wpbf_clean_head][]" value="remove_rsd" '. checked( $remove_rsd, 1, false ) .' />'. __( 'Remove RSD', 'wpbfpremium' ) .'</label>';

	echo '<br>';

	echo '<label><input type="checkbox" name="wpbf_settings[wpbf_clean_head][]" value="remove_wlwmanifest" '. checked( $remove_wlwmanifest, 1, false ) .' />'. __( 'Remove wlwmanifest', 'wpbfpremium' ) .'</label>';

	echo '<br>';

	echo '<label><input type="checkbox" name="wpbf_settings[wpbf_clean_head][]" value="remove_generator" '. checked( $remove_generator, 1, false ) .' />'. __( 'Remove Generator', 'wpbfpremium' ) .'</label>';

	echo '<br>';

	echo '<label><input type="checkbox" name="wpbf_settings[wpbf_clean_head][]" value="remove_shortlink" '. checked( $remove_shortlink, 1, false ) .' />'. __( 'Remove Shortlink', 'wpbfpremium' ) .'</label>';

	echo '<br>';

	echo '<label><input type="checkbox" name="wpbf_settings[wpbf_clean_head][]" value="disable_emojis" '. checked( $disable_emojis, 1, false ) .' />'. __( 'Disable Emojis', 'wpbfpremium' ) .'</label>';

	echo '<br>';

	echo '<label><input type="checkbox" name="wpbf_settings[wpbf_clean_head][]" value="disable_embeds" '. checked( $disable_embeds, 1, false ) .' />'. __( 'Disable Embeds', 'wpbfpremium' ) .'</label>';

	echo '<br>';

	echo '<label><input type="checkbox" name="wpbf_settings[wpbf_clean_head][]" value="remove_jquery_migrate" '. checked( $remove_jquery_migrate, 1, false ) .' />'. __( 'Remove jQuery Migrate', 'wpbfpremium' ) .'</label>';

	echo '<br>';

	echo '<label><input type="checkbox" name="wpbf_settings[wpbf_clean_head][]" value="disable_rss_feed" '. checked( $disable_rss_feed, 1, false ) .' />'. __( 'Disable RSS Feed', 'wpbfpremium' ) .'</label>';

	echo '<br>';

	echo '<label><input type="checkbox" name="wpbf_settings[wpbf_clean_head][]" value="google_fonts_async" '. checked( $google_fonts_async, 1, false ) .' />'. __( 'Load Google Fonts asynchronously', 'wpbfpremium' ) .'</label>';

}

function wpbf_breakpoint_medium_callback() {

	$wpbf_settings = get_option( 'wpbf_settings' );

	if ( empty( $wpbf_settings['wpbf_breakpoint_medium'] ) ) {

		$breakpoint_medium = false;

	} else {

		$breakpoint_medium = $wpbf_settings['wpbf_breakpoint_medium'];

	}


	echo '<label><input type="text" name="wpbf_settings[wpbf_breakpoint_medium]" value="'. esc_attr( $breakpoint_medium ) .'" placeholder="768px" /><span class="description">'. __( 'Default: above 768px for tablets.', 'wpbfpremium' ) .'</span></label>';
}

function wpbf_breakpoint_desktop_callback() {

	$wpbf_settings = get_option( 'wpbf_settings' );

	if ( empty( $wpbf_settings['wpbf_breakpoint_desktop'] ) ) {

		$breakpoint_medium = false;

	} else {

		$breakpoint_medium = $wpbf_settings['wpbf_breakpoint_desktop'];

	}

	echo '<label><input type="text" name="wpbf_settings[wpbf_breakpoint_desktop]" value="'. esc_attr( $breakpoint_medium ) .'" placeholder="1024px" /><span class="description">'. __( 'Default: above 1024px for desktops.', 'wpbfpremium' ) .'</span></label>';

}

function wpbf_theme_name_callback() {

	$wpbf_settings = get_option( 'wpbf_settings' );

	if ( !isset( $wpbf_settings['wpbf_theme_name'] ) ) {

		$theme_name = false;

	} else {

		$theme_name = $wpbf_settings['wpbf_theme_name'];

	}

	echo '<input type="text" name="wpbf_settings[wpbf_theme_name]" value="'. esc_html( $theme_name ) .'" />';

}

function wpbf_theme_description_callback() {

	$wpbf_settings = get_option( 'wpbf_settings' );

	if ( !isset( $wpbf_settings['wpbf_theme_description'] ) ) {

		$theme_description = false;

	} else {

		$theme_description = $wpbf_settings['wpbf_theme_description'];

	}

	echo '<input type="text" name="wpbf_settings[wpbf_theme_description]" value="'. esc_html( $theme_description ) .'" />';

}

function wpbf_theme_tags_callback() {

	$wpbf_settings = get_option( 'wpbf_settings' );

	if ( !isset( $wpbf_settings['wpbf_theme_tags'] ) ) {

		$theme_tags = false;

	} else {

		$theme_tags = $wpbf_settings['wpbf_theme_tags'];

	}

	echo '<input type="text" name="wpbf_settings[wpbf_theme_tags]" value="'. esc_html( $theme_tags ) .'" />';

}

function wpbf_theme_company_name_callback() {

	$wpbf_settings = get_option( 'wpbf_settings' );

	if ( !isset( $wpbf_settings['wpbf_theme_company_name'] ) ) {

		$theme_company_name = false;

	} else {

		$theme_company_name = $wpbf_settings['wpbf_theme_company_name'];

	}

	echo '<input type="text" name="wpbf_settings[wpbf_theme_company_name]" value="'. esc_html( $theme_company_name ) .'" />';

}

function wpbf_theme_company_url_callback() {

	$wpbf_settings = get_option( 'wpbf_settings' );

	if ( !isset( $wpbf_settings['wpbf_theme_company_url'] ) ) {

		$theme_company_url = false;

	} else {

		$theme_company_url = $wpbf_settings['wpbf_theme_company_url'];

	}

	echo '<input type="text" name="wpbf_settings[wpbf_theme_company_url]" value="'. esc_html( $theme_company_url ) .'" />';

}

function wpbf_theme_screenshot_callback() {

	$wpbf_settings = get_option( 'wpbf_settings' );

	if ( !isset( $wpbf_settings['wpbf_theme_screenshot'] ) ) {

		$theme_screenshot = false;

	} else {

		$theme_screenshot = $wpbf_settings['wpbf_theme_screenshot'];

	}

	if( function_exists( 'wp_enqueue_media' ) ) {

		wp_enqueue_media();

	} else {

		wp_enqueue_style( 'thickbox' );
		wp_enqueue_script( 'media-upload' );
		wp_enqueue_script( 'thickbox' );

	} ?>

	<input id="wpbf-screenshot" class="wpbf-screenshot-url" type="text" name="wpbf_settings[wpbf_theme_screenshot]" size="50" value="<?php echo esc_url( $theme_screenshot ); ?>">
	<a href="#" class="wpbf-screenshot-upload button-secondary"><?php echo esc_html__( 'Add or Upload File', 'wpbfpremum' ); ?></a>
	<a href="#" class="wpbf-screenshot-remove button-secondary">x</a><br>
	<label for="wpbf-screenshot" class="description"><span class="description"><?php _e( 'Recommended image size: 1200px x 900px', 'wpbfpremium' ); ?></span></label>

	</p>

<?php }