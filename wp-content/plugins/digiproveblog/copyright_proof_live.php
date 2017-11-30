<?php
// FUNCTIONS CALLED WHEN SERVING PAGES
include_once('copyright_proof_integrity.php');						// Functions for Integrity Checking
function dprv_log_event()
{
    // The $_REQUEST contains all the data sent via ajax
    if ( isset($_REQUEST) )
	{
		$severity = $_REQUEST['severity'];
		$message = $_REQUEST['message'];
		$url = urldecode($_REQUEST['url']);
		// Now we'll return it to the javascript function
		// Anything outputted will be returned in the response
		echo "got message " . $message . " page=" . $url;

		// React ansynchronously to event
		// Log it
		$rslt = dprv_log_writeline($severity, $message, $url);
		// If so configured by user, send an email
		if (strpos(get_option('dprv_record_IP'), "Email") !== false)
		{
			global $dprv_blog_host;
			$mail_message = __("Digiprove's Copyright Proof plugin detected the following event:", "dprv_cp") . "\r\n\r\n";
			$mail_message .= $message . __(", page=", "dprv_cp") .$url . "\r\n\r\n";
			$mail_message .= __("IP address: ", "dprv_cp") . $_SERVER['REMOTE_ADDR'] . "\r\n\r\n";
			$mail_message .= __("You are receiving this email because of your Wordpress Copyright Proof settings for the site ", "dprv_cp") . $dprv_blog_host . "\r\n\r\n";
			$mail_message .= __("You can visit your Digiprove account by logging in at https://www.digiprove.com/secure/login.aspx (your user id is ", "dprv_cp") . get_option('dprv_user_id') . ").";
			wp_mail(get_option('admin_email'), __('Warning - possible attempt to copy content from ', 'dprv_cp') . get_option('blogname'), $mail_message); 
		}
    }
    // Always die in functions echoing ajax content
   die();
}
if (get_option('dprv_record_IP') != "off")
{
	add_action( 'wp_ajax_dprv_log_event', 'dprv_log_event' );
	add_action( 'wp_ajax_nopriv_dprv_log_event', 'dprv_log_event' );
}
function dprv_enqueue_live()
{
   	$jsfile = plugins_url("copyright_proof_live.js", __FILE__ );
	wp_register_script('copyright_proof_live', $jsfile, null, DPRV_VERSION, false);
    $vars_for_js = array(
                            'record_IP' => get_option('dprv_record_IP'),
                            'site_url' => site_url(),
                            'ajax_url' => admin_url('admin-ajax.php'),
                            'noRightClickMessage' => addslashes(get_option('dprv_right_click_message')),
                            'attributeCaption' => __( 'Attributions - owner(s) of some content', 'dprv_cp' )
                        );
    wp_localize_script( "copyright_proof_live", "dprv_js_bridge", $vars_for_js);
    wp_enqueue_script('copyright_proof_live', $jsfile, null, DPRV_VERSION, false);

    $dprv_frustrate_copy = get_option('dprv_frustrate_copy');
	if ($dprv_frustrate_copy == "Yes")
	{
   		$jsfile = plugins_url("frustrate_copy.js", __FILE__ );
		wp_register_script('frustrate_copy', $jsfile, null, DPRV_VERSION, false);
        wp_enqueue_script('frustrate_copy', $jsfile, null, DPRV_VERSION, false);
	}
}
add_action( 'wp_enqueue_scripts', 'dprv_enqueue_live' );   // Have to do it this way for client-facing content

function dprv_amp_post_template_head($amp_template)
{
    echo ('<script async custom-element="amp-lightbox" src="https://cdn.ampproject.org/v0/amp-lightbox-0.1.js"></script>');
}
add_action('amp_post_template_head', 'dprv_amp_post_template_head' );

//  Below added to support rendering of notice and license in AMP
function dprv_amp_add_css( $amp_template )
{
    $log = new DPLog();  
    $log->lwrite("adding css");
    // only CSS here please...
    ?>
    .dprv_notice_div
    {
        vertical-align:baseline;
        padding: 3px 3px 1px 3px;
        margin-top:2px;
        margin-bottom:2px;
        border-collapse:separate;
        float:none;
        font-family:Tahoma, MS Sans Serif;
        display:inline-block;  /*minimise width, enforce upper/lower margins, no line break*/
    }
    .dprv_notice_tab
    {
    	display:table;		/* minimise width, enforce upper/lower margins, line break*/
		padding-top:3px;
        padding-bottom:1px;
    }
    .dprv_notice_a
    {
        border:0px;
        padding:0px;
        margin:0px;
        float:none;
        display:inline;
        background:transparent none;
    }
    .dprv_notice_text
    {
        text-decoration: none;
        font-family: Tahoma, MS Sans Serif;
        font-style:normal;
        font-weight:normal;
        letter-spacing:normal;
    }

    .dprv_notice_amp_img
    {
        max-width:none;
        display:inline-block;
        border:0px;
        margin:0px;
        padding:0px;
        float:none;
        background:transparent none;
        vertical-align:middle;
    }
    .dprv_notice_span
    {
        border:0px;
        float:none;
        display:inline;
        padding:0px;
        vertical-align:middle;
    }
    .dprv_license_a
    {
        display:block;
        border:0px;
        float:none;
        text-align:left; 
        vertical-align:2px;
        padding:0px;
        margin-bottom:2px;
    }
    .dprv_icon
    {
        vertical-align:middle;
    }
    .dprv_info_panel
    {
        float:none;
        max-width:320px;
        padding:0px;
        margin-left:auto;
        margin-right:auto;
        margin-top:auto;
        margin-bottom:auto;
        display:inline-block;
    }
    .-amp-fill-content  /* Note: this is a bit naughty - does not conform to AMP standard */
    {
        display:flex;
        display:-webkit-flex;
    }
    .dprv_info_panel:hover
    {
        display:block;
    }
    .dprv
    {
        border-collapse:collapse;border-spacing:0px;border:0px;border-style:solid;padding:0px;
    }
	.dprv tr td
    {
        border:0px;
        padding:0px;
        padding-bottom:2px;
        vertical-align:top;
    }
    #dprv_attribution, #dprv_integrity
    {
        display:block;
    }
    <?php
     calculateCSS();
}
add_action('amp_post_template_css', 'dprv_amp_add_css' );

function dprv_head()    // This does not get fired in an AMP page!
{
	$log = new DPLog();  
	$log->lwrite("dprv_wp_head starts");
    
/*
    $dprv_right_click_message = get_option('dprv_right_click_message');
	$dprv_record_IP = get_option('dprv_record_IP');
	echo ("
	<script type='text/javascript'>
		//<![CDATA[
        var dprv_record_IP = '" . $dprv_record_IP . "';
        var dprv_site_url = '" . site_url() . "';
        var dprv_ajax_url = '" . admin_url( 'admin-ajax.php' ) . "';
        var dprv_noRightClickMessage='" . addslashes($dprv_right_click_message) . "';
        var dprv_justDisplayed = 0;
    
		function dprv_DisplayAttributions(attribution_text)
		{
			document.getElementById(\"dprv_attribution\").innerHTML = attribution_text;
			document.getElementById(\"dprv_attribution\").title = \"" . __('Attributions - owner(s) of some content', 'dprv_cp') . "\";
			document.getElementById(\"dprv_attribution\").onmouseover = \"\";
		}
		function dprv_DisplayLicense(post_id)
		{
			document.getElementById('license_panel' + post_id).style.display='block';
			document.getElementById('license_panel' + post_id).style.zIndex='2';
		}
		function dprv_HideLicense(post_id)
		{
			document.getElementById('license_panel' + post_id).style.display='none';
		}
	    //]]>
	</script>");
    
*/    

	echo ('	<style type="text/css">	.dprv{border-collapse:collapse;border-spacing:0px;border:0px;border-style:solid;padding:0px;}
									.dprv tr td{border:0px;padding:0px;}
			</style>');
	dprv_populate_licenses();
}


function dprv_display_content($content)
{
	global $wpdb, $dprv_licenseIds, $dprv_licenseTypes, $dprv_licenseCaptions, $dprv_licenseAbstracts, $dprv_licenseURLs;
	$log = new DPLog();
	$dprv_post_id = get_the_ID();
	$log->lwrite("dprv_display_content starts for post/page " . $dprv_post_id);

    $dprv_amp_content = false;
	if (function_exists("is_amp_endpoint") && is_amp_endpoint())
	{
		$log->lwrite("This is AMP content");
		$dprv_amp_content = true;
        dprv_populate_licenses();  // do this here because AMP does not seem to trigger wp_head
	}

	// Find out context:
	//		In Excerpt (search pages, archive pages without manual excerpt, auto-generated 55-character excerpt text with HTML tags removed)
	//		Note manual excerpts do not get filtered through the_content()
	//		From post-template.php means in standard web-page display
	//		From feed.php - In a Feed 
	//
	$in_auto_excerpt = false;
	$normal_display = false;
	$counter = 0;
	$bt = debug_backtrace();
	foreach ($bt as $caller)
	{
		$search_result = array_search("wp_trim_excerpt", $caller);
		if ($search_result == "function")
		{
			$in_auto_excerpt = true;
		}
		if ($normal_display == false && is_array($caller) && isset($caller["file"]) && (strpos($caller["file"], "post-template.php") != false || strpos($caller["file"], "feed.php") != false))
		{
			$normal_display = true;
		}
		$counter++;
		if ($counter > 6)
		{
			break;
		}
	}
	if ($normal_display == false && $in_auto_excerpt == false)	// if not called from post-template or feed, probably called from some other plugin, or not in auto_excerpt don't add any copyright stuff
	{
		return $content;
	}

	// Do Data Integrity Check and get statement for the Digiprove notice
	$dprv_integrity_headline="";
	$dprv_integrity_message="";
	// TODO - Reinstate instruction below when implementing integrity checking
	//	dprv_integrity_statement($dprv_post_id, $dprv_integrity_headline, $dprv_integrity_message);

	if (($in_auto_excerpt == true || !is_singular()) && get_option('dprv_multi_post') == "No")
	{
		return $content;
	}

	// Remove old-style notice (if there is one there) and return the core information from it 
	dprv_strip_old_notice($content, $dprv_certificate_id, $dprv_utc_date_and_time, $dprv_digital_fingerprint, $dprv_certificate_url, $dprv_first_year);


	// a Digiprove Notice is required to append to content
	$dprv_notice = "";

	// Establish Copyright / ownership details 
	// Set Default Values to start with:
	$dprv_this_all_original = "Yes";
	$dprv_this_attributions = "";
	$dprv_this_license = get_option('dprv_license');
	$dprv_this_default_license = "Yes";
	$dprv_this_license_caption = "";
	$dprv_this_license_abstract = "";
	$dprv_this_license_url = "";
	$dprv_post_info = null;
	//$dprv_status_info = "";
	if (trim($dprv_post_id == ""))
	{
		return $content;
	}
	else
	{
		// If stuff is recorded specifically for this post, use that
		$sql="SELECT * FROM " . get_option('dprv_prefix') . "dprv_posts WHERE id = %d";
		$dprv_post_info = dprv_wpdb("get_row", $sql, $dprv_post_id);
	}
    //$log->lwrite("dprv_post_info for $dprv_post_id = $dprv_post_info - count is " . count($dprv_post_info));
	if (!is_null($dprv_post_info) && count($dprv_post_info) > 0)
	{
		$dprv_this_all_original = "No";
		if ($dprv_post_info["this_all_original"] == true)
		{
			$dprv_this_all_original = "Yes";
		}
		$dprv_this_attributions = $dprv_post_info["attributions"];

        $log->lwrite("dprv_this_all_original = $dprv_this_all_original");

		$dprv_this_default_license = "Yes";
		if ($dprv_post_info["using_default_license"] == false)		// Default license set to Yes trumps individual settings
		{
			$dprv_this_default_license = "No";
			$dprv_this_license = $dprv_post_info["license"];
		}
        $log->lwrite("dprv_this_default_license = $dprv_this_default_license");
        $log->lwrite("dprv_this_default_license = $dprv_this_default_license");
		
		$dprv_number = "" . intval($dprv_this_license);
		if ($dprv_number != $dprv_this_license && $dprv_post_info["using_default_license"] == false)	// Default license set to Yes trumps individual settings
		{
			$log->lwrite("custom license");
			$dprv_this_license_caption = $dprv_post_info["custom_license_caption"];
			$dprv_this_license_abstract = $dprv_post_info["custom_license_abstract"];
			$dprv_this_license_url = $dprv_post_info["custom_license_url"];
		}
		else
		{
			$log->lwrite("not custom license");
			for ($i=0; $i<count($dprv_licenseIds); $i++)
			{
				if ($dprv_this_license == $dprv_licenseIds[$i])
				{
					$dprv_this_license = $dprv_licenseTypes[$i];
					$dprv_this_license_caption = $dprv_licenseCaptions[$i];
					$dprv_this_license_abstract = $dprv_licenseAbstracts[$i];
					$dprv_this_license_url =  $dprv_licenseURLs[$i];
				}
			}
		}
	}
	else  // nothing recorded specifically for this post, fill out other license values unless license is None
	{
		if ($dprv_this_license != 0 && $dprv_this_license != '0')
		{
			for ($i=0; $i<count($dprv_licenseIds); $i++)
			{
				if ($dprv_this_license == $dprv_licenseIds[$i])
				{
					$dprv_this_license = $dprv_licenseTypes[$i];
					$dprv_this_license_caption = $dprv_licenseCaptions[$i];
					$dprv_this_license_abstract = $dprv_licenseAbstracts[$i];
					$dprv_this_license_url =  $dprv_licenseURLs[$i];
				}
			}
		}
	}
	$dprv_license_html = "";
	if (!is_null($dprv_post_info) && count($dprv_post_info) > 0 && $dprv_post_info["digiprove_this_post"] == true && $dprv_post_info["certificate_id"] != null && $dprv_post_info["certificate_id"] != "" && $dprv_post_info["certificate_id"] != false)
	{
		$log->lwrite("Digiprove certification has been recorded in dprv_posts table");

		$dprv_certificate_id = $dprv_post_info["certificate_id"];
		$dprv_utc_date_and_time = $dprv_post_info["cert_utc_date_and_time"];
		$dprv_digital_fingerprint = $dprv_post_info["digital_fingerprint"];
		$dprv_certificate_url = $dprv_post_info["certificate_url"];
		$dprv_first_year = $dprv_post_info["first_year"];
        $log->lwrite("dprv_this_license_caption = $dprv_this_license_caption");
        if ($dprv_amp_content)
        {
		    $dprv_notice = dprv_composeNoticeAMP($dprv_certificate_id, $dprv_utc_date_and_time, $dprv_digital_fingerprint, $dprv_certificate_url, false, $dprv_first_year, $dprv_this_license, $dprv_this_license_caption, $dprv_this_license_abstract, $dprv_this_license_url, $dprv_this_all_original, $dprv_this_attributions, $dprv_post_id, $dprv_license_html, $dprv_integrity_headline, $dprv_integrity_message);
            $content .=  $dprv_notice;
        }
        else
        {
		    $dprv_notice = dprv_composeNotice($dprv_certificate_id, $dprv_utc_date_and_time, $dprv_digital_fingerprint, $dprv_certificate_url, false, $dprv_first_year, $dprv_this_license, $dprv_this_license_caption, $dprv_this_license_abstract, $dprv_this_license_url, $dprv_this_all_original, $dprv_this_attributions, $dprv_post_id, $dprv_license_html, $dprv_integrity_headline, $dprv_integrity_message);
            $content .=  $dprv_notice;
       	    $content .= $dprv_license_html;
		}
	}
	else
	{
		$log->lwrite("there is no Digiprove cert in dprv_posts table");
		if ($dprv_certificate_id != false && $dprv_certificate_id != "")
		{
			$log->lwrite("but there was an old notice - will make a new one with variables from that");
			$dprv_notice = dprv_composeNotice($dprv_certificate_id, $dprv_utc_date_and_time, $dprv_digital_fingerprint, $dprv_certificate_url, false, $dprv_first_year, $dprv_this_license, $dprv_this_license_caption, $dprv_this_license_abstract, $dprv_this_license_url, $dprv_this_all_original, $dprv_this_attributions, $dprv_post_id, $dprv_license_html, $dprv_integrity_headline, $dprv_integrity_message);
			$content .= $dprv_notice;
           	$content .= $dprv_license_html;
		}
	}
	//$content .= $dprv_status_info;
	//$content .= $dprv_license_html;
	//$log->lwrite("content to be displayed:" . $content);
	return $content;
}

function dprv_footer()
{
	$log = new DPLog();  
	$log->lwrite("dprv_footer starts");
	
	if (get_option('dprv_footer') == "Yes")
	{
		echo (sprintf(__('All original content on these pages is fingerprinted and certified by %s', 'dprv_cp'), "<a href='//www.digiprove.com' target='_blank'>Digiprove</a>"));
	}
}
function dprv_integrity_statement($dprv_post_id, &$dprv_integrity_headline,  &$dprv_integrity_message)
{
	global $wpdb, $post;
	$log = new DPLog();
	$dprv_integrity_notice = "";
	$dprv_post_types = explode(',',get_option('dprv_post_types'));
	if (array_search($post->post_type, $dprv_post_types) === false)  // Is this a post type that is selected for Digiproving
	{
		return;
	}
	$post_type_label = $post->post_type; //default value

	$sql="SELECT * FROM " . get_option('dprv_prefix') . "dprv_posts WHERE id = " . $dprv_post_id;
	$dprv_post_info = $wpdb->get_row($sql, ARRAY_A);
	if (!is_null($dprv_post_info) && count($dprv_post_info) > 0)
	{
		if (get_option('dprv_html_integrity') == "Yes" || get_option('dprv_files_integrity') == "Yes")
		{
			if (($dprv_post_info["certificate_id"] != null && $dprv_post_info["certificate_id"] != "") || ($dprv_post_info["last_time_updated"] != null && $dprv_post_info["last_fingerprint"] != ""))
			{
				$dprv_last_update_timestamp = "";
				$dprv_last_digital_fingerprint = "";
				if ($dprv_post_info["last_time_updated"] != null)
				{
					$log->lwrite("dprv_post_info[last_time_updated] = " . $dprv_post_info["last_time_updated"]);
					$dprv_last_digital_fingerprint = $dprv_post_info["last_fingerprint"];
					$dprv_last_update_timestamp = $dprv_post_info["last_time_updated"];
				}
				else
				{
					$log->lwrite("dprv_post_info[last_time_updated] == null");
					$dprv_last_digital_fingerprint = $dprv_post_info["digital_fingerprint"];
					if ($dprv_post_info["last_time_digiproved"] != null)
					{
						$dprv_last_update_timestamp = $dprv_post_info["last_time_digiproved"];
					}
					else
					{
						$dprv_last_update_timestamp = strtotime($dprv_post_info["cert_utc_date_and_time"]);
					}
				}

				$digital_fingerprint = "";
				// Might need to get from db, but try this:
				$check_content = $post->post_content;
				//$log->lwrite("post->post_content=" . $post->post_content);
				$check_content = dprv_getRawContent($check_content, $digital_fingerprint);
				$dprv_html_integrity_headline = "";
				$dprv_html_integrity_message = "";
				$dprv_files_integrity_headline = "";
				$dprv_files_integrity_message = "";
				$integrity = true;
				$html_integrity = 0;
				if ($digital_fingerprint != "")
				{
					if (get_option('dprv_html_integrity') == "Yes")
					{
						if ($digital_fingerprint == $dprv_post_info["digital_fingerprint"])
						{
							$html_integrity = 1;
							$dprv_html_integrity_headline = __('HTML Certified &amp; Verified', 'dprv_cp');
							$dprv_html_integrity_message = sprintf(__('The HTML in this %s matches last Digiprove certification', 'dprv_cp'), $post_type_label);
						}
						else
						{
							if ($digital_fingerprint == $dprv_post_info["last_fingerprint"])
							{
								$html_integrity = 2;
								$dprv_html_integrity_headline = __('HTML Verified', 'dprv_cp');
								$dprv_html_integrity_message = sprintf(__('The HTML in this %s matches last recorded update', 'dprv_cp'), $post_type_label);
							}
							else
							{
								$integrity = false;
								$html_integrity = -1;
								$dprv_html_integrity_headline = __('HTML Tamper Alert', 'dprv_cp');
								$dprv_html_integrity_message = sprintf(__('The HTML in this %1$s (id %2$s) appears to have been changed from outside Wordpress. ', 'dprv_cp'), $post_type_label, $dprv_post_id);

								// if last wp modified time was more than 5 seconds after last update noted by this plugin
								// make the discrepancy a warning rather than a Red Tamper Alert (could be that this plugin was deactivated for a period)
								$dprv_wp_last_modified_time = strtotime($post->post_modified_gmt . " GMT");
								if (($dprv_wp_last_modified_time - $dprv_last_update_timestamp) > 5)
								{
									$html_integrity = -2;
									$dprv_html_integrity_headline = __('Check HTML', 'dprv_cp');
									$dprv_html_integrity_message = sprintf(__('The HTML in this %1$s (id %2$s) has been changed without Digiprove integrity checking - check whether it is correct. ', 'dprv_cp'), $post_type_label, $dprv_post_id);
								}
							}
						}
					}
				}

				$files_integrity = 0;

				if (function_exists("hash") && get_option('dprv_files_integrity') == "Yes")
					//&& $dprv_post_info["last_time_digiproved"] != null
					//&& $dprv_post_info["last_time_digiproved"] == $dprv_post_info["last_time_updated"] )
				{
					//global $dprv_blog_host;  // check maybe this is not required

					dprv_getContentFiles($dprv_post_id, $check_content, $content_files, $content_file_names, 50, $file_count, $total_url_count, false);
					if ($file_count > 0)
					{
						$log->lwrite("file count = " . $file_count . ", count(content_files) = " . count($content_files) . ", count(content_file_names) = " . count($content_file_names));
						if (Digiprove::parseContentFiles($error_message, $content_files, $content_file_table))
						{
							$log->lwrite("file count = " . $file_count . ", count(content_file_table) = " . count($content_file_table));
							if (dprv_verifyContentFiles($error_message, $dprv_post_id, $content_file_table, $match_results))
							{
								$log->lwrite("file count = " . $file_count . ", count(match_results) = " . count($match_results));
								$files_integrity = 2;
								if ($dprv_post_info["last_time_digiproved"] != null && $dprv_post_info["last_time_digiproved"] == $dprv_post_info["last_time_updated"])
								{
									$files_integrity = 1;
								}
								$dprv_files_integrity_headline = sprintf(__("%s files Verified", "dprv_cp"), $file_count);
							}
							else
							{
								$integrity = false;
								$files_integrity = ($file_count * -1);
								$dprv_integrity_headline = sprintf(__("%s files - Tamper Warning", "dprv_cp"), $file_count);
							}
							if (is_array($match_results))
							{
								
								$comma = "";
								foreach ($match_results as $filename=>$status)
								{
									$dprv_files_integrity_message .= $comma . $filename . ": " . $status;
									$comma = ", \n";
								}
							}
						}
					}
				}
				$dprv_integrity_headline = "";
				$dprv_integrity_message = "";
				if (get_option('dprv_html_integrity') == "Yes")
				{

					$dprv_integrity_headline .= $dprv_html_integrity_headline;
					$dprv_integrity_message .= $dprv_html_integrity_message;
					if (get_option('dprv_files_integrity') == "Yes")
					{
						$dprv_integrity_headline .= "; ";
						$dprv_integrity_message .= "; \n";
					}
				}
				if (get_option('dprv_files_integrity') == "Yes")
				{
					$dprv_integrity_headline .= $dprv_files_integrity_headline;
					$dprv_integrity_message .= $dprv_files_integrity_message;
					if ($files_integrity > 0 && $html_integrity > 0)	// Ensure a nice neat headline if everything good for display in notice
					{
						if ($html_integrity == 1 && $files_integrity == 1)
						{
							$dprv_integrity_headline = __('All content certified &amp; verified', 'dprv_cp');
						}
						else
						{
							$dprv_integrity_headline =  __('All content verified', 'dprv_cp');
						}
					}
				}
				if ($integrity == false)
				{
					// Do something here so that the same message is not sent out a thousand times a day, just once would be enough
					wp_mail(get_option('dprv_email_address'), $dprv_integrity_headline, $dprv_integrity_message);
					update_option('dprv_pending_message', $dprv_integrity_headline . ": " . $dprv_integrity_message);
					$dprv_integrity_headline = "";	// blank out, we don't want to state a negative in the notice
					$dprv_integrity_message = "";
				}
			}
		}
	}
}


function dprv_composeNotice($dprv_certificate_id, $dprv_utc_date_and_time, $dprv_digital_fingerprint, $dprv_certificate_url, $preview, $dprv_first_year, $licenseType, $licenseCaption, $licenseAbstract, $licenseURL, $all_original, $attributions, $dprv_post_id, &$dprv_license_html, $dprv_integrity_headline, $dprv_integrity_message)
{
	$log = new DPLog(); 
	$log->lwrite("composeNotice starts, licenseType = " . $licenseType);
	$DigiproveNotice = "";
	$dprv_full_name = trim(get_option('dprv_first_name') . " " . get_option('dprv_last_name'));
	$dprv_notice = get_option('dprv_notice');
	if (trim($dprv_notice) == "")
	{
		$dprv_notice = __('This content has been Digiproved', 'dprv_cp');
	}
	$dprv_notice = str_replace(" ", "&nbsp;", $dprv_notice);
	if ($dprv_certificate_id === false || $dprv_certificate_url === false)
	{
		$DigiproveNotice = "\r\n&copy; " . Date("Y") . ' ' . __('and certified by Digiprove', 'dprv_cp');
	}
	else
	{
		$dprv_container = "span";
		$dprv_boxmodel = "display:inline-block;";	// minimise width, enforce upper/lower margins, no line break
		$dprv_box_pad_top = " 3px";
		$dprv_box_pad_right = " 3px";
		$dprv_box_pad_bottom = " 3px";
		$dprv_box_pad_left = " 3px";

		if (($attributions != false && $attributions != "" && $all_original != "Yes") || ($licenseType != false && $licenseType != "" && $licenseType != "Not Specified"))
		{
			$dprv_container = "div";
			$dprv_boxmodel = "display:table;";		// minimise width, enforce upper/lower margins, line break
			$dprv_box_pad_top = " 3px";
			$dprv_box_pad_bottom = " 3px";
		}

		$dprv_notice_background = get_option('dprv_notice_background');
		$background_css = "background:transparent none;";
		if ($dprv_notice_background != "None")
		{
			$background_css = 'background:' . $dprv_notice_background . ' none;';
		}
		$dprv_notice_color = get_option('dprv_notice_color');
		if ($dprv_notice_color == false || $dprv_notice_color == "")
		{
			$dprv_notice_color = "#636363";
		}
		$dprv_hover_color = get_option('dprv_hover_color');
		if ($dprv_hover_color == false || $dprv_hover_color == "")
		{
			$dprv_hover_color = "#A35353";
		}
		
		$dprv_border_css = 'border:1px solid #BBBBBB;';
		$dprv_notice_border = get_option('dprv_notice_border');
		if ($dprv_notice_border == "None")
		{
			$dprv_border_css = 'border:0px;';
		}
		else
		{
			if ($dprv_notice_border != false || $dprv_notice_border != "Gray")
			{
				$dprv_border_css = 'border:1px solid ' . strtolower($dprv_notice_border) . ';';
			}
		}

		$dprv_font_size="11px";
		$dprv_image_scale = "";
		$dprv_a_height = "16px";
		$dprv_line_height = "16px";
		$dprv_line_margin = "2px";
		$dprv_img_valign = "-3px";
		$dprv_txt_valign = "1px";
		$dprv_outside_font_size = "13px";
		$dprv_notice_pad_left = "24px";
		$dprv_notice_pad_left0 = "8px";
		$notice_size = get_option('dprv_notice_size');
		if ($notice_size == "Small")
		{
			$dprv_font_size="10px";
			$dprv_txt_valign = "2px";
		}
		if ($notice_size == "Smaller")
		{
			$dprv_font_size="9px";
			$dprv_image_scale = 'width:12px;height:12px;';
			$dprv_a_height = "12px";
			$dprv_line_height = "12px";
			$dprv_line_margin = "3px";
			$dprv_img_valign = "0px";
			$dprv_txt_valign = "3px";
			$dprv_notice_pad_left = "18px";
			$dprv_notice_pad_left0 = "6px";
			$dprv_box_pad_top = " 2px";
			$dprv_box_pad_bottom = " 2px";
		}
		if ($dprv_container == "div")
		{
			$dprv_box_pad_bottom = " 1px";
		}
		$container_style = 'vertical-align:baseline; padding:' . $dprv_box_pad_top . $dprv_box_pad_right . $dprv_box_pad_bottom . $dprv_box_pad_left . '; margin-top:2px; margin-bottom:2px; border-collapse:separate; line-height:' . $dprv_line_height . ';float:none; font-family: Tahoma, MS Sans Serif; font-size:' . $dprv_outside_font_size . ';' . $dprv_border_css . $background_css . $dprv_boxmodel;

		// TODO - put date and time into locale of user
		/* translators: the language code that will be used for the lang attribute of the Digiprove notice - http://www.w3.org/TR/html4/struct/dirlang.html#adef-lang */
		$lang = __('en', 'dprv_cp');
		$DigiproveNotice = '<' . $dprv_container . ' id="dprv_cp-v' . DPRV_VERSION . '" lang="' . $lang . '" xml:lang="' . $lang . '" class="notranslate" style="' . $container_style . '" title="' . sprintf(__('certified %1$s by Digiprove certificate %2$s', 'dprv_cp'),  $dprv_utc_date_and_time, $dprv_certificate_id) . '" >';

		$DigiproveNotice .= '<a href="' . $dprv_certificate_url . '" target="_blank" rel="copyright" style="height:' . $dprv_a_height . '; line-height: ' . $dprv_a_height . '; border:0px; padding:0px; margin:0px; float:none; display:inline;background:transparent none; line-height:normal; font-family: Tahoma, MS Sans Serif; font-style:normal; font-weight:normal; font-size:' . $dprv_font_size . ';">';
		
		$DigiproveNotice .= '<img src="' . plugins_url("dp_seal_trans_16x16.png", __FILE__ ) . '" style="max-width:none !important;' . $dprv_image_scale . 'vertical-align:' . $dprv_img_valign . '; display:inline; border:0px; margin:0px; padding:0px; float:none; background:transparent none" alt="Digiprove seal"/>';

		$DigiproveNotice .= '<span style="font-family: Tahoma, MS Sans Serif; font-style:normal; font-size:' . $dprv_font_size . '; font-weight:normal; color:' . $dprv_notice_color . '; border:0px; float:none; display:inline; letter-spacing:normal; padding:0px; padding-left:' . $dprv_notice_pad_left0 . '; vertical-align:' . $dprv_txt_valign . ';margin-bottom:' . $dprv_line_margin . '" ';

		if ($preview != true)
		{
			$DigiproveNotice .= 'onmouseover="this.style.color=\'' . $dprv_hover_color . '\';" onmouseout="this.style.color=\'' . $dprv_notice_color . '\';"';
		}
		$DigiproveNotice .= '>' . $dprv_notice;
		$dprv_c_notice = get_option('dprv_c_notice');
		if ($dprv_c_notice != "NoDisplay")
		{
			$dprv_year = Date('Y');   // default is this year
			// Extract year from date_and_time
			$posB = stripos($dprv_utc_date_and_time, " UTC");
			if ($posB != false && $posB > 13)
			{
				$dprv_year = substr($dprv_utc_date_and_time, $posB-13, 4);  // This should work if HH:MM:SS always has length of 8
			}
			if (trim($dprv_first_year) != "" && $dprv_year != $dprv_first_year)
			{
				$dprv_year = $dprv_first_year . "-" . $dprv_year;
			}
			$DigiproveNotice .= '&nbsp;&copy;&nbsp;' . $dprv_year;
			if ($dprv_c_notice == "DisplayAll")
			{
				$dprv_copyright_owner = $dprv_full_name;
				if (get_option('dprv_submitter_has_copyright') == "Yes")
				{
					$dprv_post_object = get_post($dprv_post_id);
					if (is_object($dprv_post_object) && isset($dprv_post_object->post_author))

					{
						$dprv_post_author = $dprv_post_object->post_author;
						$dprv_author_object = get_user_by('id', $dprv_post_author);
						$dprv_copyright_owner = trim($dprv_author_object->first_name . ' ' . $dprv_author_object->last_name);
					}
				}
				$DigiproveNotice .= '&nbsp;' . 	str_replace(" ", "&nbsp;", htmlspecialchars(stripslashes($dprv_copyright_owner), ENT_QUOTES, 'UTF-8'));
			}
		}
		$DigiproveNotice .= '</span></a>';

		$span_style = "font-family: Tahoma, MS Sans Serif; font-style:normal; display:block; font-size:" . $dprv_font_size . "; font-weight:normal; color:" . $dprv_notice_color . "; border:0px; float:none; text-align:left; text-decoration:none; letter-spacing:normal; line-height:" . $dprv_a_height . "; vertical-align:" . $dprv_txt_valign . "; padding:0px; padding-left:" . $dprv_notice_pad_left . ";margin-bottom:" . $dprv_line_margin . ";";
		$mouseover = "";
		if ($preview != true)
		{
			$mouseover = 'onmouseover="this.style.color=\'' . $dprv_hover_color . '\';" onmouseout="this.style.color=\'' . $dprv_notice_color . '\';"';
		}
		if ($dprv_integrity_headline != false && $dprv_integrity_headline != "")
		{
			$DigiproveNotice .= "<div id=\"dprv_integrity\" style=\"" . $span_style . "\" ";
			$DigiproveNotice .= "title=\"" . $dprv_integrity_message . "\">";
			$DigiproveNotice .=  __("Content integrity", "dprv_cp") . ":&nbsp;" . $dprv_integrity_headline . "</div>";
		}
		if ($attributions != false && $attributions != "" && $all_original != "Yes")
		{
			$DigiproveNotice .= "<div id=\"dprv_attribution\" style=\"" . $span_style . "\" ";
			if (strlen($attributions) < 45)
			{
				$DigiproveNotice .= "title=\"" . __("Attributions - owner(s) of some content", "dprv_cp") . "\">";
				$DigiproveNotice .=  __("Acknowledgements", "dprv_cp") . ":&nbsp;" . nl2HTML(htmlspecialchars(stripslashes($attributions), ENT_QUOTES, 'UTF-8')) . "</div>";
			}
			else
			{
				$DigiproveNotice .= "title=\"" . __("Attributions - owner(s) of some content - click to see full text", "dprv_cp") . "\" onclick=\"dprv_DisplayAttributions('" . __("Acknowledgements", "dprv_cp") . ":&nbsp;" . nl2HTML(htmlspecialchars($attributions, ENT_QUOTES, 'UTF-8')) . "')\" " . $mouseover . ">";
				$DigiproveNotice .=  __("Acknowledgements", "dprv_cp") . ":&nbsp;" . nl2HTML(htmlspecialchars(stripslashes(substr($attributions, 0, 37)), ENT_QUOTES, 'UTF-8')) . __(" more...", "dprv_cp") . "</div>";
			}
		}
		//$log->lwrite("licenseType = " . $licenseType . ", licenseCaption=" . $licenseCaption);
		if ($licenseType != false && $licenseType != "" && $licenseType != "Not Specified")
		{
			$DigiproveNotice .= "<a title='" . __("Click to see details of license", "dprv_cp") . "' href=\"javascript:dprv_DisplayLicense('" . $dprv_post_id . "')\" style=\"" . $span_style . "\" " . $mouseover . " target=\"_self\">";
			$DigiproveNotice .= $licenseCaption;
			$DigiproveNotice .= "</a>";
			// Need to replace transparency with inversion of text color (as license_panel is a layer):
			if (strpos($background_css, "transparent") != false)
			{
				$t1 = '0123456789ABCDEF#';
				$t2 = '89ABCDEF01234567#';
				$w_color = strtoupper($dprv_notice_color);
				$background_color = "";
				for ($i=0; $i<strlen($w_color); $i++)
				{
					$pos = strpos($t1, substr($w_color, $i,1));
					$background_color .= substr($t2, $pos,1);
				}
				$background_css = 'background:' . $background_color . ' none; opacity:0.9; filter:alpha(opacity=90);';
				//$log->lwrite("calculated background color of " . $background_color . " from foreground " . $dprv_notice_color);
			}
			$dprv_license_html = '<div id="license_panel' . $dprv_post_id . '" style="position: absolute; display:none ; font-family: Tahoma, MS Sans Serif; font-style:normal; font-size:' . $dprv_font_size . '; font-weight:normal; color:' . $dprv_notice_color . ';' . $dprv_border_css . ' float:none; max-width:640px; text-decoration:none; letter-spacing:normal; line-height:' . $dprv_line_height . '; vertical-align:' . $dprv_txt_valign . '; padding:0px;' . $background_css . '">';
			$dprv_license_html .= '<table class="dprv" style="line-height:17px;margin:0px;background-color:transparent;font-family: Tahoma, MS Sans Serif; font-style:normal; font-weight:normal; font-size:' . $dprv_font_size . '; color:' . $dprv_notice_color . '"><tbody>';
			$dprv_license_html .= '<tr><td colspan="2" style="background-color:transparent;border:0px;font-weight:bold;padding:0px;padding-left:6px; text-align:left">' . __("Original content here is published under these license terms", "dprv_cp") . ':</td><td style="width:20px;background-color:transparent;border:0px;padding:0px"><span style="float:right; background-color:black; color:white; width:20px; text-align:center; cursor:pointer" onclick="dprv_HideLicense(\'' . $dprv_post_id . '\')">&nbsp;X&nbsp;</span></td></tr>';
			$dprv_license_html .= '<tr><td colspan="3" style="height:4px;padding:0px;background-color:transparent;border:0px"></td></tr>';
			$dprv_license_html .= '<tr><td style="width:130px;background-color:transparent;padding:0px;padding-left:4px;border:0px; text-align:left">' . __('License Type', 'dprv_cp') . ':</td><td style="width:300px;background-color:transparent;border:0px;padding:0px; text-align:left">' . htmlspecialchars(stripslashes($licenseType), ENT_QUOTES, "UTF-8") . '</td><td style="border:0px; background-color:transparent"></td></tr>';
			$dprv_license_html .= '<tr><td colspan="3" style="height:4px;background-color:transparent;padding:0px;border:0px"></td></tr>';
			$dprv_license_html .= '<tr><td style="background-color:transparent;padding:0px;padding-left:4px;border:0px; vertical-align:top; text-align:left">' . __('License Summary', 'dprv_cp') . ':</td><td colspan="2" style="background-color:transparent;border:0px;padding:0px; vertical-align:top; text-align:left">' . htmlspecialchars(stripslashes($licenseAbstract), ENT_QUOTES, "UTF-8") . '</td></tr>';
			if ($licenseURL != '')
			{
				$dprv_license_html .= '<tr><td colspan="3" style="height:4px;background-color:transparent;padding:0px;border:0px"></td></tr>';
				$dprv_license_html .= '<tr><td style="background-color:transparent;padding:0px;padding-left:4px;border:0px; text-align:left">' . __('License URL', 'dprv_cp') . ':</td><td colspan="2" style="background-color:transparent;border:0px;padding:0px; text-align:left"><a href="' . htmlspecialchars(stripslashes($licenseURL), ENT_QUOTES, "UTF-8") . '" target="_blank" rel="license">' . htmlspecialchars(stripslashes($licenseURL), ENT_QUOTES, "UTF-8") . '</a></td></tr>';
			}

			$dprv_license_html .= '</tbody></table></div>';
		}
		$DigiproveNotice .= '<!--' . $dprv_digital_fingerprint . '-->';
		$DigiproveNotice .= '</' . $dprv_container . '>';
	}
    $log->lwrite($DigiproveNotice);
	return $DigiproveNotice;
}
// Replace line breaks and carriage returns with HTML new line
function nl2HTML($str)
{
	$str = str_replace("\r\n", "<br/>", $str);
	$str = str_replace("\n\r", "<br/>", $str);
	$str = str_replace("\n", "<br/>", $str);
	return str_replace("\r", "<br/>", $str);
}
function calculateCSS()  // For use in amp-custom css
{
	$log = new DPLog(); 
	$log->lwrite("calculateCSS() starts");
    $dprv_notice_div = ".dprv_notice_div{";
    $dprv_notice_text = ".dprv_notice_text{";
    $dprv_notice_a = ".dprv_notice_a{";
    $dprv_notice_a_hover = ".dprv_notice_div a:hover{";
    $dprv_notice_amp_img = ".dprv_notice_amp-img{";
    $dprv_notice_span = ".dprv_notice_span{";
    $dprv_attr_integrity = "#dprv_attribution, #dprv_integrity{";
    $dprv_info_panel = ".dprv_info_panel{";
    $dprv_license_a = ".dprv_license_a{";
    $dprv_icon = ".dprv_icon{";
    $dprv_table = ".dprv{";
    $dprv_anchors = "a, a:visited{";
    
	$dprv_box_pad_top = " 2px";
	$dprv_box_pad_right = " 3px";
	$dprv_box_pad_bottom = " 3px";
	$dprv_box_pad_left = " 3px";
    
   	$dprv_notice_color = get_option('dprv_notice_color');
	if ($dprv_notice_color == false || $dprv_notice_color == "")
	{
		$dprv_notice_color = "#636363";
	}
    $dprv_anchors .= "color:" . $dprv_notice_color . ";";
    //$dprv_license_a .= "color:" . $dprv_notice_color . ";";
    $dprv_notice_text .= "color:" . $dprv_notice_color . ";";
    
    $dprv_icon .= "fill:" . $dprv_notice_color . ";";
    
    $dprv_notice_background = get_option('dprv_notice_background');
	$background_css = 'background:' . $dprv_notice_background . ' none;';
    $info_background_css = $background_css;
	if ($dprv_notice_background == "None")
	{
    	$background_css = "background:transparent none;";
    
        // For license panel, need to replace transparency with inversion of text color (as license_panel is a layer):
		$t1 = '0123456789ABCDEF#';
		$t2 = '89ABCDEF01234567#';
		$w_color = strtoupper($dprv_notice_color);
		$background_color = "";
		for ($i=0; $i<strlen($w_color); $i++)
		{
			$pos = strpos($t1, substr($w_color, $i,1));
			$background_color .= substr($t2, $pos,1);
		}
		$info_background_css = 'background:' . $background_color . ' none; opacity:0.9; filter:alpha(opacity=90);';
    }
    $dprv_notice_div .= $background_css;
    $dprv_info_panel .= $info_background_css;
    
	$dprv_hover_color = get_option('dprv_hover_color');
	if ($dprv_hover_color == false || $dprv_hover_color == "")
	{
		$dprv_hover_color = "#A35353";
	}
    $dprv_notice_a_hover .= "color:" . $dprv_hover_color . ";";
		
	$dprv_border_css = 'border:1px solid #BBBBBB;';
	$dprv_notice_border = get_option('dprv_notice_border');
	if ($dprv_notice_border == "None")
	{
		$dprv_border_css = 'border:0px;';
	}
	else
	{
		if ($dprv_notice_border != false || $dprv_notice_border != "Gray")
		{
			$dprv_border_css = 'border:1px solid ' . strtolower($dprv_notice_border) . ';';
		}
	}
    $dprv_notice_div .= $dprv_border_css;
    $dprv_info_panel .= $dprv_border_css;
    
	$dprv_icon_size="16px";
	$dprv_font_size="11px";
	$dprv_a_height = "16px";
	$dprv_line_height = "16px";
	$dprv_margin_bottom = "2px";
	$dprv_img_valign = "-3px";
	$dprv_txt_valign = "1px";
	$dprv_outside_font_size = "13px";
	$dprv_notice_pad_left = "24px";
	$dprv_notice_pad_left0 = "8px";
	$notice_size = get_option('dprv_notice_size');
	if ($notice_size == "Small")
	{
		$dprv_font_size="10px";
		$dprv_txt_valign = "2px";
	}
	if ($notice_size == "Smaller")
	{
		$dprv_icon_size="12px";
        $dprv_font_size="9px";
		$dprv_a_height = "12px";
		$dprv_line_height = "12px";
		$dprv_margin_bottom = "3px";
		$dprv_img_valign = "0px";
		$dprv_txt_valign = "3px";
		$dprv_notice_pad_left = "18px";
		$dprv_notice_pad_left0 = "6px";
		$dprv_box_pad_top = " 2px";
		$dprv_box_pad_bottom = " 2px";
	}
    
    $dprv_notice_text .=  "font-size:" . $dprv_font_size . ";";
    $dprv_notice_text .= "line-height:" . $dprv_line_height . ";";

    $dprv_icon .= "width:" . $dprv_icon_size . ";height:" . $dprv_icon_size . ";";
    
    $dprv_notice_a .= "height:" . $dprv_a_height . ";line-height:" . $dprv_line_height . ";";

    $dprv_notice_div .= "line-height:" . $dprv_line_height . ";";
    $dprv_info_panel .= "line-height:" . $dprv_line_height . ";";

    $dprv_notice_span .= "margin-bottom:" . $dprv_margin_bottom . ";";

    $dprv_info_panel .= "vertical-align:" . $dprv_txt_valign . ";";
 
    $dprv_notice_div .= "font-size:" . $dprv_outside_font_size . ";";

    $dprv_notice_span .= "padding-left:" . $dprv_notice_pad_left0 . ";";
    $dprv_attr_integrity .= "padding-left:" . $dprv_notice_pad_left . ";";
    $dprv_license_a .= "padding-left:" . $dprv_notice_pad_left . ";";
    
    $dprv_notice_div .= "padding:" . $dprv_box_pad_top . $dprv_box_pad_right . $dprv_box_pad_bottom . $dprv_box_pad_left;
    $dprv_info_panel .= "padding:" . $dprv_box_pad_top . $dprv_box_pad_right . $dprv_box_pad_bottom . $dprv_box_pad_left;
   
    $dprv_notice_div .= "}\n";
    $dprv_notice_text .= "}\n";
    $dprv_notice_a .= "}\n";
    $dprv_notice_a_hover .= "}\n";
    $dprv_notice_amp_img .= "}\n";
    $dprv_notice_span  .= "}\n";
    $dprv_license_a  .= "}\n";
    $dprv_icon  .= "}\n";
    $dprv_info_panel  .= "}\n";
    $dprv_table .= "}\n";
    $dprv_anchors .= "}\n";
    $dprv_attr_integrity .= "}\n";
    $retval = $dprv_notice_div . $dprv_notice_text . $dprv_notice_a . $dprv_notice_a_hover . $dprv_notice_amp_img . $dprv_notice_span . $dprv_license_a . $dprv_icon . $dprv_info_panel. $dprv_table . $dprv_anchors . $dprv_attr_integrity;
	$log->lwrite("retval=" + $retval);

    echo ($retval);    
        
    if (get_option('dprv_frustrate_copy') == 'Yes')
    {
        // Prevents user-select - without JS we cannot do better than this
        echo ("body
	            {-webkit-user-select:none; -moz-user-select:none; -ms-user-select:none; user-select:none;}
	            input, textarea, select, option, optgroup, button, canvas
	            {-webkit-user-select:text; -moz-user-select:text; -ms-user-select:text;user-select:text;}");
    }
}

function dprv_composeNoticeAMP($dprv_certificate_id, $dprv_utc_date_and_time, $dprv_digital_fingerprint, $dprv_certificate_url, $preview, $dprv_first_year, $licenseType, $licenseCaption, $licenseAbstract, $licenseURL, $all_original, $attributions, $dprv_post_id, &$dprv_license_html, $dprv_integrity_headline, $dprv_integrity_message)
{
	$log = new DPLog(); 
	$log->lwrite("composeNotice starts, licenseType = " . $licenseType);
	$DigiproveNotice = "";
	$dprv_full_name = trim(get_option('dprv_first_name') . " " . get_option('dprv_last_name'));
	$dprv_notice = get_option('dprv_notice');
	if (trim($dprv_notice) == "")
	{
		$dprv_notice = __('This content has been Digiproved', 'dprv_cp');
	}
	$dprv_notice = str_replace(" ", "&nbsp;", $dprv_notice);
	if ($dprv_certificate_id === false || $dprv_certificate_url === false)
	{
		$DigiproveNotice = "\r\n&copy; " . Date("Y") . ' ' . __('and certified by Digiprove', 'dprv_cp');
	}
	else
	{
		$dprv_container = "span";
        $dprv_extra_class = "";
		if (($attributions != false && $attributions != "" && $all_original != "Yes") || ($licenseType != false && $licenseType != "" && $licenseType != "Not Specified"))
		{
			$dprv_container = "div";
            $dprv_extra_class = " dprv_notice_tab";
		}
		
		$dprv_image_scale = 'width="16" height="16"';
		$notice_size = get_option('dprv_notice_size');
		if ($notice_size == "Smaller")
		{
			$dprv_image_scale = 'width="12" height="12"';
		}

        // TODO - put date and time into locale of user
		/* translators: the language code that will be used for the lang attribute of the Digiprove notice - http://www.w3.org/TR/html4/struct/dirlang.html#adef-lang */
		$lang = __('en', 'dprv_cp');
		$DigiproveNotice = '<' . $dprv_container . ' id="dprv_cp-v' . DPRV_VERSION . '" lang="' . $lang . '" class="notranslate dprv_notice_div' . $dprv_extra_class . '" title="' . sprintf(__('certified %1$s by Digiprove certificate %2$s', 'dprv_cp'),  $dprv_utc_date_and_time, $dprv_certificate_id) . '" >';

		$DigiproveNotice .= '<a class="dprv_notice_a" href="' . $dprv_certificate_url . '" target="_blank" rel="copyright">';
		
		$DigiproveNotice .= '<amp-img class="dprv_notice_amp_img" src="' . plugins_url("dp_seal_trans_16x16.png", __FILE__ ) . '" ' . $dprv_image_scale . ' alt="Digiprove seal"></amp-img>';

		$DigiproveNotice .= '<span class="dprv_notice_span dprv_notice_text">' . $dprv_notice;
		$dprv_c_notice = get_option('dprv_c_notice');
		if ($dprv_c_notice != "NoDisplay")
		{
			$dprv_year = Date('Y');   // default is this year
			// Extract year from date_and_time
			$posB = stripos($dprv_utc_date_and_time, " UTC");
			if ($posB != false && $posB > 13)
			{
				$dprv_year = substr($dprv_utc_date_and_time, $posB-13, 4);  // This should work if HH:MM:SS always has length of 8
			}
			if (trim($dprv_first_year) != "" && $dprv_year != $dprv_first_year)
			{
				$dprv_year = $dprv_first_year . "-" . $dprv_year;
			}
			$DigiproveNotice .= '&nbsp;&copy;&nbsp;' . $dprv_year;
			if ($dprv_c_notice == "DisplayAll")
			{
				$dprv_copyright_owner = $dprv_full_name;
				if (get_option('dprv_submitter_has_copyright') == "Yes")
				{
					$dprv_post_object = get_post($dprv_post_id);
					if (is_object($dprv_post_object) && isset($dprv_post_object->post_author))

					{
						$dprv_post_author = $dprv_post_object->post_author;
						$dprv_author_object = get_user_by('id', $dprv_post_author);
						$dprv_copyright_owner = trim($dprv_author_object->first_name . ' ' . $dprv_author_object->last_name);
					}
				}
				$DigiproveNotice .= '&nbsp;' . 	str_replace(" ", "&nbsp;", htmlspecialchars(stripslashes($dprv_copyright_owner), ENT_QUOTES, 'UTF-8'));
			}
		}
		$DigiproveNotice .= '</span></a>';

		if ($dprv_integrity_headline != false && $dprv_integrity_headline != "")
		{
			$DigiproveNotice .= "<div id=\"dprv_integrity\" class=\"dprv_notice_span dprv_notice_text\" ";
			$DigiproveNotice .= "title=\"" . $dprv_integrity_message . "\">";
			$DigiproveNotice .=  __("Content integrity", "dprv_cp") . ":&nbsp;" . $dprv_integrity_headline . "</div>";
		}
		if ($attributions != false && $attributions != "" && $all_original != "Yes")
		{
			$DigiproveNotice .= '<div id="dprv_attribution" class="dprv_notice_text" ';
			if (strlen($attributions) < 37)
			{
				$DigiproveNotice .= 'title="' . __('Attributions - owner(s) of some content', 'dprv_cp') . '">';
				$DigiproveNotice .=  __('Acknowledgements', 'dprv_cp') . ':&nbsp;' . nl2HTML(htmlspecialchars(stripslashes($attributions), ENT_QUOTES, 'UTF-8'));
			}
			else
			{
				$DigiproveNotice .= 'on="tap:attribution-lightbox' . $dprv_post_id . '" role="navigation" tabindex="" title="' . __('Acknowledgements', 'dprv_cp') . ': ' . stripslashes($attributions) . '">';
				$DigiproveNotice .=  __('Acknowledgements', 'dprv_cp') . ':&nbsp;' . nl2HTML(htmlspecialchars(stripslashes(substr($attributions, 0, 37)), ENT_QUOTES, 'UTF-8')) . ' ... ';
                $DigiproveNotice .= '<svg class="dprv_icon" fill="#000000" height="18" viewBox="0 0 24 24" width="18" xmlns="http://www.w3.org/2000/svg"><path d="M0 0h24v24H0z" fill="none"/><path d="M11 17h2v-6h-2v6zm1-15C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zM11 9h2V7h-2v2z"/></svg>';
                $DigiproveNotice .= '<amp-lightbox id="attribution-lightbox' . $dprv_post_id . '" role="navigation" tabindex="" layout="nodisplay" on="tap:attribution-lightbox' . $dprv_post_id . '.close">';
			    $DigiproveNotice .= '<div class="dprv_info_panel dprv_notice_text">' . __("Acknowledgements", "dprv_cp") . ': ' . stripslashes($attributions) . '</div>';
                $DigiproveNotice .= '</amp-lightbox>';
			}
            $DigiproveNotice .= "</div>";
		}

        if ($licenseType != false && $licenseType != "" && $licenseType != "Not Specified")
		{
            $DigiproveNotice .= '<span class="dprv_license_a dprv_notice_text"  role="navigation" tabindex="" on="tap:license-lightbox' . $dprv_post_id . '">';
			$DigiproveNotice .= $licenseCaption . '&nbsp;&nbsp;';
            $DigiproveNotice .= '<svg class="dprv_icon" fill="#000000" height="18" viewBox="0 0 24 24" width="18" xmlns="http://www.w3.org/2000/svg"><path d="M0 0h24v24H0z" fill="none"/><path d="M11 17h2v-6h-2v6zm1-15C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zM11 9h2V7h-2v2z"/></svg>';
            $DigiproveNotice .= '</span>';
            $dprv_license_html = '<amp-lightbox id="license-lightbox' . $dprv_post_id . '"  role="navigation" tabindex="" layout="nodisplay" on="tap:license-lightbox' . $dprv_post_id . '.close">';
            $dprv_license_html .= '<div class="dprv_info_panel dprv_notice_text">';
			$dprv_license_html .= '<table class="dprv"><tbody>';
			$dprv_license_html .= '<tr><td colspan="3">' . __("Original content here is published under these license terms", "dprv_cp") . ':</td></tr>';
			$dprv_license_html .= '<tr><td>' . __('License&nbsp;Type', 'dprv_cp') . ':&nbsp;</td><td>' . htmlspecialchars(stripslashes($licenseType), ENT_QUOTES, "UTF-8") . '</td><td></td></tr>';
			$dprv_license_html .= '<tr><td>' . __('Summary', 'dprv_cp') . ':&nbsp;</td><td colspan="2">' . htmlspecialchars(stripslashes($licenseAbstract), ENT_QUOTES, "UTF-8") . '</td></tr>';
			if ($licenseURL != '')
			{
				$dprv_license_html .= '<tr><td>' . __('License&nbsp;URL', 'dprv_cp') . ':&nbsp;</td><td colspan="2"><a href="' . htmlspecialchars(stripslashes($licenseURL), ENT_QUOTES, "UTF-8") . '" target="_blank" rel="license">' . htmlspecialchars(stripslashes($licenseURL), ENT_QUOTES, "UTF-8") . '</a></td></tr>';
			}
			$dprv_license_html .= '</tbody></table></div>';
            $dprv_license_html .= '</amp-lightbox>';
         
            $DigiproveNotice .= $dprv_license_html;
		}
		$DigiproveNotice .= '<!--' . $dprv_digital_fingerprint . '-->';
		$DigiproveNotice .= '</' . $dprv_container . '>';
	}
	return $DigiproveNotice;
}

?>