<?php
namespace Plex;

interface inOutput {
	public function error($message=false, $description=false, $errorInfo=false);
}