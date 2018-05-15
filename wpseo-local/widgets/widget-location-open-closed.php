<?php
/**
 * @package WPSEO_Local\Frontend
 */

/**
 * Class WPSEO_Show_Open_Closed.
 *
 * Creates widget for showing the address.
 */
class WPSEO_Show_Open_Closed extends WP_Widget {

	/**
	 * WPSEO_Show_Open_Closed constructor.
	 */
	function __construct() {
		$widget_options = array(
			'classname'   => 'WPSEO_Show_Open_Closed',
			'description' => __( 'Display a message when a location is open or closed.', 'yoast-local-seo' ),
		);
		parent::__construct( false, $name = __( 'WP SEO - Show open/closed message', 'yoast-local-seo' ), $widget_options );
	}

	/** @see WP_Widget::widget
	 * Displays the store locator form.
	 *
	 * @param array $args     Array of options for this widget.
	 * @param array $instance Instance of the widget.
	 *
	 * @return void
	 */
	function widget( $args, $instance ) {
		$title          = apply_filters( 'widget_title', $instance['title'] );
		$location_id    = ! empty( $instance['location_id'] ) ? $instance['location_id'] : '';
		$message_open   = ! empty( $instance['message_open'] ) ? esc_attr( $instance['message_open'] ) : '';
		$message_closed = ! empty( $instance['message_closed'] ) ? esc_attr( $instance['message_closed'] ) : '';

		// Set location ID, since get_post_status() needs an integer as parameter.
		if ( 'current' === $location_id ) {
			$location_id = get_queried_object_id();
		}

		if ( wpseo_has_multiple_locations() && ( 'publish' != get_post_status( $location_id ) && ! current_user_can( 'edit_posts' ) ) ) {
			return;
		}

		if ( ( $location_id == '' && wpseo_has_multiple_locations() ) || ( $location_id == 'current' && ! is_singular( 'wpseo_locations' ) ) ) {
			return;
		}

		if ( 'current' == $location_id ) {
			$location_id = get_queried_object_id();
		}

		if ( isset( $args['before_widget'] ) ) {
			echo $args['before_widget'];
		}

		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		if ( yoast_seo_local_is_location_open( $location_id ) && ! empty( $message_open ) ) {
			echo wpautop( $message_open );
		}
		else {
			if ( ! empty( $message_closed ) ) {
				echo wpautop( $message_closed );
			}
		}

		if ( isset( $args['after_widget'] ) ) {
			echo $args['after_widget'];
		}
	}


	/** @see WP_Widget::update
	 * @param array $new_instance New option values for this widget.
	 * @param array $old_instance Old, current option values for this widget.
	 *
	 * @return array
	 */
	function update( $new_instance, $old_instance ) {
		$instance                   = $old_instance;
		$instance['title']          = esc_attr( $new_instance['title'] );
		$instance['location_id']    = esc_attr( $new_instance['location_id'] );
		$instance['message_open']   = esc_attr( $new_instance['message_open'] );
		$instance['message_closed'] = esc_attr( $new_instance['message_closed'] );

		return $instance;
	}

	/** @see WP_Widget::form
	 * Displays the form for the widget options.
	 *
	 * @param array $instance Array with all the (saved) option values.
	 *
	 * @return string
	 */
	function form( $instance ) {
		$title           = ( ! empty( $instance['title'] ) ) ? esc_attr( $instance['title'] ) : '';
		$cur_location_id = ( ! empty( $instance['location_id'] ) ) ? esc_attr( $instance['location_id'] ) : '';
		$message_open    = ( ! empty( $instance['message_open'] ) ) ? esc_attr( $instance['message_open'] ) : '';
		$message_closed  = ( ! empty( $instance['message_closed'] ) ) ? esc_attr( $instance['message_closed'] ) : '';
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'yoast-local-seo' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
		</p>

		<?php if ( wpseo_has_multiple_locations() ) { ?>
			<p>
				<label
					for="<?php echo $this->get_field_id( 'location_id' ); ?>"><?php _e( 'Location:', 'yoast-local-seo' ); ?></label>
				<?php
				$args      = array(
					'post_type'      => 'wpseo_locations',
					'orderby'        => 'name',
					'order'          => 'ASC',
					'posts_per_page' => -1,
					'fields'         => 'ids',
					'post_status'    => ( current_user_can( 'edit_posts' ) ? array( 'publish', 'draft' ) : '' ),
				);
				$locations = get_posts( $args );
				?>
				<select name="<?php echo $this->get_field_name( 'location_id' ); ?>" id="<?php echo $this->get_field_id( 'location_id' ); ?>">
					<?php
					if ( ! empty( $locations ) ) {
						?>
						<option value=""><?php _e( 'Select a location', 'yoast-local-seo' ); ?></option>
						<option value="current" <?php selected( $cur_location_id, 'current' ); ?>><?php _e( 'Use current location', 'yoast-local-seo' ); ?></option>
						<?php
						foreach ( $locations as $location_id ) {
							echo '<option value="' . $location_id . '" ' . selected( $cur_location_id, $location_id, false ) . '>' . get_the_title( $location_id ) . '</option>';
						}
					}
					?>
				</select>
			</p>
		<?php } ?>

		<p>
			<label for="<?php echo $this->get_field_id( 'message_open' ); ?>"><?php _e( 'Message when location is open', 'yoast-local-seo' ); ?></label>
			<textarea id="<?php echo $this->get_field_id( 'message_open' ); ?>" class="widefat" name="<?php echo $this->get_field_name( 'message_open' ); ?>"><?php echo esc_attr( $message_open ); ?></textarea>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'message_closed' ); ?>"><?php _e( 'Message when location is closed', 'yoast-local-seo' ); ?></label>
			<textarea id="<?php echo $this->get_field_id( 'message_closed' ); ?>" class="widefat" name="<?php echo $this->get_field_name( 'message_closed' ); ?>"><?php echo esc_attr( $message_closed ); ?></textarea>
		</p>
		<?php

		return '';
	}
}
