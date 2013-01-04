<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH.'/libraries/test_controller.php';

class Unit_Tests extends CI_Controller {
	public function __construct()
	{
		parent::__construct();		
	}
	
	public function index()
	{
		$data = array();
		$data['content'] ='
		<h1>Contact Engine unit-tests</h1>
		[<a href="' . site_url('') . '">Home</a>]
		
		<div id="container">
		
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
		<li><a href="/index.php/populate_LDAP/" target="_blank">populate</a> the LDAP database</li>
		</ol>
		</div>
		
		<div id="left">
		<h3>Available tests:</h3>
		<p>
		At the time of writing <b>there are more than 1100 tests</b> available for Contact Engine.<br/>
		They are split by group and you can run them by clicking on the links below.
		</p>
		<ol>
		<li style="padding: 5px;"><a href="/index.php/unit_tests_ldap">LDAP class tests</a></li>
		<li style="padding: 5px;"><a href="/index.php/unit_tests_rildap">Ri_LDAP class tests</a></li>
		<li style="padding: 5px;"><a href="/index.php/unit_tests_api">General API tests</a></li>
		<li style="padding: 5px;"><a href="/index.php/unit_tests_ce">Contact Engine class tests</a></li>
		</ol>
		</div>
		
		<div>
		</body>
		</html>';		
		
		$this->load->view('unit_tests',$data);
		
		
	}
}