<?php
namespace Plex;

class DB_Model_Result {
	public $results = array();
	public $index = 0;
	public $count = 0;
	
	function __construct($results) {
		$this->count = count($results);
		$this->results = $results;
	}
	
	function current() {
		if(isset($this->results[$this->index])) {
			return $this->results[$this->index];
		} else {
			return false;
		}		
	}
	
	function next() {
		$this->index++;
		return $this->current();
	}
	
	function previous() {
		$this->index--;
		return $this->current();
	}
	
	function item($index) {
		return $this->results[$index];
	}
}