Vue.component('field-gmap-set-position', {
    template: '#tmpl-wpuf-field-gmap-set-position',

    mixins: [
        wpuf_mixins.option_field_mixin
    ],

    /* global google */
    mounted: function () {
        var self = this,
            default_pos = self.editing_form_field.default_pos,
            default_zoom = self.editing_form_field.zoom;

        var default_latLng = default_pos.split(',');

        if (2 === default_latLng.length && isFinite(default_latLng[0]) && isFinite(default_latLng[1])) {
            default_pos = {lat: parseFloat(default_latLng[0]), lng: parseFloat(default_latLng[1])};
        } else {
            default_pos = {lat: 40.7143528, lng: -74.0059731};
        }

        var map = new google.maps.Map($(this.$el).find('.wpuf-field-google-map').get(0), {
            center: default_pos,
            zoom: parseInt(default_zoom) || 12,
            mapTypeId: 'roadmap',
            streetViewControl: false,
        });

        var geocoder = new google.maps.Geocoder();

        // Create the search box and link it to the UI element.
        var input = $(this.$el).find('.wpuf-google-map-search').get(0);
        var searchBox = new google.maps.places.SearchBox(input);
        map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

        // Bias the SearchBox results towards current map's viewport.
        map.addListener('bounds_changed', function() {
            searchBox.setBounds(map.getBounds());
        });

        var markers = [];

        set_marker(self.editing_form_field.default_pos);

        function set_marker(address) {
            geocoder.geocode({'address': address}, function(results, status) {
                if (status === 'OK') {
                    // Clear out the old markers.
                    _.each(markers, function (marker) {
                        marker.setMap(null);
                    });

                    markers = [];

                    // Create a marker for each place.
                    markers.push(new google.maps.Marker({
                        map: map,
                        position: results[0].geometry.location
                    }));

                    map.setCenter(results[0].geometry.location);

                    self.set_default_pos(results[0].geometry.location);
                }
            });
        }

        // when input latitude and longitude like "40.7143528,-74.0059731"
        input.addEventListener('input', function () {
            var address = this.value;

            var latLng = address.split(',');

            if (2 === latLng.length && isFinite(latLng[0]) && isFinite(latLng[1])) {
                set_marker(address);
            }
        });



        // Listen for the event fired when the user selects a prediction and retrieve
        // more details for that place.
        searchBox.addListener('places_changed', function() {
            var places = searchBox.getPlaces();

            if (places.length === 0) {
                return;
            }

            // Clear out the old markers.
            _.each(markers, function (marker) {
                marker.setMap(null);
            });

            markers = [];

            // For each place, get the icon, name and location.
            var bounds = new google.maps.LatLngBounds();

            _.each(places, function (place) {
                if (!place.geometry) {
                    console.log('Returned place contains no geometry');

                    return;
                }

                // Create a marker for each place.
                markers.push(new google.maps.Marker({
                    map: map,
                    position: place.geometry.location
                }));

                self.set_default_pos(place.geometry.location);

                if (place.geometry.viewport) {
                    // Only geocodes have viewport.
                    bounds.union(place.geometry.viewport);

                } else {
                    bounds.extend(place.geometry.location);
                }
            });

            map.fitBounds(bounds);
        });

        map.addListener('click', function(e) {
            var latLng = e.latLng;

            // Clear out the old markers.
            _.each(markers, function (marker) {
                marker.setMap(null);
            });

            markers = [];

            markers.push(new google.maps.Marker({
                position: latLng,
                map: map
            }));

            self.set_default_pos(latLng);

            map.panTo(latLng);
        });

        map.addListener('zoom_changed', function () {
            var zoom = map.getZoom();

            self.update_value('zoom', zoom);

            wpuf_form_builder.event_hub.$emit('wpuf-update-map-zoom-' + self.editing_form_field.id, zoom);
        });
    },

    methods: {
        toggle_checkbox_field: function (field) {
            this.editing_form_field[field] = ('yes' === this.editing_form_field[field]) ? 'no' : 'yes';
        },

        set_default_pos: function (latLng) {
            latLng = latLng.toJSON();

            this.update_value('default_pos', latLng.lat + ',' + latLng.lng);
        }
    }
});
