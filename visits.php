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

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.3.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.3.4/dist/leaflet.js"></script>
<style>

#map_area {
	width:100%;
	height:400px;
}

#legend {
	width:20rem;

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

</style>

<div class = "wrap">
	<div id="plugin-header-wrapper">
		<div id="plugin-header">Trail Management</div>
		<div id="plugin-subheader">Website Visits</div>
	</div>
	<div id="map_area"></div>
	<div id="legend">

	</div>
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
	L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token=pk.eyJ1IjoibmNiaXJkY29uc2VydmF0aW9uIiwiYSI6ImNqcGE2Nm11aTAwbGMzcG92cDQ0OHEwdXUifQ.s-RzLKbQ7CX70Q3KPCfRwQ', {
	    attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
	    maxZoom: 18,
	    id: 'mapbox.streets',
	    accessToken: 'your.mapbox.access.token'
	}).addTo(map);



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
	var colorList = [
		'#341C09',
		'#B85B14',
		'#FC7307',
		'#236AB9',
		'#D4E4F7']; //list of colors to pick from for markers
	var currColor=0; //index of the current color

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
	    	console.log(data);

	    	//gather group names/values from the site list
			var markerSize = 1000;
	        jQuery.each(data,function(index,value){
	        	var dt = value.DTTM;
	        	var currPos = [value.LAT, value.LON];

	        	//color markers by platform
		    	if (!(value.PLATFORM in platformColor)) {
		    		//add to list, pick new colors
					platformColor[value.PLATFORM] = colorList[currColor];
					currColor = currColor + 1;		

					//add to legend
					lItem = jQuery('<div/>',{class:'legend-item'});
					swatch = jQuery('<div/>',{class:'color-swatch'});
					swatch.css("background",platformColor[value.PLATFORM]);
					lItem.append(swatch);
					lItem.append('<div class="color-name">' + value.PLATFORM + '</div>');

					jQuery('#legend').append(lItem);	
		    	}

		    	//POPUP FORMATTING
		    	var popupText = '<div class=".popup"><div>' + value.DTTM + '</div><div>' + value.PLATFORM + '</div><div>' + value.BROWSER + '</div></div>'

	        	console.log(dt + " : " + currPos);
	        	visitMarkers[dt] = L.circle(currPos,{
					color:platformColor[value.PLATFORM],
					fillColor: platformColor[value.PLATFORM],
					fillOpacity: 0.6,
					radius:markerSize,
					title:dt
				})
				.bindPopup(popupText)
				.addTo(map);

	        }); //end loop through results
			console.log(platformColor);
		},
		error: function(jqxhr, status, exception) {
			      console.log("error db request")
			      console.log(status + " : " + exception);
		}
	}); //end ajax call
}); // end document loaded code





</script>