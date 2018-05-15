(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
'use strict';

/* global YoastSEO */
/* global wpseoLocalL10n */
(function () {
	'use strict';

	var AssessmentResult = require('yoastseo/js/values/AssessmentResult');
	var escapeRegExp = require("lodash/escapeRegExp");

	/**
  * Adds the plugin for videoSEO to the YoastSEO Analyzer.
  */
	var YoastlocalSEOplugin = function YoastlocalSEOplugin() {
		YoastSEO.app.registerPlugin('YoastLocalSEO', { 'status': 'ready' });

		YoastSEO.app.registerAssessment('localTitle', { getResult: this.localTitle.bind(this) }, 'YoastLocalSEO');

		YoastSEO.app.registerAssessment('localUrl', { getResult: this.localUrl.bind(this) }, 'YoastLocalSEO');

		YoastSEO.app.registerAssessment('localSchema', { getResult: this.localSchema.bind(this) }, 'YoastLocalSEO');

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

			// When no results, check for the location in h1 or h2 tags in the content.
			if (0 == matches) {
				var headings = new RegExp('<h(1|2)>.*?' + wpseoLocalL10n.location + '.*?<\/h(1|2)>', 'ig');
				matches = paper.getText().match(headings) || 0;
				result = this.scoreLocalCityInHeadings(matches);
			}

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
  * Scores the url based on the matches of the location's city in headings.
  *
  * @param {array} matches The matches of the location in the url
  * @returns {{score: number, text: *}}
  */
	YoastlocalSEOplugin.prototype.scoreLocalCityInHeadings = function (matches) {
		if (matches.length > 0) {
			return {
				score: 9,
				text: wpseoLocalL10n.heading_location
			};
		}
		return {
			score: 4,
			text: wpseoLocalL10n.heading_no_location
		};
	};

	/**
  * Runs an assessment for scoring the location in the URL.
  * @param {object} paper The paper to run this assessment on
  * @param {object} researcher The researcher used for the assessment
  * @param {object} i18n The i18n-object used for parsing translations
  * @returns {object} an assessmentresult with the score and formatted text.
  */
	YoastlocalSEOplugin.prototype.localUrl = function (paper, researcher, i18n) {
		var assessmentResult = new AssessmentResult();
		if (wpseoLocalL10n.location !== '') {
			var location = wpseoLocalL10n.location;
			location = location.replace("'", "").replace(/\s/ig, "-");
			location = escapeRegExp(location);
			var business_city = new RegExp(location, 'ig');
			var matches = paper.getUrl().match(business_city) || 0;
			var result = this.scoreLocalUrl(matches);
			assessmentResult.setScore(result.score);
			assessmentResult.setText(result.text);
		}
		return assessmentResult;
	};

	/**
  * Scores the url based on the matches of the location.
  * @param {array} matches The matches of the location in the url
  * @returns {{score: number, text: *}}
  */
	YoastlocalSEOplugin.prototype.scoreLocalUrl = function (matches) {
		if (matches.length > 0) {
			return {
				score: 9,
				text: wpseoLocalL10n.url_location
			};
		}
		return {
			score: 4,
			text: wpseoLocalL10n.url_no_location
		};
	};

	/**
  * Runs an assessment for scoring the location in the URL.
  * @param {object} paper The paper to run this assessment on
  * @param {object} researcher The researcher used for the assessment
  * @param {object} i18n The i18n-object used for parsing translations
  * @returns {object} an assessmentresult with the score and formatted text.
  */
	YoastlocalSEOplugin.prototype.localSchema = function (paper, researcher, i18n) {
		var assessmentResult = new AssessmentResult();
		var schema = new RegExp('class=["\']wpseo-location["\'] itemscope', 'ig');
		var matches = paper.getText().match(schema) || 0;
		var result = this.scoreLocalSchema(matches);

		assessmentResult.setScore(result.score);
		assessmentResult.setText(result.text);

		return assessmentResult;
	};

	/**
  * Scores the url based on the matches of the location.
  * @param {array} matches The matches of the location in the url
  * @returns {{score: number, text: *}}
  */
	YoastlocalSEOplugin.prototype.scoreLocalSchema = function (matches) {
		if (matches.length > 0) {
			return {
				score: 9,
				text: wpseoLocalL10n.address_schema
			};
		}
		return {
			score: 4,
			text: wpseoLocalL10n.no_address_schema
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

},{"lodash/escapeRegExp":10,"yoastseo/js/values/AssessmentResult":17}],2:[function(require,module,exports){
var root = require('./_root');

/** Built-in value references. */
var Symbol = root.Symbol;

module.exports = Symbol;

},{"./_root":9}],3:[function(require,module,exports){
/**
 * A specialized version of `_.map` for arrays without support for iteratee
 * shorthands.
 *
 * @private
 * @param {Array} [array] The array to iterate over.
 * @param {Function} iteratee The function invoked per iteration.
 * @returns {Array} Returns the new mapped array.
 */
function arrayMap(array, iteratee) {
  var index = -1,
      length = array == null ? 0 : array.length,
      result = Array(length);

  while (++index < length) {
    result[index] = iteratee(array[index], index, array);
  }
  return result;
}

module.exports = arrayMap;

},{}],4:[function(require,module,exports){
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

},{"./_Symbol":2,"./_getRawTag":7,"./_objectToString":8}],5:[function(require,module,exports){
var Symbol = require('./_Symbol'),
    arrayMap = require('./_arrayMap'),
    isArray = require('./isArray'),
    isSymbol = require('./isSymbol');

/** Used as references for various `Number` constants. */
var INFINITY = 1 / 0;

/** Used to convert symbols to primitives and strings. */
var symbolProto = Symbol ? Symbol.prototype : undefined,
    symbolToString = symbolProto ? symbolProto.toString : undefined;

/**
 * The base implementation of `_.toString` which doesn't convert nullish
 * values to empty strings.
 *
 * @private
 * @param {*} value The value to process.
 * @returns {string} Returns the string.
 */
function baseToString(value) {
  // Exit early for strings to avoid a performance hit in some environments.
  if (typeof value == 'string') {
    return value;
  }
  if (isArray(value)) {
    // Recursively convert values (susceptible to call stack limits).
    return arrayMap(value, baseToString) + '';
  }
  if (isSymbol(value)) {
    return symbolToString ? symbolToString.call(value) : '';
  }
  var result = (value + '');
  return (result == '0' && (1 / value) == -INFINITY) ? '-0' : result;
}

module.exports = baseToString;

},{"./_Symbol":2,"./_arrayMap":3,"./isArray":11,"./isSymbol":14}],6:[function(require,module,exports){
(function (global){
/** Detect free variable `global` from Node.js. */
var freeGlobal = typeof global == 'object' && global && global.Object === Object && global;

module.exports = freeGlobal;

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})

},{}],7:[function(require,module,exports){
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

},{"./_Symbol":2}],8:[function(require,module,exports){
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

},{}],9:[function(require,module,exports){
var freeGlobal = require('./_freeGlobal');

/** Detect free variable `self`. */
var freeSelf = typeof self == 'object' && self && self.Object === Object && self;

/** Used as a reference to the global object. */
var root = freeGlobal || freeSelf || Function('return this')();

module.exports = root;

},{"./_freeGlobal":6}],10:[function(require,module,exports){
var toString = require('./toString');

/**
 * Used to match `RegExp`
 * [syntax characters](http://ecma-international.org/ecma-262/7.0/#sec-patterns).
 */
var reRegExpChar = /[\\^$.*+?()[\]{}|]/g,
    reHasRegExpChar = RegExp(reRegExpChar.source);

/**
 * Escapes the `RegExp` special characters "^", "$", "\", ".", "*", "+",
 * "?", "(", ")", "[", "]", "{", "}", and "|" in `string`.
 *
 * @static
 * @memberOf _
 * @since 3.0.0
 * @category String
 * @param {string} [string=''] The string to escape.
 * @returns {string} Returns the escaped string.
 * @example
 *
 * _.escapeRegExp('[lodash](https://lodash.com/)');
 * // => '\[lodash\]\(https://lodash\.com/\)'
 */
function escapeRegExp(string) {
  string = toString(string);
  return (string && reHasRegExpChar.test(string))
    ? string.replace(reRegExpChar, '\\$&')
    : string;
}

module.exports = escapeRegExp;

},{"./toString":16}],11:[function(require,module,exports){
/**
 * Checks if `value` is classified as an `Array` object.
 *
 * @static
 * @memberOf _
 * @since 0.1.0
 * @category Lang
 * @param {*} value The value to check.
 * @returns {boolean} Returns `true` if `value` is an array, else `false`.
 * @example
 *
 * _.isArray([1, 2, 3]);
 * // => true
 *
 * _.isArray(document.body.children);
 * // => false
 *
 * _.isArray('abc');
 * // => false
 *
 * _.isArray(_.noop);
 * // => false
 */
var isArray = Array.isArray;

module.exports = isArray;

},{}],12:[function(require,module,exports){
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

},{"./_baseGetTag":4,"./isObjectLike":13}],13:[function(require,module,exports){
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

},{}],14:[function(require,module,exports){
var baseGetTag = require('./_baseGetTag'),
    isObjectLike = require('./isObjectLike');

/** `Object#toString` result references. */
var symbolTag = '[object Symbol]';

/**
 * Checks if `value` is classified as a `Symbol` primitive or object.
 *
 * @static
 * @memberOf _
 * @since 4.0.0
 * @category Lang
 * @param {*} value The value to check.
 * @returns {boolean} Returns `true` if `value` is a symbol, else `false`.
 * @example
 *
 * _.isSymbol(Symbol.iterator);
 * // => true
 *
 * _.isSymbol('abc');
 * // => false
 */
function isSymbol(value) {
  return typeof value == 'symbol' ||
    (isObjectLike(value) && baseGetTag(value) == symbolTag);
}

module.exports = isSymbol;

},{"./_baseGetTag":4,"./isObjectLike":13}],15:[function(require,module,exports){
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

},{}],16:[function(require,module,exports){
var baseToString = require('./_baseToString');

/**
 * Converts `value` to a string. An empty string is returned for `null`
 * and `undefined` values. The sign of `-0` is preserved.
 *
 * @static
 * @memberOf _
 * @since 4.0.0
 * @category Lang
 * @param {*} value The value to convert.
 * @returns {string} Returns the converted string.
 * @example
 *
 * _.toString(null);
 * // => ''
 *
 * _.toString(-0);
 * // => '-0'
 *
 * _.toString([1, 2, 3]);
 * // => '1,2,3'
 */
function toString(value) {
  return value == null ? '' : baseToString(value);
}

module.exports = toString;

},{"./_baseToString":5}],17:[function(require,module,exports){
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



},{"lodash/isNumber":12,"lodash/isUndefined":15}]},{},[1])
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJqcy9zcmMvd3Atc2VvLWxvY2FsLWFuYWx5c2lzLWxvY2F0aW9ucy5qcyIsIm5vZGVfbW9kdWxlcy9sb2Rhc2gvX1N5bWJvbC5qcyIsIm5vZGVfbW9kdWxlcy9sb2Rhc2gvX2FycmF5TWFwLmpzIiwibm9kZV9tb2R1bGVzL2xvZGFzaC9fYmFzZUdldFRhZy5qcyIsIm5vZGVfbW9kdWxlcy9sb2Rhc2gvX2Jhc2VUb1N0cmluZy5qcyIsIm5vZGVfbW9kdWxlcy9sb2Rhc2gvX2ZyZWVHbG9iYWwuanMiLCJub2RlX21vZHVsZXMvbG9kYXNoL19nZXRSYXdUYWcuanMiLCJub2RlX21vZHVsZXMvbG9kYXNoL19vYmplY3RUb1N0cmluZy5qcyIsIm5vZGVfbW9kdWxlcy9sb2Rhc2gvX3Jvb3QuanMiLCJub2RlX21vZHVsZXMvbG9kYXNoL2VzY2FwZVJlZ0V4cC5qcyIsIm5vZGVfbW9kdWxlcy9sb2Rhc2gvaXNBcnJheS5qcyIsIm5vZGVfbW9kdWxlcy9sb2Rhc2gvaXNOdW1iZXIuanMiLCJub2RlX21vZHVsZXMvbG9kYXNoL2lzT2JqZWN0TGlrZS5qcyIsIm5vZGVfbW9kdWxlcy9sb2Rhc2gvaXNTeW1ib2wuanMiLCJub2RlX21vZHVsZXMvbG9kYXNoL2lzVW5kZWZpbmVkLmpzIiwibm9kZV9tb2R1bGVzL2xvZGFzaC90b1N0cmluZy5qcyIsIm5vZGVfbW9kdWxlcy95b2FzdHNlby9qcy92YWx1ZXMvQXNzZXNzbWVudFJlc3VsdC5qcyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7O0FDQUE7QUFDQTtBQUNDLGFBQVc7QUFDWDs7QUFFQSxLQUFJLG1CQUFtQixRQUFTLHFDQUFULENBQXZCO0FBQ0EsS0FBSSxlQUFlLFFBQVMscUJBQVQsQ0FBbkI7O0FBRUE7OztBQUdBLEtBQUksc0JBQXNCLFNBQXRCLG1CQUFzQixHQUFVO0FBQ25DLFdBQVMsR0FBVCxDQUFhLGNBQWIsQ0FBNkIsZUFB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QTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOzs7QUNyQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7OztBQ0pBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDOUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDdEJBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ1RBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNoQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQzFCQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDdENBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUM3QkE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQzdCQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ3RCQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQzVCQTs7QUFFQSxJQUFJLGNBQWMsUUFBUSxvQkFBUixDQUFsQjtBQUNBLElBQUksV0FBVyxRQUFRLGlCQUFSLENBQWY7QUFDQTs7Ozs7QUFLQSxJQUFJLGNBQWMsU0FBUyxXQUFULEdBQXVCO0FBQ3JDLFdBQU8sRUFBUDtBQUNILENBRkQ7QUFH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Iiwic291cmNlc0NvbnRlbnQiOlsiKGZ1bmN0aW9uIGUodCxuLHIpe2Z1bmN0aW9uIHMobyx1KXtpZighbltvXSl7aWYoIXRbb10pe3ZhciBhPXR5cGVvZiByZXF1aXJlPT1cImZ1bmN0aW9uXCImJnJlcXVpcmU7aWYoIXUmJmEpcmV0dXJuIGEobywhMCk7aWYoaSlyZXR1cm4gaShvLCEwKTt2YXIgZj1uZXcgRXJyb3IoXCJDYW5ub3QgZmluZCBtb2R1bGUgJ1wiK28rXCInXCIpO3Rocm93IGYuY29kZT1cIk1PRFVMRV9OT1RfRk9VTkRcIixmfXZhciBsPW5bb109e2V4cG9ydHM6e319O3Rbb11bMF0uY2FsbChsLmV4cG9ydHMsZnVuY3Rpb24oZSl7dmFyIG49dFtvXVsxXVtlXTtyZXR1cm4gcyhuP246ZSl9LGwsbC5leHBvcnRzLGUsdCxuLHIpfXJldHVybiBuW29dLmV4cG9ydHN9dmFyIGk9dHlwZW9mIHJlcXVpcmU9PVwiZnVuY3Rpb25cIiYmcmVxdWlyZTtmb3IodmFyIG89MDtvPHIubGVuZ3RoO28rKylzKHJbb10pO3JldHVybiBzfSkiLCIvKiBnbG9iYWwgWW9hc3RTRU8gKi9cbi8qIGdsb2JhbCB3cHNlb0xvY2FsTDEwbiAqL1xuKGZ1bmN0aW9uKCkge1xuXHQndXNlIHN0cmljdCc7XG5cblx0dmFyIEFzc2Vzc21lbnRSZXN1bHQgPSByZXF1aXJlKCAneW9hc3RzZW8vanMvdmFsdWVzL0Fzc2Vzc21lbnRSZXN1bHQnICk7XG5cdHZhciBlc2NhcGVSZWdFeHAgPSByZXF1aXJlKCBcImxvZGFzaC9lc2NhcGVSZWdFeHBcIiApO1xuXG5cdC8qKlxuXHQgKiBBZGRzIHRoZSBwbHVnaW4gZm9yIHZpZGVvU0VPIHRvIHRoZSBZb2FzdFNFTyBBbmFseXplci5cblx0ICovXG5cdHZhciBZb2FzdGxvY2FsU0VPcGx1Z2luID0gZnVuY3Rpb24oKXtcblx0XHRZb2FzdFNFTy5hcHAucmVnaXN0ZXJQbHVnaW4oICdZb2FzdExvY2FsU0VPJywgeyAnc3RhdHVzJzogJ3JlYWR5JyB9KTtcblxuXHRcdFlvYXN0U0VPLmFwcC5yZWdpc3RlckFzc2Vzc21lbnQoICdsb2NhbFRpdGxlJywgeyBnZXRSZXN1bHQ6ICB0aGlzLmxvY2FsVGl0bGUuYmluZCggdGhpcyApIH0sICdZb2FzdExvY2FsU0VPJyApO1xuXG5cdFx0WW9hc3RTRU8uYXBwLnJlZ2lzdGVyQXNzZXNzbWVudCggJ2xvY2FsVXJsJywgeyBnZXRSZXN1bHQ6ICB0aGlzLmxvY2FsVXJsLmJpbmQoIHRoaXMgKSB9LCAnWW9hc3RMb2NhbFNFTycgKTtcblxuXHRcdFlvYXN0U0VPLmFwcC5yZWdpc3RlckFzc2Vzc21lbnQoICdsb2NhbFNjaGVtYScsIHsgZ2V0UmVzdWx0OiAgdGhpcy5sb2NhbFNjaGVtYS5iaW5kKCB0aGlzICkgfSwgJ1lvYXN0TG9jYWxTRU8nICk7XG5cblx0XHR0aGlzLmFkZENhbGxiYWNrKCk7XG5cdH07XG5cblx0LyoqXG5cdCAqXG5cdCAqIEBwYXJhbSB7b2JqZWN0fSBwYXBlciBUaGUgcGFwZXIgdG8gcnVuIHRoaXMgYXNzZXNzbWVudCBvblxuXHQgKiBAcGFyYW0ge29iamVjdH0gcmVzZWFyY2hlciBUaGUgcmVzZWFyY2hlciB1c2VkIGZvciB0aGUgYXNzZXNzbWVudFxuXHQgKiBAcGFyYW0ge29iamVjdH0gaTE4biBUaGUgaTE4bi1vYmplY3QgdXNlZCBmb3IgcGFyc2luZyB0cmFuc2xhdGlvbnNcblx0ICogQHJldHVybnMge29iamVjdH0gYW4gYXNzZXNzbWVudHJlc3VsdCB3aXRoIHRoZSBzY29yZSBhbmQgZm9ybWF0dGVkIHRleHQuXG5cdCAqL1xuXHRZb2FzdGxvY2FsU0VPcGx1Z2luLnByb3RvdHlwZS5sb2NhbFRpdGxlID0gZnVuY3Rpb24oIHBhcGVyLCByZXNlYXJjaGVyLCBpMThuICkge1xuXHRcdHZhciBhc3Nlc3NtZW50UmVzdWx0ID0gbmV3IEFzc2Vzc21lbnRSZXN1bHQoKTtcblx0XHRpZiggd3BzZW9Mb2NhbEwxMG4ubG9jYXRpb24gIT09ICcnICkge1xuXHRcdFx0dmFyIGJ1c2luZXNzX2NpdHkgPSBuZXcgUmVnRXhwKCB3cHNlb0xvY2FsTDEwbi5sb2NhdGlvbiwgJ2lnJyk7XG5cdFx0XHR2YXIgbWF0Y2hlcyA9IHBhcGVyLmdldFRpdGxlKCkubWF0Y2goIGJ1c2luZXNzX2NpdHkgKSB8fCAwO1xuXHRcdFx0dmFyIHJlc3VsdCA9IHRoaXMubG9jYWxUaXRsZVNjb3JlKCBtYXRjaGVzICk7XG5cblx0XHRcdC8vIFdoZW4gbm8gcmVzdWx0cywgY2hlY2sgZm9yIHRoZSBsb2NhdGlvbiBpbiBoMSBvciBoMiB0YWdzIGluIHRoZSBjb250ZW50LlxuXHRcdFx0aWYoIDAgPT0gbWF0Y2hlcyApIHtcblx0XHRcdFx0dmFyIGhlYWRpbmdzID0gbmV3IFJlZ0V4cCggJzxoKDF8Mik+Lio/JyArIHdwc2VvTG9jYWxMMTBuLmxvY2F0aW9uICsgJy4qPzxcXC9oKDF8Mik+JywgJ2lnJyApO1xuXHRcdFx0XHRtYXRjaGVzID0gcGFwZXIuZ2V0VGV4dCgpLm1hdGNoKCBoZWFkaW5ncyApIHx8IDA7XG5cdFx0XHRcdHJlc3VsdCA9IHRoaXMuc2NvcmVMb2NhbENpdHlJbkhlYWRpbmdzKCBtYXRjaGVzICk7XG5cdFx0XHR9XG5cblx0XHRcdGFzc2Vzc21lbnRSZXN1bHQuc2V0U2NvcmUoIHJlc3VsdC5zY29yZSApO1xuXHRcdFx0YXNzZXNzbWVudFJlc3VsdC5zZXRUZXh0KCByZXN1bHQudGV4dCApO1xuXHRcdH1cblx0XHRyZXR1cm4gYXNzZXNzbWVudFJlc3VsdDtcblx0fTtcblxuXHQvKipcblx0ICpcblx0ICogQHBhcmFtIG1hdGNoZXNcblx0ICogQHJldHVybnMge3tzY29yZTogbnVtYmVyLCB0ZXh0OiAqfX1cblx0ICovXG5cdFlvYXN0bG9jYWxTRU9wbHVnaW4ucHJvdG90eXBlLmxvY2FsVGl0bGVTY29yZSA9IGZ1bmN0aW9uKCBtYXRjaGVzICl7XG5cdFx0aWYgKCBtYXRjaGVzLmxlbmd0aCA+IDAgKSB7XG5cdFx0XHRyZXR1cm4ge1xuXHRcdFx0XHRzY29yZTogOSxcblx0XHRcdFx0dGV4dDogd3BzZW9Mb2NhbEwxMG4udGl0bGVfbG9jYXRpb25cblx0XHRcdH1cblx0XHR9XG5cdFx0cmV0dXJuIHtcblx0XHRcdHNjb3JlOiA0LFxuXHRcdFx0dGV4dDogd3BzZW9Mb2NhbEwxMG4udGl0bGVfbm9fbG9jYXRpb25cblx0XHR9XG5cdH07XG5cblx0LyoqXG5cdCAqIFNjb3JlcyB0aGUgdXJsIGJhc2VkIG9uIHRoZSBtYXRjaGVzIG9mIHRoZSBsb2NhdGlvbidzIGNpdHkgaW4gaGVhZGluZ3MuXG5cdCAqXG5cdCAqIEBwYXJhbSB7YXJyYXl9IG1hdGNoZXMgVGhlIG1hdGNoZXMgb2YgdGhlIGxvY2F0aW9uIGluIHRoZSB1cmxcblx0ICogQHJldHVybnMge3tzY29yZTogbnVtYmVyLCB0ZXh0OiAqfX1cblx0ICovXG5cdFlvYXN0bG9jYWxTRU9wbHVnaW4ucHJvdG90eXBlLnNjb3JlTG9jYWxDaXR5SW5IZWFkaW5ncyA9IGZ1bmN0aW9uKCBtYXRjaGVzICkge1xuXHRcdGlmICggbWF0Y2hlcy5sZW5ndGggPiAwICkge1xuXHRcdFx0cmV0dXJue1xuXHRcdFx0XHRzY29yZTogOSxcblx0XHRcdFx0dGV4dDogd3BzZW9Mb2NhbEwxMG4uaGVhZGluZ19sb2NhdGlvblxuXHRcdFx0fVxuXHRcdH1cblx0XHRyZXR1cm57XG5cdFx0XHRzY29yZTogNCxcblx0XHRcdHRleHQ6IHdwc2VvTG9jYWxMMTBuLmhlYWRpbmdfbm9fbG9jYXRpb25cblx0XHR9XG5cdH07XG5cblx0LyoqXG5cdCAqIFJ1bnMgYW4gYXNzZXNzbWVudCBmb3Igc2NvcmluZyB0aGUgbG9jYXRpb24gaW4gdGhlIFVSTC5cblx0ICogQHBhcmFtIHtvYmplY3R9IHBhcGVyIFRoZSBwYXBlciB0byBydW4gdGhpcyBhc3Nlc3NtZW50IG9uXG5cdCAqIEBwYXJhbSB7b2JqZWN0fSByZXNlYXJjaGVyIFRoZSByZXNlYXJjaGVyIHVzZWQgZm9yIHRoZSBhc3Nlc3NtZW50XG5cdCAqIEBwYXJhbSB7b2JqZWN0fSBpMThuIFRoZSBpMThuLW9iamVjdCB1c2VkIGZvciBwYXJzaW5nIHRyYW5zbGF0aW9uc1xuXHQgKiBAcmV0dXJucyB7b2JqZWN0fSBhbiBhc3Nlc3NtZW50cmVzdWx0IHdpdGggdGhlIHNjb3JlIGFuZCBmb3JtYXR0ZWQgdGV4dC5cblx0ICovXG5cdFlvYXN0bG9jYWxTRU9wbHVnaW4ucHJvdG90eXBlLmxvY2FsVXJsID0gZnVuY3Rpb24oIHBhcGVyLCByZXNlYXJjaGVyLCBpMThuICkge1xuXHRcdHZhciBhc3Nlc3NtZW50UmVzdWx0ID0gbmV3IEFzc2Vzc21lbnRSZXN1bHQoKTtcblx0XHRpZiggd3BzZW9Mb2NhbEwxMG4ubG9jYXRpb24gIT09ICcnICkge1xuXHRcdFx0dmFyIGxvY2F0aW9uID0gd3BzZW9Mb2NhbEwxMG4ubG9jYXRpb247XG5cdFx0XHRsb2NhdGlvbiA9IGxvY2F0aW9uLnJlcGxhY2UoIFwiJ1wiLCBcIlwiICkucmVwbGFjZSggL1xccy9pZywgXCItXCIgKTtcblx0XHRcdGxvY2F0aW9uID0gZXNjYXBlUmVnRXhwKCBsb2NhdGlvbiApO1xuXHRcdFx0dmFyIGJ1c2luZXNzX2NpdHkgPSBuZXcgUmVnRXhwKCBsb2NhdGlvbiwgJ2lnJyApO1xuXHRcdFx0dmFyIG1hdGNoZXMgPSBwYXBlci5nZXRVcmwoKS5tYXRjaCggYnVzaW5lc3NfY2l0eSApIHx8IDA7XG5cdFx0XHR2YXIgcmVzdWx0ID0gdGhpcy5zY29yZUxvY2FsVXJsKCBtYXRjaGVzICk7XG5cdFx0XHRhc3Nlc3NtZW50UmVzdWx0LnNldFNjb3JlKCByZXN1bHQuc2NvcmUgKTtcblx0XHRcdGFzc2Vzc21lbnRSZXN1bHQuc2V0VGV4dCggcmVzdWx0LnRleHQgKTtcblx0XHR9XG5cdFx0cmV0dXJuIGFzc2Vzc21lbnRSZXN1bHQ7XG5cdH07XG5cblx0LyoqXG5cdCAqIFNjb3JlcyB0aGUgdXJsIGJhc2VkIG9uIHRoZSBtYXRjaGVzIG9mIHRoZSBsb2NhdGlvbi5cblx0ICogQHBhcmFtIHthcnJheX0gbWF0Y2hlcyBUaGUgbWF0Y2hlcyBvZiB0aGUgbG9jYXRpb24gaW4gdGhlIHVybFxuXHQgKiBAcmV0dXJucyB7e3Njb3JlOiBudW1iZXIsIHRleHQ6ICp9fVxuXHQgKi9cblx0WW9hc3Rsb2NhbFNFT3BsdWdpbi5wcm90b3R5cGUuc2NvcmVMb2NhbFVybCA9IGZ1bmN0aW9uKCBtYXRjaGVzICkge1xuXHRcdGlmICggbWF0Y2hlcy5sZW5ndGggPiAwICkge1xuXHRcdFx0cmV0dXJue1xuXHRcdFx0XHRzY29yZTogOSxcblx0XHRcdFx0dGV4dDogd3BzZW9Mb2NhbEwxMG4udXJsX2xvY2F0aW9uXG5cdFx0XHR9XG5cdFx0fVxuXHRcdHJldHVybntcblx0XHRcdHNjb3JlOiA0LFxuXHRcdFx0dGV4dDogd3BzZW9Mb2NhbEwxMG4udXJsX25vX2xvY2F0aW9uXG5cdFx0fVxuXHR9O1xuXG5cdC8qKlxuXHQgKiBSdW5zIGFuIGFzc2Vzc21lbnQgZm9yIHNjb3JpbmcgdGhlIGxvY2F0aW9uIGluIHRoZSBVUkwuXG5cdCAqIEBwYXJhbSB7b2JqZWN0fSBwYXBlciBUaGUgcGFwZXIgdG8gcnVuIHRoaXMgYXNzZXNzbWVudCBvblxuXHQgKiBAcGFyYW0ge29iamVjdH0gcmVzZWFyY2hlciBUaGUgcmVzZWFyY2hlciB1c2VkIGZvciB0aGUgYXNzZXNzbWVudFxuXHQgKiBAcGFyYW0ge29iamVjdH0gaTE4biBUaGUgaTE4bi1vYmplY3QgdXNlZCBmb3IgcGFyc2luZyB0cmFuc2xhdGlvbnNcblx0ICogQHJldHVybnMge29iamVjdH0gYW4gYXNzZXNzbWVudHJlc3VsdCB3aXRoIHRoZSBzY29yZSBhbmQgZm9ybWF0dGVkIHRleHQuXG5cdCAqL1xuXHRZb2FzdGxvY2FsU0VPcGx1Z2luLnByb3RvdHlwZS5sb2NhbFNjaGVtYSA9IGZ1bmN0aW9uKCBwYXBlciwgcmVzZWFyY2hlciwgaTE4biApIHtcblx0XHR2YXIgYXNzZXNzbWVudFJlc3VsdCA9IG5ldyBBc3Nlc3NtZW50UmVzdWx0KCk7XG4gICAgICAgIHZhciBzY2hlbWEgPSBuZXcgUmVnRXhwKCAnY2xhc3M9W1wiXFwnXXdwc2VvLWxvY2F0aW9uW1wiXFwnXSBpdGVtc2NvcGUnLCAnaWcnICk7XG5cdFx0dmFyIG1hdGNoZXMgPSBwYXBlci5nZXRUZXh0KCkubWF0Y2goIHNjaGVtYSApIHx8IDA7XG5cdFx0dmFyIHJlc3VsdCA9IHRoaXMuc2NvcmVMb2NhbFNjaGVtYSggbWF0Y2hlcyApO1xuXG5cdFx0YXNzZXNzbWVudFJlc3VsdC5zZXRTY29yZSggcmVzdWx0LnNjb3JlICk7XG5cdFx0YXNzZXNzbWVudFJlc3VsdC5zZXRUZXh0KCByZXN1bHQudGV4dCApO1xuXG5cdFx0cmV0dXJuIGFzc2Vzc21lbnRSZXN1bHQ7XG5cdH07XG5cblx0LyoqXG5cdCAqIFNjb3JlcyB0aGUgdXJsIGJhc2VkIG9uIHRoZSBtYXRjaGVzIG9mIHRoZSBsb2NhdGlvbi5cblx0ICogQHBhcmFtIHthcnJheX0gbWF0Y2hlcyBUaGUgbWF0Y2hlcyBvZiB0aGUgbG9jYXRpb24gaW4gdGhlIHVybFxuXHQgKiBAcmV0dXJucyB7e3Njb3JlOiBudW1iZXIsIHRleHQ6ICp9fVxuXHQgKi9cblx0WW9hc3Rsb2NhbFNFT3BsdWdpbi5wcm90b3R5cGUuc2NvcmVMb2NhbFNjaGVtYSA9IGZ1bmN0aW9uKCBtYXRjaGVzICkge1xuXHRcdGlmICggbWF0Y2hlcy5sZW5ndGggPiAwICkge1xuXHRcdFx0cmV0dXJue1xuXHRcdFx0XHRzY29yZTogOSxcblx0XHRcdFx0dGV4dDogd3BzZW9Mb2NhbEwxMG4uYWRkcmVzc19zY2hlbWFcblx0XHRcdH1cblx0XHR9XG5cdFx0cmV0dXJue1xuXHRcdFx0c2NvcmU6IDQsXG5cdFx0XHR0ZXh0OiB3cHNlb0xvY2FsTDEwbi5ub19hZGRyZXNzX3NjaGVtYVxuXHRcdH1cblx0fTtcblxuXHQvKipcblx0ICogQWRkcyBjYWxsYmFjayBmb3IgdGhlIHdwc2VvX2J1c2luZXNzX2NpdHkgZmllbGQgc28gaXQgaXMgdXBkYXRlZFxuXHQgKi9cblx0WW9hc3Rsb2NhbFNFT3BsdWdpbi5wcm90b3R5cGUuYWRkQ2FsbGJhY2sgPSBmdW5jdGlvbigpIHtcblx0XHR2YXIgZWxlbSA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCAnd3BzZW9fYnVzaW5lc3NfY2l0eScgKTtcblx0XHRpZiggZWxlbSAhPT0gbnVsbCl7XG5cdFx0XHRlbGVtLmFkZEV2ZW50TGlzdGVuZXIoICdjaGFuZ2UnLCBZb2FzdFNFTy5hcHAuYW5hbHl6ZVRpbWVyLmJpbmQgKCBZb2FzdFNFTy5hcHAgKSApO1xuXHRcdH1cblx0fTtcblxuXHQvKipcblx0ICogQWRkcyBldmVudExpc3RlbmVyIG9uIHBhZ2UgbG9hZCB0byBsb2FkIHRoZSB2aWRlb1NFTy5cblx0ICovXG5cdGlmICggdHlwZW9mIFlvYXN0U0VPICE9PSAndW5kZWZpbmVkJyAmJiB0eXBlb2YgWW9hc3RTRU8uYXBwICE9PSAndW5kZWZpbmVkJyApIHtcblx0XHRuZXcgWW9hc3Rsb2NhbFNFT3BsdWdpbigpO1xuXHR9XG5cdGVsc2Uge1xuXHRcdGpRdWVyeSggd2luZG93ICkub24oXG5cdFx0XHQnWW9hc3RTRU86cmVhZHknLFxuXHRcdFx0ZnVuY3Rpb24oKSB7XG5cdFx0XHRcdG5ldyBZb2FzdGxvY2FsU0VPcGx1Z2luKCk7XG5cdFx0XHR9XG5cdFx0KTtcblx0fVxuXG59KCkpO1xuIiwidmFyIHJvb3QgPSByZXF1aXJlKCcuL19yb290Jyk7XG5cbi8qKiBCdWlsdC1pbiB2YWx1ZSByZWZlcmVuY2VzLiAqL1xudmFyIFN5bWJvbCA9IHJvb3QuU3ltYm9sO1xuXG5tb2R1bGUuZXhwb3J0cyA9IFN5bWJvbDtcbiIsIi8qKlxuICogQSBzcGVjaWFsaXplZCB2ZXJzaW9uIG9mIGBfLm1hcGAgZm9yIGFycmF5cyB3aXRob3V0IHN1cHBvcnQgZm9yIGl0ZXJhdGVlXG4gKiBzaG9ydGhhbmRzLlxuICpcbiAqIEBwcml2YXRlXG4gKiBAcGFyYW0ge0FycmF5fSBbYXJyYXldIFRoZSBhcnJheSB0byBpdGVyYXRlIG92ZXIuXG4gKiBAcGFyYW0ge0Z1bmN0aW9ufSBpdGVyYXRlZSBUaGUgZnVuY3Rpb24gaW52b2tlZCBwZXIgaXRlcmF0aW9uLlxuICogQHJldHVybnMge0FycmF5fSBSZXR1cm5zIHRoZSBuZXcgbWFwcGVkIGFycmF5LlxuICovXG5mdW5jdGlvbiBhcnJheU1hcChhcnJheSwgaXRlcmF0ZWUpIHtcbiAgdmFyIGluZGV4ID0gLTEsXG4gICAgICBsZW5ndGggPSBhcnJheSA9PSBudWxsID8gMCA6IGFycmF5Lmxlbmd0aCxcbiAgICAgIHJlc3VsdCA9IEFycmF5KGxlbmd0aCk7XG5cbiAgd2hpbGUgKCsraW5kZXggPCBsZW5ndGgpIHtcbiAgICByZXN1bHRbaW5kZXhdID0gaXRlcmF0ZWUoYXJyYXlbaW5kZXhdLCBpbmRleCwgYXJyYXkpO1xuICB9XG4gIHJldHVybiByZXN1bHQ7XG59XG5cbm1vZHVsZS5leHBvcnRzID0gYXJyYXlNYXA7XG4iLCJ2YXIgU3ltYm9sID0gcmVxdWlyZSgnLi9fU3ltYm9sJyksXG4gICAgZ2V0UmF3VGFnID0gcmVxdWlyZSgnLi9fZ2V0UmF3VGFnJyksXG4gICAgb2JqZWN0VG9TdHJpbmcgPSByZXF1aXJlKCcuL19vYmplY3RUb1N0cmluZycpO1xuXG4vKiogYE9iamVjdCN0b1N0cmluZ2AgcmVzdWx0IHJlZmVyZW5jZXMuICovXG52YXIgbnVsbFRhZyA9ICdbb2JqZWN0IE51bGxdJyxcbiAgICB1bmRlZmluZWRUYWcgPSAnW29iamVjdCBVbmRlZmluZWRdJztcblxuLyoqIEJ1aWx0LWluIHZhbHVlIHJlZmVyZW5jZXMuICovXG52YXIgc3ltVG9TdHJpbmdUYWcgPSBTeW1ib2wgPyBTeW1ib2wudG9TdHJpbmdUYWcgOiB1bmRlZmluZWQ7XG5cbi8qKlxuICogVGhlIGJhc2UgaW1wbGVtZW50YXRpb24gb2YgYGdldFRhZ2Agd2l0aG91dCBmYWxsYmFja3MgZm9yIGJ1Z2d5IGVudmlyb25tZW50cy5cbiAqXG4gKiBAcHJpdmF0ZVxuICogQHBhcmFtIHsqfSB2YWx1ZSBUaGUgdmFsdWUgdG8gcXVlcnkuXG4gKiBAcmV0dXJucyB7c3RyaW5nfSBSZXR1cm5zIHRoZSBgdG9TdHJpbmdUYWdgLlxuICovXG5mdW5jdGlvbiBiYXNlR2V0VGFnKHZhbHVlKSB7XG4gIGlmICh2YWx1ZSA9PSBudWxsKSB7XG4gICAgcmV0dXJuIHZhbHVlID09PSB1bmRlZmluZWQgPyB1bmRlZmluZWRUYWcgOiBudWxsVGFnO1xuICB9XG4gIHJldHVybiAoc3ltVG9TdHJpbmdUYWcgJiYgc3ltVG9TdHJpbmdUYWcgaW4gT2JqZWN0KHZhbHVlKSlcbiAgICA/IGdldFJhd1RhZyh2YWx1ZSlcbiAgICA6IG9iamVjdFRvU3RyaW5nKHZhbHVlKTtcbn1cblxubW9kdWxlLmV4cG9ydHMgPSBiYXNlR2V0VGFnO1xuIiwidmFyIFN5bWJvbCA9IHJlcXVpcmUoJy4vX1N5bWJvbCcpLFxuICAgIGFycmF5TWFwID0gcmVxdWlyZSgnLi9fYXJyYXlNYXAnKSxcbiAgICBpc0FycmF5ID0gcmVxdWlyZSgnLi9pc0FycmF5JyksXG4gICAgaXNTeW1ib2wgPSByZXF1aXJlKCcuL2lzU3ltYm9sJyk7XG5cbi8qKiBVc2VkIGFzIHJlZmVyZW5jZXMgZm9yIHZhcmlvdXMgYE51bWJlcmAgY29uc3RhbnRzLiAqL1xudmFyIElORklOSVRZID0gMSAvIDA7XG5cbi8qKiBVc2VkIHRvIGNvbnZlcnQgc3ltYm9scyB0byBwcmltaXRpdmVzIGFuZCBzdHJpbmdzLiAqL1xudmFyIHN5bWJvbFByb3RvID0gU3ltYm9sID8gU3ltYm9sLnByb3RvdHlwZSA6IHVuZGVmaW5lZCxcbiAgICBzeW1ib2xUb1N0cmluZyA9IHN5bWJvbFByb3RvID8gc3ltYm9sUHJvdG8udG9TdHJpbmcgOiB1bmRlZmluZWQ7XG5cbi8qKlxuICogVGhlIGJhc2UgaW1wbGVtZW50YXRpb24gb2YgYF8udG9TdHJpbmdgIHdoaWNoIGRvZXNuJ3QgY29udmVydCBudWxsaXNoXG4gKiB2YWx1ZXMgdG8gZW1wdHkgc3RyaW5ncy5cbiAqXG4gKiBAcHJpdmF0ZVxuICogQHBhcmFtIHsqfSB2YWx1ZSBUaGUgdmFsdWUgdG8gcHJvY2Vzcy5cbiAqIEByZXR1cm5zIHtzdHJpbmd9IFJldHVybnMgdGhlIHN0cmluZy5cbiAqL1xuZnVuY3Rpb24gYmFzZVRvU3RyaW5nKHZhbHVlKSB7XG4gIC8vIEV4aXQgZWFybHkgZm9yIHN0cmluZ3MgdG8gYXZvaWQgYSBwZXJmb3JtYW5jZSBoaXQgaW4gc29tZSBlbnZpcm9ubWVudHMuXG4gIGlmICh0eXBlb2YgdmFsdWUgPT0gJ3N0cmluZycpIHtcbiAgICByZXR1cm4gdmFsdWU7XG4gIH1cbiAgaWYgKGlzQXJyYXkodmFsdWUpKSB7XG4gICAgLy8gUmVjdXJzaXZlbHkgY29udmVydCB2YWx1ZXMgKHN1c2NlcHRpYmxlIHRvIGNhbGwgc3RhY2sgbGltaXRzKS5cbiAgICByZXR1cm4gYXJyYXlNYXAodmFsdWUsIGJhc2VUb1N0cmluZykgKyAnJztcbiAgfVxuICBpZiAoaXNTeW1ib2wodmFsdWUpKSB7XG4gICAgcmV0dXJuIHN5bWJvbFRvU3RyaW5nID8gc3ltYm9sVG9TdHJpbmcuY2FsbCh2YWx1ZSkgOiAnJztcbiAgfVxuICB2YXIgcmVzdWx0ID0gKHZhbHVlICsgJycpO1xuICByZXR1cm4gKHJlc3VsdCA9PSAnMCcgJiYgKDEgLyB2YWx1ZSkgPT0gLUlORklOSVRZKSA/ICctMCcgOiByZXN1bHQ7XG59XG5cbm1vZHVsZS5leHBvcnRzID0gYmFzZVRvU3RyaW5nO1xuIiwiLyoqIERldGVjdCBmcmVlIHZhcmlhYmxlIGBnbG9iYWxgIGZyb20gTm9kZS5qcy4gKi9cbnZhciBmcmVlR2xvYmFsID0gdHlwZW9mIGdsb2JhbCA9PSAnb2JqZWN0JyAmJiBnbG9iYWwgJiYgZ2xvYmFsLk9iamVjdCA9PT0gT2JqZWN0ICYmIGdsb2JhbDtcblxubW9kdWxlLmV4cG9ydHMgPSBmcmVlR2xvYmFsO1xuIiwidmFyIFN5bWJvbCA9IHJlcXVpcmUoJy4vX1N5bWJvbCcpO1xuXG4vKiogVXNlZCBmb3IgYnVpbHQtaW4gbWV0aG9kIHJlZmVyZW5jZXMuICovXG52YXIgb2JqZWN0UHJvdG8gPSBPYmplY3QucHJvdG90eXBlO1xuXG4vKiogVXNlZCB0byBjaGVjayBvYmplY3RzIGZvciBvd24gcHJvcGVydGllcy4gKi9cbnZhciBoYXNPd25Qcm9wZXJ0eSA9IG9iamVjdFByb3RvLmhhc093blByb3BlcnR5O1xuXG4vKipcbiAqIFVzZWQgdG8gcmVzb2x2ZSB0aGVcbiAqIFtgdG9TdHJpbmdUYWdgXShodHRwOi8vZWNtYS1pbnRlcm5hdGlvbmFsLm9yZy9lY21hLTI2Mi83LjAvI3NlYy1vYmplY3QucHJvdG90eXBlLnRvc3RyaW5nKVxuICogb2YgdmFsdWVzLlxuICovXG52YXIgbmF0aXZlT2JqZWN0VG9TdHJpbmcgPSBvYmplY3RQcm90by50b1N0cmluZztcblxuLyoqIEJ1aWx0LWluIHZhbHVlIHJlZmVyZW5jZXMuICovXG52YXIgc3ltVG9TdHJpbmdUYWcgPSBTeW1ib2wgPyBTeW1ib2wudG9TdHJpbmdUYWcgOiB1bmRlZmluZWQ7XG5cbi8qKlxuICogQSBzcGVjaWFsaXplZCB2ZXJzaW9uIG9mIGBiYXNlR2V0VGFnYCB3aGljaCBpZ25vcmVzIGBTeW1ib2wudG9TdHJpbmdUYWdgIHZhbHVlcy5cbiAqXG4gKiBAcHJpdmF0ZVxuICogQHBhcmFtIHsqfSB2YWx1ZSBUaGUgdmFsdWUgdG8gcXVlcnkuXG4gKiBAcmV0dXJucyB7c3RyaW5nfSBSZXR1cm5zIHRoZSByYXcgYHRvU3RyaW5nVGFnYC5cbiAqL1xuZnVuY3Rpb24gZ2V0UmF3VGFnKHZhbHVlKSB7XG4gIHZhciBpc093biA9IGhhc093blByb3BlcnR5LmNhbGwodmFsdWUsIHN5bVRvU3RyaW5nVGFnKSxcbiAgICAgIHRhZyA9IHZhbHVlW3N5bVRvU3RyaW5nVGFnXTtcblxuICB0cnkge1xuICAgIHZhbHVlW3N5bVRvU3RyaW5nVGFnXSA9IHVuZGVmaW5lZDtcbiAgICB2YXIgdW5tYXNrZWQgPSB0cnVlO1xuICB9IGNhdGNoIChlKSB7fVxuXG4gIHZhciByZXN1bHQgPSBuYXRpdmVPYmplY3RUb1N0cmluZy5jYWxsKHZhbHVlKTtcbiAgaWYgKHVubWFza2VkKSB7XG4gICAgaWYgKGlzT3duKSB7XG4gICAgICB2YWx1ZVtzeW1Ub1N0cmluZ1RhZ10gPSB0YWc7XG4gICAgfSBlbHNlIHtcbiAgICAgIGRlbGV0ZSB2YWx1ZVtzeW1Ub1N0cmluZ1RhZ107XG4gICAgfVxuICB9XG4gIHJldHVybiByZXN1bHQ7XG59XG5cbm1vZHVsZS5leHBvcnRzID0gZ2V0UmF3VGFnO1xuIiwiLyoqIFVzZWQgZm9yIGJ1aWx0LWluIG1ldGhvZCByZWZlcmVuY2VzLiAqL1xudmFyIG9iamVjdFByb3RvID0gT2JqZWN0LnByb3RvdHlwZTtcblxuLyoqXG4gKiBVc2VkIHRvIHJlc29sdmUgdGhlXG4gKiBbYHRvU3RyaW5nVGFnYF0oaHR0cDovL2VjbWEtaW50ZXJuYXRpb25hbC5vcmcvZWNtYS0yNjIvNy4wLyNzZWMtb2JqZWN0LnByb3RvdHlwZS50b3N0cmluZylcbiAqIG9mIHZhbHVlcy5cbiAqL1xudmFyIG5hdGl2ZU9iamVjdFRvU3RyaW5nID0gb2JqZWN0UHJvdG8udG9TdHJpbmc7XG5cbi8qKlxuICogQ29udmVydHMgYHZhbHVlYCB0byBhIHN0cmluZyB1c2luZyBgT2JqZWN0LnByb3RvdHlwZS50b1N0cmluZ2AuXG4gKlxuICogQHByaXZhdGVcbiAqIEBwYXJhbSB7Kn0gdmFsdWUgVGhlIHZhbHVlIHRvIGNvbnZlcnQuXG4gKiBAcmV0dXJucyB7c3RyaW5nfSBSZXR1cm5zIHRoZSBjb252ZXJ0ZWQgc3RyaW5nLlxuICovXG5mdW5jdGlvbiBvYmplY3RUb1N0cmluZyh2YWx1ZSkge1xuICByZXR1cm4gbmF0aXZlT2JqZWN0VG9TdHJpbmcuY2FsbCh2YWx1ZSk7XG59XG5cbm1vZHVsZS5leHBvcnRzID0gb2JqZWN0VG9TdHJpbmc7XG4iLCJ2YXIgZnJlZUdsb2JhbCA9IHJlcXVpcmUoJy4vX2ZyZWVHbG9iYWwnKTtcblxuLyoqIERldGVjdCBmcmVlIHZhcmlhYmxlIGBzZWxmYC4gKi9cbnZhciBmcmVlU2VsZiA9IHR5cGVvZiBzZWxmID09ICdvYmplY3QnICYmIHNlbGYgJiYgc2VsZi5PYmplY3QgPT09IE9iamVjdCAmJiBzZWxmO1xuXG4vKiogVXNlZCBhcyBhIHJlZmVyZW5jZSB0byB0aGUgZ2xvYmFsIG9iamVjdC4gKi9cbnZhciByb290ID0gZnJlZUdsb2JhbCB8fCBmcmVlU2VsZiB8fCBGdW5jdGlvbigncmV0dXJuIHRoaXMnKSgpO1xuXG5tb2R1bGUuZXhwb3J0cyA9IHJvb3Q7XG4iLCJ2YXIgdG9TdHJpbmcgPSByZXF1aXJlKCcuL3RvU3RyaW5nJyk7XG5cbi8qKlxuICogVXNlZCB0byBtYXRjaCBgUmVnRXhwYFxuICogW3N5bnRheCBjaGFyYWN0ZXJzXShodHRwOi8vZWNtYS1pbnRlcm5hdGlvbmFsLm9yZy9lY21hLTI2Mi83LjAvI3NlYy1wYXR0ZXJucykuXG4gKi9cbnZhciByZVJlZ0V4cENoYXIgPSAvW1xcXFxeJC4qKz8oKVtcXF17fXxdL2csXG4gICAgcmVIYXNSZWdFeHBDaGFyID0gUmVnRXhwKHJlUmVnRXhwQ2hhci5zb3VyY2UpO1xuXG4vKipcbiAqIEVzY2FwZXMgdGhlIGBSZWdFeHBgIHNwZWNpYWwgY2hhcmFjdGVycyBcIl5cIiwgXCIkXCIsIFwiXFxcIiwgXCIuXCIsIFwiKlwiLCBcIitcIixcbiAqIFwiP1wiLCBcIihcIiwgXCIpXCIsIFwiW1wiLCBcIl1cIiwgXCJ7XCIsIFwifVwiLCBhbmQgXCJ8XCIgaW4gYHN0cmluZ2AuXG4gKlxuICogQHN0YXRpY1xuICogQG1lbWJlck9mIF9cbiAqIEBzaW5jZSAzLjAuMFxuICogQGNhdGVnb3J5IFN0cmluZ1xuICogQHBhcmFtIHtzdHJpbmd9IFtzdHJpbmc9JyddIFRoZSBzdHJpbmcgdG8gZXNjYXBlLlxuICogQHJldHVybnMge3N0cmluZ30gUmV0dXJucyB0aGUgZXNjYXBlZCBzdHJpbmcuXG4gKiBAZXhhbXBsZVxuICpcbiAqIF8uZXNjYXBlUmVnRXhwKCdbbG9kYXNoXShodHRwczovL2xvZGFzaC5jb20vKScpO1xuICogLy8gPT4gJ1xcW2xvZGFzaFxcXVxcKGh0dHBzOi8vbG9kYXNoXFwuY29tL1xcKSdcbiAqL1xuZnVuY3Rpb24gZXNjYXBlUmVnRXhwKHN0cmluZykge1xuICBzdHJpbmcgPSB0b1N0cmluZyhzdHJpbmcpO1xuICByZXR1cm4gKHN0cmluZyAmJiByZUhhc1JlZ0V4cENoYXIudGVzdChzdHJpbmcpKVxuICAgID8gc3RyaW5nLnJlcGxhY2UocmVSZWdFeHBDaGFyLCAnXFxcXCQmJylcbiAgICA6IHN0cmluZztcbn1cblxubW9kdWxlLmV4cG9ydHMgPSBlc2NhcGVSZWdFeHA7XG4iLCIvKipcbiAqIENoZWNrcyBpZiBgdmFsdWVgIGlzIGNsYXNzaWZpZWQgYXMgYW4gYEFycmF5YCBvYmplY3QuXG4gKlxuICogQHN0YXRpY1xuICogQG1lbWJlck9mIF9cbiAqIEBzaW5jZSAwLjEuMFxuICogQGNhdGVnb3J5IExhbmdcbiAqIEBwYXJhbSB7Kn0gdmFsdWUgVGhlIHZhbHVlIHRvIGNoZWNrLlxuICogQHJldHVybnMge2Jvb2xlYW59IFJldHVybnMgYHRydWVgIGlmIGB2YWx1ZWAgaXMgYW4gYXJyYXksIGVsc2UgYGZhbHNlYC5cbiAqIEBleGFtcGxlXG4gKlxuICogXy5pc0FycmF5KFsxLCAyLCAzXSk7XG4gKiAvLyA9PiB0cnVlXG4gKlxuICogXy5pc0FycmF5KGRvY3VtZW50LmJvZHkuY2hpbGRyZW4pO1xuICogLy8gPT4gZmFsc2VcbiAqXG4gKiBfLmlzQXJyYXkoJ2FiYycpO1xuICogLy8gPT4gZmFsc2VcbiAqXG4gKiBfLmlzQXJyYXkoXy5ub29wKTtcbiAqIC8vID0+IGZhbHNlXG4gKi9cbnZhciBpc0FycmF5ID0gQXJyYXkuaXNBcnJheTtcblxubW9kdWxlLmV4cG9ydHMgPSBpc0FycmF5O1xuIiwidmFyIGJhc2VHZXRUYWcgPSByZXF1aXJlKCcuL19iYXNlR2V0VGFnJyksXG4gICAgaXNPYmplY3RMaWtlID0gcmVxdWlyZSgnLi9pc09iamVjdExpa2UnKTtcblxuLyoqIGBPYmplY3QjdG9TdHJpbmdgIHJlc3VsdCByZWZlcmVuY2VzLiAqL1xudmFyIG51bWJlclRhZyA9ICdbb2JqZWN0IE51bWJlcl0nO1xuXG4vKipcbiAqIENoZWNrcyBpZiBgdmFsdWVgIGlzIGNsYXNzaWZpZWQgYXMgYSBgTnVtYmVyYCBwcmltaXRpdmUgb3Igb2JqZWN0LlxuICpcbiAqICoqTm90ZToqKiBUbyBleGNsdWRlIGBJbmZpbml0eWAsIGAtSW5maW5pdHlgLCBhbmQgYE5hTmAsIHdoaWNoIGFyZVxuICogY2xhc3NpZmllZCBhcyBudW1iZXJzLCB1c2UgdGhlIGBfLmlzRmluaXRlYCBtZXRob2QuXG4gKlxuICogQHN0YXRpY1xuICogQG1lbWJlck9mIF9cbiAqIEBzaW5jZSAwLjEuMFxuICogQGNhdGVnb3J5IExhbmdcbiAqIEBwYXJhbSB7Kn0gdmFsdWUgVGhlIHZhbHVlIHRvIGNoZWNrLlxuICogQHJldHVybnMge2Jvb2xlYW59IFJldHVybnMgYHRydWVgIGlmIGB2YWx1ZWAgaXMgYSBudW1iZXIsIGVsc2UgYGZhbHNlYC5cbiAqIEBleGFtcGxlXG4gKlxuICogXy5pc051bWJlcigzKTtcbiAqIC8vID0+IHRydWVcbiAqXG4gKiBfLmlzTnVtYmVyKE51bWJlci5NSU5fVkFMVUUpO1xuICogLy8gPT4gdHJ1ZVxuICpcbiAqIF8uaXNOdW1iZXIoSW5maW5pdHkpO1xuICogLy8gPT4gdHJ1ZVxuICpcbiAqIF8uaXNOdW1iZXIoJzMnKTtcbiAqIC8vID0+IGZhbHNlXG4gKi9cbmZ1bmN0aW9uIGlzTnVtYmVyKHZhbHVlKSB7XG4gIHJldHVybiB0eXBlb2YgdmFsdWUgPT0gJ251bWJlcicgfHxcbiAgICAoaXNPYmplY3RMaWtlKHZhbHVlKSAmJiBiYXNlR2V0VGFnKHZhbHVlKSA9PSBudW1iZXJUYWcpO1xufVxuXG5tb2R1bGUuZXhwb3J0cyA9IGlzTnVtYmVyO1xuIiwiLyoqXG4gKiBDaGVja3MgaWYgYHZhbHVlYCBpcyBvYmplY3QtbGlrZS4gQSB2YWx1ZSBpcyBvYmplY3QtbGlrZSBpZiBpdCdzIG5vdCBgbnVsbGBcbiAqIGFuZCBoYXMgYSBgdHlwZW9mYCByZXN1bHQgb2YgXCJvYmplY3RcIi5cbiAqXG4gKiBAc3RhdGljXG4gKiBAbWVtYmVyT2YgX1xuICogQHNpbmNlIDQuMC4wXG4gKiBAY2F0ZWdvcnkgTGFuZ1xuICogQHBhcmFtIHsqfSB2YWx1ZSBUaGUgdmFsdWUgdG8gY2hlY2suXG4gKiBAcmV0dXJucyB7Ym9vbGVhbn0gUmV0dXJucyBgdHJ1ZWAgaWYgYHZhbHVlYCBpcyBvYmplY3QtbGlrZSwgZWxzZSBgZmFsc2VgLlxuICogQGV4YW1wbGVcbiAqXG4gKiBfLmlzT2JqZWN0TGlrZSh7fSk7XG4gKiAvLyA9PiB0cnVlXG4gKlxuICogXy5pc09iamVjdExpa2UoWzEsIDIsIDNdKTtcbiAqIC8vID0+IHRydWVcbiAqXG4gKiBfLmlzT2JqZWN0TGlrZShfLm5vb3ApO1xuICogLy8gPT4gZmFsc2VcbiAqXG4gKiBfLmlzT2JqZWN0TGlrZShudWxsKTtcbiAqIC8vID0+IGZhbHNlXG4gKi9cbmZ1bmN0aW9uIGlzT2JqZWN0TGlrZSh2YWx1ZSkge1xuICByZXR1cm4gdmFsdWUgIT0gbnVsbCAmJiB0eXBlb2YgdmFsdWUgPT0gJ29iamVjdCc7XG59XG5cbm1vZHVsZS5leHBvcnRzID0gaXNPYmplY3RMaWtlO1xuIiwidmFyIGJhc2VHZXRUYWcgPSByZXF1aXJlKCcuL19iYXNlR2V0VGFnJyksXG4gICAgaXNPYmplY3RMaWtlID0gcmVxdWlyZSgnLi9pc09iamVjdExpa2UnKTtcblxuLyoqIGBPYmplY3QjdG9TdHJpbmdgIHJlc3VsdCByZWZlcmVuY2VzLiAqL1xudmFyIHN5bWJvbFRhZyA9ICdbb2JqZWN0IFN5bWJvbF0nO1xuXG4vKipcbiAqIENoZWNrcyBpZiBgdmFsdWVgIGlzIGNsYXNzaWZpZWQgYXMgYSBgU3ltYm9sYCBwcmltaXRpdmUgb3Igb2JqZWN0LlxuICpcbiAqIEBzdGF0aWNcbiAqIEBtZW1iZXJPZiBfXG4gKiBAc2luY2UgNC4wLjBcbiAqIEBjYXRlZ29yeSBMYW5nXG4gKiBAcGFyYW0geyp9IHZhbHVlIFRoZSB2YWx1ZSB0byBjaGVjay5cbiAqIEByZXR1cm5zIHtib29sZWFufSBSZXR1cm5zIGB0cnVlYCBpZiBgdmFsdWVgIGlzIGEgc3ltYm9sLCBlbHNlIGBmYWxzZWAuXG4gKiBAZXhhbXBsZVxuICpcbiAqIF8uaXNTeW1ib2woU3ltYm9sLml0ZXJhdG9yKTtcbiAqIC8vID0+IHRydWVcbiAqXG4gKiBfLmlzU3ltYm9sKCdhYmMnKTtcbiAqIC8vID0+IGZhbHNlXG4gKi9cbmZ1bmN0aW9uIGlzU3ltYm9sKHZhbHVlKSB7XG4gIHJldHVybiB0eXBlb2YgdmFsdWUgPT0gJ3N5bWJvbCcgfHxcbiAgICAoaXNPYmplY3RMaWtlKHZhbHVlKSAmJiBiYXNlR2V0VGFnKHZhbHVlKSA9PSBzeW1ib2xUYWcpO1xufVxuXG5tb2R1bGUuZXhwb3J0cyA9IGlzU3ltYm9sO1xuIiwiLyoqXG4gKiBDaGVja3MgaWYgYHZhbHVlYCBpcyBgdW5kZWZpbmVkYC5cbiAqXG4gKiBAc3RhdGljXG4gKiBAc2luY2UgMC4xLjBcbiAqIEBtZW1iZXJPZiBfXG4gKiBAY2F0ZWdvcnkgTGFuZ1xuICogQHBhcmFtIHsqfSB2YWx1ZSBUaGUgdmFsdWUgdG8gY2hlY2suXG4gKiBAcmV0dXJucyB7Ym9vbGVhbn0gUmV0dXJucyBgdHJ1ZWAgaWYgYHZhbHVlYCBpcyBgdW5kZWZpbmVkYCwgZWxzZSBgZmFsc2VgLlxuICogQGV4YW1wbGVcbiAqXG4gKiBfLmlzVW5kZWZpbmVkKHZvaWQgMCk7XG4gKiAvLyA9PiB0cnVlXG4gKlxuICogXy5pc1VuZGVmaW5lZChudWxsKTtcbiAqIC8vID0+IGZhbHNlXG4gKi9cbmZ1bmN0aW9uIGlzVW5kZWZpbmVkKHZhbHVlKSB7XG4gIHJldHVybiB2YWx1ZSA9PT0gdW5kZWZpbmVkO1xufVxuXG5tb2R1bGUuZXhwb3J0cyA9IGlzVW5kZWZpbmVkO1xuIiwidmFyIGJhc2VUb1N0cmluZyA9IHJlcXVpcmUoJy4vX2Jhc2VUb1N0cmluZycpO1xuXG4vKipcbiAqIENvbnZlcnRzIGB2YWx1ZWAgdG8gYSBzdHJpbmcuIEFuIGVtcHR5IHN0cmluZyBpcyByZXR1cm5lZCBmb3IgYG51bGxgXG4gKiBhbmQgYHVuZGVmaW5lZGAgdmFsdWVzLiBUaGUgc2lnbiBvZiBgLTBgIGlzIHByZXNlcnZlZC5cbiAqXG4gKiBAc3RhdGljXG4gKiBAbWVtYmVyT2YgX1xuICogQHNpbmNlIDQuMC4wXG4gKiBAY2F0ZWdvcnkgTGFuZ1xuICogQHBhcmFtIHsqfSB2YWx1ZSBUaGUgdmFsdWUgdG8gY29udmVydC5cbiAqIEByZXR1cm5zIHtzdHJpbmd9IFJldHVybnMgdGhlIGNvbnZlcnRlZCBzdHJpbmcuXG4gKiBAZXhhbXBsZVxuICpcbiAqIF8udG9TdHJpbmcobnVsbCk7XG4gKiAvLyA9PiAnJ1xuICpcbiAqIF8udG9TdHJpbmcoLTApO1xuICogLy8gPT4gJy0wJ1xuICpcbiAqIF8udG9TdHJpbmcoWzEsIDIsIDNdKTtcbiAqIC8vID0+ICcxLDIsMydcbiAqL1xuZnVuY3Rpb24gdG9TdHJpbmcodmFsdWUpIHtcbiAgcmV0dXJuIHZhbHVlID09IG51bGwgPyAnJyA6IGJhc2VUb1N0cmluZyh2YWx1ZSk7XG59XG5cbm1vZHVsZS5leHBvcnRzID0gdG9TdHJpbmc7XG4iLCJcInVzZSBzdHJpY3RcIjtcblxudmFyIGlzVW5kZWZpbmVkID0gcmVxdWlyZShcImxvZGFzaC9pc1VuZGVmaW5lZFwiKTtcbnZhciBpc051bWJlciA9IHJlcXVpcmUoXCJsb2Rhc2gvaXNOdW1iZXJcIik7XG4vKipcbiAqIEEgZnVuY3Rpb24gdGhhdCBvbmx5IHJldHVybnMgYW4gZW1wdHkgdGhhdCBjYW4gYmUgdXNlZCBhcyBhbiBlbXB0eSBtYXJrZXJcbiAqXG4gKiBAcmV0dXJucyB7QXJyYXl9IEEgbGlzdCBvZiBlbXB0eSBtYXJrcy5cbiAqL1xudmFyIGVtcHR5TWFya2VyID0gZnVuY3Rpb24gZW1wdHlNYXJrZXIoKSB7XG4gICAgcmV0dXJuIFtdO1xufTtcbi8qKlxuICogQ29uc3RydWN0IHRoZSBBc3Nlc3NtZW50UmVzdWx0IHZhbHVlIG9iamVjdC5cbiAqXG4gKiBAcGFyYW0ge09iamVjdH0gW3ZhbHVlc10gVGhlIHZhbHVlcyBmb3IgdGhpcyBhc3Nlc3NtZW50IHJlc3VsdC5cbiAqXG4gKiBAY29uc3RydWN0b3JcbiAqL1xudmFyIEFzc2Vzc21lbnRSZXN1bHQgPSBmdW5jdGlvbiBBc3Nlc3NtZW50UmVzdWx0KHZhbHVlcykge1xuICAgIHRoaXMuX2hhc1Njb3JlID0gZmFsc2U7XG4gICAgdGhpcy5faWRlbnRpZmllciA9IFwiXCI7XG4gICAgdGhpcy5faGFzTWFya3MgPSBmYWxzZTtcbiAgICB0aGlzLl9tYXJrZXIgPSBlbXB0eU1hcmtlcjtcbiAgICB0aGlzLnNjb3JlID0gMDtcbiAgICB0aGlzLnRleHQgPSBcIlwiO1xuICAgIGlmIChpc1VuZGVmaW5lZCh2YWx1ZXMpKSB7XG4gICAgICAgIHZhbHVlcyA9IHt9O1xuICAgIH1cbiAgICBpZiAoIWlzVW5kZWZpbmVkKHZhbHVlcy5zY29yZSkpIHtcbiAgICAgICAgdGhpcy5zZXRTY29yZSh2YWx1ZXMuc2NvcmUpO1xuICAgIH1cbiAgICBpZiAoIWlzVW5kZWZpbmVkKHZhbHVlcy50ZXh0KSkge1xuICAgICAgICB0aGlzLnNldFRleHQodmFsdWVzLnRleHQpO1xuICAgIH1cbn07XG4vKipcbiAqIENoZWNrIGlmIGEgc2NvcmUgaXMgYXZhaWxhYmxlLlxuICogQHJldHVybnMge2Jvb2xlYW59IFdoZXRoZXIgb3Igbm90IGEgc2NvcmUgaXMgYXZhaWxhYmxlLlxuICovXG5Bc3Nlc3NtZW50UmVzdWx0LnByb3RvdHlwZS5oYXNTY29yZSA9IGZ1bmN0aW9uICgpIHtcbiAgICByZXR1cm4gdGhpcy5faGFzU2NvcmU7XG59O1xuLyoqXG4gKiBHZXQgdGhlIGF2YWlsYWJsZSBzY29yZVxuICogQHJldHVybnMge251bWJlcn0gVGhlIHNjb3JlIGFzc29jaWF0ZWQgd2l0aCB0aGUgQXNzZXNzbWVudFJlc3VsdC5cbiAqL1xuQXNzZXNzbWVudFJlc3VsdC5wcm90b3R5cGUuZ2V0U2NvcmUgPSBmdW5jdGlvbiAoKSB7XG4gICAgcmV0dXJuIHRoaXMuc2NvcmU7XG59O1xuLyoqXG4gKiBTZXQgdGhlIHNjb3JlIGZvciB0aGUgYXNzZXNzbWVudC5cbiAqIEBwYXJhbSB7bnVtYmVyfSBzY29yZSBUaGUgc2NvcmUgdG8gYmUgdXNlZCBmb3IgdGhlIHNjb3JlIHByb3BlcnR5XG4gKiBAcmV0dXJucyB7dm9pZH1cbiAqL1xuQXNzZXNzbWVudFJlc3VsdC5wcm90b3R5cGUuc2V0U2NvcmUgPSBmdW5jdGlvbiAoc2NvcmUpIHtcbiAgICBpZiAoaXNOdW1iZXIoc2NvcmUpKSB7XG4gICAgICAgIHRoaXMuc2NvcmUgPSBzY29yZTtcbiAgICAgICAgdGhpcy5faGFzU2NvcmUgPSB0cnVlO1xuICAgIH1cbn07XG4vKipcbiAqIENoZWNrIGlmIGEgdGV4dCBpcyBhdmFpbGFibGUuXG4gKiBAcmV0dXJucyB7Ym9vbGVhbn0gV2hldGhlciBvciBub3QgYSB0ZXh0IGlzIGF2YWlsYWJsZS5cbiAqL1xuQXNzZXNzbWVudFJlc3VsdC5wcm90b3R5cGUuaGFzVGV4dCA9IGZ1bmN0aW9uICgpIHtcbiAgICByZXR1cm4gdGhpcy50ZXh0ICE9PSBcIlwiO1xufTtcbi8qKlxuICogR2V0IHRoZSBhdmFpbGFibGUgdGV4dFxuICogQHJldHVybnMge3N0cmluZ30gVGhlIHRleHQgYXNzb2NpYXRlZCB3aXRoIHRoZSBBc3Nlc3NtZW50UmVzdWx0LlxuICovXG5Bc3Nlc3NtZW50UmVzdWx0LnByb3RvdHlwZS5nZXRUZXh0ID0gZnVuY3Rpb24gKCkge1xuICAgIHJldHVybiB0aGlzLnRleHQ7XG59O1xuLyoqXG4gKiBTZXQgdGhlIHRleHQgZm9yIHRoZSBhc3Nlc3NtZW50LlxuICogQHBhcmFtIHtzdHJpbmd9IHRleHQgVGhlIHRleHQgdG8gYmUgdXNlZCBmb3IgdGhlIHRleHQgcHJvcGVydHlcbiAqIEByZXR1cm5zIHt2b2lkfVxuICovXG5Bc3Nlc3NtZW50UmVzdWx0LnByb3RvdHlwZS5zZXRUZXh0ID0gZnVuY3Rpb24gKHRleHQpIHtcbiAgICBpZiAoaXNVbmRlZmluZWQodGV4dCkpIHtcbiAgICAgICAgdGV4dCA9IFwiXCI7XG4gICAgfVxuICAgIHRoaXMudGV4dCA9IHRleHQ7XG59O1xuLyoqXG4gKiBTZXRzIHRoZSBpZGVudGlmaWVyXG4gKlxuICogQHBhcmFtIHtzdHJpbmd9IGlkZW50aWZpZXIgQW4gYWxwaGFudW1lcmljIGlkZW50aWZpZXIgZm9yIHRoaXMgcmVzdWx0LlxuICogQHJldHVybnMge3ZvaWR9XG4gKi9cbkFzc2Vzc21lbnRSZXN1bHQucHJvdG90eXBlLnNldElkZW50aWZpZXIgPSBmdW5jdGlvbiAoaWRlbnRpZmllcikge1xuICAgIHRoaXMuX2lkZW50aWZpZXIgPSBpZGVudGlmaWVyO1xufTtcbi8qKlxuICogR2V0cyB0aGUgaWRlbnRpZmllclxuICpcbiAqIEByZXR1cm5zIHtzdHJpbmd9IEFuIGFscGhhbnVtZXJpYyBpZGVudGlmaWVyIGZvciB0aGlzIHJlc3VsdC5cbiAqL1xuQXNzZXNzbWVudFJlc3VsdC5wcm90b3R5cGUuZ2V0SWRlbnRpZmllciA9IGZ1bmN0aW9uICgpIHtcbiAgICByZXR1cm4gdGhpcy5faWRlbnRpZmllcjtcbn07XG4vKipcbiAqIFNldHMgdGhlIG1hcmtlciwgYSBwdXJlIGZ1bmN0aW9uIHRoYXQgY2FuIHJldHVybiB0aGUgbWFya3MgZm9yIGEgZ2l2ZW4gUGFwZXJcbiAqXG4gKiBAcGFyYW0ge0Z1bmN0aW9ufSBtYXJrZXIgVGhlIG1hcmtlciB0byBzZXQuXG4gKiBAcmV0dXJucyB7dm9pZH1cbiAqL1xuQXNzZXNzbWVudFJlc3VsdC5wcm90b3R5cGUuc2V0TWFya2VyID0gZnVuY3Rpb24gKG1hcmtlcikge1xuICAgIHRoaXMuX21hcmtlciA9IG1hcmtlcjtcbn07XG4vKipcbiAqIFJldHVybnMgd2hldGhlciBvciBub3QgdGhpcyByZXN1bHQgaGFzIGEgbWFya2VyIHRoYXQgY2FuIGJlIHVzZWQgdG8gbWFyayBmb3IgYSBnaXZlbiBQYXBlclxuICpcbiAqIEByZXR1cm5zIHtib29sZWFufSBXaGV0aGVyIG9yIHRoaXMgcmVzdWx0IGhhcyBhIG1hcmtlci5cbiAqL1xuQXNzZXNzbWVudFJlc3VsdC5wcm90b3R5cGUuaGFzTWFya2VyID0gZnVuY3Rpb24gKCkge1xuICAgIHJldHVybiB0aGlzLl9oYXNNYXJrcyAmJiB0aGlzLl9tYXJrZXIgIT09IGVtcHR5TWFya2VyO1xufTtcbi8qKlxuICogR2V0cyB0aGUgbWFya2VyLCBhIHB1cmUgZnVuY3Rpb24gdGhhdCBjYW4gcmV0dXJuIHRoZSBtYXJrcyBmb3IgYSBnaXZlbiBQYXBlclxuICpcbiAqIEByZXR1cm5zIHtGdW5jdGlvbn0gVGhlIG1hcmtlci5cbiAqL1xuQXNzZXNzbWVudFJlc3VsdC5wcm90b3R5cGUuZ2V0TWFya2VyID0gZnVuY3Rpb24gKCkge1xuICAgIHJldHVybiB0aGlzLl9tYXJrZXI7XG59O1xuLyoqXG4gKiBTZXRzIHRoZSB2YWx1ZSBvZiBfaGFzTWFya3MgdG8gZGV0ZXJtaW5lIGlmIHRoZXJlIGlzIHNvbWV0aGluZyB0byBtYXJrLlxuICpcbiAqIEBwYXJhbSB7Ym9vbGVhbn0gaGFzTWFya3MgSXMgdGhlcmUgc29tZXRoaW5nIHRvIG1hcmsuXG4gKiBAcmV0dXJucyB7dm9pZH1cbiAqL1xuQXNzZXNzbWVudFJlc3VsdC5wcm90b3R5cGUuc2V0SGFzTWFya3MgPSBmdW5jdGlvbiAoaGFzTWFya3MpIHtcbiAgICB0aGlzLl9oYXNNYXJrcyA9IGhhc01hcmtzO1xufTtcbi8qKlxuICogUmV0dXJucyB0aGUgdmFsdWUgb2YgX2hhc01hcmtzIHRvIGRldGVybWluZSBpZiB0aGVyZSBpcyBzb21ldGhpbmcgdG8gbWFyay5cbiAqXG4gKiBAcmV0dXJucyB7Ym9vbGVhbn0gSXMgdGhlcmUgc29tZXRoaW5nIHRvIG1hcmsuXG4gKi9cbkFzc2Vzc21lbnRSZXN1bHQucHJvdG90eXBlLmhhc01hcmtzID0gZnVuY3Rpb24gKCkge1xuICAgIHJldHVybiB0aGlzLl9oYXNNYXJrcztcbn07XG5tb2R1bGUuZXhwb3J0cyA9IEFzc2Vzc21lbnRSZXN1bHQ7XG4vLyMgc291cmNlTWFwcGluZ1VSTD1Bc3Nlc3NtZW50UmVzdWx0LmpzLm1hcFxuLy8jIHNvdXJjZU1hcHBpbmdVUkw9QXNzZXNzbWVudFJlc3VsdC5qcy5tYXBcbiJdfQ==
