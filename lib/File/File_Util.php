<?php
namespace Plex;

class File {
	public static function file_list($directory,$searchReg=false){ 
		foreach(array_diff(scandir($directory),array('.','..')) as $f) {
			if(is_file($directory.'/'.$f)&&(($searchReg)?@ereg($searchReg,$f):1))$l[]=$f; 
		}
		
		return $l; 
	} 
}