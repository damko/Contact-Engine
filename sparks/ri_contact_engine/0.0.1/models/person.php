<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
// Created on Aug 29, 2011 by Damiano Venturin @ Squadra Informatica

class Person extends ObjectCommon
{	
	public function __construct()
	{
		parent::__construct();
		
		// Person configuration
		$this->load->config('person');
		$this->conf = $this->config->item('person');
		$this->baseDn = $this->conf['baseDn'];
		$this->objName = 'person';
		
		// Get the class Person properties reading them from the LDAP schema
		$this->loadAttrs($this->conf['objectClass']);
		
		log_message('debug', 'Person class has been loaded');
	}

	public function __destruct() {
		parent::__destruct();
	}
	
	// ================================= CRUD ================================
	
	private function set_uid()
	{
		//TODO I should allow the possibility to set a uid in the input
		$counter=0;
		$maxcounter=19; //TODO put this in the config
		
		//prepare the input for the search
		$input = array('attribute' => 'uid', 
						'uid' => rand(10000000,99999999));  //TODO this random should have the max set upon the number of the stored entries
		
		//let's try $maxcounter times to set a unique uid
		while (!$this->checkUniqueAttribute($input) and $counter < $maxcounter)
		{
			$counter++;
			$input['uid'] = rand(10000000,99999999);
		}
		
		if($counter==$maxcounter) return false;
		
		$this->uid = array($input['uid']);
		return true;
	}
		
	/**
	 * 
	 * Saves a new entry in the LDAP storage
	 * @param array $input
	 * @return array containing the entry uid on success, otherwise false
	 */
	public function create(array $input)
	{
		if(!$this->set_uid()) return false;
		$input['uid'] = $this->uid;
		
		if(!$this->bindLdapValuesWithClassProperties($input,true)) return false;
				
		//save the entry on the LDAP server
		$dn = 'uid='.$this->getUid().','.$this->baseDn;
		if(empty($this->objectClass)) $this->objectClass = $this->conf['objectClass'];
		
		//return $this->ri_ldap->CEcreate($dn,$this->toRest(false)) ? $this->getUid() : false;
		if($this->ri_ldap->CEcreate($dn,$this->toRest(false)))
		{
			return $this->getUid();
		} else {
			return false;
		}
	}
	
	/**
	 * 
	 * Performs a search in the LDAP tree
	 * @param array $input
	 * @return array containing all the entries found
	 */
	public function read(array $input)
	{			
		extract($input,$extract_type = EXTR_OVERWRITE);
		
		if(!empty($input['filter'])) 
		{
			$filter = $input['filter'];
		} else {
			if(!empty($input['uid'])) $filter = '(uid='.$input['uid'].')';
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
	
	/**
	 * 
	 * Performs the update of the given entry
	 * @param array $input
	 * @return array containing the entry uid on success, otherwise false
	 */
	public function update(array $input)
	{
		//FIXME This method is fragile atm. It requires more attention. For ex. what happens if I try to change the uid or the dn or the objectClass?
		
		if(empty($input['uid'])) return false;

		//TODO Should I perform a search over the given uid to be sure the contact exists?
		//unset($input['filter']);
		//$this->read($input);
		//$search = array('uid' => $input['uid']);
		
		if(!$this->bindLdapValuesWithClassProperties($input, false, true)) return false;
		
		$this->validate();
		
		//save the entry on the LDAP server
		$dn = 'uid='.$this->getUid().','.$this->baseDn;
		$entry = $this->toRest(false);
		unset($entry['uid']); //never mess with the id during an update cause it has to do with dn	

		//$return = $this->ri_ldap->CEupdate($dn,$entry);
		$return = $this->checkReturn($this->ri_ldap->CEupdate($dn,$entry));
		
		if($return === true) 
		{
			$uid = $this->getUid();
			if($uid) return $uid;
		} else {
			return $return;
		}

		//return $this->getUid();
	}

	/**
	 * 
	 * Deletes the given entry
	 * @param array $input
	 * @return boolean
	 */
	public function delete(array $input)
	{
		if(empty($input['uid'])) 
		{
			$data = array();
			$data['error'] = 'A valid uid is required to perform a delete'; 
			return $data;
		}
		$dn = 'uid='.$input['uid'].','.$this->baseDn;
		return $this->ri_ldap->CEdelete($dn);
	}
	
	// ===================== Other Methods ===============================
	
	private function getUid()
	{
		return !empty($this->uid['0']) ? $this->uid['0'] : FALSE;
	}
	
/* 	private function getReallyUpdatedValues($input)
	{
		$this->read($input);
		foreach ($this->properties as $property)
		{
			
		}
	} */	
	
	public function associate(array $input) {
		
		$data = array();
		
		if(empty($input['to'])) $data['error'] = 'Missing input "to". Possible values: organization, location';
		
		if(empty($input['uid'])) $data['error'] = 'Missing input "uid".';
		
		if($data['error']) return $data;
		
		//we need to get a precise person not a set of people
		unset($input['filter']);
		
		//let's get the get the person's data
		$data = $this->read($input);
		
		if($data['error']) return $data;
		
		//let's add the new location
		$to = $input['to'];
		switch ($to) {
			case location:
				
				if(empty($input['locId']) or is_array($input['locId']))
				{
					$data['error'] = 'Missing input "locId".';
					return $data;
				}
				
				if(!in_array($input['locId'], $this->locRDN)) 
				{
					//add location to the previous locations
					array_push($this->locRDN, $input['locId']);
					$data['locRDN']	= $this->locRDN;					
				} else {
					return true; //TODO maybe something more meaningful here
				}
				
			break;
			
			case organization:
				if(empty($input['oid']) or is_array($input['oid'])) return false;
				if(!in_array($input['oid'], $this->oRDN))
				{
					//add organization to the previous locations
					array_push($this->oRDN, $input['oid']);
					$data['oRDN']	= $this->oRDN;
						
				} else {
					return true; //TODO maybe something more meaningful here
				}
				
			break;		
				
			default:
				return false; //association not defined
			break;
		}
		
// 		$dn = 'uid='.$this->getUid().','.$this->baseDn;
// 		unset($entry['uid']); //never mess with the id during an update cause it has to do with dn		
// 		return $this->ri_ldap->CEupdate($dn,$entry) ? $this->getUid() : false;

		$data['uid'] = $this->uid;
		return $this->update($data);
		
	}
}

/* End of person.php */