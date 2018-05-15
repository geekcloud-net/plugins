<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function monsterinsights_detect_uninstalled_addons( $settings ) {
	$network_license = get_site_option( 'monsterinsights_license', false );
	$local_license   = get_option( 'monsterinsights_license', false );
	
	if ( current_user_can( 'monsterinsights_save_settings' ) ) {
		if ( ( monsterinsights_is_network_active() && ! empty( $network_license ) ) || ( monsterinsights_is_network_active() && empty( $network_license ) && ! empty( $local_license ) ) || ( ! monsterinsights_is_network_active() && ! empty( $local_license ) ) ) {
			// Show notices they can install
			if ( ! class_exists( 'MonsterInsights_Social' ) ) {
				$settings['social']['social_notice']['id'] = 'social_notice';
				$settings['social']['social_notice']['type'] = 'notice';
				$settings['social']['social_notice']['desc'] = sprintf( esc_html__( 'In order to use the social tracking features, please %1$sinstall and activate%2$s the social addon.', 'ga-premium'), '<a href="' . admin_url( 'admin.php?page=monsterinsights_addons' ) . '"  >', '</a>' );
			}
			if ( ! class_exists( 'MonsterInsights_Ads' ) ) {
				$settings['ads']['ads_notice']['id'] = 'ads_notice';
				$settings['ads']['ads_notice']['type'] = 'notice';
				$settings['ads']['ads_notice']['desc'] = sprintf( esc_html__( 'In order to use the ads tracking features, please %1$sinstall and activate%2$s the ads addon.', 'ga-premium'), '<a href="' . admin_url( 'admin.php?page=monsterinsights_addons' ) . '"  >', '</a>' );
			}
			if ( ! class_exists( 'MonsterInsights_Forms' ) ) {
				$settings['forms']['forms_notice']['id'] = 'forms_notice';
				$settings['forms']['forms_notice']['type'] = 'notice';
				$settings['forms']['forms_notice']['desc'] = sprintf( esc_html__( 'In order to use the forms tracking features, please %1$sinstall and activate%2$s the forms addon.', 'ga-premium'), '<a href="' . admin_url( 'admin.php?page=monsterinsights_addons' ) . '"  >', '</a>' );
			}
			if ( ! class_exists( 'MonsterInsights_eCommerce' ) ) {
				$settings['ecommerce']['ecommerce_notice']['id'] = 'ecommerce_notice';
				$settings['ecommerce']['ecommerce_notice']['type'] = 'notice';
				$settings['ecommerce']['ecommerce_notice']['desc'] = sprintf( esc_html__( 'In order to use the ecommerce tracking features, please %1$sinstall and activate%2$s the ecommerce addon.', 'ga-premium'), '<a href="' . admin_url( 'admin.php?page=monsterinsights_addons' ) . '"  >', '</a>' );
			}
			if ( ! class_exists( 'MonsterInsights_Media' ) ) {
				$settings['media']['media_notice']['id'] = 'media_notice';
				$settings['media']['media_notice']['type'] = 'notice';
				$settings['media']['media_notice']['desc'] = sprintf( esc_html__( 'In order to use the media tracking features, please %1$sinstall and activate%2$s the media addon.', 'ga-premium'), '<a href="' . admin_url( 'admin.php?page=monsterinsights_addons' ) . '"  >', '</a>' );
			}
			if ( ! class_exists( 'MonsterInsights_Membership' ) ) {
				$settings['memberships']['memberships_notice']['id'] = 'memberships_notice';
				$settings['memberships']['memberships_notice']['type'] = 'notice';
				$settings['memberships']['memberships_notice']['desc'] = sprintf( esc_html__( 'In order to use the membership tracking features, please %1$sinstall and activate%2$s the membership addon.', 'ga-premium'), '<a href="' . admin_url( 'admin.php?page=monsterinsights_addons' ) . '"  >', '</a>' );
			}
			if ( ! class_exists( 'MonsterInsights_Dimensions' ) ) {
				$settings['dimensions']['dimensions_notice']['id'] = 'dimensions_notice';
				$settings['dimensions']['dimensions_notice']['type'] = 'notice';
				$settings['dimensions']['dimensions_notice']['desc'] = sprintf( esc_html__( 'In order to use the dimensions tracking features, please %1$sinstall and activate%2$s the dimensions addon.', 'ga-premium'), '<a href="' . admin_url( 'admin.php?page=monsterinsights_addons' ) . '"  >', '</a>' );
			}
			if ( ! class_exists( 'MonsterInsights_Performance' ) ) {
				$settings['performance']['performance_notice']['id'] = 'performance_notice';
				$settings['performance']['performance_notice']['type'] = 'notice';
				$settings['performance']['performance_notice']['desc'] = sprintf( esc_html__( 'In order to use the performance tracking features, please %1$sinstall and activate%2$s the performance addon.', 'ga-premium'), '<a href="' . admin_url( 'admin.php?page=monsterinsights_addons' ) . '"  >', '</a>' );
			}
			if ( ! class_exists( 'MonsterInsights_AMP' ) ) {
				$settings['amp']['amp_notice']['id'] = 'amp_notice';
				$settings['amp']['amp_notice']['type'] = 'notice';
				$settings['amp']['amp_notice']['desc'] = sprintf( esc_html__( 'In order to use the AMP integration, please %1$sinstall and activate%2$s the AMP addon.', 'ga-premium'), '<a href="' . admin_url( 'admin.php?page=monsterinsights_addons' ) . '"  >', '</a>' );
			}
			if ( ! class_exists( 'MonsterInsights_Google_Optimize' ) ) {
				$settings['goptimize']['goptimize_notice']['id'] = 'goptimize_notice';
				$settings['goptimize']['goptimize_notice']['type'] = 'notice';
				$settings['goptimize']['goptimize_notice']['desc'] = sprintf( esc_html__( 'In order to use the Google Optimize integration, please %1$sinstall and activate%2$s the Google Optimize addon.', 'ga-premium'), '<a href="' . admin_url( 'admin.php?page=monsterinsights_addons' ) . '"  >', '</a>' );
			}
			if ( ! class_exists( 'MonsterInsights_FB_Instant_Articles' ) ) {
				$settings['fbia']['fbia_notice']['id'] = 'fbia_notice';
				$settings['fbia']['fbia_notice']['type'] = 'notice';
				$settings['fbia']['fbia_notice']['desc'] = sprintf( esc_html__( 'In order to use the Facebook Instant Articles integration features, please %1$sinstall and activate%2$s the Facebook Instant Articles addon.', 'ga-premium'), '<a href="' . admin_url( 'admin.php?page=monsterinsights_addons' ) . '"  >', '</a>' );
			}
			if ( ! class_exists( 'MonsterInsights_Bounce_Reduction' ) ) {
				$settings['bounce']['bounce_notice']['id'] = 'bounce_notice';
				$settings['bounce']['fbounce_notice']['type'] = 'notice';
				$settings['bounce']['bounce_notice']['desc'] = sprintf( esc_html__( 'In order to use the Bounce Reduction addon features, please %1$sinstall and activate%2$s the Bounce Reduction addon.', 'ga-premium'), '<a href="' . admin_url( 'admin.php?page=monsterinsights_addons' ) . '"  >', '</a>' );
			}
			if ( ! class_exists( 'MonsterInsights_Reporting' ) ) {
				$settings['reporting']['reporting_notice']['id'] = 'reporting_notice';
				$settings['reporting']['reporting_notice']['type'] = 'notice';
				$settings['reporting']['reporting_notice']['desc'] = sprintf( esc_html__( 'In order to use the reporting features, please %1$sinstall and activate%2$s the reporting addon.', 'ga-premium'), '<a href="' . admin_url( 'admin.php?page=monsterinsights_addons' ) . '"  >', '</a>' );
			}
			if ( ! class_exists( 'MonsterInsights_Notifications' ) ) {
				$settings['notifications']['notifications_notice']['id'] = 'notifications_notice';
				$settings['notifications']['notifications_notice']['type'] = 'notice';
				$settings['notifications']['notifications_notice']['desc'] = sprintf( esc_html__( 'In order to use the notifications features, please %1$sinstall and activate%2$s the notifications addon.', 'ga-premium'), '<a href="' . admin_url( 'admin.php?page=monsterinsights_addons' ) . '"  >', '</a>' );
			}
		} else {
			// Show enter license key notice
			if ( ! class_exists( 'MonsterInsights_Social' ) ) {
				$settings['social']['social_notice']['id'] = 'social_notice';
				$settings['social']['social_notice']['type'] = 'notice';
				$settings['social']['social_notice']['desc'] = esc_html__( 'In order to use these pro features, please enter your license key.', 'ga-premium' );
			}
			if ( ! class_exists( 'MonsterInsights_Ads' ) ) {
				$settings['ads']['ads_notice']['id'] = 'ads_notice';
				$settings['ads']['ads_notice']['type'] = 'notice';
				$settings['ads']['ads_notice']['desc'] = esc_html__( 'In order to use these pro features, please enter your license key.', 'ga-premium' );
			}
			if ( ! class_exists( 'MonsterInsights_Forms' ) ) {
				$settings['forms']['forms_notice']['id'] = 'forms_notice';
				$settings['forms']['forms_notice']['type'] = 'notice';
				$settings['forms']['forms_notice']['desc'] = esc_html__( 'In order to use these pro features, please enter your license key.', 'ga-premium' );
			}
			if ( ! class_exists( 'MonsterInsights_eCommerce' ) ) {
				$settings['ecommerce']['ecommerce_notice']['id'] = 'ecommerce_notice';
				$settings['ecommerce']['ecommerce_notice']['type'] = 'notice';
				$settings['ecommerce']['ecommerce_notice']['desc'] = esc_html__( 'In order to use these pro features, please enter your license key.', 'ga-premium' );
			}
			if ( ! class_exists( 'MonsterInsights_Media' ) ) {
				$settings['media']['media_notice']['id'] = 'media_notice';
				$settings['media']['media_notice']['type'] = 'notice';
				$settings['media']['media_notice']['desc'] = esc_html__( 'In order to use these pro features, please enter your license key.', 'ga-premium' );
			}
			if ( ! class_exists( 'MonsterInsights_Membership' ) ) {
				$settings['memberships']['memberships_notice']['id'] = 'memberships_notice';
				$settings['memberships']['memberships_notice']['type'] = 'notice';
				$settings['memberships']['memberships_notice']['desc'] = esc_html__( 'In order to use these pro features, please enter your license key.', 'ga-premium' );
			}
			if ( ! class_exists( 'MonsterInsights_Dimensions' ) ) {
				$settings['dimensions']['dimensions_notice']['id'] = 'dimensions_notice';
				$settings['dimensions']['dimensions_notice']['type'] = 'notice';
				$settings['dimensions']['dimensions_notice']['desc'] = esc_html__( 'In order to use these pro features, please enter your license key.', 'ga-premium' );
			}
			if ( ! class_exists( 'MonsterInsights_Performance' ) ) {
				$settings['performance']['performance_notice']['id'] = 'performance_notice';
				$settings['performance']['performance_notice']['type'] = 'notice';
				$settings['performance']['performance_notice']['desc'] = esc_html__( 'In order to use these pro features, please enter your license key.', 'ga-premium' );
			}
			if ( ! class_exists( 'MonsterInsights_AMP' ) ) {
				$settings['amp']['amp_notice']['id'] = 'amp_notice';
				$settings['amp']['amp_notice']['type'] = 'notice';
				$settings['amp']['amp_notice']['desc'] = esc_html__( 'In order to use these pro features, please enter your license key.', 'ga-premium' );
			}
			if ( ! class_exists( 'MonsterInsights_Google_Optimize' ) ) {
				$settings['goptimize']['goptimize_notice']['id'] = 'goptimize_notice';
				$settings['goptimize']['goptimize_notice']['type'] = 'notice';
				$settings['goptimize']['goptimize_notice']['desc'] = esc_html__( 'In order to use these pro features, please enter your license key.', 'ga-premium' );
			}
			if ( ! class_exists( 'MonsterInsights_FB_Instant_Articles' ) ) {
				$settings['fbia']['fbia_notice']['id'] = 'fbia_notice';
				$settings['fbia']['fbia_notice']['type'] = 'notice';
				$settings['fbia']['fbia_notice']['desc'] = esc_html__( 'In order to use these pro features, please enter your license key.', 'ga-premium' );
			}
			if ( ! class_exists( 'MonsterInsights_Bounce_Reduction' ) ) {
				$settings['bounce']['bounce_notice']['id'] = 'bounce_notice';
				$settings['bounce']['bounce_notice']['type'] = 'notice';
				$settings['bounce']['bounce_notice']['desc'] = esc_html__( 'In order to use these pro features, please enter your license key.', 'ga-premium' );
			}
			if ( ! class_exists( 'MonsterInsights_Reporting' ) ) {
				$settings['reporting']['reporting_notice']['id'] = 'reporting_notice';
				$settings['reporting']['reporting_notice']['type'] = 'notice';
				$settings['reporting']['reporting_notice']['desc'] = esc_html__( 'In order to use these pro features, please enter your license key.', 'ga-premium' );
			}
			if ( ! class_exists( 'MonsterInsights_Notifications' ) ) {
				$settings['notifications']['notifications_notice']['id'] = 'notifications_notice';
				$settings['notifications']['notifications_notice']['type'] = 'notice';
				$settings['notifications']['notifications_notice']['desc'] = esc_html__( 'In order to use these pro features, please enter your license key.', 'ga-premium' );
			}
		}
	} else {
		// Doesn't have permissions
		if ( ! class_exists( 'MonsterInsights_Social' ) ) {
			$settings['social']['social_notice']['id'] = 'social_notice';
			$settings['social']['social_notice']['type'] = 'notice';
			$settings['social']['social_notice']['desc'] = esc_html__( 'In order to use these pro features, please ask your webmaster to install the necessary addon.', 'ga-premium' );
		}
		if ( ! class_exists( 'MonsterInsights_Ads' ) ) {
			$settings['ads']['ads_notice']['id'] = 'ads_notice';
			$settings['ads']['ads_notice']['type'] = 'notice';
			$settings['ads']['ads_notice']['desc'] = esc_html__( 'In order to use these pro features, please ask your webmaster to install the necessary addon.', 'ga-premium' );
		}
		if ( ! class_exists( 'MonsterInsights_Forms' ) ) {
			$settings['forms']['forms_notice']['id'] = 'forms_notice';
			$settings['forms']['forms_notice']['type'] = 'notice';
			$settings['forms']['forms_notice']['desc'] = esc_html__( 'In order to use these pro features, please ask your webmaster to install the necessary addon.', 'ga-premium' );
		}
		if ( ! class_exists( 'MonsterInsights_eCommerce' ) ) {
			$settings['ecommerce']['ecommerce_notice']['id'] = 'ecommerce_notice';
			$settings['ecommerce']['ecommerce_notice']['type'] = 'notice';
			$settings['ecommerce']['ecommerce_notice']['desc'] = esc_html__( 'In order to use these pro features, please ask your webmaster to install the necessary addon.', 'ga-premium' );
		}
		if ( ! class_exists( 'MonsterInsights_Media' ) ) {
			$settings['media']['media_notice']['id'] = 'media_notice';
			$settings['media']['media_notice']['type'] = 'notice';
			$settings['media']['media_notice']['desc'] = esc_html__( 'In order to use these pro features, please ask your webmaster to install the necessary addon.', 'ga-premium' );
		}
		if ( ! class_exists( 'MonsterInsights_Membership' ) ) {
			$settings['memberships']['memberships_notice']['id'] = 'memberships_notice';
			$settings['memberships']['memberships_notice']['type'] = 'notice';
			$settings['memberships']['memberships_notice']['desc'] = esc_html__( 'In order to use these pro features, please ask your webmaster to install the necessary addon.', 'ga-premium' );
		}
		if ( ! class_exists( 'MonsterInsights_Dimensions' ) ) {
			$settings['dimensions']['dimensions_notice']['id'] = 'dimensions_notice';
			$settings['dimensions']['dimensions_notice']['type'] = 'notice';
			$settings['dimensions']['dimensions_notice']['desc'] = esc_html__( 'In order to use these pro features, please ask your webmaster to install the necessary addon.', 'ga-premium' );
		}
		if ( ! class_exists( 'MonsterInsights_Performance' ) ) {
			$settings['performance']['performance_notice']['id'] = 'performance_notice';
			$settings['performance']['performance_notice']['type'] = 'notice';
			$settings['performance']['performance_notice']['desc'] = esc_html__( 'In order to use these pro features, please ask your webmaster to install the necessary addon.', 'ga-premium' );
		}
		if ( ! class_exists( 'MonsterInsights_AMP' ) ) {
			$settings['amp']['amp_notice']['id'] = 'amp_notice';
			$settings['amp']['amp_notice']['type'] = 'notice';
			$settings['amp']['amp_notice']['desc'] = esc_html__( 'In order to use these pro features, please ask your webmaster to install the necessary addon.', 'ga-premium' );
		}
		if ( ! class_exists( 'MonsterInsights_Google_Optimize' ) ) {
			$settings['goptimize']['goptimize_notice']['id'] = 'goptimize_notice';
			$settings['goptimize']['goptimize_notice']['type'] = 'notice';
			$settings['goptimize']['goptimize_notice']['desc'] = esc_html__( 'In order to use these pro features, please ask your webmaster to install the necessary addon.', 'ga-premium' );
		}
		if ( ! class_exists( 'MonsterInsights_FB_Instant_Articles' ) ) {
			$settings['fbia']['fbia_notice']['id'] = 'fbia_notice';
			$settings['fbia']['fbia_notice']['type'] = 'notice';
			$settings['fbia']['fbia_notice']['desc'] = esc_html__( 'In order to use these pro features, please ask your webmaster to install the necessary addon.', 'ga-premium' );
		}
		if ( ! class_exists( 'MonsterInsights_Bounce_Reduction' ) ) {
			$settings['bounce']['bounce_notice']['id'] = 'bounce_notice';
			$settings['bounce']['bounce_notice']['type'] = 'notice';
			$settings['bounce']['bounce_notice']['desc'] = esc_html__( 'In order to use these pro features, please ask your webmaster to install the necessary addon.', 'ga-premium' );
		}
		if ( ! class_exists( 'MonsterInsights_Reporting' ) ) {
			$settings['reporting']['reporting_notice']['id'] = 'reporting_notice';
			$settings['reporting']['reporting_notice']['type'] = 'notice';
			$settings['reporting']['reporting_notice']['desc'] = esc_html__( 'In order to use these pro features, please ask your webmaster to install the necessary addon.', 'ga-premium' );
		}
		if ( ! class_exists( 'MonsterInsights_Notifications' ) ) {
			$settings['notifications']['notifications_notice']['id'] = 'notifications_notice';
			$settings['notifications']['notifications_notice']['type'] = 'notice';
			$settings['notifications']['notifications_notice']['desc'] = esc_html__( 'In order to use these pro features, please ask your webmaster to install the necessary addon.', 'ga-premium' );
		}
	}
	return $settings;
}
add_action( 'monsterinsights_registered_settings', 'monsterinsights_detect_uninstalled_addons', 99999, 1 );

function monsterinsights_registered_settings_filter( $settings ) {
	
	// UserID
	$url   = 'https://www.monsterinsights.com/docs/how-to-setup-user-tracking/';
	$settings['demographics']['userid'] = array(
			'id'          => 'userid',
			'name'        => __( 'Enable User ID tracking', 'ga-premium' ),
			'desc'        => sprintf( esc_html__( 'To the extent that Google allows webmasters to discern single users, this setting allows you to identify users by their WordPress user ID if logged in. To use this feature, you will need to turn this feature on in Google Analytics. %1$sClick here%2$s for step by step directions.' , 'ga-premium' ), '<a href="' . esc_attr( $url ) .'" target="_blank" rel="noopener noreferrer" referrer="no-referrer">', '</a>'),
			'type' 		  => 'checkbox',
	);
	
	$settings['compatibility']['gatracker_compatibility_mode'] = array(
		'id'          => 'gatracker_compatibility_mode',
		'name'        => __( 'Enable _gaTracker Compatibility', 'google-analytics-for-wordpress' ),
		'desc'        => sprintf( esc_html__( 'This enables MonsterInsights to work with plugins that use ga() and don\'t support %s' , 'ga-premium' ), '<code>__gaTracker</code>' ),
		'type'        => 'checkbox',
	);

	return $settings;
}
add_filter( 'monsterinsights_registered_settings', 'monsterinsights_registered_settings_filter' );