<?php

class PFE
{
	const CHECK = 'Check';	
	const SELECT = 'Select';	
	const INPUT = 'Input';	
	const HIDDEN = 'Hidden';	
	const TEXT = 'Text';	
	const RADIO = 'Radio';
	const INFO = 'Info';
	const HEADER = 'Header';
	const FILE = 'File';
	const DATE = 'Date';
	
	public $name = false;
	public $title = false;
	public $id = "theForm";
	public $engine = false;
	public $class = false;
	public $input = array();
	public $encType = false;
	public $blank = false;
	public $createHeader = true;
	public $createFooter = true;
	
	/**
	 * Validator
	 *
	 * @var Validate
	 */
	public $validator = false;
	public $method = 'POST';
		
	private $vars = array();
	
	public function __construct($name = false, $id = 'theForm', $engine = 'Default', $class = 'adminform')
	{
		$this->name = $name;
		$this->id = $id;
		$this->engine = $engine;
		$this->class = $class;
		
		$this->validator = new Validate();
	}
	
	public function newFile($title, $name)
	{
		$this->vars[] = array(
			'type'=>self::FILE,
			'title'=>$title,
			'name'=>$name
		);
		
		$this->validator->addVar($name,array('alias'=>$title));
		
		$this->encType="multipart/form-data";
		$this->method = "POST";
	}
	
	public function newCheck($title, $name, $options, $class=false)
	{
		$this->vars[] = array(
			'type'=>self::CHECK,
			'title'=>$title,
			'name'=>$name,
			'options'=>$options,
			'class'=>$class
		);
		
		$this->validator->addVar($name,array('alias'=>$title));
	}
	
	public function newSelect($title, $name, $options, $class=false)
	{
		$this->vars[] = array(
			'type'=>self::SELECT,
			'title'=>$title,
			'name'=>$name,
			'options'=>$options,
			'class'=>$class
		);
		
		$this->validator->addVar($name,array('alias'=>$title));
	}
	
	public function newDate($title, $name, $date, $time, $class=false)
	{
		$this->vars[] = array(
			'type'=>self::DATE,
			'title'=>$title,
			'name'=>$name,
			'class'=>$class,
			'date'=>$date,
			'time'=>$time,
		);
		
		$this->validator->addVar($name,array('alias'=>$title));
	}
	
	public function newHidden($name)
	{
		$this->vars[] = array(
			'type'=>self::HIDDEN,
			'name'=>$name,
		);
		
		$this->validator->addVar($name);
	}
	
	public function newInput($title, $name, $class=false)
	{
		$this->vars[] = array(
			'type'=>self::INPUT,
			'title'=>$title,
			'name'=>$name,
			'class'=>$class
		);
		
		$this->validator->addVar($name,array('alias'=>$title));
	}
	
	public function newText($title, $name, $class=false)
	{
		$this->vars[] = array(
			'type'=>self::TEXT,
			'title'=>$title,
			'name'=>$name,
			'class'=>$class
		);
		
		$this->validator->addVar($name,array('alias'=>$title));		
	}
	
	public function newRadio($title, $name, $options, $class=false)
	{
		$this->vars[] = array(
			'type'=>self::RADIO,
			'title'=>$title,
			'name'=>$name,
			'options'=>$options,
			'class'=>$class
		);
		
		$this->validator->addVar($name,array('alias'=>$title));		
	}
	
	public function newInfo($title)
	{
		$this->vars[] = array(
			'type'=>self::INFO,
			'title'=>$title,
			'name'=>null,
		);
	}
	
	public function newHeader($title)
	{
		$this->vars[] = array(
			'type'=>self::HEADER,
			'title'=>$title,
			'name'=>null,
		);
	}
	
	public function submitted()
	{
		if($this->method == "POST" && isset($_POST['__formSubmitted']))
		return true;
		
		if($this->method == "GET" && isset($_GET['__formSubmitted']))
		return true;
		
		if($this->method == "REQUEST" && isset($_REQUEST['__formSubmitted']))
		return true;
		
		return false;
	}
	
	public function valid()
	{
		if($this->method == "POST")
		return $this->validator->parse($_POST);
		
		if($this->method == "GET")
		return $this->validator->parse($_GET);
		
		if($this->method == "REQUEST")
		return $this->validator->parse($_REQUEST);
		
		return false;
	}
	
	public function clear()
	{
		$this->blank=true;
	}
	
	public function errorPage()
	{
		$view['errors'] = $this->validator->errors;
		View::display("PFE.{$this->engine}.Error",$view);
		exit;
	}
	
	public function values()
	{
		$values = $this->validator->values();
		
		foreach($this->vars as $var)
		{
			switch($var['type'])
			{
				case self::DATE:
					
					$second = isset($_REQUEST[$var['name']."_second"]) ? $_REQUEST[$var['name']."_second"] : 0;
					$minute = isset($_REQUEST[$var['name']."_minute"]) ? $_REQUEST[$var['name']."_minute"] : 0;
					$hour = isset($_REQUEST[$var['name']."_hour"]) ? $_REQUEST[$var['name']."_hour"] : 0;
					$day = isset($_REQUEST[$var['name']."_day"]) ? $_REQUEST[$var['name']."_day"] : 0;
					$month = isset($_REQUEST[$var['name']."_month"]) ? $_REQUEST[$var['name']."_month"] : 0;
					$year = isset($_REQUEST[$var['name']."_year"]) ? $_REQUEST[$var['name']."_year"] : 0;
					
					$values[$var['name']] = $time = mktime($hour, $minute, $second, $month, $day, $year);
					
					$this->method == "POST" ? $_POST[$var['name']] = $time : $_GET[$var['name']] = $time;
										
					break;
				default:
					break;
			}
		}
		
		return $values;
	}
	
	public function __toString()
	{
		$return = '';
		
	
		foreach($this->vars as $var)
		{
			if(!$this->blank)
			{
				if($this->method == "POST" && isset($_POST[$var['name']]))
				$var['selected'] = $var['value'] = $_POST[$var['name']];
				else if($this->method == "GET" && isset($_GET[$var['name']]))
				$var['selected'] = $var['value'] = $_GET[$var['name']];
				else		
				$var['selected'] = $var['value'] = (isset($this->input[$var['name']])) ? $this->input[$var['name']] : false;
			}
			else 
			$var['selected'] = $var['value'] = null;
			
			
			$var['error'] = (isset($this->validator->errors[$var['name']])) ? $this->validator->errors[$var['name']] : false;
			
			$return .= View::fetch("PFE.{$this->engine}.{$var['type']}",$var);
		}
		
		
		$formInfo = array(
		'name'=>$this->name,
		'title'=>$this->title,
		'id'=>$this->id,
		'method'=>$this->method,
		'class'=>$this->class,
		'encType'=>$this->encType,
		'vars'=>$return,
		'createHeader'=>$this->createHeader,
		'createFooter'=>$this->createFooter
		);
		
		
		$final = View::fetch("PFE.Default.Form",$formInfo);
		
		return $final;
	}
}