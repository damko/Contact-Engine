<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
 * RestIgniter configuration file
 * Created on Aug 11, 2011 by Damiano Venturin @ squadrainformatica.com
 */

/*
 *  ex. array containing an object and a spark:
 *  $config['exposeObj'] = array(
 *  								'chartex' => 'chartex/0.0.1',  //that's the spark
 *  								'object' => 'object'  		   //that's the simple obj
 *  							);  
 */

$config['exposeObj'] = array(
								//'chartex' => 'chartex/0.0.1',
								'ri_ldap' => 'ri_ldap/0.0.1',
								'ri_contact_engine' => 'ri_contact_engine/0.0.1',								
							);

/* End of restigniter.php */