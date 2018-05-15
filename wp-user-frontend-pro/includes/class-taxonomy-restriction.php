<?php

/**
 * Taxonomy Restriction Class
 *
 * @since 2.7
 *
 * @package WP User Frontend
 */

class WPUF_Taxonomy_Restriction {

    private static $_instance;

    function __construct() {

        // add a tab to add subscription form
        add_action( 'wpuf_admin_subs_nav_tab', array( $this, 'nav_tab_func' ), 9, 1 );
        add_action( 'wpuf_admin_subs_nav_content', array( $this, 'nav_tab_content_func' ), 9, 1);
        add_action( 'save_post', array( $this,'save_func_meta' ) );

        add_action( 'admin_print_styles-post-new.php', array( $this, 'enqueue' ) );
        add_action( 'admin_print_styles-post.php', array( $this, 'enqueue' ) );

        add_filter( 'wpuf_taxonomy_checklist_args', array( $this, 'get_allowed_term_metas' ) );

    }

    public static function init() {
        if ( !self::$_instance ) {
            self::$_instance = new self();
        }

        return self::$_instance;

    }

    public function nav_tab_func() {
        echo '<li><a href="#taxonomy-restriction"><span class="dashicons dashicons-image-filter"></span> ' . __( 'Taxonomy Restriction', 'wpuf-pro' ) . '</a></li>';
    }

    public function nav_tab_content_func() {
        global $pagenow;

        $allowed_tax_id_arr = array();
        $allowed_tax_id_arr = get_post_meta( get_the_ID() , '_sub_allowed_term_ids', true );
        if ( ! $allowed_tax_id_arr ) {
            $allowed_tax_id_arr = array();
        }
        $allowed_tax_ids    = $allowed_tax_id_arr ? implode( ', ', $allowed_tax_id_arr ) : '';
        ?>
        <section id="taxonomy-restriction">
            <table class='form-table' method='post'>
            <tr><?php _e( 'Choose the taxonomy terms you want to enable for this pack:', 'wpuf' ); ?></tr>
                <tr>
                    <td>
                        <?php
                        $cts = get_taxonomies(array('_builtin'=>true), 'objects'); ?>
                        <?php foreach ($cts as $ct) {
                            if ( is_taxonomy_hierarchical( $ct->name ) ) { ?>
                            <div class="metabox-holder" style="float:left; padding:5px;">
                                <div class="postbox">
                                    <h3 class="handle"><span><?php  echo  $ct->label; ?></span></h3>
                                    <div class="inside" style="padding:0 10px;">
                                        <div class="taxonomydiv">
                                            <div class="tabs-panel" style="height: 200px; overflow-y:auto">
                                                <?php
                                                $tax_terms = get_terms ( array(
                                                    'taxonomy' => $ct->name,
                                                    'hide_empty' => false,
                                                ) );
                                                foreach ($tax_terms as $tax_term) {
                                                    $selected[] = $tax_term;
                                                ?>
                                                <ul class="categorychecklist form-no-clear">
                                                    <input type="checkbox" class="tax-term-class" name="allowed-term[]" value="<?php echo $tax_term->term_id; ?>" <?php echo in_array( $tax_term->term_id, $allowed_tax_id_arr ) ? ' checked="checked"' : ''; ?> name="<?php echo $tax_term->name; ?>"> <?php echo $tax_term->name; ?>
                                                </ul>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <p style="padding-left:10px;">
                                            <strong><?php echo count( $selected ); ?></strong> <?php echo ( count( $selected ) > 1 || count( $selected ) == 0 ) ? 'categories' : 'category'; ?> total
                                            <span class="list-controls" style="float:right; margin-top: 0;">
                                                <input type="checkbox" class="select-all" > Select All
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <?php }
                        } ?>
                    </td>

                    <?php
                    $cts = get_taxonomies(array('_builtin'=>false), 'objects'); ?>
                    <?php foreach ($cts as $ct) {
                        if ( is_taxonomy_hierarchical( $ct->name ) ) {
                            $selected = array();
                            ?>
                        <td>
                            <div class="metabox-holder" style="float:left; padding:5px;">
                                <div class="postbox">
                                    <h3 class="handle"><span><?php  echo  $ct->label; ?></span></h3>
                                    <div class="inside" style="padding:0 10px;">
                                        <div class="taxonomydiv">
                                            <div class="tabs-panel" style="height: 200px; overflow-y:auto">
                                                <?php
                                                $tax_terms = get_terms ( array(
                                                    'taxonomy' => $ct->name,
                                                    'hide_empty' => false,
                                                ) );
                                                foreach ($tax_terms as $tax_term) {
                                                    $selected[] = $tax_term;
                                                    ?>
                                                <ul class="categorychecklist form-no-clear">
                                                    <input type="checkbox" class="tax-term-class" name="allowed-term[]" value="<?php echo $tax_term->term_id; ?>" <?php echo in_array( $tax_term->term_id, $allowed_tax_id_arr ) ? ' checked="checked"' : ''; ?> name="<?php echo $tax_term->name; ?>"> <?php echo $tax_term->name; ?>
                                                </ul>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <p style="padding-left:10px;">
                                            <strong><?php echo count( $selected ); ?></strong> <?php echo ( count( $selected ) > 1 || count( $selected ) == 0 ) ? 'categories' : 'category'; ?> total
                                            <span class="list-controls" style="float:right; margin-top: 0;">
                                                <input type="checkbox" class="select-all" > Select All
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <?php }
                    } ?>
                </tr>
            </table>
        </section>

    <?php
    }

    /**
     * Save allowed term metas to subscription pack post meta
     *
     * @return integer
     */
    public function save_func_meta() {

        $c_user = get_current_user_id();
        if ( isset( $_POST['allowed-term'] ) ) {
            update_post_meta( get_the_ID(), '_sub_allowed_term_ids', $_POST['allowed-term'] );
        } else {
            update_post_meta( get_the_ID(), '_sub_allowed_term_ids', '' );
        }
    }

    /**
     * Hook to get allowed term metas
     *
     * @return integer
     */
    public function get_allowed_term_metas( $tax_args ) {
        $current_user       = get_current_user_id();
        $pack               = get_user_meta( $current_user , '_wpuf_subscription_pack', true );

        if ( $pack ) {
            $allowed_tax_id_arr = get_post_meta( $pack['pack_id'] , '_sub_allowed_term_ids', true );

            if ( !empty( $allowed_tax_id_arr ) ) {
                $allowed_tax_ids    = implode( ', ', $allowed_tax_id_arr );

                $tax_args['include'] = $allowed_tax_ids;
            }
        }

        return $tax_args;
    }

    public function enqueue() {
        wp_enqueue_script(  'taxonomy-restriction-box', WPUF_PRO_ASSET_URI . '/js/taxonomy-restriction.js'  );
    }

}