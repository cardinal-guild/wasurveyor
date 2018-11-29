var WARepositioner = {
    map: null,
    dragIcon: null,
    locationUpdates: [],
    bounds: [[0, 0], [-9500, 9500]],
    init: function(formDiv) {
        // Create the map container
        var self = this;
        $(formDiv).submit(function() {
            if(self.locationUpdates.length > 0) {
                var count = 0;
                $('#hiddenFields').html('');
                $.each(self.locationUpdates, function( index, value ) {
                    $('<input>').attr('type','hidden').attr('name',"positions["+count+"][id]").attr('value',value.island_id).appendTo('#hiddenFields');
                    $('<input>').attr('type','hidden').attr('name',"positions["+count+"][lat]").attr('value',value.lat).appendTo('#hiddenFields');
                    $('<input>').attr('type','hidden').attr('name',"positions["+count+"][lng]").attr('value',value.lng).appendTo('#hiddenFields');
                    count++;
                });
                return true;
            }
            return false;
        });
        this.dragIcon = L.icon({
            iconUrl: '/assets/dragicon.png',
            iconSize: [30, 30]
        });
        this.map = L.map('repositioner-map', {
            crs: L.CRS.Simple,
            minZoom: -4.6,
            maxZoom: -0.4,
            zoomSnap: 0.2,
            zoomDelta: 0.2,
            wheelPxPerZoomLevel: 200,
            bounds: self.bounds,
            attributionControl: false
        });
        // Set the renderer to render beyond the viewport to prevent weird half rendered polygons
        this.map.getRenderer(this.map).options.padding = 100;
        // this.map.setBounds([[0, 0], [-9500, 9500]]);
        // this.map.setMaxBounds([[19000, -19000], [-19000, 19000]]);
        // this.map.setView([-4750, 4750], -4.2);
        this.map.fitBounds(self.bounds);
        this.map.createPane('map-boundaries');
        this.map.createPane('island-dots');
        this.loadMapBoundaries();
    },
    loadMapBoundaries: function () {
        var self = this;
        $.ajax({
            url: 'https://data.cardinalguild.com/wamap.geojson',
            type: 'GET',
            dataType: "json",
            cache: false,
            success: function (data) {
                L.geoJSON(data, {
                    style: function(feature) {
                        return feature.properties;
                    },
                    maxBounds: self.bounds,
                    interactive: false
                }).addTo(self.map);
                self.loadIslands();
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error(errorThrown);
            }
        });


    },
    loadIslands: function () {
        var self = this;
        $.ajax({
            url: '/api/islands.json',
            type: 'GET',
            dataType: "json",
            cache: false,
            success: function (data) {
                console.log(data);
                if(data && data.features && data.features.length) {

                    $.each(data.features, function( index, island ) {
                        L.marker(island.geometry.coordinates, {
                            pane: 'island-dots',
                            icon: self.dragIcon,
                            draggable: true,
                            maxBounds: self.bounds,
                            id: island.properties.id
                        })
                        .addTo(self.map).addEventListener('dragend', function(e) {
                            self.dragEnd(e);
                        }.bind(self));
                    });
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error(errorThrown);
            }
        });
    },
    dragEnd: function(e) {
        var latLng = e.target.getLatLng();
        var lat = latLng.lat;
        var lng = latLng.lng;
        var correct = false;
        if (lat > 0 ) {
            lat = 0;
            correct = true;
        }
        if (lat < -9500 ) {
            lat = -9500;
            correct = true;
        }
        if (lng > 9500 ) {
            lng = 9500;
            correct = true;
        }
        if (lng < 0) {
            lng = 0;
            correct = true;
        }
        if(correct) {
            e.target.setLatLng([lat, lng]);
        }

        var found = false;
        $(e.target._icon).addClass('dragged');

        var data = {
            island_id: e.target.options.id,
            lat: lat.toFixed(2),
            lng: lng.toFixed(2)
        };
        for (var i = 0; i < this.locationUpdates.length; i++) {
            if (this.locationUpdates[i].island_id === data.island_id) {
                this.locationUpdates[i].lat = data.lat;
                this.locationUpdates[i].lng = data.lng;
                found = true;
                break;
            }
        }
        if(!found) {
            this.locationUpdates.push(data);
        }
        $('#btn_update_positions').attr('disabled', false);
    }
};
window.WARepositioner = WARepositioner;
