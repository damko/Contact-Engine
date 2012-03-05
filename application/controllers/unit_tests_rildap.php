<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH.'/libraries/test_controller.php';

class Unit_Tests_RiLdap extends Test_Controller {
	public function __construct()
	{
		parent::__construct();		
		
		$this->load->spark('ri_ldap/0.0.1');
		
		$this->server = 'ldap://ldapmaster0:389';
		$this->ldapdn = 'cn=admin,dc=2v,dc=ntw';
		$this->ldappw = 'Wi7Xkcv300z';
		$this->version = '3';
		$this->baseDN = 'ou=users,o=ce,dc=2v,dc=ntw';
	}
		
	public function index()
	{	
		$this->load->view('unit_tests');

		echo '<a href="/index.php/unit_tests/">Back</a> to unit-tests front page.<br/>';
		
		//runs tests
		echo '<div id="left">';
		$this->test_Ri_LDAP_Initialize();
		$this->test_Ri_Ldap_create();
		$this->test_Ri_Ldap_search();
		$this->test_Ri_Ldap_read();
		$this->test_Ri_Ldap_update();
		$this->test_Ri_Ldap_delete();
		echo '</div>';
		
		$this->printSummary();
		
		echo '<div></body></html>';
	}
		
	public function test_Ri_LDAP_Initialize()
	{
		$this->testTitle('Testing the Ri_LDAP Object initialize() method');
		
		
		$this->getCodeOrigin();
		$this->rildap = new Ri_Ldap();
		$test = $this->rildap->initialize();
		echo $this->run($test, 'is_true', 'Testing method $ri_ldap->initialize', '');
		
		
		
		$this->getCodeOrigin();
		$test = count($this->rildap->result->errors);
		echo $this->run($test, '0','Counting errors of the previous test: expecting 0, got: '.$test,'');
		$this->printLdapResult($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasNoContent($this->rildap->result);		
	}
	
	
	public function test_Ri_Ldap_create()
	{
		$this->testTitle('Testing the Ri_LDAP Object create() method');
	
	
		$this->subTestTitle('Performing a good creation using all the mandatory attributes');
		$this->getCodeOrigin();
		$this->rildap = new Ri_Ldap();
	
		$random = rand(999999,9999999);
		$surname = 'Coyote_'.$random;
		$name = 'Willy';
	
		//entry fields from the LDAP schema MUST attribute
		$entry = array();
		$entry['uid'] = $random;
		$entry['cn'] = $name.' '.$surname;
		$entry['sn'] = $surname;
		$entry['givenName'] = $name;
		$entry['displayName'] = $entry['cn'];
		$entry['fileAs'] = $entry['cn'];
		$entry['userPassword'] = 'mypassword';
		$entry['enabled'] = 'TRUE';
		$entry['entryCreatedBy'] = 'unit tests';
		$entry['category'] = 'mycategory';
		$entry['objectClass'] = 'dueviPerson';
	
		$dn = 'uid='.$entry['uid'].','.$this->baseDN;
	
		$test = $this->rildap->CEcreate($entry,$dn);
		echo $this->run($test, 'is_true', 'Is the exit status true ?', '');

		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObject($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasNoError($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasNoContent($this->rildap->result);
		$this->printLdapResult($this->rildap->result);
		
		

		$this->subTestTitle('Performing a good creation using a wrong DN');
		$this->getCodeOrigin();
		$this->rildap = new Ri_Ldap();
		
		$random = rand(999999,9999999);
		$surname = 'Coyote_'.$random;
		$name = 'Willy';
		
		//entry fields from the LDAP schema MUST attribute
		$entry = array();
		$entry['uid'] = $random;
		$entry['cn'] = $name.' '.$surname;
		$entry['sn'] = $surname;
		$entry['givenName'] = $name;
		$entry['displayName'] = $entry['cn'];
		$entry['fileAs'] = $entry['cn'];
		$entry['userPassword'] = 'mypassword';
		$entry['enabled'] = 'TRUE';
		$entry['entryCreatedBy'] = 'unit tests';
		$entry['category'] = 'mycategory';
		$entry['objectClass'] = 'dueviPerson';
		
		$dn = 'ou=fakeDN,o=ce,dc=2v,dc=ntw';
		
		$test = $this->rildap->CEcreate($entry,$dn);
		echo $this->run($test, 'is_false', 'Is the exit status false ?', '');
		
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObject($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasError($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasNoContent($this->rildap->result);
		$this->printLdapResult($this->rildap->result);		
		
		
		
		
		$this->subTestTitle('Performing a wrong creation not passing DN');
		$this->getCodeOrigin();
		$this->rildap = new Ri_Ldap();
		
		$random = rand(999999,9999999);
		$surname = 'Coyote_'.$random;
		$name = 'Willy';
		
		//entry fields from the LDAP schema MUST attribute
		$entry = array();
		$entry['uid'] = $random;
		$entry['cn'] = $name.' '.$surname;
		$entry['sn'] = $surname;
		$entry['givenName'] = $name;
		$entry['displayName'] = $entry['cn'];
		$entry['fileAs'] = $entry['cn'];
		$entry['userPassword'] = 'mypassword';
		$entry['enabled'] = 'TRUE';
		$entry['entryCreatedBy'] = 'unit tests';
		$entry['category'] = 'mycategory';
		$entry['objectClass'] = 'dueviPerson';

		$test = $this->rildap->CEcreate($entry);
		echo $this->run($test, 'is_false', 'Is the exit status false ?', '');
		
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObject($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasError($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasNoContent($this->rildap->result);
		$this->printLdapResult($this->rildap->result);		
		
		
		
		$this->subTestTitle('Performing a wrong creation passing empty DN');
		$this->getCodeOrigin();
		$this->rildap = new Ri_Ldap();
		
		$random = rand(999999,9999999);
		$surname = 'Coyote_'.$random;
		$name = 'Willy';
		
		//entry fields from the LDAP schema MUST attribute
		$entry = array();
		$entry['uid'] = $random;
		$entry['cn'] = $name.' '.$surname;
		$entry['sn'] = $surname;
		$entry['givenName'] = $name;
		$entry['displayName'] = $entry['cn'];
		$entry['fileAs'] = $entry['cn'];
		$entry['userPassword'] = 'mypassword';
		$entry['enabled'] = 'TRUE';
		$entry['entryCreatedBy'] = 'unit tests';
		$entry['category'] = 'mycategory';
		$entry['objectClass'] = 'dueviPerson';
		
		$dn = '';
		
		$test = $this->rildap->CEcreate($entry, $dn);
		echo $this->run($test, 'is_false', 'Is the exit status false ?', '');
		
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObject($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasError($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasNoContent($this->rildap->result);
		$this->printLdapResult($this->rildap->result);		
		
		
		
		$this->subTestTitle('Performing a wrong creation passing a DN as array');
		$this->getCodeOrigin();
		$this->rildap = new Ri_Ldap();
		
		$random = rand(999999,9999999);
		$surname = 'Coyote_'.$random;
		$name = 'Willy';
		
		//entry fields from the LDAP schema MUST attribute
		$entry = array();
		$entry['uid'] = $random;
		$entry['cn'] = $name.' '.$surname;
		$entry['sn'] = $surname;
		$entry['givenName'] = $name;
		$entry['displayName'] = $entry['cn'];
		$entry['fileAs'] = $entry['cn'];
		$entry['userPassword'] = 'mypassword';
		$entry['enabled'] = 'TRUE';
		$entry['entryCreatedBy'] = 'unit tests';
		$entry['category'] = 'mycategory';
		$entry['objectClass'] = 'dueviPerson';
		
		$dn = array('ou=fakeDN,o=ce,dc=2v,dc=ntw');
		
		$test = $this->rildap->CEcreate($entry, $dn);
		echo $this->run($test, 'is_false', 'Is the exit status false ?', '');
		
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObject($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasError($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasNoContent($this->rildap->result);
		$this->printLdapResult($this->rildap->result);		
		
		
		
		$this->subTestTitle('Performing a wrong creation passing an empty entry');
		$this->getCodeOrigin();
		$this->rildap = new Ri_Ldap();
		
		$random = rand(999999,9999999);
		$surname = 'Coyote_'.$random;
		$name = 'Willy';
		
		//entry fields from the LDAP schema MUST attribute
		$entry = '';
		$dn = 'uid=myuid,ou=users,o=ce,dc=2v,dc=ntw';
		
		$test = $this->rildap->CEcreate($entry, $dn);
		echo $this->run($test, 'is_false', 'Is the exit status false ?', '');
		
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObject($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasError($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasNoContent($this->rildap->result);
		$this->printLdapResult($this->rildap->result);	

		
		
		
		
		
		$this->subTestTitle('Performing a good creation forgetting the objectClass');
		$this->getCodeOrigin();
		$this->rildap = new Ri_Ldap();
		
		$random = rand(999999,9999999);
		$surname = 'Coyote_'.$random;
		$name = 'Willy';
		
		//entry fields from the LDAP schema MUST attribute
		$entry = array();
		$entry['uid'] = $random;
		$entry['cn'] = $name.' '.$surname;
		$entry['sn'] = $surname;
		$entry['givenName'] = $name;
		$entry['displayName'] = $entry['cn'];
		$entry['fileAs'] = $entry['cn'];
		$entry['userPassword'] = 'mypassword';
		$entry['enabled'] = 'TRUE';
		$entry['entryCreatedBy'] = 'unit tests';
		$entry['category'] = 'mycategory';
		//$entry['objectClass'] = 'dueviPerson';
		
		$dn = 'uid='.$entry['uid'].','.$this->baseDN;
		
		$test = $this->rildap->CEcreate($entry,$dn);
		echo $this->run($test, 'is_false', 'Is the exit status false ?', '');
		
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObject($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasError($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasNoContent($this->rildap->result);
		$this->printLdapResult($this->rildap->result);		
		
		
		

		
		$this->subTestTitle('Performing a good creation forgetting the uid');
		$this->getCodeOrigin();
		$this->rildap = new Ri_Ldap();
		
		$random = rand(999999,9999999);
		$surname = 'Coyote_'.$random;
		$name = 'Willy';
		
		//entry fields from the LDAP schema MUST attribute
		$entry = array();
		//$entry['uid'] = $random;
		$entry['cn'] = $name.' '.$surname;
		$entry['sn'] = $surname;
		$entry['givenName'] = $name;
		$entry['displayName'] = $entry['cn'];
		$entry['fileAs'] = $entry['cn'];
		$entry['userPassword'] = 'mypassword';
		$entry['enabled'] = 'TRUE';
		$entry['entryCreatedBy'] = 'unit tests';
		$entry['category'] = 'mycategory';
		$entry['objectClass'] = 'dueviPerson';
		
		$dn = 'uid='.$entry['uid'].','.$this->baseDN;
		
		$test = $this->rildap->CEcreate($entry,$dn);
		echo $this->run($test, 'is_false', 'Is the exit status false ?', '');
		
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObject($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasError($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasNoContent($this->rildap->result);
		$this->printLdapResult($this->rildap->result);		
		
		
		
		
		
		$this->subTestTitle('Creating an already existent entry');
		$this->getCodeOrigin();
		$this->rildap = new Ri_Ldap();
		
		$random = rand(999999,9999999);
		$surname = 'Coyote_'.$random;
		$name = 'Willy';
		
		//entry fields from the LDAP schema MUST attribute
		$entry = array();
		$entry['uid'] = '10000000';
		$entry['cn'] = $name.' '.$surname;
		$entry['sn'] = $surname;
		$entry['givenName'] = $name;
		$entry['displayName'] = $entry['cn'];
		$entry['fileAs'] = $entry['cn'];
		$entry['userPassword'] = 'mypassword';
		$entry['enabled'] = 'TRUE';
		$entry['entryCreatedBy'] = 'unit tests';
		$entry['category'] = 'mycategory';
		$entry['objectClass'] = 'dueviPerson';
		
		$dn = 'uid='.$entry['uid'].','.$this->baseDN;
		
		$test = $this->rildap->CEcreate($entry,$dn);
		echo $this->run($test, 'is_false', 'Is the exit status false ?', '');
		
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObject($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasError($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasNoContent($this->rildap->result);
		$this->printLdapResult($this->rildap->result);		
	}

	public function test_Ri_Ldap_search()
	{
		$this->testTitle('Testing the Ri_LDAP Object CEsearch() method');
		
		
		$this->subTestTitle('Performing a good search');
		$this->getCodeOrigin();
		$this->rildap = new Ri_Ldap();
		$baseDN = $this->baseDN; 
		$filter = '(uid=10000000)';
		$attributes = array(); 
		$test = $this->rildap->CEsearch($baseDN, $filter, $attributes);	
		echo $this->run($test, 'is_true', 'Is the exit status true ?', '');
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObject($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasNoError($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasContent($this->rildap->result);
		$this->printLdapResult($this->rildap->result);
		
		

		

		
		$this->subTestTitle('Performing a search which returns only one attribute');
		$this->getCodeOrigin();
		$this->rildap = new Ri_Ldap();
		$baseDN = $this->baseDN;
		$filter = '(uid=10000000)';
		$attributes = array('uid');
		$test = $this->rildap->CEsearch($baseDN, $filter, $attributes);
		echo $this->run($test, 'is_true', 'Is the exit status true ?', '');
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObject($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasNoError($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasContent($this->rildap->result);
		$this->printLdapResult($this->rildap->result);		
		
		
		
		
		
		
		
		$this->subTestTitle('Performing a bad search with wronge BaseDN');
		$this->getCodeOrigin();
		$this->rildap = new Ri_Ldap();
		$baseDN = 'ou=user,o=ce,dc=2v,dc=ntw';
		$filter = '(uid=10000000)';
		$attributes = array('uid','cn','sn','giveName');
		$test = $this->rildap->CEsearch($baseDN, $filter, $attributes);
		echo $this->run($test, 'is_false', 'Is the exit status false ?', '');
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObject($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasError($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasNoContent($this->rildap->result);
		$this->printLdapResult($this->rildap->result);		


		
		
		
		
		$this->subTestTitle('Performing a bad search passing a filter as array');
		$this->getCodeOrigin();
		$this->rildap = new Ri_Ldap();
		$baseDN = $this->baseDN;
		$filter = array('(uid=10000000)');
		$attributes = array('uid','cn','sn','giveName');
		$test = $this->rildap->CEsearch($baseDN, $filter, $attributes);
		echo $this->run($test, 'is_false', 'Is the exit status false when I search with a wrong filter ?', '');
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObject($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasError($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasNoContent($this->rildap->result);
		$this->printLdapResult($this->rildap->result);
				

		
		

		$this->subTestTitle('Performing a bad search passing "attributes" as a string');
		$this->getCodeOrigin();
		$this->rildap = new Ri_Ldap();
		$baseDN = $this->baseDN;
		$filter = '(uid=10000000)';
		$attributes = 'uid,cn,sn,giveName';
		$test = $this->rildap->CEsearch($baseDN, $filter, $attributes);
		echo $this->run($test, 'is_false', 'Is the exit status false when I search with a wrong filter ?', '');
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObject($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasError($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasNoContent($this->rildap->result);
		$this->printLdapResult($this->rildap->result);		

		

		
		$this->subTestTitle('Performing a bad search passing attributesOnly = 3');
		$this->getCodeOrigin();
		$this->rildap = new Ri_Ldap();
		$baseDN = $this->baseDN;
		$filter = '(uid=10000000)';
		$attributes = array('uid','cn','sn','giveName');
		$test = $this->rildap->CEsearch($baseDN, $filter, $attributes, '3');
		echo $this->run($test, 'is_false', 'Is the exit status false when I search with a wrong filter ?', '');
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObject($this->rildap->result);
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasError($this->rildap->result);
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasNoContent($this->rildap->result);
		$this->printLdapResult($this->rildap->result);		

		
		
		
		
		
		$this->subTestTitle('Performing a good search for a not existent entry');
		$this->getCodeOrigin();
		$this->rildap = new Ri_Ldap();
		$baseDN = $this->baseDN;
		$filter = '(uid=100000000000000000000000000000000000000)';
		$attributes = array('uid','cn','sn','giveName');
		$test = $this->rildap->CEsearch($baseDN, $filter, $attributes);
		echo $this->run($test, 'is_true', 'Is the exit status true when I search for an entry that can not be found ?', '');
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObject($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasNoError($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasNoContent($this->rildap->result);
		$this->printLdapResult($this->rildap->result);		
		
		
		
		
		
		$this->subTestTitle('Performing a bad search with wronge BaseDN and wrong filter');
		echo '<p>I expect to not get the notification about the baseDN because the filter is tested first</p>';
		$this->getCodeOrigin();
		$this->rildap = new Ri_Ldap();
		$baseDN = 'ou=user,o=ce,dc=2v,dc=ntw';
		$filter = array('(uid=100000000000000000000000000000000000000)');
		$attributes = array('uid','cn','sn','giveName');
		$test = $this->rildap->CEsearch($baseDN, $filter, $attributes);
		echo $this->run($test, 'is_false', 'Is the exit status false ?', '');
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObject($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasError($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasNoContent($this->rildap->result);
		$this->printLdapResult($this->rildap->result);


		
		
		
		$this->subTestTitle('Performing a good search with a wildcard filter');
		$this->getCodeOrigin();
		$this->rildap = new Ri_Ldap();
		$baseDN = $this->baseDN;
		$filter = '(uid=100*)';
		$attributes = array('uid','cn','sn','giveName');
		$test = $this->rildap->CEsearch($baseDN, $filter, $attributes);
		echo $this->run($test, 'is_true', 'Is the exit status true ?', '');
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObject($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasNoError($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasContent($this->rildap->result);
		
		
		
		
		$this->getCodeOrigin();
		$test = isset($this->rildap->result->data->results_number);
		echo $this->run($test, 'is_true', 'Is the results_number set ?', '');
		
		
		

		$this->getCodeOrigin();
		$test = isset($this->rildap->result->data->sent_back_results_number);
		echo $this->run($test, 'is_true', 'Is the sent_back_results_number set ?', '');
		
		
		
		
		
		$this->getCodeOrigin();
		echo $this->run($this->rildap->result->data->results_pages, '1', 'Is the total number of pages equal to 1 ?', '');
		
		
		
		
		$this->getCodeOrigin();
		echo $this->run($this->rildap->result->data->results_page, '1', 'Is the current page number equal to 1 ?', '');
		$this->printLdapResult($this->rildap->result);		
		
		
		
		
		
		
		
		
		$this->subTestTitle('Performing a good search with a wildcard filter and good pagination parameters');
		$this->getCodeOrigin();
		$this->rildap = new Ri_Ldap();
		$baseDN = $this->baseDN;
		$filter = '(givenName=Willy)';
		$attributes = array('uid','cn','sn','giveName');
		$sort_by = array('sn','givenName');
		$wanted_page = 1;
		$items_per_page = 3;
		$test = $this->rildap->CEsearch($baseDN, $filter, $attributes, 0, null, $sort_by, 'desc', $wanted_page, $items_per_page);
		//$attributesOnly = 0, $deref = null, array $sort_by = null, $flow_order = null, $wanted_page = null, $items_page = null
		echo $this->run($test, 'is_true', 'Is the exit status true ?', '');
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObject($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasNoError($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasContent($this->rildap->result);
		
		
		
		
		$this->getCodeOrigin();
		$test = isset($this->rildap->result->data->results_number);
		echo $this->run($test, 'is_true', 'Is the results_number set ?', '');
		
		
		
		
		$this->getCodeOrigin();
		$test = isset($this->rildap->result->data->sent_back_results_number);
		echo $this->run($test, 'is_true', 'Is the sent_back_results_number set ?', '');
		
		
		
		
		$this->getCodeOrigin();
		$test = ($this->rildap->result->data->results_pages > 1) ? true : false;
		echo $this->run($test, 'is_true', 'Is the total number of pages > 1 ?', '');
		
		
		
		$this->getCodeOrigin();
		$test = ($wanted_page = $this->rildap->result->data->results_page) ? true : false;
		echo $this->run($test, 'is_true', 'Is the current page number equal to '.$wanted_page.' ?', '');
		

		
		
		$this->getCodeOrigin();
		$test = ($items_per_page = $this->rildap->result->data->sent_back_results_number) ? true : false;
		echo $this->run($test, 'is_true', 'Is the declared number of items per page equal to '.$items_per_page.' ?', '');

		
		
		

		$this->getCodeOrigin();
		$test = ($items_per_page = count($this->rildap->result->data->content)) ? true : false;
		echo $this->run($test, 'is_true', 'Is the real number of items per page equal to '.$items_per_page.' ?', '');
		$this->printLdapResult($this->rildap->result);		
		
		
		
		
		
		$this->subTestTitle('Performing a good search with a wildcard filter and wrong pagination parameter (order = myorder)');
		$this->getCodeOrigin();
		$this->rildap = new Ri_Ldap();
		$baseDN = $this->baseDN;
		$filter = '(uid=10*)';
		$attributes = array('uid','cn','sn','giveName');
		$sort_by = array('sn','givenName');
		$wanted_page = 1;
		$items_per_page = 3;
		$order = 'myorder';
		$test = $this->rildap->CEsearch($baseDN, $filter, $attributes, 0, null, $sort_by, $order, $wanted_page, $items_per_page);
		echo $this->run($test, 'is_false', 'Is the exit status false ?', '');
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObject($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasError($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasNoContent($this->rildap->result);
		$this->printLdapResult($this->rildap->result);
				
		

		
		$this->subTestTitle('Performing a good search with a wildcard filter and wrong pagination parameter (wanted_page not integer)');
		$this->getCodeOrigin();
		$this->rildap = new Ri_Ldap();
		$baseDN = $this->baseDN;
		$filter = '(uid=10*)';
		$attributes = array('uid','cn','sn','giveName');
		$sort_by = array('sn','givenName');
		$wanted_page = array('1');
		$items_per_page = 3;
		$order = 'asc';
		$test = $this->rildap->CEsearch($baseDN, $filter, $attributes, 0, null, $sort_by, $order, $wanted_page, $items_per_page);
		echo $this->run($test, 'is_false', 'Is the exit status false ?', '');
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObject($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasError($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasNoContent($this->rildap->result);
		$this->printLdapResult($this->rildap->result);

		
		
		
		$this->subTestTitle('Performing a good search with a wildcard filter and wrong pagination parameter (items per page not integer)');
		$this->getCodeOrigin();
		$this->rildap = new Ri_Ldap();
		$baseDN = $this->baseDN;
		$filter = '(uid=10*)';
		$attributes = array('uid','cn','sn','giveName');
		$sort_by = array('sn','givenName');
		$wanted_page = 1;
		$items_per_page = '3';
		$order = 'asc';
		$test = $this->rildap->CEsearch($baseDN, $filter, $attributes, 0, null, $sort_by, $order, $wanted_page, $items_per_page);
		echo $this->run($test, 'is_false', 'Is the exit status false ?', '');
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObject($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasError($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasNoContent($this->rildap->result);
		$this->printLdapResult($this->rildap->result);		
	}
	
	
	public function test_Ri_Ldap_read()
	{
		$this->testTitle('Testing the Ri_LDAP Object CEread() method');
		
		$this->subTestTitle('Reading an existent entry');
		
		$dn = $this->get_random_entry();
		
		if($dn)
		{
			$this->rildap = new Ri_Ldap();
			$this->rildap->dn = $dn;
			$this->getCodeOrigin();
			$test = $this->rildap->CEread();
			echo $this->run($test, 'is_true', 'Is the exit status true ?', '');
			
			
			
			$this->getCodeOrigin();
			$this->checkLdapReturnObject($this->rildap->result);
			
			
			
			$this->getCodeOrigin();
			$this->checkLdapReturnObjectHasNoError($this->rildap->result);
			
			
			
			$this->getCodeOrigin();
			$this->checkLdapReturnObjectHasContent($this->rildap->result);
			$this->printLdapResult($this->rildap->result);
		}

		
		$this->subTestTitle('Reading a non existent entry');
		$this->rildap = new Ri_Ldap();
		$this->rildap->dn = 'uid=notexistent,'.$this->baseDN;
		$this->getCodeOrigin();
		$test = $this->rildap->CEread();
		echo $this->run($test, 'is_true', 'Is the exit status true ?', '');
			
			
			
		$this->getCodeOrigin();
		$this->checkLdapReturnObject($this->rildap->result);
			
			
			
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasNoError($this->rildap->result);
			
			
			
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasNoContent($this->rildap->result);
		$this->printLdapResult($this->rildap->result);		
	}

	
	private function get_random_entry()
	{
		$this->rildap = new Ri_Ldap();
		
		$this->subTestTitle('Searching for a valid entry');
		$this->getCodeOrigin();
		$baseDN = $this->baseDN;
		$filter = '(givenName=*)';
		$attributes = array('uid');
		$test = $this->rildap->CEsearch($baseDN, $filter, $attributes);
		echo $this->run($test, 'is_true', 'Is the exit status true ?', '');
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObject($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasNoError($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasContent($this->rildap->result);
		
		
		//take the last result
		$dn = false;
		$content = $this->rildap->result->data->content;
		$item = array_pop($content);
		if($item['uid'][0] == '10000000') $item = array_pop($content);
		if(!empty($item['uid'][0])) $dn = 'uid='.$item['uid'][0].','.$this->baseDN;
		
		return $dn;		
	}
		
	
	public function test_Ri_Ldap_update()
	{

		$this->testTitle('Testing the Ri_LDAP Object CEupdate() method');
		
		$dn = $this->get_random_entry();
		
		$this->rildap = new Ri_Ldap();
		$entry = array();
		$entry['mozillaHomeCountryName'] = 'USA';
		
		$this->subTestTitle('Updating the attribute mozillaHomeCountryName to "USA" for entry with dn: '.$dn);
		$this->getCodeOrigin();
		$test = $this->rildap->CEupdate($entry, $dn);
		echo $this->run($test, 'is_true', 'Is the exit status true ?', '');
		
	
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObject($this->rildap->result);
			
			
			
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasNoError($this->rildap->result);
			
			
			
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasNoContent($this->rildap->result);
		$this->printLdapResult($this->rildap->result);
		
		
		
		
		
		$dn = $this->get_random_entry();
		
		$this->rildap = new Ri_Ldap();
		$entry = 'fake';
		
		$this->subTestTitle('Updating the entry with dn: '.$dn.' using a string as entry');
		$this->getCodeOrigin();
		$test = $this->rildap->CEupdate($entry, $dn);
		echo $this->run($test, 'is_false', 'Is the exit status true ?', '');
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObject($this->rildap->result);
			
			
			
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasError($this->rildap->result);
			
			
			
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasNoContent($this->rildap->result);
		$this->printLdapResult($this->rildap->result);		
		
		
		
		
		
		$this->rildap = new Ri_Ldap();
		$entry = array();
		$entry['this_is_not_an_attribute'] = 'USA';
		
		$this->subTestTitle('Updating the entry with dn: '.$dn.' using an unknown attribute');
		$this->getCodeOrigin();
		$test = $this->rildap->CEupdate($entry, $dn);
		echo $this->run($test, 'is_false', 'Is the exit status true ?', '');
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObject($this->rildap->result);
			
			
			
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasError($this->rildap->result);
			
			
			
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasNoContent($this->rildap->result);
		$this->printLdapResult($this->rildap->result);		
	}
	
	
	public function test_Ri_Ldap_delete()
	{
		$this->testTitle('Testing the Ri_LDAP Object CEdelete() method');
	
		$dn = $this->get_random_entry();
		if($dn)
		{			
			$this->subTestTitle('Deleting entry with dn: '.$dn);
			$this->rildap = new Ri_Ldap();
			
			$this->getCodeOrigin();
			$test = $this->rildap->CEdelete($dn);
			echo $this->run($test, 'is_true', 'Is the exit status true ?', '');
			
			
			
			$this->getCodeOrigin();
			$this->checkLdapReturnObject($this->rildap->result);
			
			
			
			$this->getCodeOrigin();
			$this->checkLdapReturnObjectHasNoError($this->rildap->result);
			
			
			
			$this->getCodeOrigin();
			$this->checkLdapReturnObjectHasNoContent($this->rildap->result);
			$this->printLdapResult($this->rildap->result);
		}	
		unset($dn);
		
		
		
		
		
		$dn = $this->get_random_entry();
		if($dn)
		{
			$this->subTestTitle('Deleting entry with dn: '.$dn.' saving the dn in the object and avoiding to pass it as a param.');
			$this->rildap = new Ri_Ldap();
			$this->rildap->dn = $dn;
			$this->getCodeOrigin();
			$test = $this->rildap->CEdelete();
			echo $this->run($test, 'is_true', 'Is the exit status true ?', '');
				
				
				
			$this->getCodeOrigin();
			$this->checkLdapReturnObject($this->rildap->result);
				
				
				
			$this->getCodeOrigin();
			$this->checkLdapReturnObjectHasNoError($this->rildap->result);
				
				
				
			$this->getCodeOrigin();
			$this->checkLdapReturnObjectHasNoContent($this->rildap->result);
			$this->printLdapResult($this->rildap->result);
		}
		unset($dn);
		
		
		
		
		$dn = 'uid=do_not_exists,ou=users,o=ce,dc=2v,dc=ntw';
		$this->subTestTitle('Deleting a non existent entry with dn: '.$dn);
		$this->rildap = new Ri_Ldap();
		$this->rildap->dn = $dn;
		$test = $this->rildap->CEdelete();
		echo $this->run($test, 'is_false', 'Is the exit status false ?', '');
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObject($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasError($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasNoContent($this->rildap->result);
		$this->printLdapResult($this->rildap->result);

		
		
		
		$dn = array('uid=do_not_exists,ou=users,o=ce,dc=2v,dc=ntw');
		$this->subTestTitle('Deleting by passing an array as dn ');
		$this->rildap = new Ri_Ldap();
		$this->rildap->dn = $dn;
		$this->getCodeOrigin();
		$test = $this->rildap->CEdelete();
		echo $this->run($test, 'is_false', 'Is the exit status false ?', '');
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObject($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasError($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasNoContent($this->rildap->result);
		$this->printLdapResult($this->rildap->result);

		
		
		
		
		$dn = $this->baseDN;
		$this->subTestTitle('Attempt to delete the baseDn ');
		$this->rildap = new Ri_Ldap();
		$this->rildap->dn = $dn;
		$this->getCodeOrigin();
		$test = $this->rildap->CEdelete();
		echo $this->run($test, 'is_false', 'Is the exit status false ?', '');
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObject($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasError($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasNoContent($this->rildap->result);
		$this->printLdapResult($this->rildap->result);		
	}
}