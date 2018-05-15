<?php

if ( !defined( 'ABSPATH' ) || !defined( 'YITH_YWDPD_VERSION' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Implements admin features of YITH WooCommerce Dynamic Pricing and Discounts
 *
 * @class   YITH_WC_Dynamic_Pricing_Admin
 * @package YITH WooCommerce Dynamic Pricing and Discounts
 * @since   1.0.0
 * @author  Yithemes
 */
if ( !class_exists( 'YITH_WC_Dynamic_Pricing_Admin' ) ) {

	/**
	 * Class YITH_WC_Dynamic_Pricing_Admin
	 */
	class YITH_WC_Dynamic_Pricing_Admin {

        /**
         * Single instance of the class
         *
         * @var \YITH_WC_Dynamic_Pricing_Admin
         */
        protected static $instance;

        /**
         * @var $_panel Panel Object
         */
        protected $_panel;

        /**
         * @var $_premium string Premium tab template file name
         */
        protected $_premium = 'premium.php';

        /**
         * @var string Premium version landing link
         */
        protected $_premium_landing = 'http://yithemes.com/themes/plugins/yith-woocommerce-dynamic-pricing-and-discounts/';

        /**
         * @var string Panel page
         */
        protected $_panel_page = 'yith_woocommerce_dynamic_pricing_and_discounts';

        /**
         * @var string Doc Url
         */
        public $doc_url = 'https://yithemes.com/docs-plugins/yith-woocommerce-dynamic-pricing-and-discounts/';

        /**
         * @var string Doc Url
         */
        public $post_type_name = 'ywdpd_discount';

        /**
         * Returns single instance of the class
         *
         * @return \YITH_WC_Dynamic_Pricing_Admin
         * @since 1.0.0
         */
        public static function get_instance() {
            if ( is_null( self::$instance ) ) {
                self::$instance = new self();
            }
            return self::$instance;
        }


        /**
         * Constructor
         *
         * Initialize plugin and registers actions and filters to be used
         *
         * @since  1.0.0
         * @author Emanuela Castorina
         */
        public function __construct() {

            $this->create_menu_items();

            // register plugin to licence/update system
            add_action( 'wp_loaded', array( $this, 'register_plugin_for_activation' ), 99 );
            add_action( 'admin_init', array( $this, 'register_plugin_for_updates' ) );
            
            //custom tab
            add_action( 'ywdpd_price_rules_tab', array( $this, 'rules_tab' ), 10, 2 );
            add_action( 'ywdpd_cart_rules_tab', array( $this, 'rules_tab' ), 10, 2 );

            // panel type ajax action active
	        /* ajax action */
	        add_action( 'wp_ajax_ywdpd_admin_action', array( $this, 'ajax' ) );
	        add_action( 'wp_ajax_nopriv_ywdpd_admin_action', array( $this, 'ajax' ) );

            //Add action links
            add_filter( 'plugin_action_links_' . plugin_basename( YITH_YWDPD_DIR . '/' . basename( YITH_YWDPD_FILE ) ), array( $this, 'action_links' ) );
            add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 4 );

            //custom styles and javascripts
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles_scripts' ), 20);

            //@since 1.4.0
            add_action( 'admin_init', array( $this, 'change_url_to_sendback' ), 10 );
	        add_action( 'admin_init', array($this, 'add_metabox'), 1);
	        add_action( 'add_meta_boxes', array( $this, 'show_discount_action' ) );
	        add_filter( 'yit_fw_metaboxes_type_args', array( $this, 'add_custom_type_metaboxes' ) );
			add_action( 'edit_form_top', array($this, 'add_custom_type_type'));
	        add_action( 'admin_menu', array( $this, 'remove_publish_box' ) );
	        add_action( 'admin_init', array($this, 'check_post_type_action') );

			// handle ajax actions
	        add_action( 'wp_ajax_ywdpd_json_search_tags', array( $this, 'get_tags_via_ajax' ) );
	        add_action( 'wp_ajax_ywdpd_json_search_categories', array( $this, 'get_categories_via_ajax' ) );
	        add_action( 'wp_ajax_table_order_section', array( $this, 'table_order_section' ) );

        }

		/**
		 * Add metabox into ywdpd_discount editor page
		 *
		 * @since 1.4.0
		 * @author Emanuela Castorina
		 */
		public function add_metabox() {

			if ( ywdpd_check_valid_admin_page( $this->post_type_name ) ) {
				$type = false;

				if ( isset( $_REQUEST['ywdpd_discount_type'] ) ) {
					$type = $_REQUEST['ywdpd_discount_type'];
				} elseif ( isset( $_REQUEST['yit_metaboxes']['_discount_type'] ) ) {
					$type = $_REQUEST['yit_metaboxes']['_discount_type'];
				} elseif ( isset( $_REQUEST['post'] ) ) {
					$type = get_post_meta( $_REQUEST['post'], '_discount_type', true );
				}

				if (  $type ) {
					$args = require_once( YITH_YWDPD_DIR . 'plugin-options/metabox/ywdpd_' . $type . '_discount.php' );
					if ( ! function_exists( 'YIT_Metabox' ) ) {
						require_once( 'plugin-fw/yit-plugin.php' );
					}
					$metabox = YIT_Metabox( 'ywdpd_'.$type.'_discount' );
					$metabox->init( $args );

				}

			}
		}

		/**
		 * Add the metabox to show the action of ywdpd_discount post type
		 * @access public
		 *
		 * @return void
		 * @since  1.0.0
		 */
		public function show_discount_action() {
			add_meta_box( 'ywdpd-action-discount', __( 'Dynamic Action', 'ywdpd' ), array(
				$this,
				'show_discount_action_metabox'
			), $this->post_type_name, 'side', 'high' );
		}

		/**
		 * Metabox to show the action of the current discount
		 * @access public
		 *
		 * @param object $post
		 *
		 * @return void
		 * @since  1.4.0
		 */
		public function show_discount_action_metabox( $post ) {
			wc_get_template( 'metabox_discount_action_content.php', array(), '', YITH_YWDPD_TEMPLATE_PATH . 'admin/metaboxes/');
		}

		/**
		 * Add an hidden field into the form of post and a link to return to the discount list
		 *
		 * @param $post WP_Post
		 *
		 * @since 1.4.0
		 * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
		 */
		public function add_custom_type_type( $post ) {

			$type = isset( $_REQUEST['ywdpd_discount_type'] ) ? $_REQUEST['ywdpd_discount_type'] : get_post_meta( $post->ID, '_discount_type', true );

			if ( ywdpd_check_valid_admin_page( $this->post_type_name ) && ! empty( $type ) ) {
				printf( '<input type="hidden" id="ywdpd_discount_type" name="ywdpd_discount_type" value="%s" />', esc_attr( $type ) );
				printf( '<a href="%1$s" class="ywpdp_subtitle_link" title="%2$s">%2$s <img draggable="false" class="emoji" alt="⤴" src="https://s.w.org/images/core/emoji/2.3/svg/2934.svg"></a>', $this->get_panel_page_uri( $tab = $type ), __( 'Return to Discount List', 'ywdpd' ) );
			}

		}

		/**
		 * Returns the panel page URI
		 * @param string $tab
		 *
		 * @return string
		 *
		 * @since 1.4.0
		 * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
		 */
		public function get_panel_page_uri( $tab = '' ){
			$panel_uri = add_query_arg( 'page', $this->_panel_page, admin_url( 'admin.php' ) );
			if( $tab ){
				$panel_uri = add_query_arg( 'tab', $tab, $panel_uri );
			}
			return $panel_uri;
		}

		/**
		 * Remove publish box from single page page of ywdpd_discount
		 * @access public
		 *
		 * @return void
		 * @since  1.4.0
		 * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
		 */
		public function remove_publish_box() {
			remove_meta_box( 'submitdiv', $this->post_type_name, 'side' );
		}

		/**
		 * Shows custom metabox type
		 *
		 * @param $args
		 * @return mixed
		 *
		 * @since 1.4.0
		 * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
		 */
		public function add_custom_type_metaboxes( $args ) {

			if ( ywdpd_check_valid_admin_page( $this->post_type_name ) ) {

				$custom_types = array(
					'customers',
					'products',
					'categories',
					'tags',
					'quantity_discount',
					'special_offer_discount',
					'cart_discount',
					'cart_discount_type',
					'brands',
					'vendors'
				);

				if ( in_array( $args['type'], $custom_types ) ) {
					$args['basename'] = YITH_YWDPD_DIR;
					$args['path']     = 'admin/metaboxes/types/';
				}

			}

			return $args;
		}

		/**
		 * Change url to send back in the ywdpd_discount post type
		 *
		 * @since 1.4.0
		 * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
		 */
		public function change_url_to_sendback(){
	        global $pagenow;

	        if ( $pagenow == 'edit.php' && isset( $_GET['post_type'] ) && $_GET['post_type'] == 'ywdpd_discount' ) {
		        wp_safe_redirect( admin_url( 'admin.php?page='.$this->_panel_page ) );
		        exit;
	        }
        }

		/**
		 * Get Tags via Ajax for Discount Metabox
		 *
		 * @since 1.4.0
		 * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
		 */
		public function get_tags_via_ajax() {

			check_ajax_referer( 'search-products', 'security' );

			if ( ! current_user_can( 'edit_products' ) ) {
				wp_die( - 1 );
			}

			if ( ! $search_text = wc_clean( stripslashes( $_GET['term'] ) ) ) {
				wp_die();
			}

			$found_tags = array();
			$args       = array(
				'taxonomy'   => array( 'product_tag' ),
				'orderby'    => 'id',
				'order'      => 'ASC',
				'hide_empty' => true,
				'fields'     => 'all',
				'name__like' => $search_text,
			);

			if ( $terms = get_terms( $args ) ) {
				foreach ( $terms as $term ) {
					$term->formatted_name .= $term->name . ' (' . $term->count . ')';

					$found_tags[ $term->term_id ] = $term->formatted_name;
				}
			}

			wp_send_json( apply_filters( 'ywdpd_json_search_found_tags', $found_tags ) );
		}

		/**
		 * Get Category via Ajax for Discount Metabox
		 *
		 * @since 1.4.0
		 * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
		 */
		public function get_categories_via_ajax() {

			check_ajax_referer( 'search-products', 'security' );

			if ( ! current_user_can( 'edit_products' ) ) {
				wp_die( - 1 );
			}

			if ( ! $search_text = wc_clean( stripslashes( $_GET['term'] ) ) ) {
				wp_die();
			}

			$found_tags = array();
			$args       = array(
				'taxonomy'   => array( 'product_cat' ),
				'orderby'    => 'id',
				'order'      => 'ASC',
				'hide_empty' => true,
				'fields'     => 'all',
				'name__like' => $search_text,
			);

			if ( $terms = get_terms( $args ) ) {
				foreach ( $terms as $term ) {
					$term->formatted_name .= $term->name . ' (' . $term->count . ')';

					$found_tags[ $term->term_id ] = $term->formatted_name;
				}
			}

			wp_send_json( apply_filters( 'ywdpd_json_search_found_categories', $found_tags ) );
		}

		/**
		 * Change the url Move to trash to Delete the Discount definitely
		 *
		 * @since  1.4.0
		 * @author Emanuela Castorina
		 */
		public function get_delete_post_link( $url, $post_id, $type ){

			$post_type = get_post_type( $post_id );
			if( $post_type != $this->post_type_name ){
				return $url;
			}

			$action = 'delete';
			$delete_link = add_query_arg( 'action', $action, admin_url( 'admin.php' ) );
			$delete_link = add_query_arg( 'page', $this->_panel_page, $delete_link );
			$delete_link = add_query_arg( 'tab', $type, $delete_link );
			$delete_link = add_query_arg( 'post', $post_id, $delete_link );
			$delete_link = wp_nonce_url( $delete_link, "$action-post_{$post_id}" );
			return $delete_link;

		}

		/**
		 * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
		 */
		function check_post_type_action() {
			if ( ! isset( $_REQUEST['post'] ) || ! isset( $_REQUEST['_wpnonce'] ) || ! isset( $_REQUEST['action'] ) ) {
				return;
			}

			$post_id = $_REQUEST['post'];
			$action  = $_REQUEST['action'];
			if ( wp_verify_nonce( $_REQUEST['_wpnonce'], "$action-post_{$post_id}" ) ) {
				$post = get_post( $post_id );

				if ( ! ( $post && $post->post_type == $this->post_type_name ) ) {
					return;
				}

				$post_type_object = get_post_type_object( $this->post_type_name );
				if ( $action === 'delete' ) {
					if ( current_user_can( $post_type_object->cap->delete_post, $post_id ) ) {
						wp_delete_post( $post_id, true );
					}
				}
			}
		}

		/**
		 * Switch a ajax call
		 */
		public function ajax() {
			if ( isset( $_REQUEST['ywdpd_action'] ) ) {
				if ( method_exists( $this, 'ajax_' . $_REQUEST['ywdpd_action'] ) ) {
					$s = 'ajax_' . $_REQUEST['ywdpd_action'];
					$this->$s();
				}
			}

		}

		/**
		 * Order the meta on each rule
		 * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
		 */
		public function ajax_table_order_section(  ) {
			if ( ! current_user_can( 'edit_products' ) ) {
				wp_die( - 1 );
			}

			$roleid    = absint($_REQUEST['roleid']);
			$previd = absint( isset( $_POST['previd'] ) ? $_POST['previd'] : 0 );
			$nextid = absint( isset( $_POST['nextid'] ) ? $_POST['nextid'] : 0 );
			$type       = $_REQUEST['type'];


			$args = array(
				'post_type' => 'ywdpd_discount',
				'posts_per_page' => -1,
				'meta_query' => array(
					array(
						'key'     => '_discount_type',
						'value'   => $type,
					),
				),
				'orderby'   => 'meta_value_num',
				'meta_key'  => '_priority',
				'order'     => 'ASC',
			);

			$posts = new WP_Query( $args );

			$priority = array();
			$index = 1;
			foreach ( $posts->posts as $post ) {
				if ( $roleid === $post->ID ) {
					continue;
				}

				if ( $nextid && $nextid === $post->ID ) {
					$priority[ $roleid ] = $index++;
					$priority[ $nextid ]  = $index++;
				}elseif( $previd && $previd === $post->ID ) {
					$priority[ $previd ]  = $index++;
					$priority[ $roleid ] = $index++;
				} else {
					$priority[ $post->ID ] = $index++;
				}
			}

			foreach ( $priority as $post_id => $value ){
				update_post_meta( $post_id, '_priority', $value);
			}

			die();

		}

		/**
		 * Modify the capability
		 *
		 * @param $capability
		 *
		 * @return string
		 */
		function change_capability( $capability ) {
            return 'manage_woocommerce';
        }

        /**
         * Enqueue styles and scripts
         *
         * @access public
         * @return void
         * @since 1.0.0
         */
		public function enqueue_styles_scripts() {

			if ( ywdpd_check_valid_admin_page( $this->post_type_name ) || ( isset( $_GET['page'] ) && $_GET['page'] == 'yith_woocommerce_dynamic_pricing_and_discounts' ) ) {

				wp_enqueue_script( 'jquery-ui-datepicker' );
				wp_enqueue_style( 'yith_ywdpd_backend', YITH_YWDPD_ASSETS_URL . '/css/backend.css', array( 'woocommerce_admin_styles' ), YITH_YWDPD_VERSION );
				wp_enqueue_script( 'ywdpd_timepicker', YITH_YWDPD_ASSETS_URL . '/js/jquery-ui-timepicker-addon.min.js', array( 'jquery' ), YITH_YWDPD_VERSION, true );
				wp_enqueue_script( 'yith_ywdpd_admin', YITH_YWDPD_ASSETS_URL . '/js/ywdpd-admin' . YITH_YWDPD_SUFFIX . '.js', array(
					'jquery',
					'jquery-ui-sortable'
				), YITH_YWDPD_VERSION, true );
				wp_enqueue_script( 'jquery-blockui', YITH_YWDPD_ASSETS_URL . '/js/jquery.blockUI.min.js', array( 'jquery' ), false, true );

				if ( ! wp_script_is( 'selectWoo' ) ) {
					wp_enqueue_script( 'selectWoo' );
					wp_enqueue_script( 'wc-enhanced-select' );
				}

				wp_localize_script( 'yith_ywdpd_admin', 'yith_ywdpd_admin', apply_filters( 'yith_ywdpd_admin_localize', array(
					'ajaxurl' => WC()->ajax_url(),
					'del_msg' => apply_filters( 'yith_ywdpd_delete_msg_admin', __( 'Do you really want to delete this rule?', 'ywdpd' ) )
				) ) );

			}

		}

        /**
         * Create Menu Items
         *
         * Print admin menu items
         *
         * @since  1.0
         * @author Emanuela Castorina
         */
        private function create_menu_items() {
            // Add a panel under YITH Plugins tab
            add_action( 'admin_menu', array( $this, 'register_panel' ), 5 );
        }

        /**
         * Add a panel under YITH Plugins tab
         *
         * @return   void
         * @since    1.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         * @use      /Yit_Plugin_Panel class
         * @see      plugin-fw/lib/yit-plugin-panel.php
         */
        public function register_panel() {

            if ( !empty( $this->_panel ) ) {
                return;
            }

            $admin_tabs = array(
                'general' => __( 'Settings', 'ywdpd' ),
            );

            if ( defined( 'YITH_YWDPD_FREE_INIT' ) ) {
                $admin_tabs['premium'] = __( 'Premium Version', 'ywdpd' );
            }
            else {
	            $admin_tabs['pricing'] = __( 'Price Rules', 'ywdpd' );
                $admin_tabs['cart']    = __( 'Cart Discounts', 'ywdpd' );
            }

            $args = array(
                'create_menu_page' => true,
                'parent_slug'      => '',
                'page_title'       => __( 'Dynamic Pricing', 'ywdpd' ),
                'menu_title'       => __( 'Dynamic Pricing', 'ywdpd' ),
                'capability'       => 'manage_options',
                'parent'           => 'ywdpd',
                'parent_page'      => 'yit_plugin_panel',
                'page'             => $this->_panel_page,
                'admin-tabs'       => $admin_tabs,
                'options-path'     => YITH_YWDPD_DIR . '/plugin-options'
            );

            //enable shop manager to set Dynamic Pricing Options
	        $enable_shop_manager = YITH_WC_Dynamic_Pricing()->get_option('enable_shop_manager');
            if(  ywdpd_is_true( $enable_shop_manager ) ){
                add_filter( 'option_page_capability_yit_' . $args['parent'] . '_options', array($this,'change_capability') );
                $args['capability'] = 'manage_woocommerce';
            }

            /* === Fixed: not updated theme  === */
            if ( !class_exists( 'YIT_Plugin_Panel' ) ) {
                require_once( YITH_YWDPD_DIR.'/plugin-fw/lib/yit-plugin-panel.php' );
            }

            $this->_panel = new YIT_Plugin_Panel( $args );

	        $this->save_default_options();
            
        }

		/**
		 * Save default options when the plugin is installed
		 *
		 * @since   1.0.0
		 * @author  Emanuela Castorina
		 * @return  void
		 */
		public function save_default_options() {

			$options                = maybe_unserialize( get_option( 'yit_ywdpd_options', array() ) );
			$current_option_version = get_option( 'yit_ywdpd_option_version', '0' );
			$forced                 = isset( $_GET['update_ywdpd_options'] ) && $_GET['update_ywdpd_options'] == 'forced';

			if ( version_compare( $current_option_version, YITH_YWDPD_VERSION, '>=' ) && ! $forced ) {
				return;
			}

			$new_option = array_merge( $this->_panel->get_default_options(), ( array ) $options );
			update_option( 'yit_ywdpd_options', $new_option );
			update_option( 'yit_ywdpd_option_version', YITH_YWDPD_VERSION );
		}

        /**
         * Print fields table
         *
         * @access public
         * @param array $options
         * @return void
         * @since 1.0.0
         */
        public function rules_tab( $options ) {

            if( isset( $_GET['page'] ) && $_GET['page'] == $this->_panel_page
                && isset( $_GET['tab'] ) && !empty($_GET['tab'])
                && file_exists( YITH_YWDPD_TEMPLATE_PATH . '/admin/discount-tab.php' ) ) {
	            $type = $_GET['tab'];
	            $title = 'pricing' == $type ? __('Pricing Discounts', 'ywdpd') : __('Cart Discounts', 'ywdpd');
	            $this->cpt_obj_discount = new YWDPD_Discount_List_Table( array( 'type' => $type ) );

	            $discount_tab = YITH_YWDPD_TEMPLATE_PATH . '/admin/discount-tab.php';

	            if ( file_exists( $discount_tab ) ) {
		            include_once( $discount_tab );
	            }
            }
        }

        /**
         * Action Links
         *
         * add the action links to plugin admin page
         *
         * @param $links | links plugin array
         *
         * @return   mixed Array
         * @since    1.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         * @return mixed
         * @use      plugin_action_links_{$plugin_file_name}
         */
        public function action_links( $links ) {
            $links[] = '<a href="' . admin_url( "admin.php?page={$this->_panel_page}" ) . '">' . __( 'Settings', 'ywdpd' ) . '</a>';
            return $links;
        }

		/**
		 * plugin_row_meta
		 *
		 * add the action links to plugin admin page
		 *
		 * @param $plugin_meta
		 * @param $plugin_file
		 * @param $plugin_data
		 * @param $status
		 *
		 * @return   array
		 * @since    1.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 * @use      plugin_row_meta
		 */
		public function plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status ) {

			if ( defined( 'YITH_YWDPD_INIT' ) && YITH_YWDPD_INIT == $plugin_file ) {
				$plugin_meta[] = '<a href="' . $this->doc_url . '" target="_blank">' . __( 'Plugin Documentation', 'ywdpd' ) . '</a>';
			}
			return $plugin_meta;
		}

		/**
		 * Register plugins for activation tab
		 *
		 * @return void
		 * @since    2.0.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 */
		public function register_plugin_for_activation() {
			if ( ! class_exists( 'YIT_Plugin_Licence' ) ) {
				require_once ( YITH_YWDPD_DIR . 'plugin-fw/licence/lib/yit-licence.php' );
				require_once ( YITH_YWDPD_DIR . 'plugin-fw/licence/lib/yit-plugin-licence.php' );
			}
			YIT_Plugin_Licence()->register( YITH_YWDPD_INIT, YITH_YWDPD_SECRET_KEY, YITH_YWDPD_SLUG );
		}

		/**
		 * Register plugins for update tab
		 *
		 * @return void
		 * @since    2.0.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 */
		public function register_plugin_for_updates() {
			if( ! class_exists( 'YIT_Upgrade' ) ) {
				require_once YITH_YWDPD_DIR.'plugin-fw/lib/yit-upgrade.php';
			}
			YIT_Upgrade()->register( YITH_YWDPD_SLUG, YITH_YWDPD_INIT );
		}

    }
}

/**
 * Unique access to instance of YITH_WC_Dynamic_Pricing_Admin class
 *
 * @return \YITH_WC_Dynamic_Pricing_Admin
 */
function YITH_WC_Dynamic_Pricing_Admin() {
    return YITH_WC_Dynamic_Pricing_Admin::get_instance();
}