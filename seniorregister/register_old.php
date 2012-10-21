<?php

require('htmlgen.php');
require('logger.php');
require('db.php');
require('pgdb.php');

class XhtmlGeneratorEx extends XhtmlGenerator
{
	function __construct($strict)
	{
		parent::__construct($strict);
	}
	
	// should be fixed to handle multiples properly, $selected to be an array in this case
	function select($name, $options, $selected, $multiple = false)
	{		
		$t = $this->begin_tag($this->generate_meat('select', array('name' => $name, 'multiple' => $multiple ? 'multiple' : ''))) . "\n";
		foreach($options as $k => $v)
			$t .= $this->begin_tag($this->generate_meat('option', 
				array('value' => $k, 'selected' => ($k == $selected) ? 'selected' : ''))) . $v . $this->end_tag('option') . "\n";
		$t .= $this->end_tag('select') . "\n";
		
		return $t;
	}
	
	function checkbox($name, $value, $caption, $checked)
	{
		return $this->begin_tag($this->generate_meat('input', array('type' => 'checkbox', 'name' => $name, 'value' => $value, 'checked' => $checked ? 'checked' : ''))) . $caption;
	}
	
	function link($href, $caption)
	{
		return $this->begin_tag($this->generate_meat('a', array('href' => $href))) . $caption . $this->end_tag('a');
	}
}

class RegisterXhtmlGenerator extends XhtmlGeneratorEx
{
	function __construct($strict)
	{
		parent::__construct($strict);
	}

	function fixed_field($label, $value)
	{
		return $this->strong($label . ':') . ' ' . 
			$value . $this->newline() . "\n";
	}
	
	function text_field($label, $name, $value)
	{
		return $this->strong($label . ':') . ' ' . 
			$this->input('text', $name, $value) . 
			$this->newline() . "\n";
	}
	
	function list_field($label, $name, $options, $selected)
	{
		return $this->strong($label . ':') . "\n" .
			$this->select($name, $options, $selected, false) . "\n" .
			$this->newline() . "\n";
	}
	
	function checkbox_field($label, $name, $checked)
	{
		return $this->checkbox($name, '1', $label, $checked) . 
			$this->newline() . "\n";
	}
	
	function checkbox_text_field($label, $checkbox_name, $checked, $text_name, $value)
	{
		return $this->checkbox($checkbox_name, '1', $label, $checked) .
			$this->input('text', $text_name, $value) . 
			$this->newline() . "\n";
	}
}

class RegisterPage
{
	var $mHtmlg, $mDb;
	
	function __construct()
	{
		$this->mHtmlg = new RegisterXhtmlGenerator(false);
		$this->mDb = new PostgresDatabase('localhost', 'register', 'register', 'register', new Logger('register_db.log', true));
	}
	
	function get_parameter($key)
	{
		return (isset($_GET[$key]) && !empty($_GET[$key])) ? ($_GET[$key]) : (null);
	}
	
	function get_arskursnamn()
	{	
		$res = $this->mDb->select('arskursnamn', array('"AK"', '"NAMN"'), '');
		$arskursnamn = array();
		while(($obj = $res->fetch_object()) !== false)
			$arskursnamn[$obj->AK] = $obj->NAMN . ' (' . $obj->AK . ')';
		uksort($arskursnamn, create_function('$k1,$k2', '$k1 = substr($k1,1,2); $k2 = substr($k2,1,2); if($k1<35) $k1+=100; if($k2<35) $k2+=100; return $k1<$k2;'));
		
		return $arskursnamn;
	}
	
	function get_inriktningar()
	{
		$res = $this->mDb->select('inriktning', array('"ID"', '"NAMN"'), '');
		$inriktningar = array();
		while(($obj = $res->fetch_object()) !== false)
			$inriktningar[$obj->ID] = $obj->NAMN;
		ksort($inriktningar);
		
		return $inriktningar;
	}
	
	function get_tfntyper()
	{
		$res = $this->mDb->select('tfntyp', array('"ID"', '"NAMN"'), '');
		$tfntyp = array();
		while(($obj = $res->fetch_object()) !== false)
			$tfntyp[$obj->ID] = $obj->NAMN;
		ksort($tfntyp);
		
		return $tfntyp;
	}
	
	function get_titlar()
	{
		$res = $this->mDb->select('titel', array('"ID"', '"NAMN"'), '');
		$titlar = array();
		while(($obj = $res->fetch_object()) !== false)
			$titlar[$obj->ID] = $obj->NAMN;
		ksort($titlar);
		
		return $titlar;
	}
	
	function get_poster()
	{
		$res = $this->mDb->select('post', array('"ID"', '"NAMND_ID"', '"NAMN"', '"STYRET"'), '');
		$poster = array();
		while(($obj = $res->fetch_object()) !== false)
			$poster[$obj->ID] = $obj;
		ksort($poster);
		
		return $poster;
	}
	
	function get_namnder()
	{
		$res = $this->mDb->select('namnd', array('"ID"', '"NAMN"', '"KORT"', '"MEDLEMSNAMN"', '"EGENNAMND"'), '');
		$namnder = array();
		while(($obj = $res->fetch_object()) !== false)
			$namnder[$obj->ID] = $obj;
		ksort($namnder);
		
		return $namnder;
	}
	
	function get_student($id)
	{
		return $this->mDb->lookup('student', array('"ID"' => $id));
	}
	
	function get_epost($id)
	{
		$epost = array($this->mDb->lookup('epost', array('"STUDENT_ID"' => $id, '"STANDARD"' => 0)),
			$this->mDb->lookup('epost', array('"STUDENT_ID"' => $id, '"STANDARD"' => 1)));
		foreach($epost as $k => $v)
			if(!empty($v))
				$epost[$k] = $v->EPOST;
				
		return $epost;
	}
	
	function get_telefon($id)
	{
		$res = $this->mDb->select('telefon', array('"TFNTYP_ID"', '"TFN"'), array('"STUDENT_ID"' => $id));
		$telefon = array();
		while(($obj = $res->fetch_object()) !== false)
			$telefon[$obj->TFNTYP_ID] = $obj->TFN;
			
		return $telefon;
	}
	
	function get_seniorinfo($id)
	{
		return $this->mDb->lookup('seniorinfo', array('"STUDENT_ID"' => $id));
	}
	
	function get_utnamning($id)
	{
		$res = $this->mDb->select('utnamning', array('"TITEL_ID"', '"AR"'), array('"STUDENT_ID"' => $id));
		$utnamning = array();
		while(($obj = $res->fetch_object()) !== false)
			$utnamning[$obj->TITEL_ID] = $obj->AR;
			
		return $utnamning;
	}
	
	function get_nominering($id)
	{
		$res = $this->mDb->select('nominering', array('"POST_ID"', '"AR"'), array('"STUDENT_ID"' => $id));
		$nominering = array();
		while(($obj = $res->fetch_object()) !== false)
			$nominering[$obj->POST_ID] = $obj->AR;
			
		return $nominering;	
	}
	
	function get_medlemskap($id)
	{
		$res = $this->mDb->select('medlemskap', array('"NAMND_ID"', '"AR"'), array('"STUDENT_ID"' => $id));
		$medlemskap = array();
		while(($obj = $res->fetch_object()) !== false)
			$medlemskap[$obj->NAMND_ID] = $obj->AR;
			
		return $medlemskap;
	}
	
	function process($noun)
	{
		echo $this->mHtmlg->xml_doctype() . "\n" .
			$this->mHtmlg->begin_html() . "\n" .
				$this->mHtmlg->begin_head() . "\n" .
					$this->mHtmlg->title('Seniorregister') . "\n" .
				$this->mHtmlg->end_head() . "\n" .
				$this->mHtmlg->begin_body() . "\n" .
					$this->mHtmlg->heading(1, "Seniorregister ($noun)") . $this->mHtmlg->newline() . "\n";
					
		if($noun == 'person')
		{
			$id = $this->get_parameter('person_id');
			
			$arskursnamn = array_merge(array('fna' => 'N/A'), $this->get_arskursnamn());
			$inriktningar = $this->get_inriktningar();
			$inriktningar[-1] = 'N/A';
			$tfntyp = $this->get_tfntyper();
			$titlar = $this->get_titlar();
			$poster = $this->get_poster();
			$namnder = $this->get_namnder();
			
			$student = $this->get_student($id);
			$epost = $this->get_epost($id);
			$telefon = $this->get_telefon($id);
			$seniorinfo = $this->get_seniorinfo($id);
			$utnamning = $this->get_utnamning($id);
			$nominering = $this->get_nominering($id);
			$medlemskap = $this->get_medlemskap($id);
				
			if($student == null)
				echo $this->mHtmlg->strong('Fatal error:') . " Kan inte hitta student #$id. Aborting." . $this->mHtmlg->newline() . "\n";
			else
			{				
				echo $this->mHtmlg->begin_form('get', 'index.php');
				
				echo "<!--\n";
				echo "-->";
				
				$labels = array('ID' => 'Student #', 'F_NAMN' => 'Förnamn', 'E_NAMN' => 'Efternamn', 'YOB' => 'Födelseår', 'YOE' => 'Examensår', 'AK' => 'Årskurs', 'USERNAME' => 'Användarnamn', 'INRIKTNING_ID' => 'Inriktning', 'GATUADRESS' => 'Gatuadress', 'POSTADRESS' => 'Postadress', 'ARBETE' => 'Arbete', 'OVRIGT' => 'Övrigt', 'UPPDATERAD' => 'Senast uppdaterad');
				
				echo $this->mHtmlg->heading(3, "Personuppgifter") . "\n";
				
				echo $this->mHtmlg->fixed_field($labels['ID'], $student->ID) .
					$this->mHtmlg->text_field($labels['F_NAMN'], 'F_NAMN', $student->F_NAMN) .
					$this->mHtmlg->text_field($labels['E_NAMN'], 'E_NAMN', $student->E_NAMN) .
					$this->mHtmlg->text_field($labels['YOB'], 'YOB', ($seniorinfo ? $seniorinfo->YOB : '')) .
					$this->mHtmlg->text_field($labels['YOE'], 'YOE', ($seniorinfo ? $seniorinfo->EXAMEN : '')) .
					$this->mHtmlg->list_field($labels['AK'], 'AK', $arskursnamn, empty($student->AK) ? ('fna') : ($student->AK)) .
					$this->mHtmlg->text_field($labels['USERNAME'], 'USERNAME', $student->USERNAME) .
					$this->mHtmlg->list_field($labels['INRIKTNING_ID'], 'INRIKTNING_ID', $inriktningar, empty($student->INRIKTNING_ID) ? -1 : $student->INRIKTNING_ID, false) .
					$this->mHtmlg->text_field($labels['GATUADRESS'], 'GATUADRESS', $student->GATUADRESS) .
					$this->mHtmlg->text_field($labels['POSTADRESS'], 'POSTADRESS', $student->POSTADRESS) .
					$this->mHtmlg->text_field($labels['ARBETE'], 'ARBETE', $student->ARBETE) .
					$this->mHtmlg->text_field($labels['OVRIGT'], 'OVRIGT', $student->OVRIGT) .
					$this->mHtmlg->fixed_field($labels['UPPDATERAD'], $student->UPPDATERAD);
				
				echo $this->mHtmlg->heading(3, "Kontakt") . "\n";
				
				echo $this->mHtmlg->text_field('Standard e-post', 'EPOST_1', $epost[1]) .
					$this->mHtmlg->text_field('Alternativ e-post', 'EPOST_0', $epost[0]);
					
				foreach($tfntyp as $k => $v)
					echo $this->mHtmlg->text_field($v, "TFN_$k", (isset($telefon[$k]) ? $telefon[$k] : ''));
						
				echo $this->mHtmlg->heading(3, "Senior") . "\n";
				
				echo $this->mHtmlg->checkbox_field('Seniormedlem', 'SENIOR', $seniorinfo ? $seniorinfo->SENIOR : false) .
					$this->mHtmlg->checkbox_field('Force-prenumerering', 'FORCE', $seniorinfo ? $seniorinfo->FORCE : false) .
					$this->mHtmlg->checkbox_field('E-post', 'EPOST', $seniorinfo ? $seniorinfo->EPOST : false) .
					$this->mHtmlg->newline() . 
					$this->mHtmlg->text_field('Har betalat till och med', 'BET_TOM', $seniorinfo ? $seniorinfo->BET_TOM : false);
					
				/* ignoring OVRIGT field, is unused */
				
				echo $this->mHtmlg->heading(3, "Utnämningar") . "\n";
				
				foreach($titlar as $k => $v)
					echo $this->mHtmlg->checkbox_text_field($v, "TITEL_$k", isset($utnamning[$k]), "TITEL_${k}_AR", isset($utnamning[$k]) ? $utnamning[$k] : '');
				
				echo $this->mHtmlg->heading(3, "Poster") . "\n";
				
				foreach($poster as $k => $v)
					echo $this->mHtmlg->checkbox_text_field(
						$v->NAMN . ' (' . $namnder[$v->NAMND_ID]->KORT . ') ' . ($v->STYRET ? (' ' . $this->mHtmlg->strong('[Styret]')) : ''), 
						"POST_$k", isset($nominering[$k]), 
						"POST_${k}_AR", isset($nominering[$k]) ? $nominering[$k] : '');
				
				echo $this->mHtmlg->heading(3, "Nämnder") . "\n";
				
				foreach($namnder as $k => $v)
					echo $this->mHtmlg->checkbox_text_field(
						$v->NAMN, "NAMND_$k", isset($medlemskap[$k]), 
						"NAMND_${k}_AR", isset($medlemskap[$k]) ? $medlemskap[$k] : '');
				
				echo $this->mHtmlg->heading(3, "Åtgärd") . "\n";
			
				echo $this->mHtmlg->input('submit', 'submit', 'Uppdatera') . $this->mHtmlg->newline() . "\n";
			
				echo $this->mHtmlg->input('hidden', 'ID', $student->ID) . "\n" .
					$this->mHtmlg->input('hidden', 'noun', 'person_update') . "\n";
						
				echo $this->mHtmlg->end_form();
			}
		}
		elseif($noun == 'person_update')
		{
			$this->mDb->update('student',
				array('"F_NAMN"' => $this->get_parameter('F_NAMN'),
					  '"E_NAMN"' => $this->get_parameter('E_NAMN'),
					  '"AK"' => ($this->get_parameter('AK') != 'fna' ? $this->get_parameter('AK') : NULL),
					  '"USERNAME"' => $this->get_parameter('USERNAME'),
					  '"INRIKTNING_ID"' => ($this->get_parameter('INRIKTNING_ID') != -1 ? $this->get_parameter('INRIKTNING_ID') : NULL),
					  '"GATUADRESS"' => $this->get_parameter('GATUADRESS'),
					  '"POSTADRESS"' => $this->get_parameter('POSTADRESS'),
					  '"ARBETE"' => $this->get_parameter('ARBETE'),
					  '"OVRIGT"' => $this->get_parameter('OVRIGT'),
					  '"UPPDATERAD"' => date('Y-m-d')),
				array('"ID"' => $this->get_parameter('ID')));
			
			for($i = 0; $i < 2; $i++)
				if($this->get_parameter("EPOST_$i"))
				{
					if($this->mDb->lookup('epost', array('"STUDENT_ID"' => $this->get_parameter('ID'), '"STANDARD"' => $i)) != NULL)
						$this->mDb->update('epost', array('"EPOST"' => $this->get_parameter("EPOST_$i")), array('"STUDENT_ID"' => $this->get_parameter('ID'), '"STANDARD"' => $i));
					else
						$this->mDb->insert('epost', array('"EPOST"' => $this->get_parameter("EPOST_$i"), '"STUDENT_ID"' => $this->get_parameter('ID'), '"STANDARD"' => $i));
				}
				else
					$this->mDb->remove('epost', array('"STUDENT_ID"' => $this->get_parameter('ID'), '"STANDARD"' => $i));
			
			for($i = 0; $i < 6; $i++)					// should only loop over relevant types... but this works
				if($this->get_parameter("TFN_$i"))
				{
					if($this->mDb->lookup('telefon', array('"STUDENT_ID"' => $this->get_parameter('ID'), '"TFNTYP_ID"' => $i)) != NULL)
						$this->mDb->update('telefon', array('"TFN"' => $this->get_parameter("TFN_$i")), array('"STUDENT_ID"' => $this->get_parameter('ID'), '"TFNTYP_ID"' => $i));
					else
						$this->mDb->insert('telefon', array('"TFN"' => $this->get_parameter("TFN_$i"), '"STUDENT_ID"' => $this->get_parameter('ID'), '"TFNTYP_ID"' => $i));
				}
				else
					$this->mDb->remove('telefon', array('"STUDENT_ID"' => $this->get_parameter('ID'), '"TFNTYP_ID"' => $i));
					
			if($this->get_parameter('YOB') ||
				$this->get_parameter('YOE') ||
				$this->get_parameter('SENIOR') ||
				$this->get_parameter('BET_TOM') ||
				$this->get_parameter('FORCE') ||
				$this->get_parameter('EPOST'))
			{
				if($this->mDb->lookup('seniorinfo', array('"STUDENT_ID"' => $this->get_parameter('ID'))) != NULL)
					$this->mDb->update('seniorinfo',
						array('"BET_TOM"' => $this->get_parameter('BET_TOM'),
						      '"EXAMEN"' => $this->get_parameter('YOE'),
						      '"YOB"' => $this->get_parameter('YOB'),
						      '"SENIOR"' => ($this->get_parameter('SENIOR') ? 1 : 0),
						      '"FORCE"' => ($this->get_parameter('FORCE') ? 1 : 0),
						      '"EPOST"' => ($this->get_parameter('EPOST') ? 1 : 0)),
						array('"STUDENT_ID"' => $this->get_parameter('ID')));
				else
					$this->mDb->insert('seniorinfo',
						array('"STUDENT_ID"' => $this->get_parameter('ID'),
							  '"BET_TOM"' => $this->get_parameter('BET_TOM'),
						      '"EXAMEN"' => $this->get_parameter('YOE'),
						      '"YOB"' => $this->get_parameter('YOB'),
						      '"SENIOR"' => ($this->get_parameter('SENIOR') ? 1 : 0),
						      '"FORCE"' => ($this->get_parameter('FORCE') ? 1 : 0),
						      '"EPOST"' => ($this->get_parameter('EPOST') ? 1 : 0)));
			}
			else
				$this->mDb->remove('seniorinfo', array('"STUDENT_ID"' => $this->get_parameter('ID')));
				
			for($i = 0; $i < 10; $i++)					// should only loop over relevant types... but this works
				if($this->get_parameter("TITEL_$i"))
				{
					if($this->mDb->lookup('utnamning', array('"STUDENT_ID"' => $this->get_parameter('ID'), '"TITEL_ID"' => $i)) != NULL)
						$this->mDb->update('utnamning', array('"AR"' => $this->get_parameter("TITEL_${i}_AR") ? $this->get_parameter("TITEL_${i}_AR") : ''), array('"STUDENT_ID"' => $this->get_parameter('ID'), '"TITEL_ID"' => $i));
					else
						$this->mDb->insert('utnamning', array('"AR"' => $this->get_parameter("TITEL_${i}_AR") ? $this->get_parameter("TITEL_${i}_AR") : '', '"STUDENT_ID"' => $this->get_parameter('ID'), '"TITEL_ID"' => $i));
				}
				else
					$this->mDb->remove('utnamning', array('"STUDENT_ID"' => $this->get_parameter('ID'), '"TITEL_ID"' => $i));
					
			for($i = 0; $i < 50; $i++)					// should only loop over relevant types... but this works
				if($this->get_parameter("POST_$i"))
				{
					if($this->mDb->lookup('nominering', array('"STUDENT_ID"' => $this->get_parameter('ID'), '"POST_ID"' => $i)) != NULL)
						$this->mDb->update('nominering', array('"AR"' => $this->get_parameter("POST_${i}_AR") ? $this->get_parameter("POST_${i}_AR") : ''), array('"STUDENT_ID"' => $this->get_parameter('ID'), '"POST_ID"' => $i));
					else
						$this->mDb->insert('nominering', array('"AR"' => $this->get_parameter("POST_${i}_AR") ? $this->get_parameter("POST_${i}_AR") : '', '"STUDENT_ID"' => $this->get_parameter('ID'), '"POST_ID"' => $i));
				}
				else
					$this->mDb->remove('nominering', array('"STUDENT_ID"' => $this->get_parameter('ID'), '"POST_ID"' => $i));
					
			// also, this does not really work for people who are members for more than a year...
			for($i = 0; $i < 20; $i++)					// should only loop over relevant types... but this works
				if($this->get_parameter("NAMND_$i"))
				{
					if($this->mDb->lookup('medlemskap', array('"STUDENT_ID"' => $this->get_parameter('ID'), '"NAMND_ID"' => $i, '"AR"' => $this->get_parameter("NAMND_${i}_AR") ? $this->get_parameter("NAMND_${i}_AR") : '')) != NULL)
						//$this->mDb->update('medlemskap', array('"AR"' => $this->get_parameter("NAMND_${i}_AR") ? $this->get_parameter("NAMND_${i}_AR") : ''), array('"STUDENT_ID"' => $this->get_parameter('ID'), '"NAMND_ID"' => $i));
						;
						// really nothing to do here...
					else
						$this->mDb->insert('medlemskap', array('"AR"' => $this->get_parameter("NAMND_${i}_AR") ? $this->get_parameter("NAMND_${i}_AR") : '', '"STUDENT_ID"' => $this->get_parameter('ID'), '"NAMND_ID"' => $i));
				}
				else
					$this->mDb->remove('medlemskap', array('"STUDENT_ID"' => $this->get_parameter('ID'), '"NAMND_ID"' => $i, '"AR"' => $this->get_parameter("NAMND_${i}_AR") ? $this->get_parameter("NAMND_${i}_AR") : ''));
				
			echo "Uppdatering utförd." . $this->mHtmlg->newline() . "\n";
			echo $this->mHtmlg->link('index.php?noun=person&person_id=' . $this->get_parameter('ID'), "Tillbaka") .
				$this->mHtmlg->newline() . "\n";
			
		}
		elseif($noun == 'list')
		{
			$res = $this->mDb->select('titel', array('"ID"', '"NAMN"'), '');
			$titlar = array(-1 => 'Ingen');
			while(($obj = $res->fetch_object()) !== false)
				$titlar[$obj->ID] = $obj->NAMN;
			ksort($titlar);
			
			$res = $this->mDb->select('arskursnamn', array('"AK"', '"NAMN"'), '');
			$arskursnamn = array();
			while(($obj = $res->fetch_object()) !== false)
				$arskursnamn[$obj->AK] = $obj->NAMN . ' (' . $obj->AK . ')';
			uksort($arskursnamn, create_function('$k1,$k2', '$k1 = substr($k1,1,2); $k2 = substr($k2,1,2); if($k1<35) $k1+=100; if($k2<35) $k2+=100; return $k1<$k2;'));
			$arskursnamn = array_merge(array('fna' => 'Ingen'), $arskursnamn);
			
			$res = $this->mDb->select('post', array('"ID"', '"NAMND_ID"', '"NAMN"', '"STYRET"'), '');
			$poster = array(-1 => 'Ingen');
			while(($obj = $res->fetch_object()) !== false)
				$poster[$obj->ID] = $obj->NAMN;
			ksort($poster);
			
			$qual = array();
			if($this->get_parameter('epost'))
				$qual['"EPOST"'] = 1;
			if($this->get_parameter('force')) 
				$qual['"FORCE"'] = 1;
			if($this->get_parameter('utnamning') &&
			 	$this->get_parameter('utnamning') != -1)
				$qual['"TITEL_ID"'] = $this->get_parameter('utnamning');
			if($this->get_parameter('arskurs') &&
				$this->get_parameter('arskurs') != 'fna')
				$qual['"AK"'] = $this->get_parameter('arskurs');
			if($this->get_parameter('post') &&
				$this->get_parameter('post') != -1)
				$qual['"POST_ID"'] = $this->get_parameter('post');
				
			// problem, dupliceringar när folk har tagit flera titlar eller poster
			// lösning, använd distinct, eller inkludera bara de left joins som är relevanta
			// se Student klass
				
			$res = $this->mDb->select('student left join seniorinfo on student."ID" = seniorinfo."STUDENT_ID" left join utnamning on student."ID" = utnamning."STUDENT_ID" left join nominering on student."ID" = nominering."STUDENT_ID"', 
				array('"ID"', '"F_NAMN"', '"E_NAMN"', '"AK"'), $qual);		// hmm...
			
			echo $this->mHtmlg->begin_form('get', 'index.php') . "\n" .
				$this->mHtmlg->checkbox('epost', '1', 'Har betalat för e-post', $this->get_parameter('epost')) . $this->mHtmlg->newline() . "\n" .
				$this->mHtmlg->checkbox('force', '1', 'Har betalat för Force', $this->get_parameter('force')) . $this->mHtmlg->newline() . "\n" .
				$this->mHtmlg->select('utnamning', $titlar, $this->get_parameter('utnamning') ? $this->get_parameter('utnamning') : -1, false) . 
				" emeritus" . $this->mHtmlg->newline() . "\n" .
				"Årskurs: " . $this->mHtmlg->select('arskurs', $arskursnamn, $this->get_parameter('arskurs') ? $this->get_parameter('arskurs') : 'fna', false) .
				$this->mHtmlg->newline() . "\n" .
				"Post: " . $this->mHtmlg->select('post', $poster, $this->get_parameter('post') ? $this->get_parameter('post') : -1, false) .
				$this->mHtmlg->newline() . "\n" .
				$this->mHtmlg->input('hidden', 'noun', 'list') . "\n" .
				$this->mHtmlg->input('submit', 'submit', 'Uppdatera lista') .
				$this->mHtmlg->end_form() . "\n";
			
			while(($obj = $res->fetch_object()) !== false)
			{
				echo $this->mHtmlg->link('index.php?noun=person&person_id=' . $obj->ID, $obj->F_NAMN . " " . $obj->E_NAMN . " (" . $obj->AK . ")") .
					$this->mHtmlg->newline() . "\n";
			}
		}
		elseif($noun == 'search')
		{
			echo $this->mHtmlg->begin_form('get', 'index.php') . "\n" .
				$this->mHtmlg->input('text', 'query', $this->get_parameter('query')) . "\n" .
				$this->mHtmlg->input('hidden', 'noun', 'search') . "\n" .
				$this->mHtmlg->input('submit', 'submit', 'Sök') . "\n" .
				$this->mHtmlg->end_form() . "\n";
			
			if($this->get_parameter('query'))
			{
				$q = strtolower($this->get_parameter('query'));
				$res = $this->mDb->select('student',
					array('"ID"', '"F_NAMN"', '"E_NAMN"', '"AK"'),
					"lower(\"F_NAMN\") LIKE '%$q%' OR lower(\"E_NAMN\") LIKE '%$q%'");
					
				while(($obj = $res->fetch_object()) !== false)
				{
					echo $this->mHtmlg->link('index.php?noun=person&person_id=' . $obj->ID, $obj->F_NAMN . " " . $obj->E_NAMN . " (" . $obj->AK . ")") .
						$this->mHtmlg->newline() . "\n";
				}	
			}
		}
		
		echo $this->mHtmlg->heading(3, "Navigation") . "\n" .
			$this->mHtmlg->link('index.php?noun=list', 'Lista') . " - " .
			$this->mHtmlg->link('index.php?noun=search', 'Sök') . $this->mHtmlg->newline() . "\n" .
			$this->mHtmlg->end_body() . "\n" .
			$this->mHtmlg->end_html() . "\n";
	}
}

?>