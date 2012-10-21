<?php

require_once('config.php');

require_once('msg.php');

require_once('auth.php');

require_once('utils.php');

class MessageQueue
{
	var $mDb;
	
	function __construct($db)
	{
		$this->mDb = $db;
	}
	
	function list_messages()
	{
		$q = 'SELECT "message_id", "when", "recipient", "subject", "message", "headers", "sending_user_id", "username" FROM "message_queue" JOIN "auth_user" ON "sending_user_id" = "user_id" ORDER BY "when"';
		
		$res = $this->mDb->query($q);
		
		$list = array();
		
		// code stolen from Student::list_data, perhaps make a util function out of it?
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
	
	function get_message($message_id)
	{
		$q = 'SELECT "recipient", "subject", "message", "headers" FROM "message_queue" WHERE "message_id" = '."'$message_id'";

		$res = $this->mDb->query($q);
		
		$obj = $res->fetch_object();
		$msg = new Message($obj->recipient, $obj->subject, $obj->message, $obj->headers);
		
		$res->free();
		
		return $msg;		
	}
	
	function remove_message($message_id)
	{
		$q = 'DELETE FROM "message_queue" WHERE "message_id" = '."'$message_id'";
		
		$this->mDb->execute($q);
	}
	
	function front()
	{
		$q = 'SELECT "recipient", "subject", "message", "headers" FROM "message_queue" ORDER BY "when" LIMIT 1';
		
		$res = $this->mDb->query($q);
		
		$obj = $res->fetch_object();
		if($obj)
			$msg = new Message($obj->recipient, $obj->subject, $obj->message, $obj->headers);
		else
			$msg = NULL;
		
		$res->free();
		
		return $msg;
	}
	
	function dequeue()
	{
		$front = $this->front();
		
		$q = 'SELECT "message_id" FROM "message_queue" ORDER BY "when" LIMIT 1';
		
		$res = $this->mDb->query($q);
		
		$obj = $res->fetch_object();
		if($obj)
			$message_id = $obj->message_id;
		
		$res->free();
		
		if($message_id)
			$this->mDb->remove('"message_queue"', array('"message_id"' => $message_id));
		
		return $front;
	}
	
	function enqueue($msg)
	{
		$max = $this->mDb->select_one('message_queue', 'MAX(message_id) as m', '');
		$message_id = $max->m+1;
		
		$this->mDb->insert('"message_queue"', 
			array('"message_id"' => $message_id,
				'"when"' => new QueryExpression("NOW()"),
				'"sending_user_id"' => get_session_user_id(),
				'"recipient"' => $msg->recipient(),
				'"subject"' => $msg->subject(),
				'"message"' => $msg->message(),
				'"headers"' => $msg->header_string()));
	}
	
	function clear()
	{
		$q = 'DELETE FROM message_queue';
		
		$this->mDb->execute($q);
	}
}

class MessageSender
{
	function __construct()
	{
	}
	
	function allowed_email($recipient)
	{
		global $config;
		if($config['message']['allow_all'])
			return TRUE;
		
		preg_match('/<(.*?)>/', $recipient, $matches);
		
		$email = $matches[1];
		
		$q = 'SELECT COUNT(*) FROM allowed_email WHERE "email" = '."'$email'";
		
		$res = get_db()->query($q);
		
		$row = $res->fetch_array();
		
		$res->free();
		
		return ($row[0] > 0);
	}
	
	function send_message($msg)
	{	
		if(!$this->allowed_email($msg->recipient()))
			return FALSE;
		
		mail($msg->recipient(), $msg->subject(), $msg->message(), $msg->header_string());
		
		//get_logger()->log('SENT EMAIL TO '.$msg->recipient());
		
		//echo 'SENT EMAIL TO '.$msg->recipient();
		
		return TRUE;
	}
	
	function send_next_message($msgq)
	{
		$front = $msgq->dequeue();
		if(!$front)
			return FALSE;
		if(!($success = $this->send_message($front)))
			$msgq->enqueue($front);
		return $success;
	}
}

?>