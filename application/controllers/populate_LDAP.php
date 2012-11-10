<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH.'/libraries/test_controller.php';



class Populate_LDAP extends CI_Controller {
	
	public $server;
	public $ldapdn;
	public $ldappw;
	public $version;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->load->spark('ri_ldap/0.0.1');
		

		$tmp = $this->config->item('ldapMaster');		
		$this->server = $tmp[0]['url'];
		$this->ldapdn = $tmp[0]['binddn'];
		$this->ldappw = $tmp[0]['bindpw'];
		$this->version = $tmp[0]['version'];

	}	
	
	public function index(){
		echo '<html><head>
		<style type="text/css">
		body {
		background-color: #fff;
		margin: 40px;
		font-family: Lucida Grande, Verdana, Sans-serif;
		font-size: 13px;
		color: #4F5155;
		}
		i {
		color: green;
		}
		</style></head><body>';
		
		echo '<h2>Populating the LDAP database</h2>';
		echo 'Several unit tests need a populated LDAP database to work properly.<br/><br/>';
		echo 'Click <a href="/index.php/populate_LDAP/persons/" target="_blank">here</a> to populate LDAP with persons.<br/><br/>';
		echo 'Click <a href="/index.php/populate_LDAP/organizations/" target="_blank">here</a> to populate LDAP with organizations.<br/><br/>';
		echo 'Click <a href="/index.php/populate_LDAP/locations/" target="_blank">here</a> to populate LDAP with locations.<br/><br/>';
		echo '</body></html>';
	}
	
	public function delete_all_persons() {
		$this->benchmark->mark('code_start');
		$this->rildap = new Ri_Ldap();
		$baseDN = $this->baseDN;
		$filter = '(givenName=*)';
		$attributes = array('uid');
		if($this->rildap->CEsearch($baseDN, $filter, $attributes)) {
			$dns = array();
			$content = $this->rildap->result->data->content;
			$index = count($content);
			foreach ($content as $key => $item) {
				$this->rildap->dn = 'uid='.$item['uid'][0].','.$this->baseDN;
				$test = $this->rildap->CEdelete();
				if($test) echo "The entry with dn <i>".$this->rildap->dn."</i> has been deleted.<br/>";
			}
		}
		$this->benchmark->mark('code_end');
	
		$elapsed_time = $this->benchmark->elapsed_time('code_start', 'code_end');
	
		echo '<br/>';
		echo 'Elapsed time: '.$elapsed_time.' seconds<br/>';
		if($index > 0) echo 'Each of the '.$index.' operations took: '.$elapsed_time/$index.' seconds.';
	}	

	public function delete_all_organizations() {
		$this->benchmark->mark('code_start');
		$this->rildap = new Ri_Ldap();
		$baseDN = $this->baseDN;
		$filter = '(o=*)';
		$attributes = array('oid');
		if($this->rildap->CEsearch($baseDN, $filter, $attributes)) {
			$dns = array();
			$content = $this->rildap->result->data->content;
			$index = count($content);
			foreach ($content as $key => $item) {
				$this->rildap->dn = 'oid='.$item['oid'][0].','.$this->baseDN;
				$test = $this->rildap->CEdelete();
				if($test) echo "The entry with dn <i>".$this->rildap->dn."</i> has been deleted.<br/>";
			}
		}
		$this->benchmark->mark('code_end');
	
		$elapsed_time = $this->benchmark->elapsed_time('code_start', 'code_end');
	
		echo '<br/>';
		echo 'Elapsed time: '.$elapsed_time.' seconds<br/>';
		if($index > 0) echo 'Each of the '.$index.' operations took: '.$elapsed_time/$index.' seconds.';
	}

	public function delete_all_locations() {
		$this->benchmark->mark('code_start');
		$this->rildap = new Ri_Ldap();
		$baseDN = $this->baseDN;
		$filter = '(locId=*)';
		$attributes = array('locId');
		if($this->rildap->CEsearch($baseDN, $filter, $attributes)) {
			$dns = array();
			$content = $this->rildap->result->data->content;
			$index = count($content);
			foreach ($content as $key => $item) {
				$this->rildap->dn = 'locId='.$item['locId'][0].','.$this->baseDN;
				$test = $this->rildap->CEdelete();
				if($test) echo "The entry with dn <i>".$this->rildap->dn."</i> has been deleted.<br/>";
			}
		}
		$this->benchmark->mark('code_end');
	
		$elapsed_time = $this->benchmark->elapsed_time('code_start', 'code_end');
	
		echo '<br/>';
		echo 'Elapsed time: '.$elapsed_time.' seconds<br/>';
		if($index > 0) echo 'Each of the '.$index.' operations took: '.$elapsed_time/$index.' seconds.';
	}
	
	
	public function persons() {
		
		$this->baseDN = 'ou=users,o=ce,dc=2v,dc=ntw';
		
		echo '<html><head>
		<style type="text/css">
		body {
			background-color: #fff;
			margin: 40px;
			font-family: Lucida Grande, Verdana, Sans-serif;
			font-size: 11px;
			color: #4F5155;
		}
		i {
			color: green;
		}
		</style>
			<script type="text/javascript">
			function show_confirm()
			{
			  	location.href="?startpopulating=true";
			}
			</script>	
		</head><body>';
		
		echo '<h2>Populating the LDAP branch <i>'.$this->baseDN.'</i></h2>';
		echo 'This will delete all the data in the branch and will create random entries!  ';
		echo '<input type="button" onclick="show_confirm()" value="Continue" />';
		
		if(!isset($_GET['startpopulating']) || $_GET['startpopulating'] != true) die();
		
		echo '<h3>Cleaning the branch</h3>';
		
		$this->delete_all_persons();
		
		$index = 100;
		echo '<h3>Creation of '.$index.' random persons</h3>';
		
		$this->ldap = new Ldap();
		$connect = $this->ldap->connect($this->server,$this->ldapdn,$this->ldappw,$this->version);
		
		if(!$connect) die('I can not connect to LDAP. Check the settings');
		
		$this->benchmark->mark('code_start');

		//creating an entry with a specific uid that will be used in the tests
		$random = rand(999999,9999999);
		$surname = 'Coyote';
		$name = 'Wile';
		
		$entry = array();
		$entry['uid'] = '10000000';
		$entry['cn'] = $name.' '.$surname;
		$entry['sn'] = $surname;
		$entry['givenName'] = $name;
		$entry['displayName'] = $entry['cn'];
		$entry['fileAs'] = $entry['cn'];
		$entry['userPassword'] = 'password';
		$entry['enabled'] = 'TRUE';
		$entry['entryCreatedBy'] = 'unit tests';
		$entry['category'] = 'mycategory';
		$entry['objectClass'] = 'dueviPerson';
		$entry['mozillaHomeLocalityName'] = 'Varese';
		$entry['mozillaHomeCountryName'] = 'Italy';
		$entry['mail'] = strtolower($name.'@'.$surname.'.com');
		$entry['labeledURI'] = strtolower('http://www.'.$surname.'.com');
		$entry['mobile'] = '+1'.($random*2);
		$entry['homePhone'] = '+39'.($random*3);		
		$entry['entryCreationDate'] = date('Y-m-d');
		$entry['dbId'] = rand(1000,9999);
		
		$dn = 'uid='.$entry['uid'].','.$this->baseDN;
		
		$created = $this->ldap->create($entry, $dn);
		if($created) echo "Person #1 with dn <i>".$dn."</i> successfully created.<br/>";
		
		//creating all the other
		for ($i = 2; $i <= $index; $i++) {
			
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
			$entry['userPassword'] = 'password';
			$entry['enabled'] = 'TRUE';
			$entry['entryCreatedBy'] = 'unit tests';
			$entry['category'] = 'mycategory';
			$entry['objectClass'] = 'dueviPerson';
			$entry['mozillaHomeLocalityName'] = 'Varese';
			$entry['mozillaHomeCountryName'] = 'Italy';
			$entry['mail'] = strtolower($name.'@'.$surname.'.com');
			$entry['labeledURI'] = strtolower('http://www.'.$surname.'.com');
			$entry['mobile'] = '+1'.($random*2);
			$entry['homePhone'] = '+39'.($random*3);	
			$entry['entryCreationDate'] = date('Y-m-d');
		
			$dn = 'uid='.$entry['uid'].','.$this->baseDN;
		
			$created = $this->ldap->create($entry, $dn);
			if($created) echo "Person #".$i." with dn <i>".$dn."</i> successfully created.<br/>";
		}
		$this->benchmark->mark('code_end');
		
		$elapsed_time = $this->benchmark->elapsed_time('code_start', 'code_end');
		
		echo '<br/>';
		echo 'Elapsed time: '.$elapsed_time.'<br/>';
		if($index > 0) echo 'Each operation took: '.$elapsed_time/$index;
		
		echo '</body>';
	}
	
	
	public function organizations() {
	
		$this->baseDN = 'ou=organizations,o=ce,dc=2v,dc=ntw';
		
		echo '<html><head>
		<style type="text/css">
		body {
		background-color: #fff;
		margin: 40px;
		font-family: Lucida Grande, Verdana, Sans-serif;
		font-size: 11px;
		color: #4F5155;
	}
	i {
	color: green;
	}
	</style>
	<script type="text/javascript">
	function show_confirm()
	{
	location.href="?startpopulating=true";
	}
	</script>
	</head><body>';
	
		echo '<h2>Populating the LDAP branch <i>'.$this->baseDN.'</i></h2>';
		echo 'This will delete all the data in the branch and will create random entries!  ';
		echo '<input type="button" onclick="show_confirm()" value="Continue" />';
	
		if(!isset($_GET['startpopulating']) || $_GET['startpopulating'] != true) die();
	
		echo '<h3>Cleaning the branch</h3>';
	
		$this->delete_all_organizations();
	
		$index = 100;
		echo '<h3>Creation of '.$index.' random organizations</h3>';
	
		$this->ldap = new Ldap();
		$connect = $this->ldap->connect($this->server,$this->ldapdn,$this->ldappw,$this->version);
	
		if(!$connect) die('I can not connect to LDAP. Check the settings');
	
		$this->benchmark->mark('code_start');
	
		//creating an entry with a specific oid that will be used in the tests
		$random = rand(999999,9999999);
		$name = 'ACME';
	
		$entry = array();
		$entry['oid'] = '10000000';
		$entry['o'] = $name;
		$entry['enabled'] = 'TRUE';
		$entry['entryCreatedBy'] = 'unit_tests';
 		$entry['l'] = 'Milano';
 		$entry['countryName'] = 'Italy';
		$entry['omail'] = strtolower('info@'.$name.'.com');
		$entry['oURL'] = strtolower('http://www.'.$name.'.com');
		$entry['oMobile'] = '+1'.($random*2);
		$entry['telephoneNumber'] = '+39'.($random*3);
		$entry['objectClass'] = 'dueviOrganization';
		
		$dn = 'oid='.$entry['oid'].','.$this->baseDN;
	
		$created = $this->ldap->create($entry, $dn);
		if($created) echo "Organization #1 with dn <i>".$dn."</i> successfully created.<br/>";
	
		//creating all the other
		for ($i = 2; $i <= $index; $i++) {
				
			$random = rand(999999,9999999);
			$name = 'ACME_yote'.$random;
	
			//entry fields from the LDAP schema MUST attribute
			$entry = array();
			$entry['oid'] = $random;
			$entry['o'] = $name;
			$entry['enabled'] = 'TRUE';
			$entry['entryCreatedBy'] = 'unit_tests';
 			$entry['l'] = 'Milano';
 			$entry['countryName'] = 'Italy';
 			$entry['omail'] = strtolower('info@'.$name.'.com');
 			$entry['oURL'] = strtolower('http://www.'.$name.'.com');
 			$entry['oMobile'] = '+1'.($random*2);
 			$entry['telephoneNumber'] = '+39'.($random*3);
			$entry['objectClass'] = 'dueviOrganization';
			
			$dn = 'oid='.$entry['oid'].','.$this->baseDN;
	
			$created = $this->ldap->create($entry, $dn);
			if($created) echo "Organization #".$i." with dn <i>".$dn."</i> successfully created.<br/>";
		}
		$this->benchmark->mark('code_end');
	
		$elapsed_time = $this->benchmark->elapsed_time('code_start', 'code_end');
	
		echo '<br/>';
		echo 'Elapsed time: '.$elapsed_time.'<br/>';
		if($index > 0) echo 'Each operation took: '.$elapsed_time/$index;
	
		echo '</body>';
	}

	
	public function locations() {
	
		$this->baseDN = 'ou=locations,o=ce,dc=2v,dc=ntw';
		
		echo '<html><head>
		<style type="text/css">
		body {
		background-color: #fff;
		margin: 40px;
		font-family: Lucida Grande, Verdana, Sans-serif;
		font-size: 11px;
		color: #4F5155;
	}
	i {
	color: green;
	}
	</style>
	<script type="text/javascript">
	function show_confirm()
	{
	location.href="?startpopulating=true";
	}
	</script>
	</head><body>';
	
		echo '<h2>Populating the LDAP branch <i>'.$this->baseDN.'</i></h2>';
		echo 'This will delete all the data in the branch and will create random entries!  ';
		echo '<input type="button" onclick="show_confirm()" value="Continue" />';
	
		if(!isset($_GET['startpopulating']) || $_GET['startpopulating'] != true) die();
	
		echo '<h3>Cleaning the branch</h3>';
	
		$this->delete_all_locations();
	
		$index = 100;
		echo '<h3>Creation of '.$index.' random locations</h3>';
	
		$this->ldap = new Ldap();
		$connect = $this->ldap->connect($this->server,$this->ldapdn,$this->ldappw,$this->version);
	
		if(!$connect) die('I can not connect to LDAP. Check the settings');
	
		$this->benchmark->mark('code_start');
	
		//creating an entry with a specific locId that will be used in the tests
		$random = rand(999999,9999999);
		$locDescription = 'MyDescription '.$random;
		
	
		$entry = array();
		$entry['locId'] = '10000000';
		$entry['locCity'] = 'MyCity_'.$random;
		$entry['locCountry'] = 'MyCountry_'.$random;
		$entry['locDescription'] = 'MyDescription_'.$random;
		$entry['locState'] = 'MyState_'.$random;
		$entry['locStreet'] = 'MyStreet_'.$random;
		$entry['objectClass'] = 'dueviLocation';
		
		$dn = 'locId='.$entry['locId'].','.$this->baseDN;
	
		$created = $this->ldap->create($entry, $dn);
		if($created) echo "Location #1 with dn <i>".$dn."</i> successfully created.<br/>";
	
		//creating all the other
		for ($i = 2; $i <= $index; $i++) {
				
			$random = rand(999999,9999999);
			$surname = 'Coyote_'.$random;
			$name = 'Willy';
	
			//entry fields from the LDAP schema MUST attribute
			$entry = array();
			$entry['locId'] = $random;
			$entry['locCity'] = 'MyCity_'.$random;
			$entry['locCountry'] = 'MyCountry_'.$random;
			$entry['locDescription'] = 'MyDescription_'.$random;
			$entry['locState'] = 'MyState_'.$random;
			$entry['locStreet'] = 'MyStreet_'.$random;
			$entry['objectClass'] = 'dueviLocation';
	
			$dn = 'locId='.$entry['locId'].','.$this->baseDN;
	
			$created = $this->ldap->create($entry, $dn);
			if($created) echo "Location #".$i." with dn <i>".$dn."</i> successfully created.<br/>";
		}
		$this->benchmark->mark('code_end');
	
		$elapsed_time = $this->benchmark->elapsed_time('code_start', 'code_end');
	
		echo '<br/>';
		echo 'Elapsed time: '.$elapsed_time.'<br/>';
		if($index > 0) echo 'Each operation took: '.$elapsed_time/$index;
	
		echo '</body>';
	}
	
}