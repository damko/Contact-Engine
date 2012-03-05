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
		$this->load->spark('restclient/2.0.0');	
		
		//set the server page
		//$a = $this->config->item('rest_server');
		$this->rest->initialize(array('server' => $this->config->item('rest_server').'/exposeObj/chartex/'));		
	}

	public function index()
	{
		
		$this->load->view('unit_tests');
		
		echo '<a href="/index.php/unit_tests/">Back</a> to unit-tests front page.<br/>';
		
		echo '<div id="left">';
				
		//########################################
		// READ
		//########################################
		$method = 'read';
		
		//check to get an array as a return
		$rest_return = $this->rest->get($method, null, 'serialize');
		$this->getCodeOrigin();
		$this->arrayReturn($method, $rest_return);

		//check no REST error in return
		$this->getCodeOrigin();
		$this->checkNoRestError($method, $rest_return);

		//check status code == 200
		$this->getCodeOrigin();
		$this->check200($method, $rest_return);
		
		//check that data content is valid
		$expected_result = array('banana' => '2', 'potato' => '3');
		$test = (array) $rest_return['data'];
		$this->getCodeOrigin();
		echo $this->unit->run($test, $expected_result, $method.'- valid data in return ?');
				
		$this->printReturn($rest_return);

		//########################################
		// ADD
		//########################################
		$method = 'add/watermelon/5';
		
		//check to get an array as a return
		$rest_return = $this->rest->get($method, $post, 'serialize');
		$this->getCodeOrigin();
		$this->arrayReturn($method, $rest_return);

		//check no REST error in return
		$this->getCodeOrigin();
		$this->checkNoRestError($method, $rest_return);
		
		//check status code == 200
		$this->getCodeOrigin();
		$this->check200($method, $rest_return);
		
		$this->printReturn($rest_return);
		
		echo '</div>';
		
		$this->printSummary();
		
		echo '<div></body></html>';		
	}		
}

/* End of api_tests.php */