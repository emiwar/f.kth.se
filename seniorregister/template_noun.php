<?php

require_once('auth.php');

require_once('constants.php');

class TemplateNoun extends Noun
{
	function __construct() { }
	
	function noun_header()
	{
		$t = '';
		
		$edit = ($this->mReq->style() == 'edit');
		
		/*$this->output($this->mHtmlg->begin_div('style_list') . "\n");
		if($edit && !$create)
			$this->output('[edit] ' .
				$this->mHtmlg->noun_ahref(
					$this->mReq->clone_extend(array('style' => 'view', 'errors' => NULL, 'replace' => NULL)),
					'[view]'));
		elseif(!$create)
			$this->output($this->mHtmlg->noun_ahref(
				$this->mReq->clone_extend(array('style' => 'edit', 'errors' => NULL, 'replace' => NULL)),
				'[edit]') .
				' [view]');
		else
			$this->output('[edit] [view]');
		$this->output($this->mHtmlg->end_div() . "\n");*/
		
		$t .= $this->mHtmlg->begin_div(''/*TODO*/);
		
		return $t;
	}
	
	function noun_footer()
	{
		$t = $this->mHtmlg->end_div() . "\n";
		
		return $t;
	}
	
	function read()
	{		
		$edit = ($this->mReq->style() == 'edit');
		$replace = $this->mReq->data_scalar('replace');
	
		if(!$replace)
		{	
			$values = array();
		}
		else
		{
			$values = array();
		}
		
		
		if($this->mReq->data('errors'))
			$errors = $this->mReq->data_array('errors', array());
		else
			$errors = array();
				
		$error_labels = array();
			
		foreach($errors as $key => $err_code)
			$errors[$key] = $error_labels[$key][$err_code];
		foreach($values as $key => $value)
			if(!isset($errors[$key]))
				$errors[$key] = '';
				
		if($edit)
			$this->output($this->mHtmlg->begin_form('get', 'index.php') . "\n");
		
		
		if($edit)
			$this->output($this->mHtmlg->input('hidden', 'verb', 'write') . "\n" .
				$this->mHtmlg->input('hidden', 'noun', ''/*TODO*/) . "\n" .
				$this->mHtmlg->input('submit', 'submit', 'Uppdatera', array('class' => 'round_btn')) . "\n" .
				$this->mHtmlg->end_form() . "\n");
				
		$this->output($this->mHtmlg->end_form());
	}
	
	function write()
	{		
		$errors = $this->validate();
		if(count($errors) > 0)
		{
			header('Location: ' . $this->mReq->clone_extend(array('verb' => 'read', 'errors' => $errors, 'replace' => 1))->href());
			return;
		}
		
		header('Location: ' . NounRequest::new_from_spec('read', '' /*TODO*/, '', 'view', 'xhtml')->href());
	}
	
	function validate()
	{
		$errors = array();
			
		return $errors;
	}
	
	function is_display()
	{
		return ($this->mReq->verb() == 'read');
	}
	
	function is_allowed($user)
	{
		return true;
	}
}

?>