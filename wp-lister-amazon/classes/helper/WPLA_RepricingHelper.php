<?php

class WPLA_RepricingHelper {
	
	const TABLENAME = 'amazon_listings';


    static public function repriceProducts() {

        // first adjust existing lowest prices, then reset products without lowest price to max_price
        $changed_product_ids1 = self::adjustLowestPriceForProducts();
        $changed_product_ids2 = self::resetProductsToMaxPrice();

        return array_merge( $changed_product_ids1, $changed_product_ids2 );
    }


    // adjust items with min/max prices but without lowest price - where price is lower than max_price, increase to max_price
    static public function resetProductsToMaxPrice( $items = false, $verbose = false ) {

        $items               = $items ? $items : WPLA_ListingQueryHelper::getItemsWithoutLowestPriceButPriceLowerThanMaxPrice();
        $changed_product_ids = array();

        // loop found listings
        foreach ( $items as $item ) {

            // make sure there is a max_price but no lowest price
            if ( ! $item->post_id    ) continue;
            if ( ! $item->min_price  ) continue;
            if ( ! $item->max_price  ) continue;
            if ( $item->lowest_price ) continue;

            // target price is max price
            $target_price = $item->max_price;
            if ( $verbose ) wpla_show_message( $item->sku.': No BuyBox price, no competitor - falling back to Max Price: '.$target_price );

            // update price
            $price_was_changed = self::updateAmazonPrice( $item, $target_price, $verbose );
            if ( $price_was_changed ) $changed_product_ids[] = $item->post_id;
            WPLA()->logger->info('resetProductsToMaxPrice() - new price for #'.$item->sku.': '.$target_price);

        } // foreach item

        return $changed_product_ids;
    } // resetProductsToMaxPrice()



    // adjust existing lowest prices based on buybox and lowest offer
    static public function adjustLowestPriceForProducts( $items = false, $verbose = false ) {

        $items               = $items ? $items : WPLA_ListingQueryHelper::getItemsWithMinMaxAndLowestPrice();
        $changed_product_ids = array();
        $repricing_margin    = floatval( get_option('wpla_repricing_margin') );
        $lowest_offer_mode   = get_option('wpla_repricing_use_lowest_offer',0);

        // loop found listings
        foreach ( $items as $item ) {

            // make sure there is a product - and min/max prices are set (0 != NULL)
            if ( ! $item->post_id   ) continue;
            if ( ! $item->min_price ) continue;
            if ( ! $item->max_price ) continue;
            if ( ! $item->buybox_price && ! $item->compet_price ) continue;


            // build target price from BuyBox and/or competitor price
            if ( $item->buybox_price && ! $item->has_buybox ) {
            
                // decide based on uppricing mode
                if ( $lowest_offer_mode && ( $item->buybox_price != $item->compet_price ) ) {

                    // apply undercut to competitor price - if competitor price is different from BuyBox price
                    $target_price = $item->compet_price - $repricing_margin;
                    if ( $verbose ) wpla_show_message( $item->sku.': Other seller has BuyBox at '.$item->buybox_price.', lowest offer at '.$item->compet_price.' - your target price: '.$target_price );

                } else {

                    // apply undercut to BuyBox price - if there is a BuyBox price and it's not the seller's
                    $target_price = $item->buybox_price - $repricing_margin;
                    if ( $verbose ) wpla_show_message( $item->sku.': Other seller has BuyBox at '.$item->buybox_price.' - your target price: '.$target_price );

                }
            
            } elseif ( $item->buybox_price && $item->has_buybox && $item->compet_price ) {

                // decide based on uppricing mode
                if ( $lowest_offer_mode && ( $item->buybox_price != $item->compet_price ) ) {

                    // seller has BuyBox and there is competition - apply undercut to competitor price (beta)
                    $target_price = $item->compet_price - $repricing_margin;
                    if ( $verbose ) wpla_show_message( $item->sku.': You have the BuyBox, but there is a competitor at '.$item->compet_price.' - new target price: '.$target_price );

                } else {

                    // seller has BuyBox and there is competition - keep price for now
                    $target_price = $item->buybox_price;
                    if ( $verbose ) wpla_show_message( $item->sku.': You have the BuyBox - keeping current price: '.$target_price );

                }

            } elseif ( $item->buybox_price && $item->has_buybox && ! $item->compet_price ) {
            
                // seller has BuyBox and NO competition - fall back to max_price
                $target_price = $item->max_price;
                if ( $verbose ) wpla_show_message( $item->sku.': You have the BuyBox but there is no competitor - falling back to Max Price: '.$target_price );

            } elseif ( $item->compet_price ) {
            
                $target_price = $item->compet_price - $repricing_margin;
                if ( $verbose ) wpla_show_message( $item->sku.': No BuyBox price - falling back to next competitor at '.$item->compet_price.' - new target price: '.$target_price );

            } else {

                $target_price = $item->max_price;
                if ( $verbose ) wpla_show_message( $item->sku.': No BuyBox price, no competitor - falling back to Max Price: '.$target_price );

            }
            $target_price = round( $target_price, 2 );


            // update price
            $price_was_changed = self::updateAmazonPrice( $item, $target_price, $verbose );
            if ( $price_was_changed ) {
                $changed_product_ids[] = $item->post_id;
                WPLA()->logger->info('adjustLowestPriceForProducts() - new price for #'.$item->sku.': '.$target_price);
            }

        } // foreach item

        // echo "<pre>";print_r($changed_product_ids);echo"</pre>";#die();
        // echo "<pre>";print_r($items);echo"</pre>";die();

        return $changed_product_ids;
    } // adjustLowestPriceForProducts()



    // update Amazon price in WooCommerce (wp_postmeta) and WP-Lister (wp_amazon_listings)
    static function updateAmazonPrice( $item, $target_price, $verbose ) {

        // make sure we don't go below min_price
        if ( $item->min_price ) $target_price = max( $target_price, $item->min_price );

        // make sure we don't go above max_price (prevent feed error)
        if ( $item->max_price ) $target_price = min( $target_price, $item->max_price );

        // skip if there is no change in price
        if ( $target_price == $item->price ) return false;


        // update amazon price in WooCommerce
        update_post_meta( $item->post_id, '_amazon_price', $target_price );

        // update price in listings table
        $data = array( 
            'price'      => $target_price,
            'pnq_status' => 1,                  // mark price as changed
        );
        WPLA_ListingsModel::updateWhere( array( 'id' => $item->id ), $data );


        // show message
        if ( $verbose ) wpla_show_message( $item->sku.': price was changed from '.$item->price.' to <b>'.$target_price.'</b>' );

        // price was changed
        return true;

    } // updateAmazonPrice()


} // class WPLA_RepricingHelper
