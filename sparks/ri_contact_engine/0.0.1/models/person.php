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
			if(!empty($input['uid'])) {
				
				if(is_array($input['uid'])) {
					$uid = array_shift($input['uid']);
					$input['uid'] = $uid; //restores the array item as a scalar
				} else {
					$uid = $input['uid'];
				}
				
				$filter = '(uid='.$uid.')';
			}
		}
		
		$output = array();
		if(isset($filter)) $output['filter'] = $filter; 
		if(isset($wanted_attributes)) $output['wanted_attributes'] = $wanted_attributes;
		
		if(isset($sort_by)) {
			
			$person_attributes = array_keys($this->properties);
			
			if(is_array($sort_by)) {	
				foreach ($sort_by as $key => $parameter) {
					if(!in_array($parameter, $person_attributes)) unset($sort_by[$key]);
				}
				
			} else {
				if(in_array($sort_by, $person_attributes)){
					$sort_by = array($sort_by);
				} else {
					$sort_by = array();
				}
			}
		}
		if(!isset($sort_by) || count($sort_by) == 0) $sort_by = array('sn'); //default
		
		if(isset($sort_by)) $output['sort_by'] = $sort_by;
		if(isset($flow_order)) $output['flow_order'] = $flow_order; 
		if(isset($wanted_page)) $output['wanted_page'] = $wanted_page; 
		if(isset($items_page)) $output['items_page'] = $items_page;
		
		return parent::read($output);
	}
	
	/**
	 * Updates the entry to what specified in the $input array: basically the $input array represents the whole entry.
	 * All the attributes not specified in the $input array will be erased unless they are mandatory
	 *
	 * @access		public
	 * @param		array $input
	 * @return		array
	 */	
	public function update(array $input = null)
	{	
		if(count($input) == 0 || !isset($input['uid'])) {
			$this->result = new Ce_Return_Object();
			$this->result->data = array();
			$this->result->status_code = '415';
			$this->result->message = 'A valid array is required to update a '.$this->objName.' entry.';
				
			return $this->result->returnAsArray();
		}
		
		$exit_status = parent::update($input);
		return $this->result->returnAsArray();
	}
	

	/**
	 * 
	 * Deletes the given entry
	 * @param array $input
	 * @return		array
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
	
	/**
	 * Authenticates the given user
	 * Required attributes in the input array: userPassword and (uid or mail). 
	 * If uid and mail are both in $input then only uid will be considered.
	 *  
	 * @param array $input
	 * @return boolean
	 */
	public function authenticate($input)
	{
		if(isset($input['ce_key'])) $this->set_baseDn($input['ce_key']);
		
		if(!is_array($input))
		{			
			return $this->invalidCredentials('Invalid input. Please set uid or mail and userPassword');
		}
		
		if(empty($input['uid']) && empty($input['mail']))
		{
			return $this->invalidCredentials('Your input should contain one of these attributes: uid or mail');
		}

		if(empty($input['userPassword']))
		{
			return $this->invalidCredentials('Your input should contain the userPassword attribute');
		}
		
		//be sure that input is well formed and doesn't contain unnecessary fields
		$tmp = array();
		if(isset($input['uid'])) $tmp['uid'] = trim($input['uid']);
		if(isset($input['mail'])) $tmp['mail'] = trim($input['mail']);
		$tmp['userPassword'] = trim($input['userPassword']);
		$input = $tmp;
		unset($tmp);
		
		if(!empty($input['mail'])) {
			//look for a person using the mail attribute
			$search = array();
			$search['filter'] = '(mail='.strtolower($input['mail']).')';
		}
		
		if(!empty($input['uid'])) {
			//look for a person using the mail attribute
			$search = array();
			$search['filter'] = '(uid='.strtolower($input['uid']).')';
		}
		
		$result = $this->read($search);
			
		if(count($result['data']) > 1) {
			$this->result = new Ce_Return_Object();
			$this->result->data = array();
			$this->result->status_code = '500';
			$this->result->message = 'Internal Server Error';
				
			return $this->result->returnAsArray();
		}
			
		if(count($result['data']) == 0) return $this->invalidCredentials();
				
		$person = $result['data']['0'];
		$stored_password = $person['userPassword'][0];
		
		$authenticated = false;
		
		//case 1: password is stored in LDAP not encrypted
		$given_password = $input['userPassword'];
		if($given_password == $stored_password) $authenticated = true;

		//case 2: password is stored in LDAP with MD5 encryption		
		$given_password = '{MD5}'.base64_encode(pack("H*",md5($input['userPassword'])));
		if($given_password == $stored_password) $authenticated = true; 

		//case 3: password is stored in LDAP with SHA encryption		
		$given_password = '{SHA}'.base64_encode(pack("H*",sha1($input['userPassword'])));
		if($given_password == $stored_password) $authenticated = true;

		//case 3: password is stored in LDAP with CRYPT encryption
		//TODO http://php.net/manual/en/function.crypt.php
				
		if(!$authenticated) $this->invalidCredentials();
		
		$result = $this->result->returnAsArray();
		
		//TODO should I give back all contact's attributes or only part of them?
		return $result;		
	}
	
	
	private function invalidCredentials($message = null){
		
		$this->result = new Ce_Return_Object();
		$this->result->data = array();
		$this->result->status_code = '415';
		if(is_null($message)) {
			$this->result->message = 'Invalid credentials';
		} else {
			$this->result->message = $message;
		}
		
		return $this->result->returnAsArray();
	}
	
	// ===================== Other Methods ===============================
	
	protected function getUid()
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