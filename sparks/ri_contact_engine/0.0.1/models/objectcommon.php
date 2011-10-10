<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
// Created on Sep 9, 2011 by Damiano Venturin @ squadrainformatica.com

class ObjectCommon extends CI_Model
{
	protected $properties;
	protected $baseDn;
	public $conf;
		
	public function __construct(){
		parent::__construct();
	}
	
	protected function loadAttrs($object_class) {
		$period = NULL;
		if(!empty($this->conf['refreshPeriod'])) $period = $this->conf['refreshPeriod'];
		$this->ce->loadClassAttributes($object_class, &$this, '_initialize', $period);
		log_message('debug', 'Contact class properties have been loaded');
	}	
	
	public function _initialize($attribute_name, array $values){
		$this->properties[$attribute_name] = $values;
	}

	
	public function __get($property_name) {
		//workaround to deal with the multivalue field for uid
		if($property_name == 'uid')
		{
			$CI =& get_instance();
			return $CI->$key['0'];
		}

		return parent::__get($property_name);
	}
	
	
	public function __set($property_name,$value) {
		if(!isset($this->properties[$property_name])) return false;
		$this->$property_name = $value;
	}
		
	public function getProperties()	{
		return $this->properties;
	}
	
	public function getRequiredProperties() {
		foreach ($this->properties as $property => $property_value) {
			if($property_value['required'] == 1)
			{
				$output[] = $property;
			}
		}
		return $output;
	}
	
	private function unsetProperties()
	{
		foreach ($this->properties as $property => $value) {
			unset($this->$property);
		}
	}
	protected function bindLdapValuesWithClassProperties($data,$strict = false, $avoid_clean = false) {
		
		//clean everything already stored
		if(!$avoid_clean) $this->unsetProperties();
		
		//start filling the object properties with the given data accordingly to the schema
		foreach($this->properties as $key => $key_value)
		{
			//For unknown reasons ldap_get_entries returns the keys in lowercase (PD), that's why I need to use the strtolower, but
			//$data can come as input from a POST or GET request so I have to consider both cases
			unset($value);
			if(isset($data[strtolower($key)][0])) $value = $data[strtolower($key)]; //from ldap_search
			if(isset($data[$key])) $value = $data[$key]; //from POST, GET
			
			if(isset($value))
			{
				if(is_array($value)) 
				{
					unset($value['count']);
				} else {
					if($key == 'uid') $value = array($value);
				}

				//TODO add lenght checks
				
				//schema says that a string is needed for the current attribute
				if($this->properties[$key]['single-value'] == '1')
				{
					is_array($value) ? $this->$key = $value[0] : $this->$key = (string) $value; //single value;
				} else {
					$this->$key = $value; //array;
				}
			} else {
				//if it's required by the schema 
				if($strict)
				{
					if($this->properties[$key]['required'] == 1) 
					{
						return false;
					}
				}
			}
		}
		return true;
	}
	
	protected function toRest($empty_fields = true)
	{
		foreach ($this->properties as $key => $value)
		{
			if($empty_fields)
			{
				$output[$key] = $this->$key;
			} else {
				if(!empty($this->$key)) $output[$key] = $this->$key;
			}
		}
		return $output;
	}
	
	public function checkUniqueAttribute(array $input)
	{
		if(empty($input['attribute'])) return false;
		$attribute = $input['attribute'];
		if(empty($input[$attribute])) return false;
		
		$search['filter'] = '('.$attribute.'='.$input[$attribute].')';
		$found = $this->read($search);
		return count($found)== 0 ? TRUE : FALSE;
	}
}

/* End of objectcommon.php */