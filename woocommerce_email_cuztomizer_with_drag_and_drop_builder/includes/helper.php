<?php
/**
 * WooCommerce Email Customizer with Drag and Drop Email Builder
 * Create awesome transactional emails with a drag and drop email builder
 * @author Flycart Technologies LLP
 * @license GNU GPL V3 or later
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class WooMbHelper
{

    public $id = 'woo_email_customizer_page_builder';

    private $email;

    public $lang;

    /**
     * Construct empty on purpose
     */
    public function __construct()
    {
        $this->lang = WOO_ECPB_LANG;
    }

    public function getInstance()
    {
        if (!$this->email) {
            $this->email = new WC_Email_Base();
        }
        return $this->email;
    }


    // ----------------------------------------- Woo Mail --------------------------------------------------------------

    public function cssConfig()
    {
        $email_process = $this->getInstance();
        $email_process->cssConfig($_REQUEST);
        die();
    }

    public function loadCssConfig()
    {
        $email_process = $this->getInstance();
        $email_process->loadCssConfig();
        die();
    }

    /**
     * @param $html
     * @return bool|mixed
     */
    static function makeString($html)
    {
        if (is_null($html) || empty($html) || !isset($html)) return false;
        $out = $html;
        // This Process only helps, single level array.
        if (is_array($html)) {
            foreach ($html as $id => $value) {
                // Three Possible tags for PHP to Init.
                $value = preg_replace(array('/^<\?php.*\?\>/', '/^<\%.*\%\>/', '/^<\?.*\?\>/', '/^<\?=.*\?\>/'), '', $value);
                $value = self::delete_all_between('<?php', '?>', $value);
                $value = self::delete_all_between('<?', '?>', $value);
                $value = self::delete_all_between('<?=', '?>', $value);
                $value = self::delete_all_between('<%', '%>', $value);
                $value = str_replace(array('<?php', '<?', '<?=', '<%'), '', $value);
                $html[$id] = $value;
            }
            return $out;
        } else {
            // Three Possible tags for PHP to Init.
            $html = preg_replace(array('/^<\?php.*\?\>/', '/^<\%.*\%\>/', '/^<\?=.*\?\>/'), '', $html);
            $html = self::delete_all_between('<?php', '?>', $html);
            $html = self::delete_all_between('<?', '?>', $html);
            $html = self::delete_all_between('<?=', '?>', $html);
            $html = self::delete_all_between('<%', '%>', $html);
            $html = str_replace(array('<?php', '<?', '<?=', '<%'), '', $html);
            return $html;
        }
    }

    static function delete_all_between($beginning, $end, $string)
    {
        $beginningPos = strpos($string, $beginning);
        $endPos = strpos($string, $end);
        if ($beginningPos === false || $endPos === false) {
            return $string;
        }

        $textToDelete = substr($string, $beginningPos, ($endPos + strlen($end)) - $beginningPos);

        return str_replace($textToDelete, '', $string);
    }


    // ----------------------------------------- Woo Mail END ----------------------------------------------------------

    /**
     *
     */
    public function init()
    {
        //
    }

    /**
     * Allows hooking by the templates wishing to be initialized
     *
     */
    public static function register_email_templates()
    {
        do_action('register_email_template');
    }

    /**
     *  Ajax send email
     */
    public function send_email()
    {
        global $order, $woocommerce, $woo_mb_email_control;

        // Just get the current email and email contents. Send the email to the current logged in admin user
        $email_to = (isset($_REQUEST['mail']) ? sanitize_email($_REQUEST['mail']) : NULL);
        $order_id = (isset($_REQUEST['order_id']) ? sanitize_text_field($_REQUEST['order_id']) : NULL);
        $woo_mb_email_type = (isset($_REQUEST['woo_mb_email_type']) ? sanitize_text_field($_REQUEST['woo_mb_email_type']) : NULL);
        $lang = (isset($_REQUEST['lang']) ? sanitize_text_field($_REQUEST['lang']) : NULL);
        if (!empty($email_to) && !empty($order_id) && !empty($woo_mb_email_type) ) {
            $woo_mb_base = new WC_Email_Base($woo_mb_email_type);
            $woo_mb_base->setOrderId($order_id);
            $woo_mb_base->initOrderShortcodes();
            if(empty($lang)){
                $lang = 'en_US';
            }
            $html = $woo_mb_base->loadOrderEmailHTML( $woo_mb_email_type, $lang );
            $html = do_shortcode($html) ;

            $email_to = wc_clean($email_to);
            $subject = ' Test mail ';
            $message = '';
            $message = $html;
            $headers = array('Content-Type: text/html; charset=UTF-8');

            wp_mail( $email_to, $subject, $message, $headers );
        }

        die();
    }

    public function nopriv_send_email()
    {
        esc_html_e('You must be logged in', 'woo-email-customizer-page-builder');
        die();
    }

    public function woocommerce_email_recipient()
    {
        if (isset($_REQUEST['woo_mb_email_addresses'])) {
            return $_REQUEST['woo_mb_email_addresses'];
        }
    }

    /**
     * Render admin page.
     *
     */
    public function woo_mb_render_admin_page()
    {
        $wooVersion3 = version_compare( WOO_ECPB_WOOCOMMERCE_VERSION, "3.0", ">=" );
        if( $wooVersion3 ) {
            require_once __DIR__ . '/../pages/woo-admin-page-3.php';
        } else {
            require_once __DIR__ . '/../pages/woo-admin-page.php';
        }
        
    }

    /**
     * Render template page.
     *
     */
    public function woo_mb_render_template_page()
    {

    }

    /**
     * Add a submenu item to the WooCommerce menu
     *
     */
    public function admin_menu()
    {

        add_submenu_page(
            'woocommerce',
            esc_html__('WooCommerce Email Customizer', 'woo-email-customizer-page-builder'),
            esc_html__('WooCommerce Email Customizer', 'woo-email-customizer-page-builder'),
            'manage_woocommerce',
            $this->id,
            array($this, 'woo_mb_render_admin_page')
        );
    }

    public function woocommerce_settings_button($data)
    {

        global $woocommerce, $wp_scripts, $current_screen;

        $woo_mb_url = "";
        $woo_mb_url .= admin_url();
        $woo_mb_url .= "admin.php";
        $woo_mb_url .= "?";
        $woo_mb_url .= "page=woo_email_customizer_page_builder";

        if (isset($_REQUEST["section"])) {

            if (function_exists('WC')) {
                $wooinst = WC();
                $mailer = $wooinst->mailer();
                $mails = $mailer->get_emails();
            } else {
                $mailer = $woocommerce->mailer();
                $mails = $mailer->get_emails();
            }

            if (!empty($mails)) {
                foreach ($mails as $mail) {
                    $template = str_replace("wc_email_", "", sanitize_text_field($_REQUEST["section"]));
                    if ($mail->id == $template) {
                        $woo_mb_url .= "&woo_mb_email_type=" . $template;
                    }
                }
            }
        }

        ?>
        <div class="pe-wc-settings-holder">

            <?php if (isset($_REQUEST["section"]) && sanitize_text_field($_REQUEST["section"]) != "") { ?>

                <!-- Inner Tabs -->
                <h4><?php esc_html_e('Woo Mail Builder', 'woo-email-customizer-page-builder'); ?></h4>
                <p>
                    <a class="button ec" href="<?php echo esc_url($woo_mb_url) ?>" target="preview_email"><?php esc_html_e('Preview Email', 'woo-email-customizer-page-builder'); ?></a>
                    <?php esc_html_e("Preview and test emails as they will appear in mail clients when received.", 'woo-email-customizer-page-builder') ?>
                </p>

            <?php } else { ?>

                <!-- First Tab -->
                <h3><?php esc_html_e('Woo Mail Builder', 'woo-email-customizer-page-builder'); ?></h3>
                <p>
                    <a class="button ec" href="<?php echo esc_url($woo_mb_url) ?>" target="preview_email"><?php esc_html_e('Preview Email', 'woo-email-customizer-page-builder'); ?></a>
                    <?php esc_html_e("Preview and test emails as they will appear in mail clients when received.", 'woo-email-customizer-page-builder') ?>
                </p>

            <?php } ?>
        </div>
        <?php
    }

    public function woo_mb_get_new_template($located, $template_name, $args, $template_path, $default_path)
    {
       $this_template = false ;

       $vars = new stdClass();
        if(!isset($args['email']) && isset($args['order']) && isset($args['email_heading'])) {
            global $woocommerce;
            $mailerWC = $woocommerce->mailer();
            if(isset($mailerWC->emails)){
                $emailWC = $mailerWC->emails;
                foreach ($emailWC as $mailer){
                    if(!empty($mailer->object) && $args['email_heading'] == $mailer->heading){
                        $args['email'] = $mailer;
                        break;
                    }
                }
            }
        }
        if (isset($args['email']) && isset($args['email']->id) && !empty( $args['email']->id ) && isset($args['order']) && isset($args['order']->id) ) {

           $woo_mb_base = new WC_Email_Base($args['email']->id);
            $woo_mb_base->setOrderId($args['order']->id);
            if(isset($args['sent_to_admin'])){
                $woo_mb_base->initOrderShortcodes($args['sent_to_admin']);
            } else {
                $woo_mb_base->initOrderShortcodes();
            }
            $html = $woo_mb_base->loadOrderEmailHTML( $args['email']->id, '' );

            if ( !empty($html) ) {
                $this_template = WOO_ECPB_DIR . '/templates/woo-single-mail-template.php';
            }
            if (!file_exists($this_template)) $this_template = false;    
       }

       if (!$this_template) {
            $this_template = $located;
        }

        $located = $this_template;

        return $located;
    }

}