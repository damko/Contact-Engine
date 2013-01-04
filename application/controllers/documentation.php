<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH.'/libraries/dokumentor.php';

class Documentation extends Dokumentor {
	
	public function __construct()
	{
		parent::__construct();
	}

	public function index()
	{
		$data['methods_list'] = $this->displayAPI(array('person','organization','location'));	
		
		$this->load->view('documentation',$data);
	}	
}