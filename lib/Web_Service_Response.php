<?php 
namespace Plex;


class Web_Service_Response {
	
	public $value = false;
	private $settings = array();
	
	/**
	 * Creates new Web_Service_Response Object
	 *
	 * @param mixed $value
	 * @return Web_Service_Response
	 */
	static public function createInstance($value) {
		return new Web_Service_Response($value);
	}
	
	public function __construct($value) {
		$this->value = $value;
	}
	
	/**
	 * Set's Possible Settings depending on Output Driver
	 *
	 * @param String $settingName
	 * @param Mixed $settingValue
	 * @return Web_Service_Response
	 */
	public function setSetting($settingName, $settingValue) {
		$this->settings[$settingName] = $settingValue;
		return $this;
	}
	
	public function getSetting($settingName, $default=false) {
		return isset($this->settings[$settingName]) ? $this->settings[$settingName] : $default;
	}
}