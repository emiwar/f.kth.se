<?php

/* VERSION 081105 - fixed smartquote? */

class Database
{
	var $conn, $connected;
	var $errno, $error;
	var $logger;
	
	function __construct($host = false, $user = false, $pass = false, $name = false, $logger = false)
	{	
		$this->logger = $logger;
		$this->connected = false;
		
		$this->append_log("creating database object", "Database::__construct");
		
		if($host)
			$this->open($host, $user, $pass, $name);
	}

	function append_log($txt, $src = false)
	{
		if(!is_null($this->logger))
			$this->logger->log($txt, $src);
	}
	
	function is_connected()
	{
		return $this->connected;
	}
	
	/** BEGIN DBMS-DEPENDENCY **/
	
	function open($host, $user, $pass, $name)
	{	
		$this->append_log("('$host', '$user', '$pass', '$name')", "Database::open");
		
		if(!@ extension_loaded('mysql')) @ dl('mysql.so');
		if(!function_exists('mysql_connect'))
		{
			$this->append_log("no MySQL support!!", "Database::open");
			die;
		}
		
		$this->conn = mysql_connect($host, $user, $pass);
		if($this->conn === false)
		{
			$this->append_log("unable to connect", "Database::open");
			return;
		}
		if($name !== '' && $this->conn !== false)
		{
			mysql_select_db($name, $this->conn);
			if(!$this->ok())
				$this->append_log("unable to select database", "Database::open");
		}
		$this->append_log("connected", "Database::open");
		$this->connected = true;
	}
	
	function get_encoding()
	{
		return mysql_client_encoding($this->conn);
	}
	
	function set_encoding($encoding)
	{
		if(!function_exists('mysql_set_charset'))
			$this->execute("SET NAMES '$encoding'");
		else
			mysql_set_charset($encoding, $this->conn);
	}
	
	function do_query($q)
	{
		return mysql_query($q, $this->conn);
	}
	
	function num_rows($res)
	{
		if($res instanceof QueryResult)
			$res = $res->mResult;
		return mysql_num_rows($res);
	}
	
	function data_seek($res, $n)
	{
		if($res instanceof QueryResult)
			$res = $res->mResult;
		mysql_data_seek($res, $n);
	}
	
	function fetch_object($res)
	{
		if($res instanceof QueryResult)
			$res = $res->mResult;
		return mysql_fetch_object($res);
	}
	
	function fetch_array($res)
	{
		if($res instanceof QueryResult)
			$res = $res->mResult;
		return mysql_fetch_array($res);
	}
	
	function free_result($res)
	{
		if($res instanceof QueryResult)
			$res = $res->mResult;
		return mysql_free_result($res);
	}
	
	function next_id($table, $key)
	{
		return NULL;
	}

	function last_id()
	{
		return mysql_insert_id($this->conn);
	}
	
	function affected_rows()
	{
		return mysql_affected_rows($this->conn);
	}

	function errno()
	{
		return mysql_errno($this->conn);
	}

	function error()
	{
		return mysql_error($this->conn);
	}
	
	function ping()
	{
		return mysql_ping($this->conn);
	}
	
	function escape($str)
	{
		return mysql_real_escape_string($str, $this->conn);
	}

	function close()
	{
		mysql_close($this->conn);
	}
	
	function get_link()
	{
		return $this->conn;
	}
	
	/** END DBMS-DEPENDENCY **/

	function query($q)
	{
		$this->append_log("('$q')", "Database::query");
		$ret = $this->do_query($q);
		if($ret === false && 
		   ($this->errno() == 2013 || $this->errno() == 2006))
		{
			$this->append_log("lost connection", "Database::query");
			if($this->ping())
			{
				$this->append_log("regained connection", "Database::query");
				$ret = $this->do_query($q);
			}
		}
		
		return new QueryResult($this, $ret);
	}
	
	function execute($q)
	{
		$this->append_log("('$q')", "Database::execute");
		return $this->do_query($q);
	}
	
	function safe_query($pq, $args)
	{
		if(!is_array($args))
		{
			$args = func_get_args();
			array_shift($args);
		}
		$this->append_log("arguments (" . implode(", ", $args) . ")", "Database::safe_query");
		$q = $this->fill_parameters($pq, $args);
		return $this->query($q);
	}
	
	function select($table, $fields, $qual)
	{
		if(is_array($fields))
			$select = implode(',', $fields);
		else
			$select = $fields;
		
		if(is_array($table))
			$from = $this->name_list($table);
		else
			$from = $table;
		
		if(is_array($qual))
			$where = $this->and_list($qual);
		else
			$where = $qual;
		
		$this->append_log("('$from', '$select', '$where')", "Database::select");
		
		$q = "SELECT $select";
		if(!empty($from))
			$q .= " FROM $from";
		if(!empty($qual))
			$q .= " WHERE $where";
			
		return $this->query($q);
	}
	
	function select_one($table, $fields, $qual)
	{
		$res = $this->select($table, $fields, $qual);
		if($res === false)
			return false;
		if($this->num_rows($res) > 0)
			$ret = $this->fetch_object($res);
		else
			$ret = false;
		$this->free_result($res);
		return $ret;
	}

	function lookup($table, $qual)
	{
		return $this->select_one($table, "*", $qual);
	}
	
	function insert($table, $a)
	{
		$keys = $this->name_list(array_keys($a));
		$vals = $this->value_list(array_values($a));
		
		$q = "INSERT INTO $table ($keys) VALUES ($vals)";
		
		return $this->query($q) !== false;
	}
	
	function update($table, $a, $qual)
	{	
		if(is_array($qual))
			$where = $this->and_list($qual);
		else
			$where = $qual;
			
		if(is_array($a))
			$set = $this->set_list($a);
		else
			$set = $a;

		$this->append_log("('$table', '$set', '$where')", "Database::update");
	
		if(!empty($where))
			$q = "UPDATE $table SET $set WHERE $where";
		else
			$q = "UPDATE $table SET $set";
			
		return $this->query($q);
	}
	
	function set($table, $a, $qual = false)
	{
		$this->append_log("('$table', '$a', '$qual')", "Database::set");

		if($qual === false)
			$exists = false;
		else
			$exists = ($this->lookup($table, $qual) !== false);
			
		if($exists)
		{
			$this->append_log("row exists, do update", "Database::set");
			return $this->update($table, $a, $qual);
		}
		$this->append_log("row does not exist, do insert", "Database::set");
			
		if(!is_null($qual) && $qual !== false)
			if(is_array($qual))
				$a = array_merge($a, $qual);
			else
				$a = array_merge($a, array($qual));
		
		return $this->insert($table, $a, $qual);
	}
	
	function remove($table, $qual)
	{	
		$this->append_log("('$table', '$qual')", "Database::remove");
			
		if(is_array($qual))
			$where = $this->and_list($qual);
		else
			$where = $qual;
			
		if(!empty($where))
			$q = "DELETE FROM $table WHERE $where";
		// complete deletes illegal
		/*else
			$q = "DELETE FROM $table";*/
	
		return $this->execute($q);
	}

	function ok()
	{
		return (@ $this->errno() == 0) ? true : false;
	}
	
	function fill_parameters($pq, $vals)
	{
		$q = '';
		$i = 0;
		$j = 0;
		while($i < strlen($pq))
		{
			if($pq[$i] == '\\' && $i+1 < strlen($pq) && 
			   ($pq[$i+1] == '?' || $pq[$i+1] == '!'))
			{
				$q .= $pq[$i+1];
				$i += 2;
			}
			else if($pq[$i] == '?')
			{
				$q .= $this->quote($vals[$j]);
				$i += 1;
			}
			else if($pq[$i] == '!')
			{
				$q .= $vals[$j];
				$i += 1; $j += 1;
			}
			else
			{
				$q .= $pq[$i];
				$i += 1;
			}
		}
		return $q;
	}
	
	function quote($str)
	{
		return (is_null($str) ? ('NULL') : ("'" . $this->escape($str) . "'"));
	}
	
	function smart_quote($value)
	{
		/*if(is_numeric($value))
			return "$value";
		else*/if($value instanceof QueryExpression)
			return $value->get_expression();
		else
			return $this->quote($value);
	}
	
	function and_list($qual)
	{
		$pairs = array();
		foreach($qual as $f => $v)
		{
			if(is_numeric($f))
				$pairs[] = "($v)";
			elseif(is_null($v))
				$pairs[] = "$f IS NULL";
			else
				$pairs[] = "$f = " . $this->smart_quote($v);
		}
	 	return implode(' AND ', $pairs);
	}
	
	function set_list($a)
	{
		$pairs = array();
		foreach($a as $f => $v)
		{
			if(is_null($v))
				$pairs[] = "$f = NULL";
			else
				$pairs[] = "$f = " . $this->smart_quote($v);
		}
		return implode(', ', $pairs);
	}
	
	function name_list($a)
	{
		return implode(', ', $a);
	}
	
	function value_list($a)
	{
		$vals = array();
		foreach($a as $v)
			$vals[] = $this->smart_quote($v);
		return implode(', ', $vals);
	}
}

class QueryResult
{
	var $mParent, $mResult;
	
	function __construct($parent, $result)
	{
		$this->mParent = $parent;
		$this->mResult = $result;
	}
	
	function num_rows()
	{
		return $this->mParent->num_rows($this->mResult);
	}
	
	function fetch_object()
	{
		return $this->mParent->fetch_object($this->mResult);
	}

	function fetch_array()
	{
		return $this->mParent->fetch_array($this->mResult);	
	}
	
	function data_seek($n)
	{
		$this->mParent->data_seek($this->mResult, $n);
	}
	
	function free()
	{
		$this->mParent->free_result($this->mResult);
		unset($this->mParent);
		unset($this->mResult);
	}
}

class QueryExpression
{
	var $mExp;
	
	function __construct($exp) { $this->mExp = $exp; }
	
	function get_expression()
	{
		return $this->mExp;
	}
}

?>