<?php

/*require('db.php');
require('pgdb.php');
require('logger.php');*/

class Parameters
{
	var $mDb;
	
	var $mClasses;
	
	var $mSpecs;
	
	var $mPhoneTypes;
	
	var $mTitles;
	
	var $mCommittees;
	
	var $mPositions;
	
	function __construct($db)
	{
		$this->mDb = $db;
		$this->mClasses = array();
		$this->mSpecs = array();
		$this->mPhoneTypes = array();
		$this->mTitles = array();
		$this->mCommittees = array();
		$this->mPositions = array();
	}
	
	function load_generic($table, $key, $fields)
	{
		$res = $this->mDb->select($table,
			array_merge(array($key), $fields), NULL);
		
		$ret = array();
		
		while(($row = $res->fetch_array()) !== false)
		{
			foreach($row as $k => $v)
				if(!is_numeric($k))
					unset($row[$k]);
			// need to improve fetch_array...
			
			$ret[$row[0]] = (count($row) == 2) ? $row[1] : array_slice($row, 1);
		}
		
		$res->free();
		
		ksort($ret);
		
		return $ret;
	}
	
	function load_classes()
	{
		$this->mClasses = $this->load_generic('class', 'class_year', array('abbrev', 'name'));
	}
	
	function load_specializations()
	{
		$this->mSpecs = $this->load_generic('specialization', 'spec_id', array('name'));
	}

	function load_telephone_types()
	{
		$this->mPhoneTypes = $this->load_generic('telephone_type', 'type_id', array('name'));
	}

	function load_titles()
	{
		$this->mTitles = $this->load_generic('title', 'title_id', array('name'));
	}
	
	function load_committees()
	{
		$this->mCommittees = $this->load_generic('committee', 'committee_id', array('name', 'abbrev', 'member_name'));
	}
	
	function load_positions()
	{
		$this->mPositions = $this->load_generic('position', 'position_id', array('name', 'committee_id', 'gov'));
	}
	
	function load()
	{	
		$this->load_classes();
		$this->load_specializations();
		$this->load_telephone_types();
		$this->load_titles();
		$this->load_committees();
		$this->load_positions();
	}
	
	function commit_generic($table, $key, $fields, &$data)
	{		
		$res = $this->mDb->select($table,
			array_merge(array($key), $fields), NULL);
			
		$toAdd = array_keys($data);
		$toRemove = array();
		$toUpdate = array();
		
		while(($row = $res->fetch_array()) !== false)
		{
			foreach($row as $k => $v)
				if(!is_numeric($k))
					unset($row[$k]);
			// need to improve fetch_array...
		
			$i = array_search($row[0], $toAdd);
					
			if($i === false)
			{
				$toRemove[] = $row[0];
			}
			else
			{
				unset($toAdd[$i]);
				if((!is_array($data[$row[0]]) && $data[$row[0]] != $row[1]) ||
				   (is_array($data[$row[0]]) && $data[$row[0]] != array_slice($row, 1)))
				{
					$toUpdate[] = $row[0];
				}
			}
		}
		
		$res->free();
		
		foreach($toAdd as $key_val)
			$this->mDb->insert($table,
				array_merge(array($key => $key_val), 
					array_combine($fields, is_array($data[$key_val]) ? $data[$key_val] : array($data[$key_val]))));
					// ugly... should have ensure_array or something
					
		foreach($toRemove as $key_val)
			$this->mDb->remove($table,
				array($key => $key_val));
				
		foreach($toUpdate as $key_val)
			$this->mDb->update($table,
				array_combine($fields, is_array($data[$key_val]) ? $data[$key_val] : array($data[$key_val])),
				array($key => $key_val));
	}
	
	function commit_classes()
	{
		$this->commit_generic('class', '"class_year"', array('"abbrev"', '"name"'), $this->mClasses);
	}
	
	function commit_specializations()
	{
		$this->commit_generic('specialization', 'spec_id', array('name'), $this->mSpecs);
	}
	
	function commit_telephone_types()
	{
		$this->commit_generic('telephone_type', 'type_id', array('name'), $this->mPhoneTypes);
	}
	
	function commit_titles()
	{
		$this->commit_generic('title', 'title_id', array('name'), $this->mTitles);
	}

	function commit_committees()
	{
		$this->commit_generic('committee', 'committee_id', array('name', 'abbrev', 'member_name'), $this->mCommittees);
	}
	
	function commit_positions()
	{
		$this->commit_generic('position', 'position_id', array('name', 'committee_id', 'gov'), $this->mPositions);
	}
	
	function commit()
	{
		$this->commit_classes();
		$this->commit_specializations();
		$this->commit_telephone_types();
		$this->commit_titles();
		$this->commit_committees();
		$this->commit_positions();
	}
	
	function class_years()
	{
		return array_keys($this->mClasses);
	}
	
	function clear_classes()
	{
		$this->mClasses = array();
	}
	
	function class_abbreviation($year)	
	{
		return isset($this->mClasses[$year]) ? $this->mClasses[$year][0] : null;
	}
	
	function class_name($year)
	{
		return isset($this->mClasses[$year]) ? $this->mClasses[$year][1] : null;
	}
	
	function set_class($year, $abbrev, $name)
	{
		$this->mClasses[$year] = array($abbrev, $name);	
	}
	
	function remove_class($year)
	{
		unset($this->mClasses[$year]);
	}
	
	function specialization_ids()
	{
		return array_keys($this->mSpecs);
	}
	
	function clear_specializations()
	{
		$this->mSpecs = array();
	}
	
	function specialization_name($specId)
	{
		return isset($this->mSpecs[$specId]) ? $this->mSpecs[$specId] : null;
	}
	
	function set_specialization($specId, $name)
	{
		$this->mSpecs[$specId] = $name;
	}
	
	function remove_specialization($specId)
	{
		unset($this->mSpecs[$specId]);
	}
	
	function telephone_type_ids()
	{
		return array_keys($this->mPhoneTypes);
	}
	
	function clear_telephone_types()
	{
		$this->mPhoneTypes = array();
	}
	
	function telephone_type_name($typeId)
	{
		return isset($this->mPhoneTypes[$typeId]) ? $this->mPhoneTypes[$typeId] : null;
	}
	
	function set_telephone_type($typeId, $name)
	{
		$this->mPhoneTypes[$typeId] = $name;
	}
	
	function remove_telephone_type($typeId)
	{
		unset($this->mPhoneTypes[$typeId]);
	}
	
	function title_ids()
	{
		return array_keys($this->mTitles);
	}
	
	function clear_titles()
	{
		$this->mTitles = array();
	}
	
	function title_name($titleId)
	{
		return isset($this->mTitles[$titleId]) ? $this->mTitles[$titleId] : null;
	}
	
	function set_title($titleId, $name)
	{
		$this->mTitles[$titleId] = $name;
	}
	
	function remove_title($titleId)
	{
		unset($this->mTitles[$titleId]);
	}
	
	function committee_ids()
	{
		return array_keys($this->mCommittees);
	}
	
	function clear_committees()
	{
		$this->mCommittees = array();
	}
	
	function committee_name($committeeId)
	{
		return isset($this->mCommittees[$committeeId]) ? $this->mCommittees[$committeeId][0] : null;
	}
	
	function committee_abbreviation($committeeId)
	{
		return isset($this->mCommittees[$committeeId]) ? $this->mCommittees[$committeeId][1] : null;
	}
	
	function committee_member_name($committeeId)
	{
		return isset($this->mCommittees[$committeeId]) ? $this->mCommittees[$committeeId][2] : null;		
	}
	
	function set_committee($committeeId, $name, $abbrev, $memberName)
	{
		$this->mCommittees[$committeeId] = array($name, $abbrev, $memberName);
	}
	
	function remove_committee($committeeId)
	{
		unset($this->mCommittees[$committeeId]);
	}
	
	function position_ids()
	{
		return array_keys($this->mPositions);
	}
	
	function clear_positions()
	{
		$this->mPositions = array();
	}
	
	function position_name($positionId)
	{
		return isset($this->mPositions[$positionId]) ? $this->mPositions[$positionId][0] : null;
	}
	
	function position_committee_id($positionId)
	{
		return isset($this->mPositions[$positionId]) ? $this->mPositions[$positionId][1] : null;
	}
	
	function position_government($positionId)
	{
		return isset($this->mPositions[$positionId]) ? ($this->mPositions[$positionId][2] == 't' || $this->mPositions[$positionId][2] == 1) : null;		
	}
	
	function set_position($positionId, $name, $committeeId, $gov)
	{
		$this->mPositions[$positionId] = array($name, $committeeId, $gov ? 't' : 'f');
		// XXX problem
		// this isn't used right now though, so it could wait
	}
	
	function remove_position($positionId)
	{
		unset($this->mPositions[$positionId]);
	}
}
/*
header('Content-type: text/plain; charset=utf-8');

$db = new PostgresDatabase('localhost', 'register', 'register', 'register_i', new Logger('parameters_db.log', true));
$p = new Parameters($db);
$p->load();
$p->mClasses[1975] = array('fxx', 'Flörtisss');
unset($p->mClasses[1937]);
$p->mClasses[2009] = array('f09', 'Faaan');
$p->commit_classes();
$p->mSpecs[3] = 'blahaa';
unset($p->mSpecs[100]);
$p->mSpecs[101] = 'Hmm';
$p->commit_specializations();
$p->commit();
$db->close();*/

?>