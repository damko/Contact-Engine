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
		$this->objName = 'location';
		

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
		
// 	private function getLocId()
// 	{
// 		return !empty($this->locId['0']) ? $this->locId['0'] : FALSE;
// 	}
		
	public function create(array $input)
	{
		extract($input);
		if(isset($ce_key)) $this->set_baseDn($ce_key);
		
		if(!$this->set_locId())
		{
			$this->result = new Ce_Return_Object();
			$this->result->data = array();
			$this->result->status_code = '500';
			$this->result->message = 'I can not set a unique dn for the new '.$this->objName.' entry.';
		
			return $this->result->returnAsArray();
		}
		
		$input['locId'] = $this->locId;
		
		if(!$this->bindDataWithClassProperties($input,true))  return $this->result->returnAsArray();
		
		//save the entry on the LDAP server
		$dn = 'locId='.$this->locId.','.$this->baseDn;
		if(empty($this->objectClass)) $this->objectClass = $this->conf['objectClass'];
		
		//$entry = $this->toRest(false); //TODO delme
		$exit_status = $this->ri_ldap->CEcreate($this->toRest(false),$dn);
		
		$this->result->importLdapReturnObject($this->ri_ldap->result);

		if($exit_status) 
		{
			$this->result->pushData(array('locId' => $this->locId));
			$return = $this->result->returnAsArray();
			
			//get geolocation
			if($this->getLatitudeLongitude())
			{
				$entry = array();
				$entry['locLatitude'] = $this->locLatitude;
				$entry['locLongitude'] = $this->locLongitude;
			
				$this->ri_ldap->CEupdate($entry, $dn);
			}
				
		}
		
		return $return;
	}
	
	public function read(array $input)
	{			
		extract($input);
		if(isset($ce_key)) $this->set_baseDn($ce_key);
		
		if(!empty($input['filter']))
		{
			$filter = $input['filter'];
		} else {
			if(!empty($input['locId'])) $filter = '(locId='.$input['locId'].')';
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

	public function update(array $input)
	{
		//FIXME This method requires more attention. For ex. what happens if I try to change the locId or the dn or the objectClass?

		extract($input);
		if(isset($ce_key)) $this->set_baseDn($ce_key);
		
		$return = $this->read($input);
		if(count($return['data']) == 0) return $this->result->returnAsArray();
		
		if(!$this->bindDataWithClassProperties($input, false, true)) return $this->result->returnAsArray();
		
		//$this->validate();
		
		//save the entry on the LDAP server
		$dn = 'locId='.$this->locId.','.$this->baseDn;
		
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
		
		unset($entry['locId']); //never mess with the id during an update cause it has to do with dn
		unset($entry['dn']);
		
		$exit_status = $this->ri_ldap->CEupdate($entry, $dn);
		
		$this->result->importLdapReturnObject($this->ri_ldap->result);

		if($exit_status) 
		{	
			$this->result->pushData(array('locId' => $this->locId));
			$return = $this->result->returnAsArray();
			
			//get geolocation
			if($this->getLatitudeLongitude())
			{
				$entry = array();
				$entry['locLatitude'] = $this->locLatitude;
				$entry['locLongitude'] = $this->locLongitude;
				
				$this->ri_ldap->CEupdate($entry, $dn);
			}
		}
		
		return $return;
		
	}

	public function delete($input)
	{
		
		extract($input);
		if(isset($ce_key)) $this->set_baseDn($ce_key);
		
		if(!is_array($input) || empty($input['locId']))
		{
			$this->result = new Ce_Return_Object();
			$this->result->data = array();
			$this->result->status_code = '415';
			$this->result->message = 'A valid locId is required to delete a '.$this->objName.' entry.';
	
			return $this->result->returnAsArray();
		}
	
		$dn = 'locId='.$input['locId'].','.$this->baseDn;
	
		return parent::delete($dn);
	}
		
	private function getLatitudeLongitude()
	{	
		//a bit of validation
		if(empty($this->locStreet)) return false;
		if(empty($this->locCity)) return false;
		if(empty($this->locState)) return false;
		if(empty($this->locCountry)) return false;
		
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
		if ($xml->Result->quality >= 60 && $xml->Error==0)
		{
			$this->locLatitude = (string) $xml->Result->latitude;
			$this->locLongitude = (string) $xml->Result->longitude;
			return true;
		}
		
		return false;
	}	
}

/* End of location.php */