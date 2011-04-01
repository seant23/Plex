<?php 

class P2X2P_System
{
	static private function getServer() {		
		
		if(P2X2P_Server::$__P2X2P_Server)
		return P2X2P_Server::$__P2X2P_Server;
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
		$className = P2X2P_Server::GLOBAL_CLASS;
		
		if($className != P2X2P_Server::GLOBAL_CLASS)
		{
			if(!class_exists($className))
			$Server->method_fault(500);

			if(!method_exists($className,$methodName))
			$Server->method_fault(501);

			$meth = new ReflectionMethod($className,$methodName);

			if(!$meth->isStatic() || !$meth->isPublic())
			$Server->method_fault(502);
			
			$R["class"] = $meth->getDeclaringClass()->getName();
			
		}
		else
		{
			if(!function_exists($method_name))
			$Server->method_fault(501);
			$meth = new ReflectionFunction($methodName);
			$className=P2X2P_Server::GLOBAL_CLASS;
		}
		
		$R['user_defined'] = $meth->isUserDefined();
		$R["name"] =  $meth->getName();
		$R["doc"] =  $meth->getDocComment();
		$R["parameters_min"] =  $meth->getNumberOfRequiredParameters();
		$R["parameters_max"] =  $meth->getNumberOfParameters();
		
		return $R;
	}
}