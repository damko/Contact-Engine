<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
// Created on Aug 29, 2011 by Damiano Venturin @ Squadra Informatica

class Location extends ObjectCommon
{	
	public function __construct()
	{
		parent::__construct();
		
		// Location configuration
		$this->load->config('location');
		$this->conf = $this->config->item('location');
		$this->baseDn = $this->conf['baseDn'];

		// Get the class Location properties reading them from the LDAP schema
		$this->loadAttrs($this->conf['objectClass']);
				
		log_message('debug', 'Location class has been loaded');
	}

	public function __destruct() {
		parent::__destruct();
	}

	
	// ================================= CRUD ================================
	
	private function set_locId()
	{
		//FIXME this function should go in contact and take the parameter as input
		//TODO I should allow the possibility to set a locId in the input
		$counter=0;
		$maxcounter=19; //TODO put this in the config
		
		//prepare the input for the search
		$input = array('attribute' => 'locId', 
						'locId' => rand(10000000,99999999));  //TODO this random should have the max set upon the number of the stored entries
		
		//let's try $maxcounter times to set a unique locId
		while (!$this->checkUniqueAttribute($input) and $counter < $maxcounter)
		{
			$counter++;
			$input['locId'] = rand(10000000,99999999);
		}
		if($counter==$maxcounter) return false;
		$this->locId = $input['locId'];
		return true;
	}
		
	private function getLocId()
	{
		return !empty($this->locId['0']) ? $this->locId['0'] : FALSE;
	}
		
	public function create(array $input)
	{
		if(!$this->set_locId()) return false;
		$input['locId'] = $this->locId;
		
		if(!$this->bindLdapValuesWithClassProperties($input,true)) return false;
		
		//save the entry on the LDAP server
		$dn = 'locId='.$this->locId.','.$this->baseDn;
		if(empty($this->objectClass)) $this->objectClass = $this->conf['objectClass'];
		return $this->ri_ldap->CEcreate($dn,$this->toRest(false)) ? $this->locId : false;
	}
	
	public function read(array $input)
	{	
		if(!empty($input['filter'])) 
		{
			$filter = $input['filter'];
		} else {
			if(!empty($input['locId'])) $filter = '(locId='.$input['locId'].')';
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
		
		//if(empty($filter)) return false;
		
		return parent::read($input); //, $filter, $wanted_attributes, $sort_by, $flow_order, $wanted_page, $items_page);		
	}

	public function update(array $input)
	{
		//FIXME This method is fragile atm. It requires more attention. For ex. what happens if I try to change the locId or the dn or the objectClass?
		
		if(empty($input['locId'])) return false;

		//TODO Should I perform a search over the given locId to be sure the contact exists?
		//unset($input['filter']);
		//$this->read($input);
		
		if(!$this->bindLdapValuesWithClassProperties($input, false, true)) return false;
		
		if(empty($this->locLatitude) or empty($this->locLongitude)) $this->getLatitudeLongitude();
		
		//save the entry on the LDAP server
		$dn = 'locId='.$this->getLocId().','.$this->baseDn;
		$entry = $this->toRest(false);
		unset($entry['locId']); //never mess with the id during an update cause it has to do with dn
		return $this->ri_ldap->CEupdate($dn,$entry) ? $this->locId : false;
	}

	public function delete($input)
	{
		if(empty($input['locId'])) return false;
		$dn = 'locId='.$input['locId'].','.$this->baseDn;
		return $this->ri_ldap->CEdelete($dn);
	}
	
	private function getLatitudeLongitude()
	{	
		//a bit of validation
		if(empty($this->locStreet)) return false;
		if(empty($this->locCity)) return false;
		
		//that's the yahoo placefinder application ID
		$appId='dj0yJmk9eUVJNWFjNFhxRll3JmQ9WVdrOVRuQkdaa1J5TjJrbWNHbzlOalF6TmpFNE1UWXkmcz1jb25zdW1lcnNlY3JldCZ4PTY5';
				
		//compose the address for the request
		$address='&street='.addslashes($this->locStreet);
		if(!empty($this->locZip)) $address.='&postal='.addslashes($this->locZip);
		if(!empty($this->locCity)) $address.='&city='.addslashes($this->locCity);
		if(!empty($this->locState)) $address.='&state='.addslashes($this->locState);
		if(!empty($this->locCountry)) $address.='&country='.addslashes($this->locCountry);
		$address=str_replace(' ','+',$address);
		$search='http://where.yahooapis.com/geocode?location='.$address.'&appid='.$appId;
	
		//makes the request to yahoo
		$xml=simplexml_load_file($search,"SimpleXMLElement",LIBXML_NOCDATA);
		if ($xml->Result->quality > 60 && $xml->Error==0)
		{
			$this->locLatitude = (string) $xml->Result->latitude;
			$this->locLongitude = (string) $xml->Result->longitude;
		}
	}	
}

/* End of location.php */