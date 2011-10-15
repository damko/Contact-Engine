<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
// Created on Aug 26, 2011 by Damiano Venturin @ Squadra Informatica

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH.'/libraries/test_controller.php';

class Ce_Tests extends Test_Controller {
	public function __construct()
	{
		parent::__construct();
	}	

	public function index()
	{
		$this->rest->initialize(array('server' => $this->config->item('rest_server').'/exposeObj/person/'));
		
		//########################################
		// READ
		//########################################
		$method = 'read';
		$data['testdesc'] = 'Gets all data about all the persons in the storage. Rest query performed with HTTP_Request2. <i>Output: PHP array</i>';
		$data['url'] = site_url('/api/exposeObj/person/read/format/serialize');
		$data['filter'] = '(objectClass=*)';
		$data['method'] = 'POST';
			
		//check to get an array as a return
		$rest_return = $this->rest->get($method, null, 'serialize');
		$this->arrayReturn($method, $rest_return);
	
		//check no REST error in return
		$this->checkNoRestError($method, $rest_return);
	
		//check status code == 200
		$this->check200($method, $rest_return);
	
/* 		//check that data content is valid
		$expected_result = array('banana' => '2', 'potato' => '3');
		$test = (array) $rest_return['data'];
		echo $this->unit->run($test, $expected_result, $method.'- valid data in return ?');
 */	
		$this->printReturn($rest_return);

	}
	
	public function testRead_All_A1($loadview = true, $getInfo = false)
	{
		$data = array();
		$data['testname'] = __FUNCTION__;
		$data['object'] = 'Person';
		$data['testdesc'] = 'Gets all data about all the persons in the storage. Rest query performed with HTTP_Request2. <i>Output: PHP array</i>';
		$data['url'] = site_url('/api/exposeObj/person/read/format/serialize');
		$data['filter'] = '(objectClass=*)';
		$data['method'] = 'POST';
	
		if($getInfo) return $data;
	
		$request = new HTTP_Request2($data['url']);
		$request->setMethod(HTTP_Request2::METHOD_POST)
		->addPostParameter('filter', $data['filter']);
	
		//Benchmark starts
		$this->benchmark->mark('code_start');
	
		$response = $request->send();
		$data['response'] = unserialize($response->getBody());
	
		//Benchmark stops
		$this->benchmark->mark('code_end');
		$data['benchmark'] = $this->benchmark->elapsed_time('code_start', 'code_end').' seconds to retrieve '.count($data['response']).' usable contacts';
			
		if($loadview) $this->load->view('tests_view',$data);
	
		//passed or failed
		return empty($data['response']['error']) ? true: false;
	}	
	
}