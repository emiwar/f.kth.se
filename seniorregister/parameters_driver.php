<?php

require('db.php');
require('pgdb.php');
require('logger.php');
require('parameters.php');

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

function test_parameters()
{
	echo "<strong>Student test</strong><br />\n";
	
	echo "Connecting to database...";
	$db = new PostgresDatabase('localhost', 'register', 'register', 'register_i_test', new Logger('log/student_db.log', true));
	echo "OK<br />\n";
	
	echo "Clearing tables...";
	
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
	
	echo "Creating parameter records...";
	
	$db->insert('class', array('class_year' => 2004, 'abbrev' => 'f04', 'name' => 'Fanfar'));
	$db->insert('class', array('class_year' => 2005, 'abbrev' => 'f05', 'name' => 'Flörtfrisk'));
	
	$db->insert('specialization', array('spec_id' => 11, 'name' => 'Matematik'));
	$db->insert('specialization', array('spec_id' => 12, 'name' => 'Optik'));
	
	$db->insert('telephone_type', array('type_id' => 21, 'name' => 'Hem'));
	$db->insert('telephone_type', array('type_id' => 22, 'name' => 'Mobil'));
	
	$db->insert('title', array('title_id' => 31, 'name' => 'Kamratstipendiat'));
	$db->insert('title', array('title_id' => 32, 'name' => 'Integralorden'));
	
	$db->insert('committee', array('committee_id' => 41, 'name' => 'Fysiks Klubbmästeri', 'abbrev' => 'fkm*', 'member_name' => 'Marsalk'));
	$db->insert('committee', array('committee_id' => 42, 'name' => 'Styret', 'abbrev' => 'styr', 'member_name' => 'Styretmedlem'));
	
	$db->insert('position', array('position_id' => 51, 'name' => 'Klubbmästare', 'committee_id' => 41, 'gov' => new QueryExpression('TRUE'))); // should be true
	$db->insert('position', array('position_id' => 52, 'name' => 'Spritmästare', 'committee_id' => 41, 'gov' => new QueryExpression('FALSE'))); // ditto
		
	echo "OK<br />\n";
	
	echo "Loading Parameters object...";
	
	$p = new Parameters($db);
	$p->load();
	
	echo "OK<br />\n";
	
	echo "Verifying object data...";
	
	assert(set_equals($p->class_years(), array(2004, 2005)));
	assert($p->class_abbreviation(2004) == 'f04');
	assert($p->class_name(2004) == 'Fanfar');
	assert($p->class_abbreviation(2005) == 'f05');
	assert($p->class_name(2005) == 'Flörtfrisk');
	
	assert(set_equals($p->specialization_ids(), array(11, 12)));
	assert($p->specialization_name(11) == 'Matematik');
	assert($p->specialization_name(12) == 'Optik');
	
	assert(set_equals($p->telephone_type_ids(), array(21, 22)));
	assert($p->telephone_type_name(21) == 'Hem');
	assert($p->telephone_type_name(22) == 'Mobil');
	
	assert(set_equals($p->title_ids(), array(31, 32)));
	assert($p->title_name(31) == 'Kamratstipendiat');
	assert($p->title_name(32) == 'Integralorden');
	
	assert(set_equals($p->committee_ids(), array(41, 42)));
	assert($p->committee_name(41) == 'Fysiks Klubbmästeri');
	assert($p->committee_abbreviation(41) == 'fkm*');
	assert($p->committee_member_name(41) == 'Marsalk');
	assert($p->committee_name(42) == 'Styret');
	assert($p->committee_abbreviation(42) == 'styr');
	assert($p->committee_member_name(42) == 'Styretmedlem');
	
	assert(set_equals($p->position_ids(), array(51, 52)));
	assert($p->position_name(51) == 'Klubbmästare');
	assert($p->position_committee_id(51) == 41);
	assert($p->position_government(51) == true);
	assert($p->position_name(52) == 'Spritmästare');
	assert($p->position_committee_id(52) == 41);
	assert($p->position_government(52) == false); // interesting, mapping problem between 'f' and false, shallow correct
	
	echo "OK<br />\n";
	
	echo "Editing parameters...";
	
	$p->set_class(2004, 'f04', 'FanFar!');
	$p->remove_class(2005);
	$p->set_class(2006, 'f06', 'Friskus');
	
	$p->set_specialization(11, 'Teoretisk Fysik');
	$p->remove_specialization(12);
	$p->set_specialization(13, 'Akustik');
	
	$p->set_telephone_type(21, 'Arbete');
	$p->remove_telephone_type(22);
	$p->set_telephone_type(23, 'Fax');
	
	$p->set_title(31, 'Dumväst');
	$p->remove_title(32);
	$p->set_title(33, 'Hedersmedlem');
	
	$p->set_committee(41, 'Fysiks KlubbMästeri', 'fkm', 'Marsalk');
	$p->remove_committee(42);
	$p->set_committee(43, 'Force', 'frc', 'Partikelfysiker');
	
	$p->set_position(51, 'Force majeure', 41, false);
	$p->remove_position(52);
	$p->set_position(53, 'Force illustratör', 41, true);
	
	echo "OK<br />\n";
	
	echo "Verifying edits...";
	
	assert(set_equals($p->class_years(), array(2004, 2006)));
	assert($p->class_abbreviation(2004) == 'f04');
	assert($p->class_name(2004) == 'FanFar!');
	assert($p->class_abbreviation(2006) == 'f06');
	assert($p->class_name(2006) == 'Friskus');
	
	assert(set_equals($p->specialization_ids(), array(11, 13)));
	assert($p->specialization_name(11) == 'Teoretisk Fysik');
	assert($p->specialization_name(13) == 'Akustik');
	
	assert(set_equals($p->telephone_type_ids(), array(21, 23)));
	assert($p->telephone_type_name(21) == 'Arbete');
	assert($p->telephone_type_name(23) == 'Fax');
	
	assert(set_equals($p->title_ids(), array(31, 33)));
	assert($p->title_name(31) == 'Dumväst');
	assert($p->title_name(33) == 'Hedersmedlem');
	
	assert(set_equals($p->committee_ids(), array(41, 43)));
	assert($p->committee_name(41) == 'Fysiks KlubbMästeri');
	assert($p->committee_abbreviation(41) == 'fkm');
	assert($p->committee_member_name(41) == 'Marsalk');
	assert($p->committee_name(43) == 'Force');
	assert($p->committee_abbreviation(43) == 'frc');
	assert($p->committee_member_name(43) == 'Partikelfysiker');
	
	assert(set_equals($p->position_ids(), array(51, 53)));
	assert($p->position_name(51) == 'Force majeure');
	assert($p->position_committee_id(51) == 41);
	assert($p->position_government(51) == false);
	assert($p->position_name(53) == 'Force illustratör');
	assert($p->position_committee_id(53) == 41);
	assert($p->position_government(53) == true);
	
	echo "OK<br />\n";
	
	echo "Committing edits...";
	
	$p->commit();
	
	echo "OK<br />\n";
	
	echo "Verifying commits...";
	
	$rows = fetch_rows($db->select('class', array('class_year', 'abbrev', 'name'), null));
	assert(set_equals($rows, array(array(2004, 'f04', 'FanFar!'), array(2006, 'f06', 'Friskus'))));
	
	$rows = fetch_rows($db->select('specialization', array('spec_id', 'name'), null));
	assert(set_equals($rows, array(array(11, 'Teoretisk Fysik'), array(13, 'Akustik'))));
	
	$rows = fetch_rows($db->select('telephone_type', array('type_id', 'name'), null));
	assert(set_equals($rows, array(array(21, 'Arbete'), array(23, 'Fax'))));
	
	$rows = fetch_rows($db->select('title', array('title_id', 'name'), null));
	assert(set_equals($rows, array(array(31, 'Dumväst'), array(33, 'Hedersmedlem'))));
	
	$rows = fetch_rows($db->select('committee', array('committee_id', 'name', 'abbrev', 'member_name'), null));
	assert(set_equals($rows, array(array(41, 'Fysiks KlubbMästeri', 'fkm', 'Marsalk'), array(43, 'Force', 'frc', 'Partikelfysiker'))));
	
	$rows = fetch_rows($db->select('position', array('position_id', 'name', 'committee_id', 'gov'), null));
	assert(set_equals($rows, array(array(51, 'Force majeure', 41, 'f'), array(53, 'Force illustratör', 41, 't'))));
	
	echo "OK<br />\n";
	
	echo "Clearing parameters...";
	
	$p->clear_classes();
	$p->clear_specializations();
	$p->clear_telephone_types();
	$p->clear_titles();
	$p->clear_committees();
	$p->clear_positions();
	
	echo "OK<br />\n";
	
	echo "Verifying cleared parameters...";
	
	assert($p->class_years() == array());
	assert($p->class_abbreviation(2004) == null);
	assert($p->class_name(2004) == null);
	
	assert($p->specialization_ids() == array());
	assert($p->specialization_name(2004) == null);
	
	assert($p->telephone_type_ids() == array());
	assert($p->telephone_type_name(21) == null);
	
	assert($p->title_ids() == array());
	assert($p->title_name(31) == null);
	
	assert($p->committee_ids() == array());
	assert($p->committee_name(41) == null);
	assert($p->committee_abbreviation(41) == null);
	assert($p->committee_member_name(41) == null);
	
	assert($p->position_ids() == array());
	assert($p->position_name(51) == null);
	assert($p->position_committee_id(51) == null);
	assert($p->position_government(51) == null);
	
	echo "OK<br />\n";
	
	$db->close();
}

test_parameters();

?>