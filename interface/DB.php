<?php
namespace Plex;

interface inDB
{
	public static function connect($hostname, $username, $password, $database);
	public static function commit($database);
	public static function disconnect($database);
	public static function error($database);
	public static function escape($database, $var);
	public static function prepare($database, $query);
	public static function rollback($database);
	public static function query($database, $query);
	public static function start_transaction($database);
	public static function insert_id($database, $sequence);
}

interface inDBStatement
{	
	public function bind_param($value,$type='s');
	public function bind_params($types, $values);
	public function execute();
	public function fetch();
	public function free();
	public function reset();
	public function insert_id($idVar=false);
}

interface inDBResult
{
	public function __construct($database, $query);
	public function fetch_all($resultType=DB_ASSOC);
	public function fetch($resultType=DB_ASSOC);
	public function fetch_assoc();
	public function fetch_row();
	public function free();
}