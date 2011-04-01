<?php
namespace Plex;

class Plex
{	
	public static $BASE_DIR = null;
	public static $BASE_URL = null;
	public static $ABSOLUTE_BASE_URL = null;
	public static $PLUGINS = array();
	
	public static $startTime = false;
	public static $outputObj = false;
	
	public static function start() {
		self::$startTime = microtime(true);
		
		date_default_timezone_set('UTC');
		Plex::preload_plugins(); 
		Route::startRoute();		
	}
	
	public static function generatedTime($percision=null) {
		$finishTime = microtime(true);
		return round($finishTime-self::$startTime,$percision);
	}
	
	public static function setOutput($obj) {
		self::$outputObj = $obj;
	}
	
	public static function getOutput() {
		if(self::$outputObj) {
			return self::$outputObj;
		} else {
			return new Error(); 
		}		
	}
	
	public static function com($className) {
		static $com = array();
		
		$a = func_get_args();
		$comId = implode('#',$a);
		
		if(isset($com[$comId]))
		return $com[$comId];

		/**
		 * Their Is Gotta be a better way than this, and Eval :0
		 */
			
		switch(count($a)-1)
		{
			case 0:return $com[$comId] = new $className();
			case 1:return $com[$comId] = new $className($a[1]);
			case 2:return $com[$comId] = new $className($a[1],$a[2]);
			case 2:return $com[$comId] = new $className($a[1],$a[2],$a[3]);
			case 3:return $com[$comId] = new $className($a[1],$a[2],$a[3],$a[4]);
			case 4:return $com[$comId] = new $className($a[1],$a[2],$a[3],$a[4],$a[5]);
			case 5:return $com[$comId] = new $className($a[1],$a[2],$a[3],$a[4],$a[5],$a[6]);
			case 6:return $com[$comId] = new $className($a[1],$a[2],$a[3],$a[4],$a[5],$a[6],$a[7]);
			case 7:return $com[$comId] = new $className($a[1],$a[2],$a[3],$a[4],$a[5],$a[6],$a[7],$a[8]);
			case 8:return $com[$comId] = new $className($a[1],$a[2],$a[3],$a[4],$a[5],$a[6],$a[7],$a[8],$a[9]);
			case 9:return $com[$comId] = new $className($a[1],$a[2],$a[3],$a[4],$a[5],$a[6],$a[7],$a[8],$a[9],$a[10]);
			case 10:return $com[$comId] = new $className($a[1],$a[2],$a[3],$a[4],$a[5],$a[6],$a[7],$a[8],$a[9],$a[10],$a[11]);
			case 11:return $com[$comId] = new $className($a[1],$a[2],$a[3],$a[4],$a[5],$a[6],$a[7],$a[8],$a[9],$a[10],$a[11],$a[12]);
			case 12:return $com[$comId] = new $className($a[1],$a[2],$a[3],$a[4],$a[5],$a[6],$a[7],$a[8],$a[9],$a[10],$a[11],$a[12],$a[13]);
			case 13:return $com[$comId] = new $className($a[1],$a[2],$a[3],$a[4],$a[5],$a[6],$a[7],$a[8],$a[9],$a[10],$a[11],$a[12],$a[13],$a[14]);
			case 14:return $com[$comId] = new $className($a[1],$a[2],$a[3],$a[4],$a[5],$a[6],$a[7],$a[8],$a[9],$a[10],$a[11],$a[12],$a[13],$a[14],$a[15]);
			case 15:return $com[$comId] = new $className($a[1],$a[2],$a[3],$a[4],$a[5],$a[6],$a[7],$a[8],$a[9],$a[10],$a[11],$a[12],$a[13],$a[14],$a[15],$a[16]);
		}		
	}
	
	/**
	 * Add Include Directory To INI
	 *
	 * @param string $dir
	 */ 
	public static function add_include_dir($dir, $use_base_dir=false) {
		$dir = $use_base_dir ? Plex::$BASE_DIR.$dir : $dir;			
		set_include_path(get_include_path() . PATH_SEPARATOR . $dir);
	}
	
	public static function clear_include_dirs() {
		set_include_path(".");
	}
	
	/**
	 * Get Array Containing Include Directories
	 *
	 */
	public static function get_include_dirs() {
		return explode(PATH_SEPARATOR, get_include_path());
	}
	
	/*
	 * Load Plex Plugin
	 *
	 */
	public static function load_plugin($pluginName) {
		
		
		if(!isset(self::$PLUGINS[$pluginName])) {
			include(self::$BASE_DIR. "plugin/" . $pluginName.'.php');
			
			
			
			if(method_exists("Plex\\$pluginName", 'load')) {
				call_user_func(array(__NAMESPACE__ ."\\$pluginName", 'load'));
			}
			
		}
				
		self::$PLUGINS[$pluginName] = true;
	}
	
	/*
	 * Preload Plugins
	 */
	public static function preload_plugins() {
		foreach(Config::get('App', 'preload-plugins') as $plugin) {
			Plex::load_plugin($plugin);
		}
	}
}