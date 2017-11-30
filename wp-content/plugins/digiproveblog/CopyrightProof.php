<?php
/*
Plugin Name: Copyright Proof
Plugin URI: https://www.digiprove.com/copyright_proof_wordpress_plugin.aspx
Description: Digitally certify your posts to prove copyright ownership, generate copyright notice and license statement, copy-protect text and images, and monitor/log/alert attempted content theft.  Digiprove certifications are verifiable.
Version: 3.05
Author: Digiprove
Author URI: https://www.digiprove.com/
License: GPL
*/
/*  Copyright 2008-2014  Digiprove (email : cian.kinsella@digiprove.com)
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

// Acknowledgement to Honza Odvarko, whose jscolor color-picker is used in this plug-in 
// under the GNU Lesser General Public License (LGPL): www.gnu.org/copyleft/lesser.html

// Acknowledgements to BTE, Akismet, and After the Deadline, some of whose code was used
// in the development of this plug-in
	// Declare and initialise global variables:
	
    define("DPRV_VERSION", "3.05");
	define("DPRV_WWW", "www.digiprove.com");

    include_once('copyright_proof_admin.php');						// Functions for Settings panel
	include_once('copyright_proof_edit.php');						// Functions for Creating and Editing content
	include_once('copyright_proof_live.php');						// Functions for Serving pages on web
	if (intval(substr(PHP_VERSION,0,1)) > 4)
	{
		include_once('copyright_proof_error.php');					// Functions for handling errors
	}
	include_once('Digiprove.php');									// Digiprove SDK functions
	include_once('copyright_proof_integrity.php');					// Functions for Verification


	global $dprv_licenseIds, $dprv_licenseTypes, $dprv_licenseCaptions, $dprv_licenseAbstracts, $dprv_licenseURLs, $dprv_post_id, $dprv_mime_types, $dprv_blog_host, $dprv_wp_host, $dprv_last_error;

	$dprv_blog_url = parse_url(get_option('home'));
	$dprv_blog_host = trim($dprv_blog_url['host']);
	$dprv_wp_url = parse_url(get_option('siteurl'));
	$dprv_wp_host = trim($dprv_wp_url['host']);
	if ($dprv_blog_host == "")
	{
		$dprv_blog_host = $dprv_wp_host;
	}

	// Define arrays of license information - populated in header sections
	$dprv_licenseIds = array();
	$dprv_licenseTypes = array();
	$dprv_licenseCaptions = array();
	$dprv_licenseAbstracts = array();
	$dprv_licenseURLs = array();

	$dprv_mime_types = array(	"All"=>array("All"),
								"Images"=>array("bmp","cgm","djv","djvu","gif","ico","ief","j2","jpe","jpeg","jpg","mac","pbm","pct","pgm","pic","pict","png","pnm","pnt","pntg","ppm","qti","qtif","ras","rgb","svg","tif","tiff","wbmp","xpm"),
								"Audio"=>array("aif","aifc","aiff","au","kar","m3u","m4a","m4b","m4p","mid","midi","mp2","mp3","mpga","mxu","ra","ram","wav"),
								"Video"=>array("avi","dif","dv","m4u","m4v","mov","movie","mp4","mpe","mpeg","mpg","qt","swf"),
								"Web Pages"=>array("","htm","html","php","asp","aspx"),
								"Documents"=>array("csv","doc","docx","odt","ods","odp","pdf","ppt","pptx","rtf","txt","xls","xlsx"),
								"Code"=>array("js","jar"));

	// Register hooks
	register_activation_hook(__FILE__, 'dprv_activate');		//  Do on Activation
	register_deactivation_hook(__FILE__, 'dprv_deactivate');	//  Do on de-activation
	add_action('activated_plugin','dprv_activation_error');		//  Do on error in activation
	add_action('init', 'dprv_init');							//  Every start-up
	add_action('delete_post', 'dprv_post_sync', 10);			//	On delete post

	add_action('admin_menu', 'dprv_settings_menu');				
	add_action('admin_head', 'dprv_admin_head');
	add_action('admin_footer', 'dprv_admin_footer');
	add_action('admin_enqueue_scripts', 'dprv_admin_enqueue_scripts');

	add_action('post_submitbox_start', 'dprv_add_digiprove_submit_button');
	add_filter('wp_insert_post_data', 'dprv_parse_post', 99, 2);
	add_filter('wp_insert_page_data', 'dprv_parse_post', 99, 2);
	add_action('wp_insert_post', 'dprv_digiprove_post');
	add_action('wp_insert_page', 'dprv_digiprove_post');
	add_filter('plugin_action_links', 'dprv_add_settings_link', 10, 2 );

	if (get_option('dprv_enrolled') == "Yes")
	{
		global $wp_version;
		if (intval($wp_version)>3)
		{
			add_action('add_meta_boxes', 'dprv_postbox');
		}
		else
		{
			add_action( 'admin_init', 'dprv_postbox', 1);
		}
	}
	add_action("wp_head", "dprv_head");
	add_filter("the_content", "dprv_display_content" );
	add_action("wp_footer", "dprv_footer" );
	add_action('wp_ajax_dprv_verify_revision', 'dprv_verify_revision_callback');

    
    //REMOVE TEST CODE BELOW
    function return_filters_for( $hook = '' )
{
    global $wp_filter;
    if( empty( $hook ) || !isset( $wp_filter[$hook] ) )
        return "";

    return '<pre>' . print_r($wp_filter[$hook], true) . '</pre>';
}


	// Hooks for Integrity checking:
	// TODO - Reinstate these hooks when implementing integrity checking
	// add_action('post_submitbox_misc_actions', 'dprv_verify_box');			// Displays verify info on edit screen
	// add_action('wp_ajax_dprv_verify', 'dprv_verify_callback');				// 
	// add_action('wp_ajax_nopriv_dprv_verify', 'dprv_verify_callback' );		// as above but for not-logged-in users

	if(!function_exists("strripos"))
	{
		function strripos($haystack, $needle, $offset = 0)
		{
			if(!is_string($needle))
			{
				$needle = chr(intval($needle));
			}
			if ($offset < 0)
			{
				$temp_cut = strrev(substr($haystack, 0, abs($offset)));
			}
			else
			{
				$temp_cut = strrev(substr($haystack, 0, max((strlen($haystack) - $offset),0)));
			}
			if(($found = stripos( $temp_cut, strrev($needle))) === FALSE)return FALSE;
			$pos = (strlen($haystack) - ($found + $offset + strlen( $needle)));
			return $pos;
		}
	}


	if (!function_exists("stripos"))
	{
		function stripos($haystack, $needle, $offset=0)
		{
			return strpos(strtoupper($haystack), strtoupper($needle), $offset);
		}
	}

	if (!function_exists("strpbrk"))
	{
		function strpbrk($haystack, $char_list)
		{
			$strlen = strlen($char_list);
			$found = false;
			for($i=0; $i<$strlen; $i++)
			{
				if(($tmp = strpos($haystack, $char_list{$i})) !== false )
				{
					if(!$found) 
					{
						$pos = $tmp;
						$found = true;
						continue;
					}
					$pos = min($pos, $tmp);
				}
			}
			if(!$found)
			{
				return false;
			}
			return substr($haystack, $pos);
		}
	}


	// FROM HERE DOWN IS ALL FUNCTIONS:

	// ON-ACTIVATION FUNCTIONS:
	function dprv_activation_error($plugin_name)
	{
		if (strpos($plugin_name, "CopyrightProof") !== false)
		{
			$dprv_last_error = trim(ob_get_contents());
			if ($dprv_last_error != "")
			{
				$dprv_last_error = "Error on activation: " . $dprv_last_error;
				dprv_record_event($dprv_last_error);

			}
		}
	}
	function dprv_activate()
	{
		// TODO: set error handler to dprvErrors if in existence for this function
		$log = new DPLog();  
		$log->lwrite("");
		$log->lwrite("VERSION " . DPRV_VERSION . " ACTIVATED");
		update_option('dprv_activated_version', DPRV_VERSION);	// If different to installed, activation steps will take place
		//add_option('dprv_verified_db_version', '');	            // If not up to date, will be checked and updated if necessary
		// To preserve consistency of behaviour, if this is an upgrade installation but dprv_auto_posts did not exist before set it to yes
		if (get_option('dprv_user_id') !== false && get_option('dprv_auto_posts') === false)
		{
			add_option('dprv_auto_posts', 'Yes');
		}
		else						// Otherwise if not already set, set it to the default value we want (No)
		{
			add_option('dprv_auto_posts', 'No');
		}
		if (get_option('dprv_user_id') === false)
		{
			// New installation of plugin
			add_option('dprv_registration_status', 'Not registered');
		}
		else
		{
			// Previously existing installation, if option doesn't exist, add it
			add_option('dprv_registration_status', 'Unknown');
		}
		add_option('dprv_email_address', '');
		add_option('dprv_subscription_type', '');               // Will be empty until activation of membership
		add_option('dprv_subscription_expiry', '');
		add_option('dprv_content_type', '');
		add_option('dprv_notice', '');
		add_option('dprv_c_notice', 'DisplayAll');
		add_option('dprv_submitter_is_author', 'No');
		add_option('dprv_submitter_has_copyright', 'No');
		add_option('dprv_notice_size', '');
		add_option('dprv_license', '0');
		add_option('dprv_frustrate_copy', '');
		add_option('dprv_right_click_message', '');
		add_option('dprv_record_IP', 'off');                       // Not used (yet)
		add_option('dprv_notice_border', '');
		add_option('dprv_notice_background', '');
		add_option('dprv_notice_color', '');
		add_option('dprv_hover_color', '');
		add_option('dprv_obscure_url','Obscure');
		add_option('dprv_display_name','Yes');
		add_option('dprv_email_certs','No');
		add_option('dprv_linkback','Nolink');
		add_option('dprv_save_content','Nosave');
		add_option('dprv_post_types','post,page');
		$subscription_type = get_option('dprv_subscription_type');
		$dprv_html_tags = dprv_set_default_html_tags();
		if ($subscription_type == 'Basic' || $subscription_type == '')
		{
			foreach ($dprv_html_tags as $key=>$value)
			{
				$dprv_html_tags[$key]["selected"] = "False";
			}
		}
		add_option('dprv_html_tags',$dprv_html_tags);
		add_option('dprv_outside_media','NoOutside');
		delete_option('dprv_body_or_footer');		// Not used, discard
		add_option('dprv_footer', 'No');
		add_option('dprv_multi_post', 'No');
		add_option('dprv_enrolled', 'No');                  // Is User registered with Digiprove?  
		add_option('dprv_user_id', '');
		add_option('dprv_api_key', '');
		add_option('dprv_last_action', '');                 // Is set to "Digiprove id=nnnn" at start of a Digiprove action (parse_post) if this is blank dprv_last_result will not be displayed as an admin message
		add_option('dprv_last_result', '');			        // Contains result of Digiprove action
		add_option('dprv_pending_message', '');		        // A message which has yet to be displayed in message box (usually same as dprv_last_result)
		add_option('dprv_last_date','');
		add_option('dprv_last_date_count','0');
        add_option('dprv_back_digiproved_count', '0');      // How many posts/pages have already been back-digiproved
		add_option('dprv_event', '');				        // Place for error messages to be recorded, may be notified via API eventually
		add_option('dprv_html_integrity', 'No');	        // Whether to check HTML data integrity
		add_option('dprv_files_integrity', 'No');	        // Whether to check data integrity of embedded files
		delete_option('dprv_activation_event');		        // No longer required
		add_option('dprv_prefix');
		add_option('dprv_featured_images', 'No');
		create_dprv_license_table();
		create_dprv_post_table();
		create_dprv_post_content_files_table();
		create_dprv_log_table();
	}

	function dprv_set_default_html_tags()
	{
		$dprv_html_tags=array(	"a"=>array("name"=>"Anchor","selected"=>"True","incl_excl"=>"Include", "All"=>"False", "Images"=>"False", "Audio"=>"True", "Video"=>"True", "Web Pages"=>"False", "Documents"=>"False", "Code"=>"False"),
								"img"=>array("name"=>"Images","selected"=>"True","incl_excl"=>"Include", "All"=>"True", "Images"=>"False", "Audio"=>"False", "Video"=>"False", "Web Pages"=>"False", "Documents"=>"False", "Code"=>"False"), 
								"embed"=>array("name"=>"Embedded Media","selected"=>"False","incl_excl"=>"Include", "All"=>"False", "Images"=>"False", "Audio"=>"False", "Video"=>"True", "Web Pages"=>"False", "Documents"=>"False", "Code"=>"False"),
								"applet"=>array("name"=>"Java Applets","selected"=>"False","incl_excl"=>"Include", "All"=>"False", "Images"=>"False", "Audio"=>"False", "Video"=>"False", "Web Pages"=>"False", "Documents"=>"False", "Code"=>"True"), 
								"object"=>array("name"=>"Objects (various)","selected"=>"False","incl_excl"=>"Include", "All"=>"False", "Images"=>"False", "Audio"=>"False", "Video"=>"True", "Web Pages"=>"False", "Documents"=>"False", "Code"=>"True"), 
								"iframe"=>array("name"=>"iFrame","selected"=>"False","incl_excl"=>"Exclude", "All"=>"False","Images"=>"False", "Audio"=>"False", "Video"=>"True", "Web Pages"=>"True", "Documents"=>"False", "Code"=>"True"), 
								"script"=>array("name"=>"Scripts & Code","selected"=>"False","incl_excl"=>"Include", "All"=>"False","Images"=>"False", "Audio"=>"False", "Video"=>"False", "Web Pages"=>"False", "Documents"=>"False", "Code"=>"True"),
								"notag"=>array("name"=>"Elsewhere in HTML","selected"=>"True","incl_excl"=>"Include", "All"=>"False","Images"=>"True", "Audio"=>"True", "Video"=>"False", "Web Pages"=>"False", "Documents"=>"False", "Code"=>"False"));
		return $dprv_html_tags;
	}

	function create_dprv_license_table()
	{
		$log = new DPLog();  
		global $wpdb;

		// First, investigate and set dprv_prefix to allow for various situations such as user changes wp_prefix, dbDelta used to ignore caps in prefix
		$dprv_prefix = get_option('dprv_prefix');
		$dprv_licenses = $dprv_prefix . "dprv_licenses";
		
		if ($dprv_prefix === false)
		{
			// First-time activation of this plugin
			$dprv_prefix = $wpdb->prefix;
			update_option('dprv_prefix', $dprv_prefix);
		}
		else
		{
			$like_dprv_licenses = dprv_wpdb("get_var", "show tables like '$dprv_licenses'");
			$log->lwrite("like_dprv_licenses=$like_dprv_licenses");
			if ($like_dprv_licenses != $dprv_licenses)
			{
				$dprv_licenses = strtolower($dprv_licenses);
				$like_dprv_licenses = dprv_wpdb("get_var", "show tables like '$dprv_licenses'");
				if ($like_dprv_licenses == $dprv_licenses)
				{
					// Due to pre 3.4 db Delta bug, table name was created with all lower case prefix
					$dprv_prefix = strtolower($dprv_prefix);
					$log->lwrite("just set dprv_prefix to $dprv_prefix (lower case)");
				}
				else
				{
					$dprv_prefix = $wpdb->prefix;
					$log->lwrite("just set dprv_prefix to $dprv_prefix");
				}
				update_option('dprv_prefix', $dprv_prefix);
			}
		}

		// Now check to see if the table exists already, if not, then create it
		$dprv_licenses = $dprv_prefix . "dprv_licenses";
		if(dprv_wpdb("get_var", "show tables like '$dprv_licenses'") != $dprv_licenses)
		{
			$log->lwrite("creating license table " . $dprv_licenses);  
			$sql = "CREATE TABLE " . $dprv_licenses . " (
					id mediumint(9) NOT NULL AUTO_INCREMENT,
					license_type varchar(50) NOT NULL,
					license_caption varchar (40) NOT NULL,
					license_abstract text(1000),
					license_url varchar(255),
					UNIQUE KEY id (id)
					);";

			//We need to include this file so we have access to the dbDelta function below (which is used to create the table)
			//require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

			dbDelta($sql);
			//$dprv_sql_error = mysql_error();				
			$dprv_db_error = $wpdb->last_error;
			$log->lwrite("just created (if necessary) license table " . $dprv_licenses . "; error text = " . $dprv_db_error);

			$like_dprv_licenses = dprv_wpdb("get_var", "show tables like '$dprv_licenses'");
			$log->lwrite("like_dprv_licenses=$like_dprv_licenses");
			if ($like_dprv_licenses != $dprv_licenses)
			{
				// Check if created at lower case (would be if pre 3.4 version of WP)
				$like_dprv_licenses = dprv_wpdb("get_var", "show tables like '" . strtolower($dprv_licenses) . "'");
				if ($like_dprv_licenses != strtolower($dprv_licenses))
				{
					$message = "Failure to create table " . $dprv_licenses;
					if (trim($dprv_db_error) != "")
					{
						$message .= " with error " .  $dprv_db_error;
					}
					//if (trim($dprv_sql_error) != "")
					//{
					//	$message .= " sql error " .  $dprv_sql_error;
					//}
					$temp = sprintf(__("Failed to create db table %s"), $dprv_licenses);
					if (trim($dprv_db_error) != "")
					{
						$temp .= " <a href='javascript:alert(\"" . __("Details:") . " $dprv_db_error" . "\")'>More</a>";
					}
					$log->lwrite($temp);
					update_option("dprv_pending_message", $temp); 
					dprv_record_event($message);
					return false;
				}
				else
				{
					// Looks like dbDelta just created table using lower-case version of prefix, modify dprv_prefix accordingly
					$dprv_prefix = strtolower($dprv_prefix);
					$log->lwrite("dbDelta converted prefix to lower case, just set dprv_prefix to $dprv_prefix");
					update_option('dprv_prefix', $dprv_prefix);
				}
			}
			record_dprv_licenses();
		}
		else
		{
			$log->lwrite("Table " . $dprv_licenses . " exists already");  
		}
		return true;
	}

	function create_dprv_post_table()
	{
		$log = new DPLog();  
		global $wpdb;
		$dprv_prefix = get_option('dprv_prefix');
		$dprv_posts = $dprv_prefix . "dprv_posts";
		$sql =	"CREATE TABLE " . $dprv_posts . " (
				id bigint(20) NOT NULL,
				digiprove_this_post bool NOT NULL,
				this_all_original bool NOT NULL,
				attributions text CHARACTER SET utf8 COLLATE utf8_general_ci,
				using_default_license bool NOT NULL,
				license varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci,
				custom_license_caption varchar(40) CHARACTER SET utf8 COLLATE utf8_general_ci,
				custom_license_abstract text CHARACTER SET utf8 COLLATE utf8_general_ci,
				custom_license_url varchar(255),
				certificate_id varchar(12),
				digital_fingerprint varchar(64),
				cert_utc_date_and_time varchar(40),
				certificate_url varchar(255),
				first_year smallint,
				last_time_digiproved int,
				last_fingerprint varchar(64),
				last_time_updated int,
				UNIQUE KEY id (id)
				);";

		//We need to include this file so we have access to the dbDelta function below (which is used to create the table)
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta($sql);

		// TODO: statements below may be pointless and do not capture dbDelta info.  Test to determine facts
		//$dprv_sql_error = mysql_error();				
		//$dprv_sql_info = mysql_info();
		$dprv_db_error = $wpdb->last_error;

		$log->lwrite ("Executed create table " . $dprv_posts . ", last_error " . $dprv_db_error);

		$like_dprv_posts = dprv_wpdb("get_var", "show tables like '$dprv_posts'");
		if ($like_dprv_posts !== false && $like_dprv_posts !== $dprv_posts)
		{
			//$message = "Failure to create table " . $dprv_posts . "!=(" . $like_dprv_posts . ") with error " .  $dprv_db_error . ", mysql_error " . $dprv_sql_error;
			$message = "Failure to create table " . $dprv_posts . "!=(" . $like_dprv_posts . ") with error " .  $dprv_db_error;
			dprv_record_event($message);
		}
		else
		{
			$like_last_fingerprint = dprv_wpdb("get_var","show columns from $dprv_posts LIKE 'last_fingerprint'");

			if ($like_last_fingerprint != 'last_fingerprint')														// Table existed, but dbDelta failed to add new columns
			{
				if ($like_last_fingerprint === false)
				{
					// There was an error 28 or incorrect key file error trying to find the column maybe it is there after all:-)
					$message = "dbDelta might not have added last_fingerprint column to " . $dprv_posts . ", check failed, ignore for now";
					dprv_record_event($message);
				}
				else
				{

					$message = "dbDelta did not add last_fingerprint column  (= " . $like_last_fingerprint . ") to " . $dprv_posts;
					if ($dprv_db_error != "")
					{
						$message .= " with error " .  $dprv_db_error;
					}
					//if ($dprv_sql_error != "")
					//{
					//	$message .= ", mysql_error " .  $dprv_sql_error;
					//}
					//if ($dprv_sql_info != "")
					//{
					//	$message .= ", mysql_info " .  $dprv_sql_info;
					//}
					
					$message .= ", will try adding with wpdb";
					dprv_record_event($message);
					// TODO: diagnose why adding a varchar column to an existing table defaults to latin but in Create table defaults to utf-8
					dprv_add_column($dprv_posts, 'last_time_digiproved', 'int');
					dprv_add_column($dprv_posts, 'last_fingerprint', 'varchar(64)');
					dprv_add_column($dprv_posts, 'last_time_updated', 'int');
	/*
					// Not yet implemented:
					$result1 = dprv_add_column($dprv_posts, 'last_time_digiproved', 'int');
					if (strpos($result1, "Duplicate column name") !== false)
					{
						update_option('dprv_verified_db_version', '2');
					}
					else
					{
						$result2 = dprv_add_column($dprv_posts, 'last_fingerprint', 'varchar(64)');
						$result3 = dprv_add_column($dprv_posts, 'last_time_updated', 'int');
						if ($result1 === true && $result2 === true && $result3 === true)
						{
							update_option('dprv_verified_db_version', '2');
						}
					}
	*/
					$like_last_fingerprint = dprv_wpdb("get_var","show columns from $dprv_posts like 'last_fingerprint'");
					if ($like_last_fingerprint != 'last_fingerprint')															// If still does not exist
					{
						$message = "Failed to add last_fingerprint column to " . $dprv_posts;
						dprv_record_event($message);
					}
				}
			}
		}
	}
	function create_dprv_post_content_files_table()
	{
		$log = new DPLog();
		global $wpdb;
		$dprv_prefix = get_option('dprv_prefix');
		$dprv_post_content_files = $dprv_prefix . "dprv_post_content_files";

		// Check to see if the table exists already, if not, then create it
		//if($wpdb->get_var("show tables like '$dprv_post_content_files'") != $dprv_post_content_files)
		if(dprv_wpdb("get_var", "show tables like '$dprv_post_content_files'") != $dprv_post_content_files)
		{
			$log->lwrite("creating table " . $dprv_post_content_files);
			$sql = "CREATE TABLE " . $dprv_post_content_files . " (
					post_id bigint(20) NOT NULL, INDEX (post_id),
					filename varchar(280) CHARACTER SET utf8 COLLATE utf8_general_ci,
					digital_fingerprint varchar(64)
					);";
			//We need to include this file so we have access to the dbDelta function below (which is used to create the table)
			//require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta($sql);
			$dprv_db_error =$wpdb->last_error;
			$log->lwrite("just created posts table " . $dprv_post_content_files . "; error text = " . $dprv_db_error);
			//if($wpdb->get_var("show tables like '$dprv_post_content_files'") != $dprv_post_content_files)
			if(dprv_wpdb("get_var", "show tables like '$dprv_post_content_files'") != $dprv_post_content_files)
			{
				$message = "Failure to create table " . $dprv_post_content_files . " with error " .  $dprv_db_error;
				dprv_record_event($message);
			}
		}
		else
		{
			$log->lwrite("Table " . $dprv_post_content_files . " exists already");  
		}
	}


	function create_dprv_log_table()
	{
		$log = new DPLog();
		global $wpdb;
		$dprv_prefix = get_option('dprv_prefix');
		$dprv_log = $dprv_prefix . "dprv_log";
		// Check to see if the table exists already, if not, then create it
		if(dprv_wpdb("get_var", "show tables like '$dprv_log'") != $dprv_log)
		{
			$log->lwrite("creating table " . $dprv_log);
			$sql = "CREATE TABLE " . $dprv_log . " (
					id bigint NOT NULL AUTO_INCREMENT PRIMARY KEY,
					timestamp int,
					ip_address varchar(40),
					url varchar(256),
					event varchar(200)
					);";
			//We need to include this file so we have access to the dbDelta function below (which is used to create the table)
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta($sql);
			$dprv_db_error =$wpdb->last_error;
			$log->lwrite("just created log table " . $dprv_log . "; error text = " . $dprv_db_error);
			if(dprv_wpdb("get_var", "show tables like '$dprv_log'") != $dprv_log)
			{
				$message = "Failure to create table " . $dprv_log . " with error " .  $dprv_db_error;
				dprv_record_event($message);
			}
		}
		else
		{
			$log->lwrite("Table " . $dprv_log . " exists already");  
		}
	}

	function dprv_add_column($table, $column_name, $qualifier)
	{				
		$log = new DPLog();
		global $wpdb;
		$dprv_db_last_error = $wpdb->last_error;
		if ($wpdb->get_var("SHOW COLUMNS FROM $table LIKE '$column_name'") != $column_name)
		{
			$sql = "ALTER TABLE " . $table . " ADD COLUMN $column_name $qualifier";
			$wpdb->query($sql);
			$dprv_db_error = $wpdb->last_error;
			//$log->lwrite("add col $column_name last error $dprv_db_error");
			$message = "";
			if ($wpdb->get_var("SHOW COLUMNS FROM $table LIKE '$column_name'") != $column_name)
			{
				$message .= "Did not add column $column_name to $table, last db error $dprv_db_error";
				dprv_record_event($message);
				return false;
			}
		}
		return true;
	}

	function record_dprv_licenses()
	{
	$log = new DPLog();  
	$licenseTypes = array(
							__("Read Only","dprv_cp"),
							__("Commercial","dprv_cp"),
							__("Attribution","dprv_cp"),
							__("Attribution, No derivative work","dprv_cp"),
							__("Non-commercial, Attribution, no Derivative work","dprv_cp"),
							__("Non-commercial, Attribution","dprv_cp"),
							__("Non-commercial, Attribution, Share Alike","dprv_cp"),
							__("Attribution, Share Alike","dprv_cp"),
							__("General Public License","dprv_cp"));

	$licenseCaptions = array(
								__("All Rights Reserved", "dprv_cp"),
								__("All Rights Reserved", "dprv_cp"),
								__("Some Rights Reserved", "dprv_cp"),
								__("Some Rights Reserved", "dprv_cp"),
								__("Some Rights Reserved", "dprv_cp"),
								__("Some Rights Reserved", "dprv_cp"),
								__("Some Rights Reserved", "dprv_cp"),
								__("Some Rights Reserved", "dprv_cp"),
								__("Some Rights Reserved", "dprv_cp"));

	$licenseAbstracts = array(
								__("You may read the original content in the context in which it is published (at this web address). No other copying or use is permitted without written agreement from the author.","dprv_cp"),
								__("You may read the original content in the context in which it is published (at this web address). You may make other uses of the content only with the written permission of the author on payment of a fee.","dprv_cp"),
								__("You may copy this content, create derivative work from it, and re-publish it, provided you include an overt attribution to the author(s).","dprv_cp"),
								__("You may copy this content and re-publish it in unmodified form, provided you include an overt attribution to the author(s). You are not permitted to create derivative works.","dprv_cp"),
								__("You may copy this content, and re-publish it in unmodified form for non-commercial purposes, provided you include an overt attribution to the author(s). You are not permitted to create derivative works.","dprv_cp"),
								__("You may copy this content, create derivative work from it, and re-publish it for non-commercial purposes, provided you include an overt attribution to the author(s).","dprv_cp"),
								__("You may copy this content, create derivative work from it, and re-publish it for non-commercial purposes, provided you include an overt attribution to the author(s) and the re-publication must itself be under the terms of this license or similar.","dprv_cp"),
								__("You may copy this content, create derivative work from it, and re-publish it, provided you include an overt attribution to the author(s) and the re-publication must itself be under the terms of this license or similar.","dprv_cp"),
								__("The GNU General Public License is a free, copyleft license for software and other kinds of works. You may re-publish the content (modified or unmodified) providing the re-publication is itself under the terms of the GPL.","dprv_cp"));

	$licenseURLs = array(
							"",
							"",
							"https://creativecommons.org/licenses/by/3.0/",
							"https://creativecommons.org/licenses/by-nd/3.0/",
							"https://creativecommons.org/licenses/by-nc-nd/3.0/",
							"https://creativecommons.org/licenses/by-nc/3.0/",
							"https://creativecommons.org/licenses/by-nc-sa/3.0/",
							"https://creativecommons.org/licenses/by-sa/3.0/",
							"https://www.gnu.org/licenses/gpl.html");

		global $wpdb;
		$dprv_licenses = get_option('dprv_prefix') . "dprv_licenses";

		for ($i=0; $i<count($licenseTypes); $i++)
		{
			//$rows_affected = $wpdb->insert($dprv_licenses, array('license_type'=>$licenseTypes[$i], 'license_caption'=>$licenseCaptions[$i], 'license_abstract'=>$licenseAbstracts[$i], 'license_url'=>$licenseURLs[$i]));
			$rows_affected = $wpdb->insert($dprv_licenses, array('license_type'=>$licenseTypes[$i], 'license_caption'=>$licenseCaptions[$i], 'license_abstract'=>$licenseAbstracts[$i], 'license_url'=>$licenseURLs[$i]), array('%s', '%s', '%s', '%s'));
		}
	}
	// END OF ON-ACTIVATION FUNCTIONS:


	// DE-ACTIVATION FUNCTION:
	function dprv_deactivate()
	{
		$log = new DPLog();  
		$log->lwrite("VERSION " . DPRV_VERSION . " DEACTIVATED");  
	}


	// EVERY TIME WE START UP, CHECK IF ACTIVATION NEEDS TO BE DONE, CHECK FOR DB PREFIX CHANGE AND DISPLAY REMINDER ABOUT CONFIGURATION IF NECESSARY
	function dprv_init()
	{
		$log = new DPLog();  
		if (get_option('dprv_activated_version') !== DPRV_VERSION)
		{
			$log->lwrite("change of plugin version detected, calling activation");
			dprv_activate();
		}
		// Load language file if applicable
		$plugin_dir = basename(dirname(__FILE__)) . '/languages/';
		load_plugin_textdomain( 'dprv_cp', false, $plugin_dir );

		// Check if db prefix changed and adjust if need be
		global $wpdb;
		$dprv_posts = get_option('dprv_prefix') . "dprv_posts";
		if (get_option('dprv_prefix') !== false && get_option('dprv_prefix') != $wpdb->prefix  && get_option('dprv_prefix') != strtolower($wpdb->prefix))
		{
			if(dprv_wpdb("get_var", "show tables like '$dprv_posts'") != $dprv_posts)
			{
				$dprv_posts = $wpdb->prefix . "dprv_posts";

				if(dprv_wpdb("get_var", "show tables like '$dprv_posts'") == $dprv_posts)
				{
					// User has changed db prefix of dprv tables (assume all), adjust dprv_prefix accordingly 
					$dprv_this_event = "found table for wpdb-prefix(" . $wpdb->prefix . ") but not dprv_prefix(" . get_option('dprv_prefix') . "), updating dprv_prefix"; 
					dprv_record_event($dprv_this_event);
					update_option('dprv_prefix', $wpdb->prefix);
				}
				else
				{
					$dprv_this_event = "could not find table for wpdb-prefix(" . $wpdb->prefix . ") or dprv_prefix(" . get_option('dprv_prefix') . "), re-doing activation"; 
					dprv_record_event($dprv_this_event);
					dprv_activate();
				}
			}
			else
			{
				// User has changed db prefix but has not changed prefix on dprv tables, that's ok (user might do it later)
				// record event found table for dprv_prefix (dprv_prefix) even though different to wpdb->prefix
			}
		}
		// TODO: Only perform this check if last_fingerprint may be required, e.g. edit or display posts/pages
		// Check whether version 2.n db upgrade has been done successfully:

/////////////////////////////////////////////////////////////////////////////////////////////////////////
		$int_db_ok = dprv_db_ok();
		if ($int_db_ok < 0)
		{
			// Repeat activation
			dprv_activate();
		}

/////////////////////////////////////////////////////////////////////////////////////////////////////////		

/*
		$like_last_fingerprint = dprv_wpdb("get_var", "SHOW COLUMNS FROM $dprv_posts LIKE 'last_fingerprint'");
		if ($like_last_fingerprint !== false)	// false would indicate that there is an error 28 or incorrect key file in either case maybe we should ignore, other ops might work
		{
			$log->lwrite ("like_last_fingerprint=" . $like_last_fingerprint);
			if (($like_last_fingerprint === null || $like_last_fingerprint != 'last_fingerprint') && current_user_can("activate_plugins"))
			{
				$more_eval = "";
				if (is_null($like_last_fingerprint))
				{
					$more_eval = " (like_last_fingerprint is null) ";
				}
				else
				{
					if (!is_string($like_last_fingerprint))
					{
						//$more_eval = dprv_eval($like_last_fingerprint);
						$more_eval =  " (like_last_fingerprint is not a string) ";
					}
				}
				$dprv_this_event = "last_fingerprint does not exist in " . $dprv_posts . "(=" . $like_last_fingerprint . ")" . $more_eval . ", repeating activation";
				dprv_record_event($dprv_this_event);
				dprv_activate();
			}
		}

*/

		if (get_option('dprv_enrolled') != "Yes")
		{
			define("DPRV_REMINDER", "<strong>".__("Copyright Proof is almost ready.", "dprv_cp")."</strong> <span style=\"color:red\">".sprintf(__('You must %1$s Configure Copyright Proof and Register %2$s to get it working', 'dprv_cp'), '<a href="options-general.php?page=copyright-proof-settings"><b>', '</b></a>') . "</span>");
			add_action('admin_notices', 'dprv_reminder');
			return;
		}
		if (trim(get_option('dprv_api_key')) == '')
		{	
			define("DPRV_REMINDER", "<strong>".__("Copyright Proof needs configuration.", "dprv_cp")."</strong> ".sprintf(__('Please %s obtain a new api key %s to get it working', 'dprv_cp'), '<a href="options-general.php?page=copyright-proof-settings"><b>', '</b></a>'));
			add_action('admin_notices', 'dprv_reminder');
			return;
		}
		$dprv_pending_message = trim(get_option('dprv_pending_message'));
		if ($dprv_pending_message != '')
		{	
			define("DPRV_REMINDER", "<strong>" . $dprv_pending_message . "</strong>");
			add_action('admin_notices', 'dprv_reminder');
			update_option('dprv_pending_message', '');
			return;
		}
	}

	function dprv_log_writeline($severity, $message, $url)
	{
		$log = new DPLog();  
		//$log->lwrite("entered dprv_error with " . $errstr);
		if (strlen($message) > 200)
		{
			$message = substr($message, 0, 200);
		}
		if (strlen($url) > 256)
		{
			$url = substr($url, 0, 256);
		}
		global $dprv_last_error, $wpdb;
		//if (false === $wpdb->insert(get_option('dprv_prefix') . "dprv_log", array('timestamp'=>time(), 'ip_address'=>$_SERVER['REMOTE_ADDR'],'url'=>$url, 'event'=>$message)))
		if (false === $wpdb->insert(get_option('dprv_prefix') . "dprv_log", array('timestamp'=>time(), 'ip_address'=>$_SERVER['REMOTE_ADDR'],'url'=>$url, 'event'=>$message), array('%d', '%s', '%s', '%s')))
		{
			$dprv_db_error = $wpdb->last_error;
			$dprv_this_event = $dprv_db_error . ' inserting log entry ' . $message;
			dprv_record_event($dprv_this_event);
			return false;
		}
		return true;
	}



	function dprv_reminder()
	{
		$script_name = pathinfo($_SERVER['PHP_SELF'], PATHINFO_BASENAME);
		$posDot = strrpos($script_name,'.');
		if ($posDot != false)
		{
			$script_name = substr($script_name, 0, $posDot);
		}

		if ($script_name != "options-general" || strpos($_SERVER['QUERY_STRING'], "copyright-proof-settings") === false)
		{
			echo "<div id='dprv_reminder' class='updated fade'><p>" . DPRV_REMINDER . "</p></div>";
		}
	}

	function dprv_populate_licenses()
	{
		$log = new DPLog();
		global $dprv_licenseIds, $dprv_licenseTypes, $dprv_licenseCaptions, $dprv_licenseAbstracts, $dprv_licenseURLs, $wpdb;
		$dbquery = 'SELECT * FROM ' . get_option('dprv_prefix') . 'dprv_licenses';
		$license_info = $wpdb->get_results($dbquery, ARRAY_A);
		if (empty($license_info))
		{
			$log->lwrite("license_info is empty");
		}
		else
		{
			$dprv_licenseIds = array(count($license_info));
			$dprv_licenseTypes = array(count($license_info));
			$dprv_licenseCaptions = array(count($license_info));
			$dprv_licenseAbstracts = array(count($license_info));
			$dprv_licenseURLs = array(count($license_info));

			for ($i=0; $i<count($license_info); $i++)
			{
				$dprv_licenseIds[$i] = $license_info[$i]["id"];
				$dprv_licenseTypes[$i] = $license_info[$i]["license_type"];
				$dprv_licenseCaptions[$i] = $license_info[$i]["license_caption"];
				$dprv_licenseAbstracts[$i] = $license_info[$i]["license_abstract"];
				$dprv_licenseURLs[$i] = $license_info[$i]["license_url"];
			}
		}
	}

	function dprv_populate_licenses_js()
	{
		global $dprv_licenseIds, $dprv_licenseTypes, $dprv_licenseCaptions, $dprv_licenseAbstracts, $dprv_licenseURLs;
		// Pass variables into Javascript for later use:
		echo('<script type="text/javascript">
				//<![CDATA[
				');
		$licence_count = count($dprv_licenseIds);
		echo('
				var dprv_licenseIds = new Array(' . $licence_count . ');
				var dprv_licenseTypes = new Array(' . $licence_count . ');
				var dprv_licenseCaptions = new Array(' . $licence_count . ');
				var dprv_licenseAbstracts = new Array(' . $licence_count . ');
				var dprv_licenseURLs = new Array(' . $licence_count . ');
			');

		for ($i=0; $i<$licence_count; $i++)
		{
			echo('
				dprv_licenseIds[' . $i . '] = "' . $dprv_licenseIds[$i] .'";
				dprv_licenseTypes[' . $i . '] = "' . $dprv_licenseTypes[$i] .'";
				dprv_licenseCaptions[' . $i . '] = "' . $dprv_licenseCaptions[$i] .'";
				dprv_licenseAbstracts[' . $i . '] = "' . $dprv_licenseAbstracts[$i] .'";
				dprv_licenseURLs[' . $i . '] = "' . $dprv_licenseURLs[$i] .'";
				');
		}

		$dprv_default_licenseType = "";
		$dprv_license = get_option('dprv_license');
		for ($i=0; $i<count($dprv_licenseIds); $i++)
		{
			if ($dprv_licenseIds[$i] == $dprv_license)
			{
				$dprv_default_licenseType = $dprv_licenseTypes[$i];
				break;
			}
		}
		echo ('
				var dprv_default_licenseType = "' . $dprv_default_licenseType . '";
				var dprv_defaultLicenseId = "' . $dprv_license . '";
				//]]>
			</script>');
	}

	function dprv_createUpgradeLink($dprv_User = null)
	{
		global $dprv_blog_host;

		if ($dprv_User == null)
		{
			if (trim(get_option('dprv_user_id')) != "")
			{
				$dprv_qs = '?UserId='  . trim(get_option('dprv_user_id')) . '&amp;ApiKey=' . get_option('dprv_api_key') . '&amp;Domain=' . $dprv_blog_host . '&amp;UserAgent=Copyright+Proof+' . DPRV_VERSION;
				return "https://" . DPRV_WWW . '/secure/UpgradeRenew.html' . $dprv_qs;
			}
			else
			{
				return "https://" . DPRV_WWW . "/members/members_area.aspx?content=subscription_topup.aspx";
			}
		}
		else
		{
			$dprv_qs = '?UserId=' . $dprv_User . '&amp;UserAgent=Copyright+Proof+' . DPRV_VERSION;
			return "https://" . DPRV_WWW . '/secure/UpgradeRenew.html' . $dprv_qs;
		}
	}

	function dprv_post_sync($pid)
	{
		global $wpdb;

		$log = new DPLog();
		$log->lwrite("post " . $pid . " deleted, checking for dprv_post record with same id");
		$sql='SELECT id FROM ' . get_option('dprv_prefix') . 'dprv_posts WHERE id = %d';
		if (dprv_wpdb("get_var", $sql, $pid))
		{
			$log->lwrite("found a dprv_post " . $pid . ", will now delete it"); 
			return $wpdb->query($wpdb->prepare('DELETE FROM ' . get_option('dprv_prefix') . 'dprv_posts WHERE id = %d', $pid));
		}
		return true;
	}

	function dprv_add_settings_link($links, $file)
	{
		static $this_plugin;
		if (!$this_plugin)
		{
			$this_plugin = plugin_basename(__FILE__);
		}
		if ($file == $this_plugin)
		{
			$settings_link = '<a href="options-general.php?page=copyright-proof-settings">'.__("Settings", "dprv_cp").'</a>';
			array_push($links, $settings_link);
		}
		return $links;
	}

	function dprv_wpdb($action, $sql, $args = null)
	{
		$log = new DPLog();
		global $wpdb;
		if (!is_null($args))
		{
			$sql = $wpdb->prepare($sql, $args);
		}
		$result = "29 jump street";	// just to establish variable scope
		//$prev_db_error = $wpdb->last_error;
		$wpdb->last_error = "";
		$dprv_time_before = time();
		switch ($action)
		{
			case "get_var":
			{
				$result = $wpdb->get_var($sql);
				break;
			}
			case "get_row":
			{
				$result = $wpdb->get_row($sql, ARRAY_A);
				break;
			}
			case "get_results":
			{
				$result = $wpdb->get_results($sql, ARRAY_A);
				break;
			}
			case "query":
			{
				$result = $wpdb->query($sql);
				break;
			}
			default:
			{
				return false;
			}
		}
		$dprv_seconds_taken = time() - $dprv_time_before;
		$dprv_db_error = $wpdb->last_error;
		//$dprv_sql_error = mysql_error();
		if (trim($dprv_db_error) != "")
		{
			$bt = debug_backtrace();
			// Note "SHOW COLUMNS" rather than "show columns" used in this plugin indicates not a critical point (normal everyday check)
			$dprv_this_event = "wpdb SQL error " . $dprv_db_error . " on " . $sql;
			if ((stripos($dprv_db_error, "Incorrect key file for table") !== false || stripos($dprv_db_error, "Got error 28 from storage engine") !== false || stripos($dprv_db_error, "Errcode: 28") !== false) && strpos($sql, "SHOW COLUMNS FROM") !== false && is_null($result))
			{
				// Not good but (for now) ignore it, maybe other instructions will work:-)
				// Calling code will recognise false and ignore error
				// Also don't bother with logging all that backtrace stuff
				
				$dprv_this_event .= "; Result of dprv_db_ok()=" . dprv_db_ok();
				dprv_record_event($dprv_this_event);
				return false;
			}
			//if (trim($dprv_sql_error) != "" && $dprv_sql_error != $dprv_db_error)
			//{
			//	$dprv_this_event .= "; MySQL error " . $dprv_sql_error;
			//}
			$more_eval = "";
			if (is_null($result))
			{
				$more_eval = " (result is null)";
			}
			else
			{
				if (!is_string($result))
				{
					$more_eval = " (result not string = " . $result . ")";
				}
				else
				{
					$more_eval = " (result is $result)";
				}
			}
			$dprv_this_event .= $more_eval . " ";
			if ($dprv_seconds_taken > 0)
			{
				$dprv_this_event .= "; took " . $dprv_seconds_taken . " seconds ";
			}
			if (stripos($dprv_db_error, "MySQL server has gone away") !== false)
			{
				$dprv_this_event .= '; mysql.connect_timeout=' . ini_get('mysql.connect_timeout') . " ";
			}
			$counter = 0;
			if (is_array($bt))
			{
				foreach ($bt as $caller)
				{
					if (is_array($caller))
					{
						if (isset($caller["file"]))
						{
							$dprv_this_event .= "called from " . str_replace(ABSPATH, "", $caller["file"]) . " line " . $caller["line"] . "\r\n";
						}
						else
						{
							$dprv_this_event .= "function " . $caller["function"] . "\r\n";
						}
					}
					$counter++;
					if ($counter > 4)
					{
						break;
					}
				}
			}
			$dprv_this_event .= "; Result of dprv_db_ok()=" . dprv_db_ok();
			dprv_record_event($dprv_this_event);
			if (stripos($sql, "SHOW COLUMNS FROM") === false)  // Do not display SQL error message if the problem occurred on Show columns from
			{
				$dprv_pending_message = get_option('dprv_pending_message');
				$dprv_new_message = "MySQL Error occurred: " . $dprv_db_error;
				if (strpos($dprv_pending_message, $dprv_new_message) === false)		// Avoid repeating the same message over and over
				{
					if ($dprv_pending_message != "")
					{
						$dprv_pending_message .= "<br/>";
					}
					$dprv_pending_message .= $dprv_new_message . " on &quot;" . $sql . "&quot;";
					update_option('dprv_pending_message', $dprv_pending_message);
				}
			}
		}
		return $result;
	}


	// CHECK FOR EXISTENCE OF "last_fingerprint" COLUMN (if not there, then 2.n db updates not performed OK)
	function dprv_db_ok()
	{
		$log = new DPLog();
		global $wpdb;
		$wpdb->last_error = "";
		$sql="SELECT last_fingerprint FROM " . get_option('dprv_prefix') . "dprv_posts WHERE id = 0 LIMIT 1";
		$result = $wpdb->get_var($sql);
		//$dprv_sql_error = mysql_error();
		$dprv_db_error = $wpdb->last_error;
		if (trim($dprv_db_error) != "")
		{
			$dprv_this_event = $dprv_db_error . " while testing for last_fingerprint";
			dprv_record_event($dprv_this_event);
			if (strpos(strtolower($dprv_db_error), "unknown column") !== false)
			{
				return -1;		// last fingerprint column does not exist
			}
			return 0;			// some other error
		}
		return 1;				// OK
	}


	// ADD NEW COLUMN  
    function dprv_columns_head($defaults)
	{  
 		$defaults['digiproved'] = '<span title="' . __("Copyright of content secured by Digiprove?", "dprv_cp") . '">Digiproved?</span>';  
        return $defaults;  
    }  
      
    // SHOW THE Digiproved Status  
    function dprv_columns_content($column_name, $dprv_post_id)
	{  
        if ($column_name == 'digiproved')
		{  
            $post_digiproved = dprv_get_dp_status($dprv_post_id);  
            if ($post_digiproved) 
			{  
                echo $post_digiproved;  
            }  
        }  
    }  
	
	// GET DIGIPROVED DATA  
    function dprv_get_dp_status($dprv_post_id)
	{  
   		$sql="SELECT * FROM " . get_option('dprv_prefix') . "dprv_posts WHERE id = %d";
		$dprv_post_info = dprv_wpdb("get_row", $sql, $dprv_post_id);
		if (!is_null($dprv_post_info) && count($dprv_post_info) > 0)
		{
			if ($dprv_post_info["digiprove_this_post"] == true)
			{
				if ($dprv_post_info["certificate_id"] != null)
				{
					$dprv_timestamp = strtotime($dprv_post_info["cert_utc_date_and_time"]);
					$dprv_timestamp = "<span title='" . date("j M Y H.i:s", $dprv_timestamp) . " UTC'>" . date("j M Y", $dprv_timestamp) . "</span>";
					return $dprv_timestamp;
				}
				else
				{
					return "No";
				}
			}
			else
			{
				return "De-selected";
			}
		}
		else
		{
			return "No";
		}
	}

	$dprv_post_types = explode(',',get_option('dprv_post_types'));
	foreach ($dprv_post_types as $dprv_post_type)
	{
		if ($dprv_post_type == "post")
		{
			add_filter('manage_posts_columns', 'dprv_columns_head');  
			add_action('manage_posts_custom_column', 'dprv_columns_content', 10, 2);
		}
		else
		{
			if ($dprv_post_type == "page")
			{
				add_filter('manage_pages_columns', 'dprv_columns_head');  
				add_action('manage_pages_custom_column', 'dprv_columns_content', 10, 2);
			}
			else
			{
				// User defined post type
				add_filter('manage_users_sortable_columns', 'dprv_columns_head');  
				add_action('manage_users_custom_column', 'dprv_columns_content', 10, 2);
			}
		}
	}
	function dprv_record_event(&$dprv_this_event, $dprv_event = null)	// Record some event for error reporting purposes (will eventually show up on server log at next successful api transaction)
	{
		$log = new DPLog();
		$log->lwrite($dprv_this_event);
		if ($dprv_event == null)
		{
			$dprv_event = get_option('dprv_event');
		}
		if ($dprv_event != "")
		{
			$dprv_event .= "; ";
		}
		$dprv_this_event =  date("Y/m/d-H:i O") . ': ' . $dprv_this_event;
		$dprv_event .= $dprv_this_event;
		$event_length = strlen($dprv_event);
		if ($event_length > 16000) //  Placing a limit on size
		{
			$prefix = ".";
			for ($pos=0; $pos<$event_length; $pos++)
			{
				if (substr($dprv_event, $pos, 1) == ".")
				{
					$prefix .= ".";
				}
				else
				{
					break;
				}
			}
			$dprv_event = substr($dprv_event, $pos);
			$event_length = strlen($dprv_event);
			if ($event_length > 16000)	// still over the top?
			{
				$dprv_event = $prefix . substr($dprv_event, $event_length - 15999);
			}
			else
			{
				$dprv_event = $prefix . $dprv_event;
			}
		}
		update_option('dprv_event', $dprv_event);
		return $dprv_event;
	}

	function dprv_unrecord_event($dprv_this_event, $dprv_event = null)
	{
		if ($dprv_event == null)
		{
			$dprv_event = get_option('dprv_event');
		}
		$dprv_event = str_replace('; ' . $dprv_this_event, '', $dprv_event);
		$dprv_event = trim(str_replace($dprv_this_event, '', $dprv_event));
		update_option('dprv_event', $dprv_event);
		return $dprv_event;
	}
	function dprv_dbDelta($sql)
	{



	}

    $dprv_back_digiprove_allowances = array();
    //function dprv_daily_limit($dprv_subscription_type, &$dprv_max_file_count)  // This function moved out of copyright_proof_edit.php and renamed
    function dprv_entitlements($dprv_subscription_type, &$dprv_max_file_count, &$dprv_back_digiprove_allowance)
    {
   	    // Declare and initialise subscription plan permissions:
	    //$dprv_daily_limits = array (5,20,100,500,-1);               // Maximum Digiproves per day (-1 = unlimited)
	    $dprv_daily_limits = array (10,50,200,2000,-1);               // Maximum Digiproves / Autoprotects per day (-1 = unlimited)
        $dprv_max_file_counts = array (0,10,40,100,999);            // Maximum number of media files in a post
        $dprv_back_digiprove_allowances = array(30,150,-1,-1,-1);   // Maximum number of back-Digiproves (batch Digiproving of older posts)

        $dprv_today_limit = $dprv_daily_limits[0];
	    $dprv_max_file_count = $dprv_max_file_counts[0];
        $dprv_back_digiprove_allowance = $dprv_back_digiprove_allowances[0];
	    switch ($dprv_subscription_type)
	    {
		    case "Personal":
		    {
        	    $dprv_today_limit = $dprv_daily_limits[1];
			    $dprv_max_file_count = $dprv_max_file_counts[1];
                $dprv_back_digiprove_allowance = $dprv_back_digiprove_allowances[1];
			    break;
		    }
		    case "Professional":
		    {
        	    $dprv_today_limit = $dprv_daily_limits[2];
			    $dprv_max_file_count = $dprv_max_file_counts[2];
                $dprv_back_digiprove_allowance = $dprv_back_digiprove_allowances[2];
			    break;
		    }
		    case "Corporate Light":
		    {
        	    $dprv_today_limit = $dprv_daily_limits[3];
			    $dprv_max_file_count = $dprv_max_file_counts[3];
                $dprv_back_digiprove_allowance = $dprv_back_digiprove_allowances[3];
			    break;
		    }
		    case "Corporate":
		    {
        	    $dprv_today_limit = $dprv_daily_limits[4];
			    $dprv_max_file_count = $dprv_max_file_counts[4];
                $dprv_back_digiprove_allowance = $dprv_back_digiprove_allowances[4];
			    break;
		    }
		    default:
		    {
			    break;
		    }
	    }
	    return $dprv_today_limit;
    }

    function dprv_php2js($dprv_variable_array)
    {
        // Move a variable into javascript
        // substitute for wp_localize_script which must be called early (before enqueue_script instantiates) - too early for variables to be available
        // Note setting enqueue_script to instantiate on footer works but is dependent on wp_footer being there (some themes omit)
        if (!is_array($dprv_variable_array))
        {
            return false;
        }
        print ('<script type="text/javascript">
                <!--
                var temp = "";');
        foreach ($dprv_variable_array as $key=>$value)
        {
            print 'temp = \'' . json_encode($value) . '\';';
            print 'var ' . $key . '= JSON.parse(temp);';
        }
        print ('//-->
               </script>');
        return true;
    }
function dprv_eval($something, $html = false, $tabs = "")
{
	if (is_null($something))
	{
		return "NULL; ";
	}
	$log = new DPLog();
	$line = "\r\n";
	$tab = "\t";
	$apos = "'";
	$arrow = "=> ";
	if ($html)
	{
		$line = "<br/>";
		$tab = "&nbsp;&nbsp;&nbsp;&nbsp;";
		$apos = "&#039;";
		$arrow = "=&gt;&nbsp;";
	}
	$return = "";
	if (is_object($something))
	{
		//$called_class = "";
		//if (function_exists("get_called_class"))
		//{
		//	$called_class = get_called_class($something);
		//	if ($called_class === false)
		//	{
		//		$called_class = ", called from outside a class";
		//	}
		//	else
		//	{
		//		$called_class = ", called in class " . $called_class;
		//	}
		//}
		//$return .= "(object) of " . count($something) . " properties, parent class is " . get_parent_class($something) . ", class is " . get_class($something) . $called_class . "; ";
		$return .= "(object) of " . count($something) . " properties, parent class is " . get_parent_class($something) . ", class is " . get_class($something) . "; ";
		$return .= "array of class methods is " . dprv_eval(get_class_methods($something), $html, $tabs);
		$return .= "array of class variables is " . dprv_eval(get_class_vars(get_class($something)), $html, $tabs);
		$return .= "array of object variables is " . dprv_eval(get_object_vars($something), $html, $tabs);
	}
	if (is_array($something))
	{
		$return .= "(array) [" . count($something) . "]";
		if (count($something) > 0)
		{
			$return .= ": " . $line . $tabs . "{" . $line;
			foreach ($something as $a_key => $a_value)
			{
				$return .= $tabs . $tab . $apos . $a_key . $apos . $arrow . dprv_eval($a_value, $html, $tabs . $tab) . $line;
			}
			$return .= $tabs . "}" . $line;
		}
	}
	if (is_bool($something))
	{
		$return .= "(bool)";
		if ($something == true)
		{
			$return .= " true; ";
		}
		else
		{
			$return .= " false; ";
		}
	}
	if (is_callable($something))
	{
		$return .= "is callable; ";
	}
	if (is_double($something))
	{
		$return .= "(double)" . "; ";
	}
	if (is_float($something))
	{
		$return .= "(float)" . "; ";
	}
	if (is_int($something))
	{
		$return .= "(int) " . $something . "; ";
	}
	if (is_integer($something))
	{
		$return .= "(integer) " . $something . "; ";
	}
	if (is_long($something))
	{
		$return .= "(long)" . "; ";
	}
	if (is_numeric($something))
	{
		$return .= ", is numeric; ";
	}
	if (is_real($something))
	{
		$return .= ", is real; ";
	}
	if (is_resource($something))
	{
		$return .= ", is resource; ";
	}
	if (is_string($something))
	{
		$return .= "string (" . strlen($something) . ")";
		if (strlen($something) > 0)
		{
			$return .= ": ";
			if (strlen($something) > 200)
			{
				$return .= " begins with " . substr($something, 0, 150) . "; ";
			}
			else
			{
				$return .= $something . "; ";
			}
		}
	}
	else
	{
		if (is_scalar($something))
		{
			$return .= ", is scalar; ";
		}
	}
	return $return;
}

?>