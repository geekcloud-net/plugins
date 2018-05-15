<?php

/**
 * Class TCB_Lightbox
 *
 * This file is only included when TCB is installed as a stand-alone plugin
 */
class TCB_Lightbox extends TCB_Post {
	public function get_html() {
		if ( ! $this->post ) {
			return '';
		}
		$lightbox_id      = $this->post->ID;
		$lightbox_content = str_replace( ']]>', ']]&gt;', apply_filters( 'the_content', $this->post_content ) );
		$config           = $this->globals();

		return sprintf(
			'<div%s id="tve_thrive_lightbox_%s"><div class="tve_p_lb_overlay" data-style="%s" style="%s"%s></div>' .
			'<div class="tve_p_lb_content bSe cnt%s tcb-lp-lb" style="%s"%s><div class="tve_p_lb_inner" id="tve-p-scroller" style="%s"><article>%s</article></div>' .
			'<a href="javascript:void(0)" class="tve_p_lb_close%s" style="%s"%s title="Close">x</a></div></div>',
			empty( $data['visible'] ) ? ' style="display: none"' : '',
			$lightbox_id,
			$config['overlay']['css'],
			$config['overlay']['css'],
			$config['overlay']['custom_color'],
			$config['content']['class'],
			$config['content']['css'],
			$config['content']['custom_color'],
			$config['inner']['css'],
			$lightbox_content,
			$config['close']['class'],
			$config['close']['css'],
			$config['close']['custom_color']
		);
	}

	/**
	 * Reads the globals saved in TCB2 format
	 *
	 * @param array $config
	 *
	 * @return array
	 */
	public function globals_v2( $config ) {
		return array(
			'overlay' => array(
				'css'          => '',
				'custom_color' => ! empty( $config['overlay_css'] ) ? ' data-css="' . $config['overlay_css'] . '"' : '',
			),
			'content' => array(
				'css'          => '',
				'class'        => '',
				'custom_color' => ! empty( $config['content_css'] ) ? ' data-css="' . $config['content_css'] . '"' : '',
			),
			'close'   => array(
				'css'          => '',
				'class'        => '',
				'custom_color' => ! empty( $config['close_css'] ) ? ' data-css="' . $config['close_css'] . '"' : '',
			),
			'inner'   => array(
				'css' => '',
			),
		);
	}

	/**
	 * Fetches Lightbox global settings
	 * Checks if this lightbox has content stored in TCB2
	 *
	 * @return array
	 */
	public function globals() {
		$config = $this->meta( 'tve_globals' );
		if ( isset( $config['content_css'] ) || isset( $config['close_css'] ) || isset( $config['overlay_css'] ) ) {
			/* this is a tcb2-style lightbox */

			return $this->globals_v2( $config );
		}

		$html = array(
			'overlay' => array(
				'css'          => empty( $config['l_oo'] ) ? '' : 'opacity:' . $config['l_oo'],
				'custom_color' => empty( $config['l_ob'] ) ? '' : ' data-tve-custom-colour="' . $config['l_ob'] . '"',
			),
			'content' => array(
				'custom_color' => empty( $config['l_cb'] ) ? '' : ' data-tve-custom-colour="' . $config['l_cb'] . '"',
				'class'        => empty( $config['l_ccls'] ) ? '' : ' ' . $config['l_ccls'],
				'css'          => '',
			),
			'inner'   => array(
				'css' => '',
			),
			'close'   => array(
				'custom_color' => '',
				'class'        => '',
				'css'          => '',
			),
		);

		if ( ! empty( $config['l_cimg'] ) ) { // background image
			$html['content']['css'] .= "background-image:url('{$config['l_cimg']}');background-repeat:no-repeat;background-size:cover;";
		} elseif ( ! empty( $config['l_cpat'] ) ) {
			$html['content']['css'] .= "background-image:url('{$config['l_cpat']}');background-repeat:repeat;";
		}

		if ( ! empty( $config['l_cbs'] ) ) { // content border style
			$html['content']['class'] .= ' ' . $config['l_cbs'];
			$html['close']['class']   .= ' ' . $config['l_cbs'];
		}

		if ( ! empty( $config['l_cbw'] ) ) { // content border width
			$html['content']['css'] .= "border-width:{$config['l_cbw']};";
			$html['close']['css']   .= "border-width:{$config['l_cbw']};";
		}

		if ( ! empty( $config['l_cmw'] ) ) { // content max width
			$html['content']['css'] .= "max-width:{$config['l_cmw']}";
		}

		// Close Custom Color settings
		$html['close']['custom_color'] = empty( $config['l_ccc'] ) ? '' : ' data-tve-custom-colour="' . $config['l_ccc'] . '"';

		return $html;
	}

	/**
	 * Outputs the editor / preview page for a Thrive Lightbox
	 */
	public function output_layout() {
		if ( is_editor_page() ) {
			tve_enqueue_style( 'tve_lightbox_post', tve_editor_css() . '/editor_lightbox.css' );
		}

		/**
		 * Fix added by Paul McCarthy 16th October 2014 - added to solve THesis Child themes not loading CSS in Thrive lightboxes
		 * Thesis v 2.1.9 loads style sheets for their child themes with this:- add_filter('template_include', array($this, '_skin'));
		 * The filter isn't applied when the content builder lightbox is loaded because of our template_redirect filter
		 * This function checks if the theme is Thesis, if so it checks for the existance of the css.css file that all Thesis child themes should have
		 * If the file is found, it enqueuest the stylesheet in both editor and front end mode.
		 */
		if ( wp_get_theme() == 'Thesis' && defined( 'THESIS_USER_SKIN_URL' ) && file_exists( THESIS_USER_SKIN . '/css.css' ) ) {
			wp_enqueue_style( 'tve_thesis_css', THESIS_USER_SKIN_URL . '/css.css' );
		}

		$landing_page_dir = plugin_dir_path( dirname( dirname( __FILE__ ) ) ) . 'landing-page';

		if ( $for_landing_page = $this->meta( 'tve_lp_lightbox' ) ) {
			if ( tve_is_cloud_template( $for_landing_page ) ) {
				$config   = tve_get_cloud_template_config( $for_landing_page, false );
				$css_file = 'templates/css/' . $for_landing_page . '_lightbox.css';
				/* only enqueue CSS for v < 2 */
				if ( (int) $config['LP_VERSION'] !== 2 && is_file( tcb_get_cloud_base_path() . $css_file ) ) {
					/* load up the lightbox style for this landing page */
					tve_enqueue_style( 'thrive_landing_page_lightbox', tcb_get_cloud_base_url() . $css_file );
				}
			} else {
				if ( is_file( $landing_page_dir . '/templates/css/' . $for_landing_page . '_lightbox.css' ) ) {
					/* load up the lightbox style for this landing page */
					tve_enqueue_style( 'thrive_landing_page_lightbox', TVE_LANDING_PAGE_TEMPLATE . '/css/' . $for_landing_page . '_lightbox.css' );
				}
			}
		}

		tcb_template( 'layouts/editor-lightbox', array(
			'lightbox' => $this,
		) );
	}

	/**
	 * Generate and save a new Thrive Lightbox
	 *
	 * @param string $title           lightbox title
	 * @param string $tcb_content
	 * @param array  $tve_globals     tve_globals array to save for the lightbox
	 * @param array  $extra_meta_data array of key => value pairs, each will be saved in a meta field for the lightbox
	 *
	 * @return int the saved lightbox id
	 */
	public static function create( $title = '', $tcb_content = '', $tve_globals = array(), $extra_meta_data = array() ) {
		/* just to make sure that our content filter does not get applied when inserting a (possible) new lightbox */
		$GLOBALS['TVE_CONTENT_SKIP_ONCE'] = true;
		$lightbox_id                      = wp_insert_post( array(
			'post_content' => '',
			'post_title'   => $title,
			'post_status'  => 'publish',
			'post_type'    => 'tcb_lightbox',
		) );
		foreach ( $extra_meta_data as $meta_key => $meta_value ) {
			update_post_meta( $lightbox_id, $meta_key, $meta_value );
		}

		update_post_meta( $lightbox_id, 'tve_updated_post', $tcb_content );
		update_post_meta( $lightbox_id, 'tve_globals', $tve_globals );

		unset( $GLOBALS['TVE_CONTENT_SKIP_ONCE'] );

		return $lightbox_id;
	}

	/**
	 * Updates a Thrive Lightbox
	 *
	 * @param int $lightbox_id local lightbox post id
	 * @param string $title lightbox title
	 * @param string $tcb_content
	 * @param array $tve_globals tve_globals array to save for the lightbox
	 * @param array $extra_meta_data array of key => value pairs, each will be saved in a meta field for the lightbox
	 *
	 * @return int the saved lightbox id
	 */
	public static function update( $lightbox_id, $title = '', $tcb_content = '', $tve_globals = array(), $extra_meta_data = array() ) {

		/* just to make sure that our content filter does not get applied when inserting a (possible) new lightbox */
		$GLOBALS['TVE_CONTENT_SKIP_ONCE'] = true;

		$post_data = array(
			'post_content' => '',
			'post_title'   => $title,
			'post_status'  => 'publish',
			'post_type'    => 'tcb_lightbox',
			'ID'           => $lightbox_id,
		);

		wp_update_post( $post_data );

		foreach ( $extra_meta_data as $meta_key => $meta_value ) {
			update_post_meta( $lightbox_id, $meta_key, $meta_value );
		}

		update_post_meta( $lightbox_id, 'tve_updated_post', $tcb_content );
		update_post_meta( $lightbox_id, 'tve_globals', $tve_globals );

		unset( $GLOBALS['TVE_CONTENT_SKIP_ONCE'] );

		return $lightbox_id;
	}
}

/**
 * Instantiates a new TCB_Lightbox helper class
 *
 * @param null|mixed $post_id
 *
 * @return TCB_Lightbox
 */
function tcb_lightbox( $post_id = null ) {
	return new TCB_Lightbox( $post_id );
}

/**
 * Registers the tcb_lightbox post type - only available when TCB is installed as a separate plugin
 */
function tcb_lightbox_init() {
	register_post_type( 'tcb_lightbox', array(
		'labels'              => array(
			'name'          => __( 'Thrive Lightboxes', 'thrive-cb' ),
			'singular_name' => __( 'Thrive Lightbox', 'thrive-cb' ),
			'add_new_item'  => __( 'Add New Thrive Lightbox', 'thrive-cb' ),
			'edit_item'     => __( 'Edit Thrive Lightbox', 'thrive-cb' ),
		),
		'exclude_from_search' => true,
		'public'              => true,
		'has_archive'         => false,
		'rewrite'             => false,
		'show_in_nav_menus'   => false,
		'show_in_menu'        => is_plugin_active( 'thrive-visual-editor/thrive-visual-editor.php' ),
	) );
}

/**
 * Registers the tcb_lightbox post type
 */
add_action( 'init', 'tcb_lightbox_init' );
