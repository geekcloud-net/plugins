<?php

$settings = array(

	'general' => array(

		'header'    => array(

			array(
				'name' => __( 'General Settings', 'yith-infinite-scrolling' ),
				'type' => 'title'
			),

			array( 'type' => 'close' )
		),


		'settings' => array(

			array( 'type' => 'open' ),

			array(
				'id'      => 'yith-infs-enable',
				'name'    => __( 'Enable Infinite Scrolling', 'yith-infinite-scrolling' ),
				'desc'    => '',
				'type'    => 'on-off',
				'std'     => 'yes'
			),

			array(
				'id'      => 'yith-infs-enable-mobile',
				'name'    => __( 'Enable Infinite Scrolling on mobile device', 'yith-infinite-scrolling' ),
				'desc'    => '',
				'type'    => 'on-off',
				'std'     => 'yes',
				'deps'    => array(
					'ids'       => 'yith-infs-enable',
					'values'    => 'yes'
				)
			),			
			
			array(
				'id'      => 'yith-infs-change-url',
				'name'    => __( 'Change the URL of the page whenever new items are loaded', 'yith-infinite-scrolling' ),
				'desc'    => '',
				'type'    => 'on-off',
				'std'     => 'no',
				'deps'    => array(
					'ids'       => 'yith-infs-enable',
					'values'    => 'yes'
				)
			),

			array(
				'id'      => 'yith-infs-section',
				'name'    => __( 'Add section and set options', 'yith-infinite-scrolling' ),
				'desc'    => '',
				'type'    => 'options-section',
				'deps'    => array(
					'ids'       => 'yith-infs-enable',
					'values'    => 'yes'
				)
			),

			// @@ new strings version 1.0.6

            array(
                'id'      => 'yith-infs-custom-js',
                'name'    => __( 'Custom JavaScript', 'yith-infinite-scrolling' ),
                'desc'    => sprintf( __( 'Add here your custom JavaScript code. You can use two plugin triggers: %s is triggered before the new elements are appended to the current content, and %s is triggered after the AJAX call ends', 'yith-infinite-scrolling' ),
	                '<strong>yith_infs_adding_elem</strong>', '<strong>yith_infs_added_elem</strong>' ),
                'type'    => 'textarea',
                'deps'    => array(
                    'ids'       => 'yith-infs-enable',
                    'values'    => 'yes'
                )
            ),

			array( 'type' => 'close' ),
		)
	)
);

return apply_filters( 'yith_infs_panel_settings_options', $settings );