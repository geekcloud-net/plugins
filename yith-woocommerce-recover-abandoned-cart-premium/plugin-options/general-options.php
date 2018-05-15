<?php

$settings = array(

    'general' => array(

            'section_general_settings'     => array(
                'name' => __( 'General settings', 'yith-woocommerce-recover-abandoned-cart' ),
                'type' => 'title',
                'id'   => 'ywrac_section_general'
            ),

            'enabled' => array(
                'name'    =>  __( 'Enable Recover Abandoned Cart', 'yith-woocommerce-recover-abandoned-cart' ),
                'desc'    => '',
                'id'      => 'ywrac_enabled',
                'type'    => 'checkbox',
                'default' => 'yes'
            ),

            'enable_shop_manager' => array(
                'name'    =>  __( 'Enable Recover Abandoned Cart to Shop Manager', 'yith-woocommerce-recover-abandoned-cart' ),
                'desc'    => '',
                'id'      => 'ywrac_enable_shop_manager',
                'type'    => 'checkbox',
                'default' => 'no'
            ),

            'cut_off_time' => array(
                'name'    =>  __( 'Cut-off time for abandoned carts', 'yith-woocommerce-recover-abandoned-cart' ),
                'desc'    =>  __( 'Minutes that have to pass to consider a cart abandoned', 'yith-woocommerce-recover-abandoned-cart' ),
                'id'      => 'ywrac_cut_off_time',
                'type'    => 'text',
                'default' => '60'
            ),

            'delete_cart' => array(
                'name'    =>  __( 'Delete Abandoned Cart after:', 'yith-woocommerce-recover-abandoned-cart' ),
                'desc'    => __( 'Hours that have to pass to delete an abandoned cart. Leave zero to never delete a cart', 'yith-woocommerce-recover-abandoned-cart' ),
                'id'      => 'ywrac_delete_cart',
                'type'    => 'text',
                'default' => '160'
            ),

            'section_end_form'=> array(
                'type'              => 'sectionend',
                'id'                => 'ywrac_section_general_end_form'
            ),
			//from 1.1.0
            'section_general_settings_pending_orders'     => array(
	            'name' => __( 'Pending Orders', 'yith-woocommerce-recover-abandoned-cart' ),
	            'type' => 'title',
	            'id'   => 'ywrac_section_general_pending_orders'
            ),
	        //from 1.1.0
            'pending_orders_enabled' => array(
	            'name'    =>  __( 'Enable Recover Pending Orders', 'yith-woocommerce-recover-abandoned-cart' ),
	            'desc'    => '',
	            'id'      => 'ywrac_pending_orders_enabled',
	            'type'    => 'checkbox',
	            'default' => 'no'
            ),
	        //from 1.1.0
            'pending_orders_delete' => array(
	            'name'    =>  __( 'Delete Pending Orders after:', 'yith-woocommerce-recover-abandoned-cart' ),
	            'desc'    =>  __( 'Hours that have to pass to delete an pending orders. Leave zero to never delete a pending order', 'yith-woocommerce-recover-abandoned-cart' ),
	            'id'      => 'ywrac_pending_orders_delete',
	            'type'    => 'text',
	            'default' => '360'
            ),

            'section_end_form_pending_orders'=> array(
	            'type'              => 'sectionend',
	            'id'                => 'ywrac_section_general_end_form_pending_orders'
            ),

            'section_user_settings'     => array(
                'name' => __( 'User Settings', 'yith-woocommerce-recover-abandoned-cart' ),
                'type' => 'title',
                'id'   => 'ywrac_section_user'
            ),

            'enable_guest' => array(
                'name'    =>  __( 'Enable Guests', 'yith-woocommerce-recover-abandoned-cart' ),
                'desc'    => __( 'Guests\' abandoned carts will be saved only if they write their emails in the checkout page.', 'yith-woocommerce-recover-abandoned-cart' ),
                'id'      => 'ywrac_user_guest_enabled',
                'type'    => 'checkbox',
                'default' => 'yes'
            ),

            'user_roles' => array(
                'name'     => __( 'Enable for these roles', 'yith-woocommerce-recover-abandoned-cart' ),
                'desc'     => '',
                'id'       => 'ywrac_user_roles',
                'class'    => 'ywrac-chosen wc-enhanced-select',
                'type'     => 'multiselect',
                'multiple' => true,
                'options'  => yith_ywrac_get_roles(),
                'default'  => 'all'
            ),

            'section_user_end_form'=> array(
                'type'              => 'sectionend',
                'id'                => 'ywrac_section_user_general_end_form'
            ),

            'section_email_settings'     => array(
                'name' => __( 'User Email Settings', 'yith-woocommerce-recover-abandoned-cart' ),
                'type' => 'title',
                'id'   => 'ywrac_section_email'
            ),

            'sender_name' => array(
                'name' => __( 'Email Sender Name', 'yith-woocommerce-recover-abandoned-cart' ),
                'desc' => '',
                'id'   => 'ywrac_sender_name',
                'type' => 'text',
                'default'  => get_bloginfo( 'name' )
            ),

            'sender_email' => array(
                'name' => __( 'Email Sender', 'yith-woocommerce-recover-abandoned-cart' ),
                'desc' => '',
                'id'   => 'ywrac_email_sender',
                'type' => 'text',
                'default'  => get_bloginfo( 'admin_email' )
            ),

            'reply_to' => array(
                'name' => __( 'Reply To:', 'yith-woocommerce-recover-abandoned-cart' ),
                'desc' => '',
                'id'   => 'ywrac_email_reply',
                'type' => 'text',
                'default'  => ''
            ),


            'section_email_end_form'=> array(
                'type'              => 'sectionend',
                'id'                => 'ywrac_section_email_end_form'
            ),

            'section_email_admin_settings'     => array(
                'name' => __( 'Admin Email Settings', 'yith-woocommerce-recover-abandoned-cart' ),
                'type' => 'title',
                'id'   => 'ywrac_section_email_admin'
            ),

            'enable_email_admin' => array(
                'name'    =>  __( 'Send an email to administrators when an abandoned cart is recovered', 'yith-woocommerce-recover-abandoned-cart' ),
                'desc'    => '',
                'id'      => 'ywrac_enable_email_admin',
                'type'    => 'checkbox',
                'default' => 'yes'
            ),

            'email_admin_sender_name' => array(
                'name' => __( 'Admin Email Sender Name', 'yith-woocommerce-recover-abandoned-cart' ),
                'desc' => '',
                'id'   => 'ywrac_admin_sender_name',
                'type' => 'text',
                'default'  => get_bloginfo( 'name' )
            ),

            'email_admin_recipient' => array(
                'name' => __( 'Email Recipients', 'yith-woocommerce-recover-abandoned-cart' ),
                'desc' => __('Enter recipients (separated by commas)','yith-woocommerce-recover-abandoned-cart'),
                'id'   => 'ywrac_admin_email_recipient',
                'type' => 'text',
                'default'  => get_bloginfo( 'admin_email' )
            ),


            'email_admin_subject' => array(
                'name' => __( 'Email Subject', 'yith-woocommerce-recover-abandoned-cart' ),
                'desc' => '',
                'id'   => 'ywrac_admin_email_subject',
                'type' => 'text',
                'default'  => __('New Recovered Cart','yith-woocommerce-recover-abandoned-cart')
            ),

            'section_email_admin_end_form'=> array(
                'type'              => 'sectionend',
                'id'                => 'ywrac_section_email_admin_end_form'
            ),

            'section_cron_settings'     => array(
                'name' => __( 'Cron Settings', 'yith-woocommerce-recover-abandoned-cart' ),
                'type' => 'title',
                'id'   => 'ywrac_section_cron'
            ),


            'cron_time_type' => array(
                'name' => __( 'Cron time type', 'yith-woocommerce-recover-abandoned-cart' ),
                'desc' => '',
                'id'   => 'ywrac_cron_time_type',
                'type' => 'select',
                'options' => array(
                    'minutes' => __('Minutes','yith-woocommerce-recover-abandoned-cart'),
                    'hours' => __('Hours','yith-woocommerce-recover-abandoned-cart'),
                    'days' => __('Days','yith-woocommerce-recover-abandoned-cart'),
                ),
                'default'  => 'minutes'
            ),

            'cron_time' => array(
                'name' => __( 'Cron time', 'yith-woocommerce-recover-abandoned-cart' ),
                'desc' => '',
                'id'   => 'ywrac_cron_time',
                'type' => 'text',
                'default'  => '10'
            ),

            'section_cron_settings_end_form'=> array(
                'type'              => 'sectionend',
                'id'                => 'ywrac_section_email_admin_end_form'
            ),

    )

);

return apply_filters( 'yith_ywrac_panel_settings_options', $settings );