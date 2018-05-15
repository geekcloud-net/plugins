<?php
/**
 * Extended version of scbPostMetabox class that creates metaboxes on the post editing page.
 *
 * @package Framework\Metaboxes
 */
class APP_Meta_Box extends scbPostMetabox {

	public function __construct( $id, $title, $post_types = 'post', $context = 'advanced', $priority = 'default' ) {
		parent::__construct( $id, $title, array(
			'post_type' => $post_types,
			'context' => $context,
			'priority' => $priority
		) );

	}

	public function form_fields() {
		return $this->form();
	}

	public function form() {
		return array();
	}

	public function table_row( $row, $formdata, $errors = array() ) {
		if ( empty( $row['tip'] ) ) {
			$tip = '';
		} else {
			$tip  = html( "img", array(
				'class' => 'tip-icon',
				'title' => __( 'Help', APP_TD ),
				'src' => appthemes_framework_image( 'help.png' )
			) );
			$tip .= html( "div class='tip-content'", $row['tip'] );
		}

		if ( isset( $row['desc'] ) )
			$row['desc'] = html( 'span class="description"', $row['desc'] );

		$input = scbForms::input( $row, $formdata );

		// If row has an error, highlight it
		$style = ( in_array( $row['name'], $errors ) ) ? 'style= "background-color: #FFCCCC"' : '';

		return html( 'tr',
			html( "th $style scope='row'", $row['title'] ),
			html( "td class='tip'", $tip ),
			html( "td $style", $input )
		);
	}

	public function admin_enqueue_scripts() {
		// print tooltip styles and script only once
		add_action( 'admin_print_styles', array( __CLASS__, '_tip_styles' ) );
		add_action( 'admin_print_footer_scripts', array( __CLASS__, '_tip_scripts' ) );
	}

	final public static function _tip_styles() {
?>
<style type="text/css">
td.tip { width: 16px; }
.tip-icon { margin-top: 3px; cursor: pointer; }
.tip-content { display: none; }
.tip-show { border: 1px solid #ccc; }
</style>
<?php
	}

	final public static function _tip_scripts() {
?>
<script type="text/javascript">
jQuery(document).ready(function($) {
	// tooltip
	$(document).on('click', '.tip-icon', function(ev) {
		var $row = $(this).closest('tr');

		var $show = $row.next('.tip-show');

		if ( $show.length ) {
			$show.remove();
		} else {
			$show = $('<tr class="tip-show">').html(
				$('<td colspan="3">').html( $row.find('.tip-content').html() )
			);

			$row.after( $show );
		}
	});
});
</script>
<?php
	}

}

