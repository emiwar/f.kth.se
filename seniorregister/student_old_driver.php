<?php

require('db.php');
require('pgdb.php');
require('logger.php');
require('student_old.php');

function set_equals($a, $b)
{
	sort($a);
	sort($b);
	
	return ($a == $b);
}

function set_equalsi($a, $b)
{
	sort($a);
	sort($b);
	
	if(count($a) != count($b))
		return false;
		
	for($i = 0; $i < count($a); $i++)
		if(strtolower($a[$i]) != strtolower($b[$i]))
			return false;
	
	return true;
}

function fetch_rows($res)
{
	$rows = array();
	
	while(($arr = $res->fetch_array()) !== false)
	{
		// this is not so much fun, there should be a fetch_row in db...
		$row = array();
		$i = 0;
		while(isset($arr[$i]))
			$row[] = $arr[$i++];
		$rows[] = $row;
	}
		
	return $rows;
}

function test_student_db()
{
	echo "<strong>Student test</strong><br />\n";
	
	echo "Connecting to database...";
	$db = new PostgresDatabase('localhost', 'register', 'register', 'register_test', new Logger('student_db.log', true));
	echo "OK<br />\n";
	
	echo "Clearing tables...";
	
	$db->execute('DELETE FROM student');
	$db->execute('DELETE FROM seniorinfo');
	$db->execute('DELETE FROM epost');
	$db->execute('DELETE FROM telefon');
	$db->execute('DELETE FROM utnamning');
	$db->execute('DELETE FROM nominering');
	$db->execute('DELETE FROM medlemskap');
	
	echo "OK<br />\n";
	
	echo "Verifying student creation process...";
	
	$s = Student::create_student($db, 'Peter', 'Ståhl');
	$s = Student::create_student($db, 'Elias', 'Freider');
	
	assert($db->lookup('student', array('"ID"' => 1))->F_NAMN == 'Peter');
	assert($db->lookup('student', array('"ID"' => 2))->F_NAMN == 'Elias');
	
	echo "OK<br />\n";
	
	echo "Creating student records...";
	$db->insert('student',
		array('"ID"' => 21,
		'"F_NAMN"' => 'Joakim',
		'"E_NAMN"' => 'Andén',
		'"AK"' => 'f05',
		'"USERNAME"' => 'janden',
		'"INRIKTNING_ID"' => 13,
		'"GATUADRESS"' => 'Studentbacken 23/315',
		'"POSTADRESS"' => '115 57 STOCKHOLM',
		'"ARBETE"' => 'Programmerare',
		'"OVRIGT"' => 'Skapare av programvaran',
		'"UPPDATERAD"' => '2008-11-22 08:30:04'));
	
	$db->insert('seniorinfo',
		array('"STUDENT_ID"' => 21,
		'"BET_TOM"' => '2010',
		'"EXAMEN"' => '2009',
		'"YOB"' => '1987',
		'"SENIOR"' => 1,
		'"FORCE"' => 0,
		'"EPOST"' => 0));
		
	$db->insert('epost',
		array('"STUDENT_ID"' => 21,
		'"EPOST"' => 'janden@kth.se',
		'"STANDARD"' => 1));
		
	$db->insert('epost',
		array('"STUDENT_ID"' => 21,
		'"EPOST"' => 'janden@nada.kth.se',
		'"STANDARD"' => 0));
		
	$db->insert('epost',
		array('"STUDENT_ID"' => 21,
		'"EPOST"' => 'joakim.anden@gmail.com',
		'"STANDARD"' => 0));
		
	$db->insert('telefon',
		array('"STUDENT_ID"' => 21,
		'"TFNTYP_ID"' => 1,
		'"TFN"' => '08-156801'));
		
	$db->insert('telefon',
		array('"STUDENT_ID"' => 21,
		'"TFNTYP_ID"' => 2,
		'"TFN"' => '076-2685717'));
		
	$db->insert('utnamning',
		array('"STUDENT_ID"' => 21,
		'"TITEL_ID"' => 30,
		'"AR"' => 2001));
		
	$db->insert('nominering',
		array('"STUDENT_ID"' => 21,
		'"POST_ID"' => 44,
		'"AR"' => 2001));
		
	$db->insert('nominering',
		array('"STUDENT_ID"' => 21,
		'"POST_ID"' => 44,
		'"AR"' => 2002));
		
	$db->insert('nominering',
		array('"STUDENT_ID"' => 21,
		'"POST_ID"' => 45,
		'"AR"' => 2001));
		
	$db->insert('medlemskap',
		array('"STUDENT_ID"' => 21,
		'"NAMND_ID"' => 4,
		'"AR"' => 2001));
		
	$db->insert('medlemskap',
		array('"STUDENT_ID"' => 21,
		'"NAMND_ID"' => 4,
		'"AR"' => 2002));
		
	$db->insert('medlemskap',
		array('"STUDENT_ID"' => 21,
		'"NAMND_ID"' => 5,
		'"AR"' => 2001));
	echo "OK<br />\n";
		
	echo "Loading Student object...";
	$s = new Student($db, 21);
	$s->load();
	echo "OK<br />\n";
	
	echo "Verifying object data...";
	assert($s->id() == 21);
	assert($s->first_name() == 'Joakim');
	assert($s->last_name() == 'Andén');
	assert($s->starting_year() == 'f05');
	assert($s->username() == 'janden');
	assert($s->specialization_id() == 13);
	assert($s->street_address() == 'Studentbacken 23/315');
	assert($s->postal_address() == '115 57 STOCKHOLM');
	assert($s->work() == 'Programmerare');
	assert($s->miscellaneous() == 'Skapare av programvaran');
	assert($s->last_updated() == '2008-11-22 08:30:04');
	
	assert($s->has_paid_until() == 2010);
	assert($s->graduation_year() == 2009);
	assert($s->birth_year() == 1987);
	assert($s->is_senior_member() == 1);
	assert($s->wants_force() == 0);
	assert($s->wants_email() == 0);
	
	assert(set_equalsi($s->email_addresses(), 
		array('janden@nada.kth.se', 'janden@kth.se', 'joakim.anden@gmail.com')));	
	assert($s->standard_email_address() == 'janden@kth.se');
	
	assert(set_equals($s->telephone_number_types(),
		array(1, 2)));
	assert($s->telephone_number(1) == '08-156801');
	assert($s->telephone_number(2) == '076-2685717');
	
	assert(set_equals($s->title_ids(), array(30)));
	assert($s->award_year(30) == 2001);	
	
	assert(set_equals($s->position_ids(), array(44, 45)));
	assert(set_equals($s->nomination_years(44), array(2001, 2002)));
	assert(set_equals($s->nomination_years(45), array(2001)));
	
	assert(set_equals($s->group_ids(), array(4, 5)));
	assert(set_equals($s->membership_years(4), array(2001, 2002)));
	assert(set_equals($s->membership_years(5), array(2001)));	
	
	echo "OK<br />\n";
	
	echo "Editing core information...";
	
	$s->first_name('Mats');
	$s->last_name('Abdollahi');
	$s->starting_year('f04');
	$s->username('matsabd');
	$s->specialization_id(11);
	$s->street_address('42, boulevard de Bercy');
	$s->postal_address('750 12 PARIS');
	$s->work('Posten');
	$s->miscellaneous('Bor hos Caroline');
	$s->has_paid_until('2020');
	$s->graduation_year('2011');
	$s->birth_year('1986');
	$s->is_senior_member(0);
	$s->wants_force(1);
	$s->wants_email(1);
	
	echo "OK<br />\n";
	
	echo "Verifying core edits...";
	
	assert($s->id() == 21);
	assert($s->first_name() == 'Mats');
	assert($s->last_name() == 'Abdollahi');
	assert($s->starting_year() == 'f04');
	assert($s->username() == 'matsabd');
	assert($s->specialization_id() == 11);
	assert($s->street_address() == '42, boulevard de Bercy');
	assert($s->postal_address() == '750 12 PARIS');
	assert($s->work() == 'Posten');
	assert($s->miscellaneous() == 'Bor hos Caroline');
	assert($s->last_updated() == '2008-11-22 08:30:04');
	
	assert($s->has_paid_until() == 2020);
	assert($s->graduation_year() == 2011);
	assert($s->birth_year() == 1986);
	assert($s->is_senior_member() == 0);
	assert($s->wants_force() == 1);
	assert($s->wants_email() == 1);
	
	echo "OK<br />\n";
	
	$db->execute('BEGIN');
	
	echo "Committing core information...";
	
	$s->commit_core();
	
	echo "OK<br />\n";
	
	echo "Verifying core records...";
	
	$student_row = $db->lookup('student', array('"ID"' => 21));
	
	assert($student_row->F_NAMN == 'Mats');
	assert($student_row->E_NAMN == 'Abdollahi');
	assert($student_row->AK == 'f04');
	assert($student_row->USERNAME == 'matsabd');
	assert($student_row->INRIKTNING_ID == 11);
	assert($student_row->GATUADRESS == '42, boulevard de Bercy');
	assert($student_row->POSTADRESS == '750 12 PARIS');
	assert($student_row->ARBETE == 'Posten');
	assert($student_row->OVRIGT == 'Bor hos Caroline');
	assert($student_row->UPPDATERAD != '2008-11-22 08:30:04');
	
	$senior_row = $db->lookup('seniorinfo', array('"STUDENT_ID"' => 21));
	
	assert($senior_row->BET_TOM == 2020);
	assert($senior_row->EXAMEN == 2011);
	assert($senior_row->YOB == 1986);
	assert($senior_row->SENIOR == 0);
	assert($senior_row->FORCE == 1);
	assert($senior_row->EPOST == 1);	
	
	echo "OK<br />\n";
	
	$db->execute('ROLLBACK');
	
	echo "Editing and verifying e-mail fields...";
	
	$s->add_email_address('JANDEN@kth.se');
	assert(set_equalsi($s->email_addresses(), 
		array('janden@nada.kth.se', 'janden@kth.se', 'joakim.anden@gmail.com')));
		
	$s->add_email_address('joakim.ANDEN@gmail.com', true);
	assert(strtolower($s->standard_email_address()) == strtolower('joakim.anden@gmail.com'));
	
	$s->add_email_address('frenchwhale@hotmail.com', false);
	assert(set_equalsi($s->email_addresses(), 
		array('janden@nada.kth.se', 'janden@kth.se', 'joakim.anden@gmail.com', 'frenchwhale@hotmail.com')));
	assert(strtolower($s->standard_email_address()) == strtolower('joakim.anden@gmail.com'));
	
	$s->remove_email_address('frenchwhale@hotmail.com');
	assert(set_equalsi($s->email_addresses(), 
		array('janden@nada.kth.se', 'janden@kth.se', 'joakim.anden@gmail.com')));
	assert(strtolower($s->standard_email_address()) == strtolower('joakim.anden@gmail.com'));
	
	$s->remove_email_address('joakim.anden@gmail.com');
	assert(set_equalsi($s->email_addresses(), 
		array('janden@nada.kth.se', 'janden@kth.se')));
	assert($s->standard_email_address() == '');
	
	$s->standard_email_address('janden@NADA.kth.se');
	assert(strtolower($s->standard_email_address()) == strtolower('janden@nada.kth.se'));
	
	$s->standard_email_address('person@example.com');
	assert(strtolower($s->standard_email_address()) == strtolower('janden@nada.kth.se'));
	
	$s->clear_email_addresses();
	assert($s->email_addresses() == array());
	
	echo "OK<br />\n";
	
	echo "Verifying e-mail commit process...";
	
	$db->execute('BEGIN');
	
	$s->load_emails();
	$s->remove_email_address('janden@nada.kth.se');
	$s->add_email_address('frenchwhale@hotmail.com');
	$s->commit_emails();
	
	$rows = fetch_rows($db->select('epost', array('"EPOST"', '"STANDARD"'), array('"STUDENT_ID"' => 21)));
	assert(set_equals($rows, array(array('janden@kth.se', 1), array('joakim.anden@gmail.com', 0), array('frenchwhale@hotmail.com', 0))));
	
	$db->execute('ROLLBACK');
	
	$db->execute('BEGIN');
	
	$s->load_emails();
	$s->add_email_address('frenchwhale@hotmail.com', true);
	$s->commit_emails();
	
	$rows = fetch_rows($db->select('epost', array('"EPOST"', '"STANDARD"'), array('"STUDENT_ID"' => 21)));
	assert(set_equals($rows, array(array('janden@nada.kth.se', 0), array('janden@kth.se', 0), array('joakim.anden@gmail.com', 0), array('frenchwhale@hotmail.com', 1))));
	
	$db->execute('ROLLBACK');
	
	$db->execute('BEGIN');
	
	$s->load_emails();
	$s->standard_email_address('joakim.anden@gmail.com');
	$s->commit_emails();
	
	$rows = fetch_rows($db->select('epost', array('"EPOST"', '"STANDARD"'), array('"STUDENT_ID"' => 21)));
	assert(set_equals($rows, array(array('janden@kth.se', 0), array('joakim.anden@gmail.com', 1), array('janden@nada.kth.se', 0))));
	
	$db->execute('ROLLBACK');
	
	echo "OK<br />\n";
	
	echo "Editing and verifying telephone fields...";
	
	$s->add_telephone_number(1, '+33600112233');
	assert(set_equals($s->telephone_number_types(), array(1, 2)));
	assert($s->telephone_number(1) == '+33600112233');
	
	$s->remove_telephone_number(2);
	assert(set_equals($s->telephone_number_types(), array(1)));
	assert($s->telephone_number(2) == '');
	
	$s->clear_telephone_numbers();
	assert($s->telephone_number_types() == array());
	
	echo "OK<br />\n";
	
	echo "Verifying telephone commit process...";
	
	$db->execute('BEGIN');
	
	$s->load_telephones();
	$s->remove_telephone_number(1);
	$s->add_telephone_number(3, '+16306550966');
	$s->commit_telephones();
	
	$rows = fetch_rows($db->select('telefon', array('"TFNTYP_ID"', '"TFN"'), array('"STUDENT_ID"' => 21)));
	assert(set_equals($rows, array(array(2, '076-2685717'), array(3, '+16306550966'))));
	
	$db->execute('ROLLBACK');
	
	$db->execute('BEGIN');
	
	$s->load_telephones();
	$s->add_telephone_number(2, '+33600112233');
	$s->commit_telephones();
	
	$rows = fetch_rows($db->select('telefon', array('"TFNTYP_ID"', '"TFN"'), array('"STUDENT_ID"' => 21)));
	assert(set_equals($rows, array(array(1, '08-156801'), array(2, '+33600112233'))));
	
	$db->execute('ROLLBACK');
	
	echo "OK<br />\n";
	
	echo "Editing and verifying award fields...";
	
	$s->add_award(31, 2002);
	$s->add_award(30, 2004);
	assert(set_equals($s->title_ids(), array(30, 31)));
	assert($s->award_year(30) == 2004);
	assert($s->award_year(31) == 2002);
	assert($s->award_year(32) == false);
	
	$s->remove_award(30);
	$s->remove_award(32);	
	assert(set_equals($s->title_ids(), array(31)));
	
	$s->clear_awards();
	assert(set_equals($s->title_ids(), array()));
	
	echo "OK<br />\n";
	
	echo "Verifying the award commit process...";
	
	$db->execute('BEGIN');
	
	$s->load_awards();
	$s->remove_award(30);
	$s->add_award(31, 2005);
	$s->commit_awards();
	
	$rows = fetch_rows($db->select('utnamning', array('"TITEL_ID"', '"AR"'), array('"STUDENT_ID"' => 21)));
	assert(set_equals($rows, array(array(31, 2005))));
	
	$db->execute('ROLLBACK');
	
	$db->execute('BEGIN');
	
	$s->load_awards();
	$s->add_award(30, 2004);
	$s->commit_awards();
	
	$rows = fetch_rows($db->select('utnamning', array('"TITEL_ID"', '"AR"'), array('"STUDENT_ID"' => 21)));
	assert(set_equals($rows, array(array(30, 2004))));
	
	$db->execute('ROLLBACK');
	
	echo "OK<br />\n";
	
	echo "Editing and verifying nomination fields...";
	
	$s->add_nomination(44, 2003);
	$s->add_nomination(47, 2004);
	assert(set_equals($s->position_ids(), array(44, 45, 47)));
	assert(set_equals($s->nomination_years(44), array(2001, 2002, 2003)));
	assert(set_equals($s->nomination_years(47), array(2004)));
	assert($s->nomination_years(48) == false);
	
	$s->remove_nomination(40, 2000);
	$s->remove_nomination(44, 2000);
	$s->remove_nomination(44, 2001);
	$s->remove_nomination(45, 2001);
	assert(set_equals($s->position_ids(), array(44, 47)));	
	assert(set_equals($s->nomination_years(44), array(2002, 2003)));
	
	$s->clear_nominations();
	assert(set_equals($s->position_ids(), array()));
	
	echo "OK<br />\n";
	
	echo "Verifying nomination commit process...";
	
	$db->execute('BEGIN');
	
	$s->load_nominations();
	$s->remove_nomination(44, 2001);
	$s->add_nomination(47, 2004);
	$s->commit_nominations();
	
	$rows = fetch_rows($db->select('nominering', array('"POST_ID"', '"AR"'), array('"STUDENT_ID"' => 21)));
	assert(set_equals($rows, array(array(44, 2002), array(45, 2001), array(47, 2004))));
	
	$db->execute('ROLLBACK');
	
	echo "OK<br />\n";
	
	echo "Editing and verifying membership fields...";
	
	$s->add_membership(4, 2003);
	$s->add_membership(7, 2004);
	assert(set_equals($s->group_ids(), array(4, 5, 7)));
	assert(set_equals($s->membership_years(4), array(2001, 2002, 2003)));
	assert(set_equals($s->membership_years(7), array(2004)));
	assert($s->membership_years(10) == false);
	
	$s->remove_membership(2, 2000);
	$s->remove_membership(4, 2000);
	$s->remove_membership(4, 2001);
	$s->remove_membership(5, 2001);
	assert(set_equals($s->group_ids(), array(4, 7)));
	assert(set_equals($s->membership_years(4), array(2002, 2003)));
	
	$s->clear_memberships();
	assert(set_equals($s->group_ids(), array()));
	
	echo "OK<br />\n";
	
	echo "Verifying membership commit process...";
	
	$db->execute('BEGIN');
	
	$s->load_memberships();
	$s->remove_membership(4, 2001);
	$s->add_membership(7, 2003);
	$s->commit_memberships();
	
	$rows = fetch_rows($db->select('medlemskap', array('"NAMND_ID"', '"AR"'), array('"STUDENT_ID"' => 21)));
	assert(set_equals($rows, array(array(4, 2002), array(5, 2001), array(7, 2003))));
	
	$db->execute('ROLLBACK');
	
	echo "OK<br />\n";
	
	$db->close();
}

function test_list()
{
	echo "<strong>List test</strong><br />\n";
	
	echo "Connecting to database...";
	$db = new PostgresDatabase('localhost', 'register', 'register', 'register_test', new Logger('student_db.log', true));
	echo "OK<br />\n";
	
	echo "Clearing tables...";
	
	$db->execute('DELETE FROM student');
	$db->execute('DELETE FROM seniorinfo');
	$db->execute('DELETE FROM epost');
	$db->execute('DELETE FROM telefon');
	$db->execute('DELETE FROM utnamning');
	$db->execute('DELETE FROM nominering');
	$db->execute('DELETE FROM medlemskap');
	
	echo "OK<br />\n";
	
	echo "Creating students...";
	
	$s = array(Student::create_student($db, 'Joakim', 'Andén'),
		Student::create_student($db, 'Mats', 'Abdollahi'),
		Student::create_student($db, 'Peter', 'Ståhl'),
		Student::create_student($db, 'Elias', 'Freider'));

	$s[0]->is_senior_member(1);
	$s[1]->is_senior_member(1);
		
	$s[1]->wants_force(1);
	$s[2]->wants_force(1);
	
	$s[2]->wants_email(1);
	$s[3]->wants_email(1);
	
	$s[0]->starting_year('f05');
	$s[1]->starting_year('f04');
	$s[2]->starting_year('f06');
	$s[3]->starting_year('f05');
	
	$s[0]->add_award(10, 2005);
	$s[0]->add_award(11, 2006);
	$s[1]->add_award(10, 2004);
	
	$s[1]->add_nomination(20, 2004);
	$s[1]->add_nomination(21, 2003);
	$s[2]->add_nomination(20, 2006);
	
	$s[2]->add_membership(30, 2005);
	$s[2]->add_membership(31, 2005);
	$s[3]->add_membership(30, 2006);
	
	foreach($s as $student)
		$student->commit();
	
	echo "OK<br />\n";
	
	echo "Verifying IsSeniorCriterion...";
	
	$l = Student::list_students($db, new IsSeniorCriterion());
	assert(set_equals(array_keys($l), array(1, 2)));
	
	$c = new IsSeniorCriterion();
	
	$s = new Student($db, 1);
	$s->load();
	assert($c->satisfies($s));
	
	$s = new Student($db, 4);
	$s->load();
	assert(!$c->satisfies($s));
	
	echo "OK<br />\n";
	
	echo "Verifying WantsForceCriterion...";
	
	$l = Student::list_students($db, new WantsForceCriterion());
	assert(set_equals(array_keys($l), array(2, 3)));
	
	$c = new WantsForceCriterion();
	
	$s = new Student($db, 2);
	$s->load();
	assert($c->satisfies($s));
	
	$s = new Student($db, 4);
	$s->load();
	assert(!$c->satisfies($s));
	
	echo "OK<br />\n";
	
	echo "Verifying WantsEmailCriterion...";
	
	$l = Student::list_students($db, new WantsEmailCriterion());
	assert(set_equals(array_keys($l), array(3, 4)));
	
	$c = new WantsEmailCriterion();
	
	$s = new Student($db, 3);
	$s->load();
	assert($c->satisfies($s));
	
	$s = new Student($db, 1);
	$s->load();
	assert(!$c->satisfies($s));
	
	echo "OK<br />\n";
	
	echo "Verifying StartingYearCriterion...";
	
	$c = new StartingYearCriterion('f08');
	assert($c->starting_year() == 'f08');
	
	$l = Student::list_students($db, new StartingYearCriterion('f05'));
	assert(set_equals(array_keys($l), array(1, 4)));
	
	$l = Student::list_students($db, new StartingYearCriterion('f06'));
	assert(set_equals(array_keys($l), array(3)));
	
	$l = Student::list_students($db, new StartingYearCriterion('f07'));
	assert(array_keys($l) == array());
	
	$c = new StartingYearCriterion('f05');
	
	$s = new Student($db, 1);
	$s->load();
	assert($c->satisfies($s));
	
	$s = new Student($db, 3);
	$s->load();
	assert(!$c->satisfies($s));
	
	echo "OK<br />\n";
	
	echo "Verifying AwardCriterion...";
	
	$c = new AwardCriterion(10, 2005);
	assert($c->award_title_id() == 10);
	assert($c->award_year() == 2005);
	
	$c = new AwardCriterion(11);
	assert($c->award_title_id() == 11);
	assert($c->award_year() == null);
	
	$l = Student::list_students($db, new AwardCriterion(10));
	assert(set_equals(array_keys($l), array(1, 2)));
	
	$l = Student::list_students($db, new AwardCriterion(10, 2004));
	assert(set_equals(array_keys($l), array(2)));
	
	$l = Student::list_students($db, new AwardCriterion(15));
	assert(array_keys($l) == array());
	
	$c = new AwardCriterion(10);
	
	$s = new Student($db, 1);
	$s->load();
	assert($c->satisfies($s));
	
	$s = new Student($db, 4);
	$s->load();
	assert(!$c->satisfies($s));
	
	echo "OK<br />";
	
	echo "Verifying NominationCriterion...";
	
	$c = new NominationCriterion(20, 2003);
	assert($c->nomination_position_id() == 20);
	assert($c->nomination_year() == 2003);
	
	$c = new NominationCriterion(21);
	assert($c->nomination_position_id() == 21);
	assert($c->nomination_year() == null);
	
	$l = Student::list_students($db, new NominationCriterion(20));
	assert(set_equals(array_keys($l), array(2, 3)));
	
	$l = Student::list_students($db, new NominationCriterion(20, 2004));
	assert(set_equals(array_keys($l), array(2)));
	
	$l = Student::list_students($db, new NominationCriterion(25));
	assert(array_keys($l) == array());
	
	$c = new NominationCriterion(20);
	
	$s = new Student($db, 2);
	$s->load();
	assert($c->satisfies($s));
	
	$s = new Student($db, 4);
	$s->load();
	assert(!$c->satisfies($s));
	
	echo "OK<br />\n";
	
	echo "Verifying MembershipCriterion...";

	$c = new MembershipCriterion(30, 2004);
	assert($c->membership_group_id() == 30);
	assert($c->membership_year() == 2004);
	
	$c = new MembershipCriterion(31);
	assert($c->membership_group_id() == 31);
	assert($c->membership_year() == null);	
	
	$l = Student::list_students($db, new MembershipCriterion(30));
	assert(set_equals(array_keys($l), array(3, 4)));
	
	$l = Student::list_students($db, new MembershipCriterion(30, 2006));
	assert(set_equals(array_keys($l), array(4)));
	
	$l = Student::list_students($db, new MembershipCriterion(35));
	assert(array_keys($l) == array());
	
	$c = new MembershipCriterion(30);
	
	$s = new Student($db, 3);
	$s->load();
	assert($c->satisfies($s));
	
	$s = new Student($db, 2);
	$s->load();
	assert(!$c->satisfies($s));
	
	echo "OK<br />\n";
	
	echo "Verifying NameCriterion...";
	
	$c = new NameCriterion('jocke');
	assert($c->name() == 'jocke');
	
	$l = Student::list_students($db, new NameCriterion('Joakim'));
	assert(set_equals(array_keys($l), array(1)));
	
	$l = Student::list_students($db, new NameCriterion('ATS'));
	assert(set_equals(array_keys($l), array(2)));
	
	$l = Student::list_students($db, new NameCriterion('m'));
	assert(set_equals(array_keys($l), array(1, 2)));
	
	$l = Student::list_students($db, new NameCriterion('ö'));
	assert(array_keys($l) == array());
	
	$c = new NameCriterion('ATS');
	
	$s = new Student($db, 2);
	$s->load();
	assert($c->satisfies($s));
	
	$s = new Student($db, 3);
	$s->load();
	assert(!$c->satisfies($s));
	
	echo "OK<br />\n";
	
	echo "Verifying AndCriterion...";
	
	$l = Student::list_students($db, new AndCriterion(new MembershipCriterion(30), new NominationCriterion(20, 2006), new WantsForceCriterion()));
	assert(set_equals(array_keys($l), array(3)));
	
	$l = Student::list_students($db, new AndCriterion(new AwardCriterion(10, 2004), new NominationCriterion(21), new IsSeniorCriterion()));
	assert(set_equals(array_keys($l), array(2)));
	
	$l = Student::list_students($db, new AndCriterion(new IsSeniorCriterion(), new AwardCriterion(10)));
	assert(set_equals(array_keys($l), array(1, 2)));
	
	$l = Student::list_students($db, new AndCriterion(new IsSeniorCriterion(), new WantsEmailCriterion()));
	assert(array_keys($l) == array());
	
	$c = new AndCriterion(new IsSeniorCriterion(), new AwardCriterion(10));
	
	$s = new Student($db, 1);
	$s->load();
	assert($c->satisfies($s));
	
	$s = new Student($db, 3);
	$s->load();
	assert(!$c->satisfies($s));
	
	echo "OK<br />\n";
	
	echo "Verifying OrCriterion...";
	
	$l = Student::list_students($db, new OrCriterion(new IsSeniorCriterion(), new WantsForceCriterion(), new WantsEmailCriterion()));
	assert(set_equals(array_keys($l), array(1, 2, 3, 4)));
	
	$l = Student::list_students($db, new OrCriterion(new IsSeniorCriterion(), new MembershipCriterion(30, 2006)));
	assert(set_equals(array_keys($l), array(1, 2, 4)));	
	
	$l = Student::list_students($db, new OrCriterion(new WantsForceCriterion(), new NominationCriterion(20)));
	assert(set_equals(array_keys($l), array(2, 3)));
	
	$l = Student::list_students($db, new OrCriterion(new AwardCriterion(15), new NominationCriterion(25)));
	assert(array_keys($l) == array());
	
	$c = new OrCriterion(new WantsForceCriterion(), new NominationCriterion(20));
	
	$s = new Student($db, 2);
	$s->load();
	assert($c->satisfies($s));
	
	$s = new Student($db, 4);
	$s->load();
	assert(!$c->satisfies($s));
	
	echo "OK<br />\n";
	
	$db->close();
}

test_student_db();

test_list();

?>