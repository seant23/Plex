<?php
namespace Plex;


class Array_Validator {
	private $varConfigs = array();
	
	public $inputSource = null;
	public $errors = array();

	function __construct($inputSource = null) {
		$this->inputSource = $inputSource;
	}

	/**
	 * Create a New Validator and Add To this Array 
	 *
	 * @param String $key
	 * @param String $alias
	 * @param Boolean $required
	 * @return Validator
	 */
	function addVar($key, $alias=null, $required=null) 	{
		$vc = new Validator();
		$vc->alias($alias)->isRequired($required);
		$this->vars[$key] = $vc;
		
		return $vc;
	}


	function validate(&$inputSource=null) {
		$this->errors = array();
		
		foreach($this->vars as $key=>$config) {
			if(!$config->validate($inputSource[$key], $key)) {
				$this->errors[$key] = $config->validationError;
			}
		}
				
		return !count($this->errors);
	}
	
	function getDefaultValues() {
		$values = array();
		
		foreach($this->vars as $key=>$config) {
			$values[$key] = $config->defaultValue;
		}
				
		return $values;
	}
} 