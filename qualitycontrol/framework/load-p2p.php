<?php

if ( !function_exists( 'p2p_register_connection_type' ) ) :  // In case the full P2P plugin is activated

	define( 'P2P_TEXTDOMAIN', APP_TD );

	require_once dirname( __FILE__ ) . '/p2p-core/init.php';

	add_action( 'appthemes_first_run', array( 'P2P_Storage', 'install' ), 9 );

endif;
