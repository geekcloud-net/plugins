<?php
/**
 * Customer completed order email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/customer-completed-order.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you (the theme developer).
 * will need to copy the new files to your theme to maintain compatibility. We try to do this.
 * as little as possible, but it does happen. When this occurs the version of the template file will.
 * be bumped and the readme will list any important changes.
 *
 * @see 	    http://docs.woothemes.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates/Emails
 * @version     2.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $woo_email_arguments;
$woo_email_arguments = $args;
$html = '';
if(!isset($args['email']) && isset($args['order']) && isset($args['email_heading'])) {
	global $woocommerce;
	$mailerWC = $woocommerce->mailer();
	if(isset($mailerWC->emails)){
		$emailWC = $mailerWC->emails;
		foreach ($emailWC as $mailer){
            if(!empty($mailer->object) && $args['email_heading'] == $mailer->heading){
				$args['email'] = $mailer;
				break;
            } else if(isset($mailer->settings) && !empty($mailer->settings)){
                $settings = $mailer->settings;
                if(isset($settings['heading']) && $args['email_heading'] == $settings['heading']){
                    $args['email'] = $mailer;
                    break;
                }
            }
		}
	}
}
if (isset($args['email']) && isset($args['email']->id) && !empty( $args['email']->id ) && isset($args['order']) && $args['order']->get_id() ) {
	$woo_mb_base = new WC_Email_Base($args['email']->id);
	$woo_mb_base->setOrderId($args['order']->get_id());
	if(isset($args['sent_to_admin'])){
        $woo_mb_base->initOrderShortcodes($args['sent_to_admin']);
	} else {
        $woo_mb_base->initOrderShortcodes();
	}
    $selectedLanguage = '';
    if(is_admin()){
        $selectedLanguage = $woo_mb_base->getLanguageFromOrder($args);
    }
	$html = $woo_mb_base->loadOrderEmailHTML( $args['email']->id, $selectedLanguage);
	$html = do_shortcode($html) ;
} else if (isset($args['email']) && isset($args['email']->id)) {
	if($args['email']->id == 'customer_new_account' || $args['email']->id == 'customer_reset_password') {
		$woo_mb_base = new WC_Email_Base($args['email']->id);
		$woo_mb_base->setOrderId(0);
		$woo_mb_base->initOrderShortcodes($args['sent_to_admin'], $args);
		$html = $woo_mb_base->loadOrderEmailHTML($args['email']->id, '');
		$html = do_shortcode($html);
	}
}

?>

<?php echo $html; ?>