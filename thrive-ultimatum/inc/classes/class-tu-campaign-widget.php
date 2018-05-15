<?php

/**
 * Handles the WIDGET design type
 */
class TU_Campaign_Widget extends WP_Widget {

	/**
	 * TU_Campaign_Widget constructor.
	 */
	public function __construct() {
		parent::__construct( 'tve_ult_widget', __( 'Thrive Ultimatum', TVE_Ult_Const::T ) );
	}

	/**
	 * Echoes the widget content.
	 * This will just output a placeholder for the widget, the widget design will be loaded via ajax
	 *
	 * @param array $args Display arguments including 'before_title', 'after_title',
	 *                        'before_widget', and 'after_widget'.
	 * @param array $instance The settings for the particular instance of the widget.
	 */
	public function widget( $args, $instance ) {
		if ( ! isset( $instance['campaign_ids'] ) || ! is_array( $instance['campaign_ids'] ) ) {
			return;
		}
		global $tve_ult_frontend;
		if ( ! ( $matched = $tve_ult_frontend->get_campaigns() ) ) {
			return;
		}
		/**
		 * calculate an intersection between the matched campaigns and the campaigns setup from admin for this widget
		 */
		$matched_ids = array();
		foreach ( $matched as $campaign ) {
			$matched_ids [] = $campaign->ID;
		}
		$diff = array_intersect( $matched_ids, $instance['campaign_ids'] );
		/* if no campaign setup from widget -> bail */
		if ( empty( $diff ) ) {
			return;
		}

		// add id to wrapper to be used in js
		echo '<div style="display:none" id="tve-ult-widget-container" data-widget-id="' . $this->id . '">';
		if ( ! empty( $args['before_widget'] ) ) {
			echo $args['before_widget'];
		}
		echo '<span id="tve-ult-widget-placeholder"></span>';

		if ( ! empty( $args['after_widget'] ) ) {
			echo $args['after_widget'];
		}

		echo '</div>';

	}

	/**
	 * Outputs the settings update form.
	 *
	 * @access public
	 *
	 * @param array $instance Current settings.
	 *
	 * @return string Default return is 'noform'.
	 */
	public function form( $instance ) {

		$ids = empty( $instance['campaign_ids'] ) ? array() : $instance['campaign_ids'];
		if ( empty( $ids ) || ! is_array( $ids ) ) {
			$ids = array();
		}

		$campaigns = tve_ult_get_campaigns( array(
			'get_settings' => false,
		) );

		echo '<p><label>' . __( 'Choose the applicable campaigns for the widget', TVE_Ult_Const::T ) . '</label></p>';

		if ( empty( $campaigns ) ) {
			echo '<p class="no-options-widget">' .
			     sprintf( __( 'You have no campaigns setup yet. %sSetup your campaigns first%s', TVE_Ult_Const::T ), '<a href="' . admin_url( 'admin.php?page=tve_ult_dashboard' ) . '">', '</a>' ) .
			     '</p>';

			return '';
		}

		echo '<p><label id="tve-ult-select-all" style="cursor: pointer; font-weight: bold;">' . __( 'Select all/none', TVE_Ult_Const::T ) . '</label></p>';
		?>
		<script type="text/javascript">
			(function ( $ ) {
				$( function () {
					$( 'body' ).off( 'click', '#tve-ult-select-all' ).on( 'click', '#tve-ult-select-all', function () {
						var $this = $( this ),
							check = $this.data( 'checked' ) !== undefined ? ! $this.data( 'checked' ) : true,
							$checkboxes = $this.parents( 'form' ).first().find( '.tve-ult-campaign' );

						var $checked = $checkboxes.filter( function ( index, element ) {
							return $( element ).is( ':checked' );
						} );

						check = check && $checkboxes.length === $checked.length ? false : check;

						$checkboxes.prop( 'checked', check );
						$this.data( 'checked', check );
					} );
				} );
			})( jQuery )
		</script>
		<?php

		$field_name = $this->get_field_name( 'campaign_ids' );
		foreach ( $campaigns as $c ) {
			echo '<p><label><input class="tve-ult-campaign" type="checkbox" value="'
			     . $c->ID . '" name="' . $field_name . '[]"'
			     . ( in_array( $c->ID, $ids ) ? ' checked' : '' )
			     . '>&nbsp; ' . $c->post_title . '</label></p>';
		}

		return '';

	}

	/**
	 * Updates a particular instance of a widget.
	 *
	 * This function should check that `$new_instance` is set correctly. The newly-calculated
	 * value of `$instance` should be returned. If false is returned, the instance won't be
	 * saved/updated.
	 *
	 *
	 * @param array $new_instance New settings for this instance as input by the user via
	 *                            WP_Widget::form().
	 * @param array $old_instance Old settings for this instance.
	 *
	 * @return array Settings to save or bool false to cancel saving.
	 */
	public function update( $new_instance, $old_instance ) {
		if ( ! isset( $new_instance['campaign_ids'] ) || ! is_array( $new_instance['campaign_ids'] ) ) {
			$new_instance['campaign_ids'] = array();
		}

		return $new_instance;
	}
}
