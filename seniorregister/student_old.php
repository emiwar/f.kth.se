<?php

/*require('db.php');
require('pgdb.php');
require('logger.php');*/

class Student
{
	var $mDb;
	
	var $mId;
	
	var $mFirstName, $mLastName, $mStartYear, $mUsername, $mSpecId, 
		$mStreetAddress, $mPostalAddress, $mWork, $mMisc, $mUpdated;
		
	var $mPaid, $mGraduation, $mBirth, $mSeniorMember, $mWantsForce, $mWantsEmail;
		
	var $mEmail, $mStandardEmail;
	
	var $mTelephone;
	
	var $mAwards;
	
	var $mMemberships;
	
	function create_student($db, $firstName, $lastName)
	{
		$max = $db->select_one('student', 'MAX("ID") as m', '');
		$id = $max->m+1;
		
		$db->insert('student', array('"ID"' => $id, '"F_NAMN"' => $firstName, '"E_NAMN"' => $lastName));
		$db->insert('seniorinfo', array('"STUDENT_ID"' => $id, '"BET_TOM"' => ''));
		
		$s = new Student($db, $id);
		$s->load();
		
		return $s;
	}
	
	function list_students($db, $criteria)
	{
		$tables = $criteria->tables($db);
		$requirement = $criteria->requirement($db);
	
		$from = 'student';
		foreach($tables as $t)
			$from .= ' LEFT JOIN ' . $t[0] . ' ON student."ID" = ' . $t[0] . '."' . $t[1] . '"';
		
		$res = $db->select($from, array('student."ID"', 'student."F_NAMN"', 'student."E_NAMN"', 'student."AK"'), $requirement);
		
		$list = array();
		
		while(($obj = $res->fetch_object()) !== false)
			$list[$obj->ID] = array($obj->F_NAMN, $obj->E_NAMN, $obj->AK);
		
		$res->free();
		
		return $list;
	}
	
	function __construct($db, $id = null)
	{
		$this->mDb = $db;
		$this->mId = $id;
	}
	
	function load_core()
	{
		$obj = $this->mDb->lookup('student', array('"ID"' => $this->mId));
		
		$this->mFirstName = $obj->F_NAMN;
		$this->mLastName = $obj->E_NAMN;
		$this->mStartYear = ($obj->AK !== NULL ? $obj->AK : 'fna');
		$this->mUsername = $obj->USERNAME;
		$this->mSpecId = ($obj->INRIKTNING_ID !== NULL ? $obj->INRIKTNING_ID : -1);
		$this->mStreetAddress = $obj->GATUADRESS;
		$this->mPostalAddress = $obj->POSTADRESS;
		$this->mWork = $obj->ARBETE;
		$this->mMisc = $obj->OVRIGT;
		$this->mUpdated = $obj->UPPDATERAD;
		
		$obj = $this->mDb->lookup('seniorinfo', array('"STUDENT_ID"' => $this->mId));
		
		if($obj)
		{
			$this->mPaid = $obj->BET_TOM;
			$this->mGraduation = $obj->EXAMEN;
			$this->mBirth = $obj->YOB;
			$this->mSeniorMember = $obj->SENIOR;
			$this->mWantsForce = $obj->FORCE;
			$this->mWantsEmail = $obj->EPOST;
		}
	}
	
	function load_emails()
	{
		$res = $this->mDb->select('epost', array('"EPOST"', '"STANDARD"'), array('"STUDENT_ID"' => $this->mId));
		
		$this->mEmail = array();
		$this->mStandardEmail = 0;
		$i = 0;
		while(($obj = $res->fetch_object()) !== false)
		{
			$this->mEmail[$i] = $obj->EPOST;
			if($obj->STANDARD)
				$this->mStandardEmail = $i;
			
			$i++;
		}
		
		$res->free();
	}
	
	function load_telephones()
	{
		$res = $this->mDb->select('telefon', array('"TFNTYP_ID"', '"TFN"'), array('"STUDENT_ID"' => $this->mId));
		
		$this->mTelephone = array();
		while(($obj = $res->fetch_object()) !== false)
			$this->mTelephone[$obj->TFNTYP_ID] = $obj->TFN;
		
		$res->free();
	}
	
	function load_awards()
	{	
		// we here assume that one can only receive an award once, as opposed to nominations
		
		$res = $this->mDb->select('utnamning', array('"TITEL_ID"', '"AR"'), array('"STUDENT_ID"' => $this->mId));
		
		$this->mAwards = array();
		while(($obj = $res->fetch_object()) !== false)
			$this->mAwards[] = array($obj->TITEL_ID, $obj->AR);

		$res->free();
	}
	
	function load_nominations()
	{
		$res = $this->mDb->select('nominering', array('"POST_ID"', '"AR"'), array('"STUDENT_ID"' => $this->mId));
		
		$this->mNominations = array();
		while(($obj = $res->fetch_object()) !== false)
			$this->mNominations[] = array($obj->POST_ID, $obj->AR);
		
		$res->free();
	}
	
	function load_memberships()
	{
		$res = $this->mDb->select('medlemskap', array('"NAMND_ID"', '"AR"'), array('"STUDENT_ID"' => $this->mId));

		$this->mMemberships = array();
		while(($obj = $res->fetch_object()) !== false)
			$this->mMemberships[] = array($obj->NAMND_ID, $obj->AR);

		$res->free();
	}
	
	function load()
	{
		$this->load_core();
		$this->load_emails();
		$this->load_telephones();
		$this->load_awards();
		$this->load_nominations();
		$this->load_memberships();
	}
	
	function set_last_update()
	{
		$this->mUpdated = date('Y-m-d H:i:s');
		
		$this->mDb->update('student',
			array('"UPPDATERAD"' => $this->mUpdated),
			array('"ID"' => $this->mId));
	}
	
	function commit_core()
	{
		$studentFields = array(
			'"F_NAMN"' => $this->mFirstName,
			'"E_NAMN"' => $this->mLastName,
			'"AK"' => ($this->mStartYear != 'fna' ? $this->mStartYear : NULL),
			'"USERNAME"' => $this->mUsername,
			'"INRIKTNING_ID"' => ($this->mSpecId != -1 ? $this->mSpecId : NULL),
			'"GATUADRESS"' => $this->mStreetAddress,
			'"POSTADRESS"' => $this->mPostalAddress,
			'"ARBETE"' => $this->mWork,
			'"OVRIGT"' => $this->mMisc);
		
		$seniorinfoFields = array('"BET_TOM"' => $this->mPaid,
			'"EXAMEN"' => $this->mGraduation,
			'"YOB"' => $this->mBirth,
			'"SENIOR"' => $this->mSeniorMember,
			'"FORCE"' => $this->mWantsForce,
			'"EPOST"' => $this->mWantsEmail);
		
		$this->mDb->update('student', $studentFields, array('"ID"' => $this->mId));
			
		if($this->mDb->lookup('seniorinfo', array('"STUDENT_ID"' => $this->mId)) != NULL)
			$this->mDb->update('seniorinfo', $seniorinfoFields,
				array('"STUDENT_ID"' => $this->mId));
		else
			$this->mDb->insert('seniorinfo',
				array_merge(array('"STUDENT_ID"' => $this->mId), $seniorinfoFields));
				
		$this->set_last_update();
	}
	
	function commit_emails()
	{
		$res = $this->mDb->select('epost', array('"EPOST"', '"STANDARD"'), array('"STUDENT_ID"' => $this->mId));
		
		$toAdd = $this->mEmail;
		$toRemove = array();
		$toDeStandard = '';
		$toStandard = '';
		
		while(($obj = $res->fetch_object()) !== false)
		{
			$i = $this->array_searchi($obj->EPOST, $toAdd);
			
			if($i === false)
				$toRemove[] = $obj->EPOST;
			else
			{
				unset($toAdd[$i]);
				if($obj->STANDARD && 
					strtolower($obj->EPOST) != strtolower($this->standard_email_address()))
					$toDeStandard = $obj->EPOST;
				else if(!$obj->STANDARD && 
					strtolower($obj->EPOST) == strtolower($this->standard_email_address()))
					$toStandard = $obj->EPOST;
			}
		}
		
		foreach($toAdd as $email)
			$this->mDb->insert('epost', 
				array('"STUDENT_ID"' => $this->mId,
					'"EPOST"' => $email,
					'"STANDARD"' => (strtolower($email) == strtolower($this->standard_email_address()) ? 1 : 0)));
					
		foreach($toRemove as $email)
			$this->mDb->remove('epost',
			 	array('"STUDENT_ID"' => $this->mId,
					'lower("EPOST")' => strtolower($email)));
		
		if($toDeStandard != '')
			$this->mDb->update('epost', array('"STANDARD"' => 0), array('"STUDENT_ID"' => $this->mId, 'lower("EPOST")' => strtolower($toDeStandard)));
		
		if($toStandard != '')
			$this->mDb->update('epost', array('"STANDARD"' => 1), array('"STUDENT_ID"' => $this->mId, 'lower("EPOST")' => strtolower($toStandard)));
		
		$res->free();
		
		$this->set_last_update();
	}
	
	function commit_telephones()
	{
		$res = $this->mDb->select('telefon', array('"TFNTYP_ID"', '"TFN"'), array('"STUDENT_ID"' => $this->mId));
		
		$toAdd = array_keys($this->mTelephone);
		$toRemove = array();
		$toUpdate = array();
		
		while(($obj = $res->fetch_object()) !== false)
		{
			$i = array_search($obj->TFNTYP_ID, $toAdd);
			
			if($i === false)
				$toRemove[] = $obj->TFNTYP_ID;
			else
			{
				unset($toAdd[$i]);
				if($obj->TFN != $this->mTelephone[$obj->TFNTYP_ID])
					$toUpdate[] = $obj->TFNTYP_ID;
			}
		}
		
		$res->free();
		
		foreach($toAdd as $type)
			$this->mDb->insert('telefon', 
				array('"STUDENT_ID"' => $this->mId,
					'"TFNTYP_ID"' => $type, 
					'"TFN"' => $this->mTelephone[$type]));
		
		foreach($toRemove as $type)
			$this->mDb->remove('telefon',
				array('"STUDENT_ID"' => $this->mId,
					'"TFNTYP_ID"' => $type));
		
		foreach($toUpdate as $type)
			$this->mDb->update('telefon',
				array('"TFN"' => $this->mTelephone[$type]),
				array('"STUDENT_ID"' => $this->mId,
					'"TFNTYP_ID"' => $type));

		$this->set_last_update();
	}
	
	function commit_awards()
	{	
		$res = $this->mDb->select('utnamning', array('"TITEL_ID"', '"AR"'), array('"STUDENT_ID"' => $this->mId));
		
		$toAdd = $this->title_ids();
		$toRemove = array();
		$toUpdate = array();
		
		while(($obj = $res->fetch_object()) !== false)
		{
			$i = array_search($obj->TITEL_ID, $toAdd);
			
			if($i === false)
			{
				$toRemove[] = $obj->TITEL_ID;
			}
			else
			{
				unset($toAdd[$i]);
				if($obj->AR != $this->award_year($obj->TITEL_ID))
					$toUpdate[] = $obj->TITEL_ID;
			}
		}	

		$res->free();
		
		foreach($toAdd as $title)
			$this->mDb->insert('utnamning',
				array('"STUDENT_ID"' => $this->mId,
					'"TITEL_ID"' => $title,
					'"AR"' => $this->award_year($title)));
		
		foreach($toRemove as $title)
			$this->mDb->remove('utnamning',
				array('"STUDENT_ID"' => $this->mId,
					'"TITEL_ID"' => $title));
		
		foreach($toUpdate as $title)
			$this->mDb->update('utnamning',
				array('"AR"' => $this->award_year($title)),
				array('"STUDENT_ID"' => $this->mId,
					'"TITEL_ID"' => $title));
		
		$this->set_last_update();
	}
	
	function commit_nominations()
	{
		$res = $this->mDb->select('nominering', array('"POST_ID"', '"AR"'), array('"STUDENT_ID"' => $this->mId));
		
		$toAdd = $this->mNominations;
		$toRemove = array();
		
		while(($obj = $res->fetch_object()) !== false)
		{
			$nomination = array($obj->POST_ID, $obj->AR);
			$i = array_search($nomination, $toAdd);
			
			if($i === false)
				$toRemove[] = $nomination;
			else
				unset($toAdd[$i]);
		}	

		$res->free();
		
		foreach($toAdd as $nomination)
			$this->mDb->insert('nominering',
				array('"STUDENT_ID"' => $this->mId,
					'"POST_ID"' => $nomination[0],
					'"AR"' => $nomination[1]));
		
		foreach($toRemove as $nomination)
			$this->mDb->remove('nominering',
				array('"STUDENT_ID"' => $this->mId,
					'"POST_ID"' => $nomination[0],
					'"AR"' => $nomination[1]));

		$this->set_last_update();
	}
	
	function commit_memberships()
	{
		$res = $this->mDb->select('medlemskap', array('"NAMND_ID"', '"AR"'), array('"STUDENT_ID"' => $this->mId));

		$toAdd = $this->mMemberships;
		$toRemove = array();
		
		while(($obj = $res->fetch_object()) !== false)
		{
			$membership = array($obj->NAMND_ID, $obj->AR);
			$i = array_search($membership, $toAdd);
			
			if($i === false)
			{
				$toRemove[] = $membership;
			}
			else
			{
				unset($toAdd[$i]);
			}
		}
		
		$res->free();
		
		foreach($toAdd as $membership)
			$this->mDb->insert('medlemskap',
				array('"STUDENT_ID"' => $this->mId,
					'"NAMND_ID"' => $membership[0],
					'"AR"' => $membership[1]));
		
		foreach($toRemove as $membership)
			$this->mDb->remove('medlemskap',
				array('"STUDENT_ID"' => $this->mId,
					'"NAMND_ID"' => $membership[0],
					'"AR"' => $membership[1]));

		$this->set_last_update();
	}
	
	function commit()
	{
		$this->commit_core();
		$this->commit_emails();
		$this->commit_telephones();
		$this->commit_awards();
		$this->commit_nominations();
		$this->commit_memberships();
	}
	
	function set_var(&$dst, $src)
	{
		$old = $dst;
		if($src !== null)
			$dst = $src;
		return $old;
	}
	
	function array_searchi($needle, $haystack)
	{
		$needle = strtolower($needle);
		foreach($haystack as $k => $v)
			if($needle == strtolower($v))
				return $k;
		return false;
	}
	
	function id()
	{
		return $this->mId;
	}
	
	function first_name($firstName = null)
	{
		return $this->set_var($this->mFirstName, $firstName);
	}
	
	function last_name($lastName = null)
	{
		return $this->set_var($this->mLastName, $lastName);
	}
	
	function starting_year($startYear = null)
	{
		return $this->set_var($this->mStartYear, $startYear);
	}
	
	function username($username = null)
	{
		return $this->set_var($this->mUsername, $username);
	}
	
	function specialization_id($specId = null)
	{
		return $this->set_var($this->mSpecId, $specId);
	}
	
	function street_address($streetAddress = null)
	{
		return $this->set_var($this->mStreetAddress, $streetAddress);
	}
	
	function postal_address($postalAddress = null)
	{
		return $this->set_var($this->mPostalAddress, $postalAddress);
	}
	
	function work($work = null)
	{
		return $this->set_var($this->mWork, $work);
	}
	
	function miscellaneous($misc = null)
	{
		return $this->set_var($this->mMisc, $misc);
	}
	
	function last_updated()
	{
		return $this->mUpdated;
	}
	
	function has_paid_until($paid = null)
	{
		return $this->set_var($this->mPaid, $paid);	
	}
	
	function graduation_year($graduation = null)
	{
		return $this->set_var($this->mGraduation, $graduation);
	}
	
	function birth_year($birth = null)
	{
		return $this->set_var($this->mBirth, $birth);
	}
	
	function is_senior_member($seniorMember = null)
	{
		return $this->set_var($this->mSeniorMember, $seniorMember);
	}
	
	function wants_force($wantsForce = null)
	{
		return $this->set_var($this->mWantsForce, $wantsForce);
	}
	
	function wants_email($wantsEmail = null)
	{
		return $this->set_var($this->mWantsEmail, $wantsEmail);
	}
	
	function email_addresses()
	{
		return array_values($this->mEmail);
	}
	
	function clear_email_addresses()
	{
		$this->mEmail = array();
		$this->mStandardEmail = -1;
	}
	
	function add_email_address($email, $standard = false)
	{
		if($this->array_searchi($email, $this->mEmail) === false)
			$this->mEmail[] = $email;
		if($standard)
			$this->mStandardEmail = $this->array_searchi($email, $this->mEmail);
	}
	
	function remove_email_address($email)
	{
		if(($i = $this->array_searchi($email, $this->mEmail)) !== false)
		{
			unset($this->mEmail[$i]);
			if($this->mStandardEmail == $i)
				$this->mStandardEmail = -1;
		}
	}
	
	function standard_email_address($standard_email = null)
	{
		if(isset($this->mEmail[$this->mStandardEmail]))
			$old = $this->mEmail[$this->mStandardEmail];
		else
			$old = '';
		
		if(!is_null($standard_email))
		{
			$i = $this->array_searchi($standard_email, $this->mEmail);
			if($i !== false)
				$this->mStandardEmail = $i;
		}
		
		return $old;
	}
	
	function telephone_number_types()
	{
		return array_keys($this->mTelephone);
	}
	
	function clear_telephone_numbers()
	{
		$this->mTelephone = array();
	}
	
	function telephone_number($type)
	{
		return (isset($this->mTelephone[$type]) ? $this->mTelephone[$type] : '');
	}
	
	function add_telephone_number($type, $telephone)
	{
		$this->mTelephone[$type] = $telephone;
	}
	
	function remove_telephone_number($type)
	{
		unset($this->mTelephone[$type]);
	}
	
	function title_ids()
	{
		return array_map(create_function('$a', 'return $a[0];'), $this->mAwards);
	}
	
	function clear_awards()
	{
		$this->mAwards = array();
	}
	
	function award_year($title_id)
	{
		foreach($this->mAwards as $award)
			if($award[0] == $title_id)
				return $award[1];
		return false;
	}
	
	function add_award($title_id, $year)
	{
		if(($i = array_search($title_id, $this->title_ids())) !== false)
			unset($this->mAwards[$i]);
			
		$this->mAwards[] = array($title_id, $year);
	}
	
	function remove_award($title_id)
	{
		$year = $this->award_year($title_id);
		if($year !== false)
			unset($this->mAwards[array_search(array($title_id, $year), $this->mAwards)]);
	}
	
	function position_ids()
	{
		return array_values(
			array_unique(
				array_map(
					create_function('$a', 'return $a[0];'), $this->mNominations)));
	}
	
	function clear_nominations()
	{
		$this->mNominations = array();
	}
	
	function nomination_years($position_id)
	{	
		$years = array();
		foreach($this->mNominations as $nomination)
			if($nomination[0] == $position_id)
				$years[] = $nomination[1];
		if(!empty($years))
			return $years;
		return false;
	}
	
	function add_nomination($position_id, $year)
	{
		if(array_search(array($position_id, $year), $this->mNominations) === false)
			$this->mNominations[] = array($position_id, $year);
	}
	
	function remove_nomination($position_id, $year)
	{
		if(($i = array_search(array($position_id, $year), $this->mNominations)) !== false)
			unset($this->mNominations[$i]);
	}
	
	function group_ids()
	{
		return array_values(
			array_unique(
				array_map(
					create_function('$a', 'return $a[0];'), $this->mMemberships)));
	}
	
	function clear_memberships()
	{
		$this->mMemberships = array();
	}
	
	function membership_years($group_id)
	{
		$years = array();
		foreach($this->mMemberships as $membership)
			if($membership[0] == $group_id)
				$years[] = $membership[1];
		if(!empty($years))
			return $years;
		return false;
	}
	
	function add_membership($group_id, $year)
	{
		if(array_search(array($group_id, $year), $this->mMemberships) === false)
			$this->mMemberships[] = array($group_id, $year);
	}
	
	function remove_membership($group_id, $year)
	{
		if(($i = array_search(array($group_id, $year), $this->mMemberships)) !== false)
			unset($this->mMemberships[$i]);
	}
}

interface StudentCriterion
{
	function tables($db);
	
	function requirement($db);
	
	function satisfies($s);
}

class AndCriterion implements StudentCriterion
{
	var $mSub;
	
	function __construct($sub) { if(is_array($sub)) $this->mSub = $sub; else $this->mSub = func_get_args(); }
	
	function tables($db)
	{
		$t = array(); 
		foreach($this->mSub as $s)
			foreach($s->tables($db) as $st) { if(!in_array($st, $t)) $t[] = $st; } 
		return $t; 
	}
	// ugly. due to array_unique failure
	
	function requirement($db)
	{ 
		$r = array();
		foreach($this->mSub as $s)
			$r[] = $s->requirement($db);
		return $db->and_list($r); 
	}
	
	function satisfies($s)
	{
		$r = true;
		foreach($this->mSub as $sub)
			$r = ($r && $sub->satisfies($s));
		return $r;
	}
}

class OrCriterion implements StudentCriterion
{
	var $mSub;
	
	function __construct($sub) { if(is_array($sub)) $this->mSub = $sub; else $this->mSub = func_get_args(); }

	function tables($db)
	{
		$t = array(); 
		foreach($this->mSub as $s)
			foreach($s->tables($db) as $st) { if(!in_array($st, $t)) $t[] = $st; } 
		return $t;
	}
	// again, ugly
	
	function requirement($db) 
	{
		$r = array();
		foreach($this->mSub as $s)
			$r[] = $s->requirement($db);
		return implode(' OR ', array_map(create_function('$rq', 'return "(" . $rq . ")";'), $r));
	}
	// a $db->or_list() would be nice (and symmetric)
	
	function satisfies($s)
	{
		$r = false;
		foreach($this->mSub as $sub)
			$r = ($r || $sub->satisfies($s));
		return $r;
	}
}

class IsSeniorCriterion implements StudentCriterion
{
	function __construct() { }
	
	function tables($db) { return array(array('seniorinfo', 'STUDENT_ID')); }
	
	function requirement($db) { return $db->and_list(array('seniorinfo."SENIOR"' => 1)); }
	
	function satisfies($s) { return $s->is_senior_member(); }
}

class WantsForceCriterion implements StudentCriterion
{
	function __construct() { }
	
	function tables($db) { return array(array('seniorinfo', 'STUDENT_ID')); }
	
	function requirement($db) { return $db->and_list(array('seniorinfo."FORCE"' => 1)); }
	
	function satisfies($s) { return $s->wants_force(); }
}

class WantsEmailCriterion implements StudentCriterion
{
	function __construct() { }
	
	function tables($db) { return array(array('seniorinfo', 'STUDENT_ID')); }
	
	function requirement($db) { return $db->and_list(array('seniorinfo."EPOST"' => 1)); }
	
	function satisfies($s) { return $s->wants_email(); }
}

class StartingYearCriterion implements StudentCriterion
{
	var $mYear;
	
	function __construct($year) { $this->mYear = $year; }
	
	function starting_year() { return $this->mYear; }
	
	function tables($db) { return array(); }
	
	function requirement($db) { return $db->and_list(array('student."AK"' => $this->mYear)); }
	
	function satisfies($s) { return ($s->starting_year() == $this->mYear); }
}

class AwardCriterion implements StudentCriterion
{
	var $mTitleId, $mYear;
	
	function __construct($titleId, $year = null) { $this->mTitleId = $titleId; $this->mYear = $year; }
	
	function award_title_id() { return $this->mTitleId; }
	
	function award_year() { return $this->mYear; }
	
	function tables($db) { return array(array('utnamning', 'STUDENT_ID')); }
	
	function requirement($db)
	{
		$r = array('utnamning."TITEL_ID"' => $this->mTitleId);
		if($this->mYear)
			$r['utnamning."AR"'] = $this->mYear;
		return $db->and_list($r);
	}
	
	function satisfies($s)
	{
		if($this->mYear)
			return ($s->award_year($this->mTitleId) == $this->mYear);
		return ($s->award_year($this->mTitleId) !== false);
	}
}

class NominationCriterion implements StudentCriterion
{
	var $mPositionId, $mYear;
	
	function __construct($positionId, $year = null) { $this->mPositionId = $positionId; $this->mYear = $year; }
	
	function nomination_position_id() { return $this->mPositionId; }
	
	function nomination_year() { return $this->mYear; }
	
	function tables($db) { return array(array('nominering', 'STUDENT_ID')); }
	
	function requirement($db)
	{
		$r = array('nominering."POST_ID"' => $this->mPositionId);
		if($this->mYear)
			$r['nominering."AR"'] = $this->mYear;
		return $db->and_list($r);
	}
	
	function satisfies($s)
	{
		if($this->mYear)
			return ($s->nomination_years($this->mPositionId) !== false &&
				array_search($this->mYear, $s->nomination_years($this->mPositionId)) !== false);
		return ($s->nomination_years($this->mPositionId) !== false);
	}
}

class MembershipCriterion implements StudentCriterion
{
	var $mGroupId, $mYear;
	
	function __construct($groupId, $year = null) { $this->mGroupId = $groupId; $this->mYear = $year; }
	
	function membership_group_id() { return $this->mGroupId; }
	
	function membership_year() { return $this->mYear; }
	
	function tables($db) { return array(array('medlemskap', 'STUDENT_ID')); }
	
	function requirement($db)
	{
		$r = array('medlemskap."NAMND_ID"' => $this->mGroupId);
		if($this->mYear)
			$r['medlemskap."AR"'] = $this->mYear;
		return $db->and_list($r);
	}
	
	function satisfies($s)
	{
		if($this->mYear)
			return ($s->membership_years($this->mGroupId) !== false &&
				array_search($this->mYear, $s->membership_years($this->mGroupId)) !== false);
		return ($s->membership_years($this->mGroupId) !== false);
	}
}

class NameCriterion implements StudentCriterion
{
	var $mName;
	
	function __construct($name) { $this->mName = $name; }
	
	function name() { return $this->mName; }
	
	function tables($db) { return array(); }
	
	function requirement($db)
	{
		$name = strtolower($this->mName);
		
		return "lower(student.\"F_NAMN\") LIKE '%$name%' OR lower(student.\"E_NAMN\") LIKE '%$name%'";
	}
	
	function satisfies($s)
	{
		return ((stristr($s->first_name(), $this->mName) !== FALSE) || (stristr($s->last_name(), $this->mName)));
	}
}
	
?>