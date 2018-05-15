<?php
/**
 * Admin order file status update
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/** @var YWQA_Question $question */
?>

<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

	<div class="ywqa-header">
		<div class="ywqa-product-picture">
			<?php echo get_the_post_thumbnail( $question->product_id, 'thumbnail' ); ?>
		</div>

		<div class="ywqa-description">
			<?php echo sprintf( __( "A user submitted a question about the product <b>%s</b> that you have bought recently.", 'yith-woocommerce-questions-and-answers' ),
				'<a href="' . get_permalink( $question->product_id ) . '">' . wc_get_product( $question->product_id )->get_title() . '</a>' );
			?>
		</div>
	</div>

	<div class="ywqa-content">
		<div class="ywqa-content-intro"><?php _e( "The question", 'yith-woocommerce-questions-and-answers' ); ?></div>
		<div class="ywqa-content-description"><?php echo $question->content; ?></div>

		<div class="ywqa-give-answer">
			<span class="ywqa-give-answer-title"><?php _e( "Do you want to answer to the question?", 'yith-woocommerce-questions-and-answers' ); ?></span>
			<a class="ywqa-give-answer-link" href="<?php echo esc_url( add_query_arg( 'reply-to-question', $question->ID, get_permalink( $question->product_id ) ) ); ?>">
				<?php _e( "Go to the question", 'yith-woocommerce-questions-and-answers' ); ?>
			</a>
		</div>
	</div>

<?php if ( $unsubscribe_product_url || $unsubscribe_all_product_url ): ?>

	<div class="ywqa-unsubscribe_section">
		<?php if ( $unsubscribe_product_url ): ?>
			<a href="<?php echo $unsubscribe_product_url; ?>"
			   class="ywqa-unsubscribe-single-product"><?php _e( "Don't receive any more requests for this product", 'yith-woocommerce-questions-and-answers' ); ?>
			</a>
		<?php endif; ?>

		<?php if ( $unsubscribe_all_product_url ): ?>
			<a href="<?php echo $unsubscribe_all_product_url; ?>"
			   class="ywqa-unsubscribe-all-products"><?php _e( "Don't receive any more requests for any products", 'yith-woocommerce-questions-and-answers' ); ?>
			</a>
		<?php endif; ?>
	</div>
<?php endif; ?>

<?php do_action( 'woocommerce_email_footer' );