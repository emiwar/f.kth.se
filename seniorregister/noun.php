<?php

require_once('globals.php');

$gNouns = array(
	'list' => 'ListNoun', 
	'student' => 'StudentNoun', 
	'auth' => 'AuthNoun',
	'userlist' => 'UserListNoun',
	'user' => 'UserNoun',
	'pref' => 'PreferencesNoun',
	'invitelist' => 'InviteListNoun',
	'invite' => 'InviteNoun',
	'dialog' => 'DialogNoun',
	'message' => 'MessageNoun',
	'messagequeue' => 'MessageQueueNoun',
	'messagesender' => 'MessageSenderNoun');

abstract class Noun
{
	public $mBuffer, $mOut, $mReq, $mHtmlg, $mDb, $mParams;
	
	function __construct() { }
	
	function new_from_request($req)
	{
		global $gNouns;
		
		$req = NounRequest::new_from_request($req);
		
		$nounClass = new ReflectionClass($gNouns[$req->noun()]);
		$noun = $nounClass->newInstance();
		/*if($req->noun() == 'student')
			$noun = new StudentNoun();
		elseif($req->noun() == 'list')
			$noun = new ListNoun();*/
		
		$noun->init($req);
		
		return $noun;
	}
	
	function init($req)
	{
		$this->mBuffer = '';
		$this->mReq = $req;
		$this->mHtmlg = get_htmlg();
		$this->mDb = get_db();
		$this->mParams = get_parameters();
	}
	
	function output($str)
	{
		$this->mBuffer .= $str;
	}
	
	abstract function noun_header();
	abstract function noun_footer();
	
	function top_box() { return ''; }
	function tab_array() { return array(); }
	function tab_selected() { return 0; }
	
	abstract function read();
	abstract function write();
	abstract function validate();
	abstract function remove();
	
	abstract function is_display();
	
	abstract function is_allowed($user);
	
	function process()
	{
		if($this->is_display() && $this->mReq->format() == 'xhtml')
			$this->output($this->noun_header());
		
		if($this->mReq->verb() == 'read')
			$this->output($this->read());
		elseif($this->mReq->verb() == 'write')
			$this->output($this->write());
		/*elseif($this->mReq->verb() == 'validate')
			$this->output($this->validate());*/
		elseif($this->mReq->verb() == 'remove')
			$this->output($this->remove());
			
		if($this->is_display() && $this->mReq->format() == 'xhtml')
			$this->output($this->noun_footer());
			
		return $this->mBuffer;
	}
}

?>