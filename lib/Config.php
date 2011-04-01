<?php
namespace Plex;

/**
 * Config Loader / Parser
 *
 */

class Config
{
	/**
	 * cache of currently loaded config varibles
	 *
	 * @var array
	 */
	public static $configVars = array();
	
	/**
	 * Returns individual parameter from a config file
	 *
	 * @param string $config
	 * @param string $var
	 * @return string
	 */
	public static function get($config,$var)
	{
		self::load($config);
		return self::$configVars[$config][$var];
	}
	
	/**
	 * Returns all paremeters in $config File
	 *
	 * @param string $config
	 * @return array
	 */
	public static function getAll($config)
	{
		self::load($config);
		return self::$configVars[$config];
	}
	
	/**
	 * Loads config file into cache
	 *
	 * @param string $config
	 */
	public static function load($config)
	{
		$configDir = Plex::$BASE_DIR."/config/";
		$yamlFile = $configDir.$config.".yml";
			
		if(!isset(self::$configVars[$config]))
		self::$configVars[$config] = Spyc::YAMLLoad($yamlFile);
	}
}