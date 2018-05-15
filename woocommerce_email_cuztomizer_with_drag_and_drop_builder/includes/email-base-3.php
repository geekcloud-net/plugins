<?php
/**
 * WooCommerce Email Customizer with Drag and Drop Email Builder
 * Create awesome transactional emails with a drag and drop email builder
 * @author Flycart Technologies LLP
 * @license GNU GPL V3 or later
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

global $wp_scripts, $woocommerce, $wpdb, $current_user, $order;
if (!defined('ABSPATH')) exit; // Exit if accessed directly

include_once('helper-3.php');

/**
 * Includes
 */
class WC_Email_Base
{
    /**
     * @var bool
     */
    public $order_id = false;
    /**
     * @var
     */
    public $order;

    /**
     * @var
     */
    public $order_data;

    /**
     * @var
     */
    public $logo;

    public $lang;

    public $email_type;


    /**
     * WC_Email_Base_Controller constructor.
     */
    public function __construct($email_type = '')
    {
        // Init Language.
        $this->lang = WOO_ECPB_LANG;
        $this->email_type = $email_type;
        $this->extractOrderID();
        $this->initOrderShortcodes();
    }

    /**
     * Set the order id
     * */
    public function setOrderId( $order_id ){
        $this->order_id = $order_id ;
    }

    /**
     * Initialize all order shortcodes
     * */
    public function initOrderShortcodes($sent_to_admin = '', $args = array()){
        
        if ($this->order_id && class_exists('WC_Order')) {
            $this->order = new WC_Order($this->order_id);
            $this->collectOrderData($sent_to_admin);
        }

        if(empty($this->order_id) || !$this->order_id){
            $out = $this->order_data;
            if (isset($_REQUEST['billing_email'])) {
                $out['[woo_mb_user_email]'] = sanitize_email($_REQUEST['billing_email']);
                $user = get_user_by( 'email', sanitize_email($_REQUEST['billing_email']));
                if ( ! empty( $user ) ) {
                    $out['[woo_mb_user_name]'] = $user->user_login;
                    $out['[woo_mb_user_id]'] = $user->ID;
                }
            }
            if(empty($out['[woo_mb_user_name]'])){
                if(isset( $_REQUEST['user_email'] )){
                    $user = get_user_by( 'email', sanitize_email($_REQUEST['user_email']));
                    if(isset($user->user_login)){
                        $out['[woo_mb_user_name]'] = $user->user_login;
                    }
                    if(isset($user->ID)) $out['[woo_mb_user_id]'] = $user->ID;
                } else if(isset( $_REQUEST['email'] )){
                    $user = get_user_by( 'email', sanitize_email($_REQUEST['email']));
                    if(isset($user->user_login)){
                        $out['[woo_mb_user_name]'] = $user->user_login;
                    }
                    if(isset($user->ID)) $out['[woo_mb_user_id]'] = $user->ID;
                }
            }
            if(empty($out['[woo_mb_user_email]'])){
                if(isset( $_REQUEST['user_email'] )){
                    $user = get_user_by( 'email', sanitize_email($_REQUEST['user_email']));
                    if(isset($user->user_email)){
                        $out['[woo_mb_user_email]'] = $user->user_email;
                    }
                    if(isset($user->ID)) $out['[woo_mb_user_id]'] = $user->ID;
                } else if(isset( $_REQUEST['email'] )){
                    $user = get_user_by( 'email', sanitize_email($_REQUEST['email']));
                    if(isset($user->user_email)){
                        $out['[woo_mb_user_email]'] = $user->user_email;
                    }
                    if(isset($user->ID)) $out['[woo_mb_user_id]'] = $user->ID;
                }
            }
            if(!empty($args)){
                if(isset($args['email'])){
                    if(isset($args['email']->id) && $args['email']->id == 'customer_reset_password'){
                        $out['[woo_mb_user_name]'] = $args['email']->user_login;
                        $out['[woo_mb_user_email]'] = $args['email']->user_email;
                        $resetURL = esc_url( add_query_arg( array( 'key' => $args['email']->reset_key, 'login' => rawurlencode( $args['email']->user_login ) ), wc_get_endpoint_url( 'lost-password', '', wc_get_page_permalink( 'myaccount' ) ) ) );
                        $out['[woo_mb_password_reset_url]'] = $resetURL;
                    }
                    if(isset($args['email']->id) && $args['email']->id == 'customer_new_account'){
                        global $wpdb, $wp_hasher;
                        // Generate something random for a password reset key.
                        $key = wp_generate_password( 20, false );

                        /** This action is documented in wp-login.php */
                        do_action( 'retrieve_password_key', $args['email']->user_login, $key );

                        if(isset($args['email']->user_pass) && !empty($args['email']->user_pass)){
                            $out['[woo_mb_user_password]'] = $args['email']->user_pass;
                        } else {
                            if(isset($_REQUEST['pass1-text']) && $_REQUEST['pass1-text'] != ''){
                                $out['[woo_mb_user_password]'] = $_REQUEST['pass1-text'];
                            } else if(isset($_REQUEST['pass1']) && $_REQUEST['pass1'] != ''){
                                $out['[woo_mb_user_password]'] = $_REQUEST['pass1-text'];
                            } else {
                                $out['[woo_mb_user_password]'] = '';
                            }
                        }

                        // Now insert the key, hashed, into the DB.
                        if ( empty( $wp_hasher ) ) {
                            $wp_hasher = new PasswordHash( 8, true );
                        }
                        $hashed = time() . ':' . $wp_hasher->HashPassword( $key );
                        $wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $args['email']->user_login ) );
                        $activation_url = network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($args['email']->user_login), 'login');
                        $out['[woo_mb_user_activation_link]'] = $activation_url;
                    }
                }
            }
            $this->order_data = $out;
        }

        /** Woo Mail Builder Shortcodes */
        /* Init Shortcodes */
        add_shortcode('woo_mb_order_id', array($this, 'processTag'));
        add_shortcode('woo_mb_order_link', array($this, 'processTag'));
        add_shortcode('woo_mb_transaction_id', array($this, 'processTag'));
        add_shortcode('woo_mb_order_sub_total', array($this, 'processTag'));
        add_shortcode('woo_mb_order_payment_method', array($this, 'processTag'));
        add_shortcode('woo_mb_order_payment_url', array($this, 'processTag'));
        add_shortcode('woo_mb_order_total', array($this, 'processTag'));
        add_shortcode('woo_mb_order_fee', array($this, 'processTag'));
        add_shortcode('woo_mb_order_refund', array($this, 'processTag'));
        add_shortcode('woo_mb_order_date', array($this, 'processTag'));
        add_shortcode('woo_mb_billing_address', array($this, 'processTag'));
        add_shortcode('woo_mb_shipping_address', array($this, 'processTag'));
        add_shortcode('woo_mb_view_order_url', array($this, 'processTag'));
        add_shortcode('woo_mb_site_url', array($this, 'processTag'));
        add_shortcode('woo_mb_site_name', array($this, 'processTag'));

        add_shortcode('woo_mb_payment_method', array($this, 'processTag'));
        add_shortcode('woo_mb_shipping_method', array($this, 'processTag'));

        add_shortcode('woo_mb_items', array($this, 'processTag'));

        add_shortcode('woo_mb_user_name', array($this, 'processTag'));
        add_shortcode('woo_mb_user_id', array($this, 'processTag'));
        add_shortcode('woo_mb_user_email', array($this, 'processTag'));
        add_shortcode('woo_mb_password_reset_url', array($this, 'processTag'));
        add_shortcode('woo_mb_user_activation_link', array($this, 'processTag'));
        add_shortcode('woo_mb_customer_note', array($this, 'processTag'));
        add_shortcode('woo_mb_customer_notes', array($this, 'processTag'));
        add_shortcode('woo_mb_customer_provided_note', array($this, 'processTag'));

        add_shortcode('woo_mb_billing_first_name', array($this, 'processTag'));
        add_shortcode('woo_mb_billing_last_name', array($this, 'processTag'));
        add_shortcode('woo_mb_billing_company', array($this, 'processTag'));
        add_shortcode('woo_mb_billing_address_1', array($this, 'processTag'));
        add_shortcode('woo_mb_billing_address_2', array($this, 'processTag'));
        add_shortcode('woo_mb_billing_city', array($this, 'processTag'));
        add_shortcode('woo_mb_billing_state', array($this, 'processTag'));
        add_shortcode('woo_mb_billing_postcode', array($this, 'processTag'));
        add_shortcode('woo_mb_billing_country', array($this, 'processTag'));
        add_shortcode('woo_mb_billing_phone', array($this, 'processTag'));
        add_shortcode('woo_mb_billing_email', array($this, 'processTag'));

        add_shortcode('woo_mb_shipping_first_name', array($this, 'processTag'));
        add_shortcode('woo_mb_shipping_last_name', array($this, 'processTag'));
        add_shortcode('woo_mb_shipping_company', array($this, 'processTag'));
        add_shortcode('woo_mb_shipping_address_1', array($this, 'processTag'));
        add_shortcode('woo_mb_shipping_address_2', array($this, 'processTag'));
        add_shortcode('woo_mb_shipping_city', array($this, 'processTag'));
        add_shortcode('woo_mb_shipping_state', array($this, 'processTag'));
        add_shortcode('woo_mb_shipping_postcode', array($this, 'processTag'));
        add_shortcode('woo_mb_shipping_country', array($this, 'processTag'));
        add_shortcode('woo_mb_user_password', array($this, 'processTag'));
        add_shortcode('woo_mb_custom_code', array($this, 'getCustomCode'));

        /* To get custom fields */
        if(!empty($this->order)){
            if(function_exists('wc_get_custom_checkout_fields')) {
                $custom_fields = wc_get_custom_checkout_fields($this->order);
                if (!empty($custom_fields)) {
                    foreach ($custom_fields as $key => $custom_field) {
                        add_shortcode('woo_mb_'.$key, array($this, 'processTag'));
                    }
                }
            }

            /**
             * Compatible - Flexible Checkout Fields for WooCommerce
             * */
            $custom_fields_flexible_checkout = WooEmailCustomizerCommon::getCustomFieldsOfFlexibleCheckoutFields();
            if(!empty($custom_fields_flexible_checkout) && count($custom_fields_flexible_checkout) > 0){
                foreach ($custom_fields_flexible_checkout as $key => $custom_fields_flexible_checkout_field) {
                    add_shortcode('woo_mb'.$key, array($this, 'processTag'));
                }
            }
        }
    }

    /**
     * Get language from order
     * */
    public function getLanguageFromOrder($args){
        $selectedLanguage = '';
        //to get language from WPML language
        $language = get_post_meta($args['order']->get_id(), 'wpml_language', true );
        if(isset($args['sent_to_admin']) && $args['sent_to_admin']){} else {
            if($language !== false && $language != ''){
                if(function_exists('icl_get_languages')){
                    $languages = icl_get_languages();
                    if(isset($languages[$language])){
                        if(isset($languages[$language]['default_locale'])){
                            $selectedLanguage = $languages[$language]['default_locale'];
                        }
                    }
                }
            }
        }
        return $selectedLanguage;
    }
    /**
     * To load Custom code
     * */
    public function getCustomCode($attr, $content, $tag){
        ob_start();
        global $woo_email_arguments;
        $template = $this->getTemplateOverride('woo_mail/custom_code.php');
        $path = WOO_ECPB_DIR . '/templates/woo_mail/custom_code.php';
        if($template){
            $path = $template;
        }
        $sent_to_admin = isset($woo_email_arguments['sent_to_admin'])? $woo_email_arguments['sent_to_admin']: false;
        $plain_text = isset($woo_email_arguments['plain_text'])? $woo_email_arguments['plain_text']: false;
        $email = isset($woo_email_arguments['email'])? $woo_email_arguments['email']: false;
        $order = $this->order;
        $email_id = $this->email_type;
        include($path);
        $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }

    /**
     * @param $template
     * @param $fromDB
     * @return bool|string
     */
    public function loadOrderEmailHTML($template, $lang)
    {
        if($template == 'customer_partially_refunded_order') $template = 'customer_refunded_order';

        $html_email = '';
        
        $this->checkLang(); // TODO: load lang from order

        if (empty($lang)) {
            $lang = $this->lang;
        }
        if($lang == 'en'){
            $lang = 'en_US';
        }
        global $has_template_in_woo_email_customizer_page_builder;
        if( !empty($template) ){
            $postID = $this->getEmailTemplateFromPost($template, $lang);
            if( $postID ){
                $emailTemplate = get_post($postID);
                $email_cont = json_decode($emailTemplate->post_content);
                if (isset($email_cont->html)) {
                    $stripTagContent = strip_tags($email_cont->html);
                    if(!empty($stripTagContent)){
                        $html_email = $email_cont->html;
                        $has_template_in_woo_email_customizer_page_builder = 1;
                    }
                }
            }
        }

        return $html_email;
    }

    /**
     *
     */
    public function extractOrderID()
    {
        $order_id = false;
        if (isset($_REQUEST['woo_mb_email_order'])) {
            if (sanitize_text_field($_REQUEST['woo_mb_email_type']) !== '') {
                $order_id = sanitize_text_field($_REQUEST['woo_mb_email_order']);
            }
        } elseif (isset($_REQUEST['post_ID'])) {
            if(isset($_REQUEST['post_type']))
            if (sanitize_text_field($_REQUEST['post_type']) == 'shop_order') {
                $order_id = sanitize_text_field($_REQUEST['post_ID']);
            }
        }
        $order_id = intval($order_id);
        if (!$order_id) {
            $id = '';
        }
        $this->order_id = $order_id;
    }

    /**
     * @param $atts
     * @param $content
     * @param $tag
     * @return string 
     */
    public function processTag($atts, $content, $tag)
    {
        return (isset($this->order_data['[' . $tag . ']']) ? $this->order_data['[' . $tag . ']'] : '');
    }

    public function switchLanguage()
    {
        $request = $_REQUEST;

        $this->checkLang();

        $template = sanitize_text_field($request['mailType']);

        $content = $this->loadBodyTemplate($template);

        echo json_encode($content);
        die();
    }

    protected function checkLang()
    {
        $this->lang = WOO_ECPB_LANG;
        $this->lang = get_locale();
        $lang = isset($_REQUEST['lang']) ? sanitize_text_field($_REQUEST['lang']) : false;
        if (!$lang) return false;

        // Default Accepted language string length is 5 (ex. tn_IN, en_US..)
        $lang = trim($lang);
        if (strlen($lang) == 5) {
            // Updating Active Language.
            $this->lang = $lang;
        }
    }

    /**
     * @param $header
     * @param $logo
     * @return string
     */
    public function processLOGO($header, $logo)
    {
        ob_start();
        $path = $this->loadTemplateURL('template-header');
        $data['header'] = $header;
        $data['logo'] = $logo;
        include($path);
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    /**
     * @param $tag
     * @return mixed
     */
    public function getImageCode($tag)
    {
        // Perform Changes in Image Code.
        return $tag;
    }

    /**
     * @return WP_Query
     * [NOT USED]
     */
    public function orderListInfo()
    {
        $limit_orders = 800;
        $order_collection = new WP_Query(array(
            'post_type' => 'shop_order',
            'post_status' => array_keys(wc_get_order_statuses()),
            'posts_per_page' => $limit_orders,
        ));

        $order_collection = $order_collection->posts;

        return $order_collection;
    }

    /**
     * save email Template
     * */
    public function save_email_template()
    {
        $post = $_REQUEST;
        $result['status'] = 'FAILED';
        $result['status_code'] = 0;
        $result['status_message'] = esc_html__('Failed to save');
        if(isset($post['email_type']) && isset($post['lang']) && isset($post['email'])){
            $newPost['post_type'] = 'woo_mb_template';
            $newPost['post_title'] = sanitize_text_field($post['email_type']);
            $lang = sanitize_text_field($post['lang']);
            if ( empty($lang) ) {
                $post['lang'] = 'en_US';
            }
            $newPost['post_name'] = sanitize_text_field($post['lang']);
            $postid = $this->getEmailTemplateFromPost(sanitize_text_field($post['email_type']), sanitize_text_field($post['lang']));
            $newPost['post_content'] = $post['email'];
            if($postid){
                $newPost['ID'] = $postid;
                $updated = wp_update_post($newPost);
                if($updated){
                    $result['status'] = 'SUCCESS';
                    $result['status_code'] = 200;
                    $result['status_message'] = esc_html__('Save success');
                }
            } else {
                $inserted = wp_insert_post($newPost);
                if($inserted){
                    $result['status'] = 'SUCCESS';
                    $result['status_code'] = 200;
                    $result['status_message'] = esc_html__('Save success');
                }
            }
        }
        echo json_encode($result);
        die();
    }

    /**
     * reset email Template to default
     * */
    public function reset_email_templates()
    {
        $isAdmin = is_admin();
        if($isAdmin){
            require_once ('activation-helper.php');
            WOOMBPB_RemoveEmailTemplateFromPost();
            WOOMBPBonActivatePlugin();
            $result['status'] = 'SUCCESS';
            $result['status_code'] = 200;
            $result['status_message'] = esc_html__('Template reset successfully');
        } else {
            $result['status'] = 'FAILED';
            $result['status_code'] = 0;
            $result['status_message'] = esc_html__('Template reset Failed');
        }
        echo json_encode($result);
        die();
    }

    /**
     * Get Email template from post
     * */
    protected function getEmailTemplateFromPost($email_type, $lang){
        if($email_type != '' && $lang != ''){
            global $wpdb;
            $postid = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . $email_type . "' AND post_name = '".strtolower($lang)."' AND post_type = 'woo_mb_template'" );
            return $postid;
        } else {
            return false;
        }
    }

    /**
     * This function can return bulk tagged data or formatted data.
     *
     * @return bool True|False
     */
    public function email_template_parser()
    {
        $request = $_REQUEST;
        $this->order_id = false;
        if (isset($request['order_id'])) {
            $order_id = sanitize_text_field($request['order_id']);
            $order_id = intval($order_id);
            if (!$order_id) {
                $order_id = '';
            }
            $this->order_id = $order_id;
        }

        $return_formatted = false;
        if (isset($request['return_state'])) {
            $return_formatted = (sanitize_text_field($request['return_state']) == '') ? false : true;
        }

        if ($this->order_id == false) return false;

        if ($return_formatted) {
            if (isset($request['header'])) $this->header = $request['header'];
            if (isset($request['body'])) $this->body = $request['body'];
            if (isset($request['footer'])) $this->footer = $request['footer'];
            if (isset($request['logo'])) $this->logo = sanitize_text_field($request['logo']);
        }

        $this->order = new WC_Order($this->order_id);

        if (is_null($this->order) or empty($this->order) or !isset($this->order)) return false;

        $this->collectOrderData();

        if ($return_formatted == true) {
            $content = array('header',
                'body',
                'footer');

            foreach ($content as $block) {
                $this->parsing_tags($block);
            }
            $result = array(
                'header' => $this->header,
                'body' => $this->body,
                'footer' => $this->footer,
                'logo' => '<img src="' . $this->logo . '">'
            );
            echo json_encode($result);
        } else {

            $this->collectOrderData();

            $result = new stdClass();
            $result->order_id = $this->order_id;
            $result->order = $this->order;
            $result->order_data = $this->order_data;
            $result->order_items = $result->order->get_items();
            $result->user_details = $result->order->get_user();
            $result->email = '';
            if(isset($request['email_type']) && isset($request['lang'])){
                $postID = $this->getEmailTemplateFromPost(sanitize_text_field($request['email_type']), sanitize_text_field($request['lang']));
                if($postID){
                    $emailTemplate = get_post($postID);
                    $result->email = $emailTemplate->post_content;
                }
            }
            if (extension_loaded ('newrelic')) {
                newrelic_disable_autorum();
            }
            echo json_encode($result);
        }
        die();
    }

    /**
     * To Load CSS Configurations.
     *
     * @return bool|string
     */
    public function loadCssConfig()
    {
        $url = WOO_ECPB_DIR . '/assets/css/custom.css';
        return $this->readFile($url);
    }

    /**
     * To Save Custom CSS.
     */
    public function cssConfig()
    {
        $url = WOO_ECPB_DIR . '/assets/css/custom.css';
        $data = $_REQUEST['style'];
        $this->saveFile($url, $data);
    }

    /**
     * Send Mail
     *
     * @return mixed
     */
    public function sendTestMail()
    {
        $request = $_REQUEST;
        $request['subject'] = 'Test Mail';
        $this->sendMail($request);
    }

    /**
     * @param $request
     * @return bool
     */
    public function sendMail($request)
    {
        $mail_to = false;
        $cc = array();
        $body = '';
        $subject = '';
        $attachments = array();
        if (isset($request['subject'])) $mail_to = sanitize_text_field($request['subject']);
        if (isset($request['mail'])) {

            if (filter_var($request['mail'], FILTER_VALIDATE_EMAIL)) {
                $mail_to = $request['mail'];
            }
        }

        if (isset($request['cc'])) $cc = $request['cc'];
        if (isset($request['header'])) $body .= $request['header'];
        if (isset($request['body'])) $body .= $request['body'];
        if (isset($request['footer'])) $body .= $request['footer'];

        if ($mail_to == false) return false;

        if (!empty($cc)) {
            foreach ($cc as $val) {
                $headers[] = 'Cc: ' . $val;
            }
        }
        $headers[] = 'MIME-Version: 1.0' . "\r\n";
        $headers[] = 'Content-type:text/html;charset=UTF-8' . "\r\n";
        $mail = new WC_Email();
        $mail->send($mail_to, $subject, $body, $headers, $attachments);
    }

    /**
     * @param $block
     * @return bool
     */
    public function parsing_tags($block)
    {
        if (!isset($block) || empty($block) || is_null($block)) return false;
        $body = $this->$block;
        foreach ($this->order_data as $index => $data) {
            $body = str_replace($index, $this->order_data[$index], $body);
        }
        $this->$block = $body;
    }

    /**
     *
     */
    public function collectOrderData($sent_to_admin = '')
    {
        $order = $this->order;
        if (is_null($this->order_id)) return false;
        if (is_null($order)) return false;
        $items = $order->get_items();

        $this->getOrderItems($items);

        //Getting Fee & Refunds:
        $fee = 0;
        $refund = 0;
        $order = $this->order;
        $totals = $order->get_order_item_totals();

        foreach ($totals as $index => $value) {
            if (strpos($index, 'fee') !== false) {
                $fees = $order->get_fees();
                foreach ($fees as $feeVal){
                    $fee += $feeVal->get_amount();
                }
            }
            if (strpos($index, 'refund') !== false) {
                $refund = $order->get_total_refunded();
            }
        }
        unset($order_total);
        //User Info
        $user_data = $order->get_user();
        if(isset($user_data->user_nicename)){
            $out['[woo_mb_user_name]'] = $user_data->user_nicename;
        } else {
            $out['[woo_mb_user_name]'] = $order->get_billing_first_name();
        }
        if(isset($user_data->user_email)){
            $out['[woo_mb_user_email]'] = $user_data->user_email;
        } else {
            $out['[woo_mb_user_email]'] = $order->get_billing_email();
        }

        //Order totals
        if(isset($totals['cart_subtotal']['value'])){
            $out['[woo_mb_order_sub_total]'] = $totals['cart_subtotal']['value'];
        } else {
            $out['[woo_mb_order_sub_total]'] = '';
        }
        if(isset($totals['payment_method']['value'])){
            $out['[woo_mb_order_payment_method]'] = $totals['payment_method']['value'];
        } else {
            $out['[woo_mb_order_payment_method]'] = '';
        }
        $out['[woo_mb_order_total]'] = $this->orderTotal();
        $out['[woo_mb_order_fee]'] = $fee;
        $out['[woo_mb_order_refund]'] = $refund;
        $out['[woo_mb_order_shipping]'] = $order->calculate_shipping();

        $out['[woo_mb_order_payment_url]'] = '<a href="'.esc_url( $order->get_checkout_payment_url() ).'">'.esc_html__('Payment page', 'woo-email-customizer-page-builder').'</a>';

        $woo_mb_settings = get_option('woo_mb_settings', '');
        if ($woo_mb_settings != ''){
            $woo_mb_settings = json_decode($woo_mb_settings);
        }
        $order_url = isset($woo_mb_settings->order_url)? $woo_mb_settings->order_url: '';

        //Order Info
        $out['[woo_mb_order_id]'] = $order->get_id();
        $out['[woo_mb_order_link]'] = '<a href="'.$order_url.'">'.esc_html__('Order', 'woo-email-customizer-page-builder').'</a>';
        $out['[woo_mb_order_link]'] = str_replace('[woo_mb_order_id]', $order->get_id(), $out['[woo_mb_order_link]']);
        $created_date = $order->get_date_created();
        if($created_date != null){
            $out['[woo_mb_order_date]'] = $order->get_date_created()->date_i18n(wc_date_format());
        }
        $out['[woo_mb_shipping_method]'] = $order->get_shipping_method();
        $out['[woo_mb_payment_method]'] = $order->get_payment_method_title();

        $out['[woo_mb_view_order_url]'] = $order->get_view_order_url();

        //Address Details
        $out['[woo_mb_transaction_id]'] = $order->get_transaction_id();
        $out['[woo_mb_billing_address]'] = $order->get_formatted_billing_address();
        $out['[woo_mb_shipping_address]'] = $order->get_formatted_shipping_address();

        $out['[woo_mb_billing_first_name]'] = $order->get_billing_first_name();
        $out['[woo_mb_billing_last_name]'] = $order->get_billing_last_name();
        $out['[woo_mb_billing_company]'] = $order->get_billing_company();
        $out['[woo_mb_billing_address_1]'] = $order->get_billing_address_1();
        $out['[woo_mb_billing_address_2]'] = $order->get_billing_address_2();
        $out['[woo_mb_billing_city]'] = $order->get_billing_city();
        $out['[woo_mb_billing_state]'] = $order->get_billing_state();
        $out['[woo_mb_billing_postcode]'] = $order->get_billing_postcode();
        $out['[woo_mb_billing_country]'] = $order->get_billing_country();
        $out['[woo_mb_billing_phone]'] = $order->get_billing_phone();
        $out['[woo_mb_billing_email]'] = $order->get_billing_email();

        $out['[woo_mb_shipping_first_name]'] = $order->get_shipping_first_name();
        $out['[woo_mb_shipping_last_name]'] = $order->get_shipping_last_name();
        $out['[woo_mb_shipping_company]'] = $order->get_shipping_company();
        $out['[woo_mb_shipping_address_1]'] = $order->get_shipping_address_1();
        $out['[woo_mb_shipping_address_2]'] = $order->get_shipping_address_2();
        $out['[woo_mb_shipping_city]'] = $order->get_shipping_city();
        $out['[woo_mb_shipping_state]'] = $order->get_shipping_state();
        $out['[woo_mb_shipping_postcode]'] = $order->get_shipping_postcode();
        $out['[woo_mb_shipping_country]'] = $order->get_shipping_country();

        $customerNotes = $order->get_customer_order_notes();
        $customerNoteHtml = $customerNoteHtmlList = '';
        if(!empty($customerNotes) && count($customerNotes)){
            $customerNoteHtmlList = $this->getOrderCustomerNotes($customerNotes);
            $customerNote_single[] = $customerNotes[0];
            $customerNoteHtml = $this->getOrderCustomerNotes($customerNote_single);
        }
        $out['[woo_mb_customer_note]'] = $customerNoteHtml;
        $out['[woo_mb_customer_notes]'] = $customerNoteHtmlList;
        $out['[woo_mb_customer_provided_note]'] = $order->get_customer_note();

        $out['[woo_mb_site_name]'] = get_bloginfo('name');
        $out['[woo_mb_site_url]'] = '<a href="' . site_url() . '"> '.esc_html__('Go to site', 'woo-email-customizer-page-builder').' </a>';

        $out['[woo_mb_items]'] = $this->orderItems($items, $sent_to_admin);

        if(isset($out['[woo_mb_user_email]']) && $out['[woo_mb_user_email]'] != ''){
            $user = get_user_by( 'email', $out['[woo_mb_user_email]']);
            $out['[woo_mb_user_id]'] = (isset($user->ID))? $user->ID: '';
        }

        //TMP
        $out['[woo_mb_logo]'] = ' <img src="' . $this->logo . '">';

        /* To get custom fields */
        if(!empty($order)){
            if(function_exists('wc_get_custom_checkout_fields')) {
                $custom_fields = wc_get_custom_checkout_fields($order);
                if (!empty($custom_fields)) {
                    foreach ($custom_fields as $key => $custom_field) {
                        $out['[woo_mb_' . $key . ']'] = get_post_meta($order->ID, $key, true);
                    }
                }
            }
        }

        /**
         * Compatible - Flexible Checkout Fields for WooCommerce
         * */
        $custom_fields_flexible_checkout = WooEmailCustomizerCommon::getCustomFieldsOfFlexibleCheckoutFields();
        if(!empty($custom_fields_flexible_checkout) && count($custom_fields_flexible_checkout) > 0){
            foreach ($custom_fields_flexible_checkout as $key => $custom_fields_flexible_checkout_field) {
                $out['[woo_mb' . $key . ']'] = wpdesk_get_order_meta($order, $key, true);
            }
        }

        $this->order_data = $out;
    }

    /**
     * Get template override
     * $template_name woo_mail/order_items-3.php
     * */
    public function getTemplateOverride($template_name){
        $template = locate_template(
            array(
                trailingslashit( dirname(WOO_ECPB_PLUGIN_BASENAME) ) . $template_name,
                $template_name,
            )
        );

        return $template;
    }

    /**
     * @param $items
     * @return string
     */
    public function orderItems($items, $sent_to_admin = '')
    {

        ob_start();
        $template = $this->getTemplateOverride('woo_mail/order_items-3.php');
        $path = WOO_ECPB_DIR . '/templates/woo_mail/order_items-3.php';
        if($template){
            $path = $template;
        }
        $config = $items;
        $order = $this->order;
        include($path);
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    /**
     * Get order item table instead of loading from woo-commerce
     * @param $order
     * @return string
     */
    public function getOrdetItemTables($order, $default_args){
        $items = $order->get_items();
        $template_order_item = $this->getTemplateOverride('woo_mail/email-order-items-3.php');
        $path_order_item = WOO_ECPB_DIR . '/templates/woo_mail/email-order-items-3.php';
        if($template_order_item){
            $path_order_item = $template_order_item;
        }
        $woo_mb_settings = get_option('woo_mb_settings', '');
        if ($woo_mb_settings != ''){
            $woo_mb_settings = json_decode($woo_mb_settings);
        }
        $show_product_image = isset($woo_mb_settings->show_product_image)? $woo_mb_settings->show_product_image: 0;

        $default_args['image_size'][0] = isset($woo_mb_settings->product_image_width)? $woo_mb_settings->product_image_width: 32;
        $default_args['image_size'][1] = isset($woo_mb_settings->product_image_height)? $woo_mb_settings->product_image_height: 32;

        $args = array(
            'order'               => $order,
            'items'               => $order->get_items(),
            'show_download_links' => $order->is_download_permitted() && ! $default_args['sent_to_admin'],
            'show_sku'            => $default_args['show_sku'],
            'show_purchase_note'  => $order->is_paid() && ! $default_args['sent_to_admin'],
            'show_image'          => $show_product_image,
            'image_size'          => $default_args['image_size'],
            'plain_text'          => $default_args['plain_text'],
            'sent_to_admin'       => $default_args['sent_to_admin'],
        );
        include($path_order_item);
    }

    /**
     * @param $items
     * @return string
     */
    public function getOrderCustomerNotes($customerNotes)
    {
        ob_start();
        $default_path = WC()->plugin_path() . '/templates/';

        $template = $this->getTemplateOverride('woo_mail/order_customer_notes.php');
        $path = WOO_ECPB_DIR . '/templates/woo_mail/order_customer_notes.php';
        if($template){
            $path = $template;
        }

        include($path);
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    /**
     * @return string
     */
    public function orderTotal()
    {
        ob_start();
        $template = $this->getTemplateOverride('woo_mail/order_totals-3.php');
        $path = WOO_ECPB_DIR . '/templates/woo_mail/order_totals-3.php';
        if($template){
            $path = $template;
        }

        $order = $this->order;
        include($path);
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    /**
     * @param $items
     */
    public function getOrderItems(&$items)
    {
       /* $item_list = array();
        foreach ($items as $index => $item) {
            $item_list[$index]['product_name'] = $this->get_first($item['name']);
            $item_list[$index]['type'] = $this->get_first($item['type']);
            $item_list[$index]['qty'] = $this->get_first($item['item_meta']['_qty']);
            $item_list[$index]['tax_class'] = $this->get_first($item['item_meta']['_tax_class']);
            $item_list[$index]['product_id'] = $this->get_first($item['item_meta']['_product_id']);
            $item_list[$index]['variation_id'] = $this->get_first($item['item_meta']['_variation_id']);
            $item_list[$index]['line_total'] = $this->get_first($item['item_meta']['_line_total']);
            $item_list[$index]['line_subtotal'] = $this->get_first($item['item_meta']['_line_subtotal']);
            $item_list[$index]['line_subtotal_tax'] = $this->get_first($item['item_meta']['_line_subtotal_tax']);
            $item_list[$index]['line_tax'] = $this->get_first($item['item_meta']['_line_tax']);
            $item_list[$index]['line_tax_data'] = $this->get_first($item['item_meta']['_line_tax_data']);
            $item_list[$index]['item'] = $item;
        }
        $items = $item_list;*/
    }

    /**
     * @param $array
     * @return mixed
     */
    public function get_first($array)
    {
        $res = $array;
        if (is_array($array)) {
            if (isset($array[0])) {
                $res = $array[0];
            }
        }
        return $res;
    }

    public function copyTemplateFromAnother(){
        $isAdmin = is_admin();
        if($isAdmin){
            $status = 0;
            $request = $_REQUEST;
            if(empty($request['lang']) || $request['lang'] == 'en' || $request['lang'] == ''){
                $request['lang'] = 'en_us';
            }
            if(empty($request['lang_from']) || $request['lang_from'] == 'en' || $request['lang_from'] == ''){
                $request['lang_from'] = 'en_us';
            }
            $copyFrom = $this->getEmailTemplateFromPost(sanitize_text_field($request['email_type_from']), sanitize_text_field($request['lang_from']));
            $copyTo = $this->getEmailTemplateFromPost(sanitize_text_field($request['email_type']), sanitize_text_field($request['lang']));
            if($copyFrom){
                $emailTemplate = get_post($copyFrom);
                $content = $emailTemplate->post_content;
                $newPost['post_name'] = strtolower(sanitize_text_field($request['lang']));
                $newPost['post_title'] = sanitize_text_field($request['email_type']);
                $newPost['post_type'] = 'woo_mb_template';
                $newPost['post_content'] = $content;
                if($copyTo){
                    $newPost['ID'] = $copyTo;
                    $updated = wp_update_post($newPost);
                    if($updated){
                        $status = 1;
                        $this->updateTheSameContentTableToTable($copyFrom, $updated);
                    } else {
                        $status = 0;
                    }
                } else {
                    $inserted = wp_insert_post($newPost);
                    if($inserted){
                        $status = 1;
                        $this->updateTheSameContentTableToTable($copyFrom, $inserted);
                    } else {
                        $status = 0;
                    }
                }
            } else {
                $result['status'] = 'FAILED';
                $result['status_code'] = 0;
                $result['status_message'] = esc_html__('Failed to copy: Template not exist'.$request['email_type_from'].' '.$request['lang_from']);
                echo json_encode($result);
                die();
            }
            if($status){
                $result['status'] = 'SUCCESS';
                $result['status_code'] = 200;
                $result['status_message'] = esc_html__('Copied email template successfully');
            } else {
                $result['status'] = 'FAILED';
                $result['status_code'] = 0;
                $result['status_message'] = esc_html__('Failed to copy email template');
            }
        } else {
            $result['status'] = 'FAILED';
            $result['status_code'] = 0;
            $result['status_message'] = esc_html__('Failed to copy email template');
        }
        echo json_encode($result);
        die();
    }

    /**
     * Update the same content in table to table //This due to while get the content through post it removes the slashes
     * */
    protected function updateTheSameContentTableToTable($source_id, $target_id){
        global $wpdb;
        $query = "UPDATE $wpdb->posts AS target";
        $query .= " LEFT JOIN $wpdb->posts AS source ON source.ID = $source_id";
        $query .= " SET target.post_content = source.post_content";
        $query .= " WHERE target.ID = $target_id";
        $wpdb->get_var($query);
    }
}
