<?php 

/*
 * Template Name: Trail Site Management Template
 * Displays the list of sites in the database, and allows editing...
 */


?>
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">

<style>
.site-list-panel {
	border-color:#777;
	position:fixed;
	width: 15rem;
	height:80vh;
	overflow-y:scroll;
	background-color:#ddd;
}

#site-detail-panel {
	margin-left:16rem;
	margin-right:1rem;
}

.site-list-item {
	cursor:pointer;
	padding:0.2rem 0.4rem;
}
.site-list-item:hover {
	background:#777;
	color:#fff;
}

.site-data-item {
	display:block;

}
.site-data-heading {
	padding: 0.2rem;
	margin-right:0.3rem;
	height:100%;
	display:inline;
    font-variant: small-caps;
    font-size: 1.1rem;
    font-weight: 700;
}

.site-data-data {
	width:100%;
	display:inline;
}
.site-data-textarea {
	height:10rem;
	width:100%;

}
#plugin-header-wrapper {
	width:100%;
	padding:0.6rem 0rem ;
}
#plugin-header {
	display:block;
	font-size:1.2rem;
	font-weight:700;
}
#plugin-subheader {
	font-size:1rem;
	font-style:italic;
	display:block;
}

#site-detail-button-wrapper {
	width:100%;
	padding:0.2rem;
	border-style:solid;
	border-color:#333;
	border-width:0.1rem;
	border-radius:0.2rem;
}

.fas {
	cursor:pointer;
}

#site-detail-new,
#site-detail-edit,
#site-detail-delete,
#site-detail-clear,
#site-detail-save {
	font-size:1.2rem;
	padding:0rem 0.4rem;
	display:inline;
}

#site-detail-clear,
#site-detail-save{
	float:right;
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
		<i id="site-detail-edit" title='click on site in list to edit' class="fas fa-edit">Edit</i>
		<i id="site-detail-delete" class="fas fa-trash-alt">Delete</i>
		<i id="site-detail-clear" class="fas fa-eraser">Clear Fields</i>
		<i id="site-detail-save" class="fas fa-save">Save</i>
	</div>
	
	<form id="site-detail-form">
		
	</form>
</div>

</div> <!-- wrap div -->
<script type="text/javascript">
		// ==================================================================
		// PLACE MARKERS, DEFINE MARKER BEHAVIOR
		//retrieve data to load markers and popup labels for each site
				// ajax call to load markers
	// ARRAY THAT DETERMINES WHAT TYPE OF INPUT FIELD TO DEFINE
	fieldTypes = {
		"id":"text",
		"title":"text",
		"siteslug":"text",
		"category":"text",
		"directions":"textarea",
		"description":"textarea",
		"sname":"text",
		"extwebsite":"url",
		"groupslug":"text",
		"species":"text",
		"habitats":"textarea",
		"coords":"text",
		"lat":"text",
		"lon":"text",
		"region":"text",
		"boataccess":"checkbox",
		"fee":"checkbox",
		"picnic":"checkbox",
		"hiking":"checkbox",
		"trailmaps":"checkbox",
		"camping":"checkbox",
		"visitor":"checkbox",
		"hunting":"checkbox",
		"restrooms":"checkbox",
		"handicap":"checkbox",
		"viewing":"checkbox",
		"boatlaunch":"checkbox",
		"interpretive":"checkbox",
		"placeid":"text",
		"locid":"text",
		"what3words":"text",
		"group":"text",
	}

    jQuery.ajax({
        type: "POST",
        dataType: "json",
        url: ajaxurl, //url for WP ajax php file, var def added to header in functions.php
        data: {
            'action': 'get_trailmgmt_data', //server side function
            // 'dbrequest': 'site_detail', //request type
            'dbrequest': 'site_list' //TESTING
            //'dbrequest': 'test' //TESTING
        },
        success: function(data, status) {
        	//AFTER LIST IS POPULATED, set up events for:
        	// new site completion, edit existing site, view data, etc.
        	//place code here to deal with database results
        	console.log("successful db request");
            console.log(status);
            console.log(data);

            //POPULATE THE SITE LIST PANEL
            jQuery.each(data,function(index, value) {
            	//setup variables for each site
            	var slug = this.siteslug;
            	var title = this.title;

            	jQuery('#site-list').append('<li class="site-list-item" id="' + slug + '">' + title + '</li>');

            });

            //SETUP EVENT FOR RETRIEVING AND DISPLAYING SITE DETAIL
		    jQuery('.site-list-item').click(function(){
		    	console.log('site-list-item click triggered');
		    	buildSiteDataForm(this.id); //RETRIEVE SITE DATA, POPULATE FORM
		    	});


		    //TOGGLE EDIT BUTTON (enables and disables fields)
		    //NEEDS WORK, make sure some fields not editaable (id, coords)
		    jQuery('#enable-editing').click(function(){
		    	if (this.text == "Edit" ) {
		    		jQuery('.site-data-data').removeAttr("disabled");
		    		this.text = "Stop Editing";

		    	} else {
		    		jQuery('.site-data-data').attr("disabled","disabled");
		    		this.text = "Edit";

		    	};

		    	jQuery.ajax({
		    		type: "POST",
		    		dataType:"text",
		    		url:ajaxurl,
		    		data: {
			            'action': 'get_trailmgmt_data', //server side function
			            'dbrequest': 'retrieve_data_fields'
			        },
			        success: function(data, status) {
			        	console.log('retrieved structure!');
			        	console.log(data);
			        },
			        error: function(jqxr,status,exception){
			          console.log("error db request")
			          console.log(status + " : " + exception);

			        	
			        }
		    	});

		    });

        }, 
        error: function(jqxhr, status, exception) {
          console.log("error db request")
          console.log(status + " : " + exception);
    	}
    });




    function setupBlankDataForm(){

    	//LOOP THROUGH DATABASE ITEMS, create blank fields



	    //SETUP EVENT FOR CREATING A NEW SITE when button clicked
	    jQuery('#test-new-site').click(function(){
	    	//testing creating a new site

	    	//collect data from fields, populate jsonrowdata
	    	var jsonrowdata ={};
	    	jQuery('.site-data-data').each(function(index, value){
	    		jsonrowdata[this.id]=this.value;
	    	});
	    	console.log(jsonrowdata);
/*
	    	jQuery.ajax({
	    		type:"POST",
	    		url:ajaxurl,
	    		data: {
	    			'action': 'get_trailmgmt_data',
	    			'dbrequest': 'create_new_site',
	    			'rowdata': jsonrowdata
	    		},
	    		success: function(data,status){
	    			console.log("successful!");
	    			console.log(status);
	    			console.log(data);

	    		},
		        error: function(jqxhr, status, exception) {
		          console.log("error db request")
		          console.log(status + " : " + exception);
		    	}
	    	});
	    	*/
	    });

    }

    function buildSiteDataForm(siteslug){
		//retrieve site info
		//siteslug = this.id;
		//ONCE setupBlankDataForm Developed, run first, then populate with data
		//if no siteslug sent, build blank fields from first record

		var bBlank; //flag to indicate if data should be filled 
		var dbRequest; //variable that determines request type

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
		        'slug': siteslug,
		        'dbrequest': dbRequest //TESTING
		    },
		    success: function(data, status) {
		    	//place code here to deal with database results
		    	console.log("successful db request")
		        jQuery.each(data,function(index,value){
		        	//loop through site data, create elements in detail panel for each field

		        	if (bBlank) {value = null}; //blank out values if new blank form requested

		        	var inputType;
		        	var tagInfo = ''; //extra info to put in tag
		        	var inputTag = 'input';
		        	var inputClass = '';
		        	var interTagValue = '';

		        	switch(fieldTypes[index]) {
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
		        	default:

		        	}



		        	//build and insert item
		        	jQuery('#site-detail-form').append('<div class="site-data-item"><div class="site-data-heading" id="' + index + '-heading">' + index + '</div><'+ inputTag	+' type="'+ fieldTypes[index] + '" name="' + index + '" class="site-data-data'+inputClass+'" id="' + index +  '" value="' + value + '" ' + tagInfo +' disabled>'+interTagValue+'</'+inputTag+'></div>');

		        });

		    }, 
		    error: function(jqxhr, status, exception) {
		      console.log("error db request")
		      console.log(status + " : " + exception);
			}

		});


    };

    //run these after document loads
    jQuery(document).ready(function() {


    	//click events
    	jQuery('site-detail-new').click(function(){
    		console.log("new clicked");
    		buildSiteDataForm(); // build blank data form

    	});
    	
    	//enable editing of fields
    	jQuery('site-detail-edit').click(function(){
    		console.log("edit clicked");


	    	if (this.text == "Edit" ) {
	    		jQuery('.site-data-data').removeAttr("disabled");
	    		this.text = "Stop Editing";

	    	} else {
	    		jQuery('.site-data-data').attr("disabled","disabled");
	    		this.text = "Edit";

	    	};
    	});
    	jQuery('site-detail-clear').click(function(){
    		console.log("clear clicked");
    		buildSiteDataForm();

    	});
    	jQuery('site-detail-delete').click(function(){
    		// BUILD FUNCTION TO DELETE RECORD
    		console.log("delete clicked");

    	});

    	//save existing data
    	jQuery('site-detail-save').click(function(){
    		//BUILD FUNCTION TO SAVE RECORD
    		console.log("save clicked");
    	});


    });
</script>
