<?php
/**
 * @package WPSEO_Local\Frontend
 */

/**
 * Class WPSEO_Show_Locations_By_Category.
 *
 * Creates widget for showing the address.
 */
class WPSEO_Show_Locations_By_Category extends WP_Widget {

	/**
	 * WPSEO_Show_Locations_By_Category constructor.
	 */
	function __construct() {
		$widget_options = array(
			'classname'   => 'WPSEO_Show_Locations_By_Category',
			'description' => __( 'Shows a list of location names by category.', 'yoast-local-seo' ),
		);
		parent::__construct( false, $name = __( 'WP SEO - Show Locations By Category', 'yoast-local-seo' ), $widget_options );
	}

	/** @see WP_Widget::widget
	 * Displays the store locator form.
	 *
	 * @param array $args     Array of options for this widget.
	 * @param array $instance Instance of the widget.
	 *
	 * @return string|void
	 */
	function widget( $args, $instance ) {
		$title       = apply_filters( 'widget_title', $instance['title'] );
		$category_id = ! empty( $instance['category_id'] ) ? esc_attr( $instance['category_id'] ) : '';

		if ( empty( $category_id ) ) {
			return;
		}

		$repo = new WPSEO_Local_Locations_Repository();
		$repo->get(array(
			'category_id' => $category_id,
		));
		$locations = $repo->query;

		if ( $locations->post_count > 0 ) {

			if ( isset( $args['before_widget'] ) ) {
				echo $args['before_widget'];
			}

			if ( ! empty( $title ) ) {
				echo $args['before_title'] . $title . $args['after_title'];
			}

			echo '<ul>';
			while ( $locations->have_posts() ) {
				$locations->the_post();
				echo '<li><a href="' . get_permalink() . '">' . get_the_title() . '</a></li>';
			}
			echo '</ul>';

			if ( isset( $args['after_widget'] ) ) {
				echo $args['after_widget'];
			}
		}

		return '';
	}


	/** @see WP_Widget::update
	 * @param array $new_instance New option values for this widget.
	 * @param array $old_instance Old, current option values for this widget.
	 *
	 * @return array
	 */
	function update( $new_instance, $old_instance ) {
		$instance                = $old_instance;
		$instance['title']       = esc_attr( $new_instance['title'] );
		$instance['category_id'] = esc_attr( $new_instance['category_id'] );

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
		$title       = ( ! empty( $instance['title'] ) ) ? esc_attr( $instance['title'] ) : '';
		$category_id = ( ! empty( $instance['category_id'] ) ) ? esc_attr( $instance['category_id'] ) : '';
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'yoast-local-seo' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>"/>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'category_id' ); ?>">
				<?php esc_html_e( 'Category:', 'yoast-local-seo' ); ?>
				<select id="<?php echo $this->get_field_id( 'category_id' ); ?>" name="<?php echo $this->get_field_name( 'category_id' ); ?>">
					<option value=""> -- <?php _e( 'Select a category', 'yoast-local-seo' ); ?> --</option>
					<?php
					$categories = get_terms( 'wpseo_locations_category', array(
						'hide_empty' => false,
					) );

					foreach ( $categories as $category ) {
						?>
						<option value="<?php echo $category->term_id; ?>" <?php selected( $category_id, $category->term_id ); ?>><?php echo $category->name; ?></option>
						<?php
					}
					?>
				</select>
			</label>
		</p>
		<?php

		return '';
	}
}
