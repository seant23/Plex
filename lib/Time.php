<?php
namespace Plex;

class Time
{
	public $lastAction = 0;
	
	function __construct()
	{
		$this->lastAction = time();
	}
	
	function now($action)
	{
		$lastAction = time();
		$length = $lastAction - $this->lastAction;
		$this->lastAction = $lastAction;
		echo "\n$action Took $length Seconds";
	}
}