<?php

/* VERSION 081105 */

class PostgresDatabase extends Database
{	
	var $lastResult;
	
	function __construct($host = false, $user = false, $pass = false, $name = false, $logger = false)
	{	
		parent::__construct($host, $user, $pass, $name, $logger);
	}
	
	/** BEGIN DBMS-DEPENDENCY **/
	
	function open($host, $user, $pass, $name)
	{	
		$this->append_log("('$host', '$user', '$pass', '$name')", "Database::open");
		
		/*if(!@ extension_loaded('mysql')) @ dl('mysql.so');*/
		if(!function_exists('pg_connect'))
		{
			$this->append_log("no PostgreSQL support!!", "Database::open");
			die;
		}
		
		$this->conn = pg_connect("host=$host dbname=$name user=$user password=$pass");
		if($this->conn === false)
		{
			$this->append_log("unable to connect", "Database::open");
			return;
		}
		$this->append_log("connected", "Database::open");
		$this->connected = true;
	}
	
	function get_encoding()
	{
		return pg_client_encoding($this->conn);
	}
	
	function set_encoding($encoding)
	{
		return pg_set_client_encoding($this->conn, $encoding);
	}
	
	function do_query($q)
	{
		return ($this->lastResult = pg_query($this->conn, $q));
	}
	
	function num_rows($res)
	{
		if($res instanceof QueryResult)
			$res = $res->mResult;
		return pg_num_rows($res);
	}
	
	function data_seek($res, $n)
	{
		if($res instanceof QueryResult)
			$res = $res->mResult;
		pg_result_seek($res, $n);
	}
	
	function fetch_object($res)
	{
		if($res instanceof QueryResult)
			$res = $res->mResult;
		return pg_fetch_object($res);
	}
	
	function fetch_array($res)
	{
		if($res instanceof QueryResult)
			$res = $res->mResult;
		return pg_fetch_array($res);
	}
	
	function free_result($res)
	{
		if($res instanceof QueryResult)
			$res = $res->mResult;
		return pg_free_result($res);
	}
	
	function next_id($table, $key)
	{
		return new QueryExpression("nextval('\"${table}_${key}_seq\"')");
	}

	function last_id()
	{
		$obj = $this->select_one(NULL, "lastval() AS id", NULL);
		return $obj == false ? false : $obj->id;
	}
	
	function affected_rows()
	{
		return pg_affected_rows($this->lastResult);
	}

	function errno()
	{
		//return mysql_errno($this->conn);
		// FIX THIS
		return 0;
	}

	function error()
	{
		return pg_last_error($this->conn);
	}
	
	function ping()
	{
		return pg_ping($this->conn);
	}
	
	function escape($str)
	{
		return pg_escape_string($this->conn, $str);
	}

	function close()
	{
		pg_close($this->conn);
	}
	
	function get_link()
	{
		return $this->conn;
	}
	
	/** END DBMS-DEPENDENCY **/
}

?>