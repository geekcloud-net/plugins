<?php
/**
 * The template for displaying Comments.
 *
 * The area of the page that contains both current comments
 * and the comment form. The actual display of comments is
 * handled by a callback to qc_comment which is
 * located in the inc/comments.php file.
 *
 * @package Quality_Control
 * @since Quality Control 0.1
 */
 ?>

<?php appthemes_before_comments(); ?>

<?php if ( have_comments() ) :
	wp_list_comments( array(
		'callback' => 'qc_comment'
	) );

	if ( get_comment_pages_count() > 1 ) :
?>

	<div class="comment-pagination">
		<?php
			paginate_comments_links( array(
				'prev_text' => '&laquo;',
				'next_text' => '&raquo;'
			) );
		?>
	</div>

<?php
	endif;
endif;

appthemes_after_comments();

if ( qc_can_edit_ticket( $post->ID ) ) :
	get_template_part( 'templates/form', 'update-ticket' );
endif;
