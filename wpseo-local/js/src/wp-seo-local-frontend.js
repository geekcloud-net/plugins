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

window.checkForTouch = function() {
    return !!(navigator.userAgent.match(/Android/i) || navigator.userAgent.match(/webOS/i) || navigator.userAgent.match(/iPhone/i) || navigator.userAgent.match(/iPad/i) || navigator.userAgent.match(/iPod/i) || navigator.userAgent.match(/BlackBerry/i) || navigator.userAgent.match(/Windows Phone/i));
}

window.wpseo_get_directions = function (map, location_data, counter, show_route) {
    var directionsDisplay = '';

    if (show_route && location_data.length >= 1) {
        directionsDisplay = new google.maps.DirectionsRenderer();
        directionsDisplay.setMap(map);
        directionsDisplay.setPanel(document.getElementById("directions" + (counter != 0 ? '_' + counter : '')));
    }

    return directionsDisplay;
}

window.getInfoBubbleText = function(business_name, business_city_address, business_url, self_url) {
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
}

window.wpseo_calculate_route = function(map, dirDisplay, coords_lat, coords_long, counter) {
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
}

window.wpseo_sl_show_route = function(obj, coords_lat, coords_long) {
    $ = jQuery;

    // Create hidden inputs to pass through the lat/long coordinates for which is needed for calculating the route.
    $('.wpseo-sl-coords').remove();
    var inputs = '<input type="hidden" class="wpseo-sl-coords" id="wpseo-sl-coords-lat" value="' + coords_lat + '">';
    inputs += '<input type="hidden" class="wpseo-sl-coords" id="wpseo-sl-coords-long" value="' + coords_long + '">';

    $('#wpseo-directions-form').append(inputs).submit();
    $('#wpseo-directions-wrapper').slideUp(function () {
        $(this).insertAfter($(obj).parents('.wpseo-result')).slideDown();
    });
}

window.wpseo_detect_location = function(event, target) {
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
}

window.wpseo_current_location_buttons = document.getElementsByClassName('wpseo_use_current_location');
for (var i = 0; i < wpseo_current_location_buttons.length; i++) {
    wpseo_current_location_buttons[i].addEventListener('click', function (event) {
        var target = this.dataset.target;
        wpseo_detect_location(event, target);
    }, false);
}

window.filterMarkers = function(category, map_id) {
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
}
