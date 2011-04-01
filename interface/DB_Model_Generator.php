<?php
namespace Plex;

interface inDB_Model_Generator {
	public function generateModel($connection, $database, $table, $modelName);
	public function generateInsertSQL($connection, $database, $table);
	public function generateUpdateSQL($connection, $database, $table);
	public function getPrimaryKeys($connection, $database, $table);
}
