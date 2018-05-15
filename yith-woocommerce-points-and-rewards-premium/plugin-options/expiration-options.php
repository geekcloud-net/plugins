<?php

$expiration = array(

	'expiration' => array(

		'header'    => array(
			array( 'type' => 'open' ),

			array(
				'name' => __( 'Expiration Options', 'yith-woocommerce-points-and-rewards' ),
				'type' => 'title'
			),

			array( 'type' => 'close' )
		),
	)
);

$expiration['expiration']['expiration'] = array(
	array( 'type' => 'open' ),
);

if( 1 != get_option('ywpar_points_expired_check') && 'from_1.3.0' != get_option('yit_ywpar_expiration_mode') && ! apply_filters( 'ywpar_expiration_old_mode', false ) ){
	$expiration['expiration']['expiration'] [] = array(
		'id'           => 'fix_expiration_points',
		'name'         => __( 'Fix Expired Points', 'yith-woocommerce-points-and-rewards' ),
		'desc'         => __( 'Before enabling point expiration, it is advisable to  back up the databaset and fix the expired points.', 'yith-woocommerce-points-and-rewards' ),
		'type'         => 'text-button',
		'button-class' => 'ywpar_fix_expiration_points',
		'button-name'  => __( 'Fix it!', 'yith-woocommerce-points-and-rewards' ),
		'show_data'    => true,
		'std'          => ''
	);
}else{
	$expiration_close = array(
		array(
			'id'      => 'enable_expiration_point',
			'name'    => __( 'Enable point expiration', 'yith-woocommerce-points-and-rewards' ),
			'desc'    => '',
			'type'    => 'on-off',
			'std'     => 'yes'
		),

		array(
			'id'      => 'days_before_expiration',
			'name'    => __( 'Points valid for (days)', 'yith-woocommerce-points-and-rewards' ),
			'desc'    => __( 'Type here the number of days for point validity', 'yith-woocommerce-points-and-rewards' ),
			'type'    => 'text',
			'std'     => ''
		),

		array(
			'id'      => 'send_email_before_expiration_date',
			'name'    => __( 'Send an email before expiration date', 'yith-woocommerce-points-and-rewards' ),
			'desc'    => '',
			'type'    => 'on-off',
			'std'     => 'yes'
		),

		array(
			'id'      => 'send_email_days_before',
			'name'    => __( 'Days before point expire (days)', 'yith-woocommerce-points-and-rewards' ),
			'desc'    => __( 'Number of days before point expiration when email will be sent', 'yith-woocommerce-points-and-rewards' ),
			'type'    => 'text',
			'std'     => '',
			'deps'    => array(
				'ids'       => 'send_email_before_expiration_date',
				'values'    => 'yes'
			)
		),

		array(
			'id'      => 'expiration_email_content',
			'name'    => __( 'Email content', 'yith-woocommerce-points-and-rewards' ),
			'desc'    => __( 'You can use the following placeholders,<br>
                {username} = customer\'s username <br>
                {expiring_points} = expiring points <br>
                {label_points} = label for points <br>
                {expiring_date} = point expiry date<br>
                {total_points} = current point credit', 'yith-woocommerce-points-and-rewards' ),

			'type'    => 'textarea',
			'std'     => 'Hi {username},
this email has been seent to remind you you have {expiring_points} {label_points} about to expire.
Expiry date is {expiring_date}.',
			'deps'    => array(
				'ids'       => 'send_email_before_expiration_date',
				'values'    => 'yes'
			)
		),

		array( 'type' => 'close' ),
	);

	$expiration['expiration']['expiration'] = array_merge( $expiration['expiration']['expiration'], $expiration_close );
}




return apply_filters( 'yith_ywpar_panel_expiration_options', $expiration );