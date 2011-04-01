<?php
namespace Plex;

/**
 * Error Handling Class
 *
 * @author Sean_T23
 * @package Core
 * @todo Add Magic Varibles %line %file %message To Custom Error Messages
 * @todo Add Customizable 404 Messages...
 */

/**
 * Error Handling Class
 */
class Error implements inOutput {
	/**
	 * Loose Error Handling Style - Die Only On Fatal Errors
	 */
	const LOOSE=1;
	/**
	 * Strict Error Handling Style - Die Only On Any Errors
	 */
	const STRICT=2;
	
	/**
	 * Error Handling Style
	 * Possible Values Are Error:STRICT & Error:LOOSE
	 */
	public static $style=2;
	
	public static $customHandler = false;
	
	
	/**
	 * Default Error Display
	 *
	 * @param String $message
	 * @param String $description
	 * @param Array $errorInfo
	 */
	public function error($message=false, $description=false, $errorInfo=false, $backtraceCount=0) {
		header("content-type:text/html");
		
		if($errorInfo) {
			View::display("Plex.Error.script",$errorInfo);
		} else if($message || $description) {
			$data['title'] = $message;
			$data['body'] = $description;
			$data['backtrace'] = array_slice(debug_backtrace(),1, $backtraceCount);
			
			View::display("Plex.Error.server",$data);
		}
	}
	
	/**
	 * Default Error Handler / Used If Plex's Output Is Not Set
	 *
	 * @param Int $severity Error Severity
	 * @param String $message Error Message
	 * @param String $file File Error Occured In
	 * @param Int $line Line Of File Error Occured On
	 */
	public static function handler($severity, $message, $file=false, $line=false){
		if(self::$style == self::STRICT) {
			//ob_clean();	
			
			
			if($severity == 8192) {
				return true;
			}
			
			
			if($severity>0) {
				$type = Config::get("Error",$severity);
			} else {
				$type = "Undefined";
			}
			
			$errorInfo['severity'] = $severity;
			$errorInfo['message'] = $message;
			$errorInfo['file'] = $file;
			$errorInfo['line'] = $line;
			$errorInfo['backtraces'] = array_slice(debug_backtrace(),1);
			
			
			$date = date("m-d-y");
			$id = count(File_Util::file_list(dirname(Log::logFile("Error.$date.Test")), 'txt$'));
			Log::append("Error", "(Error/$date/Error_$id.txt) $file:$line - $message");
			Log::custom("Error.$date.Error_$id", print_r($errorInfo, true));
			
			if(self::$customHandler) {
				call_user_func(self::$customHandler, $errorInfo);
			} else {
				Plex::getOutput()->error(false, false, $errorInfo);
				exit;
			}
		}
	}
	
	/**
	 * User Error
	 *
	 * @param String $title Error Title
	 * @param String $description Error Description
	 * @param Boolean $die Die On This Error
	 */
	public static function show($title, $description=false, $die=true) {
		if(php_sapi_name() == 'cli') {
			CLI::NL(1);
			CLI::colorEcho($title, true, CLI::RED);
			echo $description;
			CLI::NL(2);
		} else {
			ob_clean();
			Plex::getOutput()->error($title, $description, false);
		}
		
		if($die) {
			exit;
		}
	}
	
	/**
	 * Web Server 404 - Used mostly by Route
	 */
	public static function show404() {
		header("HTTP/1.1 404 Not Found");
		self::show("404 Page Not Found", "The page you requested was not found.");
	}
}