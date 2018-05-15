<?php

class QC_Dashboard extends APP_DashBoard {

	const SUPPORT_FORUM = 'http://forums.appthemes.com/external.php?type=RSS2';

	public function __construct() {

		parent::__construct( array(
			'page_title' => __( 'Quality Control Dashboard', APP_TD ),
			'menu_title' => __( 'Quality Control', APP_TD ),
			'icon_url' => appthemes_locate_template_uri( 'images/admin-menu.png' ),
		) );

		$this->boxes[] = array( 'support_forum', $this->box_icon( 'comments.png' ) . __( 'Support Forum', APP_TD ), 'normal', 'low' );

		$stats_icon = $this->box_icon( 'chart-bar.png' );
		$stats = array( 'stats', $stats_icon . __( 'Snapshot', APP_TD ), 'normal' );
		array_unshift( $this->boxes, $stats );

	}

	public function stats_box() {

		$users = array();
		$users_stats = $this->get_user_counts();

		$users[ __( 'New Registrations Today', APP_TD ) ] = $users_stats['today'];
		$users[ __( 'New Registrations Yesterday', APP_TD ) ] = $users_stats['yesterday'];

		$users[ __( 'Total Users', APP_TD ) ] = array(
			'text' => $users_stats['total_users'],
			'url' => 'users.php',
		);

		$this->output_list( $users, '<ul style="float: right; width: 45%">' );

		$stats = array();

		$listings = $this->get_listing_counts();

		if ( isset( $listings['all'] ) ) {
			$stats[ __( 'Total Tickets', APP_TD ) ] = array(
				'text' => $listings['all'],
				'url' => add_query_arg( array( 'post_type' => QC_TICKET_PTYPE ), admin_url( 'edit.php' ) ),
			);
		} else {
			$stats[ __( 'Total Tickets', APP_TD ) ] = 0;
		}

		if ( current_theme_supports( 'changesets' ) ) {
			$changesets = $this->get_listing_counts( QC_CHANGESET_PTYPE );

			if ( isset( $changesets['all'] ) ) {
				$stats[ __( 'Total Changesets', APP_TD ) ] = array(
					'text' => $changesets['all'],
					'url' => add_query_arg( array( 'post_type' => QC_CHANGESET_PTYPE ), admin_url( 'edit.php' ) ),
				);
			} else {
				$stats[ __( 'Total Changesets', APP_TD ) ] = 0;
			}
		
		}

		$stats[ __( 'Product Version', APP_TD ) ] = QC_VERSION;
		$stats[ __( 'Product Support', APP_TD ) ] = html( 'a', array( 'href' => 'http://forums.appthemes.com' ), __( 'Forum', APP_TD ) );
		$stats[ __( 'Product Support', APP_TD ) ] .= ' | ' . html( 'a', array( 'href' => 'http://docs.appthemes.com/' ), __( 'Documentation', APP_TD ) );

		$this->output_list( $stats );

	}

	public function support_forum_box() {
		echo '<div class="rss-widget">';
		wp_widget_rss_output( self::SUPPORT_FORUM, array( 'items' => 5, 'show_author' => 0, 'show_date' => 1, 'show_summary' => 1 ) );
		echo '</div>';
	}

	private function output_list( $array, $begin = '<ul>', $end = '</ul>', $echo = true ) {

		$html = '';
		foreach ( $array as $title => $value ) {
			if ( is_array( $value ) ) {
				$html .= '<li>' . $title . ': <a href="' . $value['url'] . '">' . $value['text'] . '</a></li>';
			} else {
				$html .= '<li>' . $title . ': ' . $value . '</li>';
			}
		}

		$html = $begin . $html . $end;

		if ( $echo ) {
			echo $html;
		} else {
			return $html;
		}
	}

	private function get_user_counts() {
		global $wpdb;

		$users = (array) count_users();

		$capabilities_meta = $wpdb->prefix . 'capabilities';
		$date_today = date( 'Y-m-d', current_time( 'timestamp' ) );
		$date_yesterday = date( 'Y-m-d', strtotime( '-1 days', current_time( 'timestamp' ) ) );

		$users['today'] = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(ID) FROM $wpdb->users INNER JOIN $wpdb->usermeta ON $wpdb->users.ID = $wpdb->usermeta.user_id WHERE $wpdb->usermeta.meta_key = %s AND ($wpdb->usermeta.meta_value NOT LIKE %s) AND $wpdb->users.user_registered >= %s", $capabilities_meta, '%administrator%', $date_today ) );
		$users['yesterday'] = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(ID) FROM $wpdb->users INNER JOIN $wpdb->usermeta ON $wpdb->users.ID = $wpdb->usermeta.user_id WHERE $wpdb->usermeta.meta_key = %s AND ($wpdb->usermeta.meta_value NOT LIKE %s) AND $wpdb->users.user_registered BETWEEN %s AND %s", $capabilities_meta, '%administrator%', $date_yesterday, $date_today ) );

		return $users;
	}

	private function get_listing_counts( $post_type = QC_TICKET_PTYPE ) {

		$listings = (array) wp_count_posts( $post_type );

		$all = 0;
		foreach ( (array) $listings as $type => $count ) {
			$all += $count;
		}
		$listings['all'] = $all;

		return $listings;
	}

}
