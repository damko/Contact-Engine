<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
// Created on Oct 14, 2011 by Damiano Venturin @ Squadra Informatica
// Php unit tests

class Test_Controller extends CI_Controller {

	public $show_return = false;
	public $count = true;
	public $failed = array();
	public $code_file = null;
	public $code_line = null;
	
	public function __construct()
	{
		//load the embedded unit_test library
		//$CI =& get_instance();
		
		parent::__construct();
		$this->load->library('unit_test');
		
		//set up a new template for the tests output
		$this->unit->set_test_items(array('test_name', 'test_datatype', 'res_datatype', 'result', 'notes'));
		
		$str  = "\n".'<table style="width:100%; font-size:small; margin:0px 0; border-collapse:collapse; border:1px solid #CCC; background-color: #e8e8e8;">';
		$str .= '{rows}';
		$str .= "\n".'</table>';
		$this->unit->set_template($str);
		
		$str = "\n\t".'<tr>';
		$str .= "\n\t\t".'<th style="text-align: left; border-bottom:1px solid #CCC; width: 20%;">{item}</th>';
		$str .= "\n\t\t".'<td style="border-bottom:1px solid #CCC; width: 80%">{result}</td>';
		$str .= "\n\t".'</tr>';
		$this->unit->set_template_rows($str);		
		
		if(isset($_GET['verbose']) && ($_GET['verbose'] == 'on')) $this->show_return = true; 
		
		$this->count = 0;
	}
	
	protected function getCodeOrigin() {
		$backtrace = debug_backtrace();
		
		$pieces = preg_split('/\//', $backtrace[0]['file']);
		$this->code_file = array_pop($pieces);
		$this->code_line = $backtrace[0]['line'];
	}
	
	protected  function resetCodeOrigin() {
		$this->code_file = null;
		$this->code_line = null;
	}
	
	protected function run($test, $expected_result, $description, $note = null) {
		if(is_null($this->code_file)) $this->getCodeOrigin();
		
		//test header
		echo '<H4>test #'.$this->count.'<a name="test'.$this->count.'"></a>
			  <span style="font-size: 12px;">
			  	written in '.$this->code_file.' line '.$this->code_line.'</span>
			  <span style="font-size: 12px; float: right;">
			  	<a style="color: blue;" href="'.site_url().'">Home</a> &nbsp;|&nbsp;
			  	<a style="color: blue;" href="'.site_url().'/unit_tests/">Unit-tests home</a> &nbsp;|&nbsp;
			  	<a style="color: blue;" href="#top">Top</a>&nbsp;&nbsp;
			  	
			  </span>
			  </H4>';
		
		//runs test
		$test_results = $this->unit->run($test, $expected_result, $description, $note);
		
		//echos results
		echo $test_results;
		
		//parses test return to see if it's failed
		if(preg_match('/Failed<\/span>/', $test_results))
		{
			$this->failed[] = $this->count;
		}
		
		$this->count ++;
	}
	
	protected function printSummary()
	{
		echo '<div id="right">';
		
		//verbose button
		$turn_on = '<input class="button" type="button" onclick=verbose("on") value="turn ON verbose" />';
		$turn_off = '<input class="button" type="button" onclick=verbose("off") value="turn OFF verbose" />';
		echo '<p>';
		if(!isset($_GET['verbose'])) { 
			echo $turn_on;
		} else {
			if($_GET['verbose'] == 'on') {
				echo $turn_off;
			} else {
				echo $turn_on;
			}
		}
		echo '</p>';
		
		if(count($this->failed)> 0)
		{
			echo '<div style="background-color: white; border: 3px dashed red; padding-left: 5px;">';
			echo '<p>Found '.count($this->failed).' tests FAILED on '.$this->count.'</p>';
			echo '<ul>';
			foreach ($this->failed as $key => $testID) {
				echo '<li><a style="color: red;" href="#test'.$testID.'">Test #'.$testID.'</a></li>';
			}
			echo '</ul>';
			echo '</div>';
		} else {
			echo '<div style="background-color: white; border: 3px dashed green; padding-left: 5px;">';
			echo '<p style="color: green; text-align: center;">No failed tests found on '.$this->count.'</p>';
			echo '</div>';
		}
		echo '</div>';		
	}
	
	protected function testTitle($text,$subtext = null) {
		//echo '[<a href="'.site_url().'">Home</a>]';
		echo '<hr/>';
		echo '<h3 class="blue">'.$text.'</h3>';
		echo '<p>'.$subtext.'</p>';
	}	
	
	protected function subTestTitle($text) {
		echo '<h4 class="subtest"><span style="font-size: 13px;"> subtest: </span>'.$text.'</h4>';
	}
	
	protected function arrayReturn($method, $rest_return, $note = null)
	{
		if(empty($note)) $note = 'I expect an array in return';
		echo $this->run($rest_return, 'is_array', $method.'- array in return ?', $note);
	}
	
	protected function anyError($rest_return)
	{
		$test = (array) $rest_return['status'];
		return isset($test['error_message']) ? true : false;
	}
	
	protected function checkNoRestError($method, $rest_return, $note = null)
	{
		$expected_result = false;
		$test = $this->anyError($rest_return);
		if(empty($note)) $note = 'I expect there should be no REST error';
		echo $this->run($test, $expected_result, $method.' Any REST error ?', $note);
	}

	protected function checkRestError($method, $rest_return, $note = null)
	{
		$expected_result = true;
		$test = $this->anyError($rest_return);
		if(empty($note)) $note = 'I expect a REST error';
		echo $this->run($test, $expected_result, $method.' Any REST error ?', $note);
	}
		
	protected function check200($method, $rest_return, $note = null)
	{
		$expected_result = '200';
		$test = (array) $rest_return['status'];
		if(isset($test['status_code'])) 
		{
			$test = $test['status_code'];
		} else {
			$test = 'UNKNOWN ???';
			$note = 'UNKNOWN ???';
		}
		if(empty($note)) $note = 'I expect a 200';
		echo $this->run($test, $expected_result, $method.'- status code == 200 ?', $note);
	}

	protected function check400($method, $rest_return, $note = null)
	{
		$expected_result = '400';
		$test = (array) $rest_return['status'];
		if(isset($test['status_code'])) 
		{
			$test = $test['status_code'];
		} else {
			$test = 'UNKNOWN ???';
			$note = 'UNKNOWN ???';
		}
		if(empty($note)) $note = 'I expect a 400';
		echo $this->run($test, $expected_result, $method.'- status code == 400 ?', $note);
	}

	protected function check404($method, $rest_return, $note = null)
	{
		$expected_result = '404';
		$test = (array) $rest_return['status'];
		
		//TODO refactory
		if(isset($test['status_code'])) 
		{
			$test = $test['status_code'];
		} else {
			$test = 'UNKNOWN ???';
			$note = 'UNKNOWN ???';
		}
		if(empty($note)) $note = 'I expect a 404';
		echo $this->run($test, $expected_result, $method.'- status code == 404 ?', $note);
	}
		
	protected function printReturn($rest_return)
	{
		if(!$this->show_return) return;
		
		echo '<h3>REST return</h3>';
		echo '<pre style="font-size: 9px; background-color: #e8e8e8; margin-left: 15px; padding-left: 10px;">';
		print_r($rest_return);
		echo '</pre>';
	}
	
	protected function printLdapResult($result)
	{
		if(!$this->show_return) return;
		
		echo '<h3>LDAP return</h3>';
		echo '<pre style="font-size: 9px; background-color: #e8e8e8; margin-left: 15px; padding-left: 10px;">';
		print_r($result);
		echo '</pre>';		
	}
	
	protected function checkLdapReturnObject($lro) {
		
		$test = is_object($lro);
		echo $this->run($test, 'is_true', 'Is the result an object?', '');
		
		$test = get_class($lro);
		echo $this->run($test, 'Ldap_Return_Object', 'Is the returned object an LdapReturnObject ?', '');

		$test = $lro->errors;
		echo $this->run($test, 'is_array', 'Is the LdapReturnObject->errors an array ?', '');
		
		$test = is_array($lro->data);
		echo $this->run($test, 'is_false', 'Is LdapReturnObject->data not an array ?', '');
	}

	private  function checkLdapReturnObjectHasDataObject($lro) {
		$test = get_class($lro->data);
		echo $this->run($test, 'Ldap_Data_Object', 'Is LdapReturnObject->data a Ldap_Data_Object ?', '');
				
		$test = true;
		if(!isset($lro->data->http_status_code)) $test = false;
		if(!isset($lro->data->http_status_message)) $test = false;
		if(!isset($lro->data->content)) $test = false;
		echo $this->run($test, 'is_true', 'Are the LdapReturnObject->data mandatory attributes all set ?', '');

		$test = $lro->data->content;
		echo $this->run($test, 'is_array', 'Is the LdapReturnObject->data->content an array ?', '');
	}
	
	protected function checkLdapReturnObjectHasContent($lro) {
		
		$this->checkLdapReturnObjectHasDataObject($lro);
		
		$test = empty($lro->data->content);
		echo $this->run($test, 'is_false', 'Does the LdapReturnObject have data ?', '');

	}

	protected function checkLdapReturnObjectHasNoContent($lro) {
		
		$this->checkLdapReturnObjectHasDataObject($lro);
		
		$test = count($lro->data->content);
		$test = ($test > 0) ? true : false;
		echo $this->run($test, 'is_false', 'Does the LdapReturnObject have NO data ?', '');
	}
		
	protected function checkLdapReturnObjectHasError($lro) {
		
		$test = count($lro->errors);
		$test = ($test > 0) ? true : false; 
		echo $this->run($test, 'is_true', 'Does the LdapReturnObject have errors ?', '');
		
		$test = is_array($lro->errors);
		echo $this->run($test, 'is_true', 'Is LdapReturnObject->errors an array ?', '');
		
		foreach ($lro->errors as $key => $error) {
			$test = get_class($error);
			echo $this->run($test, 'Ldap_Error_Object', 'Is LdapReturnObject->data['.$key.'] a Ldap_Error_Object ?', '');
		}		

		
		foreach ($lro->errors as $key => $error) {
			$test = true;
			if(!isset($error->http_status_code)) $test = false;
			if(!isset($error->http_status_message)) $test = false;
			if(!isset($error->message)) $test = false;
			if(!isset($error->php_errno)) $test = false;
			if(!isset($error->file)) $test = false;
			if(!isset($error->line)) $test = false;
			echo $this->run($test, 'is_true', 'Are the LdapReturnObject->error['.$key.'] attributes all set ?', '');
		}
		
		foreach ($lro->errors as $key => $error) {
			$test = true;
			if(empty($error->http_status_code)) $test = false;
			if(empty($error->http_status_message)) $test = false;
			if(empty($error->message)) $test = false;
			if(empty($error->php_errno)) $test = false;
			if(empty($error->file)) $test = false;
			if(empty($error->line)) $test = false;
			echo $this->run($test, 'is_true', 'Are the main LdapReturnObject->error['.$key.'] attributes populated ?', '');
		}		
	}

	protected function checkLdapReturnObjectHasNoError($lro) {	
		$test = count($lro->errors);
		$test = ($test > 0) ? true : false;
		echo $this->run($test, 'is_false', 'Does the LdapReturnObject have NO errors ?', '');
		
		echo $this->run($lro->data->http_status_code, '200', 'Do I get the data http_status_code = 200 ?', '');

		echo $this->run($lro->data->http_status_message, 'OK', 'Do I get the data http_status_message = OK ?', '');
	}	
}