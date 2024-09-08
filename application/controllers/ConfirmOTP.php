<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ConfirmOTP extends CI_Controller {
	function __construct(){
		parent::__construct();
		$this->load->model('DataModel');
	}
	
	public function index(){
		$this->load->view('verify');
	}
}
