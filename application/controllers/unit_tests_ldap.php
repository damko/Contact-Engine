<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH.'/libraries/test_controller.php';

class Unit_Tests_Ldap extends Test_Controller {
	public function __construct()
	{
		parent::__construct();		
		
		$this->load->spark('ri_ldap/0.0.1');
		
		$tmp = $this->config->item('ldapMaster');		
		$this->server = $tmp[0]['url'];
		$this->ldapdn = $tmp[0]['binddn'];
		$this->ldappw = $tmp[0]['bindpw'];
		$this->version = $tmp[0]['version'];
		$this->baseDN = 'ou=users,o=ce,dc=2v,dc=ntw';
	}
	
	public function index()
	{	
		$this->load->view('unit_tests');
				
		echo '<a href="/index.php/unit_tests/">Back</a> to unit-tests front page.<br/>';
		
		//runs tests
		echo '<div id="left">';
		$this->test_Ldap_Connection();
		$dn = $this->test_Ldap_create();
		if($dn) $this->test_Ldap_read($dn);
		if($dn) $this->test_Ldap_update($dn);
		if($dn) $this->test_Ldap_delete($dn);
		echo '</div>';
		
		$this->printSummary();
		
		echo '<div></body></html>';
	}
		
	public function test_Ldap_Connection()
	{
		$this->testTitle('Testing the LDAP Object connect() method');
	
		$this->getCodeOrigin();
		$this->ldap = new Ldap();
		$note = '';
		echo $this->run($this->ldap, 'is_object', 'Do I get the ldap object back?', $note);
	
		$old_ldap_server = $this->server;
	
		$this->getCodeOrigin();
		$this->ldap = new Ldap();
		$note = '';
		$this->server = $old_ldap_server;
		$test = $this->ldap->connect($this->server,$this->ldapdn,$this->ldappw,$this->version);
		echo $this->run($test, 'is_true', 'Using '.$this->server.' as ldap connection.', $note);
	
	}
	
	public function test_Ldap_create()
	{
		$this->testTitle('Testing the LDAP Object create() method');
	
	
		$this->subTestTitle('Person creation using all the mandatory attributes');
		$this->getCodeOrigin();
		$this->ldap = new Ldap();
		$this->ldap->connect($this->server,$this->ldapdn,$this->ldappw,$this->version);
	
	
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
	
		$test = $this->ldap->create($entry, $dn);
		echo $this->run($test, 'is_true', 'Is the exit status true ?', '');		
		
		if($test){
			$dn_save = $dn;
		}

		$this->subTestTitle('Creation of an Organization branch');
		$this->getCodeOrigin();
		
		$branchname = 'testorg_'.rand('100000', '999999');
		$entry = array();
		$entry['objectClass'][] = 'organization';
		$entry['objectClass'][] = 'top';
		$entry['o'] = $branchname;
		$entry['description'] = 'php generated branch';
		$dn = 'o='.$branchname.',dc=2v,dc=ntw';
		$test = $this->ldap->create($entry, $dn);
		echo $this->run($test, 'is_true', 'Is the exit status true ?', '');

		if($test){
			echo 'Created entry with dn '.$dn.'<br/>';
			return $dn_save;
		}
		
	}
	
	public function test_Ldap_read($dn)
	{
		$this->testTitle('Testing the LDAP Object read() method');
	
	
		$this->subTestTitle('Performing a good creation using all the mandatory attributes');
		$this->getCodeOrigin();
		$this->ldap = new Ldap();
		$this->ldap->connect($this->server,$this->ldapdn,$this->ldappw,$this->version);
	
		$test = $this->ldap->read($dn,'500','5','0');
		echo $this->run($test, 'is_true', 'Is the exit status true ?', '');
	
	}
	
	public function test_Ldap_update($dn)
	{
		$this->testTitle('Testing the LDAP Object update() method');
	
	
		$this->subTestTitle('Performing a good update');
		$this->getCodeOrigin();
		$this->ldap = new Ldap();
		$this->ldap->connect($this->server,$this->ldapdn,$this->ldappw,$this->version);
	
		//entry fields from the LDAP schema MUST attribute
		$entry = array();
		$entry['uid'] = '10000000';
		$entry['mozillaHomeCountryName'] = 'Italy';
	
		$dn = 'uid='.$entry['uid'].','.$this->baseDN;
	
		$test = $this->ldap->update($entry, $dn);
		echo $this->run($test, 'is_true', 'Is the exit status true ?', '');
	
		if($test){
			echo 'Created entry with dn '.$dn.'<br/>';
			return $dn;
		}
	
	}
	
	public function test_Ldap_delete($dn)
	{
		$this->testTitle('Testing the LDAP Object delete() method');
	
		if(is_null($dn)) return false;
	
		$this->subTestTitle('Deleting entry with dn: '.$dn);
		$this->getCodeOrigin();
		$this->ldap = new Ldap();
		$this->ldap->connect($this->server,$this->ldapdn,$this->ldappw,$this->version);
	
	
		$test = $this->ldap->delete($dn);
		echo $this->run($test, 'is_true', 'Is the exit status true ?', '');
	}	
}