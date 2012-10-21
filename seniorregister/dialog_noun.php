<?php

require_once('auth.php');

require_once('constants.php');

$gDialogs = array(
	'remove_student' =>
		array(
			'title' => 'Ta bort student', 
			'message' => 'Är du säker på att du vill ta bort denna student?', 
			'buttons' =>
				array('yes' => 
					array(
						'caption' => 'Ja',
						'action' => NounRequest::new_from_spec('remove', 'student', '', 'view', 'xhtml')),
					'no' => 
					array(
						'caption' => 'Nej',
						'action' => NounRequest::new_from_spec('read', 'student', 'core', 'view', 'xhtml')))
		),
		'message_sent' =>
			array(
				'title' => 'Meddelandet skickat', 
				'message' => 'Meddelandet har skickats.', 
				'buttons' =>
					array('ok' => 
						array(
							'caption' => 'OK',
							'action' => NounRequest::new_from_spec('read', 'messagequeue', '', 'view', 'xhtml')))
			),
		'message_failed' =>
			array(
				'title' => 'Skickandet misslyckades', 
				'message' => 'Meddelandet kunde inte någon mottagare.', 
				'buttons' =>
					array('ok' => 
						array(
							'caption' => 'OK',
							'action' => NounRequest::new_from_spec('read', 'list', 'filter', 'view', 'xhtml')))
			)
	);

class DialogNoun extends Noun
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
		global $gDialogs;
		
		$dialog = $gDialogs[$this->mReq->section()];
		
		$this->output($this->mHtmlg->div('', 'group_head', $dialog['title']) . "\n" .
			$this->mHtmlg->begin_div('', 'group_content') . "\n" .
			$this->mHtmlg->p($dialog['message']) . "\n" .
			$this->mHtmlg->begin_form('get', 'index.php', array('id' => 'dialog_form')) . "\n" .
			$this->mHtmlg->input('hidden', 'dialog_result', '', array('id' => 'dialog_result')) . "\n" .
			$this->mHtmlg->input('hidden', 'verb', 'write') . "\n" .
			$this->mHtmlg->input('hidden', 'noun', 'dialog/'.$this->mReq->section()) . "\n");
			
		foreach($this->mReq->extra_data() as $key => $data)
			$this->output($this->mHtmlg->input('hidden', $key, $data) . "\n");
		
		foreach($dialog['buttons'] as $value => $button)
			$this->output($this->mHtmlg->input('button', $value, $button['caption'], array('onclick' => "dialog_answer(this,'$value')")));
		
		$this->output($this->mHtmlg->end_form() . "\n" .
			$this->mHtmlg->end_div() . "\n");
	}
	
	function write()
	{
		global $gDialogs;

		$dialog = $gDialogs[$this->mReq->section()];
		
		$action = $dialog['buttons'][$this->mReq->data('dialog_result')]['action']->clone_extend($this->mReq->extra_data())->clone_extend(array('dialog_result' => NULL));
		
		header('Location: ' . $action->href());
	}
	
	function validate()
	{
		return array();
	}
	
	function remove()
	{
		return '';
	}
	
	function is_display()
	{
		return ($this->mReq->verb() == 'read');
	}
	
	function is_allowed($user)
	{
		global $gDialogs;
		
		return isset($gDialogs[$this->mReq->section()]);
	}
}

?>