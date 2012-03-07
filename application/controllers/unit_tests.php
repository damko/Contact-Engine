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
			<h3>What are unit tests?</h3>
			<p>
				In few words they are a way to keep under control the quality of the software.
				They are useful for these reasons:
			</p>
			<ul>
				<li>developers, running the test everytime the code is modified, can see if the update caused some new issue on the previous code</li>
				<li>they can be used as example to understand how to use the code</li>
			</ul>
			<p>For further details see <a href="http://en.wikipedia.org/wiki/Unit_tests" target="_blank">wikipedia</a></p>
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
		echo '<p>
				At the time of writing <b>there are more than 1100 tests</b> available for Contact Engine.<br/>
				They are split by group and you can run them by clicking on the links below.
			</p>';
		echo 'Run <a href="/index.php/unit_tests_ldap">LDAP class tests</a><br/>';
		echo 'Run <a href="/index.php/unit_tests_rildap">Ri_LDAP class tests</a><br/>';
		echo 'Run <a href="/index.php/unit_tests_api">general API tests</a><br/>';
		echo 'Run <a href="/index.php/unit_tests_ce">Contact Engine class tests</a><br/>';
		echo '</div>';
		
		echo '<div></body></html>';
	}
}