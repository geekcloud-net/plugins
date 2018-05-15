/**
 * Field template: Date
 */
Vue.component('form-google_map', {
    template: '#tmpl-wpuf-form-google_map',

    mixins: [
        wpuf_mixins.form_field_mixin
    ],

    data: function () {
        return {
            map: {},
            geocoder: {},
            markers: []
        };
    },

    /* global google */
    mounted: function () {
        var self = this,
            default_pos = self.field.default_pos,
            default_zoom = self.field.zoom;

        var default_latLng = default_pos.split(',');

        if (2 === default_latLng.length && isFinite(default_latLng[0]) && isFinite(default_latLng[1])) {
            default_pos = {lat: parseFloat(default_latLng[0]), lng: parseFloat(default_latLng[1])};
        } else {
            default_pos = {lat: 40.7143528, lng: -74.0059731};
        }

        self.map = new google.maps.Map($(this.$el).find('.wpuf-form-google-map').get(0), {
            center: default_pos,
            zoom: parseInt(default_zoom) || 12,
            mapTypeId: 'roadmap',
            streetViewControl: false,
        });

        self.geocoder = new google.maps.Geocoder();

        // Create the search box and link it to the UI element.
        var input = $(this.$el).find('.wpuf-google-map-search').get(0);
        var searchBox = new google.maps.places.SearchBox(input);
        self.map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

        // Bias the SearchBox results towards current map's viewport.
        self.map.addListener('bounds_changed', function() {
            searchBox.setBounds(self.map.getBounds());
        });

        self.markers = [];

        self.set_marker(self.field.default_pos);
    },

    methods: {
        set_marker: function (address) {
            var self = this;

            self.geocoder.geocode({'address': address}, function(results, status) {
                if (status === 'OK') {
                    // Clear out the old markers.
                    _.each(self.markers, function (marker) {
                        marker.setMap(null);
                    });

                    self.markers = [];

                    // Create a marker for each place.
                    self.markers.push(new google.maps.Marker({
                        map: self.map,
                        position: results[0].geometry.location
                    }));

                    self.map.setCenter(results[0].geometry.location);
                }
            });
        }
    },

    watch: {
        field: {
            deep: true,
            handler: function (newVal) {
                this.set_marker(newVal.default_pos);
                this.map.setZoom(parseInt(newVal.zoom));
            }
        }
    }
});
