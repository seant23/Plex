<?php
namespace Plex;

class Validate
{
	const MIN = 'min';
	const MAX = 'max';
	const EMAIL = 'email';
	const NUMBERS_ONLY = 'numbers_only';
	const NO_ZERO = 'no_zero';
	const REQUIRED = 'required';
	const ALIAS = 'alias';

	public $defaultConfig = array(
	self::MIN=>false,
	self::MAX=>false,
	self::EMAIL=>false,
	self::NUMBERS_ONLY=>false,
	self::NO_ZERO =>false,
	self::REQUIRED =>true,
	self::ALIAS =>false);
	
	public $errors = array();

	private $vars = array();

	function __construct($defaultConfig=array())
	{
		$this->defaultConfig = array_merge($this->defaultConfig, $defaultConfig);
	}

	function addVar($key, $config=array())
	{
		$this->vars[$key] = array_merge($this->defaultConfig, $config);
	}
	
	function modVar($key, $config=array())
	{
		$this->vars[$key] = array_merge($this->vars[$key], $config);
	}

	function parse($input)
	{
		$pass = true;
		
		foreach($this->vars as $key=>&$config)
		{
			if($config[self::REQUIRED])
			{
				if(!isset($input[$key]))
				$pass = $this->error("required_fail", $config, $key);
			}
			
			if($config[self::MIN] !== false)
			{
				if(!$this->checkMin($input[$key],$config[self::MIN]))
				$pass = $this->error("min_fail", $config, $key);
			}
			
			if($config[self::MAX] !== false)
			{
				if(!$this->checkMax($input[$key],$config[self::MAX]))
				$pass = $this->error("max_fail", $config, $key);
			}
			
			if($config[self::EMAIL])
			{
				if(!$this->checkEmail($input[$key]))
				$pass = $this->error("email_fail", $config, $key);
			}
			
			if($config[self::NUMBERS_ONLY])
			{
				if(!$this->checkNumbers($input[$key]))
				$pass = $this->error("numbers_fail", $config, $key);
			}
			
			if($config[self::NO_ZERO])
			{
				if(!$this->checkZero($input[$key]))
				$pass = $this->error("no_zero_fail", $config, $key);
			}
			
			$config['value'] = isset($input[$key]) ? $input[$key] : false;
		}
				
		return $pass;
	}
	
	public function values()
	{
		$return = array();
		
		foreach($this->vars as $key=>$config)
		$return[$key]=$config['value'];
		
		return $return;
	}

	public static function checkEmail($value) {
		return eregi("^[a-z0-9\._-]+@{1}([a-z0-9]{1}[a-z0-9-]*[a-z0-9]{1}\.{1})+([a-z]+\.){0,1}([a-z]+){1}$", $value);
	}

	public static function checkMin($value, $min)
	{
		return strlen($value)>=$min;
	}

	public static function checkMax($value, $max)
	{
		return strlen($value)<=$max;
	}

	public static function checkNumbers($value)
	{
		return is_numeric($value);
	}

	public static function checkZero($value)
	{
		return $value == 0;
	}

	private function error($errorType, $config, $key) {
		
		$alias = $config[self::ALIAS] ? $config[self::ALIAS] : $key;
		$message = Config::get("Validate", $errorType);

		$message = str_replace("%key%",$key,$message);
		$message = str_replace("%alias%",$alias,$message);
		$message = str_replace("%min%",$config[self::MIN],$message);
		$message = str_replace("%max%",$config[self::MAX],$message);

		$this->errors[$key]=$message;

		return false;
	}
}