<?php
// ERROR-HANDLING FUNCTION:
/*
	BEWARE:
	 - Use this at appropriate places and ensure that it will get set off - remember you're in a plugin, you do not want to be trapping errors in other people's code 
	 - Every time you set this in a function ensure that it gets reset to previous before you return; there is a hierarchy which is tracked by PHP
*/

class dprvErrors
{
	// Below function not used?
	//function set_handler()
	//{
	//	set_error_handler(array("dprvErrors", "dprv_catch_error"));
	//}
	static public function dprv_catch_error($errno, $errstr, $errfile, $errline)
	{
	 	// Note this function can be triggered twice by a single problem e.g. fsockopen dns error generates 2 (seems to try twice)
		$log = new DPLog();  
		//$log->lwrite("entered dprv_error with " . $errstr);
		global $dprv_last_error;
		$dprv_last_error = $errstr;
		// TODO: add in additional caregories (deprecated etc.)
		switch ($errno)
		{
			case E_NOTICE:
			case E_USER_NOTICE:
				$level = "Notice";
				break;
			case E_WARNING:
			case E_USER_WARNING:
				$level = "Warning";
				break;
			case E_ERROR:
			case E_USER_ERROR:
				$level = "Fatal Error";
				break;
			default:
				$level = "Unknown";
				break;
		}
		$message = $level . '(' . $errno . '): ' . $errstr . ' in ' . $errfile . ' at line ' . $errline;
		dprv_record_event($message);
		
		// This bit below kind of pointless as Fatal Errors will always bypass this handler
		if ($level == "Fatal Error" || $level == "Unknown")
		{
			restore_error_handler();
			return false;
		}

		if (ini_get('log_errors'))
		{
			error_log(sprintf("PHP %s:::  %s in %s on line %d", $level, $errstr, $errfile, $errline));
		}
		return true;
	}
}
?>