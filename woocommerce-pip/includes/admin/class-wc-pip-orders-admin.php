<?php
/**
 * WooCommerce Print Invoices/Packing Lists
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade WooCommerce Print
 * Invoices/Packing Lists to newer versions in the future. If you wish to
 * customize WooCommerce Print Invoices/Packing Lists for your needs please refer
 * to http://docs.woocommerce.com/document/woocommerce-print-invoice-packing-list/
 *
 * @package   WC-Print-Invoices-Packing-Lists/Admin/Orders
 * @author    SkyVerge
 * @copyright Copyright (c) 2011-2018, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * PIP Admin Order class
 *
 * Handles customizations to the Orders/Edit Order screens
 *
 * @since 3.0.0
 */
class WC_PIP_Orders_Admin {


	/**
	 * Add various admin hooks/filters
	 *
	 * @since 3.0.0
	 */
	public function __construct() {

		// add 'Print Count' orders page column header
		add_filter( 'manage_edit-shop_order_columns', array( $this, 'add_order_status_column_header' ), 20 );

		// add information to the columns in the orders edit screen
		add_action( 'manage_shop_order_posts_custom_column', array( $this, 'add_order_status_column_content' ), 20, 2 );

		// add invoice information to the order preview
		add_filter( 'woocommerce_admin_order_preview_get_order_details', array( $this, 'add_order_preview_invoice_number' ), 10, 2 );
		add_action( 'woocommerce_admin_order_preview_start',             array( $this, 'display_order_preview_invoice_number' ) );

		// add bulk order filter for printed / non-printed orders
		add_action( 'restrict_manage_posts', array( $this, 'filter_orders_by_print_status') , 20 );
		add_filter( 'request',               array( $this, 'filter_orders_by_print_status_query' ) );

		// add invoice numbers to shop orders search fields
		add_filter( 'woocommerce_shop_order_search_fields', array( $this, 'make_invoice_numbers_searchable' ) );

		// generate invoice number upon order save
		add_action( 'save_post', array( $this, 'generate_invoice_number_order_save' ), 20, 2 );

		// display invoice number on order screen
		add_action( 'woocommerce_admin_order_data_after_order_details', array( $this, 'display_order_invoice_number' ), 42, 1 );

		// add buttons for PIP actions for individual orders in Orders screen table
		add_filter( 'woocommerce_admin_order_actions', array( $this, 'add_order_actions' ), 10, 2 );

		// add bulk actions to the Orders screen table bulk action drop-downs
		add_action( 'admin_footer-edit.php', array( $this, 'add_order_bulk_actions' ) );

		// add actions to individual Order edit screen
		add_filter( 'woocommerce_order_actions', array( $this, 'add_order_meta_box_actions' ) );

		// process orders bulk actions
		add_action( 'load-edit.php', array( $this, 'process_orders_bulk_actions' ) );

		// process individual order actions
		if ( $actions = $this->get_actions() ) {
			foreach ( array_keys( $actions ) as $name ) {
				add_action( "woocommerce_order_action_{$name}", array( $this, 'process_order_actions' ) );
			}
		}

		// add a nonce for individual order actions
		add_action( 'woocommerce_admin_order_data_after_order_details', array( $this, 'send_email_order_action_nonce' ) );

		// handling of individual orders send email actions
		add_action( 'admin_init', array( $this, 'send_email_order_action' ) );

		// display admin notices for bulk actions
		add_action( 'admin_notices', array( $this, 'render_messages' ) );

		// display admin notices for emails sent
		add_action( 'admin_init', array( $this, 'render_email_sent_message' ) );
	}


	/**
	 * Render any messages set by bulk actions
	 *
	 * @since 3.0.0
	 * @param WP_Screen $current_screen
	 */
	public function render_messages( $current_screen = null ) {

		if ( ! $current_screen instanceof WP_Screen ) {
			$current_screen = get_current_screen();
		}

		if ( isset( $current_screen->id ) && in_array( $current_screen->id, array( 'shop_order', 'edit-shop_order' ), true ) ) {

			if ( ( $bulk_action_message_opt = get_option( '_woocommerce_pip_bulk_action_confirmation' ) ) && is_array( $bulk_action_message_opt ) ) {

				$user_id = key( $bulk_action_message_opt );

				if ( get_current_user_id() !== (int) $user_id ) {
					return;
				}

				$handler = wc_pip()->get_message_handler();
				$message = wp_kses_post( current( $bulk_action_message_opt ) );

				$handler->add_message( $message );

				delete_option( '_woocommerce_pip_bulk_action_confirmation' );
			}

			wc_pip()->get_message_handler()->show_messages( array(
				'capabilities' => wc_pip()->get_handler_instance()->get_admin_capabilities(),
			) );
		}
	}


	/**
	 * Process bulk actions
	 *
	 * @since 3.0.0
	 */
	public function process_orders_bulk_actions() {
		global $typenow;

		if ( 'shop_order' === $typenow ) {

			// Get the bulk action
			$wp_list_table = _get_list_table( 'WP_Posts_List_Table' );
			$action        = $wp_list_table->current_action();
			$order_ids     = array();

			// Return if not processing PIP actions
			if ( ! $action || ! array_key_exists( $action, $this->get_bulk_actions() ) ) {
				return;
			}

			// Make sure order IDs are submitted
			if ( isset( $_REQUEST['post'] ) ) {
				$order_ids = array_map( 'absint', $_REQUEST['post'] );
			}

 			// Return if there are no orders to print
 			if ( ! $order_ids ) {
				return;
 			}

			// Get document and document action
			$document_type = str_replace( '_', '-', str_replace( array( 'wc_pip_print_', 'wc_pip_send_email_' ), '', $action ) );
			$action_type   = strpos( $action, 'print' ) ? 'print' : 'send_email';
			$redirect_url  = admin_url( 'edit.php?post_type=shop_order' );

			if ( 'send_email' === $action_type ) {

				$emails_sent = 0;

				if ( in_array( $document_type, array( 'invoice', 'packing-list' ), true ) ) {

					foreach ( $order_ids as $order_id ) {

						$document = wc_pip()->get_document( $document_type, array( 'order_id' => $order_id ) );

						if ( $document ) {

							/**
							 * Fires when sending an email manually from the orders edit screens.
							 *
							 * This is meant to force enable emails that are normally disabled.
							 *
							 * @see \WC_PIP_Email_Invoice::is_enabled()
							 * @see \WC_PIP_Email_Packing_List::is_enabled()
							 *
							 * @since 3.5.0
							 *
							 * @param \WC_PIP_Document $document related document object
							 */
							do_action( 'wc_pip_sending_manual_order_email', $document );

							$document->send_email();

							$emails_sent++;
						}
					}

				} elseif ( 'pick-list' === $document_type ) {

					$document = wc_pip()->get_document( $document_type, array( 'order_id' => $order_ids[0], 'order_ids' => $order_ids ) );

					if ( $document ) {

						/* this action is documented in class-wc-pip-orders-admin.php */
						do_action( 'wc_pip_sending_manual_order_email', $document );

						$document->send_email();

						$emails_sent = 1;
					}
				}

				/**
				 * Fires after emails are sent via a bulk action.
				 *
				 * @since 3.0.0
				 * @param string $document_type WC_PIP_Document type
				 * @param int[] $order_ids Array of WC_Order ids
				 */
				do_action( 'wc_pip_process_orders_bulk_action_send_email', $document_type, $order_ids );

				$pip_message = array(
					'wc_pip_document'  => $document_type,
					'wc_pip_action'    => 'admin_message',
					'wc_pip_message'   => 'emails_sent',
					'emails_count'     => $emails_sent,
					'orders_processed' => count( $order_ids ),
				);

				/* @see WC_PIP_Orders_Admin::render_email_sent_message() */
				wp_redirect( add_query_arg( $pip_message, $redirect_url ) );
				exit;
			}

			$document = wc_pip()->get_document( $document_type, array( 'order_id' => $order_ids[0], 'order_ids' => $order_ids ) );

			if ( 'print' === $action_type ) {

				// Trigger an admin notice to have the user manually open a print window
				$message = $this->get_print_confirmation_message( $document, $order_ids, $redirect_url );

				/* @see WC_PIP_Orders_Admin::render_messages() */
				update_option( '_woocommerce_pip_bulk_action_confirmation', array( get_current_user_id() => $message ) );

			} else {

				/**
				 * Fires after order bulk action is processed.
				 *
				 * @since 3.0.0
				 * @param string $action_type Action to be performed
				 * @param WC_PIP_Document $document Document object
				 */
				do_action( 'wc_pip_process_orders_bulk_action', $action_type, $document );
			}
 		}
	}


	/**
	 * Process individual order actions
	 *
	 * @since 3.0.0
	 * @param WC_Order $order
	 */
	public function process_order_actions( $order ) {

		if ( $actions = $this->get_actions() ) {

			$document = null;
			$message  = '';

			foreach ( array_keys( $actions ) as $action ) {

				if ( doing_action( 'woocommerce_order_action_'. $action ) ) {

					if ( in_array( $action, array( 'wc_pip_print_invoice', 'wc_pip_send_email_invoice' ), true ) ) {

						$document = wc_pip()->get_document( 'invoice', array( 'order' => $order ) );

					} elseif ( in_array( $action, array( 'wc_pip_print_packing_list', 'wc_pip_send_email_packing_list' ), true ) ) {

						$document = wc_pip()->get_document( 'packing-list', array( 'order' => $order ) );

					// the pick list is normally not added as an individual order action but we include this possibility to make a customization easier in case it's added to the order by customization
					} elseif ( in_array( $action, array( 'wc_pip_print_pick_list', 'wc_pip_send_email_pick_list' ), true ) ) {

						$document = wc_pip()->get_document( 'pick-list', array( 'order' => $order ) );
					}

					if ( in_array( $action, array( 'wc_pip_print_invoice', 'wc_pip_print_packing_list', 'wc_pip_print_pick_list' ), true ) ) {
						$order_id = SV_WC_Order_Compatibility::get_prop( $order, 'id' );
						$message  = $this->get_print_confirmation_message( $document, array( $order_id ), admin_url() );
					}

					switch ( $action ) {

						case 'wc_pip_print_invoice' :
						case 'wc_pip_print_packing_list' :
						case 'wc_pip_print_pick_list' :

							/* @see WC_PIP_Orders_Admin::render_messages() */
							update_option( '_woocommerce_pip_bulk_action_confirmation', array( get_current_user_id() => $message ) );

						break;

						case 'wc_pip_send_email_invoice' :
						case 'wc_pip_send_email_packing_list' :

							/* TODO it seems that actions that contain 'email' in the key are hijacked by WooCommerce - the following won't work, workaround in JS */
							if ( $document ) {

								/* this action is documented in class-wc-pip-orders-admin.php */
								do_action( 'wc_pip_sending_manual_order_email', $document );

								$document->send_email();
							}

						break;
					}
				}
			}
		}
	}


	/**
	 * Add a nonce for individual order actions
	 *
	 * @since 3.0.4
	 */
	public function send_email_order_action_nonce() {

		wp_nonce_field( 'wc_pip_document', 'wc_pip_document_nonce' );
	}


	/**
	 * Send email by admin action
	 *
	 * @since 3.0.0
	 */
	public function send_email_order_action() {

		$get_request  = isset( $_GET['wc_pip_document'] ) && isset( $_GET['wc_pip_action'] ) && 'send_email' === $_GET['wc_pip_action'];
		$post_request = isset( $_POST['wc_order_action'] ) && isset( $_POST['wc_pip_document_nonce'] ) && in_array( $_POST['wc_order_action'], array( 'wc_pip_send_email_invoice', 'wc_pip_send_email_packing_list' ), true );

		// listen for 'send_email' query string or order action post
		if ( $get_request || $post_request ) {

			// bail out early if user hasn't the necessary privileges
			if ( ! is_user_logged_in() || ! wc_pip()->get_handler_instance()->current_admin_user_can_manage_documents() ) {
				return;
			}

			// get nonce according to request type
			if ( $get_request ) {
				$nonce = isset( $_REQUEST['_wpnonce'] ) ? $_REQUEST['_wpnonce'] : '';
			} else {
				$nonce = $_POST['wc_pip_document_nonce'];
			}

			// verify nonce
			if ( ! $nonce || ! wp_verify_nonce( $nonce, 'wc_pip_document' ) ) {
				return;
			}

			// get the document type to send an email for
			if ( $get_request ) {
				$type = str_replace( '_', '-', $_GET['wc_pip_document'] );
			} else {
				$type = str_replace( '_', '-', str_replace( 'wc_pip_send_email_', '', $_POST['wc_order_action'] ) );
			}

			$document  = null;
			$order_id  = 0;
			$order_ids = array();

			// get order id(s)
			if ( $get_request ) {

				if ( isset( $_GET['order_ids'] ) ) {
					$order_ids = is_array( $_GET['order_ids'] ) ? array_map( 'intval', $_GET['order_ids'] ) : array_map( 'intval', implode( ',', $_GET['order_ids'] ) );
				}

				if ( isset( $_GET['order_id'] ) ) {
					$order_id = $order_ids ? $order_ids[0] : max( 0, (int) $_GET['order_id'] );
				}

			} else {

				// in single order action, we only have one id
				$order_id = (int) $_POST['post_ID'];
			}

			$order = wc_get_order( $order_id );

			// if we have an order, get the document...
			if ( $order ) {

				$args = $order_ids ? array( 'order' => $order, 'order_id' => $order_ids[0], 'order_ids' => $order_ids ) : array( 'order' => $order, 'order_id' => $order_id );

				$document = wc_pip()->get_document( $type, $args );

				// if we have a document, send an email...
				if ( $document ) {

					/* this action is documented in class-wc-pip-orders-admin.php */
					do_action( 'wc_pip_sending_manual_order_email', $document );

					$document->send_email();

					// we can stop here if this was a single order action
					if ( $post_request ) {
						return;
					}

					// little hack to clean the address bar from send email query strings
					// which might cause to send emails again if the user reloads the page...
					$previous_screen = remove_query_arg( array(
						'wc_pip_action',
						'wc_pip_document',
						'emails_count',
						'order_id',
						'order_ids',
						'_wpnonce',
					) );

					// ... however we add a new query string to generate a notice message:
					$pip_message = array(
						'wc_pip_document' => $type,
						'wc_pip_action'   => 'admin_message',
						'wc_pip_message'  => 'emails_sent',
						'emails_count'    => 1,
					);

					/* @see WC_PIP_Email::render_sent_email_message() */
					wp_redirect( add_query_arg( $pip_message, $previous_screen ) );
					exit;
				}
			}
		}
	}


	/**
	 * Render sent email messages
	 *
	 * @see WC_PIP_Email::send_email_action()
	 *
	 * @since 3.0.0
	 */
	public function render_email_sent_message() {

		// Listen for 'admin_message' query string
		if ( isset( $_GET['wc_pip_document'], $_GET['wc_pip_action'] ) && 'admin_message' === $_GET['wc_pip_action'] ) {

			$document_type    = $_GET['wc_pip_document'];
			$emails_sent      = isset( $_GET['emails_count'] )     ? (int) $_GET['emails_count']               : 0;
			$orders_processed = isset( $_GET['orders_processed'] ) ? max( 1, (int) $_GET['orders_processed'] ) : 1;
			$message_type     = isset( $_GET['wc_pip_message'] )   ? $_GET['wc_pip_message']                   : '';
			$document         = wc_pip()->get_document( $document_type, array( 'order_id' => 0 ) );

			if ( $document && 'emails_sent' === $message_type ) {

				if ( $emails_sent > 0 ) {
					if ( 'pick-list' === $document->type ) {
						/* translators: Placeholders: %d - number of emails sent */
						$message = sprintf( _n( 'Pick List email for %d order sent.', 'Pick List email for %d orders sent.', $orders_processed, 'woocommerce-pip' ), $orders_processed );
					} else {
						/* translators: Placeholders: %d - number of emails sent, %s - document name */
						$message = sprintf( _n( '%d %s email sent.', '%d %s emails sent.', (int) $emails_sent, 'woocommerce-pip' ), (int) $emails_sent, $document->name );
					}
				} else {
					/* translators: Placeholder: %s - document name */
					$message = sprintf( __( 'No %s emails sent.', 'woocommerce-pip' ), $document->name );
				}

				wc_pip()->get_message_handler()->add_message( $message );
			}
		}
	}


	/**
	 * Get print confirmation message
	 *
	 * @since 3.0.0
	 * @param WC_PIP_Document $document Document object
	 * @param int[] $order_ids Array of WC_Order ids
	 * @param string $redirect_url Optional, defaults to admin url
	 * @return string
	 */
	public function get_print_confirmation_message( $document, $order_ids, $redirect_url = '' ) {

		$orders_count = count( $order_ids );

		if ( $orders_count < 1 ) {
			/* translators: Placeholder: %s - Document name */
			return sprintf( __( 'No %s created for printing. Please select valid orders or reload this page first.', 'woocommerce-pip' ), $document->name );
		}

		$order_ids_hash = md5( json_encode( $order_ids ) );

		// Save the order IDs in a option.
		// Initially we were using a transient, but this seemed to cause issues
		// on some hosts (mainly GoDaddy) that had difficulty in implementing a
		// proper object cache override.
		update_option( "wc_pip_order_ids_{$order_ids_hash}", $order_ids );

		$action_url = wp_nonce_url(
			add_query_arg(
				array(
					'wc_pip_action'   => 'print',
					'wc_pip_document' => $document->type,
					'order_id'        => $order_ids[0],
					'order_ids'       => $order_ids_hash,
				),
				'' !== $redirect_url ? $redirect_url : admin_url()
			),
			'wc_pip_document'
		);

		$print_link = '<a href="' . $action_url .'" target="_blank">' . __( 'Print now.', 'woocommerce-pip' ) . '</a>';

		if ( $orders_count > 1 ) {

			if ( $document->is_type( 'pick-list' ) ) {
				/* translators: Placeholders: %1$s - document name (pick list), %2$s - number of documents created, %3$s - link to print */
				$message = sprintf( __( '%1$s for %2$s orders created. %3$s', 'woocommerce-pip' ), $document->name, $orders_count, $print_link );
			} else {
				/* translators: Placeholders: %1$s - number of documents created, %2$s - document name, %3$s - link to print */
				$message = sprintf( __( '%1$s %2$s created. %3$s', 'woocommerce-pip' ), $orders_count, $document->name_plural, $print_link );
			}

		} else {
			/* translators: Placeholders: %1$s - document name, %2$s - link to print */
			$message = sprintf( __( '%1$s created. %2$s', 'woocommerce-pip' ), $document->name, $print_link );
		}

		return $message;
	}


	/**
	 * Generate the invoice number upon order save
	 *
	 * @since 3.0.0
	 * @param int $post_id Post id
	 * @param WP_Post $post Post object
	 */
	public function generate_invoice_number_order_save( $post_id, $post ) {

		if ( 'shop_order' !== $post->post_type ) {
			return;
		}

		/* This filter is documented in /includes/class-wc-pip-handler.php */
		if ( false === apply_filters( 'wc_pip_generate_invoice_number_on_order_paid', true ) ) {
			return;
		}

		$wc_order = wc_get_order( $post_id );

		if ( ! $wc_order ) {
			return;
		}

		// Generate the invoice number, will trigger post meta update
		if ( $wc_order->is_paid() ) {

			$document = wc_pip()->get_document( 'invoice', array( 'order' => $wc_order ) );

			if ( $document ) {
				$document->get_invoice_number();
			}
		}
	}


	/**
	 * Display the invoice number in the order screen meta box
	 *
	 * @since 3.0.0
	 * @param WC_Order|int $wc_order Order object or id
	 */
	public function display_order_invoice_number( $wc_order ) {

		if ( is_numeric( $wc_order ) ) {
			$wc_order = wc_get_order( $wc_order );
		}

		$order_id = $wc_order instanceof WC_Order ? SV_WC_Order_Compatibility::get_prop( $wc_order, 'id' ) : null;

		// only display if the invoice number was generated before
		if ( is_numeric( $order_id ) && $order_id > 0 ) :

			$document = wc_pip()->get_document( 'invoice', array( 'order' => $wc_order ) );

			if ( $document && $document->has_invoice_number() ) :

				?>
				<p class="form-field form-field-wide wc-pip-invoice-number">
					<label for="pip-invoice-number"><?php esc_html_e( 'Invoice number:', 'woocommerce-pip' ); ?></label>
					<strong><?php echo esc_html( $document->get_invoice_number() ); ?></strong>
				</p>
				<?php

			endif;

		endif;
	}


	/**
	 * Get individual order actions
	 *
	 * @since 3.0.0
	 * @return array Associative array of actions with their labels
	 */
	public function get_actions() {

		$actions = array();

		if ( wc_pip()->get_handler_instance()->current_admin_user_can_manage_documents() ) {

			/**
			 * Filters the admin order actions.
			 *
			 * @since 3.0.0
			 * @param array $actions
			 */
			$actions = apply_filters( 'wc_pip_admin_order_actions', array(
				'wc_pip_print_invoice'           => __( 'Print Invoice', 'woocommerce-pip' ),
				'wc_pip_send_email_invoice'      => __( 'Email Invoice', 'woocommerce-pip' ),
				'wc_pip_print_packing_list'      => __( 'Print Packing List', 'woocommerce-pip' ),
				'wc_pip_send_email_packing_list' => __( 'Email Packing List', 'woocommerce-pip' ),
			) );
		}

		return $actions;
	}


	/**
	 * Get orders bulk actions
	 *
	 * @since 3.0.0
	 * @return array Associative array of actions with their labels
	 */
	public function get_bulk_actions() {

		$shop_manager_actions = array();

		if ( wc_pip()->get_handler_instance()->current_admin_user_can_manage_documents() ) {

			/**
			 * Filters the bulk order actions.
			 *
			 * @since 3.0.0
			 *
			 * @param array $actions
			 */
			$shop_manager_actions = apply_filters( 'wc_pip_admin_order_bulk_actions', array_merge( $this->get_actions(), array(
				'wc_pip_print_pick_list'      => __( 'Print Pick List', 'woocommerce-pip' ),
				'wc_pip_send_email_pick_list' => __( 'Email Pick List', 'woocommerce-pip' ),
			) ) );
		}

		return $shop_manager_actions;
	}


	/**
	 * Adds 'Invoice' and 'Packing List' column headers
	 * to 'Orders' page immediately before the 'Actions' column
	 *
	 * @since 3.0.0
	 * @param array $columns
	 * @return array $new_columns
	 */
	public function add_order_status_column_header( $columns ) {

		$new_columns = array();

		foreach ( $columns as $column_name => $column_info ) {

			$new_columns[ $column_name ] = $column_info;

			if ( 'order_total' === $column_name ) {

				$new_columns['pip_print_invoice']      = __( 'Invoice', 'woocommerce-pip' );
				$new_columns['pip_print_packing-list'] = __( 'Packing List', 'woocommerce-pip' );
			}
		}

		return $new_columns;
	}


	/**
	 * Adds content to the order columns.
	 *
	 * - The invoice number (if already generated) under the order ID and customer info
	 * - The invoice print status
	 * - The packing list print status
	 * - Hidden HTML content in the order actions that will be used to output a document print button in JS
	 *
	 * Note (WC 3.0+): WooCommerce 3.0+ makes it difficult to set an order object and make us call WC_PIP_Document, which would otherwise result in too many queries.
	 * Therefore this callback method (which comes from a generic WordPress hook) does not allow us to use PIP internals to gather any of the above information.
	 * Legacy `get_post_meta()` will be used to reduce the number of queries otherwise triggered by PIP.
	 *
	 * Note (WC 3.3+): WooCommerce 3.3 overhauled the orders edit screen and some columns changed names - we might to check different ones for BC purposes.
	 *
	 * @internal
	 *
	 * @since 3.0.0
	 *
	 * @param array $column Name of column being displayed
	 * @param int $order_id The post (order) ID
	 */
	public function add_order_status_column_content( $column, $order_id ) {

		// Invoice No. ('order_number' is for WC 3.3+)
		if ( 'order_title' === $column || 'order_number' === $column ) {

			$invoice_number = get_post_meta( $order_id, '_pip_invoice_number', true );

			if ( ! empty( $invoice_number ) && is_string( $invoice_number ) ) {
				/* translators: Placeholder: %s - invoice number */
				echo '<span class="wc-pip-invoice-number">' . sprintf( __( 'Invoice: %s', 'woocommerce-pip' ), $invoice_number ) . '</span>';
			}

		// Invoice print status
		} elseif ( 'pip_print_invoice' === $column ) {

			echo $this->get_print_status( $order_id, 'invoice' );

		// Packing List print status
		} elseif ( 'pip_print_packing-list' === $column ) {

			echo $this->get_print_status( $order_id, 'packing_list' );

		// hidden content that will be injected into a WP Pointer via JS ('wc_actions' here is for WC 3.3+)
		} elseif ( 'order_actions' === $column || 'wc_actions' === $column ) {

			?>
			<div id="wc-pip-pointer-order-actions-<?php echo esc_attr( $order_id ); ?>" style="display:none;">

				<input type="hidden" value="<?php echo esc_attr( $order_id ); ?>" />

				<h3 class="wp-pointer-header"><?php
					/* translators: Placeholder: %s - order number */
					printf( esc_html__( 'Invoice/Packing List (Order #%s)', 'woocommerce-pip' ), $order_id ); ?></h3>

				<div class="wp-pointer-inner-content">
					<?php

					$actions = array();
					$counts  = array();

					foreach ( $this->get_actions() as $action => $name ) :

						$document_type = str_replace( array( 'wc_pip_print_', 'wc_pip_send_email_' ), '', $action );
						$action_type   = strpos( $action, 'print' ) ? 'print' : 'send_email';
						$http          = is_ssl() ? 'https://' : 'http://';
						$url           = add_query_arg(
							array(
								'wc_pip_action'   => $action_type,
								'wc_pip_document' => $document_type,
								'order_id'        => $order_id,
							),
							'send_email' === $action_type ? $http . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] : admin_url()
						);

						ob_start();

						?>
						<a class="button button-small <?php echo sanitize_html_class( $action ); ?> wc-pip-document-tooltip-order-action"
						   href="<?php echo wp_nonce_url( $url, 'wc_pip_document' ); ?>"
						   target="<?php echo 'print' === $action_type ? '_blank' : '_self'; ?>">
							<?php echo esc_html( $name ); ?>
						</a>
						<?php

						$actions[ $action_type ][] = ob_get_clean();

						if ( ! isset( $counts[ $action_type ] ) ) {
							$counts[ $action_type ] = 0;
						}

						$counts[ $action_type ]++;

					endforeach;

					$max_cols = max( $counts );

					?>
					<table>
						<tbody>
							<?php foreach ( $actions as $action_type => $action_buttons ) : ?>
								<tr>

									<?php if ( 'print' === $action_type ) : ?>
										<th><?php esc_html_e( 'Print', 'woocommerce-pip' ); ?></th>
									<?php elseif ( 'send_email' === $action_type ) : ?>
										<th><?php esc_html_e( 'Email' , 'woocommerce-pip' ); ?></th>
									<?php endif; ?>

									<?php for ( $cols = count( $action_buttons ); $cols < $max_cols; $cols++ ) : ?>
										<?php $action_buttons[] = ''; ?>
									<?php endfor; ?>

									<?php foreach( (array) $action_buttons as $button ) : ?>
										<td><?php echo $button; ?></td>
									<?php endforeach; ?>

								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
			<?php
		}
	}


	/**
	 * Adds the invoice number to the order data meant for order preview.
	 *
	 * @since 3.4.0
	 *
	 * @internal
	 *
	 * @param array $data associative array with order data
	 * @param \WC_Order $order the order object
	 * @return array
	 */
	public function add_order_preview_invoice_number( $data, $order ) {

		if ( $order ) {

			$invoice_number = get_post_meta( SV_WC_Order_Compatibility::get_prop( $order, 'id' ), '_pip_invoice_number', true );

			$data['invoice_number'] = ! empty( $invoice_number ) && is_string( $invoice_number ) ? $invoice_number : '&mdash;';
		}

		return $data;
	}


	/**
	 * Displays the invoice number information in order preview modals.
	 *
	 * @internal
	 *
	 * @since 3.4.0
	 */
	public function display_order_preview_invoice_number() {

		?>
		<div class="wc-pip-order-preview">
			<h2><?php esc_html_e( 'Invoice Number', 'woocommerce-pip' ); ?></h2>
			<span class="wc-pip-invoice-number">{{{ data.invoice_number }}}</span>
		</div>
		<?php
	}


	/**
	 * Returns the order documents print status (whether a document had a print window open).
	 *
	 * @since 3.0.0
	 *
	 * @param int $order_id Corresponding order ID
	 * @param string $document_type PIP Document type
	 * @return string HTML
	 */
	private function get_print_status( $order_id, $document_type ) {
		return get_post_meta( $order_id, "_wc_pip_{$document_type}_print_count", true ) > 0 ? '&#10004' : '<strong>&ndash;</strong>';
	}


	/**
	 * Adds order action icons to the Orders screen table for printing the invoice and packing list.
	 *
	 * Processed via Ajax.
	 *
	 * @internal
	 *
	 * @since 3.0.0
	 *
	 * @param array $actions Order actions
	 * @param int\WC_Order $order Order object or order ID
	 * @return array
	 */
	public function add_order_actions( $actions, $order ) {

		if ( ! $order instanceof WC_Order && is_numeric( $order ) ) {
			$wc_order = wc_get_order( $order );
		} else {
			$wc_order = $order;
		}

		if ( $wc_order instanceof WC_Order && wc_pip()->get_handler_instance()->current_admin_user_can_manage_documents() ) {

			$order_id = SV_WC_Order_Compatibility::get_prop( $wc_order, 'id' );
			$actions  = array_merge( $actions, array( array(
				'name'   => __( 'Print Invoices / Packing Lists', 'woocommerce-pip' ),
				'action' => 'wc_pip_document',
				'url'    => sprintf( '#%s', $order_id ),
			) ) );
		}

		return $actions;
	}


	/**
	 * Adds custom bulk actions to the Orders screen table bulk action drop-down
	 *
	 * @internal
	 *
	 * @since 3.0.0
	 */
	public function add_order_bulk_actions() {
		global $post_type, $post_status;

		if ( $post_type === 'shop_order' && $post_status !== 'trash' ) :

			?>
			<script type="text/javascript">
				jQuery( document ).ready( function ( $ ) {
					$( 'select[name^=action]' ).append(
						<?php $index = count( $actions = $this->get_bulk_actions() ); ?>
						<?php foreach ( $actions as $action => $name ) : ?>
							$( '<option>' ).val( '<?php echo esc_js( $action ); ?>' ).text( '<?php echo esc_js( $name ); ?>' )
							<?php --$index; ?>
							<?php if ( $index ) { echo ','; } ?>
						<?php endforeach; ?>
					);
				} );
			</script>
			<?php

		endif;
	}


	/**
	 * Add order actions to the Edit Order screen
	 *
	 * @internal
	 *
	 * @since 3.0.0
	 * @param array $actions
	 * @return array
	 */
	public function add_order_meta_box_actions( $actions ) {
		global $post;

		// bail out if the order hasn't been saved yet
		if ( $post instanceof WP_Post && 'auto-draft' === $post->post_status ) {
			return $actions;
		}

		return array_merge( $actions, $this->get_actions() );
	}


	/**
	 * Display a dropdown to filter orders by print status
	 *
	 * @internal
	 *
	 * @since 3.0.0
	 */
	public function filter_orders_by_print_status() {
		global $typenow;

		if ( 'shop_order' === $typenow ) :

			$options  = array(
				'invoice_not_printed'      => __( 'Invoice not printed', 'woocommerce-pip' ),
				'invoice_printed'          => __( 'Invoice printed', 'woocommerce-pip' ),
				'packing_list_not_printed' => __( 'Packing List not printed', 'woocommerce-pip' ),
				'packing_list_printed'     => __( 'Packing List printed', 'woocommerce-pip' ),
				'pick_list_not_printed'    => __( 'Pick List not printed', 'woocommerce-pip' ),
				'pick_list_printed'        => __( 'Pick List printed', 'woocommerce-pip' ),
			);

			$selected = isset( $_GET['_shop_order_pip_print_status'] ) ? $_GET['_shop_order_pip_print_status'] : '';

			?>
			<select name="_shop_order_pip_print_status" id="dropdown_shop_order_pip_print_status">
				<option value=""><?php esc_html_e( 'Show all print statuses', 'woocommerce-pip' ); ?></option>
				<?php foreach ( $options as $option_value => $option_name ) : ?>
					<option value="<?php echo esc_attr( $option_value ); ?>" <?php selected( $selected, $option_value ); ?>><?php echo esc_html( $option_name ); ?></option>
				<?php endforeach; ?>
			</select>
			<?php

		endif;
	}


	/**
	 * Filter orders by print status query vars
	 *
	 * @internal
	 *
	 * @since 3.0.0
	 * @param array $vars WP_Query vars
	 * @return array
	 */
	public function filter_orders_by_print_status_query( $vars ) {
		global $typenow;

		if ( 'shop_order' === $typenow && isset( $_GET['_shop_order_pip_print_status'] ) ) {

			$meta    = '';
			$compare = '';
			$value   = '';

			switch ( $_GET['_shop_order_pip_print_status'] ) {

				case 'invoice_not_printed' :

					$meta    = '_wc_pip_invoice_print_count';
					$compare = 'NOT EXISTS';

				break;

				case 'invoice_printed' :

					$meta    = '_wc_pip_invoice_print_count';
					$compare = '>';
					$value   = '0';

				break;

				case 'packing_list_not_printed' :

					$meta  = '_wc_pip_packing_list_print_count';
					$compare = 'NOT EXISTS';

				break;

				case 'packing_list_printed' :

					$meta    = '_wc_pip_packing_list_print_count';
					$compare = '>';
					$value   = '0';

				break;

				case 'pick_list_not_printed' :

					$meta    = '_wc_pip_pick_list_print_count';
					$compare = 'NOT EXISTS';

				break;

				case 'pick_list_printed' :

					$meta    = '_wc_pip_pick_list_print_count';
					$compare = '>';
					$value   = '0';

				break;

			}

			if ( $meta && $compare ) {

				$vars['meta_key']     = $meta;
				$vars['meta_value']   = $value;
				$vars['meta_compare'] = $compare;
			}
		}

		return $vars;
	}


	/**
	 * Make invoice numbers searchable
	 *
	 * @internal
	 *
	 * @since 3.0.0
	 * @param array $search_fields Existing search fields
	 * @return array
	 */
	public function make_invoice_numbers_searchable( $search_fields ) {
		return array_merge( $search_fields, array( '_pip_invoice_number' ) );
	}


}
