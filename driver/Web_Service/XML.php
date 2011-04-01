<?php
namespace Plex; 

class Web_Service_XML extends Web_Service implements inOutput {
	
	public $DOM = false;
	
	public function __construct() {
		$this->DOM = new \DOMDocument();
		Plex::setOutput($this);
		
		parent::__construct();
	}

	public function error($title=false, $description=null,  $errorInfo=false) {
		
		if($errorInfo) {
			$error = $this->DOM->createElement("error");
			$errorMessage = $this->DOM->createElement("message");
			$errorMessageText = $this->DOM->createTextNode($errorInfo['message']);
			$errorMessage->appendChild($errorMessageText);
			$error->appendChild($errorMessage);
			
			$errorFile = $this->DOM->createElement("file");
			$errorFileText = $this->DOM->createTextNode($errorInfo['file']);
			$errorFile->appendChild($errorFileText);
			$error->appendChild($errorFile);
			
			$errorLine = $this->DOM->createElement("line");
			$errorLineText = $this->DOM->createTextNode($errorInfo['line']);
			$errorLine->appendChild($errorLineText);
			$error->appendChild($errorLine);
			
			$errorLine = $this->DOM->createElement("type");
			$errorLineText = $this->DOM->createTextNode($errorInfo['severity']);
			$errorLine->appendChild($errorLineText);
			$error->appendChild($errorLine);
			
			
			$backtraceND = $this->DOM->createElement("backtrace");
			$error->appendChild($backtraceND);
			
			$backtrace = debug_backtrace();
			
			
			foreach($backtrace as $steptrace) {
				
				$stepND = $this->DOM->createElement("step");
				$backtraceND->appendChild($stepND);
				
				$errorLine = $this->DOM->createElement("file");
				$errorLineText = $this->DOM->createTextNode($steptrace['file']);
				$errorLine->appendChild($errorLineText);
				$stepND->appendChild($errorLine);
				
				$errorLine = $this->DOM->createElement("line");
				$errorLineText = $this->DOM->createTextNode($steptrace['line']);
				$errorLine->appendChild($errorLineText);
				$stepND->appendChild($errorLine);
				
				$errorLine = $this->DOM->createElement("function");
				$errorLineText = $this->DOM->createTextNode($steptrace['function']);
				$errorLine->appendChild($errorLineText);
				$stepND->appendChild($errorLine);
				
			}
			
			$this->respond($error);
		} else {
			$error = $this->DOM->createElement("error");
			$errorTitle = $this->DOM->createElement("title");
			$errorTitleText = $this->DOM->createTextNode($title);
			$errorTitle->appendChild($errorTitleText);
			$error->appendChild($errorTitle);
			
			$errorDescription = $this->DOM->createElement("description");
			$errorDescriptionText = $this->DOM->createTextNode($description);
			$errorDescription->appendChild($errorDescriptionText);
			$error->appendChild($errorDescription);
			
			$this->respond($error);
		}
	}
	
	private function packSearchResult($resultInfo) {
		$result = $this->DOM->createElement('result');
		
		foreach($resultInfo as $key=>$val) {
			$result->setAttribute($key,$val);
		}
		
		return $result;
	}
	
	function search($modelSearch) {
		
		$modelSearch->run();
		$results = $this->DOM->createElement('results');
		
		foreach($modelSearch->results as $key=>$result) {
			$results->appendChild($this->packSearchResult($result));
		}
		
		$info = $this->DOM->createElement("search");
		$info->setAttribute('count', $modelSearch->count);
		$info->setAttribute('per_page', $modelSearch->perPage);
		$info->setAttribute('current_page', $modelSearch->page);
		$info->setAttribute('page_count', $modelSearch->pageCount);
		$info->setAttribute('time', time());
		
		$responseNode = $this->DOM->createElement("response");
		$this->DOM->appendChild($responseNode);
		
		$responseNode->appendChild($results);
		$responseNode->appendChild($info);
		
		header('content-type: text/xml');
		echo $this->DOM->saveXML();
	}
	
	/**
	 * Pack Array Into XML
	 * 
	 * Optionally you can pass a Web_Service_Response with a useAttributes Setting...
	 *
	 * @param Array $array
	 * @param DOMElement $parentNode
	 * @param Boolean $useAttributes
	 */
	public function packArray($array, $parentNode, $useAttributes=false) {
		foreach($array as $key=>$val) {
			if($useAttributes) {
				$newNode = $this->DOM->createElement('value');
				foreach($val as $attributeName=>$attributeValue) {
					$newNode->setAttribute($attributeName, (String) $attributeValue);
				}
				$parentNode->appendChild($newNode);
			} else {
				$key = is_numeric($key) ? 'value' : $key;
			
				$responseNode = $this->DOM->createElement($key);
				
				if($val instanceof Web_Service_Response) {
					$useAttributesOnVal = $val->getSetting('useAttributes');
					$val = $val->value;
				} else {
					$useAttributesOnVal = false;
				}
				
				if(is_array($val)) {
					$val = $this->packArray($val, $responseNode, $useAttributesOnVal);
				} else {
					$responseValue = $this->DOM->createTextNode($val);
					$responseNode->appendChild($responseValue);
				}
				$parentNode->appendChild($responseNode);
			}
		}
	}

	public function respond($response=false) {
		
		if(is_bool($response)) {
			$response = (int) $response;
		}
		
		$responseNode = $this->DOM->createElement("response");
		$this->DOM->appendChild($responseNode);
		
		if($response instanceof Web_Service_Response) {
			$useAttributesOnVal = $response->getSetting('useAttributes');
			$response = $response->value;
		} else {
			$useAttributesOnVal = false;
		}
		
		if($response instanceof \DOMElement) {
			$responseNode->appendChild($response);
		} else if (is_array($response)) {
			$this->packArray($response,$responseNode, $useAttributesOnVal);
		} else {
			$responseTextNode = $this->DOM->createTextNode($response);
			$responseNode->appendChild($responseTextNode);
		}
		
		header('content-type: text/xml');
		echo $this->DOM->saveXML();
		exit;
	}
}
