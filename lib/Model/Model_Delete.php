<?php
namespace Plex;


class Model_Delete {
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
					Error::show("Missing Primary Key", "In Order To Update You Must Pass The Unique Primary Key ($key)");
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
		
		
		$this->success = $this->model->delete($primaryKeyValue, $respond);
		
		if($respond) {
			$response = array(
				'success'=>true
			);
			Plex::getOutput()->respond($response);
		}
	}		
}