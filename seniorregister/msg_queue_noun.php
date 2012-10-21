<?php

require_once('config.php');
	
require_once('auth.php');

require_once('constants.php');

class MessageQueueNoun extends Noun
{
	function __construct() { }
	
	function noun_header()
	{
		return '';
	}
	
	function noun_footer()
	{	
		$t = $this->mHtmlg->div('', 'group_head', 'Administration') . "\n" .
			$this->mHtmlg->begin_div('', 'group_content') . "\n" .
			$this->mHtmlg->ahref(NounRequest::new_from_spec('remove', 'messagequeue', '', '', '')->href(), 'Rensa kö') . $this->mHtmlg->newline() ."\n" .
			$this->mHtmlg->ahref(NounRequest::new_from_spec('read', 'messagesender', '', '', '')->href(), 'Automatisk utskickning') . "\n" .
			$this->mHtmlg->end_div() . "\n";
			
		return $t;
	}
	
	function read()
	{
		$q = get_message_queue();
		
		$offset = $this->mReq->int_scalar('offset', 0);
		$length = $this->mReq->int_scalar('length', 30);
		
		$l = $q->list_messages();
		$count = count($l);
		
		if($this->mReq->format() == 'xhtml')
		{
			$this->output(
				$this->mHtmlg->div('', 'group_head', 'Meddelandekö') . "\n" .
				$this->mHtmlg->begin_div('', 'group_content') . "\n");
			
			$this->output($this->mHtmlg->begin_table());
			$this->output($this->mHtmlg->begin_row() . "\n" .
				//$this->mHtmlg->hcell('Datum', array('class' => 'when')) . "\n" .
				$this->mHtmlg->hcell('Mottagare', array('class' => 'recipient')) . "\n" .
				$this->mHtmlg->hcell('Ämne', array('class' => 'subject')) ."\n".
				//$this->mHtmlg->hcell('Användare', array('class' => 'sending_user')) . "\n" .
				$this->mHtmlg->hcell('Hantera', array('class' => 'action')) . "\n" .
				$this->mHtmlg->end_row() . "\n");
		
			$i = 0;
			foreach($l as $id => $m_data)
			{			
				if($i >= $offset && $i < $offset+$length)
					$this->output(
						$this->mHtmlg->begin_row() . "\n" .
						//$this->mHtmlg->cell(htmlentities($m_data[0])) . "\n" .
						$this->mHtmlg->cell(htmlentities($m_data[1], ENT_COMPAT, 'utf-8')) . "\n" .
						$this->mHtmlg->cell($this->mHtmlg->noun_ahref(
						NounRequest::new_from_spec('read', 'message', '', 'view', 'xhtml', array('message_id' => $id)), 
						$m_data[2])) . "\n" .
						//$this->mHtmlg->cell(htmlentities($m_data[6], ENT_COMPAT, 'utf-8')) . "\n" .
						$this->mHtmlg->cell($this->mHtmlg->noun_ahref(NounRequest::new_from_spec('write', 'messagequeue', '', 'send', 'xhtml', array('message_id' => $id)), 'Skicka') . ", " .
						($this->mHtmlg->noun_ahref(NounRequest::new_from_spec('write', 'messagequeue', '', 'remove', 'xhtml', array('message_id' => $id)), 'Ta bort'))) . "\n" .
						$this->mHtmlg->end_row() . "\n");
				elseif($i >= $offset+$length)
					break;
				$i++;
			}
			
		
			$this->output($this->mHtmlg->end_table() . "\n");
		
			// stolen from ListNoun, perhaps merge?
			$this->output($this->mHtmlg->begin_div('page_list') . "\n");
	
			$default_length = 30;
			$pages = $count/$default_length;
			if($pages > 1)
				for($i = 0; $i < $pages; $i++)
					if($offset != $i*$default_length)
						$this->output($this->mHtmlg->noun_ahref($this->mReq->clone_extend(array('offset' => $i*$default_length, 'length' => $default_length)), $i+1) . "\n");
					else
						$this->output($i+1 . "\n");
	
			$this->output($this->mHtmlg->end_div() . "\n" .
						$this->mHtmlg->end_div() . "\n");
		}
		elseif($this->mReq->format() == 'json')
		{
			$this->output(json_encode($l));
		}
	}
	
	function write()
	{
		$msgq = get_message_queue();
		$msgs = get_message_sender();
		$style = $this->mReq->style();
		
		if($style == 'send')
		{
			$message_id = $this->mReq->int_scalar_or_null('message_id');
			
			if($message_id)
			{
				$msg = $msgq->get_message($message_id);
			
				if($msgs->send_message($msg))
					$msgq->remove_message($message_id);
			}
			
			if($this->mReq->format() == 'xhtml')
				header('Location: '.$this->mReq->clone_extend(array('verb' => 'read', 'style' => NULL, 'message_id' => NULL))->href());
			elseif($this->mReq->format() == 'json')
				$this->output(json_encode($message_id != NULL));
		}
		elseif($style == 'remove')
		{
			$msgq = get_message_queue();

			$message_id = $this->mReq->int_scalar_or_null('message_id');

			if($message_id)
				$msgq->remove_message($message_id);

			if($this->mReq->format() == 'xhtml')
				header('Location: '.$this->mReq->clone_extend(array('verb' => 'read', 'style' => NULL, 'message_id' => NULL))->href());
			elseif($this->mReq->format() == 'json')
				$this->output(json_encode($message_id != NULL));
		}
	}

	
	function validate()
	{
		$errors = array();
		
		return $errors;
	}
	
	function remove()
	{
		$msgq = get_message_queue();
		
		$msgq->clear();
		
		if($this->mReq->format() == 'xhtml')
			header('Location: '.$this->mReq->clone_extend(array('verb' => 'read'))->href());
		elseif($this->mReq->format() == 'json')
			$this->output(json_encode(TRUE));
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