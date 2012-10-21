<?php

// prepared html? intriguing idea...

class XhtmlGenerator
{
	var $strict;
	
	function __construct($strict)
	{
		$this->strict = $strict;
	}
	
	function tag($content) { return "<$content>"; }
		
	function comment($content) { return $this->tag("!-- $content --"); }
	
	function begin_tag($content) { return $this->tag($content); }
	function end_tag($content) { return $this->tag("/$content"); }
	function empty_tag($content) { return $this->tag("$content /"); }
	
	function generate_meat($name, $attribs)
	{
		return $name . 
			(is_null($attribs) ? 
				('') :
				(implode('', 
					array_map(create_function('$k,$v', 'return (!empty($v) || $v === 0) ? (" $k=\"$v\"") : "";'), 
						array_keys($attribs), array_values($attribs)))));
		/* !empty($v) by itself does not work. for example array('value' => 0) has to work */
	}
	
	function trim_attribs($attribs)
	{
		$t_attribs = array();
		foreach($attribs as $k => $v)
			if(!empty($v))
				$t_attribs[$k] = $v;
		
		return $t_attribs;
	}
	
	function generate_meat_req($name, $attribs, $req)
	{
		return $this->generate_meat($name, array_merge($req, (is_null($attribs) ? array() : $attribs)));
	}
	
	function xml_declaration()
	{
		return "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
	}
	
	function xhtml_doctype()
	{
		return (($this->strict) ?
				("<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">") :
				("<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">")
			);
	}
	
	function begin_html($attribs = NULL) { return $this->begin_tag($this->generate_meat_req('html', $attribs, array("xmlns" => "http://www.w3.org/1999/xhtml"))); }
	function end_html() { return $this->end_tag('html'); }
	
	function begin_head($attribs = NULL) { return $this->begin_tag($this->generate_meat('head', $attribs)); }
	function end_head() { return $this->end_tag('head'); }
	
	function title($title_text) { return $this->begin_tag('title') . $title_text . $this->end_tag('title'); }
	
	function meta($attribs = NULL) { return $this->empty_tag($this->generate_meat('meta', $attribs)); }
	function meta_http_equiv($http_equiv, $content) { return $this->meta(array('http-equiv' => $http_equiv, 'content' => $content)); }
	function meta_name($name, $content) { return $this->meta(array('name' => $name, 'content' => $content)); }
	
	function link($rel, $type, $href, $attribs = NULL)
	{
		return $this->empty_tag($this->generate_meat_req('link', $attribs, array('rel' => $rel, 'type' => $type, 'href' => $href)));
	}
	
	function link_stylesheet($href, $attribs = NULL) { return $this->link('stylesheet', 'text/css', $href, $attribs); }
	
	function begin_body($attribs = NULL) { return $this->begin_tag($this->generate_meat('body', $attribs)); }
	function end_body() { return $this->end_tag('body'); }
	
	function newline() { return $this->empty_tag('br'); }
	
	function heading($lvl, $content) { return $this->begin_tag('h' . $lvl) . $content . $this->end_tag('h' . $lvl); }
	
	function begin_table($attribs = NULL) { return $this->begin_tag($this->generate_meat('table', $attribs)); }
	function end_table() { return $this->end_tag('table'); }
	
	function begin_row($attribs = NULL) { return $this->begin_tag($this->generate_meat('tr', $attribs)); }
	function end_row() { return $this->end_tag('tr'); }
	
	function begin_cell($attribs = NULL) { return $this->begin_tag($this->generate_meat('td', $attribs)); }
	function end_cell() { return $this->end_tag('td'); }
	function cell($content, $attribs = NULL) { return $this->begin_cell($attribs) . $content . $this->end_cell(); }
	
	function begin_form($method, $action, $attribs = NULL)
	{
		return $this->begin_tag($this->generate_meat_req('form', $attribs, array('method' => $method, 'action' => $action)));
	}
	
	function end_form() { return $this->end_tag('form'); }
	
	function input($type, $name, $value, $attribs = NULL)
	{
		return $this->empty_tag($this->generate_meat_req('input', $attribs, array('type' => $type, 'name' => $name, 'value' => $value)));
	}
	
	function begin_p($attribs = NULL) { return $this->begin_tag($this->generate_meat('p', $attribs)); }
	function end_p() { return $this->end_tag('p'); }
	function p($content, $attribs = NULL) { return $this->begin_p($attribs) . $content . $this->end_p($attribs); }
	
	function begin_div($id, $class = '', $attribs = NULL)
	{
		return $this->begin_tag($this->generate_meat_req('div', $attribs, array('class' => $class, 'id' => $id)));
	}
	function end_div() { return $this->end_tag('div'); }
	function div($id, $class = '', $content = '', $attribs = NULL) { return $this->begin_div($id, $class, $attribs) . $content . $this->end_div(); }
	
	function strong($content) { return $this->begin_tag('strong') . $content . $this->end_tag('strong'); }
	function emphasis($content) { return $this->begin_tag('em') . $content . $this->end_tag('em'); }
}

?>