<?php

require_once('noun.php');
require_once('student.php');
require_once('parameters.php');

require_once('auth.php');

class ListNoun extends Noun
{
	function __construct() { }
	
	function top_box()
	{
		if($this->mReq->section() == 'filter')
			return $this->mHtmlg->noun_ahref(NounRequest::new_from_spec('read', 'student', 'core', 'create', 'xhtml'), 'Ny student', true);
		return '';
	}
	
	function tab_array() { return array($this->mReq->section() == 'filter' ? 'Lista' : 'Sök'); }
	
	function noun_header()
	{
		$section = $this->mReq->section();
		
		$t = '';
		
		$t = 
			$this->mHtmlg->div('', 'group_head', $section == 'filter' ? 'Kriterier' : 'Namn') . "\n" .
			$this->mHtmlg->begin_div('', 'group_content') . "\n" .
			$this->mHtmlg->begin_div('list_parameters') . "\n" .
			$this->mHtmlg->begin_form('get', 'index.php') . "\n";
		
		if($section == 'filter')
		{
			$p = $this->mParams;
		
			$titles = array();
			$titles[-1] = 'Ingen';
			foreach($p->title_ids() as $title_id)
				$titles[$title_id] = $p->title_name($title_id);
		
			$classes = array();
			$classes[-1] = 'Ingen';
			foreach($p->class_years() as $y)
				$classes[$y] = $p->class_abbreviation($y) . ($p->class_name($y) != '' ? (' (' . $p->class_name($y) . ')') : '');
			
			$positions = array();
			$positions[-1] = 'Ingen';
			foreach($p->position_ids() as $position_id)
				$positions[$position_id] = $p->position_name($position_id) . ' (' . $p->committee_name($p->position_committee_id($position_id)) . ')';
		
			// what if an input element could "remember" its last state on its own?
			$t .= $this->mHtmlg->checkbox('senior', '1', 'Är seniormedlem', $this->mReq->data_scalar('senior'), true, array('onclick' => "update_checkbox('senior')")) . "\n" .
				'(' . $this->mHtmlg->checkbox('senior_neg', '1', 'icke', $this->mReq->data_scalar('senior_neg'), $this->mReq->data_scalar('senior')) . ')' . $this->mHtmlg->newline() . "\n" .
				$this->mHtmlg->checkbox('email', '1', 'Har betalat för e-post', $this->mReq->data_scalar('email'), true, array('onclick' => "update_checkbox('email')")) . "\n" .
				'(' . $this->mHtmlg->checkbox('email_neg', '1', 'icke', $this->mReq->data_scalar('email_neg'), $this->mReq->data_scalar('email')) . ')' . $this->mHtmlg->newline() . "\n" .
				$this->mHtmlg->checkbox('force', '1', 'Har betalat för force', $this->mReq->data_scalar('force'), true, array('onclick' => "update_checkbox('force')")) . "\n" . 
				'(' . $this->mHtmlg->checkbox('force_neg', '1', 'icke', $this->mReq->data_scalar('force_neg'), $this->mReq->data_scalar('force')) . ')' . $this->mHtmlg->newline() . "\n" .
				$this->mHtmlg->checkbox('has_user', '1', 'Har konto', $this->mReq->data_scalar('has_user'), true, array('onclick' => "update_checkbox('has_user')")) . "\n" . 
				'(' . $this->mHtmlg->checkbox('has_user_neg', '1', 'icke', $this->mReq->data_scalar('has_user_neg'), $this->mReq->data_scalar('has_user')) . ')' . $this->mHtmlg->newline() . "\n" .
				$this->mHtmlg->checkbox('has_invite', '1', 'Är inbjuden', $this->mReq->data_scalar('has_invite'), true, array('onclick' => "update_checkbox('has_invite')")) . "\n" . 
				'(' . $this->mHtmlg->checkbox('has_invite_neg', '1', 'icke', $this->mReq->data_scalar('has_invite_neg'), $this->mReq->data_scalar('has_invite')) . ')' . $this->mHtmlg->newline() . "\n" .
				$this->mHtmlg->select('award', $titles, replace_empty($this->mReq->int_scalar_or_null('award'), -1), false) . " emeritus" . $this->mHtmlg->newline() . "\n" .
				"Årskurs: " . $this->mHtmlg->select('starting_year', $classes, replace_empty($this->mReq->int_scalar_or_null('starting_year'), -1), false) . $this->mHtmlg->newline() . "\n" .
				"Post: " . $this->mHtmlg->select('nomination', $positions, replace_empty($this->mReq->int_scalar_or_null('nomination'), -1)) . $this->mHtmlg->newline() . "\n" ;
		}
		elseif($section == 'search')
		{
			$t .= $this->mHtmlg->input('text', 'query', $this->mReq->data_scalar('query')) . "\n";
		}
		
		$t .= $this->mHtmlg->input('hidden', 'noun', 'list/' . $section) . "\n" .
			$this->mHtmlg->input('hidden', 'style', 'view') . "\n" .
			$this->mHtmlg->input('hidden', 'format', 'xhtml') . "\n" .
			$this->mHtmlg->input('submit', 'submit', $section == 'filter' ? 'Uppdatera lista' : 'Sök') . "\n" .
			$this->mHtmlg->end_form() . "\n" .
			/*
			$this->mHtmlg->ahref("#", "Show Email", array('onclick' => 'show_list_emails();')) . "\n" .
			$this->mHtmlg->ahref("#", "Hide Email", array('onclick' => 'hide_list_emails();')) . "\n" .		*/
			$this->mHtmlg->end_div() . "\n" .
			$this->mHtmlg->end_div() . "\n" .
			$this->mHtmlg->div('', 'group_head', 'Resultat') . "\n" .
			$this->mHtmlg->begin_div('', 'group_content') . "\n";
		
		return $t;
	}
	
	function noun_footer()
	{
		$t = $this->mHtmlg->end_div() . "\n" .
			$this->mHtmlg->div('', 'group_head', 'Exportera') . "\n" .
			$this->mHtmlg->begin_div('', 'group_content') . "\n" .
			$this->mHtmlg->begin_div('email_link') . "\n" .
			$this->mHtmlg->ahref('#', 'Hämta e-postadresser', array('onclick' => 'get_email_list(this);return false;')) . "\n" .
			$this->mHtmlg->end_div() . "\n" .
			$this->mHtmlg->begin_div('email_list') . "\n" .
			$this->mHtmlg->textarea('email_list_text', '', array('id' => 'email_list_text')) . "\n" .
			$this->mHtmlg->div('email_list_error', 'error', '') . "\n" .
			$this->mHtmlg->end_div() . "\n" .
			$this->mHtmlg->end_div() . "\n";
		if($this->mReq->section() == 'filter')
			$t .= $this->mHtmlg->div('', 'group_head', 'Administration') . "\n" .
				$this->mHtmlg->begin_div('', 'group_content') . "\n" .
				$this->mHtmlg->ahref($this->mReq->clone_extend(array('noun' => 'message', 'section' => NULL, 'style' => 'edit', 'type' => $this->mReq->section()))->href(), 'Skicka ett meddelande') . $this->mHtmlg->newline() . "\n" .
				$this->mHtmlg->ahref($this->mReq->clone_extend(array('noun' => 'invite', 'section' => NULL, 'style' => 'create', 'type' => $this->mReq->section(), 'replace' => '1'))->href(), 'Bjud in alla') . "\n" .
				$this->mHtmlg->end_div() . "\n";
		
		return $t;
	}
	
	function read()
	{
		$section = $this->mReq->section();
		$format = $this->mReq->format();
		
		$offset = $this->mReq->int_scalar('offset', 0);
		$length = $this->mReq->int_scalar('length', 30);
		
		$p = $this->mParams;
		
		$criterion = ListNoun::extract_criterion($this->mReq, $section);
			
		$l = Student::list_students($this->mDb, $criterion, true);
		$count = count($l);
		
		//$this->output("<!-- $count -->\n");
		
		// note: must do uasort. otherwise the js search function will break since 
		// it relies on id => value arrays.
		uasort($l, create_function('$a,$b', 'return strcmp(strtolower($a[1]), strtolower($b[1]));'));
		
		if($format == 'xhtml')
		{
			$this->output($this->mHtmlg->begin_div('student_list') . "\n");
		
			$i = 0;
			foreach($l as $id => $s_data)
			{
				if($i >= $offset && $i < $offset+$length)
					$this->output($this->mHtmlg->noun_ahref(
						NounRequest::new_from_spec('read', 'student', 'core', 'view', 'xhtml', array('student_id' => $id)), 
						$s_data[0] . " " . $s_data[1] . ($s_data[2] ? (' (' . $p->class_abbreviation($s_data[2]) . ')') : '')) . $this->mHtmlg->newline() .  "\n");
				elseif($i >= $offset+$length)
					break;
				$i++;
			}
			
			if($i == 0)
				$this->output("Inga studenter funna.");
		
			$this->output($this->mHtmlg->end_div() . "\n");
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
		elseif($format == 'json')
		{
			$this->output(json_encode($l));
		}
	}
	
	function write()
	{
		return '';
	}
	
	function validate() { return ''; }
	function remove() { return ''; }
	
	function is_display()
	{
		return ($this->mReq->verb() == 'read');
	}
	
	function is_allowed($user)
	{
		return ($this->mReq->section() == 'filter' &&
			$user->can_do(VIEW_CORE_PRIV) &&
			$user->can_do(VIEW_CONTACT_PRIV) &&
			$user->can_do(VIEW_AWARD_PRIV) &&
			$user->can_do(VIEW_NOMINATION_PRIV) &&
			$user->can_do(VIEW_MEMBERSHIP_PRIV)) ||
			($this->mReq->section() == 'search' &&
			$user->can_do(VIEW_CORE_PRIV));
	}
	
	function extract_criterion($req, $type)
	{
		if($type == 'search' &&
			$req->data_scalar_set('query'))
			$criterion = new NameCriterion($req->data_scalar('query'));
		elseif($type == 'search')
			$criterion = new NoneCriterion();
		else
			$criterion = new AnyCriterion();
		
		if($req->data_scalar_set('senior'))
			if(!$req->data_scalar_set('senior_neg'))
				$criterion = new AndCriterion($criterion, new IsSeniorCriterion());
			else
				$criterion = new AndCriterion($criterion, new NotCriterion(new IsSeniorCriterion()));
		if($req->data_scalar_set('email'))
			if(!$req->data_scalar_set('email_neg'))
				$criterion = new AndCriterion($criterion, new WantsEmailCriterion());
			else
				$criterion = new AndCriterion($criterion, new NotCriterion(new WantsEmailCriterion()));
		if($req->data_scalar_set('force'))
			if(!$req->data_scalar_set('force_neg'))
				$criterion = new AndCriterion($criterion, new WantsForceCriterion());
			else
				$criterion = new AndCriterion($criterion, new NotCriterion(new WantsForceCriterion()));
		if($req->data_scalar_set('has_user'))
			if(!$req->data_scalar_set('has_user_neg'))
				$criterion = new AndCriterion($criterion, new HasUserCriterion());
			else
				$criterion = new AndCriterion($criterion, new NotCriterion(new HasUserCriterion()));
		if($req->data_scalar_set('has_invite'))
			if(!$req->data_scalar_set('has_invite_neg'))
				$criterion = new AndCriterion($criterion, new HasInviteCriterion());
			else
				$criterion = new AndCriterion($criterion, new NotCriterion(new HasInviteCriterion()));
		if($req->data_numeric('starting_year') &&
			$req->int_scalar('starting_year') != -1)
			$criterion = new AndCriterion($criterion, new StartingYearCriterion($req->int_scalar('starting_year')));
		if($req->data_numeric('award') &&
			$req->int_scalar('award') != -1)
			$criterion = new AndCriterion($criterion, new AwardCriterion($req->int_scalar('award')));
		if($req->data_numeric('nomination') &&
			$req->int_scalar('nomination') != -1)
			$criterion = new AndCriterion($criterion, new NominationCriterion($req->int_scalar('nomination')));
			
		return $criterion;
	}
}

?>