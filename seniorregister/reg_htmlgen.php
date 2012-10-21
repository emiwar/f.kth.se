<?php

require_once('htmlgen.php');

require_once('globals.php');

class XhtmlGeneratorEx extends XhtmlGenerator
{
	function __construct($strict)
	{
		parent::__construct($strict);
	}
	
	function script_src($language, $src, $attribs = null)
	{
		return $this->begin_tag($this->generate_meat_req('script', $attribs, array('langauge' => $language, 'src' => $src))) . $this->end_tag('script');
	}
	
	function begin_script($language, $attribs = null)
	{
		return $this->begin_tag($this->generate_meat_req('script', $attribs, array('language' => $language)));
	}
	
	function end_script()
	{
		return $this->end_tag('script');
	}
	
	function begin_span($id, $class = '', $attribs = NULL)
	{
		return $this->begin_tag($this->generate_meat_req('span', $attribs, array('class' => $class, 'id' => $id)));
	}
	
	function end_span() { return $this->end_tag('span'); }
	
	function span($id, $class = '', $content = '', $attribs = NULL) { return $this->begin_span($id, $class, $attribs) . $content . $this->end_span(); }
	
	function begin_select($name, $multiple = false, $attribs = null)
	{
		return $this->begin_tag($this->generate_meat_req('select', $attribs, array('name' => $name, 'multiple' => $multiple ? 'multiple' : '')));
	}
	
	function begin_option($value, $selected = false, $attribs = null)
	{
		return $this->begin_tag($this->generate_meat_req('option', $attribs, array('value' => $value, 'selected' => $selected ? 'selected' : '')));
	}
	
	function end_option()
	{
		return $this->end_tag('option');
	}

	function option($value, $caption, $selected = false, $attribs = null)
	{
		return $this->begin_option($value, $selected, $attribs) . $value . $this->end_option();
	}
	
	function end_select()
	{
		return $this->end_tag('select');
	}
	
	// should be fixed to handle multiples properly, $selected to be an array in this case
	function select($name, $options, $selected, $multiple = false)
	{
		$t = $this->begin_tag($this->generate_meat('select', array('name' => $name, 'id' => $name, 'multiple' => $multiple ? 'multiple' : ''))) . "\n";
		foreach($options as $k => $v)
			$t .= $this->begin_tag($this->generate_meat('option', 
				array('value' => $k, 'selected' => ($k == $selected) ? 'selected' : ''))) . $v . $this->end_tag('option') . "\n";
		$t .= $this->end_tag('select') . "\n";
		
		return $t;
	}
	
	function checkbox($name, $value, $caption, $checked, $enabled = true, $attribs = NULL)
	{
		return $this->begin_tag($this->generate_meat_req('input', $attribs, array('type' => 'checkbox', 'name' => $name, 'id' => $name, 'value' => $value, 'checked' => $checked ? 'checked' : '', 'disabled' => $enabled ? '' : 'disabled'))) . $caption;
	}
	
	function textarea($name, $value, $attribs = NULL)
	{
		return $this->begin_tag($this->generate_meat_req('textarea', $attribs, array('name' => $name))) . $value . $this->end_tag('textarea');
	}
	
	function ahref($href, $caption, $attribs = NULL)
	{
		return $this->begin_tag($this->generate_meat_req('a', $attribs, array('href' => $href))) . $caption . $this->end_tag('a');
	}
	
	function begin_hcell($attribs = NULL) { return $this->begin_tag($this->generate_meat('th', $attribs)); }
	function end_hcell() { return $this->end_tag('th'); }
	function hcell($content, $attribs = NULL) { return $this->begin_hcell($attribs) . $content . $this->end_hcell(); }
	
	function row($content, $attribs = NULL) { return $this->begin_row($attribs) . $content . $this->end_row(); }
}

class RegisterXhtmlGenerator extends XhtmlGeneratorEx
{
	function __construct($strict)
	{
		parent::__construct($strict);
	}
	
	function field_row($caption, $value, $editable, $error = '')
	{
		return $this->begin_row(array('class' => 'field_row')) . $this->cell($caption, array('class' => 'field_caption')) . $this->cell($value, array('class' => 'field_value ' . ($editable ? 'edited' : 'viewed'))) .
		$this->cell(($error ? $this->span('', 'error', $error) : ''), array('class' => 'field_error')) . $this->end_row() . "\n";
	}

	function fixed_field($label, $value, $edit = false)
	{
		assert($edit == false);
		return $this->field_row($label . ':', $value, $edit);
	}
	
	function text_field($label, $name, $value, $edit = false, $error = '')
	{
		return $this->field_row($label . ':', ($edit ? ($this->input('text', $name, $value, array('id' => $name))) : ($value)), $edit, $error);
	}
	
	function textarea_field($label, $name, $value, $edit = false, $error = '')
	{
		return $this->field_row($label . ':', ($edit ? ($this->textarea($name, $value, array('id' => $name))) : (nl2br($value))), $edit, $error);
	}
	
	function password_field($label, $name, $value, $edit = false, $error = '')
	{
		return $this->field_row($label . ':', ($edit ? ($this->input('password', $name, $value, array('id' => $name))) : (str_repeat('&bull;', strlen($value)))), $edit, $error);
	}
	
	function list_field($label, $name, $options, $selected, $edit = false)
	{
		return $this->field_row($label . ':', ($edit ? ($this->select($name, $options, $selected, false)) : ($options[$selected])), $edit);
	}
	
	/*function list_classes_field($label, $name, $parameters, $selected, $edit = false)
	{
		$t = $this->strong($label . ':') . "\n";
		if($edit)
			$t .= $this->begin_select($name, false) . "\n";
		$years = $parameters->class_years();
		if($edit)
			foreach($years as $year)
				$t .= $this->option($year, )
	}*/
	
	function checkbox_field($label, $name, $checked, $edit = false)
	{
		return $this->field_row($this->checkbox($name, '1', $label, $checked, $edit), '', $edit);
	}
	/*
	function checkbox_text_field($label, $checkbox_name, $checked, $text_name, $value, $edit = false)
	{
		return $this->begin_row() . $this->cell($this->checkbox($checkbox_name, '1', $label, $checked, $edit)) .
			$this->cell(($edit ? ($this->input('text', $text_name, $value)) : ($value))) . 
			$this->end_row() . "\n";
	}*/
	
	function noun_ahref($noun_req, $caption, $hide = false, $attribs = NULL)
	{
		//$valid = true;
		/*$req = new RegisterRequest(array_merge(array('verb' => $verb, 'noun' => $noun . '/' . $section, 'style' => $style, 'format' => $format), !is_null($data) ? $data : array()), array(), array(), array());
		if($noun == 'student')
		{
			$n = new StudentNoun(NULL, $req, NULL, NULL, NULL);
			$valid = $n->is_allowed($this->mUser);
		}
		if($noun == 'list')
		{
			$n = new ListNoun(NULL, $req, NULL, NULL, NULL);
			$valid = $n->is_allowed($this->mUser);
		}*/
		// funky... this is what we want though...
		
		$n = Noun::new_from_request($noun_req);
		$valid = $n->is_allowed(get_session_user());
		// better
		
		$href = $noun_req->href();
		return $valid ? $this->ahref($href, $caption, $attribs) : (!$hide ? $caption : '');
	}
}

?>