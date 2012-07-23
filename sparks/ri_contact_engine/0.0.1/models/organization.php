<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
// Created on Aug 29, 2011 by Damiano Venturin @ Squadra Informatica

class Organization extends ObjectCommon
{	
	public function __construct(array $input = null)
	{
		parent::__construct();
		
		// Organization configuration
		$this->load->config('organization');
		$this->conf = $this->config->item('organization');
		//default baseDn value
		$this->baseDn = $this->conf['baseDn'];
		$this->objName = 'organization';
		
		// Get the class Organization properties reading them from the LDAP schema
		$this->loadAttrs($this->conf['objectClass']);
				
		log_message('debug', 'Organization class has been loaded');
	}

	public function __destruct() {
		parent::__destruct();
	}
	
	// ================================= CRUD ================================
	
	
	private function set_oid()
	{
		//FIXME this function should go in contact and take the parameter as input
		//TODO I should allow the possibility to set a oid in the input
		$counter=0;
		$maxcounter=19; //TODO put this in the config
		
		//prepare the input for the search
		$input = array('attribute' => 'oid', 
						'oid' => rand(10000000,99999999));  //TODO this random should have the max set upon the number of the stored entries
		
		//let's try $maxcounter times to set a unique oid
		while (!$this->checkUniqueAttribute($input) and $counter < $maxcounter)
		{
			$counter++;
			$input['oid'] = rand(10000000,99999999);
		}
		if($counter==$maxcounter) return false;
		$this->oid = $input['oid'];
		return true;
	}
		
	public function create(array $input)
	{		
		extract($input);
		if(isset($ce_key)) $this->set_baseDn($ce_key);		
		
		if(!$this->set_oid())
		{
			$this->result = new Ce_Return_Object();
			$this->result->data = array();
			$this->result->status_code = '500';
			$this->result->message = 'I can not set a unique dn for the new '.$this->objName.' entry.';
// 			$this->result->results_number = '0';
// 			$this->result->results_got_number = 0;
		
			return $this->result->returnAsArray();
		}
		
		$input['oid'] = $this->oid;
		
		if(!$this->bindDataWithClassProperties($input,true))  return $this->result->returnAsArray();
		
		//save the entry on the LDAP server
		$dn = 'oid='.$this->oid.','.$this->baseDn;
		if(empty($this->objectClass)) $this->objectClass = $this->conf['objectClass'];
		
		//$entry = $this->toRest(false); //TODO delme
		$exit_status = $this->ri_ldap->CEcreate($this->toRest(false),$dn);
		
		$this->result->importLdapReturnObject($this->ri_ldap->result);

		if($exit_status) $this->result->pushData(array('oid' => $this->oid));
		
		return $this->result->returnAsArray();	
	}
	
	public function read(array $input)
	{	
		extract($input);
		
		if(isset($ce_key)) $this->set_baseDn($ce_key);
		
		if(!empty($input['filter']))
		{
			$filter = $input['filter'];
		} else {
			if(!empty($input['oid'])) $filter = '(oid='.$input['oid'].')';
			if(!empty($input['dbId'])) $filter = '(dbId='.$input['dbId'].')'; //TODO maybe I can remove this
		}
		
		$output = array();
		if(isset($filter)) $output['filter'] = $filter;
		if(isset($wanted_attributes)) $output['wanted_attributes'] = $wanted_attributes;
		if(isset($sort_by)) $output['sort_by'] = $sort_by;
		if(isset($flow_order)) $output['flow_order'] = $flow_order;
		if(isset($wanted_page)) $output['wanted_page'] = $wanted_page;
		if(isset($items_page)) $output['items_page'] = $items_page;
		
		return parent::read($output);		
	}

	public function update(array $input = null)
	{
		//FIXME This method requires more attention. For ex. what happens if I try to change the uid or the dn or the objectClass?
				
		if(!is_null($input)) {
			
			extract($input);
		
			if(isset($ce_key)) $this->set_baseDn($ce_key);
			
			$return = $this->read($input);
				
			if(count($return['data']) == 0) return $this->result->returnAsArray();
				
			if(!$this->bindDataWithClassProperties($input, false, true)) return $this->result->returnAsArray();
		}
		
		if(empty($this->oid)) {
			$this->result = new Ce_Return_Object();
			$this->result->data = array();
			$this->result->status_code = '415';
			$this->result->message = 'The '.$this->objName.' entry can not be identified.';

			return $this->result->returnAsArray();
		}		
		
		//$this->validate();
		
		//save the entry on the LDAP server
		$dn = 'oid='.$this->oid.','.$this->baseDn;
		
		$entry = $this->toRest(false);
		
		//if an attribute has been deleted then it's not contained in the $input.
		//The only way to understand what's has been deleted is to compare the original entry value with the new ones
		$deleted_attributes = array_diff(array_keys($original_values), array_keys($entry));
		$required_attributes = $this->getRequiredProperties();
		foreach ($deleted_attributes as $key => $attribute) {
			if($attribute=='objectClass' || in_array($attribute,$required_attributes) || $attribute=='entryCreationDate'){
				continue;
			} else {
				if(is_array($original_values[$attribute])) {
					$entry[$attribute] = array();
				} else {
					$entry[$attribute] = '';
				}
			}
		}			
		
		unset($entry['oid']); //never mess with the id during an update cause it has to do with dn
		unset($entry['dn']);
		
		$exit_status = $this->ri_ldap->CEupdate($entry, $dn);
		
		$this->result->importLdapReturnObject($this->ri_ldap->result);

		if($exit_status) $this->result->pushData(array('oid' => $this->oid));
		
		return $this->result->returnAsArray();
	}

	public function delete($input)
	{
		if(!is_array($input) || empty($input['oid']))
		{
			
			$this->result = new Ce_Return_Object();
			$this->result->data = array();
			$this->result->status_code = '415';
			$this->result->message = 'A valid oid is required to delete a '.$this->objName.' entry.';
// 			$this->result->results_number = '0';
// 			$this->result->results_got_number = 0;
		
			return $this->result->returnAsArray();
		}

		extract($input);
		if(isset($ce_key)) $this->set_baseDn($ce_key);
		
		$dn = 'oid='.$input['oid'].','.$this->baseDn;
		 		
		return parent::delete($dn);
	}
		
	public function associate(array $input) {
		
		$errors = array();
	
		extract($input);
		if(isset($ce_key)) $this->set_baseDn($ce_key);
		
		if(empty($input['to'])) $errors[] = 'Missing input "to". Possible values: organization, location';
	
		if(empty($input['oid'])) $errors[] = 'Missing input "oid"';
	
		if(count($errors) > 0) {
			$this->result = new Ce_Return_Object();
			$this->result->data = array();
			$this->result->status_code = '415';
			$this->result->message = implode(', ', $errors);
// 			$this->result->results_number = '0';
// 			$this->result->results_got_number = 0;
	
			return $this->result->returnAsArray();
		}
	
		unset($input['filter']); //we need to get a precise person not a set of people
	
		$return = $this->read($input);
		if(count($return['data']) == 0) return $this->result->returnAsArray();
	
		//let's add the new location
		$to = $input['to'];
		switch ($to) {
			case 'location':
				if(empty($input['locId']) or is_array($input['locId']))
					$errors[] = 'Missing input "locId".';
	
				if(isset($this->locRDN) && is_array($this->locRDN)) {
					if(!in_array($input['locId'], $this->locRDN))
					{
						array_push($this->locRDN, $input['locId']);  //add location to the previous locations
					}
				} else {
					$this->locRDN = array($input['locId']);
				}
	
			break;
		
	
			default:
				$errors[] = 'Unknown association-type for parameter "to". Possible values: organization, location';
			break;
		}
	
		if(count($errors) > 0) {
			$this->result = new Ce_Return_Object();
			$this->result->data = array();
			$this->result->status_code = '415';
			$this->result->message = implode(', ', $errors);
// 			$this->result->results_number = '0';
// 			$this->result->results_got_number = 0;
	
			return $this->result->returnAsArray();
		}
	
		return $this->update();		

	}	
}

/* End of organization.php */