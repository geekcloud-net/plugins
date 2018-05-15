<?php
/**
 * Slider
 *
 * @package Framework\Slider
 */

add_action( 'after_setup_theme', '_appthemes_load_slider', 999 );
add_action( 'appthemes_first_run', 'appthemes_slider_init_image_size' );
add_filter( 'intermediate_image_sizes_advanced', 'appthemes_slider_set_image_crop' );


function _appthemes_load_slider() {
	if ( ! current_theme_supports( 'app-slider' ) )
		return;

	list( $args ) = get_theme_support( 'app-slider' );

	$defaults = array (
		'enqueue_scripts' => true,
	);

	$args = wp_parse_args( $args, $defaults );

	if ( $args['enqueue_scripts'] )
		add_action( 'template_redirect', 'appthemes_slider_init_scripts' );
}

function appthemes_slider_enqueue_scripts( $script_uri = '' ) {
	if ( ! current_theme_supports( 'app-slider' ) )
		return;

	$script_uri = !empty( $script_uri ) ? $script_uri : APP_FRAMEWORK_URI . '/includes/slider/slider.js';
	wp_enqueue_script (
		'app-slider',
		$script_uri,
		array( 'jquery' ),
		'1.0'
	);
}

function appthemes_slider_init_image_size() {
	if ( ! current_theme_supports( 'app-slider' ) )
		return;

	list( $args ) = get_theme_support( 'app-slider' );

	$defaults = array (
		'height' => 300,
		'width' => 475,
	);

	$args = wp_parse_args( $args, $defaults );

	$size = apply_filters( 'appthemes_slider_image_size', array( 'width' => $args['width'], 'height' => $args['height'] ) );

	update_option( 'app_slider_size_w', $size['width'] );
	update_option( 'app_slider_size_h', $size['height'] );
	update_option( 'app_slider_crop', true );
}

function appthemes_slider_set_image_crop( $sizes ) {
	$sizes['app_slider']['crop'] = true;
	return $sizes;
}

class APP_Slider {

	public $id;
	public $slider_class;

	public $mime_groups;
	public $image_mime_types;
	public $video_mime_types;
	public $embed_video_mime_types;

	public $width;
	public $height;

	function __construct( $args = array() ) {
		$defaults = array(
			'mime_groups' => array( 'image', 'video', 'video_iframe_embed' ),
			'image_mime_types' => array( 'image/png', 'image/jpeg', 'image/gif' ),
			'video_mime_types' => array( 'video/mp4' ),
			'video_iframe_embed_mime_types' => array('video/youtube-iframe-embed', 'video/vimeo-iframe-embed', 'video/iframe-embed'),

			'id' => 'app-slider',
			'slider_class' => '',
			'video_embed_class' => '',

			'height' => 300,
			'width' => 475,

			'attachment_image_size' => 'app_slider',
			'image_a_attr' => array (),
		);

		$args = wp_parse_args( $args, $defaults );

		foreach( $args as $arg_k => $arg_v ) {
			$this->{$arg_k} = $arg_v;
		}
	}

	function get_attachments( $post_id = 0 ) {
		$post_id = $post_id ? $post_id : get_the_ID();

		$posts = get_posts( array('post_parent' => $post_id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'nopaging' => true, 'orderby' => 'menu_order', 'order' => 'asc' ) );

		return $posts;
	}

	function display() {
		$attachments = $this->get_attachments();

		$attachments_html = '';
		$id = 0;
		foreach ( $attachments as $attachment ) {
			$mime_display = $this->display_mime_type( $attachment );
			if( empty( $mime_display ) )
				continue;
			$attachments_html .= html( 'div', array( 'class' => 'attachment', 'id' => 'attachment_' . $id ), $mime_display );
			$id++;
		}

		$attachments_slider = html( 'div', array( 'class' => 'attachments-slider', 'id' => 'attachments-slider', 'data-position' => 0 ), $attachments_html );
		$attachments_container = html( 'div', array( 'class' => 'attachments'), $attachments_slider );

		$arrow_left = html( 'div', array( 'class' => 'left-arrow' ) );
		$arrow_right = html( 'div', array( 'class' => 'right-arrow' ) );

		$slider = html( 'div', array( 'id' => $this->id, 'class' => $this->slider_class ), $arrow_left, $arrow_right, $attachments_container );
		$slider .= $this->scripts();
		return $slider;
	}

	function display_mime_type( $attachment ) {

		$mime_type = $attachment->post_mime_type;
		foreach( $this->mime_groups  as $mime_group ) {
			$html = apply_filters( 'appthemes_slider_attachment_mime_group-' . $mime_group, null, $attachment );
			if ( !is_null( $html ) )
					return $html;

			if ( in_array( $mime_type, $this->{ $mime_group . '_mime_types' } ) ) {
				$method = 'display_' . $mime_group;
				$html = $this->$method( $attachment );
				return $html;
			}
		}

		$html = apply_filters( 'appthemes_slider_attachment_mime_type_unknown', '', $attachment );
		return $html;
	}

	function display_image( $attachment ) {
		return html( 'a', $this->image_a_attr + array(
			'href' => wp_get_attachment_url( $attachment->ID ),
			'title' => trim( strip_tags( get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ) ) )
		), wp_get_attachment_image( $attachment->ID, $this->attachment_image_size ) );
	}

	function display_video( $attachment ) {
		$args = array(
			'mp4' => $attachment->guid,
			'width' => $this->width,
			'height' => $this->height,
		);

		// Opera 12 (Presto, pre-Chromium) fails to load ogv properly
		// when combined with ME.js. Works fine in Opera 15.
		// Don't serve ogv to Opera 12 to avoid complete brokeness.
		if ( $GLOBALS['is_opera'] )
		unset( $args['ogv'] );

		return wp_video_shortcode( $args );
	}

	function display_video_iframe_embed( $attachment ) {
		$iframe = html( 'iframe webkitallowfullscreen mozallowfullscreen allowfullscreen ', array(
				'src' => $attachment->guid,
				'frameborder' => 0,
				'style' => 'display:block;border:0;',
				'height' => $this->height,
				'width' => $this->width
		) );

		return html( 'div',  array(
				'class' => 'slider-video-iframe slider-video-iframe-' . esc_attr( $attachment->post_mime_type ) . ' ' . $this->video_embed_class,
				'style' => 'height:' . $this->height . 'px;width:' . $this->width . 'px;margin:0;padding:0;'
		), $iframe );
	}

	function scripts() {
		ob_start();
	?>
		<script type="text/javascript">
		jQuery(function() {

			jQuery('#<?php echo esc_js( $this->id ); ?>').appthemes_slider();
		});
		</script>
	<?php
		return ob_get_clean();
	}
}
