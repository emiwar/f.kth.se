<?php

/* VERSION 081105 */

class Logger
{
	var $mFile, $mActive;
	
	function __construct($file, $active)
	{
		$this->mFile = $file;
		$this->mActive = $active;
	}
	
	static function new_from_file($file)
	{
		return new Logger($file, true);
	}
	
	static function new_dummy()
	{
		return new Logger('', false);
	}
	
	function clear()
	{
		$this->log('log cleared' , 'system', false);
	}
	
	function log($txt, $src = false, $append = true)
	{
		if(!$this->mActive)
			return;
			
		if($src === false)
			$src = 'unknown';
			
		if(($fd = @ fopen($this->mFile, ($append ? 'a' : 'w'))) === false)
			die("<strong>Fatal Error:</strong> open logging file '" . $this->mFile . "'. Aborting.<br />\n");
		if(@ fwrite($fd, date('D M j G:i:s T Y') . " [$src] $txt\n") === false)
			die("<strong>Fatal Error:</strong>Cannot write to logging file '" . $this->mFile . "'. Aborting.<br />\n");
		@ fclose($fd);
	}
}

?>