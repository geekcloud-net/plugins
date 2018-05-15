<?php
/**
 * Questions Template for YITH WooCommerce Questions and Answers
 *
 * @author        Yithemes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div id="ywqa-questions-and-answers" data-product-id="<?php echo $product_id; ?>" class="ywqa-container">

	<?php do_action( 'yith_questions_and_answers_before_content' ); ?>

	<div class="ywqa-content">
		<?php do_action( 'yith_questions_and_answers_content' ); ?>
	</div>

	<?php do_action( 'yith_questions_and_answers_after_content' ); ?>
</div>
