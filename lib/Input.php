<?php

class Input
{
	public $input = array();
	
	public $values = array();
	public $errors = array();
	
	const MIN = 1;
	const MAX = 2;
	const REQUIRED = 3;
	const EMAIL = 4;
	const NUMBERS = 5;
	
	function __construct($inputVar = false)
	{
		$this->input = $inputVar ? $inputVar : $_REQUEST;		
	}
	
	function check($key, $options, $alias=false)
	{
		$default_options = array(
			self::MIN => false,
			self::MAX => false,
			self::REQUIRED => false,
			self::EMAIL => false,
			self::NUMBERS => false,
		);
		
		$options = array_merge($options, $default_options);
		
		
		/**
		 * Requirment Check
		 */
		if($options[self::REQUIRED] == true)
		{
			if(!isset($this->input[$key]))
			return $this->errors[$key] = $this->error("required_fail", $options, $key, $alias);
			else 
			$value = $this->input[$key];
		}
		else
		{
			if(isset($this->input[$key]))
			$value = $this->input[$key];
			else 
			{
				$this->values[$key] = null;
				$this->errors[$key] = false;
				return true;
			}
		}
		
		
		/**
		 * Minimum Character Check
		 */
		if($options[self::MIN] && $options[self::MIN]>strlen($value))
		return $this->errors[$key] = $this->error("min_fail", $options, $key, $alias);
		
		
		/**
		 * Maximum Character Check
		 */
		if($options[self::MAX] && $options[self::MAX]<strlen($value))
		return $this->errors[$key] = $this->error("max_fail", $options, $key, $alias);
		
		
		/**
		 * Email Validation Check
		 */
		if($options[self::EMAIL] && !eregi("^[a-z0-9\._-]+"."@{1}"."([a-z0-9]{1}[a-z0-9-]*[a-z0-9]{1}\.{1})+"."([a-z]+\.){0,1}"."([a-z]+){1}$", $this->conf[$email]['value']))
		return $this->errors[$key] = $this->error("email_fail", $options, $key, $alias);
		
		
		/**
		 * Numbers Validation Check
		 */
		if($options[self::NUMBERS] && !is_numeric($value))
		return $this->errors[$key] = $this->error("numbers_fail", $options, $key, $alias);
		
		
		/**
		 * Passed All Checks
		 */
		return true;		
	}
	
	function error($errorType, $options, $key, $alias)
	{
		$message = Config::get("Input", $errorType);
		
		$message = str_replace("%key%",$key,$message);
		$message = str_replace("%alias%",$alias,$message);
		$message = str_replace("%min%",$options[self::MIN],$message);
		$message = str_replace("%max%",$options[self::MAX],$message);
		
		$this->errors[$key]=$message;
		
		return false;
	}
	
	
	
	
	
	
}