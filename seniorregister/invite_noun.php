<?php

require_once('auth.php');
require_once('utils.php');

require_once('constants.php');

class InviteNoun extends Noun
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
	
	function tab_array() { return array('Inbjudan'); }
	
	function top_box()
	{
		/*$create = ($this->mReq->style() == 'create');
		$edit = ($this->mReq->style() == 'edit');
		
		if(!$edit && !$create)
			return $this->mHtmlg->noun_ahref(
				$this->mReq->clone_extend(array('style' => 'edit', 'errors' => NULL, 'replace' => NULL)),
				'Redigera');*/
		return '';
	}
	
	function noun_footer()
	{
		$t = $this->mHtmlg->end_div() . "\n";
		
		return $t;
	}
	
	function read()
	{
		$code = $this->mReq->data_scalar('invite_code');
		
		// kan ej modifiera invites
		$edit = /*($this->mReq->style() == 'edit');*/ false;
		$create = ($this->mReq->style() == 'create');
		$replace = $this->mReq->data_scalar('replace');
		
		$type = $this->mReq->data_scalar('type');
		
		if($this->mReq->data('errors'))
			$errors = $this->mReq->data_array('errors', array());
		else
			$errors = array();
		
		if(!$create)
		{
			$i = new AuthInvite($this->mDb, $code);
			$i->load();
		}
	
		if(!$replace && !$create)
		{			
			if($i->invite_code() == '')
			{
				$this->output('Ogiltig inbjudanskod.');
				return;
			}
			
			$values = array(
				'super' => $i->super(),
				'owns_student_id' => $i->owns_student_id());
		}
		elseif(!$replace && $create)
		{
			$values = array(
				'super' => false,
				'owns_student_id' => -1);
		}
		else
		{
			if($type == 'filter')
			{
				$criterion = ListNoun::extract_criterion($this->mReq, $type);
				$values = array('super' => false,
					'owns_student_id' => $criterion);
			}
			else
			{
				$values = array(
					'super' => $this->mReq->data_scalar_set('super'),
					'owns_student_id' => $this->mReq->int_scalar_or_null('owns_student_id'));
			}
		}
			
		$error_labels = array(
			'owns_student_id' => array(VERR_INVALID => 'Ogiltigt student-id', VERR_OTHER => 'Denna student är redan Knuten till en användare/inbjudan'));
			
			
		foreach($errors as $key => $err_code)
			$errors[$key] = $error_labels[$key][$err_code];
		foreach($values as $key => $value)
			if(!isset($errors[$key]))
				$errors[$key] = '';
		
		$this->output($this->mHtmlg->div('', 'group_head', 'Inbjudan') . "\n" .
			$this->mHtmlg->begin_div('', 'group_content') . "\n");
		
		if($edit || $create)
			$this->output($this->mHtmlg->begin_form('get', 'index.php') . "\n");
		
		$this->output($this->mHtmlg->begin_table() . "\n".
			$this->mHtmlg->fixed_field('Inbjudanskod', !$create ? $code : 'N/A', false) .
			$this->mHtmlg->checkbox_field('Superanvändare', 'super', $values['super'], $edit || ($create && $type != 'filter'), $errors['super']));
		
		if($type == 'filter')
		{	
			$l = Student::list_students($this->mDb, $criterion, false);
			$count = count($l);
			$student_name = "$count studenter";
		}
		else
		{
			$s = new Student($this->mDb, $values['owns_student_id']);
			$s->load_core();
			if($s->id() != -1)
				$student_name = $s->first_name().' '.$s->last_name();
			else
				$student_name = 'Ingen';
		}
			
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
			$this->mHtmlg->field_row('Knuten till student', ($edit || ($create && $type != 'filter')) ? $student_search : $student_name, $edit || $create, $errors['owns_student_id']));
					
			/*$this->mHtmlg->text_field('Knuten till student #', 'owns_student_id', $values['owns_student_id'], $edit || $create, $errors['owns_student_id']));*/
		if(!$edit && !$create)
			$this->output($this->mHtmlg->field_row('Hantera', 
				$this->mHtmlg->noun_ahref(NounRequest::new_from_spec('read', 'user', '', 'create', 'xhtml', array('invite_code' => $code)), 'Använd') . ", " .
				$this->mHtmlg->noun_ahref(NounRequest::new_from_spec('write', 'invite', '', 'remove', 'xhtml', array('invite_code' => $code)), 'Ta bort'), false));
		$this->output($this->mHtmlg->end_table() . "\n");
		
		if($edit || $create)
			$this->output(
				/*$this->mHtmlg->input('hidden', 'invite_code', $code) . "\n" .
				$this->mHtmlg->input('hidden', 'owns_student_id', $values['owns_student_id']) . "\n" .
				$this->mHtmlg->input('hidden', 'verb', 'write') . "\n" .
				$this->mHtmlg->input('hidden', 'noun', 'invite') . "\n" .
				$this->mHtmlg->input('hidden', 'style', $this->mReq->style()) . "\n" .*/
				$this->mReq->clone_extend(array(
					'verb' => 'write',
					'owns_student_id' => NULL,
					))->to_hidden() .
				($type != 'filter' ? $this->mHtmlg->input('hidden', 'owns_student_id', $values['owns_student_id'], array('id' => 'owns_student_id')) . "\n" : '') .
				$this->mHtmlg->input('submit', 'submit', $edit ? 'Uppdatera' : 'Skapa', array('class' => 'round_btn')) . "\n" .
				$this->mHtmlg->end_form() . "\n");
				
		if($edit || $create)
			$this->output($this->mHtmlg->end_form());
				
		$this->output($this->mHtmlg->end_div());
	}
	
	function write()
	{
		$errors = $this->validate();
		if(count($errors) > 0)
		{
			if(isset($errors['invite_code']))
				header('Location: ' . NounRequest::new_from_spec('read', 'invitelist', '', 'view', 'xhtml')->href());
			else
				header('Location: ' . $this->mReq->clone_extend(array('verb' => 'read', 'errors' => $errors, 'replace' => 1))->href());
			return;
		}
		
		if($this->mReq->style() == 'create')
		{
			if($this->mReq->data_scalar('type') != 'filter')
			{
				$super = $this->mReq->data_scalar_set('super');
				$owns_student_id = $this->mReq->int_scalar_or_null('owns_student_id');
		
				$i = AuthInvite::create_invite($this->mDb, $super, replace_empty($owns_student_id, -1));
				$code = $i->invite_code();
			
				header('Location: ' . NounRequest::new_from_spec('read', 'invite', '', 'view', 'xhtml', array('invite_code' => $code))->href());
			}
			else
			{
				$criterion = ListNoun::extract_criterion($this->mReq, $this->mReq->data_scalar('type'));
				$l = Student::list_students($this->mDb, $criterion);
				foreach($l as $id => $data)
					AuthInvite::create_invite($this->mDb, false, $id);
					
				header('Location: ' . NounRequest::new_from_spec('read', 'invitelist', '', 'view', 'xhtml')->href());	
			}
		}
		elseif($this->mReq->style() == 'remove')
		{
			$this->remove();
		}
	}
	
	function validate()
	{
		$errors = array();
		
		if($this->mReq->style() == 'create')
		{
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
					if($u->user_id() != -1)
						$errors['owns_student_id'] = VERR_OTHER;
					else
					{
						$i = AuthInvite::new_from_student($this->mDb, $owns_student_id);
						$i->load();
						if($i->invite_code() != NULL)
							$errors['owns_student_id'] = VERR_OTHER;
					}
				}
			}
		}
		elseif($this->mReq->style() == 'remove')
		{
			$code = $this->mReq->data_scalar('invite_code');
			$i = new AuthInvite($this->mDb, $code);
			$i->load();
			if($i->invite_code() == NULL)
				$errors['invite_code'] = VERR_INVALID;
		}
			
		return $errors;
	}
	
	function remove()
	{
		$code = $this->mReq->data_scalar('invite_code');
		
		$i = new AuthInvite($this->mDb, $code);
		$i->load();
		$i->remove();
		
		header('Location: ' . NounRequest::new_from_spec('read', 'invitelist', '', 'view', 'xhtml')->href());
	}
	
	function is_display()
	{
		return ($this->mReq->verb() == 'read');
	}
	
	function is_allowed($user)
	{
		return ($user->can_do(MANAGE_USER_PRIV));
	}
}

?>