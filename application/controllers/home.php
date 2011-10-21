<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
// Created on Aug 26, 2011 by Damiano Venturin @ Squadra Informatica

// TODO can't I avoid this ???
require APPPATH.'/libraries/dokumentor.php';

class Home extends Dokumentor {
	public function __construct()
	{
		parent::__construct();
	}

	public function index()
	{
		$this->load->view('home');
	}

	public function documentation()
	{
		$data['methods_list'] = $this->displayAPI(array('person','organization','location'));	
		
		$this->load->view('ce-documentation',$data);
	}	
}

/* End of home.php */