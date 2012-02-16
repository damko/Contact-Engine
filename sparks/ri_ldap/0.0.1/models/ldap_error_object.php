<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Ldap Error Object. Describes and contains a tipical ldap error
 * 
 * @author 		Damiano Venturin
 * @copyright 	2V S.r.l.
 * @license		GPL
 * @link		http://www.squadrainformatica.com/en/development#mcbsb  MCB-SB official page
 * @since		Feb 16, 2012
 * 
 * @todo	Improve object description		
 */
class Ldap_Error_Object extends CI_Model {
	var $http_status_code; //this is for REST response.
	var $http_status_message; //this is for REST response.
	var $message;
	var $php_errno;
	var $php_errtype;
	var $file;
	var $line;
	
	public function __construct(){
		parent::__construct();
	}
	
	public function __destruct(){
	
	}
	
	public function __set($attribute, $value) {
		if(is_null($value)) $this->$attribute = $value;
	}
	
	public function __get($attribute) {
		return $this->$attribute;
	}
	
	public function addError($php_errno, $message, $file, $line, $http_status_code) {
		
		$http_status_codes = get_HTTP_status_codes();
		$php_error_codes = get_PHP_error_codes();
				
		if(!in_array($http_status_code, array_keys($http_status_codes['all_errors']))) 
		{
			$this->http_status_code = '500';
		} else {
			$this->http_status_code = $http_status_code;
		}
		
		$this->http_status_message = $http_status_codes['all_errors'][$this->http_status_code];
		
 		if(!in_array($php_errno,array_keys($php_error_codes)))
 		{
 			$this->php_errno = '8';
 		} else {
 			$this->php_errno = $php_errno;
 		}
 		
 		$this->php_errtype = $php_error_codes[$php_errno];
 		$this->message = $message;
 		$this->file = $file;
 		$this->line = $line;
	}
}