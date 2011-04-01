<?php


class DB_Model_Generator_mysqli implements inDB_Model_Generator {
	
	public static function indent($input, $tabs, $useSpaces = false) {
		$indent = str_pad("", $tabs, $useSpaces ? "    " : "\t");
		return $indent.str_replace("\n", "\n" .$indent, $input);
	}
	
	public function generateModel($connection, $database, $table, $modelName) {
				
		$primaryKeysInfos = $this->getPrimaryKeys($connection, $database, $table);
		$primaryKeys = array();
		
		foreach($primaryKeysInfos as $primaryKeysInfo) {
			$primaryKeys[] = "'{$primaryKeysInfo['Field']}'";
		}
		
		$tableFieldNames = array();
		$tableFieldDefinitions=DB::query(constant($connection), "DESCRIBE `$database`.$table`")->fetch_all();
		
		foreach($tableFieldDefinitions as &$tableFieldDefinition) {
			$tableFieldNames[] = "`{$tableFieldDefinition['Field']}`";
			$tableFieldDefinition['Field_Type'] = self::getFieldType($tableFieldDefinition['Type']);
		
			$QBType = "CONTAINS";
		
			if(strpos($tableFieldDefinition['Type'], 'int') !== false)  {
				//$ConradType = (strpos($column['Type'], 'tinyint') === false) ? "INT" : "BOOLEAN";
				$QBType = "EQUALS";
			} else if($tableFieldDefinition['Field']=='email') {
				//$ConradType = "EMAIL";
			} else if($tableFieldDefinition['Field']=='contact_phone') {
				//$ConradType = "PHONE";
			} else {
				//$ConradType = "STRING";
			}
			
			if(($tableFieldDefinition['Extra']=='auto_increment')) {
				//$v['primary_key'] = $column['Field'];
			} else {
				$viewData['qb'][] = array('key' => $tableFieldDefinition['Field'], 'type'=>$QBType);
			}
			
			//$v['conrad'][] = array('key' => $column['Field'], 'type'=>$ConradType);
			
		}
		
		$viewData['createProcedure'] = self::indent($this->generateInsertSQL($connection, $database, $table), 2);
		$viewData['updateProcedure'] = self::indent($this->generateUpdateSQL($connection, $database, $table), 2);
		$viewData['primaryKeys'] = implode(',', $primaryKeys);
		
		
		$viewData['table'] = $table;
		$viewData['database'] = $database;
		$viewData['connection'] = $connection;
		$viewData['className'] = $modelName;
		
		return View::fetch("Plex.DB_Model_Generator.mysqli.generatedDBModel", $viewData);
	}
	
	public function generateInsertSQL($connection, $database, $table) {
		
		$tableFieldNames = array();
		$tableFieldDefinitions=DB::query(constant($connection), "DESCRIBE `$database`.$table`")->fetch_all();
		
		foreach($tableFieldDefinitions as &$tableFieldDefinition) {
			$tableFieldNames[] = "`{$tableFieldDefinition['Field']}`";
			$tableFieldDefinition['Field_Type'] = self::getFieldType($tableFieldDefinition['Type']);
		}
		
		$varibleHolders = implode(',', array_pad(array(), count($tableFieldNames), '?'));
		$fieldList = implode(',', $tableFieldNames);
		
		$viewData['columns'] = $tableFieldDefinitions;
		$viewData['query'] = "INSERT INTO `$table` ($fieldList) VALUES ($varibleHolders); ";
		$viewData['table'] = $table;
		$viewData['database'] = $database;
		$viewData['connection'] = $connection;
		
		return View::fetch("Plex.DB_Model_Generator.mysqli.generatedInsert", $viewData);
	}
	
	public function generateUpdateSQL($connection, $database, $table) {
		
		$tableFieldNames = array();
		$tableFieldDefinitions=DB::query(constant($connection), "DESCRIBE `$database`.$table`")->fetch_all();
		
		$primaryKeyFieldNames = array();
		$primaryKeys = $this->getPrimaryKeys($connection, $database, $table);
		
		if(count($primaryKeys)==0) {
			return false;
		}
		
		foreach($tableFieldDefinitions as &$tableFieldDefinition) {
			$tableFieldNames[] = "`{$tableFieldDefinition['Field']}`=?";
			$tableFieldDefinition['Field_Type'] = self::getFieldType($tableFieldDefinition['Type']);
		}
		
		foreach($primaryKeys as &$primaryKey) {
			$primaryKeyFieldNames[] = "`{$primaryKey['Field']}`=?";
		}
		
		$fieldList = implode(',', $tableFieldNames);
		$keyFieldList = implode(',', $primaryKeyFieldNames);
		
		$viewData['columns'] = $tableFieldDefinitions;
		
		$viewData['primaryKeys'] = $primaryKeys;
		$viewData['query'] = "UPDATE `$table` SET $fieldList WHERE $keyFieldList";
		$viewData['table'] = $table;
		$viewData['database'] = $database;
		$viewData['connection'] = $connection;
		
		return View::fetch("Plex.DB_Model_Generator.mysqli.generatedUpdate", $viewData);
	}
	
	public function getPrimaryKeys($connection, $database, $table) {
		$primaryKeys = array();
		$tableFieldDefinitions=DB::query(constant($connection), "DESCRIBE `$database`.$table`")->fetch_all();
		
		foreach($tableFieldDefinitions as $tableFieldDefinition) {			
			if($tableFieldDefinition['Key']=='PRI') {
				$tableFieldDefinition['Field_Type'] = $this->getFieldType($tableFieldDefinition['Type']);
				$primaryKeys[] = $tableFieldDefinition;
			}
		}
		
		return $primaryKeys;
	}
	
		
	public function getFieldType($type) {
		if( strpos($type,'INT')!==false || strpos($type,'BOOL')!==false) {
			return 'i';
		} else if(strpos($type,'DOUBLE')!==false) {
			return 'd';				
		} else if(strpos($type,'BLOB')!==false) {
			return 'b';
		} else {
			return 's';
		}
	}
}