<?php
/**
 * Use this file to include required files for the front-end
 * Keep it simple
 */
require_once TVE_Ult_Const::plugin_path() . 'inc/functions.php';
require_once TVE_Ult_Const::plugin_path() . 'inc/class-tve-ult-db.php';
require_once TVE_Ult_Const::plugin_path() . 'inc/helpers.php';
require_once TVE_Ult_Const::plugin_path() . 'inc/data.php';
require_once TVE_Ult_Const::plugin_path() . 'inc/classes/class-tu-request-handler.php';
require_once TVE_Ult_Const::plugin_path() . 'inc/classes/class-tu-template-manager.php';
require_once TVE_Ult_Const::plugin_path() . 'inc/classes/class-tu-state-manager.php';
require_once TVE_Ult_Const::plugin_path() . 'inc/classes/class-tu-schedule-abstract.php';
require_once TVE_Ult_Const::plugin_path() . 'inc/classes/class-tu-schedule-absolute.php';
require_once TVE_Ult_Const::plugin_path() . 'inc/classes/class-tu-schedule-rolling.php';
require_once TVE_Ult_Const::plugin_path() . 'inc/classes/class-tu-schedule-evergreen.php';
require_once TVE_Ult_Const::plugin_path() . 'inc/classes/class-tu-timeline.php';
require_once TVE_Ult_Const::plugin_path() . 'inc/classes/class-tu-event-action.php';
require_once TVE_Ult_Const::plugin_path() . 'inc/classes/class-tu-campaign-event.php';
require_once TVE_Ult_Const::plugin_path() . 'inc/classes/class-tu-event.php';
require_once TVE_Ult_Const::plugin_path() . 'inc/classes/class-tu-shortcodes.php';

if ( ! is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
	require_once TVE_Ult_Const::plugin_path() . 'inc/classes/class-tu-frontend-handler.php';
	/** @var TU_Frontend_Handler $tve_ult_frontend */
	global $tve_ult_frontend;
	$tve_ult_frontend = new TU_Frontend_Handler();
}

require_once TVE_Ult_Const::plugin_path() . 'tcb-bridge/tcb_hooks.php';

require_once TVE_Ult_Const::plugin_path() . 'database/class-tve-ult-database-manager.php';
Tve_Ult_Database_Manager::check();

require_once TVE_Ult_Const::plugin_path() . 'inc/hooks.php';
