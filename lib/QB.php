<?php
namespace Plex;

class QB extends QB_Condition {
	const ANY = -2;
	const ALL = -1;
	const EQUALS = -1;
	const LESSTHEN = -2;
	const GREATERTHEN = -3;
	const CONTAINS = -4;
	const STARTSWITH = -5;
	const ENDSWITH = -6;
	const ISNULL = -7;
	const ISNOTNULL = -8;
	const _AND = -9;
	const _OR = -10;
	const CONDITION = -20;
	const RAW = -2100;
	
	const IN = -21;
	const NOT_IN = -22;
	
	const ASC = "ASC";
	const DESC = "DESC";
	const NONE = "NONE";
	
	const INFO = -29;
	const QUERY = -30;
	const SET = -31;
	
	public $DB_READ = false; 	//Database To Query
	public $source = false;		//Query Input
	public $limit = false;		//Query Limit
	public $offset = false;		//Query Limit Offset
	public $orderBy = false;	//Query Order By
	public $addOnFalse = true;	//Default - If Input Is False Should Query Add Collumn???
	public $matchBy = QB::ALL;	//Default - Match By All or Any
	public $conds = array();	//QB_Conditions
	
	public function __construct($tableName, $database, $source=false) {
		$this->tableName = $tableName;
		$this->DB_READ = $database;
		$this->source = $source ? $source : $_REQUEST;
		
		$this->type = QB::QUERY;
	}
	
	public function query($infoOnly=false) {
		
		$this->type = $infoOnly ? QB::INFO : QB::QUERY;
			
		$query = $this->build($this);
		
		if(isset($_REQUEST['debug'])) {
			echo $query;
			//Plex::getOutput()->respond($query);
			exit;
		}
		
		Log::append('QB', $query);
		
		return $query;
	}
}

class QB_Condition {
	public $vars = array();		//SELECT Vars (Defaults TO *)
	public $type = QB::SET;		//Conditon Style
	public $tableName = false; 	//Table To Query (ONLY USED IF QUERY TYPE)
	public $limit = false;		//Query Limit
	public $offset = false;		//Query Limit Offset
	public $orderBy = array();	//
	public $addOnFalse = null;	//Default - If Input Is False Should Query Add Collumn???
	public $matchBy = null;		//Default - Match By QB::ALL or QB::ANY
	public $conds = array();	//QB_Conditions
	public $columns = false;	//Select Columns - Default is just *
	public $types = array();
	
	public function __construct($type) {
		$this->type = $type;
	}
	
	function addVar($varName,$varType=self::EQUALS,$forcedValue=null) {
		$newVar['name']=$varName;
		$newVar['type']=$varType;
		$newVar['forcedValue']=$forcedValue;
		
		$this->types[$varName] = $varType;
		
		array_push($this->conds,$newVar);
	}
	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $type
	 * @return QB_Condition
	 */
	function addCondition($type=QB::SET) {
		$condition = new QB_Condition($type);
		array_push($this->conds, $condition);
		return $condition;
	}
	
	function addOrderBy($direction=QB::ASC, $varName) {
		
		$paramsIn = func_get_args();
		$params = array_slice($paramsIn, 1);
		$vars = array();
		
		foreach($params as $var) {
			$vars[]="$var";
		}
		
		if(count($vars)>1) {
			$this->orderBy[] = '('. implode(',',$vars) . ') ' . $direction;
		} else {
			$this->orderBy[] = $vars[0] . ' ' . $direction;
		}
	}
	
	function build($QB, $isMain=false) {
		
		//setup build config
		$addOnFalse = $this->addOnFalse === null ? $QB->addOnFalse : $this->addOnFalse;
		$matchBy = $this->matchBy === null ? $QB->matchBy : $this->matchBy;
		
		//Output Varible
		$output = array();
		
		foreach($this->conds as $condition) {
			if($condition instanceof QB_Condition) {
				$cond = $condition->build($QB, false);
				if($cond){
					$output[] = $cond;
				}
			} else {
				 //Does Source Have This Var??? Or Does It Have A Forced Val
				 
				if(isset($QB->source[$condition['name']]) || $condition['forcedValue']!==null) {
					$val = isset($QB->source[$condition['name']]) ? $QB->source[$condition['name']] : $condition['forcedValue'];
					
					if($val && strlen($val) && ($QB->addOnFalse || $val!==false)) {
						
						$qVal = DB::escape($QB->DB_READ, $val);
						
						if($qVal == 'NULL') {
							$condition['type'] = QB::ISNULL;
						}
						
						if($condition['type'] == QB::EQUALS && strpos($qVal, ',')) {
							$condition['type'] = QB::IN;
						}
						
						
						switch($condition['type']) {
							case QB::EQUALS :
								$output[]="{$condition['name']} = '$qVal'";
								break;
								
							case QB::IN :
								$output[]="{$condition['name']} IN ($qVal)";
								break;
								
							case QB::LESSTHEN :
								$output[]="{$condition['name']} < '$qVal'";
								break;
								
							case QB::GREATERTHEN :
								$output[]="{$condition['name']} > '$qVal'";
								break;
								
							case QB::CONTAINS :
								$output[]="{$condition['name']} ILIKE '%$qVal%'";
								break;
								
							case QB::STARTSWITH :
								$output[]="{$condition['name']} ILIKE '$qVal%'";
								break;
								
							case QB::ENDSWITH  :
								$output[]="{$condition['name']} ILIKE '%$qVal'";
								break;
								
							case QB::ISNULL :
								$output[]="{$condition['name']} IS NULL";
								break;
								
							case QB::ISNOTNULL :
								$output[]="{$condition['name']} IS NOT NULL";
								break;
								
							case QB::RAW :
								$output[]="{$val}";
								break;
						}
					}
				}	
			}
		}
		
		switch($this->matchBy) {
			case QB::ANY:
				$conn = " OR ";
				break;
			default:
				$conn = " AND ";
				break;
		}
		
		if($this->type == QB::QUERY) {
			
			if($this->columns) {
				$col = implode(', ', $this->columns);
				$query = "SELECT $col FROM {$this->tableName}";
			} else {
				$query = "SELECT * FROM {$this->tableName}";
			}			
			
			if(count($output)) {
				$query .= " WHERE " . implode($conn,$output);
			}
			
			if(is_array($this->orderBy) && count($this->orderBy)>0) {
				$query .= " ORDER BY ".implode(',',$this->orderBy);
			}
			
			if($this->limit && !$this->offset)
			$query .= " LIMIT {$this->limit}";
			else if($this->limit && $this->offset)
			$query .= " LIMIT {$this->limit} OFFSET {$this->offset}";		
		} else if($this->type == QB::INFO) {
			$query = "SELECT COUNT(*) AS count FROM {$this->tableName}";
			
			if(count($output)) {
				$query .= " WHERE " . implode($conn,$output);
			}
		} else {
			if(count($output)) {
				$query = "( " . implode($conn,$output) . " )";
			} else {
				$query = false;
			}		
		}
		
		return $query;
	}
}
