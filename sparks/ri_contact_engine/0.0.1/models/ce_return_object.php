<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ce_Return_Object extends CI_Model
{	
	protected $data = array(); //contains data
	protected $http_status_code = null; //this is for REST response.
	protected $http_message = null; //this is for REST response.
	protected $results_number = null; //total number of result for the current request
	protected $results_pages = null; //number of pages necessary to display all the data
	protected $results_page = null; //page number of the current set	
	protected $sent_back_results_number = null; //number of items contained in the current set
	
	public function __construct(){
		parent::__construct();
	}
	
	public function __destruct(){
	
	}
	
	public function __set($attribute, $value) {
		$this->$attribute = $value;
	}
	
	public function __get($attribute) {
		return $this->$attribute;
	}
	
	public function importLdapReturnObject($lro)
	{
		$obj_name = get_class($lro);
		if($obj_name != 'Ldap_Return_Object') return false;
		
		$this->http_status_code = $lro->data->http_status_code;
		$this->http_message = $lro->data->http_status_message;
		$this->data = $lro->data->content;		
		$this->results_number = $lro->data->results_number; 
		$this->sent_back_results_number = $lro->data->sent_back_results_number;
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
		$this->sent_back_results_number = $this->results_number;
		
		$this->results_pages = '1';
		$this->results_page = '1';		
	}
	
	public function returnAsArray() {
		$output = array(
				'status' => array(
						'results_number' => 0,
						'results_got_number' => 0,
						'results_pages' => '1',
						'results_page' => '1',
						'finished' => null,
						'duration' => null,
						'status_code' => null,
						'message' => null,
				),
				'data' => array(),
		);		
		
		//REST info
		if(isset($this->http_status_code)) $output['status']['status_code'] = $this->http_status_code;
		if(isset($this->http_message)) $output['status']['message'] = $this->http_message;
		
		//pagination
		if(isset($this->results_number)) $output['status']['results_number'] = $this->results_number;
		if(isset($this->results_pages)) $output['status']['results_pages'] = $this->results_pages;
		if(isset($this->results_page)) $output['status']['results_page'] = $this->results_page;
		if(isset($this->sent_back_results_number)) $output['status']['results_got_number'] = $this->sent_back_results_number;
		
		//content
		if(isset($this->data)) $output['data'] = $this->data;
		
		return $output;
	}
	
}