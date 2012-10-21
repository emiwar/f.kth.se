<?php

require_once('noun.php');

require_once('auth.php');
require_once('globals.php');

class AuthNoun extends Noun
{
	function __construct() { }
	
	function noun_header()
	{
	}
	
	function noun_footer()
	{
	}
	
	function tab_array() { return array('Logga in'); }
	
	function read()
	{
		$req = $this->mReq;
		$disabled = $req->data_scalar('nodb');
		$htmlg = get_htmlg();
		
		/*if(get_session_user_id() != -1)
		{
			header('Location: ' . NounRequest::new_from_spec('read', 'default', '', 'view', 'xhtml')->href());
			return;
		}*/
		
		$this->output($htmlg->begin_form('post', 'index.php') . "\n" .
			$htmlg->begin_table() . "\n" .
			$htmlg->text_field('Användarnamn', 'username', $req->data_scalar('username'), true) . 
			$htmlg->password_field('Lösenord', 'password', '', true) .
			$htmlg->end_table() . "\n" .
			$htmlg->input('hidden', 'verb', 'write') . "\n" .
			$htmlg->input('hidden', 'noun', 'auth/in') . "\n" .
			$htmlg->input('submit', 'submit', 'Logga in', array('disabled' => $disabled ? 'disabled' : NULL)) . "\n" .
			$htmlg->end_form() . "\n");
			
		if($req->data_scalar('error'))
			$this->output($htmlg->newline() . $htmlg->newline() . "Ogiltigt användarnamn/lösenord");
	}
	
	function write()
	{
		$req = $this->mReq;
		
		if($req->section() == 'in')
		{
			$u = AuthUser::new_from_username(get_db(), $req->data_scalar('username'));
			$u->load();
		
			if($u->compare_password($req->data_scalar('password')))
			{
				set_session_user_id($u->user_id());
				
				if($u->owns_student_id() != -1)
					$href = NounRequest::new_from_spec('read', 'student', 'core', 'view', 'xhtml', array('student_id' => $u->owns_student_id()))->href();
				else
					$href = NounRequest::new_from_spec('read', 'list', 'filter', 'view', 'xhtml')->href();
			}
			else
				$href = $req->clone_extend(array('verb' => 'read', 'password' => '', 'error' => 1))->href();
		}
		else
		{
			clear_session_user_id();
			$href = $req->clone_extend(array('verb' => 'read', 'section' => 'in'))->href();
		}
		
		header('Location: ' . $href);
	}
	
	function validate() { return ''; }
	function remove() { return ''; }
	
	function is_display()
	{
		return ($this->mReq->verb() == 'read');
	}
	
	function is_allowed($user)
	{		
		return ($this->mReq->section() == 'in') || ($this->mReq->section() == 'out' && get_session_user_id() != -1);
	}
}


?>