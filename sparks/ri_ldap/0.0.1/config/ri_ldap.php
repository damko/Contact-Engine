<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
 * Contact Engine configuration file
 * Created on Aug 11, 2011 by dam d.venturin@squadrainformatica.com
 */

//configuration for the ldap people and organization storage

//Parameters influencing search results. Ref: http://it.php.net/manual/en/function.ldap-search.php
$config['ldap']['sizeLimit'] = '1000'; //Enables you to limit the count of entries fetched. Setting this to 0 means no limit. 
$config['ldap']['timeLimit'] = '5'; //Sets the number of seconds how long is spend on the search. Setting this to 0 means no limit.
$config['ldap']['defer'] = '0'; //Specifies how aliases should be handled during the search.
$config['ldap']['debug'] = false;  //outputs in $ldap->result all the PHP errors even the ones different from OutOfRangeException (errno 8)
$config['ldap']['service_unavailable'] = false;  //if true all the requestes are dropped and an error message is sent back (503 HTTP error)

//1ST LDAP MASTER SERVER
$config['ldapMaster'][0]['url'] = "ldap://ldapmaster0:389";
$config['ldapMaster'][0]['version'] = 3;
$config['ldapMaster'][0]['binddn'] = "cn=admin,dc=2v,dc=ntw";
$config['ldapMaster'][0]['bindpw'] = "Wi7Xkcv300z";
//$config['ldapMaster'][0]['referrals'] = true;

//1ST LDAP SLAVE SERVER
$config['ldapSlave'][0]['url'] = "ldap://ldapslave0:389";
$config['ldapSlave'][0]['version'] = 3;
$config['ldapSlave'][0]['binddn'] = "cn=admin,dc=2v,dc=ntw";
$config['ldapSlave'][0]['bindpw'] = "Wi7Xkcv300z";
//$config['ldapSlave'][0]['referrals'] = true;

//2ND LDAP SLAVE SERVER
// $config['ldapSlave'][1]['url'] = "ldap://ldapslave1:389";
// $config['ldapSlave'][1]['version'] = 3;
// $config['ldapSlave'][1]['binddn'] = "cn=admin,dc=2v,dc=ntw";
// $config['ldapSlave'][1]['bindpw'] = "Wi7Xkcv300z";
//$config['ldapSlave'][1]['referrals'] = true;