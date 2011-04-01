<?php

$nf_element_ID = 0;

class nf_Element
{
	public $name = false;
	public $child_nodes = array();
	public $id = false;
	public $attributes = array();

	function __construct($name,$value=false)
	{
		global $nf_element_ID;
		
		$this->name = $name;
		$this->id = $nf_element_ID++;
		
		if($value !== false)
		{
			$newChild = new nf_Text_Node($value);
			$this->child_nodes[$newChild->id]=$newChild;
		}
	}

	function appendChild($child)
	{
		$this->child_nodes[$child->id]=$child;
	}
	
	
	/**
	 * Create Child Node
	 *
	 * @return nf_Element
	 */
	function createChild($name,$value=false)
	{
		$newChild = new nf_Element($name,$value);
		$this->child_nodes[$newChild->id]=$newChild;
		return $newChild;
	}
	
	/**
	 * Create Child Node
	 *
	 * @return nf_Text_Node
	 */
	function createTextChild($value)
	{
		$newChild = new nf_Text_Node($value);
		$this->child_nodes[$newChild->id]=$newChild;
		return $newChild;
	}

	function removeChild($child)
	{
		unset($this->child_nodes[$child->id]);
	}

	function setAttribute($attribute,$value)
	{
		$this->attributes[$attribute]=$value;
	}
	
	function removeAttribute($attribute)
	{
		unset($this->attributes[$attribute]);
	}

	function __toString()
	{
		$Atts=null;
		if(count($this->attributes))
		foreach($this->attributes as $key=>$val)
		$Atts.=" $key=\"".htmlentities($val)."\"";

		$value = implode($this->child_nodes);
		return "<{$this->name}{$Atts}>$value</{$this->name}>";
	}
}

class nf_Text_Node
{
	public $value = false;
	public $id = false;

	function __construct($value)
	{
		$this->value=$value;
		$this->id = $GLOBALS['nf_element_ID']++;
	}

	function __toString()
	{
		return "<![CDATA[{$this->value}]]>";
	}
}