<?php
/**
 * add amazon images metaboxes to product edit page
 */

class WPLA_Product_Images_MetaBox {

	function __construct() {

		add_action( 'add_meta_boxes', array( &$this, 'add_meta_box' ) );

        add_action( 'wp_ajax_wpla_update_disabled_gallery_images', 	array( &$this, 'wpla_update_disabled_gallery_images' ) ); 

	}

	function add_meta_box() {

		$title = __('Amazon Images', 'wpla');
		add_meta_box( 'wpla-amazon-images', $title, array( &$this, 'meta_box_images' ), 'product', 'normal', 'default');

	}

	function meta_box_images() {
		global $post;

		$this->add_inline_js();

        ?>
        <style type="text/css">
            #wpla-amazon-images .wpla_gallery_thumb_link { 
            	float: left;
            	margin-right: 1em;
            	border: 1px solid #ccc;
            }
            #wpla-amazon-images .wpla_gallery_thumb_link:hover,
            #wpla-amazon-images .wpla_gallery_thumb_link.disabled:hover { 
            	border: 1px solid #555;
            }
            #wpla-amazon-images .wpla_gallery_thumb_link.disabled { 
            	border: 1px solid #eee;
            }
            #wpla-amazon-images .wpla_gallery_thumb_link.disabled img { 
            	opacity: 0.33;
            }
            #wpla-amazon-images .wpla_gallery_thumb_img { 
            	width:79px;
            }
        </style>
        <?php

        // get disabled images as array of attachment_ids
		$disabled_images = get_post_meta( $post->ID, '_wpla_disabled_gallery_images', true );
		$disabled_images = explode( ',', $disabled_images );

		// get featured image
		$featured_image_id = get_post_thumbnail_id( $post->ID );

        // get gallery images
		$product        = WPLA_ProductWrapper::getProduct( $post->ID );
        $attachment_ids = WPLA_ProductWrapper::getGalleryAttachmentIDs( $product );

		// use featured image first, and merge gallery images
		$attachment_ids = array_unique(array_merge( array($featured_image_id), $attachment_ids ));

		// process gallery images
		$gallery_images = array();
		foreach ( $attachment_ids as $attachment_id ) {
	        $src = wp_get_attachment_image_src( $attachment_id, 'thumbnail' ); //getting image source
			$image            = new stdClass();
			$image->id        = $attachment_id;
			$image->src       = $src[0];
			$gallery_images[] = $image;
		}

		// output thumbnails
		foreach ( $gallery_images as $image ) {
			$css_class = in_array( $image->id, $disabled_images ) ? 'disabled' : '';
			echo '<a href="#" class="wpla_gallery_thumb_link '.$css_class.'" data-attachment_id="'.$image->id.'" title="'.basename($image->src).'"/>';
			echo '<img src="'.$image->src.'" class="wpla_gallery_thumb_img" data-attachment_id="'.$image->id.'"/>';
			echo '</a>';
		}
		echo '<p style="clear:both;">';
		echo '<small>';
		echo __('Click an image to disable / enable it to be used on Amazon.','wpla');
		echo '</small></p>';
        echo '<div id="amazon_result_info" class="updated" style="display:none"><p></p></div>';
		// echo "<pre>";print_r($disabled_images);echo"</pre>";#die();        
		// echo "<pre>";print_r($gallery_images);echo"</pre>";#die();        


	} // meta_box_images()


	function add_inline_js() {
		global $post;
        wc_enqueue_js("
			jQuery( document ).ready( function () {

			    // 
			    // Validation
			    // 

				// check required values on submit
				jQuery('.wpla_gallery_thumb_link').on('click', function() {

					jQuery(this).toggleClass('disabled');

					var post_id         = '{$post->ID}';
					var disabled_images = new Array();

					jQuery('.wpla_gallery_thumb_link.disabled').each( function() {
						var thumb = jQuery(this);
						disabled_images.push( thumb.data('attachment_id') );
						// console.log( thumb.data('attachment_id') );
					});	

	                // load task list
	                var params = {
	                    action: 'wpla_update_disabled_gallery_images',
	                    post_id: post_id,
	                    disabled_images: disabled_images,
	                    nonce: 'TODO'
	                };
	                var jqxhr = jQuery.getJSON( ajaxurl, params )
	                .success( function( response ) { 

	                    if ( response.success ) {

	                     //    var logMsg = 'Amazon gallery images have been updated successfully.';
	                     //    jQuery('#amazon_result_info p').html( logMsg );
		                 //    jQuery('#amazon_result_info').addClass( 'updated' ).removeClass('error');
	                     //    jQuery('#amazon_result_info').slideDown();

	                    } else {

	                        var logMsg = '<b>There was a problem updating Amazon gallery images.</b><br><br>'+response.error;
	                        jQuery('#amazon_result_info p').html( logMsg );
	                        jQuery('#amazon_result_info').addClass( 'error' ).removeClass('updated');
	                        jQuery('#amazon_result_info').slideDown();

	                    }

	                })
	                .error( function(e,xhr,error) { 
	                    jQuery('#amazon_result_info p').html( 'The server responded: ' + e.responseText + '<br>' );
	                    jQuery('#amazon_result_info').addClass( 'error' ).removeClass('updated');
	                    jQuery('#amazon_result_info').slideDown();

	                    console.log( 'error', xhr, error ); 
	                    console.log( e.responseText ); 
	                });

					return false;
				})

			});
	    ");
	} // add_inline_js()


    /**
     * update disabled gallery images (ajax)
     */
    function wpla_update_disabled_gallery_images() {

		// get field values
		$post_id         = $_REQUEST['post_id'];
		$disabled_images = isset($_REQUEST['disabled_images']) ? $_REQUEST['disabled_images'] : array();
		if ( ! is_array($disabled_images) ) $disabled_images = array();
        // echo "<pre>";print_r($_REQUEST);echo"</pre>";die();

		// update meta data
		update_post_meta( $post_id, '_wpla_disabled_gallery_images', join( ',', $disabled_images ) );

		$response = new stdClass();
		$response->success = true;

        $this->returnJSON( $response );
        exit();

    } // wpla_update_disabled_gallery_images()

    public function returnJSON( $data ) {
        header('content-type: application/json; charset=utf-8');
        echo json_encode( $data );
    }


} // class WPLA_Product_Images_MetaBox
// $WPLA_Product_Images_MetaBox = new WPLA_Product_Images_MetaBox();
