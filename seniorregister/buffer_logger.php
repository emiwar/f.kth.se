<?php

require_once('logger.php');

class BufferLogger extends Logger
{
	var $mBuffer, $mActive;
	
	function __construct($active)
	{
		$this->mBuffer = '';
		$this->mActive = $active;
	}
	
	static function new_buffer()
	{
		return new BufferLogger(true);
	}
	
	static function new_dummy()
	{
		return new Logger('', false);
	}
	
	function log($txt, $src = false, $append = true)
	{
		if(!$this->mActive)
			return;
			
		if($src === false)
			$src = 'unknown';
			
		if(!$append)
			$this->mBuffer = '';
			
		$this->mBuffer .= date('D M j G:i:s T Y') . " [$src] $txt\n";
	}
	
	function get_log()
	{
		return $this->mBuffer;
	}
}

?>