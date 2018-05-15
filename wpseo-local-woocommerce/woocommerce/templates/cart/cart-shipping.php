<?php
/**
 * Shipping Methods Display
 *
 * In 2.1 we show methods per package. This allows for multiple methods per order if so desired.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart-shipping.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.5.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$yoast_seo_subset_started = false;
$yoast_seo_subset_ended = true;
$settings = get_option( 'woocommerce_yoast_wcseo_local_pickup_settings' );

?>
<tr class="shipping">
	<th><?php echo wp_kses_post( $package_name ); ?></th>
	<td data-title="<?php echo esc_attr( $package_name ); ?>">
		<?php if ( 1 < count( $available_methods ) ) : ?>
			<ul id="shipping_method">
				<?php foreach ( $available_methods as $method ) : ?>

					<?php

						//if the method is one of our local pickup store?
						if ( $method->method_id == 'yoast_wcseo_local_pickup' ) {

							// and if we haven't yet started our subset
							if ( ! $yoast_seo_subset_started ) {

								//check/uncheck the parent aka the-toggler
								if ( 0 === strpos( $chosen_method, 'yoast_wcseo_local_pickup_' ) ) {
									$parent_method = 'yoast_wcseo_local_pickup';
								} else {
									$parent_method = false;
								}

								//output the parent toggler which enables us to show/hide the subset
								printf(
									'<li class="parent-toggler"><input type="radio" name="yoast-local-seo-shipping_method_toggle" id="yoast-local-seo-shipping_method_toggle" class="yoast-local-seo-shipping_method_toggle" %1$s /><label for="yoast-local-seo-shipping_method_toggle">%2$s</label>',
									checked( 'yoast_wcseo_local_pickup', $parent_method, false ),
									__( 'Local store pickup', 'yoast-local-seo-woocommerce' )
								);

								//output the subset wrapper as a list or as a dropdown
								if ( $settings['checkout_mode'] == 'radio' ) {
									echo '<ul class="shipping_method_subset">';
								} else {
									echo '<select class="shipping_method shipping_method_subset" name="shipping_method[' . $index . ']" data-index="' . $index . '" id="shipping_method_select">';
								}

								//flag that we have started the subset but not yet ended it!
								$yoast_seo_subset_started = true;
								$yoast_seo_subset_ended   = false;

							}

						} else {

							// if this is not a local pickup store we may need to end our subset-loop
							if ( $yoast_seo_subset_started && ( ! $yoast_seo_subset_ended ) ) {

								//close the radio-list or the checkbox
								if ( $settings['checkout_mode'] == 'radio' ) {
									echo '</ul><!-- .shipping_method_subset -->';
								} else {
									echo '</select><!-- .shipping_method_subset -->';
								}

								//close the paren toggler
								echo '</li><!-- .parent-toggler -->';

								//flag that we have ended our subset, and we have not started a new one
								$yoast_seo_subset_started = false;
								$yoast_seo_subset_ended = true;

							}

						}

						// show a Local pickup store in a differnet way then other shipping methods
						if ( $method->method_id == 'yoast_wcseo_local_pickup' ) {

							//do we desire radio buttons?
							if ( $settings['checkout_mode'] == 'radio' ) {

								//output radio with some extra address data
								printf(
									'<li><input type="radio" name="shipping_method[%1$d]" data-index="%1$d" id="shipping_method_%1$d_%2$s" value="%3$s" class="shipping_method" %4$s /><label for="shipping_method_%1$d_%2$s">%5$s <small class="shipping_method_address">%6$s</small></label></li>',
									$index,
									sanitize_title( $method->id ),
									esc_attr( $method->id ),
									checked( $method->id, $chosen_method, false ),
									wc_cart_totals_shipping_method_label( $method ),
									yoast_seo_local_woocommerce_get_address_for_method_id( $method->id )
								);

							//or do we desire options inside a dropdown?
							} else {

								//output option with some extra address data
								printf(
									'<option value="%3$s" class="shipping_method_option" %4$s >%5$s - %6$s</option>',
									$index,
									sanitize_title( $method->id ),
									esc_attr( $method->id ),
									selected( $method->id, $chosen_method, false ),
									wc_cart_totals_shipping_method_label( $method ),
									yoast_seo_local_woocommerce_get_address_for_method_id( $method->id )
								);

							}

						//show all other shipping methods in the regular way ( radios without extra data )
						} else {

							//regular radio button
							printf(
								'<li><input type="radio" name="shipping_method[%1$d]" data-index="%1$d" id="shipping_method_%1$d_%2$s" value="%3$s" class="shipping_method" %4$s /><label for="shipping_method_%1$d_%2$s">%5$s</label></li>',
								$index,
								sanitize_title( $method->id ),
								esc_attr( $method->id ),
								checked( $method->id, $chosen_method, false ),
								wc_cart_totals_shipping_method_label( $method )
							);
						}

						do_action( 'woocommerce_after_shipping_rate', $method, $index );

					?>

				<?php endforeach; ?>

			<?php

				//all done? we may need to end our subset-loop
				if ( $yoast_seo_subset_started && ( ! $yoast_seo_subset_ended ) ) {

					//close the radio-list or the checkbox
					if ( $settings['checkout_mode'] == 'radio' ) {
						echo '</ul><!-- .shipping_method_subset -->';
					} else {
						echo '</select><!-- .shipping_method_subset -->';
					}

					//close the parent toggler
					echo '</li><!-- .parent-toggler -->';

					//flag that we have ended our subset, and we have not started a new one
					$yoast_seo_subset_started = false;
					$yoast_seo_subset_ended = true;

				}

				//end all shipping methods list
				echo '</ul><!-- #shipping_method -->';
			?>

		<?php elseif ( 1 === count( $available_methods ) ) :  ?>
			<?php
				$method = current( $available_methods );
				printf( '%3$s <input type="hidden" name="shipping_method[%1$d]" data-index="%1$d" id="shipping_method_%1$d" value="%2$s" class="shipping_method" />', $index, esc_attr( $method->id ), wc_cart_totals_shipping_method_label( $method ) );
				do_action( 'woocommerce_after_shipping_rate', $method, $index );
			?>
		<?php elseif ( ! WC()->customer->has_calculated_shipping() ) : ?>
			<?php echo wpautop( __( 'Shipping costs will be calculated once you have provided your address.', 'yoast-local-seo-woocommerce' ) ); ?>
		<?php else : ?>
			<?php echo apply_filters( is_cart() ? 'woocommerce_cart_no_shipping_available_html' : 'woocommerce_no_shipping_available_html', wpautop( __( 'There are no shipping methods available. Please double check your address, or contact us if you need any help.', 'yoast-local-seo-woocommerce' ) ) ); ?>
		<?php endif; ?>

		<?php if ( $show_package_details ) : ?>
			<?php echo '<p class="woocommerce-shipping-contents"><small>' . esc_html( $package_details ) . '</small></p>'; ?>
		<?php endif; ?>

		<?php if ( is_cart() && ! $index ) : ?>
			<?php woocommerce_shipping_calculator(); ?>
		<?php endif; ?>
	</td>
</tr>

<script>

	jQuery( document ).ready(function( $ ) {

		// Select2 Enhancement if it exists
		if ( $().select2 && ( yoast_wcseo_local_translations.select2 == 'enabled' ) ) {
			$( '#shipping_method_select' ).select2({width: 'resolve'});
		}

		$('input.shipping_method').on( 'change', function( e ) {

			$('#shipping_method_select').remove();
		});

		// init change toggle on our radio-btn
		$('#yoast-local-seo-shipping_method_toggle').on( 'change', function( e ) {

			$this       = $(this);
			var checked = $this.prop( 'checked' );
			var $subset = $this.parent().find( '.shipping_method_subset' );

			if ( checked ) {

				// if it is checked...
				if( $subset.find( 'input' ).length > 0 ) { //look for inputs inside the subset

					// look for a checked item in the subset radiolist
					if( $subset.find( 'input:checked' ).length == 0 ) {

						// if not any checked items are found, make sure the first item is checked and triggered ( so Woo's calculator fires correctly )
						$subset.find( 'input:first' ).trigger( 'click' );
					}

				} else {

					if ( $subset.find( 'input:selected' ).length == 0 ) {
						$subset.find( 'option:first' ).prop( 'selected', true );
					}
					
					$subset.trigger( 'change' );
				}
			}
		});
	});

</script>

<style>
	.shipping_method_subset {
		display: none;
	}
	input:checked ~ .shipping_method_subset {
		display: block;
	}
	select.shipping_method_subset {
		margin: 10px 0;
	}
	.woocommerce ul#shipping_method li .select2-container {
		text-indent: 0;
		max-width: 300px;
	}
</style>
