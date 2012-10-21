<?php

require_once('reg_request.php');
require_once('htmlgen.php');
require_once('logger.php');
require_once('buffer_logger.php');
require_once('db.php');
require_once('pgdb.php');

require_once('student.php');
require_once('parameters.php');

require_once('utils.php');

require_once('reg_htmlgen.php');

require_once('student_noun.php');
require_once('list_noun.php');
require_once('auth_noun.php');
require_once('userlist_noun.php');
require_once('user_noun.php');
require_once('pref_noun.php');
require_once('invitelist_noun.php');
require_once('invite_noun.php');
require_once('dialog_noun.php');
require_once('msg_noun.php');
require_once('msg_queue_noun.php');
require_once('msg_sender_noun.php');

/*require_once('mailer.php');*/

require_once('auth.php');

require_once('globals.php');

class RegisterPage
{
	var $mOut, $mReq, $mHtmlg, $mDb, $mParams, $mUser;
	
	var $mTabs, $mSelectedTab, $mTopBox;
	
	function __construct()
	{
		$this->mOut = fopen("php://output", "w");
		/*$this->mReq = new NounRequest();
		$this->mHtmlg = new RegisterXhtmlGenerator(false);
		$this->mDb = new PostgresDatabase('localhost', 'register', 'register', 'register_i', new Logger('register_db.log', true));
		$this->mParams = new Parameters($this->mDb);
		$this->mParams->load();
		
		$this->mUser = new AuthUser($this->mDb, get_session_user_id());
		$this->mUser->load();*/
		
		$this->mReq = get_noun_request();
		$this->mHtmlg = get_htmlg();
		$this->mDb = get_db();
		$this->mParams = get_parameters();
		$this->mUser = get_session_user();
		//$this->mHtmlg->mUser = $this->mUser;
		// cringe...
		
		$this->mTabs = array();
		$this->mTabSelected = 0;
		$this->mTopBox = '';
	}
	
	function get_parameter($key, $default = null)
	{
		/*return (isset($_GET[$key]) && !empty($_GET[$key])) ? (get_magic_quotes_gpc() ? removeslashes($_GET[$key]) : $_GET[$key]) : ($default);*/
		return $this->mReq->data($key, $default);
	}
	
	function output($str)
	{
		fwrite($this->mOut, $str);
	}
	
	function lists_json()
	{
		$p = $this->mParams;
		
		$titles = array();
		foreach($p->title_ids() as $title_id)
			$titles[$title_id] = $p->title_name($title_id);
			
		$positions = array();
		foreach($p->position_ids() as $position_id)
			$positions[$position_id] = $p->position_name($position_id) . ' (' . $p->committee_name($p->position_committee_id($position_id)) . ')';
		
		$committees = array();
		foreach($p->committee_ids() as $committee_id)
			$committees[$committee_id] = $p->committee_name($committee_id) . ' (' . $p->committee_abbreviation($committee_id) . ')';
			
		$resp = array('titles' => $titles, 'positions' => $positions, 'committees' => $committees);
		
		return json_encode($resp);
	}
	
	function page_header()
	{	
		global $config;
		
		$stylesheet_dir = $config['dirs']['stylesheet'];
		$script_dir = $config['dirs']['script'];
		
		// apparently xml declarations confuse msie 6.0, reverting to quirks mode
		$t = //$this->mHtmlg->xml_declaration() . "\n" . 
			$this->mHtmlg->xhtml_doctype() . "\n" .
			$this->mHtmlg->begin_html() . "\n" .
				$this->mHtmlg->begin_head() . "\n" .
					$this->mHtmlg->title('Seniorregister') . "\n" .
					//$this->mHtmlg->meta_http_equiv("Content-Type", "application/xml+xhtml; charset=utf-8") . "\n" .
					//$this->mHtmlg->meta_http_equiv("Content-Type", "text/html; charset=utf-8") . "\n" .
					$this->mHtmlg->link_stylesheet($stylesheet_dir.'main.css') . "\n" .
					$this->mHtmlg->script_src('javascript', $script_dir.'lib/jquery.js') . "\n" .
					$this->mHtmlg->script_src('javascript', $script_dir.'lib/jquery-ui.js') . "\n" .
					$this->mHtmlg->script_src('javascript', $script_dir.'main.js') . "\n" .
					$this->mHtmlg->begin_script('javascript') . "\n" .
					"lists = " . $this->lists_json() . ";\n" .
					$this->mHtmlg->end_script() . "\n" .
				$this->mHtmlg->end_head() . "\n" .
				$this->mHtmlg->begin_body(array('onload' => 'load()')) . "\n" .
					$this->mHtmlg->begin_div('outer') . "\n" .
					$this->mHtmlg->begin_div('topnav') . "\n" .
					'topnav' .
					$this->mHtmlg->end_div() . "\n" .
					$this->mHtmlg->begin_div('header') . "\n" .
					$this->mHtmlg->heading(1, "Seniorregister") . "\n" .
					$this->mHtmlg->end_div() . "\n" .
					/*$this->mHtmlg->begin_div('ltabs') . "\n" .
					$this->mHtmlg->begin_div('tab1', 'tab selected') . "tab1" . $this->mHtmlg->end_div() . "\n" .
					$this->mHtmlg->begin_div('tab2', 'tab') . "tab2" . $this->mHtmlg->end_div() . "\n" .
					$this->mHtmlg->end_div() . "\n" .*/
					$this->mHtmlg->begin_div('wrapper') .
					$this->mHtmlg->begin_div('tabs') . "\n";
					
		foreach($this->mTabs as $i => $tab_data)
		{
			$t .= $this->mHtmlg->begin_div('tab_'.$i, 'tab' . ($i == $this->mTabSelected ? ' selected' : '')) . $tab_data . $this->mHtmlg->end_div() . "\n";
		}
		$t .= $this->mHtmlg->end_div() . "\n" .
			$this->mHtmlg->begin_div('noun') . "\n";
		if($this->mTopBox)
			$t .= $this->mHtmlg->begin_div('topbox') . $this->mTopBox . $this->mHtmlg->end_div('topbox') . "\n";
		$t .= $this->mHtmlg->begin_div('inner') . "\n";
		
		return $t;
	}
	
	function page_footer()
	{
		$t = $this->mHtmlg->end_div() . "\n" .
			$this->mHtmlg->end_div() . "\n" .
			$this->mHtmlg->begin_div('navigation') . "\n" .
			$this->mHtmlg->heading(3, "Navigation") . "\n";
		
		$links = array(
			(get_session_user()->owns_student_id() != -1 ? 
				$this->mHtmlg->noun_ahref(NounRequest::new_from_spec('read', 'student', 'core', 'view', 'xhtml', array('student_id' => get_session_user()->owns_student_id())), 'Hem', false, array('accesskey' => 'h')) : ''),
			$this->mHtmlg->noun_ahref(NounRequest::new_from_spec('read', 'list', 'filter', 'view', 'xhtml'), 'Lista', true, array('accesskey' => 'l')),
			$this->mHtmlg->noun_ahref(NounRequest::new_from_spec('read', 'list', 'search', 'view', 'xhtml'), 'Sök', true, array('accesskey' => 'f')),
			$this->mHtmlg->noun_ahref(NounRequest::new_from_spec('read', 'userlist', '', 'view', 'xhtml'), 'Användare', true, array('accesskey' => 'u')),
			$this->mHtmlg->noun_ahref(NounRequest::new_from_spec('read', 'invitelist', '', 'view', 'xhtml'), 'Inbjudan', true, array('accesskey' => 'i')),
			$this->mHtmlg->noun_ahref(NounRequest::new_from_spec('read', 'messagequeue', '', 'view', 'xhtml'), 'Meddelandekö', true, array('accesskey' => 'm')),
			$this->mHtmlg->noun_ahref(NounRequest::new_from_spec('read', 'pref', '', 'edit', 'xhtml'), 'Inställningar', false, array('accesskey' => ',')),
			$this->mHtmlg->noun_ahref(NounRequest::new_from_spec('write', 'auth', 'out', 'view', 'xhtml'), 'Logga ut', false, array('accesskey' => 'q')));
			
		foreach($links as $link)
			if($link)
				$t .= $link . "<br />\n"; 
			
		$t .= $this->mHtmlg->end_div() . "\n" .
			$this->mHtmlg->end_div() . "\n" .
			$this->mHtmlg->end_div() . "\n" .
			$this->mHtmlg->end_body() . "\n" .
			$this->mHtmlg->end_html() . "\n";
		
		return $t;
	}
	
	function page_forbidden()
	{
		return $this->page_header() . 
			"Operationen är ej tillåten." . 
			$this->page_footer();
	}
	
	function process()
	{			
		global $gNouns;
		
		if(!$this->mDb->ping() && !$this->mReq->data_scalar('nodb'))
		{
			//echo "no ping";
			echo "'" . $this->mReq->data_scalar('nodb') . "'";
			echo('Location: ' . NounRequest::new_from_spec('read', 'auth', 'in', 'view', 'xhtml', array('nodb' => 1))->href());
			return;
		}
		
		/*$mailer = new Mailer(get_db());
		$mailer->set_header('From', 'janden@kth.se');
		$mailer->set_header('Reply-To', 'janden@kth.se');
		$mailer->send_mail('joakim.anden@gmail.com', 'Hey there!', "What's up over there?\n\nJoakim", array());*/ 
				
		if(isset($gNouns[$this->mReq->noun()]))
		{
			$n = Noun::new_from_request($this->mReq);
			
			if($n->is_allowed($this->mUser))
			{
				$this->mTabs = $n->tab_array();
				$this->mTabSelected = $n->tab_selected();
				$this->mTopBox = $n->top_box();
			
				if($n->is_display() && $this->mReq->format() == 'xhtml')
				{
					//header('Content-type: application/xhtml+xml; charset=utf-8');
					// ^ might be a bit too strict for us...
					header('Content-type: text/html; charset=utf-8');
					$this->output($this->page_header());
				}
		
				$this->output($n->process());
		
				if($n->is_display() && $this->mReq->format() == 'xhtml')
					$this->output($this->page_footer());
			}
			else
				$this->output($this->page_forbidden());
				
			if($this->mReq->format() == 'xhtml' &&
				get_logger() instanceof BufferLogger)
				$this->output("<!--\n" . get_logger()->get_log() . "-->");
		}
		else
		{
			if(get_session_user()->user_id() == -1)
				header('Location: ' . NounRequest::new_from_spec('read', 'auth', 'in', 'view', 'xhtml')->href());
			else if(get_session_user()->owns_student_id() == -1)
				header('Location: ' . NounRequest::new_from_spec('read', 'list', 'filter', 'view', 'xhtml')->href());
			else
				header('Location: ' . NounRequest::new_from_spec('read', 'student', 'core', 'view', 'xhtml', array('student_id' => get_session_user()->owns_student_id()))->href());
		}
	}
}

?>