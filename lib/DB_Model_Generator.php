<?php
namespace Plex;

class DB_Model_Generator {
	
	public static $drivers = array();
	
	public static function buildQueue() {
		return $config = Config::getAll("DB_Model_Generator");
	}
	
	public static function generateModels() {
		foreach(self::buildQueue() as $connection=>$queueSettings) {
			$connectionInfo = DB::connection(constant($connection));
			$driver = DB_Model_Generator::getDriver($connectionInfo['driver']);
			
			foreach($queueSettings as $tableName=>$queueSetting) {
				
				$newModelName = str_replace(" ","_",ucwords(str_replace("_"," ",$tableName)));
				$newModelFile = Plex::$BASE_DIR . "/model/__$newModelName.php";
				$newModel = $driver->generateModel($connection, $connectionInfo['database'], $tableName, $newModelName);
				
				file_put_contents($newModelFile, $newModel);
				
				if(isset($queueSetting['Web_Service'])) {
					if(isset($queueSetting['Web_Service']['Create']) && $queueSetting['Web_Service']['Create']) {
						self::createCreateWebService($newModelName);
					}
					
					if(isset($queueSetting['Web_Service']['Delete']) && $queueSetting['Web_Service']['Delete']) {
						self::createDeleteWebService($newModelName);
					}
					
					if(isset($queueSetting['Web_Service']['Update']) && $queueSetting['Web_Service']['Update']) {
						self::createUpdateWebService($newModelName);
					}
					
					if(isset($queueSetting['Web_Service']['Search']) && $queueSetting['Web_Service']['Search']) {
						self::createSearchWebService($newModelName);
					}
				}
				
			}
		}
  	}
  	
  	/**
  	 * Loads and returns driver class
  	 *
  	 * @param String $driverName
  	 * @return inDB_Model_Generator
  	 */
  	public static function getDriver($driverName) {
  		if(!isset(self::$drivers[$driverName])) {
			if(Driver::exists("DB_Model_Generator", $driverName)) {
				self::$drivers[$driverName] = Driver::createInstance("DB_Model_Generator", $driverName);
			} else {
				Error::show("Driver Not Installed", "You don't have the proper Database Model Generator driver ({$driverName}) for this model!");
			}			
		}
		
		return self::$drivers[$driverName];
  	}
  	
  	public static function createCreateWebService($modelName) {
  		
  		$service = View::fetch("Plex.DB_Model_Generator.Web_Service.Create", array('modelName'=>$modelName));
  		$serviceFile = Plex::$BASE_DIR . "/handler/Web_Service/$modelName/Create.php";
  		
  		if(!file_exists(dirname($serviceFile))) {
			mkdir(dirname($serviceFile));
		}
		
		if(!file_exists($serviceFile)) {
			file_put_contents($serviceFile, $service);
		} else {
			/* File Already Exists */
		}
  		
  	}
  	
  	public static function createDeleteWebService($modelName) {
  		$service = View::fetch("Plex.DB_Model_Generator.Web_Service.Delete", array('modelName'=>$modelName));
  		$serviceFile = Plex::$BASE_DIR . "/handler/Web_Service/$modelName/Delete.php";
  		
  		if(!file_exists(dirname($serviceFile))) {
			mkdir(dirname($serviceFile));
		}
		
		if(!file_exists($serviceFile)) {
			file_put_contents($serviceFile, $service);
		} else {
			/* File Already Exists */
		}
  	}
  	
  	public static function createUpdateWebService($modelName) {
  		$service = View::fetch("Plex.DB_Model_Generator.Web_Service.Update", array('modelName'=>$modelName));
  		$serviceFile = Plex::$BASE_DIR . "/handler/Web_Service/$modelName/Update.php";
  		
  		if(!file_exists(dirname($serviceFile))) {
			mkdir(dirname($serviceFile));
		}
		
		if(!file_exists($serviceFile)) {
			file_put_contents($serviceFile, $service);
		} else {
			/* File Already Exists */
		}
  	}
	
  	public static function createSearchWebService($modelName) {
  		$service = View::fetch("Plex.DB_Model_Generator.Web_Service.Search", array('modelName'=>$modelName));
  		$serviceFile = Plex::$BASE_DIR . "/handler/Web_Service/$modelName/Search.php";
  		
  		if(!file_exists(dirname($serviceFile))) {
			mkdir(dirname($serviceFile));
		}
		
		if(!file_exists($serviceFile)) {
			file_put_contents($serviceFile, $service);
		} else {
			/* File Already Exists */
		}
  	}
}