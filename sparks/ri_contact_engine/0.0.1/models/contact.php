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
	
	public function read(array $input)
	{
		$data = array();
		if(!is_array($input) || empty($input)) 
		{
			$this->result = new Ce_Return_Object();
			$this->result->data = array();
			$this->result->http_status_code = '415';
			$this->result->http_message = 'The method read for the object '.$this->objName.' requires an array in input.';
			$this->result->results_number = '0';
			$this->result->results_got_number = 0;
				
			return $this->result->returnAsArray(); 
			
		} else {
			$return = $this->person->read($input);
			$status_people = $return['status'];
			if($status_people['status_code'] == '200')
			{
				$people = $return['data'];
				$results_pages_people = $return['status']['results_pages'];
				$results_page_people = $return['status']['results_page'];						
			} else {
				$this->result->importLdapReturnObject($this->ri_ldap->result);	
				return $this->result->returnAsArray();
			}
			
			$return = $this->organization->read($input);
			$status_organizations = $return['status'];
			if($status_organizations['status_code'] == '200')
			{
				$organizations  = $return['data'];
				$results_pages_organizations = $return['status']['results_pages'];
				$results_page_organizations = $return['status']['results_page'];
			} else {
				$this->result->importLdapReturnObject($this->ri_ldap->result);
				return $this->result->returnAsArray();
			}			
				
			$data = array_merge($people,$organizations);
			$this->result = new Ce_Return_Object();
			$this->result->data = $data;
			$this->result->http_status_code = '200';
			$this->result->http_message = 'OK';
			$this->result->results_number = $status_people['results_number'] + $status_organizations['results_number'];
			$this->result->results_got_number = $status_people['results_got_number'] + $status_organizations['results_got_number'];
			
			//with pages now is a big deal. I return the highest value
			$diff = $results_pages_people - $results_pages_organizations;
			if($diff >= 0)
			{
				$this->result->results_pages = $results_pages_people;
				$this->result->results_page = $results_page_people;
			} else {
				$this->result->results_pages = $results_pages_organizations;
				$this->result->results_page = $results_page_organizations;
			}
			
			return $this->result->returnAsArray();

		}
	}
}