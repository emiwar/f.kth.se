<?php

require('db.php');
require('pgdb.php');
require('logger.php');
require('student.php');

require_once('auth.php');

require_once('utils.php');

function test_student_db()
{
	echo "<strong>Student test</strong><br />\n";
	
	echo "Connecting to database...";
	$db = new PostgresDatabase('localhost', 'register', 'register', 'register_i_test', new Logger('log/student_db.log', true));
	echo "OK<br />\n";
	
	echo "Clearing tables...";

	$db->execute('DELETE FROM auth_invite');
	$db->execute('DELETE FROM auth_access');
	$db->execute('DELETE FROM auth_member');
	$db->execute('DELETE FROM auth_group');
	$db->execute('DELETE FROM auth_user');
	
	$db->execute('DELETE FROM nomination');
	$db->execute('DELETE FROM award');	
	$db->execute('DELETE FROM membership');
	$db->execute('DELETE FROM telephone');
	$db->execute('DELETE FROM email');
	$db->execute('DELETE FROM student');
	$db->execute('DELETE FROM position');
	$db->execute('DELETE FROM committee');
	$db->execute('DELETE FROM title');
	$db->execute('DELETE FROM telephone_type');
	$db->execute('DELETE FROM specialization');
	$db->execute('DELETE FROM class');
	
	echo "OK<br />\n";
	
	echo "Verifying student creation process...";
	
	$s = Student::create_student($db, 'Peter', 'Ståhl');
	$s = Student::create_student($db, 'Elias', 'Freider');
	
	assert($db->lookup('student', array('"student_id"' => 1))->first_name == 'Peter');
	assert($db->lookup('student', array('"student_id"' => 2))->first_name == 'Elias');
	
	echo "OK<br />\n";
	
	$db->insert('class', array('"class_year"' => 2005, '"abbrev"' => 'f05', '"name"' => 'Flörtfrisk'));
	$db->insert('class', array('"class_year"' => 2004, '"abbrev"' => 'f04', '"name"' => 'Fanfar'));
	$db->insert('specialization', array('"spec_id"' => 11, '"name"' => 'Inriktning #1'));
	$db->insert('specialization', array('"spec_id"' => 13, '"name"' => 'Inriktning #2'));
	$db->insert('committee', array('"committee_id"' => 4, '"name"' => 'Namnd #1'));
	$db->insert('committee', array('"committee_id"' => 5, '"name"' => 'Namnd #2'));
	$db->insert('committee', array('"committee_id"' => 7, '"name"' => 'Namnd #3'));
	$db->insert('position', array('"position_id"' => 44, '"name"' => 'Post #1', '"committee_id"' => 4));
	$db->insert('position', array('"position_id"' => 45, '"name"' => 'Post #2', '"committee_id"' => 5));
	$db->insert('position', array('"position_id"' => 47, '"name"' => 'Post #3', '"committee_id"' => 7));
	$db->insert('title', array('"title_id"' => 30, '"name"' => 'Titel #1'));
	$db->insert('title', array('"title_id"' => 31, '"name"' => 'Titel #2'));
	$db->insert('telephone_type', array('"type_id"' => 1, '"name"' => 'Hem'));
	$db->insert('telephone_type', array('"type_id"' => 2, '"name"' => 'Mobil'));
	$db->insert('telephone_type', array('"type_id"' => 3, '"name"' => 'Fax'));
	
	echo "Creating student records...";
	$db->insert('student',
		array('"student_id"' => 21,
		'"first_name"' => 'Joakim',
		'"last_name"' => 'Andén',
		'"class_year"' => 2005,
		'"username"' => 'janden',
		'"spec_id"' => 13,
		'"street_address"' => 'Studentbacken 23/315',
		'"postal_address"' => '115 57 STOCKHOLM',
		'"work"' => 'Programmerare',
		'"misc"' => 'Skapare av programvaran',
		'"last_updated"' => '2008-11-22 08:30:04',
		'"paid_until"' => '2010',
		'"graduation"' => '2009',
		'"birthyear"' => '1987',
		'"senior"' => 1,
		'"wants_force"' => 0,
		'"wants_email"' => 0));
		
	$db->insert('email',
		array('"student_id"' => 21,
		'"email"' => 'janden@kth.se',
		'"standard"' => 1));
		
	$db->insert('email',
		array('"student_id"' => 21,
		'"email"' => 'janden@nada.kth.se',
		'"standard"' => 0));
		
	$db->insert('email',
		array('"student_id"' => 21,
		'"email"' => 'joakim.anden@gmail.com',
		'"standard"' => 0));
		
	$db->insert('telephone',
		array('"student_id"' => 21,
		'"type_id"' => 1,
		'"number"' => '08-156801'));
		
	$db->insert('telephone',
		array('"student_id"' => 21,
		'"type_id"' => 2,
		'"number"' => '076-2685717'));
		
	$db->insert('award',
		array('"student_id"' => 21,
		'"title_id"' => 30,
		'"year"' => 2001));
		
	$db->insert('nomination',
		array('"student_id"' => 21,
		'"position_id"' => 44,
		'"year"' => 2001));
		
	$db->insert('nomination',
		array('"student_id"' => 21,
		'"position_id"' => 44,
		'"year"' => 2002));
		
	$db->insert('nomination',
		array('"student_id"' => 21,
		'"position_id"' => 45,
		'"year"' => 2001));
		
	$db->insert('membership',
		array('"student_id"' => 21,
		'"committee_id"' => 4,
		'"year"' => 2001));
		
	$db->insert('membership',
		array('"student_id"' => 21,
		'"committee_id"' => 4,
		'"year"' => 2002));
		
	$db->insert('membership',
		array('"student_id"' => 21,
		'"committee_id"' => 5,
		'"year"' => 2001));
	echo "OK<br />\n";
	
	echo "Loading Student object...";
	$s = new Student($db, 21);
	$s->load();
	echo "OK<br />\n";
	
	echo "Verifying object data...";
	assert($s->id() == 21);
	assert($s->first_name() == 'Joakim');
	assert($s->last_name() == 'Andén');
	assert($s->starting_year() == 2005);
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
	assert($s->is_senior_member() == true);
	assert($s->wants_force() == false);
	assert($s->wants_email() == false);
	
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
	$s->starting_year(2004);
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
	assert($s->starting_year() == 2004);
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
	assert($s->is_senior_member() == false);
	assert($s->wants_force() == true);
	assert($s->wants_email() == true);
	
	echo "OK<br />\n";
	
	$db->execute('BEGIN');
	
	echo "Committing core information...";
	
	// when testing commits, should try to leave blanks for certain values, this can cause problems
	
	$s->commit_core();
	
	echo "OK<br />\n";
	
	echo "Verifying core records...";
	
	$student_row = $db->lookup('student', array('"student_id"' => 21));
	
	assert($student_row->first_name == 'Mats');
	assert($student_row->last_name == 'Abdollahi');
	assert($student_row->class_year == 2004);
	assert($student_row->username == 'matsabd');
	assert($student_row->spec_id == 11);
	assert($student_row->street_address == '42, boulevard de Bercy');
	assert($student_row->postal_address == '750 12 PARIS');
	assert($student_row->work == 'Posten');
	assert($student_row->misc == 'Bor hos Caroline');
	assert($student_row->last_updated != '2008-11-22 08:30:04');
	
	assert($student_row->paid_until == 2020);
	assert($student_row->graduation == 2011);
	assert($student_row->birthyear == 1986);
	assert($student_row->senior != 't' && $student_row->senior != 1);
	assert($student_row->wants_force == 't' && $student_row->wants_force != 1);
	assert($student_row->wants_email == 't' && $student_row->wants_email != 1);	
	
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
	
	$rows = fetch_rows($db->select('email', array('"email"', '"standard"'), array('"student_id"' => 21)));
	assert(set_equals($rows, array(array('janden@kth.se', 't'), array('joakim.anden@gmail.com', 'f'), array('frenchwhale@hotmail.com', 'f'))));
	
	$db->execute('ROLLBACK');
	
	$db->execute('BEGIN');
	
	$s->load_emails();
	$s->add_email_address('frenchwhale@hotmail.com', true);
	$s->commit_emails();
	
	$rows = fetch_rows($db->select('email', array('"email"', '"standard"'), array('"student_id"' => 21)));
	assert(set_equals($rows, array(array('janden@nada.kth.se', 'f'), array('janden@kth.se', 'f'), array('joakim.anden@gmail.com', 'f'), array('frenchwhale@hotmail.com', 't'))));
	
	$db->execute('ROLLBACK');
	
	$db->execute('BEGIN');
	
	$s->load_emails();
	$s->standard_email_address('joakim.anden@gmail.com');
	$s->commit_emails();
	
	$rows = fetch_rows($db->select('email', array('"email"', '"standard"'), array('"student_id"' => 21)));
	assert(set_equals($rows, array(array('janden@kth.se', 'f'), array('joakim.anden@gmail.com', 't'), array('janden@nada.kth.se', 'f'))));
	
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
	
	// the strangest thing happened at this point. the commit failed, causing psql to ignore
	// all subsequent commands to be ignored, which in turn led to a faulty query in the selects
	// that were passed to fetch_rows. here, the problem became even worse as rows were to be
	// fetched from booleans. something went very wrong inside the database code.
	
	$rows = fetch_rows($db->select('telephone', array('"type_id"', '"number"'), array('"student_id"' => 21)));
	assert(set_equals($rows, array(array(2, '076-2685717'), array(3, '+16306550966'))));
	
	$db->execute('ROLLBACK');
	
	$db->execute('BEGIN');
	
	$s->load_telephones();
	$s->add_telephone_number(2, '+33600112233');
	$s->commit_telephones();
	
	$rows = fetch_rows($db->select('telephone', array('"type_id"', '"number"'), array('"student_id"' => 21)));
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
	
	$rows = fetch_rows($db->select('award', array('"title_id"', '"year"'), array('"student_id"' => 21)));
	assert(set_equals($rows, array(array(31, 2005))));
	
	$db->execute('ROLLBACK');
	
	$db->execute('BEGIN');
	
	$s->load_awards();
	$s->add_award(30, 2004);
	$s->commit_awards();
	
	$rows = fetch_rows($db->select('award', array('"title_id"', '"year"'), array('"student_id"' => 21)));
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
	
	$rows = fetch_rows($db->select('nomination', array('"position_id"', '"year"'), array('"student_id"' => 21)));
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
	
	$rows = fetch_rows($db->select('membership', array('"committee_id"', '"year"'), array('"student_id"' => 21)));
	assert(set_equals($rows, array(array(4, 2002), array(5, 2001), array(7, 2003))));
	
	$db->execute('ROLLBACK');
	
	echo "OK<br />\n";
	
	echo "Verifying removal commit process...";
	
	$db->execute('BEGIN');
	
	$s->remove();
	
	$rows = fetch_rows($db->select('email', array('"email"', '"standard"'), array('"student_id"' => 21)));
	assert($rows == array());
	
	$rows = fetch_rows($db->select('telephone', array('"type_id"', '"number"'), array('"student_id"' => 21)));
	assert($rows == array());
	
	$rows = fetch_rows($db->select('award', array('"title_id"', '"year"'), array('"student_id"' => 21)));
	assert($rows == array());
	
	$rows = fetch_rows($db->select('nomination', array('"position_id"', '"year"'), array('"student_id"' => 21)));
	assert($rows == array());
	
	$rows = fetch_rows($db->select('membership', array('"committee_id"', '"year"'), array('"student_id"' => 21)));
	assert($rows == array());
	
	$db->execute('ROLLBACK');
	
	echo "OK<br />\n";
	
	$db->close();
}

function test_list()
{
	echo "<strong>List test</strong><br />\n";
	
	echo "Connecting to database...";
	$db = new PostgresDatabase('localhost', 'register', 'register', 'register_i_test', new Logger('log/student_db.log', true));
	echo "OK<br />\n";
	
	echo "Clearing tables...";

	$db->execute('DELETE FROM auth_invite');
	$db->execute('DELETE FROM auth_access');
	$db->execute('DELETE FROM auth_member');
	$db->execute('DELETE FROM auth_group');
	$db->execute('DELETE FROM auth_user');
	
	$db->execute('DELETE FROM nomination');
	$db->execute('DELETE FROM award');	
	$db->execute('DELETE FROM membership');
	$db->execute('DELETE FROM telephone');
	$db->execute('DELETE FROM email');
	$db->execute('DELETE FROM student');
	$db->execute('DELETE FROM position');
	$db->execute('DELETE FROM committee');
	$db->execute('DELETE FROM title');
	$db->execute('DELETE FROM telephone_type');
	$db->execute('DELETE FROM specialization');
	$db->execute('DELETE FROM class');
	
	echo "OK<br />\n";
	
	echo "Creating students...";
	
	$db->insert('class', array('"class_year"' => 2006, '"abbrev"' => 'f06', '"name"' => 'Friskus'));
	$db->insert('class', array('"class_year"' => 2005, '"abbrev"' => 'f05', '"name"' => 'Flörtfrisk'));
	$db->insert('class', array('"class_year"' => 2004, '"abbrev"' => 'f04', '"name"' => 'Fanfar'));
	$db->insert('committee', array('"committee_id"' => 30, '"name"' => 'Namnd #1'));
	$db->insert('committee', array('"committee_id"' => 31, '"name"' => 'Namnd #2'));
	$db->insert('position', array('"position_id"' => 20, '"name"' => 'Post #1', '"committee_id"' => 30));
	$db->insert('position', array('"position_id"' => 21, '"name"' => 'Post #2', '"committee_id"' => 31));
	$db->insert('title', array('"title_id"' => 10, '"name"' => 'Titel #1'));
	$db->insert('title', array('"title_id"' => 11, '"name"' => 'Titel #2'));
	
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
	
	$s[0]->starting_year(2005);
	$s[1]->starting_year(2004);
	$s[2]->starting_year(2006);
	$s[3]->starting_year(2005);
	
	$s[0]->add_award(10, 2005);
	$s[0]->add_award(11, 2006);
	$s[1]->add_award(10, 2004);
	
	$s[1]->add_nomination(20, 2004);
	$s[1]->add_nomination(21, 2003);
	$s[2]->add_nomination(20, 2006);
	
	$s[2]->add_membership(30, 2005);
	$s[2]->add_membership(31, 2005);
	$s[3]->add_membership(30, 2006);
	
	$s[1]->add_email_address('matsabd@kth.se', true);
	$s[1]->add_email_address('mats.bahman@gmail.com', false);
	$s[2]->add_email_address('petsta@kth.se', true);
	$s[3]->add_email_address('freider@kth.se', true);
	
	foreach($s as $student)
		$student->commit();
		
	AuthUser::create_user($db, 'janden', 'abc123', false, 1);
	AuthUser::create_user($db, 'matsabd', 'ABC123', false, 2);
	
	AuthInvite::create_invite($db, false, 3);
	AuthInvite::create_invite($db, false, 4);
	
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
	
	$c = new StartingYearCriterion(2008);
	assert($c->starting_year() == 2008);
	
	$l = Student::list_students($db, new StartingYearCriterion(2005));
	assert(set_equals(array_keys($l), array(1, 4)));
	
	$l = Student::list_students($db, new StartingYearCriterion(2006));
	assert(set_equals(array_keys($l), array(3)));
	
	$l = Student::list_students($db, new StartingYearCriterion(2008));
	assert(array_keys($l) == array());
	
	$c = new StartingYearCriterion(2005);
	
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
	
	echo "Verifying IdCriterion...";
	
	$c = new IdCriterion(2);
	assert($c->id() == 2);
	
	$l = Student::list_students($db, new IdCriterion(2));
	assert(set_equals(array_keys($l), array(2)));
	
	$l = Student::list_students($db, new IdCriterion(66));
	assert(set_equals(array_keys($l), array()));
	
	$c = new IdCriterion(2);
	
	$s = new Student($db, 2);
	$s->load();
	assert($c->satisfies($s));
	
	$s = new Student($db, 3);
	$s->load();
	assert(!$c->satisfies($s));
	
	echo "OK<br />\n";
	
	echo "Verifying NoneCriterion...";
	
	$l = Student::list_students($db, new NoneCriterion());
	assert(set_equals(array_keys($l), array()));
	
	echo "OK<br />\n";
	
	echo "Verifying AnyCriterion...";
	
	$l = Student::list_students($db, new AnyCriterion());
	assert(set_equals(array_keys($l), array(1, 2, 3, 4)));
	
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
	
	echo "Verifying NotCriterion...";
	
	$l = Student::list_students($db, new NotCriterion(new NoneCriterion()));
	assert(set_equals(array_keys($l), array(1, 2, 3, 4)));
	
	$l = Student::list_students($db, new NotCriterion(new AnyCriterion()));
	assert(set_equals(array_keys($l), array()));
	
	$l = Student::list_students($db, new NotCriterion(new IdCriterion(3)));
	assert(set_equals(array_keys($l), array(1, 2, 4)));
	
	$c = new NotCriterion(new IdCriterion(3));
	
	$s = new Student($db, 1);
	$s->load();
	assert($c->satisfies($s));
	
	$s = new Student($db, 3);
	$s->load();
	assert(!$c->satisfies($s));
	
	echo "OK<br />\n";
	
	echo "Verifying HasUserCriterion...";
	
	$l = Student::list_students($db, new HasUserCriterion());
	assert(set_equals(array_keys($l), array(1, 2)));
	
	// TODO
	
	echo "OK<br />\n";
	
	echo "Verifying HasInviteCriterion...";
	
	$l = Student::list_students($db, new HasInviteCriterion());
	assert(set_equals(array_keys($l), array(3, 4)));
	
	// TODO
	
	echo "OK<br />\n";
	
	echo "Verifying e-mail address listing...";
	
	$l = Student::list_students($db, new AnyCriterion(), true);
	assert($l[1][3] == '');
	assert($l[2][3] == 'matsabd@kth.se');
	assert($l[3][3] == 'petsta@kth.se');
	assert($l[4][3] == 'freider@kth.se');
	
	echo "OK<br />\n";
	
	$db->close();
}

test_student_db();

test_list();

?>