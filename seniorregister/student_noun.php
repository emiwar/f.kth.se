<?php

require_once('noun.php');
require_once('student.php');
require_once('parameters.php');

require_once('auth.php');

require_once('constants.php');

class StudentNoun extends Noun
{
	var $mTopBox;
	
	function __construct()
	{ }
	
	function top_box()
	{
		$style = $this->mReq->style();	
		$edit_link = $this->mHtmlg->noun_ahref(
			$this->mReq->clone_extend(array('style' => 'edit', 'errors' => NULL, 'replace' => NULL)), 'Redigera', true);
		if($style != 'edit' && $edit_link)
			return $edit_link;
		return '';
	}
	
	function tab_array()
	{
		$section = $this->mReq->section();
		
		$sections = array('core' => 'Personuppgifter', 
			'contact' => 'Kontaktinformation', 
			'awards' => 'Utnämningar', 
			'nominations' => 'Nomineringar', 
			'memberships' => 'Medlemskap');
			
		$tabs = array();
		foreach($sections as $current_section => $caption)
		{
			if($section !== $current_section)
				$tabs[] = $this->mHtmlg->noun_ahref(
					$this->mReq->clone_extend(array('section' => $current_section, 'style' => replace_if($this->mReq->style(), 'edit', 'view'), 'errors' => NULL, 'replace' => NULL)),
					$caption);
			else
				$tabs[] = $caption;
		}
		
		return $tabs;
	}
	
	function tab_selected()
	{
		return array_search($this->mReq->section(),
			array('core', 'contact', 'awards', 'nominations', 'memberships'));
	}
	
	function noun_header()
	{	
		$verb = $this->mReq->verb();
		$noun = $this->mReq->noun();
		$section = $this->mReq->section();
		$style = $this->mReq->style();
		$format = $this->mReq->format();
		
		$id = $this->mReq->int_scalar('student_id', -1);
		
		$t = '';
		$t .= $this->mHtmlg->begin_div('section') . "\n";
		
		return $t;
	}
	
	function noun_footer()
	{
		return $this->mHtmlg->end_div() . "\n";
	}
	
	function table_noun($type, $option_name, $text_name, $options, $strings, $rows, $max, $edit, $row_errors)
	{
		$this->output($this->mHtmlg->begin_table(array('id' => $type.'s')) . "\n" .
				$this->mHtmlg->begin_row() .
				$this->mHtmlg->hcell($strings['option'], array('class' => 'field_option '.$type.'_'.$option_name)) .
				$this->mHtmlg->hcell($strings['text'], array('class' => 'field_text '.$type.'_'.$text_name)) .
				$this->mHtmlg->hcell('', array('class' => 'field_error hidden')) .
				$this->mHtmlg->end_row() . "\n");
		
		$this->output($this->mHtmlg->row($this->mHtmlg->cell("Inga ${strings['object']} registrerade.", array('colspan' => 3, 'id' => 'no_'.$type, 'class' => (count($rows) > 0 ? 'hidden' : '')))) . "\n");
		
		foreach($rows as $i => $tuple)
		{
			$id = $tuple[0];
			$text_value = $tuple[1];
			
			$this->output($this->mHtmlg->begin_row(array('id' => "${type}_$i")) . "\n");
			if($edit)
			{
				$this->output($this->mHtmlg->cell($this->mHtmlg->select($type.'_'.$i.'_'.$option_name, $options, $id, false), array('class' => 'field_option '.$type.'_'.$option_name)) .
					$this->mHtmlg->cell($this->mHtmlg->input('text', $type.'_'.$i.'_'.$text_name, $text_value) . "\n" .
					$this->mHtmlg->input('button', '', 'Ta bort', array('onclick' => 'remove_erow(\''.$type.'\',this)')), array('class' => 'field_text '.$type.'_'.$text_name)));
			}
			else
				$this->output($this->mHtmlg->cell($options[$id], array('class' => 'field_option '.$type.'_'.$option_name)) . $this->mHtmlg->cell($text_value, array('class' => 'field_text '.$type.'_'.$text_name)) . "\n");
			if(isset($row_errors[$i]) && $row_errors[$i])
				$this->output($this->mHtmlg->cell($this->mHtmlg->span('', 'error', $row_errors[$i]), array('class' => 'field_error')) . "\n");
			else
				$this->output($this->mHtmlg->cell('', 	array('class' => 'field_error')) . "\n");
			$this->output($this->mHtmlg->end_row() . "\n");
		
			$i++;
		}
		
		$this->output($this->mHtmlg->end_table() . "\n");
		
		if($edit)
			$this->output($this->mHtmlg->input('hidden', 'max_'.$type, $max, array('id' => 'max_'.$type)) . "\n" . 
				$this->mHtmlg->input('button', '', 'Lägg till', array('onclick' => 'add_erow(\''.$type.'\')')) .
				$this->mHtmlg->newline() . "\n");
	}
	
	function read()
	{
		$verb = $this->mReq->verb();
		$noun = $this->mReq->noun();
		$section = $this->mReq->section();
		$style = $this->mReq->style();
		$format = $this->mReq->format();
		
		$id = $this->mReq->int_scalar('student_id', -1);
		
		$p = $this->mParams;
		
		$s = new Student($this->mDb, $id);
		
		$edit = ($style == 'edit');
		$create = ($style == 'create');
		$replace = $this->mReq->data_scalar('replace');
		
		$edit_payment = (($edit || $create) && (get_session_user()->can_do(EDIT_PAYMENT_PRIV) || 
			(get_session_user()->owns_student_id() == $this->mReq->int_scalar('student_id') &&
			get_session_user()->can_do(EDIT_OWN_PAYMENT_PRIV))));
		
		/*$this->output($this->mHtmlg->begin_div('style_list') . "\n");
		if($edit)
			$this->output('[edit] ' .
				$this->mHtmlg->noun_ahref(
					$this->mReq->clone_extend(array('style' => 'view', 'errors' => NULL, 'replace' => NULL)),
					'[view]'));
		else
			$this->output($this->mHtmlg->noun_ahref(
				$this->mReq->clone_extend(array('style' => 'edit', 'errors' => NULL, 'replace' => NULL)),
				'[edit]') .
				' [view]');
		$this->output($this->mHtmlg->end_div() . "\n");*/
				
		if($this->mReq->data('errors'))
			$errors = $this->mReq->data_array('errors', array());
		else
			$errors = array();
		
		$this->output($this->mHtmlg->begin_div('fields') . "\n");
		
		if($edit || $create)
			$this->output($this->mHtmlg->begin_form('get', 'index.php'));
		
		if($section == 'core')
		{			
			$labels = array(
				'id' => 'Student #', 
				'first_name' => 'Förnamn', 
				'last_name' => 'Efternamn', 
				'birth_year' => 'Födelseår', 
				'graduation_year' => 'Examensår', 
				'starting_year' => 'Årskurs', 
				'username' => 'Användarnamn', 
				'spec_id' => 'Inriktning', 
				'street_address' => 'Gatuadress', 
				'postal_address' => 'Postadress', 
				'work' => 'Arbete', 
				'misc' => 'Övrigt', 
				'has_paid_until' => 'Betalat till och med', 
				'is_senior' => 'Seniormedlem', 
				'wants_force' => 'Vill ha Force', 
				'wants_email' => 'Vill ha e-post', 
				'last_updated' => 'Senast uppdaterad');
				
			$error_labels = array(
				'first_name' => array(VERR_MISSING => 'Förnamn saknas'),
				'last_name' => array(VERR_MISSING => 'Efternamn saknas'),
				'birth_year' => array(VERR_INVALID => 'Ogiltigt födelseår'),
				'graduation_year' => array(VERR_INVALID => 'Ogiltigt examensår'),
				'starting_year' => array(VERR_INVALID => 'Ogiltig årskurs'),
				'spec_id' => array(VERR_INVALID => 'Ogiltig inriktning'),
				'has_paid_until' => array(VERR_INVALID => 'Ogiltigt datum (YYYY)'));
				
			if(!$replace && !$create)
			{
				$s->load_core();
				$values = array(
					'id' => $s->id(),
					'first_name' => $s->first_name(),
					'last_name' => $s->last_name(),
					'birth_year' => $s->birth_year(),
					'graduation_year' => $s->graduation_year(),
					'starting_year' => replace_empty($s->starting_year(), -1),
					'username' => $s->username(),
					'spec_id' => replace_empty($s->specialization_id(), -1),
					'work' => $s->work(),
					'misc' => $s->miscellaneous(),
					'has_paid_until' => $s->has_paid_until(),
					'is_senior' => $s->is_senior_member(),
					'wants_force' => $s->wants_force(),
					'wants_email' => $s->wants_email());
			}
			elseif(!$replace && $create)
			{	
				$values = array(
					'id' => -1,
					'first_name' => '',
					'last_name' => '',
					'birth_year' => '',
					'graduation_year' => '',
					'starting_year' => -1,
					'username' => '',
					'spec_id' => -1,
					'work' => '',
					'misc' => '',
					'has_paid_until' => '',
					'is_senior' => false,
					'wants_force' => false,
					'wants_email' => false);
			}
			else
			{
				$values = $this->request_core_values();
				if(!$edit_payment)
				{
					$s->load_core();
					$values = array_merge(
						$values,
						array(
							'has_paid_until' => $s->has_paid_until(),
							'is_senior' => $s->is_senior_member(),
							'wants_force' => $s->wants_force(),
							'wants_email' => $s->wants_email()));
				}
			}
			
			/*if($edit)
			{	
				$r_values = $this->request_core_values();
				
				foreach($r_values as $key => $value)
					if(!is_null($value))
						$values[$key] = $value;
			}*/
			
			foreach($errors as $key => $err_code)
				$errors[$key] = $error_labels[$key][$err_code];
			foreach($values as $key => $value)
				if(!isset($errors[$key]))
					$errors[$key] = '';
			
			$classes = array();
			$classes[-1] = 'N/A';
			foreach($p->class_years() as $y)
				$classes[$y] = $p->class_abbreviation($y) . ($p->class_name($y) != '' ? (' (' . $p->class_name($y) . ')') : '');
			
			$specs = array();
			$specs[-1] = 'N/A';
			foreach($p->specialization_ids() as $spec_id)
				$specs[$spec_id] = $p->specialization_name($spec_id);
				
			$this->output($this->mHtmlg->div('', 'group_head', 'Medlem') . "\n" .
				$this->mHtmlg->begin_div('', 'group_content') . "\n");
				
			$this->output($this->mHtmlg->begin_table() . "\n" .
				/*$this->mHtmlg->fixed_field($labels['id'], $s->id(), false) .*/
				$this->mHtmlg->text_field($labels['first_name'], 'first_name', $values['first_name'], $edit || $create, $errors['first_name']) .
				$this->mHtmlg->text_field($labels['last_name'], 'last_name', $s->last_name(), $edit || $create, $errors['last_name']) .
				$this->mHtmlg->text_field($labels['birth_year'], 'birth_year', $s->birth_year(), $edit || $create, $errors['birth_year']) .
				$this->mHtmlg->end_table() . "\n");
				
			$this->output($this->mHtmlg->end_div() . "\n" .
				$this->mHtmlg->div('', 'group_head', 'Studier') . "\n" .
				$this->mHtmlg->begin_div('', 'group_content') . "\n");			
					
			$this->output($this->mHtmlg->begin_table() . "\n" .
				$this->mHtmlg->text_field($labels['graduation_year'], 'graduation_year', $s->graduation_year(), $edit || $create, $errors['graduation_year']) .
				$this->mHtmlg->list_field($labels['starting_year'], 'starting_year', $classes, replace_empty($s->starting_year(), -1), $edit || $create, $errors['starting_year']) .
				$this->mHtmlg->text_field($labels['username'], 'username', $s->username(), $edit || $create, $errors['username']) .
				$this->mHtmlg->list_field($labels['spec_id'], 'spec_id', $specs, replace_empty($s->specialization_id(), -1), $edit || $create, $errors['spec_id']) .
				$this->mHtmlg->end_table() . "\n");
				
			$this->output($this->mHtmlg->end_div() . "\n" .
				$this->mHtmlg->div('', 'group_head', 'Seniormedlemskap') . "\n" .
				$this->mHtmlg->begin_div('', 'group_content') . "\n");
			$this->output($this->mHtmlg->begin_table() . "\n" .
				$this->mHtmlg->text_field($labels['has_paid_until'], 'has_paid_until', $s->has_paid_until(), $edit_payment, $errors['has_paid_until']) . 
				$this->mHtmlg->checkbox_field($labels['is_senior'], 'is_senior', $s->is_senior_member(), $edit_payment, $errors['is_senior']) .
				$this->mHtmlg->checkbox_field($labels['wants_force'], 'wants_force', $s->wants_force(), $edit_payment, $errors['wants_force']) .
				$this->mHtmlg->checkbox_field($labels['wants_email'], 'wants_email', $s->wants_email(), $edit_payment, $errors['wants_email']) .
				$this->mHtmlg->end_table() . "\n");
				
			$this->output($this->mHtmlg->end_div() . "\n" .
				$this->mHtmlg->div('', 'group_head', 'Övrigt') . "\n" .
				$this->mHtmlg->begin_div('', 'group_content') . "\n");
			$this->output($this->mHtmlg->begin_table() . "\n" .
				$this->mHtmlg->text_field($labels['work'], 'work', $s->work(), $edit || $create, $errors['work']) .
				$this->mHtmlg->text_field($labels['misc'], 'misc', $s->miscellaneous(), $edit || $create, $errors['misc']) .
				$this->mHtmlg->fixed_field($labels['last_updated'], $s->last_updated(), false) .
				$this->mHtmlg->end_table() . "\n");
			
			$this->output($this->mHtmlg->end_div() . "\n");
		}
		elseif($section == 'contact')
		{
			$s->load_core();
			$s->load_emails();
			$s->load_telephones();
			
			$types = array();
			foreach($p->telephone_type_ids() as $type_id)
				$types[$type_id] = $p->telephone_type_name($type_id);
				
			// bug(?): standard_email_address() returns first address if none is standard
			/*$this->output($this->mHtmlg->begin_div('email_template', 'hidden') . "\n" .
				$this->mHtmlg->input('radio', 'standard_email', '{i}') . "\n" .
				$this->mHtmlg->input('text', 'email_{i}' . '_text', '') . "\n" .
				$this->mHtmlg->input('button', '', 'Remove', array('onclick' => 'remove_row_i(\'email\',this)')) . "\n" .
				$this->mHtmlg->end_div() . "\n");*/
				
			if(!$replace)
			{
				$email_addresses = array();
				$i = 1;
				$standard_email = -1;
				foreach($s->email_addresses() as $email)
				{
					$email_addresses[$i] = $email;
					if($s->standard_email_address() == $email)
						$standard_email = $i;
					$i++;
				}
				$telephone_numbers = array();
				foreach($types as $type_id => $type_name)
					$telephone_numbers[$type_id] = $s->telephone_number($type_id);
				$contact_values = array(
					'email_addresses' => $email_addresses, 
					'standard_email' => $standard_email, 
					'max_email' => $i, 
					'street_address' => $s->street_address(),
					'postal_address' => $s->postal_address(),
					'telephone_numbers' => $telephone_numbers);
			}
			else
				$contact_values = $this->request_contact_values();
			
			$email_addresses = $contact_values['email_addresses'];
			$standard_email = $contact_values['standard_email'];
			$max_email = $contact_values['max_email'];
			$telephone_numbers = $contact_values['telephone_numbers'];
			
			foreach($errors as $key => $err_code)
			{
				if(substr($key, 0, 6) == 'email_')
				{
					if($err_code == VERR_INVALID)
						$errors[$key] = 'Ogiltig e-mailaddress';
					else
						$errors[$key] = '';
				}
				elseif($key == 'standard_email')
				{
					$errors[$key] = 'Ingen standardaddress vald';
				}
				elseif(substr($key, 0, 4) == 'tel_')
				{
					if($err_code == VERR_INVALID)
						$errors[$key] = 'Ogiltigt telefonnummer';
					else
						$errors[$key] = '';
				}
			}

			$this->output($this->mHtmlg->div('', 'group_head', 'E-postadresser') . "\n" .
				$this->mHtmlg->begin_div('', 'group_content') . "\n");
			
			$this->output($this->mHtmlg->begin_table(array('id' => 'emails')) . "\n" .
				$this->mHtmlg->begin_row() .
				$this->mHtmlg->hcell('Standard', array('class' => 'standard_email')) .
				$this->mHtmlg->hcell('E-postadress', array('class' => 'email_text')) .
				$this->mHtmlg->hcell('', array('class' => 'field_error hidden')) .
				$this->mHtmlg->end_row() . "\n");
				
			$this->output($this->mHtmlg->row($this->mHtmlg->cell("Ingen e-postadress registrerad.", array('colspan' => 3, 'id' => 'no_email', 'class' => (count($email_addresses) > 0 ? 'hidden' : '')))) . "\n");
				
			foreach($email_addresses as $i => $email)
			{
				$this->output($this->mHtmlg->begin_row(array('id' => "email_$i")) . "\n" .
					$this->mHtmlg->cell(
						$this->mHtmlg->input('radio', 'standard_email', $i, 
							array('checked' => ($i == $standard_email) ? 'checked' : '',
								'disabled' => $edit ? '' : 'disabled')),
						array('class' => 'standard_email')) . "\n" .
					$this->mHtmlg->begin_cell(array('class' => 'email_text')));
				if($edit)
				{
					$this->output($this->mHtmlg->input('text', 'email_' . $i . '_text', $email) .
						$this->mHtmlg->input('button', '' . $i, 'Ta bort', array('onclick' => 'remove_erow(\'email\', this)')) . "\n");
				}
				else
				{
					$this->output($email);
				}
				$this->output($this->mHtmlg->end_cell() . "\n" .
					$this->mHtmlg->begin_cell(array('class' => 'field_error')));
				if(isset($errors['email_'.$i.'_text']))
					$this->output($this->mHtmlg->span('', 'error', $errors['email_'.$i.'_text']));
				$this->output($this->mHtmlg->end_cell() . "\n");
			}
				
			$this->output($this->mHtmlg->end_table() . "\n");
			
			if($edit)
				$this->output($this->mHtmlg->input('button', '', 'Lägg till', array('id' => 'email_add', 'onclick' => 'add_erow(\'email\')')) . 
					$this->mHtmlg->newline() . "\n");

			$this->output($this->mHtmlg->input('hidden', 'max_email', $max_email, array('id' => 'max_email')));
			
			if(isset($errors['standard_email']))
				$this->output($this->mHtmlg->span('', 'error', $errors['standard_email']));
					
			$this->output($this->mHtmlg->end_div() . "\n" .
				$this->mHtmlg->div('', 'group_head', 'Adress') . "\n" .
				$this->mHtmlg->begin_div('', 'group_content') . "\n");
					
			$this->output($this->mHtmlg->begin_table() . "\n" .
				$this->mHtmlg->text_field('Gatuadress', 'street_address', $contact_values['street_address'], $edit, isset($errors['street_address']) ? $errors['street_address'] : '') .
				$this->mHtmlg->text_field('Postadress', 'postal_address', $contact_values['postal_address'], $edit, isset($errors['postal_address']) ? $errors['postal_address'] : '') .
				$this->mHtmlg->end_table() . "\n");
			
			$this->output($this->mHtmlg->end_div() . "\n" .
				$this->mHtmlg->div('', 'group_head', 'Telefon') . "\n" .
				$this->mHtmlg->begin_div('', 'group_content') . "\n");
			
			$this->output($this->mHtmlg->begin_table() . "\n");
			foreach($types as $type_id => $type_name)
				$this->output($this->mHtmlg->text_field($type_name, 'tel_' . $type_id, $telephone_numbers[$type_id], $edit, isset($errors['tel_'.$type_id]) ? $errors['tel_'.$type_id] : ''));
			$this->output($this->mHtmlg->end_table() . "\n");
			
			$this->output($this->mHtmlg->end_div() . "\n");
		}
		elseif($section == 'awards')
		{
			$s->load_awards();
			
			$titles = array();
			foreach($p->title_ids() as $title_id)
				$titles[$title_id] = $p->title_name($title_id);
				
			if(!$replace)
			{
				$awards = array();
				$i = 1;
				foreach($s->title_ids() as $title_id)
					$awards[$i++] = array($title_id, $s->award_year($title_id));
			}
			else
				$awards = $this->request_table_values('award', 'title', 'year');
				
			$row_errors = array();
			foreach($awards as $i => $tuple)
			{
				$row_errors[$i] = array();
				if(isset($errors["award_${i}_title"]) &&
					$errors["award_${i}_title"] == VERR_INVALID)
					$row_errors[$i][] = 'Ogiltig titel';
				if(isset($errors["award_${i}_year"]) &&
					$errors["award_${i}_year"] == VERR_INVALID)
					$row_errors[$i][] = 'Ogiltigt år (YYYY)';
				$row_errors[$i] = implode(', ', $row_errors[$i]);
			}
			
			
			$this->output(
				$this->mHtmlg->div('', 'group_head', 'Utnämningar') . "\n" .
				$this->mHtmlg->begin_div('', 'group_content') . "\n");
				
			$this->table_noun('award', 'title', 'year', $titles, array('option' => 'Titel', 'text' => 'År', 'object' => 'utnämningar'), $awards, count($awards), $edit, $row_errors);
			
			$this->output($this->mHtmlg->end_div() . "\n");
			/*
			$this->output($this->mHtmlg->begin_div('awards'));
			
			$i = 1;
			foreach($s->title_ids() as $title_id)
			{
				$this->output($this->mHtmlg->begin_div("award_$i") . "\n");
				if($edit)
				{
					$this->output($this->mHtmlg->select('award_' . $i . '_title', $titles, $title_id, false) .
						$this->mHtmlg->input('text', 'award_' . $i . '_year', $s->award_year($title_id)) . "\n" .
						$this->mHtmlg->input('button', '', 'Remove', array('onclick' => 'remove_row(\'award\',this)')) .
						$this->mHtmlg->newline() . "\n");
				}
				else
					$this->output($titles[$title_id] . ' ' . $s->award_year($title_id) . 
						$this->mHtmlg->newline() . "\n");
				$this->output($this->mHtmlg->end_div() . "\n");
				
				$i++;
			}
			if(count($s->title_ids()) == 0 && !$edit)
				$this->output("Inga utnämningar registrerade." . $this->mHtmlg->newline() . "\n");
			
			$this->output($this->mHtmlg->end_div() . "\n");
			
			if($edit)
				$this->output($this->mHtmlg->input('hidden', 'max_award', $i, array('id' => 'max_award')) . "\n" . 
					$this->mHtmlg->input('button', '', 'Add', array('onclick' => 'add_row(\'award\')')) .
					$this->mHtmlg->newline() . "\n");
			*/
		}
		elseif($section == 'nominations')
		{
			$s->load_nominations();
			
			$positions = array();
			foreach($p->position_ids() as $position_id)
				$positions[$position_id] = $p->position_name($position_id) . ' (' . $p->committee_name($p->position_committee_id($position_id)) . ')';
				
			if(!$replace)
			{
				$nominations = array();
				$i = 1;
				foreach($s->position_ids() as $position_id)
					foreach($s->nomination_years($position_id) as $year)
						$nominations[$i++] = array($position_id, $year);
			}
			else
				$nominations = $this->request_table_values('nomination', 'position', 'year');
			
			$row_errors = array();
			foreach($nominations as $i => $tuple)
			{
				$row_errors[$i] = array();
				if(isset($errors["nomination_${i}_position"]) &&
					$errors["nomination_${i}_position"] == VERR_INVALID)
					$row_errors[$i][] = 'Ogiltig post';
				if(isset($errors["nomination_${i}_year"]) &&
					$errors["nomination_${i}_year"] == VERR_INVALID)
					$row_errors[$i][] = 'Ogiltigt år (YYYY)';
				$row_errors[$i] = implode(', ', $row_errors[$i]);
			}
			
			$this->output(
				$this->mHtmlg->div('', 'group_head', 'Nomineringar') . "\n" .
				$this->mHtmlg->begin_div('', 'group_content') . "\n");
			
			$this->table_noun('nomination', 'position', 'year', $positions, array('option' => 'Post', 'text' => 'År', 'object' => 'nomineringar'), $nominations, count($nominations), $edit, $row_errors);
			
			$this->output($this->mHtmlg->end_div() . "\n");

			/*
			$this->output($this->mHtmlg->begin_div('nominations'));
			
			$i = 1;
			foreach($s->position_ids() as $position_id)
			{
				foreach($s->nomination_years($position_id) as $year)
				{
					$this->output($this->mHtmlg->begin_div("nomination_$i") . "\n");
					if($edit)
					{
						$this->output($this->mHtmlg->select('nomination_' . $i . '_position', $positions, $position_id, false) .
							$this->mHtmlg->input('text', 'nomination_' . $i . '_year', $year) . "\n" .
							$this->mHtmlg->input('button', '', 'Remove', array('onclick' => 'remove_row(\'nomination\',this)')) .
							$this->mHtmlg->newline() . "\n");
					}
					else
						$this->output($positions[$position_id] . ' ' . $year .
							$this->mHtmlg->newline() . "\n");
					$this->output($this->mHtmlg->end_div() . "\n");
				
					$i++;
				}
			}
			if(count($s->position_ids()) == 0 && !$edit)
				$this->output("Inga nomineringar registrerade." . $this->mHtmlg->newline() . "\n");
			
			$this->output($this->mHtmlg->end_div() . "\n");
			
			if($edit)
				$this->output($this->mHtmlg->input('hidden', 'max_nomination', $i, array('id' => 'max_nomination')) . "\n" .
					$this->mHtmlg->input('button', '', 'Add', array('onclick' => 'add_row(\'nomination\')')) . 
					$this->mHtmlg->newline() . "\n");
			*/
		}
		elseif($section == 'memberships')
		{
			$s->load_memberships();

			$committees = array();
			foreach($p->committee_ids() as $committee_id)
				$committees[$committee_id] = $p->committee_name($committee_id) . ' (' . $p->committee_abbreviation($committee_id) . ')';
			//$committees[100] = "HAHA";
			
			if(!$replace)
			{
				$memberships = array();
				$i = 1;
				foreach($s->group_ids() as $group_id)
					foreach($s->membership_years($group_id) as $year)
						$memberships[$i++] = array($group_id, $year);
			}
			else
				$memberships = $this->request_table_values('membership', 'committee', 'year');
			
			$row_errors = array();
			foreach($memberships as $i => $tuple)
			{
				$row_errors[$i] = array();
				if(isset($errors["membership_${i}_committee"]) &&
					$errors["membership_${i}_committee"] == VERR_INVALID)
					$row_errors[$i][] = 'Ogiltig nämnd';
				if(isset($errors["membership_${i}_year"]) &&
					$errors["membership_${i}_year"] == VERR_INVALID)
					$row_errors[$i][] = 'Ogiltigt år (YYYY)';
				$row_errors[$i] = implode(', ', $row_errors[$i]);
			}
			
			$this->output(
				$this->mHtmlg->div('', 'group_head', 'Nämndmedlemskap') . "\n" .
				$this->mHtmlg->begin_div('', 'group_content') . "\n");
			
			$this->table_noun('membership', 'committee', 'year', $committees, array('option' => 'Nämnd', 'text' => 'År', 'object' => 'medlemskap'), $memberships, count($memberships), $edit, $row_errors);
			
			$this->output($this->mHtmlg->end_div() . "\n");
			
			/*
			$this->output($this->mHtmlg->begin_div('memberships'));
			
			$i = 1;
			foreach($s->group_ids() as $group_id)
			{
				foreach($s->membership_years($group_id) as $year)
				{
					$this->output($this->mHtmlg->begin_div("membership_$i") . "\n");
					if($edit)
					{
						$this->output($this->mHtmlg->select('membership_' . $i . '_committee', $committees, $group_id, false) .
							$this->mHtmlg->input('text', 'membership_' . $i . '_year', $year) . "\n" .
							$this->mHtmlg->input('button', '', 'Remove', array('onclick' => 'remove_row(\'membership\',this)')) .
							$this->mHtmlg->newline() . "\n");
					}
					else
						$this->output($committees[$group_id] . ' ' . $year .
							$this->mHtmlg->newline() . "\n");
					$this->output($this->mHtmlg->end_div() . "\n");

					$i++;
				}
			}
			if(count($s->group_ids()) == 0 && !$edit)
				$this->output("Inga medlemskap registrerade." . $this->mHtmlg->newline() . "\n");

			$this->output($this->mHtmlg->end_div() . "\n");

			if($edit)
				$this->output($this->mHtmlg->input('hidden', 'max_membership', $i, array('id' => 'max_membership')) . "\n" .
					$this->mHtmlg->input('button', '', 'Add', array('onclick' => 'add_row(\'membership\')')) . 
					$this->mHtmlg->newline() . "\n");
			*/
		}
		else
		{
			$this->output("Ogiltig sektion.");
		}
		
		if(!$create && $section == 'core' && get_session_user()->can_do(MANAGE_USER_PRIV))
		{
			$this->output(
				$this->mHtmlg->div('', 'group_head', 'Administration') . "\n" .
				$this->mHtmlg->begin_div('admin', 'group_content') . "\n");
				
			$this->output(
				$this->mHtmlg->begin_p() . "\n");
			$u = AuthUser::new_from_student($this->mDb, $id);
			$u->load();
			if($u->user_id() != -1)
				$this->output("Knuten till användare " .
					$this->mHtmlg->noun_ahref(NounRequest::new_from_spec('read', 'user', '', 'view', 'xhtml', array('user_id' => $u->user_id())), $u->username()) . ". ");
			$i = AuthInvite::new_from_student($this->mDb, $id);
			$i->load();
			if($i->invite_code() != NULL)
				$this->output("Knuten till inbjudan " .
					$this->mHtmlg->noun_ahref(NounRequest::new_from_spec('read', 'invite', '', 'view', 'xhtml', array('invite_code' => $i->invite_code())), "'" . $i->invite_code() . "'") . ".");
			if($u->user_id() == -1 && $i->invite_code() == NULL)
				$this->output("Inte knuten till någon användare. " . $this->mHtmlg->noun_ahref(NounRequest::new_from_spec('read', 'invite', '', 'create', 'xhtml', array('replace' => 1, 'owns_student_id' => $id)), 'Bjud in') . " denna student.");
			$this->output($this->mHtmlg->end_p() . "\n" .
				$this->mHtmlg->begin_p() . "\n" .
				$this->mHtmlg->noun_ahref(NounRequest::new_from_spec('read', 'dialog', 'remove_student', 'view', 'xhtml', array('student_id' => $id)), 'Ta bort') . " studenten från seniorregistret.\n" .
				$this->mHtmlg->end_p() ."\n");
				
			$this->output($this->mHtmlg->end_div());
		}
		
		if($edit || $create)
			$this->output($this->mHtmlg->input('hidden', 'student_id', $id) . "\n" .
				$this->mHtmlg->input('hidden', 'verb', 'write') . "\n" .
				$this->mHtmlg->input('hidden', 'noun', 'student/' . $section) . "\n" .
				$this->mHtmlg->input('hidden', 'style', $this->mReq->style()) . "\n" .
				$this->mHtmlg->input('submit', 'submit', $edit ? 'Uppdatera' : 'Skapa', array('class' => 'round_btn')) . "\n" .
				$this->mHtmlg->end_form() . "\n");
		
		/*if($section == 'core')
		{
			$user = get_session_user();
			
			$this->output('Super: ' . (get_session_user()->super() ? 't' : 'f') . $this->mHtmlg->newline() . "\n");
			$this->output('Groups: ' . implode(', ', get_session_user()->group_ids()) . $this->mHtmlg->newline() . "\n");
			$this->output('Privileges: ' . implode(', ', get_session_user()->privilege_ids()) . $this->mHtmlg->newline() . "\n");
		}*/
				
		$this->output($this->mHtmlg->end_div() . "\n");
	}
	
	function write()
	{
		$verb = $this->mReq->verb();
		$noun = $this->mReq->noun();
		$section = $this->mReq->section();
		$style = $this->mReq->style();
		$format = $this->mReq->format();
		
		$user = get_session_user();
		
		$s = new Student($this->mDb, $this->mReq->int_scalar('student_id', -1));
		
		//$this->output($user->user_id() . "\n");
		
		/*	$this->output('Super: ' . (get_session_user()->super() ? 't' : 'f') . $this->mHtmlg->newline() . "\n");
			$this->output('Groups: ' . implode(', ', get_session_user()->group_ids()) . $this->mHtmlg->newline() . "\n");
			$this->output('Privileges: ' . implode(', ', get_session_user()->privilege_ids()) . $this->mHtmlg->newline() . "\n");
			$this->output('EDIT_OWN_PAYMENT_PRIV: ' . ($user->can_do(EDIT_PAYMENT_PRIV) ? 't' : 'f') . $this->mHtmlg->newline() . "\n");*/
		//return;
		
		$noredirect = false;
		
		$p = get_parameters();
		
		$errors = $this->validate();
		if(count($errors) > 0)
		{
			if(isset($errors['student_id']))
				header('Location: ' . NounRequest::new_from_spec('read', 'default', '', 'view', 'xhtml')->href());
			else
				header('Location: ' . $this->mReq->clone_extend(array('verb' => 'read', 'errors' => $errors, 'replace' => 1))->href());
			return;
		}
		
		if($section == 'core')
		{	
			$r_values = $this->request_core_values();
				
			if($s->id() == -1)
				$s = Student::create_student($this->mDb, $r_values['first_name'], $r_values['last_name']);
			else
				$s->load_core();
		
			// perhaps replace_empty should be done in class, not here?
			/*$s->first_name($this->mReq->data_scalar('first_name'));
			$s->last_name($this->mReq->data_scalar('last_name'));
			$s->birth_year(replace_empty($this->mReq->int_scalar_or_null('birth_year'), null));
			$s->graduation_year(replace_empty($this->mReq->int_scalar_or_null('graduation_year'), null));
			$s->starting_year(replace_if($this->mReq->int_scalar_or_null('starting_year'), -1, null));
			$s->username(replace_empty($this->mReq->data_scalar('username', null), null));
			$s->specialization_id(replace_if($this->mReq->int_scalar_or_null('spec_id'), -1, null));
			$s->street_address(replace_empty($this->mReq->data_scalar('street_address'), null));
			$s->postal_address(replace_empty($this->mReq->data_scalar('postal_address'), null));
			$s->work(replace_empty($this->mReq->data_scalar('work'), null));
			$s->miscellaneous(replace_empty($this->mReq->data_scalar('misc'), null));
			$s->has_paid_until(replace_empty($this->mReq->data_scalar('has_paid_until'), null));
			$s->is_senior_member($this->mReq->data_scalar_set('is_senior'));
			$s->wants_force($this->mReq->data_scalar_set('wants_force'));
			$s->wants_email($this->mReq->data_scalar_set('wants_email'));*/
			
			$s->first_name($r_values['first_name']);
			$s->last_name($r_values['last_name']);
			$s->birth_year(replace_empty($r_values['birth_year'], null));
			$s->graduation_year(replace_empty($r_values['graduation_year'], null));
			$s->starting_year(replace_if($r_values['starting_year'], -1, null));
			$s->username(replace_empty($r_values['username'], null));
			$s->specialization_id(replace_if($r_values['spec_id'], -1, null));
			$s->work(replace_empty($r_values['work'], null));
			$s->miscellaneous(replace_empty($r_values['misc'], null));
			
			if($user->can_do(EDIT_PAYMENT_PRIV) || 
				($user->owns_student_id() == $this->mReq->int_scalar('student_id') && $user->can_do(EDIT_OWN_PAYMENT_PRIV)))
			{
				$s->has_paid_until(replace_empty($r_values['has_paid_until'], null));
				$s->is_senior_member($r_values['is_senior']);
				$s->wants_force($r_values['wants_force']);
				$s->wants_email($r_values['wants_email']);
			}
			$s->commit_core();
		}
		elseif($section == 'contact')
		{
			$s->load_core();
			$s->load_emails();
			$s->load_telephones();
			
			$r_values = $this->request_contact_values();
			
			//$noredirect = true;
			
			//print_r($r_values);
			
			// bad...
			$s->clear_email_addresses();
			
			/*for($i = 1; $i <= $maxEmail; $i++)
				if($this->mReq->data_scalar('email_' . $i . '_text') !== null)
				{*/
			foreach($r_values['email_addresses'] as $i => $email)
			{
				//$email = $this->mReq->data_scalar('email_' . $i . '_text');
				//echo $this->mParent->get_parameter('email_' . $i . '_text', null) . $this->mParent->mHtmlg->newline() . "\n";
				$standard = false;
				if($i == $r_values['standard_email'])
					$standard = true;
				//echo $email . "(" . ($standard ? 1 : 0) . ")<br />\n";
				$s->add_email_address($email, $standard);
			}
			
			$s->street_address(replace_empty($r_values['street_address'], null));
			$s->postal_address(replace_empty($r_values['postal_address'], null));
				
			// bad...
			$s->clear_telephone_numbers();
			
			/*foreach($p->telephone_type_ids() as $type_id)
			{
				$num = $this->mReq->data_scalar('tel_' . $type_id);
				
				// again, null checking, in student or not?
				if($num)
					$s->add_telephone_number($type_id, $num);
				else
					$s->remove_telephone_number($type_id);
			}*/
			
			foreach($r_values['telephone_numbers'] as $type_id => $num)
				$s->add_telephone_number($type_id, $num);
			
			$s->commit_core();
			$s->commit_emails();
			$s->commit_telephones();
		}
		elseif($section == 'awards')
		{
			$s->load_awards();
			
			$s->clear_awards();
			
			$awards = $this->request_table_values('award', 'title', 'year');
			
			/*$maxAward = $this->mReq->int_scalar('max_award');
			for($i = 1; $i <= $maxAward; $i++)
				if($this->mReq->int_scalar_or_null('award_' . $i . '_title') !== null)
				{
					$title_id = $this->mReq->int_scalar('award_' . $i . '_title');
					$year = $this->mReq->int_scalar('award_' . $i . '_year');
					$s->add_award($title_id, $year);
				}*/
				
			foreach($awards as $tuple)
				$s->add_award($tuple[0], $tuple[1]);
			
			$s->commit_awards();
		}
		elseif($section == 'nominations')
		{
			$s->load_nominations();
			
			$s->clear_nominations();
			
			$nominations = $this->request_table_values('nomination', 'position', 'year');
			
			/*$maxNomination = $this->mReq->int_scalar('max_nomination');
			for($i = 1; $i <= $maxNomination; $i++)
				if($this->mReq->int_scalar_or_null('nomination_' . $i . '_position') !== null)
				{
					$position_id = $this->mReq->int_scalar('nomination_' . $i . '_position');
					$year = $this->mReq->int_scalar('nomination_' . $i . '_year');
					$s->add_nomination($position_id, $year);
				}*/
				
			foreach($nominations as $tuple)
				$s->add_nomination($tuple[0], $tuple[1]);
			
			$s->commit_nominations();
		}
		elseif($section == 'memberships')
		{
			$s->load_memberships();

			$s->clear_memberships();
			
			$memberships = $this->request_table_values('membership', 'committee', 'year');

			/*$maxMembership = $this->mReq->int_scalar('max_membership');
			for($i = 1; $i <= $maxMembership; $i++)
				if($this->mReq->int_scalar_or_null('membership_' . $i . '_committee') !== null)
				{
					$committee_id = $this->mReq->int_scalar('membership_' . $i . '_committee', null);
					$year = $this->mReq->int_scalar('membership_' . $i . '_year', null);
					$s->add_membership($committee_id, $year);
				}*/
				
			foreach($memberships as $tuple)
				$s->add_membership($tuple[0], $tuple[1]);

			$s->commit_memberships();
		}
		
		if(!$noredirect)
			header('Location: index.php?verb=read&noun=student/' . $section . '&style=view&student_id=' . $s->id());
	}
	
	function validate()
	{
		$req = $this->mReq;
		$edit = ($req->style() == 'edit');
		$create = ($req->style() == 'create');
		$params = get_parameters();
		
		$errors = array();
		
		if($edit)
		{
			$student_id = $this->mReq->int_scalar('student_id');
			$s = new Student($this->mDb, $student_id);
			$s->load_core();
			if($s->id() == -1)
				$errors['student_id'] = VERR_INVALID;
		}
		
		if($req->section() == 'core')
		{
			if(!$req->data('first_name'))
				$errors['first_name'] = VERR_MISSING; //'Förnamn saknas';
				
			if(!$req->data('last_name'))
				$errors['last_name'] = VERR_MISSING; //'Efternamn saknas';
		
			if($req->data('birth_year') &&
				!$req->data_numeric_range('birth_year', 1800, 2200))
				$errors['birth_year'] = VERR_INVALID;
			
			if($req->data('graduation_year') &&
				!$req->data_numeric_range('graduation_year', 1800, 2200))
				$errors['graduation_year'] = VERR_INVALID;
				
			if($req->data('starting_year') != -1 &&
				!in_array($req->data_scalar('starting_year'), $params->class_years()))
				$errors['starting_year'] = VERR_INVALID;
				
			if($req->data('spec_id') != -1 &&
				!in_array($req->data_scalar('spec_id'), $params->specialization_ids()))
				$errors['spec_id'] = VERR_INVALID;
				
			if($req->data('has_paid_until') &&
				!$req->data_numeric_range('has_paid_until', 1800, 2200))
				$errors['has_paid_until'] = VERR_INVALID;
		}
		elseif($req->section() == 'contact')
		{
			$max_email = $req->int_scalar('max_email');
			
			$email = array();
			for($i = 1; $i <= $max_email; $i++)
			{
				$email[$i] = $req->data_scalar('email_' . $i . '_text');
				/* regex from php.net/preg_match */
				/* should check out http://code.iamcal.com/php/rfc822/ */
				if(!is_null($email[$i]) &&
					!preg_match('/^[^@]*@[^@]*\.[^@]*$/', $email[$i]))
					$errors['email_'.$i.'_text'] = VERR_INVALID;
			}
			
			if(count(array_filter($email)) > 0 &&
				(!$req->data_numeric_range('standard_email', 1, 99) ||
				!isset($email[$req->int_scalar('standard_email')])))
				$errors['standard_email'] = VERR_INVALID;
				
			foreach($params->telephone_type_ids() as $type_id)
			{
				$num = $this->mReq->data_scalar('tel_' . $type_id);
				if($num &&
					!preg_match('/^[0-9\- ]/', $num))
					$errors['tel_'.$type_id] = VERR_INVALID;
			}
		}
		elseif($req->section() == 'awards' || $req->section() == 'nominations' || $req->section() == 'memberships')
		{
			$section = $req->section();
			$type = array('awards' => 'award', 'nominations' => 'nomination', 'memberships' => 'membership');
			$option_name = array('awards' => 'title', 'nominations' => 'position', 'memberships' => 'committee');
			$text_name = array('awards' => 'year', 'nominations' => 'year', 'memberships' => 'year');
			$rows = $this->request_table_values($type[$section], $option_name[$section], $text_name[$section]);
			foreach($rows as $i => $tuple)
			{
				if(($section == 'awards' && !in_array($tuple[0], $params->title_ids())) ||
					($section == 'nominations' && !in_array($tuple[0], $params->position_ids())) ||
					($section == 'memberships' && !in_array($tuple[0], $params->committee_ids())))
					$errors[$type[$section]."_${i}_".$option_name[$section]] = VERR_INVALID;
				if(!is_numeric($tuple[1]) || $tuple[1] < 1800 || 2200 < $tuple[1])
					$errors[$type[$section]."_${i}_".$text_name[$section]] = VERR_INVALID;
			}
		}
		
		return $errors;
	}
	
	function remove()
	{
		// removing student...
		$student_id = $this->mReq->int_scalar('student_id');
		$s = new Student($this->mDb, $student_id);
		$s->remove();
		
		header('Location: ' . NounRequest::new_from_spec('read', 'list', 'filter', 'view', 'xhtml')->href());	
	}
	
	function is_display()
	{
		return ($this->mReq->verb() == 'read');
	}
	
	function is_allowed($user)
	{
		return ($this->mReq->verb() == 'read' && $this->mReq->style() == 'view' && 	
			(($this->mReq->section() == 'core' && ($user->can_do(VIEW_CORE_PRIV) ||
				(($user->owns_student_id() == $this->mReq->data('student_id') &&
					$user->can_do(VIEW_OWN_CORE_PRIV))))) ||
			($this->mReq->section() == 'contact' && ($user->can_do(VIEW_CONTACT_PRIV) ||
				(($user->owns_student_id() == $this->mReq->data('student_id') &&
					$user->can_do(VIEW_OWN_CONTACT_PRIV))))) ||
			($this->mReq->section() == 'awards' && ($user->can_do(VIEW_AWARD_PRIV) ||
				(($user->owns_student_id() == $this->mReq->data('student_id') &&
					$user->can_do(VIEW_OWN_AWARD_PRIV))))) ||
			($this->mReq->section() == 'nominations' && ($user->can_do(VIEW_NOMINATION_PRIV) ||
				(($user->owns_student_id() == $this->mReq->data('student_id') &&
					$user->can_do(VIEW_OWN_NOMINATION_PRIV))))) ||
			($this->mReq->section() == 'memberships' && ($user->can_do(VIEW_MEMBERSHIP_PRIV) ||
				(($user->owns_student_id() == $this->mReq->data('student_id') &&
					$user->can_do(VIEW_OWN_MEMBERSHIP_PRIV))))))) ||
				(($this->mReq->verb() == 'write' || ($this->mReq->verb() == 'read' && $this->mReq->style() == 'edit')) &&
			(($this->mReq->section() == 'core' && (($user->can_do(EDIT_CORE_PRIV) || $user->can_do(EDIT_PAYMENT_PRIV)) ||
				(($user->owns_student_id() == $this->mReq->data('student_id') &&
					($user->can_do(EDIT_OWN_CORE_PRIV) || $user->can_do(EDIT_OWN_PAYMENT_PRIV)))))) ||
			($this->mReq->section() == 'contact' && ($user->can_do(EDIT_CONTACT_PRIV) ||
				(($user->owns_student_id() == $this->mReq->data('student_id') &&
					$user->can_do(EDIT_OWN_CONTACT_PRIV))))) ||
			($this->mReq->section() == 'awards' && ($user->can_do(EDIT_AWARD_PRIV) ||
				(($user->owns_student_id() == $this->mReq->data('student_id') &&
					$user->can_do(EDIT_OWN_AWARD_PRIV))))) ||
			($this->mReq->section() == 'nominations' && ($user->can_do(EDIT_NOMINATION_PRIV) ||
				(($user->owns_student_id() == $this->mReq->data('student_id') &&
					$user->can_do(EDIT_OWN_NOMINATION_PRIV))))) ||
			($this->mReq->section() == 'memberships' && ($user->can_do(EDIT_MEMBERSHIP_PRIV) ||
				(($user->owns_student_id() == $this->mReq->data('student_id') &&
					$user->can_do(EDIT_OWN_MEMBERSHIP_PRIV))))))) ||
				($this->mReq->verb() == 'read' &&
				 $this->mReq->style() == 'create' && $user->can_do(MANAGE_STUDENT_PRIV) &&
				 $this->mReq->section() == 'core') ||
				($this->mReq->verb() == 'remove' && $user->can_do(MANAGE_STUDENT_PRIV));
	}
	
	function request_core_values()
	{
		$req = $this->mReq;
		
		return array(
			'first_name' => $req->data_scalar('first_name'),
			'last_name' => $req->data_scalar('last_name'),
			'birth_year' => $req->int_scalar_or_null('birth_year'),
			'graduation_year' => $req->int_scalar_or_null('graduation_year'),
			'starting_year' => $req->int_scalar_or_null('starting_year'),
			'username' => $req->data_scalar('username'),
			'spec_id' => $req->int_scalar_or_null('spec_id'),
			'work' => $req->data_scalar('work'),
			'misc' => $req->data_scalar('misc'),
			'has_paid_until' => $req->int_scalar_or_null('has_paid_until'),
			'is_senior' => $req->data_scalar_set('is_senior'),
			'wants_force' => $req->data_scalar_set('wants_force'),
			'wants_email' => $req->data_scalar_set('wants_email'));
	}
	
	function request_contact_values()
	{
		$req = $this->mReq;
		$p = $this->mParams;
		
		$max_email = $req->int_scalar('max_email');
		$email_addresses = array();
		for($i = 1; $i <= $max_email; $i++)
		{
			$email = $req->data_scalar('email_' . $i . '_text');
			if(!is_null($email))
				$email_addresses[$i] = $email;
		}
		$standard_email = $req->data_scalar('standard_email');
		
		$telephone_numbers = array();
		foreach($p->telephone_type_ids() as $type_id)
			$telephone_numbers[$type_id] = $req->data_scalar('tel_' . $type_id);
		
		return array(
			'email_addresses' => $email_addresses, 
			'standard_email' => $standard_email, 
			'max_email' => $max_email, 
			'street_address' => $req->data_scalar('street_address'),
			'postal_address' => $req->data_scalar('postal_address'),
			'telephone_numbers' => $telephone_numbers);
	}
	
	function request_table_values($type, $option_name, $text_name)
	{
		$req = $this->mReq;
		
		$max = $req->int_scalar('max_'.$type);
		$rows = array();
		for($i = 1; $i <= $max; $i++)
		{
			$id = $req->int_scalar_or_null($type.'_' . $i . '_'.$option_name);
			if(!is_null($id))
			{
				// this is to make sure data stays the same even if it's non-int..
				// otherwise use int_scalar
				$text_value = $req->data_scalar($type.'_' . $i . '_'.$text_name);
				$rows[$i] = array($id, $text_value);
			}
		}
		
		return $rows;
	}
}

?>