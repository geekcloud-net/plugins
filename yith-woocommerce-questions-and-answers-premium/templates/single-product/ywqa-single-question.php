<?php
/**
 * Single question Template for YITH WooCommerce Questions and Answers
 *
 * @author        Yithemes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! function_exists( "answer_now_link" ) ) {

	function answer_now_link( $question, $label, $class = '' ) {

		$classes = "goto-question";
		if ( ! empty( $class ) ) {
			$classes .= " " . $class;
		}
		$link = '<a class="' . $classes . '" data-discussion-id="' . $question->ID . '" href="' . add_query_arg( array(
				"reply-to-question" => $question->ID,
				"qa"                => 1,
			), remove_query_arg( "show-all-questions", get_permalink( $question->product_id ) ) ) . '">' . ywqa_strip_trim_text( $label, apply_filters('ywqa_single_question_excerpt', 100) ) . '</a>';

		return $link;
	}
}

/** var YWQA_Question $question */
$first_answer = $question->get_answers( 1 );
if ( $first_answer ) {
	$first_answer = $first_answer[0];
}

?>

<li id="li-question-<?php echo $question->ID; ?>" class="question-container <?php echo $classes; ?>">
	<?php do_action( 'yith_questions_answers_before_content', $question ); ?>

	<div class="question-text <?php echo $classes; ?>">
		<div class="question-content">
			<span class="question-symbol"><?php _e( "Q", 'yith-woocommerce-questions-and-answers' ); ?></span>
			<span class="question"><?php echo answer_now_link( $question, $question->content ); ?>
				<?php if ( $first_answer && ( apply_filters('yith_wcqa_allow_user_to_reply',true) ) ) :
					echo answer_now_link( $question, __( "answer now", 'yith-woocommerce-questions-and-answers' ), "answer-now" );
				endif;
				?>
			</span>
		</div>

		<div class="answer-content">
			<?php if ( $first_answer ) : ?>
				<?php
				$user_can         = user_can( $first_answer->discussion_author_id, 'manage_options' );
				$replied_by_admin = apply_filters( 'yith_ywqa_show_as_admin_capabilities', $user_can, $first_answer );

				if ( $replied_by_admin && ! YITH_YWQA()->faq_mode && ! YITH_YWQA()->anonymise_user ): ?>
					<span class="admin-answer-symbol">
						<?php echo apply_filters( 'ywqa_answered_by_admin_label',__( "Answered by the admin", 'yith-woocommerce-questions-and-answers' )); ?>
					</span>
				<?php else: ?>
					<span class="answer-symbol"><?php _e( "A", 'yith-woocommerce-questions-and-answers' ); ?></span>
				<?php endif; ?>

				<?php if ( ( YITH_YWQA()->answer_excerpt_length > 0 ) && ( strlen( $first_answer->content ) > YITH_YWQA()->answer_excerpt_length ) ) : ?>
					<span class="answer"><?php echo substr( $first_answer->content, 0, YITH_YWQA()->answer_excerpt_length ) . '...'; ?></span>
					<a href="#" data-discussion-id="<?php echo $first_answer->ID; ?>" class="read-more">
						<?php _e( "Read more", 'yith-woocommerce-questions-and-answers' ); ?>
					</a>
				<?php else: ?>
					<span class="answer"><?php echo $first_answer->content; ?></span>
				<?php endif; ?>

			<?php else: ?>
				<span class="answer">
					<?php _e( "There are no answers for this question yet.", 'yith-woocommerce-questions-and-answers' ); ?>
				</span>
                <?php if( apply_filters('yith_wcqa_allow_user_to_reply',true) ): ?>
                    <a
                            href="<?php echo add_query_arg( array(
                                "reply-to-question" => $question->ID,
                                "qa"                => 1,
                            ), remove_query_arg( "show-all-questions", get_permalink( $question->product_id ) ) ); ?>"
                            data-discussion-id="<?php echo $question->ID; ?>"
                            class="goto-question write-first-answer"><?php _e( "Answer now", 'yith-woocommerce-questions-and-answers' ); ?></a>
                <?php endif; ?>
            <?php endif; ?>
		</div>

		<?php if ( ( $count = $question->get_answers_count() ) > 1 ) : ?>
			<div class="all-answers-section">
				<a href="<?php echo add_query_arg( array(
					"reply-to-question" => $question->ID,
					"qa"                => 1,
				), remove_query_arg( "show-all-questions", get_permalink( $question->product_id ) ) ); ?>"
				   id="all-answers-<?php echo $question->ID; ?>" class="all-answers goto-question"
				   data-discussion-id="<?php echo $question->ID; ?>">
					<?php echo sprintf( __( "Show all %s answers", 'yith-woocommerce-questions-and-answers' ), $count ); ?>
				</a>
			</div>
		<?php endif; ?>
	</div>
</li>