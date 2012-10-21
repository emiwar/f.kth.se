<?php

require_once('auth.php');

require_once('constants.php');

class PreferencesNoun extends Noun
{
	function __construct() { }
	
	function noun_header()
	{
		$t = '';
		
		$t .= $this->mHtmlg->begin_div('preferences');
		
		return $t;
	}
	
	function noun_footer()
	{
		$t = $this->mHtmlg->end_div() . "\n";
		
		return $t;
	}
	
	function tab_array() { return array('Inställningar'); }
	
	function read()
	{
		$edit = ($this->mReq->style() == 'edit');
		$replace = $this->mReq->data_scalar('replace');
	
		if(!$replace)
		{	
			$values = array(
				'password_1' => DUMMY_PASSWORD,
				'password_2' => DUMMY_PASSWORD);
		}
		else
		{
			$values = array(
				'password_1' => DUMMY_PASSWORD,
				'password_2' => DUMMY_PASSWORD);
		}
		
		if($this->mReq->data('errors'))
			$errors = $this->mReq->data_array('errors', array());
		else
			$errors = array();
				
		$error_labels = array(
			'password_2' => array(VERR_MISSING => 'Lösenord saknas', VERR_OTHER => 'Lösenorden måste överenstämma', VERR_INVALID => 'Lösenord får endast innehålla A-Z, 0-9, . och _, samt vara mellan 8 och 16 bokstäver långt'));
			
		foreach($errors as $key => $err_code)
			$errors[$key] = $error_labels[$key][$err_code];
		foreach($values as $key => $value)
			if(!isset($errors[$key]))
				$errors[$key] = '';
				
		$this->output($this->mHtmlg->div('', 'group_head', 'Ändra lösenord') . "\n" .
			$this->mHtmlg->begin_div('', 'group_content') . "\n");
				
		if($edit)
			$this->output($this->mHtmlg->begin_form('get', 'index.php') . "\n");
		
		$this->output($this->mHtmlg->begin_table() . "\n" .
			$this->mHtmlg->password_field('Lösenord', 'password_1', $values['password_1'], $edit, $errors['password_1']) .
			$this->mHtmlg->password_field('Bekräfta lösenord', 'password_2', $values['password_2'], $edit, $errors['password_2']) .
			$this->mHtmlg->end_table() . "\n");
		
		if($edit)
			$this->output($this->mHtmlg->input('hidden', 'verb', 'write') . "\n" .
				$this->mHtmlg->input('hidden', 'noun', 'pref') . "\n" .
				$this->mHtmlg->input('submit', 'submit', 'Uppdatera', array('class' => 'round_btn')) . "\n" .
				$this->mHtmlg->end_form() . "\n");
				
		$this->output($this->mHtmlg->begin_div('status'));	
		if($this->mReq->data_scalar('success'))
			$this->output("Lösenordet ändrat.");
		$this->output($this->mHtmlg->end_div());
				
		$this->output($this->mHtmlg->end_form());
		
		$this->output($this->mHtmlg->end_div());
	}
	
	function write()
	{		
		$errors = $this->validate();
		if(count($errors) > 0)
		{
			header('Location: ' . $this->mReq->clone_extend(array('verb' => 'read', 'style' => 'edit', 'errors' => $errors, 'replace' => 1))->href());
			return;
		}
		
		$password_1 = $this->mReq->data_scalar('password_1');
		
		$u = get_session_user();
		$u->set_password($password_1);
		$u->commit();
		
		header('Location: ' . NounRequest::new_from_spec('read', 'pref', '', 'edit', 'xhtml', array('success' => 1))->href());
	}
	
	function validate()
	{
		$errors = array();
			
		// code copied from user_noun.php
		$password_1 = $this->mReq->data_scalar('password_1');
		$password_2 = $this->mReq->data_scalar('password_2');

		if($password_1 != $password_2)
			$errors['password_2'] = VERR_OTHER;
		elseif($password_1 == '')
			$errors['password_2'] = VERR_MISSING;
		elseif(!preg_match('/^[a-zA-Z0-9._]{8,16}$/', $password_1))
			$errors['password_2'] = VERR_INVALID;
			
		return $errors;
	}
	
	function remove() { return ''; }
	
	function is_display()
	{
		return ($this->mReq->verb() == 'read');
	}
	
	function is_allowed($user)
	{
		return (($this->mReq->verb() == 'write' || ($this->mReq->verb() == 'read' && $this->mReq->style() == 'edit')) &&
			$user->is_member(USER_GROUP));
	}
}

?>