<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
// Created on Oct 17, 2011 by Damiano Venturin @ Squadra Informatica

//this obj is just a way to simplify the search through people and organizations in one shot
class Contact extends ObjectCommon
{
	protected $person;
	protected $organization;
	
	public function __construct()
	{
		parent::__construct();

		$this->person = new Person();
		$this->organization = new Organization();
		
		log_message('debug', 'Contact class has been loaded');
	}

	public function __destruct() {
		parent::__destruct();
	}
	
	public function read($input)
	{
		if(!is_array($input) || empty($input))
		{
			$data = array();
			$data['error'] = 'The input should be a populated array';
		}
		
		$output = array();
		$output['person'] = $this->person->read($input); 
		$output['organization'] =$this->organization->read($input);
		
		return $output;
	}
}