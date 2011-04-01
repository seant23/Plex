<?php
namespace Plex;

class Event_Handler {
	
	private static $callbacks = array();
	
	public static function dispatchEvent(Event &$event) {
		if(isset(self::$callbacks[$event->type])) {
			
			foreach (self::$callbacks[$event->type] as $callback) {
				
				call_user_func($callback, $event);
			}
		}
	}
	
	public static function addEventHandler($eventType, $callback) {
		self::$callbacks[$eventType][] = $callback;
	}	
}