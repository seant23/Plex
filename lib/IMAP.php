<?php
class IMAP{
	private static $mail 		= false;
	private static $mailboxes 	= array();
	private static $domain 		= null;
	private static $username 	= null;
	private static $password 	= null;
	private static $port 		= null;
	private static $error		= null;
	private static $connect 	= false;
	private static $check		= null;
	private static $overview	= null;
	private static $message 	= null;

	static function connect($username,$password,$domain,$port=143){
		self::$error	= null;
		self::$domain 	= $domain;
		self::$username = $username;
		self::$password = $password;
		self::$port		= $port;
		self::$mail 	= imap_open('{'.$domain.':'.$port.'}INBOX', $username, $password);

		if (self::$mail == false){
			self::$error = "Connection to Server {self::$domain} over port {self::$port} failed.";
			return false;
		}
		self::$connect = true;
		self::$check = imap_check(self::$mail);
		return true;
	}
	
	/*
	This returns an array of message headers. 
	*/
	static function getMessageOverview($ignoreDeleted = true){
		self::$error	= null;
		if (self::$connect == false){
			self::$error = 'No Connection to mail server';
			return false;
		}
		$mail = array();

		$result = imap_fetch_overview(self::$mail,'1:'.self::$check->Nmsgs,0);
		foreach ($result as $overview) {
			
			// ignore deleted files
			if ($overview->deleted != true || $ignoreDeleted != true){
				$tmp = array();
				$tmp['subject'] 		= $overview->subject;
				$tmp['to']				= $overview->to;
				$tmp['date']			= $overview->date;
				$tmp['from']			= $overview->from;
				$tmp['message_id'] 		= $overview->message_id;
				$tmp['message_number']	= $overview->msgno;
				$tmp['uid']				= $overview->uid;
				$tmp['size']			= $overview->size;
				$tmp['read']			= $overview->seen;
				$mail[$tmp['message_number']]	=$tmp;
			}
		}
		self::$overview = $mail;
		return $mail;
	}
	
	static function deleteMessage($message_number){
		self::$error	= null;
		if (self::$connect == false){
			self::$error = 'No Connection to mail server';
			return false;
		}
		if (!isset(self::$overview[$message_number])){
			self::$error = 'Message does not exist.';
			return false;
		}

		return imap_delete(self::$mail,$message_number);
	}
	
	static function getMessage($message_number){
		self::$error	= null;
		if (self::$connect == false){
			self::$error = 'No Connection to mail server';
			return false;
		}

		if (!isset(self::$overview[$message_number])){
			self::$error = 'Message does not exist.';
			return false;
		}
		self::$message = array();
		$body = imap_fetchstructure(self::$mail,$message_number);

		if (!isset($body->parts)){
			self::getMessageBody($message_number,$body,1);
		}else {  
	    	$section =1;
	        foreach($body->parts as $part){
	        	self::getMessageBody($message_number,$part,$section);
	        	$section++;
	        }
	    }
	    $tmp = self::$overview[$message_number];
	    $tmp['body'] = self::$message;
	    return $tmp;
	}
	
	/*
		This is a recursive function that goes through each attachment and sub attachments and puts
		then into an array for you to do whatever you want it. It also decodes encoded documents.
	*/
	private static function getMessageBody($message_number,$params,$section){
		if (!isset($params->parts)){

			$content = imap_fetchbody(self::$mail,$message_number,$section);
			$data = array();
	
			// deal with encoding types
			if ($params->encoding==4)
	        	$content = quoted_printable_decode($content);
	    	elseif ($params->encoding==3)
	        	$content = base64_decode($content);
			
	        // add all parameters to the array.
	        if (isset($params->parameters)){
	        	foreach ($params->parameters as $row){
	        		$data[ strtolower($row->attribute )] = $row->value;
	        	}
	        }

	        // attachments can have the param name filename and/or name. 
	        // this just makes sure there is a filename in all attachment cases.
	        if (isset($params->subtype))
	        	$data['type'] = $params->subtype;
	        if (isset($data['name'])){
	        	$data['filename'] = $data['name'];
	        }
	        
	        if(!isset($data['filename']))
	        	$data['filename'] = 'message.html';


	        if ($params->ifid == 1){
	        	$data['cid'] = preg_replace(array("/^\</","/\>$/"),array('',''),$params->id);
	        }

	        $data['size'] = mb_strlen($content);
	        $data['data'] = $content;
			self::$message[]=$data;

		}else {
			// here's where the recursion appears. If there is a sub 
			// attachment then grab that too. 
	    	$sub_section =1;
	        foreach($params->parts as $part){
	        	self::getMessageBody($message_number,$part,"{$section}.{$sub_section}");
	        	$sub_section++;
	        }
	    }
	}

	static function getMessageHeaders(){
		self::$error	= null;
		if (self::$connect == false){
			self::$error = 'No Connection to mail server';
			return false;
		}

		$header = imap_headers(self::$mail);
		return $header;
	}
	
	static function close(){
		self::$error	= null;
		if (self::$connect == false){
			self::$error = 'No Connection to mail server';
			return false;
		}
		imap_close(self::$mail);
		return true;
	}
	
	static function getMailboxes(){
		self::$error	= null;
		if (self::$connect == false){
			self::$error = 'No Connection to mail server';
			return false;
		}

		$self->mailboxes = imap_list(self::$mail, '{'.self::$domain.':'.self::$port.'}', "*");
		return $self->mailboxes;
	}
	
	static function getLastError(){
		return self::$error;
	}
}