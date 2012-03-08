<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
// Created on Oct 14, 2011 by Damiano Venturin @ Squadra Informatica
// Php unit tests

class Test_Controller extends CI_Controller {

	public $show_return = false;
	public $count = true;
	public $failed = array();
	public $code_file = null;
	public $code_line = null;
	public $http_status_codes;
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
		$this->http_status_codes = get_HTTP_status_codes();
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
		
		$this->arrayWellFormed($rest_return);
	}
	
	private function arrayWellFormed($rest_return)
	{
		$test = isset($rest_return['status']);
		echo $this->run($test, 'is_true', 'Is return[status] set? ');
		
		$fake = array(
						'results_number' => '0',
						'results_got_number' => '0',
						'results_pages' => '1',
						'results_page' => '1',
						'finished' => null,
						'duration' => null,
						'status_code' => null,
						'message' => null,
					);
		$status = $rest_return['status'];
		$diff = array_diff_key($fake, $status);
		$test = true;
		if(count($diff) > 0) $test = false;
		echo $this->run($test, 'is_true', 'Is the return[status] well formed ? ');
		
		$test = isset($rest_return['data']);
		echo $this->run($test, 'is_true', 'Is return[data] set? ');		
	}
	
	private function meaningfullStatusCode($rest_return) {
		if(in_array($rest_return['status']['status_code'],array_keys($this->http_status_codes['all']))) {
			$test = true;
		} else {
			$test = false;
		}
		
		echo $this->run($test, 'is_true', 'Is the returned status code a valid REST RETURN ? ');
	}
	
	/**
	 * Returns TRUE if there are errors
	 * 
	 * @access		public
	 * @param		
	 * @var			
	 * @return		
	 * @example
	 * @see
	 * 
	 * @author 		Damiano Venturin
	 * @copyright 	2V S.r.l.
	 * @license		GPL
	 * @link		http://www.contact-engine.info
	 * @since		Mar 6, 2012
	 * 
	 * @todo		
	 */
	private function isThereAnyError($rest_return)
	{
		$test = $rest_return['status'];
		if(empty($test['status_code'])) return true;
		
		if(in_array($test['status_code'],array_keys($this->http_status_codes['all_errors']))) {
			return true;
		} else {
			return false;
		}
	}
	
	private function checkError($rest_return, $method, $note, $expect_error) {
		
		$this->meaningfullStatusCode($rest_return);
		
		if(empty($note)) 
		{
			if($expect_error) 
			{
				$note = 'I expect there should be a REST error';
			} else {
				$note = 'I expect there should be NOT a REST error';
			}
		}
		
		$test = $this->isThereAnyError($rest_return);
		
		echo $this->run($test, $expect_error, $method.' Any REST error ?', $note);
	}
	
	protected function checkNoRestError($method, $rest_return, $note = null) {
		$this->checkError($rest_return, $method, $note, false);
	}

	protected function checkRestError($method, $rest_return, $note = null)
	{
		$this->checkError($rest_return, $method, $note, true);
	}

	private function check($method, $rest_return, $expected_status_code, $note = null) {
		if(!isset($rest_return['status']['status_code'])) return false;
		
		$test = false;
		
		if( $expected_status_code == $rest_return['status']['status_code']) 
			$test = true;
		
		echo $this->run($test, 'is_true', $method, $note.' - I have got: '.$rest_return['status']['status_code']);
	}
	
	protected function check200($method, $rest_return, $note = null)
	{
		$expected_status_code = '200';
		
		if(empty($note)) $note = 'I expect 200';
		
		$this->check($method, $rest_return, $expected_status_code, $note);
	}

	protected function check400($method, $rest_return, $note = null)
	{
		$expected_status_code = '400';
		
		if(empty($note)) $note = 'I expect 400';
		
		$this->check($method, $rest_return, $expected_status_code, $note);
	}

	protected function check404($method, $rest_return, $note = null)
	{
		$expected_status_code = '404';
		
		if(empty($note)) $note = 'I expect 404';
		
		$this->check($method, $rest_return, $expected_status_code, $note);
	}

	protected function check415($method, $rest_return, $note = null)
	{
		$expected_status_code = '415';
	
		if(empty($note)) $note = 'I expect 415';
	
		$this->check($method, $rest_return, $expected_status_code, $note);
	}
		
	protected function check500($method, $rest_return, $note = null)
	{
		$expected_status_code = '500';
	
		if(empty($note)) $note = 'I expect 500';
	
		$this->check($method, $rest_return, $expected_status_code, $note);
	}	
	
	private function checkResultNumberMatch($method, $rest_return)
	{
		$test = false;
		
		$count_results = count($rest_return['data']);
		
		if($count_results == $rest_return['status']['results_got_number']) $test = true;
				
		echo $this->run($test, 'is_true', $method, 'The returned result is equal to the specified "results_got_number" parameter');
		
		
		$test = false;
		if($rest_return['status']['results_number'] >= $count_results ) $test = true;
		
		echo $this->run($test, 'is_true', $method, 'Is the parameter "results_number" &gt;= "results_got_number" ?');

		
		$test = false;
		if($rest_return['status']['results_pages'] >= 1 ) $test = true;
		
		echo $this->run($test, 'is_true', $method, 'Is the parameter "results_pages" &gt;= 1 ?');

		
		$test = false;
		if($rest_return['status']['results_page'] >= 1 ) $test = true;
		
		echo $this->run($test, 'is_true', $method, 'Is the parameter "results_page" &gt;= 1 ?');


		$test = false;
		if($rest_return['status']['results_pages'] >= $rest_return['status']['results_page'] ) $test = true;
		
		echo $this->run($test, 'is_true', $method, 'Is the parameter "results_pages" &gt;= "results_page" ?');
	}
	
	protected function checkHasData($method, $rest_return, $note = null)
	{
		if(empty($note)) $note = 'I expect to have some data';
	
		$test = false;
		$count_results = count($rest_return['data']);
		if($count_results > 0) $test = true;
		
		echo $this->run($test, 'is_true', $method, $note.' - I have got: '.$count_results.' results');
		
		$this->checkResultNumberMatch($method, $rest_return);
	}

	protected function checkHasNoData($method, $rest_return, $note = null)
	{
		if(empty($note)) $note = 'I expect to have NO data';
	
		$test = false;
		$count_results = count($rest_return['data']);
		if($count_results == 0) $test = true;
	
		echo $this->run($test, 'is_true', $method, $note.' - I have got: '.$count_results.' results');
	
		$this->checkResultNumberMatch($method, $rest_return);
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
		if(!isset($lro->data->results_number)) $test = false;
		if(!isset($lro->data->results_pages)) $test = false;
		if(!isset($lro->data->results_page)) $test = false;
		if(!isset($lro->data->results_got_number)) $test = false;
		
		if(!isset($lro->data->content)) $test = false;
		echo $this->run($test, 'is_true', 'Are the LdapReturnObject->data mandatory attributes all set ?', '');

		$test = $lro->data->content;
		echo $this->run($test, 'is_array', 'Is the LdapReturnObject->data->content an array ?', '');

		
	}
	
	protected function checkLdapReturnObjectHasContent($lro) {
		
		$this->checkLdapReturnObjectHasDataObject($lro);
		
		$test = empty($lro->data->content);
		echo $this->run($test, 'is_false', 'Does the LdapReturnObject have data ?', '');
		
		
		
		$test = false;
		if($lro->data->results_number >= 1) $test = true;		
		echo $this->run($test, 'is_true', 'Is results_number &gt;= 1 ?', '');

		$test = false;
		if($lro->data->results_got_number >= 1) $test = true;
		echo $this->run($test, 'is_true', 'Is results_got_number &gt;= 1 ?', '');
		
		$test = false;
		if($lro->data->results_got_number <= $lro->data->results_number) $test = true;
		echo $this->run($test, 'is_true', 'Is results_got_number <= $lro->data->results_number ?', '');
		
		
		
		
		$test = false;
		if($lro->data->results_pages >= 1) $test = true; 
		echo $this->run($test, 'is_true', 'Is results_pages &gt;= 1 ?', '');
		
		$test = false;
		if($lro->data->results_page >= 1) $test = true;
		echo $this->run($test, 'is_true', 'Is results_page &gt;= 1 ?', '');	

		$test = false;
		if($lro->data->results_page <= $lro->data->results_pages) $test = true;
		echo $this->run($test, 'is_true', 'Is results_page &lt;= $lro->data->results_page ?', '');
				
	}

	protected function checkLdapReturnObjectHasNoContent($lro) {
		
		$this->checkLdapReturnObjectHasDataObject($lro);
		
		$test = count($lro->data->content);
		$test = ($test > 0) ? true : false;
		echo $this->run($test, 'is_false', 'Does the LdapReturnObject have NO data ?', '');
		
		echo $this->run($lro->data->results_number, '0', 'Is results_number == 0 ?', '');
		
		echo $this->run($lro->data->results_pages, '1', 'Is results_pages == 1 ?', '');
		
		echo $this->run($lro->data->results_page, '1', 'Is results_page == 1 ?', '');
		
		echo $this->run($lro->data->results_got_number, '0', 'Is results_got_number == 0 ?', '');
		
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
		
		//check that the returned status_code contained in "data" is equal 
		//to the status code of the last error in the errors list

		if(count($lro->errors) > 0 && isset($lro->data->http_status_code))
		{
			$errors = $lro->errors;
			$error = array_pop($errors);
			
			$this->run($error->http_status_code, $lro->data->http_status_code, 'Is the http_status_code of the last error equal to the code reported in data? ', '');
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