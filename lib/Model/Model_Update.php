<?php
namespace Plex;

class Model_Update {	
	
	const SUCCESS = "Model_Update.SUCCESS";
	
	private $model=false;
	private $input=false;
	public 	$success = false;
	
	function __construct($model, $input=false, $run=false) {
		if($input==false) {
			$input=$_REQUEST;
		}
		
		$this->model=$model;
		$this->input=$input;
		
		if($run) {
			$this->run(true);
		}
	}
	
	function run($respond=false) {
		
		$primaryKey = $this->model->DB_KEY;
		
		if(is_array($primaryKey)) {
			$primaryKeyValue = array();
			
			foreach($primaryKey as $key) {
				if(!isset($this->input[$key])) {
					Error::show("Missing Primary Key", "In Order To Update You Must Pass The Unique Primary Keys ($key)");
				} else {
					$primaryKeyValue[$key] = $this->input[$key];
				}
			}
		} else {
			if(!isset($this->input[$primaryKey])) {
				Error::show("Missing Primary Key", "In Order To Update You Must Pass The Unique Primary Key ($primaryKey)");
			} else {
				$primaryKeyValue = $this->input[$primaryKey];
			}
		}
		
		
		$this->success = $this->model->set($primaryKeyValue, $this->input, $respond);
		
		Event_Handler::dispatchEvent(new Event(Model_Update::SUCCESS, $this, $this->success));
		
		if($respond) {
			$response = array(
				'success'=>true,
				'result'=>$this->success
			);
						
			Plex::getOutput()->respond($response);
		} else {
			return $this->success;
		}
	}		
}