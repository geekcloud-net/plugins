<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function monsterinsights_ads_output_after_script( $options ) {
	$events_mode   = monsterinsights_get_option( 'events_mode', false );
	$tracking_mode = monsterinsights_get_option( 'tracking_mode', 'analytics' );
	$track_user    = monsterinsights_track_user();
	$ua            = monsterinsights_get_ua_to_output();
	$track_adsense = true;

	if ( $track_user && $events_mode === 'js' && $tracking_mode === 'analytics' && $ua ) {
		ob_start();
		echo PHP_EOL;
		?>
<!-- MonsterInsights Ads Tracking -->
<script type="text/javascript">
<?php
if ( $track_adsense && monsterinsights_get_ua_to_output() ) {
	$tracking_code = monsterinsights_get_ua_to_output();
	echo "window.google_analytics_uacct = '" . $tracking_code . "';" . PHP_EOL . PHP_EOL;
}
?>
var MonsterInsightsAds = function(){
	function __gaTrackerGetAdType( el ) {
		var type    = 'notad';

		if ( el.parentElement.className.split(' ').indexOf('adsanity-gati')!==-1 ) {
			type = 'adsanity';
		}

		return type;
	}

	function __gaTrackerClickEvent( event ) {
		var el            = event.srcElement || event.target;
		var valuesArray   = [];
		var fieldsArray;

		// Start Values Array
		valuesArray.el         = el;
		valuesArray.ga_loaded  = MonsterInsightsObject.__gaTrackerLoaded();
		valuesArray.click_type = MonsterInsightsObject.__gaTrackerTrackedClickType( event );

		/* If GA is blocked or not loaded, or not main|middle|touch click then don't track */
		if ( ! MonsterInsightsObject.__gaTrackerLoaded() || ! MonsterInsightsObject.__gaTrackerTrackedClick( event ) ) {
			valuesArray.exit = 'loaded';
			MonsterInsightsObject.__gaTrackerNotSend( valuesArray );
			return;
		}

		/* Loop up the DOM tree through parent elements if clicked element is not a link (eg: an image inside a link) */
		while ( el && (typeof el.tagName == 'undefined' || el.tagName.toLowerCase() != 'a' || ! el.href ) ) {
			el = el.parentNode;
		}

		/* if a link with valid href has been clicked */
		if ( el && el.href ) {
			var link                		= el.href;						/* What link are we tracking */
			var type      			        = __gaTrackerGetAdType( el );   /* Is this an ad, and if so, what type */

			/* Element */
			valuesArray.el                  = el;					/* el is an a element so we can parse it */
			valuesArray.el_href             = el.href; 				/* "http://example.com:3000/pathname/?search=test#hash" */
			valuesArray.el_protocol         = el.protocol; 			/* "http:" */
			valuesArray.el_hostname         = el.hostname; 			/* "example.com" */
			valuesArray.el_port             = el.port; 				/* "3000" */
			valuesArray.el_pathname         = el.pathname; 			/* "/pathname/" */
			valuesArray.el_search           = el.search; 			/* "?search=test" */
			valuesArray.el_hash             = el.hash;				/* "#hash" */
			valuesArray.el_host             = el.host; 				/* "example.com:3000" */

			/* Settings */
			valuesArray.debug_mode          = MonsterInsightsObject.__gaTrackerIsDebug(); /* "example.com:3000" */

			/* Parsed/Logic */
			valuesArray.link                = link; 				/* What link are we tracking */
			valuesArray.type                = type; 				/* What type of ad link is this */
			valuesArray.title 				= el.title || el.textContent || el.innerText; /* Try link title, then text content */

			/* Let's track any ads */
			if ( type !== 'notad' ) {
				if ( type == 'adsanity' ) {
					fieldsArray = { 
					hitType        : 'event',
					eventCategory  : 'Ad - AdSanity',
					eventAction    : link,
					eventLabel     : valuesArray.title,
					eventValue     : 1,
					nonInteraction : 1
					};
					// Todo: view + impression + whether to do Ad as cat, adsanity as action and then link or title as label
					MonsterInsightsObject.__gaTrackerSend( valuesArray, fieldsArray );
				}
			} else {
				valuesArray.exit = 'notad';
				MonsterInsightsObject.__gaTrackerNotSend( valuesArray );
			}
		} else {
			valuesArray.exit = 'notlink';
			MonsterInsightsObject.__gaTrackerNotSend( valuesArray );
		}
	}

	/* Attach the event to all clicks in the document after page has loaded */
	if ( MonsterInsightsObject.__gaTrackerWindow.addEventListener ) {
		MonsterInsightsObject.__gaTrackerWindow.addEventListener( 
			"load", 
			function() { 
				document.body.addEventListener(
					"click", 
					__gaTrackerAdsClickEvent,
					 false
				);
			}, 
			false
		);
	} else { 
		if ( MonsterInsightsObject.__gaTrackerWindow.attachEvent ) {
			MonsterInsightsObject.__gaTrackerWindow.attachEvent(
				"onload", 
				function() {
					document.body.attachEvent( "onclick", __gaTrackerAdsClickEvent);
				}
			);
		}
	}
};
var MonsterInsightsAdsObject = new MonsterInsightsAds();
</script>
<!-- End MonsterInsights Ads Tracking -->
<?php
		echo PHP_EOL;
		echo ob_get_clean();
	}

}
//add_action( 'monsterinsights_tracking_after_analytics', 'monsterinsights_ads_output_after_script' );

function monsterinsights_ads_output_after_script_old( $options ) {
	$events_mode   = monsterinsights_get_option( 'events_mode', false );
	$tracking_mode = monsterinsights_get_option( 'tracking_mode', 'analytics' );
	$track_user    = monsterinsights_track_user();
	$ua            = monsterinsights_get_ua_to_output();
	$track_adsense = true;

	if ( $track_user && $events_mode === 'js' && $tracking_mode === 'analytics' && $ua ) {
		ob_start();
		echo PHP_EOL;
		?>
<!-- MonsterInsights Ads Tracking -->
<script type="text/javascript">
<?php
if ( $track_adsense && monsterinsights_get_ua_to_output() ) {
	$tracking_code = monsterinsights_get_ua_to_output();
	echo "window.google_analytics_uacct = '" . $tracking_code . "';" . PHP_EOL . PHP_EOL;
}
?>
</script>
<!-- End MonsterInsights Ads Tracking -->
<?php
		echo PHP_EOL;
		echo ob_get_clean();
	}

}
add_action( 'monsterinsights_tracking_after_analytics', 'monsterinsights_ads_output_after_script_old' );