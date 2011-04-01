<?php

namespace {
	function autoloadPlex($className) {
		
		$fullClassName = $className;
		
		if(substr($className, 0, 5) == "Plex\\") {
			$className = substr($className, 5);
		}		
		
		if(substr($className,0,2)=='in') {
			$interface = substr($className,2);
			$interfaceFile = Plex\Plex::$BASE_DIR . "interface/$interface.php";
			
			require_once($interfaceFile);
									
			if(interface_exists($fullClassName)) {
				return true;
			}
		} else {
			include_once("$className.php");
		}
		
		if(!class_exists($fullClassName)) {
			
			foreach(array_reverse(Plex\Plex::get_include_dirs()) as $libDir) {
				if($libDir=='.') {
					continue;
				}
				
				$libFile = $libDir."/$className.php";
				
				if(file_exists($libFile)) {
					include_once($libFile);
				}
				
				if(class_exists($fullClassName)) {
					break;
				}
			}
	
			if(!class_exists($fullClassName)) {
				echo "Class($fullClassName) Could Not Be Loaded\n";
				print_r(debug_backtrace());
				exit;
			}
		}
	} 
	
	if(!function_exists('__autoLoad')) {
		function __autoLoad($className) {
			autoloadPlex($className);
		}
	}
}


namespace Plex {
	/**
	 * Configuration / Startup Script
	 *
	 * @author Sean_T23
	 * @package Plex
	 */
	
	require_once('lib/Plex.php');
	
	require_once('interface/Output.php');
	require_once('lib/Error.php');
	require_once('lib/Spyc.php'); 
	require_once('lib/Config.php');
	
	Plex::$BASE_DIR = dirname(__FILE__).'/';
	Plex::$BASE_URL = substr($_SERVER['SCRIPT_NAME'],0,strrpos($_SERVER['SCRIPT_NAME'],'/')+1);
	Plex::$BASE_URL = Plex::$BASE_URL == '/' ? '' : Plex::$BASE_URL;
	Plex::$ABSOLUTE_BASE_URL = "CLI"; //($_SERVER['SERVER_PORT']==80?'http://':'https://') . $_SERVER["SERVER_NAME"] . Plex::$BASE_URL;
	
	
	Plex::add_include_dir(Plex::$BASE_DIR."lib");
	Plex::add_include_dir(Plex::$BASE_DIR."model");
	Plex::add_include_dir(Plex::$BASE_DIR."plugin");
	
	
	
	set_error_handler(__NAMESPACE__ .'\Error::handler');
	date_default_timezone_set("UTC");
	
	
	define('DB_ASSOC',0);
	define('DB_NUM',1);
	define('DB_BOTH',2);
	
	$dbconfigs = Config::getAll("DB");
	
	foreach($dbconfigs as $const=>$dbconfig) {
		$dbconfig['const'] = $const;
		$id = array_push(DB::$connections,$dbconfig);
		define($const,$id-1);
	}
		
	Plex::preload_plugins();
}
