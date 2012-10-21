<?php

require_once('config.php');
	
require_once('auth.php');

require_once('constants.php');

class MessageNoun extends Noun
{
	function __construct() { }
	
	function noun_header()
	{
		return '';
	}
	
	function noun_footer()
	{
		return '';
	}
	
	function read()
	{
		global $config;

		$edit = ($this->mReq->style() == 'edit');
		$replace = $this->mReq->data_scalar('replace');
		
		$type = $this->mReq->data_scalar('type');
		
		$message_id = $this->mReq->int_scalar_or_null('message_id');
		
		if($type == 'filter')
			$criterion = ListNoun::extract_criterion($this->mReq, $type);
		else
			$criterion = new IdCriterion($this->mReq->int_scalar_or_null('student_id'));
	
		if(!$replace)
		{	
			if($message_id != NULL)
			{
				$msgq = get_message_queue();

				$msg = $msgq->get_message($message_id);

				$recipient_string = htmlentities($msg->recipient(), ENT_COMPAT, 'utf-8');

				$values = array(
					'subject' => $msg->subject(),
					'message_body' => $msg->message());				
			}
			else
				$values = array('subject' => '', 'message_body' => '');
		}
		else
		{
			$values = array(
				'subject' => $this->mReq->data_scalar('subject'), 
				'message_body' => isset($_SESSION['message_body']) ? $_SESSION['message_body'] : '');
		}
		
		if($this->mReq->data('errors'))
			$errors = $this->mReq->data_array('errors', array());
		else
			$errors = array();

		$error_labels = array(
			'subject' => array(VERR_MISSING => 'Ämne saknas'),
			'message_body' => array(VERR_MISSING => 'Meddelande saknas', VERR_INVALID => 'Meddelandet innehåller ogiltiga nyckelord'));
			
		foreach($errors as $key => $err_code)
			$errors[$key] = $error_labels[$key][$err_code];
		foreach($values as $key => $value)
			if(!isset($errors[$key]))
				$errors[$key] = '';
			
		if($message_id == NULL)
		{
			$l = Student::list_students($this->mDb, $criterion, true);
			$count = 0;
			foreach($l as $data)
				if($data[3])
					$count++;
		
			if($count == 1)
			{
				$l = array_values($l);
				$recipient_string = '"'.$l[0][0].' '.$l[0][1].'" &lt;'.$l[0][3].'&gt;';
			}
			else
				$recipient_string = "$count studenter";
		}
			
		$this->output($this->mHtmlg->div('', 'group_head', 'Meddelande') . "\n" .
			$this->mHtmlg->begin_div('', 'group_content') . "\n");
				
		if($edit)
			$this->output($this->mHtmlg->begin_form('post', 'index.php') . "\n");
			
		$this->output($this->mHtmlg->begin_table() . "\n" .
			$this->mHtmlg->fixed_field('Från', $config['message']['from'], false) .
			$this->mHtmlg->fixed_field('Till', $recipient_string, false) .
			$this->mHtmlg->text_field('Ämne', 'subject', $values['subject'], $edit, $errors['subject']) .
			$this->mHtmlg->textarea_field('Meddelande', 'message_body', $values['message_body'], $edit, $errors['message_body']) .
			$this->mHtmlg->end_table() . "\n");
		
		
		if($edit)
			$this->output(/*$this->mHtmlg->input('hidden', 'verb', 'write') . "\n" .
				$this->mHtmlg->input('hidden', 'noun', 'message') . "\n" .
				$this->mHtmlg->input('hidden', 'style', $this->mReq->style()) . "\n" .
				$this->mHtmlg->input('hidden', 'type', $type) . "\n" .
				$this->mHtmlg->input('hidden', 'q', $this->mReq->data_scalar('q')) . "\n" .
				($this->mReq->data_scalar_set('email') ? ($this->mHtmlg->input('hidden', 'email', '1') . "\n") : '') .
				($this->mReq->data_scalar_set('force') ? ($this->mHtmlg->input('hidden', 'force', '1') . "\n") : '') .
				$this->mHtmlg->input('hidden', 'starting_year', $this->mReq->int_scalar('starting_year', -1)) . "\n" .
				$this->mHtmlg->input('hidden', 'award', $this->mReq->int_scalar('award', -1)) . "\n" .
				$this->mHtmlg->input('hidden', 'nomination', $this->mReq->int_scalar('nomination', -1)) . "\n" .*/
				$this->mReq->clone_extend(array('verb' => 'write'))->to_hidden() . 
				$this->mHtmlg->input('submit', 'submit', 'Skapa meddelelanden', array('class' => 'round_btn')) . "\n" .
				$this->mHtmlg->end_form() . "\n");
		else
			$this->output($this->mHtmlg->input('button', '', 'Ta bort', array('onclick' => 'remove_message('.$message_id.')')) . "\n" .
				$this->mHtmlg->input('button', '', 'Skicka', array('onclick' => 'send_message('.$message_id.')')) . "\n");
				
		$this->output($this->mHtmlg->end_form() . "\n" .
			$this->mHtmlg->end_div() . "\n");
			
		$tags = array(
				'student.first_name' => 'Förnamn',
				'student.last_name' => 'Efternamn',
				'student.class_year' => 'Årskurs',
				'student.street_address' => 'Gatuadress',
				'student.postal_address' => 'Postadress',
				'student.paid_until' => 'Betalat till och med',
				'student.graduation' => 'Examensår',
				'student.email' => 'E-postadress (standard)',
				'user.username' => 'Användarnamnet för den användare som är knuten till studenten',
				'invite.use_link' => '"Använd"-länken för den inbjudan som är knuten till studenten'
			);
		
		if($edit)
		{
			$this->output($this->mHtmlg->div('', 'group_head', 'Nyckelord') . "\n" .
				$this->mHtmlg->begin_div('', 'group_content') . "\n" .
				$this->mHtmlg->begin_table() . "\n");
			foreach($tags as $tag => $desc)
				$this->output($this->mHtmlg->begin_row() . 
					$this->mHtmlg->cell('{'.$tag.'}', array('class' => 'tag_name')) .
					$this->mHtmlg->cell($desc, array('class' => 'tag_description')) .
					$this->mHtmlg->end_row() . "\n");
			$this->output($this->mHtmlg->end_table() . "\n" .
				$this->mHtmlg->end_div() . "\n");
		}
	}
	
	function write()
	{
		$errors = $this->validate();
		if(count($errors) > 0)
		{
			$_SESSION['message_body'] = $this->mReq->data_scalar('message_body');
			header('Location: ' . $this->mReq->clone_extend(array('verb' => 'read', 'errors' => $errors, 'replace' => 1, 'message_body' => NULL))->href());
			return;
		}
		
		$type = $this->mReq->data_scalar('type');
		
		if($type == 'filter')
			$criterion = ListNoun::extract_criterion($this->mReq, $type);
		else
			$criterion = new IdCriterion($this->mReq->int_scalar_or_null('student_id'));
		
		$subject = $this->mReq->data_scalar('subject');
		$message = $this->mReq->data_scalar('message_body');
		
		$tmpl = $this->create_template($subject, $message);
		
		//$invalids = $tmpl->test_template($criterion);
		
		$msgs = $tmpl->fill_template($criterion);
		
		if(count($msgs) > 0)
		{	
			$msgq = get_message_queue();
			foreach($msgs as $msg)
				$msgq->enqueue($msg);
		
			//header('Location: ' . NounRequest::new_from_spec('read', 'dialog', 'message_sent', 'view', 'xhtml')->href());
			header('Location: ' . NounRequest::new_from_spec('read', 'messagequeue', '', 'view', 'xhtml')->href());
		}
		else
			header('Location: ' . NounRequest::new_from_spec('read', 'dialog', 'message_failed', 'view', 'xhtml')->href());
	}

	
	function validate()
	{
		$errors = array();
		
		$subject = $this->mReq->data_scalar('subject');
		$message = $this->mReq->data_scalar('message_body');
		
		if($subject == '')
			$errors['subject'] = VERR_MISSING;

		if($message == '')
			$errors['message_body'] = VERR_MISSING;
		else
		{
			$tmpl = $this->create_template($subject, $message);
			
			if(!$tmpl->valid())
				$errors['message_body'] = VERR_INVALID;
		}
		
		return $errors;
	}
	
	function remove()
	{
		
	}
	
	function create_template($subject, $message)
	{
		global $config;
		return new MessageTemplate($this->mDb, '"{student.first_name} {student.last_name}" <{student.email}>', $subject, $message, array('From' => $config['message']['from']));
	}
	
	function is_display()
	{
		return ($this->mReq->verb() == 'read');
	}
	
	function is_allowed($user)
	{
		return $user->can_do(SEND_MESSAGE_PRIV);
	}
}

?>