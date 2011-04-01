<?php 
echo "<?php";
?>

namespace Plex;

/**
 * Model For <?=$database;?> / <?=$table;?> Table
 *
 * @author sthayne
 * @version 1.0
 * @package Model 
 *
 **/
 
class __<?=$className;?> extends DB_Model {
	/**
	 * DB Constant Used For Writing (Procedure Calls)
	 *
	 * @var Int
	 */
	public $DB_WRITE = <?=$connection;?>;
	/**
	 * DB Constant Used For Reading (Selects)
	 *
	 * @var Int
	 */
	public $DB_READ = <?=$connection;?>;
	/**
	 * DB Table Used For QB (Selects)
	 *
	 * @var String
	 */
	public $DB_TABLE = "<?=$table;?>";
	/**
	 * DB Table Primary Key
	 *
	 * @var String
	 */
	public $DB_KEY = array(<?=$primaryKeys;?>);
	
	/**
	 * Configurs Validation Configuration
	 *
	 * @return boolean
	 */
	public function __construct() {
		$this->Validator = new Array_Validator();
<? foreach($qb as $column):?>
		$this->Validator->addVar('<?=$column['key'];?>')->defaultValue(DB::_DEFAULT);
<? endforeach; ?>

		return true;
	}
    
	/**
	 * Configurs Query Builder
	 *
	 * @param QB_Condition $QB
	 * @return boolean
	 */
	public function configQB($QB) {	
<? foreach($qb as $column):?>
		$QB->addVar('<?=$column['key'];?>', QB::<?=$column['type'];?>);
<? endforeach; ?>

		return true;
	}
	
	/**
	 * Statically Calls Non-Static function get
	 *
	 * @param mixed $input
	 * @return DB_Model_Result
	 */
	public static function staticGet($input=null) {
		return parent::staticGet($input);
	}
	
	/**
	 * Statically Calls Non-Static function set
	 *
	 * @param mixed $id
	 * @param array $input
	 * @return mixed
	 */
	public static function staticSet($id, $input) {
		return parent::staticSet($id, $input);
	}
	
	/**
	 * Statically Calls Non-Static function create
	 *
	 * @param array $input
	 * @return mixed
	 */
	public static function staticCreate($input) {
		return parent::staticCreate($input);
	}
	
		
	public function delete($primaryKeyValues, $errorOut=false) {

<? if($deleteProcedure): ?>
<?=$deleteProcedure;?>
<? else: ?>
		//REPLACE WITH PROCEDURE CALL FOR DELETING
		return false;
<? endif; ?>
		
	}
	
		/**
	 * Updates <?=$table;?> Tables
	 *
	 * @param mixed $primaryKeyValues
	 * @param array $input
	 * @param boolean $errorOut
	 * @return mixed
	 */
	public function set($primaryKeyValues, $input, $errorOut=false) {		
		$input = $this->mergeInputs($input, $this->get($primaryKeyValues)->current());

<? if($updateProcedure): ?>
<?=$updateProcedure;?>
<? else: ?>
		//REPLACE WITH PROCEDURE CALL FOR UPDATING
		return false;
<? endif; ?>	
		
		return $input;
	}

	/**
	 * Create New Entry For <?=$table;?> Table
	 *
	 * @param array $input
	 * @param boolean $errorOut
	 * @return mixed
	 */
	public function create($input, $errorOut=false) {
		if(!$this->Validator->validate($input)) {
			/**
			 * Invalid Input, Probably Missing Required Variables...
			 */
			
			if($errorOut) {
				Error::show("Model Validation Error", "Users::create Input has not passed it's required validation, please check your input againt the Validator settings.");
			} else {
				return false;
			}
		}
		
<? if($createProcedure): ?>
<?=$createProcedure;?>
<? else: ?>
		//REPLACE WITH PROCEDURE CALL FOR CREATING
		return false;
<? endif; ?>
		
		$insertIDs = array();
		
<?  $keysString = substr($primaryKeys, 1, strlen($primaryKeys)-2);
	$keys = explode(',', $keysString);
	foreach($keys as $key) : ?>
		$insertIDs['<?=trim($key);?>'] = $statment->insert_id('<?=trim($table);?>_<?=trim($key);?>_seq');	// <?=$key;?>
		
<? endforeach; ?>

		return $insertIDs;
	}
}
