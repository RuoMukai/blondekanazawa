// FUNCTIONS TO SUPPORT COPYRIGHT PROOF SETTINGS PAGE

// Tab Display Functions
function dprv_DisplayAdvanced()
{
	if (dprv_enrolled == "No")
	{
		return;
	}
	document.getElementById("BasicTab").style.borderBottom="1px solid #666666";
	document.getElementById("AdvancedTab").style.borderBottom="0px";
	document.getElementById("LicenseTab").style.borderBottom="1px solid #666666";
	document.getElementById("ContentTab").style.borderBottom="1px solid #666666";
	document.getElementById("CopyProtectTab").style.borderBottom="1px solid #666666";
	document.getElementById("HistoryTab").style.borderBottom="1px solid #666666";
	document.getElementById("BasicPart1").style.display="none";
	document.getElementById("BasicPart2").style.display="none";
	if (document.getElementById("BasicPart3") != null)
	{
		document.getElementById("BasicPart3").style.display="none";
	}	
	document.getElementById("AdvancedPart1").style.display="";
	document.getElementById("AdvancedPart2").style.display="";
	document.getElementById("Content").style.display="none";
	document.getElementById("ContentPart2").style.display="none";
	document.getElementById("License").style.display="none";
	document.getElementById("CopyProtect").style.display="none";
	document.getElementById("History").style.display="none";
	document.getElementById('dprv_submit').style.display='';	
	dprv_HideHelpText();
}

function dprv_DisplayLicenseTab()
{
	if (dprv_enrolled == "No")
	{
		return;
	}
	document.getElementById("BasicTab").style.borderBottom="1px solid #666666";
	document.getElementById("AdvancedTab").style.borderBottom="1px solid #666666";
	document.getElementById("LicenseTab").style.borderBottom="0px";
	document.getElementById("ContentTab").style.borderBottom="1px solid #666666";
	document.getElementById("CopyProtectTab").style.borderBottom="1px solid #666666";
	document.getElementById("HistoryTab").style.borderBottom="1px solid #666666";
	document.getElementById("BasicPart1").style.display="none";
	document.getElementById("BasicPart2").style.display="none";
	if (document.getElementById("BasicPart3") != null)
	{
		document.getElementById("BasicPart3").style.display="none";
	}	
	document.getElementById("AdvancedPart1").style.display="none";
	document.getElementById("AdvancedPart2").style.display="none";
	document.getElementById("Content").style.display="none";
	document.getElementById("ContentPart2").style.display="none";
	document.getElementById("License").style.display="";
	document.getElementById("CopyProtect").style.display="none";
	document.getElementById("History").style.display="none";
	dprv_LicenseActionAbandon();
	dprv_HideHelpText();
}

function dprv_DisplayContentTab()
{
	if (dprv_enrolled == "No")
	{
		return;
	}
	document.getElementById("BasicTab").style.borderBottom="1px solid #666666";
	document.getElementById("AdvancedTab").style.borderBottom="1px solid #666666";
	document.getElementById("LicenseTab").style.borderBottom="1px solid #666666";
	document.getElementById("ContentTab").style.borderBottom="0px";
	document.getElementById("CopyProtectTab").style.borderBottom="1px solid #666666";
	document.getElementById("HistoryTab").style.borderBottom="1px solid #666666";
	document.getElementById("BasicPart1").style.display="none";
	document.getElementById("BasicPart2").style.display="none";
	if (document.getElementById("BasicPart3") != null)
	{
		document.getElementById("BasicPart3").style.display="none";
	}	
	document.getElementById("AdvancedPart1").style.display="none";
	document.getElementById("AdvancedPart2").style.display="none";
	document.getElementById("License").style.display="none";
	document.getElementById("Content").style.display="";
	// TODO: Restore instruction below when implementing data integrity checking
	// document.getElementById("ContentPart2").style.display="";
	document.getElementById("CopyProtect").style.display="none";
	document.getElementById("History").style.display="none";
	document.getElementById('dprv_submit').style.display='';	
	dprv_HideHelpText();
}

function dprv_DisplayCopyProtect()
{
	if (dprv_enrolled == "No")
	{
		return;
	}
	document.getElementById("BasicTab").style.borderBottom="1px solid #666666";
	document.getElementById("AdvancedTab").style.borderBottom="1px solid #666666";
	document.getElementById("LicenseTab").style.borderBottom="1px solid #666666";
	document.getElementById("ContentTab").style.borderBottom="1px solid #666666";
	document.getElementById("CopyProtectTab").style.borderBottom="0px";
	document.getElementById("HistoryTab").style.borderBottom="1px solid #666666";
	document.getElementById("BasicPart1").style.display="none";
	document.getElementById("BasicPart2").style.display="none";
	if (document.getElementById("BasicPart3") != null)
	{
		document.getElementById("BasicPart3").style.display="none";
	}	
	document.getElementById("AdvancedPart1").style.display="none";
	document.getElementById("AdvancedPart2").style.display="none";
	document.getElementById("Content").style.display="none";
	document.getElementById("ContentPart2").style.display="none";
	document.getElementById("License").style.display="none";
	document.getElementById("CopyProtect").style.display="";
	document.getElementById("History").style.display="none";
	document.getElementById('dprv_submit').style.display='';	
	dprv_HideHelpText();
}
function dprv_DisplayHistory()
{
	if (dprv_enrolled == "No")
	{
		return;
	}
	document.getElementById("BasicTab").style.borderBottom="1px solid #666666";
	document.getElementById("AdvancedTab").style.borderBottom="1px solid #666666";
	document.getElementById("LicenseTab").style.borderBottom="1px solid #666666";
	document.getElementById("ContentTab").style.borderBottom="1px solid #666666";
	document.getElementById("CopyProtectTab").style.borderBottom="1px solid #666666";
	document.getElementById("HistoryTab").style.borderBottom="0px";
	document.getElementById("BasicPart1").style.display="none";
	document.getElementById("BasicPart2").style.display="none";
	if (document.getElementById("BasicPart3") != null)
	{
		document.getElementById("BasicPart3").style.display="none";
	}	
	document.getElementById("AdvancedPart1").style.display="none";
	document.getElementById("AdvancedPart2").style.display="none";
	document.getElementById("Content").style.display="none";
	document.getElementById("ContentPart2").style.display="none";
	document.getElementById("License").style.display="none";
	document.getElementById("CopyProtect").style.display="none";
	document.getElementById("History").style.display="";
	document.getElementById('dprv_submit').style.display='';	
	dprv_HideHelpText();
}


// FUNCTIONS FOR BASIC TAB:

function dprv_DisplayBasic()
{
	document.getElementById("BasicTab").style.borderBottom="0px";
	document.getElementById("AdvancedTab").style.borderBottom="1px solid #666666";
	document.getElementById("LicenseTab").style.borderBottom="1px solid #666666";
	document.getElementById("ContentTab").style.borderBottom="1px solid #666666";
	document.getElementById("CopyProtectTab").style.borderBottom="1px solid #666666";
	document.getElementById("HistoryTab").style.borderBottom="1px solid #666666";
	document.getElementById("BasicPart1").style.display="";
	document.getElementById("BasicPart2").style.display="";
	if (document.getElementById("BasicPart3") != null)
	{
		document.getElementById("BasicPart3").style.display="";
	}
	document.getElementById("AdvancedPart1").style.display="none";
	document.getElementById("AdvancedPart2").style.display="none";
	document.getElementById("Content").style.display="none";
	document.getElementById("ContentPart2").style.display="none";
	document.getElementById("License").style.display="none";
	document.getElementById("CopyProtect").style.display="none";
	document.getElementById("History").style.display="none";
	document.getElementById('dprv_submit').style.display='';	
	dprv_HideHelpText();
}

// Stuff required to deal with annoying FF3.5 bug
function dprv_SavePassword()
{
	dprv_SavedPassword = document.getElementById("dprv_password").value;
}

function dprv_ScheduleRestorePassword()
{
	setTimeout("dprv_RestorePassword()",100);
}

function dprv_RestorePassword()
{
	if (navigator.userAgent.indexOf("Firefox/3.5") > -1)
	{
		document.getElementById("dprv_password").value = dprv_SavedPassword;
	}
}
// End of Stuff


function dprv_DisplayNameChanged(element)
{
	if (element.value == "No" && document.getElementById("dprv_c_notice").value == "DisplayAll")
	{
		document.getElementById("dprv_c_notice").value = "Display";
		dprv_Preview();
	}
}

function dprv_ResendEmail()
{
	document.getElementById("dprv_action").value = "ResendEmail";
	document.getElementById("dprv_cp").submit();
}

function dprv_SyncUser()
{
	document.getElementById("dprv_action").value = "SyncUser";
	document.getElementById("dprv_cp").submit();
}


function dprv_toggleCredentials()
{
	if (document.getElementById("dprv_enrolled").value == "Yes")
	{
		document.getElementById("dprv_resend_activation").style.display="";
		document.getElementById("dprv_register_row0").style.display="none";
		document.getElementById("dprv_register_row1").style.display="none";
		document.getElementById("dprv_user_id_row1").style.display="";
		document.getElementById("dprv_user_id_row2").style.display="";
		document.getElementById("dprv_user_id_labelA").style.display="";
		document.getElementById("dprv_user_id_labelB").style.display="none";
		document.getElementById("dprv_api_key_row_0").style.display="";
		document.getElementById("dprv_api_key_row_1").style.display="";
		document.getElementById("dprv_api_key_row_2").style.display="";
		document.getElementById("dprv_password_row1").style.display="none";
		document.getElementById("dprv_password_row2").style.display="none";
		document.getElementById("dprv_password_row3").style.display="none";
		document.getElementById("dprv_contact_row0").style.display="none";
		document.getElementById("dprv_contact_row1").style.display="none";
		document.getElementById("dprv_submit").value = dprv_literals["Update_settings"];
		dprv_renewApiKey();
	}
	else
	{
		document.getElementById("dprv_resend_activation").style.display="none";
		document.getElementById("dprv_register_row0").style.display="";
		document.getElementById("dprv_register_row1").style.display="";
		document.getElementById("dprv_api_key_row_0").style.display="none";
		document.getElementById("dprv_api_key_row_1").style.display="none";
		document.getElementById("dprv_api_key_row_2").style.display="none";
		document.getElementById("dprv_user_id_labelA").style.display="none";
		document.getElementById("dprv_user_id_labelB").style.display="";
		if (document.getElementById("dprv_sub_row0"))
		{
			document.getElementById("dprv_sub_row0").style.display="none";
			document.getElementById("dprv_sub_row1").style.display="none";
		}

		if (document.getElementById("dprv_register_yes").checked == true)
		{
			//document.getElementById("dprv_user_id").disabled=false;
			document.getElementById("dprv_user_id_row1").style.display="";
			document.getElementById("dprv_user_id_row2").style.display="";
			document.getElementById("dprv_password_row1").style.display="";
			document.getElementById("dprv_password_row2").style.display="";
			document.getElementById("dprv_password_row3").style.display="";
			document.getElementById("dprv_contact_row0").style.display="";
			document.getElementById("dprv_contact_row1").style.display="";
			document.getElementById("dprv_submit").value = dprv_literals["Update_&_register"];
		}
		else
		{
			//document.getElementById("dprv_user_id").disabled=true;
			document.getElementById("dprv_user_id_row1").style.display="none";
			document.getElementById("dprv_user_id_row2").style.display="none";
			document.getElementById("dprv_password_row1").style.display="none";
			document.getElementById("dprv_password_row2").style.display="none";
			document.getElementById("dprv_password_row3").style.display="none";
			document.getElementById("dprv_contact_row0").style.display="none";
			document.getElementById("dprv_contact_row1").style.display="none";
			document.getElementById("dprv_submit").value = dprv_literals["Update_settings"];
		}
	}
	dprv_HideHelpText();
}

function dprv_EnableRegistrationInputs()
{
	if (confirm(dprv_literals["No_touch_warning0"]))				// Once you have registered successfully and the plugin is working it should not be necessary to modify any of these values, and entering an incorrect value may cause the plugin to stop working.  If you are sure you wish to proceed, press OK."
	{
		document.getElementById("dprv_enrolled").disabled=false;
		document.getElementById("dprv_register_yes").disabled=false;
		document.getElementById("dprv_register_no").disabled=false;
		document.getElementById("dprv_user_id").disabled=false;
		document.getElementById("dprv_renew_api_key").disabled=false;
		document.getElementById("dprv_input_api_key").disabled=false;
		document.getElementById("dprv_password").disabled=false;
		document.getElementById("dprv_pw_confirm").disabled=false;
		document.getElementById("dprv_change_reg").style.display = "none";
		return true;
	}
	return false;
}


function dprv_UserIdChanged()
{
	if (dprv_lastUserId == document.getElementById('dprv_user_id').value)
	{
		return true;
	}
	document.getElementById('dprv_user_id').value = dprv_trim(document.getElementById('dprv_user_id').value);
	if (dprv_indexOfAny(document.getElementById('dprv_user_id').value, "/?\\:*<>|\"") != -1)
	{
		alert(dprv_literals["No_special_chars"]);
		document.getElementById('dprv_user_id').value = dprv_lastUserId;
		return false;
	}
	if (document.getElementById('dprv_enrolled').value == "No")
	{
		return true;
	}
	if ((dprv_last_result.indexOf(dprv_literals["Digiprove certificate id"]) !== -1 || dprv_last_result.indexOf("User already activated") !== -1) && dprv_lastUserId != "")
	{
		if (!confirm(dprv_literals["No_touch_warning1"]))			// "You are changing your Digiprove User Id. This may cause the plugin to stop working.  Press OK if you are sure, or Cancel to restore the previous value."
		{
			document.getElementById('dprv_user_id').value = dprv_lastUserId;
			return false;
		}
	}
	dprv_lastUserId = document.getElementById('dprv_user_id').value;
	if (document.getElementById("dprv_sub_row0"))
	{
		document.getElementById("dprv_sub_row0").style.display="none";
		document.getElementById("dprv_sub_row1").style.display="none";
	}
	document.getElementById('dprv_renew_api_key').checked=true;
	dprv_renewApiKey();
	return true;
}

function dprv_ApiKeyChange()
{
	document.getElementById('dprv_api_key').value = document.getElementById('dprv_api_key').value.replace(/^\s\s*/, '').replace(/\s\s*$/, '');		// trim because precision is important here
	if (dprv_lastApiKey == document.getElementById('dprv_api_key').value)
	{
		return true;
	}
	if (dprv_lastApiKey == "")
	{
		// You have entered a value for API Key - do this only if the new API key was obtained from Digiprove
		if (!confirm(dprv_literals["No_touch_warning2"]))			// "You have entered a value for API Key - do this only if the new API key was obtained from Digiprove
		{
			document.getElementById('dprv_api_key').value = dprv_lastApiKey;
			return false;
		}
	}
	else
	{
		if (document.getElementById('dprv_api_key').value == "")
		{
			alert(dprv_literals["API_key_empty"]);					// "An empty api key is invalid."
			document.getElementById('dprv_api_key').value = dprv_lastApiKey;
			return false;
		}
		var length_message = "";
		if (document.getElementById('dprv_api_key').value.length != 22)
		{
			
			length_message = " " + dprv_literals["API_key_warning1"];		// "That does not look like a valid API key which is normally 22 characters."
		}
		if (!confirm(dprv_literals["API_key_warning0"] + length_message + dprv_literals["API_key_warning2"]))
		{
			document.getElementById('dprv_api_key').value = dprv_lastApiKey;
			return false;
		}
	}
	dprv_lastApiKey = document.getElementById('dprv_api_key').value;
	return true;
}

function dprv_renewApiKey()	// Toggle display settings dependent on renew api key checkbox
{
	if (document.getElementById("dprv_renew_api_key").checked == true)
	{
		document.getElementById("dprv_input_api_key").checked = false;
		document.getElementById("dprv_api_key").disabled = true;
	}
	else
	{
		if (document.getElementById("dprv_api_key").value == "")
		{
			if (dprv_lastApiKey == "")
			{
				document.getElementById("dprv_input_api_key").checked = true;
				document.getElementById("dprv_api_key").disabled = false;
			}
			else
			{
				document.getElementById("dprv_input_api_key").checked = false;
				document.getElementById("dprv_api_key").value = dprv_lastApiKey;
				document.getElementById("dprv_api_key").disabled = true;
			}
		}
	}
	dprv_toggleApiKey();
}

function dprv_inputApiKey()	// Toggle display settings dependent on renew api key checkbox
{
	if (document.getElementById("dprv_input_api_key").checked == true)
	{
		document.getElementById("dprv_renew_api_key").checked = false;
	}
	else
	{
		if (document.getElementById("dprv_api_key").value == "")
		{
			if (dprv_lastApiKey == "")
			{
				document.getElementById("dprv_renew_api_key").checked = true;
			}
			else
			{
				document.getElementById("dprv_renew_api_key").checked = false;
				document.getElementById("dprv_api_key").value = dprv_lastApiKey;
			}
		}
	}
	dprv_toggleApiKey();
}

function dprv_toggleApiKey()
{
	if (document.getElementById("dprv_renew_api_key").disabled == false)
	{
		if (document.getElementById("dprv_input_api_key").checked == true)
		{
			if (dprv_lastApiKey == "")
			{
				
				if (!confirm(dprv_literals["API_key_warning3"]))		// "You should only input a value for API Key if you have already obtained this API key from Digiprove."
				{
					document.getElementById("dprv_input_api_key").checked = false;
					return false;
				}
			}
			else
			{
				
				if (!confirm(dprv_literals["API_key_warning4"]))				// "Do this only if the new API key was obtained from Digiprove, otherwise the plugin will stop working."
				{
					document.getElementById("dprv_input_api_key").checked = false;
					dprv_restoreApiKey();
					return false;
				}
			}
		}
		if (document.getElementById("dprv_renew_api_key").checked == true)
		{
			if (document.getElementById("dprv_enrolled").value == "Yes")
			{
				//document.getElementById("dprv_api_key_caption").innerHTML = "Current API Key:"
				document.getElementById("dprv_api_key_caption").innerHTML = dprv_literals["Current_API_key"];
				document.getElementById("dprv_api_key").disabled = true;
				document.getElementById("dprv_api_key").style.backgroundColor = "#EEEEEE";
				document.getElementById("dprv_api_key_row_2").style.display="";
				if (document.getElementById("dprv_api_key").value != "" && dprv_lastApiKey != "")
				{
					document.getElementById("dprv_api_key").value = dprv_lastApiKey;
				}
			}
			else
			{
				document.getElementById("dprv_api_key_row_2").style.display="none";
			}
			//document.getElementById("dprv_password_label").innerHTML = "Enter Password:";
			document.getElementById("dprv_password_label").innerHTML = dprv_literals["Enter_password"];			// "Enter Password:"
			document.getElementById("dprv_password_row1").style.display="";
			document.getElementById("dprv_password_row2").style.display="";
			document.getElementById("dprv_password").focus();
		}
		else
		{
			//document.getElementById("dprv_password_label").innerHTML = "Select a Password:";
			document.getElementById("dprv_password_label").innerHTML = dprv_literals["Select_password"];		//"Select a Password:"
			document.getElementById("dprv_password_row1").style.display="none";
			document.getElementById("dprv_password_row2").style.display="none";
			if (document.getElementById("dprv_input_api_key").checked == true)
			{
				document.getElementById("dprv_api_key_row_2").style.display="";
				//document.getElementById("dprv_api_key_caption").innerHTML = "Enter API Key:"
				document.getElementById("dprv_api_key_caption").innerHTML = dprv_literals["Enter_API_key"];
				document.getElementById("dprv_api_key").disabled = false;
				document.getElementById("dprv_api_key").style.backgroundColor = "";
				document.getElementById("dprv_api_key").focus();
			}
			else
			{
				document.getElementById("dprv_api_key").disabled = true;
				document.getElementById("dprv_api_key").style.backgroundColor = "#EEEEEE";
				//document.getElementById("dprv_api_key_caption").innerHTML = "Current API Key:";
				document.getElementById("dprv_api_key_caption").innerHTML = dprv_literals["Current_API_key"];			}
		}
	}
	return true;
}

function dprv_restoreApiKey()
{
	if (dprv_savedApiKey != "")
	{
		document.getElementById("dprv_api_key").value = dprv_savedApiKey;
		//document.getElementById("dprv_api_key_caption").innerHTML = "Current API Key:"
		document.getElementById("dprv_api_key_caption").innerHTML = dprv_literals["Current_API_key"];
		document.getElementById("dprv_api_key").disabled = true;
		document.getElementById("dprv_api_key").style.backgroundColor = "#EEEEEE";
		document.getElementById("dprv_api_key_row_2").style.display="";
	}
}

function dprv_ShowPersonalDetailsText()
{
	
	// 'Copyright Proof uses the Digiprove service (<a href="http://www.digiprove.com/creative-and-copyright.aspx" target="_blank">www.digiprove.com</a>) to certify the content and timestamp of your Wordpress posts. Digiprove needs the name of the person claiming copyright and a valid email address to which the digitally-signed content certificates will be sent. The personal details on this panel will be used only in automatically creating your account at Digiprove.  The server records of this information are accessible at <a href="https://www.digiprove.com/members/preferences.aspx" target="_blank">https://www.digiprove.com/members/preferences.aspx</a>.'
	dprv_DisplayHelpText(dprv_literals["Personal_help"]);
}

function dprv_ShowPrivacyText()
{
	// 'The Copyright Proof notice appearing at the foot of your blog posts contains a link to a web-page showing details of the Digiprove certificate of your content. If you do not want your name to appear on this page select the &#39;Keep private&#39; option. Your email address and Digiprove User id are never revealed. Click <a href="http://www.digiprove.com/privacypolicy.aspx" target="_blank">here</a> to read the full privacy statement.'
	dprv_DisplayHelpText(dprv_literals["Privacy_help"]);
}

function dprv_ShowEmailCertText()
{
	// 'Every post you publish or update will cause a cryptographically encoded certificate to be created which is your proof that your content was published by you at that exact time and date. This is retained for you in case it is required in future. Each certificate may be downloaded at the Digiprove website if required - you will need a current subscription to do this. Digiprove subscribers have the option to receive their certificates automatically by email. To set this option, select &#39;Yes&#39; here.  You will still be able to download these certificates from the Digiprove site if required.'
	dprv_DisplayHelpText(dprv_literals["Email_cert_help"]);
}

function dprv_ShowAPIText(domain_name, password_on_record)
{
	var dprv_api_text = dprv_literals["API_key_required0"];		// 'A Digiprove API key for ' + dprv_blog_host + ' is required for this domain to use the Digiprove service.'
	
	if (document.getElementById('dprv_api_key').value == "")
	{
		if (password_on_record == 'Yes')
		{
			// 'If you have already registered, you do not need to do anything, it will be filled in automatically when required. You can also obtain an API key <a href="https://www.digiprove.com/members/api_keys.aspx" target="_blank">from the Digiprove members&#39; website</a> (you will be asked to log in)'
			dprv_api_text += dprv_literals["API_key_required1"];
		}
		else
		{
			// 'If you are already registered with Digiprove, you can obtain your API key for this domain by ticking the &quot;Obtain New API Key&quot; box above (you will be asked for your password).  API keys can also be obtained at the <a href="https://www.digiprove.com/members/api_keys.aspx" target="_blank">Digiprove members&#39; website</a> (you will be asked to log in)'
			dprv_api_text += dprv_literals["API_key_required2"];
		}
	}
	else
	{
		// 'If you registered from this page this field will have been filled in automatically - there is no need to change it. If you are receiving error messages regarding your api key you can obtain a new one by ticking &quot;Obtain New Api Key&quot; box (you will need to input your password) or it can also be done from the <a href="https://www.digiprove.com/members/api_keys.aspx" target="_blank">Digiprove  members&#39; website</a> (you will be asked to log in)'
		dprv_api_text += dprv_literals["API_key_required3"];
	}
	dprv_DisplayHelpText(dprv_api_text);
}
function dprv_ShowAPIFaqText(domain_name, password_on_record)
{
	dprv_DisplayHelpText(dprv_literals["API_key_FAQ"]);
}

function dprv_ShowPasswordText()
{
	// 'Your password to give you access to the Digiprove website members&#39; area. An encrypted version of the password is stored on the Digiprove server but <em>not here on your Wordpress server</em>.'
	dprv_DisplayHelpText(dprv_literals["Password_help"]);
}

function dprv_ShowRegistrationText()
{
	// 'Copyright Proof uses the Digiprove service (<a href="http://www.digiprove.com/creative-and-copyright.aspx" target="_blank">www.digiprove.com</a>) to certify the content and timestamp of your Wordpress posts. You need to register with Digiprove before Copyright Proof will start working for you; by selecting &quot;Yes, register me now&quot; this registration process will take place; you will then receive an email with an activation link.'
	dprv_DisplayHelpText(dprv_literals["Register_help"]);
}

function dprv_ShowTermsOfUseText()
{
	// 'Using this plugin, the core Digiprove services are provided free-of-charge.  There are <a href="http://www.digiprove.com/termsofuse_page.aspx" target="_blank">terms of use</a> governing things like privacy and abuse. There are some premium services that are available only to Digiprove subscribers'
	dprv_DisplayHelpText(dprv_literals["Commercial_help"]);
}

// END OF FUNCTIONS FOR BASIC TAB


// FUNCTIONS FOR ADVANCED TAB:
function dprv_createOwnText(element)
{
	if (dprv_subscription_type == "Basic" || dprv_subscription_type == "" || dprv_subscription_expired == "Yes" )
	{
		dprv_SubscribersOnly("Custom License");
		element.value="";
	}
	dprv_Preview();
}

function dprv_noBackgroundChanged(element)
{
	if(element.checked==true)
	{
		lastBackgroundColor=document.getElementById('dprv_notice_background').value;
		lastBackgroundTextColor=document.getElementById('dprv_notice_background').style.color;
		document.getElementById('dprv_notice_background').value='None';
		document.getElementById('dprv_notice_background').style.backgroundColor='';
		document.getElementById('dprv_notice_background').style.color='';
	}
	else
	{
		if (lastBackgroundColor == "")
		{
			lastBackgroundColor = "#FFFFFF";
			lastBackgroundTextColor = "#000000";
		}
		document.getElementById('dprv_notice_background').value=lastBackgroundColor;
		document.getElementById('dprv_notice_background').style.backgroundColor=lastBackgroundColor;
		document.getElementById('dprv_notice_background').style.color=lastBackgroundTextColor;
	}
	dprv_Preview();
}

function dprv_noBorderChanged(element)
{
	if(element.checked==true)
	{
		lastBorderColor=document.getElementById('dprv_notice_border').value;
		lastBorderTextColor=document.getElementById('dprv_notice_border').style.color;
		document.getElementById('dprv_notice_border').value='None';
		document.getElementById('dprv_notice_border').style.backgroundColor='';
		document.getElementById('dprv_notice_border').style.color='';
	}
	else
	{
		if (lastBorderColor == "")
		{
			lastBorderColor = "#BBBBBB";
			lastBorderTextColor = "#000000";
		}
		document.getElementById('dprv_notice_border').value=lastBorderColor;
		document.getElementById('dprv_notice_border').style.backgroundColor=lastBorderColor;
		document.getElementById('dprv_notice_border').style.color=lastBorderTextColor;
	}
	dprv_Preview();
}


function dprv_setCheckboxes()
{
	if (document.getElementById("dprv_notice_background").value != "None")
	{
		document.getElementById('dprv_no_background').checked = false;
	}
	if (document.getElementById("dprv_notice_border").value != "None")
	{
		document.getElementById('dprv_no_border').checked = false;
	}
}

function dprv_Preview()
{
	var notice_text = document.getElementById("dprv_notice").value;
	if (document.getElementById("dprv_custom_notice").value != "")
	{
		notice_text = document.getElementById("dprv_custom_notice").value;
	}
	var c_notice = document.getElementById("dprv_c_notice").value;
	var notice_font_size = "11px";
	var image_scale = "";

	var a_height = "16px";
	var txt_valign = "0px";
	var img_valign = "-3px";
	var outside_font_size = "13px";
	if (document.getElementById("dprv_notice_small").checked == true)
	{
		notice_font_size="10px";
		txt_valign = "1px";;
	}
	if (document.getElementById("dprv_notice_smaller").checked == true)
	{
		notice_font_size="9px";
		image_scale=" width='12px' height='12px'";
		a_height = "12px";
		outside_font_size = "10px";
		img_valign = "-2px";
	}

	var notice_color = document.getElementById("dprv_notice_color").value;
	var hover_color = document.getElementById("dprv_hover_color").value;
	var background_color = document.getElementById("dprv_notice_background").value;
	var notice_background_css = "";
	if (background_color != "None")
	{
		notice_background_css = "background-color:" + background_color + ";";
	}
	var border_color = document.getElementById("dprv_notice_border").value;
	var border_css = "border:0px;"
	if (border_color != "None")
	{
		border_css = "border:1px solid " + border_color + ";";
	}
	var now = new Date();
	
	var DigiproveNotice = "<span lang='en' xml:lang='en' style='vertical-align:middle; display:inline; padding:2px; margin-top:2px; margin-bottom:2px; height:auto; float:none; line-height:normal; font-family: Tahoma, MS Sans Serif; font-size:" + outside_font_size + ";" + border_css + notice_background_css + "' >";

	DigiproveNotice += "<span style='height:" + a_height + "; border:0px; padding:0px; margin:0px; float:none; display:inline; text-decoration: none; background-color:transparent; line-height:normal; font-family: Tahoma, MS Sans Serif; font-style:normal; font-weight:normal; font-size:" + notice_font_size + ";'>";

	DigiproveNotice += "<img src='" + dprv_plugin_url + "/dp_seal_trans_16x16.png' style='vertical-align:" + img_valign + "; display:inline; border:0px; margin:0px; float:none; background-color:transparent' border='0'" + image_scale + "/>";

	DigiproveNotice += "<span  style='font-family: Tahoma, MS Sans Serif; font-style:normal; font-weight:normal; font-size:" + notice_font_size + ";color:" + notice_color + "; border:0px; float:none; text-decoration: none; letter-spacing:normal; vertical-align:" + txt_valign + ";' onmouseover=\"this.style.color='" + hover_color + "'\" onmouseout=\"this.style.color='" + notice_color + "'\" >";
	
	DigiproveNotice += "&nbsp;&nbsp;" + notice_text;
	if (c_notice != "NoDisplay")
	{
		var year = now.getFullYear();
		var cName = document.getElementById("dprv_first_name").value + " " + document.getElementById("dprv_last_name").value;
		DigiproveNotice += "&nbsp;&copy; " + year;
		if (c_notice == "DisplayAll")
		{
			DigiproveNotice += " " + cName.replace(/^\s\s*/, "").replace(/\s\s*$/, "");
		}
	}
	DigiproveNotice += "</span></span></span>";
	document.getElementById("dprv_notice_preview").innerHTML = DigiproveNotice;
}

function dprv_ToggleFooterWarning()
{
	if (document.getElementById("dprv_footer").checked == true)
	{
		document.getElementById("footer_warning_link").style.display = "";
	}
	else
	{
		document.getElementById("footer_warning_link").style.display = "none";
	}
}
function dprv_ShowMultiPostText()
{
	// 'Tick this to allow the Digiprove notice to be included in post excerpts that appear on multi-post pages such as search results, archive pages etc.'
	dprv_DisplayHelpText(dprv_literals["Multipost_help"]);
}

function dprv_ShowFooterText()
{
	// 'Please check how this looks on your site.  Whether and where the notice appears is determined by your theme.  To change this, look for wp_footer() in the footer.php file of your theme.'
	dprv_DisplayHelpText(dprv_literals["Footer_text_help"]);
}

// END OF FUNCTIONS FOR ADVANCED TAB


// FUNCTIONS FOR DATA INTEGRITY TAB
function dprv_ShowDataIntegrityText()
{
	dprv_DisplayHelpText(dprv_literals["DataIntegrity_help"]);
}
function dprv_FileIntegrityNote()
{
	dprv_DisplayHelpText(dprv_literals["FileIntegrityNote"]);
}
// END OF FUNCTIONS FOR DATA INTEGRITY TAB


// FUNCTIONS FOR CONTENT TAB:
function dprv_ShowAutoText()
{
	// For posts and pages entered via Wordpress Edit Post/Page functions, you will be able to choose whether to Digiprove each time you save.  Here you specify the default action 
	// which will apply for posts submitted in other ways e.g. via xmlrpc, QuickPress or generated (perhaps by plugins)
	dprv_DisplayHelpText(dprv_literals["AutoText"]);
}
function dprv_ShowFingerprintText(hash_supported)
{
	var caution = ''
	if (hash_supported=='No')
	{
		// 'Your version of PHP does not support this function. Ask your provider to upgrade you to PHP 5.1.2 or later.'
		caution = ' <b><font color="red">' + dprv_literals["PHP_warning0"] + "</b</font>" + dprv_literals["PHP_warning1"];
	}
	dprv_DisplayHelpText(dprv_literals["Fingerprint_media_help"] + caution);
}

function dprv_ShowBetaText()
{
	// 'Note the media file Digiproving functions are in Beta form. We have tested them in a number of environments, but we are anxious for your feedback.  If you experience problems, firstly please advise us at support@digiprove.com, so we can fix the underlying problem. To get rid of problems, simply untick all of these boxes, or press &quot;Clear all&quot;.'
	dprv_DisplayHelpText(dprv_literals["Beta_warning"]);
}

function dprv_toggleMedia(element)
{
	var targetId = element.id + '_ie_col';
	if (element.checked == true)
	{
		if (dprv_subscription_type == "Basic" || dprv_subscription_type == "" || dprv_subscription_expired == "Yes" )
		{
			dprv_SubscribersOnly("Content File Fingerprinting");
			return false;
		}
		document.getElementById(targetId).style.visibility = 'visible';
	}
	else
	{
		document.getElementById(targetId).style.visibility = 'hidden';
	}
	return true;
}
function dprv_toggleInclExcl(element)
{
	var pos = element.name.lastIndexOf("_ie");
	var id_root = element.name.substr(0,pos);
	var checkbox_0 = document.getElementById(id_root + "_types_0");
	pos = checkbox_0.name.lastIndexOf("_");
	if (checkbox_0.name.substr(pos+1) == "All")
	{
		if (element.value == "Include")
		{
			document.getElementById(id_root + "_labels_0").style.visibility="visible";
			document.getElementById(id_root + "_types_0").style.visibility="visible";
		}
		else
		{
			document.getElementById(id_root + "_labels_0").style.visibility="hidden";
			document.getElementById(id_root + "_types_0").style.visibility="hidden";
		}
		dprv_toggleMimeTypes(checkbox_0);
	}
}

function dprv_toggleMimeTypes(element)
{
	var pos = element.id.lastIndexOf("_0");
	var id_root = element.id.substr(0,pos);
	var option_counter = 1;
	var disabled = false;
	if (element.checked == true && element.style.visibility != "hidden")
	{
		disabled = true;
	}
	while (option_counter != -1)
	{
		if (document.getElementById(id_root + '_' + option_counter))
		{
			document.getElementById(id_root + '_' + option_counter).disabled = disabled;
			option_counter++;
		}
		else
		{
			option_counter = -1;
			break;
		}
	}
}
function dprv_default_html_tags()
{
	if (dprv_subscription_type == "Basic" || dprv_subscription_type == "" || dprv_subscription_expired == "Yes" )
	{
		dprv_SubscribersOnly("Digiprove Files");
		return;
	}
	document.getElementById("dprv_action").value = "DefaultHTMLTags";
	document.getElementById("dprv_cp").submit();
}
function dprv_clear_html_tags()
{
	if (dprv_subscription_type == "Basic" || dprv_subscription_type == "" || dprv_subscription_expired == "Yes" )
	{
		dprv_SubscribersOnly("Digiprove Files");
		return;
	}
	document.getElementById("dprv_action").value = "ClearHTMLTags";
	document.getElementById("dprv_cp").submit();
}
function dprv_toggleIntegrity()			// Is this function used any more?
{
	if (document.getElementById("dprv_html_integrity_yes").checked == true)
	{
		document.getElementById("integrity_row_0").style.display = "";
		document.getElementById("integrity_row_1").style.display = "";
	}
	else
	{
		document.getElementById("integrity_row_0").style.display = "none";
		document.getElementById("integrity_row_1").style.display = "none";
	}
}

// END OF FUNCTIONS FOR CONTENT TAB


// FUNCTIONS FOR LICENSE TAB:

function dprv_PreviewLicense()
{
	if (document.getElementById("dprv_license").value == "0")
	{
		document.getElementById('dprv_amend_license_button').style.display='none';
		document.getElementById('dprv_remove_license_button').style.display='none';
		document.getElementById("dprv_license_caption").innerHTML = "";
		document.getElementById("dprv_license_abstract").innerHTML = "";
		document.getElementById("dprv_license_url").innerHTML = "";
		document.getElementById("dprv_license_url").href = "";
	}
	else
	{
		document.getElementById('dprv_amend_license_button').style.display='';
		document.getElementById('dprv_remove_license_button').style.display='';
		for (var i=0; i<dprv_licenseIds.length; i++)
		{
			if (dprv_licenseIds[i] == document.getElementById("dprv_license").value)
			{
				document.getElementById("dprv_license_caption").innerHTML = dprv_licenseCaptions[i];
				document.getElementById("dprv_license_abstract").innerHTML = dprv_licenseAbstracts[i];
				document.getElementById("dprv_license_url").innerHTML = dprv_licenseURLs[i];
				document.getElementById("dprv_license_url").href = dprv_licenseURLs[i];
				break;
			}
		}
	}
	document.getElementById("dprv_license_caption").style.display = "";
	document.getElementById("dprv_license_abstract").style.display = "";
	document.getElementById("dprv_license_url").style.display = "";
	document.getElementById("dprv_custom_license_caption").style.display = "none";
	document.getElementById("dprv_custom_license_abstract").style.display = "none";
	document.getElementById("dprv_custom_license_url").style.display = "none";
}


function dprv_AddLicense()
{
	if (dprv_subscription_type == "Basic" || dprv_subscription_type == "" || dprv_subscription_expired == "Yes" )
	{
		dprv_SubscribersOnly("Add license");
		return false;
	}
	document.getElementById('dprv_license_heading').innerHTML = dprv_literals["New_license_type"];			// 'New License Type';
	document.getElementById('dprv_license_type_caption').innerHTML = dprv_literals["License_type_name"];	// 'License Type Name';
	document.getElementById('dprv_license').style.display='none';
	document.getElementById('dprv_license_caption').style.display='none';
	document.getElementById('dprv_license_abstract').style.display='none';
	document.getElementById('dprv_license_url').style.display='none';
	document.getElementById('License_customization').style.display='none';
	document.getElementById('dprv_custom_license').style.display='';
	document.getElementById('dprv_custom_license_caption').style.display='';
	document.getElementById('dprv_custom_license_abstract').style.display='';
	document.getElementById('dprv_custom_license_url').style.display='';
	document.getElementById('dprv_custom_license').value ='';
	document.getElementById('dprv_custom_license_abstract').value='';
	document.getElementById('dprv_custom_license_url').value='';
	document.getElementById('dprv_license_commit_0').style.display='';
	document.getElementById('dprv_license_commit_1').style.display='';
	document.getElementById('dprv_license_commit').value= dprv_literals["Add_this_license"];				// 'Add this license';
	document.getElementById('dprv_submit').style.display='none';	
	document.getElementById('dprv_custom_license').focus();
	return true;
}
function dprv_AmendLicense()
{
	if (dprv_subscription_type == "Basic" || dprv_subscription_type == "" || dprv_subscription_expired == "Yes" )
	{
		dprv_SubscribersOnly("Amend license");
		return false;
	}
	document.getElementById('dprv_license_heading').innerHTML = dprv_literals["Amend_license_type"];		// 'Amend License Type';
	document.getElementById('dprv_license').style.display='none';
	document.getElementById('dprv_license_caption').style.display='none';
	document.getElementById('dprv_license_abstract').style.display='none';
	document.getElementById('dprv_license_url').style.display='none';
	document.getElementById('License_customization').style.display='none';
	document.getElementById('dprv_custom_license').style.display='';
	document.getElementById('dprv_custom_license_caption').style.display='';
	document.getElementById('dprv_custom_license_abstract').style.display='';
	document.getElementById('dprv_custom_license_url').style.display='';
	for (var i=0; i<dprv_licenseIds.length; i++)
	{
		if (document.getElementById('dprv_license').value == dprv_licenseIds[i])
		{
			document.getElementById('dprv_custom_license').value = dprv_licenseTypes[i];
			break;
		}
	}
	document.getElementById('dprv_custom_license_caption').value = document.getElementById('dprv_license_caption').innerHTML;
	document.getElementById('dprv_custom_license_abstract').value = document.getElementById('dprv_license_abstract').innerHTML;
	document.getElementById('dprv_custom_license_url').value = document.getElementById('dprv_license_url').innerHTML;
	document.getElementById('dprv_license_commit_0').style.display='';
	document.getElementById('dprv_license_commit_1').style.display='';
	document.getElementById('dprv_license_commit').value= dprv_literals["Update_this_license"];				// 'Update this license';
	document.getElementById('dprv_submit').style.display='none';	
	document.getElementById('dprv_custom_license').focus();
	return true;
}
function dprv_RemoveLicense()
{
	if (dprv_subscription_type == "Basic" || dprv_subscription_type == "" || dprv_subscription_expired == "Yes" )
	{
		dprv_SubscribersOnly("Remove license");
		return false;
	}
	for (var i=0; i<dprv_licenseIds.length; i++)
	{
		if (document.getElementById('dprv_license').value == dprv_licenseIds[i])
		{
			//if (confirm("Remove License Type " + dprv_licenseTypes[i] + "?  (This operation cannot be undone)"))
			if (confirm(dprv_literals["Remove_this_license"].replace("%s", dprv_licenseTypes[i])))
			{
					document.getElementById("dprv_action").value = "RemoveLicense";
					document.getElementById("dprv_cp").submit();
			}
			else
			{
				return false;
			}
			break;
		}
	}
	return true;
}

function dprv_LicenseActionCommit()
{
	// Do a bit of validation
	{
			if (document.getElementById('dprv_custom_license').value == "")
			{
				alert(dprv_literals["License_Type_Missing"]);
				document.getElementById('dprv_custom_license').focus();
				return false;
			}
			
			//if (document.getElementById('dprv_custom_license_abstract').value == "" && !confirm("You have not entered a value for License Summary.  Press OK if this is intentional"))
			if (document.getElementById('dprv_custom_license_abstract').value == "" && !confirm(dprv_literals["License_summary_empty"]))
			{
				document.getElementById('dprv_custom_license_abstract').focus();
				return false;
			}

			if (document.getElementById('dprv_license_commit').value == dprv_literals["Update_this_license"])		// 'Update this license'
			{
				document.getElementById("dprv_action").value = "UpdateLicense";
			}
			else
			{
				document.getElementById("dprv_action").value = "AddLicense";
			}
			document.getElementById("dprv_cp").submit();
	}
}

function dprv_LicenseActionAbandon()
{
	document.getElementById('dprv_license_heading').innerHTML = dprv_literals["Default_license_statement"];			// 'Default License Statement';
	document.getElementById('dprv_license_type_caption').innerHTML = dprv_literals["License_type"];					// 'License Type:';
	document.getElementById('dprv_license').style.display='';
	document.getElementById('dprv_license_caption').style.display='';
	document.getElementById('dprv_license_abstract').style.display='';
	document.getElementById('dprv_license_url').style.display='';
	document.getElementById('License_customization').style.display='';
	document.getElementById('dprv_custom_license').style.display='none';
	document.getElementById('dprv_custom_license_caption').style.display='none';
	document.getElementById('dprv_custom_license_abstract').style.display='none';
	document.getElementById('dprv_custom_license_url').style.display='none';
	document.getElementById('dprv_custom_license').value='';
	document.getElementById('dprv_custom_license_abstract').value='';
	document.getElementById('dprv_custom_license_url').value='';
	document.getElementById('dprv_license_commit_0').style.display='none';
	document.getElementById('dprv_license_commit_1').style.display='none';
	document.getElementById('dprv_submit').style.display='';	
}

// FUNCTIONS FOR COPY-PROTECT TAB:

function dprv_toggle_r_c_checkbox()
{
	if (document.getElementById('dprv_frustrate_no').checked == false)
	{
		document.getElementById('dprv_right_click_box').disabled = false;
		document.getElementById('dprv_right_click_box').style.backgroundColor = '#FFFFFF';
		document.getElementById('dprv_record_IP').disabled = false;
		document.getElementById('dprv_record_IP').style.backgroundColor = '#FFFFFF';
		dprv_toggle_record_ip();
		if (document.getElementById('dprv_right_click_box').checked == true)
		{
			document.getElementById('dprv_right_click_message').disabled = false;
			document.getElementById('dprv_right_click_message').style.backgroundColor = '#FFFFFF';
		}
		else
		{
			document.getElementById('dprv_right_click_message').disabled = true;
			document.getElementById('dprv_right_click_message').style.backgroundColor = '#CCCCCC';
		}
	}
	else
	{
		document.getElementById('dprv_right_click_box').disabled = true;
		document.getElementById('dprv_right_click_box').style.backgroundColor = '#CCCCCC';
		document.getElementById('dprv_right_click_message').disabled = true;
		document.getElementById('dprv_right_click_message').style.backgroundColor = '#CCCCCC';
		document.getElementById('dprv_record_IP').disabled = true;
		document.getElementById('dprv_record_IP').style.backgroundColor = '#CCCCCC';
		document.getElementById('dprv_send_email').disabled = true;
		document.getElementById('dprv_send_email').style.backgroundColor = '#CCCCCC';
	}

}
function dprv_toggle_r_c_text(element)
{
	if (element.checked == true)
	{
		document.getElementById('dprv_right_click_message').disabled = false;
		document.getElementById('dprv_right_click_message').style.backgroundColor = '#FFFFFF';
		if (document.getElementById('dprv_right_click_message').value == "")
		{
			document.getElementById('dprv_right_click_message').value = dprv_literals["Default_right_click_alert"];			// "Sorry, right-clicking is disabled; please respect copyrights";
		}
	}
	else
	{
		document.getElementById('dprv_right_click_message').value = "";
		document.getElementById('dprv_right_click_message').disabled = true;
		document.getElementById('dprv_right_click_message').style.backgroundColor = '#CCCCCC';
	}
}
function dprv_toggle_record_ip()
{
	if (document.getElementById('dprv_record_IP').checked == false)
	{
		document.getElementById('dprv_send_email').disabled = true;
		document.getElementById('dprv_send_email').style.backgroundColor = '#CCCCCC';
	}
	else
	{
		document.getElementById('dprv_send_email').disabled = false;
		document.getElementById('dprv_send_email').style.backgroundColor = '#FFFFFF';
	}
}
function dprv_ShowFrustrateCopyText()
{
	// Selecting this option will prevent a user from right-clicking on your web-page (in order to view the source), selecting content (in order to copy to clipboard), or pressing CTRL/U (to view the source) in most browsers.  This may prevent the unauthorised use of your content by unsophisticated users, but will be a small nuisance to a determined content thief. This is as good as it gets on the web - DO NOT BELIEVE the claims of some other plugin authors that your content cannot be stolen...
	dprv_DisplayHelpText(dprv_literals["Frustrate_copy_help"]);
}

// FUNCTIONS FOR DISPLAY OF EVENT LOG HISTORY
var dprv_log_current_page = 1;
function dprv_first_page_clicked()
{
	dprv_log_current_page = 1;
	dprv_draw_log_history_page();
}
function dprv_previous_page_clicked()
{
	if (dprv_log_current_page == 0)
	{
		return false;
	}
	dprv_log_current_page --;
	dprv_draw_log_history_page();
}
function dprv_next_page_clicked()
{
	if (dprv_log_current_page == parseInt(document.getElementById('dprv_total_pages').innerHTML, 10))
	{
		return;
	}
	dprv_log_current_page ++;
	dprv_draw_log_history_page();
}
function dprv_last_page_clicked()
{
	dprv_log_current_page = parseInt(document.getElementById('dprv_total_pages').innerHTML, 10);
	dprv_draw_log_history_page();
}

function setLogHeader()
{
	//document.getElementById('dprv_next_page_button').style.display = '';
    //document.getElementById('dprv_previous_page_button').style.display = '';
    if (document.getElementById('dprv_log_history').innerHTML.length < 2)
    {
        return;
    }
    // Investigate why we check for this value "Event history log is empty" which (when occurs) seems to be inserted above this point in #dprv_log_headers
    //if (document.getElementById('dprv_log_history').innerHTML.indexOf("Event history log is empty") != -1)
	//{
	//	document.getElementById('dprv_next_page_button').disabled = true;
	//	document.getElementById('dprv_previous_page_button').disabled = true;
	//	return;
	//}
	var dprv_log_entries_per_page = 0, dprv_number_of_log_entries = 0, dprv_from_log_entry, dprv_to_log_entry;
	if (document.getElementById('dprv_log_headers').innerHTML.indexOf('Event history log is empty') == -1)
	{
		if (dprv_log_current_page == parseInt(document.getElementById('dprv_total_pages').innerHTML, 10))
		{
			document.getElementById('dprv_next_page_button').disabled = true;
			document.getElementById('dprv_last_page_button').disabled = true;
		}
		else
		{
			document.getElementById('dprv_next_page_button').disabled = false;
			document.getElementById('dprv_last_page_button').disabled = false;
		}
		if (dprv_log_current_page == 1)
		{
			document.getElementById('dprv_previous_page_button').disabled = true;
			document.getElementById('dprv_first_page_button').disabled = true;
		}
		else
		{
			document.getElementById('dprv_previous_page_button').disabled = false;
			document.getElementById('dprv_first_page_button').disabled = false;
		}
		document.getElementById('dprv_this_page').innerHTML = dprv_log_current_page.toString();
		dprv_log_entries_per_page = parseInt(document.getElementById('dprv_log_entries_per_page').innerHTML, 10);
		dprv_number_of_log_entries = parseInt(document.getElementById('dprv_number_of_log_entries').innerHTML, 10);
		dprv_from_log_entry = ((dprv_log_current_page -1) * dprv_log_entries_per_page) + 1;
		dprv_to_log_entry = dprv_log_current_page * dprv_log_entries_per_page;
		if (dprv_to_log_entry > dprv_number_of_log_entries)
		{
			dprv_to_log_entry = dprv_number_of_log_entries;
		}
		document.getElementById('dprv_from_log_entry').innerHTML = dprv_from_log_entry;
		document.getElementById('dprv_to_log_entry').innerHTML = dprv_to_log_entry;
	}
}
function dprv_delete_clicked()
{
	var submittedDate = jQuery.trim(document.getElementById('dprv_log_removal_date').value);
	if (submittedDate == "")
	{
		alert("Please select a date");
		return false;
	}
	var dprv_removal_date = new Date(submittedDate);
	var today = new Date().toJSON().slice(0,10);
	var today = new Date(today);
	var formattedDate = jQuery.datepicker.formatDate('d M yy', dprv_removal_date);
	var dprv_timestamp = parseInt(jQuery.datepicker.formatDate('@', dprv_removal_date), 10) / 1000;
	var confirm_msg = "This will permanently delete all event log entries prior to " +  formattedDate + ".  Press OK to proceed.";
	if (dprv_removal_date >= today)
	{
		confirm_msg = "Are you sure? This will permanently delete all event log entries.";
	}
	if (confirm(confirm_msg))
	{
		dprv_remove_log_entries(dprv_timestamp);
		return true;
	}
	return false;
}
function dprv_remove_log_entries(dprv_timestamp)
{	
	if (document.getElementById('dprv_log_history').innerHTML.indexOf("Event history log is empty") != -1)
	{
		return;
	}
	jQuery(document).ready(function($) 
	{
		// This does the ajax request
		$.ajax({
			url: ajaxurl,
			data:
			{
				'action':'dprv_log_functions',
				'function':'RemoveLogEntries',
				'timestamp':dprv_timestamp
			},
			success:function(data)
			{
			    // This outputs the result of the ajax request
			    data = jQuery.trim(data);
				document.getElementById('popupDialog').innerHTML = data;
				$("#popupDialog").dialog("open");
				dprv_redraw_log_headers();
				setTimeout('dprv_tidyUpAfterRedraw()', 2000);
			},
			error: function(jqXHR, textStatus, errorThrown)
			{
				alert("ajax error: " + textStatus + " " + errorThrown);
			}
		});  
				  
	});
}
function dprv_tidyUpAfterRedraw()
{
				jQuery(document).ready(function($)
				{
			       		jQuery("#dprv_log_removal_date").datepicker({dateFormat: "d M yy" });
				});
				dprv_first_page_clicked();
}

function dprv_draw_log_history_page()
{	
	if (document.getElementById('dprv_log_history').innerHTML.indexOf("Event history log is empty") != -1)
	{
		return;
	}
	jQuery(document).ready(function($) 
	{
		// This does the ajax request
		$.ajax({
			url: ajaxurl,
			data:
			{
				'action':'dprv_log_functions',
				'function':'ShowLogPage',
				'page':dprv_log_current_page
			},
			success:function(data)
			{
			    // This outputs the result of the ajax request
			    data = jQuery.trim(data);
			    document.getElementById('dprv_log_history').innerHTML = data;
				setLogHeader();
			},
			error: function(jqXHR, textStatus, errorThrown)
			{
				alert("ajax error: " + textStatus + " " + errorThrown);
			}
		});  
				  
	});
}

function dprv_redraw_log_headers()
{	
	jQuery(document).ready(function($) 
	{
		// This does the ajax request
		$.ajax({
			url: ajaxurl,
			data:
			{
				'action':'dprv_log_functions',
				'function':'ReDrawLogHeaders'
			},
			success:function(data)
			{
			    // This outputs the result of the ajax request
			    data = jQuery.trim(data);
				document.getElementById('dprv_log_headers').innerHTML = data;
			},
			error: function(jqXHR, textStatus, errorThrown)
			{
				alert("ajax error: " + textStatus + " " + errorThrown);
			}
		});  
				  
	});
}
// FUNCTIONS FOR HISTORY TAB
function dprv_ShowBackDigiproveNote()
{
    dprv_DisplayHelpText(dprv_literals["Back_digiprove_note"]);
}
function digiproveAllClicked()
{
    var undigiproved_posts_count = parseInt(document.getElementById("undigiproved_posts_count").innerHTML, 10);
    if (undigiproved_posts_count > 100 && dprv_subscription_type != "Basic")
	{
		alert("There are " + undigiproved_posts_count + " posts/pages not yet Digiproved. Only 100 at a time will be processed");
	}
	dprv_digiprove_history(0);
}
//function dprv_digiprove_history(count, dprv_all_to_be_Digiproved)
function dprv_digiprove_history(count)
{	
    var dprv_all_to_be_Digiproved = JSON.parse(document.getElementById('dprv_all_to_be_Digiproved').innerHTML);
    var undigiproved_posts_count_old = parseInt(document.getElementById("undigiproved_posts_count").innerHTML, 10);
    var undigiproved_posts_count = dprv_all_to_be_Digiproved.length;
    //alert("undigiproved_posts_count=" + undigiproved_posts_count + "/" + undigiproved_posts_count_old);
    //alert("digiproveAllClicked\ndprv_all_to_be_Digiproved=" + dprv_all_to_be_Digiproved);
    var dprv_post_id = dprv_all_to_be_Digiproved[count];
    //alert("about to Digiprove post[" + count + "], id=" + dprv_post_id);
    document.getElementById("digiproving_now_caption").style.display = "";
    document.getElementById("digiproving_now").innerHTML = dprv_post_id;

    jQuery(document).ready(function ($)
	{
		// This does the ajax request
		$.ajax({
			url: ajaxurl,
			data:
			{
				'action':'dprv_history_functions',
				'function':'Digiprove_Now',
				'count': count,
				'dprv_post_id':dprv_post_id
			},
			success:function(data)
			{
				// This outputs the result of the ajax request
				//alert("data = " + typeof data + " " + data);
			    try
			    {
			        var dprv_response = JSON.parse(data);
			        if (typeof dprv_response == "string")
			        {
			            document.getElementById("back_digiprove_now_button").style.display = "none";
			            document.getElementById("dprv_back_digiprove_error").innerHTML = dprv_response;
			            document.getElementById("digiproving_now").innerHTML += ": failed";
			        }
			        else
			        {
			            if (dprv_response["result_code"] == 0)
			            {
			                var digiproved_posts_count = parseInt(document.getElementById("digiproved_posts_count").innerHTML, 10);
			                var undigiproved_posts_count = parseInt(document.getElementById("undigiproved_posts_count").innerHTML, 10);

			                digiproved_posts_count++;
			                undigiproved_posts_count--;
			                count++;
			                document.getElementById("digiproved_posts_count").innerHTML = digiproved_posts_count;
			                document.getElementById("undigiproved_posts_count").innerHTML = undigiproved_posts_count;
			                document.getElementById("digiproved_now_count").innerHTML = count;
			                var remaining_back_digiprove_allowance = document.getElementById("remaining_back_digiprove_allowance").innerHTML;
			                if (remaining_back_digiprove_allowance != dprv_literals["unlimited"])
			                {
			                    remaining_back_digiprove_allowance = parseInt(document.getElementById("remaining_back_digiprove_allowance").innerHTML, 10);
			                    remaining_back_digiprove_allowance--;
			                    document.getElementById("remaining_back_digiprove_allowance").innerHTML = remaining_back_digiprove_allowance;
			                }
                            
			                //if ((remaining_back_digiprove_allowance == dprv_literals["unlimited"] || remaining_back_digiprove_allowance > 0) && count < 2 && undigiproved_posts_count > 0)
			                if ((remaining_back_digiprove_allowance == dprv_literals["unlimited"] || remaining_back_digiprove_allowance > 0) && undigiproved_posts_count > 0)
			                {
			                    // setTimeout?
			                    setTimeout("dprv_digiprove_history(" + count + ")", 200);
			                }
			                else
			                {
			                    document.getElementById("back_digiprove_now_button").style.display = "none";
			                    document.getElementById("digiproving_now_caption").style.display = "none";
			                    document.getElementById("digiproving_now").style.display = "none";
			                }
			            }
			            else
			            {
			                alert("error response from ajax = " + data);
			            }
			        }
			    }
			    catch (err)
			    {
			        alert("bad response from ajax: " + err);
			    }
			},
			error: function(jqXHR, textStatus, errorThrown)
			{
				alert("ajax error: " + textStatus + " " + errorThrown);
			}
		});  
				  
	});
}

// GENERIC (CROSS-TAB) FUNCTIONS:
function dprv_SubmitSelected()
{
	if (document.getElementById('dprv_renew_api_key').checked == true || dprv_ApiKeyChange())
	{
		if (document.getElementById("message") != null)
		{
			document.getElementById("message").innerHTML = "<p>" + dprv_literals["Processing..."] + "</p>";
		}
		return true;
	}
	return false;
}

function dprv_SubscribersOnly(f)
{
	f=f.replace(/_/g," ");
	if (dprv_subscription_type == "Basic")
	{
		// 'The ' + f + ' function is available only to Digiprove subscribers.  <a href=\"' + dprv_upgrade_link + '&Action=Upgrade\" target=\"_blank\">Select a subscription plan</a>.'
		dprv_DisplayHelpText(dprv_literals["Subscribers_only_basic"].replace("%1$s", f));
	}
	else
	{
		if (dprv_subscription_type == "")
		{
			// 'The ' + f + ' function is available only to Digiprove subscribers. Please complete registration first.'
			dprv_DisplayHelpText(dprv_literals["Subscribers_only_none"].replace("%s", f));
		}
		else
		{
			// 'The ' + f + ' function is available only to current Digiprove subscribers. Your ' + dprv_subscription_type + ' account expired on ' + dprv_subscription_expiry + '. <a href=\"' + dprv_upgrade_link + '&Action=Renew\" target=\"_blank\">Renew your subscription plan</a>.'
			dprv_DisplayHelpText(dprv_literals["Subscribers_only_expired"].replace("%1$s", f));
		}
	}
}

function dprv_PremiumOnly(f)
{
	f=f.replace(/_/g," ");
	if (dprv_subscription_type == "")
	{
		// 'The ' + f + ' function is available only to Digiprove subscribers at Professional level or above. Please complete registration first.'
		dprv_DisplayHelpText(dprv_literals["Premium_only_none"].replace("%1$s", f));
	}
	else
	{
		if (dprv_subscription_type == "Personal")
		{

			// 'The ' + f + ' function is not available under your current plan (' + dprv_subscription_type + ').  <a href=\"' + dprv_upgrade_link + '&Action=Upgrade\" target=\"_blank\">Upgrade your subscription plan</a>.'
			dprv_DisplayHelpText(dprv_literals["Premium_only_personal"].replace("%1$s", f));
		}
		else
		{
			// 'The ' + f + ' function is available to subscribers at Professional level and above - your current plan is &quot;' + dprv_subscription_type + '&quot;.  <a href=\"' + dprv_upgrade_link + '&Action=Upgrade\" target=\"_blank\">Upgrade your subscription plan</a>.'
			dprv_DisplayHelpText(dprv_literals["Premium_only_else"].replace("%1$s", f));
		}
	}
}

function dprv_DisplayHelpText(help_text)
{
	document.getElementById('HelpText').innerHTML = help_text;
	document.getElementById('HelpTextContainer').style.display='';
	document.getElementById('HelpTextContainer').style.borderColor='red';
	setTimeout("document.getElementById('HelpTextContainer').style.borderColor='black';",1000);

}
function dprv_HideHelpText()
{
	document.getElementById('HelpTextContainer').style.display='none';
}

// GENERIC Functions
function dprv_indexOfAny(string, values)
{
	var result = -1;
	if (string != null && values != null)
	{
		for (var i=0; i< values.length; i++)
		{
			var pos = string.indexOf(values[i]);
			if (pos != -1)
			{
				if (result == -1 || pos < result)
				{
					result = pos;
				}
			}
		}
	}
	return result;
}
function dprv_trim(str)
{
	return str.replace(/^\s\s*/, '').replace(/\s\s*$/, '');
}
function autoCompleteOff(element)
{
	if (document.getElementById(element).disabled == false)
	{
		document.getElementById(element).disabled = true;
		var dprv_timer = window.setTimeout( function()
		{
			document.getElementById(element).disabled = false;
			//document.getElementById(element).value = '';
			clearTimeout(dprv_timer);
			},
			800);
	}
}

function dprv_addLoadEvent(func)
{ 
	if (typeof window.onload != 'function')
	{ 
		window.onload = func; 
	}
	else
	{ 
		var oldonload = window.onload;
		window.onload = function()
		{ 
			if (oldonload)
			{ 
				oldonload(); 
			}
			func(); 
		} 
	} 
} 

