<?php
/**
 * Creates options for admin area.
 *
 * @package Quality_Control
 * @subpackage Administration
 * @since Quality Control 0.1
 */

class QC_Options_Page extends APP_Tabs_Page {


	function setup() {
		$this->textdomain = APP_TD;

		$this->args = array(
			'page_title' => __( 'Quality Control Settings', APP_TD ),
			'menu_title' => __( 'Settings', APP_TD ),
			'page_slug' => 'app-settings',
			'parent' => 'app-dashboard',
			'screen_icon' => 'options-general',
			'admin_action_priority' => 10,
		);


	}


	protected function init_tabs() {
		// Remove unwanted query args from urls
		$_SERVER['REQUEST_URI'] = remove_query_arg( array( 'firstrun' ), $_SERVER['REQUEST_URI'] );

		add_action( 'qc_settings_head', 'qc_status_colors_css' );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		$this->tabs->add( 'general', __( 'General', APP_TD ) );

		$this->tab_sections['general']['main'] = array(
			'title' => __( 'General Settings', APP_TD ),
			'fields' => array(
				array(
					'title' => __( 'Permissions', APP_TD ),
					'type' => 'radio',
					'name' => 'assigned_perms',
					'values' => array(
						'protected' => __( 'Users can only view their own tickets and tickets they are assigned to.', APP_TD ),
						'read-only' => __( 'Users can view all tickets.', APP_TD ),
						'read-write' => __( 'Users can view and updated all tickets.', APP_TD ),
					),
				),
				array(
					'title' => __( 'Lock Site from Visitors', APP_TD ),
					'name' => 'lock_site',
					'type' => 'checkbox',
					'desc' => __( 'Yes', APP_TD ),
					'tip' => __( 'Visitors will be asked to login, and will not be able to browse site. Also content of the sidebars and menus will be hidden.', APP_TD ),
				),
			),
		);

		$this->tab_sections['general']['states'] = array(
			'title' => __( 'States', APP_TD ),
			'fields' => array(
				array(
					'title' => __( 'Default State', APP_TD ),
					'desc' => __( 'This state will be selected by default when creating a ticket.', APP_TD ),
					'type' => 'select',
					'sanitize' => 'absint',
					'name' => 'ticket_status_new',
					'values' => $this->ticket_states(),
				),
				array(
					'title' => __( 'Resolved State', APP_TD ),
					'desc' => __( 'Tickets in this state are assumed to no longer need attention.', APP_TD ),
					'type' => 'select',
					'sanitize' => 'absint',
					'name' => 'ticket_status_closed',
					'values' => $this->ticket_states(),
				),
			),
		);
		$this->tab_sections['general']['colors'] = array(
			'fields' => $this->status_colors_options(),
			'renderer' => array( $this, 'render_status_colors' ),
		);

		$this->tab_sections['general']['modules'] = array(
			'title' => __( 'Modules', APP_TD ),
			'fields' => array(
				array(
					'title' => __( 'Enable Modules', APP_TD ),
					'type' => 'checkbox',
					'name' => 'modules',
					'values' => array(
						'assignment' => __( 'Assignment', APP_TD ),
						'attachments' => __( 'Attachments', APP_TD ),
						'categories' => __( 'Categories', APP_TD ),
						'changesets' => __( 'Changesets', APP_TD ),
						'milestones' => __( 'Milestones', APP_TD ),
						'priorities' => __( 'Priorities', APP_TD ),
						'tags' => __( 'Tags', APP_TD ),
					),
					'tip' => __( 'Choose the modules that you want to use on your site.', APP_TD ),
				),
			),
		);

	}


	protected function ticket_states() {
		$states = array( '-1' => __( '&mdash; Select &mdash;', APP_TD ) );
		$terms = get_terms( 'ticket_status', array( 'hide_empty' => 0 ) );

		foreach ( (array) $terms as $term )
			$states[ $term->term_id ] = $term->name;

		return $states;
	}


	/**
	 * Enqueues scripts and styles
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts() {
		// color picker for status color options
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_style( 'wp-color-picker' );
	}


	function page_head() {
		parent::page_head();
?>
<style type="text/css">
.settings-section ul {
	margin: 0;
}

.ticket-status {
	text-decoration: none;
	padding: 5px 10px;
	text-transform: uppercase;
	font-weight: bold;
	border-radius: 3px;
	-webkit-border-radius: 3px;
	-moz-border-radius: 3px;
	min-width: 100px;
}
</style>
<?php
		do_action( 'qc_settings_head' );
	}


	function page_footer() {
?>
<script type="text/javascript">
jQuery(document).ready(function($) {
	/* attach color picker - Available since WP 3.5, so check first */
	if ( jQuery.isFunction( jQuery.fn.wpColorPicker ) ) {
		$('.color-picker').wpColorPicker();
	}

} );
</script>
<?php
		parent::page_footer();
	}


	function status_colors_options() {
		$options = array();
		$states = get_terms( 'ticket_status', 'hide_empty=0' );

		foreach ( (array) $states as $state ) {
			$options[ $state->slug . '-background' ] = array(
				'type' => 'text',
				'sanitize' => array( $this, 'validate_color' ),
				'name' => array( 'status_colors', $state->slug, 'background' ),
				'extra' => 'class="color-picker"'
			);

			$options[ $state->slug . '-text' ] = array(
				'type' => 'text',
				'sanitize' => array( $this, 'validate_color' ),
				'name' => array( 'status_colors', $state->slug, 'text' ),
				'extra' => 'class="color-picker"'
			);
		}

		return $options;
	}


	function render_status_colors( $section, $section_id ) {
		$output = '';
		$colors_table = '';

		if ( empty( $section['fields'] ) ) {
			$output = sprintf( 'You haven&#39;t created any states. Please visit the <a href="%s">states</a> screen to add them.', admin_url( 'edit-tags.php?taxonomy=ticket_status&post_type=ticket' ) );
			echo $this->table_wrap( $output );
			return;
		}

		$colors_table .= html( "tr",
			html( "th", __( 'Background', APP_TD ) ),
			html( "th", __( 'Text', APP_TD ) ),
			html( "th", __( 'Preview', APP_TD ) )
		);

		$states = get_terms( 'ticket_status', 'hide_empty=0' );

		foreach ( $states as $state ) {
			$colors_table .= html( "tr",
				html( "td", scbForms::input( $section['fields'][ $state->slug . '-background' ], $this->options->get() ) ),
				html( "td", scbForms::input( $section['fields'][ $state->slug . '-text' ], $this->options->get() ) ),
				html( "td", html( 'span class="ticket-status ' . $state->slug . '"', $state->name ) )
			);
		}

		$colors_table = html( 'table id="state-colors" class="widefat"', $colors_table );
		$colors_table .= html( 'span class="description"', __( 'Enter colors in a hexadecimal format (i.e. #F66 or #557544).', APP_TD ) );

		$output .= html( "tr",
			html( "th scope='row'", __( 'Colors', APP_TD ) ),
			html( "td class='tip'", '' ),
			html( "td", $colors_table )
		);

		echo $this->table_wrap( $output );
	}


	function validate_color( $color ) {
		if ( ! empty( $color ) ) {
			if ( ! preg_match( '/^#[a-f0-9]{3,6}$/i', $color ) )
				$color = '';
		}

		return $color;
	}

}

