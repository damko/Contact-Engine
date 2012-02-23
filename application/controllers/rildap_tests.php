<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH.'/libraries/test_controller.php';



class Rildap_Tests extends Test_Controller {
	public function __construct()
	{
		parent::__construct();		
		
		$this->show_rest_return = false;

		if(isset($_GET['verbose']) && ($_GET['verbose'] == 'on'))
		{
			$this->show_rest_return = true;
		}

/*
 		if(ENVIRONMENT != 'production') {
			echo '<h2>please set the constant ENVIROMENT in index.php to "production" !</h2>';
			die();
		} 
*/

		//set up a new template for the tests output
		$this->unit->set_test_items(array('test_name', 'test_datatype', 'res_datatype', 'result', 'notes'));

		$str  = "\n".'<table style="width:100%; font-size:small; margin:0px 0; border-collapse:collapse; border:1px solid #CCC; background-color: #e8e8e8;">';
		$str .= '{rows}';
		$str .= "\n".'</table>';
		$this->unit->set_template($str); 	

		$str = "\n\t".'<tr>';
		$str .= "\n\t\t".'<th style="text-align: left; border-bottom:1px solid #CCC; width: 20%;">{item}</th>';
		$str .= "\n\t\t".'<td style="border-bottom:1px solid #CCC; width: 80%">{result}</td>';
		$str .= "\n\t".'</tr>';		
		$this->unit->set_template_rows($str);
		
		$this->load->spark('ri_ldap/0.0.1');
		
		$this->server = 'ldap://ldapmaster0:389';
		$this->ldapdn = 'cn=admin,dc=2v,dc=ntw';
		$this->ldappw = 'Wi7Xkcv300z';
		$this->version = '3';		
	}
	
	public function index()
	{

		$this->load->view('rildap_tests');
				
		echo '<h1>RiLdap Unit Tests</h1>
		<div>
			<h3>Before running these tests remind to: </h3>
			<ol>
			<li>set the connections parameters hardcoded in the tests file rildap_tests.php</li>
			</ol>
		</div>
		<div id="container">';

		//runs tests
		echo '<div id="left">';
		$this->test_LDAP_Connection();
		$this->test_Ri_LDAP_Initialize();
		$this->test_Ri_Ldap_search();
		echo '</div>';
		
		$this->printSummary();
		
		echo '<div></body></html>';
	}
	
	
	public function test_LDAP_Connection()
	{	
		$this->testTitle('Testing the LDAP Object connect() method');
		
		$this->getCodeOrigin();
		$this->ldap = new Ldap();
		$note = '';
		echo $this->run($this->ldap, 'is_object', 'Do I get the ldap object back?', $note);
		
		$old_ldap_server = $this->server;
		
		
		
		$this->getCodeOrigin();
		$this->ldap = new Ldap();
		$note = 'I\'m not even passing the ldap:// protocol.';
		$this->server = 'fake_server';
		$test = $this->ldap->connect($this->server,$this->ldapdn,$this->ldappw,$this->version);
		echo $this->run($test, 'is_false', 'Using '.$this->server.' as ldap connection.', $note);

		
		
		
		$this->getCodeOrigin();
		$this->ldap = new Ldap();
		$note = 'I\'m passing the ldap:// protocol but with a fake server name.';
		$this->server = 'fake_server';
		$test = $this->ldap->connect($this->server,$this->ldapdn,$this->ldappw,$this->version);
		echo $this->run($test, 'is_false', 'Using '.$this->server.' as ldap connection.', $note);

		
		
		$this->getCodeOrigin();
		$this->ldap = new Ldap();
		$note = '';
		$this->server = $old_ldap_server;
		$test = $this->ldap->connect($this->server,$this->ldapdn,$this->ldappw,$this->version);
		echo $this->run($test, 'is_true', 'Using '.$this->server.' as ldap connection.', $note);
		
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
	}
	
	
	public function test_Ri_Ldap_search()
	{
		$this->testTitle('Testing the Ri_LDAP Object CEsearch() method');
		
		
		$this->subTestTitle('Performing a good search');
		$this->getCodeOrigin();
		$this->rildap = new Ri_Ldap();
		$baseDn = 'ou=users,o=2v,dc=2v,dc=ntw'; 
		$filter = '(uid=10000000)';
		$attributes = array('uid','cn','sn','giveName'); 
		$test = $this->rildap->CEsearch($baseDn, $filter, $attributes);	
		echo $this->run($test, 'is_true', 'Do I get true back as exit status when I search for something meaningful ?', '');
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObject($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasNoError($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasData($this->rildap->result);
		$this->printLdapResult($this->rildap->result);
		
		

		

		
		$this->subTestTitle('Performing a search which returns only one attribute');
		$this->getCodeOrigin();
		$this->rildap = new Ri_Ldap();
		$baseDn = 'ou=users,o=2v,dc=2v,dc=ntw';
		$filter = '(uid=10000000)';
		$attributes = array('uid');
		$test = $this->rildap->CEsearch($baseDn, $filter, $attributes);
		echo $this->run($test, 'is_true', 'Do I get true back as exit status when I search for something meaningful ?', '');
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObject($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasNoError($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasData($this->rildap->result);
		$this->printLdapResult($this->rildap->result);		
		
		
		
		
		
		
		
		$this->subTestTitle('Performing a bad search with wronge BaseDN');
		$this->getCodeOrigin();
		$this->rildap = new Ri_Ldap();
		$baseDn = 'ou=user,o=2v,dc=2v,dc=ntw';
		$filter = '(uid=10000000)';
		$attributes = array('uid','cn','sn','giveName');
		$test = $this->rildap->CEsearch($baseDn, $filter, $attributes);
		echo $this->run($test, 'is_false', 'Do I get false back as exit status when I search with a wrong baseDN ?', '');
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObject($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasError($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasNoData($this->rildap->result);
		$this->printLdapResult($this->rildap->result);		


		
		
		
		
		$this->subTestTitle('Performing a bad search passing a filter as array');
		$this->getCodeOrigin();
		$this->rildap = new Ri_Ldap();
		$baseDn = 'ou=users,o=2v,dc=2v,dc=ntw';
		$filter = array('(uid=10000000)');
		$attributes = array('uid','cn','sn','giveName');
		$test = $this->rildap->CEsearch($baseDn, $filter, $attributes);
		echo $this->run($test, 'is_false', 'Do I get false back as exit status when I search with a wrong filter ?', '');
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObject($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasError($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasNoData($this->rildap->result);
		$this->printLdapResult($this->rildap->result);
				

		
		

		$this->subTestTitle('Performing a bad search passing "attributes" as a string');
		$this->getCodeOrigin();
		$this->rildap = new Ri_Ldap();
		$baseDn = 'ou=users,o=2v,dc=2v,dc=ntw';
		$filter = '(uid=10000000)';
		$attributes = 'uid,cn,sn,giveName';
		$test = $this->rildap->CEsearch($baseDn, $filter, $attributes);
		echo $this->run($test, 'is_false', 'Do I get false back as exit status when I search with a wrong filter ?', '');
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObject($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasError($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasNoData($this->rildap->result);
		$this->printLdapResult($this->rildap->result);		

		

		
		$this->subTestTitle('Performing a bad search passing attributesOnly = 3');
		$this->getCodeOrigin();
		$this->rildap = new Ri_Ldap();
		$baseDn = 'ou=users,o=2v,dc=2v,dc=ntw';
		$filter = '(uid=10000000)';
		$attributes = array('uid','cn','sn','giveName');
		$test = $this->rildap->CEsearch($baseDn, $filter, $attributes, '3');
		echo $this->run($test, 'is_false', 'Do I get false back as exit status when I search with a wrong filter ?', '');
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObject($this->rildap->result);
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasError($this->rildap->result);
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasNoData($this->rildap->result);
		$this->printLdapResult($this->rildap->result);		

		
		
		
		
		
		$this->subTestTitle('Performing a good search for a not existent entry');
		$this->getCodeOrigin();
		$this->rildap = new Ri_Ldap();
		$baseDn = 'ou=users,o=2v,dc=2v,dc=ntw';
		$filter = '(uid=100000000000000000000000000000000000000)';
		$attributes = array('uid','cn','sn','giveName');
		$test = $this->rildap->CEsearch($baseDn, $filter, $attributes);
		echo $this->run($test, 'is_true', 'Do I get true back as exit status when I search for an entry that can not be found ?', '');
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObject($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasNoError($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasData($this->rildap->result);
		$this->printLdapResult($this->rildap->result);		
		
		
		
		
		
		$this->subTestTitle('Performing a bad search with wronge BaseDN and wrong filter');
		echo '<p>I expect to not get the notification about the baseDN because the filter is tested first</p>';
		$this->getCodeOrigin();
		$this->rildap = new Ri_Ldap();
		$baseDn = 'ou=user,o=2v,dc=2v,dc=ntw';
		$filter = array('(uid=100000000000000000000000000000000000000)');
		$attributes = array('uid','cn','sn','giveName');
		$test = $this->rildap->CEsearch($baseDn, $filter, $attributes);
		echo $this->run($test, 'is_false', 'Do I get false back as exit status when I search with a wrong baseDN ?', '');
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObject($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasError($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasNoData($this->rildap->result);
		$this->printLdapResult($this->rildap->result);


		
		
		
		$this->subTestTitle('Performing a good search with a wildcard filter');
		$this->getCodeOrigin();
		$this->rildap = new Ri_Ldap();
		$baseDn = 'ou=users,o=2v,dc=2v,dc=ntw';
		$filter = '(uid=100*)';
		$attributes = array('uid','cn','sn','giveName');
		$test = $this->rildap->CEsearch($baseDn, $filter, $attributes);
		echo $this->run($test, 'is_true', 'Do I get true back as exit status when I search with a wrong baseDN ?', '');
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObject($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasNoError($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasData($this->rildap->result);
		
		
		
		
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
		$baseDn = 'ou=users,o=2v,dc=2v,dc=ntw';
		$filter = '(uid=10*)';
		$attributes = array('uid','cn','sn','giveName');
		$sort_by = array('sn','givenName');
		$wanted_page = 1;
		$items_per_page = 3;
		$test = $this->rildap->CEsearch($baseDn, $filter, $attributes, 0, null, $sort_by, 'desc', $wanted_page, $items_per_page);
		//$attributesOnly = 0, $deref = null, array $sort_by = null, $flow_order = null, $wanted_page = null, $items_page = null
		echo $this->run($test, 'is_true', 'Do I get true back as exit status ?', '');
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObject($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasNoError($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasData($this->rildap->result);
		
		
		
		
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
		$baseDn = 'ou=users,o=2v,dc=2v,dc=ntw';
		$filter = '(uid=10*)';
		$attributes = array('uid','cn','sn','giveName');
		$sort_by = array('sn','givenName');
		$wanted_page = 1;
		$items_per_page = 3;
		$order = 'myorder';
		$test = $this->rildap->CEsearch($baseDn, $filter, $attributes, 0, null, $sort_by, $order, $wanted_page, $items_per_page);
		echo $this->run($test, 'is_false', 'Do I get false back as exit status when I search with a wrong baseDN ?', '');
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObject($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasError($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasNoData($this->rildap->result);
		$this->printLdapResult($this->rildap->result);
				
		

		
		$this->subTestTitle('Performing a good search with a wildcard filter and wrong pagination parameter (wanted_page not integer)');
		$this->getCodeOrigin();
		$this->rildap = new Ri_Ldap();
		$baseDn = 'ou=users,o=2v,dc=2v,dc=ntw';
		$filter = '(uid=10*)';
		$attributes = array('uid','cn','sn','giveName');
		$sort_by = array('sn','givenName');
		$wanted_page = array('1');
		$items_per_page = 3;
		$order = 'asc';
		$test = $this->rildap->CEsearch($baseDn, $filter, $attributes, 0, null, $sort_by, $order, $wanted_page, $items_per_page);
		echo $this->run($test, 'is_false', 'Do I get false back as exit status when I search with a wrong baseDN ?', '');
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObject($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasError($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasNoData($this->rildap->result);
		$this->printLdapResult($this->rildap->result);

		
		
		
		$this->subTestTitle('Performing a good search with a wildcard filter and wrong pagination parameter (items per page not integer)');
		$this->getCodeOrigin();
		$this->rildap = new Ri_Ldap();
		$baseDn = 'ou=users,o=2v,dc=2v,dc=ntw';
		$filter = '(uid=10*)';
		$attributes = array('uid','cn','sn','giveName');
		$sort_by = array('sn','givenName');
		$wanted_page = 1;
		$items_per_page = '3';
		$order = 'asc';
		$test = $this->rildap->CEsearch($baseDn, $filter, $attributes, 0, null, $sort_by, $order, $wanted_page, $items_per_page);
		echo $this->run($test, 'is_false', 'Do I get false back as exit status when I search with a wrong baseDN ?', '');
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObject($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasError($this->rildap->result);
		
		
		
		$this->getCodeOrigin();
		$this->checkLdapReturnObjectHasNoData($this->rildap->result);
		$this->printLdapResult($this->rildap->result);		
	}
}