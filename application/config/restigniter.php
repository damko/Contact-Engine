<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
 * RestIgniter configuration file
 * Created on Aug 11, 2011 by Damiano Venturin @ squadrainformatica.com
 */

$config['exposeObj'] = array(
								'chartex' => 'chartex/0.0.1', //used just for unit tests
								'ri_ldap' => 'ri_ldap/0.0.2', 
								'ri_contact_engine' => 'ri_contact_engine/0.0.2',								
							);
$config['only_localhost'] = true;
/* End of restigniter.php */