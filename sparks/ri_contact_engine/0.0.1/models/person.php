<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
// Created on Aug 29, 2011 by Damiano Venturin @ Squadra Informatica

class Person extends ObjectCommon
{	
	public function __construct()
	{
		parent::__construct();
		
		// Person configuration
		//$this->load->config('person');
		$this->conf = $this->config->item('person');
		$this->baseDn = $this->conf['baseDn'];
		$this->obj = 'person';

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
		$return = array();
		
		//extract(&$input,$extract_type = EXTR_OVERWRITE); //FIXME why this doesn't work at all?
		
		if(!empty($input['filter'])) 
		{
			$filter = $input['filter'];
		} else {
			if(!empty($input['uid'])) $filter = '(uid='.$input['uid'].')';
			if(!empty($input['dbId'])) $filter = '(dbId='.$input['dbId'].')';
		}
				
		return parent::read($input); //, $filter, $wanted_attributes, $sort_by, $flow_order, $wanted_page, $items_page);
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
		
		if(!$this->bindLdapValuesWithClassProperties($input, false, true)) return false;
		
		//save the entry on the LDAP server
		$dn = 'uid='.$this->getUid().','.$this->baseDn;
		$entry = $this->toRest(false);
		unset($entry['uid']); //never mess with the id during an update cause it has to do with dn		
		return $this->ri_ldap->CEupdate($dn,$entry) ? $this->getUid() : false;
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
	
	public function associate(array $input) {
		if(empty($input['to'])) 
		{
			$data = array();
			$data['error'] = 'Missing input "to". Possible values: organization, location';
		}
		
		if(empty($input['uid'])) 
		{
			$data = array();
			$data['error'] = 'Missing input "uid".';
		}
		
		//we need to get a precise person not a set of people
		unset($input['filter']);
		
		//let's get the get the person's data
		$this->read($input);
		
		if(empty($this->cn)) return false; //person not found
		
		//let's add the new location
		$to = $input['to'];
		switch ($to) {
			case location:
				if(empty($input['locId']) or is_array($input['locId'])) return false;
				if(!in_array($input['locId'], $this->locRDN)) 
				{
					$entry= array('locRDN' => $input['locId']);
				} else {
					return true; //TODO maybe something more meaningful here
				}
			break;
			
			case organization:
				if(empty($input['oid']) or is_array($input['oid'])) return false;
				if(!in_array($input['oid'], $this->oRDN))
				{
					$entry= array('oRDN' => $input['oid']);
				} else {
					return true; //TODO maybe something more meaningful here
				}
				
			break;		
				
			default:
				return false; //association not defined
			break;
		}
		
		$dn = 'uid='.$this->getUid().','.$this->baseDn;
		unset($entry['uid']); //never mess with the id during an update cause it has to do with dn		
		return $this->ri_ldap->CEupdate($dn,$entry) ? $this->getUid() : false;
	}
		
	// ===================== EXAMPLES ===============================
	
	/**
	 * 
	 * A method meant to be used by the developer to try how it works
	 * @param array $input
	 */
	public function exampleGetInfo(array $input)
	{
		if(empty($input['id'])) return FALSE;

		$persons = array();
		$persons[] = array(
				'first_name' => 'John',
				'last_name' => 'Doe',
				'member_id' => '123435');

		$persons[] = array(
				'first_name' => 'Robert',
				'last_name' => 'Doe',
				'member_id' => '123435');

		if(isset($persons[$input['id']])) return $persons[$input['id']];

		return 'person not found';
	}	
	
	/**
	 * 
	 * A method meant to be used by the developer to try how it works
	 */
	public function exampleSearchPersons() {
		$persons[] = array(
				'first_name' => 'John',
				'last_name' => 'Doe',
				'member_id' => '123435');

		$persons[] = array(
				'first_name' => 'Robert',
				'last_name' => 'Doe',
				'member_id' => '123435');
		return $persons;
	}	
}

/* End of person.php */