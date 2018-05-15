<?php
if ( !defined( 'YITH_WCBEP' ) ) {
    exit;
} // Exit if accessed directly

$custom_fields         = YITH_WCBEP_Custom_Fields_Manager::get_custom_fields();
$has_custom_fields_set = !empty( $custom_fields );

?>

<!-- - - - - - - - - - - - - -   B   U   L   K        E   D   I   T   O   R   - - - - - - - - - - - - - -->

<div id="yith-wcbep-bulk-editor">
    <div id="yith-wcbep-bulk-editor-container">
        <span class="dashicons dashicons-no yith-wcbep-close-bulk-editor"></span>
        <h2><?php _e( 'Bulk editing', 'yith-woocommerce-bulk-product-editing' ) ?></h2>
        <ul>
            <li><a href="#yith-wcbep-bulk-general"><?php _e( 'General', 'yith-woocommerce-bulk-product-editing' ) ?></a></li>
            <li><a href="#yith-wcbep-bulk-attr"><?php _e( 'Categories, Tags, Attributes', 'yith-woocommerce-bulk-product-editing' ) ?></a></li>
            <li><a href="#yith-wcbep-bulk-pricing"><?php _e( 'Pricing', 'yith-woocommerce-bulk-product-editing' ) ?></a></li>
            <li><a href="#yith-wcbep-bulk-shipping"><?php _e( 'Shipping', 'yith-woocommerce-bulk-product-editing' ) ?></a></li>
            <li><a href="#yith-wcbep-bulk-stock"><?php _e( 'Stock', 'yith-woocommerce-bulk-product-editing' ) ?></a></li>
            <li><a href="#yith-wcbep-bulk-type"><?php _e( 'Type', 'yith-woocommerce-bulk-product-editing' ) ?></a></li>
            <?php if ( $has_custom_fields_set ) : ?>
                <li><a href="#yith-wcbep-bulk-custom-fields"><?php _e( 'Custom Fields', 'yith-woocommerce-bulk-product-editing' ) ?></a></li>
            <?php endif ?>
        </ul>

        <div id="yith-wcbep-bulk-general">
            <table class="yith-wcbep-bulk-editor-table">
                <tr>
                    <td class="yith-wcbep-bulk-form-label-col">
                        <label><?php _e( 'Title', 'woocommerce' ) ?></label>
                    </td>
                    <td class="yith-wcbep-bulk-form-content-col">
                        <select id="yith-wcbep-title-bulk-select" name="yith-wcbep-title-bulk-select" class="yith-wcbep-miniselect is_resetable">
                            <option value="new"><?php _e( 'Set new', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="pre"><?php _e( 'Prepend', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="app"><?php _e( 'Append', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="rep"><?php _e( 'Replace', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                        </select>
                        <input type="text" id="yith-wcbep-title-bulk-value" name="yith-wcbep-title-bulk-value" class="yith-wcbep-minifield is_resetable">
                        <input type="text" id="yith-wcbep-title-bulk-replace" name="yith-wcbep-title-bulk-replace"
                               class="yith_wcbep_no_display yith-wcbep-minifield is_resetable" placeholder="With">
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcbep-bulk-form-label-col">
                        <label><?php _e( 'Slug', 'woocommerce' ) ?></label>
                    </td>
                    <td class="yith-wcbep-bulk-form-content-col">
                        <select id="yith-wcbep-slug-bulk-select" name="yith-wcbep-slug-bulk-select" class="yith-wcbep-miniselect is_resetable">
                            <option value="new"><?php _e( 'Set new', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="pre"><?php _e( 'Prepend', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="app"><?php _e( 'Append', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="rep"><?php _e( 'Replace', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                        </select>
                        <input type="text" id="yith-wcbep-slug-bulk-value" name="yith-wcbep-slug-bulk-value" class="yith-wcbep-minifield is_resetable">
                        <input type="text" id="yith-wcbep-slug-bulk-replace" name="yith-wcbep-slug-bulk-replace"
                               class="yith_wcbep_no_display yith-wcbep-minifield is_resetable" placeholder="With">
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcbep-bulk-form-label-col">
                        <label><?php _e( 'SKU', 'woocommerce' ) ?></label>
                    </td>
                    <td class="yith-wcbep-bulk-form-content-col">
                        <select id="yith-wcbep-sku-bulk-select" name="yith-wcbep-sku-bulk-select" class="yith-wcbep-miniselect is_resetable">
                            <option value="new"><?php _e( 'Set new', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="pre"><?php _e( 'Prepend', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="app"><?php _e( 'Append', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="rep"><?php _e( 'Replace', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                        </select>
                        <input type="text" id="yith-wcbep-sku-bulk-value" name="yith-wcbep-sku-bulk-value" class="yith-wcbep-minifield is_resetable">
                        <input type="text" id="yith-wcbep-sku-bulk-replace" name="yith-wcbep-sku-bulk-replace"
                               class="yith_wcbep_no_display yith-wcbep-minifield is_resetable" placeholder="With">
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcbep-bulk-form-label-col">
                        <label><?php _e( 'Description', 'woocommerce' ) ?></label>
                    </td>
                    <td class="yith-wcbep-bulk-form-content-col">
                        <select id="yith-wcbep-description-bulk-select" name="yith-wcbep-description-bulk-select" class="yith-wcbep-miniselect is_resetable">
                            <option value="new"><?php _e( 'Set new', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="pre"><?php _e( 'Prepend', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="app"><?php _e( 'Append', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="rep"><?php _e( 'Replace', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                        </select>
                        <textarea type="text" id="yith-wcbep-description-bulk-value" name="yith-wcbep-description-bulk-value"
                                  class="yith-wcbep-minifield is_resetable"></textarea>
                        <textarea type="text" id="yith-wcbep-description-bulk-replace" name="yith-wcbep-description-bulk-replace"
                                  class="yith_wcbep_no_display yith-wcbep-minifield is_resetable"
                                  placeholder="With"></textarea>
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcbep-bulk-form-label-col">
                        <label><?php _e( 'Short Description', 'yith-woocommerce-bulk-product-editing' ) ?></label>
                    </td>
                    <td class="yith-wcbep-bulk-form-content-col">
                        <select id="yith-wcbep-shortdesc-bulk-select" name="yith-wcbep-shortdesc-bulk-select" class="yith-wcbep-miniselect is_resetable">
                            <option value="new"><?php _e( 'Set new', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="pre"><?php _e( 'Prepend', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="app"><?php _e( 'Append', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="rep"><?php _e( 'Replace', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                        </select>
                        <textarea type="text" id="yith-wcbep-shortdesc-bulk-value" name="yith-wcbep-shortdesc-bulk-value"
                                  class="yith-wcbep-minifield is_resetable"></textarea>
                        <textarea type="text" id="yith-wcbep-shortdesc-bulk-replace" name="yith-wcbep-shortdesc-bulk-replace"
                                  class="yith_wcbep_no_display yith-wcbep-minifield is_resetable"
                                  placeholder="With"></textarea>
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcbep-bulk-form-label-col">
                        <label><?php _e( 'Purchase Note', 'woocommerce' ) ?></label>
                    </td>
                    <td class="yith-wcbep-bulk-form-content-col">
                        <select id="yith-wcbep-purchase_note-bulk-select" name="yith-wcbep-purchase_note-bulk-select" class="yith-wcbep-miniselect is_resetable">
                            <option value="new"><?php _e( 'Set new', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="pre"><?php _e( 'Prepend', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="app"><?php _e( 'Append', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="rep"><?php _e( 'Replace', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                        </select>
                        <textarea type="text" id="yith-wcbep-purchase_note-bulk-value" name="yith-wcbep-purchase_note-bulk-value"
                                  class="yith-wcbep-minifield is_resetable"></textarea>
                        <textarea type="text" id="yith-wcbep-purchase_note-bulk-replace" name="yith-wcbep-purchase_note-bulk-replace"
                                  class="yith_wcbep_no_display yith-wcbep-minifield is_resetable"
                                  placeholder="With"></textarea>
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcbep-bulk-form-label-col">
                        <label><?php _e( 'Menu order', 'woocommerce' ) ?></label>
                    </td>
                    <td class="yith-wcbep-bulk-form-content-col">
                        <select id="yith-wcbep-menu_order-bulk-select" name="yith-wcbep-menu_order-bulk-select" class="yith-wcbep-miniselect is_resetable">
                            <option value="new"><?php _e( 'Set new', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="inc"><?php _e( 'Increase by value', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="dec"><?php _e( 'Decrease by value', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="del"><?php _e( 'Delete', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                        </select>
                        <input type="text" id="yith-wcbep-menu_order-bulk-value" name="yith-wcbep-menu_order-bulk-value" class="yith-wcbep-minifield is_resetable">
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcbep-bulk-form-label-col">
                        <label><?php _e( 'Sold Individually', 'woocommerce' ) ?></label>
                    </td>
                    <td class="yith-wcbep-bulk-form-content-col">
                        <select id="yith-wcbep-sold_individually-bulk-select" name="yith-wcbep-sold_individually-bulk-select" class="yith-wcbep-miniselect is_resetable">
                            <option value="skip"></option>
                            <option value="yes"><?php _e( 'Yes', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="no"><?php _e( 'No', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcbep-bulk-form-label-col">
                        <label><?php _e( 'Enable Reviews', 'yith-woocommerce-bulk-product-editing' ) ?></label>
                    </td>
                    <td class="yith-wcbep-bulk-form-content-col">
                        <select id="yith-wcbep-enable_reviews-bulk-select" name="yith-wcbep-enable_reviews-bulk-select" class="yith-wcbep-miniselect is_resetable">
                            <option value="skip"></option>
                            <option value="yes"><?php _e( 'Yes', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="no"><?php _e( 'No', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcbep-bulk-form-label-col">
                        <label><?php _e( 'Status', 'woocommerce' ) ?></label>
                    </td>
                    <td class="yith-wcbep-bulk-form-content-col">
                        <select id="yith-wcbep-status-bulk-select" name="yith-wcbep-status-bulk-select" class="yith-wcbep-miniselect is_resetable">
                            <option value="skip"></option>
                            <?php
                            $statuses = get_post_statuses();
                            foreach ( $statuses as $key => $value ) {
                                ?>
                                <option value="<?php echo $key ?>"><?php echo $value ?></option> <?php
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcbep-bulk-form-label-col">
                        <label><?php _e( 'Catalog visibility', 'yith-woocommerce-bulk-product-editing' ) ?></label>
                    </td>
                    <td class="yith-wcbep-bulk-form-content-col">
                        <select id="yith-wcbep-visibility-bulk-select" name="yith-wcbep-visibility-bulk-select" class="yith-wcbep-miniselect is_resetable">
                            <option value="skip"></option>
                            <?php
                            $visibility_options = apply_filters( 'woocommerce_product_visibility_options', array(
                                'visible' => __( 'Catalog/search', 'woocommerce' ),
                                'catalog' => __( 'Catalog', 'woocommerce' ),
                                'search'  => __( 'Search', 'woocommerce' ),
                                'hidden'  => __( 'Hidden', 'woocommerce' ),
                            ) );
                            foreach ( $visibility_options as $key => $value ) {
                                ?>
                                <option value="<?php echo $key ?>"><?php echo $value ?></option> <?php
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcbep-bulk-form-label-col">
                        <label><?php _e( 'Date', 'yith-woocommerce-bulk-product-editing' ) ?></label>
                    </td>
                    <td class="yith-wcbep-bulk-form-content-col">
                        <input type="hidden" id="yith-wcbep-date-bulk-select" name="yith-wcbep-date-bulk-select" value="new"/>
                        <input type="text" id="yith-wcbep-date-bulk-value" name="yith-wcbep-date-bulk-value" class="yith-wcbep-datepicker yith-wcbep-minidate is_resetable">
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcbep-bulk-form-label-col">
                        <label><?php _e( 'Image', 'yith-woocommerce-bulk-product-editing' ) ?></label>
                    </td>
                    <td class="yith-wcbep-bulk-form-content-col">
                        <select id="yith-wcbep-image-bulk-select" name="yith-wcbep-image-bulk-select" class="yith-wcbep-miniselect is_resetable">
                            <option value="new"><?php _e( 'Set new', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="del"><?php _e( 'Delete', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                        </select>
                        <input type="hidden" id="yith-wcbep-image-bulk-value" name="yith-wcbep-image-bulk-value" class="is_resetable">
                        <input type="hidden" id="yith-wcbep-image-bulk-src" name="yith-wcbep-image-bulk-src" class="is_resetable">
                        <input type="button" id="yith-wcbep-image-bulk-choose-image" name="yith-wcbep-image-bulk-choose-image" class="button" value="<?php _e( 'Choose Image' ); ?>"/>
                        <span id="yith-wcbep-image-bulk-preview"></span>
                    </td>
                </tr>
                <?php do_action( 'yith_wcbep_extra_general_bulk_fields' ); ?>
            </table>
        </div>

        <div id="yith-wcbep-bulk-attr">
            <table class="yith-wcbep-bulk-editor-table">
                <tr>
                    <td class="yith-wcbep-bulk-form-label-col">
                        <label><?php _e( 'Categories', 'woocommerce' ) ?></label>
                    </td>
                    <td class="yith-wcbep-bulk-form-content-col">
                        <select id="yith-wcbep-categories-bulk-select" name="yith-wcbep-categories-bulk-select" class="yith-wcbep-miniselect is_resetable">
                            <option value="add"><?php _e( 'Add', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="rem"><?php _e( 'Remove', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="new"><?php _e( 'Set new', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                        </select>
                        <?php
                        $cat_args   = array(
                            'hide_empty' => false,
                            'orderby'    => 'name',
                            'order'      => 'ASC',
                        );
                        $categories = get_terms( 'product_cat', $cat_args );
                        if ( !empty( $categories ) ) {
                            ?>
                            <div class="yith-wcbep-bulk-chosen-wrapper">
                                <select id="yith-wcbep-categories-bulk-chosen" class="chosen yith-wcbep-chosen yith-wcbep-miniselect is_resetable" multiple
                                        xmlns="http://www.w3.org/1999/html">
                                    <?php
                                    foreach ( $categories as $c ) {
                                        ?>
                                        <option value="<?php echo $c->term_id; ?>"><?php echo $c->name; ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </div>
                            <?php
                        } ?>
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcbep-bulk-form-label-col">
                        <label><?php _e( 'Tags', 'woocommerce' ) ?></label>
                    </td>
                    <td class="yith-wcbep-bulk-form-content-col">
                        <select id="yith-wcbep-tags-bulk-select" name="yith-wcbep-tags-bulk-select" class="yith-wcbep-miniselect is_resetable">
                            <option value="new"><?php _e( 'Set new', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="pre"><?php _e( 'Prepend', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="app"><?php _e( 'Append', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="rep"><?php _e( 'Replace', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="del"><?php _e( 'Delete all', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                        </select>
                        <input type="text" id="yith-wcbep-tags-bulk-value" name="yith-wcbep-tags-bulk-value" class="yith-wcbep-minifield is_resetable">
                        <input type="text" id="yith-wcbep-tags-bulk-replace" name="yith-wcbep-tags-bulk-replace"
                               class="yith_wcbep_no_display yith-wcbep-minifield is_resetable" placeholder="With">
                    </td>
                </tr>
                <?php
                // A T T R I B U T E S
                $attribute_taxonomies = wc_get_attribute_taxonomies();
                //echo '<pre>'; var_dump($attribute_taxonomies); echo '</pre>';
                if ( $attribute_taxonomies ) {
                    foreach ( $attribute_taxonomies as $tax ) {
                        $attribute_taxonomy_name = wc_attribute_taxonomy_name( $tax->attribute_name );
                        $attr_label              = $tax->attribute_label;
                        $terms                   = get_terms( $attribute_taxonomy_name, array( 'hide_empty' => '0' ) );
                        if ( count( $terms ) > 0 ) {
                            $visible_row_id    = "yith-wcbep-bulk-form-attributes-visible-row-$attribute_taxonomy_name";
                            $variation_row_id  = "yith-wcbep-bulk-form-attributes-used-for-variation-row-$attribute_taxonomy_name";
                            $toggle_rows_class = "yith-wcbep-bulk-form-attributes-options-row-$attribute_taxonomy_name";
                            ?>
                            <tr>
                                <td class="yith-wcbep-bulk-form-label-col" style="position: relative">
                                    <label><?php echo $attr_label; ?></label>
                                    <span class="yith-wcbep-bulk-form-attributes-toggle-options dashicons dashicons-arrow-down-alt2 yith-wcbep-toggle closed"
                                          data-target=".<?php echo $toggle_rows_class ?>"></span>
                                </td>
                                <td class="yith-wcbep-bulk-form-content-col">
                                    <select class="yith-wcbep-attributes-bulk-select yith-wcbep-miniselect is_resetable">
                                        <option value="add"><?php _e( 'Add', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                                        <option value="rem"><?php _e( 'Remove', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                                        <option value="new"><?php _e( 'Set new', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                                    </select>

                                    <div class="yith-wcbep-bulk-chosen-wrapper">
                                        <select id="yith-wcbep-attr-bulk-<?php echo $attribute_taxonomy_name; ?>"
                                                data-taxonomy-name="<?php echo $attribute_taxonomy_name; ?>"
                                                name="yith-wcbep-attr-bulk-<?php echo $attribute_taxonomy_name; ?>[]"
                                                class="chosen is_resetable yith-wcbep-attributes-bulk-chosen" multiple
                                                xmlns="http://www.w3.org/1999/html">
                                            <?php
                                            foreach ( $terms as $t ) {
                                                ?>
                                                <option value="<?php echo $t->term_id; ?>"><?php echo $t->name; ?></option>
                                                <?php
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </td>
                            </tr>
                            <tr id="<?php echo $visible_row_id; ?>" class="yith-wcbep-bulk-form-attributes-visible-row <?php echo $toggle_rows_class ?>">
                                <td class="yith-wcbep-bulk-form-label-col">
                                    <label><?php echo $attr_label; ?> - <?php _e( 'is visible', 'yith-woocommerce-bulk-product-editing' ) ?></label>
                                </td>
                                <td class="yith-wcbep-bulk-form-content-col">
                                    <select class="yith-wcbep-attributes-visible-bulk-select yith-wcbep-miniselect is_resetable"
                                            data-taxonomy-name="<?php echo $attribute_taxonomy_name; ?>">
                                        <option value=""></option>
                                        <option value="yes"><?php _e( 'Yes', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                                        <option value="no"><?php _e( 'No', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr id="<?php echo $variation_row_id; ?>" class="yith-wcbep-bulk-form-attributes-used-for-variation-row <?php echo $toggle_rows_class ?>">
                                <td class="yith-wcbep-bulk-form-label-col">
                                    <label><?php echo $attr_label; ?> - <?php _e( 'used for variations', 'yith-woocommerce-bulk-product-editing' ) ?></label>
                                </td>
                                <td class="yith-wcbep-bulk-form-content-col">
                                    <select class="yith-wcbep-attributes-used-for-variation-bulk-select yith-wcbep-miniselect is_resetable"
                                            data-taxonomy-name="<?php echo $attribute_taxonomy_name; ?>">
                                        <option value=""></option>
                                        <option value="yes"><?php _e( 'Yes', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                                        <option value="no"><?php _e( 'No', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                                    </select>
                                </td>
                            </tr>
                            <?php
                        }
                    }
                }
                ?>
                <?php do_action( 'yith_wcbep_extra_attr_bulk_fields' ); ?>
            </table>
        </div>

        <div id="yith-wcbep-bulk-pricing">
            <table class="yith-wcbep-bulk-editor-table">
                <tr>
                    <td class="yith-wcbep-bulk-form-label-col">
                        <label><?php _e( 'Regular Price', 'woocommerce' ) ?></label>
                    </td>
                    <td class="yith-wcbep-bulk-form-content-col">
                        <select id="yith-wcbep-regular_price-bulk-select" name="yith-wcbep-regular_price-bulk-select" class="yith-wcbep-miniselect is_resetable">
                            <option value="new"><?php _e( 'Set new', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="inc"><?php _e( 'Increase by value', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="dec"><?php _e( 'Decrease by value', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="incp"><?php _e( 'Increase by %', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="decp"><?php _e( 'Decrease by %', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                        </select>
                        <input type="text" id="yith-wcbep-regular_price-bulk-value" name="yith-wcbep-regular_price-bulk-value" class="yith-wcbep-minifield is_resetable">
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcbep-bulk-form-label-col">
                        <label><?php _e( 'Sale Price', 'woocommerce' ) ?></label>
                    </td>
                    <td class="yith-wcbep-bulk-form-content-col">
                        <select id="yith-wcbep-sale_price-bulk-select" name="yith-wcbep-sale_price-bulk-select" class="yith-wcbep-miniselect is_resetable">
                            <option value="new"><?php _e( 'Set new', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="inc"><?php _e( 'Increase by value', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="dec"><?php _e( 'Decrease by value', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="incp"><?php _e( 'Increase by %', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="decp"><?php _e( 'Decrease by %', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="decfr"><?php _e( 'Decrease by value from regular', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="decpfr"><?php _e( 'Decrease by % from regular', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="del"><?php _e( 'Delete', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                        </select>
                        <input type="text" id="yith-wcbep-sale_price-bulk-value" name="yith-wcbep-sale_price-bulk-value" class="yith-wcbep-minifield is_resetable">
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcbep-bulk-form-label-col">
                        <label><?php _e( 'Sale Price From', 'yith-woocommerce-bulk-product-editing' ) ?></label>
                    </td>
                    <td class="yith-wcbep-bulk-form-content-col">
                        <select id="yith-wcbep-sale_price_from-bulk-select" name="yith-wcbep-sale_price_from-bulk-select" class="yith-wcbep-miniselect is_resetable">
                            <option value="new"><?php _e( 'Set new', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="del"><?php _e( 'Delete', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                        </select>
                        <input type="text" id="yith-wcbep-sale_price_from-bulk-value" name="yith-wcbep-sale_price_from-bulk-value"
                               class="yith-wcbep-datepicker yith-wcbep-minifield is_resetable">
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcbep-bulk-form-label-col">
                        <label><?php _e( 'Sale Price To', 'yith-woocommerce-bulk-product-editing' ) ?></label>
                    </td>
                    <td class="yith-wcbep-bulk-form-content-col">
                        <select id="yith-wcbep-sale_price_to-bulk-select" name="yith-wcbep-sale_price_to-bulk-select" class="yith-wcbep-miniselect is_resetable">
                            <option value="new"><?php _e( 'Set new', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="del"><?php _e( 'Delete', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                        </select>
                        <input type="text" id="yith-wcbep-sale_price_to-bulk-value" name="yith-wcbep-sale_price_to-bulk-value"
                               class="yith-wcbep-datepicker yith-wcbep-minifield is_resetable">
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcbep-bulk-form-label-col">
                        <label><?php _e( 'Tax Status', 'woocommerce' ) ?></label>
                    </td>
                    <td class="yith-wcbep-bulk-form-content-col">
                        <select id="yith-wcbep-tax_status-bulk-select" name="yith-wcbep-tax_status-bulk-select" class="yith-wcbep-miniselect is_resetable">
                            <option value="skip"></option>
                            <option value="taxable"><?php _e( 'Taxable', 'woocommerce' ) ?></option>
                            <option value="shipping"><?php _e( 'Shipping only', 'woocommerce' ) ?></option>
                            <option value="none"><?php _ex( 'None', 'Tax status', 'woocommerce' ) ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcbep-bulk-form-label-col">
                        <label><?php _e( 'Tax Class', 'woocommerce' ) ?></label>
                    </td>
                    <td class="yith-wcbep-bulk-form-content-col">
                        <select id="yith-wcbep-tax_class-bulk-select" name="yith-wcbep-tax_class-bulk-select" class="yith-wcbep-miniselect is_resetable">
                            <option value="skip"></option>
                            <?php
                            // TAX CLASSES
                            $tax_classes           = WC_Tax::get_tax_classes();
                            $classes_options       = array();
                            $classes_options[ '' ] = __( 'Standard', 'woocommerce' );
                            if ( $tax_classes ) {
                                foreach ( $tax_classes as $class ) {
                                    $classes_options[ sanitize_title( $class ) ] = esc_html( $class );
                                }
                            }
                            foreach ( $classes_options as $key => $value ) {
                                echo '<option value="' . $key . '">' . $value . '</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
            </table>
        </div>

        <div id="yith-wcbep-bulk-shipping">
            <table class="yith-wcbep-bulk-editor-table">
                <tr>
                    <td class="yith-wcbep-bulk-form-label-col">
                        <label><?php _e( 'Shipping class', 'woocommerce' ) ?></label>
                    </td>
                    <td class="yith-wcbep-bulk-form-content-col">
                        <select id="yith-wcbep-shipping_class-bulk-select" name="yith-wcbep-shipping_class-bulk-select" class="yith-wcbep-miniselect is_resetable">
                            <option value="skip"></option>
                            <option value="-1"><?php _e( 'No shipping class', 'woocommerce' ) ?></option>
                            <?php
                            $sc_args          = array(
                                'hide_empty' => false,
                                'orderby'    => 'name',
                                'order'      => 'ASC',
                            );
                            $shipping_classes = get_terms( 'product_shipping_class', $sc_args );
                            if ( !empty( $shipping_classes ) ) {
                                foreach ( $shipping_classes as $s ) {
                                    ?>
                                    <option value="<?php echo $s->term_id; ?>"><?php echo $s->name; ?></option> <?php
                                }
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcbep-bulk-form-label-col">
                        <label><?php _e( 'Weight', 'woocommerce' ) ?></label>
                    </td>
                    <td class="yith-wcbep-bulk-form-content-col">
                        <select id="yith-wcbep-weight-bulk-select" name="yith-wcbep-weight-bulk-select" class="yith-wcbep-miniselect is_resetable">
                            <option value="new"><?php _e( 'Set new', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="inc"><?php _e( 'Increase by value', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="dec"><?php _e( 'Decrease by value', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="incp"><?php _e( 'Increase by %', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="decp"><?php _e( 'Decrease by %', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="del"><?php _e( 'Delete', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                        </select>
                        <input type="text" id="yith-wcbep-weight-bulk-value" name="yith-wcbep-weight-bulk-value" class="yith-wcbep-minifield is_resetable">
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcbep-bulk-form-label-col">
                        <label><?php _e( 'Height', 'woocommerce' ) ?></label>
                    </td>
                    <td class="yith-wcbep-bulk-form-content-col">
                        <select id="yith-wcbep-height-bulk-select" name="yith-wcbep-height-bulk-select" class="yith-wcbep-miniselect is_resetable">
                            <option value="new"><?php _e( 'Set new', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="inc"><?php _e( 'Increase by value', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="dec"><?php _e( 'Decrease by value', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="incp"><?php _e( 'Increase by %', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="decp"><?php _e( 'Decrease by %', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="del"><?php _e( 'Delete', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                        </select>
                        <input type="text" id="yith-wcbep-height-bulk-value" name="yith-wcbep-height-bulk-value" class="yith-wcbep-minifield is_resetable">
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcbep-bulk-form-label-col">
                        <label><?php _e( 'Width', 'woocommerce' ) ?></label>
                    </td>
                    <td class="yith-wcbep-bulk-form-content-col">
                        <select id="yith-wcbep-width-bulk-select" name="yith-wcbep-width-bulk-select" class="yith-wcbep-miniselect is_resetable">
                            <option value="new"><?php _e( 'Set new', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="inc"><?php _e( 'Increase by value', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="dec"><?php _e( 'Decrease by value', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="incp"><?php _e( 'Increase by %', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="decp"><?php _e( 'Decrease by %', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="del"><?php _e( 'Delete', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                        </select>
                        <input type="text" id="yith-wcbep-width-bulk-value" name="yith-wcbep-width-bulk-value" class="yith-wcbep-minifield is_resetable">
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcbep-bulk-form-label-col">
                        <label><?php _e( 'Length', 'woocommerce' ) ?></label>
                    </td>
                    <td class="yith-wcbep-bulk-form-content-col">
                        <select id="yith-wcbep-length-bulk-select" name="yith-wcbep-length-bulk-select" class="yith-wcbep-miniselect is_resetable">
                            <option value="new"><?php _e( 'Set new', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="inc"><?php _e( 'Increase by value', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="dec"><?php _e( 'Decrease by value', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="incp"><?php _e( 'Increase by %', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="decp"><?php _e( 'Decrease by %', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="del"><?php _e( 'Delete', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                        </select>
                        <input type="text" id="yith-wcbep-length-bulk-value" name="yith-wcbep-length-bulk-value" class="yith-wcbep-minifield is_resetable">
                    </td>
                </tr>
            </table>
        </div>

        <div id="yith-wcbep-bulk-stock">
            <table class="yith-wcbep-bulk-editor-table">
                <tr>
                    <td class="yith-wcbep-bulk-form-label-col">
                        <label><?php _e( 'Manage Stock', 'woocommerce' ) ?></label>
                    </td>
                    <td class="yith-wcbep-bulk-form-content-col">
                        <select id="yith-wcbep-manage_stock-bulk-select" name="yith-wcbep-manage_stock-bulk-select" class="yith-wcbep-miniselect is_resetable">
                            <option value="skip"></option>
                            <option value="yes"><?php _e( 'Yes', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="no"><?php _e( 'No', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcbep-bulk-form-label-col">
                        <label><?php _e( 'Stock status', 'woocommerce' ) ?></label>
                    </td>
                    <td class="yith-wcbep-bulk-form-content-col">
                        <select id="yith-wcbep-stock_status-bulk-select" name="yith-wcbep-stock_status-bulk-select" class="yith-wcbep-miniselect is_resetable">
                            <option value="skip"></option>
                            <option value="yes"><?php _e( 'In stock', 'woocommerce' ) ?></option>
                            <option value="no"><?php _e( 'Out of stock', 'woocommerce' ) ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcbep-bulk-form-label-col">
                        <label><?php _e( 'Stock Qty', 'woocommerce' ) ?></label>
                    </td>
                    <td class="yith-wcbep-bulk-form-content-col">
                        <select id="yith-wcbep-stock_quantity-bulk-select" name="yith-wcbep-stock_quantity-bulk-select" class="yith-wcbep-miniselect is_resetable">
                            <option value="new"><?php _e( 'Set new', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="inc"><?php _e( 'Increase by value', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="dec"><?php _e( 'Decrease by value', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="incp"><?php _e( 'Increase by %', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="decp"><?php _e( 'Decrease by %', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="del"><?php _e( 'Delete', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                        </select>
                        <input type="text" id="yith-wcbep-stock_quantity-bulk-value" name="yith-wcbep-stock_quantity-bulk-value"
                               class="yith-wcbep-minifield is_resetable">
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcbep-bulk-form-label-col">
                        <label><?php _e( 'Allow Backorders?', 'woocommerce' ) ?></label>
                    </td>
                    <td class="yith-wcbep-bulk-form-content-col">
                        <select id="yith-wcbep-allow_backorders-bulk-select" name="yith-wcbep-allow_backorders-bulk-select" class="yith-wcbep-miniselect is_resetable">
                            <option value="skip"></option>
                            <option value="no"><?php _e( 'Do not allow', 'woocommerce' ); ?></option>
                            <option value="notify"><?php _e( 'Allow, but notify customer', 'woocommerce' ) ?></option>
                            <option value="yes"><?php _e( 'Allow', 'woocommerce' ) ?></option>
                        </select>
                    </td>
                </tr>
            </table>
        </div>

        <div id="yith-wcbep-bulk-type">
            <table class="yith-wcbep-bulk-editor-table">
                <tr>
                    <td class="yith-wcbep-bulk-form-label-col">
                        <label><?php _e( 'Product Type', 'woocommerce' ) ?></label>
                    </td>
                    <td class="yith-wcbep-bulk-form-content-col">
                        <select id="yith-wcbep-prod_type-bulk-select" name="yith-wcbep-prod_type-bulk-select" class="yith-wcbep-miniselect is_resetable">
                            <option value="skip"></option>
                            <?php
                            $product_type_selector = apply_filters( 'product_type_selector', array(
                                'simple'   => __( 'Simple product', 'woocommerce' ),
                                'grouped'  => __( 'Grouped product', 'woocommerce' ),
                                'external' => __( 'External/Affiliate product', 'woocommerce' ),
                                'variable' => __( 'Variable product', 'woocommerce' ),
                            ) );
                            foreach ( $product_type_selector as $key => $value ) {
                                ?>
                                <option value="<?php echo $key ?>"><?php echo $value ?></option> <?php
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcbep-bulk-form-label-col">
                        <label><?php _e( 'Featured', 'woocommerce' ) ?></label>
                    </td>
                    <td class="yith-wcbep-bulk-form-content-col">
                        <select id="yith-wcbep-featured-bulk-select" name="yith-wcbep-featured-bulk-select" class="yith-wcbep-miniselect is_resetable">
                            <option value="skip"></option>
                            <option value="yes"><?php _e( 'Yes', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="no"><?php _e( 'No', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcbep-bulk-form-label-col">
                        <label><?php _e( 'Virtual', 'woocommerce' ) ?></label>
                    </td>
                    <td class="yith-wcbep-bulk-form-content-col">
                        <select id="yith-wcbep-virtual-bulk-select" name="yith-wcbep-virtual-bulk-select" class="yith-wcbep-miniselect is_resetable">
                            <option value="skip"></option>
                            <option value="yes"><?php _e( 'Yes', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="no"><?php _e( 'No', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcbep-bulk-form-label-col">
                        <label><?php _e( 'Downloadable', 'woocommerce' ) ?></label>
                    </td>
                    <td class="yith-wcbep-bulk-form-content-col">
                        <select id="yith-wcbep-downloadable-bulk-select" name="yith-wcbep-downloadable-bulk-select" class="yith-wcbep-miniselect is_resetable">
                            <option value="skip"></option>
                            <option value="yes"><?php _e( 'Yes', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="no"><?php _e( 'No', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcbep-bulk-form-label-col">
                        <label><?php _e( 'Download Limit', 'woocommerce' ) ?></label>
                    </td>
                    <td class="yith-wcbep-bulk-form-content-col">
                        <select id="yith-wcbep-download_limit-bulk-select" name="yith-wcbep-download_limit-bulk-select" class="yith-wcbep-miniselect is_resetable">
                            <option value="new"><?php _e( 'Set new', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="inc"><?php _e( 'Increase by value', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="dec"><?php _e( 'Decrease by value', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="incp"><?php _e( 'Increase by %', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="decp"><?php _e( 'Decrease by %', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="del"><?php _e( 'Delete', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                        </select>
                        <input type="text" id="yith-wcbep-download_limit-bulk-value" name="yith-wcbep-download_limit-bulk-value"
                               class="yith-wcbep-minifield is_resetable">
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcbep-bulk-form-label-col">
                        <label><?php _e( 'Download Expiry', 'woocommerce' ) ?></label>
                    </td>
                    <td class="yith-wcbep-bulk-form-content-col">
                        <select id="yith-wcbep-download_expiry-bulk-select" name="yith-wcbep-download_expiry-bulk-select" class="yith-wcbep-miniselect is_resetable">
                            <option value="new"><?php _e( 'Set new', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="inc"><?php _e( 'Increase by value', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="dec"><?php _e( 'Decrease by value', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="incp"><?php _e( 'Increase by %', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="decp"><?php _e( 'Decrease by %', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="del"><?php _e( 'Delete', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                        </select>
                        <input type="text" id="yith-wcbep-download_expiry-bulk-value" name="yith-wcbep-download_expiry-bulk-value"
                               class="yith-wcbep-minifield is_resetable">
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcbep-bulk-form-label-col">
                        <label><?php _e( 'Download Type', 'woocommerce' ) ?></label>
                    </td>
                    <td class="yith-wcbep-bulk-form-content-col">
                        <select id="yith-wcbep-download_type-bulk-select" name="yith-wcbep-download_type-bulk-select" class="yith-wcbep-miniselect is_resetable">
                            <option value="skip"></option>
                            <?php
                            $download_types = array(
                                ''            => __( 'Standard Product', 'woocommerce' ),
                                'application' => __( 'Application/Software', 'woocommerce' ),
                                'music'       => __( 'Music', 'woocommerce' ),
                            );
                            foreach ( $download_types as $key => $value ) {
                                ?>
                                <option value="<?php echo $key ?>"><?php echo $value ?></option> <?php
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcbep-bulk-form-label-col">
                        <label><?php _e( 'Button text', 'woocommerce' ) ?></label>
                    </td>
                    <td class="yith-wcbep-bulk-form-content-col">
                        <select id="yith-wcbep-button_text-bulk-select" name="yith-wcbep-button_text-bulk-select" class="yith-wcbep-miniselect is_resetable">
                            <option value="new"><?php _e( 'Set new', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="pre"><?php _e( 'Prepend', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="app"><?php _e( 'Append', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="rep"><?php _e( 'Replace', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="del"><?php _e( 'Delete', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                        </select>
                        <input type="text" id="yith-wcbep-button_text-bulk-value" name="yith-wcbep-button_text-bulk-value" class="yith-wcbep-minifield is_resetable">
                        <input type="text" id="yith-wcbep-button_text-bulk-replace" name="yith-wcbep-button_text-bulk-replace"
                               class="yith_wcbep_no_display yith-wcbep-minifield is_resetable"
                               placeholder="With">
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcbep-bulk-form-label-col">
                        <label><?php _e( 'Product URL', 'woocommerce' ) ?></label>
                    </td>
                    <td class="yith-wcbep-bulk-form-content-col">
                        <select id="yith-wcbep-product_url-bulk-select" name="yith-wcbep-product_url-bulk-select" class="yith-wcbep-miniselect is_resetable">
                            <option value="new"><?php _e( 'Set new', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="pre"><?php _e( 'Prepend', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="app"><?php _e( 'Append', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="rep"><?php _e( 'Replace', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="del"><?php _e( 'Delete', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                        </select>
                        <input type="text" id="yith-wcbep-product_url-bulk-value" name="yith-wcbep-product_url-bulk-value" class="yith-wcbep-minifield is_resetable">
                        <input type="text" id="yith-wcbep-product_url-bulk-replace" name="yith-wcbep-product_url-bulk-replace"
                               class="yith_wcbep_no_display yith-wcbep-minifield is_resetable"
                               placeholder="With">
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcbep-bulk-form-label-col">
                        <label><?php _e( 'Up-Sells', 'woocommerce' ) ?></label>
                    </td>
                    <td class="yith-wcbep-bulk-form-content-col">
                        <select id="yith-wcbep-up_sells-bulk-select" name="yith-wcbep-up_sells-bulk-select" class="yith-wcbep-miniselect is_resetable">
                            <option value="new"><?php _e( 'Set new', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="pre"><?php _e( 'Prepend', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="app"><?php _e( 'Append', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="rep"><?php _e( 'Replace', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="del"><?php _e( 'Delete', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                        </select>
                        <input type="text" id="yith-wcbep-up_sells-bulk-value" name="yith-wcbep-up_sells-bulk-value" class="yith-wcbep-minifield is_resetable">
                        <input type="text" id="yith-wcbep-up_sells-bulk-replace" name="yith-wcbep-up_sells-bulk-replace"
                               class="yith_wcbep_no_display yith-wcbep-minifield is_resetable" placeholder="With">
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcbep-bulk-form-label-col">
                        <label><?php _e( 'Cross-Sells', 'woocommerce' ) ?></label>
                    </td>
                    <td class="yith-wcbep-bulk-form-content-col">
                        <select id="yith-wcbep-cross_sells-bulk-select" name="yith-wcbep-cross_sells-bulk-select" class="yith-wcbep-miniselect is_resetable">
                            <option value="new"><?php _e( 'Set new', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="pre"><?php _e( 'Prepend', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="app"><?php _e( 'Append', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="rep"><?php _e( 'Replace', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                            <option value="del"><?php _e( 'Delete', 'yith-woocommerce-bulk-product-editing' ) ?></option>
                        </select>
                        <input type="text" id="yith-wcbep-cross_sells-bulk-value" name="yith-wcbep-cross_sells-bulk-value" class="yith-wcbep-minifield is_resetable">
                        <input type="text" id="yith-wcbep-cross_sells-bulk-replace" name="yith-wcbep-cross_sells-bulk-replace"
                               class="yith_wcbep_no_display yith-wcbep-minifield is_resetable"
                               placeholder="With">
                    </td>
                </tr>
            </table>
        </div>

        <?php if ( $has_custom_fields_set ) : ?>
            <div id="yith-wcbep-bulk-custom-fields">
                <table class="yith-wcbep-bulk-editor-table">
                    <?php do_action( 'yith_wcbep_extra_bulk_custom_fields' ); ?>
                </table>
            </div>

        <?php endif ?>
    </div>
    <div id="yith-wcbep-bulk-editor-notes">
        <?php _e( 'Please note: Bulk Editor edits only enabled columns', 'yith-woocommerce-bulk-product-editing' ) ?>
    </div>
    <div id="yith-wcbep-bulk-button-wrap">
        <input id="yith-wcbep-bulk-apply" type="button" class="button button-primary button-large"
               value="<?php _e( 'Apply', 'yith-woocommerce-bulk-product-editing' ) ?>">
        <input id="yith-wcbep-bulk-cancel" type="button" class="button button-secondary button-large"
               value="<?php _e( 'Cancel', 'yith-woocommerce-bulk-product-editing' ) ?>">
    </div>
</div>