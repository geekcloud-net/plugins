<?php 
    // called as: WPLA_ImportHelper::render_import_preview_table( $wpl_data_rows, $wpl_report_summary ) 
?>  

<?php 
    // page size for import preview
    $page_size = 100;

    // get total counts
    $total_rows  = count( $wpl_rows );
    $total_pages = intval( $total_rows / $page_size ) + 1;

    // slice results array
    if ( ! $wpl_pagenum ) $wpl_pagenum = 1;    
    $page     = $wpl_pagenum - 1;
    $offset   = $page * $page_size;
    $wpl_rows = array_slice( $wpl_rows, $offset, $page_size );

?>  

    <?php if ( $wpl_query ) : ?>
        <h4>You searched for report rows containing '<?php echo $wpl_query ?>' - page <?php echo $wpl_pagenum ?> of <?php echo $total_pages ?>:</h4>
    <?php endif; ?>

    <table id="wpla_import_preview_table" class="csv-table">
        <thead>
        <tr>
            <th scope="col" id="cb" class="manage-column column-cb check-column" style=""><input id="cb-select-all-1" type="checkbox"></th>
            <th><?php echo __('Name','wpla') ?></th>
            <th><?php echo __('SKU','wpla') ?></th>
            <th><?php echo __('ASIN','wpla') ?></th>
            <th><?php echo __('Qty','wpla') ?></th>
            <th><?php echo __('Price','wpla') ?></th>
            <th><?php echo __('Listing will be...','wpla') ?></th>
            <th><?php echo __('Product will be...','wpla') ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($wpl_rows as $row) : ?>
        <?php
        	// if ( $row_count > $page_size ) continue;

        	// $listing_asin = $row['product-id'];
        	// $listing_asin   = isset( $row['asin1'] ) ? $row['asin1'] : $row['asin'];

            // special treatment for amazon.ca
            $row_asin = false;
            $row_asin = isset( $row['asin1'] ) ? $row['asin1'] : $row_asin;
            $row_asin = isset( $row['asin']  ) ? $row['asin']  : $row_asin;
            if ( ! $row_asin && isset($row['product-id']) ) {
                if ( $row['product-id-type'] == 1 ) {
                    $row_asin = $row['product-id'];
                }
            }
            $listing_asin = $row_asin;

            // // handle search query
            // if ( $wpl_query && (
            //         stripos( $row['item-name'],  $wpl_query ) === false  &&
            //         stripos( $row['seller-sku'], $wpl_query ) === false  &&
            //         stripos( $row_asin,          $wpl_query ) === false
            //     ) ) continue;

            // // count rows - after processing query
            // $row_count++;


        	$listing_exists = in_array( $listing_asin, $wpl_report_summary->listings_to_update ) ? true : false;
        	if ( $listing_exists ) $listing_asin = '<a href="admin.php?page=wpla&s='.$listing_asin.'" target="_blank">'.$listing_asin.'</a>';
            if ( ! isset($row['asin']) && ! $listing_asin ) $listing_asin = '<span style="color:darkred">No ASIN found in report!</span>';

        	$product_sku    = $row['seller-sku'];
        	$product_exists = in_array( $row['seller-sku'], $wpl_report_summary->products_to_update ) ? true : false;
        	if ( $product_exists ) $product_sku = '<a href="edit.php?post_type=product&s='.$product_sku.'" target="_blank">'.$product_sku.'</a>';
            if ( ! isset($row['seller-sku']) ) $product_sku = '<span style="color:darkred">Invalid Report - no SKU column found</span>';
        ?>
        <tr>
            <th scope="row" class="check-column"><input type="checkbox" name="row[]" value="<?php echo $row['seller-sku'] ?>"></th>
            <!-- <td><?php echo utf8_encode( $row['item-name'] ) ?></td> -->
            <td><?php echo WPLA_ListingsModel::convertToUTF8( $row['item-name'] ) ?></td>
            <td><?php echo $product_sku ?></td>
            <td><?php echo $listing_asin ?></td>
            <td style="text-align:right;">
                <?php 
                    if ( $row['quantity'] ) {
                        echo $row['quantity'];
                    } elseif ( isset($row['fulfillment-channel']) && ( $row['fulfillment-channel'] != 'DEFAULT' ) ) {
                        echo '<span style="color:silver">FBA</span>';
                    } else {
                        echo "&mdash;";
                    }
                ?>
            </td>
            <td style="text-align:right;">
                <?php echo number_format_i18n( floatval($row['price']), 2 ) ?>
            </td>
            <td>
            	<?php 
            		if ( $listing_exists ) {
                        echo '<span style="color:green">updated</span>';
                    } else {
                        echo '<span style="color:orange">imported</span>';
            		}
            	?>
            </td>
            <td>
            	<?php 
            		if ( in_array( $row['seller-sku'], $wpl_report_summary->products_to_update ) ) {
                        echo '<span style="color:green">updated</span>';
            		} else {
                        echo '<span style="color:orange">imported</span>';
            		}
            	?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <p>Displaying rows <?php echo $offset + 1 ?> - <?php echo min( $offset + $page_size, $total_rows ) ?> of <?php echo $total_rows ?> total rows.</h4>
