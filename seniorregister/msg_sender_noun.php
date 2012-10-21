<?php

require_once('config.php');
	
require_once('auth.php');

require_once('constants.php');

class MessageSenderNoun extends Noun
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
		$q = get_message_queue();
		
		$l = $q->list_messages();
		$count = count($l);
		
		$this->output(
			$this->mHtmlg->div('', 'group_head', 'Automatisk utskickning') . "\n" .
			$this->mHtmlg->begin_div('', 'group_content') . "\n" .
			$this->mHtmlg->span('messages_remaining', '', $count) . " meddelanden kvar att skicka.\n" .
			$this->mHtmlg->end_div() . "\n");
	}
	
	function write()
	{
		$q = get_message_queue();
		$s = get_message_sender();
		
		$s->send_next_message($q);
		
		$l = $q->list_messages();
		$count = count($l);
		
		if($this->mReq->format() == 'xhtml')
			header('Location: '.$this->mReq->clone_extend(array('verb' => 'read'))->href());
		else
			$this->output(json_encode($count));
	}
	
	function validate()
	{
		$errors = array();
		
		return $errors;
	}
	
	function remove()
	{
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