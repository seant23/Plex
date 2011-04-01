<?php

/**
 * This class is used to serialize php variables into bencoded varibles which is
 * used for bittorrent metafiles, and tracker transmission.
 */

class Bencoding_Util {

	//Encode Functions
	static public function encode($mixed) {
		$type = gettype($mixed);

		switch ($type) {
			case is_null($mixed):
				$r = self::encode_string('');
				break;
			case 'string':
				$r = self::encode_string($mixed);
				break;
			case 'integer':
			case 'double':
				$r =  self::encode_int(round($mixed));
				break;
			case 'array':
				$r = self::encode_array($mixed);
				break;
			case 'boolean':
				$r = self::encode_string($mixed);
				break;
			default:
				Error::show("Unsupported Varible Type", "This Variable Type ($type) Is Not Supported By Bencoding");
		}
				
		return $r;
	}

	static public function encode_string($str) {
		return strlen($str) . ':' . $str;
	}

	static public function encode_int($int) {
		return 'i' . $int . 'e';
	}

	static public function encode_array($array) {
		// Check for strings in the keys
		$isList = true;
		foreach (array_keys($array) as $key) {
			if (!is_int($key)) {
				$isList = false;
				break;
			}
		}

		if ($isList) { 
			// We build a list
			ksort($array, SORT_NUMERIC);
			$return = 'l';
			foreach ($array as $val) {
				$return .= self::encode($val);
			}

			$return .= 'e';
		}
		else {
			ksort($array, SORT_STRING);
			$return = 'd';
			foreach ($array as $key => $val) {
				$return .= self::encode(strval($key));
				$return .= self::encode($val);
			}
			$return .= 'e';
		}
		return $return;
	}

	static public function decode($bdata,&$position=0) {
		while($position < strlen($bdata)) {
			switch ($bdata{$position}) {
				case 'i': //INT
					$end = strpos($bdata, 'e', $position);
					$return = (int) substr($bdata ,$position+1, $end - $position);
					$position = $end+1;
					return $return;
				break;

				case 'l': //LIST
					$position++;
					$return_value = array();
					while($bdata{$position}!='e')
					$return_value[] = self::decode($bdata,$position);
					$position++;
					return $return_value;
				break;

				case 'd': //Dictionary
					$position++;
					$return_value = array();
					while($bdata{$position}!='e') {
						$key = self::decode($bdata,$position);
						$val = self::decode($bdata,$position);
						$return_value[$key] = $val;
					}
					$position++;
					return $return_value;
				break;


				default: //String
					$col = strpos($bdata, ':', $position);
					$len = (int) substr($bdata, $position, $col);
					$return = substr($bdata, $col + 1, $len);
					$position = $col+$len+1;
				return $return;
			}
		}
	}
}

