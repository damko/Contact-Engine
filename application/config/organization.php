<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
 * Contact Engine configuration file
 * Created on Aug 11, 2011 by dam d.venturin@squadrainformatica.com
 */

//configuration for the organization object
$config['organization']['baseDn'] = "ou=organizations,o=ce,dc=2v,dc=ntw";
$config['organization']['objectClass'] = "dueviOrganization"; //ldap objectClass representing the organization class
$config['organization']['refreshPeriod'] = ""; //seconds