<?php


class HTML
{
	
	public static function checkBoxes($name, $options=array(), $selected=array(), $seperator="<br>")
	{
		$resp = '';
		
		if(is_array(current($selected)))
		{
			$selectedArray = $selected;
			$selected = array();
			
			foreach($selectedArray as $key=>$val)
			{
				$selected[current($val)]=true;
			}
			
		}
		
		foreach($options as $value=>$text)
		{
			if(is_array($text))
			{
				$value=current($text);
				$text=next($text);
			}
			
			
			$isSelected = isset($selected[$value]) ? 'checked' : '';
			$resp .= "<label><input style='vertical-align:text-top' type=\"checkbox\" name=\"{$name}\" value='{$value}' $isSelected><span style='vertical-align:middle'>$text</span></label>$seperator";
		}
		
		return $resp;
	}
	
	public static function options($options, $selected)
	{		
		$resp = '';
		
		
		foreach($options as $value=>$text)
		{			
			if(is_array($text))
			{
				$value=current($text);
				$text=next($text);
			}
			
			
			$isSelected = $value==$selected ? 'selected' : '';
			$resp .= "<option value='{$value}' $isSelected>$text</option>";
		}
		
		return $resp;
	}	
}