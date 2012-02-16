<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ldap_Return_Object extends CI_Model {
	var $errors = array(); //contains the collected errors
	var $data = array(); //contains data retrieved from LDAP
	
	public function __construct(){
		parent::__construct();
	}
		
	public function __destruct(){
		parent::__destruct();
	}
		
	public function __set($attribute, $value) {
		$this->$attribute = $value;
	}
		
	public function __get($attribute) {
		return $this->$attribute;
	}
	
	public function addError($type, $message, $file = null, $line = null) {
		$error = new Ldap_Error_Object();
		$error->type = $type;
		$error->message = $message;
		$error->file = $file;
		$error->line = $line;
	}
	
	private function storeError(object $error) {
		array_push($this->errors, $error);
	}
}
