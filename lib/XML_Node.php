<?php

class XML_Node extends DOMElement {
	/**
	 * XML Node
	 *
	 * @var DOMElement
	 */
	public $node;
	
	/**
	 * XML Node
	 *
	 * @var XML
	 */
	public $DOM;
	
	public function __construct($DOM, $name, $value=null, $attributes=null) {
		$this->DOM = $DOM;
		parent::__construct($name);
		
		/**
		 * Hack Around Read Only Error
		 */
		$this->DOM->appendChild($this);
		$this->DOM->removeChild($this);
		/**
		 * End Of Hack
		 */
		
		if($value!=null) {
			$txtNode = $this->DOM->createTextNode($value);
			$this->appendChild($txtNode);
		}
		
		$this->setAttributes($attributes);
	}
	
	public function setAttributes($attributes=null) {
		if(is_array($attributes)) {
			foreach ($attributes as $k=>$v) {
				$this->setAttribute($k, $v);
			}
		}
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
		$child = $this->DOM->createElement($tagName, $value, $attributes);
		$this->appendChild($child);
		return $child;
	}
	
	public function appendChildren($children=null) {
		if(is_array($children)) {
			foreach ($children as $child) {
				$this->appendChild($child);
			}
		}
	}
	
	public function remove() {
		if($this->parentNode) {
			$this->parentNode->removeChild($this);
		}
	}
	
	public function injectInside($node) {
		if($node instanceof DOMElement) {
            $node->appendChild($this);
        } else {
        	Error::show("Unknown Type", "Can't Complete Action, Check XML Node Type");
        }
	}
}