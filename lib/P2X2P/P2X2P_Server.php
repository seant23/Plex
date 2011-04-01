<?php

$__P2X2P_SERVER = false;

class System
{
	static private function getServer()
	{
		global $__P2X2P_SERVER;
		if($__P2X2P_SERVER)
		return $__P2X2P_SERVER;
		return false;
	}

	/**
	 * Returns Array Containing A List Of All Classes, Their Functions, And The Required Parameter Count
	 *
	 * @return array
	 */
	static public function getCapabilities()
	{
		$Server = self::getServer();
		return $Server->classes;
	}
	
	/**
	 * Returns The Definition Of The Given Method
	 *
	 * @param string $methodName
	 * @return array
	 */
	static public function defineMethod($methodName)
	{
		$Server = self::getServer();
		$R = array();
		
		if($dot=strpos($methodName,'.'))
		{
			$className = substr($methodName,0,$dot);
			$methodName = substr($methodName,$dot+1);
		}
		else
		$className = GLOBAL_CLASS;
		
		if($className != GLOBAL_CLASS)
		{
			if(!class_exists($className))
			$Server->methodFault(500);

			if(!method_exists($className,$methodName))
			$Server->methodFault(501);

			$meth = new ReflectionMethod($className,$methodName);

			if(!$meth->isStatic() || !$meth->isPublic())
			$Server->methodFault(502);
			
			$R["class"] = $meth->getDeclaringClass()->getName();
			
		}
		else
		{
			if(!function_exists($method_name))
			$Server->methodFault(501);
			$meth = new ReflectionFunction($methodName);
			$className=GLOBAL_CLASS;
		}
		
		$R['user_defined'] = $meth->isUserDefined();
		$R["name"] =  $meth->getName();
		$R["doc"] =  $meth->getDocComment();
		$R["parameters_min"] =  $meth->getNumberOfRequiredParameters();
		$R["parameters_max"] =  $meth->getNumberOfParameters();
		
		return $R;
	}
}

class P2X2P_Server extends P2X2P
{
	public $users = array();
	public $private = false;
	public $classes = array();

	public function __construct($use_system=true,$private=false)
	{
		global $__P2X2P_SERVER;
		
		if($__P2X2P_SERVER)
		die("ONLY ONE RPC SERVER CAN EXIST");
		
		$__P2X2P_SERVER=$this;

		$this->private=$private;

		if($use_system)
		$this->addClass('System');
	}

	public function addUser($user,$pass,$host)
	{
		$host = str_replace('%','([0-9]{1})',$host);
		$this->users[$user]=array('P'=>$pass,'H'=>$host);
	}

	private function authenticate()
	{
		$U = @$_SERVER['PHP_AUTH_USER'];
		$P = @$_SERVER['PHP_AUTH_PW'];
		$H = $_SERVER['REMOTE_ADDR'];



		if(isset($this->users[$U]))
		{
			if($this->users[$U]['P']==$P)
			{
				if(ereg($this->users[$U]['H'],$H))
				return true;
				else
				$fault = $this->methodFault(152,"Permission Denined For User ($U) - Invalid Host ($H)");
			}
			else
			$fault = $this->methodFault(151,"Permission Denined For User ($U) - Incorrect Password");
		}
		else
		$fault = $this->methodFault(150,"Permission Denined For User ($U) - No Such User");

	}

	public function addClass($class)
	{
		if(!class_exists($class))
		return false;

		$class = new ReflectionClass($class);

		foreach($class->getMethods() as $method)
		{
			$this->addMethod($method->name,$method->class);
		}
	}

	public function addMethod($method_name,$class=null)
	{
		if($class)
		{
			if(!class_exists($class))
			return false;

			if(!method_exists($class,$method_name))
			return false;

			$meth = new ReflectionMethod($class,$method_name);

			if(!$meth->isStatic() || !$meth->isPublic())
			return false;
		}
		else
		{
			if(!function_exists($method_name))
			return(false);
			$meth = new ReflectionFunction($method_name);
			$class=GLOBAL_CLASS;
		}

		$meth_data['required']=$meth->getNumberOfRequiredParameters();
		$meth_data['total']=$meth->getNumberOfParameters();
		$this->classes[$class][$method_name]=$meth_data;

		return true;
	}

	public function methodFault($code,$string=false)
	{
		if(!$string)
		$string = $this->error($code);

		$fault_v = array('faultCode'=>$code,'faultString'=>$string);

		$methodResponse = new nf_Element('methodResponse');
		$fault = $methodResponse->createChild('fault');
		$value = $fault->createChild('value');
		$value->appendChild($this->p2X_struct($fault_v));

		$this->Respond($methodResponse);
	}

	private function parse_request()
	{
		if(!isset($GLOBALS["HTTP_RAW_POST_DATA"]))
		$this->methodFault(100);

		$DOM = new DOMDocument;

		if(!@$DOM->loadXML($GLOBALS["HTTP_RAW_POST_DATA"]))
		$this->methodFault(102);

		if(!@$DOM->schemaValidate(dirname(__FILE__).'/../driver/P2X2P/xmlrpc.xsd'))
		$this->methodFault(101);

		$method = $DOM->getElementsByTagName('methodCall')->item(0);
		$methodName = $method->getElementsByTagName('methodName')->item(0)->nodeValue;
		$params = $method->getElementsByTagName('params')->item(0)->getElementsByTagName('param');
		$php_params = array();

		for ($i = 0; $i < $params->length; $i++)
		$php_params[] = $this->X2p_value($params->item($i)->firstChild);

		if($dot=strpos($methodName,'.'))
		{
			$className = substr($methodName,0,$dot);
			$methodName = substr($methodName,$dot+1);
		}
		else
		$className = GLOBAL_CLASS;


		if(!isset($this->classes[$className][$methodName]))
		$this->methodFault(200);

		if(count($php_params)<$this->classes[$className][$methodName]['required'])
		$this->methodFault(201);

		if(count($php_params)>$this->classes[$className][$methodName]['total'])
		$this->methodFault(201);

		if($className==GLOBAL_CLASS)
		@$value=call_user_func_array($methodName,$php_params);
		else 
		@$value=call_user_func_array(array($className,$methodName),$php_params);

		$this->method_response($value);
	}

	private function method_response($return)
	{
		$methodResponse = new nf_Element('methodResponse');
		$params = $methodResponse->createChild('params');
		$param = $params->createChild('param');
		$param->appendChild($this->p2X_value($return));
		$this->Respond($methodResponse);
	}

	private function Respond($data)
	{
		die($data);
	}

	public function Start_Server()
	{
		if($this->private)
		$this->authenticate();

		$this->parse_request();


	}
}
