<?php
namespace Plex;

class View
{
	public static $currentVars = array();
	public static $currentView = false;
	
	public static function display($fileName, $vars=array())
	{
		echo self::fetch($fileName, $vars);
	}
	
	public static function fetch($fileName, $vars=array())
	{
		
		ob_start();
		
		self::$currentVars = $vars;
		self::$currentView = $fileName;
		
		self::insert($fileName);
		
		$buffer = ob_get_contents();
		@ob_end_clean();
		
		Log::append("View", "Template ($fileName) Loaded");
		self::$currentVars = array();
		self::$currentView = false;
		
		return $buffer;
	}
	
	public static function insert($fileName)
	{
		$fileName=str_replace('.','/',$fileName).'.php';
		$__tplFile = Plex::$BASE_DIR . Config::get("Plex","view_dir") . "/" . $fileName;
		
		extract(self::$currentVars);
		if(!file_exists($__tplFile))
		{
			Error::show("View Error!","Template ($fileName) Does Not Exist",true);
			Log::append("View", "Unable To Find Template ($__tplFile)");
		}
		
		if ((bool) @ini_get('short_open_tag') === FALSE AND Config::get('rewrite_short_tags') == TRUE)
		echo eval('?>'.preg_replace("/;*\s*\?>/", "; ?>", str_replace('<?=', '<?php echo ', file_get_contents($__tplFile))).'<?php ');
		else
		include($__tplFile);
	}
}
