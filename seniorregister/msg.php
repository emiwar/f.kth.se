<?php

require_once('student.php');

// for replacing tags in templates
function on_match($values, $matches)
{
	return isset($values[strtolower($matches[1])]) ? $values[strtolower($matches[1])] : '{INVALID}';
}

function header_string_to_array($headers)
{
	$headers = explode("\r\n", $headers);
	$r_headers = array();
	foreach($headers as $i => $str)
	{
		if($str == '')
			continue;
		$header = explode(':', $str);
		$header[0] = trim($header[0]);
		$header[1] = trim($header[1]);
		
		$r_headers[] = $header;
	}
	
	return $r_headers;
}

class Message
{
	var $mTo, $mSubject, $mMessage;
	
	var $mHeaders;
	
	function __construct($recipient = '', $subject = '', $message = '', $headers = NULL)
	{		
		$this->mTo = $recipient;
		$this->mSubject = $subject;
		$this->mMessage = $message;
	
		if(is_null($headers))
			$headers = array();
		elseif(is_array($headers) &&
			count($headers) == 2 &&
			!is_array($headers[0]))
			$headers = array($headers);
		elseif(!is_array($headers))
			$headers = header_string_to_array($headers);
						
		$this->mHeaders = $headers;
	}

	function set_var_ex(&$dst, $args)
	{
		$old = $dst;
		if(count($args) == 1)
			$dst = $args[0];
		return $old;
	}
	
	function recipient()
	{
		$args = func_get_args(); return $this->set_var_ex($this->mTo, $args);	
	}
	
	function subject()
	{
		$args = func_get_args(); return $this->set_var_ex($this->mSubject, $args);
	}
	
	function message()
	{
		$args = func_get_args(); return $this->set_var_ex($this->mMessage, $args);
	}
	
	function header_keys()
	{
		$keys = array();
		foreach($this->mHeaders as $tuple)
			if(!in_array($tuple[0], $keys))
				$keys[] = $tuple[0];
		return $keys;
	}
	
	function header_values($key)
	{
		$values = array();
		foreach($this->mHeaders as $tuple)
			if($tuple[0] == $key)
				$values[] = $tuple[1];
		return $values;
	}
	
	function add_header($key, $value)
	{
		$this->mHeaders[] = array($key, $value);
	}
	
	function remove_header($key, $value = NULL)
	{
		foreach($this->mHeaders as $i => $tuple)
			if($tuple[0] == $key && 
					(is_null($value) || $tuple[1] == $value))
				unset($this->mHeaders[$i]);
	}
	
	function clear_headers()
	{
		$this->mHeaders = array();
	}

	function headers()
	{
		return $this->mHeaders;
	}
	
	function header_string()
	{
		$headerString = array();
		foreach($this->mHeaders as $tuple)
			$headerString[] = $tuple[0] . ": " . $tuple[1];
		return implode("\r\n", $headerString);
	}
}

class MessageTemplate extends Message
{
	var $mDb;
	
	var $mTables = array(
			array('email', 'student_id'),
			array('auth_user', 'owns_student_id'),
			array('auth_invite', 'owns_student_id'));
	
	var $mTagFields = array(
			'student.first_name' => 'student."first_name"',
			'student.last_name' => 'student."last_name"',
			'student.class_year' => 'student."class_year"',
			'student.street_address' => 'student."street_address"',
			'student.postal_address' => 'student."postal_address"',
			'student.paid_until' => 'student."paid_until"',
			'student.graduation' => 'student."graduation"',
			'student.email' => 'email."email"',
			'user.username' => 'auth_user."username"',
			'invite.use_link' => 'auth_invite."invite_code"'
		);
		
	var $mTagPattern = '/{([a-zA-Z0-9._]*)}/';
	
	function __construct($db, $recipient = '', $subject = '', $message = '', $headers = NULL)
	{
		parent::__construct($recipient, $subject, $message, $headers);
		
		$this->mDb = $db;
	}
	
	function new_from_message($db, $msg)
	{
		$tmpl = new MessageTemplate(
			$db,
			$msg->recipient(),
			$msg->subject(),
			$msg->message(),
			$msg->headers());
		
		/*foreach($msg->header_keys() as $key)
			foreach($msg->header_values($key) as $value)
				$tmpl->add_header($key, $value);*/
				
		return $tmpl;
	}
	
	function fill_template($qual)
	{
		global $config;
		
		if(is_numeric($qual))
			$qual = new IdCriterion($qual);
			
		$tables = $this->merge_table_lists($qual->tables($this->mDb), $this->mTables);
		
		$data_list = Student::list_data(
			$this->mDb,
			$tables,
			/*array(
				'student."first_name"',
				'student."last_name"',
				'student."class_year"',
				'student."street_address"',
				'student."postal_address"',
				'student."paid_until"',
				'student."graduation"',
				'email."email"',
				'auth_user."username"',
				'auth_invite."invite_code"'),*/
			array_values($this->mTagFields),
			$qual->requirement($this->mDb).' AND email."standard" = TRUE');
		
		$messages = array();
		/*print_r($qual);
		print_r($data_list);*/
		
		foreach($data_list as $id => $arr)
		{
			/*$values = array(
					'student.first_name' => $arr[0],
					'student.last_name' => $arr[1],
					'student.class_year' => $arr[2],
					'student.street_address' => $arr[3],
					'student.postal_address' => $arr[4],
					'student.paid_until' => $arr[5],
					'student.graduation' => $arr[6],
					'student.email' => $arr[7],
					'user.username' => $arr[8],
					'invite.invite_code' => $arr[9]
				);*/
			$values = array();
			$i = 0;
			foreach($this->mTagFields as $key => $value)
				if($key != 'invite.use_link')
					$values[$key] = $arr[$i++];
				elseif($key == 'invite.use_link')
					$values[$key] = $config['app']['root_url'].NounRequest::new_from_spec('read', 'user', '', 'create', 'xhtml', array('invite_code' => $arr[$i++]))->href();
			
			$callback = curry('on_match', 2);
		
			$msg = new Message();
		
			$msg->recipient(preg_replace_callback($this->mTagPattern, $callback($values), $this->recipient()));
			$msg->subject(preg_replace_callback($this->mTagPattern, $callback($values), $this->subject()));
			$msg->message(preg_replace_callback($this->mTagPattern, $callback($values), $this->message()));
			
			$messages[] = $msg;
		}
		
		return $messages;
	}
	
	function test_template($qual)
	{
		if(is_numeric($qual))
			$qual = new IdCriterion($qual);
			
		$tags = $this->tag_list();
		$fields = array();
		foreach($tags as $tag)
			$fields[] = $this->mTagFields[$tag];
	
		$fields_null = implode(' OR ', array_map(create_function('$a','return "$a IS NULL";'), $fields));
		
		$tables = $this->merge_table_lists($qual->tables($this->mDb), $this->mTables);
		
		$data_list = Student::list_data(
			$this->mDb,
			$tables,
			array_values($this->mTagFields),
			$qual->requirement($this->mDb).' AND email."standard" = TRUE AND ('.$fields_null.')');
		
		return count($data_list);
	}
	
	function tag_list()
	{
		$tags = array();
		
		preg_match_all($this->mTagPattern, $this->recipient(), $recipient_matches);
		preg_match_all($this->mTagPattern, $this->subject(), $subject_matches);
		preg_match_all($this->mTagPattern, $this->message(), $message_matches);
		
		$matches = array_merge($recipient_matches[1], array_merge($subject_matches[1], $message_matches[1]));
		
		foreach($matches as $match)
			if(!in_array($match, $tags))
				$tags[] = $match;
		
		return $tags;
	}
	
	function invalid_tags()
	{
		$tags = $this->tag_list();
		
		$invalid_tags = array();
		
		foreach($tags as $tag)
			if(!isset($this->mTagFields[$tag]) &&
				!in_array($tag, $invalid_tags))
					$invalid_tags[] = $tag;
		
		print_r($invalid_tags);
		
		return $invalid_tags;
	}
	
	function valid()
	{
		return count($this->invalid_tags()) == 0;
	}
	
	function merge_table_lists($a, $b)
	{
		$tables = $a;
		foreach($b as $tuple)
		{
			$in = false;
			foreach($tables as $x_tuple)
				if($x_tuple[0] == $tuple[0])
					$in = true;
			if(!$in)
				$tables[] = $tuple;
		}
		
		return $tables;
	}
}

?>