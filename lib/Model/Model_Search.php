<?php
namespace Plex;

class Model_Search {
	private $model=false;
	private $input=false;
		
	public $results = array();
	public $count = 0;
	public $page = 0;
	public $perPage = 0;
	public $pageCount = 0;
	
	function __construct($model, $input=false) {
		
		
		if($input==false) {
			$input=$_REQUEST;
		}
		
		$this->model=$model;
		$this->input=$input;
		$this->model->initQB();
		
		if(isset($input['order_by'])) {
			
			$dir = QB::ASC;
			
			if(isset($input['order_direction']))
			$dir=$input['order_direction'];
			
			$this->model->QB->addOrderBy($dir, $input['order_by']);		
		}
		
		if(isset($input['filter']) && isset($input['filter_value'])) {
			
			$filterCondition = $this->model->QB->addCondition(QB::SET);
			$filterCondition->matchBy=QB::ANY;
			
			if(!empty($input['filter']) && !empty($input['filter_value'])) {
				$filter_params = explode(';',$input['filter']);
				
				foreach($filter_params as $param) {
					
					if(isset($this->model->QB->types[$param])) {
						$cond = $this->model->QB->types[$param];						
					} else {
						$cond = QB::CONTAINS;
					}
					
					if($cond == QB::EQUALS) {
						if(is_numeric($input['filter_value'])) {
	 						$filterCondition->addVar($param, $cond, $input['filter_value']);
						}
					} else {
						$filterCondition->addVar($param, $cond, $input['filter_value']);
					}
					
					
				}
			}
		} else if(isset($input['filterKeys']) && isset($input['filterValue'])) {
			$filterCondition = $this->model->QB->addCondition(QB::SET);
			$filterCondition->matchBy=QB::ANY;
			
			if(!empty($input['filterKeys']) && !empty($input['filterValue'])) {
				$filter_params = explode(';',$input['filterKeys']);
				
				foreach($filter_params as $param) {
					
					if(isset($this->model->QB->types[$param])) {
						$cond = $this->model->QB->types[$param];						
					} else {
						$cond = QB::CONTAINS;
					}
					
					if($cond == QB::EQUALS) {
						if(is_numeric($input['filterValue'])) {
	 						$filterCondition->addVar($param, $cond, $input['filterValue']);
						}
					} else {
						$filterCondition->addVar($param, $cond, $input['filterValue']);
					}
					
					
				}
			}
		}
		
		if(isset($input['exclude'])) {
			if(!empty($input['exclude'])) {
				$this->model->QB->columns = array();
				$exclude_params = explode(';',$input['exclude']);
				
				foreach($this->model->QB->conds as $cond) {
					if(array_search($cond['name'], $exclude_params) === false) {
						$this->model->QB->columns[] = $cond['name'];
					}
				}
			}
		}
		
		if(isset($input['columns'])) {
			if(!empty($input['columns'])) {
				$this->model->QB->columns = array();
				$columns_params = explode(';',$input['columns']);
				
				foreach($this->model->QB->conds as $cond) {
					if(array_search($cond['name'], $columns_params)) {
						$this->model->QB->columns[] = $cond['name'];
					}
				}
			}
		}
		
		$this->perPage = $this->model->QB->limit = isset($input['per_page']) ? $input['per_page'] : 20;
		$this->page = isset($input['page']) ? ($input['page']) : 1;
		$this->model->QB->offset = ($this->page-1) * $this->perPage;
		
		if(isset($input['debug'])) {
			$this->model->QB->debug=true;
		}
		
	}
	
	function run() {
		$this->results = $this->model->get($this->input)->results;
		
		if(!$this->results) {
			$this->results=array();
		}
		
		$information = DB::query($this->model->QB->DB_READ, $this->model->QB->query(true))->fetch();
		
		$this->count = $information['count'];
		$this->pageCount = $this->perPage>0 ? ceil($this->count / $this->perPage) : 0;
	}		
}