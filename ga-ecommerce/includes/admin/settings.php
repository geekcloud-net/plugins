<?php

/**
 * Class MonsterInsights_GA_eCommerce_Admin
 *
 * Admin class for the Yoast GA eCommerce plugin
 *
 * @since 6.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MonsterInsights_GA_eCommerce_Admin {

	/**
	 * Class Constructor, adds action.
	 *
	 * @since 6.0.0
	 */
	public function __construct() {
		add_filter( 'monsterinsights_settings_ecommerce', array( $this, 'add_settings' ) );
	}

	public function add_settings( $settings ) {
		$tracking_mode = monsterinsights_get_option( 'tracking_mode', '' );
		if ( $tracking_mode === 'ga' ) {
			$url = esc_url( wp_nonce_url( add_query_arg( array( 'monsterinsights-action' => 'switch_to_analyticsjs', 'return' => 'ecommerce' ) ), 'monsterinsights-switch-to-analyticsjs-nonce' ) );
			$settings['switch_to_analyticsjs'] = array( 
				'id' => 'switch_to_analyticsjs',
				'type' => 'descriptive_text',
				'desc' => monsterinsights_get_message( 'info', sprintf( esc_html__( 'eCommerce Tracking is only available on Universal Tracking (analytics.js). You\'re currently using deprecated ga.js tracking. We recommend switching to analytics.js, as it is significantly more accurate than ga.js, and allows for additional functionality (like the more accurate Javascript based events tracking we offer). Further Google Analytics has deprecated support for ga.js, and it may stop working at any time when Google decides to disable it from their server. To switch to using the newer Universal Analytics (analytics.js) %1$sclick here%2$s.', 'monsterinsights-ecommerce' ), '<a href="' . $url .'">', '</a>' ) )
			);
		} else {
			$url = esc_url( wp_nonce_url( add_query_arg( array( 'monsterinsights-action' => 'switch_to_analyticsjs', 'return' => 'ecommerce' ) ), 'monsterinsights-switch-to-analyticsjs-nonce' ) );
			$settings['setup_instructions'] = array( 
				'id' => 'setup_instructions',
				'name'  => esc_html__( 'Setup Instructions:', 'monsterinsights-ecommerce' ),
				'type' => 'descriptive_text',
				'desc' => sprintf( esc_html__( 'In order to enable eCommerce tracking, please make sure you have followed %1$sthese steps%2$s. Once this has been completed, tracking will occur automatically. There are no settings required.', 'monsterinsights-ecommerce' ), '<a href="https://www.monsterinsights.com/docs/installation-guide-for-google-analytics-ecommerce-tracking/">', '</a>')
			);

			if ( version_compare( MONSTERINSIGHTS_VERSION, '6.2.0', '>=' ) ) {
				$settings['enhanced_ecommerce'] = array( 
					'id'    => 'enhanced_ecommerce',
					'name'  => esc_html__( 'Use Enhanced eCommerce:', 'monsterinsights-ecommerce' ),
					'type'  => 'checkbox',
					'desc'  => sprintf( esc_html__( 'Enhanced eCommerce allows you to have even more detailed eCommerce reports in Google Analytics. Enhanced eCommerce is compatible with any Easy Digital Downloads version, but is only compatible with WooCommerce 3.0 and up. For older WooCommerce installations, you must use standard tracking. Please note, when switching from standard tracking to enhanced eCommerce, you will need to adjust your Google Analytics account. See the %1$ssetup guide for enhanced ecommerce%2$s and make any needed changes', 'monsterinsights-ecommerce' ), '<a href="https://www.monsterinsights.com/docs/installation-guide-for-google-analytics-ecommerce-tracking/">', '</a>')
				);
			} else {
				$settings['enhanced_ecommerce_notice'] = array( 
					'id' => 'enhanced_ecommerce_notice',
					'name'  => esc_html__( 'Use Enhanced eCommerce:', 'monsterinsights-ecommerce' ),
					'type' => 'descriptive_text',
					'desc' => monsterinsights_get_message( 'info', esc_html__( 'MonsterInsights 6.2.0 or higher is required to use enhanced eCommerce tracking. Your site is currently tracking using standard ecommerce tracking. Update your MonsterInsights plugin version to unlock this feature.', 'monsterinsights-ecommerce' ) )
				);
			}

			if ( class_exists( 'WooCommerce' ) ) {
				$settings['woocommerce'] = array( 
					'id'    => 'woocommerce',
					'name'  => esc_html__( 'WooCommerce:', 'monsterinsights-ecommerce' ),
					'type'  => 'checkbox',
					'faux'  => true,
					'std'   => true,
					'field_class' => 'monsterinsights-large-checkbox',
					'desc'  => esc_html__( 'WooCommerce has been detected and eCommerce data is being sent back to Google Analytics.', 'monsterinsights-ecommerce' )
				);
			} else {
				$settings['woocommerce'] = array( 
					'id'    => 'woocommerce',
					'name'  => esc_html__( 'WooCommerce:', 'monsterinsights-ecommerce' ),
					'type'  => 'checkbox',
					'faux'  => true,
					'std'   => false,
					'field_class' => 'monsterinsights-large-checkbox',
					'desc'  => esc_html__( 'WooCommerce has not been detected and eCommerce data is not being sent back to Google Analytics.', 'monsterinsights-ecommerce' )
				);
			}

			if ( class_exists( 'Easy_Digital_Downloads' ) ) {
				$settings['edd'] = array( 
					'id'    => 'edd',
					'name'  => esc_html__( 'Easy Digital Downloads:', 'monsterinsights-ecommerce' ),
					'type'  => 'checkbox',
					'faux'  => true,
					'std'   => true,
					'field_class' => 'monsterinsights-large-checkbox',
					'desc'  => esc_html__( 'Easy Digital Downloads has been detected and eCommerce data is being sent back to Google Analytics.', 'monsterinsights-ecommerce' )
				);
			} else {
				$settings['edd'] = array( 
					'id'    => 'edd',
					'name'  => esc_html__( 'Easy Digital Downloads:', 'monsterinsights-ecommerce' ),
					'type'  => 'checkbox',
					'faux'  => true,
					'std'   => false,
					'field_class' => 'monsterinsights-large-checkbox',
					'desc'  => esc_html__( 'Easy Digital Downloads has not been detected and eCommerce data is not being sent back to Google Analytics.', 'monsterinsights-ecommerce' )
				);
			}

		}
		return $settings;
	}
}