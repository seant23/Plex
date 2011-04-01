<?php
namespace Plex;

/**
 * @todo XSS By HTTP_REFFERRER 
 */


abstract class Web_Service {
	
	public $input = false;
	public $feed = false;
	public $action = false;
	
	const STRING = 'string';
	const INT = 'int';
	const BOOL = 'boolean';
	
	abstract public function search($model);
	abstract public function respond($response=false);
	
	public function __construct($input=false) {
		if(!$input) {
			$input = $_REQUEST;
		}
		
		$this->input = $input;
	}
	
	public function run($feed, $action) {
		$fileName = Plex::$BASE_DIR . "handler/Web_Service/{$feed}/{$action}.php";
		
		$this->feed = $feed;
		$this->action = $action;
		
		if(file_exists($fileName)) {
			$service = $this;
			require($fileName);
		} else {
			Error::show("No Such Feed", "Feed ($fileName) Does Not Exist");
		}
	}
	
	public function check($inputKey, $defaultValue=false, $strictType=false) {
		if(!isset($this->input[$inputKey])) {
			$this->input[$inputKey] = $defaultValue;
			
			if($strictType) {
				$this->strict($inputKey,$strictType);
			}
			
		}
		
		return $this->input[$inputKey];
	}
	
	public function need($inputKey, $strictType=false, $errorMessage=false) {
		
		if(!isset($this->input[$inputKey])) {
			if(!$errorMessage)
			$errorMessage = "Web Service Requires $inputKey";
			
			Error::show("Invalid Input", $errorMessage);
		}
		
		if($strictType)
		$this->strict($inputKey,$strictType);
		
		return $this->input[$inputKey];
	}
	
	public function strict($inputKey, $strictType) {
		switch($strictType) {
			case 'string': 
			case 'int': 
			case 'boolean': 
				settype($this->input[$inputKey], $strictType);
				break;
			default :				
				Error::show("Invalid Input", "Invalid Strict Type");
		}
	}
	
	public function v($inputKey) {
		return isset($this->input[$inputKey]) ? $this->input[$inputKey] : false;
	}
	
	
}
