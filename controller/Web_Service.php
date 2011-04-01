<?php
namespace Plex;

class _Web_Service extends Controller {
	
	public function index() {
		
	}
	
	public function Exec($driverName=false, $feed=false) {
		if(Driver::exists('Web_Service', $driverName)) {
			$feedInfo = explode('.', $feed);
			
			$driver = Driver::createInstance('Web_Service', $driverName);
			$driver->run($feedInfo[0], $feedInfo[1]);
		} else {
			Error::show("Driver Not Installed", "The Driver $driverName is currently not installed!");	
		}
	}
}
