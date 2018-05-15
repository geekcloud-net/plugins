/* global YoastSEO: true, wpseoVideoL10n, AssessmentResult */
(function() {
	'use strict';

	var AssessmentResult = require( 'yoastseo/js/values/AssessmentResult' );

	/**
	 * Adds eventListener on page load to load the videoSEO.
	 */
	if ( wpseoVideoL10n.has_video === '1' ) {
		if ( typeof YoastSEO !== 'undefined' && typeof YoastSEO.app !== 'undefined' ) {
			new YoastVideoSEOplugin( YoastSEO.app );
		}
		else {
			jQuery( window ).on(
				'YoastSEO:ready',
				function() {
					new YoastVideoSEOplugin( YoastSEO.app );
				}
			);
		}
	}

	/**
	 * Adds the plugin for videoSEO to the YoastSEO Analyzer.
	 */
	function YoastVideoSEOplugin( app ) {

		app.registerPlugin( 'YoastVideoSEO', { 'status': 'ready' } );

		app.registerAssessment( 'videoTitle', { getResult: this.videoTitle.bind( this ) }, 'YoastVideoSEO' );

		app.registerAssessment( 'videoBodyLength', { getResult: this.videoBodyLength.bind( this ) }, 'YoastVideoSEO' );

	}

	/**
	 * Tests if the word video appears in the title, returns number of matches
	 * @param {object} paper The paper to run this assessment on
	 * @param {object} researcher The researcher used for the assessment
	 * @param {object} i18n The i18n-object used for parsing translations
	 * @returns {object} an assessmentresult with the score and formatted text.
	 */
	YoastVideoSEOplugin.prototype.videoTitle = function( paper, researcher, i18n ) {
		var videoRegex = new RegExp( wpseoVideoL10n.video, 'ig' );
		var matches = paper.getTitle().match( videoRegex ) || 0;
		var assessmentResult = new AssessmentResult();
		var result = this.scoreVideoTitle( matches );
		assessmentResult.setScore( result.score ) ;
		assessmentResult.setText( result.text );
		return assessmentResult;
	};

	/**
	 * Returns the scoreobject based on the number of matches in the videotitle
	 * @param {array} matches The matches in the videotitle.
	 * @returns {{score: number, text: *}} The object containing the score and text
	 */
	YoastVideoSEOplugin.prototype.scoreVideoTitle = function( matches ) {
		if ( matches.length > 0 ){
			return {
				score: 9,
				text: wpseoVideoL10n.video_title_good
			};
		}
		return{
			score: 6,
			text:wpseoVideoL10n.video_title_ok
		};
	};

	/**
	 * returns the wordcount for of the text
	 * @returns int
	 */
	YoastVideoSEOplugin.prototype.videoBodyLength = function( paper, researcher, i18n ) {
		var wordCount = researcher.getResearch( "wordCountInText" );
		var assessmentResult = new AssessmentResult();
		var result = this.scoreVideoBodyLength( wordCount, i18n );
		assessmentResult.setScore( result.score ) ;
		assessmentResult.setText( result.text );

		return assessmentResult;
	};

	/**
	 * Returns the score and text based on the wordcount.
	 * @param {number} wordCount The number of words in the text
	 * @returns {{score: number, text: *}} The resultobject
	 */
	YoastVideoSEOplugin.prototype.scoreVideoBodyLength = function( wordCount, i18n ) {
		if ( wordCount <= 150 ){
			return {
				score: 6,
				text: wpseoVideoL10n.video_body_short
			}
		}
		if ( wordCount > 150 && wordCount < 400 ){
			return {
				score: 9,
				text: wpseoVideoL10n.video_body_good
			}
		}
		if ( wordCount >= 400 ) {
			return {
				score: 6,
				text: i18n.sprintf( wpseoVideoL10n.video_body_long, wpseoVideoL10n.video_body_long_url, '</a>' )
			}
		}
	};
}());
