<?php
function monsterinsights_forms_output_after_script( $options ) {
	$events_mode   = monsterinsights_get_option( 'events_mode', false );
	$tracking_mode = monsterinsights_get_option( 'tracking_mode', 'analytics' );
	$track_user    = monsterinsights_track_user();
	$ua            = monsterinsights_get_ua_to_output();

	if ( $track_user && $events_mode === 'js' && $tracking_mode === 'analytics' && $ua ) {
		ob_start();
		echo PHP_EOL;
		?>
<!-- MonsterInsights Form Tracking -->
<script type="text/javascript">
	function monsterinsights_forms_record_impression( event ) {
		var monsterinsights_forms = document.getElementsByTagName("form");
		var monsterinsights_forms_i;
		for (monsterinsights_forms_i = 0; monsterinsights_forms_i < monsterinsights_forms.length; monsterinsights_forms_i++ ) {
			var monsterinsights_form_id     = monsterinsights_forms[monsterinsights_forms_i].getAttribute("id");
			if ( monsterinsights_form_id && monsterinsights_form_id !== 'commentform' ) {
				__gaTracker( 'send', {
					hitType        : 'event',
					eventCategory  : 'form',
					eventAction    : 'impression',
					eventLabel     : monsterinsights_form_id,
					eventValue     : 1,
					nonInteraction : 1
				} );
				var __gaFormsTrackerWindow    = window;
				if ( __gaFormsTrackerWindow.addEventListener ) {
					document.getElementById(monsterinsights_form_id).addEventListener( "submit", monsterinsights_forms_record_conversion, false );
				} else {
					if ( __gaFormsTrackerWindow.attachEvent ) {
						document.getElementById(monsterinsights_form_id).attachEvent( "onsubmit", monsterinsights_forms_record_conversion );
					}
				}
			} else {
				/* If contact form 7, see if parent div ID starts with wpcf7-f{id}*/
				monsterinsights_form_id = monsterinsights_forms[monsterinsights_forms_i].parentElement.getAttribute("id");
				if ( monsterinsights_form_id && monsterinsights_form_id.lastIndexOf('wpcf7-f', 0 ) === 0  ) {
					/* If so, let's grab that and set it to be the form's ID*/
					var tokens = monsterinsights_form_id.split('-').slice(0,2);
					var result = tokens.join('-');
					monsterinsights_forms[monsterinsights_forms_i].setAttribute("id", result);

					/* Now we can do just what we did above */
					monsterinsights_form_id     = monsterinsights_forms[monsterinsights_forms_i].getAttribute("id");
					if ( monsterinsights_form_id && monsterinsights_form_id !== 'commentform' ) {
						__gaTracker( 'send', {
							hitType        : 'event',
							eventCategory  : 'form',
							eventAction    : 'impression',
							eventLabel     : monsterinsights_form_id,
							eventValue     : 1,
							nonInteraction : 1
						} );
						var __gaFormsTrackerWindow    = window;
						if ( __gaFormsTrackerWindow.addEventListener ) {
							document.getElementById(monsterinsights_form_id).addEventListener( "submit", monsterinsights_forms_record_conversion, false );
						} else {
							if ( __gaFormsTrackerWindow.attachEvent ) {
								document.getElementById(monsterinsights_form_id).attachEvent( "onsubmit", monsterinsights_forms_record_conversion );
							}
						}
					} else {
						continue;
					}
				} else {
					continue;
				}
			}
		}
	}

	function monsterinsights_forms_record_conversion( event ) {
		var monsterinsights_form_conversion_id = event.target.id;
		var monsterinsights_form_action        = event.target.getAttribute("miforms-action");
		if ( monsterinsights_form_conversion_id && ! monsterinsights_form_action ) {
			document.getElementById(monsterinsights_form_conversion_id).setAttribute("miforms-action", "submitted");
			__gaTracker( 'send', {
				hitType        : 'event',
				eventCategory  : 'form',
				eventAction    : 'conversion',
				eventLabel     : monsterinsights_form_conversion_id,
				eventValue     : 1
			} );
		}
	}

	/* Attach the events to all clicks in the document after page and GA has loaded */
	function monsterinsights_forms_load() {
		if ( __gaTracker && typeof(__gaTracker) !== 'undefined' && __gaTracker.hasOwnProperty( "loaded" ) && __gaTracker.loaded == true ) {
			var __gaFormsTrackerWindow    = window;
			if ( __gaFormsTrackerWindow.addEventListener ) {
				__gaFormsTrackerWindow.addEventListener( "load", monsterinsights_forms_record_impression, false );
			} else { 
				if ( __gaFormsTrackerWindow.attachEvent ) {
					__gaFormsTrackerWindow.attachEvent("onload", monsterinsights_forms_record_impression );
				}
			}
		} else {
			setTimeout(monsterinsights_forms_load, 500);
		}
	}
	monsterinsights_forms_load();
</script>
<!-- End MonsterInsights Form Tracking -->
<?php
		echo PHP_EOL;
		echo ob_get_clean();
	}

}
add_action( 'monsterinsights_tracking_after_analytics', 'monsterinsights_forms_output_after_script' );