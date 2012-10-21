<?php

require_once('db.php');

define('ALL_GROUP', 1);
define('USER_GROUP', 2);
define('SUPER_GROUP', 3);

define('VIEW_OWN_CORE_PRIV', 1);
define('VIEW_OWN_CONTACT_PRIV', 2);
define('VIEW_OWN_AWARD_PRIV', 3);
define('VIEW_OWN_NOMINATION_PRIV', 4);
define('VIEW_OWN_MEMBERSHIP_PRIV', 5);
define('VIEW_CORE_PRIV', 6);
define('VIEW_CONTACT_PRIV', 7);
define('VIEW_AWARD_PRIV', 8);
define('VIEW_NOMINATION_PRIV', 9);
define('VIEW_MEMBERSHIP_PRIV', 10);

define('VIEW_OWN_PAYMENT_PRIV', 11);
define('VIEW_PAYMENT_PRIV', 12);

define('EDIT_OWN_CORE_PRIV', 101);
define('EDIT_OWN_CONTACT_PRIV', 102);
define('EDIT_OWN_AWARD_PRIV', 103);
define('EDIT_OWN_NOMINATION_PRIV', 104);
define('EDIT_OWN_MEMBERSHIP_PRIV', 105);
define('EDIT_CORE_PRIV', 106);
define('EDIT_CONTACT_PRIV', 107);
define('EDIT_AWARD_PRIV', 108);
define('EDIT_NOMINATION_PRIV', 109);
define('EDIT_MEMBERSHIP_PRIV', 110);

define('EDIT_OWN_PAYMENT_PRIV', 111);
define('EDIT_PAYMENT_PRIV', 112);

define('EDIT_CLASS_PRIV', 120);
define('EDIT_SPECIALIZATION_PRIV', 121);
define('EDIT_PHONE_TYPE_PRIV', 122);
define('EDIT_TITLE_PRIV', 123);
define('EDIT_COMMITTEE_PRIV', 124);
define('EDIT_POSITION_PRIV', 125);

define('MANAGE_USER_PRIV', 130);
define('MANAGE_GROUP_PRIV', 131);
define('MANAGE_STUDENT_PRIV', 132);

define('VIEW_TEMPLATE_PRIV', 140);
define('EDIT_TEMPLATE_PRIV', 141);
define('SEND_MESSAGE_PRIV', 142);

function get_session_user_id()
{
	if(isset($_SESSION['user_id']))
		return $_SESSION['user_id'];
	return -1;
}

function set_session_user_id($userId)
{
	if($userId != -1)
		$_SESSION['user_id'] = $userId;
	else
		clear_session_user_id();
}

function clear_session_user_id()
{
	unset($_SESSION['user_id']);
}

function password_crypt($str)
{
	return $str;
}

class AuthUser
{
	var $mDb;
	
	var $mId, $mUsername, $mPassword, $mSuper, $mOwnsStudentId;
	
	var $mGroups;
	
	var $mPrivs;
	
	function __construct($db, $userId)
	{
		$this->mDb = $db;
		
		$this->mId = $userId;
		
		$this->mUsername = '';
		$this->mPassword = '';
		$this->mSuper = FALSE;
		$this->mOwnsStudentId = -1;
		$this->mGroups = array();
		$this->mPrivs = array();
	}
	
	function create_user($db, $username, $password, $super = false, $owns_student_id = -1)
	{
		$max = $db->select_one('auth_user', 'MAX("user_id") as m', '');
		$id = $max->m+1;
		
		$db->insert('auth_user', array(
			'user_id' => $id,
			'username' => $username,
			'password' => password_crypt($password),
			'super' => new QueryExpression($super ? 'TRUE' : 'FALSE'),
			'owns_student_id' => ($owns_student_id == -1 ? NULL : $owns_student_id)));
			
		$u = new AuthUser($db, $id);
		$u->load();
		
		return $u;
	}
	
	function list_users($db, $username = '', $super = NULL)
	{
		$where = array();
		if($username)
			$where[] = "auth_user.\"username\" LIKE '%$username%'";
		if($super !== NULL)
			$where[] = "auth_user.\"super\" = " . ($super ? 'TRUE' : 'FALSE') . "";
		$where = implode(' AND ', $where);
		
		$res = $db->select('auth_user LEFT JOIN student ON auth_user."owns_student_id" = student."student_id"', array('auth_user."user_id"', 'auth_user."username"', 'auth_user."super"', 'auth_user."owns_student_id"', 'student."first_name"', 'student."last_name"'), $where);
		
		$list = array();
		while(($obj = $res->fetch_object()) !== false)
		{
			$list[$obj->user_id] = array($obj->username, ($obj->super == 't' || $obj->super == 1), $obj->owns_student_id ? $obj->owns_student_id : -1, $obj->first_name . " " . $obj->last_name);
		}
		
		return $list;
	}
	
	function new_from_username($db, $username)
	{
		$row = $db->select_one('auth_user', 'user_id', array('username' => $username));
		return new AuthUser($db, $row ? $row->user_id : -1);
	}
	
	function new_from_student($db, $student_id)
	{
		$row = $db->select_one('auth_user', 'user_id', array('owns_student_id' => $student_id));
		return new AuthUser($db, $row ? $row->user_id : -1);
	}
	
	function load()
	{
		if($this->mId != -1)	
			$obj = $this->mDb->lookup('auth_user', 
				array('user_id' => $this->mId));
		else
			$obj = FALSE;
			
		if($obj === FALSE)
		{
			$this->mId = -1;
			$this->mUsername = '';
			$this->mPassword = '';
			$this->mSuper = FALSE;
			$this->mOwnsStudentId = -1;
			
			$this->mGroups = array(ALL_GROUP);
		}
		else
		{
			$this->mUsername = $obj->username;
			$this->mPassword = $obj->password;
			$this->mSuper = ($obj->super == 't' || $obj->super == 1);
			$this->mOwnsStudentId = !is_null($obj->owns_student_id) ? $obj->owns_student_id : -1;
			
			$this->mGroups = array(ALL_GROUP, USER_GROUP);
			if($this->mSuper)
				$this->mGroups[] = SUPER_GROUP;
			$res = $this->mDb->select('auth_member', 'group_id', array('user_id' => $this->mId));
			while(($obj = $res->fetch_object()) !== FALSE)
				$this->mGroups[] = $obj->group_id;
			$res->free();
		}
		
		$this->mPrivs = array();
		foreach($this->mGroups as $group_id)
		{
			$groupPrivs = array();
			$res = $this->mDb->select('auth_access', 'priv_id', array('group_id' => $group_id));
			while(($obj = $res->fetch_object()) !== FALSE)
				$groupPrivs[] = $obj->priv_id;
			$this->mPrivs = array_merge($this->mPrivs, $groupPrivs);
			// is there a merge function that will disregard duplicates?
		}
		$this->mPrivs = array_unique($this->mPrivs);
	}
	
	function commit()
	{
		$obj = $this->mDb->lookup('auth_user', 
			array('user_id' => $this->mId));
			
		if($obj->username != $this->mUsername ||
			$obj->password != $this->mPassword ||
			($obj->super == 't' || $obj->super == 1) != $this->mSuper ||
			$obj->owns_student_id != ($this->mOwnsStudentId != -1 ? $this->mOwnsStudentId : NULL))
		{
			$this->mDb->update('auth_user',
				array('username' => $this->mUsername,
					'password' => $this->mPassword,
					'super' => new QueryExpression($this->mSuper ? 'TRUE' : 'FALSE'),
					'owns_student_id' => ($this->mOwnsStudentId != -1 ? $this->mOwnsStudentId : NULL)),
				array('user_id' => $this->mId));
		}
		
		$toAdd = $this->mGroups;
		$toRemove = array();
		
		$res = $this->mDb->select('auth_member', 'group_id', array('user_id' => $this->mId));
		while(($obj = $res->fetch_object()) !== FALSE)
		{
			$i = array_search($obj->group_id, $toAdd);
			if($i !== FALSE)
				unset($toAdd[$i]);
			else
				$toRemove[] = $obj->group_id;
		}
		$res->free();
		
		foreach($toAdd as $groupId)
			if($groupId != ALL_GROUP &&
				$groupId != USER_GROUP &&
				$groupId != SUPER_GROUP)
				$this->mDb->insert('auth_member', 
				array('user_id' => $this->mId, 
					'group_id' => $groupId));
					
		foreach($toRemove as $groupId)
			$this->mDb->remove('auth_member', 
				array('user_id' => $this->mId, 
					'group_id' => $groupId));
	}
	
	function user_id()
	{
		return $this->mId;
	}
	
	function username($username = NULL)
	{
		$old = $this->mUsername;
		if($username !== NULL)
			$this->mUsername = $username;
		return $old;
	}
	
	function super($super = NULL)
	{
		$old = $this->mSuper;
		if($super !== NULL)
			$this->mSuper = $super;
		return $old;
	}
	
	function owns_student_id($ownsStudentId = NULL)
	{
		$old = $this->mOwnsStudentId;
		if($ownsStudentId !== NULL)
			$this->mOwnsStudentId = $ownsStudentId;
		return $old;
	}
	
	function group_ids()
	{
		return $this->mGroups;
	}
	
	function clear_memberships()
	{
		$this->mGroups = array();
	}
	
	function remove_membership($groupId)
	{
		$i = array_search($groupId, $this->mGroups);
		if($i !== FALSE)
			unset($this->mGroups[$i]);
	}
	
	function add_membership($groupId)
	{
		$i = array_search($groupId, $this->mGroups);
		if($i === FALSE)
			$this->mGroups[] = $groupId;
	}
	
	function privilege_ids()
	{
		return $this->mPrivs;
	}
	
	function is_member($groupId)
	{
		return (array_search($groupId, $this->mGroups) !== FALSE);
	}
	
	function can_do($privId)
	{
		return ($this->is_member(SUPER_GROUP) ||
			(array_search($privId, $this->mPrivs) !== FALSE));
	}
	
	function compare_password($password)
	{
		return (password_crypt($password) == $this->mPassword);
	}
	
	function set_password($password)
	{
		$this->mPassword = password_crypt($password);
	}
}

class AuthGroup
{
	var $mDb;
	
	var $mId, $mName;
	
	var $mPrivs;
	
	function __construct($db, $id)
	{
		$this->mDb = $db;
		
		$this->mId = $id;
		$this->mName = '';
		
		$this->mPrivs = array();
	}
	
	function create_group($db, $name)
	{
		$max = $db->select_one('auth_group', 'MAX("group_id") as m', '');
		$id = $max->m+1;
		
		$db->insert('auth_group', array('group_id' => $id, 'name' => $name));
		
		$g = new AuthGroup($db, $id);
		$g->load();
		
		return $g;
	}
	
	function list_groups($db)
	{
		$res = $db->select('auth_group', array('group_id', 'name'), '');
		
		$list = array();
		
		while(($obj = $res->fetch_object()) !== false)
			$list[$obj->group_id] = $obj->name;
		
		return $list;
	}
	
	function load()
	{
		$obj = $this->mDb->lookup('auth_group', array('group_id' => $this->mId));
		
		if($obj === FALSE)
		{
			$this->mId = -1;
			$this->mName = '';
			
			$this->mPrivs = array();
		}
		else
		{
			$this->mName = $obj->name;
			
			$this->mPrivs = array();
			$res = $this->mDb->select('auth_access', array('priv_id'), array('group_id' => $this->mId));
			while(($obj = $res->fetch_object()) !== FALSE)
				$this->mPrivs[] = $obj->priv_id;
			$res->free();
		}
	}
	
	function commit()
	{
		$obj = $this->mDb->lookup('auth_group', array('group_id' => $this->mId));
		
		if($obj->name != $this->mName)
		{
			$this->mDb->update('auth_group', array('name' => $this->mName), array('group_id' => $this->mId));
		}
		
		$toAdd = $this->mPrivs;
		$toRemove = array();
		
		$res = $this->mDb->select('auth_access', array('priv_id'), array('group_id' => $this->mId));
		while(($obj = $res->fetch_object()) !== FALSE)
		{
			$i = array_search($obj->priv_id, $toAdd);
			if($i === FALSE)
				$toRemove[] = $obj->priv_id;
			else
				unset($toAdd[$i]);
		}
		$res->free();
		
		foreach($toAdd as $privId)
			$this->mDb->insert('auth_access', array('priv_id' => $privId, 'group_id' => $this->mId));
		
		foreach($toRemove as $privId)
			$this->mDb->remove('auth_access', array('priv_id' => $privId, 'group_id' => $this->mId));
	}
	
	function group_id()
	{
		return $this->mId;
	}
	
	function name($name = NULL)
	{
		$old = $this->mName;
		if($name !== NULL)
			$this->mName = $name;
		return $old;	
	}
	
	function privilege_ids()
	{
		return $this->mPrivs;
	}
	
	function clear_privileges()
	{
		$this->mPrivs = array();
	}
	
	function add_privilege($privId)
	{
		$i = array_search($privId, $this->mPrivs);
		if($i === FALSE)
			$this->mPrivs[] = $privId;
	}
	
	function remove_privilege($privId)
	{
		$i = array_search($privId, $this->mPrivs);
		if($i !== FALSE)
			unset($this->mPrivs[$i]);
	}
	
	function can_do($privId)
	{
		return (array_search($privId, $this->mPrivs) !== FALSE);
	}
}

class AuthInvite
{
	var $mDb;
	
	var $mCode, $mSuper, $mOwnsStudentId;
	
	function __construct($db, $code)
	{
		$this->mDb = $db;
		$this->mCode = $code;
		$this->mSuper = false;
		$this->mOwnsStudentId = -1;
	}
	
	function list_invites($db, $super = NULL)
	{
		$where = array();
		if($super !== NULL)
			$where[] = "auth_invite.\"super\" = '" . new QueryExpression($super ? 'TRUE' : 'FALSE') . "'";
		$where = implode(' AND ', $where);

		$res = $db->select('auth_invite LEFT JOIN student ON auth_invite."owns_student_id" = student."student_id"', array('auth_invite."invite_code"', 'auth_invite."super"', 'auth_invite."owns_student_id"', 'student."first_name"', 'student."last_name"'), $where);

		$list = array();
		while(($obj = $res->fetch_object()) !== false)
		{
			$list[$obj->invite_code] = array($obj->super == 't' || $obj->super == 1, $obj->owns_student_id ? $obj->owns_student_id : -1, $obj->first_name . " " . $obj->last_name);
		}

		return $list;
	}
	
	function create_invite($db, $super = false, $ownsStudentId = -1)
	{
		while(true)
		{
			$code = "";
			$letters = "abcdefghijklmnopqrstuvxyz1234567890";
			while(strlen($code) < 8)
				$code .= $letters[rand()%strlen($letters)];
			if($db->lookup('auth_invite', array('invite_code' => $code)) === FALSE)
				break;
		}

		$db->insert('auth_invite', array('invite_code' => $code, 'super' => new QueryExpression($super ? 'TRUE' : 'FALSE'), 'owns_student_id' => ($ownsStudentId != -1 ? $ownsStudentId : NULL)));
		
		return new AuthInvite($db, $code);
	}

	function new_from_student($db, $student_id)
	{
		$row = $db->select_one('auth_invite', 'invite_code', array('owns_student_id' => $student_id));
		return new AuthInvite($db, $row ? $row->invite_code : NULL);
	}
	
	function load()
	{
		$obj = $this->mDb->lookup('auth_invite', array('invite_code' => $this->mCode));
		
		if($obj === FALSE)
		{
			$this->mCode = NULL;
			$this->mSuper = false;
			$this->mOwnsStudentId = -1;
		}
		else
		{
			$this->mSuper = ($obj->super == 't' || $obj->super == 1);
			$this->mOwnsStudentId = $obj->owns_student_id ? $obj->owns_student_id : -1;
		}
	}
	
	function invite_code()
	{
		return $this->mCode;
	}
	
	function super()
	{
		return $this->mSuper;
	}
	
	function owns_student_id()
	{
		return $this->mOwnsStudentId;
	}
	
	function remove()
	{
		if($this->mCode == '')
			return;
		$this->mDb->remove('auth_invite', array('invite_code' => $this->mCode));
		
		$this->mCode = NULL;
		$this->mSuper = false;
		$this->mOwnsStudentId = -1;
	}
	
	function use_invite($username, $password)
	{
		if($this->mCode == '')
			return;
		$u = AuthUser::create_user($this->mDb, $username, $password, $this->mSuper, $this->mOwnsStudentId);
		$this->remove();
		
		return $u;
	}
}

?>