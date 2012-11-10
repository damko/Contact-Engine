<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ce_Return_Object extends CI_Model
{		
	protected $data = array(); //contains data
	protected $status_code = null; //this is for REST response.
	protected $message = null; //this is for REST response.
	protected $results_number = null; //total number of result for the current request
	protected $results_got_number = null; //number of items contained in the current set
	protected $results_pages = null; //number of pages necessary to display all the data
	protected $results_page = null; //page number of the current set	
	protected $finished = null; //page number of the current set
	protected $duration = null; //page number of the current set
	
	public function __construct(){
		parent::__construct();
	}
	
	public function __destruct(){
	
	}
	
	public function __set($attribute, $value) {
		
		//default values
		$default = array(
						'data' => array(),				
						'status_code' => '500',
						'message' => 0,
						'results_number' => 0,
						'results_got_number' => 0,
						'results_pages' => '0',
						'results_page' => '0',
						'finished' => null,
						'duration' => null,
		);
		
		//sets the default values if necessary and prevents to store in the obj attributes that are not declared
		$obj_attributes = array_keys(get_object_vars($this));
		if(in_array($attribute, $obj_attributes))
		{
			if(is_null($value)) {
					$value = $default[$attribute];
			} 
			
			if($attribute == 'data' && !is_array($value)) $value = $default[$attribute];
			
			//finally store the value
			$this->$attribute = $value;
		} else {
			$a = '';
		}		
	}
	
	public function __get($attribute) {
		return $this->$attribute;
	}
	
	public function importLdapReturnObject($lro)
	{
		$obj_name = get_class($lro);
		if($obj_name != 'Ldap_Return_Object') return false;
		
		$this->status_code = $lro->data->http_status_code;
		$this->message = $lro->data->http_status_message;
		$this->data = $lro->data->content;		
		$this->results_number = $lro->data->results_number; 
		$this->results_got_number = $lro->data->results_got_number;
		$this->results_pages = $lro->data->results_pages;
		$this->results_page = $lro->data->results_page;		
		
		return true;
	}

	public function pushData($data)
	{
		//If I'm overwriting the data content then I have also to update the other parameters.
		$this->data = $data;
		//It's no more a LDAP result
		$this->updateResultsValues();		
	}
	
	private function updateResultsValues() {
		$this->results_number = count($this->data);
		$this->results_got_number = $this->results_number;		
	}
	
	public function returnAsArray() {
		$output = array(
				'status' => array(
						'results_number' => 0,
						'results_got_number' => 0,
						'results_pages' => '0',
						'results_page' => '0',
						'finished' => 0,
						'duration' => 0,
						'status_code' => null,
						'message' => null,
				),
				'data' => array(),
		);		
		
		//REST info
		if(isset($this->status_code)) $output['status']['status_code'] = $this->status_code;
		if(isset($this->message)) $output['status']['message'] = $this->message;
		
		//pagination
		if(isset($this->results_number)) $output['status']['results_number'] = $this->results_number;
		if(isset($this->results_pages)) $output['status']['results_pages'] = $this->results_pages;
		if(isset($this->results_page)) $output['status']['results_page'] = $this->results_page;
		if(isset($this->results_got_number)) $output['status']['results_got_number'] = $this->results_got_number;
		if(isset($this->finished)) $output['status']['finished'] = $this->finished;
		if(isset($this->duration)) $output['status']['duration'] = $this->duration;
		
		//content
		if(isset($this->data)) $output['data'] = $this->data;
		
		return $output;
	}
	
}