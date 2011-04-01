<?php
namespace Plex;

/**
 * Simple Class To Handle Logs
 * 
 * @todo Add Log Rotator / Max Size - Some Are Getting Big
 *
 */
class Log{
		
	public static function logFile($logName) {
		$fileName=str_replace('.','/',$logName).'.txt';
		$fileName=Plex::$BASE_DIR . Config::get("Plex","log_dir") . "/" . $fileName;
		$dirName=dirname($fileName);
		
		if(!file_exists($dirName)) {
			mkdir($dirName, 0777, true);
		}
		
		return $fileName;
	}

	public static function custom($logName, $customText) {
		file_put_contents(self::logFile($logName), $customText);
	}
	
	public static function append($logName, $msg) {
		if(isset($_SERVER['REMOTE_ADDR']))  {
			file_put_contents(self::logFile($logName),date(Config::get("Plex","log_date_format")) . " -- {$_SERVER['REMOTE_ADDR']} -- " . $msg."\r\n",FILE_APPEND);
		} else {
			file_put_contents(self::logFile($logName),date(Config::get("Plex","log_date_format")) . " -- " . $msg."\r\n",FILE_APPEND);
		}
	}
		
	public static function clear($logName) {
		file_put_contents(self::logFile($logName),"");
	}	
}