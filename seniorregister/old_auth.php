<?php

function db_get_access(&$db, $user_id, $object_id)
{
	$q = "SELECT priv_read, priv_write, priv_use FROM auth_priv WHERE user_id = " . $user_id . " AND object_id = " . $object_id;
	$db->query($q);
	if($db->ok() && $db->has_row())
	{
		$row = $db->get_row();
		return array((bool) $row[0], (bool) $row[1], (bool) $row[2]);
	}
	
	return array(FALSE, FALSE, FALSE); 
}

function db_set_access(&$db, $user_id, $object_id, $priv)
{
	$priv = priv_to_array($priv);
	
	foreach($priv as $k => $v)
		$priv[$k] = ($v ? '1' : '0');
	
	$q = "SELECT * FROM auth_priv WHERE user_id = " . $user_id . " AND object_id = " . $object_id;
	$db->query($q);
	$exists = ($db->ok() && $db->has_row());
	
	$q = ($exists ? "UPDATE" : "INSERT INTO") . " auth_priv SET ";
	if(!$exists)
		$q .= "user_id = " . $user_id . ", object_id = " . $object_id . ", "; 
	$q .= "priv_read = " . $priv[0] . ", priv_write = " . $priv[1] . ", priv_use = " . $priv[2];
	if($exists)
		$q .= " WHERE user_id = " . $user_id . " AND object_id = " . $object_id;
		
	$db->execute($q);
	return $db->ok();
}

function db_alter_access(&$db, $user_id, $object_id, $priv, $to)
{
	$priv = priv_to_array($priv);
	$original = db_get_access($db, $user_id, $object_id);
	foreach($priv as $k => $v)
		if($v)
			$original[$k] = ($to ? '1' : '0');
		else
			$original[$k] = ($original[$k] ? '1' : '0');
	return db_set_access($db, $user_id, $object_id, $original);
}

function db_can_access(&$db, $user_id, $object_id, $priv)
{
	$priv = priv_to_array($priv);
	$actual = db_get_access($db, $user_id, $object_id);
	foreach($priv as $k => $v)
		if($v && !$actual[$k])
		return FALSE;
	return TRUE;
}

function priv_to_array($priv)
{
	if(is_array($priv))
		return $priv;
	else
	{
		if($priv == 'r' || $priv == 'R')
			return array(TRUE, FALSE, FALSE);
		else if($priv == 'w' || $priv == 'W')
			return array(FALSE, TRUE, FALSE);
		else if($priv == 'u' || $priv == 'U')
			return array(FALSE, FALSE, TRUE);
	}
	return array(FALSE, FALSE, FALSE);
}

class Auth
{
	var $db;

	function Auth(&$db)
	{
		$this->db =& $db;
	}

	function clear_users()
	{
		$q = "DELETE FROM auth_user";
		$this->db->execute($q);
		return $this->db->ok();
	}

	function clear_privs()
	{
		$q = "DELETE FROM auth_priv";
		$this->db->execute($q);
		return $this->db->ok();
	}
	
	function clear_sessions()
	{
		$q = "DELETE FROM auth_session";
		$this->db->execute($q);
		return $this->db->ok();
	}

	function clear()
	{
		return ($this->clear_users() && $this->clear_privs() && $this->clear_sessions());
	}
	
	function exists($username)
	{
		return ($this->db->lookup('auth_user', 'user', "'" . $username . "'") !== 0);
	}

	function user_id($username)
	{
		$rec = $this->db->lookup('auth_user', 'user', "'" . $username . "'");
		if($rec !== 0)
			return $rec['user_id'];
		return -1;	
	}
	
	function list_user_ids()
	{
		$q = "SELECT user_id FROM auth_user";
		$this->db->query($q);
		
		$ids = array();
		
		if($this->db->ok())
			while($this->db->has_row())
			{
				$row = $this->db->get_row();
				$ids[] = $row[0];
				$this->db->next_row();
			}
			
		return $ids;
	}

	function user_info($user)
	{	
		if(is_numeric($user))
			$rec = $this->db->lookup('auth_user', 'user_id', $user);
		else
			$rec = $this->db->lookup('auth_user', 'user', "'" . $user . "'");
		if($rec !== 0)
			return array('user_id' => $rec['user_id'], 'username' => $rec['user'], 'password' => $rec['password']);
		return array('user_id' => -1, 'username' => '', 'password' => '');
	}
	
	function username($user_id)
	{
		$u = $this->user_info($user_id);
		return $u['username'];
	}
	
	function password($user_id)
	{
		$u = $this->user_info($user_id);
		return $u['password'];
	}

	function authenticate($user, $password)
	{
		$q = "SELECT user_id FROM auth_user WHERE";
		if(is_numeric($user))
			$q .= " user_id = " . $user;
		else
			$q .= " user = '" . $user . "'";
		$q .= " AND (password = '" . md5($password) . "' OR password IS NULL)";
		$this->db->query($q);
		if(!$this->db->ok() || ($this->db->ok() && !$this->db->has_row()))
			return FALSE;
		return TRUE;
	}

	function create_user($username, $password = '')
	{
		$q = "INSERT INTO auth_user SET user = '" . $username . "'";
		if($password != '')
			$q .= ", password = '" . md5($password) . "'";

		$this->db->execute($q);
		return $this->db->ok();
	}

	function change_password($user, $password)
	{
		$q = "UPDATE auth_user SET password = ";
		if($password !== '')
			$q .= "'" . md5($password) . "'";
		else
			$q .= "NULL";
		if(is_numeric($user))
			$q .= " WHERE user_id = " . $user;
		else
			$q .= " WHERE user = '" . $user . "'";
		$this->db->execute($q);
		return $this->db->ok();
	}

	function get_access($user_id, $object_id)
	{
		return db_get_access($this->db, $user_id, $object_id);
	}
	
	function set_access($user_id, $object_id, $priv)
	{
		return db_set_access($this->db, $user_id, $object_id,  $priv);
	}
	
	function alter_access($user_id, $object_id, $priv, $to)
	{
		return db_alter_access($this->db, $user_id, $object_id, $priv, $to);
	}
	
	function add_access($user_id, $object_id, $priv)
	{
		return db_alter_access($this->db, $user_id, $object_id, $priv, TRUE);
	}
	
	function sub_access($user_id, $object_id, $priv)
	{
		return db_alter_access($this->db, $user_id, $object_id, $priv, FALSE);
	}
	
	function can_access($user_id, $object_id, $priv)
	{
		return db_can_access($this->db, $user_id, $object_id, $priv);
	}
	
	function destroy_session($session_id)
	{
		$q = "DELETE FROM auth_session WHERE session_id = '" . $session_id . "'";
		$this->db->execute($q);
		return $this->db->ok();
	}
	
	function set_session_user($session_id, $user_id)
	{
		$rec = $this->db->lookup('auth_session', 'session_id', "'" . $session_id . "'");
		$exists = ($rec !== 0);
		
		$q = ($exists ? "UPDATE" : "INSERT INTO") . " auth_session SET ";
		if(!$exists)
			$q .= "session_id = '" . $session_id . "', "; 
		$q .= "user_id = " . $user_id;
		if($exists)
			$q .= " WHERE session_id = '" . $session_id . "'";
			
		$this->db->execute($q);
		
		return $this->db->ok();
	}
	
	function get_session_user($session_id)
	{
		$rec = $this->db->lookup('auth_session', 'session_id', "'" . $session_id . "'");
		
		if($rec !== 0)
			return $rec['user_id'];
		return -1;
	}
}


class AuthUser
{	
	/*  STATIC  */
	
	function clear_users(&$db)
	{
		$q = "DELETE FROM auth_user";
		$db->execute($q);
		return $db->ok();
	}
	
	function list_users(&$db)
	{	
		$q = "SELECT user_id FROM auth_user";
		$db->query($q);
		
		$ids = array();
		
		if($db->ok())
			while($db->has_row())
			{
				$row = $db->get_row();
				$ids[] = $row[0];
				$db->next_row();
			}
			
		return $ids;
	}
	
	function create_user(&$db, $username, $password)
	{	
		$q = "INSERT INTO auth_user SET user = '" . $username . "'";
		if($password != '')
			$q .= ", password = '" . md5($password) . "'";

		$db->execute($q);
		if($db->ok())
			return $db->last_insert_id();
		return -1;
	}
	
	/*  MEMBER  */
	
	var $db, $user_id, $username, $password;
	
	function AuthUser(&$db, $user)
	{
		$this->db =& $db;
		
		if(is_numeric($user))
			$rec = $this->db->lookup('auth_user', 'user_id', $user);
		else
			$rec = $this->db->lookup('auth_user', 'user', "'" . $user . "'");
		if($rec !== 0)
		{
			$this->user_id = $rec['user_id'];
			$this->username = $rec['user'];
			$this->password = $rec['password'];
		}
		else
		{
			$this->user_id = -1;
			$this->username = '';
			$this->password = '';
		}
	}
	
	function reload()
	{
		$rec = $this->lookup('auth_user', 'user_id', $this->user_id);
		
		$this->username = $rec['user'];
		$this->password = $rec['password'];
	}
	
	function valid()
	{
		return ($this->user_id > 0);
	}
	
	function get_user_id()
	{
		return $this->user_id;
	}
	
	function get_username()
	{
		return $this->username;
	}
	
	function get_password()
	{
		return $this->password;
	}
	
	function authenticate($password)
	{
		return ($this->password == '' || $this->password == md5($password));
	}
	
	function remove()
	{
		$this->db->remove('auth_user', array('user_id' => $this->user_id));
		if($this->db->ok())
			$this->user_id = -1;
		return $this->db->ok();
	}
	
	function change_password($password)
	{
		$q = "UPDATE auth_user SET password = ";
		if($password !== '')
			$q .= "'" . md5($password) . "'";
		else
			$q .= "NULL";
		$q .= " WHERE user_id = " . $this->user_id;
		$this->db->execute($q);
		if($this->db->ok())
			$this->password = $password !== '' ? md5($password) : '';
		return $this->db->ok();
	}

	function get_access($object_id)
	{
		return db_get_access($this->db, $this->user_id, $object_id);
	}

	function set_access($object_id, $priv)
	{
		return db_set_access($this->db, $this->user_id, $object_id, $priv);
	}

	function add_access($object_id, $priv)
	{
		return db_alter_access($this->db, $this->user_id, $object_id, $priv, TRUE);
	}

	function sub_access($object_id, $priv)
	{
		return db_alter_access($this->db, $this->user_id, $object_id, $priv, FALSE);
	}

	function can_access($object_id, $priv)
	{
		return db_can_access($this->db, $this->user_id, $object_id, $priv);
	}
	
	function which_can($priv)
	{
		$priv = priv_to_array($priv);
		
		$q = "SELECT object_id FROM auth_priv WHERE user_id = " . $this->user_id;

		if($priv[0])
			$set[] = "priv_read = 1";
		if($priv[1])
			$set[] = "priv_write = 1";
		if($priv[2])
			$set[] = "priv_use = 1";

		if(count($set) > 0)
			$q .= " AND " . implode(' AND ' , $set);

		$this->db->query($q);

		$ids = array();

		if($this->db->ok())
			while($this->db->has_row())
		{
			$row = $this->db->get_row();
			$ids[] = $row[0];
			$this->db->next_row();
		}

		return $ids;
	}
}

class AuthSession
{
	var $db, $session_id, $user_id;
	
	function clear_sessions(&$db)
	{
		$q = "DELETE FROM auth_session";
		$db->execute($q);
		return $db->ok();		
	}	
	
	function AuthSession(&$db, $session_id)
	{
		$this->db =& $db;
		$this->session_id = $session_id;
		
		$rec = $this->db->lookup('auth_session', 'session_id', "'" . $this->session_id . "'");

		if($rec !== 0)
			$this->user_id = $rec['user_id'];
		else
			$this->user_id = -1;
	}
	
	function get_user_id()
	{
		return $this->get_user();
	}
	
	function get_user()
	{
		return $this->user_id;
	}
	
	function set_user_id($user_id)
	{
		return $this->set_user($user_id);
	}
	
	function set_user($user_id)
	{
		$rec = $this->db->lookup('auth_session', 'session_id', "'" . $this->session_id . "'");
		$exists = ($rec !== 0);

		$q = ($exists ? "UPDATE" : "INSERT INTO") . " auth_session SET ";
		if(!$exists)
			$q .= "session_id = '" . $this->session_id . "', "; 
		$q .= "user_id = " . $user_id;
		if($exists)
			$q .= " WHERE session_id = '" . $this->session_id . "'";

		$this->db->execute($q);

		if($this->db->ok())
			$this->user_id = $user_id;

		return $this->db->ok();
	}
	
	function destroy()
	{
		$q = "DELETE FROM auth_session WHERE session_id = '" . $this->session_id . "'";
		$this->db->execute($q);
		if($this->db->ok())
			$this->user_id = -1;
		return $this->db->ok();
	}
}

class AuthObject
{
	var $db, $object_id;
	
	function AuthObject(&$db, $object_id)
	{
		$this->db =& $db;
		$this->object_id = $object_id;
	}
	
	function get_object_id()
	{
		return $this->object_id;
	}
	
	function get_access($user_id)
	{
		return db_get_access($this->db, $user_id, $this->object_id);
	}
	
	function set_access($user_id, $priv)
	{
		return db_set_access($this->db, $user_id, $this->object_id, $priv);
	}
	
	function add_access($user_id, $priv)
	{
		return db_alter_access($this->db, $user_id, $this->object_id, $priv, TRUE);
	}
	
	function sub_access($user_id, $priv)
	{
		return db_alter_access($this->db, $user_id, $this->object_id, $priv, FALSE);
	}
	
	function can_access($user_id, $priv)
	{
		return db_can_access($this->db, $user_id, $this->object_id, $priv);
	}
	
	function who_can($priv)
	{
		$priv = priv_to_array($priv);
		
		$q = "SELECT user_id FROM auth_priv WHERE object_id = " . $this->object_id;
		
		if($priv[0])
			$set[] = "priv_read = 1";
		if($priv[1])
			$set[] = "priv_write = 1";
		if($priv[2])
			$set[] = "priv_use = 1";
			
		if(count($set) > 0)
			$q .= " AND " . implode(' AND ' , $set);
			
		$this->db->query($q);
	
		$ids = array();
		
		if($this->db->ok())
			while($this->db->has_row())
			{
				$row = $this->db->get_row();
				$ids[] = $row[0];
				$this->db->next_row();
			}
			
		return $ids;
	}
}

?>