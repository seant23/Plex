<?php
namespace Plex;

DB::load();

abstract class DB_Model {
	
	const PRE_CREATE_EVENT = "DB_Model.PRE_CREATE_EVENT";
	const PRE_UPDATE_EVENT = "DB_Model.PRE_UPDATE_EVENT";
	
	
	
	
	// =============
	// = DB Config =
	// =============
    protected $DB_WRITE;
    protected $DB_READ;
    protected $DB_TABLE;
    protected $DB_PRIMARY_KEYS;

	// ======================
	// = Abstract Functions =
	// ======================
    abstract public function configQB($QB);
    abstract public function set($id, $input);
    abstract public function create($input);
    
    // ===========
    // = Publics =
    // ===========
    public $debugMode = false;
    
    /**
     * Query Builder
     *
     * @var QB
     */
    public $QB = false;
    
    /**
     * Array Validator
     *
     * @var Array_Validator
     */
    public $Validator = false;

    // =============================
    // = Checks For QB, Initialize =
    // =============================
    public function initQB() {
    	if(!$this->QB) {
    		$this->QB = new QB($this->DB_TABLE, $this->DB_READ);
    		
    		if($this->DB_PRIMARY_KEYS) {
    			
    			if(is_array($this->DB_PRIMARY_KEYS)) {
    				foreach($this->DB_PRIMARY_KEYS as $key) {
    					$this->QB->addVar($key, QB::EQUALS);
    				}
    			} else {
    				$this->QB->addVar($this->DB_PRIMARY_KEYS, QB::EQUALS);
    			}
    		}
    		
    		$this->configQB($this->QB);
    	}
    }
    
    public function error($msg) {
    	if($this->debugMode) {
    		echo $msg;
    	}
    	
    	return false;
    }
    
    // ================
    // = Merge Inputs =
    // ================
    public function mergeInputs($new, $old=false) {
    	
    	if(!$old) {
	    	$old = $this->Validator->getDefaultValues();
    	} 
    	
    	foreach($old as $key=>$val) {
    		$old[$key] = isset($new[$key]) ? $new[$key] : $old[$key];
    	}
    	
		return $old;
	}
	
	public function get($input, $limit=false, $offset=false) {
		
		$this->initQB();
		
		// ============
		// = QB Input =
		// ============
		if(is_array($input)) {
			$this->QB->source = $input;
		} else if(!is_array($this->DB_KEY)) {
			$this->QB->source = array($this->DB_KEY=>$input);
		} else {
			return $this->error("Invalid Input!");
		}
		
		// =============
		// = QB Output =
		// =============
		if(!$result = DB::query($this->DB_READ, $this->QB->query())->fetch_all()) {
			return new DB_Model_Result(array());
		} else {
			return new DB_Model_Result($result);
		}		
	}

	
	// ====================
	// = Static Functions =
	// ====================
	
	public static function staticGet($input=null) {
	    $bt = debug_backtrace();
    
		if(!isset($bt[1]['class'])) {
			die("Model Does Not Have staticGet() Call...");
		}
	    $className = $bt[1]['class'];
		$class = new $className;
		return $class->get($input);
	}
	
	public static function staticSet($id, $input) {
		$bt = debug_backtrace();
    
		if(!isset($bt[1]['class'])) {
			die("Model Does Not Have staticSet() Call...");
		}
	    $className = $bt[1]['class'];
		$class = new $className;
		
		return $class->set($id, $input);
	}
	
	public static function staticCreate($input) {
		$bt = debug_backtrace();
    
		if(!isset($bt[1]['class'])) {
			die("Model Does Not Have staticCreate() Call...");
		}
	    $className = $bt[1]['class'];
		$class = new $className;
		return $class->create($input);
	}
	
	public static function staticDelete($input) {
		$bt = debug_backtrace();
    
		if(!isset($bt[1]['class'])) {
			die("Model Does Not Have staticDelete() Call...");
		}
	    $className = $bt[1]['class'];
		$class = new $className;
		return $class->delete($input);
	}
	
	public static function timestamp(&$timestamp) {
		if(!is_int($timestamp)) {
			return false;
		} else {
			$timestamp = date("Y-m-d H:i:s",$timestamp);
			return true;
		}	
	}
	
	public function checkNull(&$var) {
		$var = (int) $var;
		
		if(!is_int($var)) {
			return false;
		} else {
			$var = $var ? $var : null;
			return true;
		}
	}
}
