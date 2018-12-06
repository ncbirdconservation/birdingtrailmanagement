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

function trailmgmt_load_scripts ($hook) {
	if ($hook != 'sites.php') { return;}
/*
	wp_register_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');
	wp_enqueue_style('font-awesome');
*/
	wp_register_style('trailmgmt', dirname( __FILE__ ) . '/css/trailmgmt.css');
	wp_enqueue_style('trailmgmt');
/*
	wp_enqueue_style('bootstrap', dirname( __FILE__ ) . '/bootstrap/css/bootstrap.css');
	wp_enqueue_style('parent-style', dirname( __FILE__ )  . '/style.css',array('bootstrap'));
	wp_enqueue_script( 'bootstrap-js', dirname( __FILE__ )  . '/bootstrap/js/bootstrap.min.js', array( 'jquery' ), '4.0.0', false );
*/	
};

add_action( 'admin_enqueue_scripts', 'trailmgmt_load_scripts' );
/*
add_action( 'wp_enqueue_scripts', 'trailmgmt_load_scripts' );
*/

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


	//sub-menu (visits)
	$submenu_visits_slug = 'visits';
	add_submenu_page($menu_slug,$submenu_visits_slug,$capability,$menu_slug);
	*/

}

//Adds ajaxurl variable to JS on pages, used in ajax calls
/* NOT NEEDED? Already loaded in functions.php?
add_action('wp_head','pluginname_ajaxurl');
function pluginname_ajaxurl() {
	?>
	<script type="text/javascript">
		var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
	</script>
	<?php
}
*/

function trailmgmt_menu() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	echo '<div class="wrap">';
	echo '<p>Here is where the form would go if I actually had options.</p>';
	echo '</div>';

}

/* SITES MENU */
/*
function trailmgmt_sites_menu() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	//start HTML for site menu
	?>
	<div class="wrap">
		<ul class="list-group">
		  <li class="list-group-item" style="width:100%;border:">testing again</li>
		  <li class="list-group-item">test site 3</li>
		  <li class="list-group-item">Morbi leo risus</li>
		  <li class="list-group-item">Porta ac consectetur ac</li>
		  <li class="list-group-item">Vestibulum at eros</li>
		</ul>		
	</div>

	<?php //end HTML for form, back to php 
	
}
*/

/* ====================================================================
* Plugin Activation
* Creates table(s) to store site data, visit data, and business data
*/

/* VERSIONING - good idea, but disabled for debugging
global $trailmgmt_db_version;
$trailmgmt_db_version = '0.1';
*/
function trailmgmt_setup_site_table(){
	//Insert code here to set up the trail site table

	global $wpdb;
	global $trailmgmt_db_version;

	$table_name = $wpdb->prefix . $sitedatatable;
	$charset_collate = $wpdb->get_charset_collate();

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

	//add_option( 'trailmgmt_db_version', $trailmgmt_db_version ); //see comment above


}

function trailmgmt_install() {
    // trigger our function that registers the custom post type
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

	if(!isset($_POST['dbrequest'])) {
		echo json_encode(array('ncbt_data_success'=>'response_missing'));
		wp_die();

	} else {

		$request = strval( $_POST['dbrequest'] );

		$sitedatatable = "trailmgmt_sites";
		global $wpdb;

		// get db login information
		include('trailmgmt_db_info.php');
		//connect to NCBT Site Data
		$dbname = "ncbirdin_a60";

		// see examples of db connection here: https://codex.wordpress.org/Class_Reference/wpdb

	    switch ($request) {
	    	case "site_list":

				//======================================================================
				//The following is working code that downloads the most recent ncbt data
				//TODO: Rewrite this function to be included in the functions.php get_ncbt_data() function (remove duplication)
				//POTENTIAL FUTURE - move this to a CRON JOB that runs each night and creates static js file (if it speeds loading)
				//======================================================================

	   			global $wpdb;

    			$table_name = $wpdb->prefix . $sitedatatable;
    			
    			$results = $wpdb->get_results(
    				"
    				SELECT siteslug, title
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
				//======================================================================
				//The following is working code that downloads the most recent site data
				//POTENTIAL FUTURE - move this to a CRON JOB that runs each night and creates static js file (if it speeds loading)
				//======================================================================

	   			global $wpdb;

    			$table_name = $wpdb->prefix . $sitedatatable;
				$siteslug = strval( $_POST['slug']); //for some reason, produces error when tag is 'siteslug' - WTF?
				$sql = 'SELECT * FROM ' . $table_name . ' WHERE SITESLUG = "' . $siteslug . '" LIMIT 1'; //will only return one record

    			
    			$results = $wpdb->get_row($sql);
    			echo json_encode($results); //return results

				wp_die(); //close DB connection
	    		break;

			case "update_site_data": //UPDATE DATA FROM PASSED PARAMETERS
    			global $wpdb;

    			$table_name = $wpdb->prefix . $sitedatatable;
				
				$data = json_decode($_POST['data']);

    			$wpdb->update(
    				$table_name,
    				array(

    				)
    			);

    			break; //end switch code execution

    		case "create_new_site": //create a new site - TESTING - NEEDS WORK
    			global $wpdb;

    			$table_name = $wpdb->prefix . $sitedatatable;

    			$wpdb->insert(
    				$table_name,
    				array(
    					'title' => 'test site',
    					'siteslug' => 'test-site',
    					'category' => 'Central Blue Ridge Parkway',
    					'directions' => 'find us here!'
    				)
    			);



				break; //end switch code evaluation

			case "delete_site"://deletes a site
				//TO BE DEVELOPED
				break;

			case "upload_data_file": //populate data from a text file
				//TO BE DEVELOPED
				// ~ delimited?

				break;

			case "retrieve_data_fields": //return fields only
				//TO BE DEVELOPED - return first record in table
	   			global $wpdb;

    			$table_name = $wpdb->prefix . $sitedatatable;
    			
    			$results = $wpdb->get_results(
    				"
    				SELECT *
    				FROM " . $table_name . "
    				LIMIT 1"
    				
    			);
    			echo json_encode($results); //return results

				wp_die(); //close DB connection
	    		break;

    		case "log_visit": //post website visit data
    			//NOT WORKING RIGHT NOW, need to create table on plugin installation
				$conn = new mysqli($servername, $username, $password, $dbname);
				
				//data passed from successful geolocation
	    		$platform = strval( $_POST['platform']);
	    		$browser = strval( $_POST['browser']);
	    		$userid = strval( $_POST['ncbtuserid']);
	    		$lat = doubleval( $_POST['lat']);
	    		$lon = doubleval( $_POST['lon']);


				//$sql = "INSERT INTO visits (PLATFORM, LAT, LON) VALUES ('test',35,85)"; //TESTING
				$sql = "INSERT INTO visits (PLATFORM, BROWSER, NCBTUSERID, LAT, LON) VALUES ('" . $platform . "', '" . $browser . "','" . $userid . "'," . $lat . "," . $lon . ")"; //post data

				if ($conn->query($sql) === TRUE) {
					echo "New record created successfully";
				} else {
				    echo "Error: " . $sql . "<br>" . $conn->error;
				}
				$conn->close();
				wp_die(); //close db connection
				break; //end switch code evaluation
    		default:
    			echo "no data";


    			break;

		
		}

	}
}
?>
