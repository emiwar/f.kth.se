<?php

require_once('auth.php');
require_once('utils.php');

require_once('constants.php');

class UserNoun extends Noun
{
	function __construct() { }
	
	function noun_header()
	{
		$t = '';
		
		$create = ($this->mReq->style() == 'create');
		$edit = ($this->mReq->style() == 'edit');
		$invite = (boolean)$this->mReq->data_scalar('invite_code');
		
		$t .= $this->mHtmlg->begin_div('user_data');
		
		return $t;
	}
	
	function tab_array() { return array('Användare'); }
	
	function top_box()
	{
		$create = ($this->mReq->style() == 'create');
		$edit = ($this->mReq->style() == 'edit');
		
		if(!$edit && !$create)
			return $this->mHtmlg->noun_ahref(
				$this->mReq->clone_extend(array('style' => 'edit', 'errors' => NULL, 'replace' => NULL)),
				'Redigera');
		return '';
	}
	
	function noun_footer()
	{
		$t = $this->mHtmlg->end_div() . "\n";
		
		return $t;
	}
	
	function read()
	{
		$user_id = $this->mReq->int_scalar('user_id');
		
		$groups = AuthGroup::list_groups($this->mDb);
		
		$edit = ($this->mReq->style() == 'edit');
		$create = ($this->mReq->style() == 'create');
		$replace = $this->mReq->data_scalar('replace');
		$invite = (boolean)$this->mReq->data_scalar('invite_code');
		$invite_code = $this->mReq->data_scalar('invite_code');
		
		if($this->mReq->data('errors'))
			$errors = $this->mReq->data_array('errors', array());
		else
			$errors = array();
		
		if(!$create)
		{
			$u = new AuthUser($this->mDb, $user_id);
			$u->load();
		}
	
		if(!$replace && !$create)
		{			
			if($u->user_id() == -1)
			{
				$this->output('Ogiltigt användar-id.');
				return;
			}
			
			$values = array(
				'username' => $u->username(),
				'password_1' => DUMMY_PASSWORD,
				'password_2' => DUMMY_PASSWORD,
				'super' => $u->super(),
				'owns_student_id' => $u->owns_student_id());
			foreach($groups as $group_id => $group_name)
				$values['group_'.$group_id] = $u->is_member($group_id);
		}
		elseif(!$replace && $create)
		{
			$values = array(
				'username' => '',
				'password_1' => '',
				'password_2' => '',
				'super' => false,
				'owns_student_id' => -1);
			foreach($groups as $group_id => $group_name)
				$values['group_'.$group_id] = false;
		}
		else
		{
			$values = array(
				'username' => $create ? $this->mReq->data_scalar('username') : $u->username(),
				'password_1' => $create ? '' : DUMMY_PASSWORD,
				'password_2' => $create ? '' : DUMMY_PASSWORD,
				'super' => $this->mReq->data_scalar_set('super'),
				'owns_student_id' => $this->mReq->int_scalar_or_null('owns_student_id'));
			foreach($groups as $group_id => $group_name)
			{
				if(!in_array($group_id, array(ALL_GROUP, USER_GROUP, SUPER_GROUP)) && 
					(get_session_user()->is_member($group_id) || get_session_user()->super()))
					$values['group_'.$group_id] = $this->mReq->data_scalar_set('group_'.$group_id);
				elseif(!$create)
					$values['group_'.$group_id] = $u->is_member($group_id);
				else
					$values['group_'.$group_id] = false;
				// apparently disabled inputs don't go through
			}
		}
			
		$error_labels = array(
			'username' => array(VERR_MISSING => 'Användarnamn saknas', VERR_INVALID => 'Användarnamn får endast innehålla A-Z, 0-9, . och _, samt vara mellan 3 och 16 bokstäver långt', VERR_OTHER => 'Användarnamnet existerar redan'),
			'password_2' => array(VERR_MISSING => 'Lösenord saknas', VERR_OTHER => 'Lösenorden måste överenstämma', VERR_INVALID => 'Lösenord får endast innehålla A-Z, 0-9, . och _, samt vara mellan 8 och 16 bokstäver långt'),
			'owns_student_id' => array(VERR_INVALID => 'Ogiltigt student-id', VERR_OTHER => 'Denna student är redan knuten till en användare'));
			
			
		foreach($errors as $key => $err_code)
			$errors[$key] = $error_labels[$key][$err_code];
		foreach($values as $key => $value)
			if(!isset($errors[$key]))
				$errors[$key] = '';
				
		if($edit || $create)
			$this->output($this->mHtmlg->begin_form('get', 'index.php') . "\n");
		
		$this->output($this->mHtmlg->div('', 'group_head', 'Konto') . "\n" .
			$this->mHtmlg->begin_div('', 'group_content') . "\n");
		
		$this->output($this->mHtmlg->begin_table() . "\n".
			$this->mHtmlg->fixed_field('Användare #', !$create ? $user_id : 'N/A', false) .
			$this->mHtmlg->text_field('Användarnamn', 'username', $values['username'], $create, $errors['username']) .
			$this->mHtmlg->password_field('Lösenord', 'password_1', $values['password_1'], $edit || $create, $errors['password_1']) .
			$this->mHtmlg->password_field('Bekräfta lösenord', 'password_2', $values['password_2'], $edit || $create, $errors['password_2']));
		if(!$invite)
		{
			$s = new Student($this->mDb, $values['owns_student_id']);
			$s->load_core();
			if($s->id() != -1)
				$student_name = $s->first_name().' '.$s->last_name();
			else
				$student_name = 'Ingen';
				
			$student_search = 
				$this->mHtmlg->span(
					'search_link', '', 
					$this->mHtmlg->ahref('#', $student_name, array('onclick' => 'show_search(this);return false;'))) .
				$this->mHtmlg->span(
					'search_box', '', 
					$this->mHtmlg->input('text', 'search_query', $student_name, array('id' => 'search_query')) . 
					$this->mHtmlg->input('button', 'search_button', 'Sök', array('onclick' => 'do_search(this);')) .
					$this->mHtmlg->div('search_results'));
			
			$this->output(
				$this->mHtmlg->checkbox_field('Superanvändare', 'super', $values['super'], $edit || $create, $errors['super']));
			$this->output(
				$this->mHtmlg->field_row('Knuten till student', ($edit || $create ) ? $student_search : $student_name, $edit || $create, $errors['owns_student_id']));
			$this->output($this->mHtmlg->end_table() . "\n");
			$this->output($this->mHtmlg->end_div() . "\n" .
				$this->mHtmlg->div('', 'group_head', 'Gruppmedlemskap') . "\n" .
				$this->mHtmlg->begin_div('', 'group_content') . "\n");
		
			$this->output($this->mHtmlg->begin_table() . "\n");
			foreach($groups as $group_id => $group_name)
				$this->output($this->mHtmlg->checkbox_field($group_name, 'group_'.$group_id, $values['group_'.$group_id], 
				($edit || $create) && !in_array($group_id, array(ALL_GROUP, USER_GROUP, SUPER_GROUP)) && 
				(get_session_user()->is_member($group_id) || get_session_user()->super()), 
				$errors['group_'.$group_id]));
		}
		else
		{
			$i = new AuthInvite($this->mDb, $invite_code);
			$i->load();
			
			if($i->owns_student_id() != -1)
			{
				$s = new Student($this->mDb, $i->owns_student_id());
				$s->load();
				
				$student_name = $s->first_name() . ' ' . $s->last_name();
			
				$this->output(
					$this->mHtmlg->field_row('Knuten till student', $student_name, false));
			}
		}
		$this->output($this->mHtmlg->end_table() . "\n");
		
		$this->output($this->mHtmlg->end_div() . "\n");
		
		if($invite)
			$this->output($this->mHtmlg->input('hidden', 'invite_code', $invite_code) . "\n");
		
		if($edit || $create)
			$this->output($this->mHtmlg->input('hidden', 'user_id', $user_id) . "\n" .
				$this->mHtmlg->input('hidden', 'owns_student_id', $values['owns_student_id'], array('id' => 'owns_student_id')) . "\n" .
				$this->mHtmlg->input('hidden', 'verb', 'write') . "\n" .
				$this->mHtmlg->input('hidden', 'noun', 'user') . "\n" .
				$this->mHtmlg->input('hidden', 'style', $this->mReq->style()) . "\n" .
				$this->mHtmlg->input('submit', 'submit', $edit ? 'Uppdatera' : 'Skapa', array('class' => 'round_btn')) . "\n" .
				$this->mHtmlg->end_form() . "\n");
	}
	
	function write()
	{
		$invite = (boolean)$this->mReq->data_scalar('invite_code');
		$invite_code = $this->mReq->data_scalar('invite_code');
		
		$user_id = $this->mReq->int_scalar('user_id');
		
		$u = new AuthUser($this->mDb, $user_id);
		$u->load();
		
		$errors = $this->validate();
		if(count($errors) > 0)
		{
			if(isset($errors['user_id']))
				header('Location: ' . NounRequest::new_from_spec('read', 'userlist', '', 'view', 'xhtml')->href());
			else
				header('Location: ' . $this->mReq->clone_extend(array('verb' => 'read', 'errors' => $errors, 'replace' => 1))->href());
			return;
		}
		
		$username = $this->mReq->data_scalar('username');
		$password_1 = $this->mReq->data_scalar('password_1');
		$super = $this->mReq->data_scalar_set('super');
		$owns_student_id = $this->mReq->int_scalar_or_null('owns_student_id');
		
		if($u->user_id() != -1)
		{
			if($password_1 != DUMMY_PASSWORD)
				$u->set_password($password_1);
			
			if(get_session_user()->super())
				$u->super($super);
			$u->owns_student_id(replace_empty($owns_student_id, -1));
			
			$groups = AuthGroup::list_groups($this->mDb);
			foreach($groups as $group_id => $group_name)
				if(!in_array($group_id, array(ALL_GROUP, USER_GROUP, SUPER_GROUP)) &&
					(get_session_user()->is_member($group_id) || get_session_user()->super()))
					if($this->mReq->data_scalar_set('group_'.$group_id))
						$u->add_membership($group_id);
					else
						$u->remove_membership($group_id);
						
			$u->commit();
		}
		elseif($this->mReq->style() == 'create' && !$invite)
		{
			$u = AuthUser::create_user($this->mDb, $username, $password_1, $super, replace_empty($owns_student_id, -1));
			$user_id = $u->user_id();
		}
		elseif($this->mReq->style() == 'create' && $invite)
		{
			$i = new AuthInvite($this->mDb, $invite_code);
			$i->load();
			
			$u = $i->use_invite($username, $password_1);
			
			$user_id = $u->user_id();
			
			if(get_session_user()->user_id() == -1)
				set_session_user_id($u->user_id());
		}
		
		if(!$invite || get_session_user()->user_id() != -1)
			header('Location: ' . NounRequest::new_from_spec('read', 'user', '', 'view', 'xhtml', array('user_id' => $user_id))->href());
		else
			header('Location: ' . NounRequest::new_from_spec('read', 'default', '', 'view', 'xhtml')->href());
	}
	
	function validate()
	{
		$errors = array();
		
		if($this->mReq->style() == 'edit')
		{
			$user_id = $this->mReq->int_scalar('user_id');
			
			$u = new AuthUser($this->mDb, $user_id);
			$u->load();
			if($u->user_id() == -1)
				$errors['user_id'] = VERR_INVALID;
		}
		
		if($this->mReq->style() == 'create')
		{
			$username = $this->mReq->data_scalar('username');
			if(empty($username))
				$errors['username'] = VERR_MISSING;
			elseif(!preg_match('/^[a-zA-Z0-9._]{3,16}$/', $username))
				$errors['username'] = VERR_INVALID;
			else
			{
				$u = AuthUser::new_from_username($this->mDb, $username);
				if($u->user_id() != -1)
					$errors['username'] = VERR_OTHER;
			}
		}
		
		$owns_student_id = $this->mReq->int_scalar_or_null('owns_student_id');
		if($owns_student_id && $owns_student_id != -1)
		{
			$s = new Student($this->mDb, $owns_student_id);
			$s->load();
			if($s->id() == -1)
				$errors['owns_student_id'] = VERR_INVALID;
			else
			{
				$u = AuthUser::new_from_student($this->mDb, $owns_student_id);
				$u->load();
				if($u->user_id() != -1 &&
					($this->mReq->style() == 'create' || $this->mReq->int_scalar('user_id') != $u->user_id()))
					$errors['owns_student_id'] = VERR_OTHER;
			}
		}
		
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
		return (($user->can_do(MANAGE_USER_PRIV) && !$this->mReq->data_scalar('invite_code')) || 
			($this->mReq->style() == 'create' && $this->mReq->data_scalar('invite_code') &&
				$this->valid_invite($this->mReq->data_scalar('invite_code'))));
	}
	
	function valid_invite($invite_code)
	{
		$i = new AuthInvite($this->mDb, $invite_code);
		$i->load();
		
		if($i->invite_code() == NULL)
			return false;
	
		if($i->owns_student_id() != -1)
		{
			$u = AuthUser::new_from_student($this->mDb, $i->owns_student_id());
			$u->load();
		
			if($u->user_id() != -1)
				return false;
		}
		
		return true;
	}
}

?>