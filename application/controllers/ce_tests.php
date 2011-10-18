<?php  if ( ! defined('BASEPATH'))
 exit('No direct script access allowed');
// Created on Aug 26, 2011 by Damiano Venturin @ Squadra Informatica

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH.'/libraries/test_controller.php';

class Ce_Tests extends Test_Controller {
	public function __construct()
	{
		parent::__construct();
		
		//false = no rest output printed
		$this->show_rest_return = false;
		
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
		$this->testPerson();
		$this->testOrganization();
		$this->testLocation();
		$this->testContact();
		$this->testPersonAssocOrg();
		$this->testPersonAssocLoc();
	}
	
	public function testPerson()
	{
		$this->testPersonCreate();
		$this->testPersonRead();
		$this->testPersonUpdate();
		$this->testPersonDelete();
	}
	
	public function testPersonProperties()
	{
		$this->rest->initialize(array('server' => $this->config->item('rest_server').'/exposeObj/person/'));
		
		//########################################
		// GET PROPERTIES
		//########################################
		$this->testTitle('Test: get person properties</h3>');
		$method = 'getProperties';
		
		//check to get an array as a return
		$rest_return = $this->rest->get($method, null, 'serialize');
		$this->arrayReturn($method, $rest_return);
		
		//check no REST error in return
		$this->checkNoRestError($method, $rest_return);
		
		//check status code == 200
		$this->check200($method, $rest_return);
		
		$this->printReturn($rest_return);
	}

	public function testPersonRead()
	{	
		$this->rest->initialize(array('server' => $this->config->item('rest_server').'/exposeObj/person/'));
		
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
	
		//----------------------
		
		//read 1 non-existant person
		$this->testTitle('Test: read a non-existant person');
		$method = 'read';
		$input = array();
		$input['filter'] = '(uid=1293813238g9238479832)';
		
		$rest_return = $this->rest->get($method, $input, 'serialize');
		
		//check to get an array as a return
		$this->arrayReturn($method, $rest_return);
		
		//the filter has not been specified then it should return an error
		$this->checkRestError($method, $rest_return);
		
		//check status code == 400
		$this->check400($method, $rest_return);
		
		$this->printReturn($rest_return);
	}

	public function testPersonCreate() 
	{	
		$this->rest->initialize(array('server' => $this->config->item('rest_server').'/exposeObj/person/'));

		//calling the methon CREATE for object person without a filter
		$this->testTitle('Test: create 1 person');
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
		
		return $test;
	}

	public function testPersonUpdate()
	{	
		$this->rest->initialize(array('server' => $this->config->item('rest_server').'/exposeObj/person/'));

		//calling the methon READ for object person with a filter
		$this->testTitle('Test: update 1 person taken randomly but starting with Willy*');
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
			$person['displayName'] = 'Willy Test';
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
	}
	
	public function testPersonDelete()
	{
		$this->rest->initialize(array('server' => $this->config->item('rest_server').'/exposeObj/person/'));

		//calling the methon READ for object person with a filter
		$this->testTitle('Test: delete 1 person taken randomly but starting with Willy*');
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
				
		//delete 1 non-existant person
		$this->testTitle('Test: delete 1 non-existant person');
		$method = 'delete';
		$input = array();
		$input['uid'] = '129381@lr91\/3238g9238479832';
		
		$rest_return = $this->rest->get($method, $input, 'serialize');
		
		//check to get an array as a return
		$this->arrayReturn($method, $rest_return);
		
		//the filter has not been specified then it should return an error
		$this->checkRestError($method, $rest_return);
		
		//check status code == 400
		$this->check400($method, $rest_return);
		
		$this->printReturn($rest_return);		
	}
	
	public function testPersonAssocOrg($loadview = true, $getInfo = false)
	{
		$this->rest->initialize(array('server' => $this->config->item('rest_server').'/exposeObj/person/'));
	
		$oid = $this->testOrganizationCreate();
		$uid = $this->testPersonCreate();
			
		$this->testTitle('Testing the association of a person to an organization');
		$method = 'associate';
		$input = array();
		$input['uid'] = $uid;
		$input['oid'] = $oid;
		$input['to'] = 'organization';
	
		$rest_return = $this->rest->get($method, $input, 'serialize');
	
		//check to get an array as a return
		$this->arrayReturn($method, $rest_return);
	
		//the filter has not been specified then it should return an error
		$this->checkNoRestError($method, $rest_return);
	
		//check status code == 200
		$this->check200($method, $rest_return);
		
		//check uid is a number
		$test = $rest_return['data']['0'];
		echo $this->unit->run($test, 'is_numeric', $method.' - Integer uid', 'Checking if the returned uid is a number');		
	
		$this->printReturn($rest_return);
	}
	
	public function testPersonAssocLoc($loadview = true, $getInfo = false)
	{
		$this->rest->initialize(array('server' => $this->config->item('rest_server').'/exposeObj/person/'));
	
		$locId = $this->testLocationCreate();
		$uid = $this->testPersonCreate();
			
		$this->testTitle('Testing the association of a person to a location');
		$method = 'associate';
		$input = array();
		$input['uid'] = $uid;
		$input['locId'] = $locId;
		$input['to'] = 'location';
	
		$rest_return = $this->rest->get($method, $input, 'serialize');
	
		//check to get an array as a return
		$this->arrayReturn($method, $rest_return);
	
		//the filter has not been specified then it should return an error
		$this->checkNoRestError($method, $rest_return);
	
		//check status code == 200
		$this->check200($method, $rest_return);
	
		//check uid is a number
		$test = $rest_return['data']['0'];
		echo $this->unit->run($test, 'is_numeric', $method.' - Integer uid', 'Checking if the returned uid is a number');
	
		$this->printReturn($rest_return);
	}	
		
	public function testOrganization()
	{
		$this->testOrganizationCreate();
		$this->testOrganizationRead();
		$this->testOrganizationUpdate();
		$this->testOrganizationDelete();
	}

	public function testOrganizationProperties()
	{
		$this->rest->initialize(array('server' => $this->config->item('rest_server').'/exposeObj/organization/'));	

		$this->testTitle('Test: get organization properties</h3>');
		$method = 'getProperties';
			
		//check to get an array as a return
		$rest_return = $this->rest->get($method, null, 'serialize');
		$this->arrayReturn($method, $rest_return);
		
		//check no REST error in return
		$this->checkNoRestError($method, $rest_return);
		
		//check status code == 200
		$this->check200($method, $rest_return);
		
		$this->printReturn($rest_return);
	}

	public function testOrganizationRead()
	{
		$this->rest->initialize(array('server' => $this->config->item('rest_server').'/exposeObj/organization/'));
		
		//calling the methon READ for object organization without a filter
		$this->testTitle('Test: read organization entries','Sending a request missing "filter". I expect a failure');
		$method = 'read';
		
		//check to get an array as a return
		$rest_return = $this->rest->get($method, null, 'serialize');
		$this->arrayReturn($method, $rest_return);
		
		//the filter has not been specified then it should return an error
		$this->checkRestError($method, $rest_return);
		
		//check status code == 400
		$this->check400($method, $rest_return);
		
		$this->printReturn($rest_return);
	
		
		//-------------------------
		
		
		//calling the methon READ for object organization with a filter
		$this->testTitle('Test: read organization entries','Sending a request with "filter". I expect to get all organizations in the storage');
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
		
	}

	public function testOrganizationCreate()
	{
		$this->rest->initialize(array('server' => $this->config->item('rest_server').'/exposeObj/organization/'));
			
		//calling the methon CREATE for object organization without a filter
		$this->testTitle('Test: create 1 organization');
		$method = 'create';
		$input = array();
		$random = rand(999,9999);
		$orgname = 'ACME'.$random;
		
		//required fields
		$input['enabled'] = 'TRUE';
		$input['entryCreatedBy'] = 'dam';
		$input['o'] = $orgname;
		//oid is automatically set, so not needed
		
		//check to get an array as a return
		$rest_return = $this->rest->get($method, $input, 'serialize');
		$this->arrayReturn($method, $rest_return);
		
		//the filter has not been specified then it should return an error
		$this->checkNoRestError($method, $rest_return);
		
		//check status code == 200
		$this->check200($method, $rest_return);
		
		//check oid is a number
		$test = $rest_return['data']['0'];
		echo $this->unit->run($test, 'is_numeric', $method.' - Integer oid', 'Checking if the returned oid is a number');
		
		$this->printReturn($rest_return);
		
		return $test; //oid
	}
		
	public function testOrganizationUpdate()
	{
		$this->rest->initialize(array('server' => $this->config->item('rest_server').'/exposeObj/organization/'));		
		
		//calling the methon READ for object organization with a filter
		$this->testTitle('Test: update 1 organization taken randomly but starting with Acme*');
		$method = 'read';
		$input = array();
		$input['filter'] = '(o=Acme*)';
		
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
			$organization = $rest_return['data'][array_rand($rest_return['data'])];
			$organization['o'] = 'Acme Test';
			$method = 'update';
			$rest_return = $this->rest->get($method, $organization, 'serialize');
		
			//check to get an array as a return
			$this->arrayReturn($method, $rest_return);
		
			//the filter has not been specified then it should return an error
			$this->checkNoRestError($method, $rest_return);
		
			//check status code == 200
			$this->check200($method, $rest_return);
		
			//check uid is a number
			$oid = $test = $rest_return['data']['0'];
			echo $this->unit->run($test, 'is_numeric', $method.' - Integer oid', 'Checking if the returned oid is a number');
		
			$this->printReturn($rest_return);
		
			//gets the organization updated
			$method = 'read';
			$input = array();
			$input['filter'] = '(oid='.$oid.')';
		
			$rest_return = $this->rest->get($method, $input, 'serialize');
		
			//check to get an array as a return
			$this->arrayReturn($method, $rest_return);
		
			//the filter has not been specified then it should return an error
			$this->checkNoRestError($method, $rest_return);
		
			//check status code == 200
			$this->check200($method, $rest_return);
		
			$this->printReturn($rest_return);
		}
	}

	public function testOrganizationDelete()
	{
		$this->rest->initialize(array('server' => $this->config->item('rest_server').'/exposeObj/organization/'));	
		//########################################
		// DELETE
		//########################################
		//calling the methon READ for object organization with a filter
		$this->testTitle('Test: delete 1 organization taken randomly but starting with Acme*');
		$method = 'read';
		$input = array();
		$input['filter'] = '(o=Acme*)';
		
		$rest_return = $this->rest->get($method, $input, 'serialize');
		
		//check to get an array as a return
		$this->arrayReturn($method, $rest_return);
		
		//the filter has not been specified then it should return an error
		$this->checkNoRestError($method, $rest_return);
		
		//check status code == 200
		$this->check200($method, $rest_return);
		
		if(is_array($rest_return) and !empty($rest_return['data']))
		{
			$organization = $rest_return['data'][array_rand($rest_return['data'])];
			//check oid is a number
			$oid = $test = $organization['oid'];
			echo $this->unit->run($test, 'is_numeric', $method.' - Integer oid', 'Checking if the returned oid is a number');
		
			$this->printReturn($rest_return);
		
			//gets the organization updated
			$method = 'delete';
			$input = array();
			$input['oid'] = $oid;
		
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
	}

	public function testLocation()
	{
		$this->testLocationCreate();
		$this->testLocationRead();
		$this->testLocationUpdate();
		$this->testLocationDelete();
	}
		
	public function testLocationProperties()
	{		
		$this->rest->initialize(array('server' => $this->config->item('rest_server').'/exposeObj/location/'));
		
		$this->testTitle('Test: get location properties</h3>');
		$method = 'getProperties';
			
		//check to get an array as a return
		$rest_return = $this->rest->get($method, null, 'serialize');
		$this->arrayReturn($method, $rest_return);
		
		//check no REST error in return
		$this->checkNoRestError($method, $rest_return);
		
		//check status code == 200
		$this->check200($method, $rest_return);
		
		$this->printReturn($rest_return);
	}
	
	public function testLocationRead()
	{
		$this->rest->initialize(array('server' => $this->config->item('rest_server').'/exposeObj/location/'));

		//calling the methon READ for object location without a filter
		$this->testTitle('Test: read location entries','Sending a request missing "filter". I expect a failure');
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
		
		//calling the methon READ for object location with a filter
		$this->testTitle('Test: read location entries','Sending a request with "filter". I expect to get all locations in the storage');
		$method = 'read';
		$input = array();
		$input['filter'] = '(objectClass=*)';
		
		$rest_return = $this->rest->get($method, $input, 'serialize');
		
		//check to get an array as a return
		$this->arrayReturn($method, $rest_return);
		
		//the filter has not been specified then it should return an error
		$this->checkNoRestError($method, $rest_return);
		
		//check status code == 200
		$this->check200($method, $rest_return);
		
		$this->printReturn($rest_return);
	}
	
	public function testLocationCreate()
	{
		$this->rest->initialize(array('server' => $this->config->item('rest_server').'/exposeObj/location/'));	

		//calling the methon CREATE for object location without a filter
		$this->testTitle('Test: create 1 location');
		$method = 'create';
		$input = array();
		$random = rand(999,9999);
		$locDescription = 'MyDescription '.$random;
		
		//required fields
		
		$input['locCity'] = 'MyCity '.$random;
		$input['locCountry'] = 'MyCountry '.$random;
		$input['locDescription'] = 'MyDescription '.$random;
		$input['locState'] = 'MyState '.$random;
		$input['locStreet'] = 'MyStreet '.$random;
		//locId is automatically set, so not needed
		
		//check to get an array as a return
		$rest_return = $this->rest->get($method, $input, 'serialize');
		$this->arrayReturn($method, $rest_return);
		
		//the filter has not been specified then it should return an error
		$this->checkNoRestError($method, $rest_return);
		
		//check status code == 200
		$this->check200($method, $rest_return);
		
		//check oid is a number
		$test = $rest_return['data']['0'];
		echo $this->unit->run($test, 'is_numeric', $method.' - Integer locId', 'Checking if the returned locId is a number');
		
		$this->printReturn($rest_return);
		
		return $test; //locId
	}

	public function testLocationUpdate()
	{
		$this->rest->initialize(array('server' => $this->config->item('rest_server').'/exposeObj/location/'));	
		
		//calling the methon READ for object location with a filter
		$this->testTitle('Test: update 1 location taken randomly but starting with MyDescription*');
		$method = 'read';
		$input = array();
		$input['filter'] = '(locDescription=MyDescription*)';
		
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
			$location = $rest_return['data'][array_rand($rest_return['data'])];
			$location['locDescription'] = 'MyDescription TEST';
			$method = 'update';
			$rest_return = $this->rest->get($method, $location, 'serialize');
		
			//check to get an array as a return
			$this->arrayReturn($method, $rest_return);
		
			//the filter has not been specified then it should return an error
			$this->checkNoRestError($method, $rest_return);
		
			//check status code == 200
			$this->check200($method, $rest_return);
		
			//check uid is a number
			$locId = $test = $rest_return['data']['0'];
			echo $this->unit->run($test, 'is_numeric', $method.' - Integer locId', 'Checking if the returned locId is a number');
		
			$this->printReturn($rest_return);
		
			//gets the location updated
			$method = 'read';
			$input = array();
			$input['filter'] = '(locId='.$locId.')';
		
			$rest_return = $this->rest->get($method, $input, 'serialize');
		
			//check to get an array as a return
			$this->arrayReturn($method, $rest_return);
		
			//the filter has not been specified then it should return an error
			$this->checkNoRestError($method, $rest_return);
		
			//check status code == 200
			$this->check200($method, $rest_return);
		
			$this->printReturn($rest_return);
		}
	}
	
	public function testLocationDelete()
	{
		$this->rest->initialize(array('server' => $this->config->item('rest_server').'/exposeObj/location/'));	

		//calling the methon READ for object location with a filter
		$this->testTitle('Test: delete 1 location taken randomly but starting with MyDescription*');
		$method = 'read';
		$input = array();
		$input['filter'] = '(locDescription=MyDescription*)';
		
		$rest_return = $this->rest->get($method, $input, 'serialize');
		
		if(is_array($rest_return) and !empty($rest_return['data']))
		{
			$location = $rest_return['data'][array_rand($rest_return['data'])];
			//check locId is a number
			$locId = $test = $location['locId']['0'];
			echo $this->unit->run($test, 'is_numeric', $method.' - Integer locId', 'Checking if the returned locId is a number');
		
			$this->printReturn($rest_return);
		
			//gets the location updated
			$method = 'delete';
			$input = array();
			$input['locId'] = $locId;
		
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
	}
		
	public function testContact()
	{
		$this->rest->initialize(array('server' => $this->config->item('rest_server').'/exposeObj/contact/'));
		
		//calling the methon READ for object contact with a filter
		$this->testTitle('Testing CONTACT: get a contact using this filter: (|(givenName=Willy*)(o=A*))');
		$method = 'read';
		$input = array();
		$input['filter'] = '(|(givenName=Willy*)(o=A*))';
		
		$rest_return = $this->rest->get($method, $input, 'serialize');
		
		//check to get an array as a return
		$this->arrayReturn($method, $rest_return);
		
		//the filter has not been specified then it should return an error
		$this->checkNoRestError($method, $rest_return);
		
		//check status code == 200
		$this->check200($method, $rest_return);
		
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