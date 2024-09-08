<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller {

	function __construct(){
		parent::__construct();
		$this->load->model('LoginModel');
	} 

	public function index(){ 
		$this->form_validation->set_rules('member_email', 'Email', 'trim|required|valid_email|max_length[36]');
		$this->form_validation->set_rules('member_password', 'Password', 'trim|required|max_length[36]');
		$this->form_validation->set_error_delimiters('','');
	
		if ($this->form_validation->run() == FALSE){
			$data['error'] = "";
			$this->load->view('login',$data);                        
		} else {
			$memberData = array(
    			'member_email' => $this->input->post('member_email'),
    			'member_password' => md5($this->input->post('member_password'))
			);
			$result = $this->LoginModel->checkLogin(MEMBER_TABLE,$memberData);
			if($result){
				$sessionData = array(
    				'member_name'  => $result->member_name,
    				'member_email' => $result->member_email,
    				'member_role' => $result->member_role,
    				'member_key' => $result->member_key,
    				'panelLog' => 'FALSE',
				);
				
				date_default_timezone_set("Asia/Kolkata");
				$memberID = $result->member_id;
				$loginData = array(
    				'member_login'  => date('d/m/Y h:i:s A'),
    				'is_login' => 'True'
				);
				$resultLoginData = $this->LoginModel->editData('member_id = '.$memberID, MEMBER_TABLE, $loginData);
				if($resultLoginData){
				$this->session->set_userdata($sessionData);
				   redirect('confirmOTP'); 
				}
				
			} else {
				$errordata['error'] = "incorrect email or password";
				$this->load->view('login',$errordata);
			}
		}
	}
	
	public function confirmOTP(){ 
		$confirmOTP = $this->input->post('confirm_otp');
		if($confirmOTP == OTP){
			$sessionData = array(
    			'panelLog' => 'TRUE',
    			'auth_key' => AUTH_KEY
			);
			$this->session->set_userdata($sessionData);
			redirect('dashboard');
		}
		else{
			redirect('login');
		}
	}
}
