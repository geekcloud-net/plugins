/* global YoastSEO, tinyMCE, wpseoWooL10n */
var AssessmentResult = require( "yoastseo/js/values/AssessmentResult" );
( function() {
	/**
	 * Registers Plugin and Test for Yoast WooCommerce.
	 *
	 * @returns {void}
	 */
	function YoastWooCommercePlugin() {
		YoastSEO.app.registerPlugin( "YoastWooCommerce", { status: "ready" } );

		YoastSEO.app.registerAssessment( "productTitle", { getResult: this.productDescription.bind( this ) }, "YoastWooCommerce" );

		this.addCallback();

		YoastSEO.app.registerPlugin( "YoastWooCommercePlugin", { status: "ready" } );

		this.registerModifications();

		this.bindEvents();
	}

	/**
	 * Adds eventlistener to load the Yoast WooCommerce plugin.
	 */
	if( typeof YoastSEO !== "undefined" && typeof YoastSEO.app !== "undefined" ) {
		new YoastWooCommercePlugin(); // eslint-disable-line no-new
	} else {
		jQuery( window ).on(
			"YoastSEO:ready",
			function() {
				new YoastWooCommercePlugin(); // eslint-disable-line no-new
			}
		);
	}

	/**
	 * Strip double spaces from text.
	 *
	 * @param {string} text The text to strip spaces from.
	 *
	 * @returns {string} The text without double spaces.
	 */
	var stripSpaces = function( text ) {
		// Replace multiple spaces with single space
		text = text.replace( /\s{2,}/g, " " );

		// Replace spaces followed by periods with only the period.
		text = text.replace( /\s\./g, "." );

		// Remove first/last character if space
		text = text.replace( /^\s+|\s+$/g, "" );

		return text;
	};

	/**
	 * Strip HTML-tags from text
	 *
	 * @param {string} text The text to strip the HTML-tags from.
	 *
	 * @returns {string} The text without HTML-tags.
	 */
	var stripTags = function( text ) {
		text = text.replace( /(<([^>]+)>)/ig, " " );
		text = stripSpaces( text );
		return text;
	};

	/**
	 * Tests the length of the product description.
	 *
	 * @returns {Object} An assessment result with the score and formatted text.
	 */
	YoastWooCommercePlugin.prototype.productDescription = function() {
		var productDescription = document.getElementById( "excerpt" ).value;
		if ( typeof tinyMCE !== "undefined" && tinyMCE.get( "excerpt" ) !== null ) {
			productDescription = tinyMCE.get( "excerpt" ).getContent();
		}

		productDescription = stripTags( productDescription );
		var result = this.scoreProductDescription( productDescription.split( " " ).length );
		var assessmentResult = new AssessmentResult();
		assessmentResult.setScore( result.score );
		assessmentResult.setText( result.text );
		return assessmentResult;
	};

	/**
	 * Returns the score based on the length of the product description.
	 *
	 * @param {number} length The length of the product description.
	 *
	 * @returns {{score: number, text: *}} The result object with score and text.
	 */
	YoastWooCommercePlugin.prototype.scoreProductDescription = function( length ) {
		if ( length === 0 ) {
			return {
				score: 1,
				text: wpseoWooL10n.woo_desc_none,
			};
		}

		if ( length > 0 && length < 20 ) {
			return {
				score: 5,
				text: wpseoWooL10n.woo_desc_short,
			};
		}

		if ( length >= 20 && length <= 50 ) {
			return {
				score: 9,
				text: wpseoWooL10n.woo_desc_good,
			};
		}
		if ( length > 50 ) {
			return {
				score: 5,
				text: wpseoWooL10n.woo_desc_long,
			};
		}
	};

	/**
	 * Adds callback to the excerpt field to trigger the analyzeTimer when product description is updated.
	 * The tinyMCE triggers automatically since that inherits the binding from the content field tinyMCE.
	 *
	 * @returns {void}
	 */
	YoastWooCommercePlugin.prototype.addCallback = function() {
		var elem = document.getElementById( "excerpt" );
		if( elem !== null ) {
			elem.addEventListener( "input", YoastSEO.app.analyzeTimer.bind( YoastSEO.app ) );
		}
	};

	/**
	 * Binds events to the add_product_images anchor.
	 *
	 * @returns {void}
	 */
	YoastWooCommercePlugin.prototype.bindEvents = function() {
		jQuery( ".add_product_images" ).find( "a" ).on( "click", this.bindLinkEvent.bind( this ) );
	};

	/**
	 * Counters for the setTimeouts, used to make sure we don't end up in an infinite loop.
	 *
	 * @type {number}
	 */
	var buttonEventCounter = 0;
	var deleteEventCounter = 0;

	/**
	 * After the modal dialog is opened, check for the button that adds images to the gallery to trigger
	 * the modification.
	 *
	 * @returns {void}
	 */
	YoastWooCommercePlugin.prototype.bindLinkEvent = function() {
		if ( jQuery( ".media-modal-content" ).find( ".media-button" ).length === 0 ) {
			buttonEventCounter++;
			if ( buttonEventCounter < 10 ) {
				setTimeout( this.bindLinkEvent.bind( this ) );
			}
		} else {
			buttonEventCounter = 0;
			jQuery( ".media-modal-content" ).find( ".media-button" ).on( "click", this.buttonCallback.bind( this )  );
		}
	};

	/**
	 * After the gallery is added, call the analyzeTimer of the app, to add the modifications.
	 * Also call the bindDeleteEvent, to bind the analyzerTimer when an image is deleted.
	 *
	 * @returns {void}
	 */
	YoastWooCommercePlugin.prototype.buttonCallback = function() {
		YoastSEO.app.analyzeTimer();
		this.bindDeleteEvent();
	};

	/**
	 * Checks if the delete buttons of the added images are available. When they are, bind the analyzeTimer function
	 * so when a image is removed, the modification is run.
	 *
	 * @returns {void}
	 */
	YoastWooCommercePlugin.prototype.bindDeleteEvent = function() {
		if ( jQuery( "#product_images_container" ).find( ".delete" ).length === 0 ) {
			deleteEventCounter++;
			if ( deleteEventCounter < 10 ) {
				setTimeout( this.bindDeleteEvent.bind( this ) );
			}
		} else {
			deleteEventCounter = 0;
			jQuery( "#product_images_container" ).find( ".delete" ).on( "click", YoastSEO.app.analyzeTimer.bind( YoastSEO.app ) );
		}
	};

	/**
	 * Registers the addImageToContent modification.
	 *
	 * @returns {void}
	 */
	YoastWooCommercePlugin.prototype.registerModifications = function() {
		var callback = this.addImageToContent.bind( this );

		YoastSEO.app.registerModification( "content", callback, "YoastWooCommercePlugin", 10 );
	};

	/**
	 * Adds the images from the page gallery to the content to be analyzed by the analyzer.
	 *
	 * @param {string} data The data string that does not have the images outer html.
	 *
	 * @returns {string} The data string parameter with the images outer html.
	 */
	YoastWooCommercePlugin.prototype.addImageToContent = function( data ) {
		var images = jQuery( "#product_images_container" ).find( "img" );

		for( var i = 0; i < images.length; i++ ) {
			data += images[ i ].outerHTML;
		}
		return data;
	};
}() );
