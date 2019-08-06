<?php

/*
Plugin Name:  Birding Trail Management
Plugin URI:   https://developer.wordpress.org/plugins/birdingtrailmanagement/
Description:  For managing nature trail site information, pairs with birdingtrail theme
Version:      0.1
Author:       Scott Anderson, ncbirdconservation
Author URI:   https://ncbirdingtrail.org
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  wporg
Domain Path:  /languages
*/

//prevents direct access
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/* ====================================================================
* Setup
*/

 function trailmgmt_enqueue_style() {
	wp_enqueue_style('trailmgmt', plugin_dir_url(__FILE__) . '/css/trailmgmt.css',false);
	wp_enqueue_style('trailmgmt-admin-ui-css',
                plugin_dir_url(__FILE__) . '/css/jquery-ui-1.12.1/jquery-ui.min.css',
                false);
	wp_enqueue_style('leaflet-style',"https://unpkg.com/leaflet@1.3.4/dist/leaflet.css",false);
 }

//function trailmgmt_load_scripts ($hook) {
function trailmgmt_load_scripts () {
	//if ($hook != 'sites.php') { return;}
	wp_enqueue_script('jquery-ui-dialog');
	wp_enqueue_script('leaflet',"https://unpkg.com/leaflet@1.3.4/dist/leaflet.js",false);
	wp_enqueue_script('heatmap',plugin_dir_url(__FILE__) . '/js/leaflet-heat.js',false);
};

add_action( 'admin_enqueue_scripts', 'trailmgmt_enqueue_style' );
add_action( 'admin_enqueue_scripts', 'trailmgmt_load_scripts' );


/* ====================================================================
* Creates Menu for Managing Trail Data
*/

//hook for adding admin menus
add_action('admin_menu','birdingtrail_add_pages');

function birdingtrail_add_pages() {

	$page_title = 'Trail Management';
	$menu_title = 'Trail Management';
	$capability = 'manage_options';
	$menu_slug = 'trail-management';
	//$function = trail_management_menu();

	//top menu
	//add_menu_page( $page_title, $menu_title, $capability, $menu_slug, trail_management_menu());
	add_menu_page( 
		$page_title,
		$menu_title,
		$capability,
		$menu_slug,
		'trailmgmt_menu'
	);

//		plugins_url('birdingtrailmanagement/img/favicon.ico'), //TESTING
	//sub-menu (setup)
	//plugin function, reference theme to use
	//import functions
	//descriptions (fields in each table, etc.)
	//$submenu_setup_slug = 'setup';
	//add_submenu_page($menu_slug,'Setup','Setup',$capability,$submenu_setup_slug);

	//sub-menu (trail sites)
	// forms for data entry, table of existing sites
	
	$submenu_trailsites_slug = 'trail-sites';
	add_submenu_page(
		$menu_slug,
		'Trail Sites',
		'Trail Sites',
		$capability,
		plugin_dir_path( __FILE__ ) . 'sites.php',
		null
	);

	/* FUTURE MENUS
	//sub-menu (businesses)
	$submenu_business_slug = 'businesses';
	add_submenu_page($menu_slug,$submenu_business_slug,$capability,$menu_slug);
	*/

	//sub-menu (visits)
	$submenu_visits_slug = 'visits';
	add_submenu_page(
		$menu_slug,
		'Site Visits',
		'Site Visits',
		$capability,
		plugin_dir_path(__FILE__) . 'visits.php',
		null
	);

}


function trailmgmt_menu() {
	//builds trail mgmt menu
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	echo '<div class="wrap">';
	echo '<p>Here is where the form would go if I actually had options.</p>';
	echo '</div>';

}

/* ====================================================================
* Plugin Activation
* Creates table(s) to store site data, visit data, and business data
*/

/* VERSIONING - good idea, but disabled for debugging
global $trailmgmt_db_version;
$trailmgmt_db_version = '0.1';
*/

function trailmgmt_setup_site_table(){
	global $wpdb;
	global $trailmgmt_db_version;

	$table_name = $wpdb->prefix . $sitedatatable;
	$charset_collate = $wpdb->get_charset_collate();

	//CONSIDER REVISION, REMOVING UNEEDED FIELDS
	// group, coords, others? change primary key to id?
	$sql = "CREATE TABLE $table_name (

		id mediumint(9) NOT NULL AUTO_INCREMENT,
		title varchar(200) NOT NULL,
		siteslug varchar(200) NOT NULL,
		category varchar(100),
		`group` varchar(100),
		directions text,
		description text,
		sname varchar(100),
		extwebsite text,
		groupslug varchar(200),
		species text,
		habitats text,
		coords varchar(50),
		lat decimal (11,8) NOT NULL,
		lon decimal (11,8) NOT NULL,
		region varchar(8),
		boataccess int(1),
		fee int(1),
		picnic int(1),
		hiking int(1),
		trailmaps int(1),
		camping int(1),
		visitor int(1),
		hunting int(1),
		restrooms int(1),
		handicap int(1),
		viewing int(1),
		boatlaunch int(1),
		interpretive int(1),
		placeid varchar(255),
		locid varchar(255),
		what3words varchar(255)
		PRIMARY KEY  (siteslug)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

}

function trailmgmt_setup_visit_table(){
	//Insert code here to set up the trail site table

	global $wpdb;
	global $trailmgmt_db_version;

	$table_name = $wpdb->prefix . 'trailmgmt_visits';
	$charset_collate = $wpdb->get_charset_collate();

	//CONSIDER REVISION, REMOVING UNEEDED FIELDS
	// group, coords, others? change primary key to id?
	$sql = "CREATE TABLE $table_name (
		ID mediumint(9) NOT NULL AUTO_INCREMENT,
		DTTM timestamp,
		NCBTUSERID text,
		PLATFORM varchar(100),
		BROWSER text,
		LAT decimal (11,8),
		LON decimal (11,8)
		PRIMARY KEY  (ID)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	//add_option( 'trailmgmt_db_version', $trailmgmt_db_version ); //see comment above


}

function trailmgmt_install() {
    // trigger our function that registers the custom post type
	//==============================================================
	//TODO
	// - add visit table also
	// - add businesses table

    trailmgmt_setup_visit_table();
    trailmgmt_setup_site_table();
 
}
register_activation_hook( __FILE__, 'trailmgmt_install' );


function trailmgmt_deactivate_site_table(){
	global $wpdb;
	//Insert code here to remove the trail site table
	$table_name = $wpdb->prefix . $sitedatatable;
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "DROP TABLE $table_name;";
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta ($sql);
}

function trailmgmt_deactivation() {
    // trigger our function that registers the custom post type
    trailmgmt_deactivate_site_table();
 
}
register_deactivation_hook( __FILE__, 'trailmgmt_deactivation' );


function trailmgmt_get_data() {

	if(!isset($_POST['dbrequest'])) {
		echo json_encode(array('ncbt_data_success'=>'response_missing'));
		wp_die();

	} else {

	}

}



//Retrieves site data from database
add_action( 'wp_ajax_get_trailmgmt_data', 'get_trailmgmt_data' );
add_action( 'wp_ajax_nopriv_get_trailmgmt_data', 'get_trailmgmt_data' );

function get_trailmgmt_data() {
	//==================================================================
	//TODO
	// - test data retrieval from front end pages
	// - test visit table write

	if(!isset($_POST['dbrequest'])) {
		echo json_encode(array('ncbt_data_success'=>'response_missing'));
		wp_die();

	} else {

		$request = strval( $_POST['dbrequest'] );

		$sitedatatable = "trailmgmt_sites";
		$visitdatatable = 'trailmgmt_visits';
		global $wpdb;

		// get db login information
		include('trailmgmt_db_info.php');
		//connect to NCBT Site Data
		$dbname = "ncbirdin_a60";

		// see examples of db connection here: https://codex.wordpress.org/Class_Reference/wpdb

	    switch ($request) {
	    	case "site_list":

				//======================================================================
				//The following is working code that downloads the most recent trail site data
				//TODO: Rewrite this function to be included in the functions.php get_ncbt_data() function (remove duplication)
				//POTENTIAL FUTURE - move this to a CRON JOB that runs each night and creates static js file (if it speeds loading)
				//======================================================================

	   			global $wpdb;

    			$table_name = $wpdb->prefix . $sitedatatable;
    			
    			$results = $wpdb->get_results(
    				"
    				SELECT siteslug, groupslug, category, title, id, lat, lon
    				FROM " . $table_name . "
    				"
    			);
    			echo json_encode($results); //return results

				wp_die(); //close DB connection
	    		break;

	    	case "test":
	    		//troubleshooting issue...
	    		echo "{response:successful test!}";

				wp_die(); //close DB connection
	    		break;

	    	case "site_detail":
				//==============================================================================================
				//The following is working code that downloads the most recent complete site data for passed id
				//==============================================================================================

	   			global $wpdb;

    			$table_name = $wpdb->prefix . $sitedatatable;
				$id = strval( $_POST['siteslug']); //for some reason, produces error when tag is 'siteslug' - WTF?
				$sql = 'SELECT * FROM ' . $table_name . ' WHERE siteslug = "' . $id . '" LIMIT 1'; //will only return one record

    			
    			$results = $wpdb->get_row($sql);
    			echo json_encode($results); //return results

				wp_die(); //close DB connection
	    		break;

			case "update_site_data": 
				//==============================================================================================
				//UPDATE SITE DATA FROM PASSED PARAMETERS
				//for passed id

    			global $wpdb;

    			$table_name = $wpdb->prefix . $sitedatatable;
    			
    			$wpdb->update(
    				$table_name,
    				array(
			 			'title' => $_POST['title'],
						'siteslug' => $_POST['siteslug'],
						'category' => $_POST['category'],
						'directions' => $_POST['directions'],
						'description' => $_POST['description'],
						'species' => $_POST['species'],
						'extwebsite' => $_POST['extwebsite'],
						'groupslug' => $_POST['groupslug'],
						'habitats' => $_POST['habitats'],
						'lat' => $_POST['lat'],
						'lon' => $_POST['lon'],
						'boataccess' => $_POST['boataccess'],
						'fee' => $_POST['fee'],
						'picnic' => $_POST['picnic'],
						'hiking' => $_POST['hiking'],
						'trailmaps' => $_POST['trailmaps'],
						'camping' => $_POST['camping'],
						'visitor' => $_POST['visitor'],
						'hunting' => $_POST['hunting'],
						'restrooms' => $_POST['restrooms'],
						'handicap' => $_POST['handicap'],
						'viewing' => $_POST['viewing'],
						'boatlaunch' => $_POST['boatlaunch'],
						'interpretive' => $_POST['interpretive'],
						'placeid' => $_POST['placeid'],
						'locid' => $_POST['locid'],
						'what3words' => $_POST['what3words']
    				),
    				array ( 'siteslug' => $_POST['siteslug'])
    			);
				

    			break; //end switch code execution

    		case "update_site_field":
				//==============================================================================================
    			// UPDATE ONE FIELD FROM PASSED DATA

    			global $wpdb;

    			$table_name = $wpdb->prefix . $sitedatatable;
    			
    			$field = strval( $_POST['field']);
	    		$data = strval( $_POST['data']);
	    		$siteslug = strval( $_POST['slug']);

				$results = $wpdb->update(
					$table_name,
					array($field => $data),
					array('siteslug' => $siteslug)
				);	    		

				echo json_encode($results);

    			break;

    		case "create_new_site":
				//==============================================================================================
    			// CREATE NEW SITE RECORD FROM PASSED DATA

    			global $wpdb;

    			$table_name = $wpdb->prefix . $sitedatatable;
    			
    			$wpdb->insert(
    				$table_name,
    				array(
			 			'title' => $_POST['title'],
						'siteslug' => $_POST['siteslug'],
						'category' => $_POST['category'],
						'directions' => $_POST['directions'],
						'description' => $_POST['description'],
						'species' => $_POST['species'],
						'extwebsite' => $_POST['extwebsite'],
						'groupslug' => $_POST['groupslug'],
						'habitats' => $_POST['habitats'],
						'lat' => $_POST['lat'],
						'lon' => $_POST['lon'],
						'boataccess' => $_POST['boataccess'],
						'fee' => $_POST['fee'],
						'picnic' => $_POST['picnic'],
						'hiking' => $_POST['hiking'],
						'trailmaps' => $_POST['trailmaps'],
						'camping' => $_POST['camping'],
						'visitor' => $_POST['visitor'],
						'hunting' => $_POST['hunting'],
						'restrooms' => $_POST['restrooms'],
						'handicap' => $_POST['handicap'],
						'viewing' => $_POST['viewing'],
						'boatlaunch' => $_POST['boatlaunch'],
						'interpretive' => $_POST['interpretive'],
						'placeid' => $_POST['placeid'],
						'locid' => $_POST['locid'],
						'what3words' => $_POST['what3words']
    				)
    			);

				$results = $wpdb->insert_id;
    			echo $results;

			case "delete_site"://deletes a site
				//==============================================================================================
				//DELETE SITE RECORD FROM PASSED id field
			
    			$table_name = $wpdb->prefix . $sitedatatable;
    			$id = json_decode($_POST['id']);

    			$results = $wpdb->delete(
    				$table_name,
    				array(
    					'id' => $id
    				)
    			);

    			echo $results;
				break; //end switch code evaluation

			case "upload_data_file": //populate bulk data from a text file
				//==============================================================================================
				//TO BE DEVELOPED
				// ~ delimited?
				// HOW TO AUTOMATE THIS?

				break;

			case "download_data_file":
				//==============================================================================================
				//download bulk data to a text file
				//TO BE DEVELOPED
				// ~ delimited?
				// HOW TO AUTOMATE THIS?

				break;

			case "get_visits":
				//==============================================================================================
				//retrieve visit list for mapping
				//TODO
				//	- Add ability to filter results
				//	- place restrictions on the number of records to return? (what is prohibitive?)

				global $wpdb;

    			$table_name = $wpdb->prefix . $visitdatatable;
    			
    			$results = $wpdb->get_results(
    				"
    				SELECT 	ID, DTTM, NCBTUSERID, PLATFORM, BROWSER, LAT, LON
    				FROM " . $table_name . "
    				ORDER BY DTTM DESC"
    			);
    			echo json_encode($results); //return results

				wp_die(); //close DB connection
	


				break;

    		case "log_visit": //post website visit data
    			//NOT WORKING RIGHT NOW, need to create table on plugin installation
    			global $wpdb;

    			$table_name = $wpdb->prefix . $visitdatatable;
    			
    			$wpdb->insert(
    				$table_name,
    				array(
			 			'PLATFORM' => strval($_POST['platform']),
			 			'BROWSER' => strval($_POST['browser']),
			 			'NCBTUSERID' => strval($_POST['ncbtuserid']),
			 			'LAT' => doubleval($_POST['lat']),
			 			'LON' => doubleval($_POST['lon'])
			 		)
    			);

				wp_die(); //close db connection
				break; //end switch code evaluation

    		default:
    			echo "no data";


    			break;

		
		}

	}
}
?>
