<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
 * Contact Engine configuration file
 * Created on Aug 11, 2011 by dam d.venturin@squadrainformatica.com
 */

//configuration for the location object
$config['location']['baseDn'] = "ou=locations,o=ce,dc=2v,dc=ntw";
$config['location']['objectClass'] = "dueviLocation"; //ldap objectClass representing the location class
$config['location']['refreshPeriod'] = ""; //seconds