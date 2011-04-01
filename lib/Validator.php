<?php
namespace Plex;

class Validator {
	
	public $min = null;
	public $max = null;
	public $isRequired = true;
	public $isEmail = false;
	public $numbersOnly = false;
	public $alias = null;
	public $noZero = null;
	
	public $defaultValue = null;
	
	public $validationError = null;
	
	public function __construct() {
		
	}
	
	/**
	 * Set Default Value
	 *
	 * @param String $defaultValue
	 * @return Validator
	 */
	public function defaultValue($defaultValue) {
		$this->defaultValue = $defaultValue;
		return $this;
	}
	
	/**
	 * Set Default Value
	 *
	 * @param String $defaultValue
	 * @return Validator
	 */
	public function min($min) {
		$this->min = $min;
		return $this;
	}
	
	/**
	 * Set Default Value
	 *
	 * @param String $defaultValue
	 * @return Validator
	 */
	public function max($max) {
		$this->max = $max;
		return $this;
	}
	
	/**
	 * Set Default Value
	 *
	 * @param String $defaultValue
	 * @return Validator
	 */
	public function isRequired($isRequired=true) {
		$this->isRequired = $isRequired;
		return $this;
	}
	
	/**
	 * Set Default Value
	 *
	 * @param String $defaultValue
	 * @return Validator
	 */
	public function isEmail($isEmail) {
		$this->isEmail = $isEmail;
		return $this;
	}
	
	/**
	 * Set Default Value
	 *
	 * @param String $defaultValue
	 * @return Validator
	 */
	public function numbersOnly($numbersOnly) {
		$this->numbersOnly = $numbersOnly;
		return $this;
	}
	
	/**
	 * Set Default Value
	 *
	 * @param String $defaultValue
	 * @return Validator
	 */
	public function alias($alias) {
		$this->alias = $alias;
		return $this;
	}
	
	/**
	 * Set Default Value
	 *
	 * @param String $defaultValue
	 * @return Validator
	 */
	public function noZero($noZero) {
		$this->noZero = $noZero;
		return $this;
	}
	
	public function validate(&$value, $key=null) {
		$this->validationError = false;		
		
		if(!$this->isRequired && $value==null) {
			$value = $this->defaultValue;
			return true;
		} else {
			if($value==null) {
				return $this->error('required_fail', $value, $key);
			}
			
			if($this->isEmail && !$this->checkEmail($value) ) {
				return $this->error('email_fail', $value, $key);
			}
			
			if($this->min != null && !$this->checkMin($value, $this->min) ) {
				return $this->error('min_fail', $value, $key);
			}
			
			if($this->max != null && !$this->checkMax($value, $this->max) ) {
				return $this->error('max_fail', $value, $key);
			}
			
			if($this->numbersOnly != null && !$this->checkMax($value, $this->max) ) {
				return $this->error('max_fail', $value, $key);
			}
		}
		
		return true;
	}
	
	public function error($errorType, $value, $key=null) {
		
		$alias = $this->alias==null ? $key : $this->alias;
		$message = Config::get("Validator", $errorType);

		$message = str_replace("%key%", $key, $message);
		$message = str_replace("%alias%", $alias, $message);
		$message = str_replace("%min%", $this->min, $message);
		$message = str_replace("%max%", $this->max, $message);
		$message = str_replace("%length%", strlen($value), $message);
		$message = str_replace("%value%", $value, $message);
		$message = str_replace("%default%", $this->defaultValue, $message);

		$this->validationError=$message;

		return false;
	}
	
	public static function checkEmail($value) {
		return eregi("^[a-z0-9\._-]+@{1}([a-z0-9]{1}[a-z0-9-]*[a-z0-9]{1}\.{1})+([a-z]+\.){0,1}([a-z]+){1}$", $value);
	}

	public static function checkMin($value, $min) {
		return strlen($value)>=$min;
	}

	public static function checkMax($value, $max) {
		return strlen($value)<=$max;
	}

	public static function checkNumbers($value) {
		return is_numeric($value);
	}

	public static function checkZero($value) {
		return $value == 0;
	}	
}

