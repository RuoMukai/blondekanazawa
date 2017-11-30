<?php
// FUNCTIONS CALLED WHEN CREATING, OR EDITING POSTS OR PAGES
// TODO Cast with this_all_original, and using_default_license - done
// TODO deal with all other booleans in db
// TODO specify the required %d %s parameters
// $wpdb->insert to include format array
// $wpdb->update to include format array and where format array
// $wpdb->delete to include where format array

include_once('copyright_proof_integrity.php');						// Functions for Integrity Checking
function dprv_postbox()
{
	$dprv_post_types = explode(',',get_option('dprv_post_types'));
	foreach ($dprv_post_types as $dprv_post_type)
	{
	    add_meta_box('dprv_post_box', __('Copyright / Ownership / Licensing', 'dprv_cp'), 'dprv_show_postbox', $dprv_post_type);
	}
}

function dprv_show_postbox($post_info)
{
	global $wpdb, $dprv_licenseIds, $dprv_licenseTypes, $dprv_licenseCaptions, $dprv_licenseAbstracts, $dprv_licenseURLs, $post_id;
 	$log = new DPLog();  
	$log->lwrite("dprv_show_postbox starts");
	print('<script type="text/javascript">
			//<![CDATA[
			');
	print ('
			var dprv_literals = new Array();
			dprv_literals["None"] = \'' . __("None", "dprv_cp") . '\';
			//]]>
			</script>
			');
	$script_name = pathinfo($_SERVER['PHP_SELF'], PATHINFO_BASENAME);
	$posDot = strrpos($script_name,'.');
	if ($posDot != false)
	{
		$script_name = substr($script_name, 0, $posDot);
	}

	// SET START VALUES

	// First, set up default values (which will apply if new post or nothing recorded already)
	$dprv_digiprove_this_post = "Yes";
	$dprv_this_all_original = "Yes";
	$dprv_this_attributions = "";
	$dprv_this_default_license = "Yes";
	$dprv_this_license = get_option('dprv_license');
	$dprv_this_license_type = "";
	$dprv_this_license_caption = "";
	$dprv_this_license_abstract = "";
	$dprv_this_license_url = "";
	$dprv_this_custom_license = "No";
	if ($dprv_this_license != 0)
	{
		for ($i=0; $i<count($dprv_licenseIds); $i++)
		{
			if (($dprv_this_license) == $dprv_licenseIds[$i])
			{
				$dprv_this_license_type = $dprv_licenseTypes[$i];
				$dprv_this_license_caption = $dprv_licenseCaptions[$i];
				$dprv_this_license_abstract = $dprv_licenseAbstracts[$i];
				$dprv_this_license_url =  $dprv_licenseURLs[$i];
			}
		}
	}
	$dprv_last_digiprove_info = __("Last Digiproved:", "dprv_cp") . " " . __("Never", "dprv_cp");
	if (stripos($post_info->post_content, "Digiprove_Start") !== false)
	{
		$dprv_last_digiprove_info = "";		// Don't display anything rather than go parsing the notice
	}

	// Secondly, get values previously assigned to this post (if set)
	if ($script_name == "post" || $script_name == "page")	// "post" (or "page" in earlier versions of wp) means we are displaying form for editing an existing post/page ("post-new" is for a new post)
	{
		// Ensure $post_id is set to correct value (have seen it being incorrect)
		if (!isset($post_id))
		{
			$log->lwrite("post id not set, using  post_info->ID ($post_info->ID)");
			$post_id = $post_info->ID;
		}
		// Added this else block at 2.14 as discovered quite often $post_id erroneously set to a value of 0:
		else
		{
			if ($post_id == 0 && $post_info->ID != 0)
			{
				$log->lwrite("replacing post id of 0 with post_info->ID ($post_info->ID)");
				$post_id = $post_info->ID;
			}
		}
		$log->lwrite("Edit $script_name, global post_id = " . $post_id);
		
		$sql="SELECT * FROM " . get_option('dprv_prefix') . "dprv_posts WHERE id = %d";
		$dprv_post_info = dprv_wpdb("get_row", $sql, $post_id);
		if (!is_null($dprv_post_info) && count($dprv_post_info) > 0)
		{
			//if ($dprv_post_info["digiprove_this_post"] == true)
			if ((bool)$dprv_post_info["digiprove_this_post"] == true)
			{
				$dprv_digiprove_this_post = "Yes";
			}
			else
			{
				$dprv_digiprove_this_post = "No";
			}
			//if ($dprv_post_info["this_all_original"] == true)
			if ((bool)$dprv_post_info["this_all_original"] == true)
			{
				$dprv_this_all_original = "Yes";
			}
			else
			{
				$dprv_this_all_original = "No";
			}
			$dprv_this_attributions = $dprv_post_info["attributions"];
			//if ($dprv_post_info["using_default_license"] == true)
			if ((bool)$dprv_post_info["using_default_license"] == true)
			{
				$dprv_this_default_license = "Yes";
			}
			else
			{
				$dprv_this_default_license = "No";
			}

			if ($dprv_this_default_license != "Yes")										// Default license set to Yes trumps individual settings
			{
				$dprv_this_license = $dprv_post_info["license"];
			}
			$dprv_this_license_type = "";
			$dprv_this_license_caption = __("Some Rights Reserved", "dprv_cp");
			$dprv_this_license_abstract = "";
			$dprv_this_license_url = "";

			$dprv_number = "" . intval($dprv_this_license);									// If dprv_this_license == a non-numeric value 
			if ($dprv_number != $dprv_this_license && $dprv_this_default_license != "Yes")	// and it is not trumped by default license being set to yes, this must be a custom license
			{
				$dprv_this_custom_license = "Yes";
				$dprv_this_license_type = $dprv_this_license; 
				$dprv_this_license_caption = $dprv_post_info["custom_license_caption"];
				$dprv_this_license_abstract = $dprv_post_info["custom_license_abstract"];
				$dprv_this_license_url = $dprv_post_info["custom_license_url"];
			}
			else
			{
				for ($i=0; $i<count($dprv_licenseIds); $i++)
				{
					if (($dprv_this_license) == $dprv_licenseIds[$i])
					{
						$dprv_this_license_type = $dprv_licenseTypes[$i]; 
						$dprv_this_license_caption = $dprv_licenseCaptions[$i];
						$dprv_this_license_abstract = $dprv_licenseAbstracts[$i];
						$dprv_this_license_url =  $dprv_licenseURLs[$i];
					}
				}
			}
			if ($dprv_post_info["certificate_id"] != null && $dprv_post_info["certificate_id"] != "")
			{
				$dprv_last_digiprove_info = __("Last Digiproved:", "dprv_cp") . " " . $dprv_post_info["cert_utc_date_and_time"] . ", " . __("certificate", "dprv_cp") . " " . $dprv_post_info["certificate_id"];
/*
				$dprv_last_digiprove_info .= " fingerprint=" . $dprv_post_info["digital_fingerprint"];
				$digital_fingerprint = "";
				$content = $post_info->post_content;
				$rawContent = dprv_getRawContent($content, $digital_fingerprint);
				if ($digital_fingerprint != "")
				{
					if ($digital_fingerprint == $dprv_post_info["digital_fingerprint"])
					{
						$dprv_last_digiprove_info .= "<br/>" . __("STATUS:", "dprv_cp") . " <span style=\"color:green; font-weight:bold\">HTML Verified OK - No Tamper Evident</span>";
					}
					else
					{
						$dprv_last_digiprove_info .= "<br/>" . __("STATUS:", "dprv_cp") . " <span style=\"color:red; font-weight:bold\">HTML Tamper Warning</span>";
					}
				}
*/
			}
			else
			{
				// This post has not previously been Digiproved
			}
		}
	}
	else
	{
		$log->lwrite("script not = post or page, post_id = " . $post_id . "; expect this is post-new");
		// Added this if block at 2.14 to test above assumption
		if ($script_name != "post-new")
		{
			$dprv_this_event = "Unexpected script name $script_name";
			dprv_record_event($dprv_this_event);
		}
	}
	// OK Values have been set, start preparing HTML
	$dprv_this_yes_checked = " checked='checked'";
	$dprv_this_no_checked = "";
	if ($dprv_digiprove_this_post == 'No')
	{
		$dprv_this_yes_checked = " checked='checked'";
		$dprv_this_no_checked = " checked='checked'";
	}
	$dprv_all_original_yes_checked = " checked='checked'";
	$dprv_all_original_no_checked = "";
	$dprv_attributionDisplay = "none";
	if ($dprv_this_all_original == 'No')
	{
		$dprv_all_original_yes_checked = "";
		$dprv_all_original_no_checked = " checked='checked'";
		$dprv_attributionDisplay = "";
	}
	$default_checked = "";
	$inputDisplay = "none";
	$labelDisplay = "none";
	$selectDisplay = "";
	$custom_checked = "";
	$other_labelDisplay = "";
	if ($dprv_this_default_license == "Yes")
	{
		$default_checked = " checked='checked'";
		$inputDisplay = "none";
		$labelDisplay = "";
		$other_labelDisplay = "";
		$selectDisplay = "none";
	}
	if ($dprv_this_custom_license == "Yes")
	{
		$custom_checked = " checked='checked'";
		$inputDisplay = "";
		$other_labelDisplay = "none";
		$labelDisplay = "none";
		$selectDisplay = "none";
	}
	$post_type_label = $post_info->post_type;
	if ($post_info->post_type != "post" && $post_info->post_type != "page")
	{
		if (function_exists("get_post_types"))
		{
			$all_post_types = get_post_types('','objects');
			$post_type_label = $all_post_types[$post_info->post_type]->labels->singular_name;
		}
	}
	$a = "<table style='padding:6px;  padding-top:0px; width:100%'><tbody>";
	$a .= "<tr><td style='width:190px; height:30px'>" . sprintf(__("Digiprove this %s", "dprv_cp"), $post_type_label) . ":</td>";
	$a .= "<td style='width:280px'><input type='radio' id='dprv_this_yes' name='dprv_this' value='Yes'" . $dprv_this_yes_checked . " onclick='dprv_TogglePanel()'/>" . __("Yes", "dprv_cp") . "&nbsp;&nbsp;&nbsp;&nbsp;<input type='radio' id='dprv_this_no' name='dprv_this' value='No'" . $dprv_this_no_checked . " onclick='dprv_TogglePanel()'/>" . __("No", "dprv_cp") . "</td>";
	$a .= "<td colspan='2'>" . $dprv_last_digiprove_info;
	/*
	if (strpos($dprv_last_digiprove_info, "Never") === false)
	{
		$dprv_blog_url = parse_url(get_option('home'));
		$dprv_blog_host = $dprv_blog_url['host'];
		$dprv_wp_host = "";		// default

		$dprv_wp_url = parse_url(get_option('siteurl'));
		$dprv_wp_host = $dprv_wp_url['host'];
		if (trim($dprv_blog_host) == "")
		{
			$dprv_blog_host = $dprv_wp_host;
		}
		$credentials = array("user_id" => get_option('dprv_user_id'), "domain_name" => $dprv_blog_host, "api_key" => get_option('dprv_api_key'), "password" => get_option('dprv_password'));
		$user_agent = "Copyright Proof " . DPRV_VERSION;
		$verify_result = digiprove::verify($error_message, $credentials, $dprv_post_info["certificate_id"], $post_info->post_content, $digiproved_content, null, $user_agent);
		if ($error_message != "")
		{
			$a .= "<br/>" . __("Encountered a problem while trying to verify content:","dprv_cp") . " " . $error_message;
		}
		else
		{
			if (intval($verify_result["result_code"]) > 209)
			{
				$a .= "<br/><span style='color:red'>" . $verify_result["result"];
				if ($verify_result["notes"] != "")
				{
					$a .= "; " . $verify_result["notes"];
				}
				$a .= "</span>";
			}
			else
			{
				$a .= "<br/>" . $verify_result["result"];
			}
		}
		$a .= "<br/>" . dprv_eval($verify_result);
	}
	*/
	$a .= "</td></tr></tbody></table>";

	$a .= "<table id='dprv_copyright_panel_body' style='padding:6px; padding-top:0px; width:100%'><tbody>";
	$a .= "<tr><td style='width:190px;'>" . __("Is content all yours?", "dprv_cp") . "</td>";
	$a .= "<td style='width:280px'><input type='radio' id='dprv_all_original_yes' name='dprv_all_original' value='Yes'" . $dprv_all_original_yes_checked . " onclick='dprv_ToggleAttributions()'/>" . __("Yes", "dprv_cp") . "&nbsp;&nbsp;&nbsp;&nbsp;<input type='radio' id='dprv_all_original_no' name='dprv_all_original' value='No'" . $dprv_all_original_no_checked . " onclick='dprv_ToggleAttributions()'/>" . __("No", "dprv_cp") . "</td>";
	$a .= "<td style='width:140px'></td><td style='min-width:110px'></td></tr>";
	$a .= "<tr id='dprv_attributions_0' style='display:" . $dprv_attributionDisplay . "'><td style='height:6px'></td></tr>";
	$a .= "<tr id='dprv_attributions_1' style='display:" . $dprv_attributionDisplay . "'><td valign='top'>" . __("Acknowledgements / Attributions", "dprv_cp") . "</td><td colspan='3'><textarea id='dprv_attributions' name='dprv_attributions' rows='1' style='width:100%'>" . htmlspecialchars(stripslashes($dprv_this_attributions), ENT_QUOTES, 'UTF-8') . "</textarea></td></tr>";
	
	$a .= "<tr><td style='height:6px'></td></tr>";
	$a .= "<tr><td style='height:25px'>" . __("License Type", "dprv_cp") . ":</td><td>";
	$a .= "<span id='dprv_this_license_label' style='display:" . $labelDisplay . "'>" . htmlspecialchars(stripslashes($dprv_this_license_type), ENT_QUOTES, 'UTF-8') . "</span>";
	$a .= "<select id='dprv_license_type' name='dprv_license_type' style='width:280px;display:" . $selectDisplay . "' onchange='dprv_LicenseChanged();'>";
	$selected="";
	if ($dprv_this_license =="0")
	{
			$selected=" selected='selected'";
	}
	$a .= "<option value='0'" . $selected . ">" . __("None", "dprv_cp") . "</option>";
	for ($i=0;$i<count($dprv_licenseIds);$i++)
	{
		$selected="";
		if ($dprv_this_license == $dprv_licenseIds[$i])
		{
			$selected=" selected='selected'";
		}
		$a .= "<option value='" . htmlspecialchars($dprv_licenseIds[$i], ENT_QUOTES, 'UTF-8') . "'" . $selected . ">" . htmlspecialchars(stripslashes($dprv_licenseTypes[$i]), ENT_QUOTES, 'UTF-8') . "</option>";
	}
	$a .= "</select>";
	$a .= "<input type='text' id='dprv_license_input' name='dprv_license_input' value='" .  htmlspecialchars(stripslashes($dprv_this_license_type), ENT_QUOTES, 'UTF-8') . "' style='width:280px; display:" . $inputDisplay . "' /></td>";
	$a .= "<td>";
	$a .= "<input type='checkbox' id='dprv_default_license' name='dprv_default_license' onclick='dprv_ToggleDefault();'" . $default_checked . "/>&nbsp;" . __("Use Default", "dprv_cp") . "</td><td>";
	$a .= "<input type='checkbox' id='dprv_custom_license' name='dprv_custom_license' onclick='dprv_ToggleCustom();'" . $custom_checked . "/>&nbsp;" . __("Custom&nbsp;for&nbsp;this&nbsp;post", "dprv_cp");
	$a .= "</td></tr>";
	$a .= "<tr><td style='height:6px'></td></tr>";
	$a .= "<tr><td>" . __("License Caption", "dprv_cp") . ":</td>";
	$a .= "<td colspan='3'>";
	$a .= "<select id='dprv_license_caption' name='dprv_license_caption' style='width:200px; display:" . $inputDisplay . "'>";
	$selected = "";
	if ($dprv_this_license_caption ==  __("Some Rights Reserved", "dprv_cp"))
	{
		$selected = " selected='selected'";
	}
	$a .= "<option value='" . __("Some Rights Reserved", "dprv_cp") . "'" . $selected . ">" .  __("Some Rights Reserved", "dprv_cp") . "</option>";
	$selected = "";
	if ($dprv_this_license_caption ==  __("All Rights Reserved", "dprv_cp"))
	{
		$selected = " selected='selected'";
	}
	$a .= "<option value='" . __("All Rights Reserved", "dprv_cp") . "'" . $selected . ">" . __("All Rights Reserved", "dprv_cp") . "</option>";
	$a .= "</select>";
	$a .= "<span id='dprv_license_caption_label' style='width:100%; display:" . $other_labelDisplay . "'>" . htmlspecialchars($dprv_this_license_caption, ENT_QUOTES, 'UTF-8') . "</span>";
	$a .= "</td></tr>";
	
	
	$a .= "<tr><td style='height:6px'></td></tr>";
	$a .= "<tr><td valign='top'>" . __("License Abstract", "dprv_cp") . ":</td>";
	$a .= "<td colspan='3'>";
	$a .= "<textarea id='dprv_license_abstract' name='dprv_license_abstract' rows='2' style='width:100%; display:" . $inputDisplay . "'>" . htmlspecialchars(stripslashes($dprv_this_license_abstract), ENT_QUOTES, 'UTF-8') . "</textarea>"; 	
	$a .= "<span id='dprv_license_abstract_label' style='width:100%; display:" . $other_labelDisplay . "'>" . htmlspecialchars(stripslashes($dprv_this_license_abstract), ENT_QUOTES, 'UTF-8') . "</span>";
	$a .= "</td></tr>";
	$a .= "<tr><td style='height:6px'></td></tr>";
	$a .= "<tr><td>" . __("License URL", "dprv_cp") . ":</td><td colspan='3'>";
	$a .= "<input id='dprv_license_url_input' name='dprv_license_url_input' value='" . htmlspecialchars(stripslashes($dprv_this_license_url), ENT_QUOTES, 'UTF-8') . "' style='width:100%;display:" . $inputDisplay . "'/>";
	$a .= "<a id='dprv_license_url_link' name='dprv_license_url_link' href='" . $dprv_this_license_url . "' target='_blank' style='display:" . $other_labelDisplay . "'>" . htmlspecialchars(stripslashes($dprv_this_license_url), ENT_QUOTES, 'UTF-8') . "</a>";
	$a .= "</td></tr>";
	$a .= "</tbody></table>";
	echo $a;
	
	$jsfile = plugins_url("copyright_proof_cr_panel.js", __FILE__) . "?v=" . DPRV_VERSION;
	echo('<script type="text/javascript" src="' . $jsfile . '"></script>');

	// Following required for correct management of F5 refresh
	echo ("<script type='text/javascript'>
				<!--
				// These functions all contained in copyright_proof_cr_panel.js (loaded just above)
				dprv_TogglePanel();
				dprv_ToggleAttributions();
				dprv_ToggleDefault();
				dprv_SetLicense();
				dprv_ToggleCustom();
				//-->
			</script>");
	return $post_info;
}

function dprv_verify_box()
{
	global $wpdb, $post, $post_id;
	$log = new DPLog();  
	$dprv_post_types = explode(',',get_option('dprv_post_types'));
	if (array_search($post->post_type, $dprv_post_types) === false)
	{
		return;
	}
	$post_type_label = $post->post_type; //default value
	if ($post->post_type == "page")
	{
		$can_publish = current_user_can("publish_pages");
		$post_type_label = __("page", "dprv_cp"); 
	}
	else
	{
		$can_publish = current_user_can("publish_posts");
		if ($post->post_type == "post")
		{
			$post_type_label = __("post", "dprv_cp"); 
		}
	}

	$dprv_post_id = $post_id;   // default
	// Added this revised if block and new else block at 2.14 as discovered quite often $post_id erroneously set to a value of 0:
	if (!isset($post_id))
	{
		$log->lwrite("post id not set, using  post->ID ($post->ID)");
		$dprv_post_id = $post->ID;
	}
	else
	{
		if ($post_id == 0 && $post->ID != 0)
		{
			$log->lwrite("replacing post id of 0 with post->ID ($post->ID)");
			$dprv_post_id = $post->ID;
		}
	}

	$dprv_subscription_type = get_option('subscription_type');
	$dprv_subscription_expiry = get_option('dprv_subscription_expiry');
	$dprv_subscription_expired = "No";
	$dprv_expiry_timestamp = strtotime($dprv_subscription_expiry . ' 23:59:59 +0000') + 864000;			// add 10-day grace period (Also handles any unforeseen timezone issues)
	if ($dprv_expiry_timestamp != false && $dprv_expiry_timestamp != -1 && time() > $dprv_expiry_timestamp)
	{
		$dprv_subscription_expired = "Yes";
	}
	$dprv_days_to_expiry = floor((strtotime($dprv_subscription_expiry . ' 23:59:59 +0000') - time())/86400);
	$log->lwrite("dprv_days_to_expiry = " . $dprv_days_to_expiry);

	
	if ($dprv_subscription_type == "Basic" || $dprv_subscription_type == "" || $dprv_subscription_type == "Personal" || $dprv_subscription_expired == "Yes")


	$sql="SELECT * FROM " . get_option('dprv_prefix') . "dprv_posts WHERE id = " . $dprv_post_id;
	$dprv_post_info = $wpdb->get_row($sql, ARRAY_A);
	if (!is_null($dprv_post_info) && count($dprv_post_info) > 0)
	{
		$dprv_verify_text = "default";
		if (in_array( $post->post_status, array('publish', 'future', 'private')) && 0 != $post->ID && get_option('dprv_html_integrity') == "Yes") //|| get_option('dprv_files_integrity') == "Yes"))
		{
			echo ('<div class="misc-pub-section" style="height:auto; overflow:auto;border-top-color:#DFDFDF;background-color:#EEEEFF">');
			if (($dprv_post_info["certificate_id"] != null && $dprv_post_info["certificate_id"] != "") || ($dprv_post_info["last_time_updated"] != null && $dprv_post_info["last_fingerprint"] != ""))
			{
				echo ('<div style="margin-bottom:7px; width:85%; text-align:center; font-weight:bold;border-bottom: 1px solid #FFFFFF; float:left"><img src="' . plugins_url('digiprove_logo_112x28.png', __FILE__ ). '"/><span style="vertical-align:14px">&nbsp;&nbsp;' . __('Integrity Check', 'dprv_cp') . '</span></div><div style="width:14%; padding-left:0px; float:right"><a style="cursor:help" href="javascript:alert(\'' . __('In Digiprove Integrity-checking panel, hold mouse over message for further information', 'dprv_cp') . '\')" title="' . __('In Digiprove Integrity-checking panel, hold mouse over message for further information', 'dprv_cp') . '">' . __("hint", "dprv_cp") . '</a></div>');

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
				//$log->lwrite("last modified time: " . $dprv_last_update_time . ", = timestamp: " . $dprv_last_update_timestamp . ", = " . date("Y-m-d h:i:s T", $dprv_last_update_timestamp));

				echo ('<div style="width:49%;float:left;border-right:1px solid #DFDFDF; text-align:center"><div style="font-style:italic;font-weight:bold">HTML</div>');
 				$digital_fingerprint = "";
				$content = $post->post_content;
				$content = dprv_getRawContent($content, $digital_fingerprint);
				if ($digital_fingerprint != "")
				{
					//if (get_option('dprv_html_integrity') == "Yes")
					//{
						if ($digital_fingerprint == $dprv_post_info["digital_fingerprint"])
						{
							$dprv_integrity_message = "<div style=\"color:green\" title=\"" . sprintf(__("The HTML in this %s has not been altered since last Digiprove certification", "dprv_cp"), $post_type_label) . "\">" . __("Integrity Check OK", "dprv_cp") . "</div>";
						}
						else
						{
							if ($digital_fingerprint == $dprv_post_info["last_fingerprint"])
							{
								$dprv_integrity_message = "<div style=\"color:green\" title=\"" . sprintf(__("The HTML in this %s has not been altered from outside Wordpress", "dprv_cp"), $post_type_label) . "\">" . __("Integrity Check OK", "dprv_cp") . "</div> " . __("(but last update was not Digiproved)", "dprv_cp");
							}
							else
							{
								$dprv_integrity_message = "<div style=\"color:red; font-weight:bold;\" title=\"" . sprintf(__("The HTML in this %s appears to have been changed from outside Wordpress", "dprv_cp"), $post_type_label) . "\">" . __("HTML Tamper Alert", "dprv_cp") . "</div>";
								
								// if last wp modified time was more than 5 seconds after last update noted by this plugin
								// make the discrepancy a warning rather than a Red Tamper Alert (could be that this plugin was deactivated for a period)
								$dprv_wp_last_modified_time = strtotime($post->post_modified_gmt . " GMT");
								if (($dprv_wp_last_modified_time - $dprv_last_update_timestamp) > 5)
								{
									$dprv_integrity_message = "<div style=\"color:orange; font-weight:bold;\" title=\"" . sprintf(__("The HTML in this %s has been changed without Digiprove integrity checking so could not verify", "dprv_cp"), $post_type_label) . "\">" . __("Content Changed", "dprv_cp") . "</div>";
								}
							}
						}
						echo ($dprv_integrity_message);
					//}
					//else
					//{
					//	echo ('<div title="' . __('Your settings indicate no HTML integrity checking', 'dprv_cp') . '">' . __("Integrity not checked", "dprv_cp") . '</div>');
					//}
				}
				else
				{
					echo ('<div title="' . __('Cannot calculate digital fingerprint of your content; try upgrading PHP', 'dprv_cp') . '">' . __("Integrity not checked", "dprv_cp") . '</div>');
				}
				echo '</div><div style="width:49%;float:right;text-align:center"><div style="font-style:italic; font-weight:bold">' . __("Files", "dprv_cp") . '</div>';
				if (function_exists("hash"))
				{					
					if (get_option('dprv_files_integrity') == "Yes" && $dprv_subscription_expired != "Yes")
					{
						//if ($dprv_post_info["last_time_digiproved"] != null && $dprv_post_info["last_time_digiproved"] == $dprv_post_info["last_time_updated"] )
						{
							global $dprv_blog_host;
							$dprv_integrity_message = __("File Integrity Unknown", "dprv_cp");

							dprv_getContentFiles($dprv_post_id, $content, $content_files, $content_file_names, 50, $file_count, $total_url_count, false);
							if ($file_count > 0)
							{
								$file_text = "";
								if ($file_count ==1)
								{
									$file_text = sprintf(__("%s file", "dprv_cp"), $file_count);
								}
								else
								{
									$file_text = sprintf(__("%s files", "dprv_cp"), $file_count);
								}
								$log->lwrite("file count = " . $file_count . ", count(content_files) = " . count($content_files) . ", count(content_file_names) = " . count($content_file_names));
								//if (Digiprove::parseContentFiles($error_message, $content_files, $content_file_names, $content_file_fingerprints, $content_file_table))
								if (Digiprove::parseContentFiles($error_message, $content_files, $content_file_table))
								{
									$log->lwrite("file count = " . $file_count . ", count(content_file_table) = " . count($content_file_table));
									if (dprv_verifyContentFiles($error_message, $dprv_post_id, $content_file_table, $match_results))
									{
										$log->lwrite("file count = " . $file_count . ", count(match_results) = " . count($match_results));
										$file_integrity_detail = "";
										if (is_array($match_results))
										{
											$comma = "";
											foreach ($match_results as $filename=>$status)
											{

												$file_integrity_detail .= $comma . $filename . ": " . $status;
												$comma = ", \n";
											}
										}
										$dprv_integrity_message = $file_text . __(" Verified OK", "dprv_cp");
										echo ("<div style=\"color:green\" title=\"" . $file_integrity_detail . "\">" . $dprv_integrity_message . "</div>");
									}
									else
									{
										// if last wp modified time was more than 5 seconds after last update noted by this plugin
										// make the discrepancy a warning rather than a Red Tamper Alert (could be that this plugin was deactivated for a period)
										$dprv_wp_last_modified_time = strtotime($post->post_modified_gmt . " GMT");
										if (($dprv_wp_last_modified_time - $dprv_last_update_timestamp) > 5)
										{
											$dprv_integrity_message = "<div style=\"color:orange; font-weight:bold;\" title=\"" . sprintf(__("The content of this %s has been changed without Digiprove integrity checking so could not verify", "dprv_cp"), $post_type_label) . " " . $file_text . "\">" . __("Content Changed", "dprv_cp") . "</div>";
											echo $dprv_integrity_message;
										}
										else
										{
											$dprv_integrity_message = $file_text . __(" - Tamper Warning", "dprv_cp");
											echo ("<div style=\"color:red\" title=\"" . $error_message . "\" onmouseover=\"dprv_DisplayFiles()\" onmouseout=\"dprv_HideFiles()\">" . $dprv_integrity_message . "</div>");
											echo ('<table style="position: absolute; display: none; font-family: Tahoma,MS Sans Serif; font-style: normal; font-size: 11px; font-weight: normal; color: rgb(99, 99, 99); border: 1px solid rgb(187, 187, 187); float: none; max-width: 640px; text-decoration: none; letter-spacing: normal; line-height: 16px; vertical-align: 1px; padding: 0px; background: none repeat scroll 0% 0% rgb(255, 255, 255); z-index: 2;" id="dprv_files_panel"><tbody>');

											foreach ($match_results as $filename=>$status)
											{
												if ($status == __("Matched", "dprv_cp"))
												{
													echo ("<tr><td style=\"vertical-align:top; text-align:left\">" . $filename . "</td><td style=\"vertical-align:top\">" . $status . "</td></tr>");
												}
												else
												{
													echo ("<tr style=\"color:red; vertical-align:top; text-align:left\"><td>" . $filename . "</td><td style=\"vertical-align:top\">" . $status . "</td></tr>");
												}
											}
											echo ('</tbody></table>');
										}
									}
								}
								else
								{
									//echo (__("File Integrity not checked") . ": " . $error_message);
									echo ('<div title="' . $error_message . '">' . __("Integrity not checked", "dprv_cp") . '</div>');
								}
							}
							else
							{
								//echo (__("# files: ") . $file_count . ", content(" . strlen($content) . ") begins with <xmp>" . substr($content,0,40) . "</xmp>");
								echo ('<div title="' . __('Either no files referenced from your content, or none that your settings indicate should be Digiproved', 'dprv_cp') . '">' . __("Integrity not checked", "dprv_cp") . '</div>');
							}
						}
						//else
						//{
						//	echo ('<div title="' . __('This content has been updated since last Digiprove certification, so cannot check file integrity', 'dprv_cp') . '">' . __("Integrity not checked", "dprv_cp") . '</div>');
						//}
					}
					else
					{
						if ($dprv_subscription_type == "Basic" || $dprv_subscription_type == "" || $dprv_subscription_type == "Personal")
						{
							echo ('<div title="' . __('File integrity checking not available to your subscription level', 'dprv_cp') . '">' . __("Integrity not checked", "dprv_cp") . '</div>');
						}
						else
						{
							if ($dprv_subscription_expired == "Yes")
							{
								echo ('<div title="' . __('Your Digiprove subscription is expired', 'dprv_cp') . '">' . __("Integrity not checked", "dprv_cp") . '</div>');
							}
							else
							{
								echo ('<div title="' . __('Your settings indicate no file integrity checking', 'dprv_cp') . '">' . __("Integrity not checked", "dprv_cp") . '</div>');
							}
						}
					}
				}
				else
				{
					echo ('<div title="' . __('Cannot calculate digital fingerprints of your files; try upgrading PHP', 'dprv_cp') . '">' . __("Integrity not checked", "dprv_cp") . '</div>');
				}
				echo '</div>';	// end of Files column
			}
			//echo ("updated: " . $dprv_post_info["last_time_updated"] . ", digiproved: " . $dprv_post_info["last_time_digiproved"]);
			if ($can_publish && ($dprv_post_info["last_time_updated"] == $dprv_post_info["last_time_digiproved"]) || ($digital_fingerprint == $dprv_post_info["digital_fingerprint"]))
			{
				$dprv_verify_text = __('Verify against Certificate', 'dprv_cp');
				$dprv_verify_hint = __('Check content against tamper-proof Digiprove certification (online check)', 'dprv_cp');
				echo ('<div style="text-align:left">');

				echo ('<input name="dprv_verify_action" type="hidden" id="dprv_verify_action" value="No" />');
				echo ('<input type="button" class="preview button" value="' . $dprv_verify_text . '" title="' . $dprv_verify_hint . '" tabindex="6" onclick="return call_dprv_verify()" style="float:none; margin-top:7px"/>');
				global $dprv_blog_host;
				echo ('<script type="text/javascript">');
				echo ('function call_dprv_verify()
						{
							jQuery(document).ready(function($) 
									{
										var data = {
													action: "dprv_verify", 
													user_id: "' . get_option('dprv_user_id') . '", 
													domain_name: "' . $dprv_blog_host . '", 
													api_key: "'.  get_option('dprv_api_key') . '",
													certificate_id: "' . $dprv_post_info["certificate_id"] . '",
													digital_fingerprint: "' . $digital_fingerprint . '"};
										jQuery.post(ajaxurl, data,  function(response) {alert(response);});
									});
						}');
				echo ('</script>');
				echo ('</div>');
			}
			echo ('</div>');
		}
	}		
}


function dprv_add_digiprove_submit_button()
{
	global $post;

	$dprv_post_types = explode(',',get_option('dprv_post_types'));

	if (array_search($post->post_type, $dprv_post_types) === false)
	{
		return;
	}
	if ($post->post_type == "page")
	{
		$can_publish = current_user_can("publish_pages");
	}
	else
	{
		$can_publish = current_user_can("publish_posts");
	}

	echo ('<input name="dprv_publish_dp_action" type="hidden" id="dprv_publish_dp_action" value="No" />');
	$dprv_publish_text = "default";
	if ( !in_array( $post->post_status, array('publish', 'future', 'private') ) || 0 == $post->ID )
	{
		if ($can_publish)
		{
			if ( !empty($post->post_date_gmt) && time() < strtotime( $post->post_date_gmt . ' +0000' ) )
			{
				$dprv_publish_text = __('Schedule & Digiprove', 'dprv_cp');
			}
			else
			{
				$dprv_publish_text = __('Publish &amp; Digiprove', 'dprv_cp');
			}
		}
		else
		{
			$dprv_publish_text = __('Digiprove &amp; Submit for Review', 'dprv_cp');
		}
	}
	else
	{
		$dprv_publish_text = __('Update &amp; Digiprove', 'dprv_cp');
	}
	
	if ($dprv_publish_text != __('Digiprove &amp; Submit for Review', 'dprv_cp'))    // At least for now, don't have Digiproving of contributor submissions
	{

		$today_count = 0;	// default value
		if (get_option('dprv_last_date') == date("Ymd"))
		{
			$today_count = intval(get_option('dprv_last_date_count'));
		}
		$dprv_subscription_type = get_option('dprv_subscription_type');
		$dprv_max_file_count = 0;
        $dprv_back_digiprove_allowance = 0;
		//$dprv_today_limit = dprv_daily_limit($dprv_subscription_type, $dprv_max_file_count);
		$dprv_today_limit = dprv_entitlements($dprv_subscription_type, $dprv_max_file_count, $dprv_back_digiprove_allowance);
        
		$color = "";
		if ($dprv_today_limit != -1)
		{
			if (($dprv_today_limit - $today_count) == 1)
			{
				$color = "color:orange;";
			}
			if (($dprv_today_limit - $today_count) < 1)
			{
				$color = "color:red;";
			}
		}
		echo ('<div id="publish_dp_div" style="height: 28px;width:100%; text-align:right;"><span style="font-size:10px;float:left;' . $color . '">' . __('Digiproved today', 'dprv_cp') . ': ' . $today_count);
		if ($dprv_today_limit != -1)
		{
			echo ('/' . $dprv_today_limit);
		}
		echo ('</span><input name="save" type="submit" class="button-primary" id="publish_dp" style="float:right" tabindex="5" onclick="return set_dprv_action()" value="' . $dprv_publish_text . '"/></div>');
		
		echo ('<script type="text/javascript">
				function set_dprv_action()
				{
					document.getElementById("dprv_publish_dp_action").value = "Yes";
					document.getElementById("publish").click();
					return false;
				}
				function dprv_renameButton(newval)
				{
					document.getElementById("publish_dp").value = newval + "' . __(' & Digiprove', 'dprv_cp') . '";
				}

				// Trace JS code which changes value of publish element (i.e. when user chooses a future publication
				if (navigator.userAgent.indexOf("MSIE") == -1)  // Older version of IE (pre IE11)?
				{
					if (window.watch)
					{
                        // Firefox
						function dprv_timestamp_changed(property, oldval, newval)
						{
							dprv_renameButton(newval);
							return newval;
						}
						setTimeout(\'document.getElementById("publish").watch("value", dprv_timestamp_changed)\',1500);
					}
					else
					{
                        // Chrome, Safari, or IE11+
						function dprv_value_might_be_changed()
						{
							var newval = document.getElementById("publish").value;
							if (newval != dprv_oldval)
							{
								dprv_renameButton(newval);
							}
							dprv_oldval = newval;
							return true;
						}
						var dprv_oldval = "unknown"; 
						var dprv_i = setInterval("dprv_value_might_be_changed()", 1000);
					}
				}
				else
				{
                    // IE pre IE11 method
					function dprv_AddOnchangeEvent()
					{
						document.getElementById("publish").onpropertychange = function onpropertychange(){dprv_property_changed()};
					}
					function dprv_property_changed()
					{
						dprv_renameButton(document.getElementById("publish").value);
						return true;
					}
					setTimeout(\'dprv_AddOnchangeEvent();\',500);
				}
				</script>');
	}
}

// Write Copyright Panel details into db
// Remove old notice if there and trim remainder, and write information from it into custom post fields 

function dprv_parse_post ($data, $raw_data)
{
	global $wpdb, $dprv_digiprove_this_post;
	$log = new DPLog();  
	$log->lwrite("dprv_parse_post starts"); 
	$script_name = pathinfo($_SERVER['PHP_SELF'], PATHINFO_BASENAME);
	$posDot = strrpos($script_name,'.');
	if ($posDot != false)
	{
		$script_name = substr($script_name, 0, $posDot);
	}
	if ($script_name == "admin-ajax")
	{
		$log->lwrite("parse_post not starting because this is ajax");
		return $data;
	}
	if ($data['post_status'] != "publish" && $data['post_status'] != "private" && $data['post_status'] != "future" && $data['post_status'] != "draft")
	{
		//if ($data['post_status'] == "draft" && $_POST['dprv_publish_dp_action'] == "Yes")
		//{
		//	$log->lwrite("draft but user has explicitly requested Digiproving, then we can proceed beyond this point");
			// If the status is set to draft but user has explicitly requested Digiproving, then we can proceed beyond this point
		//}
		//else
		//{
			$log->lwrite("dprv_parse_post not starting because status (" . $data['post_status'] . ") is not publish, private, future or draft");
			return $data;
		//}
	}

	$my_arrays = debug_backtrace();
	foreach ($my_arrays as $my_array)
	{
		$search_result = array_search("_fix_attachment_links", $my_array);
		if ($search_result == "function")
		{
			$log->lwrite("dprv_parse_post not starting because called via _fix_attachment_links, means this already processed");
			return $data;
		}
	}

	$dprv_post_types = explode(',',get_option('dprv_post_types'));
	if (array_search($data['post_type'], $dprv_post_types) === false)
	{
		$log->lwrite("dprv_parse_post not starting because type (" . $data['post_type'] . ") is not " . get_option('dprv_post_types'));
		return $data;
	}

	if (get_option('dprv_enrolled') != "Yes")
	{
		$log->lwrite("dprv_parse_post not starting because user not registered yet");
		return $data;
	}
	$content = trim($data['post_content']);
	if (strlen(trim($content)) == 0)
	{
		$log->lwrite("dprv_parse_post not starting because content is empty");
		return $data;
	}

	$dprv_post_id = $raw_data['ID'];
	if (intval($dprv_post_id) == 0)
	{
		$log->lwrite("dprv_post_id no good, = " . $dprv_post_id);  
		$dprv_post_id = -1;
	}
	
	// If "Digiprove this post" set to No, record this fact
	$dprv_digiprove_this_post = "";
	if (isset($_POST['dprv_this']))
	{
		$dprv_digiprove_this_post = $_POST['dprv_this'];
	}
	if ($dprv_digiprove_this_post == "No")
	{
		$log->lwrite("dprv_parse_post not starting - digiprove_this_post set to No for post Id " . $dprv_post_id);
		if ($dprv_post_id != -1)
		{  
			$sql="SELECT * FROM " . get_option('dprv_prefix') . "dprv_posts WHERE id = " . $dprv_post_id;
			$dprv_post_info = $wpdb->get_row($sql, ARRAY_A);
			if (count($dprv_post_info) == 0)
			{
				// create record 
				if (false === $wpdb->insert(get_option('dprv_prefix') . "dprv_posts", array('digiprove_this_post'=>0, 'this_all_original'=>1, 'using_default_license'=>1, 'id'=>$dprv_post_id), array('%d','%d', '%d', '%d')))
				{
					$dprv_this_event = $wpdb->last_error . ' inserting no-digiprove for ' . $dprv_post_id;
					dprv_record_event($dprv_this_event);
				}
			}
			else
			{
				if (false === $wpdb->update(get_option('dprv_prefix') . "dprv_posts", array('digiprove_this_post'=>0), array('id'=>$dprv_post_id), '%d', '%d'))
				{
					$dprv_this_event = $wpdb->last_error . ' updating no-digiprove for ' . $dprv_post_id;
					dprv_record_event($dprv_this_event);
				}
			}
		}
		return $data;
	}
	
	if (get_option('dprv_auto_posts') != "Yes" && !isset($_POST['dprv_publish_dp_action']))	// If no dprv_publish_action, then the user had no opportunity to choose Digiprove or Not
	{
		$log->lwrite("dprv_parse_post not starting for post $dprv_post_id in script $script_name because Digiproving of auto-posts is not selected");
		return $data;
	}

	//update_option('dprv_last_action', 'Digiprove id=' . $dprv_post_id);
	$dprv_title = $data['post_title'];
	//$log->lwrite("title=" . $dprv_title . ", id=" . $dprv_post_id);  
	$log->lwrite("");  
	$log->lwrite("dprv_parse_post STARTS for " . $dprv_post_id);  
	// if post_id == -1, means new post coming from xmlrpc (or postie) - in either case there is no copyright panel so no problem that this function does not get executed
	if ($dprv_post_id != -1  && isset($_POST['dprv_this']))
	{
		// Didn't bother combining this with operation below (of writing old notice onto record) as it really only occurs once for each old post
		dprv_record_copyright_details($dprv_post_id);
	}

	// TODO: check whether $_POST['dprv_publish_dp_action'] is set to avoid PHP Notices in logfile
	$dprv_publish_dp_action = $_POST['dprv_publish_dp_action'];
	if ($dprv_publish_dp_action == "No")
	{
		$log->lwrite("dprv_parse_post ending after writing copyright panel details but before the Digiproving bit - user selected publish/update without Digiprove for post Id " . $dprv_post_id);
		return $data;
	}

	// Remove old-style notice (if there is one there) and return the core information from it 
	dprv_strip_old_notice($content, $dprv_certificate_id, $dprv_utc_date_and_time, $dprv_digital_fingerprint, $dprv_certificate_url, $dprv_first_year);

	// If there was an old-style notice, transfer it to db
	if ($dprv_post_id != -1 && $dprv_certificate_id !== false && $dprv_certificate_id != "" )
	{
		$dprv_new_certificate_id = false;
		$sql="SELECT * FROM " . get_option('dprv_prefix') . "dprv_posts WHERE id = " . $dprv_post_id;
		$dprv_post_info = $wpdb->get_row($sql, ARRAY_A);
		if (!is_null($dprv_post_info) && count($dprv_post_info) > 0)
		{
			$dprv_new_certificate_id = $dprv_post_info["certificate_id"];
			$log->lwrite("a row already exists for this post");				
		}

		// If (as expected) nothing yet on db, record the information from the notice into the db 
		if ($dprv_new_certificate_id == false || $dprv_new_certificate_id == "")
		{
			if (count($dprv_post_info) > 0)
			{
				if ($dprv_post_info["first_year"] == null)
				{
					$log->lwrite("first year is null");
				}
				else
				{
					$dprv_first_year = intval($dprv_post_info["first_year"]);
				}
				if (false === $wpdb->update(get_option('dprv_prefix') . "dprv_posts", array('certificate_id'=>$dprv_certificate_id, 'certificate_url'=>$dprv_certificate_url, 'digital_fingerprint'=>$dprv_digital_fingerprint, 'cert_utc_date_and_time'=>$dprv_utc_date_and_time, 'first_year'=>intval($dprv_first_year)), array('id'=>$dprv_post_id), array('%s', '%s', '%s', '%s', '%d'), '%d'))
				{
					$dprv_this_event = $wpdb->last_error . ' writing old notice ' . $dprv_post_id . ' to db';
					dprv_record_event($dprv_this_event);
				}
			}
			else
			{	
				if (false === $wpdb->insert(get_option('dprv_prefix') . 'dprv_posts', array('id'=>$dprv_post_id, 'digiprove_this_post'=>1, 'certificate_id'=>$dprv_certificate_id,   'certificate_url'=>$dprv_certificate_url, 'digital_fingerprint'=>$dprv_digital_fingerprint, 'cert_utc_date_and_time'=>$dprv_utc_date_and_time, 'first_year'=>intval($dprv_first_year)), array('%d','%d','%s','%s','%s','%s','%d')))
				{
					$dprv_this_event = 'error ' . $wpdb->last_error . ' inserting old notice to db';
					dprv_record_event($dprv_this_event);
				}
			}
		}
	}

	$data['post_content'] = trim($content);
	return $data;
}

//function dprv_digiprove_post($dprv_post_id, $raw_post)	// could use $raw_post['ID'] if ID empty
function dprv_digiprove_post($dprv_post_id)
{
	// This function executes after the post has been created/updated and we have a post id
	// So we can read copyright options (or default if none there)
	// Digiprove content if appropriate and record details of Digiprove action
	// Otherwise record fingerprint and timestamp of html content for integrity checking 
	
	$log = new DPLog();  
	$script_name = pathinfo($_SERVER['PHP_SELF'], PATHINFO_BASENAME);
	$posDot = strrpos($script_name,'.');
	if ($posDot != false)
	{
		$script_name = substr($script_name, 0, $posDot);
	}
	$post_action = "";
	$dprv_post_status = "unknown";
	if (isset($_POST["action"]))
	{
		$post_action = $_POST["action"];
	}
	if (trim($dprv_post_id) == "")
	{
		global $page_id, $post_id, $post;
		$message = "edit: dprv_post_id is empty";
		$message .= ", script_name=" . $script_name;
		$message .= ", post_action=" . $post_action;
		if (isset($GLOBALS["GLOBALS"]["_POST"]["post_ID"]))
		{
			$message .= ", GLOBALS[GLOBALS][_POST][post_ID]=" . $GLOBALS["GLOBALS"]["_POST"]["post_ID"];
		}
		$message .= ", page_id=$page_id, post_id=$post_id";
		if (isset($post))
		{
			$message .= ", post->ID=" . $post->ID;
		}
		dprv_record_event($message);
	}
	// NOTE BELOW - have found that even if get_post called with empty string, it still returns current post (at least in edit mode) so $post_record->ID has the post id in it
	$post_record = get_post($dprv_post_id);
	if (is_object($post_record) && isset($post_record->post_status))
	{
		$dprv_post_status = $post_record->post_status;
	}
	else
	{
		$message = "post record for $dprv_post_id is not an object";
		if (is_null($post_record))
		{
			$message .= ", is null";
		}
		dprv_record_event($message);
		// Why was return commented out?
        // //return;
        return;
        
	}

	$log->lwrite("starting dprv_digiprove_post ($post_action) $dprv_post_id, status=$dprv_post_status");
	// Values seen for POST[action]:	empty					when (script = wp-cron)
	//															or   (script = post-new    && status = auto-draft)
	//															or   (script = xmlrpc-post && status = ? publish?)
	//															or   (script = xmlrpc-post && status = inherit)
	//									autosave				when (script = admin-ajax  && status = draft)
	//									editpost				when (script = post        && status = inherit)
	//															or	 (script = post        && status = publish)
	//															or	 (script = post        && status = future)
	//															or	 (script = post        && status = draft)  (only seen so far on Opera on future-dated posts)
	//									editpost				when (script = page        && status = inherit)     on WP 2.7
	//															or	 (script = page        && status = publish)     on WP 2.7
	//                                  runpostie				when (script = options-general  && status = draft)
	//															or   (script = options-general  && status = ? publish?)
	//									post-quickpress-save	when (script = post				&& status = auto-draft)
	//
	// and with combinations of status = private

	if ($script_name == "wp-cron")
	{
		$log->lwrite("dprv_digiprove_post not starting because script is wp-cron - any digiproving has been done already");
		return;
	}

	if ($dprv_post_status != "publish" && $dprv_post_status != "private"  && $dprv_post_status != "future")
	{
		$log->lwrite("dprv_digiprove_post not starting because status ($dprv_post_status) is not publish, private or future");
		return;
	}

	// Doing this check because of situation where if an attachment is referred to within the content,
	// action hook wp_insert_post gets fired twice with identical variables.
	// 1st time: edit_post calls wp_update_post calls wp_insert_post triggers action hook wp_insert_post
	// 2nd time: _fix_attachment_links calls wp_update_post calls wp_insert_post triggers action hook wp_insert_post

	$my_arrays = debug_backtrace();
	foreach ($my_arrays as $my_array)
	{
		$search_result = array_search("_fix_attachment_links", $my_array);
		if ($search_result == "function")
		{
			$log->lwrite("dprv_digiprove_post not starting because called via _fix_attachment_links, means this already processed");
			if (trim($dprv_post_id) == "")
			{
				$message = "edit: dprv_post_id is empty and called via _fix_attachment_links";
				dprv_record_event($message);
			}
			return;
		}
	}

	$content = trim($post_record->post_content);
	$dprv_post_types = explode(',',get_option('dprv_post_types'));
	if (array_search($post_record->post_type, $dprv_post_types) === false)
	{
		$log->lwrite("dprv_digiprove_post not starting because type (" . $post_record->post_type . ") is not in " . get_option('dprv_post_types'));  // Does this ever occur?
		return;
	}

	if (get_option('dprv_enrolled') != "Yes")
	{
		$log->lwrite("dprv_digiprove_post not starting because user not registered yet");
		return;
	}
	// Determine limits
	$dprv_subscription_expiry = get_option('dprv_subscription_expiry');
	$dprv_subscription_type = get_option('dprv_subscription_type');
	$dprv_max_file_count = 0;
    $dprv_back_digiprove_allowance = 0;
 	//$dprv_today_limit = dprv_daily_limit($dprv_subscription_type, $dprv_max_file_count);
	$dprv_today_limit = dprv_entitlements($dprv_subscription_type, $dprv_max_file_count, $dprv_back_digiprove_allowance);

	// TODO: Change this to not return, but just skip Digiproving part
	$dprv_publish_dp_action = "";
	if (isset($_POST['dprv_publish_dp_action']))
	{
		$dprv_publish_dp_action = $_POST['dprv_publish_dp_action'];
	}
	if ($dprv_publish_dp_action == "No")
	{
		$log->lwrite("dprv_digiprove_post not starting - user selected publish/update without Digiprove for post Id " . $dprv_post_id);
		dprv_record_non_dp_action($dprv_post_id, $content, $dprv_max_file_count);
		// Test whether commenting out below causes spurious messages, if so, restore
		//update_option('dprv_last_result', '');
		return;
	}
	// TODO: check whether $_POST['dprv_this'] is set to avoid PHP Notices in logfile
	$dprv_digiprove_this_post = $_POST['dprv_this'];
	if ($dprv_digiprove_this_post == "No")
	{
		$log->lwrite("dprv_digiprove_post not starting - digiprove_this_post set to No for post Id " . $dprv_post_id);
		dprv_record_non_dp_action($dprv_post_id, $content, $dprv_max_file_count);
		// Test whether commenting out below causes spurious messages, if so, restore
		//update_option('dprv_last_result', '');
		return;
	}

	if ($dprv_digiprove_this_post != "Yes")
	{
		$log->lwrite("dprv_digiprove_this_post = " . $dprv_digiprove_this_post);
		$sql="SELECT * FROM " . get_option('dprv_prefix') . "dprv_posts WHERE id = %d";
		$dprv_post_info = dprv_wpdb("get_row", $sql, $dprv_post_id);
		if (!is_null($dprv_post_info) && count($dprv_post_info) > 0)
		{
			//if ($dprv_post_info["digiprove_this_post"] == false)
			if ((bool)$dprv_post_info["digiprove_this_post"] == false)
			{
				$log->lwrite("dprv_digiprove_post not starting - recorded value for digiprove_this_post set to false for post Id " . $dprv_post_id);
				// Test whether commenting out below causes spurious messages, if so, restore
				//update_option('dprv_last_result', '');
				return;
			}
		}
	}

	if (strlen(trim($content)) == 0)
	{
		$log->lwrite("dprv_digiprove_post not starting because content is empty");
		update_option('dprv_last_result', __('Content is empty', 'dprv_cp'));
		update_option('dprv_pending_message', __('Content is empty', 'dprv_cp'));
		return;
	}
	if (trim($dprv_post_id) == "")
	{
		$log->lwrite("dprv_digiprove_post not starting because dprv_id is empty");
		return;
	}

	//if (get_option('dprv_auto_posts') != "Yes" && ($dprv_post_id == -1 || ($script_name != "post-new" && $script_name != "post" && $script_name != "post")))
	if (get_option('dprv_auto_posts') != "Yes" && !isset($_POST['dprv_publish_dp_action']))	// If no dprv_publish_action, then the user had no opportunity to choose Digiprove or No
	{
		$log->lwrite("dprv_digiprove_post not starting for post $dprv_post_id in script $script_name because Digiproving of auto-posts is not selected");
		return;
	}

	$today_count = 0;	// default value
	if (get_option('dprv_last_date') != date("Ymd"))
	{
		update_option('dprv_last_date', date("Ymd"));
		update_option('dprv_last_date_count', 0);
	}
	else
	{
		$today_count = intval(get_option('dprv_last_date_count'));
	}

	$today_count += 1;

	//$dprv_subscription_expiry = get_option('dprv_subscription_expiry');
	//$dprv_subscription_type = get_option('dprv_subscription_type');

	if ($today_count > 5)
	{
		if ($today_count > $dprv_today_limit && $dprv_today_limit != -1)
		{
			// NOTE - if changing the "Digiprove daily limit" text, also modify  dprv_admin_footer() which relies on this exact text
			$dprv_last_result = sprintf(__('Digiprove daily limit (%1$s) for %2$s accounts already reached. ', 'dprv_cp'), $dprv_today_limit, $dprv_subscription_type);
			$dprv_last_result .= sprintf(__('You can %s upgrade to increase this limit.%s', 'dprv_cp'), '<a href="' . 	dprv_createUpgradeLink() . '&Action=Upgrade" target="_blank">', '</a>.');
			update_option('dprv_last_result', $dprv_last_result);
			update_option('dprv_pending_message', $dprv_last_result);
			return;
		}

		$dprv_expiry_timestamp = strtotime($dprv_subscription_expiry . ' 23:59:59 +0000') + 864000;					// Add 10-day grace period (also handles any unforeseen timezone issues)
		if ($dprv_expiry_timestamp != false && $dprv_expiry_timestamp != -1 && time() > $dprv_expiry_timestamp)
		{
			// NOTE - if changing the "Digiprove free daily limit" text, also modify  dprv_admin_footer() which relies on this exact text
			$dprv_last_result = sprintf(__('Digiprove free daily limit (5) already reached, and your Digiprove account expired on %s. ', 'dprv_cp'), $dprv_subscription_expiry);
			$dprv_last_result .= sprintf(__('You can %s renew your account here.%s', 'dprv_cp'), '<a href="' . 	dprv_createUpgradeLink() . '&Action=Renew" target="_blank">', '</a>');
			update_option('dprv_last_result', $dprv_last_result);
			update_option('dprv_pending_message', $dprv_last_result);
			return;
		}
	}

	$log->lwrite("dprv_digiprove_post STARTS");

	//update_option('dprv_last_result', '');
	$newContent = stripslashes($content);
	$notice = "";
	

	$certifyResponse = dprv_certify($dprv_post_id, $post_record->post_title, $newContent, $digital_fingerprint, $content_file_names, $dprv_max_file_count, $dprv_subscription_type, $dprv_subscription_expiry, $dprv_last_time, $notice);
	if (!is_array($certifyResponse))
	{
		// Could be "Content unchanged since last edit", "Content is empty", or
		// One of these error messages from Digiprove:
		// User xxxxxxxx@xxxxxxxxx invalid user id -  contact support@digiprove.com for help
		// Digiprove user xxxxxx@xxxxxxxx not activated yet - please click on link in activation email
		$log->lwrite("response: $certifyResponse");
		update_option('dprv_last_result', $certifyResponse);
		update_option('dprv_pending_message', $certifyResponse);
		if (strpos($certifyResponse, __("Content unchanged since last edit", "dprv_cp")) === false 
			&& strpos($certifyResponse, __("Content is empty", "dprv_cp")) === false)
		{
			// Arguably don't bother user with this error condition, just pass to server in due course?
            // Below two instructions redundant
			//update_option('dprv_last_result', $certifyResponse);
			//update_option('dprv_pending_message', $certifyResponse);		// Ensure administrator sees

			// If it was not a communications error, no need to record event as server is already aware of it AND we can clear any previous events
			if (strpos($certifyResponse, "Error:") !== false)
			{
				$dprv_this_event = $certifyResponse;
				dprv_record_event($dprv_this_event);
			}
			else
			{
				update_option('dprv_event','');
			}
		}
		return;
	}
	if (isset($certifyResponse['result_code']))	// if there is an intelligible response we can assume that any dprv_event has been reported, it can be cleared
	{
		update_option('dprv_event','');
	}
	if (!isset($certifyResponse['result_code']) || $certifyResponse['result_code'] != '0')
	{
		$admin_message = 'Note: ' . $certifyResponse['result'];
		$log->lwrite("Digiproving failed, response: " . $certifyResponse['result']);
		update_option('dprv_last_result', $admin_message);
		update_option('dprv_pending_message', $admin_message);
		update_option('dprv_registration_status', 'Problem');
	}
	else
	{
		// This code is to replace password with a new API key, eventually we'll eliminate passwords from db
		if (isset($certifyResponse['api_key']) && $certifyResponse['api_key'] != "")
		{
			$dprv_api_key = $certifyResponse['api_key'];
			if (strlen($dprv_api_key) > 0)
			{
				update_option('dprv_api_key', $dprv_api_key);
				delete_option('dprv_password');
			}
		}
		else
		{
			// If there was a working api key, delete any recorded password from the options db
			$dprv_api_key = get_option('dprv_api_key');
			if ($dprv_api_key != false && $dprv_api_key != "")
			{
				delete_option('dprv_password');
			}
		}
		// End of API key code

		if (isset($certifyResponse['subscription_type']) && $certifyResponse['subscription_type'] != "")
		{
			$dprv_subscription_type = $certifyResponse['subscription_type'];
			update_option('dprv_subscription_type', $dprv_subscription_type);
			if (isset($certifyResponse['subscription_expiry']) && $certifyResponse['subscription_expiry'] != "")
			{
				$dprv_subscription_expiry = $certifyResponse['subscription_expiry'];
				update_option('dprv_subscription_expiry', $dprv_subscription_expiry);
			}
			else
			{
				update_option('dprv_subscription_expiry', '');
			}
		}

		//dprv_record_dp_action($dprv_post_id, $certifyResponse, $post_record->post_status, $digital_fingerprint);
		dprv_record_dp_action($dprv_post_id, $certifyResponse, $digital_fingerprint);

		// If this is first Digiprove action after enrollment record this fact
		// TODO - insert this code in user synchronisation stuff - Need to test?
		if (get_option('dprv_enrolled') != "Yes")
		{
			update_option('dprv_enrolled', 'Yes');
		}
		update_option('dprv_last_date_count', $today_count);

		$log->lwrite("Digiproving completed successfully");

		//update_option('dprv_last_result', __('Digiprove certificate id', 'dprv_cp') . ': ' . $certifyResponse['certificate_id'] . ' ' . $notice);
		$admin_message = __('Digiprove certificate id', 'dprv_cp') . ': ' . $certifyResponse['certificate_id'] . ' ' . $notice;
		update_option('dprv_last_result', $admin_message);
		update_option('dprv_pending_message', $admin_message);
		update_option('dprv_registration_status', 'OK');
	}

	$log->lwrite("finishing dprv_digiprove_post " . $dprv_post_id);
	return;
}


function dprv_strip_old_notice(&$content, &$dprv_certificate_id, &$dprv_utc_date_and_time, &$dprv_digital_fingerprint, &$dprv_certificate_url, &$dprv_copyright_year)
{
	$log = new DPLog();  
	//$log->lwrite("dprv_strip_old_notice starts");
	$certificate_info = "";
	$start_Digiprove = strpos($content, "<!--Digiprove_Start-->");
	$end_Digiprove = false;
	if ($start_Digiprove === false)
	{
		//$log->lwrite("did not find start_Digiprove marker");
		return;
	}
	$end_Digiprove = strpos($content, "<!--Digiprove_End-->");
	if ($end_Digiprove === false || $end_Digiprove <= $start_Digiprove)
	{
		//$log->lwrite("did not find end_Digiprove marker");
		return;
	}
	$posA = stripos($content, "<span", $start_Digiprove + 22);
	$posA2 = stripos($content, "<div", $start_Digiprove + 22);

	if ($posA === false && $posA2 === false)
	{
		//$log->lwrite("no span or div marker found");
		return;
	}
	if ($posA === false || ($posA2 !== false && $posA2 < $posA))
	{
		$posA = $posA2;
	}
	$Digiprove_notice = stripslashes(substr($content, $posA, ($end_Digiprove - $posA)));
	$log->lwrite("notice=" . $Digiprove_notice);

	// Find Date of Certification
	$posA = stripos($Digiprove_notice, "title=\"certified");
	$posB = stripos($Digiprove_notice, " UTC by Digiprove certificate ");

	if ($posA === false || $posB === false || $posA >= $posB)
	{
		$log->lwrite("No text UTC by Digiprove certificate");
		return;
	}
	$dprv_utc_date_and_time = substr($Digiprove_notice, $posA + 16, ($posB-$posA-12));

	// Find Certificate ID
	$posA = $posB + 30;
	$remainder = substr($Digiprove_notice, $posA);
	$posB = strpos($remainder, "\"");
	if ($posB === false)
	{
		$log->lwrite("couldnt find qm in remainder");
		return;
	}
	$dprv_certificate_id = substr($remainder, 0, $posB);
	
	// Find Certificate Url
	$posA = stripos($Digiprove_notice, "href=");
	if ($posA === false)
	{
		$log->lwrite("couldnt find href= in notice");
		return;
	}
	$remainder = substr($Digiprove_notice, $posA);
	$remainder = strpbrk($remainder, '\'\"');
	if ($remainder === false)
	{
		$log->lwrite("could not find starting delimiter");
		return;
	}

	$delimiter = substr($remainder,0,1);
	$remainder = substr($remainder,1);
	$posB = strpos($remainder, $delimiter);

	if ($posB === false)
	{
		$log->lwrite("could not find finishing delimiter");
		return;
	}
	$dprv_certificate_url = substr($remainder, 0, $posB);

	// Find year of certification
	$posA = stripos($Digiprove_notice, "&copy;");
	if ($posA === false)
	{
		$posA = strpos($Digiprove_notice, chr(169));
	}
	if ($posA !== false)
	{
		$remainder = substr($Digiprove_notice, $posA + 2);
		$posA = strpos($remainder, "20");		// creating y2.1k problem (for someone else)
		if ($posA !== false)
		{
			$dprv_copyright_year = substr($remainder, $posA, 4);

			// Find Name
			$posB = stripos($remainder, "</");	// was "</span>"
			if ($posB !== false && $posB > ($posA + 4))
			{
				$name = substr($remainder, $posA+5, $posB-$posA-5);
			}
			else
			{
				$log->lwrite("could not find name");
			}
		}
		else
		{
			$log->lwrite("could not find year, didn't look for name");	// Bit odd
		}
	}
	else
	{
		$log->lwrite("could not find c symbol, did not search for year or name");
	}


	// Find Digital Fingerprint
	$posA = strpos($remainder, "<!--");
	if ($posA !== false)
	{
		$remainder = substr($remainder, $posA + 4);
		$posB = strpos($remainder, "-->");
		if ($posB !== false)
		{
			$dprv_digital_fingerprint = substr($remainder, 0, $posB);
		}
		else
		{
			$log->lwrite("could not find end marker for digital fingerprint");
		}
	}
	else
	{
		$log->lwrite("could not find start marker for digital fingerprint");
	}

	// Remove old notice
	if ($start_Digiprove != 0)
	{
		$content = substr($content, 0, $start_Digiprove) . substr($content, $end_Digiprove + 20);
	}
	else
	{
		$content = substr($content, $end_Digiprove + 20);
	}
	$log->lwrite("successfully parsed old notice and removed it");
	return;
}

function dprv_record_copyright_details($dprv_post_id)
{
	global $wpdb;
	$log = new DPLog();
	$log->lwrite("dprv_record_copyright_details starts");

	$digiprove_this_post = false;
	if (isset($_POST['dprv_this']) && $_POST['dprv_this'] == "Yes")
	{
		$digiprove_this_post = true;
	}

	$dprv_bool_this_all_original = false;
	if (isset($_POST['dprv_all_original']) && $_POST['dprv_all_original'] == "Yes")
	{
		$dprv_bool_this_all_original = true;
	}
	$attributions = "";
	if (isset($_POST['dprv_attributions']))
	{
		$attributions = $_POST['dprv_attributions'];
	}

	$dprv_using_default_license = true;
	if (!isset($_POST['dprv_default_license']) || $_POST['dprv_default_license'] != "on")
	{
		$dprv_using_default_license = false;
	}

	if (!isset($_POST['dprv_custom_license']) || $_POST['dprv_custom_license'] != "on")
	{
		// TODO: check whether $_POST['dprv_license_type'] is set to avoid PHP Notices in logfile
		$license = $_POST['dprv_license_type'];
		$custom_license_caption = null;
		$custom_license_abstract = null;
		$custom_license_url = null;
	}
	else
	{
		$license = $_POST['dprv_license_input'];
		$custom_license_caption = $_POST['dprv_license_caption'];
		$custom_license_abstract = $_POST['dprv_license_abstract'];
		$custom_license_url = $_POST['dprv_license_url_input'];
	}

	$sql="SELECT * FROM " . get_option('dprv_prefix') . "dprv_posts WHERE id = " . $dprv_post_id;
	$dprv_post_info = $wpdb->get_row($sql, ARRAY_A);
	if (!is_null($dprv_post_info) && count($dprv_post_info) > 0)
	{
		if (false === $wpdb->update(get_option('dprv_prefix') . "dprv_posts", array('digiprove_this_post'=>(int)$digiprove_this_post, 'this_all_original'=>(int)$dprv_bool_this_all_original, 'attributions'=>$attributions, 'using_default_license'=>(int)$dprv_using_default_license, 'license'=>$license, 'custom_license_caption'=>$custom_license_caption, 'custom_license_abstract'=>$custom_license_abstract, 'custom_license_url'=>$custom_license_url), array('id'=>$dprv_post_id), array('%d','%d','%s','%d','%s','%s','%s','%s'), '%d'))
		{
			$dprv_this_event = $wpdb->last_error . ' updating copyright details for ' . $dprv_post_id;
			dprv_record_event($dprv_this_event);
		}
	}
	else
	{
		if (false === $wpdb->insert(get_option('dprv_prefix') . "dprv_posts", array('id'=>$dprv_post_id, 'digiprove_this_post'=>(int)$digiprove_this_post, 'this_all_original'=>(int)$dprv_bool_this_all_original, 'attributions'=>$attributions, 'using_default_license'=>(int)$dprv_using_default_license,  'license'=>$license, 'custom_license_caption'=>$custom_license_caption, 'custom_license_abstract'=>$custom_license_abstract, 'custom_license_url'=>$custom_license_url), array('%d','%d','%d','%s','%d','%s','%s','%s','%s')))
		{
			$dprv_this_event = $wpdb->last_error . ' inserting copyright details for ' . $dprv_post_id;
			dprv_record_event($dprv_this_event);
		}
	}
}

//function dprv_record_dp_action($dprv_post_id, $certifyResponse, $dprv_post_status, $digital_fingerprint)
function dprv_record_dp_action($dprv_post_id, $certifyResponse, $digital_fingerprint)
{
	global $wpdb;
	$log = new DPLog();  
	$log->lwrite("dprv_record_dp_action starts. post id = " . $dprv_post_id);

	$dprv_certificate_id = $certifyResponse['certificate_id'];
	$dprv_certificate_url = $certifyResponse['certificate_url'];
    // below is temporarily added until Digiprove server updated accordingly
    $dprv_certificate_url = str_replace("http://", "https://", $dprv_certificate_url);
	$dprv_utc_date_and_time = $certifyResponse['utc_date_and_time'];
	$dprv_time_recorded = time();
	if (isset($certifyResponse['digital_fingerprint']) && $certifyResponse['digital_fingerprint'] != "")
	{
		$dprv_digital_fingerprint = $certifyResponse['digital_fingerprint'];
	}
	else
	{
		// If this is PHP 5.1.2 or later, we already have digital fingerprint, use this if not supplied by server
		$dprv_digital_fingerprint = $digital_fingerprint;
	}

	$posA = strpos($dprv_utc_date_and_time, " 20");	// crude way of finding year
	$dprv_year_certified = 2010;  // default
	if ($posA != false)
	{
		$dprv_year_certified = intval(substr($dprv_utc_date_and_time, $posA+1, 4));
	}
///////////////////////////////////////////////////////////// Should never happen, as this trapped earlier
	if (trim($dprv_post_id == ""))
	{
		$message = "dp_action: dprv_post_id is empty";
		dprv_record_event($message);
	}
	else
	{

		$sql="SELECT * FROM " . get_option('dprv_prefix') . "dprv_posts WHERE id = %d";
		$dprv_post_info = dprv_wpdb("get_row", $sql, $dprv_post_id);
		if (!is_null($dprv_post_info) && count($dprv_post_info) > 0)
		{
			if ($dprv_post_info["first_year"] == null)
			{
				$log->lwrite("first year is null");
			}
			else
			{
				$dprv_year_certified = intval($dprv_post_info["first_year"]);
			}
			if (false === $wpdb->update(get_option('dprv_prefix') . "dprv_posts", array('certificate_id'=>$dprv_certificate_id, 'digital_fingerprint'=>$dprv_digital_fingerprint, 'certificate_url'=>$dprv_certificate_url, 'cert_utc_date_and_time'=>$dprv_utc_date_and_time, 'first_year'=>$dprv_year_certified, 'last_time_digiproved'=>$dprv_time_recorded, 'last_fingerprint'=>$dprv_digital_fingerprint, 'last_time_updated'=>$dprv_time_recorded), array('id'=>$dprv_post_id), array('%s','%s','%s','%s','%d','%d','%s','%d'),'%d'))
			{
				$dprv_db_error = $wpdb->last_error;
				$dprv_this_event = $dprv_db_error . ' updating dp action for ' . $dprv_post_id . ', last_query ' . $wpdb->last_query;
				if (strpos($dprv_db_error, "Unknown column 'last_time_digiproved'") !== false)
				{
					$dprv_this_event .= " (now trying without new columns)";
				}
				dprv_record_event($dprv_this_event);

				// TODO - Remove code below once issue resolved re sporadic failure to add new columns @ 2.nn
				if (strpos($dprv_db_error, "Unknown column 'last_time_digiproved'") !== false)
				{
					//if (false === $wpdb->update(get_option('dprv_prefix') . "dprv_posts", array('certificate_id'=>$dprv_certificate_id, 'digital_fingerprint'=>$dprv_digital_fingerprint, 'certificate_url'=>$dprv_certificate_url, 'cert_utc_date_and_time'=>$dprv_utc_date_and_time, 'first_year'=>$dprv_year_certified), array('id'=>$dprv_post_id)))
					if (false === $wpdb->update(get_option('dprv_prefix') . "dprv_posts", array('certificate_id'=>$dprv_certificate_id, 'digital_fingerprint'=>$dprv_digital_fingerprint, 'certificate_url'=>$dprv_certificate_url, 'cert_utc_date_and_time'=>$dprv_utc_date_and_time, 'first_year'=>$dprv_year_certified), array('id'=>$dprv_post_id), array('%s','%s','%s','%s','%d'), '%d'))
					{
						$dprv_this_event = $wpdb->last_error . ' on retry of updating dp action for ' . $dprv_post_id . ', last_query ' . $wpdb->last_query;
						dprv_record_event($dprv_this_event);
					}
				}
			}
			$sql="DELETE FROM ". get_option('dprv_prefix') . "dprv_post_content_files WHERE post_id = " . $dprv_post_id;
			if (false === $wpdb->query($sql))
			{
				$dprv_this_event = $wpdb->last_error . ' deleting dprv_post_content_files for ' . $dprv_post_id . ', last_query ' . $wpdb->last_query;
				dprv_record_event($dprv_this_event);
			}
		}
		else
		{
			//if (false === $wpdb->insert(get_option('dprv_prefix') . "dprv_posts", array('id'=>$dprv_post_id, 'digiprove_this_post'=>true, 'this_all_original'=>true,'using_default_license'=>true, 'certificate_id'=>$dprv_certificate_id, 'digital_fingerprint'=>$dprv_digital_fingerprint, 'certificate_url'=>$dprv_certificate_url, 'cert_utc_date_and_time'=>$dprv_utc_date_and_time, 'first_year'=>$dprv_year_certified, 'last_time_digiproved'=>$dprv_time_recorded, 'last_fingerprint'=>$dprv_digital_fingerprint, 'last_time_updated'=>$dprv_time_recorded)))
			if (false === $wpdb->insert(get_option('dprv_prefix') . "dprv_posts", array('id'=>$dprv_post_id, 'digiprove_this_post'=>1, 'this_all_original'=>1,'using_default_license'=>1, 'certificate_id'=>$dprv_certificate_id, 'digital_fingerprint'=>$dprv_digital_fingerprint, 'certificate_url'=>$dprv_certificate_url, 'cert_utc_date_and_time'=>$dprv_utc_date_and_time, 'first_year'=>$dprv_year_certified, 'last_time_digiproved'=>$dprv_time_recorded, 'last_fingerprint'=>$dprv_digital_fingerprint, 'last_time_updated'=>$dprv_time_recorded), array('%d', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%d')))
			{
				$dprv_db_error = $wpdb->last_error;
				//$dprv_this_event = 'error ' . $wpdb->last_error . ' inserting dp action for ' . $dprv_post_id . ', last_query ' . $wpdb->last_query;
				$dprv_this_event = $dprv_db_error . ' inserting dp action for ' . $dprv_post_id . ', last_query ' . $wpdb->last_query;
				if (strpos($dprv_db_error, "Unknown column 'last_time_digiproved'") !== false)
				{
					$dprv_this_event .= " (now trying without new columns)";
				}
				dprv_record_event($dprv_this_event);

				// TODO - Remove code below once issue resolved re sporadic failure to add new columns @ 2.nn
				if (strpos($dprv_db_error, "Unknown column 'last_time_digiproved'") !== false)
				{
					//if (false === $wpdb->insert(get_option('dprv_prefix') . "dprv_posts", array('id'=>$dprv_post_id, 'digiprove_this_post'=>true, 'this_all_original'=>true,'using_default_license'=>true, 'certificate_id'=>$dprv_certificate_id, 'digital_fingerprint'=>$dprv_digital_fingerprint, 'certificate_url'=>$dprv_certificate_url, 'cert_utc_date_and_time'=>$dprv_utc_date_and_time, 'first_year'=>$dprv_year_certified)))
					if (false === $wpdb->insert(get_option('dprv_prefix') . "dprv_posts", array('id'=>$dprv_post_id, 'digiprove_this_post'=>1, 'this_all_original'=>1,'using_default_license'=>1, 'certificate_id'=>$dprv_certificate_id, 'digital_fingerprint'=>$dprv_digital_fingerprint, 'certificate_url'=>$dprv_certificate_url, 'cert_utc_date_and_time'=>$dprv_utc_date_and_time, 'first_year'=>$dprv_year_certified), array('%d','%d','%d','%d','%s','%s','%s','%s', '%d')))
					{
						$dprv_db_error = $wpdb->last_error;
						$dprv_this_event = $dprv_db_error . ' on retry of inserting dp action for ' . $dprv_post_id . ', last_query ' . $wpdb->last_query;
						dprv_record_event($dprv_this_event);
					}
				}
			}
		}


		if (isset($certifyResponse['content_files']))
		{
			if (count($certifyResponse['content_files']) > 0)
			{
				foreach($certifyResponse['content_files'] as $filename => $file_fingerprint)
				{
					//if (false === $wpdb->insert(get_option('dprv_prefix') . "dprv_post_content_files", array('post_id'=>$dprv_post_id, 'filename'=>$filename, 'digital_fingerprint'=>$file_fingerprint)))
					if (false === $wpdb->insert(get_option('dprv_prefix') . "dprv_post_content_files", array('post_id'=>$dprv_post_id, 'filename'=>$filename, 'digital_fingerprint'=>$file_fingerprint), array('%d', '%s', '%s')))
					{
						$dprv_this_event = $wpdb->last_error . ' inserting dprv_post_content_files for ' . $dprv_post_id . ', last_query ' . $wpdb->last_query;
						dprv_record_event($dprv_this_event);
						break;
					}
				}
			}
		}
	}
}


// TODO: Complete implementation of $dprv_max_file_count (was added in integrity check upgrade)
function dprv_record_non_dp_action($dprv_post_id, $content, $dprv_max_file_count)
{
	global $wpdb;
	$log = new DPLog();  
	$log->lwrite("dprv_record_non_dp_action starts. post id = " . $dprv_post_id);
	$rawContent = dprv_getRawContent($content, $digital_fingerprint);
	if ($rawContent == "")
	{
		return __("Content is empty", "dprv_cp");	// return value unnecessary?
	}

	$dprv_time_recorded = time();

	if (trim($dprv_post_id == ""))
	{
		$message = "non_dp_action: dprv_post_id is empty";
		dprv_record_event($message);
	}
	else
	{
		$sql="SELECT * FROM " . get_option('dprv_prefix') . "dprv_posts WHERE id = %d";
		$dprv_post_info = dprv_wpdb("get_row", $sql, $dprv_post_id);
		if (!is_null($dprv_post_info) && count($dprv_post_info) > 0)
		{
			//if (false === $wpdb->update(get_option('dprv_prefix') . "dprv_posts", array('last_fingerprint'=>$digital_fingerprint, 'last_time_updated'=>$dprv_time_recorded), array('id'=>$dprv_post_id)))
			if (false === $wpdb->update(get_option('dprv_prefix') . "dprv_posts", array('last_fingerprint'=>$digital_fingerprint, 'last_time_updated'=>$dprv_time_recorded), array('id'=>$dprv_post_id), array('%s','%d'), '%d'))
			{
				$dprv_this_event = $wpdb->last_error . ' updating non_dp action for ' . $dprv_post_id . ', last_query ' . $wpdb->last_query;
				dprv_record_event($dprv_this_event);
			}
			$sql="DELETE FROM ". get_option('dprv_prefix') . "dprv_post_content_files WHERE post_id = %d";
			dprv_wpdb("query", $sql, $dprv_post_id);
		}
		else
		{
			if (false === $wpdb->insert(get_option('dprv_prefix') . "dprv_posts", array('id'=>$dprv_post_id, 'last_fingerprint'=>$digital_fingerprint, 'last_time_updated'=>$dprv_time_recorded), array('%d', '%s', '%d')))
			{
				$dprv_this_event = $wpdb->last_error . ' inserting non_dp action for ' . $dprv_post_id . ', last_query ' . $wpdb->last_query;
				dprv_record_event($dprv_this_event);
			}
		}
		if (function_exists("hash") && get_option('dprv_files_integrity') == "Yes" && $dprv_max_file_count > 0)		// Scan for files if local integrity-checking required)
		{
			$content_files = array();
			$content_file_names = array();
			$file_count = 0;							// Initialise just to avoid a notice message, not essential
			dprv_getContentFiles($dprv_post_id, $rawContent, $content_files, $content_file_names, $dprv_max_file_count, $file_count);
			// now write that stuff to db
			if ($file_count > 0)
			{
				$log->lwrite(dprv_eval($content_files));
				//foreach($content_files as $filename => $file_fingerprint)
				for ($i=0; $i<count($content_files); $i++)
				{
					$full_path = $content_files[$i];
					$file_name = basename($full_path);
					$file_data = @file_get_contents($full_path);
					if ($file_data != false)
					{
						$file_fingerprint = strtoupper(hash("sha256", $file_data));
						$log->lwrite("filepath=$filename, fingerprint=$fingerprint");
						if (false === $wpdb->insert(get_option('dprv_prefix') . "dprv_post_content_files", array('post_id'=>$dprv_post_id, 'filename'=>$file_name, 'digital_fingerprint'=>$file_fingerprint), array('%d', '%s', '%s')))
						{
							$dprv_this_event = $wpdb->last_error . ' inserting dprv_post_content_files for ' . $dprv_post_id . ', last_query ' . $wpdb->last_query;
							dprv_record_event($dprv_this_event);
							break;
						}
					}
				}

			}
		}
	}
}


function dprv_certify($dprv_post_id, $title, $content, &$digital_fingerprint, &$content_file_names, $dprv_max_file_count, $dprv_subscription_type, $dprv_subscription_expiry, &$dprv_last_time,&$notice)
{
	$log = new DPLog();  
	global $wp_version, $wpdb, $dprv_blog_host, $dprv_wp_host;

	$log->lwrite("dprv_certify starts");

	$rawContent = dprv_getRawContent($content, $digital_fingerprint);
	if ($rawContent == "")
	{
		return __("Content is empty", "dprv_cp");
	}

	if ($digital_fingerprint != "")
	{
		$sql="SELECT * FROM " . get_option('dprv_prefix') . "dprv_posts WHERE id = %d";
		$dprv_post_info = dprv_wpdb("get_row", $sql, $dprv_post_id);
		if (!is_null($dprv_post_info) && count($dprv_post_info) > 0 && $digital_fingerprint == $dprv_post_info["digital_fingerprint"])
		{
			$dprv_last_time = strtotime($dprv_post_info["cert_utc_date_and_time"]);
			return __("Content unchanged since last edit", "dprv_cp");	// Content has not changed, do not Digiprove again - stick with earlier certification
		}
	}

	$dprv_subscription_expired = "No";
	$dprv_expiry_timestamp = strtotime($dprv_subscription_expiry . ' 23:59:59 +0000') + 864000;			// add 10-day grace period (also allows for any unforeseen timezone issues)
	if ($dprv_expiry_timestamp != false && $dprv_expiry_timestamp != -1 && time() > $dprv_expiry_timestamp)
	{
		$dprv_subscription_expired = "Yes";
		$dprv_max_file_count = 0;
	}

	$content_files = array();
	$content_file_names = array();
	$file_count = 0;							// Initialise just to avoid a notice message, not essential
	$total_url_count = 0;							// Initialise just to avoid a notice message, not essential
	if (function_exists("hash") && ($dprv_max_file_count > 0 || $dprv_subscription_expired == "Yes"))		// Scan for files if permitted (or for advisory notice if subscription expired)
	{
		dprv_getContentFiles($dprv_post_id, $rawContent, $content_files, $content_file_names, $dprv_max_file_count, $file_count, $total_url_count);
	}
	if ($file_count > 0)
	{
		if ($file_count > $dprv_max_file_count)
		{
			if ($dprv_subscription_expired == "Yes")
			{
				$notice_text = sprintf(__('- Evidence of content has been created (all html and 0/%s selected media files)', 'dprv_cp'), $file_count);
				$notice_tooltip = sprintf(__('Media files not Digiproved as your subscription expired on %s', 'dprv_cp'), $dprv_subscription_expiry);
				$notice = '<span title=\'' . $notice_tooltip . '\'>' . $notice_text . '</span>';
			}
			else
			{
				$notice_text = sprintf(__('- Evidence of content has been created (all html and %1$s/%2$s selected media files)', 'dprv_cp'), $dprv_max_file_count, $file_count);
				$notice_tooltip = sprintf(__('The %1$s plan covers a maximum of %2$s media files in a single post/page', 'dprv_cp'), $dprv_subscription_type, $dprv_max_file_count);
				$notice = '<span title=\'' . $notice_tooltip . '\'>' . $notice_text . '</span>';
			}
		}
		else
		{
			$notice = sprintf(__('- Evidence of content has been created (all html and %s/%s selected media files)', 'dprv_cp'), $file_count, $file_count);
		}
	}
	else
	{
		if ($total_url_count == 0 && $dprv_max_file_count > 0)
		{
			$notice_text = __("- Evidence of this content has been created.", "dprv_cp");
			$notice_tooltip = __('No media files were referenced in this post/page', 'dprv_cp');
			$notice = '<span title=\'' . $notice_tooltip . '\'>' . $notice_text . '</span>';
		}
		else
		{
			if ($dprv_max_file_count == 0)
			{
				$notice_text = __("- Evidence of this text and HTML content has been created.", "dprv_cp");
				$notice_tooltip = __('HTML only, media files are not supported by your subscription', 'dprv_cp');
				$notice = '<span title=\'' . $notice_tooltip . '\'>' . $notice_text . '</span>';
			}
			else
			{
				$notice_text = __("- Evidence of this text and HTML content has been created.", "dprv_cp");
				$notice_tooltip = __('No media files were selected for Digiproving (based on your &quot;Certifying Content&quot; settings)', 'dprv_cp');
				$notice = '<span title=\'' . $notice_tooltip . '\'>' . $notice_text . '</span>';
			}
		}
	}

	$dprv_content_type = get_option('dprv_content_type');
	if (trim($dprv_content_type) == "")
	{
		$dprv_content_type = "Blog post";
	}

	$credentials = array("user_id" => trim(get_option('dprv_user_id')), "domain_name" => $dprv_blog_host, "api_key" => get_option('dprv_api_key'));
	if ($dprv_blog_host != $dprv_wp_host)
	{
		$credentials['alt_domain_name'] = $dprv_wp_host;
	}
	$dprv_event = get_option('dprv_event');
	if ($dprv_event !== false && $dprv_event != "")
	{
		$credentials['dprv_event'] = $dprv_event;
	}

	$script_name = pathinfo($_SERVER['PHP_SELF'], PATHINFO_BASENAME);
	$posDot = strrpos($script_name,'.');
	if ($posDot != false)
	{
		$script_name = substr($script_name, 0, $posDot);
	}

	$user_agent = "Copyright Proof " . DPRV_VERSION . " / Wordpress " . $wp_version . " / " . $script_name;
	$permalink = get_bloginfo('url');
	if ($dprv_post_id != -1)									// will be set to -1 if the value is unknown
	{
		// Note - Decided not to implement soft permalinks as can end up with broken links if user changes scheme
		$permalink = get_bloginfo('url') . "/?p=" . $dprv_post_id;
	}

	$obscure_url = false;
	if (get_option('dprv_obscure_url') != "Clear")
	{
		$obscure_url = true;
	}
	$linkback_flag = false;
	if (get_option('dprv_linkback') == "Linkback")
	{
		$linkback_flag = true;
	}
	$email_certs_flag = false;
	if (get_option('dprv_email_certs') == "Yes")
	{
		$email_certs_flag = true;
	}
	$save_content_flag = false;
	if (get_option('dprv_save_content') == "SaveContent")
	{
		$save_content_flag = true;
	}
	$metadata = array("content_title"=>$title);
	if (get_option('dprv_submitter_is_author') == "Yes")
	{
		$dprv_post_object = get_post($dprv_post_id);
		if (is_object($dprv_post_object) && isset($dprv_post_object->post_author))
		{
			$dprv_post_author = $dprv_post_object->post_author;
			$dprv_author_object = get_user_by('id', $dprv_post_author);
			$dprv_author = trim($dprv_author_object->first_name . ' ' . $dprv_author_object->last_name);
			$metadata["authors"] = $dprv_author;
		}
	}
	$document_tracking = array("original_document_id"=>$dprv_post_id);
	$return_value = Digiprove::certify($error_message, $credentials, $rawContent, $digiproved_content, $content_files, $dprv_content_type, $metadata, $document_tracking, $user_agent, $permalink, $linkback_flag, $obscure_url, false, $email_certs_flag, $save_content_flag);
	if (!$return_value)
	{
		return $error_message;
	}
	return $return_value;
}

// In current form this does not find all embedded media, even those attached explicitly - needs more testing
function dprv_alt_getContentFiles($dprv_post_id, $content, &$content_files, $dprv_max_file_count, &$file_count, $alltags = false)
{
	$log = new DPLog();	
	//$dprv_post = get_post($dprv_post_id);

	//$attachments = get_posts(array('post_type' => 'attachment',	'posts_per_page' => -1,	'post_parent' => $dprv_post_id, 'exclude' => get_post_thumbnail_id()));
	$attachments = get_posts(array('post_type' => 'attachment',	'posts_per_page' => -1,	'post_parent' => $dprv_post_id));

	if ($attachments) 
	{
		$log->lwrite("attachments=\n" . dprv_eval($attachments));
		foreach ($attachments as $attachment)
		{
			$class = "post-attachment mime-" . sanitize_title( $attachment->post_mime_type );
			$thumbimg = wp_get_attachment_link( $attachment->ID, 'thumbnail-size', true );
			//echo '<li class="' . $class . ' data-design-thumbnail">' . $thumbimg . '</li>';
		}
	}
	else
	{
		$log->lwrite("no attachments");
	}
}

// Examine HTML to find all references to files
function dprv_getContentFiles($dprv_post_id, $content, &$content_files, &$content_file_names, $dprv_max_file_count, &$file_count, &$total_url_count, $alltags = false)
{
	$start_marker = 'started dprv_getContentFiles';
	if (!is_array($content_files))
	{
		$content_files = array();
	}
	if (!is_array($content_file_names))
	{
		$content_file_names = array();
	}

	// TODO - make endless loop check on time rather than loop count:
	$start_time = time();
	$dprv_event = dprv_record_event ($start_marker);		// record temporary marker (will remove later if normal exit)
	global $dprv_mime_types;
	$log = new DPLog();  
	$log->lwrite("dprv_getContentFiles starts:");

	$file_count = 0;
	$total_url_count = 0;
	$blog_url = site_url();
	$blog_url_info = parse_url($blog_url);
	$blog_host = $blog_url_info["host"];
	$blog_path = $blog_url_info["path"];
	$root_path = ABSPATH;
	if ($blog_path != "" && strlen($root_path) > strlen($blog_path))
	{
		// strip off blog path to give rootpath corresponding to domain root
		if ((strripos($root_path, $blog_path) + 1) == strlen($root_path) - strlen($blog_path))
		{
			$root_path = substr($root_path, 0, strlen($root_path) - strlen($blog_path));
		}
		else
		{
			$this_event = 'ABSPATH ' . ABSPATH . ' and blog path ' . $blog_path . ' do not have same endings';
			$log->lwrite($this_event);
			$dprv_event = dprv_record_event($this_event, $dprv_event);
			$log->lwrite(strpos($root_path, $blog_path) . "!=" . strlen($root_path) . "-" .strlen($blog_path));
		}
	}

	$dprv_html_tags = get_option('dprv_html_tags');

	$needScan = false;
	if (is_array($dprv_html_tags))
	{
		if ($alltags == false)
		{
			foreach ($dprv_html_tags as $key=>$value)
			{
				if ($dprv_html_tags[$key]["selected"] == "True")
				{
					$needScan = true;
				}
			}
		}
		if ($needScan === false)
		{
			$log->lwrite("getContentFiles: no tags selected, skip parsing for files");
			$total_url_count = -2;
			//return;
		}
	}
	else
	{
		$log->lwrite("getContentFiles: dprv_html_tags is not an array");
		//dprv_unrecord_event($start_marker, $dprv_event);						// remove start marker from dprv event
		//return;
	}
	if ($needScan == true)
	{
		$dprv_outside_media = get_option('dprv_outside_media');
		$t = 0;
		$pos = 0;
		$delimit_chars = " \t\r\f\v\n/>";	//Space, tab, carriage-return, formfeed, vertical-tab, newline, / or >

		$parse_all = false;
		if ($dprv_html_tags["notag"]["selected"] == "True" || $alltags == true)
		{
			$parse_all = true;
		}
		$w=0;
		while ($pos !== false)
		{
			$w++;
			if ($w>1000)
			{
				$this_event = 'post ' . $dprv_post_id . ' suspected endless loop (a)';
				$dprv_event = dprv_record_event($this_event, $dprv_event);
				break;
			}

			if ($parse_all == true)
			{
				// scan the bit from here to next tag:
				$pos = strpos($content, "<");
				if ($pos === false)
				{
					$no_tag_content = $content;
				}
				else
				{
					if ($pos == 0)
					{
						$no_tag_content = "";
					}
					else
					{
						$no_tag_content = substr($content,0,$pos);
					}
				}
				$r=0;
				while ($no_tag_content != "")
				{
					//$log->lwrite("at start of notag while loop, no_tag_content begins with " . substr($no_tag_content,0,30));
					$r++;
					if ($r>300)
					{
						$this_event = 'post ' . $dprv_post_id . ' suspected endless loop (b)';
						$log->lwrite($this_event);
						$dprv_event = dprv_record_event($this_event, $dprv_event);
						break;
					}
					$pos2 = stripos($no_tag_content, "http://");
					$pos3 = stripos($no_tag_content, "https://");
					if ($pos2 !== false || $pos3 !== false)
					{
						if ($pos2 === false)
						{
							$pos1 = $pos3;
						}
						else
						{
							if ($pos3 === false)
							{
								$pos1 = $pos2;
							}
							else
							{
								$pos1 = min($pos2, $pos3);
							}
						}
						if ($pos1 > 0 && ($no_tag_content[$pos1-1] == '"' || $no_tag_content[$pos1-1] == "'"))
						{
							$pos2 = strpos($no_tag_content, $no_tag_content[$pos1-1], $pos1+1);
						}
						else
						{
							$pos2 = dprv_str_findAny($no_tag_content, array(" ","\t","\r","\f","\v","\n","<",">"), $pos1+1);
						}
						if ($pos2 !== false)
						{
							$url = substr($no_tag_content,$pos1,$pos2-$pos1);
							$no_tag_content = substr($no_tag_content,$pos2);
						}
						else
						{
							$url = substr($no_tag_content,$pos1);
							$no_tag_content = "";
						}
						dprv_processUrl($url, $dprv_html_tags, "notag", $content_files, $content_file_names, $t, $root_path, $blog_url, $blog_host, $blog_path, $dprv_max_file_count, $file_count, $total_url_count);
					}
					else
					{
						$no_tag_content = "";
						break;
					}
				}
			}
			$tag = "";
			$pos = strpos($content, "<");
			if ($pos === false || $pos == strlen($content)-1)
			{
				break;
			}
			for ($pos2=$pos+1; $pos2<strlen($content); $pos2++)
			{
				if (strpos($delimit_chars, $content[$pos2]) !== false)
				{
					break;
				}
			}
			if ($pos2 > strlen($content)-4)
			{
				break;
			}
			$tag = strtolower(substr($content, $pos+1,3));  // just for log message
			$end_of_tag_pos = strpos($content, ">",$pos+1);				// end of this tag
			//$log->lwrite("tag = $tag, starts at $pos and ends at $end_of_tag_pos");
			if ($pos2 > 0 && $content[$pos2] != "/" && $content[$pos2] != ">" && $end_of_tag_pos !== false && $end_of_tag_pos > ($pos2+1))   // Only carry on with this tag if there is a modifier and the tag is closed
			{
				$tag = strtolower(substr($content, $pos+1, $pos2-$pos-1));

				if ($alltags == true || (isset($dprv_html_tags[$tag]) && $dprv_html_tags[$tag]["selected"] != "False"))	// If this tag is not on list of media types to be digiproved, skip
				{
					$modifiers = ltrim(substr($content, $pos2 + 1,$end_of_tag_pos-$pos2-1));
					//$log->lwrite("modifiers = $modifiers");
					$src_attribute = "src";
					if ($tag == "a")
					{
						$src_attribute = "href";
					}
					$pos1 = stripos($modifiers, $src_attribute);
					if ($pos1 !== false && $pos1 < (strlen($modifiers)-6))  
					{
						$modifiers = ltrim(substr($modifiers, $pos1+strlen($src_attribute)));
						if (strpos($modifiers, "=") == 0 && strlen($modifiers) > 2)
						{
							$modifiers = ltrim(substr($modifiers,1));
							if (strlen($modifiers) > 2 && ($modifiers[0] == "'" || $modifiers[0] == '"'))
							{
								$delimiter = $modifiers[0];
								$modifiers = ltrim(substr($modifiers,1));
								$pos1 = strpos($modifiers, $delimiter);
								if ($pos1 !== false && $pos1 != 0)
								{
									$url = substr($modifiers, 0, $pos1);
									dprv_processUrl($url, $dprv_html_tags, $tag, $content_files, $content_file_names, $t, $root_path, $blog_url, $blog_host, $blog_path, $dprv_max_file_count, $file_count, $total_url_count);
								}
							}
							else
							{
								$log->lwrite("ignoring - " . $tag . " tag - could not parse src/href part (2)");
							}
						}
						else
						{
							$log->lwrite("ignoring - " . $tag . " tag - could not parse src/href part (1)");
						}
					}
					else
					{
						$log->lwrite("ignoring - " . $tag . " tag - no src/href attribute");
					}
				}
				else
				{
					$log->lwrite("ignoring - " . $tag . " tag - not selected");
				}
			}
			else
			{
				$log->lwrite("ignoring $tag tag - no modifier");
			}

			if ($end_of_tag_pos !== false && $end_of_tag_pos < (strlen($content)-3))
			{
				$content = ltrim(substr($content,$end_of_tag_pos+1));
			}
			else
			{
				break;
			}
		}
	}
	$featured_image_count = 0;
	if (get_option('dprv_featured_images') == "Yes")
	{
		$featured_image_path = dprv_get_featured_image($dprv_post_id);
		if ($featured_image_path != false && trim($featured_image_path) != "")
		{
			if (file_exists($featured_image_path))
			{
				if (array_search($featured_image_path, $content_files) === false)		// prevent duplicate references
				{
					$t = count($content_files);
					if($t < $dprv_max_file_count)
					{
						$file_name = basename($featured_image_path);
						$content_file_names[$t] = $file_name;
						$content_files[$t] = $featured_image_path;
						$log->lwrite("content_file_names[" . $t . "]=" . $content_file_names[$t] . " (is featured image)");
					}
					$file_count++;
					$total_url_count ++;
					//$featured_image_count = 1;
				}
				else
				{
					$log->lwrite("ignoring featured image - " . $file_name . " - encountered earlier)");
				}
			}
			else
			{
				$log->lwrite("Featured image file does not exist: $featured_image_path");
			}
		}
	}
	dprv_unrecord_event($start_marker, $dprv_event);						// remove start marker from dprv event
	$log->lwrite('unrecord_event just ran');
}
function dprv_str_findAny($haystack, $needles, $offset=0)
{
	for ($i=$offset; $i<strlen($haystack); $i++)
	{
		if (array_search($haystack[$i], $needles))
		{
			return $i;
		}
	}
	return false;
}
function dprv_processUrl($url, $dprv_html_tags, $tag, &$content_files, &$content_file_names, &$t, $root_path, $blog_url, $blog_host, $blog_path, $dprv_max_file_count, &$file_count, &$total_url_count)
{
	$log = new DPLog();
	$url = rtrim($url);
	//$log->lwrite("tag is $tag dprv_processUrl $url");
	if (!is_array($content_files))
	{
		$content_files = array();
	}
	if (!is_array($content_file_names))
	{
		$content_file_names = array();
	}
	$log->lwrite ("dprv_processUrl for $url, count of content_files = " . count($content_files));
	if (strpos($url, "#") === 0)
	{
		$log->lwrite ("just a link to bookmark on same page ($url), ignore");
		return;		// just a link to bookmark on same page, ignore
	}
	global $dprv_mime_types;
	$dprv_outside_media = get_option('dprv_outside_media');
	$url_info = parse_url($url);
	$url_string = $url;
	$blog_url_string = $blog_host . $blog_path;
	if (!isset($url_info["host"]))
	{
		if (!isset($url_info["path"]))
		{
			$log->lwrite("Could not get either host or path from $url");	// malformed url
			return;
		}
		if (strpos($url, "/") == 0)
		{
			$full_path = dprv_addPaths($root_path, $url_info["path"]);
		}
		else
		{
			$full_path = dprv_addPaths(ABSPATH, $url_info["path"]);
		}
	}
	else
	{
		if ($url_info["host"] != $blog_host)
		{
			// first check for partial matches
			$partial_match = false;
			if	(
					$url_info["host"] == ("www." . $blog_host)
					||
					("www." . $url_info["host"]) == $blog_host
					||
					(strripos($url_info["host"], $blog_host) !== false && strripos($url_info["host"], $blog_host) === strlen($url_info["host"]) - strlen($blog_host))
					||
					(strripos($blog_host, $url_info["host"]) !== false && strripos($blog_host, $url_info["host"]) === strlen($blog_host) - strlen($url_info["host"]))
				)
			{
				$log->lwrite("suspected match between " . $url_info["host"] . " and " . $blog_host);
				$partial_match = true;
				$url_info["host"] = $blog_host;		// use blog host value from now on
			}
			else
			{
				if ($dprv_outside_media != "Outside")
				{
					$log->lwrite("$url is not on this host");
					return;
				}
				else
				{
					// what to doo?
					return;  // don't handle this yet
				}
			}
		}
		$url_string = $url_info["host"] . $url_info["path"];
		if (stripos($url_string, $blog_url_string) !== 0)
		{
			$log->lwrite("$url is not on this wp web-site");
			return;
		}

		if (stripos($url_string, $blog_url_string) === 0 && isset($url_info["query"]) && strpos($url_info["query"], "attachment_id=") === 0)
		{
			$full_path = dprv_convert_attachment_to_file(substr($url_info["query"],14));
		}
		else
		{
			$full_path = dprv_addPaths($root_path, $url_info["path"]);
		}
	}
	$total_url_count ++;
	$file_name = basename($full_path);
	$ext = pathinfo($file_name, PATHINFO_EXTENSION);
	$file_selected = false;		// default value
	$selected_reason = "";		// this variable used just for logging / testing
	if ($dprv_html_tags[$tag]["incl_excl"] == "Include")
	{
		if ($dprv_html_tags[$tag]["All"] == "True")
		{
			$selected_reason .= "All filetypes included; ";
			$file_selected = true;
		}
		else
		{
			foreach ($dprv_html_tags[$tag] as $key=>$value)
			{
				if ($key != "All" && $key != "name" && $key != "selected" && $key != "incl_excl")
				{
					if ($value == "True")
					{
						if (array_search($ext, $dprv_mime_types[$key]))
						{
							$selected_reason .= "$ext included in $key list; ";
							$file_selected = true;
						}
					}
				}
			}
		}
	}
	else
	{
		$file_selected = true;		// default value if using Exclude
		$selected_reason .= "$ext not excluded";
		foreach ($dprv_html_tags[$tag] as $key=>$value)
		{
			if ($key != "All" && $key != "name" && $key != "selected" && $key != "incl_excl")
			{
				if ($value == "True")
				{
					if (array_search($ext, $dprv_mime_types[$key]))
					{
						$selected_reason .= "$ext excluded (in $key list)";
						$file_selected = false;
					}
				}
			}
		}
	}
	if ($file_selected == true)
	{
		if (file_exists($full_path))
		{
			if (array_search($full_path, $content_files) === false)		// prevent duplicate references
			{
				if($t < $dprv_max_file_count)
				{
					$content_file_names[$t] = $file_name;
					$content_files[$t] = $full_path;
					$log->lwrite("content_file_names[" . $t . "]=" . $content_file_names[$t] . " (from " . $tag . " tag), included because " . $selected_reason);
					$t++;
				}
				$file_count++;
			}
			else
			{
				$log->lwrite("ignoring - " . $file_name . " (from " . $tag . " tag - encountered earlier)");
			}
		}
		else
		{
			$log->lwrite("File does not exist: $full_path");
		}
	}
	else
	{
		$log->lwrite("ignoring - " . $file_name . " (from " . $tag . " tag) - $ext not a selected media type");
	}
}
function dprv_addPaths($root, $path)
{
	if ($path[0] == "/" && $root[strlen($root)-1] == "/")
	{
		$path = substr($path,1);
	}
	return $root . $path;
}
function dprv_convert_attachment_to_file($attach_id)
{
	$id = "";
	for ($i=0; $i<strlen($attach_id); $i++)
	{
		if (strpos("0123456789",$attach_id[$i]) !== false)
		{
			$id .= $attach_id[$i];
		}
		else
		{
			break;
		}
	}
	return get_attached_file($id);
}


// GET FEATURED IMAGE  
function dprv_get_featured_image($dprv_post_id) 
{  
	$post_thumbnail_id = get_post_thumbnail_id($dprv_post_id);  
	if ($post_thumbnail_id)
	{  
		$dprv_featured_image_path = get_attached_file($post_thumbnail_id);  
		return $dprv_featured_image_path;  
	}
	return false;
}  


function dprv_getTag($xmlString, $tagName)
{
	$start_contents = stripos($xmlString, "<" . $tagName . ">") + strlen($tagName) + 2;
	$end_tag = stripos($xmlString, "</" . $tagName . ">");
	if ($start_contents === false || $end_tag === false || $end_tag <= $start_contents)
	{
		return false;
	}
	return substr($xmlString, $start_contents, $end_tag - $start_contents);
}

// Extract raw content to be Digiproved, ignoring previous Digiprove embedded certs and rationalise to ignore effects of Wordpress formatting
function dprv_getRawContent($contentString, &$digital_fingerprint)
{
	$log = new DPLog();
	//$log->lwrite("getRawContent starts, content=" . $contentString);

	$raw_content = trim($contentString);
	$raw_content = dprv_normaliseContent($raw_content);
	$digital_fingerprint = "";

	if (function_exists("hash"))					// Before 5.1.2, the hash() function did not exist, calling it gives a fatal error
	{
		$digital_fingerprint = strtoupper(hash("sha256", $raw_content));
	}
	$log->lwrite("getRawContent finishes, strlen(raw_content)=" . strlen($raw_content) . ", hash=$digital_fingerprint");

	return $raw_content;
}


function dprv_normaliseContent($contentString)
{
	$log = new DPLog();  
	$contentString = htmlspecialchars_decode($contentString, ENT_QUOTES);  		// decode any encoded XML-incompatible characters now to ensure match with post-xml decoded string on server

	// Below is code inserted (at .70) after discovery that extra <p> and </p> tags are inserted when post is coming from WLW - maybe this is generated by wp.getPage or within WLW itself
	// Not strictly necessary, but improves chances of detecting unchanged content (which ideally should not be Digiproved)
	// TODO: improve normalisation to get around all this dickying with html that wp seems to do

	$pos = strlen($contentString) -7;
	if ($pos > 0 && substr($contentString, $pos) == "<p></p>")
	{
		$contentString = trim(substr($contentString, 0, $pos));
	}
	$pos = strlen($contentString);

	if ($pos > 7 && substr($contentString, 0, 3) == "<p>" && substr($contentString, $pos -4) == "</p>")
	{
		$contentString = trim(substr($contentString, 3, $pos-7));
	}
	// end of 0.70 inserted code
	return trim($contentString);
}


?>