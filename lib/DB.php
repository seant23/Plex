<?php
namespace Plex;

class DB {
	
	const _DEFAULT = -1000;
	const _NULL = -1001;
	
	const FETCH_EVENT = "DB.FETCH";
	const STAMENT_BIND_PARAM_EVENT = "DB.STAMENT_BIND_PARAM_EVENT";
	const STAMENT_EXECUTE_EVENT = "DB.STAMENT_EXECUTE_EVENT";
	
	public static $connections = array();
	public static $drivers = array();
		
	public static function connection($database)
	{
		if($database===false)
		{
			if(defined("DB_DEFAULT"))			
			$database = constant("DB_DEFAULT");
			else
			Error::show("No Database Defined", "You don't have a default database connection!");
		}
		
		return self::$connections[$database];
	}
	
	/**
	 * Return Driver For Database
	 *
	 * @param constant $database
	 * @return inDB
	 */
	public static function driver($database)
	{
		$connection = self::connection($database);
		$driverName = $connection['driver'];
		
		if(!isset(self::$drivers[$driverName])) {
			if(Driver::exists("DB", $driverName)) {
				self::$drivers[$driverName] = Driver::createInstance("DB", $driverName);
			} else {
				Error::show("Driver Not Installed", "You don't have the roper database connection driver ($driverName) for this database!");
			}			
		}
		
		return self::$drivers[$driverName];
	}
	
	public static function link($database)
	{
		$connection = self::connection($database);
		
		if(!isset($connection['link']))
		return self::$connections[$database]['link'] = self::connect($database);
				
		return $connection['link'];
	}
	
	public static function connect($database=false)
	{
		$connection = self::connection($database);
		return self::driver($database)->connect($connection['host'],$connection['user'],$connection['pass'],$connection['database']);
	}
	
	public static function commit($database=false)
	{
		return self::driver($database)->commit(self::link($database));
	}
	
	public static function disconnect($database=false)
	{
		$db = self::get_connection($database);
	}
	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $database
	 * @param unknown_type $var
	 * @return DB_mysqli_statment
	 */
	public static function escape($database=false, $var=false)
	{
		if(!$var)
		{
			$var = $database;
			$database = false;
		}
		
		return self::driver($database)->escape(self::link($database),$var);
	}
	
	/**
	 * Creates New DB Statement
	 *
	 * @param ip $database
	 * @param string $query
	 * @return inDBStatement
	 */
	public static function prepare($database=false, $query=false)
	{
		return self::driver($database)->prepare(self::link($database),$query);
	}
	
	public static function rollback($database=false) {
		return self::driver($database)->rollback(self::link($database));
	}
	
	public static function insert_id($database=false, $sequence=false) {
		return self::driver($database)->insert_id(self::link($database), $sequence);
	}
	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $database
	 * @param unknown_type $query
	 * @return inDBResult
	 */
	public static function query($database=false, $query=false)
	{
		if(!$query) {
			$query = $database;
			$database = false;
		}
		
		$q = self::driver($database)->query(self::link($database),$query);
		
		if(self::driver($database)->error(self::link($database)))		{/* ERROR */
			Error::show("Query Error", self::driver($database)->error(self::link($database)) . " With Query " . $query);
		}		
		
		return $q;
	}
	
	public static function start_transaction($database=false)
	{
		return self::driver($database)->start_transaction(self::link($database));
	}
	
	public static function load()
	{
		return true;
	}
}

