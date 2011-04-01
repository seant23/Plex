<?php
namespace Plex;

class SMTP
{
	public $host = false;
	public $port = false;
	public $timeout = false;
	public $socket = false;
	public $debug = false;

	public function __construct($host=false, $port=25, $timeout=30)
	{
		$this->host = $host;
		$this->port = $port;
		$this->timeout = $timeout;
	}

	public function connect($persitant=false)
	{
		if($this->socket) {
			#fclose($this->socket);		
		}

		if($persitant)
		{
			if(!$this->socket = @pfsockopen($this->host, $this->port, $errorNum, $errorStr, $this->timeout))
			die("Failed To Connect To {$this->host}:{$this->port}");
		}
		else
		{
			if(!$this->socket = @fsockopen($this->host, $this->port, $errorNum, $errorStr, $this->timeout))
			die("Failed To Connect To {$this->host}:{$this->port}");
		}

		return $this->get();
	}
	
	function auth($username, $password) {
        // Start authentication
        $rply = $this->sendAndGet("AUTH LOGIN");
        if($this->debug){
			echo ">>> AUTH LOGIN\r\n";
			echo ">>> $rply\r\n";
        }
        $code = substr($rply,0,3);

        if($code != 334) {
            $this->error =
                array("error" => "AUTH not accepted from server",
                      "smtp_code" => $code,
                      "smtp_msg" => substr($rply,4));
            if($this->do_debug >= 1) {
                echo "SMTP -> ERROR: " . $this->error["error"] .
                         ": " . $rply . "\r\n";
            }
            return false;
        }

        // Send encoded username
        $rply = $this->sendAndGet(base64_encode($username));
        if($this->debug){
			echo ">>> ".base64_encode($username)."\r\n";
			echo ">>> $rply\r\n";
        }
        $code = substr($rply,0,3);

        if($code != 334) {
            $this->error =
                array("error" => "Username not accepted from server",
                      "smtp_code" => $code,
                      "smtp_msg" => substr($rply,4));
            if($this->do_debug >= 1) {
                echo "SMTP -> ERROR: " . $this->error["error"] .
                         ": " . $rply . "\r\n";
            }
            return false;
        }

        // Send encoded password
         $rply = $this->sendAndGet(base64_encode($password));
        if($this->debug){
			echo ">>> ".base64_encode($password)."\r\n";
			echo ">>> $rply\r\n";
        }
        $code = substr($rply,0,3);

        if($code != 235) {
            $this->error =
                array("error" => "Password not accepted from server",
                      "smtp_code" => $code,
                      "smtp_msg" => substr($rply,4));
            if($this->do_debug >= 1) {
                echo "SMTP -> ERROR: " . $this->error["error"] .
                         ": " . $rply . "\n";
            }
            return false;
        }

        return true;
    }

	public function disconnect()
	{
		fclose($this->socket);
	}

	public function send($cmd)
	{
		if($this->debug)
		echo ">>> $cmd\r\n";

		fputs($this->socket, $cmd."\r\n");
	}

	public function get()
	{
		$data = "";
		while($str = fgets($this->socket,515))
		{
			$data .= $str;
			if(substr($str,3,1) == " ") { break; }
		}

		if($this->debug)
		echo "<<< $data\r\n";

		return $data;
	}

	public function sendAndGet($cmd)
	{
		$this->send($cmd);
		return $this->get();
	}

	public function helo($hostname=false)
	{
		if(!$hostname)
		$hostname = $_SERVER['HTTP_HOST'];

		return $this->sendAndGet("HELO $hostname");
	}

	public function mail($fromEmail=false)
	{
		if(!$fromEmail)
		$fromEmail = $_SERVER['SERVER_ADMIN'];

		return $this->sendAndGet("MAIL From: $fromEmail");
	}

	public function rcpt($toEmail)
	{
		return $this->sendAndGet("RCPT To: $toEmail");
	}

	public function size($bytes)
	{
		return $this->sendAndGet("SIZE=$bytes");
	}

	public function data($data)
	{
		$this->sendAndGet("DATA");
		
		$data = str_replace("\r\n","\n",$data);
		$data = str_replace("\r","\n",$data);
		$lines = explode("\n",$data);
		$inHeaders = false;
		$maxLength = 998;

		foreach($lines as $lineNum=>$line)
		{
			$in_headers = false;
			
			if(strpos($line,':') && $lineNum == 0)
			$inHeaders = true;

			if($line == "" && $inHeaders)
			$inHeaders=false;
			
			$subLines = array();

			while(strlen($line) > $maxLength)
			{
				$pos = strrpos(substr($line,0,$maxLength)," ");

				if(!$pos)
				$pos = $maxLength - 1;

				$subLines[] = substr($line,0,$pos);
				$line = substr($line,$pos + 1);

				if($in_headers)
				$line = "\t" . $line;
			}
			
			$subLines[]=$line;
			
			foreach($subLines as $subLine)
			$this->send($subLine);
		}

		$this->sendAndGet(".");
		return true;
	}

	public function quit()
	{
		return $this->sendAndGet("QUIT");
	}

	public function vrfy($username)
	{
		return $this->sendAndGet("VRFY $username");
	}

	public function expn($username)
	{
		return $this->sendAndGet("EXPN $username");
	}

	public function noop()
	{
		return $this->sendAndGet("NOOP");
	}

	public function rset()
	{
		return $this->sendAndGet("RSET");
	}
}