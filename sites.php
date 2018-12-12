<?php 

/*
 * Template Name: Trail Site Management Template
 * Sites Page: displays info and allows management of trail sites
 * Displays the list of sites in the database, and allows editing...
 */


?>
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">

<div class="wrap">
<div id="plugin-header-wrapper">
	<div id="plugin-header">Trail Management</div>
	<div id="plugin-subheader">Site Data</div>
</div>
<div class="site-list-panel">
	<ul id="site-list" class="list-group">
	</ul>		
</div> <!-- site list div (left) -->

<div id="site-detail-panel">
	<div class="site-detail-button-wrapper">
		<i id="site-detail-new" title='' class="fas fa-plus-square">New</i>
		<!--<i id="site-detail-edit" title='click on site in list to edit' class="fas fa-edit">Edit</i>-->
		<i id="site-detail-delete" class="fas fa-trash-alt">Delete</i>
		<!--<i id="site-detail-clear" class="fas fa-eraser">Clear Fields</i>-->
		<i id="site-detail-copy" class="fas fa-copy">Copy</i>
		<i id="site-detail-save" class="fas fa-save">Save</i>
	</div>
	
	<form id="site-detail-form">
		
	</form>
</div>

<!--
<div id="dialog-confirm" title="Empty the recycle bin?">
  <p id="dialog-confirm-content"><span class="ui-icon ui-icon-alert" style="float:left; margin:12px 12px 20px 0;"></span>These items will be permanently deleted and cannot be recovered. Are you sure?</p>
</div>
-->
</div> <!-- wrap div -->
<script type="text/javascript">
	// ==================================================================
	// PLACE MARKERS, DEFINE MARKER BEHAVIOR
	//retrieve data to load markers and popup labels for each site
			// ajax call to load markers
	// ARRAY THAT DETERMINES WHAT TYPE OF INPUT FIELD TO DEFINE
	// index 0 = field type
	// index 1 = disabled
	// index 2 = linked field - slugify version of the value goes in linked field
	// index 3 = required field (true if required)

	fieldTypes = {
		"id":["text","disabled",,false], //auto generated
		"title":["text",,"siteslug",true],
		"siteslug":["text","disabled",,true],
		"category":["select",,"groupslug",false],
		"directions":["textarea",,,false],
		"description":["textarea",,,false],
		//"sname":["text","disabled",,false],
		"extwebsite":["url",,,false],
		"groupslug":["text","disabled",,false],
		"species":["text",,,false],
		"habitats":["textarea",,,false],
		//"coords":["text","disabled",,false],
		"lat":["text",,,true],
		"lon":["text",,,true],
		"region":["text",,,false],
		"boataccess":["checkbox",,,false],
		"fee":["checkbox",,,false],
		"picnic":["checkbox",,,false],
		"hiking":["checkbox",,,false],
		"trailmaps":["checkbox",,,false],
		"camping":["checkbox",,,false],
		"visitor":["checkbox",,,false],
		"hunting":["checkbox",,,false],
		"restrooms":["checkbox",,,false],
		"handicap":["checkbox",,,false],
		"viewing":["checkbox",,,false],
		"boatlaunch":["checkbox",,,false],
		"interpretive":["checkbox",,,false],
		"placeid":["text",,,false],
		"locid":["text",,,false],
		//"group":["checkbox",,,false],
		"what3words":["text",,,false]
	};

	buttonOnColor = 'rgb(68, 68, 68)';
	buttonOffColor = 'rgb(204, 204, 204)';

	//===========================================================================================================
	//POPULATE SITE LIST ON LEFT PANEL
	//EMBED key site data
	function populateSiteList() {
		jQuery('#site-list').empty();
	    jQuery.ajax({
	        type: "POST",
	        dataType: "json",
	        url: ajaxurl, //url for WP ajax php file, var def added to header in functions.php
	        data: {
	            'action': 'get_trailmgmt_data', //server side function
	            'dbrequest': 'site_list' //TESTING
	        },
	        success: function(data, status) {
	        	//AFTER LIST IS POPULATED, set up events for:

	            //POPULATE THE SITE LIST PANEL
	            jQuery.each(data,function(index, value) {
	            	//setup variables for each site
	            	var slug = this.siteslug;
	            	var title = this.title;
	            	var id = this.id;
	            	var group = this.category;
	            	var groupslug = this.groupslug;

	            	//jQuery('#site-list').append('<li class="site-list-item" id="' + slug + '">' + title + '</li>');
	            	jQuery('#site-list').append('<li class="site-list-item" id="' + id + '">' + title + '</li>');
	            	var siteData = `<div class="site-list-data" id="site-list-data-${id}" style="display:none;">
	            		<div class="site-list-data-category" id="site-list-data-category-${id}">${group}</div>
	            		<div class="site-list-data-categoryslug" id="site-list-data-categoryslug-${id}">${groupslug}</div>
	            		<div class="site-list-data-slug" id="site-list-data-slug-${id}">${slug}</div>
	            		</div>`
	            	jQuery('#' + id).append(siteData);

	            });

	            //SETUP EVENT FOR RETRIEVING AND DISPLAYING SITE DETAIL
			    jQuery('.site-list-item').click(function(){
			    	//console.log('site-list-item click triggered');
			    	buildSiteDataForm(this.id); //RETRIEVE SITE DATA, POPULATE FORM
		    	});

	        }, 
	        error: function(jqxhr, status, exception) {
	          console.log("error db request");
	          console.log(status + " : " + exception);
	    	}
	    });
	};

	//===========================================================================================================
	// BUILD UNIQUE LIST OF ALL CAGEGORIES (GROUPS)
	function getCategoryList(){
		//returns a unique list of values of the passed selector from the site-list
		var categories = {};
		jQuery(".site-list-data-category").each(function(index, value){
			//loop through all site-list-data items

			itemData = jQuery(this).text();

			//if (!categories.includes(itemData)) {
			if (!(itemData in categories)) {
				var itemId = jQuery(this).attr('id');
				var itemIdSlug = itemId.replace("category","categoryslug");
				//itemId = jQuery("#"+itemIdSlug).text();

				categories[jQuery("#"+itemIdSlug).text()] = itemData;
			}
		});
		return categories;
	}

	//===========================================================================================================
	// SLUGIFY input text
	function slugify(str) {
		//removes 
		//return	str.replace(/[^a-z0-9\s]/gi, '').replace(/[_\s]/g, '-')
		if(str) {
			return	str.toLowerCase().replace(/[_\W]+/g, "-");
		} else {
			return null;
		}


	}

	//===========================================================================================================
	// BUILD THE SITE DATA ENTRY FORM
    //function buildSiteDataForm(siteslug){
    function buildSiteDataForm(siteslug){
		//retrieve site info
		//siteslug = this.id;
		//ONCE setupBlankDataForm Developed, run first, then populate with data
		//if no siteslug sent, build blank fields from first record

		var bBlank; //flag to indicate if data should be filled 
		var dbRequest; //variable that determines request type
		var Disabled; //variable to determine if field is enabled.

		siteslug = siteslug || 'none'; //if siteslug blank - make 'none'

		if (siteslug == 'none') {bBlank = true;dbRequest = 'retrieve_data_fields';}	//no slug passed, build blank form
		else {bBlank = false;dbRequest = 'site_detail';}; //slug passed, build form and fill in data


		console.log('buildSiteDataForm triggered: ' + siteslug);
		jQuery("#site-detail-form").empty();

		jQuery.ajax({
		    type: "POST",
		    dataType: "json",
		    url: ajaxurl, //url for WP ajax php file, var def added to header in functions.php
		    data: {
		        'action': 'get_trailmgmt_data', //server side function
		        //'slug': siteslug,
		        'id': siteslug,
		        'dbrequest': dbRequest //TESTING
		    },
		    success: function(data, status) {
		    	//place code here to deal with database results
		    	//console.log("successful db request")
		    	//console.log(data);

		    	//gather group names/values from the site list

	        	var categoryList = getCategoryList();
		        jQuery.each(data,function(index,value){
		        	//loop through site data field, create elements in detail panel for each field
		        	//console.log(index + ":" + value);
		        	if (index in fieldTypes) {
			        	if (bBlank) {value = ""}; //blank out values if new blank form requested
			        	//console.log(index + ":" + value + ":" + fieldTypes[index]);
			        	var fieldData = fieldTypes[index];
			        	var inputType;
			        	var tagInfo = ''; //extra info to put in tag
			        	var inputTag = 'input';
			        	var inputClass = '';
			        	var interTagValue = '';
			        	var tagDisabled = fieldTypes[index][1];

			        	switch(fieldData[0]) {
			        	case "checkbox": 
			            	if (value == 1) {
			            		tagInfo = 'checked';
			            	}
			        		break;
			        	case "textarea":
			        		inputTag = 'textarea';
			        		inputClass = ' site-data-textarea';
			        		interTagValue = value;
			        		break;
			        	case "select":
			        		inputTag = 'select';
			        		inputClass = ' site-data-select';

			        		break;
			        	default:

			        	}


			        	//build and insert item
			        	/* DISABLED VERSION
			        	jQuery('#site-detail-form').append('<div class="site-data-item"><div class="site-data-heading" id="' + index + '-heading">' + index + '</div><'+ inputTag	+' type="'+ fieldTypes[index] + '" name="' + index + '" class="site-data-data'+inputClass+'" id="' + index +  '" value="' + value + '" ' + tagInfo +' disabled>'+interTagValue+'</'+inputTag+'></div>');*/

						
			        	jDataItem = jQuery('<div/>',{
			        		class:'site-data-item'
			        	});
						jDataItem.append('<div class="site-data-heading" id="' + index + '-heading">' + index + '</div>');
			        	
			        	if (fieldData[0]=='select') {
			        		//create a select item
			        		//console.log('building select item');
			        		jDataField = jQuery('<select/>', {
			        			name: index,
			        			class: 'site-data-data' + inputClass,
			        			id: index
			        		});

			        		jQuery.each(categoryList,function(ind,val){
			        			
			        			var selectItem = `<option value="${ind}">${val}</option>`
			        			jQuery(jDataField).append(selectItem);
			        			//TESTING
			        			//var sInd = slugify(val);
			        			//console.log(`test slugify: ${val} - ${sInd}`);
			        		});


		        			var selectItem = `<option value="new-category">New Category</option>`
			        		jQuery(jDataField).append(selectItem);

			        		//set value to the data from the database
			        		jQuery(jDataField).attr("value",slugify(value));

			        		//add behavior for new value?



			        	} else {

				        	jDataField = jQuery('<'+ inputTag	+' type="'+ fieldData[0] + '" name="' + index + '" class="site-data-data'+inputClass+'" id="' + index +  '" value="' + value + '" ' + tagInfo +' ' + tagDisabled+'>'+interTagValue+'</'+inputTag+'></div>');
			        	}
			        	
			        	jDataItem.append(jDataField); //add the data field to the data item
			        	jQuery('#site-detail-form').append(jDataItem); //add the data item to the form


			        	//==============================================================================
			        	// some fields populate others, build functions here to automate
			        	// category -> categoryslug
			        	// title -> siteslug
			        	// 
			        	var linkedField = fieldData[2];
			        	if (linkedField){
			        		jQuery("#"+index).change(function(){
			        			var newValue = slugify(jQuery(this).attr("value"));
			        			//console.log("change triggered on "+ index + " to change " + linkedField + " to " + newValue);
			        			//jQuery("#"+linkedField).removeAttr("disabled");
			        			jQuery("#"+linkedField).attr("value",newValue);
			        			jQuery("#"+linkedField).text(newValue);
			        			//jQuery("#"+linkedField).attr("disabled","disabled");
			        		});

			        	}

	        		} //if field in fieldTypes array above, else do nothing
		        });

		        jQuery('#id').css('background','#999'); //turn id field dark to indicate that it cannot be edited

		    }, 
		    error: function(jqxhr, status, exception) {
		      console.log("error db request")
		      console.log(status + " : " + exception);
			}

		});


    };

    //SET OF FUNCTIONS TO GOVERN BUTTON BEHAVIOR
    //TODO
    //	- add required fields behavior (don't save)
    //	- add popoup notifications
    //		- are you sure you want to delete?
    //		- you have made changes, are you sure you want to discard them?
    
    function disableButton(button){jQuery(button).css('color', buttonOffColor);}

    function enableButton(button){jQuery(button).css('color', buttonOnColor);}

    function readButton(button){
    	//reads if button on or off
    	if (jQuery(button).css('color')==buttonOnColor) {
    		//button on
    		return true;
   			console.log(jQuery(button).attr('id') + " button is on");
    	} else {
   			console.log(jQuery(button).attr('id') + " button is off");
    		return false;
    	}

    };


   	//###########################################################################################################
    //run these after document loads
    jQuery(document).ready(function() {

	//Modal Box functions
/*
		jQuery( function() {
		    jQuery( "#dialog-confirm" ).dialog({
		      resizable: false,
		      height: "auto",
		      width: 400,
		      modal: true,
		      buttons: {
		        "Delete all items": function() {
		          jQuery( this ).dialog( "close" );
		        },
		        Cancel: function() {
		          jQuery( this ).dialog( "close" );
		        }
		      }
		    });
	  	});

*/		//populate site list
		populateSiteList();


		//devise error checking
		// - auto-generate slug?
		// - make sure slug is unique
		// - autofill category/group field and region field? (keep running list from site list on left - site-list-data-group)
		// - SNAME is a repeat?
		// - auto-generate group-slug
		// LOWER PRIORITY
		// - pick coords/What3Words/eBird Hotspot from a map (or list) - popup...

		//click events
		jQuery('#site-detail-form').change(function(){
			//fires when data is changed in field
			enableButton(jQuery('#site-detail-save'));

		});

		//CREATE NEW RECORD TO BE COMPLETED
		jQuery('#site-detail-new').click(function(){
			console.log("new clicked");
			buildSiteDataForm(); // build blank data form
			disableButton(jQuery('#site-detail-save')); //disable save button, will be enabled when record started.

		});

		//DELETE RECORD
		jQuery('#site-detail-delete').click(function(){
			// BUILD FUNCTION TO DELETE RECORD
			console.log("delete clicked");

			id = jQuery("#id").attr('value');

			jQuery.ajax({
			    type: "POST",
			    dataType: "json",
			    url: ajaxurl, //url for WP ajax php file, var def added to header in functions.php
			    data: {
			        'action': 'get_trailmgmt_data', //server side function
			        'id': id,
			        'dbrequest': 'delete_site' //TESTING
			    },
			    success: function(data, status) {
			    	console.log("delete successful");
				    console.log(status + " : " + data);
				    populateSiteList(); //refresh the list
				    jQuery('#site-detail-new').trigger('click'); //trigger new record click
			    },
			    error: function(jqxhr, status, exception) {
			      console.log("error db request")
			      console.log(status + " : " + exception);
				}
			});

			//jQuery('#site-detail-clear').trigger('click');

		});

		//COPY DATA
		jQuery('#site-detail-copy').click(function(){
			//simply remove the contents of the ID field

			jQuery('#id').attr("value",'');

		});

		//SAVE DATA
		jQuery('#site-detail-save').click(function(){
			//BUILD FUNCTION TO SAVE RECORD

			console.log("save clicked");

			//get id
			var saveId = jQuery('#id').attr('value');
			if (saveId.length>0) {
				//update record
				console.log('updating data for site ' + saveId);
				var dbRequest = 'update_site_data';
			} else {
				//create new record
				console.log('creating new record');
				var dbRequest = 'create_new_site';
			}
			
			jQuery.ajax({
			    type: "POST",
			    dataType: "json",
			    url: ajaxurl, //url for WP ajax php file, var def added to header in functions.php
			    data: {
			        'action': 'get_trailmgmt_data', //server side function
			        'dbrequest': dbRequest,
			        'id' : saveId,
		 			'title' : jQuery('#title').attr('value'),
					'siteslug' : jQuery('#siteslug').attr('value'),
					'category' : jQuery('#category').attr('value'),
					'directions' : jQuery('#directions').attr('value'),
					'description' : jQuery('#description').attr('value'),
					'species' : jQuery('#species').attr('value'),
					'extwebsite' : jQuery('#extwebsite').attr('value'),
					'groupslug' : jQuery('#groupslug').attr('value'),
					'habitats' : jQuery('#habitats').attr('value'),
					'lat' : jQuery('#lat').attr('value'),
					'lon' : jQuery('#lon').attr('value'),
					'boataccess' : jQuery('#boataccess').attr('value'),
					'fee' : jQuery('#fee').attr('value'),
					'picnic' : jQuery('#picnic').attr('value'),
					'hiking' : jQuery('#hiking').attr('value'),
					'trailmaps' : jQuery('#trailmaps').attr('value'),
					'camping' : jQuery('#camping').attr('value'),
					'visitor' : jQuery('#visitor').attr('value'),
					'hunting' : jQuery('#hunting').attr('value'),
					'restrooms' : jQuery('#restrooms').attr('value'),
					'handicap' : jQuery('#handicap').attr('value'),
					'viewing' : jQuery('#viewing').attr('value'),
					'boatlaunch' : jQuery('#boatlaunch').attr('value'),
					'interpretive' : jQuery('#interpretive').attr('value'),
					'placeid' : jQuery('#placeid').attr('value'),
					'locid' : jQuery('#locid').attr('value'),
					'what3words' : jQuery('#what3words').attr('value')

			    },
			    success: function(data, status) {
			    	console.log("new site successful");
				    console.log(status + " : " + data);
				    populateSiteList(); //refresh the list
				    //jQuery("<div>New Record Created!</div>").dialog();
				    //get new id and fill in the field...

				    buildSiteDataForm(data);
			    },
			    error: function(jqxhr, status, exception) {
			      console.log("error db request")
			      console.log(status + " : " + exception);

				}
			});

		});


    });
</script>