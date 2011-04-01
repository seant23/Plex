<?php

class P2X2P_Client extends P2X2P
{
	public $url = false;
	public $user = false;
	public $password = false;
	public $timeout = 30;
	public $faultCode = null;
	public $faultString = null;
	public $debug = false;

	function __construct($url=false,$user=false,$password=false)
	{
		$this->url = $url;
		$this->user = $user;
		$this->password = $password;
	}

	public function Run($data)
	{
		return $this->Send($this->url,$data,$this->user,$this->password,$this->timeout);
	}

	public function Report_Fault($code,$message=false)
	{

		if($this->debug)
		{
			if(!$message) $message=$this->error_info($code);
			echo "<br><b style='color:red'>P2X2P Error:</b> $code - $message<br>";
		}

		return false;
	}

	public function call_Function($methodName)
	{
		$methodCall = new nf_Element('methodCall');

		$methodName = $methodCall->createChild('methodName',$methodName);
		$params = $methodCall->createChild('params');

		foreach (array_slice(func_get_args(),1) AS $arg)
		{
			$param = $params->createChild('param');
			$param->appendChild($this->p2X_value($arg));
		}

		$response = $this->Run($methodCall->__toString());

		if($this->debug)
		echo htmlentities($response);

		if(empty($response))
		return $this->Report_Fault(400);

		$DOM = new DOMDocument;

//		die($response);
		if(!@$DOM->loadXML($response))
		return $this->Report_Fault(402);

		if(!@$DOM->schemaValidate(dirname(__FILE__).'/lib/xmlrpc.xsd'))
		return $this->Report_Fault(401);

		$method = $DOM->getElementsByTagName('methodResponse')->item(0);


		//Check For Fault
		if($method->getElementsByTagName('fault')->length>=1)
		{
			$fault_value_node = $method->getElementsByTagName('fault')->item(0)->firstChild;
			$fault_array = $this->X2p_value($fault_value_node);
			$this->faultCode=$fault_array['faultCode'];
			$this->faultString=$fault_array['faultString'];

			if($this->debug)
			$this->Report_Fault($this->faultCode,$this->faultString);

			return false;
		}

		$params = $method->getElementsByTagName('params')->item(0)->getElementsByTagName('param');
		$php_params = array();

		for ($i = 0; $i < $params->length; $i++)
		$php_params[] = $this->X2p_value($params->item($i)->firstChild);

		if(count($php_params==1))
		return $php_params[0];
		else
		return $php_params;
	}
}
