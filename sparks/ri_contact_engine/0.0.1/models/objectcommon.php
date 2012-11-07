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
	protected $result;
		
	public function __construct() {
		parent::__construct();
		$this->result = new Ce_Return_Object();
	}
	
	public function __destruct() {
		
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
		
	public function getProperties(array $input = null)	{
		if(isset($input[$this->objName])) {
			//it's an API request
			$this->result = new Ce_Return_Object();
			$this->result->data = $this->properties;
			$this->result->status_code = '200';
			$this->result->message = 'OK';
			$this->result->results_number = count($this->properties);
			$this->result->results_got_number = $this->result->results_number;
			
			return $this->result->returnAsArray();			
		} else {
			return $this->properties;
		}
		
	}
	
	/**
	 * This method looks in the contactengine mysql database to get the BaseDn for the given key and object.
	 * If a record is found the baseDn set in the database record will be used instead of the one 
	 * set in the config file 
	 * 
	 * @access		public
	 * @param		$client_key	String
	 * @return		boolean
	 * 
	 * @author 		Damiano Venturin
	 * @copyright 	2V S.r.l.
	 * @license	GPL
	 * @since		Jul 22, 2012
	 */ 
	protected function set_baseDn($client_key) {
		
		if(empty($client_key)) return false;
		
		$query = $this->db->query('select * from ce_keys where id="' . $client_key .'"');
	
		if($query->num_rows() == 1) {
			$rows = $query->result();
			$record = $rows[0];
			$baseDn = strtolower($this->objName) . '_basedn';
			$this->baseDn = $record->$baseDn;
			return true;
		}
		
		return false;
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
		
		extract($input);
		
		$pagination_settings = pagination_setup($input);
		
		if(is_array($pagination_settings)) extract($pagination_settings);	

		if(empty($wanted_attributes) || !is_array($wanted_attributes)) 
		{
			$wanted_attributes = array();
		}
		
		if(!isset($filter)) $filter = ''; //this will thrown an error in RI_LDAP
		
		//perform the search
		$search_exit_status = $this->ri_ldap->CEsearch($this->baseDn, $filter, $wanted_attributes, 0, null, $sort_by,  $flow_order, $wanted_page, $items_page);

		$this->result->importLdapReturnObject($this->ri_ldap->result);
		
 		if($search_exit_status)
		{
			$ldap_result = $this->ri_ldap->result->data->content;
			
			$output = array();
			if(!isset($strict)) $strict = false;
			if(is_array($ldap_result))
			{
				foreach ($ldap_result as $ldap_item) {
					//removing the count item
					unset($ldap_item['count']);
					
					if(!$this->bindDataWithClassProperties($ldap_item,$strict, false, false)) return $this->result->returnAsArray();
					
					$output[] = $this->toRest($empty_fields);
				}
			}

			$this->result->data = $output;
		}

		$output = $this->result->returnAsArray();
		return $output;		 
	}	
	
	/**
	 * Returns the total number of entries found with the specified filter
	 *
	 * @access		public
	 * @param		array $input
	 * @return		array $return
	 * @author 		Damiano Venturin
	 * @since		Nov 2, 2012
	 */
	public function count(array $input)
	{
		$result = $this->read($input);
		$return = $result;
		if($result['status']['status_code'] == 200){
			$return['data'] = array('total_entries' => count($result['data']));
		}
		return $return;
	}	
	
	public function delete($dn)
	{			
		$this->ri_ldap->CEdelete($dn);
	
		$this->result->importLdapReturnObject($this->ri_ldap->result);
	
		return $this->result->returnAsArray();
	}
		
	private function unsetProperties() {
		foreach ($this->properties as $property => $value) {
			unset($this->$property);
		}
	}
	
	protected function bindDataWithClassProperties($data,$strict = false, $avoid_clean = false, $validate = true) {
		
		$not_processed = $data;
		
		//clean everything already stored
		if(!$avoid_clean) $this->unsetProperties();
		
		//start filling the object properties with the given data accordingly to the schema
		foreach($this->properties as $key => $key_value)
		{
			//For unknown reasons ldap_get_entries returns the keys in lowercase (PD), that's why I need to use the strtolower, but
			//$data can come as input from a POST or GET request so I have to consider both cases
			unset($value);
			if(isset($data[strtolower($key)][0])) $value = $data[strtolower($key)]; //from ldap_search
			if(isset($data[$key])) $value = $data[$key]; //from POST, GET, whatever
			
			if(isset($value))
			{
				unset($not_processed[$key]);
				
				if(is_array($value)) 
				{
					unset($value['count']);
				} else {
					if($key == 'uid') $value = array($value);
				}

				//TODO add lenght checks and validation stuff
				
				//schema says that a string is needed for the current attribute
				if($this->properties[$key]['single-value'] == '1')
				{
					is_array($value) ? $this->$key = $value[0] : $this->$key = (string) $value; //single value;
				} else {
					$this->$key = $value; //array;
				}
			} else {
				 
				if($strict) //check that all attributs required by the schema [MUST] are filled
				{
					if($this->properties[$key]['required'] == 1) 
					{
						$this->result = new Ce_Return_Object();
						$this->result->data = array();
						$this->result->status_code = '415';
						$this->result->message = 'The attribute '.$key.' is mandatory for the object '.$this->objName;

						return false;
					} else {
						//removes empty attributes so that they can be deleted
						unset($this->$key);
					}
				} else {	
				
					//removes empty attributes so that they can be deleted
					unset($this->$key);
				}	
			}
		}
		
		unset($not_processed[$this->objName]); //the key "$this->objectName is set by the rest client to pass the method to call
		unset($not_processed['ce_key']);   //the key "ce_key" eventually contains the "contact engine key" to reset the baseDn via API
		
		//let's look which data weren't processed
		if($validate && count($not_processed) > 0)
		{
			$this->result = new Ce_Return_Object();
			$this->result->data = array();
			$this->result->status_code = '415';
			$this->result->message = 'The attributes '.implode(',', array_keys($not_processed)).' are not attributes for the object '.$this->objName;
			
			return false;
		}
		return true;
	}
	
	protected function toRest($empty_fields = true)
	{
		foreach ($this->properties as $key => $value)
		{			
			if($empty_fields && empty($this->$key))
			{
				$output[$key] = '';
			} else {
				if(!empty($this->$key)){
					$output[$key] = $this->$key;
				}
			}
		}
		return $output;
	}
	
	public function checkUniqueAttribute(array $input)
	{
		if(empty($input['attribute'])) return false;
		$attribute = $input['attribute'];
		
		$search['filter'] = '('.$attribute.'='.$input[$attribute].')';
		$return = $this->read($search);
		$found = count($return['data']);
		return $found== 0 ? TRUE : FALSE;
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
}

/* End of objectcommon.php */