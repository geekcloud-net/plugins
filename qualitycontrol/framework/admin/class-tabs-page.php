<?php
/**
 * Settings pages
 *
 * @package Framework\Settings
 */

require_once dirname(__FILE__) . '/class-list.php';

abstract class APP_Tabs_Page extends scbAdminPage {

	public $tabs;
	public $tab_sections;

	abstract protected function init_tabs();

	function __construct( $options = null ) {
		parent::__construct( false, $options );

		$this->tabs = new APP_List;
	}

	function page_loaded() {
		$this->init_tabs();

		do_action( 'tabs_' . $this->pagehook, $this );
		parent::page_loaded();
	}

	function form_handler() {
		if ( empty( $_POST['action'] ) || ! $this->tabs->contains( $_POST['action'] ) )
			return;

		check_admin_referer( $this->nonce );

		$form_fields = array();

		foreach ( $this->tab_sections[ $_POST['action'] ] as $section )
			$form_fields = array_merge( $form_fields, $section['fields'] );

		$to_update = scbForms::validate_post_data( $form_fields, null, $this->options->get() );

		$this->options->update( $to_update );

		do_action( 'tabs_' . $this->pagehook . '_form_handler', $this );
		add_action( 'admin_notices', array( $this, 'admin_msg' ) );
	}

	function page_head() {
?>
<style type="text/css">
.wrap h3 { margin-bottom: 0; }
.wrap .form-table + h3 { margin-top: 2em; }

.form-table td label { display: block; }

td.tip { width: 16px; }
.tip-icon { margin-top: 3px; cursor: pointer; }
.tip-content { display: none; }
.tip-show { border: 1px solid #ccc; }
</style>
<?php
	}

	function page_footer() {
		parent::page_footer();
?>
<script type="text/javascript">
jQuery(function($) {
	$(document).delegate('.tip-icon', 'click', function(ev) {
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

	function page_content() {

		do_action( 'tabs_' . $this->pagehook . '_page_content', $this );

		if ( isset( $_GET['firstrun'] ) )
			do_action( 'appthemes_first_run' );

		$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : '';

		$tabs = $this->tabs->get_all();

		if ( ! isset( $tabs[ $active_tab ] ) )
			$active_tab = key( $tabs );

		$current_url = scbUtil::get_current_url();

		echo '<h3 class="nav-tab-wrapper">';
		foreach ( $tabs as $tab_id => $tab_title ) {
			$class = 'nav-tab';

			if ( $tab_id == $active_tab )
				$class .= ' nav-tab-active';

			$href = add_query_arg( 'tab', $tab_id, $current_url );

			echo ' ' . html( 'a', compact( 'class', 'href' ), $tab_title );
		}
		echo '</h3>';

		echo '<form method="post" action="">';
		echo '<input type="hidden" name="action" value="' . $active_tab . '" />';
		wp_nonce_field( $this->nonce );

		foreach ( $this->tab_sections[ $active_tab ] as $section_id => $section ) {
			if ( isset( $section['title'] ) )
				echo html( 'h3', $section['title'] );

			if ( isset( $section['renderer'] ) )
				call_user_func( $section['renderer'], $section, $section_id );
			else
				$this->render_section( $section['fields'] );
		}

		echo '<p class="submit"><input type="submit" class="button-primary" value="' . esc_attr__( 'Save Changes', APP_TD ) . '" /></p>';
		echo '</form>';
	}

	private function render_section( $fields ) {
		$output = '';

		foreach ( $fields as $field ) {
			$output .= $this->table_row( $this->before_rendering_field( $field ) );
		}

		echo $this->table_wrap( $output );
	}

	public function table_row( $field, $formdata = false ) {
		if ( empty( $field['tip'] ) ) {
			$tip = '';
		} else {
			$tip  = html( "img", array(
				'class' => 'tip-icon',
				'title' => __( 'Help', APP_TD ),
				'src' => appthemes_framework_image( 'help.png' )
			) );
			$tip .= html( "div class='tip-content'", $field['tip'] );
		}

		if ( isset( $field['desc'] ) )
			$field['desc'] = html( 'span class="description"', $field['desc'] );

		return html( "tr",
			html( "th scope='row'", $field['title'] ),
			html( "td class='tip'", $tip ),
			html( "td", scbForms::input( $field, $this->options->get() ) )
		);
	}

	/**
	 * Useful for adding dynamic descriptions to certain fields.
	 *
	 * @param array field arguments
	 * @return array modified field arguments
	 */
	protected function before_rendering_field( $field ) {
		return $field;
	}
}

/**
 * Allows for the optional creation of a tabbed page, or the insertion of a tab
 * into a different page.
 */
abstract class APP_Conditional_Tabs_Page extends APP_Tabs_Page {

	function __construct( $options ) {

		if ( $this->conditional_create_page() ) {
			parent::__construct( $options );
		} else {
			$this->setup();
			add_action( 'admin_init', array( $this, 'tab_register' ) );
		}

	}

	abstract function conditional_create_page();

	function setup_external_page( $page ) {
		$this->tabs = &$page->tabs;
		$this->tab_sections = &$page->tab_sections;
	}

	function tab_register() {
		global $admin_page_hooks;

		$top_level = $this->args['conditional_parent'];
		$sub_level = $this->args['conditional_page'];

		if ( ! isset( $admin_page_hooks[ $top_level ] ) ) {
			return;
		}

		$top_page_hook = $admin_page_hooks[ $top_level ];

		$hook = 'tabs_%s_page_%s';
		$hook = sprintf( $hook, $top_page_hook, $sub_level );
		add_action( $hook, array( $this, 'setup_external_page' ), 9 );
		add_action( $hook, array( $this, 'init_tabs' ) );
	}

}

