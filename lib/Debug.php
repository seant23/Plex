<?php


class Debug {
	
	public static $startTime = false;
	public static $stopTime = false;
	public static $scriptTime = false;
	
	public static function dumpVar($var) {
		$input = array('var'=>$var);
		View::display("debug.dumpVar",$input);
		exit;
	}
	
	public static function start() {
		self::$startTime = microtime(true);
	}
	
	public static function stop() {
		self::$stopTime = microtime(true);
		self::$scriptTime = self::$stopTime - self::$startTime;
	}
	
	public static function report() {
		View::display("Plex.Debug.Report");
		exit;
	}
}


