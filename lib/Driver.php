<?php
namespace Plex; 


class Driver {
	
	public static function createInstance($class, $driver) {
		$driverClassName = "Plex\\{$class}_{$driver}";
		
		if(self::exists($class, $driver)) {
			return new $driverClassName();
		}
	}
	
	public static function exists($class, $driver) {
		
		$driverClassName = "Plex\\{$class}_{$driver}";
		$driverFile = Plex::$BASE_DIR."driver/$class/$driver.php";
		
		if(file_exists($driverFile)) {
			include_once $driverFile;
			if(class_exists($driverClassName)) {
				return true;
			}
		}
		
		return false;
	}
	
}