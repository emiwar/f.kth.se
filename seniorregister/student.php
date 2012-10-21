<?php

/*require('db.php');
require('pgdb.php');
require('logger.php');*/

require_once('auth.php');

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
	
	// variables need to be initialized properly
	
	function create_student($db, $firstName, $lastName)
	{
		$max = $db->select_one('student', 'MAX(student_id) as m', '');
		$id = $max->m+1;
		
		$db->insert('student', 
			array('"student_id"' => $id, 
				'"first_name"' => $firstName, 
				'"last_name"' => $lastName, 
				'"class_year"' => NULL,
				'"username"' => NULL,
				'"spec_id"' => NULL,
				'"street_address"' => NULL,
				'"postal_address"' => NULL,
				'"work"' => NULL,
				'"misc"' => NULL,
				'"paid_until"' => NULL,
				'"graduation"' => NULL,
				'"birthyear"' => NULL,
				'"senior"' => new QueryExpression('FALSE'),
				'"wants_force"' => new QueryExpression('FALSE'),
				'"wants_email"' => new QueryExpression('FALSE'),
				'"last_updated"' => date('Y-m-d h:i:s')));
		
		$s = new Student($db, $id);
		$s->load();
		
		return $s;
	}
	
	function list_students($db, $criteria, $fetch_email = false)
	{
		$tables = $criteria->tables($db);
		$requirement = $criteria->requirement($db);
		//$fields = array('student."student_id"', 'student."first_name"', 'student."last_name"', 'student."class_year"');
		$fields = array('student."first_name"', 'student."last_name"', 'student."class_year"');
		
		if($fetch_email)
		{
			// note that this will cause problems if one of the criteria is on the e-mail fields, shouldn't with the new merge_tables thingy though
			$tables[] = array('email', 'student_id');
			$requirement = "($requirement) AND (email.\"student_id\" IS NULL OR email.\"standard\" = TRUE)";
			// warning: this requirement above will fail if a student has only non-standard email addresses
			$fields[] = 'email."email"';
		}
	
		/*$from = 'student';
		foreach($tables as $t)
			$from .= ' LEFT JOIN ' . $t[0] . ' ON student."student_id" = ' . $t[0] . '."' . $t[1] . '"';
		
		$res = $db->select($from, $fields, $requirement);
		
		$list = array();
		
		while(($obj = $res->fetch_object()) !== false)
			if(!$fetch_email)
				$list[$obj->student_id] = array($obj->first_name, $obj->last_name, $obj->class_year);
			else
				$list[$obj->student_id] = array($obj->first_name, $obj->last_name, $obj->class_year, $obj->email);
		
		$res->free();
		
		return $list;*/
		
		return Student::list_data($db, $tables, $fields, $requirement);
	}
	
	function list_data($db, $tables, $fields, $qual)
	{
		$from = 'student';
		foreach($tables as $t)
			$from .= ' LEFT JOIN ' . $t[0] . ' ON student."student_id" = ' . $t[0] . '."' . $t[1] . '"';
		$fields = array_merge(array('student."student_id"'), $fields);
		
		//print_r($qual);
		
		$res = $db->select($from, $fields, $qual);
		
		$list = array();
		
		while(($arr = $res->fetch_array()) !== false)
		{
			$arr_numeric = array();
			foreach($arr as $k => $v)
				if(is_numeric($k) && $k != 0)
					$arr_numeric[$k-1] = $v;
			$list[$arr[0]] = $arr_numeric;
		}
		
		$res->free();
		
		return $list;
	}
	
	function __construct($db, $id = null)
	{
		$this->mDb = $db;
		$this->mId = $id;
	}
	
	// should have a function to test existence...
	
	function load_core()
	{
		$obj = $this->mDb->lookup('student', array('"student_id"' => $this->mId));
		
		if($obj === FALSE)
		{
			$this->mId = -1;
			return;
		}
		
		$this->mFirstName = $obj->first_name;
		$this->mLastName = $obj->last_name;
		$this->mStartYear = ($obj->class_year !== NULL ? $obj->class_year : 0);
		$this->mUsername = $obj->username;
		$this->mSpecId = ($obj->spec_id !== NULL ? $obj->spec_id : -1);
		$this->mStreetAddress = $obj->street_address;
		$this->mPostalAddress = $obj->postal_address;
		$this->mWork = $obj->work;
		$this->mMisc = $obj->misc;
		$this->mUpdated = $obj->last_updated;
		
		$this->mPaid = $obj->paid_until;
		$this->mGraduation = $obj->graduation;
		$this->mBirth = $obj->birthyear;
		$this->mSeniorMember = ($obj->senior == 't' || $obj->senior == 1);
		$this->mWantsForce = ($obj->wants_force == 't' || $obj->wants_force == 1);
		$this->mWantsEmail = ($obj->wants_email == 't' || $obj->wants_email == 1);
	}
	
	function load_emails()
	{
		$res = $this->mDb->select('email', array('"email"', '"standard"'), array('"student_id"' => $this->mId));
		
		$this->mEmail = array();
		$this->mStandardEmail = 0;
		$i = 0;
		while(($obj = $res->fetch_object()) !== false)
		{
			$this->mEmail[$i] = $obj->email;
			if($obj->standard == 't' || $obj->standard == 1)
				$this->mStandardEmail = $i;
			
			$i++;
		}
		
		$res->free();
	}
	
	function load_telephones()
	{
		$res = $this->mDb->select('telephone', array('"type_id"', '"number"'), array('"student_id"' => $this->mId));
		
		$this->mTelephone = array();
		while(($obj = $res->fetch_object()) !== false)
			$this->mTelephone[$obj->type_id] = $obj->number;
		
		$res->free();
	}
	
	function load_awards()
	{	
		// we here assume that one can only receive an award once, as opposed to nominations
		
		$res = $this->mDb->select('award', array('"title_id"', '"year"'), array('"student_id"' => $this->mId));
		
		$this->mAwards = array();
		while(($obj = $res->fetch_object()) !== false)
			$this->mAwards[] = array($obj->title_id, $obj->year);

		$res->free();
	}
	
	function load_nominations()
	{
		$res = $this->mDb->select('nomination', array('"position_id"', '"year"'), array('"student_id"' => $this->mId));
		
		$this->mNominations = array();
		while(($obj = $res->fetch_object()) !== false)
			$this->mNominations[] = array($obj->position_id, $obj->year);
		
		$res->free();
	}
	
	function load_memberships()
	{
		$res = $this->mDb->select('membership', array('"committee_id"', '"year"'), array('"student_id"' => $this->mId));

		$this->mMemberships = array();
		while(($obj = $res->fetch_object()) !== false)
			$this->mMemberships[] = array($obj->committee_id, $obj->year);

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
			array('"last_updated"' => $this->mUpdated),
			array('"student_id"' => $this->mId));
	}
	
	function commit_core()
	{
		$studentFields = array(
			'"first_name"' => $this->mFirstName,
			'"last_name"' => $this->mLastName,
			'"class_year"' => ($this->mStartYear != 0 ? $this->mStartYear : NULL),
			'"username"' => $this->mUsername,
			'"spec_id"' => ($this->mSpecId != -1 ? $this->mSpecId : NULL),
			'"street_address"' => $this->mStreetAddress,
			'"postal_address"' => $this->mPostalAddress,
			'"work"' => $this->mWork,
			'"misc"' => $this->mMisc,
			'"paid_until"' => $this->mPaid,
			'"graduation"' => $this->mGraduation,
			'"birthyear"' => $this->mBirth,
			'"senior"' => new QueryExpression($this->mSeniorMember ? 'TRUE' : 'FALSE'),
			'"wants_force"' => new QueryExpression($this->mWantsForce ? 'TRUE' : 'FALSE'),
			'"wants_email"' => new QueryExpression($this->mWantsEmail ? 'TRUE' : 'FALSE'));
		
		$this->mDb->update('student', $studentFields, array('"student_id"' => $this->mId));
		
		$this->set_last_update();
	}
	
	function commit_emails()
	{
		$res = $this->mDb->select('email', array('"email"', '"standard"'), array('"student_id"' => $this->mId));
		
		$toAdd = $this->mEmail;
		$toRemove = array();
		$toDeStandard = '';
		$toStandard = '';
		
		/*while(($obj = $res->fetch_object()) !== false)
		{
			$i = $this->array_searchi($obj->email, $toAdd);
			
			if($i === false)
				$toRemove[] = $obj->email;
			else
			{
				unset($toAdd[$i]);
				if($obj->standard == 't' && 
					strtolower($obj->email) != strtolower($this->standard_email_address()))
					$toDeStandard = $obj->email;
				else if($obj->standard == 'f' && 
					strtolower($obj->email) == strtolower($this->standard_email_address()))
					$toStandard = $obj->email;
			}
		}*/
		
		$this->mDb->execute('DELETE FROM email WHERE student_id = ' . "'" . $this->mId . "'");
		
		// this is to preserve order in the e-mails. some better solution will be thought of
		// also, see below, this behavior seems to be postgres-based
		
		foreach($toAdd as $email)
			$this->mDb->insert('email', 
				array('"student_id"' => $this->mId,
					'"email"' => $email,
					'"standard"' => (strtolower($email) == strtolower($this->standard_email_address()) ? 1 : 0)));
					// XXX
					// seems to just change the order
					
		foreach($toRemove as $email)
			$this->mDb->remove('email',
			 	array('"student_id"' => $this->mId,
					'lower("email")' => strtolower($email)));
		
		if($toDeStandard != '')
			$this->mDb->update('email', array('"standard"' => 0), array('"student_id"' => $this->mId, 'lower("email")' => strtolower($toDeStandard)));
		
		if($toStandard != '')
			$this->mDb->update('email', array('"standard"' => 1), array('"student_id"' => $this->mId, 'lower("email")' => strtolower($toStandard)));
		
		$res->free();
		
		$this->set_last_update();
	}
	
	function commit_telephones()
	{
		$res = $this->mDb->select('telephone', array('"type_id"', '"number"'), array('"student_id"' => $this->mId));
		
		$toAdd = array_keys($this->mTelephone);
		$toRemove = array();
		$toUpdate = array();
		
		while(($obj = $res->fetch_object()) !== false)
		{
			$i = array_search($obj->type_id, $toAdd);
			
			if($i === false)
				$toRemove[] = $obj->type_id;
			else
			{
				unset($toAdd[$i]);
				if($obj->number != $this->mTelephone[$obj->type_id])
					$toUpdate[] = $obj->type_id;
			}
		}
		
		$res->free();
		
		foreach($toAdd as $type)
			$this->mDb->insert('telephone', 
				array('"student_id"' => $this->mId,
					'"type_id"' => $type, 
					'"number"' => $this->mTelephone[$type]));
		
		foreach($toRemove as $type)
			$this->mDb->remove('telephone',
				array('"student_id"' => $this->mId,
					'"type_id"' => $type));
		
		foreach($toUpdate as $type)
			$this->mDb->update('telephone',
				array('"number"' => $this->mTelephone[$type]),
				array('"student_id"' => $this->mId,
					'"type_id"' => $type));

		$this->set_last_update();
	}
	
	function commit_awards()
	{	
		$res = $this->mDb->select('award', array('"title_id"', '"year"'), array('"student_id"' => $this->mId));
		
		$toAdd = $this->title_ids();
		$toRemove = array();
		$toUpdate = array();
		
		while(($obj = $res->fetch_object()) !== false)
		{
			$i = array_search($obj->title_id, $toAdd);
			
			if($i === false)
			{
				$toRemove[] = $obj->title_id;
			}
			else
			{
				unset($toAdd[$i]);
				if($obj->year != $this->award_year($obj->title_id))
					$toUpdate[] = $obj->title_id;
			}
		}	

		$res->free();
		
		foreach($toAdd as $title)
			$this->mDb->insert('award',
				array('"student_id"' => $this->mId,
					'"title_id"' => $title,
					'"year"' => $this->award_year($title)));
		
		foreach($toRemove as $title)
			$this->mDb->remove('award',
				array('"student_id"' => $this->mId,
					'"title_id"' => $title));
		
		foreach($toUpdate as $title)
			$this->mDb->update('award',
				array('"year"' => $this->award_year($title)),
				array('"student_id"' => $this->mId,
					'"title_id"' => $title));
		
		$this->set_last_update();
	}
	
	function commit_nominations()
	{
		$res = $this->mDb->select('nomination', array('"position_id"', '"year"'), array('"student_id"' => $this->mId));
		
		$toAdd = $this->mNominations;
		$toRemove = array();
		
		while(($obj = $res->fetch_object()) !== false)
		{
			$nomination = array($obj->position_id, $obj->year);
			$i = array_search($nomination, $toAdd);
			
			if($i === false)
				$toRemove[] = $nomination;
			else
				unset($toAdd[$i]);
		}	

		$res->free();
		
		foreach($toAdd as $nomination)
			$this->mDb->insert('nomination',
				array('"student_id"' => $this->mId,
					'"position_id"' => $nomination[0],
					'"year"' => $nomination[1]));
		
		foreach($toRemove as $nomination)
			$this->mDb->remove('nomination',
				array('"student_id"' => $this->mId,
					'"position_id"' => $nomination[0],
					'"year"' => $nomination[1]));

		$this->set_last_update();
	}
	
	function commit_memberships()
	{
		$res = $this->mDb->select('membership', array('"committee_id"', '"year"'), array('"student_id"' => $this->mId));

		$toAdd = $this->mMemberships;
		$toRemove = array();
		
		while(($obj = $res->fetch_object()) !== false)
		{
			$membership = array($obj->committee_id, $obj->year);
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
			$this->mDb->insert('membership',
				array('"student_id"' => $this->mId,
					'"committee_id"' => $membership[0],
					'"year"' => $membership[1]));
		
		foreach($toRemove as $membership)
			$this->mDb->remove('membership',
				array('"student_id"' => $this->mId,
					'"committee_id"' => $membership[0],
					'"year"' => $membership[1]));

		$this->set_last_update();
	}
	
	function commit()
	{
		// interesting: things that are not loaded cause problems when committed. what to do about this?
		
		$this->commit_core();
		$this->commit_emails();
		$this->commit_telephones();
		$this->commit_awards();
		$this->commit_nominations();
		$this->commit_memberships();
	}
	
	function remove()
	{
		$this->load();
		
		$this->clear_email_addresses();
		$this->clear_telephone_numbers();
		$this->clear_awards();
		$this->clear_nominations();
		$this->clear_memberships();
		
		$this->commit();
		
		$this->mDb->remove('student', array('"student_id"' => $this->id()));
	}
	
	// what if we want to set null values...?
	function set_var(&$dst, $src)
	{
		$old = $dst;
		if($src !== null)
			$dst = $src;
		return $old;
	}
	
	function set_var_ex(&$dst, $args)
	{
		$old = $dst;
		if(count($args) == 1)
			$dst = $args[0];
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
	
	function starting_year()
	{
		$args = func_get_args(); return $this->set_var_ex($this->mStartYear, $args);
	}
	
	function username()
	{
		$args = func_get_args(); return $this->set_var_ex($this->mUsername, $args);
	}
	
	function specialization_id()
	{
		$args = func_get_args(); return $this->set_var_ex($this->mSpecId, $args);
	}
	
	function street_address()
	{
		$args = func_get_args(); return $this->set_var_ex($this->mStreetAddress, $args);
	}
	
	function postal_address()
	{
		$args = func_get_args(); return $this->set_var_ex($this->mPostalAddress, $args);
	}
	
	function work()
	{
		$args = func_get_args(); return $this->set_var_ex($this->mWork, $args);
	}
	
	function miscellaneous()
	{
		$args = func_get_args(); return $this->set_var_ex($this->mMisc, $args);
	}
	
	function last_updated()
	{
		return $this->mUpdated;
	}
	
	function has_paid_until()
	{
		$args = func_get_args(); return $this->set_var_ex($this->mPaid, $args);	
	}
	
	function graduation_year()
	{
		$args = func_get_args(); return $this->set_var_ex($this->mGraduation, $args);
	}
	
	function birth_year()
	{
		$args = func_get_args(); return $this->set_var_ex($this->mBirth, $args);
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

class NoneCriterion implements StudentCriterion
{
	function tables($db)
	{
		return array();
	}
	
	function requirement($db)
	{
		return 'FALSE';
	}
	
	function satisfies($s)
	{
		return true;
	}
}

class AnyCriterion implements StudentCriterion
{
	function tables($db)
	{
		return array();
	}
	
	function requirement($db)
	{
		return 'TRUE';
	}
	
	function satisfies($s)
	{
		return true;
	}
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

class NotCriterion implements StudentCriterion
{
	var $mSub;
	
	function __construct($sub) { $this->mSub = $sub; }
	
	function tables($db)
	{
		return $this->mSub->tables($db);
	}
	
	function requirement($db)
	{
		return 'NOT ('.$this->mSub->requirement($db).')';
	}
	
	function satisfies($s)
	{
		return !$this->mSub->satisfies($s);
	}
}

class IsSeniorCriterion implements StudentCriterion
{
	function __construct() { }
	
	function tables($db) { return array(); }
	
	function requirement($db) { return $db->and_list(array('student."senior"' => 1)); }
	
	function satisfies($s) { return $s->is_senior_member(); }
}

class WantsForceCriterion implements StudentCriterion
{
	function __construct() { }
	
	function tables($db) { return array(); }
	
	function requirement($db) { return $db->and_list(array('student."wants_force"' => 1)); }
	
	function satisfies($s) { return $s->wants_force(); }
}

class WantsEmailCriterion implements StudentCriterion
{
	function __construct() { }
	
	function tables($db) { return array(); }
	
	function requirement($db) { return $db->and_list(array('student."wants_email"' => 1)); }
	
	function satisfies($s) { return $s->wants_email(); }
}

class StartingYearCriterion implements StudentCriterion
{
	var $mYear;
	
	function __construct($year) { $this->mYear = $year; }
	
	function starting_year() { return $this->mYear; }
	
	function tables($db) { return array(); }
	
	function requirement($db) { return $db->and_list(array('student."class_year"' => $this->mYear)); }
	
	function satisfies($s) { return ($s->starting_year() == $this->mYear); }
}

class AwardCriterion implements StudentCriterion
{
	var $mTitleId, $mYear;
	
	function __construct($titleId, $year = null) { $this->mTitleId = $titleId; $this->mYear = $year; }
	
	function award_title_id() { return $this->mTitleId; }
	
	function award_year() { return $this->mYear; }
	
	function tables($db) { return array(array('award', 'student_id')); }
	
	function requirement($db)
	{
		$r = array('award."title_id"' => $this->mTitleId);
		if($this->mYear)
			$r['award."year"'] = $this->mYear;
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
	
	function tables($db) { return array(array('nomination', 'student_id')); }
	
	function requirement($db)
	{
		$r = array('nomination."position_id"' => $this->mPositionId);
		if($this->mYear)
			$r['nomination."year"'] = $this->mYear;
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
	
	function tables($db) { return array(array('membership', 'student_id')); }
	
	function requirement($db)
	{
		$r = array('membership."committee_id"' => $this->mGroupId);
		if($this->mYear)
			$r['membership."year"'] = $this->mYear;
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
		$names = split(' ', strtolower($this->mName));
		
		$reqs = array();
		foreach($names as $name)
			$reqs[] = "(lower(\"student\".\"first_name\") LIKE '%$name%' OR lower(\"student\".\"last_name\") LIKE '%$name%')";
			
		return implode(' AND ', $reqs);
	}
	
	function satisfies($s)
	{
		return ((stristr($s->first_name(), $this->mName) !== FALSE) || (stristr($s->last_name(), $this->mName)));
	}
}

class IdCriterion implements StudentCriterion
{
	var $mId;
	
	function __construct($id) { $this->mId = $id; }
	
	function id() { return $this->mId; }
	
	function tables($db) { return array(); }
	
	function requirement($db)
	{
		return $db->and_list(array('student."student_id"' => $this->mId));
	}
	
	function satisfies($s)
	{
		return $s->id() == $this->mId;
	}
}

class HasUserCriterion implements StudentCriterion
{
	function __construct() { }
	
	function tables($db) { return array(array('auth_user', 'owns_student_id')); }
	
	function requirement($db) { return '"auth_user"."user_id" IS NOT NULL'; }
	
	function satisfies($s)
	{
		// TODO
		return FALSE;
	}
}

class HasInviteCriterion implements StudentCriterion
{
	function __construct() { }
	
	function tables($db) { return array(array('auth_invite', 'owns_student_id')); }
	
	function requirement($db) { return '"auth_invite"."invite_code" IS NOT NULL'; }
	
	function satisfies($s)
	{
		// TODO
		return FALSE;
	}
}
	
?>