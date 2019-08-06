<?php 

/*
 * Template Name: Trail Site Management Template
 * Visits Page: displays website visit info
 *
 * TODO
 *	- Add different map analysis
 * 		- density maps
 *	- Add ability to restrict displayed data by
 * 		- date range (month, year, etc.)
 *		- region?
 *		- within a radius of stite
 *	- Add ability to generate a report for communities, etc.
 *	- download data...
 *
 */

?>

<!-- <link rel="stylesheet" href="https://unpkg.com/leaflet@1.3.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.3.4/dist/leaflet.js"></script> -->
<style>

#map_area {
	width:100%;
	height:400px;
}


.leaflet-control-layers label {
	font-weight:800;
	padding-top:0.6rem;
}

.leaflet-control-layers 
/*
#legend {
	width: 50%;
    display: inline-block;
    float: left;
}
#under-map {
	margin-top:1rem;
}

.color-swatch,
.color-name {
	display:inline;
	float:left;
	height:inherit;
}
.legend-item {
	width:inherit;
	display:block;
	padding:0.5rem;
	height:2rem;
}
.color-swatch {
	width:2rem;
}

.color-name {
	vertical-align: center;
	font-size:1rem;
	padding:0.5rem;
}
*/
/* The container */
#options {
	display:inline-block;
	width:50%;
}
.container {
  display: block;
  position: relative;
  padding-left: 2rem;
  margin-bottom: 12px;
  cursor: pointer;
  font-size: 0.8rem;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
}

/* Hide the browser's default checkbox */
.container input {
  position: absolute;
  opacity: 0;
  cursor: pointer;
  height: 0;
  width: 0;
}

/* Create a custom checkbox */
.checkmark {
  position: absolute;
  top: 0;
  left: 0;
  height: 1.3rem;
  width: 1.3rem;
  background-color: #1a2c42;
  border:1px solid #1a2c42;
}

/* On mouse-over, add a grey background color */
/*.container:hover input ~ .checkmark {
  background-color: #ccc;
}*/

/* When the checkbox is checked, add a blue background */
.container input:checked ~ .checkmark {
  background-color: #1A2C42;
}

/* Create the checkmark/indicator (hidden when not checked) */
.checkmark:after {
  content: "";
  position: absolute;
  display: none;
}

/* Show the checkmark when checked */
.container input:checked ~ .checkmark:after {
  display: block;
}

/* Style the checkmark/indicator */
.container .checkmark:after {
	left: 0.4rem;
	top: 0.15rem;
	width: 0.3rem;
	height: 0.6rem;
  border: solid white;
  border-width: 0 3px 3px 0;
  -webkit-transform: rotate(45deg);
  -ms-transform: rotate(45deg);
  transform: rotate(45deg);
}

</style>

<div class = "wrap">
	<div id="plugin-header-wrapper">
		<div id="plugin-header">Trail Management</div>
		<div id="plugin-subheader">Website Visits</div>
	</div>
	<div id="map_area"></div>

</div>

<script type="text/javascript">


var map;
var currZoom;
var visitMarkers = {};
var defaultZoomLevel = 7;
var defaultLatLng = [35.3,-79.5]; // offcenter to allow for panel - leaflet

//###########################################################################################################
//run these after document loads
jQuery(document).ready(function() {

	<!--MAP SETUP-->

	//TEST Leaflet
	//for marker size regulation
	currZoom = {
		start: defaultZoomLevel,
		end: defaultZoomLevel
	};

	map = L.map('map_area',{
		center:defaultLatLng,
		zoom:defaultZoomLevel
	});
/*	Functions that change size of the markers
	.on('zoomstart',function(e){
		currZoom.start = map.getZoom();
	})
	.on('zoomend',function(e){
		//change marker sizes;
		currZoom.end = map.getZoom();
		var diff = currZoom.start - currZoom.end;
		$(".slug").each(function(index,value){
			var cSlug = this.innerHTML;
			if(diff>0){
				siteHighlight[cSlug].setRadius(siteHighlight[cSlug].getRadius()*4);
				siteMarkers[cSlug].setRadius(siteMarkers[cSlug].getRadius()*4);
			} else {
				siteHighlight[cSlug].setRadius(siteHighlight[cSlug].getRadius()/4);
				siteMarkers[cSlug].setRadius(siteMarkers[cSlug].getRadius()/4);

			};
		});
	});	
*/	
	var topoLayer = L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token=pk.eyJ1IjoibmNiaXJkY29uc2VydmF0aW9uIiwiYSI6ImNqcGE2Nm11aTAwbGMzcG92cDQ0OHEwdXUifQ.s-RzLKbQ7CX70Q3KPCfRwQ', {
	    attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
	    maxZoom: 18,
	    id: 'mapbox.streets',
	    accessToken: 'your.mapbox.access.token'
	});
	//.addTo(map);
	var streetLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
	    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
	});


	//================================================================
	//RETRIEVE visit records
	//only get 100 for testing
	//TODO
	//	- zoom to all visits
	//	- restrict by platform
	//	- only show mobile?
	//	- zoom to NC only
	//	- spatial query around site - how many visits?
	//	- summarize map - get summaries based on the current visible map

	var platformColor = {}; //unique list of platforms
	var platformLayers = {}; //contains layers for each type of platform
	var platformHeat = {}; //contains platform layers for heat map
	var colorList = [
		'#341C09',
		'#B85B14',
		'#FC7307',
		'#236AB9',
		'#D4E4F7']; //list of colors to pick from for markers
	var currColor=0; //index of the current color
	var visitLayer = L.layerGroup();

	jQuery.ajax({
	    type: "POST",
	    dataType: "json",
	    url: ajaxurl, //url for WP ajax php file, var def added to header in functions.php
	    data: {
	        'action': 'get_trailmgmt_data', //server side function
	        'dbrequest': 'get_visits' //TESTING
	    },
	    success: function(data, status) {
	    	//place code here to deal with database results
	    	console.log("successful db request")
	    	//console.log(data);

	    	//gather group names/values from the site list
			var markerSize = 1000;

	        jQuery.each(data,function(index,value){
	        	var dt = value.DTTM;
	        	var currPos = [value.LAT, value.LON];

	        	if (!value.LAT){console.log("lat null!")};
	        	if (!value.LON){console.log("lon null!")};
	        	//color markers by platform
		    	if (!(value.PLATFORM in platformColor)) {
		    		// if platform is new, set up items

		    		//add to list, pick new marker colors
					platformColor[value.PLATFORM] = colorList[currColor];
					currColor = currColor + 1;		

					//add to legend
/*					label = jQuery('<label/>',{class:'container',text:value.PLATFORM});
					input = jQuery('<input/>',{id:'check' + value.PLATFORM, class:'legendCheck', type:'checkbox',checked:'checked'});
					span = jQuery('<span/>', {class:'checkmark'});
					span.css('background-color',platformColor[value.PLATFORM]);

					label.append(input);
					label.append(span);

					jQuery('#legend').append(label);	
					console.log('adding legend item');*/

					//create new layer for this platform
					platformLayers[value.PLATFORM] = L.layerGroup();
					platformHeat[value.PLATFORM] = [];

		    	}


		    	//POPUP FORMATTING
		    	var popupText = '<div class=".popup"><div>' + value.DTTM + '</div><div>' + value.PLATFORM + '</div><div>' + value.BROWSER + '</div></div>'

	        	//console.log(dt + " : " + currPos);
	        	//visitMarkers[dt] = L.circle(currPos,{
	        	visitMarkers[dt] = L.circle(currPos,{
					color:platformColor[value.PLATFORM],
					fillColor: platformColor[value.PLATFORM],
					fillOpacity: 0.6,
					radius:markerSize,
					title:dt
				})
				.bindPopup(popupText);

				//add marker to appropriate layer
				platformLayers[value.PLATFORM].addLayer(visitMarkers[dt]);
				//add location data to appropriate heatmap data
				platformHeat[value.PLATFORM].push([currPos]);
				//visitLayer.addLayer(visitMarkers[dt]);
				//.addTo(map);

	        }); //end loop through results

	        var baseMaps = {
	        	"Topo": topoLayer
	        }
/*	        var overlayMaps = {
	        	"Win32": visitLayer
	        };*/

			var layerControl = L.control.layers(baseMaps);
			//loop through platform types
			jQuery.each(platformLayers, function(index, item){
				//console.log(index);
				//console.log(item);
				layerControl.addOverlay(item,'<span class="overlay-name" style="color:' + platformColor[index] + ';">' + index + '</span>');
			});


			//load heat map
			//var heat = L.heatLayer(platformHeat["Win32"],{radius:25});
			//heat.addTo(map);
			
			//layerControl.addOverlay(heat,'<span class="overlay-name" style="color:red;">Heat Map</span>');

			layerControl.addTo(map);
			//visitLayer.addTo(map);
			map.eachLayer(function(layer){
				console.log(layer);
			});

			//format last item in list - heat map
			jQuery(".leaflet-control-layers-overlays > label").last().css({
				"margin-top": "0.5rem",
				"padding": "0.5rem 0rem",
				"border-top": "0.2rem solid rgb(26,44,66)"
			});

		},
		error: function(jqxhr, status, exception) {
			      console.log("error db request")
			      console.log(status + " : " + exception);
		}
	}); //end ajax call



}); // end document loaded code





</script>