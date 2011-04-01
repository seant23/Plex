<?php
namespace Plex; 

class Web_Service_JSON extends Web_Service implements inOutput {
	
	public function __construct() {
		Plex::setOutput($this);
	}

	public function error($title=false, $description=null,  $errorInfo=false) {
		
		if($errorInfo) {
			$response = $errorInfo;	
		} else {
			$response = array("error"=>array('title'=>$title,'description'=>$description));
		}
		$this->respond($response);
	}
	
	public function search($modelSearch) {
		$modelSearch->run();
		
		$response = array(
			'results'=>$modelSearch->results,
			'count'=>$modelSearch->count,
			'page'=>$modelSearch->page,
			'page_count'=>$modelSearch->pageCount,
			'per_page'=>$modelSearch->perPage
		);
		
		$this->respond($response);
	}

	
	public function respond($response=false) {
		die(json_encode($response));
	}
}