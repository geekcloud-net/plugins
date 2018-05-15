(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
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

},{"yoastseo/js/values/AssessmentResult":11}],2:[function(require,module,exports){
var root = require('./_root');

/** Built-in value references. */
var Symbol = root.Symbol;

module.exports = Symbol;

},{"./_root":7}],3:[function(require,module,exports){
var Symbol = require('./_Symbol'),
    getRawTag = require('./_getRawTag'),
    objectToString = require('./_objectToString');

/** `Object#toString` result references. */
var nullTag = '[object Null]',
    undefinedTag = '[object Undefined]';

/** Built-in value references. */
var symToStringTag = Symbol ? Symbol.toStringTag : undefined;

/**
 * The base implementation of `getTag` without fallbacks for buggy environments.
 *
 * @private
 * @param {*} value The value to query.
 * @returns {string} Returns the `toStringTag`.
 */
function baseGetTag(value) {
  if (value == null) {
    return value === undefined ? undefinedTag : nullTag;
  }
  return (symToStringTag && symToStringTag in Object(value))
    ? getRawTag(value)
    : objectToString(value);
}

module.exports = baseGetTag;

},{"./_Symbol":2,"./_getRawTag":5,"./_objectToString":6}],4:[function(require,module,exports){
(function (global){
/** Detect free variable `global` from Node.js. */
var freeGlobal = typeof global == 'object' && global && global.Object === Object && global;

module.exports = freeGlobal;

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{}],5:[function(require,module,exports){
var Symbol = require('./_Symbol');

/** Used for built-in method references. */
var objectProto = Object.prototype;

/** Used to check objects for own properties. */
var hasOwnProperty = objectProto.hasOwnProperty;

/**
 * Used to resolve the
 * [`toStringTag`](http://ecma-international.org/ecma-262/7.0/#sec-object.prototype.tostring)
 * of values.
 */
var nativeObjectToString = objectProto.toString;

/** Built-in value references. */
var symToStringTag = Symbol ? Symbol.toStringTag : undefined;

/**
 * A specialized version of `baseGetTag` which ignores `Symbol.toStringTag` values.
 *
 * @private
 * @param {*} value The value to query.
 * @returns {string} Returns the raw `toStringTag`.
 */
function getRawTag(value) {
  var isOwn = hasOwnProperty.call(value, symToStringTag),
      tag = value[symToStringTag];

  try {
    value[symToStringTag] = undefined;
    var unmasked = true;
  } catch (e) {}

  var result = nativeObjectToString.call(value);
  if (unmasked) {
    if (isOwn) {
      value[symToStringTag] = tag;
    } else {
      delete value[symToStringTag];
    }
  }
  return result;
}

module.exports = getRawTag;

},{"./_Symbol":2}],6:[function(require,module,exports){
/** Used for built-in method references. */
var objectProto = Object.prototype;

/**
 * Used to resolve the
 * [`toStringTag`](http://ecma-international.org/ecma-262/7.0/#sec-object.prototype.tostring)
 * of values.
 */
var nativeObjectToString = objectProto.toString;

/**
 * Converts `value` to a string using `Object.prototype.toString`.
 *
 * @private
 * @param {*} value The value to convert.
 * @returns {string} Returns the converted string.
 */
function objectToString(value) {
  return nativeObjectToString.call(value);
}

module.exports = objectToString;

},{}],7:[function(require,module,exports){
var freeGlobal = require('./_freeGlobal');

/** Detect free variable `self`. */
var freeSelf = typeof self == 'object' && self && self.Object === Object && self;

/** Used as a reference to the global object. */
var root = freeGlobal || freeSelf || Function('return this')();

module.exports = root;

},{"./_freeGlobal":4}],8:[function(require,module,exports){
var baseGetTag = require('./_baseGetTag'),
    isObjectLike = require('./isObjectLike');

/** `Object#toString` result references. */
var numberTag = '[object Number]';

/**
 * Checks if `value` is classified as a `Number` primitive or object.
 *
 * **Note:** To exclude `Infinity`, `-Infinity`, and `NaN`, which are
 * classified as numbers, use the `_.isFinite` method.
 *
 * @static
 * @memberOf _
 * @since 0.1.0
 * @category Lang
 * @param {*} value The value to check.
 * @returns {boolean} Returns `true` if `value` is a number, else `false`.
 * @example
 *
 * _.isNumber(3);
 * // => true
 *
 * _.isNumber(Number.MIN_VALUE);
 * // => true
 *
 * _.isNumber(Infinity);
 * // => true
 *
 * _.isNumber('3');
 * // => false
 */
function isNumber(value) {
  return typeof value == 'number' ||
    (isObjectLike(value) && baseGetTag(value) == numberTag);
}

module.exports = isNumber;

},{"./_baseGetTag":3,"./isObjectLike":9}],9:[function(require,module,exports){
/**
 * Checks if `value` is object-like. A value is object-like if it's not `null`
 * and has a `typeof` result of "object".
 *
 * @static
 * @memberOf _
 * @since 4.0.0
 * @category Lang
 * @param {*} value The value to check.
 * @returns {boolean} Returns `true` if `value` is object-like, else `false`.
 * @example
 *
 * _.isObjectLike({});
 * // => true
 *
 * _.isObjectLike([1, 2, 3]);
 * // => true
 *
 * _.isObjectLike(_.noop);
 * // => false
 *
 * _.isObjectLike(null);
 * // => false
 */
function isObjectLike(value) {
  return value != null && typeof value == 'object';
}

module.exports = isObjectLike;

},{}],10:[function(require,module,exports){
/**
 * Checks if `value` is `undefined`.
 *
 * @static
 * @since 0.1.0
 * @memberOf _
 * @category Lang
 * @param {*} value The value to check.
 * @returns {boolean} Returns `true` if `value` is `undefined`, else `false`.
 * @example
 *
 * _.isUndefined(void 0);
 * // => true
 *
 * _.isUndefined(null);
 * // => false
 */
function isUndefined(value) {
  return value === undefined;
}

module.exports = isUndefined;

},{}],11:[function(require,module,exports){
"use strict";

var isUndefined = require("lodash/isUndefined");
var isNumber = require("lodash/isNumber");
/**
 * A function that only returns an empty that can be used as an empty marker
 *
 * @returns {Array} A list of empty marks.
 */
var emptyMarker = function emptyMarker() {
    return [];
};
/**
 * Construct the AssessmentResult value object.
 *
 * @param {Object} [values] The values for this assessment result.
 *
 * @constructor
 */
var AssessmentResult = function AssessmentResult(values) {
    this._hasScore = false;
    this._identifier = "";
    this._hasMarks = false;
    this._marker = emptyMarker;
    this.score = 0;
    this.text = "";
    if (isUndefined(values)) {
        values = {};
    }
    if (!isUndefined(values.score)) {
        this.setScore(values.score);
    }
    if (!isUndefined(values.text)) {
        this.setText(values.text);
    }
};
/**
 * Check if a score is available.
 * @returns {boolean} Whether or not a score is available.
 */
AssessmentResult.prototype.hasScore = function () {
    return this._hasScore;
};
/**
 * Get the available score
 * @returns {number} The score associated with the AssessmentResult.
 */
AssessmentResult.prototype.getScore = function () {
    return this.score;
};
/**
 * Set the score for the assessment.
 * @param {number} score The score to be used for the score property
 * @returns {void}
 */
AssessmentResult.prototype.setScore = function (score) {
    if (isNumber(score)) {
        this.score = score;
        this._hasScore = true;
    }
};
/**
 * Check if a text is available.
 * @returns {boolean} Whether or not a text is available.
 */
AssessmentResult.prototype.hasText = function () {
    return this.text !== "";
};
/**
 * Get the available text
 * @returns {string} The text associated with the AssessmentResult.
 */
AssessmentResult.prototype.getText = function () {
    return this.text;
};
/**
 * Set the text for the assessment.
 * @param {string} text The text to be used for the text property
 * @returns {void}
 */
AssessmentResult.prototype.setText = function (text) {
    if (isUndefined(text)) {
        text = "";
    }
    this.text = text;
};
/**
 * Sets the identifier
 *
 * @param {string} identifier An alphanumeric identifier for this result.
 * @returns {void}
 */
AssessmentResult.prototype.setIdentifier = function (identifier) {
    this._identifier = identifier;
};
/**
 * Gets the identifier
 *
 * @returns {string} An alphanumeric identifier for this result.
 */
AssessmentResult.prototype.getIdentifier = function () {
    return this._identifier;
};
/**
 * Sets the marker, a pure function that can return the marks for a given Paper
 *
 * @param {Function} marker The marker to set.
 * @returns {void}
 */
AssessmentResult.prototype.setMarker = function (marker) {
    this._marker = marker;
};
/**
 * Returns whether or not this result has a marker that can be used to mark for a given Paper
 *
 * @returns {boolean} Whether or this result has a marker.
 */
AssessmentResult.prototype.hasMarker = function () {
    return this._hasMarks && this._marker !== emptyMarker;
};
/**
 * Gets the marker, a pure function that can return the marks for a given Paper
 *
 * @returns {Function} The marker.
 */
AssessmentResult.prototype.getMarker = function () {
    return this._marker;
};
/**
 * Sets the value of _hasMarks to determine if there is something to mark.
 *
 * @param {boolean} hasMarks Is there something to mark.
 * @returns {void}
 */
AssessmentResult.prototype.setHasMarks = function (hasMarks) {
    this._hasMarks = hasMarks;
};
/**
 * Returns the value of _hasMarks to determine if there is something to mark.
 *
 * @returns {boolean} Is there something to mark.
 */
AssessmentResult.prototype.hasMarks = function () {
    return this._hasMarks;
};
module.exports = AssessmentResult;



},{"lodash/isNumber":8,"lodash/isUndefined":10}]},{},[1]);
