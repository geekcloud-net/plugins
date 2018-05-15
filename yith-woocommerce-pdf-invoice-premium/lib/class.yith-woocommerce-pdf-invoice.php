<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'YITH_WooCommerce_Pdf_Invoice' ) ) {
	
	/**
	 * Implements features of Yith WooCommerce Pdf Invoice
	 *
	 * @class   YITH_WooCommerce_Pdf_Invoice
	 * @package Yithemes
	 * @since   1.0.0
	 * @author  Your Inspiration Themes
	 */
	class YITH_WooCommerce_Pdf_Invoice {
		
		/**
		 * @var bool the preview mode prevent the use of the counter
		 */
		public $preview_mode = false;
		
		/**
		 * @var bool set is subtotal should include discount
		 */
		public $subtotal_incl_discount = true;
		
		public $action_create_invoice = 'create-invoice';
		
		/**
		 * @var bool set if packing slip generation is enabled
		 */
		public $enable_packing_slip = false;
		
		/**
		 * @var YITH_YWPI_Backend shortcut to backend instance
		 */
		public $backend = null;
		
		/**
		 * Constructor
		 *
		 * Initialize plugin and registers actions and filters to be used
		 *
		 * @since  1.0
		 * @author Lorenzo giuffrida
		 * @access public
		 */
		public function __construct() {
			$this->init_plugin_options();
			
			$this->backend = YITH_YWPI_Backend::get_instance();
			
			$this->add_buttons_on_customer_orders_page();
			$this->add_features_on_admin_orders_page();
			
			$this->add_order_status_related_actions();
			
			/*
			* Check if invoice should be attached to emails
			*/
			add_filter( 'woocommerce_email_attachments', array( $this, 'attach_documents_to_email' ), 99, 3 );
			
			
			//  Add stylesheets and scripts files to back-end
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			
			//  *************************************************
			//  Hook action in the ywpi-invoice-template.php file
			require_once( 'class.yith-ywpi-template.php' );
			
			/**
			 * Manage the action 'create'
			 */
			add_action( 'admin_action_create', array(
				$this,
				'manage_document_action'
			) );
			
			/**
			 * Manage the action 'view'
			 */
			add_action( 'admin_action_view', array(
				$this,
				'manage_document_action'
			) );
			
			/**
			 * Manage the action 'reset'
			 */
			add_action( 'admin_action_reset', array(
				$this,
				'manage_document_action'
			) );
			
			/**
			 * Manage the action 'preview'
			 */
			add_action( 'admin_action_preview', array(
				$this,
				'manage_document_action'
			) );

            /**
             * Manage the action 'preview'
             */
            add_action( 'admin_action_regenerate', array(
                $this,
                'manage_document_action'
            ) );
			
			add_action( 'init', array(
				$this,
				'manage_myaccount_buttons'
			) );
			
			add_action( 'yith_ywpi_column_product_after_content', array(
				$this,
				'show_content_in_product_column'
			), 10, 2 );
		}
		
		/**
		 * Fill the product column of the document being rendered with additional informations
		 *
		 * @param YITH_Document $document
		 * @param WC_Product    $_product
		 */
		public function show_content_in_product_column( $document, $_product ) {
			
			if ( ywpi_is_enabled_column_weight_dimension( $document ) ):
				$has_dimensions = ( $_product->get_length() || $_product->get_height() || $_product->get_width() ) && ! $_product->is_virtual();
				
				if ( $_product->has_weight() || $has_dimensions ) :
					$dimensions = version_compare( WC()->version, '2.7.0', '<' ) ?
						$_product->get_dimensions() :
						wc_format_dimensions( $_product->get_dimensions( false ) );
					?>
					<div style="font-size: 10px">
						<?php if ( $_product->has_weight() ) : ?>
							<br>
							<span><?php _e( 'Weight: ', 'yith-woocommerce-pdf-invoice' ) ?></span>
							<span><?php echo $_product->get_weight() . ' ' . esc_attr( get_option( 'woocommerce_weight_unit' ) ); ?></span>
						<?php endif; ?>
						
						<?php if ( $_product->has_dimensions() ) : ?>
							<br>
							<span><?php _e( 'Dimensions: ', 'yith-woocommerce-pdf-invoice' ) ?></span>
							<span><?php echo $dimensions; ?></span>
						
						<?php endif; ?>
					</div>
					<?php
				endif;
			endif;
		}

		/**
		 * Enqueue js file
		 *
		 * @since  1.0
		 * @author Daniel Sanchez
		 */
		public function enqueue_scripts() {

			/* ====== Script ====== */

			wp_register_script( 'ywpi_' . YITH_YWPI_ASSETS_URL . '-js', YITH_YWPI_ASSETS_URL . '/js/yith-wc-pdf-invoice-admin.js', array(
				'jquery',
				'jquery-ui-sortable'
			), YITH_YWPI_VERSION, true );

			wp_localize_script( 'ywpi_' . YITH_YWPI_ASSETS_URL . '-js', 'yith_wc_pdf_invoice_free_object', apply_filters( 'yith_wc_pdf_invoice_free_admin_localize', array(
				'ajax_url'    => admin_url( 'admin-ajax.php' ),
				'ajax_loader' => 'ywpi_css', YITH_YWPI_ASSETS_URL . '/images/ajax-loader.gif',
				'logo_message_1' => __( "The logo your uploading is ", 'yith-woocommerce-pdf-invoice' ),
				'logo_message_2' => __( ". Logo must be no bigger than 300 x 150 pixels", 'yith-woocommerce-pdf-invoice' ),
			) ) );

			wp_enqueue_script( 'ywpi_' . YITH_YWPI_ASSETS_URL . '-js' );

		}
		
		/**
		 * Enqueue css file
		 *
		 * @since  1.0
		 * @author Lorenzo giuffrida
		 */
		public function enqueue_styles() {
			
			wp_enqueue_style( 'ywpi_css', YITH_YWPI_ASSETS_URL . '/css/ywpi.css' );
		}
		
		public function manage_myaccount_buttons() {
			if ( is_admin() ) {
				return;
			}
			
			$this->manage_document_action();
		}
		
		/**
		 * Create invoice
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function manage_document_action() {
//			//todo Check authorization
//			if ( ! current_user_can( 'manage_woocommerce' ) ) {
//				return;
//			}

			if ( ! isset( $_REQUEST['type'] ) ) {
				return;
			}
			
			if ( ! isset( $_REQUEST['id'] ) ) {
				return;
			}
			
			if ( ! isset( $_REQUEST['action'] ) ) {
				return;
			}
			
			if ( ! isset( $_REQUEST['_wpnonce'] ) ) {
				return;
			}

			$type     = $_REQUEST['type'];
			$order_id = $_REQUEST['id'];
			$action   = $_REQUEST['action'];
			
			$nonce        = $_REQUEST['_wpnonce'];
			$nonce_action = $action . $type . $order_id;

			if ( ! wp_verify_nonce( $nonce, $nonce_action ) ) {
				return;
			}

			$allowed_type = array( 'invoice', 'packing-slip', 'proforma', 'credit-note' );
			if ( ! in_array( strtolower( $type ), $allowed_type ) ) {
				throw new Exception( __( 'YITH PDF Invoice: unknown document type. Unable to manage this action.', 'yith-woocommerce-pdf-invoice' ) );
			}

			switch ( $action ) {
				case 'create' :
					
					$this->create_document( $order_id, $type );
					break;
				
				case 'view' :
					$this->view_document( $order_id, $type );
					break;
				
				case 'reset' :
					$this->reset_document( $order_id, $type );
					break;
				
				case 'preview' :
					$this->view_preview( $order_id, $type );
					break;

                case 'regenerate' :
                    $this->regenerate_document( $order_id, $type );
                    break;
				
				default:
					return;
			}
			
			if ( is_admin() && isset( $_SERVER['HTTP_REFERER'] ) ) {
				$location = $_SERVER['HTTP_REFERER'];

				if( !wp_safe_redirect( $location ) ){
				    wp_redirect($location);
                }
				exit();
			}
		}
		
		
		/**
		 * Retrieve the plugin option value for the document notes visibility
		 *
		 * @param YITH_Document $document
		 *
		 * @return mixed|void
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function is_visible_document_notes( $document ) {
			$is_visible = '';
			
			if ( $document instanceof YITH_Credit_Note ) {
				$is_visible = ywpi_get_option( 'ywpi_show_invoice_notes', $document );
			} elseif ( ywpi_document_behave_as_invoice( $document ) ) {
				$is_visible = ywpi_get_option( 'ywpi_show_credit_note_notes', $document );
			} elseif ( $document instanceof YITH_Shipping ) {
				$is_visible = ywpi_get_option( 'ywpi_packing_slip_show_notes', $document );
			}
			
			return apply_filters( 'is_visible_document_notes', $is_visible, $document );
		}
		
		
		/**
		 * Retrieve the notes to be shown when printing a generic document
		 *
		 * @param YITH_Document $document
		 *
		 * @return mixed
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		function get_document_notes( $document ) {
			$notes = '';
			if ( $document instanceof YITH_Invoice ) {
				$notes = ywpi_get_option( 'ywpi_invoice_notes', $document );
			} elseif ( $document instanceof YITH_Pro_Forma ) {
				$notes = ywpi_get_option( 'ywpi_pro_forma_notes', $document );
			} elseif ( $document instanceof YITH_Shipping ) {
				$notes = ywpi_get_option( 'ywpi_packing_slip_notes', $document );
			} elseif ( $document instanceof YITH_Credit_Note ) {
				$notes = ywpi_get_option( 'ywpi_credit_note_notes', $document );
			}
			
			return apply_filters( 'get_document_notes', $notes, $document );
		}
		
		/**
		 * Check if a footer should be shown for a document
		 *
		 * @param YITH_Document $document
		 *
		 * @return mixed|void
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		function is_visible_document_footer( $document ) {
			$is_visible = '';
			
			
			if ( $document instanceof YITH_Credit_Note ) {
				$is_visible = ywpi_get_option( 'ywpi_show_credit_note_footer', $document );
			} elseif ( ywpi_document_behave_as_invoice( $document ) ) {
				$is_visible = 'yes' == ywpi_get_option( 'ywpi_show_invoice_footer', $document );
			} elseif ( $document instanceof YITH_Shipping ) {
				$is_visible = 'yes' == ywpi_get_option( 'ywpi_packing_slip_show_footer', $document );
			}
			
			return apply_filters( 'is_visible_document_footer', $is_visible, $document );
		}
		
		/**
		 * Retrieve the footer to be shown when printing a generic document
		 *
		 * @param YITH_Document $document
		 *
		 * @return mixed
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		function get_document_footer( $document ) {
			if ( $document instanceof YITH_Invoice ) {
				return ywpi_get_option( 'ywpi_invoice_footer', $document );
			}
			
			if ( $document instanceof YITH_Credit_Note ) {
				return ywpi_get_option( 'ywpi_credit_note_footer', $document );
			}
			
			if ( $document instanceof YITH_Pro_Forma ) {
				return ywpi_get_option( 'ywpi_pro_forma_footer', $document );
			}
			
			if ( $document instanceof YITH_Shipping ) {
				return ywpi_get_option( 'ywpi_packing_slip_footer', $document );
			}
			
			return '';
		}
		
		/**
		 * Retrieve the document title based on the document type
		 *
		 * @param YITH_Document $document
		 *
		 * @author Lorenzo Giuffrida
		 *
		 * @return string
		 *
		 * @since  1.0.0
		 */
		public function get_document_title( $document ) {
			$title = '';
			
			if ( $document instanceof YITH_Invoice ) {
				$title = __( 'Invoice', 'yith-woocommerce-pdf-invoice' );
			} else if ( $document instanceof YITH_Shipping ) {
				$title = __( 'Packing slip', 'yith-woocommerce-pdf-invoice' );
			}
			
			return apply_filters('ywpi_document_title',$title,$document);
		}
		
		/**
		 * Initialize the plugin options
		 *
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function init_plugin_options() {
			$this->preview_mode = "yes" === get_option( 'ywpi_preview_mode', false );
			
			$this->subtotal_incl_discount = "yes" === get_option( 'ywpi_subtotal_inclusive_discount', false );
			$this->enable_packing_slip    = 'yes' == ywpi_get_option( 'ywpi_enable_packing_slip', 'no' );
		}
		
		/**
		 * Add some actions triggered by order status
		 *
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function add_order_status_related_actions() {
			//  If invoice generation is only manual, no automatic actions will be added
			if ( 'auto' != ywpi_get_option( 'ywpi_invoice_generation' ) ) {
				return;
			}
			
			if ( 'new' === ywpi_get_option( 'ywpi_create_invoice_on' ) ) {
				add_action( 'woocommerce_order_status_on-hold', array( $this, 'create_automatic_invoice' ) );
			} else if ( 'processing' === ywpi_get_option( 'ywpi_create_invoice_on' ) ) {
				add_action( 'woocommerce_order_status_processing', array( $this, 'create_automatic_invoice' ) );
			} else if ( 'completed' === ywpi_get_option( 'ywpi_create_invoice_on' ) ) {
				add_action( 'woocommerce_order_status_completed', array( $this, 'create_automatic_invoice' ) );
			}
		}
		
		/**
		 * Create an invoice for a specific order
		 *
		 * @param int $order_id the order id
		 *
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function create_automatic_invoice( $order_id ) {
			//  In "Preview mode" it's not possible to generate automatically documents
			if ( $this->preview_mode ) {
				return;
			}
			
			//  Lets a third party plugin to stop the creation of invoices in automatic mode
			if ( ! apply_filters( 'yith_ywpi_create_automatic_invoices', true, $order_id ) ) {
				return;
			}
			
			$this->create_document( $order_id, 'invoice' );
		}
		
		/**
		 * Retrieve the link for a specific action on the order
		 *
		 * @param string $action   The action to perform. It could be 'view','create', 'reset' or 'preview'
		 * @param string $type     The type of document. It could be 'invoice', 'packing-slip', 'credit-note', 'proforma'
		 * @param int    $order_id The order id
		 *
		 * @return string
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function get_action_url( $action, $type, $order_id ) {
			
			$args = array(
				'action' => $action,
				'type'   => $type,
				'id'     => $order_id,
			);
			
			$nonce_action = $action . $type . $order_id;
			
			return esc_url( wp_nonce_url( add_query_arg( $args ), $nonce_action ) );
		}
		
		
		/**
		 * Attach the documents to the email
		 *
		 * @param array  $attachments
		 * @param string $status
		 * @param mixed  $object
		 *
		 * @return array
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function attach_documents_to_email( $attachments, $status, $object ) {
			
			if ( ! $object instanceof WC_Order ) {
				return $attachments;
			}
			
			$invoice_path = $this->get_invoice_attachment( $status, yit_get_prop( $object, 'id' ) );
			
			if ( $invoice_path ) {
				$attachments[] = $invoice_path;
			}
			
			$pro_forma_path = $this->get_pro_forma_attachment( $status, yit_get_prop( $object, 'id' ) );
			if ( $pro_forma_path ) {
				$attachments[] = $pro_forma_path;
			}
			
			return $attachments;
		}
		
		/**
		 * Retrieve the order invoice path
		 *
		 * @param string $status   current order status
		 * @param int    $order_id order id
		 *
		 * @return string
		 */
		public function get_invoice_attachment( $status, $order_id ) {
			
			$allowed_statuses = apply_filters( 'ywpi_attach_invoice_on_order_status', array(
				'customer_invoice',
				'customer_processing_order',
				'customer_completed_order',
			) );
			
			if ( isset( $status ) && in_array( $status, $allowed_statuses ) ) {
				$invoice = new YITH_Invoice( $order_id );
				
				if ( $invoice->is_valid() && $invoice->generated() ) {
					return $invoice->get_full_path();
				}
			}
			
			return '';
		}
		
		/**
		 * Retrieve the pro-forma invoice path
		 *
		 * @param string $status   current order status
		 * @param int    $order_id order id
		 *
		 * @return string
		 */
		public function get_pro_forma_attachment( $status, $order_id ) {
			
			return '';
		}
		
		/**
		 * Add front-end button for actions available for customers
		 */
		public function add_buttons_on_customer_orders_page() {
			/**
			 * Show print invoice button on frontend orders page
			 */
			add_action( 'woocommerce_my_account_my_orders_actions', array(
				$this,
				'print_invoice_button',
			), 10, 2 );
		}
		
		/**
		 * Add back-end buttons for actions available for admins
		 */
		public function add_features_on_admin_orders_page() {
			add_action( 'manage_shop_order_posts_custom_column', array(
				$this,
				'show_invoice_custom_column_data',
			), 99 );

		}
		
		/**
		 * Append invoice information on order_title column, if current order has an invoice associated
		 *
		 * @param string $column the column name being shown
		 */
		public function show_invoice_custom_column_data( $column ) { 
			global $post;
			$column_to_check = version_compare ( WC ()->version, '3.3', '<' ) ? 'order_title' : 'order_number' ;
			if ( $column_to_check != $column  ) {
				return;
			}

			$this->show_invoice_information_link( $post );
		}
		
		/**
		 * show a link with the order invoiced status
		 *
		 * @param WP_Post $post
		 *
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function show_invoice_information_link( $post ) {
			$order = wc_get_order( $post );
			
			$invoice = new YITH_Invoice( yit_get_prop( $order, 'id' ) );
			
			if ( ! $invoice->is_valid() || ! $invoice->generated() ) {
				return;
			}
			
			$url = $this->get_action_url( 'view', 'invoice', yit_get_prop( $order, 'id' ) );

			?>
			<div class="ywpi-invoiced-order">
				<a class="meta ywpi-invoice-information"
				   target="_blank" href="<?php echo $url; ?>"
				   title="<?php _e( "View Invoice", 'yith-woocommerce-pdf-invoice' ); ?>">
					<?php echo sprintf( __( "View Invoice No. %s", 'yith-woocommerce-pdf-invoice' ), $invoice->get_formatted_document_number() ); ?>
				</a>
			</div>
			<?php
		}
		
		
		/**
		 * Check nounce when an action for generating documents is called
		 *
		 * @param YITH_Document $document the document that is going to be created
		 *
		 * @return bool
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function check_invoice_url_for_action( $document ) {
			
			if ( ! $document ) {
				return false;
			}
			
			//  Check if the document is for a valid order
			if ( ! $document->is_valid() ) {
				return false;
			}
			
			return true;
		}
		
		
		/**
		 * Create a new document of the type requested, for a specific order
		 *
		 * @param  int   $order_id      the order id for which the document is created
		 * @param string $document_type the document type to be generated
		 *
		 * @return YITH_Document|null
		 */
		public function create_document( $order_id, $document_type = '' ) {
			
			//  In "Preview mode" it's not possible to generate valid documents
			if ( $this->preview_mode ) {
				return null;
			}
			
			if ( ! apply_filters( 'yith_ywpi_can_create_document', true, $order_id, $document_type ) ) {
				return null;
			}
			
			$document = ywpi_get_order_document_by_type( $order_id, $document_type );
			
			if ( null == $document ) {
				return null;
			}
			
			if ( ! $document instanceof YITH_Document ) {
				return null;
			}
			
			/**
			 * If a document of that type exists, return it instead of creating a new one
			 */
			if ( $document->generated() ) {
				return $document;
			}
			
			/**
			 * For YITH_Invoice and YITH_Credit_Note, assign a new unique number and set other details
			 */
			if ( ( $document instanceof YITH_Invoice ) ||
			     ( $document instanceof YITH_Credit_Note )
			) {
				
				//  Set the document value
				$document->date = apply_filters( 'yith_ywpi_set_document_date', current_time( 'mysql', 0 ), $document );
				
				$document_type    = $document instanceof YITH_Invoice ? 'invoice' : 'credit-note';
				$document->number = $this->get_next_number( $document, $document_type );
				
				$prefix           = $document instanceof YITH_Invoice ? 'ywpi_invoice_prefix' : 'ywpi_credit_note_prefix';
				$document->prefix = $this->replace_placeholders( ywpi_get_option( $prefix, $document->order ), $document->date );
				
				$suffix           = $document instanceof YITH_Invoice ? 'ywpi_invoice_suffix' : 'ywpi_credit_note_suffix';
				$document->suffix = $this->replace_placeholders( ywpi_get_option( $suffix, $document->order ), $document->date );
				
				$formatted_key    = $document instanceof YITH_Invoice ? 'ywpi_invoice_number_format' : 'ywpi_credit_note_number_format';
				$formatted_number = ywpi_get_option_with_placeholder( $formatted_key, '[number]' );

				$order_number = $document->order instanceof WC_Order ? $document->order->get_order_number() : '';
				
				$date = getdate( strtotime( $document->date ) );
				
				$replace_placeholders = str_replace(
					array(
						'[prefix]',
						'[suffix]',
						'[number]',
						'[year]',
						'[month]',
						'[day]',
						'[order_number]'
					),
					array(
						$document->prefix,
						$document->suffix,
						$document->number,
						$date['year'],
						sprintf( "%02d", $date['mon'] ),
						sprintf( "%02d", $date['mday'] ),
						$order_number
					),
					$formatted_number );
				
				$document->formatted_number = apply_filters( 'yith_ywpi_formatted_invoice_number', $replace_placeholders, $formatted_number, $document );
			}
			
			
			if ( $this->create_pdf_file( $document ) ) {
				/*  Some document type will cause the next available number to be incremented */
				$this->increment_next_document_number( $document );
				
				$document->save();
				do_action( 'yith_ywpi_document_created', $document );
			}
			
			return $document;
		}


        /**
         * Regenerate the document for the specific order
         *
         * @param  int   $order_id      the order id for which the document is created
         * @param string $document_type the document type to be generated
         *
         * @return YITH_Document|null
         */
        public function regenerate_document( $order_id, $document_type = '' ) {

            //  In "Preview mode" it's not possible to generate valid documents
            if ( $this->preview_mode ) {
                return null;
            }

            if ( ! apply_filters( 'yith_ywpi_can_create_document', true, $order_id, $document_type ) ) {
                return null;
            }

            $document = ywpi_get_order_document_by_type( $order_id, $document_type );

            if ( null == $document ) {
                return null;
            }

            if ( ! $document instanceof YITH_Document ) {
                return null;
            }

            /**
             * For YITH_Invoice and YITH_Credit_Note, assign a new unique number and set other details
             */
            if ( ( $document instanceof YITH_Invoice ) ||
                ( $document instanceof YITH_Credit_Note )
            ) {

                //  Set the document value
                apply_filters( 'yith_ywpi_set_document_date', $document->date, $document );


                $formatted_key    = $document instanceof YITH_Invoice ? 'ywpi_invoice_number_format' : 'ywpi_credit_note_number_format';
                $formatted_number = ywpi_get_option_with_placeholder( $formatted_key, '[number]' );

                $date = getdate( strtotime( $document->date ) );

				$order_number = $document->order instanceof WC_Order ? $document->order->get_order_number() : '';

                $replace_placeholders = str_replace(
                    array(
                        '[prefix]',
                        '[suffix]',
                        '[number]',
                        '[year]',
                        '[month]',
                        '[day]',
						'[order_number]'
                    ),
                    array(
                        $document->prefix,
                        $document->suffix,
                        $document->number,
                        $date['year'],
                        sprintf( "%02d", $date['mon'] ),
                        sprintf( "%02d", $date['mday'] ),
						$order_number
                    ),
                    $formatted_number );

                $document->formatted_number = apply_filters( 'yith_ywpi_formatted_invoice_number', $replace_placeholders, $formatted_number, $document );
            }


            if ( $this->create_pdf_file( $document ) ) {

                $document->save();
                do_action( 'yith_ywpi_document_created', $document );
            }

            return $document;
        }
		
		/**
		 * Show a preview of the document requested, without affecting the counter and settings that should be used only for real invoices
		 *
		 * @param int    $order_id      the order id
		 * @param string $document_type the document type requested
		 *
		 * @return null
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function view_preview( $order_id, $document_type = '' ) {
			$document = ywpi_get_order_document_by_type( $order_id, $document_type );
			
			if ( ( null == $document ) || ! $this->check_invoice_url_for_action( $document ) ) {
				return null;
			}
			
			$this->create_pdf_file( $document );
			$this->show_file( $document->get_full_path() );
		}
		
		/**
		 * Return the next available invoice number
		 *
		 * @param YITH_Document $document the document that need a new invoice number
		 * @param string        $type     the type of document, 'invoice'(default) or 'credit-note'
		 *
		 * @return int|mixed
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		private function get_next_number( $document, $type = 'invoice' ) {
			/** $document should be an instance of  YITH_Invoice or YITH_Credit_Note */
			if ( ! ( $document instanceof YITH_Invoice ) &&
			     ! ( $document instanceof YITH_Credit_Note )
			) {
				return;
			}
			
			$reset_option     = 'invoice' == $type ? 'ywpi_invoice_reset' : 'ywpi_credit_note_reset';
			$last_year_option = 'invoice' == $type ? 'ywpi_invoice_year_billing' : 'ywpi_credit_note_year_billing';
			
			//  Check if this is the first invoice of the year, in this case, if reset on new year is enabled, restart from 1
			if ( 'yes' === get_option( $reset_option ) ) {
				$last_year = get_option( $last_year_option );
				
				if ( isset( $last_year ) && is_numeric( $last_year ) ) {
					$current_year = getdate();
					$current_year = $current_year['year'];
					
					if ( $last_year < $current_year ) {
						//  set new year as last invoiced year and reset invoice number
						ywpi_update_option( $last_year_option, $current_year );
						
						return 1;
					}
				}
			}
			
			$number_option          = 'invoice' == $type ? 'ywpi_invoice_number' : 'ywpi_credit_note_next_number';
			$current_invoice_number = ywpi_get_option( $number_option, $document );
			
			if ( ! isset( $current_invoice_number ) || ! is_numeric( $current_invoice_number ) ) {
				$current_invoice_number = 1;
			}
			
			return $current_invoice_number;
		}
		
		
		/**
		 * Save the next available invoice number
		 *
		 * @param YITH_Invoice $document
		 *
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		private function increment_next_document_number( $document ) {
			/** $document should be an instance of  YITH_Invoice or YITH_Credit_Note */
			if ( ! ( $document instanceof YITH_Invoice ) &&
			     ! ( $document instanceof YITH_Credit_Note )
			) {
				return;
			}
			
			$number_option = ( $document instanceof YITH_Invoice ) ? 'ywpi_invoice_number' : 'ywpi_credit_note_next_number';
			
			ywpi_update_option( $number_option, intval( $document->number ) + 1, $document );
		}
		
		
		/**
		 * Replace fixed placeholders from a specific string
		 *
		 * @param string $text the string to be parsed
		 * @param string $date
		 *
		 * @return mixed
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		private function replace_placeholders( $text, $date ) {
			
			$date = getdate( strtotime( $date ) );
			
			$replaced_text = str_replace(
				array(
					'[year]',
					'[month]',
					'[day]',
				),
				array(
					$date['year'],
					sprintf( "%02d", $date['mon'] ),
					sprintf( "%02d", $date['mday'] ),
				),
				$text );
			
			return $replaced_text;
		}
		
		/**
		 * Show a document
		 *
		 * @param int    $order_id      the order id
		 * @param string $document_type the type of document to show
		 *
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function view_document( $order_id, $document_type ) {
			$document = ywpi_get_order_document_by_type( $order_id, $document_type );
			
			if ( ( null == $document ) || ! $this->check_invoice_url_for_action( $document ) ) {
				return;
			}
			
			$this->show_file( $document->get_full_path() );
		}
		
		/**
		 * Show a file on browser or ask for download, according with related option
		 *
		 * @param YITH_Document|string $resource the document to show or the path of the file to be shown
		 */
		public function show_file( $resource ) {
			
			$path = $resource;
			if ( $resource instanceof YITH_Document ) {
				$path = $resource->get_full_path();
			}
			
			if ( 'open' == ywpi_get_option( 'ywpi_pdf_invoice_behaviour' ) ) {
				header( 'Content-type: application/pdf' );
				header( 'Content-Disposition: inline; filename = "' . basename( $path ) . '"' );
				header( 'Content-Transfer-Encoding: binary' );
				header( 'Content-Length: ' . filesize( $path ) );
				header( 'Accept-Ranges: bytes' );
				@readfile( $path );
				exit();
			} else {
				header( "Content-type: application/pdf" );
				header( 'Content-Disposition: attachment; filename = "' . basename( $path ) . '"' );
				@readfile( $path );
				exit();
			}
		}
		
		/**
		 * Check if the current user can delete a document type for a specific order
		 *
		 * @param int    $order_id
		 * @param string $document_type
		 *
		 * @return bool
		 */
		public function user_can_delete_document( $order_id, $document_type ) {
			
			$enabled_capabilities = apply_filters( 'yith_ywpi_delete_document_capabilities',
				array( 'manage_woocommerce' ), $order_id, $document_type );
			
			if ( $enabled_capabilities ) {
				foreach ( $enabled_capabilities as $enabled_capability ) {
					if ( current_user_can( $enabled_capability ) ) {
						return true;
					}
				}
			}
			
			return false;
		}
		
		/**
		 * Cancel an order document
		 *
		 * @param int    $order_id      the order id
		 * @param string $document_type the type of document to reset
		 *
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function reset_document( $order_id, $document_type ) {
			
			if ( $this->user_can_delete_document( $order_id, $document_type ) ) {
				
				$document = ywpi_get_order_document_by_type( $order_id, $document_type );
				
				if ( $this->check_invoice_url_for_action( $document ) ) {
					$document->reset();
				}
			}
		}
		
		
		/**
		 * Add a button to print invoice, if exists, from order page on frontend.
		 *
		 * @param array    $actions current actions
		 * @param WC_Order $order   the order
		 *
		 * @return array
		 */
		public function print_invoice_button( $actions, $order ) {
			$invoice = new YITH_Invoice( yit_get_prop( $order, 'id' ) );
			
			if ( $invoice->generated() ) {
				// Add the print button
				$actions['print-invoice'] = array(
					'url'  => $this->get_action_url( 'view', 'invoice', yit_get_prop( $order, 'id' ) ),
					'name' => __( 'Invoice', 'yith-woocommerce-pdf-invoice' ),
				);
			}
			
			return $actions;
		}
		
		/**
		 * replace the customer details using the plugin option pattern
		 *
		 * @param string $pattern
		 * @param int    $order_id
		 *
		 * @return string
		 *
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function replace_customer_details_pattern( $pattern, $order_id ) {
			
			preg_match_all( "/{{([^}}]*)}}/", $pattern, $matches );
			
			$customer_details = $pattern;
			if ( isset( $matches[1] ) ) {
				foreach ( $matches[1] as $match ) {

					$replace_value = yit_get_prop( wc_get_order( $order_id ), $match, true );

					//  Convert country code and convert it to the country name
					if ( ( '_billing_country' == $match ) || ( '_shipping_country' == $match ) ) {
						$countries = WC()->countries->get_countries();
						if ( isset( $countries[ $replace_value ] ) ) {
							$replace_value = $countries[ $replace_value ];
						}
					}

                    $replace_value = apply_filters( 'yith_ywpi_replace_customer_details', $replace_value, $match, $order_id );

                    /*
                     * Integration with YITH WooCommerce EU VAT
                     */
					if( ('_yith_eu_vat') == $match ){
						$eu_vat_data = yit_get_prop( wc_get_order( $order_id ), '_ywev_order_vat_paid', true );
						$replace_value = (is_array($eu_vat_data) && array_key_exists( 'vat_number', $eu_vat_data )) ? $eu_vat_data["vat_number"] : '';
					}

                    /*
                     * Integration with YITH WooCommerce Checkout Manager
                     */
					if( defined('YWCCP') ){
                        $fields = ywccp_get_custom_fields( 'additional' );
                        foreach ( $fields as $key => $field ) {
                            if( $key == $match || '_'.$key == $match ){
                                $key = apply_filters('yith_ywpi_checkout_manager_additional_field_key',$key);
                                $replace_value = yit_get_prop( wc_get_order( $order_id ), $key, true );
                            }
                        }
                    }

					do_action('yith_ywpi_before_replace_customer_details');

					$customer_details = str_replace( "{{" . $match . "}}", $replace_value, $customer_details );
				}
			}
			
			// Clean up white space
			$replace_details = preg_replace( '/  +/', ' ', trim( $customer_details ) );
			
			$replace_details = preg_replace( '/\n\n+/', "\n", $replace_details );
			
			// Break newlines apart and remove empty lines/trim commas and white space
			$replace_details = explode( "\n", $replace_details );


			// Add html breaks
			$replace_details = implode( '<br/>', apply_filters( 'yith_pdf_invoice_customer_details_pattern', $replace_details ) );
			
			return $replace_details;
		}
		
		/**
		 * Get the customer billing details
		 *
		 * @param int $order_id the order
		 *
		 * @return string
		 *
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 *
		 */
		public function get_customer_billing_details( $order_id ) {
			$customer_details = ywpi_get_option( 'ywpi_customer_billing_details', '' );
			
			return $this->replace_customer_details_pattern( $customer_details, $order_id );
		}
		
		/**
		 * Get the customer shipping details
		 *
		 * @param int $order_id
		 *
		 * @return string
		 *
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 *
		 */
		public function get_customer_shipping_details( $order_id ) {
			$customer_details = ywpi_get_option( 'ywpi_customer_shipping_details', '' );
			
			return $this->replace_customer_details_pattern( $customer_details, $order_id );
		}
		
		
		/**
		 * Return the folder where documents have to be stored. Create the folder path if not exists.
		 *
		 * @param YITH_Document $document
		 *
		 * @return string
		 */
		private function create_storing_folder( $document ) {
			
			/* Create folders for storing documents */
			$folder_path = get_option( 'ywpi_invoice_folder_format' );
			
			$date_val = strtotime( yit_get_prop( $document->order, 'order_date' ) );
			
			$date = getdate( $date_val );
			
			$folder_path = str_replace(
				array(
					'[year]',
					'[month]',
					'[day]',
				),
				array(
					$date['year'],
					sprintf( "%02d", $date['mon'] ),
					sprintf( "%02d", $date['mday'] ),
				),
				$folder_path );
			
			$folder_path = apply_filters( 'ywpi_storing_folder', $folder_path, $document );
			
			if ( ! file_exists( YITH_YWPI_DOCUMENT_SAVE_DIR . $folder_path ) ) {
				wp_mkdir_p( YITH_YWPI_DOCUMENT_SAVE_DIR . $folder_path );
			}
			
			return $folder_path;
		}
		
		
		/**
		 * Retrieve a PDF file for a specific document
		 *
		 * @param YITH_Document $document the document for which a PDF file should be created
		 *
		 * @return int
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function create_pdf_file( $document ) {
			$pdf_content = $this->generate_template( $document );
			
			$document->save_folder = $this->create_storing_folder( $document );
			$document->save_path   = sprintf( "%s.pdf", $this->get_document_filename( $document ) );
			
			return file_put_contents( $document->get_full_path(), $pdf_content );
		}
		
		/**
		 * Return the filename associated to the document, based on plugin settings.
		 *
		 * @param YITH_Document $document
		 *
		 * @return mixed|string|void
		 */
		private function get_document_filename( $document ) {
			
			$pattern = '';
			if ( ( $document instanceof YITH_Invoice ) ||
			     ( $document instanceof YITH_Credit_Note )
			) {
				
				$option_name = ( $document instanceof YITH_Invoice ) ? 'ywpi_invoice_filename_format' : 'ywpi_credit_note_filename_format';

				// Filter to change filename format of invoice or credit note document. Use placeholder [number] to get automatically order of invoice or credit note
				$pattern     = apply_filters('ywpi_pattern_filename_invoice_or_credit_note', ywpi_get_option_with_placeholder( $option_name, '[number]' ), $document);
				
				$pattern = str_replace(
					array(
						'[number]',
						'[prefix]',
						'[suffix]',
					),
					array(
						$document->number,
						$document->prefix,
						$document->suffix,
					),
					$pattern );
			} else if ( $document instanceof YITH_Pro_Forma ) {
				// Filter to change filename format of proforma document. Use placeholder [order_number] to get order number
				$pattern = apply_filters('ywpi_pattern_filename_proforma',ywpi_get_option_with_placeholder( 'ywpi_pro_forma_invoice_filename_format', '[order_number]'),$document );
				
				$pattern = str_replace( '[order_number]', $document->order->get_order_number(), $pattern );
			} else if ( $document instanceof YITH_Shipping ) {
				// Filter to change filename format of packing slip document. Use placeholder [order_number] to get order number
				$pattern = apply_filters('ywpi_pattern_filename_shipping',ywpi_get_option_with_placeholder( 'ywpi_packing_slip_filename_format', '[order_number]' ),$document);
				$pattern = str_replace( '[order_number]', $document->order->get_order_number(), $pattern );
			}
			
			
			//  Substitute date placeholders
			$order_date = strtotime( yit_get_prop( $document->order, 'order_date' ) );
			$date       = getdate( $order_date );
			
			$pattern = str_replace(
				array(
					'[year]',
					'[month]',
					'[day]',
				),
				array(
					$date['year'],
					sprintf( "%02d", $date['mon'] ),
					sprintf( "%02d", $date['mday'] ),
				),
				$pattern );
			
			//  Sanitize the filename
			$pattern = preg_replace( '/[^a-z0-9-_.\s]/i', '_', $pattern );
			
			return $pattern;
		}
		
		
		//  ------------------------------------------------------------------------------------------------------------------
		
		/**
		 * Set a maximum execution time
		 *
		 * @param int $seconds time in seconds
		 */
		private function set_time_limit( $seconds ) {
			$check_safe_mode = ini_get( 'safe_mode' );
			if ( ( ! $check_safe_mode ) || ( 'OFF' == strtoupper( $check_safe_mode ) ) ) {
				
				@set_time_limit( $seconds );
			}
		}
		
		private function generate_template_mpdf( $document ) {
			$this->set_time_limit( 120 );
			
			ob_start();
			wc_get_template( 'yith-pdf-invoice/invoice-template.php',
				array(
					'document'   => $document,
					'main_class' => apply_filters( 'yith_ywpi_add_body_class', '' ),
				),
				'',
				YITH_YWPI_TEMPLATE_DIR );
			$html = ob_get_clean();
			$html = apply_filters( 'yith_ywpi_before_pdf_rendering_html', $html, $document );

			ob_start();
			wc_get_template( 'yith-pdf-invoice/invoice-style.css',
				null,
				'',
				YITH_YWPI_TEMPLATE_DIR );
			$style = ob_get_clean();
			
			ob_start();
			wc_get_template( 'yith-pdf-invoice/document-footer.php',
				array(
					'document' => $document,
				),
				'',
				YITH_YWPI_TEMPLATE_DIR );
			$footer = ob_get_clean();
			
			if ( ! class_exists( 'mPDF' ) ) {
				require_once( YITH_YWPI_LIB_DIR . 'mpdf60/mpdf.php' );
			}
			
			$mpdf_args = apply_filters('yith_ywpdi_mpdf_args','');
			if( is_array($mpdf_args) ){
				$mpdf = new mPDF( 
					$mpdf_args['mode'],
					$mpdf_args['format'],
					$mpdf_args['default_font_size'],
					$mpdf_args['default_font'],
					$mpdf_args['mgl'],
					$mpdf_args['mgr'],
					$mpdf_args['mgt'],
					$mpdf_args['mgb'],
					$mpdf_args['mgh'],
					$mpdf_args['mgf'],
					$mpdf_args['orientation']
				);
			}else{
				$mpdf = new mPDF();
			}
			
			
			//$mpdf->SetHTMLFooter( $footer );
			
			// write html
			$mpdf->WriteHTML( $style, 1 );
			$mpdf->WriteHTML( $html, 2 );

			// The next call will store the entire PDF as a string in $pdf
			$pdf = $mpdf->Output( 'document', 'S' );

			return $pdf;
		}
		
		
		/**
		 * Generate the template for a document
		 *
		 * @param YITH_Document $document the document to create
		 *
		 * @return string
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		private function generate_template( $document ) {
			
			do_action('yith_ywpdi_before_generate_template_mpdf');
			
			$pdf_content = $this->generate_template_mpdf( $document );
			
			return $pdf_content;
		}
	}
}