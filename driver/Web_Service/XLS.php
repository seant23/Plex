<?php
namespace Plex; 

class Web_Service_XLS extends Web_Service implements inOutput {
	
	public function __construct() {
		Plex::setOutput($this);
		parent::__construct();
	}

	public function error($title=false, $description=null,  $errorInfo=false) {
		echo $title . "\r\n" . $description;
		exit;
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
		
		if($modelSearch->count==0) {
			echo "No Results.";
			exit;
		}
		
		$name = $this->check('xls_name', $this->feed . "_" . $this->action);
		
		
		// Send Header
	    header("Pragma: public");
	    header("Expires: 0");
	    header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
	    header("Content-Type: application/force-download");
	    header("Content-Type: application/octet-stream");
	    header("Content-Type: application/download");;
	    header("Content-Disposition: attachment;filename=$name.xls ");
	    header("Content-Transfer-Encoding: binary ");
	    
	    $this->xlsBOF();
	    
	    
	    //Write Headers
	    
	    $col = 0;	 
	    $row = 0;   
	    foreach($modelSearch->results[0] as $k=>$v) {
	    	if($k{0}=='_') {
		    	$this->xlsWriteLabel($row, $col++, $k);
	    	}
	    }
	    
	    $row++;
	    
		foreach($modelSearch->results as $result) {
	    	$row++;
			$col=0;
			foreach($result as $k=>$v) {
				if($k{0}=='_') {
					$this->xlsWriteLabel($row, $col++, $v);
				}
			}			
	    }    
	    
	    $this->xlsEOF();
	}

	
	public function respond($response=false) {
		echo "Unsupported.";
		exit;
	}
	
	
	public function xlsBOF() { 
	    echo pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);  
	    return; 
	} 
	
	public function xlsEOF() { 
	    echo pack("ss", 0x0A, 0x00); 
	    return; 
	} 
	
	public function xlsWriteNumber($Row, $Col, $Value) { 
	    echo pack("sssss", 0x203, 14, $Row, $Col, 0x0); 
	    echo pack("d", $Value); 
	    return; 
	} 
	
	public function xlsWriteLabel($Row, $Col, $Value ) { 
	    $L = strlen($Value); 
	    echo pack("ssssss", 0x204, 8 + $L, $Row, $Col, 0x0, $L); 
	    echo $Value; 
		return; 
	}
	
}