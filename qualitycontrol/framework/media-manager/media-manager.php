<?php
/**
 * Provides a frontend media manager using the built-in WordPress media uploader.
 *
 * @todo enforce allowed video|audio embeds
 * @todo find better way to disable default WP media views instead of using jQuery via the APP-ITEM flag to hide them
 * @package Framework\Media-Manager
 */

define( 'APP_MEDIA_MANAGER_VERSION', '1.0' );

define( 'APP_ATTACHMENT_FILE', 'file' );		// DEFAULT - meta type assigned to any file uploads
define( 'APP_ATTACHMENT_GALLERY', 'gallery' );  // suggested meta type for image uploads that are displayed as gallery images

add_action( 'parse_query', '_appthemes_media_query_var', 10 );
add_filter( 'admin_url', '_appthemes_media_query_arg', 10, 3 );

add_filter( 'map_meta_cap','_appthemes_media_capabilities', 15, 4 );

/**
 * Sets a query var to better identify the frontend/backend media managers and also
 * acts as helper for the media manager ajax calls.
 */
function _appthemes_media_query_var( $query ) {
	if ( ! is_admin() ) {
		$query->set( 'app_media_manager', 1 );
	}
	return $query;
}

/**
 * Sets a query arg that identifies the media manager ajax calls.
 * The query arg allows distinguishing media manager ajaxs calls and admin ajax calls
 */
function _appthemes_media_query_arg( $url, $path, $blog_id  ) {

	if ( get_query_var('app_media_manager') && 'admin-ajax.php' === basename( $url ) ) {
		$url = add_query_arg( array( 'app_media_manager' => 1), $url );
	}
	return $url;
}

/**
 * Retrieve the 'get_theme_support()' args.
 */
function appthemes_media_manager_get_args( $option = '' ) {

	if ( ! current_theme_supports('app-media-manager') ) {
		return array();
	}

	list( $args ) = get_theme_support('app-media-manager');

	$defaults = array(
		'file_limit'  => -1,		// 0 = disable, -1 = no limit
		'embed_limit' => -1,		// 0 = disable, -1 = no limit
		'file_size'   => 1048577,	// limit file sizes to 1MB (in bytes), -1 = use WP default
		'mime_types'  => '',		// blank = any (accepts 'image', 'image/png', 'png, jpg', etc) (string|array)
	);

	$final = wp_parse_args( $args, $defaults );

	if ( empty( $option ) ) {
		return $final;
	} else if ( isset( $final[ $option ] ) ) {
		return $final[ $option ];
	} else {
		return false;
	}

}

class APP_Media_Manager {

	protected static $attach_ids_inputs = '_app_attach_ids_fields';
	protected static $embed_url_inputs = '_app_embed_urls_fields';

	protected static $default_filters;

	private function init_hooks() {
		add_action( 'appthemes_media_manager',		array( __CLASS__, 'output_hidden_inputs' ), 10, 4 );
		add_action( 'ajax_query_attachments_args',	array( __CLASS__, 'restrict_media_library' ), 5 );
		add_action( 'wp_ajax_app-manage-files',		array( __CLASS__ , 'ajax_refresh_attachments' ) );

		add_filter( 'wp_handle_upload_prefilter',	array( __CLASS__, 'validate_upload_restrictions' ) );
	}

	function __construct() {
		$this->init_hooks();

		$params = appthemes_media_manager_get_args();

		extract( $params );

		// filters to restrict allowed media files
		$defaults = array(
			'meta_type'		=> APP_ATTACHMENT_FILE,
		);
		self::$default_filters = wp_parse_args( $params, $defaults );
	}

	/**
	 * Enqueues the JS scripts that output WP's media uploader.
	 */
	static function enqueue_media_manager( $localization = array() ) {

		wp_register_script(
			'app-media-manager',
			APP_FRAMEWORK_URI . '/media-manager/scripts/media-manager.js',
			array( 'jquery' ),
			APP_MEDIA_MANAGER_VERSION,
			true
		);

		wp_enqueue_style(
			'app-media-manager',
			APP_FRAMEWORK_URI . '/media-manager/style.css'
		);

		$defaults = array(
			'post_id' => 0,
			'post_id_field' => '',
			'ajaxurl' => admin_url( 'admin-ajax.php', 'relative' ),
			'ajax_nonce' => wp_create_nonce('app-media-manager'),
			'files_limit_text' => __( 'Allowed files', APP_TD ),
			'files_type_text' => __( 'Allowed file types', APP_TD ),
		    'insert_media_title' => __( 'Insert Media', APP_TD ),
		    'embed_media_title' => __( 'Insert from URL', APP_TD ),
			'file_size_text' => __( 'Maximum upload file size', APP_TD ),
			'embed_limit_text' => __( 'Allowed embeds', APP_TD ),
			'clear_embeds_text' => __( 'Clear Embeds (clears any previously added embeds)', APP_TD ),
			'allowed_embeds_reached_text' => __( 'No more embeds allowed', APP_TD ),
		);
		$localization = wp_parse_args( $localization, $defaults );

		wp_localize_script( 'app-media-manager', 'app_uploader_i18n', $localization );

		wp_enqueue_script('app-media-manager');

		wp_enqueue_media();
	}

	/**
	 * Outputs the media manager HTML markup.
	 *
	 * @uses do_action() Calls 'appthemes_media_manager'
	 *
	 */
	static function output_media_manager( $post_id = 0, $atts = array(), $filters = array() ) {

		// make sure we have a unique ID for each outputed file manager
		if ( empty( $atts['id'] ) ) {
			$attach_field_id = uniqid('id');
		} else {
			$attach_field_id = $atts['id'];
		}

		// parse the custom filters for the outputted media manager
		$filters = wp_parse_args( $filters, self::$default_filters );

		// media manager fieldset attributes
		$defaults = array(
			'id'			=> $attach_field_id,
			'class'			=> 'files',
			'title'			=> '',
			'upload_text'	=> __( 'Add Media', APP_TD ),
			'manage_text'	=> __( 'Manage Media', APP_TD ),
			'no_media_text'	=> __( 'No media added yet', APP_TD ),
			'attachment_ids'=> '',
			'embed_urls'	=> '',
		);
		$atts = wp_parse_args( $atts, $defaults );

		if ( ! empty( $filters['mime_types'] ) ) {

			// extract, correct and flatten the mime types
			if ( ! is_array( $filters['mime_types'] ) ) {

				// keep the original required mime types to display to the user
				$filters['file_types'] = $filters['mime_types'];

				$mime_types = explode( ',', $filters['mime_types'] );
			} else {
				$mime_types = $filters['mime_types'];

				// keep the original required mime types to display to the user
				$filters['file_types'] = implode( ',', $filters['mime_types'] );
			}
			$mime_types = appthemes_get_mime_types_for( $mime_types );
			$filters['mime_types'] = implode( ',', $mime_types );
		}

		// get all the attachments for the current post ID, if editing a post
		if ( empty( $atts['attachment_ids'] ) && $post_id ) {
			$atts['attachment_ids'] = get_post_meta( $post_id, $attach_field_id, true );
		}

		// get all the embeds for the current post ID, if editing a post
		if ( empty( $atts['embed_urls'] ) && $post_id ) {
			$atts['embed_urls'] = get_post_meta( $post_id, $attach_field_id .'_embeds', true );
		}

		$atts['button_text'] = ( ! empty( $atts['attachment_ids'] ) ? $atts['manage_text'] : $atts['upload_text']  );

		// look for a custom template before using the default one
		$located = appthemes_load_template( 'media-manager.php', array( 'atts' => $atts, 'filters' => $filters ) );

		if ( ! $located ) {
			require APP_FRAMEWORK_DIR . '/media-manager/template/media-manager.php';
		}

		do_action( 'appthemes_media_manager', $attach_field_id, $atts['attachment_ids'], $atts['embed_urls'], $filters );
	}

	/**
	 * Process all posted inputs that contain attachment ID's that need to be assigned to the post.
	 */
	static function handle_media_upload( $post_id, $fields = array(), $duplicate = false ) {

		$attach_ids_inputs = self::$attach_ids_inputs;
		$embed_url_inputs = self::$embed_url_inputs;

		if ( ! $fields ) {
			if ( isset( $_POST[ $attach_ids_inputs ] ) ) {
				$fields = $_POST[ $attach_ids_inputs ];
			}

			if ( isset( $_POST[ $embed_url_inputs ] ) ) {
				$fields = array_merge( $fields, $_POST[ $embed_url_inputs ] );
			}
		}

		if ( empty( $fields ) ) {
			return;
		}

		foreach( (array) $fields as $field ) {
			if ( empty( $_POST[ $field ] ) ) {
				continue;
			}

			if ( intval( $_POST[ $field ] ) ) {
				self::handle_media_field( $post_id, $field, $duplicate );
			} else {
				self::handle_embed_field( $post_id, $field );
			}
		}

	}

	/**
	 * Handles embeded media related posted data.
	 */
	private static function handle_embed_field( $post_id, $field) {

		if ( empty( $_POST[ $field ] ) ) {
			return;
		}

		$embeds = explode( ',', wp_strip_all_tags( $_POST[ $field ] ) );

		// store the embed url's on the post meta
		update_post_meta( $post_id, $field, $embeds );
	}

	/**
	 * Handles attached media related posted data.
	 *
	 * @uses do_action() Calls 'appthemes_handle_media_field'
	 *
	 */
	private static function handle_media_field( $post_id, $field, $leave_original = false ) {

		if ( empty( $_POST[ $field ] ) ) {
			return;
		}

		$attachments = explode( ',', wp_strip_all_tags( $_POST[ $field ] ) );

		foreach( $attachments as $attachment_id ) {

			$attachment = get_post( $attachment_id );

			if ( $attachment->post_parent != $post_id ) {

				// keeps the original attachment untouched and instead creates a new one to be attached to the post
				if ( $leave_original ) {
					$attachment->ID = 0;
				}

				$attach_id = wp_insert_attachment( $attachment, '', $post_id );
				if ( is_wp_error( $attach_id ) ) {
					continue;
				}

			} else {
				$attach_id = $attachment_id;
			}

			if ( isset( $_POST[ $field .'_meta_type' ] ) && in_array( $_POST[ $field .'_meta_type' ], array( APP_ATTACHMENT_FILE, APP_ATTACHMENT_GALLERY ) ) ) {
				$meta_type = $_POST[ $field .'_meta_type' ];
			} else {
				$meta_type = APP_ATTACHMENT_FILE;
			}

			$media[] = $attach_id;

			update_post_meta( $attach_id, '_app_attachment_type', $meta_type );
		}

		// store the attachments on the post meta
		update_post_meta( $post_id, $field, $media );

		do_action( 'appthemes_handle_media_field', $post_id, $field );
	}

	/**
	 * Outputs the hidden inputs that act as helpers for the media manager JS.
	 */
	static function output_hidden_inputs( $attach_field_id, $attachment_ids, $embed_urls, $filters ) {

		// inputs for filters used in the media manager
		$filters_inputs = array(
			'file_types',
			'file_limit',
			'file_size',
			'embed_limit',
			'mime_types',
			'meta_type',
		);

		foreach( $filters_inputs as $input ) {

			if ( ! isset( $filters[ $input ] ) ) {
				continue;
			}

			$params = array(
				'class' => $attach_field_id,
				'type' => 'hidden',
				'name' => $attach_field_id . '_' . $input,
				'value' => $filters[ $input ],
			);
			echo html( 'input', $params );
		}

		$embeds_input = $attach_field_id . '_embeds';

		// input for the attachment ID's selected by the user in the media manager
		echo html( 'input', array( 'name' => $attach_field_id, 'type' => 'hidden', 'value' => implode( ',', (array) $attachment_ids ) ) );

		// input with all the field names that contain attachment ID's
		echo html( 'input', array( 'name' => self::$attach_ids_inputs.'[]','type' => 'hidden', 'value' => $attach_field_id ) );

		// input for the embed URL's selected by the user in the media manager
		echo html( 'input', array( 'name' => $embeds_input, 'type' => 'hidden', 'value' => implode( ',', (array) $embed_urls ) ) );

		// input with all the field names that contain embed URL's
		echo html( 'input', array( 'name' => self::$embed_url_inputs.'[]','type' => 'hidden', 'value' => $embeds_input ) );
	}

	/**
	 * Refreshes the attachments/embed list based on the user selection.
	 */
	static function ajax_refresh_attachments() {

		// retrieve attachments post data
		compact( 'post_id', 'mime_types', 'meta_type', 'file_limit', 'embed_limit', $_POST );

		$attachment_ids = array();

		if ( ! empty( $post_id ) ) {
			$filters = array(
				'file_limit' => (int) $file_limit,
				'meta_type' => sanitize_text_field( $meta_type ),
				'mime_types' => sanitize_text_field( $mime_types ),
			);
			$attachment_ids = appthemes_get_post_attachments( $post_id, $filters );
			$attachment_ids = array_values( $attachment_ids );
		}

		if ( isset( $_POST['attachments'] ) ) {
			$attachment_ids = array_merge( $attachment_ids, $_POST['attachments'] );
			$attachment_ids = array_map( 'intval', $attachment_ids );
			$attachment_ids = array_unique( $attachment_ids );
		}

		if ( ! empty( $_POST['embed_urls'] ) ) {
			$posted_embed_urls = sanitize_text_field( $_POST['embed_urls'] );
			$embed_urls = explode( ',', $posted_embed_urls );
		}

		if ( ! empty( $attachment_ids ) ) {
			$attachments = appthemes_output_attachments( $attachment_ids, $params = array(), $echo = false );
			echo json_encode( array( 'output' => $attachments ) );
		}

		if ( ! empty( $embed_urls ) ) {
			$embeds = appthemes_output_embed( $embed_urls, $params = array(), $echo = false );
			echo json_encode( array( 'url' => $posted_embed_urls, 'output' => $embeds ) );
		}

		die();
	}

	/**
	 * Restrict media library to files uploaded by the current user with
	 * no parent or whose parent is the current post ID.
	 */
	static function restrict_media_library( $query ) {
		global $current_user, $wp_query;

		// make sure we're restricting the library only on the frontend media manager
		if ( empty( $_REQUEST['app_media_manager'] ) ) {
			return $query;
		}

		if ( ! current_user_can('manage_options') ) {
		   $query['author'] = $current_user->ID;

		   if ( empty( $_REQUEST['post_id'] ) ) {
			   $query['post_parent'] = 0;
		   } else {
			   $query['post_parent'] = $_REQUEST['post_id'];
		   }

		}
		return $query;
	}

	/**
	 * Validates the files the current user is trying to upload by checking their mime types
	 * and the preset file limit.
	 */
	static function validate_upload_restrictions( $file ) {

		if ( ! empty( $_POST['app_mime_types'] ) ) {

			// can be 'mime_type/extension', 'extension' or 'mime_type'
			$allowed = explode( ',', $_POST['app_mime_types'] );

			$file_type = wp_check_filetype( $file['name'] );
			$mime_type = explode( '/', $file_type['type'] );

			$not_allowed = true;

			// check if extension and mime type are allowed
			if ( in_array( $mime_type[0], $allowed ) || in_array( $file_type['type'], $allowed ) || in_array( $file_type['ext'], $allowed ) ) {
				$not_allowed = false;
			}

			if ( $not_allowed ) {

				$allowed_mime_types = get_allowed_mime_types();

				// first pass to check if the mime type is allowed
				if ( ! in_array( $file['type'], $allowed_mime_types ) ) {

					// double check if the extension is invalid by looking at the allowed extensions keys
					foreach ( $allowed_mime_types as $ext_preg => $mime_match ) {
						$ext_preg = '!^(' . $ext_preg . ')$!i';
						if ( preg_match( $ext_preg, $file_type['ext'] ) ) {
							$not_allowed = false;
							break;
						}
					}

				}

				if ( $not_allowed ) {
					$file['error'] = __( 'Sorry, you cannot upload this file type for this field.', APP_TD );
					return $file;
				}

			}

		}

		if ( ! empty( $_POST['app_file_size'] ) ) {

			$file_size = sanitize_text_field( $_POST['app_file_size'] );

			if ( $file['size'] > $file_size ) {
				$file['error'] = __( 'Sorry, you cannot upload this file as it exceeds the size limitations for this field.', APP_TD );
				return $file;
			}

		}
		return $file;
	}

}

/**
 * Outputs the media manager HTML markup.
 *
 * @param int $post_id (optional) The post ID that the media relates to
 * @param array $atts (optional) Input attributes to be passed to the media manager:
 * 			'id'			=> the input ID - name used as meta key to store the media data
 *			'class'			=> the input CSS class
 *			'title'			=> the input title
 *			'upload_text'	=> the text to be displayed on the upload button when there are no uploads yet
 *			'manage_text'	=> the text to be displayed on the upload button when uploads already exist
 *			'no_media_text'	=> the placeholder text to be displayed while there are no uploads
 *			'attachment_ids'=> default attachment ID's to be listed (int|array),
 *			'embed_urls'	=> default embed URL's to be listed (string|array),
 * @param array $filters (optional) Filters to be passed to the media manager:
 *			'file_limit'	=> file limit (0 = disable, -1 = no limit)
 *			'file_size'		=> file size (in bytes)
 *			'embed_limit'	=> embed limit (0 = disable, -1 = no limit)
 *			'meta_type'		=> APP_ATTACHMENT_FILE (default) or APP_ATTACHMENT_GALLERY ,
 *			'mime_types'	=> the mime types accepted (default is empty - accepts any mime type) (string|array)
 */
function appthemes_media_manager( $post_id = 0, $atts = array(), $filters = array() ) {
	APP_Media_Manager::output_media_manager( $post_id, $atts, $filters );
}

/**
 * Enqueues the JS scripts that output WP's media manager.
 *
 * @param array $localization (optional) The localization params to be passed to wp_localize_script()
 * 		'post_id'			=> the existing post ID, if editing a post, or 0 for new posts (required for edits if 'post_id_field' is empty)
 *		'post_id_field'		=> an input field name containing the current post ID (required for edits if 'post_id' is empty)
 *		'ajaxurl'			=> admin_url( 'admin-ajax.php', 'relative' ),
 *		'ajax_nonce'		=> wp_create_nonce('app-media-manager'),
 *		'files_limit_text'	=> the files limit text to be displayed on the upload view
 *		'files_type_text'	=> the allowed file types to be displayed on the upload view
 *		'insert_media_title'=> the insert media title to be displayed on the upload view
 *		'embed_media_title'	=> the embed media title to be displayed on the embed view
 *		'embed_limit_text'	=> the embed limit to be displayed on the embed view
 *		'clear_embeds_text' => the text for clearing the embeds to be displayed on the embed view
 *		'allowed_embeds_reached_text' => the allowed embeds warning to be displayed when users reach the max embeds allowed
 */
function appthemes_enqueue_media_manager( $localization = array() ) {
	APP_Media_Manager::enqueue_media_manager( $localization );
}

/**
 * Handles media related post data
 *
 * @param int $post_id The post ID to which the attachments will be assigned
 * @param array $fields (optional) The media fields that should be handled
 * @param bool $duplicate (optional) Should the media files be duplicated, thus keeping the original file unattached
 * @return null|bool False if no media was processed, null otherwise
 */
function appthemes_handle_media_upload( $post_id, $fields = array(), $duplicate = false ) {
	APP_Media_Manager::handle_media_upload( $post_id, $fields, $duplicate );
}

/**
 * Outputs the HTML markup for a list of attachment ID's.
 *
 * @param array $attachment_ids The list of attachment ID's to output
 * @param array $params The params to be used to output the attachments
 *		'show_description' => displays the attachment description (default is TRUE),
 *		'show_image_thumbs' => displays the attachment thumb (default is TRUE - images only, displays an icon on other mime types),
 * @param bool $echo Should the attachments be echoed or returned (default is TRUE)
 */
function appthemes_output_attachments( $attachment_ids, $params = array(), $echo = true ) {

	$defaults = array(
		'show_description' => true,
		'show_image_thumbs' => true,
	);
	$params = wp_parse_args( $params, $defaults );

	extract( $params );

	if ( empty( $attachment_ids ) ) {
		return;
	}

	$attachments = '';

	if ( ! $echo ) {
		ob_start();
	}

	foreach( $attachment_ids as $attachment_id ) {
		appthemes_output_attachment( $attachment_id, $show_description, $show_image_thumbs );
	}

	if ( ! $echo ) {
		$attachments .= ob_get_clean();
	}

	if ( ! empty( $attachments ) ) {
		return $attachments;
	}

}

/**
 * Outputs the HTML markup for a specific attachment ID.
 *
 * @param int $attachment_id The attachment ID
 * @param bool $show_description (optional) Should the attachment description be displayed?
 * @param bool $show_image_thumbs (optional) Should images be prepended with thumbs? (defaults to mime type icons)
 * @return string The HTML markup
 */
function appthemes_output_attachment( $attachment_id, $show_description = true, $show_image_thumbs = true ) {

	$file = appthemes_get_attachment_meta( $attachment_id, $show_description );

	$link = html( 'a', array(
		'href' => $file['url'],
		'title' => $file['title'],
		'alt' => $file['alt'],
		'target' => '_blank',
	), $file['title'] );

	$mime_type = explode( '/', $file['mime_type'] );

	if ( $show_description ) {
		$attachment = get_post( $attachment_id );
		$file = array_merge( $file, array(
			'caption' => $attachment->post_excerpt,
			'description' => $attachment->post_content,
		) );
		$link .= html( 'p', array( 'class' =>  'file-description' ), $file['description'] );
	}

	if ( 'image' == $mime_type[0] && $show_image_thumbs ) {
		$thumb = wp_get_attachment_image( $attachment_id, 'thumb' );

		echo html( 'div', $thumb . $link );
		return;
	}

	echo html( 'div', array(
		'class' => 'file-extension ' . appthemes_get_mime_type_icon_class( $file['mime_type'] ),
	), $link );
}

/**
 * Outputs an embeded URL or the HTML markup for a single URL or list of URL's.
 *
 * @param string|array $urls A single URL or list of URL's
 * @param array $params (optional)
 *		'embed' => true (default)|false - should the URL be automatically embed or simply outputed?
 */
function appthemes_output_embed( $urls, $params = array(), $echo = true ) {

	$defaults = array(
		'embed' => true,
	);
	$params = wp_parse_args( $params, $defaults );

	extract( $params );

	if ( empty( $urls ) ) {
		return;
	}

	$embeds = '';

	if ( ! $echo ) {
		ob_start();
	}

	foreach( (array) $urls as $url ) {
		$url = trim( $url );

		echo html( 'br', '&nbsp;' );

		if ( $embed ) {
			$oembed = wp_oembed_get( $url );
			if ( $oembed ) {
				echo $oembed;
			} else {
				echo $url;
			}
		} else {
			echo $url;
		}

		if ( ! $echo ) {
			$embeds .= ob_get_clean();
		}
	}

	if ( ! empty( $embeds ) ) {
		return $embeds;
	}
}

/**
 * Queries the database for media manager attachments.
 * Uses the meta key '_app_attachment_type' to filter the available attachment types: gallery | file
 *
 * @param int $post_id	The listing ID
 * @param array $filters (optional) Params to be used to filter the attachments query
 */
function appthemes_get_post_attachments( $post_id, $filters = array() ) {

	if ( ! $post_id ) {
		return array();
	}

	$defaults = array(
		'file_limit' => -1,
		'meta_type'	 => APP_ATTACHMENT_FILE,
		'mime_types' => '',
	);
	$filters = wp_parse_args( $filters, $defaults );

	extract( $filters );

	return get_posts( array(
		'post_type' 		=> 'attachment',
		'post_status' 		=> 'inherit',
		'post_parent' 		=> $post_id,
		'posts_per_page' 	=> $file_limit,
		'post_mime_type'	=> $mime_types,
		'orderby' 			=> 'menu_order',
		'order' 			=> 'asc',
		'meta_key'			=> '_app_attachment_type',
		'meta_value'		=> $meta_type,
		'fields'			=> 'ids',
	) );
}

/**
 * Collects and returns the meta info for a specific attachment ID.
 *
 * Meta retrieved: title, alt, url, mime type, file size
 *
 * @param int $attachment_id  The attachment ID
 * @return array Retrieves the attachment meta
 */
function appthemes_get_attachment_meta( $attachment_id ) {
	$filename = wp_get_attachment_url( $attachment_id );

	$title = trim( strip_tags( get_the_title( $attachment_id ) ) );
	$size = size_format( filesize( get_attached_file( $attachment_id ) ), 2 );
	$basename = basename( $filename );

	$meta = array (
		'title'		=> ( ! $title ? $basename : $title ),
		'alt'		=> get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
		'url' 		=> $filename,
		'mime_type' => get_post_mime_type( $attachment_id ),
		'size' 		=> $size,
	);
	return $meta;
}

/**
 * Retrieves the CSS class that should be used for a specific mime type icon.
 *
 * @uses apply_filters() Calls 'appthemes_mime_type_icon'
 *
 * @param string $mime_type
 * @return string The mime type icon CSS class
 */
function appthemes_get_mime_type_icon_class( $mime_type ) {

	if ( ! $mime_type ) {
		$mime_type = 'generic';
	}

	$file_ext_ico = array (
		'pdf'  	  	   => 'file-pdf',
		'msword'  	   => 'file-word',
		'vnd.ms-excel' => 'file-excel',
		'csv' 		   => 'file-excel',
		'image'		   => 'file-image',
		'video'		   => 'file-video',
		'audio'		   => 'file-audio',
		'other'	   	   => 'file-other',
	);

	$mime_type = explode( '/' , $mime_type );

	if ( is_array( $mime_type ) ) {
		// simplify the mime match for image types by using the 'image' part (i.e: image/png, image/jpg, etc)
		if ( in_array( $mime_type[0], array( 'video', 'audio', 'image' ) ) ) {
			$mime_type = $mime_type[0];
		} else {
			$mime_type = $mime_type[1];
		}

	}

	if ( ! isset( $file_ext_ico[ $mime_type ] ) ) {
		$mime_type = 'other';
	}
	return apply_filters( 'appthemes_mime_type_icon', $file_ext_ico[ $mime_type ], $mime_type );
}

/**
 * Compares full/partial mime types or file extensions and tries to retrieve a list of related mime types.
 *
 * examples:
 * 'image'	=> 'image/png', 'image/gif', etc
 * 'pdf'	=> 'application/pdf'
 *
 * @param mixed $mime_types_ext The full/partial mime type or file extension to search
 * @return array The list of mime types if found, or an empty array
 */
function appthemes_get_mime_types_for( $mime_types_ext ) {

	$normalized_mime_types = array();

	$all_mime_types = wp_get_mime_types();

	// sanitize the file extensions/mime types
	$mime_types_ext = array_map( 'trim', (array) $mime_types_ext );
	$mime_types_ext = preg_replace( "/[^a-z\/]/i", '', $mime_types_ext );

	foreach( $mime_types_ext as $mime_type_ext ) {

		if ( isset( $all_mime_types[ $mime_type_ext ] ) ) {
			$normalized_mime_types[] = $all_mime_types[ $mime_type_ext ];
		} elseif( in_array( $mime_type_ext, $all_mime_types ) ) {
			$normalized_mime_types[] = $mime_type_ext;
		} else {

			// try to get the full mime type from extension (e.g.: png, .jpg, etc ) or mime type parts (e.g.: image, application)
			foreach ( $all_mime_types as $exts => $mime ) {
				$mime_parts = explode( '/', $mime );

				if ( preg_match( "!({$exts})$|({$mime_parts[0]})!i", $mime_type_ext ) ) {
					$normalized_mime_types[] = $mime;
				}
			}
		}
	}
	return $normalized_mime_types;
}

/**
 * Meta cababilities for uploading files.
 *
 * Users need the 'upload_media' cap to be able to upload files
 */
function _appthemes_media_capabilities( $caps, $cap, $user_id, $args ) {

	switch( $cap ) {
		case 'upload_files':
			if ( user_can( $user_id, 'upload_media' ) && ! empty( $_REQUEST['app_media_manager'] ) ) {
				$caps = array( 'exist' );
			}
			break;

	}
	return $caps;
}
