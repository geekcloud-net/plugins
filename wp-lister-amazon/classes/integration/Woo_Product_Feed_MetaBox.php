<?php
/**
 * add amazon feed attributes metaboxes to product edit page
 */

class WPLA_Product_Feed_MetaBox {

	function __construct() {

		add_action( 'add_meta_boxes', array( &$this, 'add_meta_box' ) );
		add_action( 'woocommerce_process_product_meta', array( &$this, 'save_meta_box' ), 0, 2 );

        // add_action( 'wp_ajax_wpla_update_custom_feed_columns', 	array( &$this, 'wpla_update_custom_feed_columns' ) ); 

	}

	function add_meta_box() {

		$title = __('Amazon Feed Attributes', 'wpla');
		add_meta_box( 'wpla-amazon-feed_columns', $title, array( &$this, 'meta_box_feed_columns' ), 'product', 'normal', 'default');

	}

	function meta_box_feed_columns() {
		global $post;

		$this->add_inline_js();
		$this->add_inline_css();

		$this->display_feed_template_selector();

        // get custom feed_columns as array of attachment_ids
		// $custom_feed_tpl_id  = get_post_meta( $post->ID, '_wpla_custom_feed_tpl_id' , true );
		// $custom_feed_columns = get_post_meta( $post->ID, '_wpla_custom_feed_columns', true );
		// echo "<pre>";print_r($custom_feed_columns);echo"</pre>";#die();        

	} // meta_box_feed_columns()


	function display_feed_template_selector() {
		global $post;

		// get templates
		$templates          = WPLA_AmazonFeedTemplate::getAll();
		$custom_feed_tpl_id = get_post_meta( $post->ID, '_wpla_custom_feed_tpl_id' , true );

		// separate ListingLoader templates
		$category_templates = array();
		$liloader_templates = array();
		foreach ($templates as $tpl) {
			if ( $tpl->title == 'Offer' ) {
				$tpl->title = "Listing Loader";
				$liloader_templates[] = $tpl;
			} else {
				$category_templates[] = $tpl;
			}
		}

		// compatibility with profile code
		$wpl_category_templates = $category_templates;
		$wpl_liloader_templates = $liloader_templates;

		?>
							<label for="wpl-text-tpl_id" class="text_label">
								<?php echo __('Feed Template','wpla'); ?>
                                <?php wpla_tooltip('Each main category on Amazon uses a different feed template with special fields for that particular category.<br>You need to select the right template for your category and make sure all the required fields are filled in - or are populated from product details or attributes.') ?>
							</label>
							<select id="wpl-text-tpl_id" name="wpla_tpl_id" class="required-entry select">
							<option value="">-- <?php echo __('Select feed template','wpla') ?> --</option> 
							<optgroup label="Generic Feeds">
								<?php foreach ( $wpl_liloader_templates as $tpl ) : ?>
									<option value="<?php echo $tpl->id ?>" 
										<?php if ( $custom_feed_tpl_id == $tpl->id ) : ?>
											selected="selected"
										<?php endif; ?>
										<?php $site = WPLA()->memcache->getMarket( $tpl->site_id ); ?>
										><?php echo $tpl->title ?> (<?php echo $site ? $site->code : '?' ?>)</option>
								<?php endforeach; ?>
							</optgroup>
							<optgroup label="Category Specific Feeds">
								<?php foreach ( $wpl_category_templates as $tpl ) : ?>
									<option value="<?php echo $tpl->id ?>" 
										<?php if ( $custom_feed_tpl_id == $tpl->id ) : ?>
											selected="selected"
										<?php endif; ?>
										<?php $site = WPLA()->memcache->getMarket( $tpl->site_id ); ?>
										><?php echo $tpl->title ?> (<?php echo $site ? $site->code : '?' ?>)</option>
								<?php endforeach; ?>
							</optgroup>
							</select>
							<br class="clear" />
							<p class="desc" style="">
								<?php $link = sprintf( '<a href="%s">%s</a>', 'admin.php?page=wpla-settings&tab=categories', __('Amazon &raquo; Settings &raquo; Categories','wpla') ); ?>
								<?php echo sprintf( __('You can add additional feed templates at %s.','wpla'), $link ); ?>
							</p>


					<div id="FeedDataBox">
						<hr>
						<!-- <h3 class="hndle"><span><?php echo __('Feed Attributes','wpla'); ?></span></h3> -->
						<div class="x-inside" id="wpla_feed_data_wrapper">
						</div>
					</div>

					<!-- hidden ajax categories tree -->
					<div id="amazon_categories_tree_wrapper">
						<div id="amazon_categories_tree_container">TEST</div>
					</div>

		<?php
	} // display_feed_template_selector()


	function add_inline_css() {
		?>
			<style type="text/css">
				#wpla-amazon-feed_columns p.desc {
					font-size: smaller;
					font-style: italic;
					margin-top: 0;
					margin-left: 35%;
				}
				#wpla-amazon-feed_columns label.text_label {
					display: block;
					float: left;
					width: 33%;
					margin: 1px;
					padding: 3px;
					/*white-space:nowrap;*/
				}
				#wpla-amazon-feed_columns input.text_input,
				#wpla-amazon-feed_columns textarea,
				#wpla-amazon-feed_columns select.select {
					width: 65%;
					margin-bottom: 5px;
					padding: 3px 8px;
				}
				#feed-template-data {
					width: 100%;
				}


				/* BTG selector */
				#amazon_categories_tree_wrapper {
					/*max-height: 320px;*/
					/*margin-left: 35%;*/
					overflow: auto;
					width: 65%;
					display: none;
				}


				/* Tooltips */
				#wpla-amazon-feed_columns img.help_tip {
					vertical-align: bottom;
					float: right;
					margin: 0;
					margin-top: 2px;
				}

				#wpla-amazon-feed_columns th img.help_tip {
					float: none;
					margin: -2px;
				}


			</style>
		<?php
	} // add_inline_css()


	function add_inline_js() {
		global $post;
        wc_enqueue_js("

			// load template data
			function loadTemplateData() {
				var tpl_id = jQuery('#wpl-text-tpl_id')[0].value;
				var post_id = '{$post->ID}';

				// jQuery('#wpla_feed_data_wrapper').slideUp(500);
				// jQuery('#FeedDataBox .loadingMsg').slideDown(500);
				jQuery('#wpla_feed_data_wrapper').html('<p><i>loading feed template...</i></p>');

		        // fetch category conditions
		        var params = {
		            action: 'wpla_load_template_data_for_product',
		            tpl_id: tpl_id,
		            post_id: post_id,
		            nonce: 'TODO'
		        };

		        var jqxhr = jQuery('#wpla_feed_data_wrapper').load( ajaxurl, params, function( response, status, xhr ) {
					if ( status == 'error' ) {
				    	var msg = 'Sorry but there was an error: ';
				    	jQuery( '#error' ).html( msg + xhr.status + ' ' + xhr.statusText );
				  	} else {
			
						// init tooltips
						jQuery('#FeedDataBox .help_tip').tipTip({
					    	'attribute' : 'data-tip',
					    	'maxWidth' : '250px',
					    	'fadeIn' : 50,
					    	'fadeOut' : 50,
					    	'delay' : 200
					    });

				  	}
				});
		        // console.log('jqxhr',jqxhr);

			}


			// init 
			jQuery( document ).ready( function () {
				
				jQuery('#wpl-text-tpl_id').change(function() {
					if ( jQuery('#wpl-text-tpl_id').val() != '' ) {
						loadTemplateData();
					}
				});
				jQuery('#wpl-text-tpl_id').change();



				// jqueryFileTree - amazon categories / browse tree guide
			    jQuery('#amazon_categories_tree_container').fileTree({
			        root: '/0/',
			        script: ajaxurl+'?action=wpla_get_amazon_categories_tree',
			        expandSpeed: 400,
			        collapseSpeed: 400,
			        loadMessage: 'loading browse tree guide...',
			        multiFolder: false
			    }, function(catpath) {

					// console.log('catpath: ',catpath);

					// get cat id from full path
			        var cat_id = catpath.split('/').pop(); // get last item - like php basename()

			        var cat_array = catpath.split('/');
			        if ( cat_array[ cat_array.length - 1 ] == '' ) {
			        	cat_id = cat_array[ cat_array.length - 2 ];
			        }

			        // get name of selected category
			        // var cat_name = '';

			        // var pathname = wpl_getCategoryPathName( catpath.split('/') );
			        // var pathname = catpath;
					// console.log('cat_id: ',cat_id);

					// insert shortcode / value
					wpla_insert_selected_browse_node( cat_id );

			        // update fields
			        // jQuery('#amazon_category_id_'+wpla_selecting_cat).attr( 'value', cat_id );
			        // jQuery('#amazon_category_name_'+wpla_selecting_cat).html( pathname );
			        
			        // close thickbox
			        // tb_remove();


			    });
	
			});	

	    ");
	} // add_inline_js()


	function save_meta_box( $post_id, $post ) {

		if ( isset( $_POST['wpla_tpl_id'] ) ) {
            // update selected template
            update_post_meta( $post_id, '_wpla_custom_feed_tpl_id',	esc_attr( $_POST['wpla_tpl_id'] ) );

            if ( empty( $_POST['wpla_tpl_id'] ) ) {
                // delete the custom feed columns if no template is set at the product level
                delete_post_meta( $post_id, '_wpla_custom_feed_columns' );
            } else {
                // update template columns
                $tpl_columns = $this->getPreprocessedTemplateColumns();
                update_post_meta( $post_id, '_wpla_custom_feed_columns', $tpl_columns );
            }
		}

	} // save_meta_box()

	public function getPreprocessedTemplateColumns() {

		$prefix     = 'tpl_col_';
		$skip_empty = true; 
		$field_data = array();

		foreach ( $_POST as $key => $val ) {
			if ( ! $val && $skip_empty ) continue;
			if ( substr( $key, 0, strlen($prefix) ) == $prefix ) {
				$field = substr( $key, strlen($prefix) );
				$val   = stripslashes( $val );
				
				$field_data[$field] = $val;	
			}
		}

		return $field_data;
	} // getPreprocessedPostData()


    public function returnJSON( $data ) {
        header('content-type: application/json; charset=utf-8');
        echo json_encode( $data );
    }


} // class WPLA_Product_Feed_MetaBox
// $WPLA_Product_Feed_MetaBox = new WPLA_Product_Feed_MetaBox();
