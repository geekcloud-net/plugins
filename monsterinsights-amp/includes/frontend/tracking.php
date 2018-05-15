<?php
function monsterinsights_amp_rest_add_userid( $amp_template ) {
	?>

	<meta name="amp-google-client-id-api" content="googleanalytics">

	<?php
}
add_action( 'amp_post_template_head', 'monsterinsights_amp_rest_add_userid',12 );


function monsterinsights_amp_add_analytics( $analytics ) {
	// if Yoast is outputting analytics
	if ( isset( $analytics['yst-googleanalytics'] ) ) {
		return $analytics;
	}

	$track = function_exists( 'monsterinsights_track_user' ) ? monsterinsights_track_user() : ! monsterinsights_disabled_user_group();
	if ( ! $track ) {
		return $analytics;
	}

	// if there's no UA code set
	$ua = monsterinsights_get_ua_to_output( array( 'amp' => true ) );
	if ( empty( $ua ) ) {
		return $analytics;
	}
	$site_url = str_replace( array( 'http:', 'https:'), '',  site_url() );
	$analytics['monsterinsights-googleanalytics'] = array(
		'type' => 'googleanalytics',
		'attributes'  => array(),
		'config_data' => array(
			'vars' => array( 
				'account' => $ua,
			),
			'triggers' => array(
				'trackPageview' => array(
					'on'      => 'visible',
					'request' => 'pageview',
				),
			),
		),
	);

	// Dimensions Addon Integration
	// First, let's get dimensions by pulling them out of the normal frontend output
	$options = apply_filters( 'monsterinsights_frontend_tracking_options_analytics_before_pageview', array() );
	$has_dim = false;
	foreach ( $options as $optionname => $optionvalue ) {
		if ( monsterinsights_string_starts_with( $optionname, 'dimension' ) ) {
			$has_dim       = true;
			$num           = str_replace( 'dimension', '',  $optionname );
			$dimensionname = str_replace( 'dimension', 'cd',  $optionname );
			$optionvalue   = str_replace("'set', 'dimension" . absint( $num ) . "', '", '', $optionvalue );
			$optionvalue   = rtrim( $optionvalue,"'" );
			$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['trackPageview']['vars'][$dimensionname] = $optionvalue;
			if ( isset( $analytics['monsterinsights-googleanalytics']['config_data']['requests'] ) ) {
				$analytics['monsterinsights-googleanalytics']['config_data']['requests']['pageviewWithCDs'] = $analytics['monsterinsights-googleanalytics']['config_data']['requests']['pageviewWithCDs'] . '&cd' . $num . '=${cd' . $num . '}';
			} else {
				$analytics['monsterinsights-googleanalytics']['config_data']['requests']['pageviewWithCDs'] = '${pageview}' . '&cd' . $num . '=${cd' . $num . '}';
			}
		}
	}

	if ( $has_dim ){
		$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['trackPageview']['request'] = 'pageviewWithCDs';
	}

	$tracking       = monsterinsights_get_option( 'tracking_mode', false );
	$events         = monsterinsights_get_option( 'events_mode', false );
	if ( $events === 'js' && $tracking === 'analytics' ) {
		// Track Downloads
		$track_as = monsterinsights_get_option( 'track_download_as', '' );
		if ( $track_as === 'pageview' ) {
			$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['downloadLinks']['on'] = 'click';
			$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['downloadLinks']['selector'] = 'a, .monsterinsights-download';
			$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['downloadLinks']['request'] = 'pageview';
			$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['downloadLinks']['vars'] = array( 
				'page' => '${page}',
			);
		} else {
			$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['downloadLinks']['on'] = 'click';
			$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['downloadLinks']['selector'] = 'a, .monsterinsights-download';
			$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['downloadLinks']['request'] = 'event';
			$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['downloadLinks']['vars'] = array( 
				'eventCategory' => '${category}',
				'eventAction'   => '${action}',
				'eventLabel'    => '${label}',
			);
		}

		// Track Internal as Outbound
		$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['internalAsOutboundLinks']['on'] = 'click';
		$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['internalAsOutboundLinks']['selector'] = 'a, .monsterinsights-internal-as-outbound';
		$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['internalAsOutboundLinks']['request'] = 'event';
		$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['internalAsOutboundLinks']['vars'] = array( 
			'eventCategory' => '${category}',
			'eventAction'   => '${action}',
			'eventLabel'    => '${label}',
		);

		// Track External
		$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['outboundLinks']['on'] = 'click';
		$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['outboundLinks']['selector'] = 'a, .monsterinsights-outbound-link';
		$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['outboundLinks']['request'] = 'event';
		$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['outboundLinks']['vars'] = array( 
			'eventCategory' => '${category}',
			'eventAction'   => '${action}',
			'eventLabel'    => '${label}',
		);

		// Track Tel
		$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['telLinks']['on'] = 'click';
		$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['telLinks']['selector'] = 'a, .monsterinsights-tel';
		$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['telLinks']['request'] = 'event';
		$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['telLinks']['vars'] = array( 
			'eventCategory' => '${category}',
			'eventAction'   => '${action}',
			'eventLabel'    => '${label}',
		);

		// Track Mailto
		$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['mailtoLinks']['on'] = 'click';
		$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['mailtoLinks']['selector'] = 'a, .monsterinsights-mailto';
		$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['mailtoLinks']['request'] = 'event';
		$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['mailtoLinks']['vars'] = array( 
			'eventCategory' => '${category}',
			'eventAction'   => '${action}',
			'eventLabel'    => '${label}',
		);
	}

	$samplerate = monsterinsights_get_option( 'samplerate', 100 );
	// If performance addon turned on sample our event
	if ( (int ) $samplerate > 0 && (int) $samplerate < 100 ) {
		// Set ours to sample
		$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['trackPageview']['sampleSpec']['sampleOn'] = '${clientId}';
		$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['trackPageview']['sampleSpec']['threshold'] = (int) $samplerate;
	}

	$samplerate = monsterinsights_get_option( 'speedsamplerate', 1 );
	// If performance addon turned on sample Google's pagespeed event
	if ( (int) $samplerate > 0 && (int) $samplerate < 100 && (int) $samplerate !== 1 ) {
		// Set Google's to sample
		$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['performanceTiming']['sampleSpec']['threshold'] = (int) $samplerate;
	}

	// Todo: Future: Optin: https://github.com/ampproject/amphtml/blob/master/extensions/amp-user-notification/amp-user-notification.md or
	// https://www.ampproject.org/docs/reference/components/amp-analytics 'data-consent-notification-id'
	// Todo: Clarification on tracking of internal links
	return $analytics;
}
add_filter( 'amp_post_template_analytics', 'monsterinsights_amp_add_analytics' );

function monsterinsights_not_tracking_amp() {
	$track = function_exists( 'monsterinsights_track_user' ) ? monsterinsights_track_user() : ! monsterinsights_disabled_user_group();
	if ( ! $track ) {
		echo '<!-- Note: MonsterInsights is not tracking this page as you are either a logged in administrator or a disabled user group. -->';
	}
}
add_filter( 'amp_post_template_footer', 'monsterinsights_not_tracking_amp' );

/**
 * Add our own sanitizer to the array of sanitizers
 *
 * @param array $sanitizers
 *
 * @return array
 */
function monsterinsights_amp_add_sanitizer( $sanitizers ) {
	$tracking       = monsterinsights_get_option( 'tracking_mode', false );
	$events         = monsterinsights_get_option( 'events_mode', false );
	if ( $events === 'js' && $tracking === 'analytics' ) {
		require_once 'link-parser.php';
		$sanitizers['MonsterInsights_AMP_Parser'] = array();
	}
	return $sanitizers;
}
add_filter( 'amp_content_sanitizers', 'monsterinsights_amp_add_sanitizer' );

// If Yoast SEO Glue is active, turn off our integration hosted in their plugin and use
// the more advanced one from this addon.
remove_class_filter( 'amp_post_template_analytics', 'YoastSEO_AMP_Frontend', 'analytics' );