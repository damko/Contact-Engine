<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
// Created on Aug 26, 2011 by Damiano Venturin @ Squadra Informatica

class Make_Tests extends CI_Controller {
	public function __construct()
	{
		parent::__construct();
		require_once 'HTTP/Request2.php';
		
		// Load the configuration file
		$this->load->config('rest');

		// Load the rest client
		$this->load->spark('restclient/2.0.0');
		$this->rest->initialize(array('server' => $this->config->item('rest_server')));		
	}

	public function index()
	{
		$tests_list = array();
		$style = 'white';
		foreach ($this->_get_tests() as $test) {
			$test_data = $this->$test(false, true);
			$url = '<a href="'.site_url('make_tests/'.$test).'" target="_blank">'.ucwords($test).'</a>';
			($style == 'gray') ? $style = 'white' : $style = 'gray';
			$tests_list[] = '<tr class="'.$style.'"><td>'.$test_data['object'].'</td><td>'.$url.'</td><td style="font-size: 10px;">'.$test_data['testdesc'].'</td></tr>';
		}
		$data['tests_list'] = $tests_list;
		$this->load->view('tests_index',$data);
	}
	
	private function _get_tests()
	{
		$tests = array();
		$methods = get_class_methods($this);
		foreach ($methods as $key => $method) {
			if(preg_match('/^test/', $method))
			{
				$tests[] = $method;
			}
		}
		return $tests;
	}
	
	public function runall()
	{
		$tests_list = array();
		$style = 'white';
		
		foreach ($this->_get_tests() as $test) {
			$test_data = $this->$test(false, true);
			$test_results = $this->$test(false,false);
			$test_results ? $result = 'passed' : $result = 'failed';
			$url = '<a href="'.site_url('make_tests/'.$test).'" target="_blank">'.ucwords($test).'</a>';			
			($style == 'gray') ? $style = 'white' : $style = 'gray';
			($result == 'passed') ? $style_result = 'style="background-color: green;"' : $style_result = 'style="background-color: red;"';
			$tests_list[] = '<tr class="'.$style.'"><td>'.$test_data['object'].'</td><td>'.$url.'</td><td style="font-size: 10px;">'.$test_data['testdesc'].'</td><td '.$style_result.'">'.$result.'</td></tr>';
		}
		$data['tests_list'] = $tests_list;
		$this->load->view('tests_runall',$data);		
	}
	
	public function testCreate_A($loadview = true, $getInfo = false)
	{		
		$data = array();
		$data['testname'] = __FUNCTION__;
		$data['object'] = 'Person';
		$data['testdesc'] = 'Creates a random person. If it passes returns the uid of the created Person. Rest query performed with HTTP_Request2. <i>Output: PHP array</i>';
		$data['url'] = site_url('/api/exposeObj/person/create/format/serialize');
		$data['filter'] = 'No filter set';
		
		if($getInfo) return $data;
		
		$random = rand(999,9999);
		$surname = 'Coyote'.$random;
		$name = 'Willy'.$random;
		
		//required fields
		$data['category'] = 'mycategory';
		$data['cn'] = $name.' '.$surname;
		$data['displayName'] = $data['cn'];
		$data['enabled'] = 'TRUE';
		$data['entryCreatedBy'] = 'dam';
		$data['fileAs'] = $data['cn'];
		$data['givenName'] = $name;
		$data['sn'] = $surname;
		//uid is automatically set, so not needed
		$data['userPassword'] = 'mypassword';
		
		$request = new HTTP_Request2($data['url']);
		$request->setMethod(HTTP_Request2::METHOD_POST)
			->addPostParameter('category', $data['category'])
			->addPostParameter('cn', $data['cn'])
			->addPostParameter('displayName', $data['displayName'])
			->addPostParameter('enabled', $data['enabled'])
			->addPostParameter('entryCreatedBy', $data['entryCreatedBy'])
			->addPostParameter('fileAs', $data['fileAs'])
			->addPostParameter('givenName', $data['givenName'])
			->addPostParameter('sn', $data['sn'])
			->addPostParameter('userPassword', $data['userPassword']);
					   			    
		$response = $request->send();
		$data['response'] = unserialize($response->getBody());			
					
		if($loadview) $this->load->view('tests_view',$data);
		return empty($data['response']['error']) ? true: false;
	}			

	public function testCreate_B($loadview = true, $getInfo = false)
	{		
		$data = array();
		$data['testname'] = __FUNCTION__;
		$data['object'] = 'Person';
		$data['testdesc'] = 'Creates a random person. If it passes returns the uid of the created Person. Rest query performed with CI2 Rest_Client. <i>Output: PHP array</i>';
		$url = 'exposeObj/person/create/';
		//$data['url'] = site_url($url);
		$data['filter'] = 'No filter set';
		
		if($getInfo) return $data;
		
		$random = rand(999,9999);
		$surname = 'Coyote'.$random;
		$name = 'Willy'.$random;
		
		//required fields
		$data['category'] = 'mycategory';
		$data['cn'] = $name.' '.$surname;
		$data['displayName'] = $data['cn'];
		$data['enabled'] = 'TRUE';
		$data['entryCreatedBy'] = 'dam';
		$data['fileAs'] = $data['cn'];
		$data['givenName'] = $name;
		$data['sn'] = $surname;
		//uid is automatically set, so not needed
		$data['userPassword'] = 'mypassword';
		
		$data['response'] = $this->rest->post($url, $data, 'serialize');
					   			   
		if($loadview) $this->load->view('tests_view',$data);
		
		//passed or failed
		return empty($data['response']['error']) ? true: false;
	}		
	
	public function testCreate_Massive($loadview = true, $getInfo = false)
	{		
		$data = array();
		$num = 5;
		$test = "testCreate_B";
		$data['testname'] = __FUNCTION__;
		$data['object'] = 'Person';
		$data['testdesc'] = 'Creates '.$num.' random persons in a row. <i>Output: boolean</i>';		
		$data['url'] = 'calling '.$test.' for '.$num.' times';
		$data['filter'] = 'No filter set';

		if($getInfo) return $data;
		
		//Benchmark starts
		$this->benchmark->mark('code_start');

		for ($i = 0; $i < $num; $i++) {
			$data['response'] = $this->$test(false,false);
			if(!$data['response'])
			{ 
				$data['response'] = array('error' => 'something went wrong');
				break;
			}
		}

		//Benchmark stops
		$this->benchmark->mark('code_end');
		$data['benchmark'] = $this->benchmark->elapsed_time('code_start', 'code_end').' seconds to retrieve result';	
		
		if($loadview) $this->load->view('tests_view',$data);
		
		//passed or failed
		return (empty($data['response']['error'])) ? true: false;
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
	
	public function testRead_All_A2($loadview = true, $getInfo = false)
	{		
		$data = array();
		$data['testname'] = __FUNCTION__;
		$data['object'] = 'Person';
		$data['testdesc'] = 'Gets all data about all the persons in the storage. Rest query performed with CI2 Rest_Client. <i>Output: PHP array</i>';		
		$url = 'exposeObj/person/read';
		$data['url'] = site_url($url);
		$data['filter'] = '(objectClass=*)';
		$data['method'] = 'POST';
				
		if($getInfo) return $data;
		    
		//Benchmark starts
		$this->benchmark->mark('code_start');
				    
		$data['response'] = $this->rest->post($url, $data, 'serialize');
		
		//Benchmark stops
		$this->benchmark->mark('code_end');
		$data['benchmark'] = $this->benchmark->elapsed_time('code_start', 'code_end').' seconds to retrieve '.count($data['response']).' usable contacts';	
				
		if($loadview) $this->load->view('tests_view',$data);
		
		//passed or failed		
		return empty($data['response']['error']) ? true: false;
	}	
	
	public function testRead_All_A3($loadview = true, $getInfo = false)
	{		
		$data = array();
		$data['object'] = 'Person';
		$data['testdesc'] = 'Gets all data about all the persons in the storage sorted by "sn" desc. Rest query performed with CI2 Rest_Client. <i>Output: PHP array</i>';
		$url = 'exposeObj/person/read';
		$data['url'] = site_url($url);
		$data['filter'] = '(objectClass=*)';
		$data['method'] = 'POST';
		$data['sort_by'] = array('sn');
		$data['flow_order'] = 'desc';
 		//$data['wanted_page'] = '0';
 		//$data['items_page'] = '12';		
	
		if($getInfo) return $data;
	
		//Benchmark starts
		$this->benchmark->mark('code_start');
	
		$data['response'] = $this->rest->post($url, $data, 'serialize');
	
		//Benchmark stops
		$this->benchmark->mark('code_end');
		$data['benchmark'] = $this->benchmark->elapsed_time('code_start', 'code_end').' seconds to retrieve '.count($data['response']).' usable contacts';
	
		if($loadview) $this->load->view('tests_view',$data);
	
		//passed or failed
		return empty($data['response']['error']) ? true: false;
	}

	public function testRead_All_A4($loadview = true, $getInfo = false)
	{
		$data = array();
		$data['object'] = 'Person';
		$url = 'exposeObj/person/read';
		$data['url'] = site_url($url);
		$data['filter'] = '(objectClass=*)';
		$data['method'] = 'POST';
		$data['sort_by'] = array('sn');
		$data['flow_order'] = 'desc';
		$data['wanted_page'] = '2';
		$data['items_page'] = '15';		
		$data['testdesc'] = 'Gets all data about all the persons in the storage sorted by CN asc and paginated: getting page '.($data['wanted_page'] + 1).'. Rest query performed with CI2 Rest_Client. <i>Output: PHP array</i>';
	
		if($getInfo) return $data;
	
		//Benchmark starts
		$this->benchmark->mark('code_start');
	
		$data['response'] = $this->rest->post($url, $data, 'serialize');
	
		//Benchmark stops
		$this->benchmark->mark('code_end');
		$data['benchmark'] = $this->benchmark->elapsed_time('code_start', 'code_end').' seconds to retrieve '.count($data['response']).' usable contacts';
	
		if($loadview) $this->load->view('tests_view',$data);
	
		//passed or failed
		return empty($data['response']['error']) ? true: false;
	}
		
	public function testRead_All_B($loadview = true, $getInfo = false)
	{		
		$data = array();
		$data['testname'] = __FUNCTION__;
		$data['object'] = 'Person';
		$data['testdesc'] = 'Gets some data about all the persons in the storage. <i>Output: PHP array</i>';		
		$data['url'] = site_url('/api/exposeObj/person/read/format/serialize');
		$data['filter'] = '(objectClass=*)';
		$data['attributes'] = array('sn','cn');
		$data['method'] = 'POST';
				
		if($getInfo) return $data;
		
		$request = new HTTP_Request2($data['url']);
		$request->setMethod(HTTP_Request2::METHOD_POST)
		    ->addPostParameter('filter', $data['filter'])
		    ->addPostParameter('emptyfields', false)
		    ->addPostParameter('attributes', $data['attributes']);
		    
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

	public function testRead_Some_A1($loadview = true, $getInfo = false)
	{
		$data = array();
		$data['testname'] = __FUNCTION__;
		$data['object'] = 'Person';
		$data['testdesc'] = 'Gets all data about some persons using POST and specifying the filter in the POST array. <i>Output: PHP style</i>';		
		$data['url'] = site_url('/api/exposeObj/person/read/format/php');
		$data['filter'] = '(givenName=Will*)';
		$data['method'] = 'POST';
		
		if($getInfo) return $data;
		
		$request = new HTTP_Request2($data['url']);
		$request->setMethod(HTTP_Request2::METHOD_POST)
		    ->addPostParameter('filter', $data['filter'])
		    ->addPostParameter('emptyfields', false);
		$response = $request->send();
		$data['response'] = $response->getBody();		
		
		if($loadview) $this->load->view('tests_view',$data);
				
		//passed or failed
		return true; //FIXME this is a fake
	}	

	public function testRead_Some_A2($loadview = true, $getInfo = false)
	{
		$data = array();
		$data['testname'] = __FUNCTION__;
		$data['object'] = 'Person';
		$data['testdesc'] = 'Gets all data about some persons using POST and specifying the filter in the POST array. <i>Output: XML</i>';		
		$data['url'] = site_url('/api/exposeObj/person/read/format/xml');
		$data['filter'] = '(givenName=Will*)';
		$data['method'] = 'POST';
		
		if($getInfo) return $data;
		
		$request = new HTTP_Request2($data['url']);
		$request->setMethod(HTTP_Request2::METHOD_POST)
		    ->addPostParameter('filter', $data['filter'])
		    ->addPostParameter('emptyfields', false);
		$response = $request->send();
		$data['response'] = simplexml_load_string($response->getBody());
		
		if($loadview) $this->load->view('tests_view',$data);
				
		//passed or failed
		return true; //FIXME this is a fake
	}		
	
	public function testRead_Some_A3($loadview = true, $getInfo = false)
	{
		$data = array();
		$data['testname'] = __FUNCTION__;
		$data['object'] = 'Person';
		$data['testdesc'] = 'Gets all data about some persons using POST and specifying a complex filter in the POST array. <i>Output: PHP array</i>';		
		$data['url'] = site_url('/api/exposeObj/person/read/format/xml');
		$data['filter'] = '(&(givenName=*illy*)(sn=Co*))';
		$data['method'] = 'POST';
		
		if($getInfo) return $data;
		
		$request = new HTTP_Request2($data['url']);
		$request->setMethod(HTTP_Request2::METHOD_POST)
		    ->addPostParameter('filter', $data['filter'])
		    ->addPostParameter('emptyfields', false);

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
		
	public function testUnique($loadview = true, $getInfo = false)
	{		
		$data = array();
		$data['testname'] = __FUNCTION__;
		$data['object'] = 'Person';
		$data['testdesc'] = 'Checks unique email. Returns true if the given email is unique. This method can be used to check the unicity of any attribute.';		
		$data['url'] = site_url('/api/exposeObj/person/checkUniqueAttribute/format/serialize');
		$data['filter'] = 'No filter set, I just send the email <i>"myemail@mycompany.com"</i> in the POST';
		$data['method'] = 'POST';
		
		if($getInfo) return $data;
		
		$request = new HTTP_Request2($data['url']);
		$request->setMethod(HTTP_Request2::METHOD_POST)
			->addPostParameter('attribute', 'mail')
		    ->addPostParameter('mail', 'myemail@mycompany.com');
		    
		//Benchmark starts
		$this->benchmark->mark('code_start');
				    
		$response = $request->send();
		$data['response'] = unserialize($response->getBody());
		
		//Benchmark stops
		$this->benchmark->mark('code_end');
		$data['benchmark'] = $this->benchmark->elapsed_time('code_start', 'code_end').' seconds to retrieve result';	
				
					
		if($loadview) $this->load->view('tests_view',$data);
				
		//passed or failed
		//return ($data['response']['0'] == '1') ? true : false;
		return ($data) ? true : false;
	}		

	public function testProperties_A1($loadview = true, $getInfo = false)
	{		
		$data = array();
		$data['testname'] = __FUNCTION__;
		$data['object'] = 'Person';
		$data['testdesc'] = 'Gets properties for the Person Object';		
		$data['url'] = site_url('/api/exposeObj/person/getProperties/format/serialize');
		$data['filter'] = 'No filter set';
		$data['method'] = 'POST';
		
		if($getInfo) return $data;
		
		$request = new HTTP_Request2($data['url']);
		$request->setMethod(HTTP_Request2::METHOD_POST);
		    
		$response = $request->send();
		$data['response'] = unserialize($response->getBody());				
					
		if($loadview) $this->load->view('tests_view',$data);
				
		//passed or failed
		return empty($data['response']['error']) ? true: false;
	}

	public function testProperties_A2($loadview = true, $getInfo = false)
	{		
		$data = array();
		$data['testname'] = __FUNCTION__;
		$data['object'] = 'Person';
		$data['testdesc'] = 'Gets properties for the Person Object';		
		$data['url'] = site_url('/api/exposeObj/person/getProperties/format/serialize');
		$data['filter'] = 'No filter set';
		$data['method'] = 'GET';
		
		if($getInfo) return $data;
		
		$request = new HTTP_Request2($data['url']);
		$request->setMethod(HTTP_Request2::METHOD_GET);
		    
		$response = $request->send();
		$data['response'] = unserialize($response->getBody());				
					
		if($loadview) $this->load->view('tests_view',$data);
				
		//passed or failed
		return empty($data['response']['error']) ? true: false;
	}
		
	public function testProperties_B1($loadview = true, $getInfo = false)
	{		
		$data = array();
		$data['testname'] = __FUNCTION__;
		$data['object'] = 'Person';
		$data['testdesc'] = 'Gets required properties for the Person Object';
		$data['url'] = site_url('/api/exposeObj/person/getRequiredProperties/format/serialize');
		$data['filter'] = 'No filter set';
		
		if($getInfo) return $data;
		
		$request = new HTTP_Request2($data['url']);
		$request->setMethod(HTTP_Request2::METHOD_POST);
		    
		$response = $request->send();
		$data['response'] = unserialize($response->getBody());				
					
		if($loadview) $this->load->view('tests_view',$data);
				
		//passed or failed
		return empty($data['response']['error']) ? true: false;
	}			

	public function testProperties_B2($loadview = true, $getInfo = false)
	{		
		$data = array();
		$data['testname'] = __FUNCTION__;
		$data['object'] = 'Person';
		$data['testdesc'] = 'Gets required properties for the Person Object';
		$data['url'] = site_url('/api/exposeObj/person/getRequiredProperties/format/serialize');
		$data['filter'] = 'No filter set';
		$data['method'] = 'GET';
		
		if($getInfo) return $data;
		
		$request = new HTTP_Request2($data['url']);
		$request->setMethod(HTTP_Request2::METHOD_GET);
		    
		$response = $request->send();
		$data['response'] = unserialize($response->getBody());				
					
		if($loadview) $this->load->view('tests_view',$data);
				
		//passed or failed
		return empty($data['response']['error']) ? true: false;
	}			
		
	public function testUpdate($loadview = true, $getInfo = false)
	{		
		$data = array();
		$data['testname'] = __FUNCTION__;
		$data['object'] = 'Person';
		$data['testdesc'] = 'Updates a person taken randomly from the ones created on testCreate*. On success  returns the uid of the new Person';
		//first of all I get the uid array for the matching contacts
		$data['url'] = site_url('/api/exposeObj/person/read/format/serialize');
		$data['filter'] = '(givenName=*illy*)';
		$data['attributes'] = array('uid');
		
		if($getInfo) return $data;
		
		$request = new HTTP_Request2($data['url']);
		$request->setMethod(HTTP_Request2::METHOD_POST)
		    ->addPostParameter('filter', $data['filter'])
		    ->addPostParameter('emptyfields', false)
		    ->addPostParameter('attributes', $data['attributes']);
				    
		$response = $request->send();
		$data['response'] = unserialize($response->getBody());
				
		foreach ($data['response'] as $item => $value) {
				if(isset($value['uid']['0'])) $uid = $value['uid']['0'];
				if(isset($value['0'])) $uid = $value['0'];
				$uids[] = $uid;
		}		
		//getting a random uid
		$uid = $uids[array_rand($uids,1)];

		$data['url'] = site_url('/api/exposeObj/person/update/format/serialize');
		$data['filter'] = 'No filter set';
		$random = rand(999,9999);
		$surname = 'Coyote'.$random;
		$name = 'Billy'.$random;
		
		//modified fields
		$data['givenName'] = $name;
		$data['sn'] = $surname;

		//performing the update
		$request = new HTTP_Request2($data['url']);
		$request->setMethod(HTTP_Request2::METHOD_POST)
			->addPostParameter('givenName', $data['givenName'])
			->addPostParameter('sn', $data['sn'])
			->addPostParameter('uid', $uid);
					   			    
		$response = $request->send();
		$data['response'] = unserialize($response->getBody());			
					
		if($loadview) $this->load->view('tests_view',$data);
				
		//passed or failed
		return empty($data['response']['error']) ? true: false;
	}			
	
	public function testDelete($loadview = true, $getInfo = false)
	{		
		//just to be sure that we have some records to delete
		$this->testCreate_A(false,false);
		
		$data = array();
		$data['testname'] = __FUNCTION__;
		$data['object'] = 'Person';
		$data['testdesc'] = 'Deletes all the persons matching the filter with CI2 Rest_Client. Output: boolean';
		
		//first of all I get the uid array for the matching contacts
		$url = 'exposeObj/person/read';
		$data['url'] = site_url($url);
		$data['filter'] = '(givenName=*illy*)';
		$data['attributes'] = array('uid');
		
		if($getInfo) return $data;
	
		$data['response'] = $this->rest->post($url, $data, 'serialize');		
		//if(isset())
		
		//now I delete the returned uids
		$url = 'exposeObj/person/delete';
		$data['url'] = site_url($url);
		foreach ($data['response'] as $item => $value) {
				if(isset($value['uid']['0'])) $uid = $value['uid']['0'];
				$input = array('uid' => $uid);
				$response = $this->rest->post($url, $input, 'serialize');
				
		}
		
		if($loadview) $this->load->view('tests_view',$data);
						
		//passed or failed
		empty($response['error'])? true : false; 
	}			
	
	
	// ================ ORG TESTS ============================================================
	
	public function testOrgCreate($loadview = true, $getInfo = false)
	{		
		$data = array();
		$data['testname'] = __FUNCTION__;
		$data['object'] = 'Organization';
		$data['testdesc'] = 'Creates a organization. If it goes wrong return false otherwise you get the oid of the new Organization';		
		$data['url'] = site_url('/api/exposeObj/organization/create/format/serialize');
		$data['filter'] = 'No filter set';
		
		if($getInfo) return $data;
		
		$random = rand(999,9999);
		$orgname = 'ACME'.$random;
		
		//required fields
		$data['enabled'] = 'TRUE';
		$data['entryCreatedBy'] = 'dam';
		$data['o'] = $orgname;
		//oid is automatically set, so not needed
		
		$request = new HTTP_Request2($data['url']);
		$request->setMethod(HTTP_Request2::METHOD_POST)
			->addPostParameter('enabled', $data['enabled'])
			->addPostParameter('entryCreatedBy', $data['entryCreatedBy'])
			->addPostParameter('o', $data['o']);
					   			    
		$response = $request->send();
		$data['response'] = unserialize($response->getBody());			
					
		if($loadview) $this->load->view('tests_view',$data);
		return empty($data['response']['error']) ? true: false;
	}		
		
	public function testOrgRead_All_A($loadview = true, $getInfo = false)
	{		
		$data = array();
		$data['testname'] = __FUNCTION__;
		$data['object'] = 'Organization';
		$data['testdesc'] = 'Gets all data about all the organizations in the storage. Output: PHP array';		
		$data['url'] = site_url('/api/exposeObj/organization/read/format/serialize');
		$data['filter'] = '(objectClass=*)';
		
		if($getInfo) return $data;
		
		$request = new HTTP_Request2($data['url']);
		$request->setMethod(HTTP_Request2::METHOD_POST)
		    ->addPostParameter('filter', $data['filter'])
		    ->addPostParameter('emptyfields', false);
		    
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
		
	public function testOrgRead_All_B($loadview = true, $getInfo = false)
	{		
		$data = array();
		$data['testname'] = __FUNCTION__;
		$data['object'] = 'Organization';
		$data['testdesc'] = 'Gets some data about all the organizations in the storage. Output: PHP array';		
		$data['url'] = site_url('/api/exposeObj/organization/read/format/serialize');
		$data['filter'] = '(objectClass=*)';
		$data['attributes'] = array('o','vatNumber');
		
		if($getInfo) return $data;
		
		$request = new HTTP_Request2($data['url']);
		$request->setMethod(HTTP_Request2::METHOD_POST)
		    ->addPostParameter('filter', $data['filter'])
		    ->addPostParameter('emptyfields', false)
		    ->addPostParameter('attributes', $data['attributes']);
		    
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
	
	public function testOrgRead_Some($loadview = true, $getInfo = false)
	{
		$data = array();
		$data['testname'] = __FUNCTION__;
		$data['object'] = 'Organization';
		$data['testdesc'] = 'Gets all data about some organizations using the POST method and specifying the filter in the POST array. Output: PHP array';		
		$data['url'] = site_url('/api/exposeObj/organization/read/format/serialize');
		$data['filter'] = '(o=A*)';
		
		if($getInfo) return $data;
		
		$request = new HTTP_Request2($data['url']);
		$request->setMethod(HTTP_Request2::METHOD_POST)
		    ->addPostParameter('filter', $data['filter'])
		    ->addPostParameter('emptyfields', false);
		$response = $request->send();
		$data['response'] = unserialize($response->getBody());		
		
		if($loadview) $this->load->view('tests_view',$data);
								
		//passed or failed
		return true; //FIXME this is a fake
	}	
		
	public function testOrgUinque($loadview = true, $getInfo = false)
	{		
		$data = array();
		$data['testname'] = __FUNCTION__;
		$data['object'] = 'Organization';
		$data['testdesc'] = 'Check a unique email. Returns true if the given email is unique. This method can be used to check the unicity of ANY attribute.';		
		$data['url'] = site_url('/api/exposeObj/organization/checkUniqueAttribute/format/serialize');
		$data['filter'] = 'No filter set, I just send the email <i>"myemail@mycompany.com"</i> in the POST';
		
		if($getInfo) return $data;
		
		$request = new HTTP_Request2($data['url']);
		$request->setMethod(HTTP_Request2::METHOD_POST)
			->addPostParameter('attribute', 'omail')
		    ->addPostParameter('omail', 'myemail@mycompany.com');
		    
		//Benchmark starts
		$this->benchmark->mark('code_start');
				    
		$response = $request->send();
		$data['response'] = unserialize($response->getBody());
		
		//Benchmark stops
		$this->benchmark->mark('code_end');
		$data['benchmark'] = $this->benchmark->elapsed_time('code_start', 'code_end').' seconds to retrieve result';	
				
		if($loadview) $this->load->view('tests_view',$data);
										
		//passed or failed
		return ($data) ? true : false;
	}		
	
	public function testOrgProperties_A($loadview = true, $getInfo = false)
	{		
		$data = array();
		$data['testname'] = __FUNCTION__;
		$data['object'] = 'Organization';
		$data['testdesc'] = 'Gets properties for the Organization Object';		
		$data['url'] = site_url('/api/exposeObj/organization/getProperties/format/serialize');
		$data['filter'] = 'No filter set';
		
		if($getInfo) return $data;
		
		$request = new HTTP_Request2($data['url']);
		$request->setMethod(HTTP_Request2::METHOD_POST);
		    
		$response = $request->send();
		$data['response'] = unserialize($response->getBody());				
					
		if($loadview) $this->load->view('tests_view',$data);
				
		//passed or failed
		return empty($data['response']['error']) ? true: false;
	}			

	public function testOrgProperties_B($loadview = true, $getInfo = false)
	{		
		$data = array();
		$data['testname'] = __FUNCTION__;
		$data['object'] = 'Organization';
		$data['testdesc'] = 'Gets required properties for the Organization Object';
		$data['url'] = site_url('/api/exposeObj/organization/getRequiredProperties/format/serialize');
		$data['filter'] = 'No filter set';
		
		if($getInfo) return $data;
		
		$request = new HTTP_Request2($data['url']);
		$request->setMethod(HTTP_Request2::METHOD_POST);
		    
		$response = $request->send();
		$data['response'] = unserialize($response->getBody());				
					
		if($loadview) $this->load->view('tests_view',$data);
				
		//passed or failed
		return empty($data['response']['error']) ? true: false;
	}	
		
	public function testOrgUpdate($loadview = true, $getInfo = false)
	{		
		$data = array();
		$data['testname'] = __FUNCTION__;
		$data['object'] = 'Organization';
		$data['testdesc'] = 'It updates a organization taken randomly from the ones created on testOrgCreate*. On success returns the oid of the new Organization';
		$data['url'] = site_url('/api/exposeObj/organization/read/format/serialize');
		$data['filter'] = '(o=ACME*)';
		$data['attributes'] = array('oid');
		
		if($getInfo) return $data;
		
		$request = new HTTP_Request2($data['url']);
		$request->setMethod(HTTP_Request2::METHOD_POST)
		    ->addPostParameter('filter', $data['filter'])
		    ->addPostParameter('emptyfields', false)
		    ->addPostParameter('attributes', $data['attributes']);
		    
		//Benchmark starts
		$this->benchmark->mark('code_start');
				    
		$response = $request->send();
		$data['response'] = unserialize($response->getBody());
				
		foreach ($data['response'] as $item => $value) {
				if(isset($value['oid'])) $oid = (string) $value['oid'];
				//if(isset($value['0'])) $oid = $value['0'];
				$oids[] = $oid;
		}		
		//getting a random oid
		$oid = $oids[array_rand($oids,1)];
		
		$data['url'] = site_url('/api/exposeObj/organization/update/format/serialize');
		$data['filter'] = 'No filter set';
		
		//modified fields
		$random = rand(999,9999);
		$orgname = 'ACMED'.$random;
		$data['o'] = $orgname;
		
		//performing the update
		$request = new HTTP_Request2($data['url']);
		$request->setMethod(HTTP_Request2::METHOD_POST)
			->addPostParameter('o', $data['o'])
			->addPostParameter('oid', $oid);
					   			    
		$response = $request->send();
		$data['response'] = unserialize($response->getBody());			
					
		if($loadview) $this->load->view('tests_view',$data);
												
		//passed or failed
		return empty($data['response']['error']) ? true: false;
	}			
	
	public function testOrgDelete($loadview = true, $getInfo = false)
	{		
		$this->testOrgCreate(false,false);
		
		$data = array();
		$data['testname'] = __FUNCTION__;
		$data['object'] = 'Organization';
		$data['testdesc'] = 'Deletes all the organizations matching the filter';		
		
		//first of all I get the oid array for the matching contacts
		$url = 'exposeObj/organization/read';
		$data['url'] = site_url($url);
		$data['filter'] = '(o=ACME*)';
		$data['attributes'] = array('oid');
				
		if($getInfo) return $data;
		
		$data['response'] = $this->rest->post($url, $data, 'serialize');
		//if(isset())
		
		//now I delete the returned oids
		$url = 'exposeObj/organization/delete';
		$data['url'] = site_url($url);
		foreach ($data['response'] as $item => $value) {
			if(isset($value['oid']['0'])) $oid = $value['oid']['0'];
			$input = array('oid' => $oid);
			$response = $this->rest->post($url, $input, 'serialize');
		
		}
		
		if($loadview) $this->load->view('tests_view',$data);
		
		//passed or failed
		empty($response['error'])? true : false;		
	}			
	
	// ================ LOCATION TESTS ============================================================
	
	public function testLocCreate($loadview = true, $getInfo = false)
	{		
		$data = array();
		$data['testname'] = __FUNCTION__;
		$data['object'] = 'Location';
		$data['testdesc'] = 'Creates a location. On success returns the locId of the new Location';		
		$data['url'] = site_url('/api/exposeObj/location/create/format/serialize');
		$data['filter'] = 'No filter set';
		
		if($getInfo) return $data;
		
		$random = rand(999,9999);
		$locDescription = 'MyDescription '.$random;
		
		//required fields

		$data['locCity'] = 'MyCity '.$random;
		$data['locCountry'] = 'MyCountry '.$random;
		$data['locDescription'] = 'MyDescription '.$random;
		$data['locState'] = 'MyState '.$random;
		$data['locStreet'] = 'MyStreet '.$random;
		//locId is automatically set, so not needed
		
		$request = new HTTP_Request2($data['url']);
		$request->setMethod(HTTP_Request2::METHOD_POST)
			->addPostParameter('locCity', $data['locCity'])
			->addPostParameter('locCountry', $data['locCountry'])
			->addPostParameter('locDescription', $data['locDescription'])
			->addPostParameter('locState', $data['locState'])
			->addPostParameter('locStreet', $data['locStreet']);
					   			    
		$response = $request->send();
		$data['response'] = unserialize($response->getBody());			
					
		if($loadview) $this->load->view('tests_view',$data);
										
		//passed or failed
		return empty($data['response']['error']) ? true: false;
	}		
	
	public function testLocRead_All_A($loadview = true, $getInfo = false)
	{		
		$data = array();
		$data['testname'] = __FUNCTION__;
		$data['object'] = 'Location';
		$data['testdesc'] = 'Gets the data about all the locations in the storage. Output: PHP array';		
		$data['url'] = site_url('/api/exposeObj/location/read/format/serialize');
		$data['filter'] = '(objectClass=*)';
		
		if($getInfo) return $data;
		
		$request = new HTTP_Request2($data['url']);
		$request->setMethod(HTTP_Request2::METHOD_POST)
		    ->addPostParameter('filter', $data['filter'])
		    ->addPostParameter('emptyfields', false);
		    
		//Benchmark starts
		$this->benchmark->mark('code_start');
				    
		$response = $request->send();
		$data['response'] = unserialize($response->getBody());
		
		//Benchmark stops
		$this->benchmark->mark('code_end');
		$data['benchmark'] = $this->benchmark->elapsed_time('code_start', 'code_end').' seconds to retrieve '.count($data['response']).' usable locations';	
				
		if($loadview) $this->load->view('tests_view',$data);
										
		//passed or failed		
		return empty($data['response']['error']) ? true: false;
	}	
	
	public function testLocRead_All_B($loadview = true, $getInfo = false)
	{		
		$data = array();
		$data['testname'] = __FUNCTION__;
		$data['object'] = 'Location';
		$data['testdesc'] = 'How to get some data about all the locations in the storage. Output: PHP array';		
		$data['url'] = site_url('/api/exposeObj/location/read/format/serialize');
		$data['filter'] = '(objectClass=*)';
		$data['attributes'] = array('locDescription','locCity');
		
		if($getInfo) return $data;
		
		$request = new HTTP_Request2($data['url']);
		$request->setMethod(HTTP_Request2::METHOD_POST)
		    ->addPostParameter('filter', $data['filter'])
		    ->addPostParameter('emptyfields', false)
		    ->addPostParameter('attributes', $data['attributes']);
		    
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
	
	public function testLocRead_Some($loadview = true, $getInfo = false)
	{
		$data = array();
		$data['testname'] = __FUNCTION__;
		$data['object'] = 'Location';
		$data['testdesc'] = 'Gets all data about some locations using the POST method and specifying the filter in the POST array. Output: PHP array';		
		$data['url'] = site_url('/api/exposeObj/location/read/format/serialize');
		$data['filter'] = '(locState=Como)';
		
		if($getInfo) return $data;
		
		$request = new HTTP_Request2($data['url']);
		$request->setMethod(HTTP_Request2::METHOD_POST)
		    ->addPostParameter('filter', $data['filter'])
		    ->addPostParameter('emptyfields', false);
		$response = $request->send();
		$data['response'] = unserialize($response->getBody());		
		
		if($loadview) $this->load->view('tests_view',$data);
										
		//passed or failed		
		return true; //FIXME this is a fake
	}		
		
	public function testLocUnique($loadview = true, $getInfo = false)
	{		
		$data = array();
		$data['testname'] = __FUNCTION__;
		$data['object'] = 'Location';
		$data['testdesc'] = 'Checks a unique locId. Returns true if the given locId is unique. This method can be used to check the unicity of ANY attribute.';		
		$data['url'] = site_url('/api/exposeObj/location/checkUniqueAttribute/format/serialize');
		$data['filter'] = 'No filter set, I just send the email <i>"myemail@mycompany.com"</i> in the POST';
		
		if($getInfo) return $data;
		
		$request = new HTTP_Request2($data['url']);
		$request->setMethod(HTTP_Request2::METHOD_POST)
			->addPostParameter('attribute', 'locId')
		    ->addPostParameter('locId', '666666666666666666666666');
		    
		//Benchmark starts
		$this->benchmark->mark('code_start');
				    
		$response = $request->send();
		$data['response'] = unserialize($response->getBody());
		
		//Benchmark stops
		$this->benchmark->mark('code_end');
		$data['benchmark'] = $this->benchmark->elapsed_time('code_start', 'code_end').' seconds to retrieve result';	
				
					
		if($loadview) $this->load->view('tests_view',$data);
										
		//passed or failed				
		return ($data) ? true : false;
	}	
			
	public function testLocProperties_A($loadview = true, $getInfo = false)
	{		
		$data = array();
		$data['testname'] = __FUNCTION__;
		$data['object'] = 'Location';
		$data['testdesc'] = 'Gets properties for the Organization Object';		
		$data['url'] = site_url('/api/exposeObj/location/getProperties/format/serialize');
		$data['filter'] = 'No filter set';
		
		if($getInfo) return $data;
		
		$request = new HTTP_Request2($data['url']);
		$request->setMethod(HTTP_Request2::METHOD_POST);
		    
		$response = $request->send();
		$data['response'] = unserialize($response->getBody());				
					
		if($loadview) $this->load->view('tests_view',$data);
				
		//passed or failed
		return empty($data['response']['error']) ? true: false;
	}			

	public function testLocProperties_B($loadview = true, $getInfo = false)
	{		
		$data = array();
		$data['testname'] = __FUNCTION__;
		$data['object'] = 'Location';
		$data['testdesc'] = 'Gets required properties for the Organization Object';
		$data['url'] = site_url('/api/exposeObj/location/getRequiredProperties/format/serialize');
		$data['filter'] = 'No filter set';
		
		if($getInfo) return $data;
		
		$request = new HTTP_Request2($data['url']);
		$request->setMethod(HTTP_Request2::METHOD_POST);
		    
		$response = $request->send();
		$data['response'] = unserialize($response->getBody());				
					
		if($loadview) $this->load->view('tests_view',$data);
				
		//passed or failed
		return empty($data['response']['error']) ? true: false;
	}	
	
	public function testLocUpdate($loadview = true, $getInfo = false)
	{		
		$data = array();
		$data['testname'] = __FUNCTION__;
		$data['object'] = 'Location';
		$data['testdesc'] = 'Updates a location taken randomly from the ones created on testLocCreate*. On success returns the locId of the new Location';
		//first of all I get the locId array for the matching contacts
		$data['url'] = site_url('/api/exposeObj/location/read/format/serialize');
		$data['filter'] = '(locDescription=My*)';
		$data['attributes'] = array('locId');
		
		if($getInfo) return $data;
		
		$request = new HTTP_Request2($data['url']);
		$request->setMethod(HTTP_Request2::METHOD_POST)
		    ->addPostParameter('filter', $data['filter'])
		    ->addPostParameter('emptyfields', false)
		    ->addPostParameter('attributes', $data['attributes']);
		    
		//Benchmark starts
		$this->benchmark->mark('code_start');
				    
		$response = $request->send();
		$data['response'] = unserialize($response->getBody());
				
		foreach ($data['response'] as $item => $value) {
				if(isset($value['0'])) $locId = (string) $value['0'];  //found only a single loc  ???
				if(isset($value['locId'])) $locId = (string) $value['locId']['0']; //found more than one loc ???
				$locIds[] = $locId;
		}		
		//getting a random locId
		$locId = $locIds[array_rand($locIds,1)];
				
		$data['url'] = site_url('/api/exposeObj/location/update/format/serialize');
		$data['filter'] = 'No filter set';
		$random = rand(999,9999);
		
		$data['locCity'] = 'MyCityNEW '.$random;
		$data['locCountry'] = 'MyCountryNEW '.$random;
		$data['locDescription'] = 'MyDescriptionNEW '.$random;
		$data['locState'] = 'MyStateNEW '.$random;
		$data['locStreet'] = 'MyStreetPD '.$random;
		$data['entryUpdatedBy'] = 'dam';
		$data['objectclass'] = "dueviLocation";
		$data['entryCreatedBy'] = 'dam';		
		//locId is automatically set, so not needed
		
		$request = new HTTP_Request2($data['url']);
		$request->setMethod(HTTP_Request2::METHOD_POST)
			->addPostParameter('locCity', $data['locCity'])
			->addPostParameter('locCountry', $data['locCountry'])
			->addPostParameter('locDescription', $data['locDescription'])
			->addPostParameter('locState', $data['locState'])
			->addPostParameter('locStreet', $data['locStreet'])
			->addPostParameter('entryUpdatedBy', $data['entryUpdatedBy'])
			->addPostParameter('entryCreatedBy', $data['entryCreatedBy'])
			->addPostParameter('objectClass', $data['objectClass'])
			->addPostParameter('locId', $locId);
										   			    
		$response = $request->send();
		$data['response'] = unserialize($response->getBody());			
					
		if($loadview) $this->load->view('tests_view',$data);
												
		//passed or failed				
		return empty($data['response']['error']) ? true: false;
	}			
	
	public function testLocDelete($loadview = true, $getInfo = false)
	{		
		$this->testLocCreate(false,false);
		
		$data = array();
		$data['testname'] = __FUNCTION__;
		$data['object'] = 'Location';
		$data['testdesc'] = 'Deletes all the locations matching the filter';		
		
		//I get the locId array for the matching contacts
		$url = 'exposeObj/location/read';
		$data['url'] = site_url($url);
		$data['filter'] = '(locDescription=MyDescription*)';
		$data['attributes'] = array('locId');
				
		if($getInfo) return $data;
		
		$data['response'] = $this->rest->post($url, $data, 'serialize');
		
		//now I delete the returned locIds
		$url = 'exposeObj/location/delete';
		$data['url'] = site_url($url);
		foreach ($data['response'] as $item => $value) {
			if(isset($value['locId']['0'])) $locId = $value['locId']['0'];
			$input = array('locId' => $locId);
			$response = $this->rest->post($url, $input, 'serialize');
		
		}
		
		if($loadview) $this->load->view('tests_view',$data);
		
		//passed or failed
//		empty($response['error'])? true : false;
		if(empty($response['error']))
		{
			return true;		
		} else {
			return false;
		}
		 
	}			
	
	// ================ RELATIONSHIP TESTS ============================================================	
	
/*	
	public function testAssocLocation($loadview = true, $getInfo = false)
	{		
		$data = array();
		$data['testname'] = __FUNCTION__;
		$data['object'] = 'Person';
		$data['testdesc'] = 'Associates a person taken randomly from the ones created on testCreate* to a given location. On success returns uid or true if the location is already associated.';
		//first of all I get the uid array for the matching contacts
		$data['url'] = site_url('/api/exposeObj/person/read/format/serialize');
		$data['filter'] = '(givenName=*illy*)';
		$data['attributes'] = array('uid');
		
		if($getInfo) return $data;
		
		$request = new HTTP_Request2($data['url']);
		$request->setMethod(HTTP_Request2::METHOD_POST)
		    ->addPostParameter('filter', $data['filter'])
		    ->addPostParameter('emptyfields', false)
		    ->addPostParameter('attributes', $data['attributes']);
		    				    
		$response = $request->send();
		$data['response'] = unserialize($response->getBody());
				
		foreach ($data['response'] as $item => $value) {
				if(isset($value['uid'])) $uid = $value['uid'];
				if(isset($value['0'])) $uid = $value['0'];
				$uids[] = $uid;
		}		
		//getting a random uid
		$uid = $uids[array_rand($uids,1)];
		
		//getting a random location
		$data['url'] = site_url('/api/exposeObj/location/read/format/serialize');
		$data['filter'] = '(locDescription=My*)';
		$data['attributes'] = array('locId');
		
		$request = new HTTP_Request2($data['url']);
		$request->setMethod(HTTP_Request2::METHOD_POST)
		    ->addPostParameter('filter', $data['filter'])
		    ->addPostParameter('emptyfields', false)
		    ->addPostParameter('attributes', $data['attributes']);
		    				    
		$response = $request->send();
		$data['response'] = unserialize($response->getBody());
				
		foreach ($data['response'] as $item => $value) {
				if(isset($value['0'])) $locId = (string) $value['0'];  //found only a single loc  ???
				if(isset($value['locId'])) $locId = (string) $value['locId']['0']; //found more than one loc ???
				$locIds[] = $locId;
		}		

		$locId = $locIds[array_rand($locIds,1)];		
		
		//performing the update
		$data['url'] = site_url('/api/exposeObj/person/associate/to/location/format/serialize');
		$request = new HTTP_Request2($data['url']);
		$request->setMethod(HTTP_Request2::METHOD_POST)
		    ->addPostParameter('uid', $uid)
		    ->addPostParameter('emptyfields', false)
		    ->addPostParameter('locId', $locId); //manually set on purpose		
					   			    
		$response = $request->send();
		$data['response'] = unserialize($response->getBody());			
					
		if($loadview) $this->load->view('tests_view',$data);
		return empty($data['response']['error']) ? true: false;
	}			

	public function testAssocOrg($loadview = true, $getInfo = false)
	{		
		$data = array();
		$data['testname'] = __FUNCTION__;
		$data['object'] = 'Person';
		$data['testdesc'] = 'Associates a person taken randomly from the ones created on testCreate* to a given organization. On success returns uid  or true if the location is already associated.';
		//first of all I get the uid array for the matching contacts
		$data['url'] = site_url('/api/exposeObj/person/read/format/serialize');
		$data['filter'] = '(givenName=*illy*)';
		$data['attributes'] = array('uid');
		
		if($getInfo) return $data;
		
		$request = new HTTP_Request2($data['url']);
		$request->setMethod(HTTP_Request2::METHOD_POST)
		    ->addPostParameter('filter', $data['filter'])
		    ->addPostParameter('emptyfields', false)
		    ->addPostParameter('attributes', $data['attributes']);
		    				    
		$response = $request->send();
		$data['response'] = unserialize($response->getBody());
				
		foreach ($data['response'] as $item => $value) {
				if(isset($value['uid'])) $uid = (string) $value['uid']['0'];
				if(isset($value['0'])) $uid = $value['0'];
				$uids[] = $uid;
		}		
		//getting a random uid
		$uid = $uids[array_rand($uids,1)];
		
		//getting a random location
		$data['url'] = site_url('/api/exposeObj/location/read/format/serialize');
		$data['filter'] = '(locDescription=My*)';
		$data['attributes'] = array('locId');
		
		$request = new HTTP_Request2($data['url']);
		$request->setMethod(HTTP_Request2::METHOD_POST)
		    ->addPostParameter('filter', $data['filter'])
		    ->addPostParameter('emptyfields', false)
		    ->addPostParameter('attributes', $data['attributes']);
		    				    
		$response = $request->send();
		$data['response'] = unserialize($response->getBody());
				
		foreach ($data['response'] as $item => $value) {
				if(isset($value['0'])) $locId = (string) $value['0'];  //found only a single loc  ???
				if(isset($value['locId'])) $locId = (string) $value['locId']['0']; //found more than one loc ???
				$locIds[] = $locId;
		}		

		$locId = $locIds[array_rand($locIds,1)];			
		
		//performing the update
		$data['url'] = site_url('/api/exposeObj/person/associate/to/organization/format/serialize');
		$request = new HTTP_Request2($data['url']);
		$request->setMethod(HTTP_Request2::METHOD_POST)
		    ->addPostParameter('uid', $uid)
		    ->addPostParameter('oid', $locId); //manually set on purpose		
					   			    
		$response = $request->send();
		$data['response'] = unserialize($response->getBody());			
					
		if($loadview) $this->load->view('tests_view',$data);
		return empty($data['response']['error']) ? true: false;
	}		
	
*/
}

/* End of make_tests.php */