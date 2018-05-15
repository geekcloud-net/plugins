<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/**
 * Save the Notification Post
 *
 * @param $model
 *
 * @return int|false
 */
function td_nm_save_notification( $model ) {
	$defaults = array(
		'post_type'   => 'td_nm_notification',
		'post_status' => 'publish',
	);

	if ( empty( $model['ID'] ) ) {
		$id = wp_insert_post( array_merge( $defaults, $model ) );
	} else {
		$id = wp_update_post( $model );
	}

	if ( empty( $id ) || is_wp_error( $id ) ) {
		return false;
	}

	//save trigger or delete the notification
	$trigger_saved = td_nm_save_trigger( $model['trigger'], $id );
	if ( empty( $trigger_saved ) ) {
		td_nm_delete_notification( $id );

		return false;
	}

	//save the actions or delete the notification
	$actions_saved = td_nm_save_actions( $model['actions'], $id );
	if ( empty( $actions_saved ) ) {
		td_nm_delete_notification( $id );

		return false;
	}

	return $id;
}

/**
 * Assign some post_meta for the a notification|post
 *
 * @param array $trigger
 * @param int   $notification_id
 *
 * @return int|false
 */
function td_nm_save_trigger( $trigger, $notification_id ) {
	if ( empty( $trigger['type'] ) || empty( $trigger['settings'] ) || get_post_status( $notification_id ) === false ) {
		return false;
	}

	$old_type     = get_post_meta( $notification_id, 'td_nm_trigger_type', true );
	$old_settings = get_post_meta( $notification_id, 'td_nm_trigger_settings', true );

	if ( $trigger['type'] === $old_type && $old_settings === $trigger['settings'] ) {
		return $notification_id;
	}

	update_post_meta( $notification_id, 'td_nm_trigger_type', $trigger['type'] );
	update_post_meta( $notification_id, 'td_nm_trigger_settings', $trigger['settings'] );

	return $notification_id;
}

/**
 * Read Notifications from DB based on filters
 *
 * @param array $filters
 *
 * @return array
 */
function td_nm_get_notifications( $filters = array() ) {
	$defaults = array(
		'posts_per_page' => - 1,
		'post_type'      => 'td_nm_notification',
		'orderby'        => 'date',
		'order'          => 'ASC',
	);

	if ( ! function_exists( 'is_plugin_active' ) ) {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}

	$filters       = array_merge( $defaults, $filters );
	$posts         = get_posts( $filters );
	$notifications = array();

	foreach ( $posts as $post ) {

		$trigger          = td_nm_get_trigger( $post->ID );
		$trigger_instance = TD_NM_Trigger_Factory::get_instance( $trigger );

		if ( $applicable = $trigger_instance->is_notification_applicable() ) {
			$post->trigger = $trigger;
			$post->actions = td_nm_get_actions( $post->ID );;
			$notifications[] = $post;
		}
	}

	return $notifications;
}

/**
 * Get trigger for a notification|post
 *
 * @param int $notification_id
 *
 * @return array
 */
function td_nm_get_trigger( $notification_id ) {
	$item = array(
		'type'     => null,
		'settings' => array(),
	);

	$item['type']     = get_post_meta( $notification_id, 'td_nm_trigger_type', true );
	$item['settings'] = get_post_meta( $notification_id, 'td_nm_trigger_settings', true );
	$item['ID']       = $notification_id;

	return $item;
}


/**
 * Get actions for a notification|post
 *
 * @param int $notification_id
 *
 * @return array
 */
function td_nm_get_actions( $notification_id ) {
	$items = get_post_meta( $notification_id, 'td_nm_actions', true );
	foreach ( $items as $key => &$item ) {
		$item['ID'] = $key;
	}

	return $items;
}

/**
 * @param $actions
 * @param $notification_id
 *
 * @return bool|int
 */
function td_nm_save_actions( $actions, $notification_id ) {
	foreach ( $actions as &$action ) {
		$action['notification_id'] = $notification_id;
	}

	return update_post_meta( $notification_id, 'td_nm_actions', $actions );
}

/**
 * Delete notification|post with all its data|meta
 *
 * @param $notification_id
 *
 * @return bool
 */
function td_nm_delete_notification( $notification_id ) {
	$post_deleted = wp_delete_post( $notification_id, true );

	return empty( $post_deleted ) || is_wp_error( $post_deleted ) ? false : true;
}
