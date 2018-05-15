<?php

require_once MONSTERINSIGHTS_PLUGIN_DIR . 'pro/includes/admin/settings.php';

if ( is_admin() ) {		
	require_once MONSTERINSIGHTS_PLUGIN_DIR . 'pro/includes/admin/reports.php';
	new MonsterInsights_Admin_Pro_Reports();
	require_once MONSTERINSIGHTS_PLUGIN_DIR . 'pro/includes/admin/tools.php';

	//require_once MONSTERINSIGHTS_PLUGIN_DIR . 'pro/includes/admin/tab-support.php';
}
	
require_once MONSTERINSIGHTS_PLUGIN_DIR . 'pro/includes/frontend/class-frontend.php';