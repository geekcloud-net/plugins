<?php
/**
 * @package WPSEO_Local\Frontend
 */

/**
 * Class WPSEO_Show_Map.
 *
 * Creates widget for showing the map.
 */
class WPSEO_Show_Map extends WP_Widget {

	/**
	 * WPSEO_Show_Map constructor.
	 */
	function __construct() {
		$widget_options = array(
			'classname'   => 'WPSEO_Show_Map',
			'description' => __( 'Shows Google Map of your location', 'yoast-local-seo' ),
		);
		parent::__construct( false, $name = __( 'WP SEO - Show Map', 'yoast-local-seo' ), $widget_options );
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
		$title                   = apply_filters( 'widget_title', isset( $instance['title'] ) ? $instance['title'] : '' );
		$location_id             = ! empty( $instance['location_id'] ) ? $instance['location_id'] : '';
		$show_all_locations      = ! empty( $instance['show_all_locations'] ) && $instance['show_all_locations'] == '1';
		$width                   = ! empty( $instance['width'] ) ? $instance['width'] : 200;
		$height                  = ! empty( $instance['height'] ) ? $instance['height'] : 150;
		$zoom                    = ( ! empty( $instance['zoom'] ) || '0' == $instance['zoom'] ) ? $instance['zoom'] : 10;
		$show_route              = ! empty( $instance['show_route'] ) && $instance['show_route'] == '1';
		$show_state              = ! empty( $instance['show_state'] ) && $instance['show_state'] == '1';
		$show_country            = ! empty( $instance['show_country'] ) && $instance['show_country'] == '1';
		$show_url                = ! empty( $instance['show_url'] ) && $instance['show_url'] == '1';
		$show_category_filter    = ! empty( $instance['show_category_filter'] ) && $instance['show_category_filter'] == 1;
		$default_show_infowindow = ! empty( $instance['default_show_infowindow'] ) && $instance['default_show_infowindow'] == '1';
		$marker_clustering       = ! empty( $instance['marker_clustering'] ) && $instance['marker_clustering'] == 1;

		// Set location ID, since get_post_status() needs an integer as parameter.
		if ( 'current' === $location_id ) {
			$location_id = get_queried_object_id();
		}

		if ( wpseo_has_multiple_locations() && ( 'publish' != get_post_status( $location_id ) && ! current_user_can( 'edit_posts' ) ) ) {
			return;
		}

		if ( ( $location_id == '' && wpseo_has_multiple_locations() && $show_all_locations != '1' ) || ( $location_id == 'current' && ! is_singular( 'wpseo_locations' ) ) ) {
			return;
		}

		if ( isset( $args['before_widget'] ) ) {
			echo $args['before_widget'];
		}

		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		$map_args = array(
			'width'                   => $width,
			'height'                  => $height,
			'zoom'                    => $zoom,
			'id'                      => ( $show_all_locations ) ? 'all' : $location_id,
			'show_route'              => $show_route,
			'show_state'              => $show_state,
			'show_country'            => $show_country,
			'show_url'                => $show_url,
			'show_category_filter'    => $show_category_filter,
			'default_show_infowindow' => $default_show_infowindow,
			'marker_clustering'       => $marker_clustering,

		);

		echo wpseo_local_show_map( $map_args );

		if ( isset( $args['after_widget'] ) ) {
			echo $args['after_widget'];
		}

		return;
	}

	/** @see WP_Widget::update
	 * @param array $new_instance New option values for this widget.
	 * @param array $old_instance Old, current option values for this widget.
	 *
	 * @return array
	 */
	function update( $new_instance, $old_instance ) {
		$instance                            = $old_instance;
		$instance['title']                   = ! empty( $new_instance['title'] ) ? esc_attr( $new_instance['title'] ) : '';
		$instance['location_id']             = ! empty( $new_instance['location_id'] ) ? esc_attr( $new_instance['location_id'] ) : '';
		$instance['show_all_locations']      = ! empty( $new_instance['show_all_locations'] ) && esc_attr( $new_instance['show_all_locations'] ) == '1';
		$instance['width']                   = ! empty( $new_instance['width'] ) ? $new_instance['width'] : 400;
		$instance['height']                  = ! empty( $new_instance['height'] ) ? $new_instance['height'] : 300;
		$instance['zoom']                    = ( ! empty( $new_instance['zoom'] ) || '0' == $new_instance['zoom'] ) ? $new_instance['zoom'] : 10;
		$instance['show_route']              = ! empty( $new_instance['show_route'] ) && esc_attr( $new_instance['show_route'] ) == '1';
		$instance['show_state']              = ! empty( $new_instance['show_state'] ) && esc_attr( $new_instance['show_state'] ) == '1';
		$instance['show_country']            = ! empty( $new_instance['show_country'] ) && esc_attr( $new_instance['show_country'] ) == '1';
		$instance['show_url']                = ! empty( $new_instance['show_url'] ) && esc_attr( $new_instance['show_url'] ) == '1';
		$instance['show_category_filter']    = ! empty( $new_instance['show_category_filter'] ) && esc_attr( $new_instance['show_category_filter'] ) == '1';
		$instance['default_show_infowindow'] = ! empty( $new_instance['default_show_infowindow'] ) && esc_attr( $new_instance['default_show_infowindow'] ) == '1';
		$instance['marker_clustering']       = ! empty( $new_instance['marker_clustering'] ) && esc_attr( $new_instance['marker_clustering'] ) == '1';

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
		$title                   = ! empty( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$location_id             = ! empty( $instance['location_id'] ) ? esc_attr( $instance['location_id'] ) : '';
		$show_all_locations      = ! empty( $instance['show_all_locations'] ) && esc_attr( $instance['show_all_locations'] ) == '1';
		$width                   = ! empty( $instance['width'] ) ? $instance['width'] : 400;
		$height                  = ! empty( $instance['height'] ) ? $instance['height'] : 300;
		$zoom                    = ( ! empty( $instance['zoom'] ) || ( isset( $instance['zoom'] ) && '0' == $instance['zoom'] ) ) ? $instance['zoom'] : 10;
		$show_route              = ! empty( $instance['show_route'] ) && esc_attr( $instance['show_route'] ) == '1';
		$show_state              = ! empty( $instance['show_state'] ) && esc_attr( $instance['show_state'] ) == '1';
		$show_country            = ! empty( $instance['show_country'] ) && esc_attr( $instance['show_country'] ) == '1';
		$show_url                = ! empty( $instance['show_url'] ) && esc_attr( $instance['show_url'] ) == '1';
		$show_category_filter    = ! empty( $instance['show_category_filter'] ) && esc_attr( $instance['show_category_filter'] ) == '1';
		$default_show_infowindow = ! empty( $instance['default_show_infowindow'] ) && esc_attr( $instance['default_show_infowindow'] ) == '1';
		$marker_clustering       = ! empty( $instance['marker_clustering'] ) && esc_attr( $instance['marker_clustering'] ) == '1';

		?>
		<p>
			<label
				for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'yoast-local-seo' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
				   name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
		</p>

		<?php
		if ( wpseo_has_multiple_locations() ) {
			?>
			<p><?php _e( 'Choose to show all your locations in the map, otherwise just pick one in the selectbox below', 'yoast-local-seo' ); ?></p>
			<p id="wpseo-checkbox-multiple-locations-wrapper">
				<label for="<?php echo $this->get_field_id( 'show_all_locations' ); ?>">
					<input id="<?php echo $this->get_field_id( 'show_all_locations' ); ?>"
						   name="<?php echo $this->get_field_name( 'show_all_locations' ); ?>" type="checkbox"
						   class="wpseo_widget_show_locations_checkbox"
						   value="1" <?php echo ! empty( $show_all_locations ) ? ' checked="checked"' : ''; ?> />
					<?php _e( 'Show all locations', 'yoast-local-seo' ); ?>
				</label>
			</p>

			<p id="wpseo-locations-wrapper" <?php echo ( $show_all_locations ) ? 'style="display: none;"' : ''; ?>>
				<label
					for="<?php echo $this->get_field_id( 'location_id' ); ?>"><?php _e( 'Location:', 'yoast-local-seo' ); ?></label>
				<?php
				$repo = new WPSEO_Local_Locations_Repository();
				$locations = $repo->get( array(), false );
				?>
				<select name="<?php echo $this->get_field_name( 'location_id' ); ?>"
						id="<?php echo $this->get_field_id( 'location_id' ); ?>">
					<option value=""><?php _e( 'Select a location', 'yoast-local-seo' ); ?></option>
					<option value="current" <?php selected( $location_id, 'current' ); ?>><?php _e( 'Use current location', 'yoast-local-seo' ); ?></option>
					<?php foreach ( $locations as $loc_id ) { ?>
						<option
							value="<?php echo $loc_id; ?>" <?php selected( $location_id, $loc_id ); ?>><?php echo get_the_title( $loc_id ); ?></option>
					<?php } ?>
				</select>
			</p>

			<script>
				// Add click event to slectbox. Moved to HTML in widget, since the javascript events are gone, when adding the events in external JS

			</script>
			<?php
		}
		?>

		<h4><?php _e( 'Maps settings', 'yoast-local-seo' ); ?></h4>
		<p>
			<label
				for="<?php echo $this->get_field_id( 'width' ); ?>"><?php _e( 'Width:', 'yoast-local-seo' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'width' ); ?>"
				   name="<?php echo $this->get_field_name( 'width' ); ?>" type="text" value="<?php echo $width; ?>" />
		</p>
		<p>
			<label
				for="<?php echo $this->get_field_id( 'height' ); ?>"><?php _e( 'Height:', 'yoast-local-seo' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'height' ); ?>"
				   name="<?php echo $this->get_field_name( 'height' ); ?>" type="text" value="<?php echo $height; ?>" />
		</p>
		<p>
			<?php
			$nr_zoom_levels = 20;
			?>
			<label
				for="<?php echo $this->get_field_id( 'zoom' ); ?>"><?php _e( 'Zoom level:', 'yoast-local-seo' ); ?></label>
			<select class="" id="<?php echo $this->get_field_id( 'zoom' ); ?>"
					name="<?php echo $this->get_field_name( 'zoom' ); ?>">
				<?php for ( $i = 1; $i <= $nr_zoom_levels; $i++ ) { ?>
					<option value="<?php echo $i; ?>"<?php echo ( $zoom == $i ) ? ' selected="selected"' : ''; ?>><?php echo $i; ?></option>
				<?php } ?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'show_state' ); ?>">
				<input id="<?php echo $this->get_field_id( 'show_state' ); ?>" name="<?php echo $this->get_field_name( 'show_state' ); ?>" type="checkbox" value="1" <?php echo ! empty( $show_state ) ? ' checked="checked"' : ''; ?> />
				<?php _e( 'Show state in info-window', 'yoast-local-seo' ); ?>
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'show_country' ); ?>">
				<input id="<?php echo $this->get_field_id( 'show_country' ); ?>" name="<?php echo $this->get_field_name( 'show_country' ); ?>" type="checkbox" value="1" <?php echo ! empty( $show_country ) ? ' checked="checked"' : ''; ?> />
				<?php _e( 'Show country in info-window', 'yoast-local-seo' ); ?>
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'show_url' ); ?>">
				<input id="<?php echo $this->get_field_id( 'show_url' ); ?>" name="<?php echo $this->get_field_name( 'show_url' ); ?>" type="checkbox" value="1" <?php echo ! empty( $show_url ) ? ' checked="checked"' : ''; ?> />
				<?php _e( 'Show URL in info-window', 'yoast-local-seo' ); ?>
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'show_route' ); ?>">
				<input id="<?php echo $this->get_field_id( 'show_route' ); ?>" name="<?php echo $this->get_field_name( 'show_route' ); ?>" type="checkbox" value="1" <?php echo ! empty( $show_route ) ? ' checked="checked"' : ''; ?> />
				<?php _e( 'Show route planner', 'yoast-local-seo' ); ?>
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'show_category_filter' ); ?>">
				<input id="<?php echo $this->get_field_id( 'show_category_filter' ); ?>" name="<?php echo $this->get_field_name( 'show_category_filter' ); ?>" type="checkbox" value="1" <?php echo ! empty( $show_category_filter ) ? ' checked="checked"' : ''; ?> />
				<?php _e( 'Show category filter', 'yoast-local-seo' ); ?>
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'default_show_infowindow' ); ?>">
				<input id="<?php echo $this->get_field_id( 'default_show_infowindow' ); ?>" name="<?php echo $this->get_field_name( 'default_show_infowindow' ); ?>" type="checkbox" value="1" <?php echo ! empty( $default_show_infowindow ) ? ' checked="checked"' : ''; ?> />
				<?php _e( 'Show infowindow by default', 'yoast-local-seo' ); ?>
			</label>
		</p>
		<?php if ( wpseo_has_multiple_locations() ) { ?>
			<p>
				<label for="<?php echo $this->get_field_id( 'marker_clustering' ); ?>">
					<input id="<?php echo $this->get_field_id( 'marker_clustering' ); ?>" name="<?php echo $this->get_field_name( 'marker_clustering' ); ?>" type="checkbox" value="1" <?php echo ! empty( $marker_clustering ) ? ' checked="checked"' : ''; ?> />
					<?php _e( 'Marker clustering', 'yoast-local-seo' ); ?>
				</label>
			</p>
		<?php } ?>
		<?php

		return '';
	}
}
