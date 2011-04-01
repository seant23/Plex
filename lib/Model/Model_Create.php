<?php
namespace Plex;
class Model_Create {
	/**
	 * Model
	 *
	 * @var DB_Model
	 */
	private $model=false;
	private $input=false;
	public 	$insertID = 0;
	
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
		$this->insertID = $this->model->create($this->input, $respond);
		
		if($respond) {
			$response = array(
				'insert_ID'=>$this->insertID
			);
			Plex::getOutput()->respond($response);
		} else {
			return $this->insertID;
		}
	}		
}