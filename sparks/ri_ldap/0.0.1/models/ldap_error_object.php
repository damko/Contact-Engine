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
	var $type;
	var $message;
	var $file;
	var $line;
	
	public function __construct(){
		parent::__construct();
	}
	
	public function __destruct(){
		parent::__destruct();
	}
	
	public function __set($attribute, $value) {
		if(is_null($value)) $this->$attribute = $value;
	}
	
	public function __get($attribute) {
		return $this->$attribute;
	}
	
	public function addError($type, $message, $file = null, $line = null) {
		
	}
}