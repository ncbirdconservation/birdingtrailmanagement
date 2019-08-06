<?php 

/*
 * Template Name: Trail Site Management Template
 * Sites Page: displays info and allows management of trail sites
 * Displays the list of sites in the database, and allows editing...
 */


?>
<!-- eventually, move this to the main birdingtrailmanagement.php page -->
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">

<style>
	#siteslug-duplicate-warning {
	color:#ff0000;
	font-weight:600;
	font-size:0.8rem;
}


</style>

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
	            	jQuery('#site-list').append('<li class="site-list-item" id="' + slug + '">' + title + '</li>');
	            	var siteData = `<div class="site-list-data" id="site-list-data-${slug}" style="display:none;">
	            		<div class="site-list-data-category" id="site-list-data-category-${slug}">${group}</div>
	            		<div class="site-list-data-categoryslug" id="site-list-data-categoryslug-${slug}">${groupslug}</div>
	            		<div class="site-list-data-slug" id="site-list-data-slug-${slug}">${slug}</div>
	            		</div>`
	            	jQuery('#' + slug).append(siteData);

	            });

	            //SETUP EVENT FOR RETRIEVING AND DISPLAYING SITE DETAIL
			    jQuery('.site-list-item').click(function(){
			    	//console.log('site-list-item click triggered');
			    	console.log(this.id + " list item clicked");
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
			//console.log("category: "+ itemData);

			//if (!categories.includes(itemData)) {
			if (!(itemData in categories)) {
				var itemId = jQuery(this).attr('id'); //catagory slug
				var itemIdSlug = itemId.replace("category","categoryslug");
				//itemId = jQuery("#"+itemIdSlug).text();

				categories[jQuery("#"+itemIdSlug).text()] = itemData;
			}
		});
		//console.log(categories);
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
   			console.log(jQuery(button).attr('id') + " button is on");
    		return true;
    	} else {
   			console.log(jQuery(button).attr('id') + " button is off");
    		return false;
    	}

    };

    function isSlugUnique(slug) {
    	//check to make sure the passed slug is not in the list
    	//when siteslug is changed evaluate if unique
    	//return false if slug not passed
    	var response = true;
    	if (slug){
    		//slug exists
    		//console.log('slug passed');
			jQuery('.site-list-data-slug').each(function(index,value){
				if (slug == jQuery(this).text()) {response=false;} //loop through slugs in list
			});
    		
    	} else {response = false;}
    	console.log(slug + ' is unique?:' + response);
    	return response;
    }


    function isDataClean() {
    	//check to see if the data form has required records and no duplicates
    	bSlugUnique = isSlugUnique();

    	if (bSlugUnique ){
    		return true;
    	} else {
    		return false;
    	}

    };

	//===========================================================================================================
	// BUILD THE SITE DATA ENTRY FORM

	function buildFormField (index,value){
		//input field name and value, return data form object to be inserted...
		if (index in fieldTypes) {// make sur the passed field name is in the field array
	    	//if (bBlank) {value = ""}; //blank out values if new blank form requested
	    	//console.log(index + ":" + value + ":" + fieldTypes[index]);
	    	var fieldData = fieldTypes[index]; //retrieve appropriate fieldType info from array
	    	//var inputType; //not needed?
	    	var tagInfo = ''; //extra info to put in tag for fields like checkbox
	    	var inputTag = 'input'; //default tag
	    	var inputClass = ''; //css class for the field
	    	var interTagValue = ''; //value to go inside html tags for fields like checkbox
	    	var tagDisabled = fieldData[1]; //is this field disabled?
	    	var bRequired = fieldData[3]; //is this field required?
	    	var textRequired = ""; //dunno?

			//create a jQuery object for the field
	    	jDataItem = jQuery('<div/>',{
	    		class:'site-data-item'
	    	});
	    	if (bRequired) {textRequired = '*'};
			jDataItem.append('<div class="site-data-heading" id="' + index + '-heading">' + index + textRequired + '</div>');

	    	switch(fieldData[0]) {
	    	case "checkbox": 
	        	if (value == 1) {
	        		tagInfo = 'checked';
	        	}
	        	jDataField = jQuery('<'+ inputTag	+' type="'+ fieldData[0] + '" name="' + index + '" class="site-data-data'+inputClass+'" id="' + index +  '" value="' + value + '" ' + tagInfo +' ' + tagDisabled+'>'+interTagValue+'</'+inputTag+'></div>');
	    		break;
	    	case "textarea":
	    		inputTag = 'textarea';
	    		inputClass = ' site-data-textarea';
	    		interTagValue = value;

	        	jDataField = jQuery('<'+ inputTag	+' type="'+ fieldData[0] + '" name="' + index + '" class="site-data-data'+inputClass+'" id="' + index +  '" value="' + value + '" ' + tagInfo +' ' + tagDisabled+'>'+interTagValue+'</'+inputTag+'></div>');
	    		break;
	    	case "select":
	    		inputTag = 'select';
	    		inputClass = ' site-data-select';

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
	    		break;
	    	default:

	    	} // end of switch


	    	//build and insert item
	    	/* DISABLED VERSION
	    	jQuery('#site-detail-form').append('<div class="site-data-item"><div class="site-data-heading" id="' + index + '-heading">' + index + '</div><'+ inputTag	+' type="'+ fieldTypes[index] + '" name="' + index + '" class="site-data-data'+inputClass+'" id="' + index +  '" value="' + value + '" ' + tagInfo +' disabled>'+interTagValue+'</'+inputTag+'></div>');*/

	    	jDataItem.append(jDataField); //add the data field to the data item

	    	return jDataItem;
		}// end if checking if passed field name is in array
	};//end of function


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
		console.log('building form for: ' + siteslug);

		console.log('buildSiteDataForm triggered: ' + siteslug);
		jQuery("#site-detail-form").empty(); //clear out the exising form


		if (siteslug == 'none') {
			bBlank = true;
			dbRequest = 'retrieve_data_fields';
			}	//no slug passed, build blank form
		else {

			bBlank = false;
			dbRequest = 'site_detail'; //slug passed, build form and fill in data
			//retrieve record, use fields for building form, fill in field data if siteslug present
			jQuery.ajax({
			    type: "POST",
			    dataType: "json",
			    url: ajaxurl, //url for WP ajax php file, var def added to header in functions.php
			    data: {
			        'action': 'get_trailmgmt_data', //server side function
			        'siteslug': siteslug,
			        'dbrequest': dbRequest //TESTING
			    },
			    success: function(data, status) {
			    	//place code here to deal with database results
			    	//console.log("successful db request")
			    	//console.log(data);

			    	//gather group names/values from the site list

		        	var categoryList = getCategoryList();

			        jQuery.each(data,function(index,value){
			        	//loop through each site data field, create elements in detail panel for each field
			        	//console.log(index + ":" + value);

		        		//change this to invoke the buildFormField function

			        	//jQuery('#site-detail-form').append(jDataItem); //add the data item to the form
			        	jQuery('#site-detail-form').append(buildFormField(index, value)); //add the data item to the form


				        //=================================================================
				        //SETUP FORM EVENTS
			        	//==============================================================================
			        	// some fields populate others, build functions here to automate
			        	// category -> categoryslug
			        	// title -> siteslug
			        	// 
			        	var linkedField = fieldData[3]; //name of linked field
			        	if (linkedField){
			        		console.log ("linked field found: " + linkedField);
			        		jQuery("#"+index).change(function(){
			        			console.log(index + " field changed");
			        			var newValue = slugify(jQuery(this).attr("value"));
			        			//console.log("change triggered on "+ index + " to change " + linkedField + " to " + newValue);
			        			//jQuery("#"+linkedField).removeAttr("disabled");
			        			jQuery("#"+linkedField).attr("value",newValue);
			        			jQuery("#"+linkedField).text(newValue);
			        			//jQuery("#"+linkedField).attr("disabled","disabled");
			        			if (index == 'title') { //check for uniqueness in title field
			        				var slug = newValue;
			        				console.log(slug + ' populated, fixin to check if unique')
							    	//first check to make sure we are not editing, but creating a new record
							    	console.log('id: ' + jQuery('#id').text());
							    	console.log('id length: ' + jQuery('#id').text().length);
							    	if (!(jQuery('#id').text().length>0)) { //id not populated, therefore a new record
								    	if (isSlugUnique(slug)) { //check if slug is unique...
								    		console.log("slug is unique");
								    		jQuery('#siteslug-duplicate-warning').remove(); //remove warning label
								    		jQuery('#siteslug').css('color',''); //remove red outline
								    	} else {
								    		//turn text red
								    		console.log("slug is NOT unique");
								    		jQuery('#siteslug-heading').append('<div id="siteslug-duplicate-warning">DUPLICATE VALUE!</div>');
								    		jQuery('#siteslug').css('color','#ff0000');//add red outline
								    	}
								    }
			        				
			        			}
			        		});

				        } // end of linked field code


				        jQuery('#id').css('background','#999'); //turn id field dark to indicate that it cannot be edited
			        
			        }); //end of if field loop...
			    }, // end of dbrequest success
			    error: function(jqxhr, status, exception) {
			      console.log("error db request")
			      console.log(status + " : " + exception);
    		}); // end of dbrequest
		} //end if/else
    }; //end of function



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
			//add code here to save record...
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
			//simply remove the contents of the ID and slug field

			jQuery('#siteslug').attr("value",'');
			jQuery('#id').attr("value",'');

		});

		//SAVE DATA
		jQuery('#site-detail-save').click(function(){
			//BUILD FUNCTION TO SAVE RECORD

			console.log("save clicked");

			//get id
			var saveId = jQuery('#id').attr('value');
			var slugToSave = jQuery('#siteslug').attr('value'); 			
			if (saveId.length>0 && ) {
				//update record
				console.log('updating data for site ' + saveId);
				var dbRequest = 'update_site_data';
			} else {
				//create new record
				console.log('creating new record');
				var dbRequest = 'create_new_site';
			}
			
			if (isDataClean()){
				jQuery.ajax({
				    type: "POST",
				    dataType: "json",
				    url: ajaxurl, //url for WP ajax php file, var def added to header in functions.php
				    data: {
				        'action': 'get_trailmgmt_data', //server side function
				        'dbrequest': dbRequest,
				        'id' : saveId,
			 			'title' : jQuery('#title').attr('value'),
						'siteslug' : slugToSave,
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
	
					    buildSiteDataForm(slugToSave);
				    },
				    error: function(jqxhr, status, exception) {
				      console.log("error db request")
				      console.log(status + " : " + exception);
	
					}
				}); //end ajax call
			} //end if data clean
		});// end save button click


    });
</script>
