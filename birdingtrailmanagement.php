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

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

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
	$function = trail_management_menu();

	//top menu
	add_menu_page( $page_title, $menu_title, $capability, $menu_slug, trail_management_menu(), '', 7 );

	//sub-menu (setup)
	//plugin function, reference theme to use
	//import functions
	//descriptions (fields in each table, etc.)


	//sub-menu (trail sites)
	// forms for data entry, table of existing sites


	//sub-menu (businesses)


	//sub-menu (visits)
}


function trail_management_menu() {
 global $title;
    ?>
        <h2><?php echo $title;?></h2>
        My New Menu Page!!
        <?php

}



/* ====================================================================
* Creates table(s) to store stie data, visit data, and business data
* 
*/

//EXAMPLE From https://codex.wordpress.org/Creating_Tables_with_Plugins
/*
global $jal_db_version;
$jal_db_version = '1.0';

function jal_install() {
	global $wpdb;
	global $jal_db_version;

	$table_name = $wpdb->prefix . 'liveshoutbox';
	
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		name tinytext NOT NULL,
		text text NOT NULL,
		url varchar(55) DEFAULT '' NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	add_option( 'jal_db_version', $jal_db_version );
}

function jal_install_data() {
	global $wpdb;
	
	$welcome_name = 'Mr. WordPress';
	$welcome_text = 'Congratulations, you just completed the installation!';
	
	$table_name = $wpdb->prefix . 'liveshoutbox';
	
	$wpdb->insert( 
		$table_name, 
		array( 
			'time' => current_time( 'mysql' ), 
			'name' => $welcome_name, 
			'text' => $welcome_text, 
		) 
	);
}

*/

/* ====================================================================
* Retrieves detail for a single site, identified by the passed site_slug
* (the name of the site with spaces replaced by -)
*/



//echo "get-ncbt-data";
if (isset($_POST['siteslug'])) {
    $slug = $_POST['siteslug'];    
    $sql = 'SELECT * FROM site_data WHERE SITESLUG = "' . $slug . '" LIMIT 1'; //will only return one record
    $result = $ncbt_conn->query($sql);

    if($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo json_encode($row);
        }
    }
}
/* elseif (isset($_POST['aou'])) { //retrieves siteslugs where birds likely
    //NEEDS TESTING
    $aou = $_POST['aou'];    
    $sql = 'SELECT * FROM sitebirdlist WHERE AOU = "' . $aou .'"'; //will only return one record
    $result = $ncbt_conn->query($sql);

    if($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo json_encode($row);
        }
    }

} elseif (isset($_POST['sitebirds']) { //retrieves birds list for passed siteslug
    //NEEDS WORK
    $slug = $_POST['siteslug'];    
    $sql = 'SELECT * FROM site_data WHERE SITESLUG = "' . $slug . '" LIMIT 1'; //will only return one record
    $result = $ncbt_conn->query($sql);

    if($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo json_encode($row);
        }
    }

}
*/



/* ====================================================================
* Retrieves data for the map (site slug, site name, etc.)
* NOT WORKING
*/
 /*
function retrieve_map_data(){



	if(!isset($_POST['response'])) {
		echo json_encode(array('ncbt_data_success'=>'response_missing'));
		wp_die();
	} else {
		//Connect to database, get site data
		// FUTURE - avoid hard-coding this, provide plug in interface to set these parameters

	    $servername = "localhost";
	    $username = "ncbirdin_ncbtweb";
	    $password = "9%VI&p&Yo844";
	    $dbname = "ncbirdin_ncbt_data";
	    //$ncbt_conn = new mysqli($servername, $username, $password, $dbname);
		
		$response_safe = filter_var($_POST['response'], FILTER_SANITIZE_STRING);
		if($response_safe=='ncbt') {
			//connect to NCBT Site Data
			//$dbname = "ncbirdin_ncbt_data";
			$conn = new mysqli($servername, $username, $password, $dbname);
			
			$sql = "SELECT * FROM site_data";
			//$result = $conn->query($sql);
			$result = $conn->query($sql);

			//echo json_encode($result->fetch_assoc());
						
			//loop through resulting records
			$rows = array();
			if($result->num_rows > 0) {
				while($r = $result->fetch_assoc()) {
					$rows[] = array('SITESLUG'=>$r['SITESLUG'], 'TITLE'=>$r['TITLE'],'LAT'=>$r['LAT'],'LON'=>$r['LON']);
				};
			};
			
			echo json_encode($rows);
			wp_die();
		} else if ($response_safe=='bfb') {
			echo json_encode(array('ncbt_data_success'=>'bfb_response'));
			wp_die();
		} else if ($response_safe=='ncbtsite') {
			//retrieve one site's data
			//add code here to make this happen!
			echo json_encode(array('ncbt_data_success'=>'bfb_response'));
			wp_die();
		} else {
			echo json_encode(array('ncbt_data_success'=>'no_response'));
			wp_die();
		}
	}
}

}
*/
?>