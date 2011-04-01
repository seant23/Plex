<?php
namespace Plex; 

class XML extends \DOMDocument {
	
	/**
	 * Creates New Element
	 *
	 * @param String $tagName
	 * @param String $value
	 * @param Array $attributes
	 * @return XML_Node
	 */
	public function createElement($tagName, $value=null, $attributes=null) {
		return new \XML_Node($this, $tagName, $value, $attributes);
	}
	
	/**
	 * Create new Node and Appends it to the current document
	 *
	 * @param String $tagName
	 * @param String $value
	 * @param Array $attributes
	 * @return XML_Node
	 */
	public function createChild($tagName, $value=null, $attributes=null) {
		$child = self::createElement($tagName, $value, $attributes);
		$this->appendChild($child);
		return $child;
	}
	
	/**
	 * Outputs Current XML Document and supplies Headers
	 *
	 */
	public function serve() {
		header("CONTENT-TYPE: text/xml");
		echo $this->saveXML();
	}
	
}