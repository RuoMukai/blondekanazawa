<?php
	// FUNCTIONS CALLED WHEN CHECKING INTEGRITY OF POSTS OR PAGES
	// BUT ONE BELOW NOT CALLED!!
	function dprv_verifyContentFiles(&$error_message, $dprv_post_id, $content_file_table, &$match_results)
	{
		global $wpdb;
		$log = new DPLog();  
		$log->lwrite("verifyContentFiles begins");
		//$log->lwrite(dprv_eval($content_file_table));

		$success=true;				// default value 

		$sql="SELECT * FROM " . get_option('dprv_prefix') . "dprv_post_content_files WHERE post_id = %d";
		$dprv_post_files = dprv_wpdb("get_results", $sql, $dprv_post_id);
		$match_results = array();
		if (!is_null($dprv_post_files))
		{
			for ($i=0; $i<count($dprv_post_files); $i++)
			{
				$file = $dprv_post_files[$i];
				$filename=$file['filename'];
				$fingerprint = $file['digital_fingerprint'];
				$log->lwrite("i=$i, filename=$filename, fingerprint=$fingerprint");
				if (isset($content_file_table[$filename]))
				{
					if ($content_file_table[$filename] == $fingerprint)
					{
						$match_results[$filename] = __("Matched", "dprv_cp");
					}
					else
					{
						if ($content_file_table[$filename] == "Processed")
						{
							// Must be a duplicate filename, ignore
						}
						else
						{
							$match_results[$filename] = __("File contents changed", "dprv_cp");
							$success=false;
						}
					}
					$content_file_table[$filename] = "Processed";
				}
				else
				{
					$match_results[$filename] = __("No longer there", "dprv_cp");
					$success=false;
				}

			}
		}
		foreach ($content_file_table as $filename => $fingerprint)
		{
			if ($fingerprint != "Processed")
			{
				$match_results[$filename] = __("Was not there before", "dprv_cp");
				$success=false;
			}
		}
 		return $success;
	}
	function dprv_verify_callback()
	{
		global $dprv_blog_host;
		$log = new DPLog();  
		$log->lwrite("Ajax verify");
		$error_message = "";
		$certificate_id =  $_POST['certificate_id'];
		$digital_fingerprint =  $_POST['digital_fingerprint'];
		$content = null;
		$digiproved_content = null;
		$content_files = null;
		$user_agent = "Copyright Proof " . DPRV_VERSION;
		$credentials = array("user_id" => get_option('dprv_user_id'), "domain_name" => $dprv_blog_host, "api_key" => get_option('dprv_api_key'));
		$response = Digiprove::verify_fingerprint($error_message, $credentials, $certificate_id, $digital_fingerprint, $content, $digiproved_content, $content_files, $user_agent);
		echo "Response from Digiprove: " . $response["result"];
		die();
	}

	function dprv_verify_revision_callback()
	{
		global $dprv_blog_host;
		$log = new DPLog();
		$dprv_post_id = $_POST['dprv_post_id'];
		$log->lwrite("Ajax verify_revision for $dprv_post_id");
		$sql="SELECT * FROM " . get_option('dprv_prefix') . "posts WHERE ID = %d";
		$dprv_post = dprv_wpdb("get_row", $sql, $dprv_post_id);
		if ($dprv_post === false || is_null($dprv_post))
		{
			echo "Verification failure, could not find post data for revision $dprv_post_id";
		}
		else
		{
			$dprv_post_content = $dprv_post["post_content"];
			$dprv_original_post_id = $dprv_post["post_parent"];
			//$log->lwrite("content=" . $dprv_post_content);
			$dprv_raw_content = trim($dprv_post_content);
			$dprv_raw_content = dprv_normaliseContent($dprv_raw_content);
			if (function_exists("hash"))					// Before 5.1.2, the hash() function did not exist, calling it gives a fatal error
			{
				$dprv_digital_fingerprint = strtoupper(hash("sha256", $dprv_raw_content));
				$dprv_raw_content = null;
			}
			$error_message = "";
			$certificate_id = "";
			$digiproved_content = null;
			$content_files = null;
			$user_agent = "Copyright Proof " . DPRV_VERSION;
			$credentials = array("user_id" => get_option('dprv_user_id'), "domain_name" => $dprv_blog_host, "api_key" => get_option('dprv_api_key'));
			$response = Digiprove::verify_fingerprint($error_message, $credentials, $certificate_id, $dprv_digital_fingerprint, $dprv_raw_content, $digiproved_content, $content_files, $user_agent);
			$result_code = intval($response["result_code"]);
			$message = "<table id='dprv_verify_panel'>";
			$message .= "<tr><td style='width:90px'><b>" . __("Searched on fingerprint", "dprv_cp") . "</b></td><td style='color:blue;font-family:monospace'>" . substr($dprv_digital_fingerprint, 0,32) . "<br/>" . substr($dprv_digital_fingerprint, 32) . "</td></tr>";
			if ($result_code > 199 && $result_code < 210)
			{
				$message .= "<tr><td colspan='2' style='padding-top:3px'><img src='" . plugins_url("DP_tick.png", __FILE__) . "'/>&nbsp;&nbsp;<span style='vertical-align:6px; font-weight:bold'>" . $response["result"] . ".</span><br/>" . __("Digiprove certifies that the content of this revision was verifiably Digiproved", 'dprv_cp');
				$instance_count = intval($response["instance_count"]);
				if ($instance_count > 1)
				{
					$message .= sprintf(__(" %s times.  Details of the earliest shown below", "dprv_cp"), $instance_count);
				}
				$message .= ".</td></tr>";
				
				$message .= "<tr><td><b>" . __("Certified&nbsp;at&nbsp;", 'dprv_cp') . "</b></td><td>" . $response["document_0"]["utc_date_and_time"] . "</td></tr>";
				$message .= "<tr><td><b>" . __("Certificate&nbsp;Id&nbsp;", 'dprv_cp') . "</b></td><td>" . $response["document_0"]["certificate_id"] . "</td></tr>";
				$message .= "<tr><td><b>" . __("Certificate&nbsp;URL&nbsp;", 'dprv_cp') . "</b></td><td style='word-wrap:break-word;'>" . $response["document_0"]["certificate_url"] . "</td></tr>";
				$message .= "</table>";
				$user_stuff = ($credentials["user_id"] . " (" . $response["user_full_name"] . ")");
				$user_stuff = str_replace(" ()", "", $user_stuff);

				$message .= "<span style='display:none' id='verifyMessage'>Digiprove certifies that Digiprove user " . $user_stuff . " was in possession of digital content with the digital fingerprint " . $dprv_digital_fingerprint . " on " . $response["document_0"]["utc_date_and_time"] . ".";
				if ($response["document_0"]["published_url"] && $response["document_0"]["published_url"] != "")
				{
					$message .= " This content was published at " . $response["document_0"]["published_url"] . ". ";
				}
				$message .= "Digiprove has issued the certificate id " . $response["document_0"]["certificate_id"] . ", which can be viewed and verified online at " . $response["document_0"]["certificate_url"] . ".</span>";
			}
			else
			{
				$message .= "<tr><td>" . __("Response from Digiprove: ", "dprv_cp") . "</td><td>";
				if ($result_code != 200)
				{
					$message .= $response["result_code"] . "/";
				}
				$message .= $response["result"];
				if ($error_message != "" && $error_message != $response["result"])
				{
					$message .= "/" . $error_message;
				}
				$message .= "</td></tr></table>";
			}
			echo $message;
		}
		die();
	}


?>