		var map;
		var geojson = null;

    		
		// http://gis.stackexchange.com/a/116193
		// http://jsfiddle.net/GFarkas/qzdr2w73/4/
    // The most important part is the border-radius property. It will round your shape at the corners. To create a regular circle with it, you have to calculate the radius with the border. The formula is width / 2 + border * 4 if width = height.
		var icon = new L.divIcon({className: 'mydivicon'});		

		//--------------------------------------------------------------------------------
		function onEachFeature(feature, layer) {
			// does this feature have a property named popupContent?
			if (feature.properties && feature.properties.popupContent) {
				//console.log(feature.properties.popupContent);
				// content must be a string, see http://stackoverflow.com/a/22476287
				layer.bindPopup(String(feature.properties.popupContent));
			}
		}	
			
		//--------------------------------------------------------------------------------
		function create_map() {
			map = new L.Map('map');

			// create the tile layer with correct attribution
			var osmUrl='http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
			var osmAttrib='Map data © <a href="http://openstreetmap.org">OpenStreetMap</a> contributors';
			var osm = new L.TileLayer(osmUrl, {minZoom: 1, maxZoom: 12, attribution: osmAttrib});		

			map.setView(new L.LatLng(0, 0),4);
			map.addLayer(osm);		
		}
		
		//--------------------------------------------------------------------------------
		function clear_map() {
			if (geojson) {
				map.removeLayer(geojson);
			}
		}	
	
		//--------------------------------------------------------------------------------
		function add_data(data) {
			clear_map();
		
			geojson = L.geoJson(data, { 

			pointToLayer: function (feature, latlng) {
                return L.marker(latlng, {
                    icon: icon});
            },			
			style: function (feature) {
				return feature.properties && feature.properties.style;
			},
			onEachFeature: onEachFeature,
			}).addTo(map);
			
			// Open popups on hover
  			geojson.on('mouseover', function (e) {
    			e.layer.openPopup();
  			});
		
			if (data.type) {
				if (data.type == 'Polygon') {
					for (var i in data.coordinates) {
					  minx = 180;
					  miny = 90;
					  maxx = -180;
					  maxy = -90;
				  
					  for (var j in data.coordinates[i]) {
						minx = Math.min(minx, data.coordinates[i][j][0]);
						miny = Math.min(miny, data.coordinates[i][j][1]);
						maxx = Math.max(maxx, data.coordinates[i][j][0]);
						maxy = Math.max(maxy, data.coordinates[i][j][1]);
					  }
					}
					
					bounds = L.latLngBounds(L.latLng(miny,minx), L.latLng(maxy,maxx));
					map.fitBounds(bounds);
				}
				if (data.type == 'MultiPoint') {
					minx = 180;
					miny = 90;
					maxx = -180;
					maxy = -90;				
					for (var i in data.coordinates) {
						minx = Math.min(minx, data.coordinates[i][0]);
						miny = Math.min(miny, data.coordinates[i][1]);
						maxx = Math.max(maxx, data.coordinates[i][0]);
						maxy = Math.max(maxy, data.coordinates[i][1]);
					}
					
					bounds = L.latLngBounds(L.latLng(miny,minx), L.latLng(maxy,maxx));
					map.fitBounds(bounds);
				}
				if (data.type == 'FeatureCollection') {
					minx = 180;
					miny = 90;
					maxx = -180;
					maxy = -90;				
					for (var i in data.features) {
						//console.log(JSON.stringify(data.features[i]));
					
						minx = Math.min(minx, data.features[i].geometry.coordinates[0]);
						miny = Math.min(miny, data.features[i].geometry.coordinates[1]);
						maxx = Math.max(maxx, data.features[i].geometry.coordinates[0]);
						maxy = Math.max(maxy, data.features[i].geometry.coordinates[1]);
						
					}
					
					bounds = L.latLngBounds(L.latLng(miny,minx), L.latLng(maxy,maxx));
					map.fitBounds(bounds);
				}
			}		    					
		}
    
		function show_map(id) {
		
			$.getJSON('api.php?id=' + id + '&format=geojson&callback=?',
				function(data){
          if (data.features.length > 0) {
			create_map();
            add_data(data);
          } else {
           $('#map').hide();
          }
				 }				
			);
		}    