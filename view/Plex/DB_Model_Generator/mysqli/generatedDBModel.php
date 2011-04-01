<?php 
echo "<?php";
?>


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
	public $DB_PRIMARY_KEYS = array(<?=$primaryKeys;?>);
	/**
	 * Conrad Configuration (Validates Input)
	 *
	 * @var Array
	 */
	public $columns = array();
	
	/**
	 * Configurs Conrad Configuration
	 *
	 * @return boolean
	 */
	public function configDefaults() {
		if(!$this->init()) {
			return true;
		}		

<? foreach($qb as $column):?>
		$this->conrad->VC('<?=$column['key'];?>', null,Conrad::<?= $column['type'];?>);
<? endforeach;?>

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
	public static function staticGet($input) {
		return parent::staticGet(__CLASS__, $input);
	}
	
	/**
	 * Statically Calls Non-Static function set
	 *
	 * @param mixed $id
	 * @param array $input
	 * @return mixed
	 */
	public static function staticSet($id, $input) {
		return parent::staticSet(__CLASS__, $id, $input);
	}
	
	/**
	 * Statically Calls Non-Static function create
	 *
	 * @param array $input
	 * @return mixed
	 */
	public static function staticCreate($input) {
		return parent::staticCreate(__CLASS__, $input);
	}
	
	/**
	 * Updates <?=$table;?> Tables
	 *
	 * @param mixed $id
	 * @param array $input
	 * @param boolean $errorOut
	 * @return mixed
	 */
	public function set($id, $input, $errorOut=false) {		
		$input = $this->mergeInputs($input, $this->get($id)->current());

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
		$input = $this->mergeInputs($input);
		
<? if($createProcedure): ?>
<?=$createProcedure;?>
<? else: ?>
		//REPLACE WITH PROCEDURE CALL FOR CREATING
		return false;
<? endif; ?>
		
		
		return $statment->insert_id();
	}
}