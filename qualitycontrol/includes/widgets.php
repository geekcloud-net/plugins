<?php
/**
 * Widget for the taxonomies
 *
 * @since Quality Control 0.1
 */
class QC_Widget_Taxonomy extends scbWidget {

	protected $defaults = array(
		'title' => '',
		'taxonomy' => '',
		'show_rss' => '',
		'show_count' => ''
	);

	function __construct() {
		parent::__construct(
			'cat-tax',
			 __( 'QC: Taxonomy', APP_TD ),
			array(
				'description' => __( 'Create a list of any taxonomy.', APP_TD )
			)
		);
	}

	function form( $instance ) {
		if ( empty( $instance ) )
			$instance = $this->defaults;

		$taxonomies = array();
		foreach ( get_taxonomies( array( 'public' => true ), 'object' ) as $tax )
			$taxonomies[ $tax->name ] = $tax->labels->singular_name;

		$fields = array(
			array(
				'name' => 'title',
				'type' => 'text',
				'desc' => __( 'Title:', APP_TD ),
				'extra' => array( 'class' => 'widefat' )
			),

			array(
				'name' => 'taxonomy',
				'type' => 'select',
				'desc' => __( 'Taxonomy:', APP_TD ),
				'values' => $taxonomies
			),

			array(
				'name' => 'show_rss',
				'type' => 'checkbox',
				'desc' => __( 'Show RSS Link', APP_TD ),
			),

			array(
				'name' => 'show_count',
				'type' => 'checkbox',
				'desc' => __( 'Show Ticket Count', APP_TD ),
			),
		);

		foreach ( $fields as $field ) {
			echo html( 'p', $this->input( $field, $instance ) );
		}
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['taxonomy'] = strip_tags( $new_instance['taxonomy'] );
		$instance['show_rss'] = isset( $new_instance['show_rss'] );
		$instance['show_count'] = isset( $new_instance['show_count'] );

		return $instance;
	}

	function content( $instance ) {
		extract( $instance );
?>
			<ul>
				<?php
					$taxes = get_categories( array(
						'hide_empty' => 0,
						'taxonomy' => $instance['taxonomy'],
						'orderby' => 'name'
					) );
					if ( $taxes ) : foreach ( $taxes as $tax ) :
				?>
						<li>
							<a href="<?php echo get_term_link( $tax, $instance['taxonomy'] ); ?>?feed=rss2" class="rss">
								<img src="<?php echo appthemes_locate_template_uri( 'images/rss.gif' ); ?>" alt="RSS" />
							</a>
							<a href="<?php echo get_term_link( $tax, $instance['taxonomy'] ); ?>" title="<?php printf( __( 'View all tickets marked %s', APP_TD ), $tax->name ); ?>">
								<?php echo $tax->name; ?>
								<?php if ( isset( $instance['show_count'] ) ) : ?><small>(<?php echo $tax->count; ?>)</small><?php endif; ?>
							</a>
						</li>
				<?php endforeach; else : ?>

					<li><?php _e( 'No Results', APP_TD ); ?></li>

				<?php endif; ?>
			</ul>
<?php
	}
}


/**
 * Used by QC_Widget_Team widget
 */
class QC_User_Activity {

	static function init() {
		add_action( 'wp_insert_comment', array( __CLASS__, 'post_comment' ), 10, 2 );
		add_action( 'qc_create_ticket', array( __CLASS__, 'post_ticket' ), 10, 2 );
	}

	static function post_ticket( $ticket_id, $ticket ) {
		self::update_last_activity( $ticket['ticket_author'] );
	}

	static function post_comment( $comment_id, $comment ) {
		self::update_last_activity( $comment->user_id );
	}

	static function update_last_activity( $user_id ) {
		update_user_meta( $user_id, '_last_activity', current_time( 'mysql' ) );
	}

	static function get( $user_id ) {
		return get_user_meta( $user_id, '_last_activity', true );
	}
}
QC_User_Activity::init();

/**
 * Widget for the project team to list
 * them out with last activity time
 *
 * @since Quality Control 0.4
 */
class QC_Widget_Team extends scbWidget {

	function __construct() {
		$this->defaults = array(
			'title' => __( 'Project Team', APP_TD )
		);

		parent::__construct( 'qc_project_team', __( 'QC: Project Team', APP_TD ), array(
			'description' => __( 'Lists all team members assigned to this project and the time of their last activity.', APP_TD )
		) );
	}

	function content( $instance ) {
		extract( $instance );

		$users = get_users();

		echo '<ul>';

		foreach ( $users as $user ) {
			echo '<li>';

			printf(
				'<a href="%1$s" title="%2$s">%3$s%4$s</a>',
				get_author_posts_url( $user->ID, $user->user_nicename ),
				sprintf( __( 'View tickets by %s', APP_TD ), $user->user_nicename ),
				get_avatar( $user->ID, '28' ),
				$user->display_name
			);

			if ( current_theme_supports( 'ticket-assignment' ) ) {
				printf(
					' <span class="assignment"><a href="%1$s" title="%2$s">%3$s</a></span>',
					qc_get_assigned_to_url( $user->user_login ),
					sprintf( __( 'View tickets assigned to %s', APP_TD ), $user->user_nicename ),
					__( '(assigned to)', APP_TD )
				);
			}

			if ( $last_activity = QC_User_Activity::get( $user->ID ) ) {
				echo ' <span class="activity">' . sprintf( __( 'Last activity: %s ago', APP_TD ), human_time_diff( strtotime( $last_activity ) ) ) . '</span>';
			}

			echo '</li>';
		}

		echo '</ul>';
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$new_instance = wp_parse_args( (array) $new_instance, array( 'title' => '') );
		$instance['title'] = strip_tags( $new_instance['title'] );
		return $instance;
	}

	function form( $instance ) {
		if ( empty( $instance ) )
			$instance = $this->defaults;

		extract( $instance );
?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', APP_TD ); ?></label>
			<input type="text" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $title ); ?>" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" />
		</p>
<?php
	}
}


/**
 * Recent Tickets widget
 *
 * @since 0.7
 */
class QC_Widget_Recent_Tickets extends WP_Widget {

	function __construct() {
		$widget_ops = array( 'classname' => 'widget_recent_tickets', 'description' => __( 'The most recent tickets on your site', APP_TD ) );
		parent::__construct( 'recent-tickets', __( 'QC: Recent Tickets', APP_TD ), $widget_ops );

		add_action( 'save_post', array( $this, 'flush_widget_cache' ) );
		add_action( 'deleted_post', array( $this, 'flush_widget_cache' ) );
		add_action( 'switch_theme', array( $this, 'flush_widget_cache' ) );
	}

	function widget( $args, $instance ) {
		$cache = wp_cache_get( 'widget_recent_tickets', 'widget' );

		if ( ! is_array( $cache ) ) {
			$cache = array();
		}

		if ( ! isset( $args['widget_id'] ) ) {
			$args['widget_id'] = $this->id;
		}

		if ( isset( $cache[ $args['widget_id'] ] ) ) {
			echo $cache[ $args['widget_id'] ];
			return;
		}

		ob_start();
		extract( $args );

		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Recent Tickets', APP_TD ) : $instance['title'], $instance, $this->id_base );
		if ( empty( $instance['number'] ) || ! $number = absint( $instance['number'] ) ) {
 			$number = 10;
 		}
		$show_date = isset( $instance['show_date'] ) ? $instance['show_date'] : false;

		$tickets_args = array(
			'post_type' => QC_TICKET_PTYPE,
			'post_status' => 'publish',
			'posts_per_page' => $number,
			'no_found_rows' => true,
			'ignore_sticky_posts' => true,
		);

		$tickets = new WP_Query( apply_filters( 'widget_tickets_args', $tickets_args ) );
		if ( $tickets->have_posts() ) {
			echo $before_widget;

			if ( $title )
				echo $before_title . $title . $after_title;

			echo '<ul>';

			while ( $tickets->have_posts() ) {
				$tickets->the_post();

				echo '<li>';

				echo html( 'a', array( 'href' => get_permalink(), 'title' => esc_attr( get_the_title() ) ), get_the_title() );

				if ( $show_date ) {
					echo html( 'span class="post-date"', get_the_date() );
				}

				echo '</li>';
			}

			echo '</ul>';

			echo $after_widget;

			// Reset the global $the_post as this query will have stomped on it
			wp_reset_postdata();

		}

		$cache[ $args['widget_id'] ] = ob_get_flush();
		wp_cache_set( 'widget_recent_tickets', $cache, 'widget' );
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['number'] = (int) $new_instance['number'];
		$instance['show_date'] = (bool) $new_instance['show_date'];
		$this->flush_widget_cache();

		return $instance;
	}

	function flush_widget_cache() {
		wp_cache_delete( 'widget_recent_tickets', 'widget' );
	}

	function form( $instance ) {
		$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$number = isset( $instance['number'] ) ? absint( $instance['number'] ) : 5;
		$show_date = isset( $instance['show_date'] ) ? (bool) $instance['show_date'] : false;
?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', APP_TD ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p><label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of tickets to show:', APP_TD ); ?></label>
		<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo $number; ?>" size="3" /></p>

		<p><input class="checkbox" type="checkbox" <?php checked( $show_date ); ?> id="<?php echo $this->get_field_id( 'show_date' ); ?>" name="<?php echo $this->get_field_name( 'show_date' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'show_date' ); ?>"><?php _e( 'Display ticket date?', APP_TD ); ?></label></p>
<?php
	}
}


/**
 * Recent Tickets Updates widget
 *
 * @since 0.8
 */
class QC_Widget_Recent_Tickets_Updates extends WP_Widget {

	function __construct() {
		$widget_ops = array( 'classname' => 'widget_recent_tickets_updates', 'description' => __( 'The most recent tickets updates on your site.', APP_TD ) );
		parent::__construct( 'recent-tickets-updates', __( 'QC: Recent Tickets Updates', APP_TD ), $widget_ops );
		$this->alt_option_name = 'widget_recent_tickets_updates';

		add_action( 'comment_post', array( $this, 'flush_widget_cache' ) );
		add_action( 'edit_comment', array( $this, 'flush_widget_cache' ) );
		add_action( 'transition_comment_status', array( $this, 'flush_widget_cache' ) );
	}

	function flush_widget_cache() {
		wp_cache_delete( 'widget_recent_tickets_updates', 'widget' );
	}

	function widget( $args, $instance ) {
		global $comments, $comment;

		$cache = wp_cache_get( 'widget_recent_tickets_updates', 'widget' );

		if ( ! is_array( $cache ) ) {
			$cache = array();
		}

		if ( ! isset( $args['widget_id'] ) ) {
			$args['widget_id'] = $this->id;
		}

		if ( isset( $cache[ $args['widget_id'] ] ) ) {
			echo $cache[ $args['widget_id'] ];
			return;
		}

 		extract( $args, EXTR_SKIP );
 		$output = '';

		$title = ( ! empty( $instance['title'] ) ) ? $instance['title'] : __( 'Recent Tickets Updates', APP_TD );
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );
		$number = ( ! empty( $instance['number'] ) ) ? absint( $instance['number'] ) : 5;
		if ( ! $number ) {
 			$number = 5;
 		}

		$comments = get_comments( apply_filters( 'qc_widget_recent_tickets_updates_args', array( 'number' => $number, 'status' => 'approve', 'post_status' => 'publish', 'post_type' => QC_TICKET_PTYPE ) ) );
		$output .= $before_widget;
		if ( $title ) {
			$output .= $before_title . $title . $after_title;
		}

		$output .= '<ul id="recent-tickets-updates">';
		if ( $comments ) {
			foreach ( (array) $comments as $comment) {
				$output .= html( 'li', sprintf(
					_x( '%1$s on %2$s', 'widget recent updates', APP_TD ),
					get_comment_author_link(),
					html_link( get_comment_link( $comment->comment_ID ), get_the_title( $comment->comment_post_ID ) )//'<a href="' . esc_url( get_comment_link($comment->comment_ID) ) . '">' . get_the_title($comment->comment_post_ID) . '</a>'
				) );
			}
 		}
		$output .= '</ul>';
		$output .= $after_widget;

		echo $output;
		$cache[ $args['widget_id'] ] = $output;
		wp_cache_set( 'widget_recent_tickets_updates', $cache, 'widget' );
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['number'] = absint( $new_instance['number'] );
		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset( $alloptions['widget_recent_tickets_updates'] ) ) {
			delete_option( 'widget_recent_tickets_updates' );
		}

		return $instance;
	}

	function form( $instance ) {
		$title  = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$number = isset( $instance['number'] ) ? absint( $instance['number'] ) : 5;
?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', APP_TD ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p><label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of updates to show:', APP_TD ); ?></label>
		<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo $number; ?>" size="3" /></p>
<?php
	}
}


/**
 * Register widgets
 */
function qc_init_widgets() {
	register_widget( 'QC_Widget_Taxonomy' );
	register_widget( 'QC_Widget_Team' );
	register_widget( 'QC_Widget_Recent_Tickets' );
	register_widget( 'QC_Widget_Recent_Tickets_Updates' );

	// remove some of the default sidebar widgets
	//unregister_widget( 'WP_Widget_Calendar' );
	//unregister_widget( 'WP_Widget_Categories' );
	//unregister_widget( 'WP_Widget_Recent_Posts' );
	//unregister_widget( 'WP_Widget_Archives' );
	//unregister_widget( 'WP_Widget_Links' );
	//unregister_widget( 'WP_Widget_Search' );
	//unregister_widget( 'WP_Widget_Tag_Cloud' );
}
add_action( 'widgets_init', 'qc_init_widgets' );


/**
 * Display only comments for posts in Recent Comments widget.
 *
 * @param array $args An array of args to query comments
 *
 * @return array
 */
function qc_widget_recent_comments_args( $args ) {
	$args['post_type'] = 'post';

	return $args;
}
add_filter( 'widget_comments_args', 'qc_widget_recent_comments_args' );

