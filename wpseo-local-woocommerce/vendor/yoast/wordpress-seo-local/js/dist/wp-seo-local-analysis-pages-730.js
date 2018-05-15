(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
'use strict';

/* global YoastSEO */
/* global wpseoLocalL10n */
(function () {
	'use strict';

	var AssessmentResult = require('yoastseo/js/values/AssessmentResult');

	/**
  * Adds the plugin for videoSEO to the YoastSEO Analyzer.
  */
	var YoastlocalSEOplugin = function YoastlocalSEOplugin() {
		YoastSEO.app.registerPlugin('YoastLocalSEO', { 'status': 'ready' });

		YoastSEO.app.registerAssessment('localStorelocatorContent', { getResult: this.localStorelocatorContent.bind(this) }, 'YoastLocalSEO');

		this.addCallback();
	};

	/**
  *
  * @param {object} paper The paper to run this assessment on
  * @param {object} researcher The researcher used for the assessment
  * @param {object} i18n The i18n-object used for parsing translations
  * @returns {object} an assessmentresult with the score and formatted text.
  */
	YoastlocalSEOplugin.prototype.localTitle = function (paper, researcher, i18n) {
		var assessmentResult = new AssessmentResult();
		if (wpseoLocalL10n.location !== '') {
			var business_city = new RegExp(wpseoLocalL10n.location, 'ig');
			var matches = paper.getTitle().match(business_city) || 0;

			var result = this.localTitleScore(matches);

			assessmentResult.setScore(result.score);
			assessmentResult.setText(result.text);
		}
		return assessmentResult;
	};

	/**
  *
  * @param matches
  * @returns {{score: number, text: *}}
  */
	YoastlocalSEOplugin.prototype.localTitleScore = function (matches) {
		if (matches.length > 0) {
			return {
				score: 9,
				text: wpseoLocalL10n.title_location
			};
		}
		return {
			score: 4,
			text: wpseoLocalL10n.title_no_location
		};
	};

	/**
  * Runs an assessment for scoring the location in the URL.
  * @param {object} paper The paper to run this assessment on
  * @param {object} researcher The researcher used for the assessment
  * @param {object} i18n The i18n-object used for parsing translations
  * @returns {object} an assessmentresult with the score and formatted text.
  */
	YoastlocalSEOplugin.prototype.localStorelocatorContent = function (paper, researcher, i18n) {
		var assessmentResult = new AssessmentResult();

		var store_locator = new RegExp('\<\!\-\-local_seo_store_locator_start\-\-\>((.|[\n|\r|\r\n])*?)\<\!\-\-local_seo_store_locator_end\-\-\>', 'ig');
		var content = paper.getText().replace(store_locator, '');
		var result = this.scoreLocalStorelocatorContent(content);

		assessmentResult.setScore(result.score);
		assessmentResult.setText(result.text);

		return assessmentResult;
	};

	/**
  * Scores the url based on the matches of the location.
  * @param {array} content The content outsde of the storelocator shortcode.
  * @returns {{score: number, text: *}}
  */
	YoastlocalSEOplugin.prototype.scoreLocalStorelocatorContent = function (content) {
		if (content.length <= 200) {
			return {
				score: 6,
				text: wpseoLocalL10n.storelocator_content
			};
		}

		return {
			score: 9,
			text: ''
		};
	};

	/**
  * Adds callback for the wpseo_business_city field so it is updated
  */
	YoastlocalSEOplugin.prototype.addCallback = function () {
		var elem = document.getElementById('wpseo_business_city');
		if (elem !== null) {
			elem.addEventListener('change', YoastSEO.app.analyzeTimer.bind(YoastSEO.app));
		}
	};

	/**
  * Adds eventListener on page load to load the videoSEO.
  */
	if (typeof YoastSEO !== 'undefined' && typeof YoastSEO.app !== 'undefined') {
		new YoastlocalSEOplugin();
	} else {
		jQuery(window).on('YoastSEO:ready', function () {
			new YoastlocalSEOplugin();
		});
	}
})();

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



},{"lodash/isNumber":8,"lodash/isUndefined":10}]},{},[1])
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJqcy9zcmMvd3Atc2VvLWxvY2FsLWFuYWx5c2lzLXBhZ2VzLmpzIiwibm9kZV9tb2R1bGVzL2xvZGFzaC9fU3ltYm9sLmpzIiwibm9kZV9tb2R1bGVzL2xvZGFzaC9fYmFzZUdldFRhZy5qcyIsIm5vZGVfbW9kdWxlcy9sb2Rhc2gvX2ZyZWVHbG9iYWwuanMiLCJub2RlX21vZHVsZXMvbG9kYXNoL19nZXRSYXdUYWcuanMiLCJub2RlX21vZHVsZXMvbG9kYXNoL19vYmplY3RUb1N0cmluZy5qcyIsIm5vZGVfbW9kdWxlcy9sb2Rhc2gvX3Jvb3QuanMiLCJub2RlX21vZHVsZXMvbG9kYXNoL2lzTnVtYmVyLmpzIiwibm9kZV9tb2R1bGVzL2xvZGFzaC9pc09iamVjdExpa2UuanMiLCJub2RlX21vZHVsZXMvbG9kYXNoL2lzVW5kZWZpbmVkLmpzIiwibm9kZV9tb2R1bGVzL3lvYXN0c2VvL2pzL3ZhbHVlcy9Bc3Nlc3NtZW50UmVzdWx0LmpzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7QUNBQTtBQUNBO0FBQ0MsYUFBVztBQUNYOztBQUVBLEtBQUksbUJBQW1CLFFBQVMscUNBQVQsQ0FBdkI7O0FBRUE7OztBQUdBLEtBQUksc0JBQXNCLFNBQXRCLG1CQUFzQixHQUFVO0FBQ25DLFdBQVMsR0FBVCxDQUFhLGNBQWIsQ0FBNkIsZUFBN0IsRUFBOEMsRUFBRSxVQUFVLE9BQVosRUFBOUM7O0FBRUEsV0FBUyxHQUFULENBQWEsa0JBQWIsQ0FBaUMsMEJBQWpDLEVBQTZELEVBQUUsV0FBWSxLQUFLLHdCQUFMLENBQThCLElBQTlCLENBQW9DLElBQXBDLENBQWQsRUFBN0QsRUFBeUgsZUFBekg7O0FBRUEsT0FBSyxXQUFMO0FBQ0EsRUFORDs7QUFRQTs7Ozs7OztBQU9BLHFCQUFvQixTQUFwQixDQUE4QixVQUE5QixHQUEyQyxVQUFVLEtBQVYsRUFBaUIsVUFBakIsRUFBNkIsSUFB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QTtBQUNBO0FBQ0E7Ozs7QUNKQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQzlDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ3RCQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNUQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDdENBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUM3QkE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUN0QkE7O0FBRUEsSUFB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LFFBQTNCLEdBQXNDLFlBQVk7QUFDOUMsV0FBTyxLQUFLLFNBQVo7QUFDSCxDQUZEO0FBR0EsT0FBTyxPQUFQLEdBQWlCLGdCQUFqQjtBQUNBO0FBQ0EiLCJmaWxlIjoiZ2VuZXJhdGVkLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXNDb250ZW50IjpbIihmdW5jdGlvbiBlKHQsbixyKXtmdW5jdGlvbiBzKG8sdSl7aWYoIW5bb10pe2lmKCF0W29dKXt2YXIgYT10eXBlb2YgcmVxdWlyZT09XCJmdW5jdGlvblwiJiZyZXF1aXJlO2lmKCF1JiZhKXJldHVybiBhKG8sITApO2lmKGkpcmV0dXJuIGkobywhMCk7dmFyIGY9bmV3IEVycm9yKFwiQ2Fubm90IGZpbmQgbW9kdWxlICdcIitvK1wiJ1wiKTt0aHJvdyBmLmNvZGU9XCJNT0RVTEVfTk9UX0ZPVU5EXCIsZn12YXIgbD1uW29dPXtleHBvcnRzOnt9fTt0W29dWzBdLmNhbGwobC5leHBvcnRzLGZ1bmN0aW9uKGUpe3ZhciBuPXRbb11bMV1bZV07cmV0dXJuIHMobj9uOmUpfSxsLGwuZXhwb3J0cyxlLHQsbixyKX1yZXR1cm4gbltvXS5leHBvcnRzfXZhciBpPXR5cGVvZiByZXF1aXJlPT1cImZ1bmN0aW9uXCImJnJlcXVpcmU7Zm9yKHZhciBvPTA7bzxyLmxlbmd0aDtvKyspcyhyW29dKTtyZXR1cm4gc30pIiwiLyogZ2xvYmFsIFlvYXN0U0VPICovXG4vKiBnbG9iYWwgd3BzZW9Mb2NhbEwxMG4gKi9cbihmdW5jdGlvbigpIHtcblx0J3VzZSBzdHJpY3QnO1xuXG5cdHZhciBBc3Nlc3NtZW50UmVzdWx0ID0gcmVxdWlyZSggJ3lvYXN0c2VvL2pzL3ZhbHVlcy9Bc3Nlc3NtZW50UmVzdWx0JyApO1xuXG5cdC8qKlxuXHQgKiBBZGRzIHRoZSBwbHVnaW4gZm9yIHZpZGVvU0VPIHRvIHRoZSBZb2FzdFNFTyBBbmFseXplci5cblx0ICovXG5cdHZhciBZb2FzdGxvY2FsU0VPcGx1Z2luID0gZnVuY3Rpb24oKXtcblx0XHRZb2FzdFNFTy5hcHAucmVnaXN0ZXJQbHVnaW4oICdZb2FzdExvY2FsU0VPJywgeyAnc3RhdHVzJzogJ3JlYWR5JyB9KTtcblxuXHRcdFlvYXN0U0VPLmFwcC5yZWdpc3RlckFzc2Vzc21lbnQoICdsb2NhbFN0b3JlbG9jYXRvckNvbnRlbnQnLCB7IGdldFJlc3VsdDogIHRoaXMubG9jYWxTdG9yZWxvY2F0b3JDb250ZW50LmJpbmQoIHRoaXMgKSB9LCAnWW9hc3RMb2NhbFNFTycgKTtcblxuXHRcdHRoaXMuYWRkQ2FsbGJhY2soKTtcblx0fTtcblxuXHQvKipcblx0ICpcblx0ICogQHBhcmFtIHtvYmplY3R9IHBhcGVyIFRoZSBwYXBlciB0byBydW4gdGhpcyBhc3Nlc3NtZW50IG9uXG5cdCAqIEBwYXJhbSB7b2JqZWN0fSByZXNlYXJjaGVyIFRoZSByZXNlYXJjaGVyIHVzZWQgZm9yIHRoZSBhc3Nlc3NtZW50XG5cdCAqIEBwYXJhbSB7b2JqZWN0fSBpMThuIFRoZSBpMThuLW9iamVjdCB1c2VkIGZvciBwYXJzaW5nIHRyYW5zbGF0aW9uc1xuXHQgKiBAcmV0dXJucyB7b2JqZWN0fSBhbiBhc3Nlc3NtZW50cmVzdWx0IHdpdGggdGhlIHNjb3JlIGFuZCBmb3JtYXR0ZWQgdGV4dC5cblx0ICovXG5cdFlvYXN0bG9jYWxTRU9wbHVnaW4ucHJvdG90eXBlLmxvY2FsVGl0bGUgPSBmdW5jdGlvbiggcGFwZXIsIHJlc2VhcmNoZXIsIGkxOG4gKSB7XG5cdFx0dmFyIGFzc2Vzc21lbnRSZXN1bHQgPSBuZXcgQXNzZXNzbWVudFJlc3VsdCgpO1xuXHRcdGlmKCB3cHNlb0xvY2FsTDEwbi5sb2NhdGlvbiAhPT0gJycgKSB7XG5cdFx0XHR2YXIgYnVzaW5lc3NfY2l0eSA9IG5ldyBSZWdFeHAoIHdwc2VvTG9jYWxMMTBuLmxvY2F0aW9uLCAnaWcnKTtcblx0XHRcdHZhciBtYXRjaGVzID0gcGFwZXIuZ2V0VGl0bGUoKS5tYXRjaCggYnVzaW5lc3NfY2l0eSApIHx8IDA7XG5cblx0XHRcdHZhciByZXN1bHQgPSB0aGlzLmxvY2FsVGl0bGVTY29yZSggbWF0Y2hlcyApO1xuXG5cdFx0XHRhc3Nlc3NtZW50UmVzdWx0LnNldFNjb3JlKCByZXN1bHQuc2NvcmUgKTtcblx0XHRcdGFzc2Vzc21lbnRSZXN1bHQuc2V0VGV4dCggcmVzdWx0LnRleHQgKTtcblx0XHR9XG5cdFx0cmV0dXJuIGFzc2Vzc21lbnRSZXN1bHQ7XG5cdH07XG5cblx0LyoqXG5cdCAqXG5cdCAqIEBwYXJhbSBtYXRjaGVzXG5cdCAqIEByZXR1cm5zIHt7c2NvcmU6IG51bWJlciwgdGV4dDogKn19XG5cdCAqL1xuXHRZb2FzdGxvY2FsU0VPcGx1Z2luLnByb3RvdHlwZS5sb2NhbFRpdGxlU2NvcmUgPSBmdW5jdGlvbiggbWF0Y2hlcyApe1xuXHRcdGlmICggbWF0Y2hlcy5sZW5ndGggPiAwICkge1xuXHRcdFx0cmV0dXJuIHtcblx0XHRcdFx0c2NvcmU6IDksXG5cdFx0XHRcdHRleHQ6IHdwc2VvTG9jYWxMMTBuLnRpdGxlX2xvY2F0aW9uXG5cdFx0XHR9XG5cdFx0fVxuXHRcdHJldHVybiB7XG5cdFx0XHRzY29yZTogNCxcblx0XHRcdHRleHQ6IHdwc2VvTG9jYWxMMTBuLnRpdGxlX25vX2xvY2F0aW9uXG5cdFx0fVxuXHR9O1xuXG5cdC8qKlxuXHQgKiBSdW5zIGFuIGFzc2Vzc21lbnQgZm9yIHNjb3JpbmcgdGhlIGxvY2F0aW9uIGluIHRoZSBVUkwuXG5cdCAqIEBwYXJhbSB7b2JqZWN0fSBwYXBlciBUaGUgcGFwZXIgdG8gcnVuIHRoaXMgYXNzZXNzbWVudCBvblxuXHQgKiBAcGFyYW0ge29iamVjdH0gcmVzZWFyY2hlciBUaGUgcmVzZWFyY2hlciB1c2VkIGZvciB0aGUgYXNzZXNzbWVudFxuXHQgKiBAcGFyYW0ge29iamVjdH0gaTE4biBUaGUgaTE4bi1vYmplY3QgdXNlZCBmb3IgcGFyc2luZyB0cmFuc2xhdGlvbnNcblx0ICogQHJldHVybnMge29iamVjdH0gYW4gYXNzZXNzbWVudHJlc3VsdCB3aXRoIHRoZSBzY29yZSBhbmQgZm9ybWF0dGVkIHRleHQuXG5cdCAqL1xuXHRZb2FzdGxvY2FsU0VPcGx1Z2luLnByb3RvdHlwZS5sb2NhbFN0b3JlbG9jYXRvckNvbnRlbnQgPSBmdW5jdGlvbiggcGFwZXIsIHJlc2VhcmNoZXIsIGkxOG4gKSB7XG5cdFx0dmFyIGFzc2Vzc21lbnRSZXN1bHQgPSBuZXcgQXNzZXNzbWVudFJlc3VsdCgpO1xuXG5cdFx0dmFyIHN0b3JlX2xvY2F0b3IgPSBuZXcgUmVnRXhwKCAnXFw8XFwhXFwtXFwtbG9jYWxfc2VvX3N0b3JlX2xvY2F0b3Jfc3RhcnRcXC1cXC1cXD4oKC58W1xcbnxcXHJ8XFxyXFxuXSkqPylcXDxcXCFcXC1cXC1sb2NhbF9zZW9fc3RvcmVfbG9jYXRvcl9lbmRcXC1cXC1cXD4nLCAnaWcnICk7XG5cdFx0dmFyIGNvbnRlbnQgPSBwYXBlci5nZXRUZXh0KCkucmVwbGFjZSggc3RvcmVfbG9jYXRvciwgJycgKTtcblx0XHR2YXIgcmVzdWx0ID0gdGhpcy5zY29yZUxvY2FsU3RvcmVsb2NhdG9yQ29udGVudCggY29udGVudCApO1xuXG5cdFx0YXNzZXNzbWVudFJlc3VsdC5zZXRTY29yZSggcmVzdWx0LnNjb3JlICk7XG5cdFx0YXNzZXNzbWVudFJlc3VsdC5zZXRUZXh0KCByZXN1bHQudGV4dCApO1xuXG5cdFx0cmV0dXJuIGFzc2Vzc21lbnRSZXN1bHQ7XG5cdH07XG5cblx0LyoqXG5cdCAqIFNjb3JlcyB0aGUgdXJsIGJhc2VkIG9uIHRoZSBtYXRjaGVzIG9mIHRoZSBsb2NhdGlvbi5cblx0ICogQHBhcmFtIHthcnJheX0gY29udGVudCBUaGUgY29udGVudCBvdXRzZGUgb2YgdGhlIHN0b3JlbG9jYXRvciBzaG9ydGNvZGUuXG5cdCAqIEByZXR1cm5zIHt7c2NvcmU6IG51bWJlciwgdGV4dDogKn19XG5cdCAqL1xuXHRZb2FzdGxvY2FsU0VPcGx1Z2luLnByb3RvdHlwZS5zY29yZUxvY2FsU3RvcmVsb2NhdG9yQ29udGVudCA9IGZ1bmN0aW9uKCBjb250ZW50ICkge1xuXHRcdGlmICggY29udGVudC5sZW5ndGggPD0gMjAwICkge1xuXHRcdFx0cmV0dXJue1xuXHRcdFx0XHRzY29yZTogNixcblx0XHRcdFx0dGV4dDogd3BzZW9Mb2NhbEwxMG4uc3RvcmVsb2NhdG9yX2NvbnRlbnRcblx0XHRcdH1cblx0XHR9XG5cblx0XHRyZXR1cm57XG5cdFx0XHRzY29yZTogOSxcblx0XHRcdHRleHQ6ICcnXG5cdFx0fVxuXHR9O1xuXG5cdC8qKlxuXHQgKiBBZGRzIGNhbGxiYWNrIGZvciB0aGUgd3BzZW9fYnVzaW5lc3NfY2l0eSBmaWVsZCBzbyBpdCBpcyB1cGRhdGVkXG5cdCAqL1xuXHRZb2FzdGxvY2FsU0VPcGx1Z2luLnByb3RvdHlwZS5hZGRDYWxsYmFjayA9IGZ1bmN0aW9uKCkge1xuXHRcdHZhciBlbGVtID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoICd3cHNlb19idXNpbmVzc19jaXR5JyApO1xuXHRcdGlmKCBlbGVtICE9PSBudWxsKXtcblx0XHRcdGVsZW0uYWRkRXZlbnRMaXN0ZW5lciggJ2NoYW5nZScsIFlvYXN0U0VPLmFwcC5hbmFseXplVGltZXIuYmluZCAoIFlvYXN0U0VPLmFwcCApICk7XG5cdFx0fVxuXHR9O1xuXG5cdC8qKlxuXHQgKiBBZGRzIGV2ZW50TGlzdGVuZXIgb24gcGFnZSBsb2FkIHRvIGxvYWQgdGhlIHZpZGVvU0VPLlxuXHQgKi9cblx0aWYgKCB0eXBlb2YgWW9hc3RTRU8gIT09ICd1bmRlZmluZWQnICYmIHR5cGVvZiBZb2FzdFNFTy5hcHAgIT09ICd1bmRlZmluZWQnICkge1xuXHRcdG5ldyBZb2FzdGxvY2FsU0VPcGx1Z2luKCk7XG5cdH1cblx0ZWxzZSB7XG5cdFx0alF1ZXJ5KCB3aW5kb3cgKS5vbihcblx0XHRcdCdZb2FzdFNFTzpyZWFkeScsXG5cdFx0XHRmdW5jdGlvbigpIHtcblx0XHRcdFx0bmV3IFlvYXN0bG9jYWxTRU9wbHVnaW4oKTtcblx0XHRcdH1cblx0XHQpO1xuXHR9XG5cbn0oKSk7XG4iLCJ2YXIgcm9vdCA9IHJlcXVpcmUoJy4vX3Jvb3QnKTtcblxuLyoqIEJ1aWx0LWluIHZhbHVlIHJlZmVyZW5jZXMuICovXG52YXIgU3ltYm9sID0gcm9vdC5TeW1ib2w7XG5cbm1vZHVsZS5leHBvcnRzID0gU3ltYm9sO1xuIiwidmFyIFN5bWJvbCA9IHJlcXVpcmUoJy4vX1N5bWJvbCcpLFxuICAgIGdldFJhd1RhZyA9IHJlcXVpcmUoJy4vX2dldFJhd1RhZycpLFxuICAgIG9iamVjdFRvU3RyaW5nID0gcmVxdWlyZSgnLi9fb2JqZWN0VG9TdHJpbmcnKTtcblxuLyoqIGBPYmplY3QjdG9TdHJpbmdgIHJlc3VsdCByZWZlcmVuY2VzLiAqL1xudmFyIG51bGxUYWcgPSAnW29iamVjdCBOdWxsXScsXG4gICAgdW5kZWZpbmVkVGFnID0gJ1tvYmplY3QgVW5kZWZpbmVkXSc7XG5cbi8qKiBCdWlsdC1pbiB2YWx1ZSByZWZlcmVuY2VzLiAqL1xudmFyIHN5bVRvU3RyaW5nVGFnID0gU3ltYm9sID8gU3ltYm9sLnRvU3RyaW5nVGFnIDogdW5kZWZpbmVkO1xuXG4vKipcbiAqIFRoZSBiYXNlIGltcGxlbWVudGF0aW9uIG9mIGBnZXRUYWdgIHdpdGhvdXQgZmFsbGJhY2tzIGZvciBidWdneSBlbnZpcm9ubWVudHMuXG4gKlxuICogQHByaXZhdGVcbiAqIEBwYXJhbSB7Kn0gdmFsdWUgVGhlIHZhbHVlIHRvIHF1ZXJ5LlxuICogQHJldHVybnMge3N0cmluZ30gUmV0dXJucyB0aGUgYHRvU3RyaW5nVGFnYC5cbiAqL1xuZnVuY3Rpb24gYmFzZUdldFRhZyh2YWx1ZSkge1xuICBpZiAodmFsdWUgPT0gbnVsbCkge1xuICAgIHJldHVybiB2YWx1ZSA9PT0gdW5kZWZpbmVkID8gdW5kZWZpbmVkVGFnIDogbnVsbFRhZztcbiAgfVxuICByZXR1cm4gKHN5bVRvU3RyaW5nVGFnICYmIHN5bVRvU3RyaW5nVGFnIGluIE9iamVjdCh2YWx1ZSkpXG4gICAgPyBnZXRSYXdUYWcodmFsdWUpXG4gICAgOiBvYmplY3RUb1N0cmluZyh2YWx1ZSk7XG59XG5cbm1vZHVsZS5leHBvcnRzID0gYmFzZUdldFRhZztcbiIsIi8qKiBEZXRlY3QgZnJlZSB2YXJpYWJsZSBgZ2xvYmFsYCBmcm9tIE5vZGUuanMuICovXG52YXIgZnJlZUdsb2JhbCA9IHR5cGVvZiBnbG9iYWwgPT0gJ29iamVjdCcgJiYgZ2xvYmFsICYmIGdsb2JhbC5PYmplY3QgPT09IE9iamVjdCAmJiBnbG9iYWw7XG5cbm1vZHVsZS5leHBvcnRzID0gZnJlZUdsb2JhbDtcbiIsInZhciBTeW1ib2wgPSByZXF1aXJlKCcuL19TeW1ib2wnKTtcblxuLyoqIFVzZWQgZm9yIGJ1aWx0LWluIG1ldGhvZCByZWZlcmVuY2VzLiAqL1xudmFyIG9iamVjdFByb3RvID0gT2JqZWN0LnByb3RvdHlwZTtcblxuLyoqIFVzZWQgdG8gY2hlY2sgb2JqZWN0cyBmb3Igb3duIHByb3BlcnRpZXMuICovXG52YXIgaGFzT3duUHJvcGVydHkgPSBvYmplY3RQcm90by5oYXNPd25Qcm9wZXJ0eTtcblxuLyoqXG4gKiBVc2VkIHRvIHJlc29sdmUgdGhlXG4gKiBbYHRvU3RyaW5nVGFnYF0oaHR0cDovL2VjbWEtaW50ZXJuYXRpb25hbC5vcmcvZWNtYS0yNjIvNy4wLyNzZWMtb2JqZWN0LnByb3RvdHlwZS50b3N0cmluZylcbiAqIG9mIHZhbHVlcy5cbiAqL1xudmFyIG5hdGl2ZU9iamVjdFRvU3RyaW5nID0gb2JqZWN0UHJvdG8udG9TdHJpbmc7XG5cbi8qKiBCdWlsdC1pbiB2YWx1ZSByZWZlcmVuY2VzLiAqL1xudmFyIHN5bVRvU3RyaW5nVGFnID0gU3ltYm9sID8gU3ltYm9sLnRvU3RyaW5nVGFnIDogdW5kZWZpbmVkO1xuXG4vKipcbiAqIEEgc3BlY2lhbGl6ZWQgdmVyc2lvbiBvZiBgYmFzZUdldFRhZ2Agd2hpY2ggaWdub3JlcyBgU3ltYm9sLnRvU3RyaW5nVGFnYCB2YWx1ZXMuXG4gKlxuICogQHByaXZhdGVcbiAqIEBwYXJhbSB7Kn0gdmFsdWUgVGhlIHZhbHVlIHRvIHF1ZXJ5LlxuICogQHJldHVybnMge3N0cmluZ30gUmV0dXJucyB0aGUgcmF3IGB0b1N0cmluZ1RhZ2AuXG4gKi9cbmZ1bmN0aW9uIGdldFJhd1RhZyh2YWx1ZSkge1xuICB2YXIgaXNPd24gPSBoYXNPd25Qcm9wZXJ0eS5jYWxsKHZhbHVlLCBzeW1Ub1N0cmluZ1RhZyksXG4gICAgICB0YWcgPSB2YWx1ZVtzeW1Ub1N0cmluZ1RhZ107XG5cbiAgdHJ5IHtcbiAgICB2YWx1ZVtzeW1Ub1N0cmluZ1RhZ10gPSB1bmRlZmluZWQ7XG4gICAgdmFyIHVubWFza2VkID0gdHJ1ZTtcbiAgfSBjYXRjaCAoZSkge31cblxuICB2YXIgcmVzdWx0ID0gbmF0aXZlT2JqZWN0VG9TdHJpbmcuY2FsbCh2YWx1ZSk7XG4gIGlmICh1bm1hc2tlZCkge1xuICAgIGlmIChpc093bikge1xuICAgICAgdmFsdWVbc3ltVG9TdHJpbmdUYWddID0gdGFnO1xuICAgIH0gZWxzZSB7XG4gICAgICBkZWxldGUgdmFsdWVbc3ltVG9TdHJpbmdUYWddO1xuICAgIH1cbiAgfVxuICByZXR1cm4gcmVzdWx0O1xufVxuXG5tb2R1bGUuZXhwb3J0cyA9IGdldFJhd1RhZztcbiIsIi8qKiBVc2VkIGZvciBidWlsdC1pbiBtZXRob2QgcmVmZXJlbmNlcy4gKi9cbnZhciBvYmplY3RQcm90byA9IE9iamVjdC5wcm90b3R5cGU7XG5cbi8qKlxuICogVXNlZCB0byByZXNvbHZlIHRoZVxuICogW2B0b1N0cmluZ1RhZ2BdKGh0dHA6Ly9lY21hLWludGVybmF0aW9uYWwub3JnL2VjbWEtMjYyLzcuMC8jc2VjLW9iamVjdC5wcm90b3R5cGUudG9zdHJpbmcpXG4gKiBvZiB2YWx1ZXMuXG4gKi9cbnZhciBuYXRpdmVPYmplY3RUb1N0cmluZyA9IG9iamVjdFByb3RvLnRvU3RyaW5nO1xuXG4vKipcbiAqIENvbnZlcnRzIGB2YWx1ZWAgdG8gYSBzdHJpbmcgdXNpbmcgYE9iamVjdC5wcm90b3R5cGUudG9TdHJpbmdgLlxuICpcbiAqIEBwcml2YXRlXG4gKiBAcGFyYW0geyp9IHZhbHVlIFRoZSB2YWx1ZSB0byBjb252ZXJ0LlxuICogQHJldHVybnMge3N0cmluZ30gUmV0dXJucyB0aGUgY29udmVydGVkIHN0cmluZy5cbiAqL1xuZnVuY3Rpb24gb2JqZWN0VG9TdHJpbmcodmFsdWUpIHtcbiAgcmV0dXJuIG5hdGl2ZU9iamVjdFRvU3RyaW5nLmNhbGwodmFsdWUpO1xufVxuXG5tb2R1bGUuZXhwb3J0cyA9IG9iamVjdFRvU3RyaW5nO1xuIiwidmFyIGZyZWVHbG9iYWwgPSByZXF1aXJlKCcuL19mcmVlR2xvYmFsJyk7XG5cbi8qKiBEZXRlY3QgZnJlZSB2YXJpYWJsZSBgc2VsZmAuICovXG52YXIgZnJlZVNlbGYgPSB0eXBlb2Ygc2VsZiA9PSAnb2JqZWN0JyAmJiBzZWxmICYmIHNlbGYuT2JqZWN0ID09PSBPYmplY3QgJiYgc2VsZjtcblxuLyoqIFVzZWQgYXMgYSByZWZlcmVuY2UgdG8gdGhlIGdsb2JhbCBvYmplY3QuICovXG52YXIgcm9vdCA9IGZyZWVHbG9iYWwgfHwgZnJlZVNlbGYgfHwgRnVuY3Rpb24oJ3JldHVybiB0aGlzJykoKTtcblxubW9kdWxlLmV4cG9ydHMgPSByb290O1xuIiwidmFyIGJhc2VHZXRUYWcgPSByZXF1aXJlKCcuL19iYXNlR2V0VGFnJyksXG4gICAgaXNPYmplY3RMaWtlID0gcmVxdWlyZSgnLi9pc09iamVjdExpa2UnKTtcblxuLyoqIGBPYmplY3QjdG9TdHJpbmdgIHJlc3VsdCByZWZlcmVuY2VzLiAqL1xudmFyIG51bWJlclRhZyA9ICdbb2JqZWN0IE51bWJlcl0nO1xuXG4vKipcbiAqIENoZWNrcyBpZiBgdmFsdWVgIGlzIGNsYXNzaWZpZWQgYXMgYSBgTnVtYmVyYCBwcmltaXRpdmUgb3Igb2JqZWN0LlxuICpcbiAqICoqTm90ZToqKiBUbyBleGNsdWRlIGBJbmZpbml0eWAsIGAtSW5maW5pdHlgLCBhbmQgYE5hTmAsIHdoaWNoIGFyZVxuICogY2xhc3NpZmllZCBhcyBudW1iZXJzLCB1c2UgdGhlIGBfLmlzRmluaXRlYCBtZXRob2QuXG4gKlxuICogQHN0YXRpY1xuICogQG1lbWJlck9mIF9cbiAqIEBzaW5jZSAwLjEuMFxuICogQGNhdGVnb3J5IExhbmdcbiAqIEBwYXJhbSB7Kn0gdmFsdWUgVGhlIHZhbHVlIHRvIGNoZWNrLlxuICogQHJldHVybnMge2Jvb2xlYW59IFJldHVybnMgYHRydWVgIGlmIGB2YWx1ZWAgaXMgYSBudW1iZXIsIGVsc2UgYGZhbHNlYC5cbiAqIEBleGFtcGxlXG4gKlxuICogXy5pc051bWJlcigzKTtcbiAqIC8vID0+IHRydWVcbiAqXG4gKiBfLmlzTnVtYmVyKE51bWJlci5NSU5fVkFMVUUpO1xuICogLy8gPT4gdHJ1ZVxuICpcbiAqIF8uaXNOdW1iZXIoSW5maW5pdHkpO1xuICogLy8gPT4gdHJ1ZVxuICpcbiAqIF8uaXNOdW1iZXIoJzMnKTtcbiAqIC8vID0+IGZhbHNlXG4gKi9cbmZ1bmN0aW9uIGlzTnVtYmVyKHZhbHVlKSB7XG4gIHJldHVybiB0eXBlb2YgdmFsdWUgPT0gJ251bWJlcicgfHxcbiAgICAoaXNPYmplY3RMaWtlKHZhbHVlKSAmJiBiYXNlR2V0VGFnKHZhbHVlKSA9PSBudW1iZXJUYWcpO1xufVxuXG5tb2R1bGUuZXhwb3J0cyA9IGlzTnVtYmVyO1xuIiwiLyoqXG4gKiBDaGVja3MgaWYgYHZhbHVlYCBpcyBvYmplY3QtbGlrZS4gQSB2YWx1ZSBpcyBvYmplY3QtbGlrZSBpZiBpdCdzIG5vdCBgbnVsbGBcbiAqIGFuZCBoYXMgYSBgdHlwZW9mYCByZXN1bHQgb2YgXCJvYmplY3RcIi5cbiAqXG4gKiBAc3RhdGljXG4gKiBAbWVtYmVyT2YgX1xuICogQHNpbmNlIDQuMC4wXG4gKiBAY2F0ZWdvcnkgTGFuZ1xuICogQHBhcmFtIHsqfSB2YWx1ZSBUaGUgdmFsdWUgdG8gY2hlY2suXG4gKiBAcmV0dXJucyB7Ym9vbGVhbn0gUmV0dXJucyBgdHJ1ZWAgaWYgYHZhbHVlYCBpcyBvYmplY3QtbGlrZSwgZWxzZSBgZmFsc2VgLlxuICogQGV4YW1wbGVcbiAqXG4gKiBfLmlzT2JqZWN0TGlrZSh7fSk7XG4gKiAvLyA9PiB0cnVlXG4gKlxuICogXy5pc09iamVjdExpa2UoWzEsIDIsIDNdKTtcbiAqIC8vID0+IHRydWVcbiAqXG4gKiBfLmlzT2JqZWN0TGlrZShfLm5vb3ApO1xuICogLy8gPT4gZmFsc2VcbiAqXG4gKiBfLmlzT2JqZWN0TGlrZShudWxsKTtcbiAqIC8vID0+IGZhbHNlXG4gKi9cbmZ1bmN0aW9uIGlzT2JqZWN0TGlrZSh2YWx1ZSkge1xuICByZXR1cm4gdmFsdWUgIT0gbnVsbCAmJiB0eXBlb2YgdmFsdWUgPT0gJ29iamVjdCc7XG59XG5cbm1vZHVsZS5leHBvcnRzID0gaXNPYmplY3RMaWtlO1xuIiwiLyoqXG4gKiBDaGVja3MgaWYgYHZhbHVlYCBpcyBgdW5kZWZpbmVkYC5cbiAqXG4gKiBAc3RhdGljXG4gKiBAc2luY2UgMC4xLjBcbiAqIEBtZW1iZXJPZiBfXG4gKiBAY2F0ZWdvcnkgTGFuZ1xuICogQHBhcmFtIHsqfSB2YWx1ZSBUaGUgdmFsdWUgdG8gY2hlY2suXG4gKiBAcmV0dXJucyB7Ym9vbGVhbn0gUmV0dXJucyBgdHJ1ZWAgaWYgYHZhbHVlYCBpcyBgdW5kZWZpbmVkYCwgZWxzZSBgZmFsc2VgLlxuICogQGV4YW1wbGVcbiAqXG4gKiBfLmlzVW5kZWZpbmVkKHZvaWQgMCk7XG4gKiAvLyA9PiB0cnVlXG4gKlxuICogXy5pc1VuZGVmaW5lZChudWxsKTtcbiAqIC8vID0+IGZhbHNlXG4gKi9cbmZ1bmN0aW9uIGlzVW5kZWZpbmVkKHZhbHVlKSB7XG4gIHJldHVybiB2YWx1ZSA9PT0gdW5kZWZpbmVkO1xufVxuXG5tb2R1bGUuZXhwb3J0cyA9IGlzVW5kZWZpbmVkO1xuIiwiXCJ1c2Ugc3RyaWN0XCI7XG5cbnZhciBpc1VuZGVmaW5lZCA9IHJlcXVpcmUoXCJsb2Rhc2gvaXNVbmRlZmluZWRcIik7XG52YXIgaXNOdW1iZXIgPSByZXF1aXJlKFwibG9kYXNoL2lzTnVtYmVyXCIpO1xuLyoqXG4gKiBBIGZ1bmN0aW9uIHRoYXQgb25seSByZXR1cm5zIGFuIGVtcHR5IHRoYXQgY2FuIGJlIHVzZWQgYXMgYW4gZW1wdHkgbWFya2VyXG4gKlxuICogQHJldHVybnMge0FycmF5fSBBIGxpc3Qgb2YgZW1wdHkgbWFya3MuXG4gKi9cbnZhciBlbXB0eU1hcmtlciA9IGZ1bmN0aW9uIGVtcHR5TWFya2VyKCkge1xuICAgIHJldHVybiBbXTtcbn07XG4vKipcbiAqIENvbnN0cnVjdCB0aGUgQXNzZXNzbWVudFJlc3VsdCB2YWx1ZSBvYmplY3QuXG4gKlxuICogQHBhcmFtIHtPYmplY3R9IFt2YWx1ZXNdIFRoZSB2YWx1ZXMgZm9yIHRoaXMgYXNzZXNzbWVudCByZXN1bHQuXG4gKlxuICogQGNvbnN0cnVjdG9yXG4gKi9cbnZhciBBc3Nlc3NtZW50UmVzdWx0ID0gZnVuY3Rpb24gQXNzZXNzbWVudFJlc3VsdCh2YWx1ZXMpIHtcbiAgICB0aGlzLl9oYXNTY29yZSA9IGZhbHNlO1xuICAgIHRoaXMuX2lkZW50aWZpZXIgPSBcIlwiO1xuICAgIHRoaXMuX2hhc01hcmtzID0gZmFsc2U7XG4gICAgdGhpcy5fbWFya2VyID0gZW1wdHlNYXJrZXI7XG4gICAgdGhpcy5zY29yZSA9IDA7XG4gICAgdGhpcy50ZXh0ID0gXCJcIjtcbiAgICBpZiAoaXNVbmRlZmluZWQodmFsdWVzKSkge1xuICAgICAgICB2YWx1ZXMgPSB7fTtcbiAgICB9XG4gICAgaWYgKCFpc1VuZGVmaW5lZCh2YWx1ZXMuc2NvcmUpKSB7XG4gICAgICAgIHRoaXMuc2V0U2NvcmUodmFsdWVzLnNjb3JlKTtcbiAgICB9XG4gICAgaWYgKCFpc1VuZGVmaW5lZCh2YWx1ZXMudGV4dCkpIHtcbiAgICAgICAgdGhpcy5zZXRUZXh0KHZhbHVlcy50ZXh0KTtcbiAgICB9XG59O1xuLyoqXG4gKiBDaGVjayBpZiBhIHNjb3JlIGlzIGF2YWlsYWJsZS5cbiAqIEByZXR1cm5zIHtib29sZWFufSBXaGV0aGVyIG9yIG5vdCBhIHNjb3JlIGlzIGF2YWlsYWJsZS5cbiAqL1xuQXNzZXNzbWVudFJlc3VsdC5wcm90b3R5cGUuaGFzU2NvcmUgPSBmdW5jdGlvbiAoKSB7XG4gICAgcmV0dXJuIHRoaXMuX2hhc1Njb3JlO1xufTtcbi8qKlxuICogR2V0IHRoZSBhdmFpbGFibGUgc2NvcmVcbiAqIEByZXR1cm5zIHtudW1iZXJ9IFRoZSBzY29yZSBhc3NvY2lhdGVkIHdpdGggdGhlIEFzc2Vzc21lbnRSZXN1bHQuXG4gKi9cbkFzc2Vzc21lbnRSZXN1bHQucHJvdG90eXBlLmdldFNjb3JlID0gZnVuY3Rpb24gKCkge1xuICAgIHJldHVybiB0aGlzLnNjb3JlO1xufTtcbi8qKlxuICogU2V0IHRoZSBzY29yZSBmb3IgdGhlIGFzc2Vzc21lbnQuXG4gKiBAcGFyYW0ge251bWJlcn0gc2NvcmUgVGhlIHNjb3JlIHRvIGJlIHVzZWQgZm9yIHRoZSBzY29yZSBwcm9wZXJ0eVxuICogQHJldHVybnMge3ZvaWR9XG4gKi9cbkFzc2Vzc21lbnRSZXN1bHQucHJvdG90eXBlLnNldFNjb3JlID0gZnVuY3Rpb24gKHNjb3JlKSB7XG4gICAgaWYgKGlzTnVtYmVyKHNjb3JlKSkge1xuICAgICAgICB0aGlzLnNjb3JlID0gc2NvcmU7XG4gICAgICAgIHRoaXMuX2hhc1Njb3JlID0gdHJ1ZTtcbiAgICB9XG59O1xuLyoqXG4gKiBDaGVjayBpZiBhIHRleHQgaXMgYXZhaWxhYmxlLlxuICogQHJldHVybnMge2Jvb2xlYW59IFdoZXRoZXIgb3Igbm90IGEgdGV4dCBpcyBhdmFpbGFibGUuXG4gKi9cbkFzc2Vzc21lbnRSZXN1bHQucHJvdG90eXBlLmhhc1RleHQgPSBmdW5jdGlvbiAoKSB7XG4gICAgcmV0dXJuIHRoaXMudGV4dCAhPT0gXCJcIjtcbn07XG4vKipcbiAqIEdldCB0aGUgYXZhaWxhYmxlIHRleHRcbiAqIEByZXR1cm5zIHtzdHJpbmd9IFRoZSB0ZXh0IGFzc29jaWF0ZWQgd2l0aCB0aGUgQXNzZXNzbWVudFJlc3VsdC5cbiAqL1xuQXNzZXNzbWVudFJlc3VsdC5wcm90b3R5cGUuZ2V0VGV4dCA9IGZ1bmN0aW9uICgpIHtcbiAgICByZXR1cm4gdGhpcy50ZXh0O1xufTtcbi8qKlxuICogU2V0IHRoZSB0ZXh0IGZvciB0aGUgYXNzZXNzbWVudC5cbiAqIEBwYXJhbSB7c3RyaW5nfSB0ZXh0IFRoZSB0ZXh0IHRvIGJlIHVzZWQgZm9yIHRoZSB0ZXh0IHByb3BlcnR5XG4gKiBAcmV0dXJucyB7dm9pZH1cbiAqL1xuQXNzZXNzbWVudFJlc3VsdC5wcm90b3R5cGUuc2V0VGV4dCA9IGZ1bmN0aW9uICh0ZXh0KSB7XG4gICAgaWYgKGlzVW5kZWZpbmVkKHRleHQpKSB7XG4gICAgICAgIHRleHQgPSBcIlwiO1xuICAgIH1cbiAgICB0aGlzLnRleHQgPSB0ZXh0O1xufTtcbi8qKlxuICogU2V0cyB0aGUgaWRlbnRpZmllclxuICpcbiAqIEBwYXJhbSB7c3RyaW5nfSBpZGVudGlmaWVyIEFuIGFscGhhbnVtZXJpYyBpZGVudGlmaWVyIGZvciB0aGlzIHJlc3VsdC5cbiAqIEByZXR1cm5zIHt2b2lkfVxuICovXG5Bc3Nlc3NtZW50UmVzdWx0LnByb3RvdHlwZS5zZXRJZGVudGlmaWVyID0gZnVuY3Rpb24gKGlkZW50aWZpZXIpIHtcbiAgICB0aGlzLl9pZGVudGlmaWVyID0gaWRlbnRpZmllcjtcbn07XG4vKipcbiAqIEdldHMgdGhlIGlkZW50aWZpZXJcbiAqXG4gKiBAcmV0dXJucyB7c3RyaW5nfSBBbiBhbHBoYW51bWVyaWMgaWRlbnRpZmllciBmb3IgdGhpcyByZXN1bHQuXG4gKi9cbkFzc2Vzc21lbnRSZXN1bHQucHJvdG90eXBlLmdldElkZW50aWZpZXIgPSBmdW5jdGlvbiAoKSB7XG4gICAgcmV0dXJuIHRoaXMuX2lkZW50aWZpZXI7XG59O1xuLyoqXG4gKiBTZXRzIHRoZSBtYXJrZXIsIGEgcHVyZSBmdW5jdGlvbiB0aGF0IGNhbiByZXR1cm4gdGhlIG1hcmtzIGZvciBhIGdpdmVuIFBhcGVyXG4gKlxuICogQHBhcmFtIHtGdW5jdGlvbn0gbWFya2VyIFRoZSBtYXJrZXIgdG8gc2V0LlxuICogQHJldHVybnMge3ZvaWR9XG4gKi9cbkFzc2Vzc21lbnRSZXN1bHQucHJvdG90eXBlLnNldE1hcmtlciA9IGZ1bmN0aW9uIChtYXJrZXIpIHtcbiAgICB0aGlzLl9tYXJrZXIgPSBtYXJrZXI7XG59O1xuLyoqXG4gKiBSZXR1cm5zIHdoZXRoZXIgb3Igbm90IHRoaXMgcmVzdWx0IGhhcyBhIG1hcmtlciB0aGF0IGNhbiBiZSB1c2VkIHRvIG1hcmsgZm9yIGEgZ2l2ZW4gUGFwZXJcbiAqXG4gKiBAcmV0dXJucyB7Ym9vbGVhbn0gV2hldGhlciBvciB0aGlzIHJlc3VsdCBoYXMgYSBtYXJrZXIuXG4gKi9cbkFzc2Vzc21lbnRSZXN1bHQucHJvdG90eXBlLmhhc01hcmtlciA9IGZ1bmN0aW9uICgpIHtcbiAgICByZXR1cm4gdGhpcy5faGFzTWFya3MgJiYgdGhpcy5fbWFya2VyICE9PSBlbXB0eU1hcmtlcjtcbn07XG4vKipcbiAqIEdldHMgdGhlIG1hcmtlciwgYSBwdXJlIGZ1bmN0aW9uIHRoYXQgY2FuIHJldHVybiB0aGUgbWFya3MgZm9yIGEgZ2l2ZW4gUGFwZXJcbiAqXG4gKiBAcmV0dXJucyB7RnVuY3Rpb259IFRoZSBtYXJrZXIuXG4gKi9cbkFzc2Vzc21lbnRSZXN1bHQucHJvdG90eXBlLmdldE1hcmtlciA9IGZ1bmN0aW9uICgpIHtcbiAgICByZXR1cm4gdGhpcy5fbWFya2VyO1xufTtcbi8qKlxuICogU2V0cyB0aGUgdmFsdWUgb2YgX2hhc01hcmtzIHRvIGRldGVybWluZSBpZiB0aGVyZSBpcyBzb21ldGhpbmcgdG8gbWFyay5cbiAqXG4gKiBAcGFyYW0ge2Jvb2xlYW59IGhhc01hcmtzIElzIHRoZXJlIHNvbWV0aGluZyB0byBtYXJrLlxuICogQHJldHVybnMge3ZvaWR9XG4gKi9cbkFzc2Vzc21lbnRSZXN1bHQucHJvdG90eXBlLnNldEhhc01hcmtzID0gZnVuY3Rpb24gKGhhc01hcmtzKSB7XG4gICAgdGhpcy5faGFzTWFya3MgPSBoYXNNYXJrcztcbn07XG4vKipcbiAqIFJldHVybnMgdGhlIHZhbHVlIG9mIF9oYXNNYXJrcyB0byBkZXRlcm1pbmUgaWYgdGhlcmUgaXMgc29tZXRoaW5nIHRvIG1hcmsuXG4gKlxuICogQHJldHVybnMge2Jvb2xlYW59IElzIHRoZXJlIHNvbWV0aGluZyB0byBtYXJrLlxuICovXG5Bc3Nlc3NtZW50UmVzdWx0LnByb3RvdHlwZS5oYXNNYXJrcyA9IGZ1bmN0aW9uICgpIHtcbiAgICByZXR1cm4gdGhpcy5faGFzTWFya3M7XG59O1xubW9kdWxlLmV4cG9ydHMgPSBBc3Nlc3NtZW50UmVzdWx0O1xuLy8jIHNvdXJjZU1hcHBpbmdVUkw9QXNzZXNzbWVudFJlc3VsdC5qcy5tYXBcbi8vIyBzb3VyY2VNYXBwaW5nVVJMPUFzc2Vzc21lbnRSZXN1bHQuanMubWFwXG4iXX0=
