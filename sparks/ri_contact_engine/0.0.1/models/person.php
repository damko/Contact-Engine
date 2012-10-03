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
	 * Saves a new entry in the LDAP storage
	 * 
	 * @param array $input
	 * @return array containing the entry uid on success, otherwise false
	 */
	public function create(array $input)
	{
		extract($input);
		if(isset($ce_key)) $this->set_baseDn($ce_key);
		
		if(!$this->set_uid())
		{
			$this->result = new Ce_Return_Object();
			$this->result->data = array();
			$this->result->status_code = '500';
			$this->result->message = 'I can not set a unique dn for the new '.$this->objName.' entry.';
				
			return $this->result->returnAsArray();
		}
		
		$input['uid'] = $this->uid;
		
		if(!$this->bindDataWithClassProperties($input,true))  return $this->result->returnAsArray();
				
		//save the entry on the LDAP server
		if(empty($this->objectClass)) $this->objectClass = $this->conf['objectClass'];
		$dn = 'uid='.$this->getUid().','.$this->baseDn;
		
		$exit_status = $this->ri_ldap->CEcreate($this->toRest(false),$dn);
		
		$this->result->importLdapReturnObject($this->ri_ldap->result);

		if($exit_status) $this->result->pushData(array('uid' => $this->getUid()));
		
		return $this->result->returnAsArray();		
 		
	}
	
	/**
	 * 
	 * Performs a search in the LDAP tree
	 * @param array $input
	 * @return array containing all the entries found
	 */
	public function read(array $input)
	{			
		extract($input);
		if(isset($ce_key)) $this->set_baseDn($ce_key);
		
		if(!empty($input['filter'])) 
		{
			$filter = $input['filter'];
		} else {
			if(!empty($input['uid'])) $filter = '(uid='.$input['uid'].')';
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
	public function update(array $input = null)
	{
		//FIXME This method requires more attention. For ex. what happens if I try to change the uid or the dn or the objectClass?
		
		if(!is_null($input)) {
			
			extract($input);
			if(isset($ce_key)) $this->set_baseDn($ce_key);
			
			$return = $this->read($input);
			
			$original_values = $this->toRest(false);
			
			if(count($return['data']) == 0) return $this->result->returnAsArray();
			
			if(!$this->bindDataWithClassProperties($input, false, true)) return $this->result->returnAsArray();

		} 
		
		//if the person has not been found return 415
		if(!$this->getUid()) {
			$this->result = new Ce_Return_Object();
			$this->result->data = array();
			$this->result->status_code = '415';
			$this->result->message = 'The '.$this->objName.' entry can not be identified.';
			return $this->result->returnAsArray();			
		}
		
		//$this->validate();
		
		//save the entry on the LDAP server
		$dn = 'uid='.$this->getUid().','.$this->baseDn;
		
		$entry = $this->toRest(false);
		
		//if an attribute has been deleted then it's not contained in the $input. 
		//The only way to understand what's has been deleted is to compare the original entry value with the new ones
		$deleted_attributes = array_diff(array_keys($original_values), array_keys($entry));
		$required_attributes = $this->getRequiredProperties();
		foreach ($deleted_attributes as $key => $attribute) {
			
			if($attribute=='objectClass' || in_array($attribute,$required_attributes) || $attribute=='entryCreationDate'){
				//these are special values that we don't want to delete in any case
				continue;
			} else {
				$prop = $this->properties;	
				
				if( $this->properties[$attribute]['no-user-modification'] == 1) continue;
						
				if( $this->properties[$attribute]['boolean'] == 1) {
					$entry[$attribute] = 'FALSE';
					continue;
				}

				if( $this->properties[$attribute]['single-value'] == 1) {
					$entry[$attribute] = '';
					continue;
				} else {
					$entry[$attribute] = array();
					continue;
				}						
			}
		}
		
		unset($entry['uid']); //never mess with the id during an update cause it has to do with dn
		unset($entry['dn']);
		
		$exit_status = $this->ri_ldap->CEupdate($entry, $dn);
		
		$this->result->importLdapReturnObject($this->ri_ldap->result);

		if($exit_status) $this->result->pushData(array('uid' => $this->getUid()));
		
		return $this->result->returnAsArray();		
	}

	/**
	 * 
	 * Deletes the given entry
	 * @param array $input
	 * @return boolean
	 */
	public function delete($input)
	{
		extract($input);
		if(isset($ce_key)) $this->set_baseDn($ce_key);
		
		if(!is_array($input) || empty($input['uid']))
		{
			$this->result = new Ce_Return_Object();
			$this->result->data = array();
			$this->result->status_code = '415';
			$this->result->message = 'A valid uid is required to delete a '.$this->objName.' entry.';
		
			return $this->result->returnAsArray();
		}
				
		$dn = 'uid='.$input['uid'].','.$this->baseDn;
		 		
		return parent::delete($dn);
	}
	
	// ===================== Other Methods ===============================
	
	private function getUid()
	{
		return !empty($this->uid['0']) ? $this->uid['0'] : FALSE;
	}
	
	public function associate(array $input) {
		
		$errors = array();
		
		extract($input);
		if(isset($ce_key)) $this->set_baseDn($ce_key);		
		
		if(empty($input['to'])) $errors[] = 'Missing input "to". Possible values: organization, location';
		
		if(empty($input['uid'])) $errors[] = 'Missing input "uid"';
		
		if(count($errors) > 0) {
			$this->result = new Ce_Return_Object();
			$this->result->data = array();
			$this->result->status_code = '415';
			$this->result->message = implode(', ', $errors);
		
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
			
			case 'organization':
				if(empty($input['oid']) or is_array($input['oid']))	
					$errors[] = 'Missing input "locId".';
				
				if(isset($this->oRDN) && is_array($this->oRDN)) {
					if(!in_array($input['oid'], $this->oRDN))
					{
						array_push($this->oRDN, $input['oid']); //add organization to the previous organizations						
					}
				} else {
					$this->oRDN = array($input['oid']);
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
		
			return $this->result->returnAsArray();
		}				

		return $this->update();		
	}
}

/* End of person.php */