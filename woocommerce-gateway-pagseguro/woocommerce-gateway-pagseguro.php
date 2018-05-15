<?php
/**
 * Plugin Name: WooCommerce PagSeguro Gateway
 * Plugin URI: https://woocommerce.com/products/pagseguro/
 * Description: PagSeguro integration for WooCommerce
 * Author: WooCommerce
 * Author URI: https://woocommerce.com/
 * Version: 1.3.3
 * Text domain: woocommerce-gateway-pagseguro
 * WC tested up to: 3.3
 * WC requires at least: 2.6
 * Woo: 18641:58b516c415a082f44cfc46eb2e844b2a
 *
 * Copyright: © 2015-2017 WooCommerce
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

/**
 * Required functions
 */
if ( ! function_exists( 'woothemes_queue_update' ) ) {
	require_once( 'woo-includes/woo-functions.php' );
}

/**
 * Plugin updates
 */
woothemes_queue_update( plugin_basename( __FILE__ ), '58b516c415a082f44cfc46eb2e844b2a', '18641' );

add_action( 'plugins_loaded', 'woocomerce_gateway_pagseguro' );

function woocomerce_gateway_pagseguro() {

	if ( !class_exists( 'WC_Payment_Gateway' ) ) {
		return;
	}

	load_plugin_textdomain('woocommerce-gateway-pagseguro', false, basename( dirname( __FILE__ ) ) . '/languages' );

	add_filter( 'woocommerce_payment_gateways', 'add_pagseguro_gateway' );

	define( 'WC_GATEWAY_PAGSEGURO_VERSION', '1.3.3' );

	class WC_Gateway_Pagseguro extends WC_Payment_Gateway {

		public function __construct() {
			global $woocommerce;

			$this->id			= 'pagseguro';
			$this->method_title = 'PagSeguro';
			$this->icon 		= WP_PLUGIN_URL . "/" . plugin_basename( dirname(__FILE__)) . '/images/pagseguro.png';
			$this->has_fields 	= false;
			$this->liveurl 		= 'https://pagseguro.uol.com.br/v2/checkout/payment.html';


			// Load the form fields.
			$this->init_form_fields();

			// Load the settings.
			$this->init_settings();

			// Define user set variables
			$this->title 		= $this->settings['title'];
			$this->description 	= $this->settings['description'];
			$this->email 		= $this->settings['email'];
			$this->token 		= $this->settings['token'];
			$this->debug		= $this->settings['debug'];
			$this->notificationCode	= (isset($this->settings['notification_code']))?$this->settings['notification_code']:'notification_code';
			$this->library 		= (isset($this->settings['library']))?$this->settings['library']:false;

			// Logs
			if ($this->debug=='yes') $this->log = new WC_Logger();

			if ( ! $this->is_valid_currency() || ! $this->are_credentials_set() )
				$this->enabled = false;

			// Actions
			add_action('woocommerce_receipt_pagseguro',	array( $this, 'receipt_page' ) );
			add_action( 'woocommerce_update_options_payment_gateways_'. $this->id, array( $this, 'process_admin_options' ) );
			add_action( 'woocommerce_api_'. strtolower( get_class( $this ) ), array( $this, 'notificacao' ) );
		}


		/**
		 * Initialise Gateway Settings Form Fields
		 */
		function init_form_fields() {

			$this->form_fields = array(
				'enabled' => array(
								'title' => __( 'Enable/Disable', 'woocommerce-gateway-pagseguro' ),
								'type' => 'checkbox',
								'label' => __( 'Enable PagSeguro standard', 'woocommerce-gateway-pagseguro' ),
								'default' => 'yes'
							),
				'title' => array(
								'title' => __( 'Title', 'woocommerce-gateway-pagseguro' ),
								'type' => 'text',
								'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce-gateway-pagseguro' ),
								'default' => __( 'PagSeguro', 'woocommerce-gateway-pagseguro' )
							),
				'description' => array(
								'title' => __( 'Description', 'woocommerce-gateway-pagseguro' ),
								'type' => 'textarea',
								'description' => __( 'This controls the description which the user sees during checkout.', 'woocommerce-gateway-pagseguro' ),
								'default' => __("Pay securely via PagSeguro using your Credit Card or bank account.", 'woocommerce-gateway-pagseguro')
							),
				'email' => array(
								'title' => __( 'PagSeguro Email', 'woocommerce-gateway-pagseguro' ),
								'type' => 'text',
								'description' => __( 'Please enter your PagSeguro email address; this is needed in order to take payment.', 'woocommerce-gateway-pagseguro' ),
								'default' => ''
							),
				'token' => array(
								'title' => __( 'PagSeguro Token', 'woocommerce-gateway-pagseguro' ),
								'type' => 'text',
								'description' => __( 'Please enter your PagSeguro token; this is needed in order to take payment. See <a href="https://pagseguro.uol.com.br/integracao/token-de-seguranca.jhtml" target="_blank">Token of security</a>', 'woocommerce-gateway-pagseguro' ),
								'default' => ''
							),
				'library' => array(
								'title' => __( 'Use HTML integration', 'woocommerce-gateway-pagseguro' ),
								'type' => 'checkbox',
								'description' => __( 'If your host plan no have support to cURL library, check this.', 'woocommerce-gateway-pagseguro' ),
								'default' => 'no'
							),
				'notification_code' => array(
								'title' => __( 'What is your transaction code?', 'woocommerce-gateway-pagseguro' ),
								'type' => 'text',
								'description' => __( 'This is used in redirect page and return of data. Change only if you change in your PagSeguro account. See <a href="https://pagseguro.uol.com.br/integracao/pagina-de-redirecionamento.jhtml" target="_blank">Transaction code</a>', 'woocommerce-gateway-pagseguro' ),
								'default' => 'notification_code'
							),
				'debug' => array(
								'title' => __( 'Enable/Disable Log', 'woocommerce-gateway-pagseguro' ),
								'type' => 'checkbox',
								'description' => '',
								'default' => ''
							),

				);

		} // End init_form_fields()


		/**
		 * Admin Panel Options
		 * - Options for bits like 'title' and availability on a country-by-country basis
		 *
		 * @since 1.0.0
		 */
		public function admin_options() {

			?>
			<h3><?php echo __('PagSeguro Gateway (API v2)', 'woocommerce-gateway-pagseguro'); ?></h3>
			<p><?php echo __('PagSeguro works by sending the user to PagSeguro Gateway to enter their payment information. This Payment Gateway works only API v2 and you need make changes in <a href="https://pagseguro.uol.com.br/integracao/notificacao-de-transacoes.jhtml" target="_blank">Notifications of Transactions</a>', 'woocommerce-gateway-pagseguro'); ?></p>
			<table class="form-table">
				<?php if ( ! $this->is_valid_currency() ) : ?>
					<div class="inline error">
						<p><strong><?php _e( 'Gateway Disabled', 'woocommerce-gateway-pagseguro' ); ?></strong>: <?php _e( 'PagSeguro does not support your store\'s currency. You need to select the currency of Brazil Real.', 'woocommerce-gateway-pagseguro' ); ?></p>
					</div>
				<?php endif; ?>

				<?php if ( ! $this->are_credentials_set() ) : ?>
					<div class="inline error">
						<p><strong><?php _e( 'Gateway Disabled', 'woocommerce-gateway-pagseguro' ); ?></strong>: <?php _e( 'You must give the token of your account email.', 'woocommerce-gateway-pagseguro' ); ?></p>
					</div>
				<?php endif; ?>
			<?php
				// Generate the HTML For the settings form.
				$this->generate_settings_html();
			?>
			</table><!--/.form-table-->
			<?php
		} // End admin_options()



		/**
		 * Check if PagSeguro can be used with the store's currency.
		 * For now only work with Real of Brazil, but...
		 *
		 * @since 1.0
		 */
		function is_valid_currency() {
			if ( !in_array( get_option( 'woocommerce_currency' ), array( 'BRL' ) ) )
				return false;
			else
				return true;
		}



		/**
		 * Check if PagSeguro Credentials are set
		 *
		 * @since 1.0
		 */
		function are_credentials_set() {
			if( empty( $this->email ) || empty( $this->token ) )
				return false;
			else
				return true;
		}


		/**
		 * There are no payment fields for PagSeguro, but we want to show the description if set.
		 **/
		function payment_fields() {
			if ($this->description) echo wpautop(wptexturize($this->description));
		}


		/**
		 * Generate the PagSeguro button form
		 **/
		public function generate_pagseguro_form_HTML( $order_id ) {
			global $woocommerce;

			$order = new WC_Order( $order_id );

			$js = '
			jQuery(function(){
				jQuery("body").block(
					{
						message: "<img src=\"'.esc_url( $woocommerce->plugin_url() ).'/assets/images/ajax-loader.gif\" alt=\"'. __("Redirecting...", 'woocommerce-gateway-pagseguro') .'\" style=\"float:left; margin-right: 10px;\" />'.__('Thank you for your order. We are now redirecting you to PagSeguro to make payment.', 'woocommerce-gateway-pagseguro').'",
						overlayCSS:
						{
							background: "#fff",
							opacity: 0.6
						},
						css: {
							padding:        20,
							textAlign:      "center",
							color:          "#555",
							border:         "3px solid #aaa",
							backgroundColor:"#fff",
							cursor:         "wait",
							lineHeight:		"32px"
						}
					});
				jQuery("#submit_pagseguro_payment_form").click();
			});';

			if ( function_exists( 'wc_enqueue_js' ) ) {
				wc_enqueue_js( $js );
			} else {
				$woocommerce->add_inline_js( $js );
			}

			if ( version_compare( WC_VERSION, '3.0', '<' ) ) {
				$order->billing_phone = str_replace( array( '(', '-', ' ', ')' ), '', $order->billing_phone );
				$order_id = $order->id;
				$full_name = $order->billing_first_name . ' ' . $order->billing_last_name;
				$order_total = $order->order_total;
				$billing_email = $order->billing_email;
			} else {
				$order->set_billing_phone( str_replace( array( '(', '-', ' ', ')' ), '', $order->get_billing_phone() ) );
				$order_id = $order->get_id();
				$full_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
				$order_total = $order->get_total();
				$billing_email = $order->get_billing_email();
			}

			$item_loop = 1;
			$url = $this->liveurl ."/?email=" . $this->email . "&token=" . $this->token;

			// HTML to payment
			$html = '<form action="https://pagseguro.uol.com.br/v2/checkout/payment.html" method="post" id="pagseguro_payment_form">
					<!-- Campos obrigatórios -->
					<input type="hidden" name="receiverEmail" value="'. $this->email .'">
					<input type="hidden" name="currency" value="BRL">

					<!-- Itens do pagamento -->
					<input type="hidden" name="itemId'. $item_loop .'" value="'. $item_loop .'">
					<input type="hidden" name="itemDescription'. $item_loop .'" value="Pagamento do pedido ' . $order_id . '">
					<input type="hidden" name="itemAmount'. $item_loop .'" value="'. number_format( $order_total, 2, '.', '' ) .'">
					<input type="hidden" name="itemQuantity'. $item_loop .'" value="1">
					<input type="hidden" name="itemWeight'. $item_loop .'" value="0">

					<!-- Código de referência do pagamento no seu sistema -->
					<input type="hidden" name="reference" value="' . esc_html( $order_id ) . '">

					<!-- Dados do comprador -->
					<input type="hidden" name="senderName" value="' . esc_attr( $full_name ) . '">
					<input type="hidden" name="senderEmail" value="' . esc_attr( $billing_email ) . '">
						<input type="submit" class="button-alt" id="submit_pagseguro_payment_form" value="'.__('Pay via PagSeguro', 'woocommerce-gateway-pagseguro').'" /> <a class="button cancel" href="'.esc_url( $order->get_cancel_order_url() ).'">'.__('Cancel order &amp; restore cart', 'woocommerce-gateway-pagseguro').'</a>
					</form>';

			return $html;
		}



		/**
		 * Generate the PagSeguro button link
		 **/
		public function generate_pagseguro_form_API( $order_id ) {
			global $woocommerce;

			$order = new WC_Order( $order_id );

			// give me, please =)   (receive a token)
			$token = $this->request_token_API( $order );


			// show error code if exists
			$codigo = 0;
			if( isset( $woocommerce->session->PSerro ) && !empty( $woocommerce->session->PSerro ) ){
				$codigo = $woocommerce->session->PSerro;
			}


			// token is false
			if( $token == false ) {

				$html  ="<h3>". __("Sorry, an error has occurred", 'woocommerce-gateway-pagseguro') ."</h3>";
				$html .="<p>". __("Try again, if the problem persists try to choose another payment method or contact the service.", 'woocommerce-gateway-pagseguro') ."</p>";
				$html .="<p>Código: ". $codigo ."</p>";

				if ($this->debug=='yes') $this->log->add( 'PagSeguro', 'Token inválido/falso: '. $codigo ."<br>". $token );


			} else {

				$js = '
				jQuery(function(){
					jQuery("body").block(
						{
							message: "'.__('Thank you for your order. We are now redirecting you to PagSeguro to make payment.', 'woocommerce-gateway-pagseguro').'",
							overlayCSS:
							{
								background: "#fff",
								opacity: 0.6
							},
							css: {
								padding:        20,
								textAlign:      "center",
								color:          "#555",
								border:         "3px solid #aaa",
								backgroundColor:"#fff",
								cursor:         "wait",
								lineHeight:		"32px"
							}
						});
					jQuery("#submit_pagseguro_payment_form").click();
				});';

				if ( function_exists( 'wc_enqueue_js' ) ) {
					wc_enqueue_js( $js );
				} else {
					$woocommerce->add_inline_js( $js );
				}

				// HTML to payment
				$html = '<form action="'.esc_url( $token ).'" method="post" id="pagseguro_payment_form">
							<input type="submit" class="button-alt" id="submit_pagseguro_payment_form" value="'.__('Pay via PagSeguro', 'woocommerce-gateway-pagseguro').'" /> <a class="button cancel" href="'.esc_url( $order->get_cancel_order_url() ).'">'.__('Cancel order &amp; restore cart', 'woocommerce-gateway-pagseguro').'</a>
						</form>';


				if ($this->debug=='yes') $this->log->add( 'PagSeguro', 'Formulário de pagamento gerado com sucesso.' );
			}

			return $html;
		}


		/**
		 * Process the payment and return the result
		 * checkout
		 * checkout page
		 *
		 **/
		function process_payment( $order_id = 0 ) {
			global $woocommerce;

			$order = new WC_Order( $order_id );

			// clean cart
			$woocommerce->cart->empty_cart();

			// Empty awaiting payment session
			unset( $woocommerce->session->order_awaiting_payment );

			return array(
				'result' 	=> 'success',
				'redirect'	=> $order->get_checkout_payment_url( true )
			);
		}


		/**
		 * Checkout -> Pay
		 * Payment page, redirect to gateway
		 **/
		function receipt_page( $order ) {
			echo '<p>'.__('Thank you for your order, please click the button below to pay with PagSeguro.', 'woocommerce-gateway-pagseguro').'</p>';

			/**
			 * Test choosed library of connection
			 *
			 * $this->library
			 */
			if ( ! $this->library ) {
				echo $this->generate_pagseguro_form_HTML( $order );
			} else {
				echo $this->generate_pagseguro_form_API( $order );
			}
		}


		/**
		 * Ajuda a identificar o status de pagamento
		 * @see https://pagseguro.uol.com.br/v2/guia-de-integracao/api-de-notificacoes.html#v2-item-api-de-notificacoes-status-da-transacao
		 * @param Integer $status status de pagamento retornado
		 * @return Array status de pagamento convertido para o WooCommerce
		 */
		public function status_helper( $status = null, $order = null ) {
			global $woocommerce;

			switch ( $status ){

				case 1:
					$arrStatus['status'] = "pending"; // aguardando pagamento
					$arrStatus['log'] = __('Payment pending, Waiting payment confirmation', 'woocommerce-gateway-pagseguro'); // aguardando pagamento

					// add note to control
					$order->add_order_note( $arrStatus['log'] );

					break;

				case 2:
					$arrStatus['status'] = "pending"; // em analise
					$arrStatus['log'] = __('Payment in analysis, Waiting payment confirmation', 'woocommerce-gateway-pagseguro');

					// add note to control
					$order->add_order_note( $arrStatus['log'] );

					break;

				case 3:
					$arrStatus['status'] = "completed"; // paga
					$arrStatus['log'] = __('Manual confirmation of payment, Check payment confirmation', 'woocommerce-gateway-pagseguro');

					// change payment status
					$order->payment_complete();

					break;

				case 4:// disponivel
					$arrStatus['status'] = "completed"; // disponivel
					$arrStatus['log'] = __('Payment completed', 'woocommerce-gateway-pagseguro');

					// add note to control
					$order->add_order_note( $arrStatus['log'] );

					break;

				case 5: // disputa
					$arrStatus['status'] = "on-hold";
					$arrStatus['log'] = __('Failed on process of negociation (dispute)', 'woocommerce-gateway-pagseguro');

					// add note to control
					$order->add_order_note( $arrStatus['log'] );

					break;

				case 6:
					$arrStatus['status'] = "refunded"; // devolvida
					$arrStatus['log'] = __('Payment refunded', 'woocommerce-gateway-pagseguro');

					// add note to control
					$order->add_order_note( $arrStatus['log'] );

					break;

				case 7:
					$arrStatus['status'] = "cancelled"; // cancelada
					$arrStatus['log'] = __('Order cancelled', 'woocommerce-gateway-pagseguro');

					// cancel this order
					WC()->session->set( 'order_awaiting_payment', false );
					$order->update_status( 'cancelled', version_compare( WC_VERSION, '3.0', '<' ) ? $order->id : $order->get_id() );

					break;

				// improvavel mas nao custa prevenir
				default:
					$arrStatus['status'] = 'pending';
					$arrStatus['log'] = __('Payment pending, Waiting payment confirmation', 'woocommerce-gateway-pagseguro');

					// add note to control
					$order->add_order_note( $arrStatus['log'] );
			}
			return $arrStatus;
		}



		/**
		 * This a hack fix to dinamic URL used in PagSeguro
		 *
		 * Until this day PagSeguro no have supports to
		 * query strings in redirectURL
		 *
		 * @since 2012-04-24
		 */
		public function fix_url_order_received(){
			global $woocommerce;


			// if notificationCode and order exists and have a value
			if( isset( $_GET[$this->notificationCode] ) && !empty( $_GET[$this->notificationCode] ) && isset( $_GET['order'] ) && !empty( $_GET['order'] ) ){

				// this order exists?
				$order = new WC_Order( (int) $_GET['order'] );

				/**
				 * PagSeguro no support URLs with query strings
				 * This is a simple hack to fix this problem
				 *
				 * Verify the transaction code and redirect
				 * to URL with order_id and key
				 */
				if ( method_exists( $order, 'get_checkout_order_received_url' ) ) {
					$redirect = $order->get_checkout_order_received_url();
				} else {
					$pre_wc_30 = version_compare( WC_VERSION, '3.0', '<' );
					$redirect = add_query_arg(
						array(
							'key' => $pre_wc_30 ? $order->order_key : $order->get_order_key(),
							'order' => $pre_wc_30 ? $order->id : $order->get_id(),
						),
						get_permalink( get_option( 'woocommerce_thanks_page_id' ) )
					);
				}

				wp_redirect( $redirect );
			}
		}



		/**
		 * Notification of status payments
		 *
		 */
		function notificacao() {
			global $woocommerce;

			// who are you?
			if(isset($_POST['notificationType']) && $_POST['notificationType'] == 'transaction') {




				if ($this->debug=='yes') $this->log->add( 'PagSeguro', 'Notificação de pagamento $_POST[notificationCode] '. print_r( $_POST['notificationCode'], true ) );

				// Fix return of data in new version of PagSeguro API 2.0
				$url = "https://ws.pagseguro.uol.com.br/v2/transactions/notifications/" . $_POST['notificationCode'] . "?email=" . $this->email . "&token=" . $this->token;

				if ($this->debug=='yes') $this->log->add( 'PagSeguro', 'Valor do this->notificationCode '. $this->notificationCode );


				$curl = curl_init($url);
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
				$transaction= curl_exec($curl);
				curl_close($curl);

				/**
				 * if any errors occur
				 * kill this process
				 */
				if($transaction == 'Unauthorized'){

					if ($this->debug=='yes') $this->log->add( 'PagSeguro', 'Verificação não autorizada (valores inválidos)' );

					$this->add_error(__('There was an error during the payment process, please try again.', 'woocommerce-gateway-pagseguro'));
					exit;
				}

				$transaction = simplexml_load_string($transaction);

				// short cut
				$order_code 	= $transaction->code;
				$order_ID 		= $transaction->reference;
				$order_status 	= $transaction->status;
				$order_total 	= $transaction->grossAmount; // sem desconto
				$order_desconto = $transaction->netAmount; // com desconto

				// this order exists?
				$pedido = new WC_Order( (int) $order_ID );

				if ($this->debug=='yes') $this->log->add( 'PagSeguro', 'Notificação do pedido #'. print_r( $transaction, true ) );


				/**
				 * For security reasons we check if the application
				 * exists and that the total order value is the
				 * same as the amount charged in store
				 */
				$pre_wc_30 = version_compare( WC_VERSION, '3.0', '<' );

				if ( $order_total == ( $pre_wc_30 ? $pedido->order_total : $pedido->get_total() ) ) {
					// what a is this?
					$arrStatus = $this->status_helper( $order_status, $pedido );

					/**
					 * PagSeguro no support URLs with query strings
					 * This is a simple hack to fix this problem
					 *
					 * Verify the transaction code and redirect
					 * to URL with order_id and key
					 */
					if ( method_exists( $order, 'get_checkout_order_received_url' ) ) {
						$redirect = $pedido->get_checkout_order_received_url();
					} else {
						$redirect = add_query_arg(
							array(
								'key' => $pre_wc_30 ? $pedido->order_key : $pedido->get_order_key(),
								'order' => $pre_wc_30 ? $pedido->id : $pedido->get_id(),
							),
							get_permalink( get_option( 'woocommerce_thanks_page_id' ) )
						);
					}

					wp_redirect( $redirect );

				} else {

					if ($this->debug=='yes') $this->log->add( 'PagSeguro', 'There was an error during the payment process, this order no exists.' );

					// this order no exists or was modified
					$this->add_error(__('There was an error during the payment process, this order no exists.', 'woocommerce-gateway-pagseguro'));
				}

			}
		}





		public function percorre_produtos_API( $order ) {
			$pre_wc_30 = version_compare( WC_VERSION, '3.0', '<' );
			$strXML = "";

			$strXML .= "
			<item>
				<id>1</id>
				<description>Pagamento do pedido #" . esc_html( $pre_wc_30 ? $order->id : $order->get_id() ) . "</description>
				<amount>". number_format( ( $pre_wc_30 ? $order->order_total : $order->get_total() ), 2, '.', '' ) ."</amount>
				<quantity>1</quantity>
				<weight>0</weight>
			</item>
			";

			return $strXML;
		}



		public function request_token_API( $order ) {
			global $woocommerce;

			if ( version_compare( WC_VERSION, '3.0', '<' ) ) { 
				$order->billing_phone = $billing_phone = str_replace( array( '(', '-', ' ', ')' ), '', $order->billing_phone );
				$order_id = $order->id;
				$order_key = $order->order_key;
				$full_name = $order->billing_first_name . ' ' . $order->billing_last_name;
				$billing_email = $order->billing_email;
			} else {
				$billing_phone = str_replace( array( '(', '-', ' ', ')' ), '', $order->get_billing_phone() );
				$order->set_billing_phone( $billing_phone );
				$order_id = $order->get_id();
				$order_key = $order->get_order_key();
				$full_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
				$billing_email = $order->get_billing_email();
			}

			$ddd = substr( $billing_phone, 0, 2 );
			$telefone = substr( $billing_phone, 2 );

			// no support to query string
			if ( method_exists( $order, 'get_checkout_order_received_url' ) ) {
				$urlRetorno = $order->get_checkout_order_received_url();
			} else {
				$urlRetorno = add_query_arg(
					array(
						'key' => $order_key,
						'order' => $order_id,
					),
					get_permalink( get_option( 'woocommerce_thanks_page_id' ) )
				);
			}

			if ($this->debug=='yes') $this->log->add( 'PagSeguro', "URL de retorno: ". $urlRetorno );

			$url = "https://ws.pagseguro.uol.com.br/v2/checkout/?email=" . $this->email . "&token=" . $this->token;
			$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>
				<checkout>
					<currency>BRL</currency>
					<redirectURL>". $urlRetorno ."</redirectURL>
					<items>
						". $this->percorre_produtos_API( $order ) ."
					</items>
					<reference>" . esc_html( $order_id ) . "</reference>
					<sender>
						<name>" . esc_html( $full_name ) . "</name>
						<email>" . esc_html( $billing_email ) . "</email>
					</sender>
					<shipping>
						<type>3</type>
						<address>
							<country>BRA</country>
						</address>
					</shipping>
				</checkout>";


			if ($this->debug=='yes') $this->log->add( 'PagSeguro', "Pacote XML gerado: \n". $xml );



			$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/xml; charset=UTF-8"));
			curl_setopt($curl, CURLOPT_POSTFIELDS, $xml);
			$xml= curl_exec($curl);
			curl_close($curl);



			if ($this->debug=='yes') $this->log->add( 'PagSeguro', "Requisição cURL: \n". $xml );



			if($xml == 'Unauthorized'){
				$this->add_error(__('There was an error during the payment process, unauthorized.', 'woocommerce-gateway-pagseguro'));

				if ($this->debug=='yes') $this->log->add( 'PagSeguro', 'There was an error during the payment process, unauthorized. ' . print_r( $xml, true ) );

				$woocommerce->session->PSerro = "1 Requsição não autorizada";
				return false;
			}

			$xml= simplexml_load_string($xml);

			if(count( $xml->error->code ) > 0){

				if ($this->debug=='yes') $this->log->add( 'PagSeguro', 'There was an error during the payment process, error on request token.' );

				$this->add_error(__('There was an error during the payment process, error on request token.', 'woocommerce-gateway-pagseguro'));
				$woocommerce->session->PSerro = "2 O valor retornado pela transação é nulo <!-- ". nl2br( print_r( $xml, true )) ."-->";
				return false;
			}else{


				/**
				 * Write token to accept pay in other time
				 * this is necessary why in PagSeguro for each
				 * request added a intention of payment
				 * unique by order.
				 */
				add_post_meta( $order_id, 'pagseguro_token', (string) $xml->code, true );


				return 'https://pagseguro.uol.com.br/v2/checkout/payment.html?code='. $xml->code .'&order='. $order_id;
			}

		}

		/**
		 * Add an error message
		 */
		public function add_error( $message ) {
			if ( function_exists( 'wc_add_notice' ) ) {
				wc_add_notice( $message, 'error' );
			} else {
				global $woocommerce;

				$woocommerce->add_error( $message );
			}
		}


	}

}

function add_pagseguro_gateway( $methods ) {
	$methods[] = 'WC_Gateway_Pagseguro'; return $methods;
}
