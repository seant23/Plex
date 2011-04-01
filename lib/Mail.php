<?php
namespace Plex;

class Mail_Attachment {
	public $type = Mail::ATTACH_NORMAL;
	public $cid = false;
	public $fileName = false;
	public $filePath = false;
	public $fileContents = false;
	public $fileEncoding = Mail::ENC_BASE64;
	public $mimeType = "application/octet-stream";
}

class Mail {
	/**
	 * Message Specs
	 *
	 */
	public $priority = self::PRI_NORM ;
	public $charset = 'iso-8859-1';
	public $contentType = self::CONTENT_TEXT ;
	public $encoding = self::ENC_8BIT;
	public $errorInfo = null;

	/**
	 * Addresses
	 *
	 */
	public $fromEmail = false;
	public $fromAlias = false;
	public $returnPath = false;
	public $notificationEmail = false;

	public $addresses = array();
	public $ccAddresses = array();
	public $bccAddresses = array();
	public $replyAddresses = array();
	public $attachments = array();
	public $xHeaders = array();

	/**
	 * Local Server Settings 
	 *
	 */
	public $hostName = false;
	public $sendMailBin = "/usr/sbin/sendmail";

	/**
	 * SMTP Server Settings
	 */
	public $smtpHost = false;
	public $smtpPort = 587;
	public $smtpHelo = false;
	public $smtpAuthenticate = false;
	public $smtpUsername = false;
	public $smtpPassword = false;
	public $smtpTimeout = 10;
	public $smtpPersitent = false;
	public $eol = "\n";


	/**
	 * Message Contents
	 *
	 */
	public $messageSubject = null;
	public $messageBody = null;
	public $messageTxtBody = null;

	/**
	 * Plex Mailer Variables/Constants
	 *
	 */
	public $version = '1.0';
	public $error = false;
	public $header = null;
	public $body = null;
	public $type = self::TYPE_PLAIN;
	public $boundary = array();

	/**
	 * @var _SMTP
	 */
	public $smtp = false;

	const ENC_BASE64 = 'base64';
	const ENC_7BIT = '7bit';
	const ENC_8BIT = '8bit';
	const ENC_BIN = 'binary';
	const ENC_QP = 'quoted-printable';

	const CONTENT_HTML = "text/html";
	const CONTENT_TEXT = "text/plain";
	const CONTENT_MULTI = "multipart/alternative";

	const TYPE_PLAIN = "plain";
	const TYPE_ATTACHMENTS = "attachments";
	const TYPE_ALT = "alt";
	const TYPE_ALT_ATTACHMENTS = "alt_attachments";

	const ATTACH_INLINE = 'inline';
	const ATTACH_OUTLINE = -1;
	const ATTACH_NORMAL = 'attachment';

	const PRI_HIGH = 1;
	const PRI_NORM = 3;
	const PRI_LOW = 5;

	const HEAD_TEXT = 0;
	const HEAD_COMMENT = -1;
	const HEAD_PHRASE = -2;

	public function __construct($smtpHost=false,$smtpUser=false,$smtpPass=false)
	{
		$this->smtpHost=$smtpHost;
		$this->smtpUsername=$smtpUser;

		if($smtpPass)
		{
			$this->smtpPassword=$smtpPass;
			$this->smtpAuthenticate=true;
		}
	}

	public function loadAccount($accountName)
	{
		global $jackRabbit;

		$this->smtpHost=$jackRabbit[$accountName]['host'];
		$this->smtpUsername=$jackRabbit[$accountName]['user'];

		if(isset($jackRabbit[$accountName]['password']))
		{
			$this->smtpPassword=$jackRabbit[$accountName]['password'];
			$this->smtpAuthenticate=true;
		}


		$this->fromEmail=$jackRabbit[$accountName]['email'];

		if(isset($jackRabbit[$accountName]['alias']))
		$this->fromAlias=$jackRabbit[$accountName]['alias'];

		if(isset($jackRabbit[$accountName]['replyTo']))
		$this->addReply($jackRabbit[$accountName]['replyTo']);

	}
	
	public function resetRecipients() {
		$this->addresses = 
		$this->ccAddresses = 
		$this->bccAddresses = 
		$this->replyAddresses =
		array();
	}

	public function addRecipient($address,$alias = false)
	{
		return array_push($this->addresses,array($address,$alias?$alias:$address));
	}

	public function addCC($address,$alias = false)
	{
		return array_push($this->ccAddresses,array($address,$alias?$alias:$address));
	}

	public function addBCC($address,$alias = false)
	{
		return array_push($this->bccAddresses,array($address,$alias?$alias:$address));
	}

	public function addReply($address,$alias = false)
	{
		return array_push($this->replyAddresses,array($address,$alias?$alias:$address));
	}

	public function error($error)
	{
		$this->error=$error;
		return false;
	}

	function addDispositionNotificationTo($value){
		if($value)
			$this->header.="Disposition-Notification-To: $value{$this->eol}";
		else
		return false;
	}

	function addHeader($name, $value=false)
	{
		if($value)
			$this->header.="$name: $value{$this->eol}";
		else
		return false;
	}

	function packRecipients($recipients)
	{
		if(!count($recipients))
		return false;


		$packed = array();

		foreach($recipients as $recipient)
		{
			if($recipient[1]) //alias
			$packed[]=$this->encodeHeader($recipient[1], 'phrase') . " <{$recipient[0]}>";
			else
			$packed[]=$recipient[0];
		}

		return implode(', ',$packed);
	}

	public function headers()
	{
		$id = md5(uniqid(time()));
		$this->boundary[1] = "b1_" . $id;
		$this->boundary[2] = "b2_" . $id;


		$this->addHeader("Date",date('r'));
		$this->addHeader("Return-Path",$this->returnPath?$this->returnPath:$this->fromEmail);

		$this->addHeader("To",$toRec = $this->packRecipients($this->addresses));
		$ccrec = $this->addHeader("Cc",$ccRec = $this->packRecipients($this->ccAddresses));

		if(!$toRec && !$ccRec)
		$this->addHeader('To',"undisclosed-recipients:;");

		$this->addHeader("From",$this->packRecipients(array(array($this->fromEmail,$this->fromAlias))));
		$this->addHeader("Reply-to",$this->packRecipients($this->replyAddresses));
		$this->addHeader("Subject",$this->encodeHeader(trim($this->messageSubject)));
		$this->addHeader("Message-ID","<$id@{$_SERVER['SERVER_NAME']}>");
		$this->addHeader("X-Priority",$this->priority);
		$this->addHeader("X-Mailer","Plex Mailer [version {$this->version}]");

		if($this->notificationEmail)
		$this->addHeader("Disposition-Notification-To","<".$this->notificationEmail.">");

		foreach($this->xHeaders as $k=>$v)
		$this->addHeader($k,$v);

		$this->addHeader("MIME-Version","1.0");

		switch($this->type)
		{
			case self::TYPE_PLAIN:
				$this->addHeader("Content-Transfer-Encoding",$this->encoding);
				$this->addHeader("Content-Type","$this->contentType;{$this->eol}\tcharset=\"{$this->charset}\"");
				break;
			case self::TYPE_ATTACHMENTS:
			case self::TYPE_ALT_ATTACHMENTS:
				if($this->inlineCount())
				$this->addHeader("Content-Type","multipart/related;{$this->eol}\ttype=\"text/html\";{$this->eol}\tboundary=\"{$this->boundary[1]}\"");
				else
				$this->addHeader("Content-Type","multipart/mixed;{$this->eol}\tboundary=\"{$this->boundary[1]}\"");
				break;
			case self::TYPE_ALT:
				$this->addHeader("Content-Type","multipart/alternative;{$this->eol}\tboundary=\"{$this->boundary[1]}\"");
				break;
		}

		$this->header.=$this->eol;
		$this->header.=$this->eol;

		return $this->header;
	}

	function encodeString ($val, $enc = self::ENC_BASE64)
	{
		switch(strtolower($enc))
		{
			case self::ENC_BASE64:
				$return = chunk_split(base64_encode($val), 76, $this->eol);
				break;
			case self::ENC_7BIT:
			case self::ENC_8BIT:
				$return = $this->fixEol($val);
				if (substr($return, -(strlen($this->eol))) != $this->eol)
				$return .= $this->eol;
				break;
			case self::ENC_BIN:
				$return = $val;
				break;
			case self::ENC_QP:
				$return = $this->EncodeQP($val);
				break;
			default:
				$return = $this->error("Uknown Encoding ($enc)");
				break;
		}

		return $return;
	}


	function startBoundary($start, $charset=false, $contentType=false, $encoding=false)
	{
		$charset = $charset ? $charset : $this->charset;
		$contentType = $contentType ? $contentType : $this->contentType;
		$encoding = $encoding ? $encoding : $this->encoding;

		$return  = "--$start{$this->eol}";
		$return .= "Content-Type: $contentType;{$this->eol}\tcharset = \"$charset\"{$this->eol}";
		$return .= "Content-Transfer-Encoding: $encoding{$this->eol}";

		return $return;
	}

	function closeBoundary($boundary)
	{
		return $this->eol . "--" . $boundary . "--" . $this->eol;
	}

	public function body()
	{
		switch($this->type)
		{
			case self::TYPE_ALT:
				$this->body .= $this->startBoundary($this->boundary[1],false,"text/plain",false);
				$this->body .= $this->eol;
				$this->body .= $this->encodeString($this->messageTxtBody,$this->encoding);
				$this->body .= $this->eol.$this->eol;
				$this->body .= $this->startBoundary($this->boundary[1],false,"text/html",false);
				$this->body .= $this->eol;
				$this->body .= $this->encodeString($this->messageBody,$this->encoding);
				$this->body .= $this->eol.$this->eol;
				$this->body .= $this->closeBoundary($this->boundary[1]);
				break;
			case self::TYPE_PLAIN:
				$this->body .= $this->encodeString($this->messageTxtBody, $this->encoding);
				break;
			case self::TYPE_ATTACHMENTS:
				$this->body .= $this->startBoundary($this->boundary[1]);
				$this->body .= $this->encodeString($this->messageBody, $this->encoding);
				$this->body .= $this->eol;
				$this->body .= $this->packAttachments();
				break;
			case self::TYPE_ALT_ATTACHMENTS:
				$this->body .= "--{$this->boundary[1]}{$this->eol}";
				$this->body .= "Content-Type: multipart/alternative;{$this->eol}\tboundary=\"{$this->boundary[2]}\"{$this->eol}{$this->eol}";
				$this->body .= $this->startBoundary($this->boundary[2],false,'text/plain',false) . $this->eol . $this->eol;
				$this->body .= $this->encodeString($this->messageTxtBody,$this->encoding) . $this->eol . $this->eol;
				$this->body .= $this->startBoundary($this->boundary[2],false,'text/html',false) . $this->eol . $this->eol;
				$this->body .= $this->encodeString($this->messageBody,$this->encoding) . $this->eol . $this->eol;
				$this->body .= $this->closeBoundary($this->boundary[2]);
				$this->body .= $this->packAttachments();
				break;
		}

		return $this->body;
	}

	public function send()
	{
		$this->body = false;
		$this->header = false;

		if($this->messageBody)
		$this->contentType=self::CONTENT_MULTI ;

		if(count($this->attachments) && $this->messageBody)
		$this->type = self::TYPE_ALT_ATTACHMENTS;
		else if(count($this->attachments))
		$this->type = self::TYPE_ATTACHMENTS;
		else if($this->messageBody)
		$this->type = self::TYPE_ALT;
		else
		$this->type = self::TYPE_PLAIN;

		if(!count($this->addresses) && !count($this->ccAddresses) && !count($this->bccAddresses))
		return $this->error("No Recipients");

		$header = $this->headers();
		$body = $this->body();

		if(!$this->error)
		return $this->smtpSend($header, $body);
	}


	function smtpConnect()
	{
		list($host, $port) = strpos($this->smtpHost,':') ? explode(":", $hosts[$index]) : $addr = array($this->smtpHost,$this->smtpPort);
		
		if(!$this->smtp instanceof SMTP)
		$this->smtp = new SMTP($host, $port, $this->smtpTimeout);

		if($this->smtp->connect($this->smtpPersitent))
		{
			$this->smtp->helo($this->smtpHelo);

			if($this->smtpAuthenticate)
			{
				if(!$this->smtp->auth($this->smtpUsername,$this->smtpPassword))
				{
					$this->error("Unabled To Authenticate");
					$this->smtp->Reset();
					return false;
				}
			}
		}

		return true;
	}

	function smtpSend($header, $body)
	{
		if(!$this->smtpConnect())
		return false;

		$errorRecipients = array();

		$smtpFrom = $this->returnPath ? $this->returnPath : $this->fromEmail;

		if(!$this->smtp->mail($smtpFrom))
		{
			$this->smtp->rset();
			return $this->error("Error Sending From $smtpFrom");;
		}

		//Recipients
		foreach($this->addresses as $address)
		{
			if(!$this->smtp->rcpt($address[0]))
			$errorRecipients[]=$address[0];
		}

		//CC Recipients
		foreach($this->ccAddresses as $address)
		{
			if(!$this->smtp->rcpt($address[0]))
			$errorRecipients[]=$address[0];
		}

		//BCC Recipients
		foreach($this->bccAddresses as $address)
		{
			if(!$this->smtp->rcpt($address[0]))
			$errorRecipients[]=$address[0];
		}

		if(count($errorRecipients))
		return $this->error("Error Sending Email To Recipients (".implode(', '.$errorRecipients).")");

		if(!$this->smtp->data($header . $body))
		{
			$this->smtp->rset();
			return $this->error("Data Was Not Accepted");
		}

		$this->smtpPersitent ? $this->smtp->rset() : $this->smtpClose();

		return true;
	}

	function smtpClose()
	{
		if($this->smtp != NULL)
		{
			if($this->smtp->socket)
			{
				$this->smtp->quit();
				$this->smtp->disconnect();
			}
		}
	}
	
	public function addAttachment($filePath=false, $fileName = false, $fileEncoding = self::ENC_BASE64, $mimeType = "application/octet-stream") {
		$newAttachment = new Mail_Attachment();
		
		$mimeType = $this->getMime($fileName);

		$newAttachment->filePath = $filePath;
		$newAttachment->fileName = $fileName;
		$newAttachment->fileEncoding = $fileEncoding;
		$newAttachment->mimeType = $mimeType;
		
		$this->attachments[] = $newAttachment;
		return $newAttachment;
	}
	
	private function getMime($fileName){
		$mime = array(
			'xml' => 'text/xml',
			'avi' => 'video/x-msvideo',
			'zip' => 'application/x-zip-compressed',
			'mp2' => 'video/mpeg',
			'mpe' => 'video/mpeg',
			'mpeg' => 'video/mpeg',
			'mpg' => 'video/mpeg',
			'mov' => 'video/quicktime',
			'qt' => 'video/quicktime',
			'wmv' => 'video/x-ms-wmv',
			'wmx' => 'video/x-ms-wmx',
			'gif' => 'image/gif',
			'ief' => 'image/ief',
			'jpe' => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'jpg' => 'image/jpeg',
			'png' => 'image/png',
			'tiff' => 'image/tiff',
			'tif' => 'image/tiff',
			'pdf' => 'application/pdf',
			'doc' => 'application/msword',
			'xls' => 'application/excel',
			'pot' => 'application/vnd.ms-powerpoint',
			'pps' => 'application/vnd.ms-powerpoint',
			'ppt' => 'application/vnd.ms-powerpoint',
			'ppz' => 'application/vnd.ms-powerpoint',
			'xlc' => 'application/vnd.ms-excel',
			'xll' => 'application/vnd.ms-excel',
			'xlm' => 'application/vnd.ms-excel',
			'xls' => 'application/vnd.ms-excel',
			'xlw' => 'application/vnd.ms-excel',
			'htm' => 'text/html',
			'html' => 'text/html',
		);
		
		$chunk = explode(".",$fileName);
		$len = count($chunk) - 1;
		if ($len <= 0)
			return 'application/octet-stream';
		return $mime[$chunk[$len]];
	}

	private function packAttachments() {
		$return = null;

		foreach($this->attachments as $attachment) {
			
//			list($fileContent,$filePath,$fileName,$fileEncoding, $mimeType, $isString, $attachmentType, $cId) = $attachment;
			$return .= "--{$this->boundary[1]}{$this->eol}";
			$return .= "Content-Type: ".$attachment->mimeType."; name=\"".$attachment->fileName."\"{$this->eol}";
			$return .= "Content-Transfer-Encoding: ".$attachment->fileEncoding." {$this->eol}";

			if($attachment->type == self::ATTACH_INLINE) {
				$return .= "Content-ID: <".$attachment->cid.">{$this->eol}";
			}
			
			$return .= "Content-Disposition: ".$attachment->type."; filename=\"".$attachment->fileName."\"{$this->eol}{$this->eol}";

			if($attachment->fileContents) {
				$return .= $this->encodeString($attachment->fileContents) . $this->eol . $this->eol;
			} else {
				$return .= $this->encodeFile($attachment->filePath, $attachment->fileEncoding) . $this->eol . $this->eol;
			}
		}

		$return .= "--{$this->boundary[1]}--{$this->eol}";
		return $return;
	}

	function encodeFile ($filePath, $fileEncoding = self::ENC_BASE64) {
		return $this->encodeString(file_get_contents($filePath),$fileEncoding);
	}

	function encodeHeader ($str, $type = self::HEAD_TEXT)
	{
		$x = 0;

		switch ($type)
		{
			case self::HEAD_PHRASE:
				if (!preg_match('/[\200-\377]/', $str))
				{
					$encoded = addcslashes($str, "\0..\37\177\\\"");
					if (($str == $encoded) && !preg_match('/[^A-Za-z0-9!#$%&\'*+\/=?^_`{|}~ -]/', $str))
					return ($encoded);
					else
					return ("\"$encoded\"");
				}
				$x = preg_match_all('/[^\040\041\043-\133\135-\176]/', $str, $matches);
				break;
			case self::HEAD_COMMENT :
				$x = preg_match_all('/[()"]/', $str, $matches);
			case self::HEAD_TEXT :
			default:
				$x += preg_match_all('/[\000-\010\013\014\016-\037\177-\377]/', $str, $matches);
				break;
		}

		if ($x == 0)
		return ($str);

		$maxlen = 75 - 7 - strlen($this->CharSet);

		if (strlen($str)/3 < $x)
		{
			$encoding = 'B';
			$encoded = base64_encode($str);
			$maxlen -= $maxlen % 4;
			$encoded = trim(chunk_split($encoded, $maxlen, "\n"));
		}
		else
		{
			$encoding = 'Q';
			$encoded = $this->EncodeQ($str, $type);
			$encoded = $this->WrapText($encoded, $maxlen, true);
			$encoded = str_replace("=".$this->eol, "\n", trim($encoded));
		}

		$encoded = preg_replace('/^(.*)$/m', " =?".$this->charset."?$encoding?\\1?=", $encoded);
		$encoded = trim(str_replace("\n", $this->eol, $encoded));

		return $encoded;
	}


	function EncodeQP ($str)
	{
		$encoded = $this->fixEol($str);
		if (substr($encoded, -(strlen($this->eol))) != $this->eol)
		$encoded .= $this->eol;

		$encoded = preg_replace('/([\000-\010\013\014\016-\037\075\177-\377])/e',"'='.sprintf('%02X', ord('\\1'))", $encoded);
		$encoded = preg_replace("/([\011\040])".$this->eol."/e","'='.sprintf('%02X', ord('\\1')).'".$this->eol."'", $encoded);

		return $this->WrapText($encoded, 74, true);
	}

	function EncodeQ ($str, $type = self::HEAD_TEXT)
	{
		// There should not be any EOL in the string
		$encoded = preg_replace("[\r\n]", "", $str);

		switch ($type) {
			case self::HEAD_PHRASE :
				$encoded = preg_replace("/([^A-Za-z0-9!*+\/ -])/e", "'='.sprintf('%02X', ord('\\1'))", $encoded);
				break;
			case self::HEAD_COMMENT :
				$encoded = preg_replace("/([\(\)\"])/e", "'='.sprintf('%02X', ord('\\1'))", $encoded);
			case self::HEAD_TEXT :
			default:
				$encoded = preg_replace('/([\000-\011\013\014\016-\037\075\077\137\177-\377])/e',"'='.sprintf('%02X', ord('\\1'))", $encoded);
				break;
		}

		return str_replace(" ", "_", $encoded);;
	}

	function inlineCount()
	{
		$i = 0;
		foreach($this->attachments as $attachment) { 	
			if(count($attachment)) {
				$i += $attachment->type == self::ATTACH_INLINE  ? 1 : 0;
			}
		}		
		return $i;
	}

	function fixEol($str)
	{
		return str_replace("\n", $this->eol, str_replace("\r", "\n", str_replace("\r\n", "\n", $str)));
	}
	
	function WrapText($message, $length, $qp_mode = false) {
		$soft_break = ($qp_mode) ? sprintf(" =%s", $this->eol) : $this->eol;
 
		$message = $this->FixEOL($message);
		if (substr($message, -1) == $this->eol)
			$message = substr($message, 0, -1);
 
		$line = explode($this->eol, $message);
		$message = "";
		for ($i=0 ;$i < count($line); $i++)
		{
		$line_part = explode(" ", $line[$i]);
		$buf = "";
		for ($e = 0; $e<count($line_part); $e++)
		{
			$word = $line_part[$e];
			if ($qp_mode and (strlen($word) > $length))
			{
				$space_left = $length - strlen($buf) - 1;
				if ($e != 0)
				{
					if ($space_left > 20)
					{
						$len = $space_left;
						if (substr($word, $len - 1, 1) == "=")
						$len--;
						elseif (substr($word, $len - 2, 1) == "=")
						$len -= 2;
						$part = substr($word, 0, $len);
						$word = substr($word, $len);
						$buf .= " " . $part;
						$message .= $buf . sprintf("=%s", $this->eol);
					}
					else
					{
						$message .= $buf . $soft_break;
					}
					$buf = "";
				}
				while (strlen($word) > 0)
				{
					$len = $length;
					if (substr($word, $len - 1, 1) == "=")
						$len--;
					elseif (substr($word, $len - 2, 1) == "=")
						$len -= 2;
					$part = substr($word, 0, $len);
					$word = substr($word, $len);
 
					if (strlen($word) > 0)
						$message .= $part . sprintf("=%s", $this->eol);
					else
						$buf = $part;
				}
			}
			else
			{
				$buf_o = $buf;
				$buf .= ($e == 0) ? $word : (" " . $word); 
 
				if (strlen($buf) > $length and $buf_o != "")
				{
					$message .= $buf_o . $soft_break;
					$buf = $word;
				}
			}
		}
		$message .= $buf . $this->eol;
		}
 
		return $message;
	}
 
}
