<?php


class Array_Util {
	
	public static function setIndex($array, $newIndex) {
	   $newArray = array();
	   foreach($array as $value) {
	       $newArray[$value[$newIndex]] = $value;
	   }
	   return $newArray;
	}
	
}