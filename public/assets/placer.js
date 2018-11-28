var WAPlacer = {
    debug: 0,
    settings: {},
    map: {},
    uniqId: '',
    lat: 0,
    lng: 0,
    targetIcon: null,
    targetMarker: null,

    init: function(divId, uniqId, lat, lng) {
        this.targetIcon = L.icon({
            iconUrl: '/assets/images/target.svg',
            iconSize: [2000, 2000]
        });
        this.lat = lat;
        this.lng = lng;
        this.uniqId = uniqId;
        // Create the map container
        this.map = L.map(divId, {
            crs: L.CRS.Simple,
            minZoom: -4.6,
            maxZoom: -0.4,
            zoomSnap: 0.2,
            zoomDelta: 0.2,
            wheelPxPerZoomLevel: 200,
            attributionControl: false
        });
        // Set the renderer to render beyond the viewport to prevent weird half rendered polygons
        this.map.getRenderer(this.map).options.padding = 100;
        this.map.setMaxBounds([[0, 0], [-9500, 9500]]);
        this.map.setView([-4750, 4750], -4.2);
        this.map.createPane('map-boundaries');
        this.map.createPane('island-dots');
        this.map.createPane('target');
        this.map.addEventListener('click', function(e) {
            this.mapClick(e);
        }.bind(this));
        this.loadMapBoundaries();
    },
    mapClick: function(e) {
        $("input[name='"+this.uniqId+"[lat]']").val(e.latlng.lat.toFixed(2));
        $("input[name='"+this.uniqId+"[lng]']").val(e.latlng.lng.toFixed(2));
        if(!this.targetMarker) {
            this.targetMarker = L.marker(e.latlng, {
                icon: this.targetIcon
            }).addTo(this.map);
        }
        this.targetMarker.setLatLng(e.latlng);
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
                if(data && data.features && data.features.length) {

                    $.each(data.features, function( index, island ) {
                        L.circleMarker(island.geometry.coordinates, { pane: 'island-dots', interactive: false, radius: 5, stroke: false, fillColor: '#FF0000', fillOpacity: 0.6 }).addTo(self.map);
                    });
                }
                if(self.lat !== '0' && self.lng !== '0') {
                    if(!self.targetMarker) {
                        self.targetMarker = L.marker([self.lat, self.lng], {
                            icon: self.targetIcon
                        }).addTo(self.map);
                    }
                    self.targetMarker.setLatLng([self.lat, self.lng]);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error(errorThrown);
            }
        });
    }
};
window.WAPlacer = WAPlacer;
