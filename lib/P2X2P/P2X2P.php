<?php


define("GLOBAL_CLASS",-1000);

class P2X2P
{
	public function Send($url,$data,$user=false,$password=false,$timeout=30)
	{
		$headers = array("Content-type: text/xml","Content-length: ".strlen($data));

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		//		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

		if($user)
		curl_setopt($ch, CURLOPT_USERPWD, "{$user}:{$password}");


		$response = curl_exec($ch);

		if(curl_errno($ch))
		Error::show("P2X2P Error", "CURL ERROR #" . curl_errno($ch) . " : " . curl_error($ch));
		
		curl_close($ch);

		return $response;
	}

	public function error($errorNumber)
	{
		
		$message = Config::get("P2X2P",$errorNumber);;
		Error::show("P2X2P Error", "#$errorNumber : $message");
	}

	// XML 2 PHP TOOLBOX
	public function X2p_value($value_node)
	{
		$value_kid = $value_node->firstChild;
		$type = $value_kid->nodeName;

		switch($type)
		{
			case 'string': case 'NULL': return (string) $value_kid->nodeValue; break;
			case 'boolean': return (boolean) $value_kid->nodeValue; break;
			case 'double': return (float) $value_kid->nodeValue; break;
			case 'int': case 'i4': return (int) $value_kid->nodeValue; break;
			case 'Base64': return unserialize(gzuncompress(base64_decode($value_kid->nodeValue))); break;
			case 'struct':return $this->X2p_struct($value_kid);break;
			default:$this->error(300);return false;break;
		}
	}

	public function X2p_struct($struct_node)
	{
		$return = array();
		$members = $struct_node->childNodes;

		for ($i = 0; $i < $members->length; $i++)
		{
			$name = $members->item($i)->getElementsByTagName('name')->item(0)->nodeValue;
			$value = $this->X2p_value($members->item($i)->getElementsByTagName('value')->item(0));
			$return[$name?$name:0] = $value;
		}

		return $return;
	}


	// PHP 2 XML TOOLBOX
	public function p2X_value($var)
	{
		
		$value = new nf_Element('value');

		if(!is_array($var))
		{
			$child_type = $this->p2X_type($var);
			$child_value = $this->p2X_enc_value($var);
			
			$value->createChild($child_type,$child_value);
		}
		else
		{
			$struct = $this->p2X_struct($var);
			$value->appendChild($struct);
		}

		return $value;
	}

	public function p2X_type($var)
	{
		$return = false;

		switch($type=gettype($var))
		{
			case 'string':case 'NULL':$return = 'string';break;
			case 'boolean':$return = 'boolean';break;
			case 'float':case 'double':$return = 'double';break;
			case 'integer':$return = 'int';break;
			case 'object':$return = 'Base64';break;
			case 'array':$return = 'array';break;
			default:$this->error(300);break;
		}

		return $return;
	}

	public function p2X_struct($var)
	{
		$struct = new nf_Element("struct");

		foreach($var as $key=>$val)
		{
			$member = $struct->createChild('member');
			$name = $member->createChild('name',$key);

			$value = $this->p2X_value($val);
			$member->appendChild($value);
		}

		return $struct;
	}

	public function p2X_enc_value($var)
	{
		switch($type=gettype($var))
		{
			case 'string':case 'NULL':break;
			case 'boolean':$var=$var?1:0;break;
			case 'float':case 'double':break;
			case 'integer':break;
			case 'object':$var=base64_encode(gzcompress(serialize($var)));	break;
			case 'array':return $this->p2xStruct($var);break;
			default:$this->p2xFault("Un Recognized Variable Type [$type]");return false;	break;
		}

		return $var;
	}

}