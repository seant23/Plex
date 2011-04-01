<?php
namespace Plex; 

class DB_mysql implements inDB {
	
	public $defaultValue = 'Null';
	public $nullValue = 'Null';
	
	public static function connect($hostname, $username, $password, $database)
	{
		$link = mysql_connect($hostname, $username, $password); 
		mysql_select_db($database, $link);
		return $link;
		
	}
	
	public static function commit($database)
	{
		
	}
	
	public static function disconnect($connection)
	{
		return mysql_close($connection);
	}
	
	public static function escape($database, $var)
	{
		return mysql_real_escape_string($var, $database);
	}
	
	public static function prepare($database, $query)
	{
		
	}
	
	public static function rollback($database)
	{

	}	
	
	public static function query($database, $query)
	{
		return new DB_mysql_result($database, $query);
	}
	
	public static function start_transaction($database)
	{

	}
	
	public function insert_id($database)
	{
		return mysql_insert_id($database);
	}
}

class DB_mysql_statment implements inDBStatement
{
	public $error;
	public $errno;
	public $num_rows;
	public $prepareQ;
	public $paramTypes;
	public $paramVars;
	public $statment;
	
	public function __construct($database, $query)
	{
		
	}
	
	public function bind_param($type='s',$value)
	{
		
	}
	
	public function bind_params($types, $values)
	{
		
	}
	
	public function execute()
	{
		
	}
	
	public function fetch()
	{
		
	}
	
	public function free()
	{
		
	}
	
	public function prepare()
	{
		
	}
	
	public function reset()
	{
		
	}
	
}


class DB_mysql_result implements inDBResult 
{
	public $error;
	public $errno;
	public $num_rows;
	
	public $result;
	public $database;
	public $query;
	
	public function __construct($database, $query)
	{
		$this->database = $database;
		$this->query = $query;
		
		$this->result = mysql_query($query, $database);
		$this->error = mysql_error($database);
		$this->errno = mysql_errno($database);
		$this->num_rows = mysql_num_rows($this->result);
		
	}
	
	public function fetch_all($resultType=DB_ASSOC)
	{
		$results = array();
		
		while($result = $this->fetch($resultType))
		$results[] = $result;
		
		return $results;
	}
	
	public function fetch($resultType=DB_ASSOC)
	{
		switch ($resultType)
		{
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
		
		return mysql_fetch_array($this->result,$type);
	}
	
	public function fetch_assoc()
	{
		return $this->fetch(DB_ASSOC);
	}
	
	public function fetch_row()
	{
		return $this->fetch(DB_NUM);
	}
	
	public function free()
	{
		return mysql_free_result($this->result);
	}
}