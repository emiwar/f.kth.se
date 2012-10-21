<?php

require_once('auth.php');

class InviteListNoun extends Noun
{
	function __construct() { }
	
	function noun_header()
	{
		$t = '';
		
		return $t;
	}
	
	function noun_footer()
	{
		$t = ""; //$this->mHtmlg->end_div() . "\n";
		
		return $t;
	}
	
	function tab_array() { return array('Inbjudan'); }
	
	function top_box()
	{
		return $this->mHtmlg->noun_ahref(NounRequest::new_from_spec('read', 'invite', '', 'create', 'xhtml'), 'Ny inbjudan', true);
	}
	
	function read()
	{
		$offset = $this->mReq->int_scalar('offset', 0);
		$length = $this->mReq->int_scalar('length', 30);
		
		$this->output(
			$this->mHtmlg->div('', 'group_head', 'Aktiva inbjudan') . "\n" .
			$this->mHtmlg->begin_div('invitelist_group', 'group_content') . "\n");
			
		$l = AuthInvite::list_invites($this->mDb, NULL);
		$count = count($l);
		
		$this->output($this->mHtmlg->begin_table());
		$this->output($this->mHtmlg->begin_row() . "\n" .
			$this->mHtmlg->hcell('Kod', array('class' => 'invite_code')) . "\n" .
			$this->mHtmlg->hcell('Super?', array('class' => 'user_type')) . "\n" .
			$this->mHtmlg->hcell('Knuten till student', array('class' => 'owns_student_id')) ."\n".
			$this->mHtmlg->hcell('Hantera', array('class' => 'action')) . "\n" .
			$this->mHtmlg->end_row() . "\n");
			
		$i = 0;
		foreach($l as $code => $i_data)
		{			
			if($i >= $offset && $i < $offset+$length)
				$this->output(
					$this->mHtmlg->begin_row() . "\n" .
					$this->mHtmlg->cell($this->mHtmlg->noun_ahref(
					NounRequest::new_from_spec('read', 'invite', '', 'view', 'xhtml', array('invite_code' => $code)), 
					$code)) . "\n" .
					$this->mHtmlg->cell($i_data[0] ? 'Ja' : 'Nej') . "\n" .
					$this->mHtmlg->cell($i_data[1] == -1 ? '' : $this->mHtmlg->noun_ahref(NounRequest::new_from_spec('read', 'student', 'core', 'view', 'xhtml', array('student_id' => $i_data[1])), $i_data[2])) . "\n" .
					$this->mHtmlg->cell($this->mHtmlg->noun_ahref(NounRequest::new_from_spec('read', 'user', '', 'create', 'xhtml', array('invite_code' => $code)), 'Använd') . ", " .
					($this->mHtmlg->noun_ahref(NounRequest::new_from_spec('write', 'invite', '', 'remove', 'xhtml', array('invite_code' => $code)), 'Ta bort'))) . "\n" .
					$this->mHtmlg->end_row() . "\n");
			elseif($i >= $offset+$length)
				break;
			$i++;
		}
		
		$this->output($this->mHtmlg->end_table() . "\n" . 
			$this->mHtmlg->end_div() . "\n");
			
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
		
		$this->output($this->mHtmlg->end_div() . "\n");
	}
	
	function write()
	{
		
	}
	
	function validate() { return ''; }
	function remove() { return ''; }
	
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