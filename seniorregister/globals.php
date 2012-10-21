<?php

require_once('config.php');

require_once('reg_request.php');
require_once('db.php');
require_once('pgdb.php');
require_once('logger.php');
require_once('buffer_logger.php');
require_once('parameters.php');
require_once('reg_htmlgen.php');
require_once('auth.php');
require_once('msg_queue.php');

function get_noun_request()
{
	static $req;
	if(!$req)
		$req = new NounRequest();
	return $req;
}

function get_logger()
{
	global $config;
	static $logger;
	if(!$logger)
	{
		if($config['log']['type'] == 'file')
			$logger = new Logger($config['dirs']['log'].$config['log']['file'], $config['log']['enable']);
		elseif($config['log']['type'] == 'buffer')
			$logger = new BufferLogger($config['log']['enable']);
	}
	return $logger;
}

function get_db()
{
	global $config;
	static $db;
	if(!$db)
	{
		if($config['db']['system'] == 'postgres')
		{
			$db = new PostgresDatabase(
				$config['db']['server'], 
				$config['db']['user'], 
				$config['db']['pass'], 
				$config['db']['database'], 
				get_logger());
		}
		elseif($config['db']['system'] == 'mysql')
		{
			$db = new Database(
				$config['db']['server'], 
				$config['db']['user'], 
				$config['db']['pass'],
				$config['db']['database'], 
				get_logger());
			$db->execute("SET SESSION sql_mode='ANSI_QUOTES'");
		}
		
		$db->set_encoding('utf-8');
	}
	return $db;
}

function get_parameters()
{
	static $params;
	if(!$params)
	{
		$params = new Parameters(get_db());
		if(get_db()->ping())
			$params->load();
	}
	return $params;
}

function get_htmlg()
{
	static $htmlg = NULL;
	if(!$htmlg)
		$htmlg = new RegisterXhtmlGenerator(false);
	return $htmlg;
}

function get_session_user()
{
	static $user;
	if(!$user)
	{
		$user = new AuthUser(get_db(), get_session_user_id());
		if(get_db()->ping())
			$user->load();
	}
	return $user;
}

function get_message_queue()
{
	static $msgq = NULL;
	if(!$msgq)
		$msgq = new MessageQueue(get_db());
	return $msgq;
}

function get_message_sender()
{
	static $msgsnd = NULL;
	if(!$msgsnd)
		$msgsnd = new MessageSender();
	return $msgsnd;
}

?>