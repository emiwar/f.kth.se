<?php

require_once('auth.php');

class UserListNoun extends Noun
{
	function __construct() { }
	
	function noun_header()
	{
		$t = '';
		
		/*
		$t = $this->mHtmlg->begin_div('userlist_parameters') . 
			$this->mHtmlg->begin_form('get', 'index.php') . "\n";
		
		$t .= $this->mHtmlg->input('text', 'query', $this->mReq->data_scalar('query')) . "\n";
		
		$t .= $this->mHtmlg->input('hidden', 'noun', 'userlist') . "\n" .
			$this->mHtmlg->input('hidden', 'style', 'view') . "\n" .
			$this->mHtmlg->input('hidden', 'format', 'xhtml') . "\n" .
			$this->mHtmlg->input('submit', 'submit', 'Sök') . "\n" .
			$this->mHtmlg->end_form() . "\n" .
			$this->mHtmlg->end_div() . "\n" .
			$this->mHtmlg->begin_div('userlist_results') . "\n";
		*/
		
		return $t;
	}
	
	function noun_footer()
	{
		$t = ""; //$this->mHtmlg->end_div() . "\n";
		
		return $t;
	}
	
	function tab_array() { return array('Användare'); }
	
	function top_box()
	{
		return $this->mHtmlg->noun_ahref(NounRequest::new_from_spec('read', 'user', '', 'create', 'xhtml'), 'Ny användare', true);
	}
	
	function read()
	{
		$offset = $this->mReq->int_scalar('offset', 0);
		$length = $this->mReq->int_scalar('length', 30);
		
		$this->output($this->mHtmlg->div('', 'group_head', 'Användare') . "\n" .
			$this->mHtmlg->begin_div('userlist_group', 'group_content') . "\n");
			
		$l = AuthUser::list_users($this->mDb, '', NULL);
		$count = count($l);
		
		$this->output($this->mHtmlg->begin_table());
		$this->output($this->mHtmlg->begin_row() . "\n" .
			$this->mHtmlg->hcell('Användarnamn', array('class' => 'username')) . "\n" .
			$this->mHtmlg->hcell('Super?', array('class' => 'user_type')) . "\n" .
			$this->mHtmlg->hcell('Knuten till student', array('class' => 'owns_student_id')) ."\n".
			$this->mHtmlg->end_row() . "\n");
		$i = 0;
		foreach($l as $id => $u_data)
		{
			if($i >= $offset && $i < $offset+$length)
				$this->output(
					$this->mHtmlg->begin_row() . "\n" .
					$this->mHtmlg->cell($this->mHtmlg->noun_ahref(
					NounRequest::new_from_spec('read', 'user', '', 'view', 'xhtml', array('user_id' => $id)), 
					$u_data[0]), array('class' => 'username')) . "\n" .
					$this->mHtmlg->cell($u_data[1] ? 'Ja' : 'Nej', array('class' => 'user_type')) . "\n" .
					$this->mHtmlg->cell($u_data[2] == -1 ? '' : $this->mHtmlg->noun_ahref(NounRequest::new_from_spec('read', 'student', 'core', 'view', 'xhtml', array('student_id' => $u_data[2])), $u_data[3]), array('class' => 'owns_student_id')) . "\n" .
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