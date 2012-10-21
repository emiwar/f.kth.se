<?php

require_once('request.php');

class NounRequest extends Request
{
	function __construct($get = null, $post = null, $cookie = null, $server = null)
	{
		parent::__construct($get, $post, $cookie, $server);
	}
	
	function new_from_request($req)
	{
		if($req instanceof NounRequest)
			return $req;
		elseif($req instanceof Request)
			return new NounRequest($req->get_array(), $req->post_array(), $req->cookie_array(), $req->server_array());
	}
	
	function new_from_spec($verb, $noun, $section, $style, $format, $extra = NULL)
	{
		if(is_null($extra))
			$extra = array();
		return new NounRequest(array_merge(array('verb' => $verb, 'noun' => "$noun/$section", 'style' => $style, 'format' => $format), $extra), array(), array(), array());
	}
	
	function verb()
	{
		return $this->data('verb', 'read');
	}
	
	function noun()
	{
		$p = explode('/', $this->data('noun', 'default') . '/');
		return $p[0];
	}
	
	function section()
	{
		$p = explode('/', $this->data('noun', 'default') . '/');		
		return $p[1];
	}
	
	function style()
	{
		return replace_empty($this->data('style', 'view'), 'view');
	}
	
	function format()
	{
		return replace_empty($this->data('format', 'xhtml'), 'xhtml');
	}
	
	function extra_data()
	{
		$extra = array();
		foreach($this->data_keys() as $key)
			if(!in_array($key, array('verb', 'noun', 'section', 'style', 'format')))
				$extra[$key] = $this->data($key);
		return $extra;
	}
	
	function href()
	{
		$href = "index.php?";
		$href .= "verb=" . $this->verb() .
			"&noun=" . $this->noun() . "/" . $this->section() . 
			"&style=" . $this->style() . 
			"&format=" . $this->format();
		foreach($this->extra_data() as $k => $v)
			if(is_array($v))
				foreach($v as $sk => $sv)
				{
					if($sv !== NULL)
						$href .= "&${k}[$sk]=$sv";
				} // this is strange, braces shouldn't be necessary?
			elseif($v !== NULL)
				$href .= "&$k=$v";
		return $href;
	}
	
	function to_href() { return $this->href(); }
	
	function to_hidden()
	{
		$htmlg = get_htmlg();
		
		$t = '';
		
		$t .= $htmlg->input('hidden', 'verb', $this->verb()) . "\n" .
			$htmlg->input('hidden', 'noun', $this->noun() . '/' . $this->section()) . "\n" .
			$htmlg->input('hidden', 'style', $this->style()) . "\n" .
			$htmlg->input('hidden', 'format', $this->format()) . "\n";
		
		foreach($this->extra_data() as $k => $v)
			if(is_array($v))
				foreach($v as $sk => $sv)
				{
					if($sv !== NULL)
						$t .= $htmlg->input('hidden', "${k}[$sk]", $sv) . "\n";
				}
			elseif($v !== NULL)
				$t .= $htmlg->input('hidden', $k, $v) . "\n";
		
		return $t;
	}
	
	function clone_extend($args = NULL)
	{
		if(!is_array($args))
			$args = array();
		return NounRequest::new_from_spec(
			isset($args['verb']) ? $args['verb'] : $this->verb(),
			isset($args['noun']) ? $args['noun'] : $this->noun(),
			isset($args['section']) ? $args['section'] : $this->section(),
			isset($args['style']) ? $args['style'] : $this->style(),
			isset($args['format']) ? $args['format'] : $this->format(),
			array_merge($this->extra_data(), $args));
	}
	
	function suppress_extension() {} //...?
}

?>