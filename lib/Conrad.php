<?php

class Conrad {
	const INT = 0;
	const STRING = 1;
	const EMAIL = 2;
	const PHONE = 3;
	const BOOLEAN = 4;
	
	const ERROR_MIN = 'min_fail';
	const ERROR_MAX = 'max_fail';
	const ERROR_REQUIRED = 'required_fail';
	const ERROR_EMAIL = 'email_fail';
	const ERROR_NUMBERS = 'numbers_fail';
	
	public $vars = array();
	public $errors = array();
	
	public function VC($varName, $default=null, $typeCheck=self::STRING) {

		$varConfig = new Conrad_Var_Config($varName);
		
		if($default!==null) {
			$varConfig->_default($default);
		}
		
		$varConfig->typeCheck($typeCheck);
		$this->vars[$varName] = $varConfig;
		
		return $varConfig;
	}
	
	public static function errorMessage($var, $failureType) {
		$alias = $var->alias===null ? $var->varName : $var->alias;
		$message = Config::get("Validate", $failureType);

		$message = str_replace("%key%", $var->varName, $message);
		$message = str_replace("%alias%", $alias, $message);
		$message = str_replace("%length%", strlen($var->value), $message);
		$message = str_replace("%value%", $var->value, $message);
		$message = str_replace("%default%", $var->default, $message);
		$message = str_replace("%min%", $var->min, $message);
		$message = str_replace("%max%", $var->max, $message);
		
		return $message;
	}
	
	public function addError($var, $failureType) {
		$this->errors[$var->varName][$failureType] = array(
			'varName'=>$var->varName,
			'failureType'=>$failureType,
			'failureMessage'=> $this->errorMessage($var, $failureType)
		);
	}
	
	public function validateSingle($varName, $input, $dieOnInvalid=false) {
	
		// ==========
		// = Get VC =
		// ==========
		if($varName instanceof Conrad_Var_Config) {
			$vc = $varName;		
		} else if (isset($this->vars[$varName])) {
			$vc = $this->vars[$varName];
		} else {
			Error::show("Unknown Var", "Can't Find $varName in Config");
		}
		
		// ===========================
		// = Check Input && Required =
		// ===========================
		if(isset($input[$varName])) {
			$vc->value = $input[$varName];
		} else {
			if($vc->required) {
				$this->addError($vc, self::ERROR_REQUIRED);
			} else {
				$vc->value = $vc->default;
			}
		}
		
		// ====================
		// = Check Min && Max =
		// ====================
		$vc->length = strlen($vc->value);
		if($vc->min !== false) {
			if(is_numeric($vc->value)) {
				if($vc->value<$vc->min) {
					$this->addError($vc, self::ERROR_MIN);
				}
			} else {
				if($vc->length < $vc->min) {
					$this->addError($vc, self::ERROR_MIN);
				}
			}
		} else if($vc->max !== false) {
			if(is_numeric($vc->value)) {
				if($vc->value>$vc->max) {
					$this->addError($vc, self::ERROR_MAX);
				}
			} else {
				if($vc->length > $vc->max) {
					$this->addError($vc, self::ERROR_MAX);
				}
			}
		}
		
		/**
		 * @todo Add Type Checking
		 */
				
	}
	
	public function validateMultiple($input, $dieOnInvalid=false) {
		foreach($this->vars as $var) {
			$this->validateSingle($var->varName, $input, $dieOnInvalid);
		}
		
		if(count($this->errors)) {
			Plex::getOutput()->respond($this->errors);
			exit;
		}
	}	
}

class Conrad_Var_Config {
	public $required = false;
	public $min = false;
	public $max = false;
	public $default = null;
	public $alias = null;
	public $typeCheck = null;
	
	public $varName = null;
	public $value = null;
	public $length = null;
	
	
	public function __construct($varName) {
		$this->varName = $varName;
	}
	
	public function min($value) {
		$this->min = $value;
		return $this;
	}
	
	public function max($value) {
		$this->max = $value;
		return $this;
	}
	
	public function alias($value) {
		$this->alias = $value;
		return $this;
	}
	
	public function _default($value) {
		$this->default = $value;
		return $this;
	}
	
	public function required($value) {
		$this->required = true;
		return $this;
	}
	
	public function typeCheck($value) {
		$this->typeCheck=$value;
		return $this;
	}
	
	public function typeSet($value) {
		$this->typeSet=$value;
		return $this;
	}
}