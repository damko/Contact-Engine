<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
// Created on Oct 14, 2011 by Damiano Venturin @ Squadra Informatica
// Php unit tests

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH.'/libraries/test_controller.php';

class Unit_Tests_Api extends Test_Controller {

	public function __construct()
	{	
		parent::__construct();
				
		if(ENVIRONMENT != 'production') {
			echo '<h2>please set the constant ENVIROMENT in index.php to "production" !</h2>';
			die();
		}
				
		//load the chartex class
		$this->load->spark('chartex/0.0.1');
				
		//load the rest client
		$this->load->spark('restclient/2.1.0');	
		
		//set the server page
		//$a = $this->config->item('rest_server');
		$this->rest->initialize(array('server' => $this->config->item('rest_server').'/exposeObj/chartex/'));		
	}

	public function index()
	{
		
		$this->load->view('unit_tests');
		
		echo '<a href="/index.php/unit_tests/">Back</a> to unit-tests front page.<br/>';
		
		$this->printSummary();
		
		echo '<div id="left">';
				
		$method = 'read';
		
		$this->testTitle('Use method read of chartex object');
		//check to get an array as a return
		$rest_return = $this->rest->get($method, null, 'serialize');
		$test = false;
		if(is_array($rest_return)) $test = true;
		$this->getCodeOrigin();
		echo $this->run($test, 'is_true', $method.'- valid data in return ?');
		
		
		$this->subTestTitle('Check for valid return');
		$expected_result = array('banana' => '2', 'potato' => '3');
		$return_no_status = $rest_return;
		unset($return_no_status['status']);
		$this->getCodeOrigin();
		echo $this->run($return_no_status, $expected_result, $method.'- valid data in return ?');
				
		$this->printReturn($rest_return);


		
		
		
		$this->testTitle('Use method add of chartex object');
		$method = 'add/watermelon/5';
		//check to get an array as a return
		$rest_return = $this->rest->get($method, $post, 'serialize');
		$test = false;
		if(is_array($rest_return)) $test = true;
		$this->getCodeOrigin();
		echo $this->run($test, 'is_true', $method.'- valid data in return ?');
		
		
		$this->subTestTitle('Check for valid return');
		$expected_result = array('banana' => '2', 'potato' => '3', 'watermelon' => '5');
		$return_no_status = $rest_return;
		unset($return_no_status['status']);
		//$test = (array) $return_no_status;
		$this->getCodeOrigin();
		echo $this->run($return_no_status, $expected_result, $method.'- valid data in return ?');
		
		
		$this->printReturn($rest_return);
		
		echo '</div>';
		
		
		echo '<div></body></html>';		
	}		
}

/* End of api_tests.php */