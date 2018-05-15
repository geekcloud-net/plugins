<?php
if ( ! defined ( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists ( 'YITH_WooCommerce_Pdf_Invoice_Premium' ) ) {
	
	/**
	 * Implements features of YITH WooCommerce Pdf Invoice
	 *
	 * @class   YITH_WooCommerce_Pdf_Invoice_Premium
	 * @package Yithemes
	 * @since   1.0.0
	 * @author  Your Inspiration Themes
	 */
	class YITH_WooCommerce_Pdf_Invoice_Premium extends YITH_WooCommerce_Pdf_Invoice {
		
		/**
		 * @var bool set if pro-forma invoice are available
		 */
		public $enable_pro_forma = false;
		
		/**
		 * @var bool set if a pro-forma invoice should be sent
		 */
		public $send_pro_forma = false;
		
		/**
		 * @var bool set if credit notes are enabled
		 */
		public $enable_credit_note = false;
		
		/**
		 * Single instance of the class
		 *
		 * @since 1.0.0
		 */
		protected static $instance;
		
		/**
		 * Returns single instance of the class
		 *
		 * @since 1.0.0
		 */
		public static function get_instance() {
			if ( is_null ( self::$instance ) ) {
				self::$instance = new self();
			}
			
			return self::$instance;
		}
		
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
			parent::__construct ();
			
			add_filter ( 'woocommerce_admin_settings_sanitize_option_ywpi_dropbox_key', array(
				$this,
				'custom_save_ywpi_dropbox'
			) );
			
			
			add_action ( 'woocommerce_update_option', array( $this, 'copy_invoice_logo_to_local_path' ) );
			
			/**
			 * Sync new documents with DropBox
			 */
			add_action ( 'yith_ywpi_document_created', array(
					$this,
					'sync_to_dropbox'
				)
			);
			
			/**
			 * Check for third party plugin compatibility
			 */
			$this->check_third_part_compatibility ();
			
			/**
			 * Add some fields on checkout process, that can be added on invoices.
			 */
			YITH_Checkout_addon::get_instance ()->initialize ();
			
			add_action ( 'yith_pdf_invoice_before_total', array(
				$this,
				'show_gift_card_discount'
			) );
		}
		
		public function show_gift_card_discount( $order ) {
			$gift_card_total = get_post_meta ( yit_get_prop ( $order, 'id' ), '_ywgc_applied_gift_cards_totals', true );
			
			if ( ! empty( $gift_card_total ) ) {
				ob_start ();
				?>
				<tr>
					<td class="left-content column-product"><?php _e ( "Gift card discount:", 'yith-woocommerce-pdf-invoice' ); ?></td>
					<td class="right-content column-total"><?php echo wc_price ( $gift_card_total ); ?></td>
				</tr>
				<?php
				$res = ob_get_clean ();
				echo apply_filters ( 'yith_pdf_invoice_show_gift_card_amount', $res, $gift_card_total, $order );
			}
		}
		
		/**
		 * Sync new documents with DropBox
		 */
		public function sync_to_dropbox( $document ) {
			
			if ( ywpi_get_option( 'ywpi_dropbox_access_token' ) ) {
				YITH_PDF_Invoice_DropBox::get_instance()->send_document_to_dropbox( $document );
			}
		}
		
		/**
		 * Retrive the document title
		 *
		 * @param YITH_Document $document the document
		 *
		 * @author Lorenzo Giuffrida
		 * @return string
		 * @since  1.0.0
		 */
		public function get_document_title( $document ) {
			
			if ( $document instanceof YITH_Pro_Forma ) {
				$title = apply_filters('yith_ywpi_proforma_document_title',__ ( 'Pro-forma Invoice', 'yith-woocommerce-pdf-invoice' ),10,1);
				
				return $title;
			} elseif ( $document instanceof YITH_Credit_Note ) {
				$title = apply_filters('yith_ywpi_credit_note_document_title',__ ( 'Credit note', 'yith-woocommerce-pdf-invoice' ),10,1);

				return $title;
			}
			
			return parent::get_document_title ( $document );
		}
		
		/**
		 * Initialize the plugin options
		 *
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function init_plugin_options() {
			$this->enable_pro_forma   = ( 'yes' == ywpi_get_option ( 'ywpi_enable_pro_forma' ) );
			$this->send_pro_forma     = ( 'yes' == ywpi_get_option ( 'ywpi_send_pro_forma' ) );
			$this->enable_credit_note = ( 'yes' == ywpi_get_option ( 'ywpi_enable_credit_notes' ) );
			
			parent::init_plugin_options ();
		}
		
		/**
		 * Show the document section with the document data like number, data, ...
		 *
		 * @param YITH_Document $document
		 */
		public function yith_ywpi_template_document_details( $document ) {
			
			if ( $document instanceof YITH_Pro_Forma ) {
				wc_get_template ( 'yith-pdf-invoice/document-data-proforma.php',
					array(
						'document' => $document,
					),
					'',
					YITH_YWPI_TEMPLATE_DIR );
			}
		}
		
		/**
		 * Make a local copy of the image to be used as company logo
		 *
		 * @param array $option
		 *
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function copy_invoice_logo_to_local_path( $option ) {
			if ( 'ywpi_company_logo' === $option["id"] ) {
				if ( ! empty( $_POST["ywpi_company_logo"] ) ) {
					
					$upload_dir = wp_upload_dir ();
					$local_path = str_replace ( $upload_dir["baseurl"], $upload_dir["basedir"], $_POST["ywpi_company_logo"] );
					copy ( $local_path, YITH_YWPI_INVOICE_LOGO_PATH );
				}
			}
		}
		
		
		/**
		 * Check for third party plugin compatibility needs
		 *
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function check_third_part_compatibility() {
			if ( ywpi_is_active_woo_eu_vat_number () ) {
				add_filter ( 'ywpi_general_options', array( $this, 'add_vat_number_source_option' ) );
			}
		}
		
		/**
		 * Add an option to choose the VAT number source
		 *
		 * @param array $settings current option list
		 *
		 * @return mixed
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function add_vat_number_source_option( $settings ) {
			$vat_number_source = array(
				'name'    => __ ( 'VAT number source', 'yith-woocommerce-pdf-invoice' ),
				'id'      => 'ywpi_ask_vat_number_source',
				'type'    => 'radio',
				'options' => array(
					'yith'          => "Show the standard VAT number field from YITH WooCommerce PDF Invoice",
					'eu-vat-number' => "Use the VAT number field from WooThemes EU VAT number plugin",
				),
				'default' => 'yith',
			);
			
			$settings['general'] = array_slice ( $settings['general'], 0, count ( $settings['general'] ) - 2, true ) +
			                       array( 'vat_number_source' => $vat_number_source ) +
			                       array_slice ( $settings['general'], 3, count ( $settings['general'] ) - 1, true );
			
			return $settings;
		}
		
		/**
		 * Check if there are action to be done at this moment, related to back end
		 *
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function execute_generation_actions() {
			//  Check actions from the base version
			parent::execute_generation_actions ();
			
			//  Check generation actions
			if ( isset( $_GET[ YITH_YWPI_RESET_DROPBOX ] ) ) {
				YITH_PDF_Invoice_DropBox::get_instance ()->disable_dropbox_backup ();
				
				wp_redirect ( esc_url_raw ( remove_query_arg ( YITH_YWPI_RESET_DROPBOX ) ) );
			}
		}
		
		/**
		 * add the right action based on GET var current used
		 *
		 */
		public function execute_visualization_actions() {
			parent::execute_visualization_actions ();
			
			if ( isset( $_GET[ YITH_YWPI_CREATE_PRO_FORMA_INVOICE_ACTION ] ) ) {
				$this->get_pro_forma_invoice ( $_GET[ YITH_YWPI_CREATE_PRO_FORMA_INVOICE_ACTION ] );
			}
		}
		
		/**
		 * Create a new pro-forma invoice document if not exists, else return the previous one
		 *
		 * @param int $order_id the order id for which the document is created
		 */
		public function get_pro_forma_invoice( $order_id ) {
			
			if ( ! $this->enable_pro_forma ) {
				return;
			}
			
			$document = $this->create_document ( $order_id, 'proforma' );
			
			/*
			 * Check for url validation
			 */
			if ( ! $document ) {
				return;
			}
			
			//  Create the file and show it
			$this->show_file ( $document );
		}
		
		
		public function custom_save_ywpi_dropbox() {
			
			YITH_PDF_Invoice_DropBox::get_instance ()->custom_save_ywpi_dropbox ();
		}
		
		
		/**
		 * Add a button to print invoice, if exists, from order page on frontend.
		 * If not exists add a button for a pro-forma document.
		 *
		 * @param array    $actions current actions
		 * @param WC_Order $order   the order being shown
		 *
		 * @return mixed
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function print_invoice_button( $actions, $order ) {
			
			$invoice = new YITH_Invoice( yit_get_prop ( $order, 'id' ) );
			
			if ( $invoice->generated () ) {
				// Add the print button
				$actions['print-invoice'] = array(
					'url'  => $this->get_action_url ( 'view', 'invoice', yit_get_prop ( $order, 'id' ) ),
					'name' => __ ( 'Invoice', 'yith-woocommerce-pdf-invoice' ),
				);
			} else if ( $this->enable_pro_forma && apply_filters ( 'yith_ywpi_show_pro_forma_invoice_button_view_order', true, $order ) ) {
				
				$actions['print-pro-forma-invoice'] = array(
					'url'  => $this->get_action_url ( 'preview', 'proforma', yit_get_prop ( $order, 'id' ) ),
					'name' => __ ( 'Pro-Forma', 'yith-woocommerce-pdf-invoice' ),
				);
			}
			
			return apply_filters ( 'yith_ywpi_my_order_actions', $actions, $order );
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
			
			$proforma_path = '';
			
			if ( $this->enable_pro_forma && $this->send_pro_forma ) {
				$attach_to_emails = apply_filters ( 'yith_ywpi_pro_forma_attachment_on_emails_ids',
					array(
						'customer_processing_order',
						'new_order',
						'customer_on_hold_order'
					) );
				
				if ( in_array ( $status, $attach_to_emails ) ) {
					
					$document = $this->create_document ( $order_id, 'proforma' );
					if ( $document && $document->generated () ) {
						$proforma_path = $document->get_full_path ();
					}
				}
			}
			
			return $proforma_path;
		}
	}
}