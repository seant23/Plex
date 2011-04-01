<?php
namespace Plex;
 

class Curl{
	
	static function Send($url,$data,$username=null,$password=null){

		// $data = array('key' =>'value);
		$data = http_build_query($data);

		// URL of gateway for cURL to post to
		$ch = curl_init($url); 

		// set to 0 to eliminate header info from response
		
		// htaccess if required
		if ($username != null){
			curl_setopt($ch, CURLOPT_HEADER, 1); 
			curl_setopt($ch,CURLOPT_HTTP_VERSION,CURLAUTH_NTLM);
			curl_setopt($ch,CURLOPT_USERPWD,"{$username}:{$password}");
		}else{
			curl_setopt($ch, CURLOPT_HEADER, 0); 
		}
		
		

		// Returns response data instead of TRUE(1)
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

		// use HTTP POST to send form data
		curl_setopt($ch, CURLOPT_POSTFIELDS, rtrim( $data, "& " ));
		
		//Do some sort of Randy hack to get the system to not case about an unsigned Cert.
		curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
		
		//execute post and get results
		$results = curl_exec($ch); 
		
		// Check if any error occured
		if(curl_errno($ch)) {
			$error = curl_error($ch);
			// close connection
			curl_close($ch);
			return $error;
		} else {
			// close connection
			curl_close($ch);
			return $results;
		}
	}
}