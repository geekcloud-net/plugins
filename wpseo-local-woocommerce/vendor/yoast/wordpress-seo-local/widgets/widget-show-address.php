<?php
/**
 * @package WPSEO_Local\Frontend
 */

/**
 * Class WPSEO_Show_Address.
 *
 * Creates widget for showing the address.
 */
class WPSEO_Show_Address extends WP_Widget {

	/**
	 * WPSEO_Show_Address constructor.
	 */
	function __construct() {
		$widget_options = array(
			'classname'   => 'WPSEO_Show_Address',
			'description' => __( 'Shows address of locations.', 'yoast-local-seo' ),
		);
		parent::__construct( false, $name = __( 'WP SEO - Show Address', 'yoast-local-seo' ), $widget_options );
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
		$title              = apply_filters( 'widget_title', $instance['title'] );
		$location_id        = ! empty( $instance['location_id'] ) ? $instance['location_id'] : '';
		$hide_name          = ! empty( $instance['hide_name'] ) && $instance['hide_name'] == '1';
		$hide_address       = ! empty( $instance['hide_address'] ) && $instance['hide_address'] = '1';
		$show_country       = ! empty( $instance['show_country'] ) && $instance['show_country'] == '1';
		$show_state         = ! empty( $instance['show_state'] ) && $instance['show_state'] == '1';
		$show_phone         = ! empty( $instance['show_phone'] ) && $instance['show_phone'] == '1';
		$show_phone_2       = ! empty( $instance['show_phone_2'] ) && $instance['show_phone_2'] == '1';
		$show_fax           = ! empty( $instance['show_fax'] ) && $instance['show_fax'] == '1';
		$show_email         = ! empty( $instance['show_email'] ) && $instance['show_email'] == '1';
		$show_vat           = ! empty( $instance['show_vat'] ) && $instance['show_vat'] == '1';
		$show_tax           = ! empty( $instance['show_tax'] ) && $instance['show_tax'] == '1';
		$show_coc           = ! empty( $instance['show_coc'] ) && $instance['show_coc'] == '1';
		$show_price_range   = ! empty( $instance['show_price_range'] ) && $instance['show_price_range'] == '1';
		$show_logo          = ! empty( $instance['show_logo'] ) && $instance['show_logo'] == '1';
		$show_opening_hours = ! empty( $instance['show_opening_hours'] ) && $instance['show_opening_hours'] == '1';
		$hide_closed        = ! empty( $instance['hide_closed'] ) && $instance['hide_closed'] == '1';
		$show_oneline       = ! empty( $instance['show_oneline'] ) && $instance['show_oneline'] == '1';
		$comment            = ! empty( $instance['comment'] ) ? esc_attr( $instance['comment'] ) : '';

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

		if ( isset( $args['before_widget'] ) ) {
			echo $args['before_widget'];
		}

		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		$shortcode_args = array(
			'id'                 => $location_id,
			'hide_name'          => $hide_name,
			'hide_address'       => $hide_address,
			'show_country'       => $show_country,
			'show_state'         => $show_state,
			'show_phone'         => $show_phone,
			'show_phone_2'       => $show_phone_2,
			'show_fax'           => $show_fax,
			'show_email'         => $show_email,
			'show_vat'           => $show_vat,
			'show_tax'           => $show_tax,
			'show_coc'           => $show_coc,
			'show_price_range'   => $show_price_range,
			'show_logo'          => $show_logo,
			'show_opening_hours' => $show_opening_hours,
			'hide_closed'        => $hide_closed,
			'oneline'            => $show_oneline,
			'comment'            => $comment,
			'from_widget'        => true,
			'widget_title'       => $title,
			'before_title'       => $args['before_title'],
			'after_title'        => $args['after_title'],
		);

		echo wpseo_local_show_address( $shortcode_args, $this->id );

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
		$instance                       = $old_instance;
		$instance['title']              = isset( $new_instance['title'] ) ? esc_attr( $new_instance['title'] ) : '';
		$instance['location_id']        = isset( $new_instance['location_id'] ) ? esc_attr( $new_instance['location_id'] ) : '';
		$instance['hide_name']          = isset( $new_instance['hide_name'] ) ? esc_attr( $new_instance['hide_name'] ) : '';
		$instance['hide_address']       = isset( $new_instance['hide_address'] ) ? esc_attr( $new_instance['hide_address'] ) : '';
		$instance['show_country']       = isset( $new_instance['show_country'] ) ? esc_attr( $new_instance['show_country'] ) : '';
		$instance['show_state']         = isset( $new_instance['show_state'] ) ? esc_attr( $new_instance['show_state'] ) : '';
		$instance['show_phone']         = isset( $new_instance['show_phone'] ) ? esc_attr( $new_instance['show_phone'] ) : '';
		$instance['show_phone_2']       = isset( $new_instance['show_phone_2'] ) ? esc_attr( $new_instance['show_phone_2'] ) : '';
		$instance['show_fax']           = isset( $new_instance['show_fax'] ) ? esc_attr( $new_instance['show_fax'] ) : '';
		$instance['show_email']         = isset( $new_instance['show_email'] ) ? esc_attr( $new_instance['show_email'] ) : '';
		$instance['show_vat']           = isset( $new_instance['show_vat'] ) ? esc_attr( $new_instance['show_vat'] ) : '';
		$instance['show_tax']           = isset( $new_instance['show_tax'] ) ? esc_attr( $new_instance['show_tax'] ) : '';
		$instance['show_coc']           = isset( $new_instance['show_coc'] ) ? esc_attr( $new_instance['show_coc'] ) : '';
		$instance['show_price_range']   = isset( $new_instance['show_price_range'] ) ? esc_attr( $new_instance['show_price_range'] ) : '';
		$instance['show_logo']          = isset( $new_instance['show_logo'] ) ? esc_attr( $new_instance['show_logo'] ) : '';
		$instance['show_opening_hours'] = isset( $new_instance['show_opening_hours'] ) ? esc_attr( $new_instance['show_opening_hours'] ) : '';
		$instance['hide_closed']        = isset( $new_instance['hide_closed'] ) ? esc_attr( $new_instance['hide_closed'] ) : '';
		$instance['show_oneline']       = isset( $new_instance['show_oneline'] ) ? esc_attr( $new_instance['show_oneline'] ) : '';
		$instance['comment']            = isset( $new_instance['comment'] ) ? esc_attr( $new_instance['comment'] ) : '';

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
		$title              = ( ! empty( $instance['title'] ) ) ? esc_attr( $instance['title'] ) : '';
		$cur_location_id    = ( ! empty( $instance['location_id'] ) ) ? esc_attr( $instance['location_id'] ) : '';
		$hide_name          = ! empty( $instance['hide_name'] ) && esc_attr( $instance['hide_name'] ) == '1';
		$hide_address       = ! empty( $instance['hide_address'] ) && esc_attr( $instance['hide_address'] ) == '1';
		$show_country       = ! empty( $instance['show_country'] ) && esc_attr( $instance['show_country'] ) == '1';
		$show_state         = ! empty( $instance['show_state'] ) && esc_attr( $instance['show_state'] ) == '1';
		$show_phone         = ! empty( $instance['show_phone'] ) && esc_attr( $instance['show_phone'] ) == '1';
		$show_phone_2       = ! empty( $instance['show_phone_2'] ) && esc_attr( $instance['show_phone_2'] ) == '1';
		$show_fax           = ! empty( $instance['show_fax'] ) && esc_attr( $instance['show_fax'] ) == '1';
		$show_email         = ! empty( $instance['show_email'] ) && esc_attr( $instance['show_email'] ) == '1';
		$show_vat           = ! empty( $instance['show_vat'] ) && esc_attr( $instance['show_vat'] ) == '1';
		$show_tax           = ! empty( $instance['show_tax'] ) && esc_attr( $instance['show_tax'] ) == '1';
		$show_coc           = ! empty( $instance['show_coc'] ) && esc_attr( $instance['show_coc'] ) == '1';
		$show_price_range   = ! empty( $instance['show_price_range'] ) && esc_attr( $instance['show_price_range'] ) == '1';
		$show_logo          = ! empty( $instance['show_logo'] ) && esc_attr( $instance['show_logo'] ) == '1';
		$show_opening_hours = ! empty( $instance['show_opening_hours'] ) && esc_attr( $instance['show_opening_hours'] ) == '1';
		$hide_closed        = ! empty( $instance['hide_closed'] ) && esc_attr( $instance['hide_closed'] ) == '1';
		$show_oneline       = ! empty( $instance['show_oneline'] ) && esc_attr( $instance['show_oneline'] ) == '1';
		$comment            = ( ! empty( $instance['comment'] ) ) ? esc_attr( $instance['comment'] ) : '';
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
				$repo = new WPSEO_Local_Locations_Repository();
				$locations = $repo->get( array(), false );
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
			<label for="<?php echo $this->get_field_id( 'hide_name' ); ?>">
				<input id="<?php echo $this->get_field_id( 'hide_name' ); ?>"
					   name="<?php echo $this->get_field_name( 'hide_name' ); ?>" type="checkbox"
					   value="1" <?php echo ! empty( $hide_name ) ? ' checked="checked"' : ''; ?> />
				<?php _e( 'Hide business name', 'yoast-local-seo' ); ?>
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'hide_address' ); ?>">
				<input id="<?php echo $this->get_field_id( 'hide_address' ); ?>"
					   name="<?php echo $this->get_field_name( 'hide_address' ); ?>" type="checkbox"
					   value="1" <?php echo ! empty( $hide_address ) ? ' checked="checked"' : ''; ?> />
				<?php _e( 'Hide business address', 'yoast-local-seo' ); ?>
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'show_country' ); ?>">
				<input id="<?php echo $this->get_field_id( 'show_country' ); ?>"
					   name="<?php echo $this->get_field_name( 'show_country' ); ?>" type="checkbox"
					   value="1" <?php echo ! empty( $show_country ) ? ' checked="checked"' : ''; ?> />
				<?php _e( 'Show country', 'yoast-local-seo' ); ?>
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'show_state' ); ?>">
				<input id="<?php echo $this->get_field_id( 'show_state' ); ?>"
					   name="<?php echo $this->get_field_name( 'show_state' ); ?>" type="checkbox"
					   value="1" <?php echo ! empty( $show_state ) ? ' checked="checked"' : ''; ?> />
				<?php _e( 'Show state', 'yoast-local-seo' ); ?>
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'show_phone' ); ?>">
				<input id="<?php echo $this->get_field_id( 'show_phone' ); ?>"
					   name="<?php echo $this->get_field_name( 'show_phone' ); ?>" type="checkbox"
					   value="1" <?php echo ! empty( $show_phone ) ? ' checked="checked"' : ''; ?> />
				<?php _e( 'Show phone number', 'yoast-local-seo' ); ?>
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'show_phone_2' ); ?>">
				<input id="<?php echo $this->get_field_id( 'show_phone_2' ); ?>"
					   name="<?php echo $this->get_field_name( 'show_phone_2' ); ?>" type="checkbox"
					   value="1" <?php echo ! empty( $show_phone_2 ) ? ' checked="checked"' : ''; ?> />
				<?php _e( 'Show second phone number', 'yoast-local-seo' ); ?>
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'show_fax' ); ?>">
				<input id="<?php echo $this->get_field_id( 'show_fax' ); ?>"
					   name="<?php echo $this->get_field_name( 'show_fax' ); ?>" type="checkbox"
					   value="1" <?php echo ! empty( $show_fax ) ? ' checked="checked"' : ''; ?> />
				<?php _e( 'Show fax number', 'yoast-local-seo' ); ?>
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'show_email' ); ?>">
				<input id="<?php echo $this->get_field_id( 'show_email' ); ?>"
					   name="<?php echo $this->get_field_name( 'show_email' ); ?>" type="checkbox"
					   value="1" <?php echo ! empty( $show_email ) ? ' checked="checked"' : ''; ?> />
				<?php _e( 'Show email address', 'yoast-local-seo' ); ?>
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'show_logo' ); ?>">
				<input id="<?php echo $this->get_field_id( 'show_logo' ); ?>"
					   name="<?php echo $this->get_field_name( 'show_logo' ); ?>" type="checkbox"
					   value="1" <?php echo ! empty( $show_logo ) ? ' checked="checked"' : ''; ?> />
				<?php _e( 'Show logo', 'yoast-local-seo' ); ?>
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'show_vat' ); ?>">
				<input id="<?php echo $this->get_field_id( 'show_vat' ); ?>"
					   name="<?php echo $this->get_field_name( 'show_vat' ); ?>" type="checkbox"
					   value="1" <?php echo ! empty( $show_vat ) ? ' checked="checked"' : ''; ?> />
				<?php _e( 'Show VAT ID', 'yoast-local-seo' ); ?>
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'show_tax' ); ?>">
				<input id="<?php echo $this->get_field_id( 'show_tax' ); ?>"
					   name="<?php echo $this->get_field_name( 'show_tax' ); ?>" type="checkbox"
					   value="1" <?php echo ! empty( $show_tax ) ? ' checked="checked"' : ''; ?> />
				<?php _e( 'Show Tax ID', 'yoast-local-seo' ); ?>
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'show_coc' ); ?>">
				<input id="<?php echo $this->get_field_id( 'show_coc' ); ?>"
					   name="<?php echo $this->get_field_name( 'show_coc' ); ?>" type="checkbox"
					   value="1" <?php echo ! empty( $show_coc ) ? ' checked="checked"' : ''; ?> />
				<?php _e( 'Show Chamber of Commerce ID', 'yoast-local-seo' ); ?>
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'show_price_range' ); ?>">
				<input id="<?php echo $this->get_field_id( 'show_price_range' ); ?>"
					   name="<?php echo $this->get_field_name( 'show_price_range' ); ?>" type="checkbox"
					   value="1" <?php echo ! empty( $show_price_range ) ? ' checked="checked"' : ''; ?> />
				<?php _e( 'Show Price Range', 'yoast-local-seo' ); ?>
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'show_opening_hours' ); ?>">
				<input id="<?php echo $this->get_field_id( 'show_opening_hours' ); ?>"
					   name="<?php echo $this->get_field_name( 'show_opening_hours' ); ?>" type="checkbox"
					   value="1" <?php echo ! empty( $show_opening_hours ) ? ' checked="checked"' : ''; ?> />
				<?php _e( 'Show opening hours', 'yoast-local-seo' ); ?>
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'hide_closed' ); ?>">
				<input id="<?php echo $this->get_field_id( 'hide_closed' ); ?>"
					   name="<?php echo $this->get_field_name( 'hide_closed' ); ?>" type="checkbox"
					   value="1" <?php echo ! empty( $hide_closed ) ? ' checked="checked"' : ''; ?> />
				<?php _e( 'Hide closed days', 'yoast-local-seo' ); ?>
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'show_oneline' ); ?>">
				<input id="<?php echo $this->get_field_id( 'show_oneline' ); ?>"
					   name="<?php echo $this->get_field_name( 'show_oneline' ); ?>" type="checkbox"
					   value="1" <?php echo ! empty( $show_oneline ) ? ' checked="checked"' : ''; ?> />
				<?php _e( 'Show address in one line', 'yoast-local-seo' ); ?>
			</label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'comment' ); ?>"><?php _e( 'Extra comment', 'yoast-local-seo' ); ?></label>
			<textarea id="<?php echo $this->get_field_id( 'comment' ); ?>" class="widefat" name="<?php echo $this->get_field_name( 'comment' ); ?>"><?php echo esc_attr( $comment ); ?></textarea>
		</p>
		<?php

		return '';
	}
}
