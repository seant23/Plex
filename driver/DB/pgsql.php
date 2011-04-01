<?php
namespace Plex; 

class DB_pgsql implements inDB {	
	
	const _DEFAULT = 'Default';
	const _NULL = 'Null';
	
	public static function connect($hostname, $username, $password, $database) {
		$connection =  pg_connect("host=$hostname dbname=$database user=$username password=$password");
		pg_query($connection, "SET SESSION TIMEZONE TO MST7MDT; SET SESSION DATESTYLE TO SQL;");
		return $connection;
	}
	
	public static function commit($database) {
		return pg_exec($this->conn_id, "commit");
	}
	
	public static function disconnect($connection) {
		pg_close($connection);
	}
	
	public static function error($database) {
		return pg_last_error($database);
	}
	
	public static function errno($database) {
		return -1;
	}
	
	public static function escape($database, $var) {
		return pg_escape_string($database, $var);
	}
	
	public static function prepare($database, $query) {
		return new DB_pgsql_statment($database, $query);
	}
	
	public static function rollback($database) {
		return pg_exec($this->conn_id, "rollback");
	}	
	
	public static function query($database, $query) {
		return new DB_pgsql_result($database, $query);
	}
	
	public static function start_transaction($database) {
		return pg_exec($database, "begin");
	}
	
	public static function version($database){
		return pg_version();
	}
	
	public static function getSequenceVal($database, $sequence, $next=false) {
		$func = $next ? 'NEXTVAL' : 'CURVAL';
		return current(pg_fetch_assoc(pg_query($database, "SELECT $func('$sequence')")));		
	}
	
	public static function getSequence($database, $table, $column) {
		if(self::version($database)>=8) {
			return current(pg_fetch_assoc(pg_query($database, "SELECT pg_get_serial_sequence('$table','$column')")));
		} else {
			return "{$table}_{$column}_seq";
		}
	}
	
	public static function lastVal($database) {
		$q = pg_query($database, "SELECT LASTVAL()");
		$r = pg_fetch_assoc($q);
		return current($r);		
	}
	
	public static function insert_id($database, $sequence) {
		
		$version = self::version($database);
		$version = $version['server'];
		
		if ($version >= '8.1' && $sequence==null) {
			return self::lastVal($database);
		} elseif ($table != null && $column != null && $v >= '8.0')
		{
			$sql = sprintf("SELECT pg_get_serial_sequence('%s','%s') as seq", $table, $column);
			$query = $this->query($sql);
			$row = $query->row();
			$sql = sprintf("SELECT CURRVAL('%s') as ins_id", $row->seq);
		}
		elseif ($table != null)
		{
			// seq_name passed in table parameter
			$sql = sprintf("SELECT CURRVAL('%s') as ins_id", $table);
		}
		else
		{
			return pg_last_oid($this->result_id);
		}
		$query = $this->query($sql);
				
		$row = $query->row();
		return $row->ins_id;
	}
}

class DB_pgsql_statment implements inDBStatement {	
	private $paramTypes = "";
	private $params = array();
	
	/**
	 * Show Errors
	 *
	 * @var boolean
	 */
	public $debug = true;
	
	/**
	 * @var DB_pgsql_result
	 */
	public $result = null;
	
	/**
	 * PostgreSQL connection resource 
	 *
	 * @var Resource
	 */
	public $database = null;
	
	/**
	 * Statement Query
	 *
	 * @var String
	 */
	public $query = null;
	
	/**
	 * Create New DB_pgsql Statement
	 *
	 * @param Resource $database PostgreSQL connection resource 
	 * @param String $query Statement Query
	 */
	public function __construct($database, $query) {
		$this->database = $database;
		$this->query = $query;
	}
	
	/**
	 * Clean Params, Makes This Statement Re-Usable
	 *
	 */
	public function clean() {
		$paramTypes = "";
		$params = array();
	}
	
	/**
	 * Bind Single Parameter
	 *
	 * @param Mixed $value Parameter Value
	 * @param String $type Parameter Type("i","d","s","b")
	 */
	public function bind_param($value,$type='s') {
		if(strlen($type)!=1 || !in_array($type,array("i","d","s","b"))) {
			Error::show("Invalid Param Type","Invalid Statement Parameter Type($type)");
		}
		
		$this->paramTypes .= $type;
		$this->params[] = $value;
	}
	
	/**
	 * Bind Multipe Parameters
	 *
	 * @param String $types Parameter Types("i","d","s","b")
	 * @param Array $values Array Containing Parameter Values
	 */
	public function bind_params($types, $values) {
		if(strlen($types) != count($values)) {
			Error::show("Invalid Parameter Count","Count Mismatch Between Types And Values");
		}
		
		for($i=0; $i<count($values); $i++) {
			$this->bind_param($types[$i], $values[$i]);
		}
	}
	
	/**
	 * Execute SQL Statment
	 *
	 * @return Boolean True on Success, False on Failure
	 */
	public function execute() {
		
		/**
		 * Prepare Parameters
		 */
		$stamentQuery = $this->query;
		$posOffset = 0;
		
		foreach($this->params as $i=>$param) {
			
			if($param == null || $param == 'NULL') {
				$formatedParam = "NULL";
			} else if($param === DB::_DEFAULT){
				$formatedParam = DB_pgsql::_DEFAULT;
			} else {
				switch($this->paramTypes[$i]) {
					case 'i':
						$formatedParam = (int) $param;
						break;
					case 'd':
						$formatedParam = (float) $param;
						break;
					case 'b':
						$formatedParam = pg_escape_bytea($this->database, $param);
						break;
					case 's':
						$formatedParam = pg_escape_string($this->database, $param);
						break;
				}
				
				$formatedParam = "'$formatedParam'";
			}
			
			$start = strpos($stamentQuery, "?", $posOffset);
			$stamentQuery = substr_replace($stamentQuery, $formatedParam, $start, 1);	
			$posOffset = $start	+ strlen($formatedParam);
		}
				
		if(strpos($stamentQuery, '?', $posOffset) !== FALSE) {
			Error::show("Invalid Parameter Count", "Count Mismatch Between Values And (?)Placements");
		}
		
		$this->result = new DB_pgsql_result($this->database, $stamentQuery);
		
		if($this->debug && $this->result->error) {
			Error::show("Database Statment Errror #{$this->result->errno}","{$this->result->error}<br><br><pre>".print_r($this,1));
		}		
		
		return !$this->result->error;
	}
	
	public function fetch() {
		
	}
	
	public function insert_id($idVar=false) {
		if($idVar) {
			$insertIDQ = DB_pgsql::query($this->database, "SELECT CURRVAL('$idVar') as last_insert_id")->fetch();
			return current($insertIDQ);
		}
	}
	
	public function reset() {
		
	}
	
	public function free() {
		
	}
}


class DB_pgsql_result implements inDBResult {
	public $error = false;
	public $errno = false;
	public $num_rows;
	
	public $result;
	public $database;
	public $query;
	
	public function __construct($database, $query) {
		$this->database = $database;
		$this->query = $query;
				
		Error::$customHandler = array($this, "error_handler");
		
		$this->result = pg_query($database, $query);
		Error::$customHandler = false;
		
		if(!$this->error && !$this->errno) {
			$this->num_rows = pg_num_rows($this->result);
		}
				
	}
	
	public function error_handler() {
		$this->error = pg_last_error($this->database);
		$this->errno = -1;
		Error::show("Query Error", $this->error . "\n\n" . $this->query, false, 1);
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
				$type = PGSQL_ASSOC;
				break;
			case DB_NUM:
				$type = PGSQL_NUM;
				break;
			case DB_BOTH:
				$type = PGSQL_BOTH;
				break;
			default:
				Error::show("Invalid Result Type","Invalid Result Type For Fetch");
				break;
		}
				
		$result = pg_fetch_array($this->result, null, $type);
		
		Event_Handler::dispatchEvent(new Event(DB::FETCH_EVENT, $this, $result));
		
		return $result;
	}
	
	public function fetch_assoc() {
		return $this->fetch(DB_ASSOC);
	}
	
	public function fetch_row() {
		return $this->fetch(DB_NUM);
	}
	
	public function free() {
		return pg_freeresult($this->result);
	}
}