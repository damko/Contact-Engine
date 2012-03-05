<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH.'/libraries/test_controller.php';

class Unit_Tests extends CI_Controller {
	public function __construct()
	{
		parent::__construct();		
	}
	
	public function index()
	{
		
		$this->load->view('unit_tests');

		$header = '
		<h1>Contact Engine unit-tests</h1>
		<div>
			<h3>Before running these tests remind to: </h3>
			<ol>
				<li>set the connections parameters hardcoded in these controllers:</li>
				<ul style="margin-bottom: 15px; margin-top: 5px;">
					<li>unit_tests_ldap.php</li>
					<li>unit_tests_rildap.php</li>
					<li>populate_LDAP.php</li>
				</ul>
				<li><a href="/index.php/populate_LDAP/" target="_blank">populate</a> the LDAP database</li>
			</ol>
		</div>
		<div id="container">
		';
		
		echo $header;

		//runs tests
		echo '<div id="left">';
		echo '<h3>Available tests:</h3>';
		echo 'Run <a href="/index.php/unit_tests_ldap">LDAP class tests</a><br/>';
		echo 'Run <a href="/index.php/unit_tests_rildap">Ri_LDAP class tests</a><br/>';
		echo 'Run <a href="/index.php/unit_tests_api">general API tests</a><br/>';
		echo 'Run <a href="/index.php/unit_tests_ce">Contact Engine class tests</a><br/>';
		echo '</div>';
		
		echo '<div></body></html>';
	}
}