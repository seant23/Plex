<?php
namespace Plex;

class Event {
	public $type;
	public $target;	
	public $data;

	public function __construct($type, $target, &$data) {
		$this->type = $type;
		$this->target = $target;
		$this->data = &$data;
	}
}