<?php
/**
 * Tiles Page
 *
 * @author    Actuality Extensions
 * @package   WoocommercePointOfSale/Classes/Tiles
 * @category    Class
 * @since     0.1
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('WC_Pos_Tiles')) :

    /**
     * WC_Pos_Tiles Class
     */
    class WC_Pos_Tiles
    {

        /**
         * @var WC_Pos_Tiles The single instance of the class
         * @since 1.9
         */
        protected static $_instance = null;

        /**
         * Main WC_Pos_Tiles Instance
         *
         * Ensures only one instance of WC_Pos_Tiles is loaded or can be loaded.
         *
         * @since 1.9
         * @static
         * @return WC_Pos_Tiles Main instance
         */
        public static function instance()
        {
            if (is_null(self::$_instance)) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        /**
         * Cloning is forbidden.
         *
         * @since 1.9
         */
        public function __clone()
        {
            _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', 'woocommerce'), '1.9');
        }

        /**
         * Unserializing instances of this class is forbidden.
         *
         * @since 1.9
         */
        public function __wakeup()
        {
            _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', 'woocommerce'), '1.9');
        }


        /**
         * __construct function.
         *
         * @access public
         * @return void
         */
        public function __construct()
        {

        }

        public function delete_tiles($id = 0)
        {
            global $wpdb;
            if (isset($_POST['id']) && !empty($_POST['id'])) {
                $ids = $_POST['id'];
            } elseif (isset($_GET['id']) && !empty($_GET['id'])) {
                $ids = $_GET['id'];
            }
            $filter = '';
            if ($ids) {
                if (is_array($ids)) {
                    $ids = implode(',', array_map('intval', $ids));
                    $filter .= "WHERE ID IN ($ids)";
                } else {
                    $filter .= "WHERE ID = $ids";
                }
                $table_name = $wpdb->prefix . "wc_poin_of_sale_tiles";
                $query = "DELETE FROM $table_name $filter";
                if ($wpdb->query($query)) {
                    return wp_redirect(add_query_arg(array("page" => WC_POS()->id_tiles, "message" => 4, "grid_id" => $_GET['grid_id']), 'admin.php'));
                }
            }
            return wp_redirect(add_query_arg(array("page" => WC_POS()->id_tiles, "grid_id" => $_GET['grid_id']), 'admin.php'));
        }

        public function display_edit_form($id = 0)
        {
            $data = array();
            $ajax = false;
            if ($id) {
                $data = $this->get_data($id);
                $data = $data[0];
                $data['grid_id'] = $_GET['grid_id'];
                $data['tile_style'] = $data['style'];
                $data['background_color'] = $data['background'];
                $data['text-color'] = $data['colour'];
                if (isset($_POST['action']) && $_POST['action'] == 'wc_pos_edit_update_tiles') {
                    foreach ($_POST as $key => $value) {
                        $data[$key] = $value;
                    }
                }
                $data['background_color'] = str_replace('#', '', $data['background_color']);
                $data['text-color'] = str_replace('#', '', $data['text-color']);
            } ?>
            <div class="wrap" id="wc-pos-outlets-edit">
                <h2><?php _e('Edit Tile', 'wc_point_of_sale'); ?></h2>
                <?php if (isset($_POST['messages']) && !empty($_POST['messages'])) {
                    $this->the_message($_POST['messages']);
                } ?>

                <div id="ajax-response"></div>
                <form id="edit_wc_pos_outlets" class="validate" action="" method="post">
                    <input type="hidden" value="<?php echo $data['grid_id']; ?>" name="grid_id">
                    <input type="hidden" value="wc_pos_edit_update_tiles" name="action">
                    <input type="hidden" value="<?php echo $data['ID']; ?>" name="id" id="id_tile">
                    <?php wp_nonce_field('nonce-edit-wc-pos-tiles'); ?>
                    <table class="form-table">
                        <tbody>
                        <tr class="form-field form-required">
                            <th valign="top" scope="row">
                                <label for="product_id"><?php _e('Product', 'wc_point_of_sale'); ?></label>
                            </th>
                            <td>
                                <?php
                                $selected = '';
                                $value = '';
                                $image = '';
                                if ($data['product_id']) {
                                    $product = wc_get_product($data['product_id']);
                                    if ($product) {
                                        $selected = wp_kses_post(html_entity_decode($product->get_formatted_name()));
                                        $value = $product->get_id();
                                    }
                                    $size = 'shop_thumbnail';
                                    if (has_post_thumbnail($data['product_id'])) {
                                        $thumbnail = wp_get_attachment_image_src(get_post_thumbnail_id($data['product_id']), $size);
                                        $image = $thumbnail[0];
                                    } elseif (($parent_id = wp_get_post_parent_id($data['product_id'])) && has_post_thumbnail($parent_id)) {
                                        $thumbnail = wp_get_attachment_image_src(get_post_thumbnail_id($parent_id), $size);
                                        $image = $thumbnail[0];
                                    } else {
                                        $image = wc_placeholder_img_src();
                                    }
                                    if (!$image || $image == NULL)
                                        $image = wc_placeholder_img_src();
                                } ?>
                                <select type="hidden" id="product_id" name="product_id"
                                        class="ajax_chosen_input_products" style="width: 400px;"
                                        data-placeholder="<?php _e('Search for a product&hellip;', 'woocommerce'); ?>"
                                        data-selected="<?php echo $selected; ?>"></select>
                                <p class="description"><?php _e('Select the product which this tile will represent.', 'wc_point_of_sale'); ?></p>
                            </td>
                        </tr>
                        <?php if (!isset($data['tile_style']) || empty($data['tile_style'])) $data['tile_style'] = 'image'; ?>
                        <tr class="form-field tile_style_row">
                            <th valign="top" scope="row">
                                <label for="tile_style"><?php _e('Style', 'wc_point_of_sale'); ?></label>
                            </th>
                            <td>
                                <ul class="wc-radios">
                                    <li>
                                        <label for="tile_style_image">
                                            <input type="radio" name="tile_style" class="tile_style"
                                                   id="tile_style_image"
                                                   value="image" <?php echo (isset($data['tile_style']) && $data['tile_style'] == 'image') ? 'checked="checked"' : ''; ?>/>
                                            <?php _e('Image', 'wc_point_of_sale'); ?>
                                        </label>
                                    </li>
                                    <li>
                                        <label for="tile_style_colour">
                                            <input type="radio" name="tile_style" class="tile_style"
                                                   id="tile_style_colour"
                                                   value="colour" <?php echo (isset($data['tile_style']) && $data['tile_style'] == 'colour') ? 'checked="checked"' : ''; ?>/>
                                            <?php _e('Colour', 'wc_point_of_sale'); ?>
                                        </label>
                                    </li>
                                </ul>
                                <p class="description"><?php _e('The style and appearance of the tile.', 'wc_point_of_sale'); ?></p>
                            </td>
                        </tr>
                        <tr class="form-field form-required tile_style_bg_row">
                            <th valign="top" scope="row">
                                <label for="background_color"><?php _e('Background Color', 'wc_point_of_sale'); ?></label>
                            </th>
                            <td>
                                <?php $default_color = '#FFF'; ?>
                                <?php if ($data['background_color']) { ?>
                                    <input type="text" name="background_color" id="background_color"
                                           value="#<?php echo $data['background_color']; ?>"/>
                                <?php } else { ?>
                                    <input type="text" name="background_color" id="background_color"
                                           value="#<?php echo esc_attr(get_background_color()); ?>"/>
                                <?php } ?>
                                <p class="description"><?php _e('Select the product which this tile will represent.', 'wc_point_of_sale'); ?></p>
                            </td>
                        </tr>
                        <tr class="form-field form-required tile_style_bg_row">
                            <th valign="top" scope="row">
                                <label for="text-color"><?php _e('Text Color', 'wc_point_of_sale'); ?></label>
                            </th>
                            <td>
                                <?php if ($data['text-color']) { ?>
                                    <input type="text" name="text-color" id="text-color"
                                           value="#<?php echo $data['text-color']; ?>"/>
                                <?php } else { ?>
                                    <input type="text" name="text-color" id="text-color"
                                           value="#<?php echo esc_attr(get_background_color()); ?>"/>
                                <?php } ?>
                                <p class="description"><?php _e('Select the text colour for this tile.', 'wc_point_of_sale'); ?></p>
                            </td>
                        </tr>

                        <tr class="form-field form-required dafault_selection" <?php echo (isset($data['default_selection']) && !empty($data['default_selection']) && $data['default_selection']) ? '' : 'style="display: none;"'; ?>>
                            <th valign="top" scope="row">
                                <label for="dafault_selection"><?php _e('Dafault Selection', 'wc_point_of_sale'); ?></label>
                            </th>
                            <td>
                                <select name="dafault_selection" id="dafault_selection" style="width: 400px;">
                                    <option value="0"><?php _e("No Default Selection", "wc_point_of_sale"); ?></option>
                                    ;
                                    <?php
                                    $args = array(
                                        'post_type' => array('product_variation'),
                                        'posts_per_page' => -1,
                                        'post_status' => 'publish',
                                        'order' => 'ASC',
                                        'orderby' => 'parent title',
                                        'post_parent' => $data['product_id'],
                                    );

                                    $posts = get_posts($args);
                                    if ($posts) {
                                        foreach ($posts as $post) {
                                            $product = wc_get_product($post->ID);
                                            $image_v = '';
                                            $size = 'shop_thumbnail';
                                            if (has_post_thumbnail($post->ID)) {
                                                $thumbnail = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), $size);
                                                $image_v = $thumbnail[0];
                                            } elseif (($parent_id = wp_get_post_parent_id($post->ID)) && has_post_thumbnail($parent_id)) {
                                                $thumbnail = wp_get_attachment_image_src(get_post_thumbnail_id($parent_id), $size);
                                                $image_v = $thumbnail[0];
                                            } else {
                                                $image_v = wc_placeholder_img_src();
                                            }
                                            if (!$image_v || $image_v == NULL) $image_v = wc_placeholder_img_src();

                                            if ($post->ID == $data['default_selection']) {
                                                echo '<option value="' . $post->ID . '" data-img="' . $image_v . '" selected>' . $product->get_formatted_name() . '</option>';
                                                $image = $image_v;
                                            } else {
                                                echo '<option value="' . $post->ID . '"  data-img="' . $image_v . '" >' . $product->get_formatted_name() . '</option>';
                                            }

                                        }
                                    }
                                    ?>
                                </select>
                                <p class="description"><?php _e('Selection the Default Section for this using variations.', 'wc_point_of_sale'); ?></p>
                            </td>
                        </tr>
                        <tr class="form-field form-required">
                            <th valign="top" scope="row">
                                <label for="preview"><?php _e('Preview', 'wc_point_of_sale'); ?></label>
                            </th>
                            <td>
                                <div class="preview_default" id="custom-background-image1"
                                     style="background: #<?php echo $data['background_color']; ?>;"
                                     data-shop_thumbnail="<?php echo $image; ?>">
										<span class="style1" id="custom-background-tiles-color"
                                              style="color: #<?php echo $data['text-color']; ?>;">
											your text here
										</span>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <p class="submit">
                        <input type="submit" name="add_new_tiles" id="submit" class="button button-primary"
                               value="<?php _e('Update', 'wc_point_of_sale'); ?>"></p>
                </form>
            </div>
            <?php
        }

        public function update_tile()
        {
            global $wpdb;
            check_admin_referer('nonce-edit-wc-pos-tiles');

            $tile_id = absint($_POST['id']);
            $grid_id = absint($_POST['grid_id']);
            $product_id = absint($_POST['product_id']);
            $product_id = absint($_POST['product_id']);
            $background_color = (isset($_POST['background_color'])) ? (string)stripslashes($_POST['background_color']) : '';
            $text_color = (isset($_POST['text-color'])) ? wc_sanitize_taxonomy_name(stripslashes((string)$_POST['text-color'])) : '';
            $default_selection = isset($_POST['dafault_selection']) ? $_POST['dafault_selection'] : 0;
            $tile_style = isset($_POST['tile_style']) ? $_POST['tile_style'] : 'image';

            $grid_exists = wc_point_of_sale_tiles_product_exists($grid_id, $product_id, $default_selection, $tile_id);
            if ($grid_exists) {
                $_POST['messages'] = 2;
            } else {
                $tiles = array(
                    'grid_id' => $grid_id,
                    'product_id' => $product_id,
                    'colour' => str_replace('#', '', $text_color),
                    'background' => str_replace('#', '', $background_color),
                    'default_selection' => $default_selection,
                    'style' => $tile_style,
                );
                $table_name = $wpdb->prefix . 'wc_poin_of_sale_tiles';
                $wpdb->update($table_name, $tiles, array('ID' => $tile_id));
                return wp_redirect(add_query_arg(array("page" => WC_POS()->id_tiles, "grid_id" => $grid_id, "message" => 1), 'admin.php'));
            }

        }

        /**
         * Handles output of the grids page in admin.
         *
         * Shows the created grids and lets you add new ones or edit existing ones.
         * The added grids are stored in the database and can be used for layered navigation.
         */
        public function output()
        {

            $grid_id = abs($_GET['grid_id']);
            // Action to perform: add, edit, delete or none
            $action = '';
            if (!empty($_POST['add_new_tiles'])) {
                $action = 'add';
            }
            // Add or edit an grid
            if ('add' === $action) {
                $data = array();
                $data['grid_id'] = $grid_id;
                $saved = false;

                $products_or_cat = isset($_POST['products_or_cat']) ? $_POST['products_or_cat'] : 'product';

                if ($products_or_cat == 'category') {
                    $category_id = isset($_POST['category_id']) ? absint($_POST['category_id']) : 0;
                    if (!$category_id)
                        $error = __('Please select category.', 'wc_point_of_sale');

                    if (!empty($error)) {
                        echo '<div id="woocommerce_errors" class="error fade"><p>' . $error . '</p></div>';
                    } else {
                        $data['background_color'] = '';
                        $data['text_color'] = '';
                        $data['default_selection'] = 0;
                        $data['tile_style'] = 'image';
                        $products = get_posts(array(
                                'showposts' => -1,
                                'post_type' => 'product',
                                'tax_query' => array(
                                    array(
                                        'taxonomy' => 'product_cat',
                                        'field' => 'term_id',
                                        'terms' => array($category_id)
                                    )
                                ))
                        );

                        foreach ($products as $product) {
                            $data['product_id'] = $product->ID;
                            $saved = $this->save_tile($data, true);
                        }
                    }
                } else {
                    $data['product_id'] = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
                    $data['background_color'] = (isset($_POST['background_color'])) ? (string)stripslashes($_POST['background_color']) : '';
                    $data['text_color'] = (isset($_POST['text-color'])) ? wc_sanitize_taxonomy_name(stripslashes((string)$_POST['text-color'])) : '';
                    $data['default_selection'] = isset($_POST['dafault_selection']) ? $_POST['dafault_selection'] : 0;
                    $data['tile_style'] = isset($_POST['tile_style']) ? $_POST['tile_style'] : 'image';

                    $saved = $this->save_tile($data);
                }
                if ($saved) {
                    $_POST = array();
                    $this->the_message(3);
                }
            }
            if (isset($_GET['message']) && !empty($_GET['message']) && 'add' != $action) {
                $this->the_message($_GET['message']);
            }
            // Show admin interface
            // fetch data record form table 'wp_wc_poin_of_sale_grids'
            $grids_single_record = wc_point_of_sale_tile_record($grid_id);
            $this->add_tiles($grids_single_record);
        }

        function save_tile($data, $cat = false)
        {
            global $wpdb;
            extract($data);
            // Error checking
            $grid_exists = wc_point_of_sale_tiles_product_exists($grid_id, $product_id, $default_selection);
            if (!$product_id) {
                if (!$cat)
                    $error = sprintf(__('Please select product.', 'wc_point_of_sale'), sanitize_title(' '));
                else
                    return false;
            } else
                if ($grid_exists) {
                    if (!$cat)
                        $error = sprintf(__('Selected product is already added. Change it, please.', 'wc_point_of_sale'), sanitize_title(' '));
                    else
                        return true;
                }

            // Show the error message if any
            if (!empty($error)) {
                echo '<div id="woocommerce_errors" class="error fade"><p>' . $error . '</p></div>';
            } else {
                $table_name = $wpdb->prefix . 'wc_poin_of_sale_tiles';
                $order_position = 1;
                $position = get_last_position_of_tile($grid_id);
                if (!empty($position->max)) $order_position = $position->max + 1;

                $tiles = array(
                    'grid_id' => $grid_id,
                    'product_id' => $product_id,
                    'colour' => str_replace('#', '', $text_color),
                    'background' => str_replace('#', '', $background_color),
                    'default_selection' => $default_selection,
                    'order_position' => $order_position,
                    'style' => $tile_style
                );
                // insert gird layout data  its table "wp_wc_poin_of_sale_grids"
                return $wpdb->insert($table_name, $tiles);
            }
        }

        /**
         * Add Grid admin panel
         *
         * Shows the interface for adding new grids
         */
        public function add_tiles($grids_single_record)
        {
            global $wpdb;
            $id_grid = $grids_single_record[0]->ID;
            $grid_t = $wpdb->prefix . "wc_poin_of_sale_grids";
            $sort = $wpdb->get_var("SELECT sort_order FROM $grid_t WHERE ID = {$id_grid} LIMIT 1");
            $sort = !empty($sort) ? $sort : 'name';
            ?>
            <div class="wrap woocommerce">
                <div class="icon32 icon32-grids" id="icon-woocommerce"><br/></div>
                <h2><?php _e(ucfirst($grids_single_record[0]->name) . ' Layout', 'wc_point_of_sale') ?></h2>
                <br class="clear"/>
                <div id="col-container">
                    <div id="col-right">
                        <div class="col-wrap">
                            <div style="clear:both"></div>
                            <form id="wc_pos_tiles_table" action="" method="post" class="<?php echo $sort; ?>">
                                <?php
                                $tiles_table = WC_POS()->tiles_table();
                                $tiles_table->prepare_items();
                                $tiles_table->display();
                                ?>
                            </form>
                            <style>
                                td.column-preview {
                                    float: none !important;
                                }

                                td.column-preview div {
                                    text-align: center;
                                }

                                th#preview {
                                    width: 20%;
                                }
                            </style>

                            <div style="clear:both"></div>
                            <!--<div class="full">
                                <br>
                                <h2><?php /*_e('Grid Preview ', 'wc_point_of_sale'); */
                            ?></h2>
                                <div class="postbox wc-pos-preview-register-grids" id="wc-pos-register-grids">
                                    <h3 class="hndle">
                                        <span><?php /*_e(ucfirst($grids_single_record[0]->name) . ' Layout', 'wc_point_of_sale') */
                            ?></span>
                                    </h3>
                                    <div class="inside" id="grid_layout_cycle">
                                        <?php
                            /*                                        $i = 0;
                                                                    $t = 0;
                                                                    $image = '';
                                                                    $titles = wc_point_of_sale_get_tiles($_GET['grid_id']);

                                                                    if ($titles) :
                                                                        $current_page = isset($_GET['paged']) ? $_GET['paged'] : 1;
                                                                        $per_page = 25;
                                                                        $titles = array_slice($titles, (($current_page - 1) * $per_page), $per_page);
                                                                        foreach ($titles as $title) :
                                                                            if ($title->default_selection)
                                                                                $product_id = $title->default_selection;
                                                                            else
                                                                                $product_id = $title->product_id;
                                                                            $i++;
                                                                            $t++;
                                                                            if ($t == 1) {
                                                                                echo '<div><table><tbody>';
                                                                            }
                                                                            if ($i == 1) echo '<tr>';
                                                                            if ($title->style == 'image') {
                                                                                $image = '';
                                                                                $size = 'shop_thumbnail';
                                                                                if (has_post_thumbnail($product_id)) {
                                                                                    $thumbnail = wp_get_attachment_image_src(get_post_thumbnail_id($product_id), $size);
                                                                                    $image = $thumbnail[0];
                                                                                } elseif (($parent_id = wp_get_post_parent_id($product_id)) && has_post_thumbnail($parent_id)) {
                                                                                    $thumbnail = wp_get_attachment_image_src(get_post_thumbnail_id($parent_id), $size);
                                                                                    $image = $thumbnail[0];
                                                                                } else {
                                                                                    $image = wc_placeholder_img_src();
                                                                                }
                                                                                if (!$image || $image == NULL) $image = wc_placeholder_img_src();

                                                                                */
                            ?>
                                                    <td id="title_<?php /*echo $title->ID */
                            ?>"
                                                        style="background: url('<?php /*echo $image; */
                            ?>') top no-repeat; background-size: auto 60px; background-color: #fff; vertical-align: bottom;"
                                                        class="title_product">
                                                        <a style="color: #222222; margin-bottom: 5px; display: block; font-weight: normal; font-size: 12px;"
                                                           data-id="<?php /*echo $product_id; */
                            ?>"><?php /*echo get_the_title($title->product_id); */
                            ?></a>
                                                    </td>
                                                    <?php
                            /*                                                } else { */
                            ?>
                                                    <td id="title_<?php /*echo $title->ID */
                            ?>"
                                                        style="background: #<?php /*echo $title->background; */
                            ?>; "
                                                        class="title_product">
                                                        <a style="color: #<?php /*echo $title->colour; */
                            ?>;"
                                                           data-id="<?php /*echo $product_id; */
                            ?>"><?php /*echo get_the_title($title->product_id); */
                            ?></a>
                                                    </td>
                                                    <?php
                            /*                                                }

                                                                            if ($i == 5) {
                                                                                echo '</tr>';
                                                                                $i = 0;

                                                                                if ($t == 25) {
                                                                                    $t = 0;
                                                                                    echo '</tbody></table></div>';
                                                                                }
                                                                            };

                                                                        endforeach;
                                                                        if ($i != 0) {
                                                                            $j = $i + 1;
                                                                            for ($j; $j <= 5; $j++) :
                                                                                */
                            ?>
                                                    <td></td>
                                                    <?php
                            /*                                                    if ($j == 5) echo '</tr>';
                                                                            endfor;
                                                                            echo '</tbody></table></div>';
                                                                        } else {
                                                                            if ($t != 0) {
                                                                                $t = 0;
                                                                                echo '</tbody></table></div>';
                                                                            }
                                                                        }
                                                                    endif;
                                                                    */
                            ?>
                                    </div>
                                    <div class="previous-next-toggles">
                                        <span class="previous-grid-layout tips"
                                              data-tip="<?php /*_e('Previous', 'wc_point_of_sale'); */
                            ?>"></span>
                                        <div id="nav_layout_cycle"></div>
                                        <span class="next-grid-layout tips"
                                              data-tip="<?php /*_e('Next', 'wc_point_of_sale'); */
                            ?>"></span>
                                    </div>
                                </div>
                            </div>-->
                        </div>
                    </div>
                    <div id="col-left">
                        <?php
                        $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
                        $background_color = (isset($_POST['background_color'])) ? (string)stripslashes($_POST['background_color']) : '8E8E8E';

                        $tile_style = isset($_POST['tile_style']) ? $_POST['tile_style'] : 'image';

                        $text_color = (isset($_POST['text-color'])) ? wc_sanitize_taxonomy_name(stripslashes((string)$_POST['text-color'])) : 'FFFFFF';
                        $default_selection = isset($_POST['dafault_selection']) ? $_POST['dafault_selection'] : 0;


                        $text_color = str_replace('#', '', $text_color);
                        $background_color = str_replace('#', '', $background_color);
                        ?>
                        <form action="" method="post" class="addGirdLayout" id="serach_tile_product" autocomplete="off">
                            <div class="col-wrap">
                                <div class="form-wrap">
                                    <h3><?php _e('Add New ' . ucfirst($grids_single_record[0]->name) . ' Tile', 'wc_point_of_sale') ?></h3>
                                    <input type="hidden" value="<?php echo $_GET['grid_id'] ?>" id="grid_id">
                                    <div class="form-field">
                                        <label for="opt"><?php _e('Origin', 'wc_point_of_sale'); ?></label>
                                        <ul class="wc-radios">
                                            <li>
                                                <label for="products_opt">
                                                    <input type="radio" id="products_opt" name="products_or_cat"
                                                           value="product" checked/>
                                                    <?php _e('Product', 'wc_point_of_sale'); ?>
                                                </label>
                                            </li>
                                            <li>
                                                <label for="category_opt">
                                                    <input type="radio" id="category_opt" name="products_or_cat"
                                                           value="category"/>
                                                    <?php _e('Category', 'wc_point_of_sale'); ?>
                                                </label>
                                            </li>
                                        </ul>
                                        <div class="clear"></div>
                                        <p class="description"><?php _e('The origin for where these tiles will populate from.', 'wc_point_of_sale'); ?></p>
                                    </div>
                                    <div id="category_opt_wrap">
                                        <div class="form-field">
                                            <label for="category_id"><?php _e('Category', 'wc_point_of_sale'); ?></label>
                                            <?php
                                            $args = array(
                                                'orderby' => 'name',
                                                'order' => 'ASC',
                                                'hide_empty' => true,
                                                'fields' => 'all'
                                            );
                                            $terms = get_terms(array('product_cat'), $args);
                                            ?>
                                            <select id="category_id" class="category_chosen" style="width: 294px;"
                                                    name="category_id">
                                                <option value=""><? _e('Select category', 'wc_point_of_sale'); ?></option>
                                                <?php
                                                if (!empty($terms)) {
                                                    foreach ($terms as $term) { ?>
                                                        <option value="<?php echo $term->term_id; ?>"><?php echo $term->name; ?></option>
                                                        <?php
                                                    }
                                                }
                                                ?>
                                            </select>
                                            <p class="description"><?php _e('Select the product category which contains the products you want to add.', 'wc_point_of_sale'); ?></p>
                                        </div>
                                    </div>
                                    <div id="products_opt_wrap">
                                        <div class="form-field">
                                            <label for="product_id"><?php _e('Product', 'wc_point_of_sale'); ?></label>
                                            <select id="product_id" name="product_id"
                                                    class="wc-product-search ajax_chosen_input_products"
                                                    style="width: 400px;"
                                                    data-placeholder="<?php _e('Search for a product&hellip;', 'woocommerce'); ?>"></select>
                                            <p class="description"><?php _e('Select the product which this tile will represent.', 'wc_point_of_sale'); ?></p>
                                        </div>
                                        <div class="form-field">
                                            <label for="tile_style"><?php _e('Style', 'wc_point_of_sale'); ?></label>
                                            <ul class="wc-radios">
                                                <li>
                                                    <label for="tile_style_image">
                                                        <input type="radio" name="tile_style" class="tile_style"
                                                               id="tile_style_image"
                                                               value="image" <?php echo ($tile_style == 'image') ? 'checked="checked"' : ''; ?>/>
                                                        <?php _e('Image', 'wc_point_of_sale'); ?>
                                                    </label>
                                                </li>
                                                <li>
                                                    <label for="tile_style_colour">
                                                        <input type="radio" name="tile_style" class="tile_style"
                                                               id="tile_style_colour"
                                                               value="colour" <?php echo ($tile_style == 'colour') ? 'checked="checked"' : ''; ?>/>
                                                        <?php _e('Colour', 'wc_point_of_sale'); ?>
                                                    </label>
                                                </li>
                                            </ul>
                                            <p class="description"><?php _e('The style and appearance of the tile.', 'wc_point_of_sale'); ?></p>
                                        </div>
                                        <div class="form-field tile_style_bg_row">
                                            <label for="background_color"><?php _e('Background Color', 'wc_point_of_sale'); ?></label>
                                            <input type="text" name="background_color" id="background_color"
                                                   value="#<?php echo $background_color; ?>"/>
                                            <p class="description"><?php _e('Select the background colour for this tile.', 'wc_point_of_sale'); ?></p>
                                        </div>
                                        <div class="form-field tile_style_bg_row">
                                            <label for="text-color"><?php _e('Text Color', 'wc_point_of_sale'); ?></label>
                                            <input type="text" name="text-color" id="text-color"
                                                   value="#<?php echo $text_color; ?>"/>
                                            <p class="description"><?php _e('Select the text colour for this tile.', 'wc_point_of_sale'); ?></p>
                                        </div>
                                        <div class="form-field dafault_selection" style="display: none;">
                                            <label for="dafault_selection"><?php _e('Dafault Selection', 'wc_point_of_sale'); ?></label>
                                            <select name="dafault_selection" id="dafault_selection"
                                                    style="width: 400px;"></select>
                                            <p class="description"><?php _e('Selection the Default Section for this using variations.', 'wc_point_of_sale'); ?></p>
                                        </div>

                                        <div class="form-field">
                                            <label for="preview"><?php _e('Preview', 'wc_point_of_sale'); ?></label>
                                            <div class="preview_default" id="custom-background-image1"
                                                 style="background: #<?php echo $background_color; ?>;"
                                                 data-shop_thumbnail="<?php echo $image; ?>">
											<span class="style1" id="custom-background-tiles-color"
                                                  style="color: #<?php echo $text_color; ?>;">
												your text here
											</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <p class="submit">
                                <input type="submit" name="add_new_tiles" id="submit" class="button button-primary"
                                       value="<?php _e('Add New Tile', 'wc_point_of_sale'); ?>"></p>
                            <?php wp_nonce_field('woocommerce-add-new_tiles'); ?>
                        </form>

                    </div>
                </div>
            </div>
            <?php
        }

        public function the_message($id = 0)
        {
            switch ($id) {
                case 1:
                    echo '<div id="woocommerce_errors" class="updated"><p>' . __('Tile updated.', 'wc_point_of_sale') . '</p></div>';
                    break;
                case 2:
                    echo '<div class="error fade" id="message"><p>' . __('Selected product is already a tile.', 'wc_point_of_sale') . '</p></div>';
                    break;
                case 3:
                    echo '<div id="woocommerce_errors" class="updated"><p>' . __('Tile added successfully.', 'wc_point_of_sale') . '</p></div>';
                    break;
                case 4:
                    echo '<div id="woocommerce_errors" class="updated"><p>' . __('Tile deleted successfully.', 'wc_point_of_sale') . '</p></div>';
                    break;
            }
        }

        public function get_data($ids = '')
        {
            global $wpdb;
            $filter = '';
            $orderby = '';
            $join = '';
            $grid_id = 0;
            if (!empty($ids)) {
                if (is_array($ids)) {
                    $ids = implode(',', array_map('intval', $ids));
                    $filter .= "WHERE ID IN  == ($ids)";
                } else {
                    $filter .= "WHERE ID = $ids";
                }
            } else if (isset($_GET['grid_id']) && !empty($_GET['grid_id'])) {
                $grid_id = $_GET['grid_id'];

                $grid_t = $wpdb->prefix . "wc_poin_of_sale_grids";
                $sort = $wpdb->get_var("SELECT sort_order FROM $grid_t WHERE ID = {$grid_id} LIMIT 1");
                switch ($sort) {
                    case 'name':
                        $join .= " LEFT JOIN {$wpdb->posts} prod ON (prod.ID = tiles_t.product_id)";
                        $orderby .= ' ORDER BY prod.post_title ASC';
                        break;
                    default:
                        $orderby .= ' ORDER BY order_position ASC';
                        break;
                }

                if (empty($filter))
                    $filter .= "WHERE grid_id = $grid_id";
                else
                    $filter .= " AND grid_id = $grid_id";

            }

            if (empty($orderby))
                $orderby = ' ORDER BY order_position ASC';

            $table_name = $wpdb->prefix . "wc_poin_of_sale_tiles";
            $db_data = $wpdb->get_results("SELECT tiles_t.* FROM {$table_name} as tiles_t {$join} {$filter} {$orderby}");
            $data = array();

            foreach ($db_data as $value) {
                $data[] = get_object_vars($value);
            }
            return $data;
        }

    }

endif;