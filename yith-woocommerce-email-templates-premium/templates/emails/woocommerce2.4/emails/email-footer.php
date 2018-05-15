<?php
/**
 * Email Footer
 *
 * @author        WooThemes
 * @package       WooCommerce/Templates/Emails
 * @version       2.0.0
 */

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Load Template
global $current_email;
$template = yith_wcet_get_email_template( $current_email );

$meta              = get_post_meta( $template, '_template_meta', true );
$socials_on_footer = ( isset( $meta[ 'socials_on_footer' ] ) ) ? $meta[ 'socials_on_footer' ] : 0;

$socials_color = ( isset( $meta[ 'socials_color' ] ) ) ? '-' . $meta[ 'socials_color' ] : '-black';

$footer_logo_url = ( isset( $meta[ 'footer_logo_url' ] ) ) ? $meta[ 'footer_logo_url' ] : '';
$footer_text     = ( isset( $meta[ 'footer_text' ] ) ) ? $meta[ 'footer_text' ] : '';

$use_mini_social_icons = get_option( 'yith-wcet-use-mini-social-icons', 'no' ) == 'yes';
$social_icon_path      = YITH_WCET_ASSETS_URL . '/images/socials-icons';
$social_icon_path .= $use_mini_social_icons ? '-mini/' : '/';

$social_icons = array(
    'facebook'  => get_option( 'yith-wcet-facebook' ) != '' ? 'http://' . str_replace( 'http://', '', get_option( 'yith-wcet-facebook' ) ) : '',
    'twitter'   => get_option( 'yith-wcet-twitter' ) != '' ? 'http://' . str_replace( 'http://', '', get_option( 'yith-wcet-twitter' ) ) : '',
    'google'    => get_option( 'yith-wcet-google' ) != '' ? 'http://' . str_replace( 'http://', '', get_option( 'yith-wcet-google' ) ) : '',
    'linkedin'  => get_option( 'yith-wcet-linkedin' ) != '' ? 'http://' . str_replace( 'http://', '', get_option( 'yith-wcet-linkedin' ) ) : '',
    'instagram' => get_option( 'yith-wcet-instagram' ) != '' ? 'http://' . str_replace( 'http://', '', get_option( 'yith-wcet-instagram' ) ) : '',
    'flickr'    => get_option( 'yith-wcet-flickr' ) != '' ? 'http://' . str_replace( 'http://', '', get_option( 'yith-wcet-flickr' ) ) : '',
    'pinterest' => get_option( 'yith-wcet-pinterest' ) != '' ? 'http://' . str_replace( 'http://', '', get_option( 'yith-wcet-pinterest' ) ) : '',
);

$at_least_one_social_setted = false;
foreach ( $social_icons as $social_name => $social_link ) {
    if ( strlen( $social_link ) > 0 ) {
        $at_least_one_social_setted = true;
        break;
    }
}
?>
</div>
</td>
</tr>
</table>
<!-- End Content -->
</td>
</tr>
</table>
<!-- End Body -->
</td>
</tr>
<tr>
    <td align="center" valign="top">
        <!-- Footer -->
        <table border="0" cellpadding="10" cellspacing="0" id="template_footer">
            <tr>
                <td valign="top">
                    <table border="0" cellpadding="10" cellspacing="0" width="100%">

                        <?php if ( strlen( $footer_logo_url ) > 0 || strlen( $footer_text ) > 0 ) : ?>
                            <tr>
                                <td>
                                    <?php if ( strlen( $footer_logo_url ) > 0 ) : ?>
                                        <img height="70px" src=" <?php echo esc_url( $footer_logo_url ) ?>" alt=" <?php echo get_bloginfo( 'name', 'display' ) ?>"/>
                                    <?php endif; ?>
                                </td>
                                <td colspan="2" valign="middle" id="template_footer_text">
                                    <?php
                                    echo call_user_func( '__', $footer_text, 'yith-woocommerce-email-templates' );
                                    ?>
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php if ( !empty( $args ) ) : ?>
                            <tr>
                                <td colspan="3" valign="middle" id="template_footer_extra_text">
                                    <?php
                                    foreach ( $args as $arg ) {

                                        echo $arg;

                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </table>
                </td>
            </tr>
        </table>
        <!-- End Footer -->
    </td>
</tr>
<?php if ( $socials_on_footer && $at_least_one_social_setted ) : ?>
    <tr>
        <td id="template_footer_social" align="center" valign="middle">

            <table border="0" cellpadding="0" cellspacing="5px">
                <tr>
                    <?php foreach ( $social_icons as $social_name => $social_link ) : ?>
                        <?php if ( strlen( $social_link ) > 0 ) { ?>
                            <td width="32px" class="yith-wcet-socials-icons" style="text-align:center;">
                                <a href="<?php echo $social_link ?>"><img width="20px"
                                                                          src="<?php echo $social_icon_path . $social_name . $socials_color ?>.png"></a>
                            </td>
                        <?php } ?>
                    <?php endforeach; ?>
                </tr>
            </table>
        </td>
    </tr>
<?php endif; ?>
<tr>
    <td id="template_footer_wc_credits" align="center" valign="middle">
        <?php echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ); ?>
    </td>
</tr>
</table>
</td>
</tr>
</table>
</div>
</body>
</html>
