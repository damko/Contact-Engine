<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$helper = FCPATH.'sparks/ri_contact_engine/0.0.1/helpers/ce_helper'.EXT;
require_once $helper; //TODO shouldn't be enough the line in the autoload file? Looks like there is a bug in RI

/**
 * This is the parent object for the obj Person, Organization and Location. Contains all the common methods between the 3 objs
 * 
 * @author 		Damiano Venturin
 * @copyright 	2V S.r.l.
 * @license		GPL
 * @link		http://www.squadrainformatica.com/en/development
 * @since		Sep 9, 2011
 * 
 * @todo		
 */ 
class ObjectCommon extends CI_Model
{
	protected $objName;
	protected $properties;
	protected $baseDn;
	public $conf;
		
	public function __construct() {
		parent::__construct();
	}
	
	public function __destruct() {
		parent::__destruct();
	}
		
	protected function loadAttrs($object_class) {
		$period = NULL;
		if(!empty($this->conf['refreshPeriod'])) $period = $this->conf['refreshPeriod'];
		$this->ce->loadClassAttributes($object_class, $this, '_initialize', $period);
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
	
	public function read(array $input) { 
		//input fields: $filter, array $wanted_attributes, $sort_by = null,  $flow_order = null, $wanted_page = null, $items_page = null) {
		
		$pagination_settings = pagination_setup($input);
		
		if(is_array($pagination_settings)) extract($pagination_settings);	

		if(!empty($input['wanted_attributes']) and is_array($input['wanted_attributes'])) 
		{
			$wanted_attributes = $input['wanted_attributes'];
		} else {
			$wanted_attributes = array();
		}
		
		if(empty($input['filter']) || is_array($input['filter'])) 
		{
			//TODO this should be a RI function handling the error
			$return = array();
			$return['error'] = 'Method "'.__FUNCTION__.'" requires a filter in input';
			return $return;
		} else {
			$filter = $input['filter'];
		}
		
		//perform the search
		$ldap_result = $this->ri_ldap->CEsearch($this->baseDn, $filter, $wanted_attributes, 0, null, $sort_by,  $flow_order, $wanted_page, $items_page);
		
		//saving and removing info about the ldap query
		if(!empty($ldap_result['RestStatus']))
		{
			$rest_status = $ldap_result['RestStatus'];
			unset($ldap_result['RestStatus']);
		} 
		
		//removing the count item
		unset($ldap_result['count']);
		
		$output = array();
		if(!isset($strict)) $strict = false;
		foreach ($ldap_result as $ldap_item) {
			
			//TODO probably it would be wiser to return the whole result without parsing everysingle entry. Don't know yet
			$this->bindLdapValuesWithClassProperties($ldap_item,$strict);
			
			$output[] = $this->toRest($empty_fields);
		}
		
		//adding saved info about the ldap query
		if(isset($rest_status)) $output['RestStatus'] = $rest_status;
			
		return $output;		
	}	
	
	private function unsetProperties() {
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
						return false;   //TODO add something meaninful in the RestStatus
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
	
	protected function validate()
	{
		/*
		 * SYNTAXES FOR INETORG PERSON
		1.3.6.1.4.1.1466.115.121.1.3 - Attribute Type Description
		1.3.6.1.4.1.1466.115.121.1.5 - Binary syntax
		1.3.6.1.4.1.1466.115.121.1.6 - Bit string syntax
		1.3.6.1.4.1.1466.115.121.1.7 - Boolean syntax
		1.3.6.1.4.1.1466.115.121.1.8 - Certificate syntax
		1.3.6.1.4.1.1466.115.121.1.9 - Certificate List syntax
		1.3.6.1.4.1.1466.115.121.1.10 - Certificate Pair syntax
		1.3.6.1.4.1.1466.115.121.1.11 - Country String syntax
		1.3.6.1.4.1.1466.115.121.1.12 - Distinguished Name syntax
		1.3.6.1.4.1.1466.115.121.1.14 - Delivery Method
		1.3.6.1.4.1.1466.115.121.1.15 - Directory String syntax
		1.3.6.1.4.1.1466.115.121.1.16 - DIT Content Rule syntax
		1.3.6.1.4.1.1466.115.121.1.17 - DIT Structure Rule Description syntax
		1.3.6.1.4.1.1466.115.121.1.21 - Enhanced Guide
		1.3.6.1.4.1.1466.115.121.1.22 - Facsimile Telephone Number syntax
		1.3.6.1.4.1.1466.115.121.1.23 - Fax image syntax
		1.3.6.1.4.1.1466.115.121.1.24 - Generalized Time syntax
		1.3.6.1.4.1.1466.115.121.1.26 - IA5 String syntax
		1.3.6.1.4.1.1466.115.121.1.27 - Integer syntax
		1.3.6.1.4.1.1466.115.121.1.28 - JPeg Image syntax
		1.3.6.1.4.1.1466.115.121.1.30 - Matching Rule Description syntax
		1.3.6.1.4.1.1466.115.121.1.31 - Matching Rule Use Description syntax
		1.3.6.1.4.1.1466.115.121.1.33 - MHS OR Address syntax
		1.3.6.1.4.1.1466.115.121.1.34 - Name and Optional UID syntax
		1.3.6.1.4.1.1466.115.121.1.35 - Name Form syntax
		1.3.6.1.4.1.1466.115.121.1.36 - Numeric String syntax
		1.3.6.1.4.1.1466.115.121.1.37 - Object Class Description syntax
		1.3.6.1.4.1.1466.115.121.1.38 - OID syntax
		1.3.6.1.4.1.1466.115.121.1.39 - Other Mailbox syntax
		1.3.6.1.4.1.1466.115.121.1.40 - Octet String
		1.3.6.1.4.1.1466.115.121.1.41 - Postal Address syntax
		1.3.6.1.4.1.1466.115.121.1.43 - Presentation Address syntax
		1.3.6.1.4.1.1466.115.121.1.44 - Printable string syntax
		1.3.6.1.4.1.1466.115.121.1.49 - Supported Algorithm
		1.3.6.1.4.1.1466.115.121.1.50 - Telephone number syntax
		1.3.6.1.4.1.1466.115.121.1.51 - Teletex Terminal Identifier
		1.3.6.1.4.1.1466.115.121.1.52 - Telex Number
		1.3.6.1.4.1.1466.115.121.1.53 - UTCTime syntax
		1.3.6.1.4.1.1466.115.121.1.54 - LDAP Syntax Description syntax
		*/
		
/* 		foreach ($this->properties as $property => $features) {
			if($features['single'])
			{
				if(is_array($this->$property))
				{
					//do something
					$a = 'a';
				}
			}
		} */
		
		return;
	}
	
	protected function checkReturn($return){
		if(is_object($return))
		{
			//it's an exception for sure
			$data = array();
			$data['error'] = $return->getMessage();
			return $data;
		}
		if(is_bool($return) and $return === true) return true;
	}

}

/* End of objectcommon.php */