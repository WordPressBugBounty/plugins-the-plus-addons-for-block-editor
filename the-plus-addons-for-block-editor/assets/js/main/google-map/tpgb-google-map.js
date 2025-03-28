document.addEventListener("DOMContentLoaded", function() {
	tpgbInitMap();
})

function tpgbInitMap(){
 var elements = document.querySelectorAll('.tpgb-adv-map');

 elements.forEach(function(el) {
		 var data_id = el.getAttribute('data-id'),
				 data = JSON.parse(el.getAttribute('data-map-settings')),
				 map = null,
				 bounds = null,
				 infoWindow = null,
				 position = null,
				 styles1 = '';

		 if (!el.classList.contains("map-loaded")) {
				 var map_toBuild = [];
				 var build = function() {
						 if (styles1 != '') {
								 data.options.styles = JSON.parse(styles1);
						 }

						 bounds = new google.maps.LatLngBounds();
						 map = new google.maps.Map(document.getElementById(data_id), data.options);
						 infoWindow = new google.maps.InfoWindow();

						 var marker, i;
						 map.setTilt(45);

						 google.maps.event.addListener(infoWindow, 'domready', function() {
								 var iwOuter = document.querySelector('.gm-style-iw');
								 var iwBackground = iwOuter.previousElementSibling;

								 var parentdiv = iwOuter.parentElement;
								 parentdiv.classList.add('marker-icon');
								 var iwCloseBtn = iwOuter.nextElementSibling;
								 iwCloseBtn.style.display = 'none';

								 iwOuter.classList.add('marker-title');
						 });

						 for (i = 0; i < data.places.length; i++) {
								 position = new google.maps.LatLng(data.places[i].latitude, data.places[i].longitude);

								 bounds.extend(position);

								 marker = new google.maps.Marker({
										 position: position,
										 map: map,
										 title: data.places[i].address,
										 icon: data.places[i].pin_icon
								 });

								 google.maps.event.addListener(marker, 'click', (function(marker, i) {
										 return function() {
												 if (data.places[i].address.length > 1) {
														 infoWindow.setContent('<div class="gmap_info_content"><p>' + data.places[i].address + '</p></div>');
												 }

												 infoWindow.open(map, marker);
										 };
								 })(marker, i));

								 map.fitBounds(bounds);
						 }

						 var bounds_Listener = google.maps.event.addListener((map), 'idle', function(event) {
								 this.setZoom(data.options.zoom);
								 google.maps.event.removeListener(bounds_Listener);
						 });

						 var update = function() {
								 google.maps.event.trigger(map, "resize");
								 map.setCenter(position);
						 };
						 update();
				 };

				 var init_Map = function() {
						 for (var i = 0, l = map_toBuild.length; i < l; i++) {
								 map_toBuild[i]();
						 }
				 };

				 var initialize = function() {
						 init_Map();
				 };

				 map_toBuild.push(build);
				 initialize();
				 el.classList.add("map-loaded");
		 }
 });
};