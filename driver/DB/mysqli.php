<?php
namespace Plex; 

class DB_mysqli implements inDB {
	
	public $defaultValue = 'Null';
	public $nullValue = 'Null';
	
	public static function connect($hostname, $username, $password, $database) {
		return mysqli_connect($hostname, $username, $password, $database);
	}
	
	public static function commit($database) {
		return mysqli_commit($database);
	}
	
	public static function disconnect($connection) {
		return mysqli_close($connection);
	}
	
	public static function error($database) {
		return mysqli_error($database);
	}
	
	public static function escape($database, $var) {
		return mysqli_real_escape_string($database, $var);
	}
	
	public static function prepare($database, $query) {
		return new DB_mysqli_statment($database, $query);
	}
	
	public static function rollback($database) {
		return mysqli_rollback($database);
	}	
	
	public static function query($database, $query) {
		return new DB_mysqli_result($database, $query);
	}
	
	public static function start_transaction($database) {
		return mysqli_autocommit($database,false);
	}
	
	public function insert_id($database) {
		return mysqli_insert_id($database);
	}
}

class DB_mysqli_statment implements inDBStatement {
	public $error;
	public $errno;
	public $num_rows;
	
	public $paramTypes;
	public $params;
	public $debug = 1;
	
	public $result;
	public $database;
	public $query;
	public $statment;
	
	public function __construct($database, $query) {
		$this->database = $database;
		$this->query = $query;
		$this->statment = mysqli_prepare($database, $query);
	}
	
	public function bind_param($value,$type='s') {
		if(strlen($type)!=1 || !in_array($type,array("i","d","s","b"))) {
			Error::show("Invalid Param Type","Invalid Statement Parameter Type($type)");
		}
		
		$this->paramTypes .= $type;
		$this->params[] = $value;
	}
	
	public function bind_params($types, $values) {
		if(strlen($types) != count($values)) {
			Error::show("Invalid Parameter Count","Count Mismatch Between Types And Values");
		}
		
		for($i=0; $i<count($values); $i++) {
			$this->bind_param($types[$i], $values[$i]);
		}
	}
	
	public function execute() {
		$params = array($this->statment,$this->paramTypes);

		foreach($this->params as $param) {
			$params[]=$param;
		}

		call_user_func_array('mysqli_bind_param',$params);
		
		$response = mysqli_execute($this->statment);
		
		$this->error = mysqli_stmt_error($this->statment);
		$this->errno = mysqli_stmt_errno($this->statment);
		$this->num_rows = mysqli_stmt_num_rows($this->statment);
		
		if($this->debug && !$response) {
			Error::show("Database Statment Errror #{$this->errno}","{$this->error}<br><br><pre>".print_r($this,1));
		}
				
		return $response;
	}
	
	public function fetch() {
		return mysqli_stmt_fetch($this->statment);
	}
	
	public function free() {
		return mysqli_stmt_free_result($this->statment);
	}
		
	public function reset() {
		return mysqli_stmt_reset($this->statment);
	}
		
	public function insert_id($idVar=false) {
		$idVar = $idVar ? $idVar : 'last_insert_id()';
		
		$q = mysqli_query($this->database, "SELECT $idVar as id");
		$r = mysqli_fetch_array($q);
		return $r['id']; 
	}
	
}


class DB_mysqli_result implements inDBResult {
	public $error;
	public $errno;
	public $num_rows;
	
	public $result;
	public $database;
	public $query;
	
	public function __construct($database, $query) {
		$this->database = $database;
		$this->query = $query;
		
		$this->result = mysqli_query($database, $query);
		$this->error = mysqli_error($database);
		$this->errno = mysqli_errno($database);
		
		if(!$this->error && !$this->errno) {
			$this->num_rows = mysqli_num_rows($this->result);
		}
	}
	
	public function fetch_all($resultType=DB_ASSOC) {
		$results = array();
		
		while($result = $this->fetch($resultType)) {
			$results[] = $result;
		}
		
		return $results;
	}
	
	public function fetch($resultType=DB_ASSOC) {
		switch ($resultType) {
			case DB_ASSOC:
				$type = MYSQL_ASSOC;
				break;
				
			case DB_NUM:
				$type = MYSQL_NUM;
				break;
				
			case DB_BOTH:
				$type = MYSQL_BOTH;
				break;
				
			default:
				Error::show("Invalid Result Type","Invalid Result Type For Fetch");
				break;
		}
		
		return mysqli_fetch_array($this->result,$type);
	}
	
	public function fetch_assoc() {
		return $this->fetch(DB_ASSOC);
	}
	
	public function fetch_row() {
		return $this->fetch(DB_NUM);
	}
	
	public function free() {
		return mysqli_free_result($this->result);
	}
}