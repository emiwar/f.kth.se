<?php

require_once('db.php');
require_once('pgdb.php');
require_once('logger.php');
require_once('student.php');

require_once('utils.php');

require_once('auth.php');

echo "Connecting to database...";
$db = new PostgresDatabase('localhost', 'register', 'register', 'register_i_test', new Logger('log/auth_db.log', true));
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

echo "Creating dummy environment...";

Student::create_student($db, 'Peter', 'Ståhl');
Student::create_student($db, 'Elias', 'Freider');
Student::create_student($db, 'Frida', 'Halvarsson');
Student::create_student($db, 'John', 'Dahl');
Student::create_student($db, 'Erik', 'Duse');

echo "OK<br />\n";

echo "Creating users and groups...";

$db->insert('auth_user',
	array('user_id' => 100, 
		'username' => 'janden', 
		'password' => password_crypt('abc123'), 
		'super' => new QueryExpression('TRUE'), 
		'owns_student_id' => NULL));
$db->insert('auth_user',
	array('user_id' => 101, 
		'username' => 'petsta', 
		'password' => password_crypt('abcABC'), 
		'super' => new QueryExpression('FALSE'), 
		'owns_student_id' => 1));
$db->insert('auth_user',
	array('user_id' => 102, 
		'username' => 'freider', 
		'password' => password_crypt('123123'), 
		'super' => new QueryExpression('FALSE'), 
		'owns_student_id' => 2));
$db->insert('auth_group',
	array('group_id' => ALL_GROUP, 'name' => '*'));
$db->insert('auth_group',
	array('group_id' => USER_GROUP, 'name' => 'user'));
$db->insert('auth_group',
	array('group_id' => 200, 'name' => 'admin'));
$db->insert('auth_group',
	array('group_id' => 201, 'name' => 'manager'));
$db->insert('auth_access',
	array('group_id' => ALL_GROUP, 'priv_id' => VIEW_CORE_PRIV));
$db->insert('auth_access',
	array('group_id' => USER_GROUP, 'priv_id' => VIEW_OWN_CORE_PRIV));
$db->insert('auth_access',
	array('group_id' => USER_GROUP, 'priv_id' => EDIT_OWN_CORE_PRIV));
$db->insert('auth_access',
	array('group_id' => 200, 'priv_id' => MANAGE_USER_PRIV));
$db->insert('auth_access',
	array('group_id' => 200, 'priv_id' => MANAGE_GROUP_PRIV));
$db->insert('auth_access',
	array('group_id' => 201, 'priv_id' => VIEW_CORE_PRIV));
$db->insert('auth_access',
	array('group_id' => 201, 'priv_id' => EDIT_CORE_PRIV));
$db->insert('auth_access',
	array('group_id' => 201, 'priv_id' => VIEW_CONTACT_PRIV));
$db->insert('auth_member',
	array('user_id' => 100, 'group_id' => 200));
$db->insert('auth_member',
	array('user_id' => 100, 'group_id' => 201));
$db->insert('auth_member',
	array('user_id' => 101, 'group_id' => 201));
$db->insert('auth_invite', 
	array('invite_code' => 'hh3euvua', 'super' => new QueryExpression('TRUE'), 'owns_student_id' => NULL));
$db->insert('auth_invite', 
	array('invite_code' => '7xi4namy', 'super' => new QueryExpression('FALSE'), 'owns_student_id' => 4));

echo "OK<br />\n";

echo "Verifying user listing...";

$l = AuthUser::list_users($db);
assert(set_equals(array_keys($l), array(100, 101, 102)));
assert($l[100][1]);
assert(!$l[102][1]);
assert($l[100][2] == -1);

$l = AuthUser::list_users($db, 'a');
assert(set_equals(array_keys($l), array(100, 101)));

$l = AuthUser::list_users($db, 'eid');
assert(set_equals(array_keys($l), array(102)));

$l = AuthUser::list_users($db, '', true);
assert(set_equals(array_keys($l), array(100)));

$l = AuthUser::list_users($db, '', false);
assert(set_equals(array_keys($l), array(101, 102)));

$l = AuthUser::list_users($db, '', NULL);
assert(set_equals(array_keys($l), array(100, 101, 102)));

echo "OK<br />\n";

echo "Verifying group listing...";

$l = AuthGroup::list_groups($db);
assert(set_equals(array_keys($l), array(ALL_GROUP, USER_GROUP, 200, 201)));

echo "OK<br />\n";

echo "Verifying group loading...";

$g = new AuthGroup($db, ALL_GROUP);
$g->load();
assert($g->name() == '*');
assert(set_equals($g->privilege_ids(), array(VIEW_CORE_PRIV)));

$g = new AuthGroup($db, 201);
$g->load();
assert($g->name() == 'manager');
assert(set_equals($g->privilege_ids(), array(VIEW_CORE_PRIV, EDIT_CORE_PRIV, VIEW_CONTACT_PRIV)));

echo "OK<br />\n";

echo "Editing group data...";

$g = new AuthGroup($db, ALL_GROUP);
$g->load();
$g->name('all');
assert($g->name() == 'all');
assert($g->can_do(VIEW_CORE_PRIV));
$g->add_privilege(VIEW_CONTACT_PRIV);
$g->remove_privilege(VIEW_CORE_PRIV);
assert(set_equals($g->privilege_ids(), array(VIEW_CONTACT_PRIV)));
assert($g->can_do(VIEW_CONTACT_PRIV));
$g->clear_privileges();
assert($g->privilege_ids() == array());

echo "OK<br />\n";

echo "Verifying group commit...";

$db->execute("BEGIN");

$g = new AuthGroup($db, 201);
$g->load();
$g->name('manage');
$g->remove_privilege(EDIT_CORE_PRIV);
$g->add_privilege(EDIT_CONTACT_PRIV);
$g->commit();

$obj = $db->lookup('auth_group', array('group_id' => 201));
assert($obj->name == 'manage');

$rows = fetch_rows($db->select('auth_access', array('priv_id'), array('group_id' => 201)));
assert(set_equals($rows, array(array(VIEW_CORE_PRIV), array(VIEW_CONTACT_PRIV), array(EDIT_CONTACT_PRIV))));

$db->execute("ROLLBACK");

echo "OK<br />\n";

echo "Verifying user loading...";

$u = new AuthUser($db, 200);
$u->load();
assert($u->user_id() == -1);

$u = new AuthUser($db, 100);
$u->load();
assert($u->username() == 'janden');
assert($u->compare_password('abc123'));
assert($u->super());
assert($u->owns_student_id() == -1);
assert(set_equals($u->group_ids(), array(SUPER_GROUP, ALL_GROUP, USER_GROUP, 200, 201)));
assert(set_equals($u->privilege_ids(), array(VIEW_CORE_PRIV, VIEW_OWN_CORE_PRIV, EDIT_OWN_CORE_PRIV, MANAGE_USER_PRIV, MANAGE_GROUP_PRIV, EDIT_CORE_PRIV, VIEW_CONTACT_PRIV)));
assert($u->is_member(SUPER_GROUP));
assert($u->is_member(USER_GROUP));
assert($u->is_member(201));
assert(!$u->is_member(205));
assert($u->can_do(VIEW_CORE_PRIV));
assert($u->can_do(EDIT_CONTACT_PRIV));

$u = new AuthUSer($db, 101);
$u->load();
assert(!$u->super());
assert(!$u->is_member(SUPER_GROUP));
assert($u->can_do(VIEW_CONTACT_PRIV));
assert(!$u->can_do(EDIT_CONTACT_PRIV));

echo "OK<br />\n";

echo "Editing user data...";

$u = new AuthUser($db, 100);
$u->load();
$u->username('hjanden');
assert($u->username() == 'hjanden');
$u->set_password('123abc');
assert($u->compare_password('123abc'));
$u->super(FALSE);
assert(!$u->super());
$u->owns_student_id(500);
assert($u->owns_student_id() == 500);
$u->owns_student_id(-1);
assert($u->owns_student_id() == -1);

$u->remove_membership(201);
$u->add_membership(202);
assert(set_equals($u->group_ids(), array(SUPER_GROUP, ALL_GROUP, USER_GROUP, 200, 202)));

$u->clear_memberships();
assert($u->group_ids() == array());

echo "OK<br />\n";

echo "Verifying user commit...";

$db->execute("BEGIN");

$u = new AuthUser($db, 101);
$u->load();
$u->username('petstar');
$u->set_password('abc560');
$u->super(TRUE);
$u->owns_student_id(-1);
$u->remove_membership(201);
$u->add_membership(200);
$u->commit();

$obj = $db->lookup('auth_user', array('user_id' => 101));
assert($obj->username == 'petstar');
assert($obj->password == password_crypt('abc560'));
assert($obj->super == 't' || $obj->super == 1);
assert($obj->owns_student_id == NULL);

$rows = fetch_rows($db->select('auth_member', array('group_id'), array('user_id' => 101)));
assert(set_equals($rows, array(array(200))));

$db->execute("ROLLBACK");

echo "OK<br />\n";

echo "Verifying new_from_username...";

$u = AuthUser::new_from_username($db, 'janden');
assert($u->user_id() == 100);
$u = AuthUser::new_from_username($db, 'hjanden');
assert($u->user_id() == -1);

echo "OK<br />\n";

echo "Verifying new_from_student...";

$u = AuthUser::new_from_student($db, 2);
assert($u->user_id() == 102);

echo "OK<br />\n";

echo "Verifying compare_password...";

$u = new AuthUser($db, 100);
$u->load();
assert($u->compare_password('abc123'));
assert(!$u->compare_password('abc124'));

echo "OK<br />\n";

echo "Verifying user creation...";

$db->execute('BEGIN');

$u = AuthUser::create_user($db, 'marnords', '123pqr');
assert($u->user_id() == 103);
assert($u->username() == 'marnords');
assert($u->compare_password('123pqr'));
assert($u->super() == false);
assert($u->owns_student_id() == -1);

$u = AuthUser::create_user($db, 'ollanta', '123pqr', true);
assert($u->user_id() == 104);
assert($u->username() == 'ollanta');
assert($u->compare_password('123pqr'));
assert($u->super() == true);
assert($u->owns_student_id() == -1);

$u = AuthUser::create_user($db, 'fridaha', '123pqr', false, 3);
assert($u->user_id() == 105);
assert($u->username() == 'fridaha');
assert($u->compare_password('123pqr'));
assert($u->super() == false);
assert($u->owns_student_id() == 3);

$u = new AuthUser($db, 104);
$u->load();
assert($u->username() == 'ollanta');

$db->execute('ROLLBACK');

echo "OK<br />\n";

echo "Verifying group creation...";

$g = AuthGroup::create_group($db, 'editor');
assert($g->group_id() == 202);
assert($g->name() == 'editor');

$g = new AuthGroup($db, 202);
$g->load();
assert($g->name() == 'editor');

echo "OK<br />\n";

echo "Verifying invite listing...";

$l = AuthInvite::list_invites($db);
assert(set_equals(array_keys($l), array('hh3euvua', '7xi4namy')));
assert(!$l['7xi4namy'][0]);
assert($l['7xi4namy'][1] == 4);
assert($l['7xi4namy'][2] == 'John Dahl');

$l = AuthInvite::list_invites($db, true);
assert(set_equals(array_keys($l), array('hh3euvua')));

$l = AuthInvite::list_invites($db, false);
assert(set_equals(array_keys($l), array('7xi4namy')));

echo "OK<br />\n";

echo "Verifying invite creation...";

$db->execute('BEGIN');

$i = AuthInvite::create_invite($db, false, 5);
$i->load();
assert(preg_match('/^[0-9a-z]{8}$/', $i->invite_code()));
assert(!$i->super());
assert($i->owns_student_id() == 5);

$i = AuthInvite::create_invite($db, true, -1);
$i->load();
assert(preg_match('/^[0-9a-z]{8}$/', $i->invite_code()));
assert($i->super());
assert($i->owns_student_id() == -1);

$db->execute('ROLLBACK');

echo "OK<br />\n";

echo "Verifying invite loading...";

$i = new AuthInvite($db, 'hh3euvua');
$i->load();
assert($i->invite_code() == 'hh3euvua');
assert($i->super());
assert($i->owns_student_id() == -1);

$i = new AuthInvite($db, '7xi4namy');
$i->load();
assert($i->invite_code() == '7xi4namy');
assert(!$i->super());
assert($i->owns_student_id() == 4);

echo "OK<br />\n";

echo "Verifying invite removal...";

$db->execute('BEGIN');

$i = new AuthInvite($db, '7xi4namy');
$i->load();
$i->remove();

$i = new AuthInvite($db, '7xi4namy');
$i->load();
assert($i->invite_code() == NULL);

$db->execute('ROLLBACK');

echo "OK<br />\n";

echo "Verifying invite usage...";

$db->execute('BEGIN');

$i = new AuthInvite($db, 'hh3euvua');
$i->load();
$i->use_invite('jvdahl', 'bullAR');

$i = new AuthInvite($db, 'hh3euvua');
$i->load();
assert($i->invite_code() == NULL);

$i->remove();

$u = AuthUser::new_from_username($db, 'jvdahl');
$u->load();
assert($u->owns_student_id() == -1);
assert($u->super());
assert($u->compare_password('bullAR'));

$i = new AuthInvite($db, 'helllloo');
$i->load();
$i->remove();

$i = new AuthInvite($db, 'he1111oo');
$i->load();
$i->use_invite('testing', 'bah');

$u = AuthUser::new_from_username($db, 'testing');
$u->load();
assert($u->user_id() == -1);

$db->execute('ROLLBACK');

echo "OK<br />\n";

$db->close();

?>