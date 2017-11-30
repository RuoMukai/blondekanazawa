<?php

// THIS VERSION DOES NOT USE TRY/CATCH BLOCKS AND CAN RUN IN PHP4
class Digiprove_HTTP
{
	static public function post($request, $host, $path, $service, $ip=null) 
	{
		$log = new DPLog();  
		$request = "xml_string=" . urlencode($request);
		$http_request  = "POST " . $path . $service . " HTTP/1.1\r\n";
		$http_request .= "Host: $host\r\n";
		$http_request .= "Content-Type: application/x-www-form-urlencoded; charset=UTF-8\r\n";
		$http_request .= "Content-Length: " . strlen($request) . "\r\n";
		$http_request .= "Connection: close\r\n\r\n";
		$http_request .= $request;  

		// use a specific IP if provided
		if ( $ip && long2ip(ip2long($ip)) )
		{
			$http_host = $ip;
		}
		else
		{
			$http_host = $host;
		}

		$response = '';                 
		$dprv_port = 80;
		if (DPRV_SSL == "Yes")
		{
			$e = get_loaded_extensions();	// using this instead of preferable stream_get_transports() which is only supported from php 5 onwards
			foreach ($e as $value)
			{
				if ($value == "openssl")
				{
					$http_host = "ssl://" . $http_host;
					$dprv_port = 443;
					break;
				}
			}
		}
		
			$errno = -1;
			$errstr = "Unknown";

			//if( false != ( $fs = @fsockopen($http_host, $dprv_port, $errno, $errstr, 10) ) ) 
			if( false != ( $fs = @fsockopen($http_host, $dprv_port, $errno, $errstr) ) )	// Use default timeout value
			{                 
				if ($errno == 0)
				{
					fwrite($fs, $http_request);
					stream_set_timeout($fs, 50);
					$get_count = 0;
					$err_level = error_reporting();							// Save current error reporting level
					while ( !feof($fs) )
					{
						error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);	// Suppress Warning errors just for this due to intermittent bug (in IIS?)
						$temp = fgets($fs);
						error_reporting($err_level);						// Reset error reporting back to previous value
						$info = stream_get_meta_data($fs);
						if ($info['timed_out'])
						{
							$log->lwrite("timed out waiting for response");
							return "Error: connection to " . $http_host . " timed out";
						}
						else
						{
							$response .= $temp;
							$get_count = $get_count + 1;
						}
					}
					$log->lwrite("finished getting, about to close socket");
					fclose($fs);
					//TODO: check that response is complete (ends with </string>)
					$response = htmlspecialchars_decode($response, ENT_QUOTES);
					$log->lwrite("response length=" . strlen($response));
					if (strlen($response) == 0)
					{
						$log->lwrite("Empty response from server");
						return "Error: " . $http_host . " returned empty response, server may be offline";
					}
				}
				else
				{
					$log->lwrite("Socket may be open, but error = " . $errno . "/" . $errstr);
					return "Error: Could not open socket to " . $http_host . ".  Error = " . $errno . "/" . $errstr;
				}
			}
			else
			{
				if ($errno ==0)
				{
					return "Error: Could not initialise socket to " . $http_host;
				}
				return "Error: Could not open socket to " . $http_host . ", Error = " . $errno . "/" . $errstr;
			}

			// TODO: Trap HTTP errors such as 403 here (happens if you try to connect to live server w/o ssl)
			if (substr($response,0,4) == "HTTP")			// error starts like this: HTTP/1.1 404 Not Found
			{	
				$pos = strpos($response, " ");
				if (substr($response, $pos+1, 3) != "200" && strpos(strtolower($response), "<title>digiprove service temporarily offline</title>") != false)
				{
					return "The Digiprove service is temporarily offline for maintenance, please try again in a few minutes";
				}
			}
			$log->lwrite("Digiprove_HTTP::post response:");
			$log->lwrite($response);
			return $response;
	}
}
?>