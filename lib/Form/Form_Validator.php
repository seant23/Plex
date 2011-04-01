<?php

class Form_Validator
{
	public $conf = array();

	public function clearVals()
	{
		foreach($this->conf as $key=>$conf)
		{
			$this->conf[$key]['error']=false;
			$this->conf[$key]['value']=false;

			if(isset($conf['default']))
			$this->conf[$key]['value'] = $conf['default'];
		}
	}

	public function addVar($varName,$options=array())
	{
		$options['error']=false;
		$options['value']=false;
		$this->conf[$varName]=$options;
	}
	
	public function submitted() {
		
		foreach($this->conf as $key=>$conf) { 
			if(!isset($_REQUEST[$key])) {
				return false;
			}
			
		}
		
		return true;
	}

	public function valid($throw_errors=false) {
		foreach($this->conf as $key=>$conf) {
			
			if(isset($conf['default']))
			$this->conf[$key]['value'] = $conf['default'];

			if(isset($_REQUEST[$key]))
			$this->conf[$key]['value']=$_REQUEST[$key];
			
			if($throw_errors) {
				if(!isset($_REQUEST[$key])) {
					$this->conf[$key]['error']=self::error("Not Posted");
					continue;
				}

				if(isset($conf['null'])) {
					if($conf['null']&&empty($_REQUEST[$key]))
					continue;
				}

				if(isset($conf['min'])) {
					if($conf['min']>strlen($_REQUEST[$key])) {
						$this->conf[$key]['error']=self::error("Minimum {$conf['min']} Characters");
						continue;
					}
				}

				if(isset($conf['max'])) {
					if($conf['max']<strlen($_REQUEST[$key])) {
						$this->conf[$key]['error']=self::error("Maximum {$conf['max']} Characters");
						continue;
					}
				}


				if(isset($conf['no_zero'])) {
					if($_REQUEST[$key]=='0') {
						$this->conf[$key]['error']=self::error("Must Select One");
						continue;
					}
				}


				if(isset($conf['email'])) {
					if(!self::validate_email($key)) {
						$this->conf[$key]['error']=self::error("Invalid Email Address");
						continue;
					}
				}

				if(isset($conf['numOnly'])) {
					if(!is_numeric($_REQUEST[$key])) {
						$this->conf[$key]['error']=self::error("Numbers Only");
						continue;
					}
				}
			}
		}

		if($this->error_count()) {
			return false;
		} else {
			return true;
		}		
	}

	static function error($error) {
		return $error;
	}

	public function errors() {
		$errors = array();

		foreach ($this->conf as $key=>$conf) {
			$errors[$key]="<p style='color:orangered;text-align:center;'>{$conf['error']}</p>";
		}
	
		return $errors;
	}
 
	public function error_count() {
		$count = 0;
		
		foreach ($this->conf as $key=>$conf) {
			if($conf['error'])
			$count++;
		}
		
		return $count;
	}

	public function values() {
		$vals = array();
		
		foreach ($this->conf as $key=>$conf) {
			$vals[$key]=$conf['value'];		
		}
		
		return $vals;
	}

	function validate_email($email) {
		if(eregi("^[a-z0-9\._-]+@{1}([a-z0-9]{1}[a-z0-9-]*[a-z0-9]{1}\.{1})+([a-z]+\.){0,1}([a-z]+){1}$", $this->conf[$email]['value'])) {
			return true;
		} else {
			$this->conf[$email]['error'] = self::error("Invalid Email Address");
			return false;
		}	
	}

	function validate_passwords($first,$second) {
		
		if($this->conf[$first]['value']!=$this->conf[$second]['value']) {
			$this->conf[$second]['error'] = self::error("Passwords Must Match");
			return false;
		}
		
		return true;
	}



}