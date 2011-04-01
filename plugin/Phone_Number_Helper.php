<?php 
namespace Plex;

class Phone_Number_Helper {
	
	public $error = null;
	
	public function validate($input) {
		
	}
	
	static public function format($input) {
	        $input = ereg_replace("[^0-9]",'', $input);
	        if (strlen($input) != 10) 
	        {
	                return $input;
	        }
	        
	        $strArea = substr($input, 0, 3);
	        $strPrefix = substr($input, 3, 3);
	        $strNumber = substr($input, 6, 4);
	        
	        $strPhone = "(".$strArea.") ".$strPrefix."-".$strNumber;
	        
	        return ($strPhone);
	}
	
}