<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
// Created on Aug 26, 2011 by Damiano Venturin @ Squadra Informatica

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH.'/libraries/test_controller.php';

class Ce_Tests extends Test_Controller {
	public function __construct()
	{
		parent::__construct();
		
		//false = no rest output printed
		$this->show_rest_return = true;
		
/* 		//set up a new template for the tests output
		$this->unit->set_test_items(array('test_name', 'result'));
		$str = '
		<table border="0" cellpadding="4" cellspacing="1">
		{rows}
		<tr>
		<td>{item}</td>
		<td>{result}</td>
		</tr>
		{/rows}
		</table>';
		
		$this->unit->set_template($str); */
				
	}	
 
	private function testTitle($text,$subtext = null)
	{
		echo '[<a href="'.site_url().'">Home</a>]';
		echo '<hr/>';
		echo '<h3>'.$text.'</h3>';
		echo '<p>'.$subtext.'</p>';
	}
	
	
	public function index()
	{
		$this->rest->initialize(array('server' => $this->config->item('rest_server').'/exposeObj/person/'));

		//########################################
		// GET PROPERTIES
		//########################################
		$this->testTitle('Test: get person properties</h3>');
		$method = 'getProperties';
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
		
		$this->printReturn($rest_return);		
		
		//########################################
		// READ
		//########################################
		//calling the methon READ for object person without a filter
		$this->testTitle('Test: read person entries','Sending a request missing "filter". I expect a failure');
		$method = 'read';
		
		//check to get an array as a return
		$rest_return = $this->rest->get($method, null, 'serialize');
		$this->arrayReturn($method, $rest_return);
	
		//the filter has not been specified then it should return an error
		$this->checkRestError($method, $rest_return);
	
		//check status code == 400
		$this->check400($method, $rest_return);
		
		$this->printReturn($rest_return);
		
		//----------------------
		//calling the methon READ for object person with a filter
		$this->testTitle('Test: read person entries','Sending a request with "filter". I expect to get all persons in the storage');
		$method = 'read';
		$input = array();
		$input['filter'] = '(objectClass=*)';
		//check to get an array as a return
		$rest_return = $this->rest->get($method, $input, 'serialize');
		$this->arrayReturn($method, $rest_return);
		
		//the filter has not been specified then it should return an error
		$this->checkNoRestError($method, $rest_return);
		
		//check status code == 200
		$this->check200($method, $rest_return);
		
		$this->printReturn($rest_return);

		//########################################
		// CREATE
		//########################################
		//calling the methon CREATE for object person without a filter
		$this->testTitle('Test: create 1 person','Sending a request with "filter". I expect it goes well and returns the uid.');
		$method = 'create';
		$input = array();	
		$random = rand(999,9999);
		$surname = 'Coyote'.$random;
		$name = 'Willy'.$random;
		
		//required fields
		$input['category'] = 'mycategory';
		$input['cn'] = $name.' '.$surname;
		$input['displayName'] = $input['cn'];
		$input['enabled'] = 'TRUE';
		$input['entryCreatedBy'] = 'dam';
		$input['fileAs'] = $input['cn'];
		$input['givenName'] = $name;
		$input['sn'] = $surname;
		//uid is automatically set, so not needed
		$input['userPassword'] = 'mypassword';
		
		//check to get an array as a return
		$rest_return = $this->rest->get($method, $input, 'serialize');
		$this->arrayReturn($method, $rest_return);
		
		//the filter has not been specified then it should return an error
		$this->checkNoRestError($method, $rest_return);
		
		//check status code == 200
		$this->check200($method, $rest_return);
		
		//check uid is a number
		$test = $rest_return['data']['0'];
		echo $this->unit->run($test, 'is_numeric', $method.' - Integer uid', 'Checking if the returned uid is a number');
		
		$this->printReturn($rest_return);
		
		
		//########################################
		// UPDATE
		//########################################
		//calling the methon READ for object person with a filter
		$this->testTitle('Test: update a person taken randomly but starting with Willy*');
		$method = 'read';
		$input = array();
		$input['filter'] = '(givenName=Willy*)';
		
		$rest_return = $this->rest->get($method, $input, 'serialize');
		
		//check to get an array as a return
		$this->arrayReturn($method, $rest_return);
		
		//the filter has not been specified then it should return an error
		$this->checkNoRestError($method, $rest_return);
		
		//check status code == 200
		$this->check200($method, $rest_return);
		
		$this->printReturn($rest_return);
		
		if(is_array($rest_return) and !empty($rest_return['data']))
		{
			$person = $rest_return['data'][array_rand($rest_return['data'])];
			$person['displayName'] = 'Test Update';
			$method = 'update';
			$rest_return = $this->rest->get($method, $person, 'serialize');

			//check to get an array as a return
			$this->arrayReturn($method, $rest_return);
			
			//the filter has not been specified then it should return an error
			$this->checkNoRestError($method, $rest_return);
			
			//check status code == 200
			$this->check200($method, $rest_return);
			
			//check uid is a number
			$uid = $test = $rest_return['data']['0'];
			echo $this->unit->run($test, 'is_numeric', $method.' - Integer uid', 'Checking if the returned uid is a number');			

			$this->printReturn($rest_return);
			
			//gets the person updated
			$method = 'read';
			$input = array();
			$input['filter'] = '(uid='.$uid.')';
			
			$rest_return = $this->rest->get($method, $input, 'serialize');
			
			//check to get an array as a return
			$this->arrayReturn($method, $rest_return);
			
			//the filter has not been specified then it should return an error
			$this->checkNoRestError($method, $rest_return);
			
			//check status code == 200
			$this->check200($method, $rest_return);			
			
			$this->printReturn($rest_return);
		}

		
		//########################################
		// DELETE
		//########################################
		//calling the methon READ for object person with a filter
		$this->testTitle('Test: delete a person taken randomly but starting with Willy*');
		$method = 'read';
		$input = array();
		$input['filter'] = '(givenName=Willy*)';
		
		$rest_return = $this->rest->get($method, $input, 'serialize');
				
		if(is_array($rest_return) and !empty($rest_return['data']))
		{
			$person = $rest_return['data'][array_rand($rest_return['data'])];
			$person['displayName'] = 'Test Update';
			$method = 'update';
			$rest_return = $this->rest->get($method, $person, 'serialize');
		
			//check uid is a number
			$uid = $test = $rest_return['data']['0'];
			echo $this->unit->run($test, 'is_numeric', $method.' - Integer uid', 'Checking if the returned uid is a number');
		
			$this->printReturn($rest_return);
				
			//gets the person updated
			$method = 'delete';
			$input = array();
			$input['uid'] = $uid;
				
			$rest_return = $this->rest->get($method, $input, 'serialize');
				
			//check to get an array as a return
			$this->arrayReturn($method, $rest_return);
				
			//the filter has not been specified then it should return an error
			$this->checkNoRestError($method, $rest_return);
				
			//check status code == 200
			$this->check200($method, $rest_return);
			
			//check return is TRUE
			$result = $rest_return['data']['0'];
			echo $this->unit->run($result, '1', $method.' - Returns true');
				
				
			$this->printReturn($rest_return);
		}
		
		
		//delete a non-existant person
		$this->testTitle('Test: delete a non-existant person');		
		$method = 'delete';
		$input = array();
		$input['uid'] = '129381@lr91\/3238g9238479832';
		
		$rest_return = $this->rest->get($method, $input, 'serialize');
		
		//check to get an array as a return
		$this->arrayReturn($method, $rest_return);
		
		//the filter has not been specified then it should return an error
		$this->checkRestError($method, $rest_return);
		
		//check status code == 200
		$this->check400($method, $rest_return);
		
		$this->printReturn($rest_return);		
	}
	
	public function pd()
	{
		$test = 'abcde';
		var_export($test);
		echo "\n";
		
		$test['aa'] = 'AA';
		var_export($test);
		echo "\n";
		
		$test['bb'] = 'BB';
		var_export($test);
		echo "\n";
		
		$test['cde'] = 'XYZ';
		var_export($test);
		echo "\n";		
	}
}