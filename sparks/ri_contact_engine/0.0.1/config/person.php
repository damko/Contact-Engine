<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
 * Contact Engine configuration file
 * Created on Aug 11, 2011 by dam d.venturin@squadrainformatica.com
 */

//configuration for the person objec
$config['person']['baseDn'] = "ou=users,o=2v,dc=2v,dc=ntw";
$config['person']['objectClass'] = "dueviPerson"; //ldap objectClass representing the person class
$config['person']['refreshPeriod'] = ""; //seconds