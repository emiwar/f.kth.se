<?php

class Request
{
	var $mGet, $mPost, $mCookie, $mServer;
	
	function __construct($get = null, $post = null, $cookie = null, $server = null)
	{
		$this->mGet = $get;
		$this->mPost = $post;
		$this->mCookie = $cookie;
		$this->mServer = $server;
		
		if(is_null($this->mGet))
		{
			$this->mGet = $_GET;
			if(get_magic_quotes_gpc())
				$this->stripslashes_recursive($this->mGet);
		}
		
		if(is_null($this->mPost))
		{
			$this->mPost = $_POST;
			if(get_magic_quotes_gpc())
				$this->stripslashes_recursive($this->mPost);
		}

		if(is_null($this->mCookie))
		{
			$this->mCookie = $_COOKIE;
			if(get_magic_quotes_gpc())
				$this->stripslashes_recursive($this->mCookie);
		}

		if(is_null($this->mServer))
		{
			$this->mServer = $_SERVER;
			if(get_magic_quotes_gpc())
				$this->stripslashes_recursive($this->mServer);
		}		
	}
	
	function stripslashes_recursive(&$a)
	{
		foreach($a as $k => $v)
			if(is_array($v))
				$this->stripslashes_recursive($v);
			else
				$a[$k] = stripslashes($v);
	}
	
	function get_array()
	{
		return $this->mGet;
	}
	
	function post_array()
	{
		return $this->mPost;
	}
	
	function cookie_array()
	{
		return $this->mCookie;
	}
	
	function server_array()
	{
		return $this->mServer;
	}
	
	function get($key, $default = NULL)
	{
		return isset($this->mGet[$key]) ? $this->mGet[$key] : $default;
	}
	
	function post($key, $default = NULL)
	{
		return isset($this->mPost[$key]) ? $this->mPost[$key] : $default;
	}
	
	function cookie($key, $default = NULL)
	{
		return isset($this->mCookie[$key]) ? $this->mCookie[$key] : $default;
	}
	
	function server($key, $default = NULL)
	{
		return isset($this->mServer[$key]) ? $this->mServer[$key] : $default;
	}
	
	function data_keys()
	{
		return array_unique(array_keys(array_merge($this->mGet, $this->mPost)));
	}
	
	function data($key, $default = NULL)
	{
		return ($this->post($key) !== NULL) ? 
			$this->post($key) : 
			(($this->get($key) !== NULL) ? 
				$this->get($key) : 
				$default);
	}
	
	function data_scalar($key, $default = NULL)
	{
		$data = $this->data($key, $default);
		if(is_array($data))
			$data = $default;
		if(is_null($data))
			return null;
		return (string) $data;
	}
	
	function data_array($key, $default = NULL)
	{
		$data = $this->data($key, $default);
		if(is_null($data))
			return null;
		return (array) $data;
	}
	
	function data_scalar_set($key)
	{
		$data = $this->data_scalar($key, NULL);
		return isset($data);
	}
	
	function data_numeric($key)
	{
		$data = $this->data_scalar($key, NULL);
		return is_numeric($data);
	}
	
	function data_numeric_range($key, $min, $max)
	{
		$data = $this->data_scalar($key, NULL);
		return is_numeric($data) && $min <= intval($data) && intval($data) <= $max;
	}
	
	function data_in($key, $values)
	{
		$data = $this->data_scalar($key, NULL);
		return in_array($data, $values);
	}
	
	function int_scalar($key, $default = 0)
	{
		return intval($this->data_scalar($key, $default));
	}
	
	function int_scalar_or_null($key)
	{
		$data = $this->data_scalar($key);
		if(!is_numeric($data))
			return null;
		return intval($data);
	}
	
	function boolean_scalar($key, $default = false)
	{
		return $this->data_scalar($key, $default) ? true : false;
	}
	
	function text_scalar($key, $default = '')
	{
		$data = $this->data_scalar($key, $default);
		return str_replace("\r\n", "\n", $data);
	}
}

?>