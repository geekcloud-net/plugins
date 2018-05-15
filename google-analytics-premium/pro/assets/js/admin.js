/** 
 * Developer's Notice:
 * 
 * Note: JS in this file (and this file itself) is not garunteed backwards compatibility. JS can be added, changed or removed at any time without notice.
 * For more information see the `Backwards Compatibility Guidelines for Developers` section of the README.md file.
 */
jQuery(document).ready(function () {	
	// Flatpickr
	var old_dates = [];
	var optional_config = {
		mode: "range",
		disableMobile: "true",
		dateFormat: "Y-m-d",
			disable: [
			function(date) {
				var startDate = date;
				var endDate   = moment(moment().tz(monsterinsights_admin.timezone).format('YYYY-MM-DD'));
				var duration  = moment.duration(endDate.diff(startDate));
				var diffDays  = duration.asDays();
				diffDays = diffDays + 1;

				var odate      = moment(date).tz(monsterinsights_admin.timezone);
				var rangestart = odate.subtract(diffDays, 'd').tz(monsterinsights_admin.timezone);

				var today         = moment();
				var inrange_left  = rangestart.isBetween(moment("01-01-2005", "MM-DD-YYYY").tz(monsterinsights_admin.timezone), today );
				var inrange_right = moment(date).isBetween(moment("01-01-2005", "MM-DD-YYYY").tz(monsterinsights_admin.timezone), today );
				return ! inrange_left || ! inrange_right;
			}
		],
		onClose: function(selectedDates, dateStr, instance) {
			// Require for now that there is a true range. Perhaps in the future we'll offer
			// open ended ranges (ie a start or just an end range)
			var dates = [];
			if ( selectedDates[0] != null && selectedDates[1] != null ) {
				dates = selectedDates.map(
				 function(date) {
				    return this.formatDate( date, "Y-m-d" );
				 }, this
				);
			} else {
				return;
			}

			if ( ( old_dates[0] != null && old_dates[1] != null ) && ( old_dates[0] == dates[0] && old_dates[1] === dates[1] ) ) {
				return;
			}

			// Blur report shown
			jQuery( "#monsterinsights-reports-pages" ).addClass( "monsterinsights-mega-blur" );

			// Which report?
			var reportname = jQuery("#monsterinsights-reports-pages").find( "div.monsterinsights-main-nav-tab.monsterinsights-active" ).attr("id").replace("monsterinsights-main-tab-", "" );
			var reportid   = jQuery("#monsterinsights-reports-pages").find( "div.monsterinsights-main-nav-tab.monsterinsights-active" ).attr("id");

			swal({
			  type: 'info',
			  title: monsterinsights_admin.refresh_report_title,
			  text: monsterinsights_admin.refresh_report_text,
			  allowOutsideClick: false,
			  allowEscapeKey: false,
			  allowEnterKey: false,
			  onOpen: function () {
				swal.showLoading();

				var data = { 
					'action'   : 'monsterinsights_refresh_reports', 
					'security' :  monsterinsights_admin.admin_nonce,
					'isnetwork':  monsterinsights_admin.isnetwork,
					'start'    :  dates[0],
					'end'      :  dates[1],
					'report'   :  reportname,
				};
				
				jQuery.post(ajaxurl, data, function( response ) {

					if ( response.success && response.data.html ) {
						// Don't allow replay
						old_dates = dates;

						// Insert new data here
						jQuery("#monsterinsights-main-tab-" + reportname + " > .monsterinsights-reports-wrap").html( response.data.html );
						
						// Resize divs
						monsterinsights_equalheight2column();
						
						jQuery("#monsterinsights-main-tab-" + reportname + " .monsterinsights-pro-report-date-control-group .monsterinsights-pro-report-7-days").removeClass("active").disable(false);
						jQuery("#monsterinsights-main-tab-" + reportname + " .monsterinsights-pro-report-date-control-group .monsterinsights-pro-report-30-days").removeClass("active").disable(false);
						jQuery("#monsterinsights-main-tab-" + reportname + " .monsterinsights-pro-report-date-control-group .monsterinsights-pro-datepicker").addClass("monsterinsights-datepicker-active");
						// swal({
						// 	type: 'success',
						// 	  title: monsterinsights_admin.refresh_report_success_title,
						// 	  text: monsterinsights_admin.refresh_report_success_text,
						//   });

						swal.close();
					} else {
						instance.setDate( old_dates );
						swal({
							type: 'error',
							  title: monsterinsights_admin.refresh_report_failure_title,
							  text: response.data.message,
						  }).catch(swal.noop);
					}
				}).then(function (result) {
					// Unblur reports
					jQuery( "#monsterinsights-reports-pages" ).removeClass( "monsterinsights-mega-blur" );
				}).fail( function(xhr, textStatus, errorThrown) {
					instance.setDate( old_dates );
					var message = jQuery(xhr.responseText).text();
					message = message.substring(0, message.indexOf("Call Stack"));
					swal({
						type: 'error',
						  title: monsterinsights_admin.refresh_report_failure_title,
						  text: message,
					  }).catch(swal.noop);
					// Unblur reports
					jQuery( "#monsterinsights-reports-pages" ).removeClass( "monsterinsights-mega-blur" );
				});
			  }
			});
		},
	};
	jQuery(".monsterinsights-pro-datepicker").flatpickr( optional_config );

	jQuery(".monsterinsights-pro-report-date-control-group > .btn").click(function( e ){
		e.preventDefault();
		var element    = jQuery(this);
		var sevendays  = jQuery(this).hasClass("monsterinsights-pro-report-7-days");
		var thirtydays = jQuery(this).hasClass("monsterinsights-pro-report-30-days");
		if ( sevendays || thirtydays ) {
			// Blur report shown
			jQuery( "#monsterinsights-reports-pages" ).addClass( "monsterinsights-mega-blur" );

			// Which report?
			var reportname = jQuery("#monsterinsights-reports-pages").find( "div.monsterinsights-main-nav-tab.monsterinsights-active" ).attr("id").replace("monsterinsights-main-tab-", "" );
			var reportid   = jQuery("#monsterinsights-reports-pages").find( "div.monsterinsights-main-nav-tab.monsterinsights-active" ).attr("id");
			var start      = sevendays ? moment().subtract(8, 'days').tz(monsterinsights_admin.timezone).format('YYYY-MM-DD') : moment().subtract(31, 'days').tz(monsterinsights_admin.timezone).format('YYYY-MM-DD');
			var end        = moment().subtract( 1, 'days' ).tz(monsterinsights_admin.timezone).format('YYYY-MM-DD');

			swal({
			  type: 'info',
			  title: monsterinsights_admin.refresh_report_title,
			  text: monsterinsights_admin.refresh_report_text,
			  allowOutsideClick: false,
			  allowEscapeKey: false,
			  allowEnterKey: false,
			  onOpen: function () {
				swal.showLoading();

				var data = { 
					'action'   : 'monsterinsights_refresh_reports', 
					'security' :  monsterinsights_admin.admin_nonce,
					'isnetwork':  monsterinsights_admin.isnetwork,
					'start'    :  start,
					'end'      :  end,
					'report'   :  reportname,
				};
				
				jQuery.post(ajaxurl, data, function( response ) {

					if ( response.success && response.data.html ) {
						// Change active button
						jQuery(element).addClass("active").disable(true).siblings().removeClass("active").disable(false).removeClass('monsterinsights-datepicker-active');

						// Insert new data here
						jQuery("#monsterinsights-main-tab-" + reportname + " > .monsterinsights-reports-wrap").html( response.data.html );

						// Resize divs
						monsterinsights_equalheight2column();
						
						// swal({
						// 	type: 'success',
						// 	  title: monsterinsights_admin.refresh_report_success_title,
						// 	  text: monsterinsights_admin.refresh_report_success_text,
						//   });
						swal.close();
					} else {
						swal({
							type: 'error',
							  title: monsterinsights_admin.refresh_report_failure_title,
							  text: response.data.message,
						  }).catch(swal.noop);
					}
				}).then(function (result) {
					// Unblur reports
					jQuery( "#monsterinsights-reports-pages" ).removeClass( "monsterinsights-mega-blur" );
				}).fail( function(xhr, textStatus, errorThrown) {
					var message = jQuery(xhr.responseText).text();
					message = message.substring(0, message.indexOf("Call Stack"));
					swal({
						type: 'error',
						  title: monsterinsights_admin.refresh_report_failure_title,
						  text: message,
					  }).catch(swal.noop);
					// Unblur reports
					jQuery( "#monsterinsights-reports-pages" ).removeClass( "monsterinsights-mega-blur" );
				});
			  }
			});
		}
	});
});