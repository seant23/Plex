<?php
namespace Plex;

class Query_Search {
	private $selectExpression=false;
	private $tableReference=false;
	private $input=false;
	private $offset=0;
	
	public $orderBy = false;
	private $direction = QB::ASC;
		
	public $results = array();
	public $count = 0;
	public $page = 1;
	public $perPage = 20;
	public $pageCount = 0;
	public $debug = false;
	
	public function __construct($selectExpression, $tableReference, $input=false) {
		if($input==false) {
			$input=$_REQUEST;
		}
		
		$this->selectExpression = $selectExpression;
		$this->tableReference = $tableReference;
		$this->input=$input;
		
		if(isset($input['order_by'])) {
			$this->direction = QB::ASC;
			
			$this->orderBy = $input['order_by'];
			
			if(isset($input['order_direction'])) 
				$this->direction=$input['order_direction'];
		}
		
		if(isset($input['per_page']))
			$this->perPage = $input['per_page'];
			
		if(isset($input['page']))
			$this->page = $input['page'];
			
		$this->offset = ($this->page-1) * $this->perPage;
		
		if(isset($input['debug'])) 
			$this->debug=true;
		
	}
	
	public function run() {
		
		$tailSQL = "";
		
		if($this->orderBy)
			$tailSQL .= " ORDER BY {$this->orderBy} {$this->direction}";
		
		if($this->perPage)
			$tailSQL .= " LIMIT {$this->perPage}";
		
		if($this->offset)
			$tailSQL .= " OFFSET {$this->offset}";
		
			//$this->debug=true;
			
		if($this->debug) {
			echo "SELECT {$this->selectExpression} {$this->tableReference}{$tailSQL}";
			exit;
		}
			
		$this->results = DB::query("SELECT {$this->selectExpression} {$this->tableReference}{$tailSQL}")->fetch_all();
		$information = DB::query("SELECT COUNT(*) AS count {$this->tableReference}")->fetch();
		
		$this->count = $information['count'];
		$this->pageCount = $this->perPage>0 ? ceil($this->count / $this->perPage) : 0;
	}
}