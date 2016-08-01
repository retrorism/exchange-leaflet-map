// initialize the function declared in construct-leaflet-map.js in document head
WPLeafletMapPlugin.init();
function createRoute(arr_m,arr_l,arr_c) {
	var iconUrl = leaflet_vars.markerUrl,
	exchange_icon = L.icon({
		iconUrl: iconUrl,
		iconSize:     [14, 14], // size of the icon
		shadowSize:   [0, 0], // size of the shadow
		iconAnchor:   [7, 7], // point of the icon which will correspond to marker's location
		shadowAnchor: [4, 62],  // the same for the shadow
		popupAnchor:  [0, 0] // point from which the popup should open relative to the iconAnchor
	}),
	route = L.featureGroup({snakingPause: 500 });
	if ( arr_m.length > 0 && arr_l.length > 0 ) {
		for ( i = 0; i < arr_l.length; i++ ) {
			var marker = L.marker( arr_m[i], {
				icon : new L.DivIcon({
					className: 'map__marker',
					html:   '<img class="map__marker__image" src="' + iconUrl + '">'+
							'<span class="map__marker__label">'+arr_c[i]+'</span>'
						})
			});
			var line = L.polyline( arr_l[i], {
				color : leaflet_vars.yellowTandem,
				className: 'map__line',
				weight : 6,
				opacity : 0.9,
				dashArray : '12, 10',
				lineJoin: 'round',
				snakingSpeed: 200
			} );
			route.addLayer( marker ).addLayer( line );
		}
		if ( arr_l.length == 1 ) {
				var marker2 = L.marker( arr_m[i], {
					icon : new L.DivIcon({
						className: 'map__marker',
						html:   '<img class="map__marker__image" src="' + iconUrl + '">'+
								'<span class="map__marker__label">'+arr_c[i]+'</span>'
							})
				} );
				marker2.bindPopup(arr_c[i]);
			route.addLayer( marker2 );
		}
	}
	return route;
};
