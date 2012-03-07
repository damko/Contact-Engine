<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// Created on Aug 26, 2011 by Damiano Venturin @ Squadra Informatica

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH.'/libraries/test_controller.php';
//TODO why this doesn't work?
//$CI = &get_instance();
//$CI->load->library('test_controller');

class Unit_Tests_Ce extends Test_Controller {
	public function __construct()
	{
		parent::__construct();
 
		if(ENVIRONMENT != 'production') {
			echo '<h2>please set the constant ENVIROMENT in index.php to "production" !</h2>';
			die();
		}
			 
		//load the rest client
		$this->load->spark('restclient/2.0.0');
	}	
	
	public function index()
	{
		$this->load->view('unit_tests');
		
		echo '<a href="/index.php/unit_tests/">Back</a> to unit-tests front page.<br/>';

		echo '<div id="left">';
		$this->testPerson();
		$this->testOrganization();
		$this->testLocation();
  		$this->testContact();
		$this->testPersonAssocOrg();
 		$this->testPersonAssocLoc();
		$this->testOrganizationAssocLoc();
		echo '</div>';
		
		$this->printSummary();
		
		echo '<div></body></html>';
		
	}
	
	public function testPerson()
	{
		$this->testPersonProperties();
		$this->testPersonCreate();
		$this->testPersonRead();
		$this->testPersonUpdate();
		$this->testPersonDelete();
	}
	
	public function testOrganization()
	{
		$this->testOrganizationProperties();
		$this->testOrganizationCreate();
		$this->testOrganizationRead();
		$this->testOrganizationUpdate();
		$this->testOrganizationDelete();
	}	
	
	public function testLocation()
	{
		$this->testLocationProperties();
		$this->testLocationCreate();
		$this->testLocationRead();
		$this->testLocationUpdate();
		$this->testLocationDelete();
	}	
	
	public function testPersonProperties()
	{
		$this->rest->initialize(array('server' => $this->config->item('rest_server').'exposeObj/person/'));

		$this->testTitle('get person properties</h3>');
		$method = 'getProperties';
		
		
		$rest_return = $this->rest->get($method, null, 'serialize');

		$test = false;
		if(is_array($rest_return)) $test = true;
		$this->run($test, 'is_true', 'Is the result an array ?');

		$test = false;
		if(count($rest_return) > 0) $test = true;
		$this->run($test, 'is_true', 'Is the result populated ?');
				
		$this->printReturn($rest_return);
	}

	public function testPersonRead()
	{	
		$this->rest->initialize(array('server' => $this->config->item('rest_server').'exposeObj/person/'));
		
		//calling the methon READ for object person with a filter
		$this->testTitle('read person entry','Sending a request with filter: (uid=10000000)');
		$method = 'read';
		$input = array();
		$input['filter'] = '(uid=10000000)';
		
		$rest_return = $this->rest->get($method, $input, 'serialize');
		$this->getCodeOrigin();
		$this->arrayReturn($method, $rest_return);
		
		//the filter has not been specified then it should return an error
		$this->getCodeOrigin();
		$this->checkNoRestError($method, $rest_return);
		
		$this->getCodeOrigin();
		$this->check200($method, $rest_return);
		
		$this->checkHasData($method, $rest_return);
		
		$this->printReturn($rest_return);


		//----------------------
		
		//calling the methon READ for object person without a filter
		$this->testTitle('read person entries','Sending a request missing "filter". I expect a failure');
		$method = 'read';
		
		
		$rest_return = $this->rest->get($method, null, 'serialize');
		$this->getCodeOrigin();
		$this->arrayReturn($method, $rest_return);
		
		//the filter has not been specified then it should return an error
		$this->getCodeOrigin();
		$this->checkRestError($method, $rest_return);
		
		$this->getCodeOrigin();
		$this->check415($method, $rest_return);

		$this->checkHasNoData($method, $rest_return);
				
		$this->printReturn($rest_return);


		//----------------------
		
		//calling the methon READ for object person with a filter
		$this->testTitle('read person entries','Sending a request with "filter". I expect to get all persons in the storage');
		$method = 'read';
		$input = array();
		$input['filter'] = '(objectClass=*)';
		
		$rest_return = $this->rest->get($method, $input, 'serialize');
		$this->getCodeOrigin();
		$this->arrayReturn($method, $rest_return);
		
		//the filter has not been specified then it should return an error
		$this->getCodeOrigin();
		$this->checkNoRestError($method, $rest_return);
		
		$this->getCodeOrigin();
		$this->check200($method, $rest_return);
		
		$this->checkHasData($method, $rest_return);
		
		$this->printReturn($rest_return);

		
		//----------------------
		
		//read 1 non-existent person
		$this->testTitle('read a non-existent person');
		$method = 'read';
		$input = array();
		$input['filter'] = '(uid=1293813238g9238479832)';
		
		$rest_return = $this->rest->get($method, $input, 'serialize');
		
		
		$this->getCodeOrigin();
		$this->arrayReturn($method, $rest_return);
		
		//the filter has not been specified then it should return an error
		$this->getCodeOrigin();
		$this->checkNoRestError($method, $rest_return);
		
		$this->getCodeOrigin();
		$this->check200($method, $rest_return);
		
		$this->checkHasNoData($method, $rest_return);
		
		$this->printReturn($rest_return);
	}

	public function testPersonCreate() 
	{	
		$this->rest->initialize(array('server' => $this->config->item('rest_server').'exposeObj/person/'));

		//calling the methon CREATE for object person without a filter
		$this->testTitle('create 1 person');
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
		
		
		$rest_return = $this->rest->get($method, $input, 'serialize');
		$this->getCodeOrigin();
		$this->arrayReturn($method, $rest_return);
		
		//the filter has not been specified then it should return an error
		$this->getCodeOrigin();
		$this->checkNoRestError($method, $rest_return);
		
		
		$this->check200($method, $rest_return);
		
		//check uid is a number
		$test = $rest_return['data']['uid'];
		$this->getCodeOrigin();
		echo $this->run($test, 'is_numeric', $method.' - Integer uid', 'Checking if the returned uid is a number');
		
		$this->printReturn($rest_return);
		
		return $rest_return['data']['uid'];
	}

	public function testPersonUpdate()
	{	
		$this->rest->initialize(array('server' => $this->config->item('rest_server').'exposeObj/person/'));

	
	
		
		//calling the methon READ for object person with a filter
		$this->testTitle('get the list of person with givenName starting with Willy*');
		$method = 'read';
		$input = array();
		$input['filter'] = '(givenName=Willy*)';
		
		$rest_return = $this->rest->get($method, $input, 'serialize');
		
		
		$this->getCodeOrigin();
		$this->arrayReturn($method, $rest_return);
		
		//the filter has not been specified then it should return an error
		$this->getCodeOrigin();
		$this->checkNoRestError($method, $rest_return);
		
		
		$this->getCodeOrigin();
		$this->check200($method, $rest_return);
		
		$this->checkHasData($method, $rest_return);
		//$this->printReturn($rest_return);
		
		
		
		//pick uid
		$this->testTitle('pick a random uid from the list');
		$method = 'read';
		if(is_array($rest_return) and !empty($rest_return['data']))
		{
			$uids = array();
			
		 	foreach ($rest_return['data'] as $key => $entry) {
		 		$uids[] = $entry['uid']['0'];
		 	}		
		 	
		 	$uid = $uids[array_rand($uids,1)];
		 	$this->getCodeOrigin();
		 	echo $this->run($uid, 'is_numeric', $method.' - Integer uid', 'Checking if the returned uid is a number');
		 	
		 	if(!$uid) return false;
		}
		
				
	
		//show the choosen entry
		$this->testTitle('show the chosen entry (uid='.$uid.')');
		$method = 'read';
		$input = array();
		$input['filter'] = '(uid='.$uid.')';
		
		$rest_return = $this->rest->get($method, $input, 'serialize');
		
		
		$this->getCodeOrigin();
		$this->arrayReturn($method, $rest_return);
		
		//the filter has been specified then it should not return an error
		$this->getCodeOrigin();
		$this->checkNoRestError($method, $rest_return);
		
		
		$this->getCodeOrigin();
		$this->check200($method, $rest_return);
		
		$this->checkHasData($method, $rest_return);
		
		$this->printReturn($rest_return);
			
		
		
		

		
		//update person 
		$method = 'update';
		$input = array();
		$input['uid'] = $uid;
		$new_displayName = $input['displayName'] = 'This is updated to '.$input['displayName'].' '.rand(100, 999);
		$this->testTitle('update person with uid='.$uid.' : setting the displayName='.$input['displayName']);
		
		$rest_return = $this->rest->get($method, $input, 'serialize');
		
		
		$this->getCodeOrigin();
		$this->arrayReturn($method, $rest_return);

		//the filter has been specified then it should not return an error
		$this->getCodeOrigin();
		$this->checkNoRestError($method, $rest_return);

		
		$this->getCodeOrigin();
		$this->check200($method, $rest_return);
		
		//check uid is a number
 		$uid = $test = $rest_return['data']['uid'];
 		$this->getCodeOrigin();
		echo $this->run($test, 'is_numeric', $method.' - Integer uid', 'Checking if the returned uid is a number');
 
		$this->printReturn($rest_return);
		
		
		
		
		//show the updated entry
		$this->testTitle('show the updated entry (uid='.$uid.')');
		$method = 'read';
		$input = array();
		$input['filter'] = '(uid='.$uid.')';
		
		$rest_return = $this->rest->get($method, $input, 'serialize');
		
		
		$this->getCodeOrigin();
		$this->arrayReturn($method, $rest_return);
		
		//the filter has been specified then it should not return an error
		$this->getCodeOrigin();
		$this->checkNoRestError($method, $rest_return);
		
		
		$this->getCodeOrigin();
		$this->check200($method, $rest_return);
		
		$this->getCodeOrigin();
		$this->checkHasData($method, $rest_return);
		
		//chech that displayName has been modified correctly
		$displayName = $test = $rest_return['data']['0']['displayName'];
		$test = false;
		if($displayName == $new_displayName) $test = true;
		$this->getCodeOrigin();
		echo $this->run($test, 'is_true', $method.' - correct result', 'The attribute displayName was successfully modified');
		
		$this->printReturn($rest_return);

		

		
		

		//update person with wrong filter
		$this->testTitle('update the same person sending an input array without any field but the uid. It means: nothing to update');
			
		$method = 'update';
		$input = array( 'uid' => $uid);
			
		$rest_return = $this->rest->get($method, $input, 'serialize');
			
		
		$this->getCodeOrigin();
		$this->arrayReturn($method, $rest_return);

		$this->getCodeOrigin();
		$this->checkNoRestError($method, $rest_return);
			
		
		$this->getCodeOrigin();
		$this->check200($method, $rest_return);

		
		$this->getCodeOrigin();
		$this->checkHasData($method, $rest_return);
		
		$this->printReturn($rest_return);
				
		
		
		

		
		
		//update person with wrong filter
		$this->testTitle('update the same person sending an input array with a wrong field)');
			
		$method = 'update';
		$input = array( 'uid' => $uid,
						'blablabla' => 'blablabla');
					
		$rest_return = $this->rest->get($method, $input, 'serialize');
					
		
		$this->getCodeOrigin();
		$this->arrayReturn($method, $rest_return);
			
		$this->getCodeOrigin();
		$this->checkRestError($method, $rest_return);
			
		$this->getCodeOrigin();
		$this->check415($method, $rest_return);
		
		$this->getCodeOrigin();
		$this->checkHasNoData($method, $rest_return);
		$this->printReturn($rest_return);
				
		

		
		
		//update person without the filter
		$this->testTitle('try to update a person without setting up the filter uid');
		//$input = $rest_return['data'][array_rand($rest_return['data'])];
		$input=array();
		$input['displayName'] = 'Willy Test';
		$method = 'update';
		$rest_return = $this->rest->get($method, $input, 'serialize');
		
		
		$this->getCodeOrigin();
		$this->arrayReturn($method, $rest_return);
		
		//the filter has not been specified then it should return an error
		$this->getCodeOrigin();
		$this->checkRestError($method, $rest_return);
		
		$this->getCodeOrigin();
		$this->check415($method, $rest_return);

		$this->getCodeOrigin();
		$this->checkHasNoData($method, $rest_return);
		$this->printReturn($rest_return);
		
	
	}
	
	public function testPersonDelete()
	{
		$this->rest->initialize(array('server' => $this->config->item('rest_server').'exposeObj/person/'));

		//calling the methon READ for object person with a filter
		$this->testTitle('delete 1 person taken randomly but having first name Willy*');
		$method = 'read';
		$input = array();
		$input['filter'] = '(givenName=Willy*)';
		
		$rest_return = $this->rest->get($method, $input, 'serialize');
		
		
		$this->getCodeOrigin();
		$this->arrayReturn($method, $rest_return);
		
		//the filter has not been specified then it should return an error
		$this->getCodeOrigin();
		$this->checkNoRestError($method, $rest_return);
		
		
		$this->getCodeOrigin();
		$this->check200($method, $rest_return);
		
		if(is_array($rest_return) and !empty($rest_return['data']))
		{
			$person = $rest_return['data'][array_rand($rest_return['data'])];
		
			//check uid is a number
			$uid = $person['uid']['0'];
			$this->getCodeOrigin();
			echo $this->run($uid, 'is_numeric', $method.' - Integer uid', 'Checking if the returned uid is a number');
		
			//delete person
			$this->testTitle('Delete entry with uid: '.$uid);
			$method = 'delete';
			$input = array();
			$input['uid'] = $uid;
		
			$rest_return = $this->rest->get($method, $input, 'serialize');
		
			
			$this->getCodeOrigin();
			$this->arrayReturn($method, $rest_return);
		
			//the filter has not been specified then it should return an error
			$this->getCodeOrigin();
			$this->checkNoRestError($method, $rest_return);
		
			
			$this->getCodeOrigin();
			$this->check200($method, $rest_return);
				
			$this->getCodeOrigin();
			$this->checkHasNoData($method, $rest_return);
		
			$this->printReturn($rest_return);
			
			
			//check the entry has been really deleted
			$this->testTitle('Check if the entry with uid: '.$uid.' has been really deleted.');
			$method = 'read';
			$input = array();
			$input['filter'] = '(uid='.$uid.')';
			
			$rest_return = $this->rest->get($method, $input, 'serialize');
			$this->getCodeOrigin();
			$this->arrayReturn($method, $rest_return);
			
			//the filter has not been specified then it should return an error
			$this->getCodeOrigin();
			$this->checkNoRestError($method, $rest_return);
			
			$this->getCodeOrigin();
			$this->check200($method, $rest_return);
			
			$this->getCodeOrigin();
			$this->checkHasNoData($method, $rest_return);
			
			$this->printReturn($rest_return);			
		}
		
		
		
		
				
		//delete a non-existent person
		$method = 'delete';
		$input = array();
//		$input['uid'] = '129381@lr91\/3238g9238479832';
		$input['uid'] = '1293813238g9238479832';
		$this->testTitle('Delete a non-existent person having uid: '.$input['uid']);
		
		$rest_return = $this->rest->get($method, $input, 'serialize');
		
		
		$this->getCodeOrigin();
		$this->arrayReturn($method, $rest_return);
		
		$this->getCodeOrigin();
		$this->checkRestError($method, $rest_return);
	
		$this->getCodeOrigin();
		$this->check415($method, $rest_return);
		
		$this->getCodeOrigin();
		$this->checkHasNoData($method, $rest_return);
		
		$this->printReturn($rest_return);

		
		
		//delete a person passing a wrong uid
		$method = 'delete';
		$input = array();
		$input['uid'] = '129381@lr91\/3238g9238479832';
		$this->testTitle('Delete a person passing a wrong uid: '.$input['uid']);
		
		$rest_return = $this->rest->get($method, $input, 'serialize');
		
		
		$this->getCodeOrigin();
		$this->arrayReturn($method, $rest_return);
		
		$this->getCodeOrigin();
		$this->checkRestError($method, $rest_return);
		
		$this->getCodeOrigin();
		$this->check500($method, $rest_return);
		
		$this->getCodeOrigin();
		$this->checkHasNoData($method, $rest_return);
		
		$this->printReturn($rest_return);

		
		
		$method = 'delete';
		$input = array();
		$input['uid'] = array('129381@lr91\/3238g9238479832');
		$this->testTitle('Delete a person passing a wrong uid [array]');
		
		$rest_return = $this->rest->get($method, $input, 'serialize');
		
		
		$this->getCodeOrigin();
		$this->arrayReturn($method, $rest_return);
		
		$this->getCodeOrigin();
		$this->checkRestError($method, $rest_return);
		
		$this->getCodeOrigin();
		$this->check415($method, $rest_return);
		
		$this->getCodeOrigin();
		$this->checkHasNoData($method, $rest_return);
		
		$this->printReturn($rest_return);
		
	}
	
	public function testPersonAssocOrg($loadview = true, $getInfo = false)
	{
		$this->rest->initialize(array('server' => $this->config->item('rest_server').'exposeObj/person/'));
	
		$oid = $this->testOrganizationCreate();
		
		$uid = $this->testPersonCreate();
			
		$this->testTitle('Testing the association of a person to an organization');
		$method = 'associate';
		$input = array();
		$input['uid'] = $uid;
		$input['oid'] = $oid;
		$input['to'] = 'organization';
	
		$rest_return = $this->rest->get($method, $input, 'serialize');
	
		
		$this->getCodeOrigin();
		$this->arrayReturn($method, $rest_return);
	
		//the filter has not been specified but it shouldn't get an error 'cause CE will filter on the UID
		$this->getCodeOrigin();
		$this->checkNoRestError($method, $rest_return);
	
		
		$this->getCodeOrigin();
		$this->check200($method, $rest_return);
		
		$this->getCodeOrigin();
		$this->checkHasData($method, $rest_return);
		
		//check uid is a number
		$test = $rest_return['data']['uid'];
		$this->getCodeOrigin();
		echo $this->run($test, 'is_numeric', $method.' - Integer uid', 'Checking if the returned uid is a number');		
	
		$this->printReturn($rest_return);
		
		
		
		
		
		$this->testTitle('Associates another organization to the same person');
		
		$method = 'associate';
		$this->getCodeOrigin();
		$this->subTestTitle('Creates a new organization');
		$locId = $this->testOrganizationCreate();
		
		//do not remove this. the previous test initialize the rest server to the object location
		$this->rest->initialize(array('server' => $this->config->item('rest_server').'exposeObj/person/'));
		
		$input = array();
		$input['uid'] = $uid;
		$input['oid'] = $oid;
		$input['to'] = 'organization';
		
		$rest_return = $this->rest->get($method, $input, 'serialize');
		
		$this->getCodeOrigin();
		$this->arrayReturn($method, $rest_return);
		
		//the filter has not been specified but it shouldn't get an error 'cause CE will filter on the UID
		$this->getCodeOrigin();
		$this->checkNoRestError($method, $rest_return);
			
		$this->getCodeOrigin();
		$this->check200($method, $rest_return);
		
		$this->getCodeOrigin();
		$this->checkHasData($method, $rest_return);
		
		//check uid is a number
		$test = $rest_return['data']['uid'];
		$this->getCodeOrigin();
		echo $this->run($test, 'is_numeric', $method.' - Integer uid', 'Checking if the returned uid is a number');
		
		$this->printReturn($rest_return);		

		
		
		
		//show the updated entry
		$this->testTitle('show the updated entry (uid='.$uid.')');
		$method = 'read';
		$input = array();
		$input['filter'] = '(uid='.$uid.')';
		
		$rest_return = $this->rest->get($method, $input, 'serialize');
		
		
		$this->getCodeOrigin();
		$this->arrayReturn($method, $rest_return);
		
		//the filter has been specified then it should not return an error
		$this->getCodeOrigin();
		$this->checkNoRestError($method, $rest_return);
		
		
		$this->getCodeOrigin();
		$this->check200($method, $rest_return);
		
		$this->getCodeOrigin();
		$this->checkHasData($method, $rest_return);
		$this->printReturn($rest_return);		
	}
	
	public function testPersonAssocLoc($loadview = true, $getInfo = false)
	{
		
		$this->rest->initialize(array('server' => $this->config->item('rest_server').'exposeObj/person/'));

		$this->getCodeOrigin();
		$this->subTestTitle('Creates a new location');
		$locId = $this->testLocationCreate();

		$this->getCodeOrigin();
		$this->subTestTitle('Creates a new person');
		$uid = $this->testPersonCreate();
			
		$this->testTitle('Testing the association of a person to an organization');
		$method = 'associate';
		$input = array();
		$input['uid'] = $uid;
		$input['locId'] = $locId;
		$input['to'] = 'location';
		
		$rest_return = $this->rest->get($method, $input, 'serialize');		

		$this->getCodeOrigin();
 		
		$this->arrayReturn($method, $rest_return);
	
		//the filter has not been specified but it shouldn't get an error 'cause CE will filter on the UID
		$this->getCodeOrigin();
		$this->checkNoRestError($method, $rest_return);
					
		$this->getCodeOrigin();
		$this->check200($method, $rest_return);
	
		$this->getCodeOrigin();
		$this->checkHasData($method, $rest_return);
		
		//check uid is a number
		$test = $rest_return['data']['uid'];
		$this->getCodeOrigin();
		echo $this->run($test, 'is_numeric', $method.' - Integer uid', 'Checking if the returned uid is a number');
	
		$this->printReturn($rest_return);
		
	
		
		
		$this->testTitle('Associates another location to the same person');
		
		$method = 'associate';
		$this->getCodeOrigin();
		$this->subTestTitle('Creates a new location');
		$locId = $this->testLocationCreate();
		
		//do not remove this. the previous test initialize the rest server to the object location
		$this->rest->initialize(array('server' => $this->config->item('rest_server').'exposeObj/person/'));	
		
		$input = array();
		$input['uid'] = $uid;
		$input['locId'] = $locId;
		$input['to'] = 'location';
		
		$rest_return = $this->rest->get($method, $input, 'serialize');		
		
		
		
		$this->getCodeOrigin();
		$this->arrayReturn($method, $rest_return);
		
		//the filter has not been specified but it shouldn't get an error 'cause CE will filter on the UID
		$this->getCodeOrigin();
		$this->checkNoRestError($method, $rest_return);
			
		$this->getCodeOrigin();
		$this->check200($method, $rest_return);
		
		$this->getCodeOrigin();
		$this->checkHasData($method, $rest_return);
		
		//check uid is a number
		$test = $rest_return['data']['uid'];
		$this->getCodeOrigin();
		echo $this->run($test, 'is_numeric', $method.' - Integer uid', 'Checking if the returned uid is a number');
		
		$this->printReturn($rest_return);
		
		
		
		//show the updated entry
		$this->testTitle('show the updated entry (uid='.$uid.')');
		$method = 'read';
		$input = array();
		$input['filter'] = '(uid='.$uid.')';
		
		$rest_return = $this->rest->get($method, $input, 'serialize');
		
		
		$this->getCodeOrigin();
		$this->arrayReturn($method, $rest_return);
		
		//the filter has been specified then it should not return an error
		$this->getCodeOrigin();
		$this->checkNoRestError($method, $rest_return);
		
		
		$this->getCodeOrigin();
		$this->check200($method, $rest_return);
		
		$this->getCodeOrigin();
		$this->checkHasData($method, $rest_return);
		$this->printReturn($rest_return);		
	}	
		

	public function testOrganizationProperties()
	{
		$this->rest->initialize(array('server' => $this->config->item('rest_server').'exposeObj/organization/'));	

		$this->testTitle('get organization properties</h3>');

		$method = 'getProperties';
				
		$rest_return = $this->rest->get($method, null, 'serialize');
		
		$test = false;
		if(is_array($rest_return)) $test = true;
		$this->run($test, 'is_true', 'Is the result an array ?');
		
		$test = false;
		if(count($rest_return) > 0) $test = true;
		$this->run($test, 'is_true', 'Is the result populated ?');
		
		$this->printReturn($rest_return);		
	}

	public function testOrganizationRead()
	{
		$this->rest->initialize(array('server' => $this->config->item('rest_server').'exposeObj/organization/'));

		
		
		
		//calling the methon READ for object organization with a filter
		$this->testTitle('Org: Reading entry with oid: 10000000');
		$method = 'read';
		$input = array();
		$input['filter'] = '(oid=10000000)';
		
		$rest_return = $this->rest->get($method, $input, 'serialize');
		$this->getCodeOrigin();
		$this->arrayReturn($method, $rest_return);
		
		//the filter has not been specified then it should return an error
		$this->getCodeOrigin();
		$this->checkNoRestError($method, $rest_return);
		
		
		$this->getCodeOrigin();
		$this->check200($method, $rest_return);

		$this->getCodeOrigin();
		$this->checkHasData($method, $rest_return);
		$this->printReturn($rest_return);

		
		
		
		//calling the methon READ for object organization with a filter
		$this->testTitle('read organization entries','Sending a request with "filter". I expect to get all organizations in the storage');
		$method = 'read';
		$input = array();
		$input['filter'] = '(objectClass=*)';
		
		$rest_return = $this->rest->get($method, $input, 'serialize');
		$this->getCodeOrigin();
		$this->arrayReturn($method, $rest_return);
		
		//the filter has not been specified then it should return an error
		$this->getCodeOrigin();
		$this->checkNoRestError($method, $rest_return);
		
		
		$this->getCodeOrigin();
		$this->check200($method, $rest_return);
		
		$this->getCodeOrigin();
		$this->checkHasData($method, $rest_return);
		
		$this->printReturn($rest_return);		
		
	
		
		
		//calling the methon READ for object organization without a filter
		$this->testTitle('read organization entries','Sending a request missing "filter". I expect a failure');
		$method = 'read';
		
		
		$rest_return = $this->rest->get($method, null, 'serialize');
		$this->getCodeOrigin();
		$this->arrayReturn($method, $rest_return);
		
		//the filter has not been specified then it should return an error
		$this->getCodeOrigin();
		$this->checkRestError($method, $rest_return);
		
		
		$this->getCodeOrigin();
		$this->check415($method, $rest_return);
		
		$this->getCodeOrigin();
		$this->checkHasNoData($method, $rest_return);
		$this->printReturn($rest_return);
	
	
		//-------------------------
		
	
		
	}

	public function testOrganizationCreate()
	{
		$this->rest->initialize(array('server' => $this->config->item('rest_server').'exposeObj/organization/'));
			
		//calling the methon CREATE for object organization without a filter
		$this->testTitle('create 1 organization');
		$method = 'create';
		$input = array();
		$random = rand(999,9999);
		$orgname = 'ACME_'.$random;
		
		//required fields
		$input['enabled'] = 'TRUE';
		$input['entryCreatedBy'] = 'dam';
		$input['o'] = $orgname;
		//oid is automatically set, so not needed
		
		
		$rest_return = $this->rest->get($method, $input, 'serialize');
		$this->getCodeOrigin();
		$this->arrayReturn($method, $rest_return);
		
		//the filter has not been specified then it should return an error
		$this->getCodeOrigin();
		$this->checkNoRestError($method, $rest_return);
		
		
		$this->check200($method, $rest_return);
		
		//check uid is a number
		$test = $rest_return['data']['oid'];
		$this->getCodeOrigin();
		echo $this->run($test, 'is_numeric', $method.' - Integer oid', 'Checking if the returned uid is a number');
		
		$this->printReturn($rest_return);	

		return $rest_return['data']['oid'];
	}
		
	public function testOrganizationUpdate()
	{
		$this->rest->initialize(array('server' => $this->config->item('rest_server').'exposeObj/organization/'));		
		
		//calling the methon READ for object organization with a filter
		$this->testTitle('update 1 organization taken randomly but starting with Acme*');
		$method = 'read';
		$input = array();
		$input['filter'] = '(o=Acme*)';
		
		$rest_return = $this->rest->get($method, $input, 'serialize');
		
		
		$this->getCodeOrigin();
		$this->arrayReturn($method, $rest_return);
		
		//the filter has not been specified then it should return an error
		$this->getCodeOrigin();
		$this->checkNoRestError($method, $rest_return);
		
		
		$this->getCodeOrigin();
		$this->check200($method, $rest_return);
		
		$this->getCodeOrigin();
		$this->checkHasData($method, $rest_return);
		$this->printReturn($rest_return);
		
		
		if(is_array($rest_return) and !empty($rest_return['data']))
		{
			$organization = $rest_return['data'][array_rand($rest_return['data'])];
			$organization['o'] = 'Acme Test';
			$method = 'update';
			$rest_return = $this->rest->get($method, $organization, 'serialize');
				
			$this->getCodeOrigin();
			$this->arrayReturn($method, $rest_return);
		
			//the filter has not been specified then it should return an error
			$this->getCodeOrigin();
			$this->checkNoRestError($method, $rest_return);
		
			 
			$this->getCodeOrigin();
			$this->check200($method, $rest_return);
		
			$this->getCodeOrigin();
			$this->checkHasData($method, $rest_return);
			
			//check uid is a number
			$oid = $test = $rest_return['data']['oid'];
			$this->getCodeOrigin();
			echo $this->run($test, 'is_numeric', $method.' - Integer oid', 'Checking if the returned oid is a number');
		
			$this->printReturn($rest_return);
		
			
			
			
			//gets the updated organization
			$method = 'read';
			$input = array();
			$input['filter'] = '(oid='.$oid.')';
		
			$rest_return = $this->rest->get($method, $input, 'serialize');
		
			
			$this->getCodeOrigin();
			$this->arrayReturn($method, $rest_return);
		
			//the filter has not been specified then it should return an error
			$this->getCodeOrigin();
			$this->checkNoRestError($method, $rest_return);
		
			
			$this->getCodeOrigin();
			$this->check200($method, $rest_return);
		
			
			$this->getCodeOrigin();
			$this->checkHasData($method, $rest_return);
			$this->printReturn($rest_return);
		}
	}

	public function testOrganizationDelete()
	{
		$this->rest->initialize(array('server' => $this->config->item('rest_server').'exposeObj/organization/'));	
	
		//calling the methon READ for object organization with a filter
		$this->testTitle('delete an organization taken randomly but starting with Acme*');
		$this->subTestTitle('gets all the organizations: (objectClass=*)');
		$method = 'read';
		$input = array();
		$input['filter'] = '(objectClass=*)';
		
		$rest_return = $this->rest->get($method, $input, 'serialize');		

		
		$this->getCodeOrigin();
		$this->arrayReturn($method, $rest_return);
				
		//the filter has not been specified then it should return an error
		$this->getCodeOrigin();
		$this->checkNoRestError($method, $rest_return);
		
		$this->getCodeOrigin();
		$this->check200($method, $rest_return);

		$this->getCodeOrigin();
		$this->checkHasData($method, $rest_return);
		
		
	
		if(is_array($rest_return) and !empty($rest_return['data']))
		{
			$this->subTestTitle('gets organization oid');
			$organization = $rest_return['data'][array_rand($rest_return['data'])];
			//check oid is a number
			$oid = $test = $organization['oid'];
			$this->getCodeOrigin();
			echo $this->run($test, 'is_numeric', $method.' - Integer oid', 'Checking if the returned oid is a number');
	
			
			
			
		
			//gets the organization updated
			$this->subTestTitle('deletes the organization with oid: '.$oid);
			$method = 'delete';
			$input = array();
			$input['oid'] = $oid;
		
			$rest_return = $this->rest->get($method, $input, 'serialize');
		
			
			$this->getCodeOrigin();
			$this->arrayReturn($method, $rest_return);
		
			//the filter has not been specified then it should return an error
			$this->getCodeOrigin();
			$this->checkNoRestError($method, $rest_return);
		
			
			$this->getCodeOrigin();
			$this->check200($method, $rest_return);
		
			//check return is TRUE
			$this->getCodeOrigin();
			$this->checkHasNoData($method, $rest_return);		
			$this->printReturn($rest_return);
			
			
			
			
			$this->subTestTitle('checks if the organization with oid: '.$oid.' has been deleted.');
			$method = 'read';
			$input = array();
			$input['filter'] = '(oid='.$oid.')';
			
			$rest_return = $this->rest->get($method, $input, 'serialize');
			
			
			$this->getCodeOrigin();
			$this->arrayReturn($method, $rest_return);
			
			//the filter has not been specified then it should return an error
			$this->getCodeOrigin();
			$this->checkNoRestError($method, $rest_return);
			
			$this->getCodeOrigin();
			$this->check200($method, $rest_return);
			
			$this->getCodeOrigin();
			$this->checkHasNoData($method, $rest_return);			
		}
	}

	public function testOrganizationAssocLoc($loadview = true, $getInfo = false)
	{
		$this->rest->initialize(array('server' => $this->config->item('rest_server').'exposeObj/organization/'));	
		
		$this->getCodeOrigin();
		$this->subTestTitle('Creates a new location');
		$locId = $this->testLocationCreate();
		
		$this->getCodeOrigin();
		$this->subTestTitle('Creates a new organization');
		$oid = $this->testOrganizationCreate();
		
			
		$this->testTitle('Testing the association of an organization to a location');
		$method = 'associate';
		$input = array();
		$input['locId'] = $locId;
		$input['to'] = 'location';
		$input['oid'] = $oid;
	
		$rest_return = $this->rest->get($method, $input, 'serialize');
	
		
		$this->getCodeOrigin();
		$this->arrayReturn($method, $rest_return);
	
		//the filter has not been specified but it shouldn't get an error 'cause CE will filter on the UID
		$this->getCodeOrigin();
		$this->checkNoRestError($method, $rest_return);
	
		
		$this->getCodeOrigin();
		$this->check200($method, $rest_return);
	
		$this->getCodeOrigin();
		$this->checkHasData($method, $rest_return);
				
		$test = $rest_return['data']['oid'];
		$this->getCodeOrigin();
		echo $this->run($test, 'is_numeric', $method.' - Integer oid', 'Checking if the returned oid is a number');
	
		$this->printReturn($rest_return);
	}
	
		
	public function testLocationProperties()
	{		
		$this->rest->initialize(array('server' => $this->config->item('rest_server').'exposeObj/location/'));
		
		$this->testTitle('get location properties</h3>');
		$method = 'getProperties';
		
		
		$rest_return = $this->rest->get($method, null, 'serialize');
		
		$test = false;
		if(is_array($rest_return)) $test = true;
		$this->run($test, 'is_true', 'Is the result an array ?');
		
		$test = false;
		if(count($rest_return) > 0) $test = true;
		$this->run($test, 'is_true', 'Is the result populated ?');
		
		$this->printReturn($rest_return);		
	}
	
	public function testLocationRead()
	{
		$this->rest->initialize(array('server' => $this->config->item('rest_server').'exposeObj/location/'));

		
		
		$this->testTitle('read location entry','Sending a request with filter: (locId=10000000)');
		$method = 'read';
		$input = array();
		$input['filter'] = '(locId=10000000)';
		
		$rest_return = $this->rest->get($method, $input, 'serialize');
		
		
		$this->getCodeOrigin();
		$this->arrayReturn($method, $rest_return);
		
		//the filter has not been specified then it should return an error
		$this->getCodeOrigin();
		$this->checkNoRestError($method, $rest_return);
		
		
		$this->getCodeOrigin();
		$this->check200($method, $rest_return);
		
		$this->getCodeOrigin();
		$this->checkHasData($method, $rest_return);
		$this->printReturn($rest_return);
		
		

		//calling the methon READ for object location with a filter
		$this->testTitle('read location entries','Sending a request with "filter". I expect to get all locations in the storage');
		$method = 'read';
		$input = array();
		$input['filter'] = '(objectClass=*)';
		
		$rest_return = $this->rest->get($method, $input, 'serialize');
		
		$this->getCodeOrigin();
		$this->arrayReturn($method, $rest_return);
		
		//the filter has not been specified then it should return an error
		$this->getCodeOrigin();
		$this->checkNoRestError($method, $rest_return);
		
		
		$this->getCodeOrigin();
		$this->check200($method, $rest_return);

		$this->getCodeOrigin();
		$this->checkHasData($method, $rest_return);		
		$this->printReturn($rest_return);		

		
		
		
		//calling the methon READ for object location without a filter
		$this->testTitle('read location entries','Sending a request missing "filter". I expect a failure');
		$method = 'read';
		
		
		$rest_return = $this->rest->get($method, null, 'serialize');
		$this->getCodeOrigin();
		$this->arrayReturn($method, $rest_return);
		
		//the filter has not been specified then it should return an error
		$this->getCodeOrigin();
		$this->checkRestError($method, $rest_return);
		
		
		$this->getCodeOrigin();
		$this->check415($method, $rest_return);
		
		$this->getCodeOrigin();
		$this->checkHasNoData($method, $rest_return);		
		$this->printReturn($rest_return);
		
		//----------------------
		

	}
	
	public function testLocationCreate()
	{
		$this->rest->initialize(array('server' => $this->config->item('rest_server').'exposeObj/location/'));	

		//calling the methon CREATE for object location without a filter
		$this->testTitle('creates a location');
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
		
		
		$rest_return = $this->rest->get($method, $input, 'serialize');
		$this->getCodeOrigin();
		$this->arrayReturn($method, $rest_return);
		
		//the filter has not been specified then it should return an error
		$this->getCodeOrigin();
		$this->checkNoRestError($method, $rest_return);
		
		
		$this->getCodeOrigin();
		$this->check200($method, $rest_return);
		
		//check oid is a number
		$test = $rest_return['data']['locId'];
		$this->getCodeOrigin();
		echo $this->run($test, 'is_numeric', $method.' - Integer locId', 'Checking if the returned locId is a number');
		
		$this->printReturn($rest_return);
		
		return $rest_return['data']['locId'];
	}

	public function testLocationUpdate()
	{
		$this->rest->initialize(array('server' => $this->config->item('rest_server').'exposeObj/location/'));	
		
		//calling the methon READ for object location with a filter
		$this->testTitle('update a location taken randomly but starting with MyDescription*');
		
		$this->subTestTitle('gets the locations list');
		$method = 'read';
		$input = array();
		$input['filter'] = '(locDescription=MyDescription*)';
		
		$rest_return = $this->rest->get($method, $input, 'serialize');
		
		
		$this->getCodeOrigin();
		$this->arrayReturn($method, $rest_return);
		
		//the filter has not been specified then it should return an error
		$this->getCodeOrigin();
		$this->checkNoRestError($method, $rest_return);
		
		
		$this->getCodeOrigin();
		$this->check200($method, $rest_return);
		
		$this->getCodeOrigin();
		$this->checkHasData($method, $rest_return);
		

		if(is_array($rest_return) and !empty($rest_return['data']))
		{
			$location = $rest_return['data'][array_rand($rest_return['data'])];
			$this->subTestTitle('updates the location');
			$location['locDescription'] = 'MyDescription TEST';
			$method = 'update';
			$rest_return = $this->rest->get($method, $location, 'serialize');
		
			
			$this->getCodeOrigin();
			$this->arrayReturn($method, $rest_return);
		
			//the filter has not been specified then it should return an error
			$this->getCodeOrigin();
			$this->checkNoRestError($method, $rest_return);
		
			
			$this->getCodeOrigin();
			$this->check200($method, $rest_return);
		
			$this->getCodeOrigin();
			$this->checkHasData($method, $rest_return);
				
			//check uid is a number
			$locId = $test = $rest_return['data']['locId'];
			$this->getCodeOrigin();
			echo $this->run($test, 'is_numeric', $method.' - Integer locId', 'Checking if the returned locId is a number');
		
			$this->printReturn($rest_return);


			//gets the location updated
			$this->subTestTitle('gets the updated location');
			$method = 'read';
			$input = array();
			$input['filter'] = '(locId='.$locId.')';
		
			$rest_return = $this->rest->get($method, $input, 'serialize');
		
			
			$this->getCodeOrigin();
			$this->arrayReturn($method, $rest_return);
		
			//the filter has not been specified then it should return an error
			$this->getCodeOrigin();
			$this->checkNoRestError($method, $rest_return);
		
			
			$this->getCodeOrigin();
			$this->check200($method, $rest_return);

			$this->getCodeOrigin();
			$this->checkHasData($method, $rest_return);
			$this->printReturn($rest_return);
		}
	}
	
	public function testLocationDelete()
	{
		$this->rest->initialize(array('server' => $this->config->item('rest_server').'exposeObj/location/'));	

		
		
		
		//calling the methon READ for object location with a filter
		$this->testTitle('deletes a location taken randomly but starting with MyDescription*');
		$method = 'read';
		$input = array();
		$input['filter'] = '(locDescription=MyDescription*)';
		
		$rest_return = $this->rest->get($method, $input, 'serialize');
		
		if(is_array($rest_return) and !empty($rest_return['data']))
		{
			$location = $rest_return['data'][array_rand($rest_return['data'])];
			
			$this->subTestTitle('Picks a  locId: '.$locId);
			$locId = $test = $location['locId'];
			$this->getCodeOrigin();
			echo $this->run($test, 'is_numeric', $method.' - Integer locId', 'Checking if the returned locId is a number');
		
		
			$this->subTestTitle('Deletes the entry with locId: '.$locId);
			$method = 'delete';
			$input = array();
			$input['locId'] = $locId;
		
			$rest_return = $this->rest->get($method, $input, 'serialize');
		
			
			$this->getCodeOrigin();
			$this->arrayReturn($method, $rest_return);
		
			//the filter has not been specified then it should return an error
			$this->getCodeOrigin();
			$this->checkNoRestError($method, $rest_return);
		

			$this->getCodeOrigin();
			$this->check200($method, $rest_return);
		
			$this->getCodeOrigin();
			$this->checkHasNoData($method, $rest_return);
			$this->printReturn($rest_return);
		}
	}
		
	public function testContact()
	{
		$this->rest->initialize(array('server' => $this->config->item('rest_server').'exposeObj/contact/'));
		
		
		//calling the methon READ for object contact with a filter
		$this->testTitle('Testing CONTACT: get contacts using this filter: (|(givenName=Willy*)(o=A*))');
		$method = 'read';
		$input = array();
		$input['filter'] = '(|(givenName=Willy*)(o=A*))';
		
		$rest_return = $this->rest->get($method, $input, 'serialize');
		
		$this->getCodeOrigin();
		$this->arrayReturn($method, $rest_return);
		
		//the filter has not been specified then it should return an error
		$this->getCodeOrigin();
		$this->checkNoRestError($method, $rest_return);
		
		$this->getCodeOrigin();
		$this->check200($method, $rest_return);

		$this->getCodeOrigin();
		$this->checkHasData($method, $rest_return);
		$this->printReturn($rest_return);

		
		
		//calling the methon READ for object contact with a filter
		$this->testTitle('Testing CONTACT: get contact using this filter: array(|(givenName=Willy*)(o=A*))');
		$method = 'read';
		$input = array();
		$input['filter'] = 'array(|(givenName=Willy*)(o=A*))';
		
		$rest_return = $this->rest->get($method, $input, 'serialize');
		
		$this->getCodeOrigin();
		$this->arrayReturn($method, $rest_return);
		
		//the filter has not been specified then it should return an error
		$this->getCodeOrigin();
		$this->checkRestError($method, $rest_return);
		
		$this->getCodeOrigin();
		$this->check415($method, $rest_return);

		$this->getCodeOrigin();
		$this->checkHasNoData($method, $rest_return);
		$this->printReturn($rest_return);		
	}
}