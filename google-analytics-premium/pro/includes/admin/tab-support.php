<?php
/**
 * Support Tab.
 *
 * @since 6.1.0
 *
 * @package MonsterInsights
 * @subpackage Settings
 * @author  Chris Christoff
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 * Callback for displaying the UI for support settings tab.
 *
 * @since 6.1.0
 * @access public
 *
 * @return void
 */
function monsterinsights_settings_support_tab() {
    ?>
    <div id="monsterinsights-settings-support">
        <?php 
        // Output any notices now
        do_action( 'monsterinsights_settings_support_tab_notice' );
        ?>
        <!-- Support Form -->
        <table class="form-table">
            <iframe src="//www.monsterinsights.com/gfembed/?f=1&title=test" width="100%" height="500" frameBorder="0" class="gfiframe"></iframe>
            <script src="//www.monsterinsights.com/wp-content/plugins/gravity-forms-iframe-develop/assets/scripts/gfembed.min.js" type="text/javascript"></script>
        </table>
        <!-- <hr /> -->
    </div>
    <?php
}
//add_action( 'monsterinsights_tab_settings_support', 'monsterinsights_settings_support_tab' );

/**
 * Callback for saving the general support tab.
 *
 * @since 6.1.0
 * @access public
 *
 * @return void
 */
function monsterinsights_settings_save_support() {
    add_action( 'monsterinsights_settings_support_tab_notice', 'monsterinsights_support_email_sent' );
}
//add_action( 'monsterinsights_settings_save_support', 'monsterinsights_settings_save_support' );

/**
 * Outputs a WordPress style notification to tell the support ticket was sent.
 *
 * @since 6.1.0
 * @access public
 *
 * @return void
 */
function monsterinsights_support_email_sent() {
    
    ?>
    <div class="notice updated below-h2">
        <p><strong><?php esc_html_e( 'Support ticket sent successfully.', 'ga-premium' ); ?></strong></p>
    </div>
    <?php     
}