<?php

class Qmail_Admin{
	private $username = 'admin';
	private $password = 'p94toyotaxcab';
	private $domain	  = '130.94.72.57';
	private $method   = 'http://';

	function __construct($username=null, $password=null,$domain=null, $secure=false){
		if ($username != null)
			$this->username = $username;
		if ($password != null)
			$this->password = $password;
		if ($domain != null)
			$this->domain = $domain;
		if ($secure == true)
			$this->method = 'https://';
	}

	public function addUser($name, $email, $password, $quota=''){
		$send_url = $this->method.$this->domain.'/mail/vqadmin/toaster.vqadmin';
		$vars = array(
			'nav' 		=> 'add_user', 	// hidden var
			'fname' 	=> $name, 		// full name  				-> text input
			'quota' 	=> $quota,		// quota in bytes  			-> text input
			'cpass' 	=> $password,	// password  				-> text input
			'eaddr' 	=> $email, 		// email address  			-> text input
			// 'udisable' 	=> '', 			// Disable pop access  		-> checkbox input
			// 'uweb' 		=> '', 			// Disable web access 		-> checkbox input
			// 'uimap' 		=> '', 			// disable imap access 		-> checkbox input
			// 'upassc' 	=> '' , 		// disable change passwords -> checkbox input
			// 'ubounce' 	=> '',			// bounce email 			-> checkbox input
			// 'urelay' 	=> '', 			// disable email relay		-> checkbox input
			'Submit'	=> 'Add Email Account'
		);

		$results = Curl::Send($send_url,$vars,$this->username, $this->password);
		return true;
	}

	public function updateUser($name, $email, $password, $quota=''){
		$send_url = $this->method.$this->domain.'/mail/vqadmin/toaster.vqadmin';
		$vars = array(
			'nav' 		=> 'mod_user', 	// hidden var
			'fname' 	=> $name, 		// full name  				-> text input
			'quota' 	=> $quota,		// quota in bytes  			-> text input
			'cpass' 	=> $password,	// password  				-> text input
			'eaddr' 	=> $email, 		// email address  			-> text input
			// 'udisable' 	=> '', 			// Disable pop access  		-> checkbox input
			// 'uweb' 		=> '', 			// Disable web access 		-> checkbox input
			// 'uimap' 		=> '', 			// disable imap access 		-> checkbox input
			// 'upassc' 	=> '' , 		// disable change passwords -> checkbox input
			// 'ubounce' 	=> '',			// bounce email 			-> checkbox input
			// 'urelay' 	=> '', 			// disable email relay		-> checkbox input
			'Submit'	=> 'Modify Email Account'
		);
		$results = Curl::Send($send_url,$vars,$this->username, $this->password);
		return true;
	}

	public function deleteUser($email){
		$send_url = $this->method.$this->domain.'/mail/vqadmin/toaster.vqadmin';
		$vars = array(
			'nav' 		=> 'del_user', 	// hidden var
			'eaddr' 	=> $email, 		// email address  			-> text input
			'Submit'	=> 'Delete user'
		);
		$results = Curl::Send($send_url,$vars,$this->username, $this->password);
		return true;
	}
}