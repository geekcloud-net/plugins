<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Main class for handling the editor page related stuff
 *
 * Class TCB_Editor_Page
 */
class TCB_Editor {
	/**
	 * Instance
	 *
	 * @var TCB_Editor
	 */
	private static $instance;

	/**
	 * Post being edited
	 *
	 * @var WP_Post
	 */
	protected $post;

	/**
	 * If the current post can be edited
	 *
	 * @var bool
	 */
	protected $can_edit_post = null;

	/**
	 * TCB Elements Class
	 *
	 * @var TCB_Elements
	 */
	public $elements;

	/**
	 * TCB_Editor constructor.
	 */
	final private function __construct() {
		$this->elements = tcb_elements();
	}

	/**
	 * Singleton instance method
	 *
	 * @return TCB_Editor
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Setup actions for the main editor frame
	 */
	public function setup_main_frame() {
		remove_all_actions( 'wp_head' );

		remove_all_actions( 'wp_enqueue_scripts' );
		remove_all_actions( 'wp_print_scripts' );
		remove_all_actions( 'wp_print_footer_scripts' );
		remove_all_actions( 'wp_footer' );
		remove_all_actions( 'wp_print_styles' );

		add_action( 'wp_head', 'wp_enqueue_scripts' );
		add_action( 'wp_head', 'wp_print_styles' );
		add_action( 'wp_print_footer_scripts', '_wp_footer_scripts' );

		add_action( 'wp_footer', array( $this, 'print_footer_templates' ), 1 );
		add_action( 'wp_footer', 'wp_auth_check_html' );

		add_action( 'wp_footer', '_wp_footer_scripts' );
		add_action( 'wp_footer', 'wp_print_footer_scripts' );
		add_action( 'wp_enqueue_scripts', array( $this, 'main_frame_enqueue' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'main_frame_dequeue' ), PHP_INT_MAX );
	}

	/**
	 * Template redirect hook for the main window ( containing the control panel and the post content iframe )
	 */
	public function hook_template_redirect() {

		if ( ! $this->is_main_frame() ) {
			return;
		}

		$this->setup_main_frame();

		/**
		 * Action hook.
		 * Allows executing 3rd party code in this point. Example: dequeue any necessary resources from the editor main page
		 */
		do_action( 'tcb_hook_template_redirect' );

		tcb_template( 'layouts/editor', $this );
		exit();
	}

	/**
	 * Check if the current screen is the main frame for the editor ( containing the control panel and the content frame )
	 */
	public function is_main_frame() {
		if ( empty( $_REQUEST[ TVE_EDITOR_FLAG ] ) || ! apply_filters( 'tcb_is_editor_page', is_singular() ) ) {
			return false;
		}
		/**
		 * If we are in the iframe request, we are not in the main editor page request
		 */
		if ( isset( $_REQUEST[ TVE_FRAME_FLAG ] ) ) {
			return false;
		}

		if ( ! $this->can_edit_post() ) { // If this isn't a TCB editable post.
			return false;
		}

		return true;
	}

	/**
	 * Check capabilities and regular conditions for the editing screen
	 *
	 * @return bool
	 */
	public function can_edit_post() {
		if ( isset( $this->can_edit_post ) ) {
			return $this->can_edit_post;
		}
		$this->post = get_post();
		if ( ! $this->post ) {
			return $this->can_edit_post = false;
		}

		if ( ! tve_is_post_type_editable( $this->post->post_type ) || ! current_user_can( 'edit_posts' ) ) {
			return $this->can_edit_post = false;
		}

		$page_for_posts = get_option( 'page_for_posts' );
		if ( $page_for_posts && (int) $this->post->ID === (int) $page_for_posts ) {
			return $this->can_edit_post = false;
		}

		if ( ! tve_tcb__license_activated() && ! apply_filters( 'tcb_skip_license_check', false ) ) {
			return $this->can_edit_post = false;
		}

		return $this->can_edit_post = apply_filters( 'tcb_user_can_edit', true, $this->post->ID );
	}

	/**
	 * Check if the current screen (request) if the inner contents iframe ( the one displaying the actual post content )
	 */
	public function is_inner_frame() {
		if ( apply_filters( 'tcb_is_inner_frame_override', false ) ) {
			return true;
		}

		if ( empty( $_REQUEST[ TVE_EDITOR_FLAG ] ) || ! apply_filters( 'tcb_is_editor_page', is_singular() ) || empty( $_REQUEST[ TVE_FRAME_FLAG ] ) ) {
			return false;
		}
		if ( ! $this->can_edit_post() ) {
			return false;
		}

		/**
		 * The iframe receives a query string variable
		 */
		if ( ! wp_verify_nonce( $_REQUEST[ TVE_FRAME_FLAG ], TVE_FRAME_FLAG ) ) {
			return false;
		}

		add_filter( 'body_class', array( $this, 'inner_frame_body_class' ) );

		return true;
	}

	/**
	 * Adds the required CSS classes to the body of the inner html document
	 *
	 * @param array $classes Classes to be added on the iframe body.
	 *
	 * @return array
	 */
	public function inner_frame_body_class( $classes ) {
		$classes [] = 'tve_editor_page';
		$classes [] = 'preview-desktop';

		return $classes;
	}

	/**
	 * Enqueue scripts and styles for the main frame
	 */
	public function main_frame_enqueue() {
		/**
		 * The constant should be defined somewhere in wp-config.php file
		 */
		$js_suffix = defined( 'TVE_DEBUG' ) && TVE_DEBUG ? '.js' : '.min.js';

		$this->enqueue_media();
		// WP colour picker
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'jquery-ui-autocomplete' );
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'tcb-velocity', TVE_DASH_URL . '/js/dist/velocity.min.js' );
		wp_enqueue_script( 'tcb-leanmodal', TVE_DASH_URL . '/js/dist/leanmodal.min.js' );

		wp_enqueue_script( 'tcb-scrollbar', tve_editor_url() . '/editor/js/libs/jquery.scrollbar.min.js' );
		wp_enqueue_script( 'tcb-ace', tve_editor_url() . '/editor/js/libs/ace/ace.js' );

		$main_deps = apply_filters( 'tve_main_js_dependencies', array(
			'jquery',
			'jquery-ui-draggable',
			'jquery-ui-autocomplete',
			'jquery-ui-datepicker',
			'jquery-effects-core',
			'jquery-effects-slide',
			'tcb-leanmodal',
			'tcb-velocity',
			'backbone',
		) );

		tve_enqueue_script( 'tve-main', tve_editor_js() . '/main' . $js_suffix, $main_deps, false, true );

		tve_enqueue_style( 'tve2_editor_style', tve_editor_css() . '/main/style.css' );

		/* wp-auth-login */
		wp_enqueue_script( 'wp-auth-check' );
		wp_enqueue_style( 'wp-auth-check' );

		TCB_Icon_Manager::enqueue_icon_pack();

		/* Font family */
		tve_enqueue_style( 'tve-editor-font', 'https://fonts.googleapis.com/css?family=Roboto:400,400i,700' );

		//Default datepicker design
		wp_enqueue_style( 'jquery-ui-datepicker', tve_editor_css() . '/jquery-ui-1.10.4.custom.min.css' );

		if ( tve_check_if_thrive_theme() ) {
			/* include the css needed for the shortcodes popup (users are able to insert Thrive themes shortcode inside the WP editor on frontend) - using the "Insert WP Shortcode" element */
			tve_enqueue_style( 'tve_shortcode_popups', tve_editor_css() . '/thrive_shortcodes_popup.css' );
		}

		/*Include Select2*/
		wp_enqueue_script( 'tcb-select2-script', TVE_DASH_URL . '/js/dist/select2.min.js' );

		/**
		 * Action filter.
		 * Used to enqueue scripts from other products
		 */
		do_action( 'tcb_main_frame_enqueue' );

		wp_localize_script( 'tve-main', 'tcb_main_const', $this->main_frame_localize() );
	}

	/**
	 * Dequeue conflicting scripts from the main frame
	 */
	public function main_frame_dequeue() {
		wp_dequeue_script( 'membermouse-blockUI' );
		wp_deregister_script( 'membermouse-blockUI' );
	}

	/**
	 * Include backbone templates and let other add their own stuff
	 */
	public function print_footer_templates() {
		$templates = tve_dash_get_backbone_templates( TVE_TCB_ROOT_PATH . 'inc/backbone', 'backbone' );

		$templates = apply_filters( 'tcb_backbone_templates', $templates );

		tve_dash_output_backbone_templates( $templates, 'tve-' );
		do_action( 'tve_editor_print_footer_scripts' );
		$this->add_footer_modals();
	}

	/**
	 * Print editor modals
	 */
	private function add_footer_modals() {

		$path  = TVE_TCB_ROOT_PATH . 'inc/views/modals/';
		$files = array_diff( scandir( $path ), array( '.', '..' ) );
		foreach ( $files as $key => $file ) {
			$files[ $key ] = $path . $file;
		}

		$files = apply_filters( 'tcb_modal_templates', $files );

		tcb_template( 'modals', array( 'post' => $this->post, 'files' => $files ) );
	}

	/**
	 * Javascript localization for the main TCB frame
	 *
	 * @return array
	 */
	public function main_frame_localize() {
		$admin_base_url = admin_url( '/', is_ssl() ? 'https' : 'admin' );
		// For some reason, the above line does not work in some instances.
		if ( is_ssl() ) {
			$admin_base_url = str_replace( 'http://', 'https://', $admin_base_url );
		}

		$fm = new TCB_Font_Manager();

		$post = tcb_post();

		$data = array(
			'frame_uri'                     => $this->inner_frame_url(),
			'plugin_url'                    => tve_editor_url() . '/',
			'nonce'                         => wp_create_nonce( TCB_Editor_Ajax::NONCE_KEY ),
			'rest_nonce'                    => wp_create_nonce( 'wp_rest' ),
			'userkey'                       => tcb_user_generate_editor_key(),
			'ajax_url'                      => $admin_base_url . 'admin-ajax.php',
			'post'                          => $this->post,
			'elements'                      => $this->elements->js(),
			'tpl_categ'                     => $this->elements->user_templates_category(),
			'theme_dependency'              => get_post_meta( $this->post->ID, 'tve_disable_theme_dependency', true ),
			'options'                       => $this->elements->component_options(),
			'fonts'                         => $fm->all_fonts(),
			'landing_page'                  => $post->is_landing_page(),
			'tve_global_scripts'            => $this->post_global_scripts( $post ),
			'templates_path'                => TVE_LANDING_PAGE_TEMPLATE,
			'dash_url'                      => TVE_DASH_URL,
			'pinned_category'               => $this->elements->pinned_category,
			'social_fb_app_id'              => tve_get_social_fb_app_id(),
			'api_connections'               => Thrive_Dash_List_Manager::getAvailableAPIs( true, array( 'recaptcha', 'email', 'social' ), true ),
			'colors'                        => array(
				'favorites'  => get_option( 'thrv_custom_colours', array() ),
				/**
				 * Filter that allows adding custom color sets to the color-picker's palette
				 */
				'color_sets' => apply_filters( 'tcb_color_sets', array(
					array(
						'title'  => 'Default colors set',
						'colors' => array( '#2ecc71', '#2196F3', '#1abc9c', '#673AB7', '#F44336', ' #FF9800', '#292929', '#f6f7f9' ),
					),
				) ),
			),
			/**
			 * Filter tcb_js_translate allows adding javascript translations to the editor page ( main editor panel ).
			 */
			'i18n'                          => apply_filters( 'tcb_js_translate', require TVE_TCB_ROOT_PATH . 'inc/i18n.php' ),
			'content_templates'             => $this->elements->get( 'ct' ) ? $this->elements->get( 'ct' )->get_list() : array(),
			/**
			 * Page events
			 */
			'page_events'                   => $post->meta( 'tve_page_events', null, true, array() ),
			/**
			 * Globals for the current post / page
			 */
			'tve_globals'                   => $post->meta( 'tve_globals', null, true, array( 'e' => 1 ) ),
			'icon_pack_css'                 => $this->icon_pack_css(),
			'cpanel_attr'                   => tve_cpanel_attributes(),
			'has_non_landing_page_settings' => apply_filters( 'tve_post_having_non_lp_settings', array( 'post', 'page' ) ),
			'tcb_selection_root'            => apply_filters( 'tcb_selection_root', $post->is_lightbox() || $post->is_landing_page() ? 'body' : '' ),
		);

		/** Do not localize anything that's not necessary */
		if ( $this->can_use_landing_pages() ) {
			$data['lp_templates']       = class_exists( 'TCB_Landing_Page' ) ? TCB_Landing_Page::templates_v2() : array();
			$data['saved_lp_templates'] = tve_landing_pages_load();
			$data['cloud_lp_templates'] = function_exists( 'tve_get_cloud_templates' ) ? tve_get_cloud_templates() : array();
		}

		$data['show_more_tag'] = apply_filters( 'tcb_show_more_tag', ! $data['landing_page'] && ! $this->is_lightbox() && ! $this->is_page() );
		if ( empty( $data['show_more_tag'] ) ) {
			unset( $data['elements']['moretag'] );
		}

		/**
		 * Filter tcb_main_frame_localize. Allows manipulating the javascript data from the main editor frame.
		 */
		$data = apply_filters( 'tcb_main_frame_localize', $data );

		return $data;
	}

	/**
	 * Render sidebar menu that contains the elements, components and settings
	 */
	public function render_menu() {
		tcb_template( 'control-panel.php', $this );
	}

	/**
	 * Output the inner control panel menus for elements ( menus for each element )
	 */
	public function inner_frame_menus() {
		/**
		 * This is called in the footer. There are some plugins that query posts in the footer,
		 * changing the global query. This makes sure the global query is reset to its initial state
		 */
		wp_reset_query();

		if ( ! $this->is_inner_frame() ) {
			return;
		}

		tcb_template( 'inner.php' );

		/**
		 * Output the editor page SVG icons
		 */
		$this->output_editor_svg();
	}

	/**
	 * Clean up inner frame ( e.g. remove admin menu )
	 */
	public function clean_inner_frame() {
		if ( ! $this->is_inner_frame() ) {
			return;
		}
		add_filter( 'show_admin_bar', '__return_false' );

		// membermouse admin bar
		global $mmplugin;
		if ( ! empty( $mmplugin ) && is_object( $mmplugin ) ) {
			remove_action( 'wp_head', array( $mmplugin, 'loadPreviewBar' ) );
		}
	}

	/**
	 * Get the inner frame URL
	 *
	 * @return string
	 */
	public function inner_frame_url() {
		/**
		 * SUPP-3569 Filter - Allows modifying the request_uri base on servers that have a custom setup (e.g. reverse proxies)
		 */
		$current_uri = apply_filters( 'tcb_frame_request_uri', $_SERVER['REQUEST_URI'] );

		return add_query_arg( TVE_FRAME_FLAG, wp_create_nonce( TVE_FRAME_FLAG ), $current_uri );
	}

	/**
	 * Output the SVG file for the editor page - to have icons in the inner frame also.
	 */
	public function output_editor_svg() {
		include TVE_TCB_ROOT_PATH . 'editor/css/fonts/editor-page.svg';

		do_action( 'tcb_output_extra_editor_svg' );
	}

	/**
	 * Enqueue wp media scripts / styles and solve some issues with 3rd party plugins.
	 */
	public function enqueue_media() {
		/** some themes have hooks defined here, which rely on functions defined only in the admin part - these will not be defined on frontend */
		remove_all_filters( 'media_view_settings' );
		remove_all_actions( 'print_media_templates' );
		// enqueue scripts for tapping into media thickbox
		wp_enqueue_media();
	}

	/**
	 * Checks if the current post / page can have page events
	 *
	 * @return bool
	 */
	public function can_use_page_events() {
		if ( ! $this->post ) {
			return false;
		}

		/**
		 * TODO: implement filter in TU and TL
		 */
		return apply_filters( 'tcb_can_use_page_events', $this->post->post_type !== 'tcb_lightbox' );
	}

	/**
	 * Checks if the current item being edited allows having the "Template setup" tab in the sidebar
	 */
	public function has_templates_tab() {
		if ( ! $this->post ) {
			return false;
		}

		/**
		 * Filter that allows one to use a landing page template on another custom post type
		 */
		return apply_filters( 'tcb_has_templates_tab', true );
	}

	/**
	 * Allows the plugins to enable / disable Revision Manager Setting
	 * By default is enabled.
	 *
	 * @return bool
	 */
	public function has_revision_manager() {
		if ( ! $this->post ) {
			return false;
		}

		/**
		 * Filter that allows plugins to enable / disable revision manager
		 */
		return apply_filters( 'tcb_has_revision_manager', true );
	}

	/**
	 * Checks if the item being edited allows landing page templates. For now, this is only true for pages
	 */
	public function can_use_landing_pages() {
		if ( ! $this->post ) {
			return false;
		}

		/**
		 * Filter that allows one to use a landing page template on another custom post type
		 */
		return apply_filters( 'tcb_can_use_landing_pages', $this->post->post_type === 'page' );
	}

	/**
	 * Checks if the user is currently editing a Thrive Lightbox
	 */
	public function is_lightbox() {
		if ( ! $this->post ) {
			return false;
		}

		return $this->post->post_type === 'tcb_lightbox';
	}

	/**
	 * Checks if the user is currently editing a page
	 *
	 * @return bool
	 */
	public function is_page() {
		if ( ! $this->post ) {
			return false;
		}

		return $this->post->post_type === 'page';
	}

	/**
	 * Get the URL for the installed icon pack, if any
	 *
	 * @return string
	 */
	public function icon_pack_css() {
		$icon_pack = get_option( 'thrive_icon_pack' );

		return ! empty( $icon_pack['css'] ) ? tve_url_no_protocol( $icon_pack['css'] ) . '?ver=' . ( isset( $icon_pack['css_version'] ) ? $icon_pack['css_version'] : TVE_VERSION ) : '';
	}

	/**
	 * Prepare the global scripts ( head, body ) for a (possible) landing page
	 *
	 * @param TCB_Post $post
	 *
	 * @return array
	 */
	public function post_global_scripts( $post ) {
		/* landing page template - we need to allow the user to setup head and footer scripts */
		$tve_global_scripts = $post->meta( 'tve_global_scripts' );
		if ( empty( $tve_global_scripts ) || ! $post->is_landing_page() ) {
			$tve_global_scripts = array( 'head' => '', 'footer' => '' );
		}
		$tve_global_scripts['head']   = preg_replace( '#<style(.+?)</style>#s', '', $tve_global_scripts['head'] );
		$tve_global_scripts['footer'] = preg_replace( '#<style(.+?)</style>#s', '', $tve_global_scripts['footer'] );

		return $tve_global_scripts;
	}
}
