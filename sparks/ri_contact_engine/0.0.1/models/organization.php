<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
// Created on Aug 29, 2011 by Damiano Venturin @ Squadra Informatica

class Organization extends ObjectCommon
{	
	public function __construct()
	{
		parent::__construct();
		
		// Organization configuration
		$this->load->config('organization');
		$this->conf = $this->config->item('organization');
		$this->baseDn = $this->conf['baseDn'];
		$this->obj = 'organization';
		
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
		if(!$this->set_oid()) return false;
		$input['oid'] = $this->oid;
		
		if(!$this->bindLdapValuesWithClassProperties($input,true)) return false;
		
		//save the entry on the LDAP server
		$dn = 'oid='.$this->oid.','.$this->baseDn;
		if(empty($this->objectClass)) $this->objectClass = $this->conf['objectClass'];
		return $this->ri_ldap->CEcreate($dn,$this->toRest(false)) ? $this->oid : false;
	}
	
	public function read(array $input)
	{	
		if(!empty($input['filter'])) 
		{
			$filter = $input['filter'];
		} else {
			if(!empty($input['oid'])) $filter = '(oid='.$input['oid'].')';
			if(!empty($input['dbId'])) $filter = '(dbId='.$input['dbId'].')';
		}
		
// 		$wanted_attributes = array();
// 		if(!empty($input['attributes']) and is_array($input['attributes'])) 
// 		{
// 			$wanted_attributes = $input['attributes'];
// 		} 
		
		//TODO why this switch?
		switch ($input['emptyfields']) {
			case true:
				$empty_fields = TRUE;
			break;
			
			case false:
				$empty_fields = FALSE;
			break;
						
			default:
				$empty_fields = TRUE;
			break;
		}		
		
		if(empty($filter)) return false;
		
		return parent::read($input); //, $filter, $wanted_attributes, $sort_by, $flow_order, $wanted_page, $items_page);
	}

	public function update(array $input)
	{
		//FIXME This method is fragile atm. It requires more attention. For ex. what happens if I try to change the oid or the dn or the objectClass?
		
		if(empty($input['oid'])) return false;

		//TODO Should I perform a search over the given oid to be sure the contact exists?
		//unset($input['filter']);
		//$this->read($input);
		
		if(!$this->bindLdapValuesWithClassProperties($input, false, true)) return false;
		
		//save the entry on the LDAP server
		$dn = 'oid='.$this->oid.','.$this->baseDn;
		$entry = $this->toRest(false);
		unset($entry['oid']); //never mess with the id during an update cause it has to do with dn
		return $this->ri_ldap->CEupdate($dn,$entry) ? $this->oid : false;
	}

	public function delete($input)
	{
		if(empty($input['oid'])) return false;
		$dn = 'oid='.$input['oid'].','.$this->baseDn;
		return $this->ri_ldap->CEdelete($dn);
	}
}

/* End of organization.php */