<?php

/*************************** LOAD THE BASE CLASS *******************************
 *******************************************************************************
 * The WP_List_Table class isn't automatically available to plugins, so we need
 * to check if it's available and load it if necessary.
 */
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}




/************************** CREATE A PACKAGE CLASS *****************************
 *******************************************************************************
 * Create a new list table package that extends the core WP_List_Table class.
 * WP_List_Table contains most of the framework for generating the table, but we
 * need to define and override some methods so that our data can be displayed
 * exactly the way we need it to be.
 * 
 * To display this example on a page, you will first need to instantiate the class,
 * then call $yourInstance->prepare_items() to handle any data manipulation, then
 * finally call $yourInstance->display() to render the table to the page.
 * 
 * Our theme for this list table is going to be profiles.
 */
class WPLA_ListingsTable extends WP_List_Table {

    var $last_product_id         = 0;
    var $last_product_object     = array();
    var $last_product_var_id     = 0;
    var $last_product_variations = array();
    var $last_profile_id         = 0;
    var $last_profile_object     = array();
    var $profiles                = array();
    var $templates               = array();
    var $total_items;

    /** ************************************************************************
     * REQUIRED. Set up a constructor that references the parent constructor. We 
     * use the parent reference to set some default configs.
     ***************************************************************************/
    function __construct(){
        global $status, $page;
                
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'listing',     //singular name of the listed records
            'plural'    => 'listings',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );

        // custom view switcher modes
        $this->modes = array(
            'list'    => __( 'View all variations', 'wpla' ),
            'excerpt' => __( 'View only parent variations', 'wpla' ),
        );
        
        // get array of profile names
        // $profilesModel = new ProfilesModel();
        // $this->profiles = $profilesModel->getAllNames();
        $this->profiles  = WPLA_AmazonProfile::getAllNames();
        $this->templates = WPLA_AmazonProfile::getAllTemplateNames();
    }
    
    
    /** ************************************************************************
     * Recommended. This method is called when the parent class can't find a method
     * specifically build for a given column. Generally, it's recommended to include
     * one method for each column you want to render, keeping your package class
     * neat and organized. For example, if the class needs to process a column
     * named 'title', it would first see if a method named $this->column_title() 
     * exists - if it does, that method will be used. If it doesn't, this one will
     * be used. Generally, you should try to use custom column methods as much as 
     * possible. 
     * 
     * Since we have defined a column_title() method later on, this method doesn't
     * need to concern itself with any column with a name of 'title'. Instead, it
     * needs to handle everything else.
     * 
     * For more detailed insight into how columns are handled, take a look at 
     * WP_List_Table::single_row_columns()
     * 
     * @param array $item A singular item (one full row's worth of data)
     * @param array $column_name The name/slug of the column to be processed
     * @return string Text or HTML to be placed inside the column <td>
     **************************************************************************/
    function column_default($item, $column_name){
        switch($column_name){
            case 'type':
            case 'quantity_sold':
            case 'asin':
            case 'status':
                return $item[$column_name];
            case 'fees':
            case 'price':
                return $this->number_format( $item[$column_name], 2 );
            case 'end_date':
            case 'date_published':
            	// use date format from wp
                return mysql2date( get_option('date_format'), $item[$column_name] );
            case 'template':
                return basename( $item['template'] );
            case 'profile':
                return isset($item['profile_id']) ? $this->profiles[ $item['profile_id'] ] : '';
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }
    
        
    /** ************************************************************************
     * Recommended. This is a custom column method and is responsible for what
     * is rendered in any column with a name/slug of 'title'. Every time the class
     * needs to render a column, it first looks for a method named 
     * column_{$column_title} - if it exists, that method is run. If it doesn't
     * exist, column_default() is called instead.
     * 
     * This example also illustrates how to implement rollover actions. Actions
     * should be an associative array formatted as 'slug'=>'link html' - and you
     * will need to generate the URLs yourself. You could even ensure the links
     * 
     * 
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (profile title only)
     **************************************************************************/
    function column_listing_title($item){
        
        // get current page with paging as url param
        $page = $_REQUEST['page'];
        if ( isset( $_REQUEST['paged'] )) $page .= '&paged='.$_REQUEST['paged'];

        $listing_url      = WPLA_ListingsModel::getUrlForItemObj( $item );
        $view_pnq_log_url = 'admin-ajax.php?action=wpla_view_pnq_log&sku='.$item['sku'];

        //Build row actions
        $actions = array(
            // 'preview_listing' => sprintf('<a href="?page=%s&action=%s&listing=%s&width=820&height=550" class="thickbox">%s</a>',$page,'preview_listing',$item['id'],__('Preview','wpla')),
            'edit'            => sprintf('<a href="?page=%s&action=%s&listing=%s">%s</a>','wpla','edit',$item['id'],__('Edit','wpla')),
            'create_product'  => sprintf('<a href="?page=%s&action=%s&listing=%s&_wpnonce=%s">%s</a>',$page,'wpla_create_product',$item['id'], wp_create_nonce( 'wpla_create_product' ), __('Create Product','wpla')),
            // 'publish2e'       => sprintf('<a href="?page=%s&action=%s&listing=%s">%s</a>',$page,'publish2e',$item['id'],__('Publish','wpla')),
            'open'            => sprintf('<a href="%s" target="_blank">%s</a>',$listing_url,__('View on Amazon','wpla')),
            // 'revise'          => sprintf('<a href="?page=%s&action=%s&listing=%s">%s</a>',$page,'revise',$item['id'],__('Revise','wpla')),
            'end_item'        => sprintf('<a href="?page=%s&action=%s&listing=%s&_wpnonce=%s">%s</a>',$page,'wpla_end_item',$item['id'], wp_create_nonce( 'wpla_end_listing' ), __('End Listing','wpla')),
            // 'update'          => sprintf('<a href="?page=%s&action=%s&listing=%s">%s</a>',$page,'update',$item['id'],__('Update','wpla')),
            // 'relist'          => sprintf('<a href="?page=%s&action=%s&listing=%s">%s</a>',$page,'relist',$item['id'],__('Relist','wpla')),
            'resubmit'        => sprintf('<a href="?page=%s&action=%s&listing=%s&_wpnonce=%s">%s</a>',$page,'wpla_resubmit',$item['id'], wp_create_nonce( 'bulk-listings' ), __('Submit again','wpla')),
            'delete'          => sprintf('<a href="?page=%s&action=%s&listing=%s&_wpnonce=%s">%s</a>',$page,'wpla_delete',$item['id'], wp_create_nonce( 'bulk-listings' ), __('Delete','wpla')),
            // 'view_pnq_log'    => sprintf('<a href="?page=%s&action=%s&listing=%s" class="thickbox">%s</a>',$page,'view_pnq_log',$item['sku'],__('Changelog','wpla')),
            'view_pnq_log'    => sprintf('<a href="%s" target="_blank" class="thickbox">%s</a>',$view_pnq_log_url,__('Changelog','wpla')),
        );

        // $profile_data = maybe_unserialize( $item['profile_data'] );
        $listing_title = $item['listing_title'];

        // limit item title to 80 characters
        // if ( WPLA_ListingsModel::mb_strlen($listing_title) > 80 ) $listing_title = WPLA_ListingsModel::mb_substr( $listing_title, 0, 77 ) . '...';
        

        // make title link to products edit page
        if ( $item['post_id'] ) {
            $post_id = $item['parent_id'] ? $item['parent_id'] : $item['post_id'];
            $source  = self::getSourceInfo( $item['source'] );
            $listing_title = '<a class="product_title_link" href="post.php?post='.$post_id.'&action=edit" title="'.$source.'">'.$listing_title.'</a>';
        }

        // show warning if WooCommerce product has been deleted
        if ( ! $this->getProduct( $item['post_id'] ) && ($item['status'] != 'imported') ) {
            $tip_msg = 'The product #'.$item['post_id'].' has been deleted!<br>Please do <i>not</i> delete products from WooCommerce, or delete the listing first.';
            $img_url  = WPLA_URL . '/img/error.gif';
            $listing_title .= '&nbsp;<img src="'.$img_url.'" style="height:12px; padding:0;" class="tips" data-tip="'.$tip_msg.'"/>&nbsp;';
            $listing_title = str_replace('product_title_link', 'missing_product_title_link', $listing_title);
        } 

        // add variations link
        $listing_title .= $this->generateVariationsHtmlLink( $item, null );

        /*
        // show errors and warning on online and failed items
        if ( in_array( $item['status'], array( 'online', 'failed' ) ) ) {

            $history = maybe_unserialize( $item['history'] );
            $tips_errors   = array();
            $tips_warnings = array();
            if ( is_array( $history ) ) {
                foreach ( $history['errors'] as $feed_error ) {
                    $tips_errors[]   = WPLA_FeedValidator::formatAmazonFeedError( $feed_error );
                }
                foreach ( $history['warnings'] as $feed_error ) {
                    $tips_warnings[] = WPLA_FeedValidator::formatAmazonFeedError( $feed_error );
                }
            }
            if ( ! empty( $tips_errors ) ) {
                $listing_title .= '<br><small style="color:darkred">'.join('<br>',$tips_errors).'</small>';
            }
            if ( ! empty( $tips_warnings ) ) {
                $listing_title .= '<small><br><a href="#" onclick="jQuery(\'#warnings_container_'.$item['id'].'\').slideToggle();return false;">&raquo; '.''.sizeof($tips_warnings).' warning(s)'.'</a></small>';
                $listing_title .= '<div id="warnings_container_'.$item['id'].'" style="display:none">';
                $listing_title .= '<small>'.join('<br>',$tips_warnings).'</small>';
                $listing_title .= '</div>';
            }

        // show description on normal items
        // } elseif ( $item['description'] ) {
        //     $description = strip_tags( $item['description'] );
        //     if ( strlen($description) > 100 ) $description = substr( $description, 0, 100 ) . '...';
        //     $listing_title .= '<br><span style="color:silver">'.$description.'</span>';
        }
        */


        // disable some actions depending on status
        if ( $item['status'] != 'imported' ) unset( $actions['create_product'] );
        if ( ! $item['asin'] ) unset( $actions['open'] );

        if ( $item['status'] != 'published' )   unset( $actions['end_item'] );
        if ( $item['status'] != 'changed' )     unset( $actions['revise'] );
        if ( $item['status'] != 'ended' )       unset( $actions['delete'] );
        if ( $item['status'] != 'ended' )       unset( $actions['relist'] );
        if ( $item['status'] != 'relisted' )    unset( $actions['update'] );
        if ( $item['status'] != 'failed' )      unset( $actions['resubmit'] );

        if ( $_REQUEST['page'] != 'wpla-tools') unset( $actions['view_pnq_log'] );

        // make edit listing link only available to developers
        if ( ! get_option('wpla_enable_item_edit_link') ) {
            unset( $actions['edit'] );
        }

        //Return the title contents
        //return sprintf('%1$s <span style="color:silver">%2$s</span>%3$s',
        return sprintf('%1$s %2$s',
            /*$1%s*/ $listing_title,
            /*$2%s*/ $this->row_actions($actions)
        );
    } // column_listing_title()

    static function getSourceInfo( $source ){
        switch( $source ){
            case 'matched':
                return 'This item was matched to an existing ASIN on Amazon.';
            case 'imported':
                return 'This item was imported from Amazon.';
            case 'foreign_import':
                return 'This item was imported from Amazon by ASIN.';
            case 'woo':
                return 'This item was listed from WooCommerce.';
            default:
                return 'Unknown source';
        }
    }  

    /**
     * overwrite WP_List_Table::single_row to insert optional message row
     */
    public function single_row( $item ) {
        echo '<tr>';
        $this->single_row_columns( $item );
        $this->displayMessageRow( $item );
        echo '</tr>';
    }

    // show errors and warnings
    function displayMessageRow( $item ){
        $listing_title = '';

        // show errors and warning on online and failed items
        if ( in_array( $item['status'], array( 'online', 'failed' ) ) ) {

            $history = maybe_unserialize( $item['history'] );
            $tips_errors   = array();
            $tips_warnings = array();
            if ( is_array( $history ) ) {
                foreach ( $history['errors'] as $feed_error ) {
                    $tips_errors[]   = WPLA_FeedValidator::formatAmazonFeedError( $feed_error );
                }
                foreach ( $history['warnings'] as $feed_error ) {
                    $tips_warnings[] = WPLA_FeedValidator::formatAmazonFeedError( $feed_error );
                }
            }
            if ( ! empty( $tips_errors ) ) {
                $listing_title .= '<!br><small style="color:darkred">'.join('<br>',$tips_errors).'</small><br>';
            }
            if ( ! empty( $tips_warnings ) ) {
                $listing_title .= '<small><!br><a href="#" onclick="jQuery(\'#warnings_container_'.$item['id'].'\').slideToggle();return false;">&raquo; '.''.sizeof($tips_warnings).' warning(s)'.'</a></small><br>';
                $listing_title .= '<div id="warnings_container_'.$item['id'].'" style="display:none">';
                $listing_title .= '<small>'.join('<br>',$tips_warnings).'</small>';
                $listing_title .= '</div>';
            }

        }

        // show listing quality issues on online and changed items
        if ( in_array( $item['status'], array( 'online', 'changed', 'prepared' ) ) ) {

            $quality_info = maybe_unserialize( $item['quality_info'] );
            if ( is_array( $quality_info ) ) {

                $quality_warning_title = $quality_info['alert-type'];
                if ( $quality_warning_title == 'Missing' ) $quality_warning_title .= ' ' . $quality_info['field-name'];

                $error_msg  = '<b>Warning: '.$quality_warning_title.'</b>';
                $error_msg .= '<br>'.$quality_info['explanation'];

                if ( ! empty( $quality_info['current-value'] ) ) {
                    $error_msg .= '<br>'.$quality_info['field-name'].': '.$quality_info['current-value'].' ';
                }

                if ( ! empty( $quality_info['status'] ) ) {
                    $error_msg .= '<br>Status: '.$quality_info['status'].' ('.$quality_info['alert-name'].')';
                }
                $error_msg = WPLA_FeedValidator::convert_links( $error_msg );

                $listing_title .= '<small style="color:darkred">'.$error_msg.'</small><br>';
            }

        }

        if ( empty($listing_title) ) return;

        echo '</tr>';
        echo '<tr>';
        // echo '<td colspan="'.sizeof( $this->_column_headers[0] ).'">';
        echo '<td>&nbsp;</td>';
        echo '<td class="wpla_auto_width_column" colspan="7">';
        echo $listing_title;
        echo '</td>';
        echo '<td>&nbsp;</td>';

    } // displayMessageRow()


    function generateVariationsHtmlLink( $item, $profile_data ){
        $variations_html = ' ';

        // check for variations
        if ( WPLA_ProductWrapper::hasVariations( $item['post_id'] ) ) {

            $listingsModel = new WPLA_ListingsModel();
            $variations    = $this->getProductVariations( $item['post_id'] );

            // check variations cache
            // $result = $listingsModel->matchCachedVariations( $item );
            // if ( $result && $result->success ) 
            //     $variations = $result->variations;
            // // echo "<pre>";print_r($result);echo"</pre>";#die();


            // show warning if not variations found
            if ( ! is_array($variations) || ! sizeof($variations) ) {
                $img_url  = WPLA_URL . '/img/error.gif';
                $variations_html .= '(<a href="#" onClick="jQuery(\'#pvars_'.$item['id'].'\').toggle();return false;">&raquo;Variations</a>)<!br>';
                $variations_html .= '&nbsp;<img src="'.$img_url.'" style="height:12px; padding:0;"/>&nbsp;<br>';
                $variations_html .= '<b style="color:darkred">No variations found.</b><br>';
                $variations_html .= '<div id="pvars_'.$item['id'].'" class="variations_list" style="display:none;margin-bottom:10px;">';
                $variations_html .= 'Please read the <a href="https://www.wplab.com/plugins/wp-lister/faq/#Variations" target="_blank">FAQ</a> or contact support.';
                $variations_html .= '</div>';
                return $variations_html;
            }

            // get max_quantity from profile
            // $max_quantity = ( isset( $profile_data['details']['max_quantity'] ) && intval( $profile_data['details']['max_quantity'] )  > 0 ) ? $profile_data['details']['max_quantity'] : PHP_INT_MAX ; 
            $max_quantity = PHP_INT_MAX; 

            // get view_switcher mode
            $mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : 'list';
            $link_css = $mode == 'excerpt' ? 'display:block;' : 'display:none;';

            // add Variations link and container
            $variations_html .= '(<a href="#TB_inline?width=600&inlineId=pvars_'.$item['id'].'" class="thickbox">&raquo;</a>';
            $variations_html .= '<a href="#" onClick="jQuery(\'#pvars_'.$item['id'].'\').toggle();return false;"> '.sizeof($variations).' '.__('Variations','wpla').'</a>)';
            $variations_html .= '<div id="pvars_'.$item['id'].'" class="variations_list" style="margin-bottom:10px;width:99%;'.$link_css.'">';


            $variations_html .= '<table class="variations_table" style="margin-bottom: 8px;">';

            // header
            $variations_html .= '<tr><th>';
            $variations_html .= '&nbsp;';
            $variations_html .= '</th><th>';
            $first_variation = reset( $variations );
            if ( is_array( $first_variation['variation_attributes'] ) ) {
                foreach ($first_variation['variation_attributes'] as $name => $value) {
                    $variations_html .= $name;
                    $variations_html .= '</th><th>';
                }
            }
            $variations_html .= __('SKU','wpla');
            $variations_html .= '</th><th align="right">';
            $variations_html .= __('Price','wpla');
            $variations_html .= '</th></tr>';

            foreach ($variations as $var) {

                // first column: quantity
                $variations_html .= '<tr><td align="right">';
                $variations_html .= min( $max_quantity, intval( $var['stock'] ) ) . '&nbsp;x';
                $variations_html .= '</td>';

                foreach ($var['variation_attributes'] as $name => $value) {
                    // $variations_html .= $name.': '.$value ;
                    $variations_html .= '<td>';
                    $variations_html .= $value ;
                    $variations_html .= '</td>';
                }
                // $variations_html .= '('.$var['sku'].') ';
                // $variations_html .= '('.$var['image'].') ';
                
                // column: SKU
                $variations_html .= '<td>';
                $variations_html .= $var['sku'] ? $var['sku'] : '<span style="color:darkred">SKU is missing!</span';
                $variations_html .= @$var['is_default'] ? ' *' : '';
                $variations_html .= '</td>';

                // last column: price
                $variations_html .= '<td align="right">';
                // $price = $listingsModel->applyProfilePrice( $var['price'], @$profile_data['details']['start_price'] );
                $price = $var['price'];
                $variations_html .= $this->number_format( $price, 2 );

                $variations_html .= '</td></tr>';

            }
            $variations_html .= '</table>';

            $variations_html .= '</div>';
        }

        return $variations_html;

    } // generateVariationsHtmlLink()

  
    function column_quantity($item){
        
        // If FBA is enabled for this item, only show FBA qty because WooCommerce qty is not used
        $fba_enabled = $item['fba_fcid'] && ( $item['fba_fcid'] != 'DEFAULT' ) ;
        if ( $item['fba_quantity'] > 0 ) {
            $qty  = '<span style="color:darkblue">' . $item['fba_quantity'].' (FBA)</span>';
            $qty .= '<br><span style="color:silver; font-size:0.8em;">' . $item['fba_fcid'].'</span>';
            return $qty;
        // unless there is no stock left in FBA
        } elseif ( $fba_enabled  ) {
            $fba_enable_fallback = get_option( 'wpla_fba_enable_fallback', 0 );
            $qty  = $fba_enable_fallback ? $item['quantity'] : '<span style="color:silver">' . $item['quantity'].'</span>'; // show woo qty in gray if fallback is disabled
            $qty .= ' / <span style="color:#dd3d36">' . $item['fba_quantity'].'</span>';
            $qty .= '<br><span style="color:silver; font-size:0.8em;">' . $item['fba_fcid'].'</span>';            
            return $qty;
        }

        // if item has variations count them...
        if ( ( $item['post_id'] ) && WPLA_ProductWrapper::hasVariations( $item['post_id'] ) ) {

            $variations = $this->getProductVariations( $item['post_id'] );

            $quantity = 0;
            foreach ($variations as $var) {
                $quantity += intval( $var['stock'] );
            }
            return $quantity;
        }
       
        // fetch latest quantity for changed items
        // if ( $item['status'] == 'changed' ) {
        //     $profile_data = maybe_unserialize( $item['profile_data'] );
        //     if ( intval($profile_data['details']['quantity']) == 0 ) {
        //         $latest_quantity = WPLA_ProductWrapper::getStock( $item['post_id'] );
        //         $$item['quantity'] = $latest_quantity;
        //     }
        // }        

        $qty = $item['quantity'];

        $profile = $this->getProfile( $item['profile_id'] );

        // check for profile quantity and use it if set #21792
        if ( !empty( $profile->fields['quantity'] ) ) {
            $qty = WPLA_FeedDataBuilder::parseProfileShortcode( $profile->fields['quantity'], $profile->fields['quantity'], $item, wc_get_product( $item['post_id'] ), $item['post_id'], $profile );
        }

        // show sold items if there are any
        if ( $item['quantity_sold'] > 0 ) {
            $qty .= '<br><span style="color:silver">' . $item['quantity_sold'].' sold</span>';
        }

        return $qty;
    }
    
    function column_lowest_price($item){

        $lowest_price = $item['lowest_price'] ? $this->number_format( $item['lowest_price'], 2 ) : '&mdash;';
        $last_updated = $item['pricing_date'] ? human_time_diff( strtotime($item['pricing_date'].' UTC') ) . ' ago' : '';
        $has_buybox   = $item['has_buybox'];

        // get prices
        $regular_price    = $this->getPriceForItem( $item );
        $sale_price       = $this->getSalePriceForItem( $item );
        $price_to_compare = $sale_price ? $sale_price : $regular_price;

        $lowest_price_color = 'darkred';
        if ( $item['lowest_price'] >= $item['min_price'] )
            $lowest_price_color = 'orange';
        if ( $item['lowest_price'] >= $price_to_compare )
            $lowest_price_color = 'green';
        if ( $item['has_buybox'] )
            $lowest_price_color = 'green';
        if ( ! $item['lowest_price'] )
            $lowest_price_color = '';

        if ( $item['lowest_price'] ) {
            // $lowest_price_link = sprintf('<a href="#" onclick="wpla_use_lowest_price(%3$s);return false;" style="color:%1$s">%2$s</a>', $lowest_price_color, $lowest_price, $item['id'] );
            $lowest_price_link = sprintf('<a href="#" data-id="%3$s" style="color:%1$s">%2$s</a>', $lowest_price_color, $lowest_price, $item['id'] );
        } else {
            $lowest_price_link = sprintf('<span style="color:%1$s">%2$s</span>', $lowest_price_color, $lowest_price );
        }

        if ( $has_buybox ) {
            $tip_msg = 'The Buy Box shows your offer.';
            $img_url = WPLA_URL . '/img/icon-success-32x32.png';
            $tip_msg = '&nbsp;<img src="'.$img_url.'" style="height:12px; padding:0;" class="tips" data-tip="'.$tip_msg.'"/>';
            $lowest_price_link .= $tip_msg;
        }

        return sprintf('%1$s<br><small style="color:silver">%2$s</small>',
            /*$2%s*/ $lowest_price_link,
            /*$3%s*/ $last_updated
        );
    }
    
    function column_loffer_price($item){

        $loffer_price = $item['loffer_price'] ? $this->number_format( $item['loffer_price'], 2 ) : '&mdash;';
        // $last_updated = $item['pricing_date'] ? human_time_diff( strtotime($item['pricing_date'].' UTC') ) . ' ago' : '';

        // get prices
        $regular_price    = $this->getPriceForItem( $item );
        $sale_price       = $this->getSalePriceForItem( $item );
        $price_to_compare = $sale_price ? $sale_price : $regular_price;

        $loffer_price_color = 'darkred';
        if ( $item['loffer_price'] >= $item['min_price'] )
            $loffer_price_color = 'orange';
        if ( $item['loffer_price'] >= $price_to_compare )
            $loffer_price_color = 'green';
        if ( ! $item['loffer_price'] )
            $loffer_price_color = '';

        if ( $item['loffer_price'] && isset($_REQUEST['page']) && $_REQUEST['page'] == 'wpla-tools' ) {
            // $loffer_price_link = sprintf('<a href="#" onclick="wpla_use_loffer_price(%3$s);return false;" style="color:%1$s">%2$s</a>', $loffer_price_color, $loffer_price, $item['id'] );
            $loffer_price_link = sprintf('<a href="#" data-id="%3$s" style="color:%1$s">%2$s</a>', $loffer_price_color, $loffer_price, $item['id'] );

            // this might not be required anymore, since we use GetLowestOfferListingsForASIN with ExcludeMe=true now! (maybe?)
            // (what if Amazon does report multiple lowest offers for the same price as our price? this needs to be tested some more...)
            $loffer_data = maybe_unserialize( $item['loffer_data'] );
            $ListingsConsidered = null;
            if ( is_array($loffer_data) ) {
                foreach ($loffer_data as $price) {
                    if ( $price->LandedPrice == $item['loffer_price'] ) {
                        $ListingsConsidered = isset($price->NumberOfOfferListingsConsidered) ? $price->NumberOfOfferListingsConsidered : null;
                    }
                }
                if ( $ListingsConsidered > 1 ) {
                    // $loffer_price_link .= '&nbsp;<span style="color:silver">('.$ListingsConsidered.')</span>';
                    $tip_msg = 'According to the Amazon API, there are currently <b>'.$ListingsConsidered.' listings</b> at this price.<br><br>';
                    $tip_msg .= 'Please note that this data can be unreliable. If you are in fact the only seller at that price, while according to the API there are multiple offers, unfortunately WP-Lister can <em>not</em> increase the price automatically.<br><br>';
                    $tip_msg .= 'In that case, please adjust the minimum price in order to increase the final price on Amazon.';
                    $img_url = WPLA_URL . '/img/info.png';
                    $tip_msg = '&nbsp;<img src="'.$img_url.'" style="height:12px; padding:0;" class="tips" data-tip="'.$tip_msg.'"/>';
                    $loffer_price_link .= $tip_msg;

                }
            }

        } else {
            $loffer_price_link = sprintf('<span style="color:%1$s">%2$s</span>', $loffer_price_color, $loffer_price );
        }

        // generate link to view pricing details
        $view_pricing_info_url  = 'admin-ajax.php?action=wpla_view_pricing_info&id='.$item['id'];
        $view_pricing_info_link = sprintf('<a href="%s" target="_blank" class="thickbox">%s</a>', $view_pricing_info_url, '&raquo;&nbsp;'.__('details','wpla') );

        return sprintf('%1$s<br><small style="color:silver">%2$s</small>',
            /*$2%s*/ $loffer_price_link,
            /*$3%s*/ $view_pricing_info_link
        );
    }
    
    function column_price($item){
        
        // for parent variations variations check each price...
        if ( $item['post_id'] && WPLA_ProductWrapper::hasVariations( $item['post_id'] ) ) {

            $variations = $this->getProductVariations( $item['post_id'] );
            if ( ! is_array($variations) || ! sizeof($variations) ) return '';

            $price_min = PHP_INT_MAX;
            $price_max = 0;
            foreach ($variations as $var) {
                $price = $var['price'];
                if ( $price > $price_max ) $price_max = $price;
                if ( $price < $price_min ) $price_min = $price;
            }

            if ( $price_min == $price_max ) {
                return $this->number_format( $price_min, 2 );
            } else {
                return $this->number_format( $price_min, 2 ) . ' - ' . $this->number_format( $price_max, 2 );
            }
        } // parent variation


        // get prices
        $regular_price  = $this->getPriceForItem( $item );
        $sale_price     = $this->getSalePriceForItem( $item );

        $profile = $this->getProfile( $item['profile_id'] );

        // check for profile prices (standard_price or price) and run the prices through WPLA_FeedDataBuilder::parseProfileShortcode()
        // to substitute shortcodes with actual values #20263
        if ( !empty( $profile->fields['standard_price'] ) ) {
            $price = WPLA_FeedDataBuilder::parseProfileShortcode( $profile->fields['standard_price'], $profile->fields['standard_price'], $item, wc_get_product( $item['post_id'] ), $item['post_id'], $profile );
            return $this->number_format( $price, 2 );
        } elseif ( !empty( $profile->fields['price'] ) ) {
            $price = WPLA_FeedDataBuilder::parseProfileShortcode( $profile->fields['price'], $profile->fields['price'], $item, wc_get_product( $item['post_id'] ), $item['post_id'], $profile );
            return $this->number_format( $price, 2 );
        }

        // no sale price
        if ( ! $sale_price )
            return $this->number_format( $regular_price, 2 );

        // show sale price
        return sprintf('<span style="%1$s">%2$s</span> <br><span style="">%3$s</span>',
            /*$1%s*/ 'text-decoration: line-through;color:silver',
            /*$2%s*/ $this->number_format( $regular_price, 2 ),
            /*$3%s*/ $this->number_format( $sale_price, 2 )
        );            

    }
    
  
    function getPriceForItem($item) {        
        if ( ! $item['post_id'] ) return $item['price'];
        $post_id = $item['post_id'];

        $product = $this->getProduct( $post_id );
        $profile = $this->getProfile( $item['profile_id'] );
        if ( ! $product ) return;

        $value   =  wpla_get_product_meta( $product, 'regular_price' );          // WC2.0 compat
        // $value   = method_exists( $product, 'get_display_price' ) ? $product->get_display_price( $value ) : $value; // maybe apply taxes (only works if taxes are calculated based on shop base address)
        $value   = $profile ? $profile->processProfilePrice( $value ) : $value;
        $value   = apply_filters( 'wpla_filter_product_price', $value, $post_id, $product, $item, $profile );

        // check for custom product price
        $product_price = get_post_meta( $item['post_id'], '_amazon_price', true );
        if ( $product_price > 0 ) $value = $product_price;

        return $value;
    }
    
    function getSalePriceForItem($item) {        
        if ( ! $item['post_id'] ) return false;
        $post_id = $item['post_id'];

        $product = $this->getProduct( $post_id );
        $profile = $this->getProfile( $item['profile_id'] );
        if ( ! $product ) return;

        $value   = wpla_get_product_meta( $product, 'sale_price' );          // WC2.0 compat
        $value   = $profile ? $profile->processProfilePrice( $value ) : $value;
        $value   = apply_filters( 'wpla_filter_sale_price', $value, $post_id, $product, $item, $profile );

        return $value;
    }
    
    function getMSRPriceForItem($item) {        
        if ( ! $item['post_id'] ) return false;
        $post_id = $item['post_id'];

        $price = get_post_meta( $post_id, '_msrp', true ) ? get_post_meta( $post_id, '_msrp', true ) : get_post_meta( $post_id, '_msrp_price', true );

        return $price;
    }
    
  
    function column_account($item){        

        $account = WPLA()->memcache->getAccount( $item['account_id'] );

        if ( ! $account )
            return '<span style="color:red">No Account!</span>';

        return $account->title . ' (' . $account->market_code . ')';
    }
	
    function column_status($item){
        $tooltip = '';

        switch( $item['status'] ){
            case 'prepared':
                $color = 'orange';
                $value = __('prepared','wpla');
				break;
            case 'matched':
                $color = '#21759B';
                $value = __('matched','wpla');
                break;
            case 'submitted':
                $color = '#21759B';
                $value = __('submitted','wpla');
				break;
            case 'online':
                $color = 'darkgreen';
                $value = __('online','wpla');
                if ( $item['quantity'] < 1 && $item['fba_quantity'] < 1 ) {
                    $color = '#66A266'; // less dark green
                    $value = __('sold out','wpla');
                }
                if ( $item['product_type'] == 'variable' ) {
                    $color = 'silver'; // less dark green
                    $value = __('variable','wpla');
                }
                break;
            case 'failed':
                $color = 'darkred';
                $value = __('failed','wpla');
                $tips  = array();
                $history = maybe_unserialize( $item['history'] );
                if ( is_array( $history ) ) {
                    foreach ($history['errors'] as $result) {
                        $tips[] = $result['error-type'].' '.$result['error-code'].': '.esc_attr( $result['error-message'] );
                    }
                    // foreach ($history['warnings'] as $result) {
                    //     $tips[] = $result['error-type'].' '.$result['error-code'].': '.esc_attr( $result['error-message'] );
                    // }
                }
                $tooltip = 'class="wide_error_tip" data-tip="'.join('<hr>',$tips).'"';
				break;
            case 'sold':
                $color = 'black';
                $value = __('sold','wpla');
                break;
            case 'ended':
                $color = '#777';
                $value = __('ended','wpla');
                break;
            case 'trash':
            case 'trashed':
                $color = '#777';
                $value = $item['status'];
                break;
            case 'imported':
                $color = 'orange';
                $value = __('queued','wpla');
				break;
            case 'selected':
                $color = 'orange';
                $value = __('selected','wpla');
                break;
            case 'changed':
                $color = 'purple';
                $value = __('changed','wpla');
                break;
            case 'relisted':
                $color = 'purple';
                $value = __('relisted','wpla');
                break;
            default:
                $color = 'black';
                $value = $item['status'];
        }

        //Return the title contents
        return sprintf('<mark id="listing-status-%4$s" style="background-color:%1$s" %2$s>%3$s</mark>',
            /*$1%s*/ $color,
            /*$2%s*/ $tooltip,
            /*$3%s*/ $value,
            /*$4%s*/ $item['id']
        );
	}
	  
    function column_profile($item){

        if ( ! $item['profile_id'] ) return '&mdash;';
        
        $profile_name  = @$this->profiles[  $item['profile_id'] ];
        $template_name = @$this->templates[ $item['profile_id'] ];

        if ( ! $profile_name ) {
            // $profile_name = '<span style="color:red;">'. __('Profile missing','wpla') .'!</span>';
            // if ( $item['profile_id'] ) $profile_name .= ' (' . $item['profile_id'] . ')';
            // return $profile_name;
            return '&mdash;';
        }

        $edit_url = "admin.php?page=wpla-profiles&action=edit";
        $edit_url = add_query_arg( 'profile', $item['profile_id'], $edit_url );
        $edit_url = add_query_arg( 'return_to', 'listings', $edit_url );

        if ( isset($_REQUEST['s']) )                $edit_url = add_query_arg( 's',              $_REQUEST['s'],              $edit_url );
        if ( isset($_REQUEST['listing_status']) )   $edit_url = add_query_arg( 'listing_status', $_REQUEST['listing_status'], $edit_url );
        if ( isset($_REQUEST['profile_id']) )       $edit_url = add_query_arg( 'profile_id',     $_REQUEST['profile_id'],     $edit_url );
        if ( isset($_REQUEST['account_id']) )       $edit_url = add_query_arg( 'account_id',     $_REQUEST['account_id'],     $edit_url );

        return sprintf(
            '<a href="%1$s" title="%2$s">%3$s</a><br><small style="color:silver">%4$s</small>',
            /*$1%s*/ $edit_url,  
            /*$2%s*/ __('Edit','wpla'),  
            /*$3%s*/ $profile_name,        
            /*$4%s*/ $template_name        
        );
    }
    
    function column_template($item){

        $template_id = basename( $item['template'] );
        $template_name = TemplatesModel::getNameFromCache( $template_id );

        if ( ! $template_name ) {
            $template_name = '<span style="color:red;">'. __('Template missing','wpla') .'!</span>';
            if ( $template_id ) $template_name .= ' (' . $template_id . ')';
            return $template_name;
        }

        return sprintf(
            '<a href="admin.php?page=wpla-templates&action=edit&template=%1$s&return_to=listings" title="%2$s">%3$s</a>',
            /*$1%s*/ $template_id,  
            /*$2%s*/ __('Edit','wpla'),  
            /*$3%s*/ $template_name        
        );
    }

    function column_img($item) {
        $post_id = $item['post_id'];
        if ( ! $post_id ) return '';

        $thumb = get_the_post_thumbnail( $post_id, 'thumbnail' );
        // $link  = 'admin.php?page=wpla&action=preview_listing&listing='.$item['id'].'&width=820&height=550&TB_iframe=true';
        // $thumb_link = '<a href="'.$link.'" class="thickbox">'.$thumb.'</a>';

        return $thumb;
    }
      
    function column_sku($item){

        $item_sku = $item['sku'];
        $prod_sku = WPLA_ProductWrapper::getSKU( $item['post_id'] );
        $sku      = $item_sku;

        // check for missing SKU
        if ( ! $item_sku && ! $prod_sku ) {
            $sku = 'No SKU';
            $sku = '<span style="color:darkred">'.$sku.'</span>';            
        }

        // check if SKU in WooCommerce and WP-Lister are the same
        if ( $item_sku !== $prod_sku && $item['status'] != 'imported' && ! empty($prod_sku) ) {
            $tip_msg = 'The SKU for this item is different in WooCommerce. You need to make sure the SKU is the same as on Amazon or feed processing will fail.';
            $img_url = WPLA_URL . '/img/error.gif';
            $tip_msg = '&nbsp;<img src="'.$img_url.'" style="height:12px; padding:0;" class="tips" data-tip="'.$tip_msg.'"/>&nbsp;';

            $sku  = 'Warning'.$tip_msg;
            $sku .= '<br>'.$item_sku;
            $sku .= '<br>'.$prod_sku;
            $sku .= '<br>SKU mismatch!';

            $sku = '<span style="color:darkred">'.$sku.'</span>';            
        }

        // check if SKU in WooCommerce is empty
        if ( $item_sku !== $prod_sku && $item['status'] != 'imported' && empty($prod_sku) ) {
            $tip_msg = 'The SKU for this product is empty in WooCommerce.<br><br> You need to make sure each product and each variation has a unique SKU. This SKU needs to be same as on Amazon or feed processing will fail.';
            $img_url = WPLA_URL . '/img/error.gif';
            $tip_msg = '&nbsp;<img src="'.$img_url.'" style="height:12px; padding:0;" class="tips" data-tip="'.$tip_msg.'"/>&nbsp;';

            $sku  = 'No SKU'.$tip_msg;
            $sku .= '<br>'.$item_sku;

            // show warning if WooCommerce product has been deleted
            if ( ! $this->getProduct( $item['post_id'] ) && ($item['status'] != 'imported') ) {
                $sku = 'No Product!';
            }

            $sku = '<span style="color:darkred">'.$sku.'</span>';            
        }

        // if item has variations count them...
        if ( ( $item['post_id'] ) && WPLA_ProductWrapper::hasVariations( $item['post_id'] ) ) {

            $variations = $this->getProductVariations( $item['post_id'] );

            foreach ($variations as $var) {
                if ( ! $var['sku'] ) {
                    $sku = '<br><span style="color:darkred">'.'Missing variable SKUs'.'</span>';
                }
            }
        }      

        // check for FNSKU
        $details = $item['details'] ? json_decode( $item['details'] ) : false;
        $fnsku   = '';
        if ( $details && is_object( $details ) && ! empty( $details->fnsku ) ) {
            $fnsku = '<br><span style="color:silver" title="FNSKU">'.$details->fnsku.'</span>';
        }

        //Return the title contents
        return sprintf('%1$s<br><span style="color:silver" title="ASIN">%2$s</span>%3$s',
            /*$1%s*/ $sku,
            /*$2%s*/ $item['asin'],
            /*$3%s*/ $fnsku
        );
    }

    // get profile object - if possible from cache
    function getProfile( $profile_id ) {

        // update cache if required
        if ( $this->last_profile_id != $profile_id ) {
            $this->last_profile_object = new WPLA_AmazonProfile( $profile_id );
            $this->last_profile_id     = $profile_id;
        }

        return $this->last_profile_object;
    }
        
    // get product object - if possible from cache
    function getProduct( $post_id ) {

        // update cache if required
        if ( $this->last_product_id != $post_id ) {
            $this->last_product_object = wc_get_product( $post_id );
            $this->last_product_id     = $post_id;
        }

        return $this->last_product_object;
    }
        
    // get product variations - if possible from cache
    function getProductVariations( $post_id ) {

        // update cache if required
        if ( $this->last_product_var_id != $post_id ) {
            $this->last_product_variations = WPLA_ProductWrapper::getVariations( $post_id );
            $this->last_product_var_id         = $post_id;
        }

        return $this->last_product_variations;
    }
        
    /** ************************************************************************
     * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
     * is given special treatment when columns are processed. It ALWAYS needs to
     * have it's own method.
     * 
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (profile title only)
     **************************************************************************/
    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("listing")
            /*$2%s*/ $item['id']       			//The value of the checkbox should be the record's id
        );
    }
    
    
    /** ************************************************************************
     * REQUIRED! This method dictates the table's columns and titles. This should
     * return an array where the key is the column slug (and class) and the value 
     * is the column's title text. If you need a checkbox for bulk actions, refer
     * to the $columns array below.
     * 
     * The 'cb' column is treated differently than the rest. If including a checkbox
     * column in your table you must create a column_cb() method. If you don't need
     * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
     * 
     * @see WP_List_Table::::single_row_columns()
     * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_columns(){
        $columns = array(
            'cb'        		=> '<input type="checkbox" />', //Render a checkbox instead of text
            'img'               => __('Image','wpla'),
            'sku'               => __('SKU','wpla'),
            'listing_title' 	=> __('Title','wpla'),
            'quantity'			=> __('Stock','wpla'),
            // 'quantity_sold'		=> __('Sold','wpla'),
            'price'             => __('Price','wpla'),
            'lowest_price'      => str_replace(' ', '&nbsp;', __('Buy Box','wpla') ),
            'loffer_price'		=> __('Lowest Offer','wpla'),
            // 'fees'				=> __('Fees','wpla'),
            'date_published'    => str_replace(' ', '&nbsp;', __('Created at','wpla') ),
            'profile'           => __('Profile','wpla'),
            'account'           => __('Account','wpla'),
            'status'		 	=> __('Status','wpla')
        );

        if ( ! get_option( 'wpla_enable_thumbs_column' ) )
            unset( $columns['img'] );

        return $columns;
    }
    
    /** ************************************************************************
     * Optional. If you want one or more columns to be sortable (ASC/DESC toggle), 
     * you will need to register it here. This should return an array where the 
     * key is the column that needs to be sortable, and the value is db column to 
     * sort by. Often, the key and value will be the same, but this is not always
     * the case (as the value is a column name from the database, not the list table).
     * 
     * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
     **************************************************************************/
    function get_sortable_columns() {
        $sortable_columns = array(
            'date_published'  	=> array('date_published',false),     //true means its already sorted
            'listing_title'     => array('listing_title',false),
            'quantity'          => array('quantity',false),
            'price'             => array('price',false),
            'status'            => array('status',false)
        );
        return $sortable_columns;
    }
    
    
    /** ************************************************************************
     * Optional. If you need to include bulk actions in your list table, this is
     * the place to define them. Bulk actions are an associative array in the format
     * 'slug'=>'Visible Title'
     * 
     * Also note that list tables are not automatically wrapped in <form> elements,
     * so you will need to create those manually in order for bulk actions to function.
     * 
     * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_bulk_actions() {
        $actions = array(
            // 'update' 	=> __('Update details from Amazon','wpla'), // TODO
            // 'reapply'   => __('Re-apply profile','wpla'),
            // 'end_item'  => __('Pause listings','wpla'),
            'wpla_get_compet_price'    => __('Fetch latest prices from Amazon','wpla'),
            'wpla_resubmit'            => __('Resubmit items','wpla'),
            'wpla_change_profile'      => __('Change profile','wpla'),
            'wpla_trash_listing'       => __('Remove from Amazon','wpla'),
            'wpla_delete'              => __('Delete from database','wpla'),
            'wpla_get_lowest_offers'   => __('Get lowest offer listings','wpla') . ' (beta)',
            'wpla_fetch_pdescription'  => __('Fetch full description','wpla') . ' (beta)',
        );
        return $actions;
    }
    
    
    /** ************************************************************************
     * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
     * For this example package, we will handle it in the class to keep things
     * clean and organized.
     * 
     * @see $this->prepare_items()
     **************************************************************************/
    function process_bulk_action() {
        global $wbdb;
        
        //Detect when a bulk action is being triggered...
        if( 'delete'===$this->current_action() ) {
            #wp_die('Items deleted (or they would be if we had items to delete)!');
            #$wpdb->query("DELETE FROM {$wpdb->prefix}amazon_listings WHERE id = ''",)
        }

        if( 'verify'===$this->current_action() ) {
			#echo "<br>verify handler<br>";			
        }
        
    }

    // status filter links
    // http://wordpress.stackexchange.com/questions/56883/how-do-i-create-links-at-the-top-of-wp-list-table
    function get_views(){
        $views    = array();
        $current_listing_status   = ( ! empty($_REQUEST['listing_status']   ) ? $_REQUEST['listing_status']   : 'all');
        $current_stock_status     = ( ! empty($_REQUEST['stock_status']     ) ? $_REQUEST['stock_status']     : 'all');
        $current_fba_status       = ( ! empty($_REQUEST['fba_status']       ) ? $_REQUEST['fba_status']       : 'all');
        // $base_url = remove_query_arg( array( 'action', 'listing', 'listing_status' ) );
        $base_url = esc_url_raw( remove_query_arg( array( 'action', 'listing' ) ) );

        // handle search query
        if ( isset($_REQUEST['s']) && $_REQUEST['s'] ) {
            $base_url = add_query_arg( 's', $_REQUEST['s'], $base_url );
        }
        // handle profile_id query
        if ( isset($_REQUEST['profile_id']) && $_REQUEST['profile_id'] ) {
            $base_url = add_query_arg( 'profile_id', $_REQUEST['profile_id'], $base_url );
        }
        // handle account_id query
        if ( isset($_REQUEST['account_id']) && $_REQUEST['account_id'] ) {
            $base_url = add_query_arg( 'account_id', $_REQUEST['account_id'], $base_url );
        }


        // get listing status summary
        $summary = WPLA_ListingsModel::getStatusSummary();

        // All link
        $class = ($current_listing_status == 'all' ? ' class="current"' :'');
        $all_url = remove_query_arg( 'listing_status', $base_url );
        $views['all']  = "<a href='{$all_url }' {$class} >".__('All','wpla')."</a>";
        $views['all'] .= '<span class="count">('.$summary->total_items.')</span>';

        // prepared link
        $prepared_url = add_query_arg( 'listing_status', 'prepared', $base_url );
        $class = ($current_listing_status == 'prepared' ? ' class="current"' :'');
        $views['prepared'] = "<a href='{$prepared_url}' {$class} >".__('Prepared','wpla')."</a>";
        if ( isset($summary->prepared) ) $views['prepared'] .= '<span class="count">('.$summary->prepared.')</span>';

        // online link
        $online_url = add_query_arg( 'listing_status', 'online', $base_url );
        $class = ($current_listing_status == 'online' ? ' class="current"' :'');
        $views['online'] = "<a href='{$online_url}' {$class} >".__('Online','wpla')."</a>";
        if ( isset($summary->online) ) $views['online'] .= '<span class="count">('.$summary->online.')</span>';

        // changed link
        $changed_url = add_query_arg( 'listing_status', 'changed', $base_url );
        $class = ($current_listing_status == 'changed' ? ' class="current"' :'');
        $views['changed'] = "<a href='{$changed_url}' {$class} >".__('Changed','wpla')."</a>";
        if ( isset($summary->changed) ) $views['changed'] .= '<span class="count">('.$summary->changed.')</span>';

        // matched link
        $matched_url = add_query_arg( 'listing_status', 'matched', $base_url );
        $class = ($current_listing_status == 'matched' ? ' class="current"' :'');
        $views['matched'] = "<a href='{$matched_url}' {$class} >".__('Matched','wpla')."</a>";
        if ( isset($summary->matched) ) $views['matched'] .= '<span class="count">('.$summary->matched.')</span>';

        // submitted link
        if ( isset($summary->submitted) ) {
            $submitted_url = add_query_arg( 'listing_status', 'submitted', $base_url );
            $class = ($current_listing_status == 'submitted' ? ' class="current"' :'');
            $views['submitted'] = "<a href='{$submitted_url}' {$class} >".__('Submitted','wpla')."</a>";
            $views['submitted'] .= '<span class="count">('.$summary->submitted.')</span>';
        }

        // ended link
        // $ended_url = add_query_arg( 'listing_status', 'ended', $base_url );
        // $class = ($current_listing_status == 'ended' ? ' class="current"' :'');
        // $views['ended'] = "<a href='{$ended_url}' {$class} >".__('Ended','wpla')."</a>";
        // if ( isset($summary->ended) ) $views['ended'] .= '<span class="count">('.$summary->ended.')</span>';

        // sold link
        if ( isset($summary->sold) ) {
            $sold_url = add_query_arg( 'listing_status', 'sold', $base_url );
            $class = ($current_listing_status == 'sold' ? ' class="current"' :'');
            $views['sold'] = "<a href='{$sold_url}' {$class} >".__('Sold','wpla')."</a>";
            $views['sold'] .= '<span class="count">('.$summary->sold.')</span>';
        }

        // failed link
        if ( isset($summary->failed) ) {
           $failed_url = add_query_arg( 'listing_status', 'failed', $base_url );
           $class = ($current_listing_status == 'failed' ? ' class="current"' :'style="color:darkred"');
           $views['failed'] = "<a href='{$failed_url}' {$class} >".__('Failed','wpla')."</a>";
           $views['failed'] .= '<span class="count">('.$summary->failed.')</span>';       
        }

        // trash link
        if ( isset($summary->trash) ) {
           $trash_url = add_query_arg( 'listing_status', 'trash', $base_url );
           $class = ($current_listing_status == 'trash' ? ' class="current"' :'');
           $views['trash'] = "<a href='{$trash_url}' {$class} >".__('Trash','wpla')."</a>";
           $views['trash'] .= '<span class="count">('.$summary->trash.')</span>';       
        }

        // trashed link
        if ( isset($summary->trashed) ) {
           $trashed_url = add_query_arg( 'listing_status', 'trashed', $base_url );
           $class = ($current_listing_status == 'trashed' ? ' class="current"' :'');
           $views['trashed'] = "<a href='{$trashed_url}' {$class} >".__('Trashed','wpla')."</a>";
           $views['trashed'] .= '<span class="count">('.$summary->trashed.')</span>';       
        }

        // imported link (Import Queue)
        if ( isset($summary->imported) ) {
           $imported_url = add_query_arg( 'listing_status', 'imported', $base_url );
           $class = ($current_listing_status == 'imported' ? ' class="current"' :'');
           $views['imported'] = "<a href='{$imported_url}' {$class} >".__('Import Queue','wpla')."</a>";
           $views['imported'] .= '<span class="count">('.$summary->imported.')</span>';       
        }

        // Quality
        if ( ! empty($summary->quality_alert) ) {
            $quality_alert_url = add_query_arg( 'listing_status', 'quality_alert', $base_url );
            $class = ($current_listing_status == 'quality_alert' ? ' class="current"' :'style="color:darkred"');
            $views['quality_alert'] = "<a href='{$quality_alert_url}' {$class} >".__('Quality','wpla')."</a>";
            $views['quality_alert'] .= '<span class="count">('.$summary->quality_alert.')</span>';       
        }


        // In Stock
        $is_in_stock_url = add_query_arg( 'stock_status', 'is_in_stock', $base_url );
        $class = ($current_stock_status == 'is_in_stock' ? ' class="current"' :'');
        $views['is_in_stock'] = "<a href='{$is_in_stock_url}' {$class} >".__('In Stock','wpla')."</a>";

        // No Stock
        $is_not_in_stock_url = add_query_arg( 'stock_status', 'is_not_in_stock', $base_url );
        $class = ($current_stock_status == 'is_not_in_stock' ? ' class="current"' :'');
        $views['is_not_in_stock'] = "<a href='{$is_not_in_stock_url}' {$class} >".__('No Stock','wpla')."</a>";

        if ( ! get_option( 'wpla_fba_enabled' ) )
            return $views;


        // FBA
        $is_fba_url = add_query_arg( 'fba_status', 'is_fba', $base_url );
        $class = ($current_fba_status == 'is_fba' ? ' class="current"' :'');
        $tooltip = 'Show only FBA enabled items. Since parent variations do not exist on FBA, they will not show up in this view.';
        $views['is_fba'] = "<a href='{$is_fba_url}' title='{$tooltip}' {$class} >".__('FBA','wpla')."</a>";
        $views['is_fba'] .= '<span class="count">('.$summary->is_fba.')</span>';       

        // Non-FBA
        $is_not_fba_url = add_query_arg( 'fba_status', 'is_not_fba', $base_url );
        $class = ($current_fba_status == 'is_not_fba' ? ' class="current"' :'');
        $tooltip = 'Show only non-FBA items - but hide parent variations.';
        $views['is_not_fba'] = "<a href='{$is_not_fba_url}' title='{$tooltip}' {$class} >".__('Non-FBA','wpla')."</a>";
        $views['is_not_fba'] .= '<span class="count">('.$summary->is_not_fba.')</span>';       

        return $views;
    }    

    function extra_tablenav( $which ) {
        if ( 'top' != $which ) return;
        $wpl_profiles = WPLA_AmazonProfile::getAll();
        $wpl_accounts = WPLA()->accounts;
        $profile_id   = ( isset($_REQUEST['profile_id']) ? $_REQUEST['profile_id'] : false);
        $account_id   = ( isset($_REQUEST['account_id']) ? $_REQUEST['account_id'] : false);
        // echo "<pre>";print_r($wpl_profiles);echo"</pre>";die();
        ?>
        <div class="alignleft actions" style="">

            <select name="profile_id">
                <option value=""><?php _e('All profiles','wpla') ?></option>
                <option value="_NONE_" <?php if ( $profile_id == '_NONE_' ) echo 'selected'; ?> ><?php _e('No profile','wpla') ?></option>
                <?php foreach ($wpl_profiles as $profile) : ?>
                    <option value="<?php echo $profile->profile_id ?>"
                        <?php if ( $profile_id == $profile->profile_id ) echo 'selected'; ?>
                        ><?php echo $profile->profile_name ?></option>
                <?php endforeach; ?>
            </select>            

            <select name="account_id">
                <option value=""><?php _e('All accounts','wpla') ?></option>
                <?php foreach ($wpl_accounts as $account) : ?>
                    <option value="<?php echo $account->id ?>"
                        <?php if ( $account_id == $account->id ) echo 'selected'; ?>
                        ><?php echo $account->title ?> (<?php echo $account->market_code ?>)</option>
                <?php endforeach; ?>
            </select>            

            <input type="submit" name="" id="post-query-submit" class="button" value="Filter">

        </div>
        <?php
    }

    /**
     * Generates the table navigation above or bellow the table and removes the
     * _wp_http_referer and _wpnonce because it generates a error about URL too large
     * 
     * @param string $which 
     * @return void
     */
    function display_tablenav( $which ) {
        $mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : 'list';
        ?>
        <div class="tablenav <?php echo esc_attr( $which ); ?>">
            <div class="alignleft actions">
                <?php $this->bulk_actions(); ?>
            </div>
            <?php
                $this->extra_tablenav( $which );
                $this->pagination( $which );
                $this->view_switcher( $mode );
            ?>
            <br class="clear" />
        </div>
        <?php
    }
       
    
    /** ************************************************************************
     * REQUIRED! This is where you prepare your data for display. This method will
     * usually be used to query the database, sort and filter the data, and generally
     * get it ready to be displayed. At a minimum, we should set $this->items and
     * $this->set_pagination_args(), although the following properties and methods
     * are frequently interacted with here...
     * 
     * @uses $this->_column_headers
     * @uses $this->items
     * @uses $this->get_columns()
     * @uses $this->get_sortable_columns()
     * @uses $this->get_pagenum()
     * @uses $this->set_pagination_args()
     **************************************************************************/
    function prepare_items( $items = false ) {
        
        // process bulk actions
        $this->process_bulk_action();
                        
        // get pagination state
        $current_page = $this->get_pagenum();
        $per_page = $this->get_items_per_page('listings_per_page', 20);
        
        // define columns
        $this->_column_headers = $this->get_column_info();
        
        // fetch listings from model - if no parameter passed
        if ( ! $items ) {

            $listingsModel = new WPLA_ListingsModel();
            $this->items = $listingsModel->getPageItems( $current_page, $per_page );
            $this->total_items = $listingsModel->total_items;

        } else {

            $this->items = $items;
            $this->total_items = count($items);

        }

        // register our pagination options & calculations.
        $this->set_pagination_args( array(
            'total_items' => $this->total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($this->total_items/$per_page)
        ) );

    }

    // small helper to make sure $price is not a string    
    function number_format( $price, $decimals = 2 ) {
        return number_format_i18n( floatval($price), $decimals );
    }
    
    
}

