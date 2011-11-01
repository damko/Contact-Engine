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
		$data = array();
		if(!is_array($input) || empty($input)) 
		{
			$data['error'] = 'The input should be a populated array'; 
		} else {
			$people = $this->person->read($input);
			$statuses[] = $people['RestStatus'];
			unset($people['RestStatus']);
			
			$organizations = $this->organization->read($input);
			$statuses[] = $organizations['RestStatus'];
			unset($organizations['RestStatus']);
			
			$data = array_merge($people,$organizations);
			foreach ($statuses['0'] as $key => $value) {
				$statuses['final'][$key] = $value + $statuses['1'][$key];
			}
			!empty($statuses['0']['results_number']) ? $statuses['final']['result_number_people'] = $statuses['0']['results_number'] : $statuses['final']['result_number_people'] = 0;
			!empty($statuses['1']['results_number']) ? $statuses['final']['result_number_orgs'] = $statuses['1']['results_number'] : $statuses['final']['result_number_orgs'] = 0;
			
			$data['RestStatus'] = $statuses['final'];
		}
		
		
		return $data;
	}
}