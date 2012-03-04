<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * The Ri_Ldap object is an interface between the LDAP object class and the Person, Organization, Location object classes defined in 
 * the spark module ri_contact_engine.
 * If you just want to connect to LDAP and to perform raw commands on the LDAP you should use the LDAP class instead of this.
 * 
 * Its config file is in config/ri_ldap.php
 *
 * @var		$conf			protected array		Contains the configuration read from the config file
 * @var 	$servers 		protected array		Contains the list of servers to which connections were established
 * @var		$WrConnection	protected resource	Ldap connection resource pointing to the current master server
 * @var		$RoConnection	protected resource	Ldap connection resource pointing to the current slave server
 * 
 * @author 		Damiano Venturin
 * @copyright 	2V S.r.l.
 * @license		GPL
 * @link		http://www.squadrainformatica.com/en/development#mcbsb  MCB-SB official page
 * @since		Sep 02, 2011
 * 
 * @todo		
 */ 
class Ri_Ldap extends Ldap {
	protected $conf = array();
	protected $servers = array();  //contains the servers configuration 
	protected $WrConnection;
	protected $RoConnection;
	
	/**
	 * The constructor method loads the LDAP class constructor, validates the ri_ldap.php configuration file and sets $ldap->debug and $ldap->service_unavailable
	 * accordingly to the configuration file.
	 * 
	 * @access		public
	 * @param		none
	 * @return		none
	 * @example
	 * @see
	 * 
	 * @author 		Damiano Venturin
	 * @copyright 	2V S.r.l.
	 * @license		GPL
	 * @link		http://www.squadrainformatica.com/en/development#mcbsb  MCB-SB official page
	 * @since		Feb 16, 2012
	 * 
	 * @todo		
	 */
	public function __construct() {
		parent::__construct();
		
		$this->conf = $this->config->item('ldap');
		
		//validation for the config file
		$conf_entries = array('sizeLimit','timeLimit','defer','debug','service_unavailable');
		foreach ($conf_entries as $conf_entry) {
			if(is_null($this->conf[$conf_entry])) {
				return $this->report('configuration_empty',$conf_entry);
			}
		}		
		
		$this->debug = $this->conf['debug'];
		if( $this->service_unavailable = $this->conf['service_unavailable'] ) $this->report('service_unavailable', null);		
		
		log_message('debug', 'ri_ldap class has been loaded');
	}
	
	/**
	 * The destructor method destroyes the Ri_Ldap object and all the LDAP connections. 
	 * 
	 * @access		public
	 * @param		
	 * @var			
	 * @return		
	 * @example
	 * @see
	 * 
	 * @author 		Damiano Venturin
	 * @copyright 	2V S.r.l.
	 * @license		GPL
	 * @link		http://www.squadrainformatica.com/en/development#mcbsb  MCB-SB official page
	 * @since		Feb 16, 2012
	 * 
	 * @todo		
	 */
	public function __destruct() {
		foreach ($this->servers as $key => $item) {
			foreach ($item as $key => $server) {
				if(!empty($server['connection'])) 
				{
					$this->connection = $server['connection'];
					$this->disconnect();	
				}
			}
		}
		parent::__destruct();
	}
	
	public function getServers()
	{
		return $this->servers;
	}
	
	/**
	 * Starts the connection to the LDAP server (at least one master and one slave) 
	 * 
	 * @access		public
	 * @param		none
	 * @return		boolean
	 * @example
	 * @see
	 * 
	 * @author 		Damiano Venturin
	 * @copyright 	2V S.r.l.
	 * @license		GPL
	 * @link		http://www.squadrainformatica.com/en/development#mcbsb  MCB-SB official page
	 * @since		Sep 02, 2011
	 * 
	 * @todo		
	 */
	public function initialize()
	{
		//establish the connection to one of the Master servers
		log_message('debug', 'Establishing master LDAP servers connections');
		$this->establishLdapConnection('ldapMaster',true);
		
		
		log_message('debug', 'Establishing slave LDAP servers connections');
		$this->establishLdapConnection('ldapSlave',false);
		
		if($this->checkNeededConnections())
		{
			$this->getActiveConnections();
		} else {
			return $this->restReturn(false);
		}
		return $this->restReturn(true);		
	}

	/**
	 * 
	 * 
	 * @param text $configItem 	Name of the configuration item: ldapMaster or ldapSlave
	 * @param boolean $master	Specifies if the connection to establish is meant to be a connection to a master server or a slave
	 */
	private function establishLdapConnection($configItem, $master){
		//check configuration
		if(!$this->config->item($configItem)) return $this->report('configuration_empty',$configItem); //die('No configuration found with item "'.$configItem.'". Check your configuration file.');

		//test all the given connections (specified in the config file)
		foreach ($this->config->item($configItem) as $item => $server)
		{
			//LDAP variables
			$ldapurl = $server['url'];
			$ldapdn = $server['binddn'];
			$ldappw = $server['bindpw'];
			$version = $server['version'];
				
			$master ?  $key = 'master' : $key = 'slave';
			
			if($this->connect($ldapurl, $ldapdn, $ldappw, $version)) 
			{
				$this->servers[$key][$item] = $server;
				$this->servers[$key][$item]['connection'] = $this->connection;
				return true;
			} else {
				log_message('debug', 'The ldap server set in '.$configItem.'['.$item.'] cannot be connected.');
				return false;
			}
		}
	}
	
	private function checkNeededConnections()
	{
		if(isset($this->servers['master']) && count($this->servers['master']) == 0) 
		{
			if(isset($this->servers['slave']) && count($this->servers['slave']) == 0)	
			{
				//die ('I can not connect to any LDAP master or slave server');
				$this->report('connection','I can not connect to any LDAP master or slave server.');	
				return false;
			} else {
				$this->report('connection','No ldap master server found. Write operations will fail.');
				//log_message('debug', 'No ldap master server found. Write operations will fail.');
			}
		}
		
		if(isset($this->servers['slave']) && count($this->servers['slave']) == 0)	
		{
			$this->report('connection','No ldap slave server found, master server will be used instead.');
			//log_message('debug', 'No ldap slave server found, master server will be used instead.');
		}
		
		return true;
	}
	
	public function CEcreate($entry, $dn ) {
		if(!$this->initialize()) return $this->result;
		$this->connection = $this->WrConnection;
		
		$this->dn = $dn;
		
		return $this->restReturn($this->create($entry));
		
	}
	
	public function CEsearch($baseDn, $filter, $attributes = null, $attributesOnly = 0, $deref = null, array $sort_by = null, $flow_order = null, $wanted_page = null, $items_page = null) {
		if(!$this->initialize()) return $this->result;
		$this->connection = $this->RoConnection;
		
		return $this->restReturn($this->search($baseDn, $filter, $attributes, $attributesOnly, null, null, $deref, $sort_by, $flow_order, $wanted_page, $items_page));
	}
	
	public function CEupdate($dn, array $entry) {
		$this->dn = $dn;
		$this->connection = $this->WrConnection;
		
		return $this->restReturn($this->update($entry));
	}
	
	public function CEdelete($dn = null) {
		$this->connection = $this->WrConnection;
		return $this->restReturn($this->delete($dn));
	}
	
	private function getActiveConnections() {
		
		$num_masters = 0;
		$num_slaves = 0;
		
		if(isset($this->servers['master'])) $num_masters = count($this->servers['master']);
		if(isset($this->servers['slave'])) $num_slaves = count($this->servers['slave']);

		if($num_masters == 0)
		{
			$this->WrConnection = null;
		}
				
		if($num_masters == 1)
		{
			$this->WrConnection = $this->servers['master']['0']['connection'];
		}
		
		if($num_masters > 1)
		{
			//connect randomly to one of the given master (provides a bit of load balancing)
			$serverId = array_rand($this->servers['master'], 1);
			$this->WrConnection = $this->servers['master'][$serverId]['connection'];
		}		
		
		if($num_slaves == 0)
		{
			$this->RoConnection = $this->WrConnection;
		}
		
		if($num_slaves == 1)
		{
			$this->RoConnection = $this->servers['slave']['0']['connection'];
		}		
		
		if($num_slaves > 1)
		{
			//connect randomly to one of the given slave (provides a bit of load balancing)
			$serverId = array_rand($this->servers['slave'], 1);
			$this->RoConnection = $this->servers['slave'][$serverId]['connection'];
		}				
	}
	
	private function restReturn($exit_status) {
		if($exit_status){
			$this->data->http_status_code = '200';
			$this->result->storeData($this->data);
			return true;
		}
		return false;
	}	
}

/* End of ri_ldap.php */