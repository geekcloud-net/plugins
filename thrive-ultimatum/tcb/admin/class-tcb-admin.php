<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 3/3/2017
 * Time: 1:11 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden.
}

/**
 * Thrive Architect admin class.
 */
class TCB_Admin {

	/**
	 * The single instance of the class.
	 *
	 * @var TCB_Admin singleton instance.
	 */
	protected static $_instance = null;

	public function __construct() {

		add_action( 'init', array( $this, 'includes' ) );

		add_filter( 'tve_dash_installed_products', array( $this, 'add_to_dashboard_list' ) );
		add_filter( 'tve_dash_admin_product_menu', array( $this, 'add_to_dashboard_menu' ) );

		/**
		 * Add admin scripts and styles
		 */
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		add_filter( 'tve_dash_features', array( $this, 'dashboard_add_features' ) );

		add_action( 'admin_footer', array( $this, 'admin_page_loader' ) );

		/* admin TCB edit button */
		add_action( 'edit_form_after_title', array( $this, 'admin_edit_button' ) );

		add_filter( 'admin_body_class', array( $this, 'wp_editor_body_class' ), 10, 4 );

		add_action( 'save_post', array( $this, 'maybe_disable_tcb_editor' ) );
	}

	/**
	 * Main TCB Admin Instance.
	 * Ensures only one instance of TCB Admin is loaded or can be loaded.
	 *
	 * @return TCB_Admin
	 */
	public static function instance() {
		if ( empty( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * make sure the TCB product is shown in the dashboard product list
	 *
	 * @param array $items
	 *
	 * @return array
	 */
	public function add_to_dashboard_list( $items ) {
		require_once 'includes/class-tcb-product.php';
		$items[] = new TCB_Product();

		return $items;
	}

	/**
	 * Includes required files
	 */
	public function includes() {
		require_once 'includes/tcb-admin-functions.php';
		require_once 'includes/class-tcb-admin-ajax.php';
	}

	/**
	 * Push the Thrive Quiz Builder to Thrive Dashboard menu
	 *
	 * @param array $menus items already in Thrive Dashboard.
	 *
	 * @return array
	 */
	public function add_to_dashboard_menu( $menus = array() ) {

		$menus['tcb'] = array(
			'parent_slug' => null, //null | tve_dash_section
			'page_title'  => __( 'Content Templates', 'thrive-cb' ),
			'menu_title'  => __( 'Content Templates', 'thrive-cb' ),
			'capability'  => 'manage_options',
			'menu_slug'   => 'tcb_admin_dashboard',
			'function'    => array( $this, 'dashboard' ),
		);

		return $menus;
	}

	/**
	 * Output TCB Admin dashboard
	 */
	public function dashboard() {
		include $this->admin_path( 'includes/views/dashboard.phtml' );
	}

	public function enqueue_scripts( $hook ) {
		$accepted_hooks = apply_filters( 'tcb_admin_accepted_admin_pages', array(
			'thrive-dashboard_page_tcb_admin_dashboard',  // Visible in Thrive Dashboard side menu
			'admin_page_tcb_admin_dashboard',  // Not visible in Thrive Dashboard side menu
		) );

		if ( 'post.php' == $hook || 'post-new.php' == $hook ) {
			if ( tve_is_post_type_editable( get_post_type( get_the_ID() ) ) ) {
				$this->enqueue_post_editor();

				return;
			}
		}

		if ( ! in_array( $hook, $accepted_hooks, true ) ) {
			return;
		}

		//TODO: Verify license activated

		/**
		 * Enqueue dash scripts
		 */
		tve_dash_enqueue();

		/**
		 * Specific admin styles
		 */
		tve_enqueue_style( 'tcb-admin-style', $this->admin_url( 'assets/css/tcb-admin-styles.css' ) );
		tve_enqueue_script( 'tcb-admin-js', $this->admin_url( 'assets/js/tcb-admin.js' ), array(
			'jquery',
			'backbone',
		) );

		wp_localize_script( 'tcb-admin-js', 'TVE_Admin', tcb_admin_get_localization() );

		/**
		 * Output the main templates for backbone views used in dashboard.
		 */
		add_action( 'admin_print_footer_scripts', array( $this, 'render_backbone_templates' ) );
	}

	/**
	 * make sure all the features required by TCB are shown in the dashboard
	 *
	 * @param array $features
	 *
	 * @return array
	 */
	public function dashboard_add_features( $features ) {
		$features['font_manager']     = true;
		$features['icon_manager']     = true;
		$features['api_connections']  = true;
		$features['general_settings'] = true;

		return $features;
	}

	/**
	 * Render backbone templates
	 */
	public function render_backbone_templates() {
		$templates = tve_dash_get_backbone_templates( $this->admin_path( 'includes/views/templates' ), 'templates' );

		tve_dash_output_backbone_templates( $templates );
	}

	/**
	 * Full admin path to file if specified
	 *
	 * @param string $file to be appended to the plugin path.
	 *
	 * @return string
	 */
	public function admin_path( $file = '' ) {
		return plugin_dir_path( __FILE__ ) . ltrim( $file, '\\/' );
	}

	/**
	 * Full plugin url to file if specified
	 *
	 * @param string $file to be appended to the plugin url.
	 *
	 * @return string
	 */
	public function admin_url( $file = '' ) {
		return plugin_dir_url( __FILE__ ) . ltrim( $file, '\\/' );
	}

	/**
	 * Enqueue and localize scripts on the admin post edit page.
	 */
	public function enqueue_post_editor() {
		$js_suffix = defined( 'TVE_DEBUG' ) && TVE_DEBUG ? '.js' : '.min.js';

		wp_enqueue_style( 'wp-pointer' );
		wp_enqueue_script( 'wp-pointer' );

		tve_enqueue_script( 'tcb-admin-edit-post', tve_editor_js() . '/admin' . $js_suffix );
		wp_localize_script( 'tcb-admin-edit-post', 'TCB_Post_Edit_Data', array_merge( tcb_admin_get_localization(), array(
			'post_id' => get_the_ID(),
		) ) );

		tve_enqueue_style( 'tcb-admin-style', $this->admin_url( 'assets/css/tcb-admin-styles.css' ) );
	}

	/**
	 * Include the HTML for a loading overlay on admin pages.
	 */
	public function admin_page_loader() {
		tcb_template( 'admin/page-loader' );
	}

	/**
	 * output TCB editor button in the admin area
	 */
	public function admin_edit_button() {
		$post_type      = get_post_type();
		$post_id        = get_the_ID();
		$page_for_posts = get_option( 'page_for_posts' );

		if ( ! tve_is_post_type_editable( $post_type ) || ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		if ( 'page' == $post_type && $page_for_posts && $post_id == $page_for_posts ) {
			tcb_template( 'admin/cannot-edit-blog-page' );

			return;
		}

		$url          = tcb_get_editor_url( get_the_ID() );
		$post_id      = get_the_ID();
		$post         = get_post( $post_id );
		$landing_page = tve_post_is_landing_page( $post_id );
		$wp_content   = $post->post_content;
		/* this means that this post has been saved with TCB at least once */
		$tcb_content = tve_get_post_meta( $post_id, 'tve_globals' );

		$show_migrate_button = false;
		if ( ! $landing_page && ! get_post_meta( $post_id, 'tcb2_ready', true ) ) {

			$show_migrate_button = true;
			/*
			if this meta does not exist, there are a couple of possible cases:
			1) post is just being created - no TCB content and no WP content - we can safely update the post_meta key
			2) no Wordpress content, but with TCB content - we can safely update the post_meta key
			3) Wordpress content, but no TCB content - this means the user never saved the post with TCB - we can safely update the meta key
			 */
			if ( empty( $wp_content ) || empty( $tcb_content ) ) {
				update_post_meta( $post_id, 'tcb2_ready', '1' );
				$show_migrate_button = false;
			}
		}

		tcb_template( 'admin/post-edit-button', array(
			'edit_url'            => $url,
			'post_id'             => $post_id,
			'show_migrate_button' => $show_migrate_button,
			'landing_page'        => $landing_page,
			'tcb_enabled'         => ! $show_migrate_button && (int) get_post_meta( $post_id, 'tcb_editor_enabled', true ) && ! (int) get_post_meta( $post_id, 'tcb_editor_disabled', true ),
		) );
	}

	/**
	 * For pages where TCB was enabled, add a class to the body in order to hide the default WP tinymce editor for the content
	 *
	 * @param string $classes
	 *
	 * @return string
	 */
	public function wp_editor_body_class( $classes ) {

		$screen = get_current_screen();
		if ( empty( $screen ) || ! $screen->base || 'post' != $screen->base ) {
			return $classes;
		}
		$post_type = get_post_type();
		$post_id   = get_the_ID();

		if ( empty( $post_id ) || empty( $post_type ) ) {
			return $classes;
		}

		if ( ! tve_is_post_type_editable( $post_type ) || ! current_user_can( 'edit_posts' ) ) {
			return $classes;
		}

		$post = tcb_post( $post_id );

		$post->maybe_auto_migrate();

		if ( $post->editor_enabled() ) {
			$classes .= ' tcb-hide-wp-editor';
		}

		return $classes;
	}

	/**
	 * Check to see if a "disable_tcb_editor" input has been submitted - if yes, we disable the tcb editor for this post, and show the default WP content.
	 */
	public function maybe_disable_tcb_editor() {
		global $post;
		$tcb_post = tcb_post( $post );
		if ( ! empty( $_POST['tcb_disable_editor'] ) && wp_verify_nonce( $_POST['tcb_disable_editor'], 'tcb_disable_editor' ) && (int) $tcb_post->meta( 'tcb2_ready' ) ) {
			$tcb_post->disable_editor();
		}
	}
}

/**
 * @return TCB_Admin
 */
function tcb_admin() {
	return TCB_Admin::instance();
}

tcb_admin();
