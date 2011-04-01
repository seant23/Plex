<?php
namespace Plex;

class DB_Model_Generator_pgsql implements inDB_Model_Generator {
	
	public static function indent($input, $tabs, $useSpaces = false) {
		$indent = str_pad("", $tabs, $useSpaces ? "    " : "\t");
		return $indent.str_replace("\n", "\n" .$indent, $input);
	}
	
	public function getTableFields($connection, $table) {
		
		$query = <<<SQL
SELECT 
	a.column_name,
    a.table_name,
    a.is_nullable,
    a.udt_name,
    a.character_maximum_length,
    a.column_default
FROM information_schema.columns a 
WHERE table_name='$table'
ORDER BY a.table_catalog, a.table_schema, a.table_name
SQL;
		
	
		$fields = DB::query(constant($connection), $query)->fetch_all();
		return $fields;
		
	}
	
	public function generateModel($connection, $database, $table, $modelName) {
		
		$primaryKeysInfos = $this->getPrimaryKeys($connection, $database, $table);		
		$primaryKeys = array();
		
		foreach($primaryKeysInfos as $primaryKeysInfo) {
			$primaryKeys[] = "'{$primaryKeysInfo['column_list']}'";
		}
		
		$tableFieldNames = array();
		$tableFieldDefinitions= $this->getTableFields($connection, $table);
		
		if(!count($tableFieldDefinitions)) {
			Error::show("Can't Find Table Info For $table", "Please check to make sure you spelled the tablename right and that you have permissions to this table");
		}
		
		foreach($tableFieldDefinitions as &$tableFieldDefinition) {
			$tableFieldNames[] = "{$tableFieldDefinition['column_name']}";
			$tableFieldDefinition['Field_Type'] = self::getFieldType($tableFieldDefinition['udt_name']);
		
			$QBType = "CONTAINS";
		
			if(strpos($tableFieldDefinition['udt_name'], 'int') !== false)  {
				//$ConradType = (strpos($column['Type'], 'tinyint') === false) ? "INT" : "BOOLEAN";
				$QBType = "EQUALS";
			} else if($tableFieldDefinition['column_name']=='email') {
				//$ConradType = "EMAIL";
			} else if($tableFieldDefinition['column_name']=='contact_phone') {
				//$ConradType = "PHONE";
			} else {
				//$ConradType = "STRING";
			}
			
			$viewData['qb'][] = array('key' => $tableFieldDefinition['column_name'], 'type'=>$QBType);
			
			
			//$v['conrad'][] = array('key' => $column['Field'], 'type'=>$ConradType);
			
		}
		
		$viewData['createProcedure'] = self::indent($this->generateInsertSQL($connection, $database, $table), 2);
		$viewData['updateProcedure'] = self::indent($this->generateUpdateSQL($connection, $database, $table), 2);
		$viewData['deleteProcedure'] = self::indent($this->generateDeleteSQL($connection, $database, $table), 2);
		$viewData['primaryKeys'] = implode(',', $primaryKeys);
		
		
		$viewData['table'] = $table;
		$viewData['database'] = $database;
		$viewData['connection'] = $connection;
		$viewData['className'] = $modelName;
		
		return View::fetch("Plex.DB_Model_Generator.pgsql.generatedDBModel", $viewData);
	}
	
	public function generateInsertSQL($connection, $database, $table) {
		
		$tableFieldNames = array();
		$tableFieldDefinitions= $this->getTableFields($connection, $table);
		
		foreach($tableFieldDefinitions as &$tableFieldDefinition) {
			$tableFieldNames[] = "{$tableFieldDefinition['column_name']}";
			$tableFieldDefinition['Field_Type'] = self::getFieldType($tableFieldDefinition['udt_name']);
		}
		
		$varibleHolders = implode(',', array_pad(array(), count($tableFieldNames), '?'));
		$fieldList = implode(',', $tableFieldNames);
		
		$viewData['columns'] = $tableFieldDefinitions;
		$viewData['query'] = "INSERT INTO $table ($fieldList) VALUES ($varibleHolders); ";
		$viewData['table'] = $table;
		$viewData['database'] = $database;
		$viewData['connection'] = $connection;
		
		return View::fetch("Plex.DB_Model_Generator.pgsql.generatedInsert", $viewData);
	}
	
	public function generateDeleteSQL($connection, $database, $table) {
		$primaryKeyFieldNames = array();
		$primaryKeys = $this->getPrimaryKeys($connection, $database, $table);
		
		if(count($primaryKeys)==0) {
			return false;
		}
		
		foreach($primaryKeys as &$primaryKey) {
			$keys = explode(',', $primaryKey['column_list']); 
			
			foreach ($keys as $key) {
				$primaryKeyFieldNames[] = "{$key}=?";
			}
		}
		
		$keyFieldList = implode(',', $primaryKeyFieldNames);
		
		$viewData['primaryKeys'] = $primaryKeys;
		$viewData['query'] = "DELETE FROM $table WHERE $keyFieldList";
		$viewData['table'] = $table;
		$viewData['database'] = $database;
		$viewData['connection'] = $connection;
		
		return View::fetch("Plex.DB_Model_Generator.pgsql.generatedDelete", $viewData);
		
		
	}
	
	public function generateUpdateSQL($connection, $database, $table) {
		
		$tableFieldNames = array();
		$tableFieldDefinitions= $this->getTableFields($connection, $table);
		
		$primaryKeyFieldNames = array();
		$primaryKeys = $this->getPrimaryKeys($connection, $database, $table);
		
		if(count($primaryKeys)==0) {
			return false;
		}
		
		
		foreach($tableFieldDefinitions as &$tableFieldDefinition) {
			$tableFieldNames[] = "{$tableFieldDefinition['column_name']}=?";
			$tableFieldDefinition['Field_Type'] = self::getFieldType($tableFieldDefinition['udt_name']);
		}
		
		foreach($primaryKeys as &$primaryKey) {
			$keys = explode(',', $primaryKey['column_list']); 
			
			foreach ($keys as $key) {
				$primaryKeyFieldNames[] = "{$key}=?";
			}
		}
		
		$fieldList = implode(',', $tableFieldNames);
		
		$keyFieldList = implode(',', $primaryKeyFieldNames);
		
		$viewData['columns'] = $tableFieldDefinitions;
		
		$viewData['primaryKeys'] = $primaryKeys;
		$viewData['query'] = "UPDATE $table SET $fieldList WHERE $keyFieldList";
		$viewData['table'] = $table;
		$viewData['database'] = $database;
		$viewData['connection'] = $connection;
		
		return View::fetch("Plex.DB_Model_Generator.pgsql.generatedUpdate", $viewData);
	}
	
	public function getPrimaryKeys($connection, $database, $table) {
		$query = <<<SQL
SELECT a.table_catalog, a.table_schema, a.table_name, 
       a.constraint_name, a.constraint_type, 
       array_to_string(
         array(
           SELECT column_name::varchar
           FROM information_schema.key_column_usage
           WHERE constraint_name = a.constraint_name
           ORDER BY ordinal_position
           ),
         ', '
         ) as column_list,
       c.table_name, c.column_name
FROM information_schema.table_constraints a 
INNER JOIN information_schema.key_column_usage b
ON a.constraint_name = b.constraint_name
LEFT JOIN information_schema.constraint_column_usage c 
ON a.constraint_name = c.constraint_name AND 
   a.constraint_type = 'FOREIGN KEY'
WHERE a.table_name = '$table'
AND a.table_catalog = '$database'
AND a.constraint_type = 'PRIMARY KEY'
GROUP BY a.table_catalog, a.table_schema, a.table_name, 
         a.constraint_name, a.constraint_type, 
         c.table_name, c.column_name
ORDER BY a.table_catalog, a.table_schema, a.table_name, 
         a.constraint_name	
SQL;

		$primaryKeys = DB::query(constant($connection), $query)->fetch_all();
		
		return $primaryKeys;
	}
	
		
	public function getFieldType($type) {
		if( strpos($type,'int4')!==false || strpos($type,'BOOL')!==false) {
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