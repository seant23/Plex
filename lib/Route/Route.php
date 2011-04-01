<?php
namespace Plex; 

/**
 * Plex Router - Parses Input Into Controller
 *
 * @author Sean_T23
 * @package Plex
 */
class Route
{
	/**
	 * Get Segments From URI
	 *
	 */
	public static function getSegments()
	{
		$URI = self::getURI();
		
		$segments = explode('/',$URI);
		
		if(empty($segments[0]))
		$segments = array_slice($segments,1);
		
		foreach($segments as $key=>$segment)
		{
			if(empty($segment))
			unset($segments[$key]);
		}
	
		$segments = array_slice($segments,1);
	
		return $segments;
	}
	
	public static function startRoute()
	{
		$custom = Config::getAll("Route");
		$segments = self::getSegments();
		
		
		if(count($custom)==0)
		$this->loadRoute($segments);
		
		// Turn the segment array into a URI string
		$uri = implode('/', $segments);
		$num = count($segments);
		
		
		if(isset($custom[$uri]))
		{
			self::loadRoute(explode('/', $custom[$uri]));
			return;
		}
				
		foreach (array_slice($custom, 0) as $key => $val)
		{						
			// Convert wild-cards to RegEx
			$key = str_replace('/', '\/', $key);
			$key = str_replace(':any', '.+', str_replace(':num', '[0-9]+', $key));

			// Does the RegEx match?
			if(preg_match('/^'.$key.'$/', $uri))
			{			
				
				// Do we have a back-reference?
				if(strpos($val, '$') !== FALSE AND strpos($key, '(') !== FALSE)
				$val = preg_replace('#^'.$key.'$#', $val, $uri);
				
				self::loadRoute(explode('/', $val));		
				return;
			}
			
		}
		
		self::loadRoute($segments);
	}
	
	public static function loadRoute($segments) {
		$controllerName = ucwords(isset($segments[0])?$segments[0]:Config::get("Plex", "main_controller"));
		$controllerFile = Plex::$BASE_DIR."controller/".$controllerName.".php";
				
		$methodName = !empty($segments[1])?$segments[1]:Config::get("Plex", "main_method");
		
		#print_r($segments);exit;

		if(!file_exists($controllerFile))
		{
			Log::append("Route", "Controller ($controllerName) - File Not Found");
			Error::show404();
		}
	
		require($controllerFile);
		
		$controllerName = "Plex\_$controllerName";
		
		if(!class_exists($controllerName))
		{
			Log::append("Route", "Controller ($controllerName) - Class Not Found");
			Error::show404();
		}
		
		if($methodName == 'controller' || substr($methodName, 0, 1) == '_' || in_array($methodName, get_class_methods('Plex\Controller'), TRUE))
		{
			Log::append("Route", "Controller ($controllerName) - Method ($methodName) Restricted");
			Error::show404();
		}
		
		$controller = new $controllerName();
		
		if(!method_exists($controller, $methodName))
		{
			Log::append("Route", "Controller ($controllerName) - Method ($methodName) Does Not Exist");
			Error::show404();
		}
		
		$segments = array_slice($segments,2);

		call_user_func_array(array($controller, $methodName), $segments);
	}
	
	public static function getURI() {
			
		$path = (isset($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : @getenv('REQUEST_URI');			
		if ($path != '' AND $path != "/".Plex::$BASE_URL) {
			$pos = strpos($path, '?');

			if($pos) {
				return substr($path, 0, $pos);
			} else {
				return $path;
			}

		}
		return $path;
						
		$path =  (isset($_SERVER['QUERY_STRING'])) ? $_SERVER['QUERY_STRING'] : @getenv('QUERY_STRING');	
		if ($path != '')
		return $path;
				
		$path = (isset($_SERVER['ORIG_PATH_INFO'])) ? $_SERVER['ORIG_PATH_INFO'] : @getenv('ORIG_PATH_INFO');	
		if ($path != '' AND $path != "/".Plex::$BASE_URL)
		return $path;
		
		return '';

	}
}
