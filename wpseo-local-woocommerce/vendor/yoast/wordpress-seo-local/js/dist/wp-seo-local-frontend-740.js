(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
'use strict';

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

// ==ClosureCompiler==
// @compilation_level ADVANCED_OPTIMIZATIONS
// @externs_url http://closure-compiler.googlecode.com/svn/trunk/contrib/externs/maps/google_maps_api_v3_3.js
// ==/ClosureCompiler==

/**
 * @name MarkerClusterer for Google Maps v3
 * @version version 1.0.1
 * @author Luke Mahe
 * @fileoverview
 * The library creates and manages per-zoom-level clusters for large amounts of
 * markers.
 * <br/>
 * This is a v3 implementation of the
 * <a href="http://gmaps-utility-library-dev.googlecode.com/svn/tags/markerclusterer/"
 * >v2 MarkerClusterer</a>.
 */

/**
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * A Marker Clusterer that clusters markers.
 *
 * @param {google.maps.Map} map The Google map to attach to.
 * @param {Array.<google.maps.Marker>=} opt_markers Optional markers to add to
 *   the cluster.
 * @param {Object=} opt_options support the following options:
 *     'gridSize': (number) The grid size of a cluster in pixels.
 *     'maxZoom': (number) The maximum zoom level that a marker can be part of a
 *                cluster.
 *     'zoomOnClick': (boolean) Whether the default behaviour of clicking on a
 *                    cluster is to zoom into it.
 *     'imagePath': (string) The base URL where the images representing
 *                  clusters will be found. The full URL will be:
 *                  {imagePath}[1-5].{imageExtension}
 *                  Default: '../images/m'.
 *     'imageExtension': (string) The suffix for images URL representing
 *                       clusters will be found. See _imagePath_ for details.
 *                       Default: 'png'.
 *     'averageCenter': (boolean) Whether the center of each cluster should be
 *                      the average of all markers in the cluster.
 *     'minimumClusterSize': (number) The minimum number of markers to be in a
 *                           cluster before the markers are hidden and a count
 *                           is shown.
 *     'styles': (object) An object that has style properties:
 *       'url': (string) The image url.
 *       'height': (number) The image height.
 *       'width': (number) The image width.
 *       'anchor': (Array) The anchor position of the label text.
 *       'textColor': (string) The text color.
 *       'textSize': (number) The text size.
 *       'backgroundPosition': (string) The position of the backgound x, y.
 * @constructor
 * @extends google.maps.OverlayView
 */
function MarkerClusterer(map, opt_markers, opt_options) {
    // MarkerClusterer implements google.maps.OverlayView interface. We use the
    // extend function to extend MarkerClusterer with google.maps.OverlayView
    // because it might not always be available when the code is defined so we
    // look for it at the last possible moment. If it doesn't exist now then
    // there is no point going ahead :)
    this.extend(MarkerClusterer, google.maps.OverlayView);
    this.map_ = map;

    /**
     * @type {Array.<google.maps.Marker>}
     * @private
     */
    this.markers_ = [];

    /**
     *  @type {Array.<Cluster>}
     */
    this.clusters_ = [];

    this.sizes = [53, 56, 66, 78, 90];

    /**
     * @private
     */
    this.styles_ = [];

    /**
     * @type {boolean}
     * @private
     */
    this.ready_ = false;

    var options = opt_options || {};

    /**
     * @type {number}
     * @private
     */
    this.gridSize_ = options['gridSize'] || 60;

    /**
     * @private
     */
    this.minClusterSize_ = options['minimumClusterSize'] || 2;

    /**
     * @type {?number}
     * @private
     */
    this.maxZoom_ = options['maxZoom'] || null;

    this.styles_ = options['styles'] || [];

    /**
     * @type {string}
     * @private
     */
    this.imagePath_ = options['imagePath'] || this.MARKER_CLUSTER_IMAGE_PATH_;

    /**
     * @type {string}
     * @private
     */
    this.imageExtension_ = options['imageExtension'] || this.MARKER_CLUSTER_IMAGE_EXTENSION_;

    /**
     * @type {boolean}
     * @private
     */
    this.zoomOnClick_ = true;

    if (options['zoomOnClick'] != undefined) {
        this.zoomOnClick_ = options['zoomOnClick'];
    }

    /**
     * @type {boolean}
     * @private
     */
    this.averageCenter_ = false;

    if (options['averageCenter'] != undefined) {
        this.averageCenter_ = options['averageCenter'];
    }

    this.setupStyles_();

    this.setMap(map);

    /**
     * @type {number}
     * @private
     */
    this.prevZoom_ = this.map_.getZoom();

    // Add the map event listeners
    var that = this;
    google.maps.event.addListener(this.map_, 'zoom_changed', function () {
        // Determines map type and prevent illegal zoom levels
        var zoom = that.map_.getZoom();
        var minZoom = that.map_.minZoom || 0;
        var maxZoom = Math.min(that.map_.maxZoom || 100, that.map_.mapTypes[that.map_.getMapTypeId()].maxZoom);
        zoom = Math.min(Math.max(zoom, minZoom), maxZoom);

        if (that.prevZoom_ != zoom) {
            that.prevZoom_ = zoom;
            that.resetViewport();
        }
    });

    google.maps.event.addListener(this.map_, 'idle', function () {
        that.redraw();
    });

    // Finally, add the markers
    if (opt_markers && (opt_markers.length || Object.keys(opt_markers).length)) {
        this.addMarkers(opt_markers, false);
    }
}

/**
 * The marker cluster image path.
 *
 * @type {string}
 * @private
 */
MarkerClusterer.prototype.MARKER_CLUSTER_IMAGE_PATH_ = '../images/m';

/**
 * The marker cluster image path.
 *
 * @type {string}
 * @private
 */
MarkerClusterer.prototype.MARKER_CLUSTER_IMAGE_EXTENSION_ = 'png';

/**
 * Extends a objects prototype by anothers.
 *
 * @param {Object} obj1 The object to be extended.
 * @param {Object} obj2 The object to extend with.
 * @return {Object} The new extended object.
 * @ignore
 */
MarkerClusterer.prototype.extend = function (obj1, obj2) {
    return function (object) {
        for (var property in object.prototype) {
            this.prototype[property] = object.prototype[property];
        }
        return this;
    }.apply(obj1, [obj2]);
};

/**
 * Implementaion of the interface method.
 * @ignore
 */
MarkerClusterer.prototype.onAdd = function () {
    this.setReady_(true);
};

/**
 * Implementaion of the interface method.
 * @ignore
 */
MarkerClusterer.prototype.draw = function () {};

/**
 * Sets up the styles object.
 *
 * @private
 */
MarkerClusterer.prototype.setupStyles_ = function () {
    if (this.styles_.length) {
        return;
    }

    for (var i = 0, size; size = this.sizes[i]; i++) {
        this.styles_.push({
            url: this.imagePath_ + (i + 1) + '.' + this.imageExtension_,
            height: size,
            width: size
        });
    }
};

/**
 *  Fit the map to the bounds of the markers in the clusterer.
 */
MarkerClusterer.prototype.fitMapToMarkers = function () {
    var markers = this.getMarkers();
    var bounds = new google.maps.LatLngBounds();
    for (var i = 0, marker; marker = markers[i]; i++) {
        bounds.extend(marker.getPosition());
    }

    this.map_.fitBounds(bounds);
};

/**
 *  Sets the styles.
 *
 *  @param {Object} styles The style to set.
 */
MarkerClusterer.prototype.setStyles = function (styles) {
    this.styles_ = styles;
};

/**
 *  Gets the styles.
 *
 *  @return {Object} The styles object.
 */
MarkerClusterer.prototype.getStyles = function () {
    return this.styles_;
};

/**
 * Whether zoom on click is set.
 *
 * @return {boolean} True if zoomOnClick_ is set.
 */
MarkerClusterer.prototype.isZoomOnClick = function () {
    return this.zoomOnClick_;
};

/**
 * Whether average center is set.
 *
 * @return {boolean} True if averageCenter_ is set.
 */
MarkerClusterer.prototype.isAverageCenter = function () {
    return this.averageCenter_;
};

/**
 *  Returns the array of markers in the clusterer.
 *
 *  @return {Array.<google.maps.Marker>} The markers.
 */
MarkerClusterer.prototype.getMarkers = function () {
    return this.markers_;
};

/**
 *  Returns the number of markers in the clusterer
 *
 *  @return {Number} The number of markers.
 */
MarkerClusterer.prototype.getTotalMarkers = function () {
    return this.markers_.length;
};

/**
 *  Sets the max zoom for the clusterer.
 *
 *  @param {number} maxZoom The max zoom level.
 */
MarkerClusterer.prototype.setMaxZoom = function (maxZoom) {
    this.maxZoom_ = maxZoom;
};

/**
 *  Gets the max zoom for the clusterer.
 *
 *  @return {number} The max zoom level.
 */
MarkerClusterer.prototype.getMaxZoom = function () {
    return this.maxZoom_;
};

/**
 *  The function for calculating the cluster icon image.
 *
 *  @param {Array.<google.maps.Marker>} markers The markers in the clusterer.
 *  @param {number} numStyles The number of styles available.
 *  @return {Object} A object properties: 'text' (string) and 'index' (number).
 *  @private
 */
MarkerClusterer.prototype.calculator_ = function (markers, numStyles) {
    var index = 0;
    var count = markers.length;
    var dv = count;
    while (dv !== 0) {
        dv = parseInt(dv / 10, 10);
        index++;
    }

    index = Math.min(index, numStyles);
    return {
        text: count,
        index: index
    };
};

/**
 * Set the calculator function.
 *
 * @param {function(Array, number)} calculator The function to set as the
 *     calculator. The function should return a object properties:
 *     'text' (string) and 'index' (number).
 *
 */
MarkerClusterer.prototype.setCalculator = function (calculator) {
    this.calculator_ = calculator;
};

/**
 * Get the calculator function.
 *
 * @return {function(Array, number)} the calculator function.
 */
MarkerClusterer.prototype.getCalculator = function () {
    return this.calculator_;
};

/**
 * Add an array of markers to the clusterer.
 *
 * @param {Array.<google.maps.Marker>} markers The markers to add.
 * @param {boolean=} opt_nodraw Whether to redraw the clusters.
 */
MarkerClusterer.prototype.addMarkers = function (markers, opt_nodraw) {
    if (markers.length) {
        for (var i = 0, marker; marker = markers[i]; i++) {
            this.pushMarkerTo_(marker);
        }
    } else if (Object.keys(markers).length) {
        for (var marker in markers) {
            this.pushMarkerTo_(markers[marker]);
        }
    }
    if (!opt_nodraw) {
        this.redraw();
    }
};

/**
 * Pushes a marker to the clusterer.
 *
 * @param {google.maps.Marker} marker The marker to add.
 * @private
 */
MarkerClusterer.prototype.pushMarkerTo_ = function (marker) {
    marker.isAdded = false;
    if (marker['draggable']) {
        // If the marker is draggable add a listener so we update the clusters on
        // the drag end.
        var that = this;
        google.maps.event.addListener(marker, 'dragend', function () {
            marker.isAdded = false;
            that.repaint();
        });
    }
    this.markers_.push(marker);
};

/**
 * Adds a marker to the clusterer and redraws if needed.
 *
 * @param {google.maps.Marker} marker The marker to add.
 * @param {boolean=} opt_nodraw Whether to redraw the clusters.
 */
MarkerClusterer.prototype.addMarker = function (marker, opt_nodraw) {
    this.pushMarkerTo_(marker);
    if (!opt_nodraw) {
        this.redraw();
    }
};

/**
 * Removes a marker and returns true if removed, false if not
 *
 * @param {google.maps.Marker} marker The marker to remove
 * @return {boolean} Whether the marker was removed or not
 * @private
 */
MarkerClusterer.prototype.removeMarker_ = function (marker) {
    var index = -1;
    if (this.markers_.indexOf) {
        index = this.markers_.indexOf(marker);
    } else {
        for (var i = 0, m; m = this.markers_[i]; i++) {
            if (m == marker) {
                index = i;
                break;
            }
        }
    }

    if (index == -1) {
        // Marker is not in our list of markers.
        return false;
    }

    marker.setMap(null);

    this.markers_.splice(index, 1);

    return true;
};

/**
 * Remove a marker from the cluster.
 *
 * @param {google.maps.Marker} marker The marker to remove.
 * @param {boolean=} opt_nodraw Optional boolean to force no redraw.
 * @return {boolean} True if the marker was removed.
 */
MarkerClusterer.prototype.removeMarker = function (marker, opt_nodraw) {
    var removed = this.removeMarker_(marker);

    if (!opt_nodraw && removed) {
        this.resetViewport();
        this.redraw();
        return true;
    } else {
        return false;
    }
};

/**
 * Removes an array of markers from the cluster.
 *
 * @param {Array.<google.maps.Marker>} markers The markers to remove.
 * @param {boolean=} opt_nodraw Optional boolean to force no redraw.
 */
MarkerClusterer.prototype.removeMarkers = function (markers, opt_nodraw) {
    // create a local copy of markers if required
    // (removeMarker_ modifies the getMarkers() array in place)
    var markersCopy = markers === this.getMarkers() ? markers.slice() : markers;
    var removed = false;

    for (var i = 0, marker; marker = markersCopy[i]; i++) {
        var r = this.removeMarker_(marker);
        removed = removed || r;
    }

    if (!opt_nodraw && removed) {
        this.resetViewport();
        this.redraw();
        return true;
    }
};

/**
 * Sets the clusterer's ready state.
 *
 * @param {boolean} ready The state.
 * @private
 */
MarkerClusterer.prototype.setReady_ = function (ready) {
    if (!this.ready_) {
        this.ready_ = ready;
        this.createClusters_();
    }
};

/**
 * Returns the number of clusters in the clusterer.
 *
 * @return {number} The number of clusters.
 */
MarkerClusterer.prototype.getTotalClusters = function () {
    return this.clusters_.length;
};

/**
 * Returns the google map that the clusterer is associated with.
 *
 * @return {google.maps.Map} The map.
 */
MarkerClusterer.prototype.getMap = function () {
    return this.map_;
};

/**
 * Sets the google map that the clusterer is associated with.
 *
 * @param {google.maps.Map} map The map.
 */
MarkerClusterer.prototype.setMap = function (map) {
    this.map_ = map;
};

/**
 * Returns the size of the grid.
 *
 * @return {number} The grid size.
 */
MarkerClusterer.prototype.getGridSize = function () {
    return this.gridSize_;
};

/**
 * Sets the size of the grid.
 *
 * @param {number} size The grid size.
 */
MarkerClusterer.prototype.setGridSize = function (size) {
    this.gridSize_ = size;
};

/**
 * Returns the min cluster size.
 *
 * @return {number} The grid size.
 */
MarkerClusterer.prototype.getMinClusterSize = function () {
    return this.minClusterSize_;
};

/**
 * Sets the min cluster size.
 *
 * @param {number} size The grid size.
 */
MarkerClusterer.prototype.setMinClusterSize = function (size) {
    this.minClusterSize_ = size;
};

/**
 * Extends a bounds object by the grid size.
 *
 * @param {google.maps.LatLngBounds} bounds The bounds to extend.
 * @return {google.maps.LatLngBounds} The extended bounds.
 */
MarkerClusterer.prototype.getExtendedBounds = function (bounds) {
    var projection = this.getProjection();

    // Turn the bounds into latlng.
    var tr = new google.maps.LatLng(bounds.getNorthEast().lat(), bounds.getNorthEast().lng());
    var bl = new google.maps.LatLng(bounds.getSouthWest().lat(), bounds.getSouthWest().lng());

    // Convert the points to pixels and the extend out by the grid size.
    var trPix = projection.fromLatLngToDivPixel(tr);
    trPix.x += this.gridSize_;
    trPix.y -= this.gridSize_;

    var blPix = projection.fromLatLngToDivPixel(bl);
    blPix.x -= this.gridSize_;
    blPix.y += this.gridSize_;

    // Convert the pixel points back to LatLng
    var ne = projection.fromDivPixelToLatLng(trPix);
    var sw = projection.fromDivPixelToLatLng(blPix);

    // Extend the bounds to contain the new bounds.
    bounds.extend(ne);
    bounds.extend(sw);

    return bounds;
};

/**
 * Determins if a marker is contained in a bounds.
 *
 * @param {google.maps.Marker} marker The marker to check.
 * @param {google.maps.LatLngBounds} bounds The bounds to check against.
 * @return {boolean} True if the marker is in the bounds.
 * @private
 */
MarkerClusterer.prototype.isMarkerInBounds_ = function (marker, bounds) {
    return bounds.contains(marker.getPosition());
};

/**
 * Clears all clusters and markers from the clusterer.
 */
MarkerClusterer.prototype.clearMarkers = function () {
    this.resetViewport(true);

    // Set the markers a empty array.
    this.markers_ = [];
};

/**
 * Clears all existing clusters and recreates them.
 * @param {boolean} opt_hide To also hide the marker.
 */
MarkerClusterer.prototype.resetViewport = function (opt_hide) {
    // Remove all the clusters
    for (var i = 0, cluster; cluster = this.clusters_[i]; i++) {
        cluster.remove();
    }

    // Reset the markers to not be added and to be invisible.
    for (var i = 0, marker; marker = this.markers_[i]; i++) {
        marker.isAdded = false;
        if (opt_hide) {
            marker.setMap(null);
        }
    }

    this.clusters_ = [];
};

/**
 *
 */
MarkerClusterer.prototype.repaint = function () {
    var oldClusters = this.clusters_.slice();
    this.clusters_.length = 0;
    this.resetViewport();
    this.redraw();

    // Remove the old clusters.
    // Do it in a timeout so the other clusters have been drawn first.
    window.setTimeout(function () {
        for (var i = 0, cluster; cluster = oldClusters[i]; i++) {
            cluster.remove();
        }
    }, 0);
};

/**
 * Redraws the clusters.
 */
MarkerClusterer.prototype.redraw = function () {
    this.createClusters_();
};

/**
 * Calculates the distance between two latlng locations in km.
 * @see http://www.movable-type.co.uk/scripts/latlong.html
 *
 * @param {google.maps.LatLng} p1 The first lat lng point.
 * @param {google.maps.LatLng} p2 The second lat lng point.
 * @return {number} The distance between the two points in km.
 * @private
 */
MarkerClusterer.prototype.distanceBetweenPoints_ = function (p1, p2) {
    if (!p1 || !p2) {
        return 0;
    }

    var R = 6371; // Radius of the Earth in km
    var dLat = (p2.lat() - p1.lat()) * Math.PI / 180;
    var dLon = (p2.lng() - p1.lng()) * Math.PI / 180;
    var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) + Math.cos(p1.lat() * Math.PI / 180) * Math.cos(p2.lat() * Math.PI / 180) * Math.sin(dLon / 2) * Math.sin(dLon / 2);
    var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    var d = R * c;
    return d;
};

/**
 * Add a marker to a cluster, or creates a new cluster.
 *
 * @param {google.maps.Marker} marker The marker to add.
 * @private
 */
MarkerClusterer.prototype.addToClosestCluster_ = function (marker) {
    var distance = 40000; // Some large number
    var clusterToAddTo = null;
    var pos = marker.getPosition();
    for (var i = 0, cluster; cluster = this.clusters_[i]; i++) {
        var center = cluster.getCenter();
        if (center) {
            var d = this.distanceBetweenPoints_(center, marker.getPosition());
            if (d < distance) {
                distance = d;
                clusterToAddTo = cluster;
            }
        }
    }

    if (clusterToAddTo && clusterToAddTo.isMarkerInClusterBounds(marker)) {
        clusterToAddTo.addMarker(marker);
    } else {
        var cluster = new Cluster(this);
        cluster.addMarker(marker);
        this.clusters_.push(cluster);
    }
};

/**
 * Creates the clusters.
 *
 * @private
 */
MarkerClusterer.prototype.createClusters_ = function () {
    if (!this.ready_) {
        return;
    }

    // Get our current map view bounds.
    // Create a new bounds object so we don't affect the map.
    var mapBounds = new google.maps.LatLngBounds(this.map_.getBounds().getSouthWest(), this.map_.getBounds().getNorthEast());
    var bounds = this.getExtendedBounds(mapBounds);

    for (var i = 0, marker; marker = this.markers_[i]; i++) {
        if (!marker.isAdded && this.isMarkerInBounds_(marker, bounds)) {
            this.addToClosestCluster_(marker);
        }
    }
};

/**
 * A cluster that contains markers.
 *
 * @param {MarkerClusterer} markerClusterer The markerclusterer that this
 *     cluster is associated with.
 * @constructor
 * @ignore
 */
function Cluster(markerClusterer) {
    this.markerClusterer_ = markerClusterer;
    this.map_ = markerClusterer.getMap();
    this.gridSize_ = markerClusterer.getGridSize();
    this.minClusterSize_ = markerClusterer.getMinClusterSize();
    this.averageCenter_ = markerClusterer.isAverageCenter();
    this.center_ = null;
    this.markers_ = [];
    this.bounds_ = null;
    this.clusterIcon_ = new ClusterIcon(this, markerClusterer.getStyles(), markerClusterer.getGridSize());
}

/**
 * Determins if a marker is already added to the cluster.
 *
 * @param {google.maps.Marker} marker The marker to check.
 * @return {boolean} True if the marker is already added.
 */
Cluster.prototype.isMarkerAlreadyAdded = function (marker) {
    if (this.markers_.indexOf) {
        return this.markers_.indexOf(marker) != -1;
    } else {
        for (var i = 0, m; m = this.markers_[i]; i++) {
            if (m == marker) {
                return true;
            }
        }
    }
    return false;
};

/**
 * Add a marker the cluster.
 *
 * @param {google.maps.Marker} marker The marker to add.
 * @return {boolean} True if the marker was added.
 */
Cluster.prototype.addMarker = function (marker) {
    if (this.isMarkerAlreadyAdded(marker)) {
        return false;
    }

    if (!this.center_) {
        this.center_ = marker.getPosition();
        this.calculateBounds_();
    } else {
        if (this.averageCenter_) {
            var l = this.markers_.length + 1;
            var lat = (this.center_.lat() * (l - 1) + marker.getPosition().lat()) / l;
            var lng = (this.center_.lng() * (l - 1) + marker.getPosition().lng()) / l;
            this.center_ = new google.maps.LatLng(lat, lng);
            this.calculateBounds_();
        }
    }

    marker.isAdded = true;
    this.markers_.push(marker);

    var len = this.markers_.length;
    if (len < this.minClusterSize_ && marker.getMap() != this.map_) {
        // Min cluster size not reached so show the marker.
        marker.setMap(this.map_);
    }

    if (len == this.minClusterSize_) {
        // Hide the markers that were showing.
        for (var i = 0; i < len; i++) {
            this.markers_[i].setMap(null);
        }
    }

    if (len >= this.minClusterSize_) {
        marker.setMap(null);
    }

    this.updateIcon();
    return true;
};

/**
 * Returns the marker clusterer that the cluster is associated with.
 *
 * @return {MarkerClusterer} The associated marker clusterer.
 */
Cluster.prototype.getMarkerClusterer = function () {
    return this.markerClusterer_;
};

/**
 * Returns the bounds of the cluster.
 *
 * @return {google.maps.LatLngBounds} the cluster bounds.
 */
Cluster.prototype.getBounds = function () {
    var bounds = new google.maps.LatLngBounds(this.center_, this.center_);
    var markers = this.getMarkers();
    for (var i = 0, marker; marker = markers[i]; i++) {
        bounds.extend(marker.getPosition());
    }
    return bounds;
};

/**
 * Removes the cluster
 */
Cluster.prototype.remove = function () {
    this.clusterIcon_.remove();
    this.markers_.length = 0;
    delete this.markers_;
};

/**
 * Returns the number of markers in the cluster.
 *
 * @return {number} The number of markers in the cluster.
 */
Cluster.prototype.getSize = function () {
    return this.markers_.length;
};

/**
 * Returns a list of the markers in the cluster.
 *
 * @return {Array.<google.maps.Marker>} The markers in the cluster.
 */
Cluster.prototype.getMarkers = function () {
    return this.markers_;
};

/**
 * Returns the center of the cluster.
 *
 * @return {google.maps.LatLng} The cluster center.
 */
Cluster.prototype.getCenter = function () {
    return this.center_;
};

/**
 * Calculated the extended bounds of the cluster with the grid.
 *
 * @private
 */
Cluster.prototype.calculateBounds_ = function () {
    var bounds = new google.maps.LatLngBounds(this.center_, this.center_);
    this.bounds_ = this.markerClusterer_.getExtendedBounds(bounds);
};

/**
 * Determines if a marker lies in the clusters bounds.
 *
 * @param {google.maps.Marker} marker The marker to check.
 * @return {boolean} True if the marker lies in the bounds.
 */
Cluster.prototype.isMarkerInClusterBounds = function (marker) {
    return this.bounds_.contains(marker.getPosition());
};

/**
 * Returns the map that the cluster is associated with.
 *
 * @return {google.maps.Map} The map.
 */
Cluster.prototype.getMap = function () {
    return this.map_;
};

/**
 * Updates the cluster icon
 */
Cluster.prototype.updateIcon = function () {
    var zoom = this.map_.getZoom();
    var mz = this.markerClusterer_.getMaxZoom();

    if (mz && zoom > mz) {
        // The zoom is greater than our max zoom so show all the markers in cluster.
        for (var i = 0, marker; marker = this.markers_[i]; i++) {
            marker.setMap(this.map_);
        }
        return;
    }

    if (this.markers_.length < this.minClusterSize_) {
        // Min cluster size not yet reached.
        this.clusterIcon_.hide();
        return;
    }

    var numStyles = this.markerClusterer_.getStyles().length;
    var sums = this.markerClusterer_.getCalculator()(this.markers_, numStyles);
    this.clusterIcon_.setCenter(this.center_);
    this.clusterIcon_.setSums(sums);
    this.clusterIcon_.show();
};

/**
 * A cluster icon
 *
 * @param {Cluster} cluster The cluster to be associated with.
 * @param {Object} styles An object that has style properties:
 *     'url': (string) The image url.
 *     'height': (number) The image height.
 *     'width': (number) The image width.
 *     'anchor': (Array) The anchor position of the label text.
 *     'textColor': (string) The text color.
 *     'textSize': (number) The text size.
 *     'backgroundPosition: (string) The background postition x, y.
 * @param {number=} opt_padding Optional padding to apply to the cluster icon.
 * @constructor
 * @extends google.maps.OverlayView
 * @ignore
 */
function ClusterIcon(cluster, styles, opt_padding) {
    cluster.getMarkerClusterer().extend(ClusterIcon, google.maps.OverlayView);

    this.styles_ = styles;
    this.padding_ = opt_padding || 0;
    this.cluster_ = cluster;
    this.center_ = null;
    this.map_ = cluster.getMap();
    this.div_ = null;
    this.sums_ = null;
    this.visible_ = false;

    this.setMap(this.map_);
}

/**
 * Triggers the clusterclick event and zoom's if the option is set.
 */
ClusterIcon.prototype.triggerClusterClick = function () {
    var markerClusterer = this.cluster_.getMarkerClusterer();

    // Trigger the clusterclick event.
    google.maps.event.trigger(markerClusterer.map_, 'clusterclick', this.cluster_);

    if (markerClusterer.isZoomOnClick()) {
        // Zoom into the cluster.
        this.map_.fitBounds(this.cluster_.getBounds());
    }
};

/**
 * Adding the cluster icon to the dom.
 * @ignore
 */
ClusterIcon.prototype.onAdd = function () {
    this.div_ = document.createElement('DIV');
    if (this.visible_) {
        var pos = this.getPosFromLatLng_(this.center_);
        this.div_.style.cssText = this.createCss(pos);
        this.div_.innerHTML = this.sums_.text;
    }

    var panes = this.getPanes();
    panes.overlayMouseTarget.appendChild(this.div_);

    var that = this;
    google.maps.event.addDomListener(this.div_, 'click', function () {
        that.triggerClusterClick();
    });
};

/**
 * Returns the position to place the div dending on the latlng.
 *
 * @param {google.maps.LatLng} latlng The position in latlng.
 * @return {google.maps.Point} The position in pixels.
 * @private
 */
ClusterIcon.prototype.getPosFromLatLng_ = function (latlng) {
    var pos = this.getProjection().fromLatLngToDivPixel(latlng);
    pos.x -= parseInt(this.width_ / 2, 10);
    pos.y -= parseInt(this.height_ / 2, 10);
    return pos;
};

/**
 * Draw the icon.
 * @ignore
 */
ClusterIcon.prototype.draw = function () {
    if (this.visible_) {
        var pos = this.getPosFromLatLng_(this.center_);
        this.div_.style.top = pos.y + 'px';
        this.div_.style.left = pos.x + 'px';
    }
};

/**
 * Hide the icon.
 */
ClusterIcon.prototype.hide = function () {
    if (this.div_) {
        this.div_.style.display = 'none';
    }
    this.visible_ = false;
};

/**
 * Position and show the icon.
 */
ClusterIcon.prototype.show = function () {
    if (this.div_) {
        var pos = this.getPosFromLatLng_(this.center_);
        this.div_.style.cssText = this.createCss(pos);
        this.div_.style.display = '';
    }
    this.visible_ = true;
};

/**
 * Remove the icon from the map
 */
ClusterIcon.prototype.remove = function () {
    this.setMap(null);
};

/**
 * Implementation of the onRemove interface.
 * @ignore
 */
ClusterIcon.prototype.onRemove = function () {
    if (this.div_ && this.div_.parentNode) {
        this.hide();
        this.div_.parentNode.removeChild(this.div_);
        this.div_ = null;
    }
};

/**
 * Set the sums of the icon.
 *
 * @param {Object} sums The sums containing:
 *   'text': (string) The text to display in the icon.
 *   'index': (number) The style index of the icon.
 */
ClusterIcon.prototype.setSums = function (sums) {
    this.sums_ = sums;
    this.text_ = sums.text;
    this.index_ = sums.index;
    if (this.div_) {
        this.div_.innerHTML = sums.text;
    }

    this.useStyle();
};

/**
 * Sets the icon to the the styles.
 */
ClusterIcon.prototype.useStyle = function () {
    var index = Math.max(0, this.sums_.index - 1);
    index = Math.min(this.styles_.length - 1, index);
    var style = this.styles_[index];
    this.url_ = style['url'];
    this.height_ = style['height'];
    this.width_ = style['width'];
    this.textColor_ = style['textColor'];
    this.anchor_ = style['anchor'];
    this.textSize_ = style['textSize'];
    this.backgroundPosition_ = style['backgroundPosition'];
};

/**
 * Sets the center of the icon.
 *
 * @param {google.maps.LatLng} center The latlng to set as the center.
 */
ClusterIcon.prototype.setCenter = function (center) {
    this.center_ = center;
};

/**
 * Create the css text based on the position of the icon.
 *
 * @param {google.maps.Point} pos The position.
 * @return {string} The css style text.
 */
ClusterIcon.prototype.createCss = function (pos) {
    var style = [];
    style.push('background-image:url(' + this.url_ + ');');
    var backgroundPosition = this.backgroundPosition_ ? this.backgroundPosition_ : '0 0';
    style.push('background-position:' + backgroundPosition + ';');

    if (_typeof(this.anchor_) === 'object') {
        if (typeof this.anchor_[0] === 'number' && this.anchor_[0] > 0 && this.anchor_[0] < this.height_) {
            style.push('height:' + (this.height_ - this.anchor_[0]) + 'px; padding-top:' + this.anchor_[0] + 'px;');
        } else {
            style.push('height:' + this.height_ + 'px; line-height:' + this.height_ + 'px;');
        }
        if (typeof this.anchor_[1] === 'number' && this.anchor_[1] > 0 && this.anchor_[1] < this.width_) {
            style.push('width:' + (this.width_ - this.anchor_[1]) + 'px; padding-left:' + this.anchor_[1] + 'px;');
        } else {
            style.push('width:' + this.width_ + 'px; text-align:center;');
        }
    } else {
        style.push('height:' + this.height_ + 'px; line-height:' + this.height_ + 'px; width:' + this.width_ + 'px; text-align:center;');
    }

    var txtColor = this.textColor_ ? this.textColor_ : 'black';
    var txtSize = this.textSize_ ? this.textSize_ : 11;

    style.push('cursor:pointer; top:' + pos.y + 'px; left:' + pos.x + 'px; color:' + txtColor + '; position:absolute; font-size:' + txtSize + 'px; font-family:Arial,sans-serif; font-weight:bold');
    return style.join('');
};

// Export Symbols for Closure
// If you are not going to compile with closure then you can remove the
// code below.
window['MarkerClusterer'] = MarkerClusterer;
MarkerClusterer.prototype['addMarker'] = MarkerClusterer.prototype.addMarker;
MarkerClusterer.prototype['addMarkers'] = MarkerClusterer.prototype.addMarkers;
MarkerClusterer.prototype['clearMarkers'] = MarkerClusterer.prototype.clearMarkers;
MarkerClusterer.prototype['fitMapToMarkers'] = MarkerClusterer.prototype.fitMapToMarkers;
MarkerClusterer.prototype['getCalculator'] = MarkerClusterer.prototype.getCalculator;
MarkerClusterer.prototype['getGridSize'] = MarkerClusterer.prototype.getGridSize;
MarkerClusterer.prototype['getExtendedBounds'] = MarkerClusterer.prototype.getExtendedBounds;
MarkerClusterer.prototype['getMap'] = MarkerClusterer.prototype.getMap;
MarkerClusterer.prototype['getMarkers'] = MarkerClusterer.prototype.getMarkers;
MarkerClusterer.prototype['getMaxZoom'] = MarkerClusterer.prototype.getMaxZoom;
MarkerClusterer.prototype['getStyles'] = MarkerClusterer.prototype.getStyles;
MarkerClusterer.prototype['getTotalClusters'] = MarkerClusterer.prototype.getTotalClusters;
MarkerClusterer.prototype['getTotalMarkers'] = MarkerClusterer.prototype.getTotalMarkers;
MarkerClusterer.prototype['redraw'] = MarkerClusterer.prototype.redraw;
MarkerClusterer.prototype['removeMarker'] = MarkerClusterer.prototype.removeMarker;
MarkerClusterer.prototype['removeMarkers'] = MarkerClusterer.prototype.removeMarkers;
MarkerClusterer.prototype['resetViewport'] = MarkerClusterer.prototype.resetViewport;
MarkerClusterer.prototype['repaint'] = MarkerClusterer.prototype.repaint;
MarkerClusterer.prototype['setCalculator'] = MarkerClusterer.prototype.setCalculator;
MarkerClusterer.prototype['setGridSize'] = MarkerClusterer.prototype.setGridSize;
MarkerClusterer.prototype['setMaxZoom'] = MarkerClusterer.prototype.setMaxZoom;
MarkerClusterer.prototype['onAdd'] = MarkerClusterer.prototype.onAdd;
MarkerClusterer.prototype['draw'] = MarkerClusterer.prototype.draw;

Cluster.prototype['getCenter'] = Cluster.prototype.getCenter;
Cluster.prototype['getSize'] = Cluster.prototype.getSize;
Cluster.prototype['getMarkers'] = Cluster.prototype.getMarkers;

ClusterIcon.prototype['onAdd'] = ClusterIcon.prototype.onAdd;
ClusterIcon.prototype['draw'] = ClusterIcon.prototype.draw;
ClusterIcon.prototype['onRemove'] = ClusterIcon.prototype.onRemove;

Object.keys = Object.keys || function (o) {
    var result = [];
    for (var name in o) {
        if (o.hasOwnProperty(name)) result.push(name);
    }
    return result;
};

},{}],2:[function(require,module,exports){
'use strict';

var wpseo_directions = [];
var wpseo_maps = [];
var markers = new Object();

var wpseo_directions = [];
var wpseo_maps = [];
var markers = new Object();

window.wpseo_show_map = function wpseo_show_map(location_data, counter, center_lat, center_long, zoom, map_style, scrollable, draggable, default_show_infowindow, is_admin, marker_clustering) {
    var bounds = new google.maps.LatLngBounds();
    var center = new google.maps.LatLng(center_lat, center_long);
    var mobileBreakpoint = 480;
    markers[counter] = [];

    var wpseo_map_options = {
        zoom: zoom,
        minZoom: 1,
        mapTypeControl: true,
        zoomControl: scrollable,
        streetViewControl: true,
        mapTypeId: google.maps.MapTypeId[map_style.toUpperCase()],
        scrollwheel: scrollable && window.innerWidth > mobileBreakpoint
    };

    // gestureHandling should only be set on devices that support touch.
    if (checkForTouch()) {
        wpseo_map_options.gestureHandling = draggable ? 'auto' : 'none';
    } else {
        wpseo_map_options.draggable = draggable;
    }

    // Set center
    if (zoom == -1) {
        for (var i = 0; i < location_data.length; i++) {
            var latLong = new google.maps.LatLng(location_data[i]["lat"], location_data[i]["long"]);
            bounds.extend(latLong);
        }

        center = bounds.getCenter();
    }
    wpseo_map_options.center = center;

    var map = new google.maps.Map(document.getElementById("map_canvas" + (counter != 0 ? '_' + counter : '')), wpseo_map_options);

    if (zoom == -1) {
        map.fitBounds(bounds);
    }

    // Set markers + info
    var infoWindow = new google.maps.InfoWindow({
        content: infoWindowHTML
    });

    for (var i = 0; i < location_data.length; i++) {
        // Create info window HTML
        var infoWindowHTML = getInfoBubbleText(location_data[i]["name"], location_data[i]["address"], location_data[i]["url"], location_data[i]["self_url"]);

        var latLong = new google.maps.LatLng(location_data[i]["lat"], location_data[i]["long"]);
        var icon = location_data[i]["custom_marker"];
        var categories = location_data[i]["categories"];

        markers[counter][i] = new google.maps.Marker({
            position: latLong,
            center: center,
            map: map,
            map_id: counter,
            html: infoWindowHTML,
            draggable: Boolean(is_admin),
            icon: typeof icon !== 'undefined' && icon || '',
            categories: typeof categories !== 'undefined' && categories || ''
        });
    }
    for (var i = 0; i < markers[counter].length; i++) {
        var marker = markers[counter][i];

        google.maps.event.addListener(marker, "click", function () {
            infoWindow.setContent(this.html);
            infoWindow.open(map, this);
        });

        google.maps.event.addListener(infoWindow, 'closeclick', function () {
            map.setCenter(this.getPosition());
        });

        google.maps.event.addListener(marker, 'dragend', function (event) {
            // If on a single location page in a multiple location setup.
            if (document.getElementById('wpseo_coordinates_lat') && document.getElementById('wpseo_coordinates_long')) {
                document.getElementById('wpseo_coordinates_lat').value = event.latLng.lat();
                document.getElementById('wpseo_coordinates_long').value = event.latLng.lng();
            }

            // If on the Yoast Local SEO settings page, using a single location.
            if (document.getElementById('location_coords_lat') && document.getElementById('location_coords_long')) {
                document.getElementById('location_coords_lat').value = event.latLng.lat();
                document.getElementById('location_coords_long').value = event.latLng.lng();
            }
        });
    }

    // If marker clustering is set, use it.
    if (marker_clustering) {
        new MarkerClusterer(map, markers[counter], { imagePath: wpseo_local_data.marker_cluster_image_path });
    }

    // If there is only one marker and the infowindow should be shown, make it so.
    if (markers[counter].length == 1 && default_show_infowindow) {
        infoWindow.setContent(markers[counter][0].html);
        infoWindow.open(map, marker);
    }

    return map;
};

window.checkForTouch = function () {
    return !!(navigator.userAgent.match(/Android/i) || navigator.userAgent.match(/webOS/i) || navigator.userAgent.match(/iPhone/i) || navigator.userAgent.match(/iPad/i) || navigator.userAgent.match(/iPod/i) || navigator.userAgent.match(/BlackBerry/i) || navigator.userAgent.match(/Windows Phone/i));
};

window.wpseo_get_directions = function (map, location_data, counter, show_route) {
    var directionsDisplay = '';

    if (show_route && location_data.length >= 1) {
        directionsDisplay = new google.maps.DirectionsRenderer();
        directionsDisplay.setMap(map);
        directionsDisplay.setPanel(document.getElementById("directions" + (counter != 0 ? '_' + counter : '')));
    }

    return directionsDisplay;
};

window.getInfoBubbleText = function (business_name, business_city_address, business_url, self_url) {
    var infoWindowHTML = '<div class="wpseo-info-window-wrapper">';

    var showSelfLink = false;
    if (self_url != undefined && wpseo_local_data.has_multiple_locations != '' && self_url != window.location.href) showSelfLink = true;

    if (showSelfLink) infoWindowHTML += '<a href="' + self_url + '">';
    infoWindowHTML += '<strong>' + business_name + '</strong>';
    if (showSelfLink) infoWindowHTML += '</a>';
    infoWindowHTML += '<br>';
    infoWindowHTML += business_city_address;

    infoWindowHTML += '</div>';

    return infoWindowHTML;
};

window.wpseo_calculate_route = function (map, dirDisplay, coords_lat, coords_long, counter) {
    if (document.getElementById('wpseo-sl-coords-lat') != null) coords_lat = document.getElementById('wpseo-sl-coords-lat').value;
    if (document.getElementById('wpseo-sl-coords-long') != null) coords_long = document.getElementById('wpseo-sl-coords-long').value;

    var start = document.getElementById("origin" + (counter != 0 ? "_" + counter : "")).value + ' ' + wpseo_local_data.default_country;
    var unit_system = google.maps.UnitSystem.METRIC;
    if (wpseo_local_data.unit_system == 'IMPERIAL') unit_system = google.maps.UnitSystem.IMPERIAL;

    // Clear all markers from the map, only show A and B
    for (var i = 0; i < markers.length; i++) {
        markers[i].setMap(null);
    }

    // Change button to link to Google Maps. iPhones and Android phones will automatically open them in Maps app, when available.
    if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
        var url = 'https://maps.google.com/maps?saddr=' + escape(start) + '&daddr=' + coords_lat + ',' + coords_long;
        window.open(url, '_blank');

        return false;
    } else {
        var latlng = new google.maps.LatLng(coords_lat, coords_long);

        var request = {
            origin: start,
            destination: latlng,
            provideRouteAlternatives: true,
            optimizeWaypoints: true,
            travelMode: google.maps.DirectionsTravelMode.DRIVING,
            unitSystem: unit_system
        };

        var directionsService = new google.maps.DirectionsService();

        directionsService.route(request, function (response, status2) {
            if (status2 == google.maps.DirectionsStatus.OK) {
                dirDisplay.setDirections(response);
            } else if (status2 == google.maps.DirectionsStatus.ZERO_RESULTS) {
                var noroute = document.getElementById('wpseo-noroute');
                noroute.setAttribute('style', 'clear: both; display: block;');
            }
        });
    }
};

window.wpseo_sl_show_route = function (obj, coords_lat, coords_long) {
    $ = jQuery;

    // Create hidden inputs to pass through the lat/long coordinates for which is needed for calculating the route.
    $('.wpseo-sl-coords').remove();
    var inputs = '<input type="hidden" class="wpseo-sl-coords" id="wpseo-sl-coords-lat" value="' + coords_lat + '">';
    inputs += '<input type="hidden" class="wpseo-sl-coords" id="wpseo-sl-coords-long" value="' + coords_long + '">';

    $('#wpseo-directions-form').append(inputs).submit();
    $('#wpseo-directions-wrapper').slideUp(function () {
        $(this).insertAfter($(obj).parents('.wpseo-result')).slideDown();
    });
};

window.wpseo_detect_location = function (event, target) {
    var searchInput = document.getElementById(target);
    if (null == searchInput) {
        searchInput = document.getElementById('origin');
    }

    if (navigator.geolocation && null != searchInput) {
        var clickedButton = event.target || event.srcElement;
        var originalImageSrc = clickedButton.getAttribute('src');
        var originalImageAltText = clickedButton.getAttribute('alt');
        var loadingAltText = clickedButton.getAttribute('data-loading-text');

        // Add spinner to the clicked button.
        clickedButton.setAttribute('src', wpseo_local_data.adminurl + 'images/loading.gif');
        clickedButton.setAttribute('alt', loadingAltText);

        navigator.geolocation.getCurrentPosition(function (position) {
            var geocoder = new google.maps.Geocoder();
            var latlng = {
                lat: parseFloat(position.coords.latitude),
                lng: parseFloat(position.coords.longitude)
            };

            geocoder.geocode({ 'location': latlng }, function (results, status) {
                if (status === google.maps.GeocoderStatus.OK) {
                    // Only enter detected location when there are results and no value yet is entered.
                    if (results.length > 0 && '' == searchInput.value) {
                        searchInput.value = results[0].formatted_address;
                    }
                }

                clickedButton.setAttribute('src', originalImageSrc);
                clickedButton.setAttribute('alt', originalImageAltText);
            });
        }, function (error) {
            var err = '[wpseo] Error detecting location: ';
            switch (error.code) {
                case error.TIMEOUT:
                    err += 'Timeout';
                    break;
                case error.POSITION_UNAVAILABLE:
                    err += 'Position unavailable';
                    break;
                case error.PERMISSION_DENIED:
                    err += 'Permission denied';
                    break;
                case error.UNKNOWN_ERROR:
                    err += 'Unknown error';
                    break;
            }

            if (typeof console != 'undefined') {
                console.log(err);
            }

            clickedButton.setAttribute('src', originalImageSrc);
            clickedButton.setAttribute('alt', originalImageAltText);
        });
    }
};

window.wpseo_current_location_buttons = document.getElementsByClassName('wpseo_use_current_location');
for (var i = 0; i < wpseo_current_location_buttons.length; i++) {
    wpseo_current_location_buttons[i].addEventListener('click', function (event) {
        var target = this.dataset.target;
        wpseo_detect_location(event, target);
    }, false);
}

window.filterMarkers = function (category, map_id) {
    for (i = 0; i < markers[map_id].length; i++) {
        marker = markers[map_id][i];

        // If is same category or category not picked
        if (marker.categories.hasOwnProperty(category) || category.length === 0) {
            marker.setVisible(true);
        }
        // Categories don't match
        else {
                marker.setVisible(false);
            }
    }
};

},{}]},{},[1,2])
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJqcy9zcmMvdmVuZG9yL21hcmtlcmNsdXN0ZXIuanMiLCJqcy9zcmMvd3Atc2VvLWxvY2FsLWZyb250ZW5kLmpzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7OztBQ0FBO0FBQ0E7QUFDQTtBQUNBOztBQUVBOzs7Ozs7Ozs7Ozs7O0FBYUE7Ozs7Ozs7Ozs7Ozs7O0FBZUE7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7O0FBbUNBLFNBQVMsZUFBVCxDQUF5QixHQUF6QixFQUE4QixXQUE5QixFQUEyQyxXQUEzQyxFQUF3RDtBQUNwRDtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsU0FBSyxNQUFMLENBQVksZUFBWixFQUE2QixPQUFPLElBQVAsQ0FBWSxXQUF6QztBQUNBLFNBQUssSUFBTCxHQUFZLEdBQVo7O0FBRUE7Ozs7QUFJQSxTQUFLLFFBQUwsR0FBZ0IsRUFBaEI7O0FBRUE7OztBQUdBLFNBQUssU0FBTCxHQUFpQixFQUFqQjs7QUFFQSxTQUFLLEtBQUwsR0FBYSxDQUFDLEVBQUQsRUFBSyxFQUFMLEVBQVMsRUFBVCxFQUFhLEVBQWIsRUFBaUIsRUFBakIsQ0FBYjs7QUFFQTs7O0FBR0EsU0FBSyxPQUFMLEdBQWUsRUFBZjs7QUFFQTs7OztBQUlBLFNBQUssTUFBTCxHQUFjLEtBQWQ7O0FBRUEsUUFBSSxVQUFVLGVBQWUsRUFBN0I7O0FBRUE7Ozs7QUFJQSxTQUFLLFNBQUwsR0FBaUIsUUFBUSxVQUFSLEtBQXVCLEVBQXhDOztBQUVBOzs7QUFHQSxTQUFLLGVBQUwsR0FBdUIsUUFBUSxvQkFBUixLQUFpQyxDQUF4RDs7QUFHQTs7OztBQUlBLFNBQUssUUFBTCxHQUFnQixRQUFRLFNBQVIsS0FBc0IsSUFBdEM7O0FBRUEsU0FBSyxPQUFMLEdBQWUsUUFBUSxRQUFSLEtBQXFCLEVBQXBDOztBQUVBOzs7O0FBSUEsU0FBSyxVQUFMLEdBQWtCLFFBQVEsV0FB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UUFBUSxNQUFSLEVBQWdCLENBQWhCLENBQVQ7O0FBRUE7QUFDQSxZQUFJLE9BQU8sVUFBUCxDQUFrQixjQUFsQixDQUFpQyxRQUFqQyxLQUE4QyxTQUFTLE1BQVQsS0FBb0IsQ0FBdEUsRUFBeUU7QUFDckUsbUJBQU8sVUFBUCxDQUFrQixJQUFsQjtBQUNIO0FBQ0Q7QUFIQSxhQUlLO0FBQ0QsdUJBQU8sVUFBUCxDQUFrQixLQUFsQjtBQUNIO0FBQ0o7QUFDSixDQWJEIiwiZmlsZSI6ImdlbmVyYXRlZC5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzQ29udGVudCI6WyIoZnVuY3Rpb24gZSh0LG4scil7ZnVuY3Rpb24gcyhvLHUpe2lmKCFuW29dKXtpZighdFtvXSl7dmFyIGE9dHlwZW9mIHJlcXVpcmU9PVwiZnVuY3Rpb25cIiYmcmVxdWlyZTtpZighdSYmYSlyZXR1cm4gYShvLCEwKTtpZihpKXJldHVybiBpKG8sITApO3ZhciBmPW5ldyBFcnJvcihcIkNhbm5vdCBmaW5kIG1vZHVsZSAnXCIrbytcIidcIik7dGhyb3cgZi5jb2RlPVwiTU9EVUxFX05PVF9GT1VORFwiLGZ9dmFyIGw9bltvXT17ZXhwb3J0czp7fX07dFtvXVswXS5jYWxsKGwuZXhwb3J0cyxmdW5jdGlvbihlKXt2YXIgbj10W29dWzFdW2VdO3JldHVybiBzKG4/bjplKX0sbCxsLmV4cG9ydHMsZSx0LG4scil9cmV0dXJuIG5bb10uZXhwb3J0c312YXIgaT10eXBlb2YgcmVxdWlyZT09XCJmdW5jdGlvblwiJiZyZXF1aXJlO2Zvcih2YXIgbz0wO288ci5sZW5ndGg7bysrKXMocltvXSk7cmV0dXJuIHN9KSIsIi8vID09Q2xvc3VyZUNvbXBpbGVyPT1cbi8vIEBjb21waWxhdGlvbl9sZXZlbCBBRFZBTkNFRF9PUFRJTUlaQVRJT05TXG4vLyBAZXh0ZXJuc191cmwgaHR0cDovL2Nsb3N1cmUtY29tcGlsZXIuZ29vZ2xlY29kZS5jb20vc3ZuL3RydW5rL2NvbnRyaWIvZXh0ZXJucy9tYXBzL2dvb2dsZV9tYXBzX2FwaV92M18zLmpzXG4vLyA9PS9DbG9zdXJlQ29tcGlsZXI9PVxuXG4vKipcbiAqIEBuYW1lIE1hcmtlckNsdXN0ZXJlciBmb3IgR29vZ2xlIE1hcHMgdjNcbiAqIEB2ZXJzaW9uIHZlcnNpb24gMS4wLjFcbiAqIEBhdXRob3IgTHVrZSBNYWhlXG4gKiBAZmlsZW92ZXJ2aWV3XG4gKiBUaGUgbGlicmFyeSBjcmVhdGVzIGFuZCBtYW5hZ2VzIHBlci16b29tLWxldmVsIGNsdXN0ZXJzIGZvciBsYXJnZSBhbW91bnRzIG9mXG4gKiBtYXJrZXJzLlxuICogPGJyLz5cbiAqIFRoaXMgaXMgYSB2MyBpbXBsZW1lbnRhdGlvbiBvZiB0aGVcbiAqIDxhIGhyZWY9XCJodHRwOi8vZ21hcHMtdXRpbGl0eS1saWJyYXJ5LWRldi5nb29nbGVjb2RlLmNvbS9zdm4vdGFncy9tYXJrZXJjbHVzdGVyZXIvXCJcbiAqID52MiBNYXJrZXJDbHVzdGVyZXI8L2E+LlxuICovXG5cbi8qKlxuICogTGljZW5zZWQgdW5kZXIgdGhlIEFwYWNoZSBMaWNlbnNlLCBWZXJzaW9uIDIuMCAodGhlIFwiTGljZW5zZVwiKTtcbiAqIHlvdSBtYXkgbm90IHVzZSB0aGlzIGZpbGUgZXhjZXB0IGluIGNvbXBsaWFuY2Ugd2l0aCB0aGUgTGljZW5zZS5cbiAqIFlvdSBtYXkgb2J0YWluIGEgY29weSBvZiB0aGUgTGljZW5zZSBhdFxuICpcbiAqICAgICBodHRwOi8vd3d3LmFwYWNoZS5vcmcvbGljZW5zZXMvTElDRU5TRS0yLjBcbiAqXG4gKiBVbmxlc3MgcmVxdWlyZWQgYnkgYXBwbGljYWJsZSBsYXcgb3IgYWdyZWVkIHRvIGluIHdyaXRpbmcsIHNvZnR3YXJlXG4gKiBkaXN0cmlidXRlZCB1bmRlciB0aGUgTGljZW5zZSBpcyBkaXN0cmlidXRlZCBvbiBhbiBcIkFTIElTXCIgQkFTSVMsXG4gKiBXSVRIT1VUIFdBUlJBTlRJRVMgT1IgQ09ORElUSU9OUyBPRiBBTlkgS0lORCwgZWl0aGVyIGV4cHJlc3Mgb3IgaW1wbGllZC5cbiAqIFNlZSB0aGUgTGljZW5zZSBmb3IgdGhlIHNwZWNpZmljIGxhbmd1YWdlIGdvdmVybmluZyBwZXJtaXNzaW9ucyBhbmRcbiAqIGxpbWl0YXRpb25zIHVuZGVyIHRoZSBMaWNlbnNlLlxuICovXG5cblxuLyoqXG4gKiBBIE1hcmtlciBDbHVzdGVyZXIgdGhhdCBjbHVzdGVycyBtYXJrZXJzLlxuICpcbiAqIEBwYXJhbSB7Z29vZ2xlLm1hcHMuTWFwfSBtYXAgVGhlIEdvb2dsZSBtYXAgdG8gYXR0YWNoIHRvLlxuICogQHBhcmFtIHtBcnJheS48Z29vZ2xlLm1hcHMuTWFya2VyPj19IG9wdF9tYXJrZXJzIE9wdGlvbmFsIG1hcmtlcnMgdG8gYWRkIHRvXG4gKiAgIHRoZSBjbHVzdGVyLlxuICogQHBhcmFtIHtPYmplY3Q9fSBvcHRfb3B0aW9ucyBzdXBwb3J0IHRoZSBmb2xsb3dpbmcgb3B0aW9uczpcbiAqICAgICAnZ3JpZFNpemUnOiAobnVtYmVyKSBUaGUgZ3JpZCBzaXplIG9mIGEgY2x1c3RlciBpbiBwaXhlbHMuXG4gKiAgICAgJ21heFpvb20nOiAobnVtYmVyKSBUaGUgbWF4aW11bSB6b29tIGxldmVsIHRoYXQgYSBtYXJrZXIgY2FuIGJlIHBhcnQgb2YgYVxuICogICAgICAgICAgICAgICAgY2x1c3Rlci5cbiAqICAgICAnem9vbU9uQ2xpY2snOiAoYm9vbGVhbikgV2hldGhlciB0aGUgZGVmYXVsdCBiZWhhdmlvdXIgb2YgY2xpY2tpbmcgb24gYVxuICogICAgICAgICAgICAgICAgICAgIGNsdXN0ZXIgaXMgdG8gem9vbSBpbnRvIGl0LlxuICogICAgICdpbWFnZVBhdGgnOiAoc3RyaW5nKSBUaGUgYmFzZSBVUkwgd2hlcmUgdGhlIGltYWdlcyByZXByZXNlbnRpbmdcbiAqICAgICAgICAgICAgICAgICAgY2x1c3RlcnMgd2lsbCBiZSBmb3VuZC4gVGhlIGZ1bGwgVVJMIHdpbGwgYmU6XG4gKiAgICAgICAgICAgICAgICAgIHtpbWFnZVBhdGh9WzEtNV0ue2ltYWdlRXh0ZW5zaW9ufVxuICogICAgICAgICAgICAgICAgICBEZWZhdWx0OiAnLi4vaW1hZ2VzL20nLlxuICogICAgICdpbWFnZUV4dGVuc2lvbic6IChzdHJpbmcpIFRoZSBzdWZmaXggZm9yIGltYWdlcyBVUkwgcmVwcmVzZW50aW5nXG4gKiAgICAgICAgICAgICAgICAgICAgICAgY2x1c3RlcnMgd2lsbCBiZSBmb3VuZC4gU2VlIF9pbWFnZVBhdGhfIGZvciBkZXRhaWxzLlxuICogICAgICAgICAgICAgICAgICAgICAgIERlZmF1bHQ6ICdwbmcnLlxuICogICAgICdhdmVyYWdlQ2VudGVyJzogKGJvb2xlYW4pIFdoZXRoZXIgdGhlIGNlbnRlciBvZiBlYWNoIGNsdXN0ZXIgc2hvdWxkIGJlXG4gKiAgICAgICAgICAgICAgICAgICAgICB0aGUgYXZlcmFnZSBvZiBhbGwgbWFya2VycyBpbiB0aGUgY2x1c3Rlci5cbiAqICAgICAnbWluaW11bUNsdXN0ZXJTaXplJzogKG51bWJlcikgVGhlIG1pbmltdW0gbnVtYmVyIG9mIG1hcmtlcnMgdG8gYmUgaW4gYVxuICogICAgICAgICAgICAgICAgICAgICAgICAgICBjbHVzdGVyIGJlZm9yZSB0aGUgbWFya2VycyBhcmUgaGlkZGVuIGFuZCBhIGNvdW50XG4gKiAgICAgICAgICAgICAgICAgICAgICAgICAgIGlzIHNob3duLlxuICogICAgICdzdHlsZXMnOiAob2JqZWN0KSBBbiBvYmplY3QgdGhhdCBoYXMgc3R5bGUgcHJvcGVydGllczpcbiAqICAgICAgICd1cmwnOiAoc3RyaW5nKSBUaGUgaW1hZ2UgdXJsLlxuICogICAgICAgJ2hlaWdodCc6IChudW1iZXIpIFRoZSBpbWFnZSBoZWlnaHQuXG4gKiAgICAgICAnd2lkdGgnOiAobnVtYmVyKSBUaGUgaW1hZ2Ugd2lkdGguXG4gKiAgICAgICAnYW5jaG9yJzogKEFycmF5KSBUaGUgYW5jaG9yIHBvc2l0aW9uIG9mIHRoZSBsYWJlbCB0ZXh0LlxuICogICAgICAgJ3RleHRDb2xvcic6IChzdHJpbmcpIFRoZSB0ZXh0IGNvbG9yLlxuICogICAgICAgJ3RleHRTaXplJzogKG51bWJlcikgVGhlIHRleHQgc2l6ZS5cbiAqICAgICAgICdiYWNrZ3JvdW5kUG9zaXRpb24nOiAoc3RyaW5nKSBUaGUgcG9zaXRpb24gb2YgdGhlIGJhY2tnb3VuZCB4LCB5LlxuICogQGNvbnN0cnVjdG9yXG4gKiBAZXh0ZW5kcyBnb29nbGUubWFwcy5PdmVybGF5Vmlld1xuICovXG5mdW5jdGlvbiBNYXJrZXJDbHVzdGVyZXIobWFwLCBvcHRfbWFya2Vycywgb3B0X29wdGlvbnMpIHtcbiAgICAvLyBNYXJrZXJDbHVzdGVyZXIgaW1wbGVtZW50cyBnb29nbGUubWFwcy5PdmVybGF5VmlldyBpbnRlcmZhY2UuIFdlIHVzZSB0aGVcbiAgICAvLyBleHRlbmQgZnVuY3Rpb24gdG8gZXh0ZW5kIE1hcmtlckNsdXN0ZXJlciB3aXRoIGdvb2dsZS5tYXBzLk92ZXJsYXlWaWV3XG4gICAgLy8gYmVjYXVzZSBpdCBtaWdodCBub3QgYWx3YXlzIGJlIGF2YWlsYWJsZSB3aGVuIHRoZSBjb2RlIGlzIGRlZmluZWQgc28gd2VcbiAgICAvLyBsb29rIGZvciBpdCBhdCB0aGUgbGFzdCBwb3NzaWJsZSBtb21lbnQuIElmIGl0IGRvZXNuJ3QgZXhpc3Qgbm93IHRoZW5cbiAgICAvLyB0aGVyZSBpcyBubyBwb2ludCBnb2luZyBhaGVhZCA6KVxuICAgIHRoaXMuZXh0ZW5kKE1hcmtlckNsdXN0ZXJlciwgZ29vZ2xlLm1hcHMuT3ZlcmxheVZpZXcpO1xuICAgIHRoaXMubWFwXyA9IG1hcDtcblxuICAgIC8qKlxuICAgICAqIEB0eXBlIHtBcnJheS48Z29vZ2xlLm1hcHMuTWFya2VyPn1cbiAgICAgKiBAcHJpdmF0ZVxuICAgICAqL1xuICAgIHRoaXMubWFya2Vyc18gPSBbXTtcblxuICAgIC8qKlxuICAgICAqICBAdHlwZSB7QXJyYXkuPENsdXN0ZXI+fVxuICAgICAqL1xuICAgIHRoaXMuY2x1c3RlcnNfID0gW107XG5cbiAgICB0aGlzLnNpemVzID0gWzUzLCA1NiwgNjYsIDc4LCA5MF07XG5cbiAgICAvKipcbiAgICAgKiBAcHJpdmF0ZVxuICAgICAqL1xuICAgIHRoaXMuc3R5bGVzXyA9IFtdO1xuXG4gICAgLyoqXG4gICAgICogQHR5cGUge2Jvb2xlYW59XG4gICAgICogQHByaXZhdGVcbiAgICAgKi9cbiAgICB0aGlzLnJlYWR5XyA9IGZhbHNlO1xuXG4gICAgdmFyIG9wdGlvbnMgPSBvcHRfb3B0aW9ucyB8fCB7fTtcblxuICAgIC8qKlxuICAgICAqIEB0eXBlIHtudW1iZXJ9XG4gICAgICogQHByaXZhdGVcbiAgICAgKi9cbiAgICB0aGlzLmdyaWRTaXplXyA9IG9wdGlvbnNbJ2dyaWRTaXplJ10gfHwgNjA7XG5cbiAgICAvKipcbiAgICAgKiBAcHJpdmF0ZVxuICAgICAqL1xuICAgIHRoaXMubWluQ2x1c3RlclNpemVfID0gb3B0aW9uc1snbWluaW11bUNsdXN0ZXJTaXplJ10gfHwgMjtcblxuXG4gICAgLyoqXG4gICAgICogQHR5cGUgez9udW1iZXJ9XG4gICAgICogQHByaXZhdGVcbiAgICAgKi9cbiAgICB0aGlzLm1heFpvb21fID0gb3B0aW9uc1snbWF4Wm9vbSddIHx8IG51bGw7XG5cbiAgICB0aGlzLnN0eWxlc18gPSBvcHRpb25zWydzdHlsZXMnXSB8fCBbXTtcblxuICAgIC8qKlxuICAgICAqIEB0eXBlIHtzdHJpbmd9XG4gICAgICogQHByaXZhdGVcbiAgICAgKi9cbiAgICB0aGlzLmltYWdlUGF0aF8gPSBvcHRpb25zWydpbWFnZVBhdGgnXSB8fFxuICAgICAgICB0aGlzLk1BUktFUl9DTFVTVEVSX0lNQUdFX1BBVEhfO1xuXG4gICAgLyoqXG4gICAgICogQHR5cGUge3N0cmluZ31cbiAgICAgKiBAcHJpdmF0ZVxuICAgICAqL1xuICAgIHRoaXMuaW1hZ2VFeHRlbnNpb25fID0gb3B0aW9uc1snaW1hZ2VFeHRlbnNpb24nXSB8fFxuICAgICAgICB0aGlzLk1BUktFUl9DTFVTVEVSX0lNQUdFX0VYVEVOU0lPTl87XG5cbiAgICAvKipcbiAgICAgKiBAdHlwZSB7Ym9vbGVhbn1cbiAgICAgKiBAcHJpdmF0ZVxuICAgICAqL1xuICAgIHRoaXMuem9vbU9uQ2xpY2tfID0gdHJ1ZTtcblxuICAgIGlmIChvcHRpb25zWyd6b29tT25DbGljayddICE9IHVuZGVmaW5lZCkge1xuICAgICAgICB0aGlzLnpvb21PbkNsaWNrXyA9IG9wdGlvbnNbJ3pvb21PbkNsaWNrJ107XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogQHR5cGUge2Jvb2xlYW59XG4gICAgICogQHByaXZhdGVcbiAgICAgKi9cbiAgICB0aGlzLmF2ZXJhZ2VDZW50ZXJfID0gZmFsc2U7XG5cbiAgICBpZiAob3B0aW9uc1snYXZlcmFnZUNlbnRlciddICE9IHVuZGVmaW5lZCkge1xuICAgICAgICB0aGlzLmF2ZXJhZ2VDZW50ZXJfID0gb3B0aW9uc1snYXZlcmFnZUNlbnRlciddO1xuICAgIH1cblxuICAgIHRoaXMuc2V0dXBTdHlsZXNfKCk7XG5cbiAgICB0aGlzLnNldE1hcChtYXApO1xuXG4gICAgLyoqXG4gICAgICogQHR5cGUge251bWJlcn1cbiAgICAgKiBAcHJpdmF0ZVxuICAgICAqL1xuICAgIHRoaXMucHJldlpvb21fID0gdGhpcy5tYXBfLmdldFpvb20oKTtcblxuICAgIC8vIEFkZCB0aGUgbWFwIGV2ZW50IGxpc3RlbmVyc1xuICAgIHZhciB0aGF0ID0gdGhpcztcbiAgICBnb29nbGUubWFwcy5ldmVudC5hZGRMaXN0ZW5lcih0aGlzLm1hcF8sICd6b29tX2NoYW5nZWQnLCBmdW5jdGlvbigpIHtcbiAgICAgICAgLy8gRGV0ZXJtaW5lcyBtYXAgdHlwZSBhbmQgcHJldmVudCBpbGxlZ2FsIHpvb20gbGV2ZWxzXG4gICAgICAgIHZhciB6b29tID0gdGhhdC5tYXBfLmdldFpvb20oKTtcbiAgICAgICAgdmFyIG1pblpvb20gPSB0aGF0Lm1hcF8ubWluWm9vbSB8fCAwO1xuICAgICAgICB2YXIgbWF4Wm9vbSA9IE1hdGgubWluKHRoYXQubWFwXy5tYXhab29tIHx8IDEwMCxcbiAgICAgICAgICAgIHRoYXQubWFwXy5tYXBUeXBlc1t0aGF0Lm1hcF8uZ2V0TWFwVHlwZUlkKCldLm1heFpvb20pO1xuICAgICAgICB6b29tID0gTWF0aC5taW4oTWF0aC5tYXgoem9vbSxtaW5ab29tKSxtYXhab29tKTtcblxuICAgICAgICBpZiAodGhhdC5wcmV2Wm9vbV8gIT0gem9vbSkge1xuICAgICAgICAgICAgdGhhdC5wcmV2Wm9vbV8gPSB6b29tO1xuICAgICAgICAgICAgdGhhdC5yZXNldFZpZXdwb3J0KCk7XG4gICAgICAgIH1cbiAgICB9KTtcblxuICAgIGdvb2dsZS5tYXBzLmV2ZW50LmFkZExpc3RlbmVyKHRoaXMubWFwXywgJ2lkbGUnLCBmdW5jdGlvbigpIHtcbiAgICAgICAgdGhhdC5yZWRyYXcoKTtcbiAgICB9KTtcblxuICAgIC8vIEZpbmFsbHksIGFkZCB0aGUgbWFya2Vyc1xuICAgIGlmIChvcHRfbWFya2VycyAmJiAob3B0X21hcmtlcnMubGVuZ3RoIHx8IE9iamVjdC5rZXlzKG9wdF9tYXJrZXJzKS5sZW5ndGgpKSB7XG4gICAgICAgIHRoaXMuYWRkTWFya2VycyhvcHRfbWFya2VycywgZmFsc2UpO1xuICAgIH1cbn1cblxuXG4vKipcbiAqIFRoZSBtYXJrZXIgY2x1c3RlciBpbWFnZSBwYXRoLlxuICpcbiAqIEB0eXBlIHtzdHJpbmd9XG4gKiBAcHJpdmF0ZVxuICovXG5NYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlLk1BUktFUl9DTFVTVEVSX0lNQUdFX1BBVEhfID0gJy4uL2ltYWdlcy9tJztcblxuXG4vKipcbiAqIFRoZSBtYXJrZXIgY2x1c3RlciBpbWFnZSBwYXRoLlxuICpcbiAqIEB0eXBlIHtzdHJpbmd9XG4gKiBAcHJpdmF0ZVxuICovXG5NYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlLk1BUktFUl9DTFVTVEVSX0lNQUdFX0VYVEVOU0lPTl8gPSAncG5nJztcblxuXG4vKipcbiAqIEV4dGVuZHMgYSBvYmplY3RzIHByb3RvdHlwZSBieSBhbm90aGVycy5cbiAqXG4gKiBAcGFyYW0ge09iamVjdH0gb2JqMSBUaGUgb2JqZWN0IHRvIGJlIGV4dGVuZGVkLlxuICogQHBhcmFtIHtPYmplY3R9IG9iajIgVGhlIG9iamVjdCB0byBleHRlbmQgd2l0aC5cbiAqIEByZXR1cm4ge09iamVjdH0gVGhlIG5ldyBleHRlbmRlZCBvYmplY3QuXG4gKiBAaWdub3JlXG4gKi9cbk1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGUuZXh0ZW5kID0gZnVuY3Rpb24ob2JqMSwgb2JqMikge1xuICAgIHJldHVybiAoZnVuY3Rpb24ob2JqZWN0KSB7XG4gICAgICAgIGZvciAodmFyIHByb3BlcnR5IGluIG9iamVjdC5wcm90b3R5cGUpIHtcbiAgICAgICAgICAgIHRoaXMucHJvdG90eXBlW3Byb3BlcnR5XSA9IG9iamVjdC5wcm90b3R5cGVbcHJvcGVydHldO1xuICAgICAgICB9XG4gICAgICAgIHJldHVybiB0aGlzO1xuICAgIH0pLmFwcGx5KG9iajEsIFtvYmoyXSk7XG59O1xuXG5cbi8qKlxuICogSW1wbGVtZW50YWlvbiBvZiB0aGUgaW50ZXJmYWNlIG1ldGhvZC5cbiAqIEBpZ25vcmVcbiAqL1xuTWFya2VyQ2x1c3RlcmVyLnByb3RvdHlwZS5vbkFkZCA9IGZ1bmN0aW9uKCkge1xuICAgIHRoaXMuc2V0UmVhZHlfKHRydWUpO1xufTtcblxuLyoqXG4gKiBJbXBsZW1lbnRhaW9uIG9mIHRoZSBpbnRlcmZhY2UgbWV0aG9kLlxuICogQGlnbm9yZVxuICovXG5NYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlLmRyYXcgPSBmdW5jdGlvbigpIHt9O1xuXG4vKipcbiAqIFNldHMgdXAgdGhlIHN0eWxlcyBvYmplY3QuXG4gKlxuICogQHByaXZhdGVcbiAqL1xuTWFya2VyQ2x1c3RlcmVyLnByb3RvdHlwZS5zZXR1cFN0eWxlc18gPSBmdW5jdGlvbigpIHtcbiAgICBpZiAodGhpcy5zdHlsZXNfLmxlbmd0aCkge1xuICAgICAgICByZXR1cm47XG4gICAgfVxuXG4gICAgZm9yICh2YXIgaSA9IDAsIHNpemU7IHNpemUgPSB0aGlzLnNpemVzW2ldOyBpKyspIHtcbiAgICAgICAgdGhpcy5zdHlsZXNfLnB1c2goe1xuICAgICAgICAgICAgdXJsOiB0aGlzLmltYWdlUGF0aF8gKyAoaSArIDEpICsgJy4nICsgdGhpcy5pbWFnZUV4dGVuc2lvbl8sXG4gICAgICAgICAgICBoZWlnaHQ6IHNpemUsXG4gICAgICAgICAgICB3aWR0aDogc2l6ZVxuICAgICAgICB9KTtcbiAgICB9XG59O1xuXG4vKipcbiAqICBGaXQgdGhlIG1hcCB0byB0aGUgYm91bmRzIG9mIHRoZSBtYXJrZXJzIGluIHRoZSBjbHVzdGVyZXIuXG4gKi9cbk1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGUuZml0TWFwVG9NYXJrZXJzID0gZnVuY3Rpb24oKSB7XG4gICAgdmFyIG1hcmtlcnMgPSB0aGlzLmdldE1hcmtlcnMoKTtcbiAgICB2YXIgYm91bmRzID0gbmV3IGdvb2dsZS5tYXBzLkxhdExuZ0JvdW5kcygpO1xuICAgIGZvciAodmFyIGkgPSAwLCBtYXJrZXI7IG1hcmtlciA9IG1hcmtlcnNbaV07IGkrKykge1xuICAgICAgICBib3VuZHMuZXh0ZW5kKG1hcmtlci5nZXRQb3NpdGlvbigpKTtcbiAgICB9XG5cbiAgICB0aGlzLm1hcF8uZml0Qm91bmRzKGJvdW5kcyk7XG59O1xuXG5cbi8qKlxuICogIFNldHMgdGhlIHN0eWxlcy5cbiAqXG4gKiAgQHBhcmFtIHtPYmplY3R9IHN0eWxlcyBUaGUgc3R5bGUgdG8gc2V0LlxuICovXG5NYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlLnNldFN0eWxlcyA9IGZ1bmN0aW9uKHN0eWxlcykge1xuICAgIHRoaXMuc3R5bGVzXyA9IHN0eWxlcztcbn07XG5cblxuLyoqXG4gKiAgR2V0cyB0aGUgc3R5bGVzLlxuICpcbiAqICBAcmV0dXJuIHtPYmplY3R9IFRoZSBzdHlsZXMgb2JqZWN0LlxuICovXG5NYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlLmdldFN0eWxlcyA9IGZ1bmN0aW9uKCkge1xuICAgIHJldHVybiB0aGlzLnN0eWxlc187XG59O1xuXG5cbi8qKlxuICogV2hldGhlciB6b29tIG9uIGNsaWNrIGlzIHNldC5cbiAqXG4gKiBAcmV0dXJuIHtib29sZWFufSBUcnVlIGlmIHpvb21PbkNsaWNrXyBpcyBzZXQuXG4gKi9cbk1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGUuaXNab29tT25DbGljayA9IGZ1bmN0aW9uKCkge1xuICAgIHJldHVybiB0aGlzLnpvb21PbkNsaWNrXztcbn07XG5cbi8qKlxuICogV2hldGhlciBhdmVyYWdlIGNlbnRlciBpcyBzZXQuXG4gKlxuICogQHJldHVybiB7Ym9vbGVhbn0gVHJ1ZSBpZiBhdmVyYWdlQ2VudGVyXyBpcyBzZXQuXG4gKi9cbk1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGUuaXNBdmVyYWdlQ2VudGVyID0gZnVuY3Rpb24oKSB7XG4gICAgcmV0dXJuIHRoaXMuYXZlcmFnZUNlbnRlcl87XG59O1xuXG5cbi8qKlxuICogIFJldHVybnMgdGhlIGFycmF5IG9mIG1hcmtlcnMgaW4gdGhlIGNsdXN0ZXJlci5cbiAqXG4gKiAgQHJldHVybiB7QXJyYXkuPGdvb2dsZS5tYXBzLk1hcmtlcj59IFRoZSBtYXJrZXJzLlxuICovXG5NYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlLmdldE1hcmtlcnMgPSBmdW5jdGlvbigpIHtcbiAgICByZXR1cm4gdGhpcy5tYXJrZXJzXztcbn07XG5cblxuLyoqXG4gKiAgUmV0dXJucyB0aGUgbnVtYmVyIG9mIG1hcmtlcnMgaW4gdGhlIGNsdXN0ZXJlclxuICpcbiAqICBAcmV0dXJuIHtOdW1iZXJ9IFRoZSBudW1iZXIgb2YgbWFya2Vycy5cbiAqL1xuTWFya2VyQ2x1c3RlcmVyLnByb3RvdHlwZS5nZXRUb3RhbE1hcmtlcnMgPSBmdW5jdGlvbigpIHtcbiAgICByZXR1cm4gdGhpcy5tYXJrZXJzXy5sZW5ndGg7XG59O1xuXG5cbi8qKlxuICogIFNldHMgdGhlIG1heCB6b29tIGZvciB0aGUgY2x1c3RlcmVyLlxuICpcbiAqICBAcGFyYW0ge251bWJlcn0gbWF4Wm9vbSBUaGUgbWF4IHpvb20gbGV2ZWwuXG4gKi9cbk1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGUuc2V0TWF4Wm9vbSA9IGZ1bmN0aW9uKG1heFpvb20pIHtcbiAgICB0aGlzLm1heFpvb21fID0gbWF4Wm9vbTtcbn07XG5cblxuLyoqXG4gKiAgR2V0cyB0aGUgbWF4IHpvb20gZm9yIHRoZSBjbHVzdGVyZXIuXG4gKlxuICogIEByZXR1cm4ge251bWJlcn0gVGhlIG1heCB6b29tIGxldmVsLlxuICovXG5NYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlLmdldE1heFpvb20gPSBmdW5jdGlvbigpIHtcbiAgICByZXR1cm4gdGhpcy5tYXhab29tXztcbn07XG5cblxuLyoqXG4gKiAgVGhlIGZ1bmN0aW9uIGZvciBjYWxjdWxhdGluZyB0aGUgY2x1c3RlciBpY29uIGltYWdlLlxuICpcbiAqICBAcGFyYW0ge0FycmF5Ljxnb29nbGUubWFwcy5NYXJrZXI+fSBtYXJrZXJzIFRoZSBtYXJrZXJzIGluIHRoZSBjbHVzdGVyZXIuXG4gKiAgQHBhcmFtIHtudW1iZXJ9IG51bVN0eWxlcyBUaGUgbnVtYmVyIG9mIHN0eWxlcyBhdmFpbGFibGUuXG4gKiAgQHJldHVybiB7T2JqZWN0fSBBIG9iamVjdCBwcm9wZXJ0aWVzOiAndGV4dCcgKHN0cmluZykgYW5kICdpbmRleCcgKG51bWJlcikuXG4gKiAgQHByaXZhdGVcbiAqL1xuTWFya2VyQ2x1c3RlcmVyLnByb3RvdHlwZS5jYWxjdWxhdG9yXyA9IGZ1bmN0aW9uKG1hcmtlcnMsIG51bVN0eWxlcykge1xuICAgIHZhciBpbmRleCA9IDA7XG4gICAgdmFyIGNvdW50ID0gbWFya2Vycy5sZW5ndGg7XG4gICAgdmFyIGR2ID0gY291bnQ7XG4gICAgd2hpbGUgKGR2ICE9PSAwKSB7XG4gICAgICAgIGR2ID0gcGFyc2VJbnQoZHYgLyAxMCwgMTApO1xuICAgICAgICBpbmRleCsrO1xuICAgIH1cblxuICAgIGluZGV4ID0gTWF0aC5taW4oaW5kZXgsIG51bVN0eWxlcyk7XG4gICAgcmV0dXJuIHtcbiAgICAgICAgdGV4dDogY291bnQsXG4gICAgICAgIGluZGV4OiBpbmRleFxuICAgIH07XG59O1xuXG5cbi8qKlxuICogU2V0IHRoZSBjYWxjdWxhdG9yIGZ1bmN0aW9uLlxuICpcbiAqIEBwYXJhbSB7ZnVuY3Rpb24oQXJyYXksIG51bWJlcil9IGNhbGN1bGF0b3IgVGhlIGZ1bmN0aW9uIHRvIHNldCBhcyB0aGVcbiAqICAgICBjYWxjdWxhdG9yLiBUaGUgZnVuY3Rpb24gc2hvdWxkIHJldHVybiBhIG9iamVjdCBwcm9wZXJ0aWVzOlxuICogICAgICd0ZXh0JyAoc3RyaW5nKSBhbmQgJ2luZGV4JyAobnVtYmVyKS5cbiAqXG4gKi9cbk1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGUuc2V0Q2FsY3VsYXRvciA9IGZ1bmN0aW9uKGNhbGN1bGF0b3IpIHtcbiAgICB0aGlzLmNhbGN1bGF0b3JfID0gY2FsY3VsYXRvcjtcbn07XG5cblxuLyoqXG4gKiBHZXQgdGhlIGNhbGN1bGF0b3IgZnVuY3Rpb24uXG4gKlxuICogQHJldHVybiB7ZnVuY3Rpb24oQXJyYXksIG51bWJlcil9IHRoZSBjYWxjdWxhdG9yIGZ1bmN0aW9uLlxuICovXG5NYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlLmdldENhbGN1bGF0b3IgPSBmdW5jdGlvbigpIHtcbiAgICByZXR1cm4gdGhpcy5jYWxjdWxhdG9yXztcbn07XG5cblxuLyoqXG4gKiBBZGQgYW4gYXJyYXkgb2YgbWFya2VycyB0byB0aGUgY2x1c3RlcmVyLlxuICpcbiAqIEBwYXJhbSB7QXJyYXkuPGdvb2dsZS5tYXBzLk1hcmtlcj59IG1hcmtlcnMgVGhlIG1hcmtlcnMgdG8gYWRkLlxuICogQHBhcmFtIHtib29sZWFuPX0gb3B0X25vZHJhdyBXaGV0aGVyIHRvIHJlZHJhdyB0aGUgY2x1c3RlcnMuXG4gKi9cbk1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGUuYWRkTWFya2VycyA9IGZ1bmN0aW9uKG1hcmtlcnMsIG9wdF9ub2RyYXcpIHtcbiAgICBpZiAobWFya2Vycy5sZW5ndGgpIHtcbiAgICAgICAgZm9yICh2YXIgaSA9IDAsIG1hcmtlcjsgbWFya2VyID0gbWFya2Vyc1tpXTsgaSsrKSB7XG4gICAgICAgICAgICB0aGlzLnB1c2hNYXJrZXJUb18obWFya2VyKTtcbiAgICAgICAgfVxuICAgIH0gZWxzZSBpZiAoT2JqZWN0LmtleXMobWFya2VycykubGVuZ3RoKSB7XG4gICAgICAgIGZvciAodmFyIG1hcmtlciBpbiBtYXJrZXJzKSB7XG4gICAgICAgICAgICB0aGlzLnB1c2hNYXJrZXJUb18obWFya2Vyc1ttYXJrZXJdKTtcbiAgICAgICAgfVxuICAgIH1cbiAgICBpZiAoIW9wdF9ub2RyYXcpIHtcbiAgICAgICAgdGhpcy5yZWRyYXcoKTtcbiAgICB9XG59O1xuXG5cbi8qKlxuICogUHVzaGVzIGEgbWFya2VyIHRvIHRoZSBjbHVzdGVyZXIuXG4gKlxuICogQHBhcmFtIHtnb29nbGUubWFwcy5NYXJrZXJ9IG1hcmtlciBUaGUgbWFya2VyIHRvIGFkZC5cbiAqIEBwcml2YXRlXG4gKi9cbk1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGUucHVzaE1hcmtlclRvXyA9IGZ1bmN0aW9uKG1hcmtlcikge1xuICAgIG1hcmtlci5pc0FkZGVkID0gZmFsc2U7XG4gICAgaWYgKG1hcmtlclsnZHJhZ2dhYmxlJ10pIHtcbiAgICAgICAgLy8gSWYgdGhlIG1hcmtlciBpcyBkcmFnZ2FibGUgYWRkIGEgbGlzdGVuZXIgc28gd2UgdXBkYXRlIHRoZSBjbHVzdGVycyBvblxuICAgICAgICAvLyB0aGUgZHJhZyBlbmQuXG4gICAgICAgIHZhciB0aGF0ID0gdGhpcztcbiAgICAgICAgZ29vZ2xlLm1hcHMuZXZlbnQuYWRkTGlzdGVuZXIobWFya2VyLCAnZHJhZ2VuZCcsIGZ1bmN0aW9uKCkge1xuICAgICAgICAgICAgbWFya2VyLmlzQWRkZWQgPSBmYWxzZTtcbiAgICAgICAgICAgIHRoYXQucmVwYWludCgpO1xuICAgICAgICB9KTtcbiAgICB9XG4gICAgdGhpcy5tYXJrZXJzXy5wdXNoKG1hcmtlcik7XG59O1xuXG5cbi8qKlxuICogQWRkcyBhIG1hcmtlciB0byB0aGUgY2x1c3RlcmVyIGFuZCByZWRyYXdzIGlmIG5lZWRlZC5cbiAqXG4gKiBAcGFyYW0ge2dvb2dsZS5tYXBzLk1hcmtlcn0gbWFya2VyIFRoZSBtYXJrZXIgdG8gYWRkLlxuICogQHBhcmFtIHtib29sZWFuPX0gb3B0X25vZHJhdyBXaGV0aGVyIHRvIHJlZHJhdyB0aGUgY2x1c3RlcnMuXG4gKi9cbk1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGUuYWRkTWFya2VyID0gZnVuY3Rpb24obWFya2VyLCBvcHRfbm9kcmF3KSB7XG4gICAgdGhpcy5wdXNoTWFya2VyVG9fKG1hcmtlcik7XG4gICAgaWYgKCFvcHRfbm9kcmF3KSB7XG4gICAgICAgIHRoaXMucmVkcmF3KCk7XG4gICAgfVxufTtcblxuXG4vKipcbiAqIFJlbW92ZXMgYSBtYXJrZXIgYW5kIHJldHVybnMgdHJ1ZSBpZiByZW1vdmVkLCBmYWxzZSBpZiBub3RcbiAqXG4gKiBAcGFyYW0ge2dvb2dsZS5tYXBzLk1hcmtlcn0gbWFya2VyIFRoZSBtYXJrZXIgdG8gcmVtb3ZlXG4gKiBAcmV0dXJuIHtib29sZWFufSBXaGV0aGVyIHRoZSBtYXJrZXIgd2FzIHJlbW92ZWQgb3Igbm90XG4gKiBAcHJpdmF0ZVxuICovXG5NYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlLnJlbW92ZU1hcmtlcl8gPSBmdW5jdGlvbihtYXJrZXIpIHtcbiAgICB2YXIgaW5kZXggPSAtMTtcbiAgICBpZiAodGhpcy5tYXJrZXJzXy5pbmRleE9mKSB7XG4gICAgICAgIGluZGV4ID0gdGhpcy5tYXJrZXJzXy5pbmRleE9mKG1hcmtlcik7XG4gICAgfSBlbHNlIHtcbiAgICAgICAgZm9yICh2YXIgaSA9IDAsIG07IG0gPSB0aGlzLm1hcmtlcnNfW2ldOyBpKyspIHtcbiAgICAgICAgICAgIGlmIChtID09IG1hcmtlcikge1xuICAgICAgICAgICAgICAgIGluZGV4ID0gaTtcbiAgICAgICAgICAgICAgICBicmVhaztcbiAgICAgICAgICAgIH1cbiAgICAgICAgfVxuICAgIH1cblxuICAgIGlmIChpbmRleCA9PSAtMSkge1xuICAgICAgICAvLyBNYXJrZXIgaXMgbm90IGluIG91ciBsaXN0IG9mIG1hcmtlcnMuXG4gICAgICAgIHJldHVybiBmYWxzZTtcbiAgICB9XG5cbiAgICBtYXJrZXIuc2V0TWFwKG51bGwpO1xuXG4gICAgdGhpcy5tYXJrZXJzXy5zcGxpY2UoaW5kZXgsIDEpO1xuXG4gICAgcmV0dXJuIHRydWU7XG59O1xuXG5cbi8qKlxuICogUmVtb3ZlIGEgbWFya2VyIGZyb20gdGhlIGNsdXN0ZXIuXG4gKlxuICogQHBhcmFtIHtnb29nbGUubWFwcy5NYXJrZXJ9IG1hcmtlciBUaGUgbWFya2VyIHRvIHJlbW92ZS5cbiAqIEBwYXJhbSB7Ym9vbGVhbj19IG9wdF9ub2RyYXcgT3B0aW9uYWwgYm9vbGVhbiB0byBmb3JjZSBubyByZWRyYXcuXG4gKiBAcmV0dXJuIHtib29sZWFufSBUcnVlIGlmIHRoZSBtYXJrZXIgd2FzIHJlbW92ZWQuXG4gKi9cbk1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGUucmVtb3ZlTWFya2VyID0gZnVuY3Rpb24obWFya2VyLCBvcHRfbm9kcmF3KSB7XG4gICAgdmFyIHJlbW92ZWQgPSB0aGlzLnJlbW92ZU1hcmtlcl8obWFya2VyKTtcblxuICAgIGlmICghb3B0X25vZHJhdyAmJiByZW1vdmVkKSB7XG4gICAgICAgIHRoaXMucmVzZXRWaWV3cG9ydCgpO1xuICAgICAgICB0aGlzLnJlZHJhdygpO1xuICAgICAgICByZXR1cm4gdHJ1ZTtcbiAgICB9IGVsc2Uge1xuICAgICAgICByZXR1cm4gZmFsc2U7XG4gICAgfVxufTtcblxuXG4vKipcbiAqIFJlbW92ZXMgYW4gYXJyYXkgb2YgbWFya2VycyBmcm9tIHRoZSBjbHVzdGVyLlxuICpcbiAqIEBwYXJhbSB7QXJyYXkuPGdvb2dsZS5tYXBzLk1hcmtlcj59IG1hcmtlcnMgVGhlIG1hcmtlcnMgdG8gcmVtb3ZlLlxuICogQHBhcmFtIHtib29sZWFuPX0gb3B0X25vZHJhdyBPcHRpb25hbCBib29sZWFuIHRvIGZvcmNlIG5vIHJlZHJhdy5cbiAqL1xuTWFya2VyQ2x1c3RlcmVyLnByb3RvdHlwZS5yZW1vdmVNYXJrZXJzID0gZnVuY3Rpb24obWFya2Vycywgb3B0X25vZHJhdykge1xuICAgIC8vIGNyZWF0ZSBhIGxvY2FsIGNvcHkgb2YgbWFya2VycyBpZiByZXF1aXJlZFxuICAgIC8vIChyZW1vdmVNYXJrZXJfIG1vZGlmaWVzIHRoZSBnZXRNYXJrZXJzKCkgYXJyYXkgaW4gcGxhY2UpXG4gICAgdmFyIG1hcmtlcnNDb3B5ID0gbWFya2VycyA9PT0gdGhpcy5nZXRNYXJrZXJzKCkgPyBtYXJrZXJzLnNsaWNlKCkgOiBtYXJrZXJzO1xuICAgIHZhciByZW1vdmVkID0gZmFsc2U7XG5cbiAgICBmb3IgKHZhciBpID0gMCwgbWFya2VyOyBtYXJrZXIgPSBtYXJrZXJzQ29weVtpXTsgaSsrKSB7XG4gICAgICAgIHZhciByID0gdGhpcy5yZW1vdmVNYXJrZXJfKG1hcmtlcik7XG4gICAgICAgIHJlbW92ZWQgPSByZW1vdmVkIHx8IHI7XG4gICAgfVxuXG4gICAgaWYgKCFvcHRfbm9kcmF3ICYmIHJlbW92ZWQpIHtcbiAgICAgICAgdGhpcy5yZXNldFZpZXdwb3J0KCk7XG4gICAgICAgIHRoaXMucmVkcmF3KCk7XG4gICAgICAgIHJldHVybiB0cnVlO1xuICAgIH1cbn07XG5cblxuLyoqXG4gKiBTZXRzIHRoZSBjbHVzdGVyZXIncyByZWFkeSBzdGF0ZS5cbiAqXG4gKiBAcGFyYW0ge2Jvb2xlYW59IHJlYWR5IFRoZSBzdGF0ZS5cbiAqIEBwcml2YXRlXG4gKi9cbk1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGUuc2V0UmVhZHlfID0gZnVuY3Rpb24ocmVhZHkpIHtcbiAgICBpZiAoIXRoaXMucmVhZHlfKSB7XG4gICAgICAgIHRoaXMucmVhZHlfID0gcmVhZHk7XG4gICAgICAgIHRoaXMuY3JlYXRlQ2x1c3RlcnNfKCk7XG4gICAgfVxufTtcblxuXG4vKipcbiAqIFJldHVybnMgdGhlIG51bWJlciBvZiBjbHVzdGVycyBpbiB0aGUgY2x1c3RlcmVyLlxuICpcbiAqIEByZXR1cm4ge251bWJlcn0gVGhlIG51bWJlciBvZiBjbHVzdGVycy5cbiAqL1xuTWFya2VyQ2x1c3RlcmVyLnByb3RvdHlwZS5nZXRUb3RhbENsdXN0ZXJzID0gZnVuY3Rpb24oKSB7XG4gICAgcmV0dXJuIHRoaXMuY2x1c3RlcnNfLmxlbmd0aDtcbn07XG5cblxuLyoqXG4gKiBSZXR1cm5zIHRoZSBnb29nbGUgbWFwIHRoYXQgdGhlIGNsdXN0ZXJlciBpcyBhc3NvY2lhdGVkIHdpdGguXG4gKlxuICogQHJldHVybiB7Z29vZ2xlLm1hcHMuTWFwfSBUaGUgbWFwLlxuICovXG5NYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlLmdldE1hcCA9IGZ1bmN0aW9uKCkge1xuICAgIHJldHVybiB0aGlzLm1hcF87XG59O1xuXG5cbi8qKlxuICogU2V0cyB0aGUgZ29vZ2xlIG1hcCB0aGF0IHRoZSBjbHVzdGVyZXIgaXMgYXNzb2NpYXRlZCB3aXRoLlxuICpcbiAqIEBwYXJhbSB7Z29vZ2xlLm1hcHMuTWFwfSBtYXAgVGhlIG1hcC5cbiAqL1xuTWFya2VyQ2x1c3RlcmVyLnByb3RvdHlwZS5zZXRNYXAgPSBmdW5jdGlvbihtYXApIHtcbiAgICB0aGlzLm1hcF8gPSBtYXA7XG59O1xuXG5cbi8qKlxuICogUmV0dXJucyB0aGUgc2l6ZSBvZiB0aGUgZ3JpZC5cbiAqXG4gKiBAcmV0dXJuIHtudW1iZXJ9IFRoZSBncmlkIHNpemUuXG4gKi9cbk1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGUuZ2V0R3JpZFNpemUgPSBmdW5jdGlvbigpIHtcbiAgICByZXR1cm4gdGhpcy5ncmlkU2l6ZV87XG59O1xuXG5cbi8qKlxuICogU2V0cyB0aGUgc2l6ZSBvZiB0aGUgZ3JpZC5cbiAqXG4gKiBAcGFyYW0ge251bWJlcn0gc2l6ZSBUaGUgZ3JpZCBzaXplLlxuICovXG5NYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlLnNldEdyaWRTaXplID0gZnVuY3Rpb24oc2l6ZSkge1xuICAgIHRoaXMuZ3JpZFNpemVfID0gc2l6ZTtcbn07XG5cblxuLyoqXG4gKiBSZXR1cm5zIHRoZSBtaW4gY2x1c3RlciBzaXplLlxuICpcbiAqIEByZXR1cm4ge251bWJlcn0gVGhlIGdyaWQgc2l6ZS5cbiAqL1xuTWFya2VyQ2x1c3RlcmVyLnByb3RvdHlwZS5nZXRNaW5DbHVzdGVyU2l6ZSA9IGZ1bmN0aW9uKCkge1xuICAgIHJldHVybiB0aGlzLm1pbkNsdXN0ZXJTaXplXztcbn07XG5cbi8qKlxuICogU2V0cyB0aGUgbWluIGNsdXN0ZXIgc2l6ZS5cbiAqXG4gKiBAcGFyYW0ge251bWJlcn0gc2l6ZSBUaGUgZ3JpZCBzaXplLlxuICovXG5NYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlLnNldE1pbkNsdXN0ZXJTaXplID0gZnVuY3Rpb24oc2l6ZSkge1xuICAgIHRoaXMubWluQ2x1c3RlclNpemVfID0gc2l6ZTtcbn07XG5cblxuLyoqXG4gKiBFeHRlbmRzIGEgYm91bmRzIG9iamVjdCBieSB0aGUgZ3JpZCBzaXplLlxuICpcbiAqIEBwYXJhbSB7Z29vZ2xlLm1hcHMuTGF0TG5nQm91bmRzfSBib3VuZHMgVGhlIGJvdW5kcyB0byBleHRlbmQuXG4gKiBAcmV0dXJuIHtnb29nbGUubWFwcy5MYXRMbmdCb3VuZHN9IFRoZSBleHRlbmRlZCBib3VuZHMuXG4gKi9cbk1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGUuZ2V0RXh0ZW5kZWRCb3VuZHMgPSBmdW5jdGlvbihib3VuZHMpIHtcbiAgICB2YXIgcHJvamVjdGlvbiA9IHRoaXMuZ2V0UHJvamVjdGlvbigpO1xuXG4gICAgLy8gVHVybiB0aGUgYm91bmRzIGludG8gbGF0bG5nLlxuICAgIHZhciB0ciA9IG5ldyBnb29nbGUubWFwcy5MYXRMbmcoYm91bmRzLmdldE5vcnRoRWFzdCgpLmxhdCgpLFxuICAgICAgICBib3VuZHMuZ2V0Tm9ydGhFYXN0KCkubG5nKCkpO1xuICAgIHZhciBibCA9IG5ldyBnb29nbGUubWFwcy5MYXRMbmcoYm91bmRzLmdldFNvdXRoV2VzdCgpLmxhdCgpLFxuICAgICAgICBib3VuZHMuZ2V0U291dGhXZXN0KCkubG5nKCkpO1xuXG4gICAgLy8gQ29udmVydCB0aGUgcG9pbnRzIHRvIHBpeGVscyBhbmQgdGhlIGV4dGVuZCBvdXQgYnkgdGhlIGdyaWQgc2l6ZS5cbiAgICB2YXIgdHJQaXggPSBwcm9qZWN0aW9uLmZyb21MYXRMbmdUb0RpdlBpeGVsKHRyKTtcbiAgICB0clBpeC54ICs9IHRoaXMuZ3JpZFNpemVfO1xuICAgIHRyUGl4LnkgLT0gdGhpcy5ncmlkU2l6ZV87XG5cbiAgICB2YXIgYmxQaXggPSBwcm9qZWN0aW9uLmZyb21MYXRMbmdUb0RpdlBpeGVsKGJsKTtcbiAgICBibFBpeC54IC09IHRoaXMuZ3JpZFNpemVfO1xuICAgIGJsUGl4LnkgKz0gdGhpcy5ncmlkU2l6ZV87XG5cbiAgICAvLyBDb252ZXJ0IHRoZSBwaXhlbCBwb2ludHMgYmFjayB0byBMYXRMbmdcbiAgICB2YXIgbmUgPSBwcm9qZWN0aW9uLmZyb21EaXZQaXhlbFRvTGF0TG5nKHRyUGl4KTtcbiAgICB2YXIgc3cgPSBwcm9qZWN0aW9uLmZyb21EaXZQaXhlbFRvTGF0TG5nKGJsUGl4KTtcblxuICAgIC8vIEV4dGVuZCB0aGUgYm91bmRzIHRvIGNvbnRhaW4gdGhlIG5ldyBib3VuZHMuXG4gICAgYm91bmRzLmV4dGVuZChuZSk7XG4gICAgYm91bmRzLmV4dGVuZChzdyk7XG5cbiAgICByZXR1cm4gYm91bmRzO1xufTtcblxuXG4vKipcbiAqIERldGVybWlucyBpZiBhIG1hcmtlciBpcyBjb250YWluZWQgaW4gYSBib3VuZHMuXG4gKlxuICogQHBhcmFtIHtnb29nbGUubWFwcy5NYXJrZXJ9IG1hcmtlciBUaGUgbWFya2VyIHRvIGNoZWNrLlxuICogQHBhcmFtIHtnb29nbGUubWFwcy5MYXRMbmdCb3VuZHN9IGJvdW5kcyBUaGUgYm91bmRzIHRvIGNoZWNrIGFnYWluc3QuXG4gKiBAcmV0dXJuIHtib29sZWFufSBUcnVlIGlmIHRoZSBtYXJrZXIgaXMgaW4gdGhlIGJvdW5kcy5cbiAqIEBwcml2YXRlXG4gKi9cbk1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGUuaXNNYXJrZXJJbkJvdW5kc18gPSBmdW5jdGlvbihtYXJrZXIsIGJvdW5kcykge1xuICAgIHJldHVybiBib3VuZHMuY29udGFpbnMobWFya2VyLmdldFBvc2l0aW9uKCkpO1xufTtcblxuXG4vKipcbiAqIENsZWFycyBhbGwgY2x1c3RlcnMgYW5kIG1hcmtlcnMgZnJvbSB0aGUgY2x1c3RlcmVyLlxuICovXG5NYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlLmNsZWFyTWFya2VycyA9IGZ1bmN0aW9uKCkge1xuICAgIHRoaXMucmVzZXRWaWV3cG9ydCh0cnVlKTtcblxuICAgIC8vIFNldCB0aGUgbWFya2VycyBhIGVtcHR5IGFycmF5LlxuICAgIHRoaXMubWFya2Vyc18gPSBbXTtcbn07XG5cblxuLyoqXG4gKiBDbGVhcnMgYWxsIGV4aXN0aW5nIGNsdXN0ZXJzIGFuZCByZWNyZWF0ZXMgdGhlbS5cbiAqIEBwYXJhbSB7Ym9vbGVhbn0gb3B0X2hpZGUgVG8gYWxzbyBoaWRlIHRoZSBtYXJrZXIuXG4gKi9cbk1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGUucmVzZXRWaWV3cG9ydCA9IGZ1bmN0aW9uKG9wdF9oaWRlKSB7XG4gICAgLy8gUmVtb3ZlIGFsbCB0aGUgY2x1c3RlcnNcbiAgICBmb3IgKHZhciBpID0gMCwgY2x1c3RlcjsgY2x1c3RlciA9IHRoaXMuY2x1c3RlcnNfW2ldOyBpKyspIHtcbiAgICAgICAgY2x1c3Rlci5yZW1vdmUoKTtcbiAgICB9XG5cbiAgICAvLyBSZXNldCB0aGUgbWFya2VycyB0byBub3QgYmUgYWRkZWQgYW5kIHRvIGJlIGludmlzaWJsZS5cbiAgICBmb3IgKHZhciBpID0gMCwgbWFya2VyOyBtYXJrZXIgPSB0aGlzLm1hcmtlcnNfW2ldOyBpKyspIHtcbiAgICAgICAgbWFya2VyLmlzQWRkZWQgPSBmYWxzZTtcbiAgICAgICAgaWYgKG9wdF9oaWRlKSB7XG4gICAgICAgICAgICBtYXJrZXIuc2V0TWFwKG51bGwpO1xuICAgICAgICB9XG4gICAgfVxuXG4gICAgdGhpcy5jbHVzdGVyc18gPSBbXTtcbn07XG5cbi8qKlxuICpcbiAqL1xuTWFya2VyQ2x1c3RlcmVyLnByb3RvdHlwZS5yZXBhaW50ID0gZnVuY3Rpb24oKSB7XG4gICAgdmFyIG9sZENsdXN0ZXJzID0gdGhpcy5jbHVzdGVyc18uc2xpY2UoKTtcbiAgICB0aGlzLmNsdXN0ZXJzXy5sZW5ndGggPSAwO1xuICAgIHRoaXMucmVzZXRWaWV3cG9ydCgpO1xuICAgIHRoaXMucmVkcmF3KCk7XG5cbiAgICAvLyBSZW1vdmUgdGhlIG9sZCBjbHVzdGVycy5cbiAgICAvLyBEbyBpdCBpbiBhIHRpbWVvdXQgc28gdGhlIG90aGVyIGNsdXN0ZXJzIGhhdmUgYmVlbiBkcmF3biBmaXJzdC5cbiAgICB3aW5kb3cuc2V0VGltZW91dChmdW5jdGlvbigpIHtcbiAgICAgICAgZm9yICh2YXIgaSA9IDAsIGNsdXN0ZXI7IGNsdXN0ZXIgPSBvbGRDbHVzdGVyc1tpXTsgaSsrKSB7XG4gICAgICAgICAgICBjbHVzdGVyLnJlbW92ZSgpO1xuICAgICAgICB9XG4gICAgfSwgMCk7XG59O1xuXG5cbi8qKlxuICogUmVkcmF3cyB0aGUgY2x1c3RlcnMuXG4gKi9cbk1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGUucmVkcmF3ID0gZnVuY3Rpb24oKSB7XG4gICAgdGhpcy5jcmVhdGVDbHVzdGVyc18oKTtcbn07XG5cblxuLyoqXG4gKiBDYWxjdWxhdGVzIHRoZSBkaXN0YW5jZSBiZXR3ZWVuIHR3byBsYXRsbmcgbG9jYXRpb25zIGluIGttLlxuICogQHNlZSBodHRwOi8vd3d3Lm1vdmFibGUtdHlwZS5jby51ay9zY3JpcHRzL2xhdGxvbmcuaHRtbFxuICpcbiAqIEBwYXJhbSB7Z29vZ2xlLm1hcHMuTGF0TG5nfSBwMSBUaGUgZmlyc3QgbGF0IGxuZyBwb2ludC5cbiAqIEBwYXJhbSB7Z29vZ2xlLm1hcHMuTGF0TG5nfSBwMiBUaGUgc2Vjb25kIGxhdCBsbmcgcG9pbnQuXG4gKiBAcmV0dXJuIHtudW1iZXJ9IFRoZSBkaXN0YW5jZSBiZXR3ZWVuIHRoZSB0d28gcG9pbnRzIGluIGttLlxuICogQHByaXZhdGVcbiAqL1xuTWFya2VyQ2x1c3RlcmVyLnByb3RvdHlwZS5kaXN0YW5jZUJldHdlZW5Qb2ludHNfID0gZnVuY3Rpb24ocDEsIHAyKSB7XG4gICAgaWYgKCFwMSB8fCAhcDIpIHtcbiAgICAgICAgcmV0dXJuIDA7XG4gICAgfVxuXG4gICAgdmFyIFIgPSA2MzcxOyAvLyBSYWRpdXMgb2YgdGhlIEVhcnRoIGluIGttXG4gICAgdmFyIGRMYXQgPSAocDIubGF0KCkgLSBwMS5sYXQoKSkgKiBNYXRoLlBJIC8gMTgwO1xuICAgIHZhciBkTG9uID0gKHAyLmxuZygpIC0gcDEubG5nKCkpICogTWF0aC5QSSAvIDE4MDtcbiAgICB2YXIgYSA9IE1hdGguc2luKGRMYXQgLyAyKSAqIE1hdGguc2luKGRMYXQgLyAyKSArXG4gICAgICAgIE1hdGguY29zKHAxLmxhdCgpICogTWF0aC5QSSAvIDE4MCkgKiBNYXRoLmNvcyhwMi5sYXQoKSAqIE1hdGguUEkgLyAxODApICpcbiAgICAgICAgTWF0aC5zaW4oZExvbiAvIDIpICogTWF0aC5zaW4oZExvbiAvIDIpO1xuICAgIHZhciBjID0gMiAqIE1hdGguYXRhbjIoTWF0aC5zcXJ0KGEpLCBNYXRoLnNxcnQoMSAtIGEpKTtcbiAgICB2YXIgZCA9IFIgKiBjO1xuICAgIHJldHVybiBkO1xufTtcblxuXG4vKipcbiAqIEFkZCBhIG1hcmtlciB0byBhIGNsdXN0ZXIsIG9yIGNyZWF0ZXMgYSBuZXcgY2x1c3Rlci5cbiAqXG4gKiBAcGFyYW0ge2dvb2dsZS5tYXBzLk1hcmtlcn0gbWFya2VyIFRoZSBtYXJrZXIgdG8gYWRkLlxuICogQHByaXZhdGVcbiAqL1xuTWFya2VyQ2x1c3RlcmVyLnByb3RvdHlwZS5hZGRUb0Nsb3Nlc3RDbHVzdGVyXyA9IGZ1bmN0aW9uKG1hcmtlcikge1xuICAgIHZhciBkaXN0YW5jZSA9IDQwMDAwOyAvLyBTb21lIGxhcmdlIG51bWJlclxuICAgIHZhciBjbHVzdGVyVG9BZGRUbyA9IG51bGw7XG4gICAgdmFyIHBvcyA9IG1hcmtlci5nZXRQb3NpdGlvbigpO1xuICAgIGZvciAodmFyIGkgPSAwLCBjbHVzdGVyOyBjbHVzdGVyID0gdGhpcy5jbHVzdGVyc19baV07IGkrKykge1xuICAgICAgICB2YXIgY2VudGVyID0gY2x1c3Rlci5nZXRDZW50ZXIoKTtcbiAgICAgICAgaWYgKGNlbnRlcikge1xuICAgICAgICAgICAgdmFyIGQgPSB0aGlzLmRpc3RhbmNlQmV0d2VlblBvaW50c18oY2VudGVyLCBtYXJrZXIuZ2V0UG9zaXRpb24oKSk7XG4gICAgICAgICAgICBpZiAoZCA8IGRpc3RhbmNlKSB7XG4gICAgICAgICAgICAgICAgZGlzdGFuY2UgPSBkO1xuICAgICAgICAgICAgICAgIGNsdXN0ZXJUb0FkZFRvID0gY2x1c3RlcjtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfVxuICAgIH1cblxuICAgIGlmIChjbHVzdGVyVG9BZGRUbyAmJiBjbHVzdGVyVG9BZGRUby5pc01hcmtlckluQ2x1c3RlckJvdW5kcyhtYXJrZXIpKSB7XG4gICAgICAgIGNsdXN0ZXJUb0FkZFRvLmFkZE1hcmtlcihtYXJrZXIpO1xuICAgIH0gZWxzZSB7XG4gICAgICAgIHZhciBjbHVzdGVyID0gbmV3IENsdXN0ZXIodGhpcyk7XG4gICAgICAgIGNsdXN0ZXIuYWRkTWFya2VyKG1hcmtlcik7XG4gICAgICAgIHRoaXMuY2x1c3RlcnNfLnB1c2goY2x1c3Rlcik7XG4gICAgfVxufTtcblxuXG4vKipcbiAqIENyZWF0ZXMgdGhlIGNsdXN0ZXJzLlxuICpcbiAqIEBwcml2YXRlXG4gKi9cbk1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGUuY3JlYXRlQ2x1c3RlcnNfID0gZnVuY3Rpb24oKSB7XG4gICAgaWYgKCF0aGlzLnJlYWR5Xykge1xuICAgICAgICByZXR1cm47XG4gICAgfVxuXG4gICAgLy8gR2V0IG91ciBjdXJyZW50IG1hcCB2aWV3IGJvdW5kcy5cbiAgICAvLyBDcmVhdGUgYSBuZXcgYm91bmRzIG9iamVjdCBzbyB3ZSBkb24ndCBhZmZlY3QgdGhlIG1hcC5cbiAgICB2YXIgbWFwQm91bmRzID0gbmV3IGdvb2dsZS5tYXBzLkxhdExuZ0JvdW5kcyh0aGlzLm1hcF8uZ2V0Qm91bmRzKCkuZ2V0U291dGhXZXN0KCksXG4gICAgICAgIHRoaXMubWFwXy5nZXRCb3VuZHMoKS5nZXROb3J0aEVhc3QoKSk7XG4gICAgdmFyIGJvdW5kcyA9IHRoaXMuZ2V0RXh0ZW5kZWRCb3VuZHMobWFwQm91bmRzKTtcblxuICAgIGZvciAodmFyIGkgPSAwLCBtYXJrZXI7IG1hcmtlciA9IHRoaXMubWFya2Vyc19baV07IGkrKykge1xuICAgICAgICBpZiAoIW1hcmtlci5pc0FkZGVkICYmIHRoaXMuaXNNYXJrZXJJbkJvdW5kc18obWFya2VyLCBib3VuZHMpKSB7XG4gICAgICAgICAgICB0aGlzLmFkZFRvQ2xvc2VzdENsdXN0ZXJfKG1hcmtlcik7XG4gICAgICAgIH1cbiAgICB9XG59O1xuXG5cbi8qKlxuICogQSBjbHVzdGVyIHRoYXQgY29udGFpbnMgbWFya2Vycy5cbiAqXG4gKiBAcGFyYW0ge01hcmtlckNsdXN0ZXJlcn0gbWFya2VyQ2x1c3RlcmVyIFRoZSBtYXJrZXJjbHVzdGVyZXIgdGhhdCB0aGlzXG4gKiAgICAgY2x1c3RlciBpcyBhc3NvY2lhdGVkIHdpdGguXG4gKiBAY29uc3RydWN0b3JcbiAqIEBpZ25vcmVcbiAqL1xuZnVuY3Rpb24gQ2x1c3RlcihtYXJrZXJDbHVzdGVyZXIpIHtcbiAgICB0aGlzLm1hcmtlckNsdXN0ZXJlcl8gPSBtYXJrZXJDbHVzdGVyZXI7XG4gICAgdGhpcy5tYXBfID0gbWFya2VyQ2x1c3RlcmVyLmdldE1hcCgpO1xuICAgIHRoaXMuZ3JpZFNpemVfID0gbWFya2VyQ2x1c3RlcmVyLmdldEdyaWRTaXplKCk7XG4gICAgdGhpcy5taW5DbHVzdGVyU2l6ZV8gPSBtYXJrZXJDbHVzdGVyZXIuZ2V0TWluQ2x1c3RlclNpemUoKTtcbiAgICB0aGlzLmF2ZXJhZ2VDZW50ZXJfID0gbWFya2VyQ2x1c3RlcmVyLmlzQXZlcmFnZUNlbnRlcigpO1xuICAgIHRoaXMuY2VudGVyXyA9IG51bGw7XG4gICAgdGhpcy5tYXJrZXJzXyA9IFtdO1xuICAgIHRoaXMuYm91bmRzXyA9IG51bGw7XG4gICAgdGhpcy5jbHVzdGVySWNvbl8gPSBuZXcgQ2x1c3Rlckljb24odGhpcywgbWFya2VyQ2x1c3RlcmVyLmdldFN0eWxlcygpLFxuICAgICAgICBtYXJrZXJDbHVzdGVyZXIuZ2V0R3JpZFNpemUoKSk7XG59XG5cbi8qKlxuICogRGV0ZXJtaW5zIGlmIGEgbWFya2VyIGlzIGFscmVhZHkgYWRkZWQgdG8gdGhlIGNsdXN0ZXIuXG4gKlxuICogQHBhcmFtIHtnb29nbGUubWFwcy5NYXJrZXJ9IG1hcmtlciBUaGUgbWFya2VyIHRvIGNoZWNrLlxuICogQHJldHVybiB7Ym9vbGVhbn0gVHJ1ZSBpZiB0aGUgbWFya2VyIGlzIGFscmVhZHkgYWRkZWQuXG4gKi9cbkNsdXN0ZXIucHJvdG90eXBlLmlzTWFya2VyQWxyZWFkeUFkZGVkID0gZnVuY3Rpb24obWFya2VyKSB7XG4gICAgaWYgKHRoaXMubWFya2Vyc18uaW5kZXhPZikge1xuICAgICAgICByZXR1cm4gdGhpcy5tYXJrZXJzXy5pbmRleE9mKG1hcmtlcikgIT0gLTE7XG4gICAgfSBlbHNlIHtcbiAgICAgICAgZm9yICh2YXIgaSA9IDAsIG07IG0gPSB0aGlzLm1hcmtlcnNfW2ldOyBpKyspIHtcbiAgICAgICAgICAgIGlmIChtID09IG1hcmtlcikge1xuICAgICAgICAgICAgICAgIHJldHVybiB0cnVlO1xuICAgICAgICAgICAgfVxuICAgICAgICB9XG4gICAgfVxuICAgIHJldHVybiBmYWxzZTtcbn07XG5cblxuLyoqXG4gKiBBZGQgYSBtYXJrZXIgdGhlIGNsdXN0ZXIuXG4gKlxuICogQHBhcmFtIHtnb29nbGUubWFwcy5NYXJrZXJ9IG1hcmtlciBUaGUgbWFya2VyIHRvIGFkZC5cbiAqIEByZXR1cm4ge2Jvb2xlYW59IFRydWUgaWYgdGhlIG1hcmtlciB3YXMgYWRkZWQuXG4gKi9cbkNsdXN0ZXIucHJvdG90eXBlLmFkZE1hcmtlciA9IGZ1bmN0aW9uKG1hcmtlcikge1xuICAgIGlmICh0aGlzLmlzTWFya2VyQWxyZWFkeUFkZGVkKG1hcmtlcikpIHtcbiAgICAgICAgcmV0dXJuIGZhbHNlO1xuICAgIH1cblxuICAgIGlmICghdGhpcy5jZW50ZXJfKSB7XG4gICAgICAgIHRoaXMuY2VudGVyXyA9IG1hcmtlci5nZXRQb3NpdGlvbigpO1xuICAgICAgICB0aGlzLmNhbGN1bGF0ZUJvdW5kc18oKTtcbiAgICB9IGVsc2Uge1xuICAgICAgICBpZiAodGhpcy5hdmVyYWdlQ2VudGVyXykge1xuICAgICAgICAgICAgdmFyIGwgPSB0aGlzLm1hcmtlcnNfLmxlbmd0aCArIDE7XG4gICAgICAgICAgICB2YXIgbGF0ID0gKHRoaXMuY2VudGVyXy5sYXQoKSAqIChsLTEpICsgbWFya2VyLmdldFBvc2l0aW9uKCkubGF0KCkpIC8gbDtcbiAgICAgICAgICAgIHZhciBsbmcgPSAodGhpcy5jZW50ZXJfLmxuZygpICogKGwtMSkgKyBtYXJrZXIuZ2V0UG9zaXRpb24oKS5sbmcoKSkgLyBsO1xuICAgICAgICAgICAgdGhpcy5jZW50ZXJfID0gbmV3IGdvb2dsZS5tYXBzLkxhdExuZyhsYXQsIGxuZyk7XG4gICAgICAgICAgICB0aGlzLmNhbGN1bGF0ZUJvdW5kc18oKTtcbiAgICAgICAgfVxuICAgIH1cblxuICAgIG1hcmtlci5pc0FkZGVkID0gdHJ1ZTtcbiAgICB0aGlzLm1hcmtlcnNfLnB1c2gobWFya2VyKTtcblxuICAgIHZhciBsZW4gPSB0aGlzLm1hcmtlcnNfLmxlbmd0aDtcbiAgICBpZiAobGVuIDwgdGhpcy5taW5DbHVzdGVyU2l6ZV8gJiYgbWFya2VyLmdldE1hcCgpICE9IHRoaXMubWFwXykge1xuICAgICAgICAvLyBNaW4gY2x1c3RlciBzaXplIG5vdCByZWFjaGVkIHNvIHNob3cgdGhlIG1hcmtlci5cbiAgICAgICAgbWFya2VyLnNldE1hcCh0aGlzLm1hcF8pO1xuICAgIH1cblxuICAgIGlmIChsZW4gPT0gdGhpcy5taW5DbHVzdGVyU2l6ZV8pIHtcbiAgICAgICAgLy8gSGlkZSB0aGUgbWFya2VycyB0aGF0IHdlcmUgc2hvd2luZy5cbiAgICAgICAgZm9yICh2YXIgaSA9IDA7IGkgPCBsZW47IGkrKykge1xuICAgICAgICAgICAgdGhpcy5tYXJrZXJzX1tpXS5zZXRNYXAobnVsbCk7XG4gICAgICAgIH1cbiAgICB9XG5cbiAgICBpZiAobGVuID49IHRoaXMubWluQ2x1c3RlclNpemVfKSB7XG4gICAgICAgIG1hcmtlci5zZXRNYXAobnVsbCk7XG4gICAgfVxuXG4gICAgdGhpcy51cGRhdGVJY29uKCk7XG4gICAgcmV0dXJuIHRydWU7XG59O1xuXG5cbi8qKlxuICogUmV0dXJucyB0aGUgbWFya2VyIGNsdXN0ZXJlciB0aGF0IHRoZSBjbHVzdGVyIGlzIGFzc29jaWF0ZWQgd2l0aC5cbiAqXG4gKiBAcmV0dXJuIHtNYXJrZXJDbHVzdGVyZXJ9IFRoZSBhc3NvY2lhdGVkIG1hcmtlciBjbHVzdGVyZXIuXG4gKi9cbkNsdXN0ZXIucHJvdG90eXBlLmdldE1hcmtlckNsdXN0ZXJlciA9IGZ1bmN0aW9uKCkge1xuICAgIHJldHVybiB0aGlzLm1hcmtlckNsdXN0ZXJlcl87XG59O1xuXG5cbi8qKlxuICogUmV0dXJucyB0aGUgYm91bmRzIG9mIHRoZSBjbHVzdGVyLlxuICpcbiAqIEByZXR1cm4ge2dvb2dsZS5tYXBzLkxhdExuZ0JvdW5kc30gdGhlIGNsdXN0ZXIgYm91bmRzLlxuICovXG5DbHVzdGVyLnByb3RvdHlwZS5nZXRCb3VuZHMgPSBmdW5jdGlvbigpIHtcbiAgICB2YXIgYm91bmRzID0gbmV3IGdvb2dsZS5tYXBzLkxhdExuZ0JvdW5kcyh0aGlzLmNlbnRlcl8sIHRoaXMuY2VudGVyXyk7XG4gICAgdmFyIG1hcmtlcnMgPSB0aGlzLmdldE1hcmtlcnMoKTtcbiAgICBmb3IgKHZhciBpID0gMCwgbWFya2VyOyBtYXJrZXIgPSBtYXJrZXJzW2ldOyBpKyspIHtcbiAgICAgICAgYm91bmRzLmV4dGVuZChtYXJrZXIuZ2V0UG9zaXRpb24oKSk7XG4gICAgfVxuICAgIHJldHVybiBib3VuZHM7XG59O1xuXG5cbi8qKlxuICogUmVtb3ZlcyB0aGUgY2x1c3RlclxuICovXG5DbHVzdGVyLnByb3RvdHlwZS5yZW1vdmUgPSBmdW5jdGlvbigpIHtcbiAgICB0aGlzLmNsdXN0ZXJJY29uXy5yZW1vdmUoKTtcbiAgICB0aGlzLm1hcmtlcnNfLmxlbmd0aCA9IDA7XG4gICAgZGVsZXRlIHRoaXMubWFya2Vyc187XG59O1xuXG5cbi8qKlxuICogUmV0dXJucyB0aGUgbnVtYmVyIG9mIG1hcmtlcnMgaW4gdGhlIGNsdXN0ZXIuXG4gKlxuICogQHJldHVybiB7bnVtYmVyfSBUaGUgbnVtYmVyIG9mIG1hcmtlcnMgaW4gdGhlIGNsdXN0ZXIuXG4gKi9cbkNsdXN0ZXIucHJvdG90eXBlLmdldFNpemUgPSBmdW5jdGlvbigpIHtcbiAgICByZXR1cm4gdGhpcy5tYXJrZXJzXy5sZW5ndGg7XG59O1xuXG5cbi8qKlxuICogUmV0dXJucyBhIGxpc3Qgb2YgdGhlIG1hcmtlcnMgaW4gdGhlIGNsdXN0ZXIuXG4gKlxuICogQHJldHVybiB7QXJyYXkuPGdvb2dsZS5tYXBzLk1hcmtlcj59IFRoZSBtYXJrZXJzIGluIHRoZSBjbHVzdGVyLlxuICovXG5DbHVzdGVyLnByb3RvdHlwZS5nZXRNYXJrZXJzID0gZnVuY3Rpb24oKSB7XG4gICAgcmV0dXJuIHRoaXMubWFya2Vyc187XG59O1xuXG5cbi8qKlxuICogUmV0dXJucyB0aGUgY2VudGVyIG9mIHRoZSBjbHVzdGVyLlxuICpcbiAqIEByZXR1cm4ge2dvb2dsZS5tYXBzLkxhdExuZ30gVGhlIGNsdXN0ZXIgY2VudGVyLlxuICovXG5DbHVzdGVyLnByb3RvdHlwZS5nZXRDZW50ZXIgPSBmdW5jdGlvbigpIHtcbiAgICByZXR1cm4gdGhpcy5jZW50ZXJfO1xufTtcblxuXG4vKipcbiAqIENhbGN1bGF0ZWQgdGhlIGV4dGVuZGVkIGJvdW5kcyBvZiB0aGUgY2x1c3RlciB3aXRoIHRoZSBncmlkLlxuICpcbiAqIEBwcml2YXRlXG4gKi9cbkNsdXN0ZXIucHJvdG90eXBlLmNhbGN1bGF0ZUJvdW5kc18gPSBmdW5jdGlvbigpIHtcbiAgICB2YXIgYm91bmRzID0gbmV3IGdvb2dsZS5tYXBzLkxhdExuZ0JvdW5kcyh0aGlzLmNlbnRlcl8sIHRoaXMuY2VudGVyXyk7XG4gICAgdGhpcy5ib3VuZHNfID0gdGhpcy5tYXJrZXJDbHVzdGVyZXJfLmdldEV4dGVuZGVkQm91bmRzKGJvdW5kcyk7XG59O1xuXG5cbi8qKlxuICogRGV0ZXJtaW5lcyBpZiBhIG1hcmtlciBsaWVzIGluIHRoZSBjbHVzdGVycyBib3VuZHMuXG4gKlxuICogQHBhcmFtIHtnb29nbGUubWFwcy5NYXJrZXJ9IG1hcmtlciBUaGUgbWFya2VyIHRvIGNoZWNrLlxuICogQHJldHVybiB7Ym9vbGVhbn0gVHJ1ZSBpZiB0aGUgbWFya2VyIGxpZXMgaW4gdGhlIGJvdW5kcy5cbiAqL1xuQ2x1c3Rlci5wcm90b3R5cGUuaXNNYXJrZXJJbkNsdXN0ZXJCb3VuZHMgPSBmdW5jdGlvbihtYXJrZXIpIHtcbiAgICByZXR1cm4gdGhpcy5ib3VuZHNfLmNvbnRhaW5zKG1hcmtlci5nZXRQb3NpdGlvbigpKTtcbn07XG5cblxuLyoqXG4gKiBSZXR1cm5zIHRoZSBtYXAgdGhhdCB0aGUgY2x1c3RlciBpcyBhc3NvY2lhdGVkIHdpdGguXG4gKlxuICogQHJldHVybiB7Z29vZ2xlLm1hcHMuTWFwfSBUaGUgbWFwLlxuICovXG5DbHVzdGVyLnByb3RvdHlwZS5nZXRNYXAgPSBmdW5jdGlvbigpIHtcbiAgICByZXR1cm4gdGhpcy5tYXBfO1xufTtcblxuXG4vKipcbiAqIFVwZGF0ZXMgdGhlIGNsdXN0ZXIgaWNvblxuICovXG5DbHVzdGVyLnByb3RvdHlwZS51cGRhdGVJY29uID0gZnVuY3Rpb24oKSB7XG4gICAgdmFyIHpvb20gPSB0aGlzLm1hcF8uZ2V0Wm9vbSgpO1xuICAgIHZhciBteiA9IHRoaXMubWFya2VyQ2x1c3RlcmVyXy5nZXRNYXhab29tKCk7XG5cbiAgICBpZiAobXogJiYgem9vbSA+IG16KSB7XG4gICAgICAgIC8vIFRoZSB6b29tIGlzIGdyZWF0ZXIgdGhhbiBvdXIgbWF4IHpvb20gc28gc2hvdyBhbGwgdGhlIG1hcmtlcnMgaW4gY2x1c3Rlci5cbiAgICAgICAgZm9yICh2YXIgaSA9IDAsIG1hcmtlcjsgbWFya2VyID0gdGhpcy5tYXJrZXJzX1tpXTsgaSsrKSB7XG4gICAgICAgICAgICBtYXJrZXIuc2V0TWFwKHRoaXMubWFwXyk7XG4gICAgICAgIH1cbiAgICAgICAgcmV0dXJuO1xuICAgIH1cblxuICAgIGlmICh0aGlzLm1hcmtlcnNfLmxlbmd0aCA8IHRoaXMubWluQ2x1c3RlclNpemVfKSB7XG4gICAgICAgIC8vIE1pbiBjbHVzdGVyIHNpemUgbm90IHlldCByZWFjaGVkLlxuICAgICAgICB0aGlzLmNsdXN0ZXJJY29uXy5oaWRlKCk7XG4gICAgICAgIHJldHVybjtcbiAgICB9XG5cbiAgICB2YXIgbnVtU3R5bGVzID0gdGhpcy5tYXJrZXJDbHVzdGVyZXJfLmdldFN0eWxlcygpLmxlbmd0aDtcbiAgICB2YXIgc3VtcyA9IHRoaXMubWFya2VyQ2x1c3RlcmVyXy5nZXRDYWxjdWxhdG9yKCkodGhpcy5tYXJrZXJzXywgbnVtU3R5bGVzKTtcbiAgICB0aGlzLmNsdXN0ZXJJY29uXy5zZXRDZW50ZXIodGhpcy5jZW50ZXJfKTtcbiAgICB0aGlzLmNsdXN0ZXJJY29uXy5zZXRTdW1zKHN1bXMpO1xuICAgIHRoaXMuY2x1c3Rlckljb25fLnNob3coKTtcbn07XG5cblxuLyoqXG4gKiBBIGNsdXN0ZXIgaWNvblxuICpcbiAqIEBwYXJhbSB7Q2x1c3Rlcn0gY2x1c3RlciBUaGUgY2x1c3RlciB0byBiZSBhc3NvY2lhdGVkIHdpdGguXG4gKiBAcGFyYW0ge09iamVjdH0gc3R5bGVzIEFuIG9iamVjdCB0aGF0IGhhcyBzdHlsZSBwcm9wZXJ0aWVzOlxuICogICAgICd1cmwnOiAoc3RyaW5nKSBUaGUgaW1hZ2UgdXJsLlxuICogICAgICdoZWlnaHQnOiAobnVtYmVyKSBUaGUgaW1hZ2UgaGVpZ2h0LlxuICogICAgICd3aWR0aCc6IChudW1iZXIpIFRoZSBpbWFnZSB3aWR0aC5cbiAqICAgICAnYW5jaG9yJzogKEFycmF5KSBUaGUgYW5jaG9yIHBvc2l0aW9uIG9mIHRoZSBsYWJlbCB0ZXh0LlxuICogICAgICd0ZXh0Q29sb3InOiAoc3RyaW5nKSBUaGUgdGV4dCBjb2xvci5cbiAqICAgICAndGV4dFNpemUnOiAobnVtYmVyKSBUaGUgdGV4dCBzaXplLlxuICogICAgICdiYWNrZ3JvdW5kUG9zaXRpb246IChzdHJpbmcpIFRoZSBiYWNrZ3JvdW5kIHBvc3RpdGlvbiB4LCB5LlxuICogQHBhcmFtIHtudW1iZXI9fSBvcHRfcGFkZGluZyBPcHRpb25hbCBwYWRkaW5nIHRvIGFwcGx5IHRvIHRoZSBjbHVzdGVyIGljb24uXG4gKiBAY29uc3RydWN0b3JcbiAqIEBleHRlbmRzIGdvb2dsZS5tYXBzLk92ZXJsYXlWaWV3XG4gKiBAaWdub3JlXG4gKi9cbmZ1bmN0aW9uIENsdXN0ZXJJY29uKGNsdXN0ZXIsIHN0eWxlcywgb3B0X3BhZGRpbmcpIHtcbiAgICBjbHVzdGVyLmdldE1hcmtlckNsdXN0ZXJlcigpLmV4dGVuZChDbHVzdGVySWNvbiwgZ29vZ2xlLm1hcHMuT3ZlcmxheVZpZXcpO1xuXG4gICAgdGhpcy5zdHlsZXNfID0gc3R5bGVzO1xuICAgIHRoaXMucGFkZGluZ18gPSBvcHRfcGFkZGluZyB8fCAwO1xuICAgIHRoaXMuY2x1c3Rlcl8gPSBjbHVzdGVyO1xuICAgIHRoaXMuY2VudGVyXyA9IG51bGw7XG4gICAgdGhpcy5tYXBfID0gY2x1c3Rlci5nZXRNYXAoKTtcbiAgICB0aGlzLmRpdl8gPSBudWxsO1xuICAgIHRoaXMuc3Vtc18gPSBudWxsO1xuICAgIHRoaXMudmlzaWJsZV8gPSBmYWxzZTtcblxuICAgIHRoaXMuc2V0TWFwKHRoaXMubWFwXyk7XG59XG5cblxuLyoqXG4gKiBUcmlnZ2VycyB0aGUgY2x1c3RlcmNsaWNrIGV2ZW50IGFuZCB6b29tJ3MgaWYgdGhlIG9wdGlvbiBpcyBzZXQuXG4gKi9cbkNsdXN0ZXJJY29uLnByb3RvdHlwZS50cmlnZ2VyQ2x1c3RlckNsaWNrID0gZnVuY3Rpb24oKSB7XG4gICAgdmFyIG1hcmtlckNsdXN0ZXJlciA9IHRoaXMuY2x1c3Rlcl8uZ2V0TWFya2VyQ2x1c3RlcmVyKCk7XG5cbiAgICAvLyBUcmlnZ2VyIHRoZSBjbHVzdGVyY2xpY2sgZXZlbnQuXG4gICAgZ29vZ2xlLm1hcHMuZXZlbnQudHJpZ2dlcihtYXJrZXJDbHVzdGVyZXIubWFwXywgJ2NsdXN0ZXJjbGljaycsIHRoaXMuY2x1c3Rlcl8pO1xuXG4gICAgaWYgKG1hcmtlckNsdXN0ZXJlci5pc1pvb21PbkNsaWNrKCkpIHtcbiAgICAgICAgLy8gWm9vbSBpbnRvIHRoZSBjbHVzdGVyLlxuICAgICAgICB0aGlzLm1hcF8uZml0Qm91bmRzKHRoaXMuY2x1c3Rlcl8uZ2V0Qm91bmRzKCkpO1xuICAgIH1cbn07XG5cblxuLyoqXG4gKiBBZGRpbmcgdGhlIGNsdXN0ZXIgaWNvbiB0byB0aGUgZG9tLlxuICogQGlnbm9yZVxuICovXG5DbHVzdGVySWNvbi5wcm90b3R5cGUub25BZGQgPSBmdW5jdGlvbigpIHtcbiAgICB0aGlzLmRpdl8gPSBkb2N1bWVudC5jcmVhdGVFbGVtZW50KCdESVYnKTtcbiAgICBpZiAodGhpcy52aXNpYmxlXykge1xuICAgICAgICB2YXIgcG9zID0gdGhpcy5nZXRQb3NGcm9tTGF0TG5nXyh0aGlzLmNlbnRlcl8pO1xuICAgICAgICB0aGlzLmRpdl8uc3R5bGUuY3NzVGV4dCA9IHRoaXMuY3JlYXRlQ3NzKHBvcyk7XG4gICAgICAgIHRoaXMuZGl2Xy5pbm5lckhUTUwgPSB0aGlzLnN1bXNfLnRleHQ7XG4gICAgfVxuXG4gICAgdmFyIHBhbmVzID0gdGhpcy5nZXRQYW5lcygpO1xuICAgIHBhbmVzLm92ZXJsYXlNb3VzZVRhcmdldC5hcHBlbmRDaGlsZCh0aGlzLmRpdl8pO1xuXG4gICAgdmFyIHRoYXQgPSB0aGlzO1xuICAgIGdvb2dsZS5tYXBzLmV2ZW50LmFkZERvbUxpc3RlbmVyKHRoaXMuZGl2XywgJ2NsaWNrJywgZnVuY3Rpb24oKSB7XG4gICAgICAgIHRoYXQudHJpZ2dlckNsdXN0ZXJDbGljaygpO1xuICAgIH0pO1xufTtcblxuXG4vKipcbiAqIFJldHVybnMgdGhlIHBvc2l0aW9uIHRvIHBsYWNlIHRoZSBkaXYgZGVuZGluZyBvbiB0aGUgbGF0bG5nLlxuICpcbiAqIEBwYXJhbSB7Z29vZ2xlLm1hcHMuTGF0TG5nfSBsYXRsbmcgVGhlIHBvc2l0aW9uIGluIGxhdGxuZy5cbiAqIEByZXR1cm4ge2dvb2dsZS5tYXBzLlBvaW50fSBUaGUgcG9zaXRpb24gaW4gcGl4ZWxzLlxuICogQHByaXZhdGVcbiAqL1xuQ2x1c3Rlckljb24ucHJvdG90eXBlLmdldFBvc0Zyb21MYXRMbmdfID0gZnVuY3Rpb24obGF0bG5nKSB7XG4gICAgdmFyIHBvcyA9IHRoaXMuZ2V0UHJvamVjdGlvbigpLmZyb21MYXRMbmdUb0RpdlBpeGVsKGxhdGxuZyk7XG4gICAgcG9zLnggLT0gcGFyc2VJbnQodGhpcy53aWR0aF8gLyAyLCAxMCk7XG4gICAgcG9zLnkgLT0gcGFyc2VJbnQodGhpcy5oZWlnaHRfIC8gMiwgMTApO1xuICAgIHJldHVybiBwb3M7XG59O1xuXG5cbi8qKlxuICogRHJhdyB0aGUgaWNvbi5cbiAqIEBpZ25vcmVcbiAqL1xuQ2x1c3Rlckljb24ucHJvdG90eXBlLmRyYXcgPSBmdW5jdGlvbigpIHtcbiAgICBpZiAodGhpcy52aXNpYmxlXykge1xuICAgICAgICB2YXIgcG9zID0gdGhpcy5nZXRQb3NGcm9tTGF0TG5nXyh0aGlzLmNlbnRlcl8pO1xuICAgICAgICB0aGlzLmRpdl8uc3R5bGUudG9wID0gcG9zLnkgKyAncHgnO1xuICAgICAgICB0aGlzLmRpdl8uc3R5bGUubGVmdCA9IHBvcy54ICsgJ3B4JztcbiAgICB9XG59O1xuXG5cbi8qKlxuICogSGlkZSB0aGUgaWNvbi5cbiAqL1xuQ2x1c3Rlckljb24ucHJvdG90eXBlLmhpZGUgPSBmdW5jdGlvbigpIHtcbiAgICBpZiAodGhpcy5kaXZfKSB7XG4gICAgICAgIHRoaXMuZGl2Xy5zdHlsZS5kaXNwbGF5ID0gJ25vbmUnO1xuICAgIH1cbiAgICB0aGlzLnZpc2libGVfID0gZmFsc2U7XG59O1xuXG5cbi8qKlxuICogUG9zaXRpb24gYW5kIHNob3cgdGhlIGljb24uXG4gKi9cbkNsdXN0ZXJJY29uLnByb3RvdHlwZS5zaG93ID0gZnVuY3Rpb24oKSB7XG4gICAgaWYgKHRoaXMuZGl2Xykge1xuICAgICAgICB2YXIgcG9zID0gdGhpcy5nZXRQb3NGcm9tTGF0TG5nXyh0aGlzLmNlbnRlcl8pO1xuICAgICAgICB0aGlzLmRpdl8uc3R5bGUuY3NzVGV4dCA9IHRoaXMuY3JlYXRlQ3NzKHBvcyk7XG4gICAgICAgIHRoaXMuZGl2Xy5zdHlsZS5kaXNwbGF5ID0gJyc7XG4gICAgfVxuICAgIHRoaXMudmlzaWJsZV8gPSB0cnVlO1xufTtcblxuXG4vKipcbiAqIFJlbW92ZSB0aGUgaWNvbiBmcm9tIHRoZSBtYXBcbiAqL1xuQ2x1c3Rlckljb24ucHJvdG90eXBlLnJlbW92ZSA9IGZ1bmN0aW9uKCkge1xuICAgIHRoaXMuc2V0TWFwKG51bGwpO1xufTtcblxuXG4vKipcbiAqIEltcGxlbWVudGF0aW9uIG9mIHRoZSBvblJlbW92ZSBpbnRlcmZhY2UuXG4gKiBAaWdub3JlXG4gKi9cbkNsdXN0ZXJJY29uLnByb3RvdHlwZS5vblJlbW92ZSA9IGZ1bmN0aW9uKCkge1xuICAgIGlmICh0aGlzLmRpdl8gJiYgdGhpcy5kaXZfLnBhcmVudE5vZGUpIHtcbiAgICAgICAgdGhpcy5oaWRlKCk7XG4gICAgICAgIHRoaXMuZGl2Xy5wYXJlbnROb2RlLnJlbW92ZUNoaWxkKHRoaXMuZGl2Xyk7XG4gICAgICAgIHRoaXMuZGl2XyA9IG51bGw7XG4gICAgfVxufTtcblxuXG4vKipcbiAqIFNldCB0aGUgc3VtcyBvZiB0aGUgaWNvbi5cbiAqXG4gKiBAcGFyYW0ge09iamVjdH0gc3VtcyBUaGUgc3VtcyBjb250YWluaW5nOlxuICogICAndGV4dCc6IChzdHJpbmcpIFRoZSB0ZXh0IHRvIGRpc3BsYXkgaW4gdGhlIGljb24uXG4gKiAgICdpbmRleCc6IChudW1iZXIpIFRoZSBzdHlsZSBpbmRleCBvZiB0aGUgaWNvbi5cbiAqL1xuQ2x1c3Rlckljb24ucHJvdG90eXBlLnNldFN1bXMgPSBmdW5jdGlvbihzdW1zKSB7XG4gICAgdGhpcy5zdW1zXyA9IHN1bXM7XG4gICAgdGhpcy50ZXh0XyA9IHN1bXMudGV4dDtcbiAgICB0aGlzLmluZGV4XyA9IHN1bXMuaW5kZXg7XG4gICAgaWYgKHRoaXMuZGl2Xykge1xuICAgICAgICB0aGlzLmRpdl8uaW5uZXJIVE1MID0gc3Vtcy50ZXh0O1xuICAgIH1cblxuICAgIHRoaXMudXNlU3R5bGUoKTtcbn07XG5cblxuLyoqXG4gKiBTZXRzIHRoZSBpY29uIHRvIHRoZSB0aGUgc3R5bGVzLlxuICovXG5DbHVzdGVySWNvbi5wcm90b3R5cGUudXNlU3R5bGUgPSBmdW5jdGlvbigpIHtcbiAgICB2YXIgaW5kZXggPSBNYXRoLm1heCgwLCB0aGlzLnN1bXNfLmluZGV4IC0gMSk7XG4gICAgaW5kZXggPSBNYXRoLm1pbih0aGlzLnN0eWxlc18ubGVuZ3RoIC0gMSwgaW5kZXgpO1xuICAgIHZhciBzdHlsZSA9IHRoaXMuc3R5bGVzX1tpbmRleF07XG4gICAgdGhpcy51cmxfID0gc3R5bGVbJ3VybCddO1xuICAgIHRoaXMuaGVpZ2h0XyA9IHN0eWxlWydoZWlnaHQnXTtcbiAgICB0aGlzLndpZHRoXyA9IHN0eWxlWyd3aWR0aCddO1xuICAgIHRoaXMudGV4dENvbG9yXyA9IHN0eWxlWyd0ZXh0Q29sb3InXTtcbiAgICB0aGlzLmFuY2hvcl8gPSBzdHlsZVsnYW5jaG9yJ107XG4gICAgdGhpcy50ZXh0U2l6ZV8gPSBzdHlsZVsndGV4dFNpemUnXTtcbiAgICB0aGlzLmJhY2tncm91bmRQb3NpdGlvbl8gPSBzdHlsZVsnYmFja2dyb3VuZFBvc2l0aW9uJ107XG59O1xuXG5cbi8qKlxuICogU2V0cyB0aGUgY2VudGVyIG9mIHRoZSBpY29uLlxuICpcbiAqIEBwYXJhbSB7Z29vZ2xlLm1hcHMuTGF0TG5nfSBjZW50ZXIgVGhlIGxhdGxuZyB0byBzZXQgYXMgdGhlIGNlbnRlci5cbiAqL1xuQ2x1c3Rlckljb24ucHJvdG90eXBlLnNldENlbnRlciA9IGZ1bmN0aW9uKGNlbnRlcikge1xuICAgIHRoaXMuY2VudGVyXyA9IGNlbnRlcjtcbn07XG5cblxuLyoqXG4gKiBDcmVhdGUgdGhlIGNzcyB0ZXh0IGJhc2VkIG9uIHRoZSBwb3NpdGlvbiBvZiB0aGUgaWNvbi5cbiAqXG4gKiBAcGFyYW0ge2dvb2dsZS5tYXBzLlBvaW50fSBwb3MgVGhlIHBvc2l0aW9uLlxuICogQHJldHVybiB7c3RyaW5nfSBUaGUgY3NzIHN0eWxlIHRleHQuXG4gKi9cbkNsdXN0ZXJJY29uLnByb3RvdHlwZS5jcmVhdGVDc3MgPSBmdW5jdGlvbihwb3MpIHtcbiAgICB2YXIgc3R5bGUgPSBbXTtcbiAgICBzdHlsZS5wdXNoKCdiYWNrZ3JvdW5kLWltYWdlOnVybCgnICsgdGhpcy51cmxfICsgJyk7Jyk7XG4gICAgdmFyIGJhY2tncm91bmRQb3NpdGlvbiA9IHRoaXMuYmFja2dyb3VuZFBvc2l0aW9uXyA/IHRoaXMuYmFja2dyb3VuZFBvc2l0aW9uXyA6ICcwIDAnO1xuICAgIHN0eWxlLnB1c2goJ2JhY2tncm91bmQtcG9zaXRpb246JyArIGJhY2tncm91bmRQb3NpdGlvbiArICc7Jyk7XG5cbiAgICBpZiAodHlwZW9mIHRoaXMuYW5jaG9yXyA9PT0gJ29iamVjdCcpIHtcbiAgICAgICAgaWYgKHR5cGVvZiB0aGlzLmFuY2hvcl9bMF0gPT09ICdudW1iZXInICYmIHRoaXMuYW5jaG9yX1swXSA+IDAgJiZcbiAgICAgICAgICAgIHRoaXMuYW5jaG9yX1swXSA8IHRoaXMuaGVpZ2h0Xykge1xuICAgICAgICAgICAgc3R5bGUucHVzaCgnaGVpZ2h0OicgKyAodGhpcy5oZWlnaHRfIC0gdGhpcy5hbmNob3JfWzBdKSArXG4gICAgICAgICAgICAgICAgJ3B4OyBwYWRkaW5nLXRvcDonICsgdGhpcy5hbmNob3JfWzBdICsgJ3B4OycpO1xuICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgc3R5bGUucHVzaCgnaGVpZ2h0OicgKyB0aGlzLmhlaWdodF8gKyAncHg7IGxpbmUtaGVpZ2h0OicgKyB0aGlzLmhlaWdodF8gK1xuICAgICAgICAgICAgICAgICdweDsnKTtcbiAgICAgICAgfVxuICAgICAgICBpZiAodHlwZW9mIHRoaXMuYW5jaG9yX1sxXSA9PT0gJ251bWJlcicgJiYgdGhpcy5hbmNob3JfWzFdID4gMCAmJlxuICAgICAgICAgICAgdGhpcy5hbmNob3JfWzFdIDwgdGhpcy53aWR0aF8pIHtcbiAgICAgICAgICAgIHN0eWxlLnB1c2goJ3dpZHRoOicgKyAodGhpcy53aWR0aF8gLSB0aGlzLmFuY2hvcl9bMV0pICtcbiAgICAgICAgICAgICAgICAncHg7IHBhZGRpbmctbGVmdDonICsgdGhpcy5hbmNob3JfWzFdICsgJ3B4OycpO1xuICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgc3R5bGUucHVzaCgnd2lkdGg6JyArIHRoaXMud2lkdGhfICsgJ3B4OyB0ZXh0LWFsaWduOmNlbnRlcjsnKTtcbiAgICAgICAgfVxuICAgIH0gZWxzZSB7XG4gICAgICAgIHN0eWxlLnB1c2goJ2hlaWdodDonICsgdGhpcy5oZWlnaHRfICsgJ3B4OyBsaW5lLWhlaWdodDonICtcbiAgICAgICAgICAgIHRoaXMuaGVpZ2h0XyArICdweDsgd2lkdGg6JyArIHRoaXMud2lkdGhfICsgJ3B4OyB0ZXh0LWFsaWduOmNlbnRlcjsnKTtcbiAgICB9XG5cbiAgICB2YXIgdHh0Q29sb3IgPSB0aGlzLnRleHRDb2xvcl8gPyB0aGlzLnRleHRDb2xvcl8gOiAnYmxhY2snO1xuICAgIHZhciB0eHRTaXplID0gdGhpcy50ZXh0U2l6ZV8gPyB0aGlzLnRleHRTaXplXyA6IDExO1xuXG4gICAgc3R5bGUucHVzaCgnY3Vyc29yOnBvaW50ZXI7IHRvcDonICsgcG9zLnkgKyAncHg7IGxlZnQ6JyArXG4gICAgICAgIHBvcy54ICsgJ3B4OyBjb2xvcjonICsgdHh0Q29sb3IgKyAnOyBwb3NpdGlvbjphYnNvbHV0ZTsgZm9udC1zaXplOicgK1xuICAgICAgICB0eHRTaXplICsgJ3B4OyBmb250LWZhbWlseTpBcmlhbCxzYW5zLXNlcmlmOyBmb250LXdlaWdodDpib2xkJyk7XG4gICAgcmV0dXJuIHN0eWxlLmpvaW4oJycpO1xufTtcblxuXG4vLyBFeHBvcnQgU3ltYm9scyBmb3IgQ2xvc3VyZVxuLy8gSWYgeW91IGFyZSBub3QgZ29pbmcgdG8gY29tcGlsZSB3aXRoIGNsb3N1cmUgdGhlbiB5b3UgY2FuIHJlbW92ZSB0aGVcbi8vIGNvZGUgYmVsb3cuXG53aW5kb3dbJ01hcmtlckNsdXN0ZXJlciddID0gTWFya2VyQ2x1c3RlcmVyO1xuTWFya2VyQ2x1c3RlcmVyLnByb3RvdHlwZVsnYWRkTWFya2VyJ10gPSBNYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlLmFkZE1hcmtlcjtcbk1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGVbJ2FkZE1hcmtlcnMnXSA9IE1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGUuYWRkTWFya2Vycztcbk1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGVbJ2NsZWFyTWFya2VycyddID1cbiAgICBNYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlLmNsZWFyTWFya2Vycztcbk1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGVbJ2ZpdE1hcFRvTWFya2VycyddID1cbiAgICBNYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlLmZpdE1hcFRvTWFya2Vycztcbk1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGVbJ2dldENhbGN1bGF0b3InXSA9XG4gICAgTWFya2VyQ2x1c3RlcmVyLnByb3RvdHlwZS5nZXRDYWxjdWxhdG9yO1xuTWFya2VyQ2x1c3RlcmVyLnByb3RvdHlwZVsnZ2V0R3JpZFNpemUnXSA9XG4gICAgTWFya2VyQ2x1c3RlcmVyLnByb3RvdHlwZS5nZXRHcmlkU2l6ZTtcbk1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGVbJ2dldEV4dGVuZGVkQm91bmRzJ10gPVxuICAgIE1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGUuZ2V0RXh0ZW5kZWRCb3VuZHM7XG5NYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlWydnZXRNYXAnXSA9IE1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGUuZ2V0TWFwO1xuTWFya2VyQ2x1c3RlcmVyLnByb3RvdHlwZVsnZ2V0TWFya2VycyddID0gTWFya2VyQ2x1c3RlcmVyLnByb3RvdHlwZS5nZXRNYXJrZXJzO1xuTWFya2VyQ2x1c3RlcmVyLnByb3RvdHlwZVsnZ2V0TWF4Wm9vbSddID0gTWFya2VyQ2x1c3RlcmVyLnByb3RvdHlwZS5nZXRNYXhab29tO1xuTWFya2VyQ2x1c3RlcmVyLnByb3RvdHlwZVsnZ2V0U3R5bGVzJ10gPSBNYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlLmdldFN0eWxlcztcbk1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGVbJ2dldFRvdGFsQ2x1c3RlcnMnXSA9XG4gICAgTWFya2VyQ2x1c3RlcmVyLnByb3RvdHlwZS5nZXRUb3RhbENsdXN0ZXJzO1xuTWFya2VyQ2x1c3RlcmVyLnByb3RvdHlwZVsnZ2V0VG90YWxNYXJrZXJzJ10gPVxuICAgIE1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGUuZ2V0VG90YWxNYXJrZXJzO1xuTWFya2VyQ2x1c3RlcmVyLnByb3RvdHlwZVsncmVkcmF3J10gPSBNYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlLnJlZHJhdztcbk1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGVbJ3JlbW92ZU1hcmtlciddID1cbiAgICBNYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlLnJlbW92ZU1hcmtlcjtcbk1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGVbJ3JlbW92ZU1hcmtlcnMnXSA9XG4gICAgTWFya2VyQ2x1c3RlcmVyLnByb3RvdHlwZS5yZW1vdmVNYXJrZXJzO1xuTWFya2VyQ2x1c3RlcmVyLnByb3RvdHlwZVsncmVzZXRWaWV3cG9ydCddID1cbiAgICBNYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlLnJlc2V0Vmlld3BvcnQ7XG5NYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlWydyZXBhaW50J10gPVxuICAgIE1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGUucmVwYWludDtcbk1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGVbJ3NldENhbGN1bGF0b3InXSA9XG4gICAgTWFya2VyQ2x1c3RlcmVyLnByb3RvdHlwZS5zZXRDYWxjdWxhdG9yO1xuTWFya2VyQ2x1c3RlcmVyLnByb3RvdHlwZVsnc2V0R3JpZFNpemUnXSA9XG4gICAgTWFya2VyQ2x1c3RlcmVyLnByb3RvdHlwZS5zZXRHcmlkU2l6ZTtcbk1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGVbJ3NldE1heFpvb20nXSA9XG4gICAgTWFya2VyQ2x1c3RlcmVyLnByb3RvdHlwZS5zZXRNYXhab29tO1xuTWFya2VyQ2x1c3RlcmVyLnByb3RvdHlwZVsnb25BZGQnXSA9IE1hcmtlckNsdXN0ZXJlci5wcm90b3R5cGUub25BZGQ7XG5NYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlWydkcmF3J10gPSBNYXJrZXJDbHVzdGVyZXIucHJvdG90eXBlLmRyYXc7XG5cbkNsdXN0ZXIucHJvdG90eXBlWydnZXRDZW50ZXInXSA9IENsdXN0ZXIucHJvdG90eXBlLmdldENlbnRlcjtcbkNsdXN0ZXIucHJvdG90eXBlWydnZXRTaXplJ10gPSBDbHVzdGVyLnByb3RvdHlwZS5nZXRTaXplO1xuQ2x1c3Rlci5wcm90b3R5cGVbJ2dldE1hcmtlcnMnXSA9IENsdXN0ZXIucHJvdG90eXBlLmdldE1hcmtlcnM7XG5cbkNsdXN0ZXJJY29uLnByb3RvdHlwZVsnb25BZGQnXSA9IENsdXN0ZXJJY29uLnByb3RvdHlwZS5vbkFkZDtcbkNsdXN0ZXJJY29uLnByb3RvdHlwZVsnZHJhdyddID0gQ2x1c3Rlckljb24ucHJvdG90eXBlLmRyYXc7XG5DbHVzdGVySWNvbi5wcm90b3R5cGVbJ29uUmVtb3ZlJ10gPSBDbHVzdGVySWNvbi5wcm90b3R5cGUub25SZW1vdmU7XG5cbk9iamVjdC5rZXlzID0gT2JqZWN0LmtleXMgfHwgZnVuY3Rpb24obykge1xuICAgICAgICB2YXIgcmVzdWx0ID0gW107XG4gICAgICAgIGZvcih2YXIgbmFtZSBpbiBvKSB7XG4gICAgICAgICAgICBpZiAoby5oYXNPd25Qcm9wZXJ0eShuYW1lKSlcbiAgICAgICAgICAgICAgICByZXN1bHQucHVzaChuYW1lKTtcbiAgICAgICAgfVxuICAgICAgICByZXR1cm4gcmVzdWx0O1xuICAgIH07IiwidmFyIHdwc2VvX2RpcmVjdGlvbnMgPSBbXTtcbnZhciB3cHNlb19tYXBzID0gW107XG52YXIgbWFya2VycyA9IG5ldyBPYmplY3QoKTtcblxudmFyIHdwc2VvX2RpcmVjdGlvbnMgPSBbXTtcbnZhciB3cHNlb19tYXBzID0gW107XG52YXIgbWFya2VycyA9IG5ldyBPYmplY3QoKTtcblxud2luZG93Lndwc2VvX3Nob3dfbWFwID0gZnVuY3Rpb24gd3BzZW9fc2hvd19tYXAobG9jYXRpb25fZGF0YSwgY291bnRlciwgY2VudGVyX2xhdCwgY2VudGVyX2xvbmcsIHpvb20sIG1hcF9zdHlsZSwgc2Nyb2xsYWJsZSwgZHJhZ2dhYmxlLCBkZWZhdWx0X3Nob3dfaW5mb3dpbmRvdywgaXNfYWRtaW4sIG1hcmtlcl9jbHVzdGVyaW5nKSB7XG4gICAgdmFyIGJvdW5kcyA9IG5ldyBnb29nbGUubWFwcy5MYXRMbmdCb3VuZHMoKTtcbiAgICB2YXIgY2VudGVyID0gbmV3IGdvb2dsZS5tYXBzLkxhdExuZyhjZW50ZXJfbGF0LCBjZW50ZXJfbG9uZyk7XG4gICAgdmFyIG1vYmlsZUJyZWFrcG9pbnQgPSA0ODA7XG4gICAgbWFya2Vyc1tjb3VudGVyXSA9IFtdO1xuXG4gICAgdmFyIHdwc2VvX21hcF9vcHRpb25zID0ge1xuICAgICAgICB6b29tOiB6b29tLFxuICAgICAgICBtaW5ab29tOiAxLFxuICAgICAgICBtYXBUeXBlQ29udHJvbDogdHJ1ZSxcbiAgICAgICAgem9vbUNvbnRyb2w6IHNjcm9sbGFibGUsXG4gICAgICAgIHN0cmVldFZpZXdDb250cm9sOiB0cnVlLFxuICAgICAgICBtYXBUeXBlSWQ6IGdvb2dsZS5tYXBzLk1hcFR5cGVJZFttYXBfc3R5bGUudG9VcHBlckNhc2UoKV0sXG4gICAgICAgIHNjcm9sbHdoZWVsOiBzY3JvbGxhYmxlICYmIHdpbmRvdy5pbm5lcldpZHRoID4gbW9iaWxlQnJlYWtwb2ludFxuICAgIH07XG5cbiAgICAvLyBnZXN0dXJlSGFuZGxpbmcgc2hvdWxkIG9ubHkgYmUgc2V0IG9uIGRldmljZXMgdGhhdCBzdXBwb3J0IHRvdWNoLlxuICAgIGlmIChjaGVja0ZvclRvdWNoKCkpIHtcbiAgICAgICAgd3BzZW9fbWFwX29wdGlvbnMuZ2VzdHVyZUhhbmRsaW5nID0gZHJhZ2dhYmxlID8gJ2F1dG8nIDogJ25vbmUnO1xuICAgIH0gZWxzZSB7XG4gICAgICAgIHdwc2VvX21hcF9vcHRpb25zLmRyYWdnYWJsZSA9IGRyYWdnYWJsZTtcbiAgICB9XG5cbiAgICAvLyBTZXQgY2VudGVyXG4gICAgaWYgKHpvb20gPT0gLTEpIHtcbiAgICAgICAgZm9yICh2YXIgaSA9IDA7IGkgPCBsb2NhdGlvbl9kYXRhLmxlbmd0aDsgaSsrKSB7XG4gICAgICAgICAgICB2YXIgbGF0TG9uZyA9IG5ldyBnb29nbGUubWFwcy5MYXRMbmcobG9jYXRpb25fZGF0YVtpXVtcImxhdFwiXSwgbG9jYXRpb25fZGF0YVtpXVtcImxvbmdcIl0pO1xuICAgICAgICAgICAgYm91bmRzLmV4dGVuZChsYXRMb25nKTtcbiAgICAgICAgfVxuXG4gICAgICAgIGNlbnRlciA9IGJvdW5kcy5nZXRDZW50ZXIoKTtcbiAgICB9XG4gICAgd3BzZW9fbWFwX29wdGlvbnMuY2VudGVyID0gY2VudGVyO1xuXG4gICAgdmFyIG1hcCA9IG5ldyBnb29nbGUubWFwcy5NYXAoZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoXCJtYXBfY2FudmFzXCIgKyAoY291bnRlciAhPSAwID8gJ18nICsgY291bnRlciA6ICcnKSksIHdwc2VvX21hcF9vcHRpb25zKTtcblxuICAgIGlmICh6b29tID09IC0xKSB7XG4gICAgICAgIG1hcC5maXRCb3VuZHMoYm91bmRzKTtcbiAgICB9XG5cbiAgICAvLyBTZXQgbWFya2VycyArIGluZm9cbiAgICB2YXIgaW5mb1dpbmRvdyA9IG5ldyBnb29nbGUubWFwcy5JbmZvV2luZG93KHtcbiAgICAgICAgY29udGVudDogaW5mb1dpbmRvd0hUTUxcbiAgICB9KTtcblxuICAgIGZvciAodmFyIGkgPSAwOyBpIDwgbG9jYXRpb25fZGF0YS5sZW5ndGg7IGkrKykge1xuICAgICAgICAvLyBDcmVhdGUgaW5mbyB3aW5kb3cgSFRNTFxuICAgICAgICB2YXIgaW5mb1dpbmRvd0hUTUwgPSBnZXRJbmZvQnViYmxlVGV4dChsb2NhdGlvbl9kYXRhW2ldW1wibmFtZVwiXSwgbG9jYXRpb25fZGF0YVtpXVtcImFkZHJlc3NcIl0sIGxvY2F0aW9uX2RhdGFbaV1bXCJ1cmxcIl0sIGxvY2F0aW9uX2RhdGFbaV1bXCJzZWxmX3VybFwiXSk7XG5cbiAgICAgICAgdmFyIGxhdExvbmcgPSBuZXcgZ29vZ2xlLm1hcHMuTGF0TG5nKGxvY2F0aW9uX2RhdGFbaV1bXCJsYXRcIl0sIGxvY2F0aW9uX2RhdGFbaV1bXCJsb25nXCJdKTtcbiAgICAgICAgdmFyIGljb24gPSBsb2NhdGlvbl9kYXRhW2ldW1wiY3VzdG9tX21hcmtlclwiXTtcbiAgICAgICAgdmFyIGNhdGVnb3JpZXMgPSBsb2NhdGlvbl9kYXRhW2ldW1wiY2F0ZWdvcmllc1wiXTtcblxuICAgICAgICBtYXJrZXJzW2NvdW50ZXJdW2ldID0gbmV3IGdvb2dsZS5tYXBzLk1hcmtlcih7XG4gICAgICAgICAgICBwb3NpdGlvbjogbGF0TG9uZyxcbiAgICAgICAgICAgIGNlbnRlcjogY2VudGVyLFxuICAgICAgICAgICAgbWFwOiBtYXAsXG4gICAgICAgICAgICBtYXBfaWQ6IGNvdW50ZXIsXG4gICAgICAgICAgICBodG1sOiBpbmZvV2luZG93SFRNTCxcbiAgICAgICAgICAgIGRyYWdnYWJsZTogQm9vbGVhbihpc19hZG1pbiksXG4gICAgICAgICAgICBpY29uOiB0eXBlb2YgaWNvbiAhPT0gJ3VuZGVmaW5lZCcgJiYgaWNvbiB8fCAnJyxcbiAgICAgICAgICAgIGNhdGVnb3JpZXM6IHR5cGVvZiBjYXRlZ29yaWVzICE9PSAndW5kZWZpbmVkJyAmJiBjYXRlZ29yaWVzIHx8ICcnXG4gICAgICAgIH0pO1xuICAgIH1cbiAgICBmb3IgKHZhciBpID0gMDsgaSA8IG1hcmtlcnNbY291bnRlcl0ubGVuZ3RoOyBpKyspIHtcbiAgICAgICAgdmFyIG1hcmtlciA9IG1hcmtlcnNbY291bnRlcl1baV07XG5cbiAgICAgICAgZ29vZ2xlLm1hcHMuZXZlbnQuYWRkTGlzdGVuZXIobWFya2VyLCBcImNsaWNrXCIsIGZ1bmN0aW9uICgpIHtcbiAgICAgICAgICAgIGluZm9XaW5kb3cuc2V0Q29udGVudCh0aGlzLmh0bWwpO1xuICAgICAgICAgICAgaW5mb1dpbmRvdy5vcGVuKG1hcCwgdGhpcyk7XG4gICAgICAgIH0pO1xuXG4gICAgICAgIGdvb2dsZS5tYXBzLmV2ZW50LmFkZExpc3RlbmVyKGluZm9XaW5kb3csICdjbG9zZWNsaWNrJywgZnVuY3Rpb24gKCkge1xuICAgICAgICAgICAgbWFwLnNldENlbnRlcih0aGlzLmdldFBvc2l0aW9uKCkpO1xuICAgICAgICB9KTtcblxuICAgICAgICBnb29nbGUubWFwcy5ldmVudC5hZGRMaXN0ZW5lcihtYXJrZXIsICdkcmFnZW5kJywgZnVuY3Rpb24gKGV2ZW50KSB7XG4gICAgICAgICAgICAvLyBJZiBvbiBhIHNpbmdsZSBsb2NhdGlvbiBwYWdlIGluIGEgbXVsdGlwbGUgbG9jYXRpb24gc2V0dXAuXG4gICAgICAgICAgICBpZiAoZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ3dwc2VvX2Nvb3JkaW5hdGVzX2xhdCcpICYmIGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCd3cHNlb19jb29yZGluYXRlc19sb25nJykpIHtcbiAgICAgICAgICAgICAgICBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnd3BzZW9fY29vcmRpbmF0ZXNfbGF0JykudmFsdWUgPSBldmVudC5sYXRMbmcubGF0KCk7XG4gICAgICAgICAgICAgICAgZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ3dwc2VvX2Nvb3JkaW5hdGVzX2xvbmcnKS52YWx1ZSA9IGV2ZW50LmxhdExuZy5sbmcoKTtcbiAgICAgICAgICAgIH1cblxuICAgICAgICAgICAgLy8gSWYgb24gdGhlIFlvYXN0IExvY2FsIFNFTyBzZXR0aW5ncyBwYWdlLCB1c2luZyBhIHNpbmdsZSBsb2NhdGlvbi5cbiAgICAgICAgICAgIGlmIChkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnbG9jYXRpb25fY29vcmRzX2xhdCcpICYmIGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdsb2NhdGlvbl9jb29yZHNfbG9uZycpKSB7XG4gICAgICAgICAgICAgICAgZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ2xvY2F0aW9uX2Nvb3Jkc19sYXQnKS52YWx1ZSA9IGV2ZW50LmxhdExuZy5sYXQoKTtcbiAgICAgICAgICAgICAgICBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnbG9jYXRpb25fY29vcmRzX2xvbmcnKS52YWx1ZSA9IGV2ZW50LmxhdExuZy5sbmcoKTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfSk7XG4gICAgfVxuXG4gICAgLy8gSWYgbWFya2VyIGNsdXN0ZXJpbmcgaXMgc2V0LCB1c2UgaXQuXG4gICAgaWYgKG1hcmtlcl9jbHVzdGVyaW5nKSB7XG4gICAgICAgIG5ldyBNYXJrZXJDbHVzdGVyZXIobWFwLCBtYXJrZXJzW2NvdW50ZXJdLCB7IGltYWdlUGF0aDogd3BzZW9fbG9jYWxfZGF0YS5tYXJrZXJfY2x1c3Rlcl9pbWFnZV9wYXRoIH0pO1xuICAgIH1cblxuICAgIC8vIElmIHRoZXJlIGlzIG9ubHkgb25lIG1hcmtlciBhbmQgdGhlIGluZm93aW5kb3cgc2hvdWxkIGJlIHNob3duLCBtYWtlIGl0IHNvLlxuICAgIGlmIChtYXJrZXJzW2NvdW50ZXJdLmxlbmd0aCA9PSAxICYmIGRlZmF1bHRfc2hvd19pbmZvd2luZG93KSB7XG4gICAgICAgIGluZm9XaW5kb3cuc2V0Q29udGVudChtYXJrZXJzW2NvdW50ZXJdWzBdLmh0bWwpO1xuICAgICAgICBpbmZvV2luZG93Lm9wZW4obWFwLCBtYXJrZXIpO1xuICAgIH1cblxuICAgIHJldHVybiBtYXA7XG59O1xuXG53aW5kb3cuY2hlY2tGb3JUb3VjaCA9IGZ1bmN0aW9uKCkge1xuICAgIHJldHVybiAhIShuYXZpZ2F0b3IudXNlckFnZW50Lm1hdGNoKC9BbmRyb2lkL2kpIHx8IG5hdmlnYXRvci51c2VyQWdlbnQubWF0Y2goL3dlYk9TL2kpIHx8IG5hdmlnYXRvci51c2VyQWdlbnQubWF0Y2goL2lQaG9uZS9pKSB8fCBuYXZpZ2F0b3IudXNlckFnZW50Lm1hdGNoKC9pUGFkL2kpIHx8IG5hdmlnYXRvci51c2VyQWdlbnQubWF0Y2goL2lQb2QvaSkgfHwgbmF2aWdhdG9yLnVzZXJBZ2VudC5tYXRjaCgvQmxhY2tCZXJyeS9pKSB8fCBuYXZpZ2F0b3IudXNlckFnZW50Lm1hdGNoKC9XaW5kb3dzIFBob25lL2kpKTtcbn1cblxud2luZG93Lndwc2VvX2dldF9kaXJlY3Rpb25zID0gZnVuY3Rpb24gKG1hcCwgbG9jYXRpb25fZGF0YSwgY291bnRlciwgc2hvd19yb3V0ZSkge1xuICAgIHZhciBkaXJlY3Rpb25zRGlzcGxheSA9ICcnO1xuXG4gICAgaWYgKHNob3dfcm91dGUgJiYgbG9jYXRpb25fZGF0YS5sZW5ndGggPj0gMSkge1xuICAgICAgICBkaXJlY3Rpb25zRGlzcGxheSA9IG5ldyBnb29nbGUubWFwcy5EaXJlY3Rpb25zUmVuZGVyZXIoKTtcbiAgICAgICAgZGlyZWN0aW9uc0Rpc3BsYXkuc2V0TWFwKG1hcCk7XG4gICAgICAgIGRpcmVjdGlvbnNEaXNwbGF5LnNldFBhbmVsKGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKFwiZGlyZWN0aW9uc1wiICsgKGNvdW50ZXIgIT0gMCA/ICdfJyArIGNvdW50ZXIgOiAnJykpKTtcbiAgICB9XG5cbiAgICByZXR1cm4gZGlyZWN0aW9uc0Rpc3BsYXk7XG59XG5cbndpbmRvdy5nZXRJbmZvQnViYmxlVGV4dCA9IGZ1bmN0aW9uKGJ1c2luZXNzX25hbWUsIGJ1c2luZXNzX2NpdHlfYWRkcmVzcywgYnVzaW5lc3NfdXJsLCBzZWxmX3VybCkge1xuICAgIHZhciBpbmZvV2luZG93SFRNTCA9ICc8ZGl2IGNsYXNzPVwid3BzZW8taW5mby13aW5kb3ctd3JhcHBlclwiPic7XG5cbiAgICB2YXIgc2hvd1NlbGZMaW5rID0gZmFsc2U7XG4gICAgaWYgKHNlbGZfdXJsICE9IHVuZGVmaW5lZCAmJiB3cHNlb19sb2NhbF9kYXRhLmhhc19tdWx0aXBsZV9sb2NhdGlvbnMgIT0gJycgJiYgc2VsZl91cmwgIT0gd2luZG93LmxvY2F0aW9uLmhyZWYpIHNob3dTZWxmTGluayA9IHRydWU7XG5cbiAgICBpZiAoc2hvd1NlbGZMaW5rKSBpbmZvV2luZG93SFRNTCArPSAnPGEgaHJlZj1cIicgKyBzZWxmX3VybCArICdcIj4nO1xuICAgIGluZm9XaW5kb3dIVE1MICs9ICc8c3Ryb25nPicgKyBidXNpbmVzc19uYW1lICsgJzwvc3Ryb25nPic7XG4gICAgaWYgKHNob3dTZWxmTGluaykgaW5mb1dpbmRvd0hUTUwgKz0gJzwvYT4nO1xuICAgIGluZm9XaW5kb3dIVE1MICs9ICc8YnI+JztcbiAgICBpbmZvV2luZG93SFRNTCArPSBidXNpbmVzc19jaXR5X2FkZHJlc3M7XG5cbiAgICBpbmZvV2luZG93SFRNTCArPSAnPC9kaXY+JztcblxuICAgIHJldHVybiBpbmZvV2luZG93SFRNTDtcbn1cblxud2luZG93Lndwc2VvX2NhbGN1bGF0ZV9yb3V0ZSA9IGZ1bmN0aW9uKG1hcCwgZGlyRGlzcGxheSwgY29vcmRzX2xhdCwgY29vcmRzX2xvbmcsIGNvdW50ZXIpIHtcbiAgICBpZiAoZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ3dwc2VvLXNsLWNvb3Jkcy1sYXQnKSAhPSBudWxsKSBjb29yZHNfbGF0ID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ3dwc2VvLXNsLWNvb3Jkcy1sYXQnKS52YWx1ZTtcbiAgICBpZiAoZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ3dwc2VvLXNsLWNvb3Jkcy1sb25nJykgIT0gbnVsbCkgY29vcmRzX2xvbmcgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnd3BzZW8tc2wtY29vcmRzLWxvbmcnKS52YWx1ZTtcblxuICAgIHZhciBzdGFydCA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKFwib3JpZ2luXCIgKyAoY291bnRlciAhPSAwID8gXCJfXCIgKyBjb3VudGVyIDogXCJcIikpLnZhbHVlICsgJyAnICsgd3BzZW9fbG9jYWxfZGF0YS5kZWZhdWx0X2NvdW50cnk7XG4gICAgdmFyIHVuaXRfc3lzdGVtID0gZ29vZ2xlLm1hcHMuVW5pdFN5c3RlbS5NRVRSSUM7XG4gICAgaWYgKHdwc2VvX2xvY2FsX2RhdGEudW5pdF9zeXN0ZW0gPT0gJ0lNUEVSSUFMJykgdW5pdF9zeXN0ZW0gPSBnb29nbGUubWFwcy5Vbml0U3lzdGVtLklNUEVSSUFMO1xuXG4gICAgLy8gQ2xlYXIgYWxsIG1hcmtlcnMgZnJvbSB0aGUgbWFwLCBvbmx5IHNob3cgQSBhbmQgQlxuICAgIGZvciAodmFyIGkgPSAwOyBpIDwgbWFya2Vycy5sZW5ndGg7IGkrKykge1xuICAgICAgICBtYXJrZXJzW2ldLnNldE1hcChudWxsKTtcbiAgICB9XG5cbiAgICAvLyBDaGFuZ2UgYnV0dG9uIHRvIGxpbmsgdG8gR29vZ2xlIE1hcHMuIGlQaG9uZXMgYW5kIEFuZHJvaWQgcGhvbmVzIHdpbGwgYXV0b21hdGljYWxseSBvcGVuIHRoZW0gaW4gTWFwcyBhcHAsIHdoZW4gYXZhaWxhYmxlLlxuICAgIGlmICgvQW5kcm9pZHx3ZWJPU3xpUGhvbmV8aVBhZHxpUG9kfEJsYWNrQmVycnl8SUVNb2JpbGV8T3BlcmEgTWluaS9pLnRlc3QobmF2aWdhdG9yLnVzZXJBZ2VudCkpIHtcbiAgICAgICAgdmFyIHVybCA9ICdodHRwczovL21hcHMuZ29vZ2xlLmNvbS9tYXBzP3NhZGRyPScgKyBlc2NhcGUoc3RhcnQpICsgJyZkYWRkcj0nICsgY29vcmRzX2xhdCArICcsJyArIGNvb3Jkc19sb25nO1xuICAgICAgICB3aW5kb3cub3Blbih1cmwsICdfYmxhbmsnKTtcblxuICAgICAgICByZXR1cm4gZmFsc2U7XG4gICAgfSBlbHNlIHtcbiAgICAgICAgdmFyIGxhdGxuZyA9IG5ldyBnb29nbGUubWFwcy5MYXRMbmcoY29vcmRzX2xhdCwgY29vcmRzX2xvbmcpO1xuXG4gICAgICAgIHZhciByZXF1ZXN0ID0ge1xuICAgICAgICAgICAgb3JpZ2luOiBzdGFydCxcbiAgICAgICAgICAgIGRlc3RpbmF0aW9uOiBsYXRsbmcsXG4gICAgICAgICAgICBwcm92aWRlUm91dGVBbHRlcm5hdGl2ZXM6IHRydWUsXG4gICAgICAgICAgICBvcHRpbWl6ZVdheXBvaW50czogdHJ1ZSxcbiAgICAgICAgICAgIHRyYXZlbE1vZGU6IGdvb2dsZS5tYXBzLkRpcmVjdGlvbnNUcmF2ZWxNb2RlLkRSSVZJTkcsXG4gICAgICAgICAgICB1bml0U3lzdGVtOiB1bml0X3N5c3RlbVxuICAgICAgICB9O1xuXG4gICAgICAgIHZhciBkaXJlY3Rpb25zU2VydmljZSA9IG5ldyBnb29nbGUubWFwcy5EaXJlY3Rpb25zU2VydmljZSgpO1xuXG4gICAgICAgIGRpcmVjdGlvbnNTZXJ2aWNlLnJvdXRlKHJlcXVlc3QsIGZ1bmN0aW9uIChyZXNwb25zZSwgc3RhdHVzMikge1xuICAgICAgICAgICAgaWYgKHN0YXR1czIgPT0gZ29vZ2xlLm1hcHMuRGlyZWN0aW9uc1N0YXR1cy5PSykge1xuICAgICAgICAgICAgICAgIGRpckRpc3BsYXkuc2V0RGlyZWN0aW9ucyhyZXNwb25zZSk7XG4gICAgICAgICAgICB9IGVsc2UgaWYgKHN0YXR1czIgPT0gZ29vZ2xlLm1hcHMuRGlyZWN0aW9uc1N0YXR1cy5aRVJPX1JFU1VMVFMpIHtcbiAgICAgICAgICAgICAgICB2YXIgbm9yb3V0ZSA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCd3cHNlby1ub3JvdXRlJyk7XG4gICAgICAgICAgICAgICAgbm9yb3V0ZS5zZXRBdHRyaWJ1dGUoJ3N0eWxlJywgJ2NsZWFyOiBib3RoOyBkaXNwbGF5OiBibG9jazsnKTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfSk7XG4gICAgfVxufVxuXG53aW5kb3cud3BzZW9fc2xfc2hvd19yb3V0ZSA9IGZ1bmN0aW9uKG9iaiwgY29vcmRzX2xhdCwgY29vcmRzX2xvbmcpIHtcbiAgICAkID0galF1ZXJ5O1xuXG4gICAgLy8gQ3JlYXRlIGhpZGRlbiBpbnB1dHMgdG8gcGFzcyB0aHJvdWdoIHRoZSBsYXQvbG9uZyBjb29yZGluYXRlcyBmb3Igd2hpY2ggaXMgbmVlZGVkIGZvciBjYWxjdWxhdGluZyB0aGUgcm91dGUuXG4gICAgJCgnLndwc2VvLXNsLWNvb3JkcycpLnJlbW92ZSgpO1xuICAgIHZhciBpbnB1dHMgPSAnPGlucHV0IHR5cGU9XCJoaWRkZW5cIiBjbGFzcz1cIndwc2VvLXNsLWNvb3Jkc1wiIGlkPVwid3BzZW8tc2wtY29vcmRzLWxhdFwiIHZhbHVlPVwiJyArIGNvb3Jkc19sYXQgKyAnXCI+JztcbiAgICBpbnB1dHMgKz0gJzxpbnB1dCB0eXBlPVwiaGlkZGVuXCIgY2xhc3M9XCJ3cHNlby1zbC1jb29yZHNcIiBpZD1cIndwc2VvLXNsLWNvb3Jkcy1sb25nXCIgdmFsdWU9XCInICsgY29vcmRzX2xvbmcgKyAnXCI+JztcblxuICAgICQoJyN3cHNlby1kaXJlY3Rpb25zLWZvcm0nKS5hcHBlbmQoaW5wdXRzKS5zdWJtaXQoKTtcbiAgICAkKCcjd3BzZW8tZGlyZWN0aW9ucy13cmFwcGVyJykuc2xpZGVVcChmdW5jdGlvbiAoKSB7XG4gICAgICAgICQodGhpcykuaW5zZXJ0QWZ0ZXIoJChvYmopLnBhcmVudHMoJy53cHNlby1yZXN1bHQnKSkuc2xpZGVEb3duKCk7XG4gICAgfSk7XG59XG5cbndpbmRvdy53cHNlb19kZXRlY3RfbG9jYXRpb24gPSBmdW5jdGlvbihldmVudCwgdGFyZ2V0KSB7XG4gICAgdmFyIHNlYXJjaElucHV0ID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQodGFyZ2V0KTtcbiAgICBpZiAobnVsbCA9PSBzZWFyY2hJbnB1dCkge1xuICAgICAgICBzZWFyY2hJbnB1dCA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdvcmlnaW4nKTtcbiAgICB9XG5cbiAgICBpZiAobmF2aWdhdG9yLmdlb2xvY2F0aW9uICYmIG51bGwgIT0gc2VhcmNoSW5wdXQpIHtcbiAgICAgICAgdmFyIGNsaWNrZWRCdXR0b24gPSBldmVudC50YXJnZXQgfHwgZXZlbnQuc3JjRWxlbWVudDtcbiAgICAgICAgdmFyIG9yaWdpbmFsSW1hZ2VTcmMgPSBjbGlja2VkQnV0dG9uLmdldEF0dHJpYnV0ZSgnc3JjJyk7XG4gICAgICAgIHZhciBvcmlnaW5hbEltYWdlQWx0VGV4dCA9IGNsaWNrZWRCdXR0b24uZ2V0QXR0cmlidXRlKCdhbHQnKTtcbiAgICAgICAgdmFyIGxvYWRpbmdBbHRUZXh0ID0gY2xpY2tlZEJ1dHRvbi5nZXRBdHRyaWJ1dGUoJ2RhdGEtbG9hZGluZy10ZXh0Jyk7XG5cbiAgICAgICAgLy8gQWRkIHNwaW5uZXIgdG8gdGhlIGNsaWNrZWQgYnV0dG9uLlxuICAgICAgICBjbGlja2VkQnV0dG9uLnNldEF0dHJpYnV0ZSgnc3JjJywgd3BzZW9fbG9jYWxfZGF0YS5hZG1pbnVybCArICdpbWFnZXMvbG9hZGluZy5naWYnKTtcbiAgICAgICAgY2xpY2tlZEJ1dHRvbi5zZXRBdHRyaWJ1dGUoJ2FsdCcsIGxvYWRpbmdBbHRUZXh0KTtcblxuICAgICAgICBuYXZpZ2F0b3IuZ2VvbG9jYXRpb24uZ2V0Q3VycmVudFBvc2l0aW9uKGZ1bmN0aW9uIChwb3NpdGlvbikge1xuICAgICAgICAgICAgdmFyIGdlb2NvZGVyID0gbmV3IGdvb2dsZS5tYXBzLkdlb2NvZGVyKCk7XG4gICAgICAgICAgICB2YXIgbGF0bG5nID0ge1xuICAgICAgICAgICAgICAgIGxhdDogcGFyc2VGbG9hdChwb3NpdGlvbi5jb29yZHMubGF0aXR1ZGUpLFxuICAgICAgICAgICAgICAgIGxuZzogcGFyc2VGbG9hdChwb3NpdGlvbi5jb29yZHMubG9uZ2l0dWRlKVxuICAgICAgICAgICAgfTtcblxuICAgICAgICAgICAgZ2VvY29kZXIuZ2VvY29kZSh7ICdsb2NhdGlvbic6IGxhdGxuZyB9LCBmdW5jdGlvbiAocmVzdWx0cywgc3RhdHVzKSB7XG4gICAgICAgICAgICAgICAgaWYgKHN0YXR1cyA9PT0gZ29vZ2xlLm1hcHMuR2VvY29kZXJTdGF0dXMuT0spIHtcbiAgICAgICAgICAgICAgICAgICAgLy8gT25seSBlbnRlciBkZXRlY3RlZCBsb2NhdGlvbiB3aGVuIHRoZXJlIGFyZSByZXN1bHRzIGFuZCBubyB2YWx1ZSB5ZXQgaXMgZW50ZXJlZC5cbiAgICAgICAgICAgICAgICAgICAgaWYgKHJlc3VsdHMubGVuZ3RoID4gMCAmJiAnJyA9PSBzZWFyY2hJbnB1dC52YWx1ZSkge1xuICAgICAgICAgICAgICAgICAgICAgICAgc2VhcmNoSW5wdXQudmFsdWUgPSByZXN1bHRzWzBdLmZvcm1hdHRlZF9hZGRyZXNzO1xuICAgICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICAgICAgY2xpY2tlZEJ1dHRvbi5zZXRBdHRyaWJ1dGUoJ3NyYycsIG9yaWdpbmFsSW1hZ2VTcmMpO1xuICAgICAgICAgICAgICAgIGNsaWNrZWRCdXR0b24uc2V0QXR0cmlidXRlKCdhbHQnLCBvcmlnaW5hbEltYWdlQWx0VGV4dCk7XG4gICAgICAgICAgICB9KTtcbiAgICAgICAgfSwgZnVuY3Rpb24gKGVycm9yKSB7XG4gICAgICAgICAgICB2YXIgZXJyID0gJ1t3cHNlb10gRXJyb3IgZGV0ZWN0aW5nIGxvY2F0aW9uOiAnO1xuICAgICAgICAgICAgc3dpdGNoIChlcnJvci5jb2RlKSB7XG4gICAgICAgICAgICAgICAgY2FzZSBlcnJvci5USU1FT1VUOlxuICAgICAgICAgICAgICAgICAgICBlcnIgKz0gJ1RpbWVvdXQnO1xuICAgICAgICAgICAgICAgICAgICBicmVhaztcbiAgICAgICAgICAgICAgICBjYXNlIGVycm9yLlBPU0lUSU9OX1VOQVZBSUxBQkxFOlxuICAgICAgICAgICAgICAgICAgICBlcnIgKz0gJ1Bvc2l0aW9uIHVuYXZhaWxhYmxlJztcbiAgICAgICAgICAgICAgICAgICAgYnJlYWs7XG4gICAgICAgICAgICAgICAgY2FzZSBlcnJvci5QRVJNSVNTSU9OX0RFTklFRDpcbiAgICAgICAgICAgICAgICAgICAgZXJyICs9ICdQZXJtaXNzaW9uIGRlbmllZCc7XG4gICAgICAgICAgICAgICAgICAgIGJyZWFrO1xuICAgICAgICAgICAgICAgIGNhc2UgZXJyb3IuVU5LTk9XTl9FUlJPUjpcbiAgICAgICAgICAgICAgICAgICAgZXJyICs9ICdVbmtub3duIGVycm9yJztcbiAgICAgICAgICAgICAgICAgICAgYnJlYWs7XG4gICAgICAgICAgICB9XG5cbiAgICAgICAgICAgIGlmICh0eXBlb2YgY29uc29sZSAhPSAndW5kZWZpbmVkJykge1xuICAgICAgICAgICAgICAgIGNvbnNvbGUubG9nKGVycik7XG4gICAgICAgICAgICB9XG5cbiAgICAgICAgICAgIGNsaWNrZWRCdXR0b24uc2V0QXR0cmlidXRlKCdzcmMnLCBvcmlnaW5hbEltYWdlU3JjKTtcbiAgICAgICAgICAgIGNsaWNrZWRCdXR0b24uc2V0QXR0cmlidXRlKCdhbHQnLCBvcmlnaW5hbEltYWdlQWx0VGV4dCk7XG4gICAgICAgIH0pO1xuICAgIH1cbn1cblxud2luZG93Lndwc2VvX2N1cnJlbnRfbG9jYXRpb25fYnV0dG9ucyA9IGRvY3VtZW50LmdldEVsZW1lbnRzQnlDbGFzc05hbWUoJ3dwc2VvX3VzZV9jdXJyZW50X2xvY2F0aW9uJyk7XG5mb3IgKHZhciBpID0gMDsgaSA8IHdwc2VvX2N1cnJlbnRfbG9jYXRpb25fYnV0dG9ucy5sZW5ndGg7IGkrKykge1xuICAgIHdwc2VvX2N1cnJlbnRfbG9jYXRpb25fYnV0dG9uc1tpXS5hZGRFdmVudExpc3RlbmVyKCdjbGljaycsIGZ1bmN0aW9uIChldmVudCkge1xuICAgICAgICB2YXIgdGFyZ2V0ID0gdGhpcy5kYXRhc2V0LnRhcmdldDtcbiAgICAgICAgd3BzZW9fZGV0ZWN0X2xvY2F0aW9uKGV2ZW50LCB0YXJnZXQpO1xuICAgIH0sIGZhbHNlKTtcbn1cblxud2luZG93LmZpbHRlck1hcmtlcnMgPSBmdW5jdGlvbihjYXRlZ29yeSwgbWFwX2lkKSB7XG4gICAgZm9yIChpID0gMDsgaSA8IG1hcmtlcnNbbWFwX2lkXS5sZW5ndGg7IGkrKykge1xuICAgICAgICBtYXJrZXIgPSBtYXJrZXJzW21hcF9pZF1baV07XG5cbiAgICAgICAgLy8gSWYgaXMgc2FtZSBjYXRlZ29yeSBvciBjYXRlZ29yeSBub3QgcGlja2VkXG4gICAgICAgIGlmIChtYXJrZXIuY2F0ZWdvcmllcy5oYXNPd25Qcm9wZXJ0eShjYXRlZ29yeSkgfHwgY2F0ZWdvcnkubGVuZ3RoID09PSAwKSB7XG4gICAgICAgICAgICBtYXJrZXIuc2V0VmlzaWJsZSh0cnVlKTtcbiAgICAgICAgfVxuICAgICAgICAvLyBDYXRlZ29yaWVzIGRvbid0IG1hdGNoXG4gICAgICAgIGVsc2Uge1xuICAgICAgICAgICAgbWFya2VyLnNldFZpc2libGUoZmFsc2UpO1xuICAgICAgICB9XG4gICAgfVxufVxuIl19
