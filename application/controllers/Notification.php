<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notification extends CI_Controller {
	function __construct(){
		parent::__construct();
		$this->load->model('DataModel');
		$this->load->library('pagination');
		
		if ($this->session->userdata('auth_key') != AUTH_KEY){ 
            redirect('login');
        }
	}
	
	public function index(){
		$this->load->view('header');
        $this->load->view('error');
        $this->load->view('footer');
	}
	
	public function checkAuth(){
        if(!empty($this->session->userdata['member_key'])) { 
            if($this->session->userdata['auth_key'] == AUTH_KEY){
                $memberKey = $this->session->userdata['member_key'];
                $data['memberData'] = $this->DataModel->getData('member_key = '.$memberKey, MEMBER_TABLE);
                if($data['memberData']){
                    $isLogin = $data['memberData']->is_login;
                } else {
                     redirect('error');
                }
            } else {
                redirect('error');
            }
        } else {
            redirect('error');
        }
        return $isLogin;
    }
    
    public function appNew(){
        $isLogin = $this->checkAuth();
        if($isLogin == "True"){
            $this->form_validation->set_rules('app_name', 'Text', 'required');
            $this->form_validation->set_rules('app_code', 'Text', 'required');
            $this->form_validation->set_rules('app_table', 'Text', 'required');
            $this->form_validation->set_rules('app_rsa', 'Text', 'required');
            if ($this->form_validation->run() == FALSE){
                $data['error'] = "";
                $this->load->view('header');
                $this->load->view('notification/app_new',$data);
                $this->load->view('footer');
            }else{
                $newData = array(
                    'app_name'=>$this->input->post('app_name'),
                    'app_code'=>$this->input->post('app_code'),
                    'app_table'=>$this->input->post('app_table'),
                    'app_rsa'=>$this->input->post('app_rsa')
                );
                $newDataEntry = $this->DataModel->insertData(APP_DATA_TABLE, $newData);
                if($newDataEntry){
                  redirect('app-view');  
                }
            }
        } else {
            redirect('logout');
        }
    }

    public function appView(){
        $isLogin = $this->checkAuth();
        if($isLogin == "True"){
            $data = array();
            //get rows count
            $conditions['returnType'] = 'count';
            $totalRec = $this->DataModel->viewAppNotificationData($conditions, APP_DATA_TABLE);
            
            //pagination config
            $config['base_url']    = site_url('app-view');
            $config['uri_segment'] = 2;
            $config['total_rows']  = $totalRec;
            $config['per_page']    = 10;
            
            //styling
            $config['num_tag_open'] = '<li class="page-item page-link">';
            $config['num_tag_close'] = '</li>';
            $config['cur_tag_open'] = '<li class="active page-item"><a href="javascript:void(0);" class="page-link" >';
            $config['cur_tag_close'] = '</a></li>';
            $config['next_link'] = '&raquo';
            $config['prev_link'] = '&laquo';
            $config['next_tag_open'] = '<li class="pg-next page-item page-link">';
            $config['next_tag_close'] = '</li>';
            $config['prev_tag_open'] = '<li class="pg-prev page-item page-link">';
            $config['prev_tag_close'] = '</li>';
            $config['first_tag_open'] = '<li class="page-item page-link">';
            $config['first_tag_close'] = '</li>';
            $config['last_tag_open'] = '<li class="page-item page-link">';
            $config['last_tag_close'] = '</li>';
            
            //initialize pagination library
            $this->pagination->initialize($config);
            
            //define offset
            $page = $this->uri->segment(2);
            $offset = !$page?0:$page;
            
            //get rows
            $conditions['returnType'] = '';
            $conditions['start'] = $offset;
            $conditions['limit'] = 10;

            $viewApp = $this->DataModel->viewAppNotificationData($conditions, APP_DATA_TABLE);
            $data['viewApp'] = array();
                    
            if (is_array($viewApp) || is_object($viewApp)){
                foreach($viewApp as $appRow){
                    $dataArray = array();
                    $dataArray['app_id'] = $appRow['app_id'];
                    $dataArray['app_name'] = $appRow['app_name'];
                    $dataArray['app_code'] = $appRow['app_code'];
                    $dataArray['app_table'] = $appRow['app_table'];
                    $dataArray['app_rsa'] = $appRow['app_rsa'];
                    $dataArray['app_user'] = $this->DataModel->countData('token_status = "active"', $appRow['app_table']);

                    array_push($data['viewApp'], $dataArray);
                }
            }
                    
            if($data['viewApp'] != null){
                $this->load->view('header');
                $this->load->view('notification/app_view',$data);
                $this->load->view('footer');
            } else {
                $data['msg'] = array(
                    'data_title'=>"App Database is Empty",
                    'data_description'=>"Please add app from the below button.",
                    'button_link'=>"app-new",
                    'button_text'=>"New App",
                );
                $this->load->view('header');
                $this->load->view('nodata', $data);
                $this->load->view('footer');
            }
        } else {
            redirect('logout');
        }
    }

    public function appEdit($appID = 0){
        $isLogin = $this->checkAuth();
        if($isLogin == "True"){
            $checkEncryption = $this->DataModel->checkEncrypt($appID,ENCRYPT_TABLE);
            if($checkEncryption){
                $appID = $checkEncryption->enc_number;
            }
           
            if(ctype_digit($appID)){
                $data['appData'] = $this->DataModel->getData('app_id = '.$appID, APP_DATA_TABLE);
                if(!empty($data['appData'])){
                    $this->load->view('header');
                    $this->load->view('notification/app_edit',$data);
                    $this->load->view('footer');
                } else {
                    redirect('error');
                }
                if($this->input->post('submit')){
                    $editData = array(
                        'app_name'=>$this->input->post('app_name'),
                        'app_code'=>$this->input->post('app_code'),
                        'app_table'=>$this->input->post('app_table'),
                        'app_rsa'=>$this->input->post('app_rsa'),
                    );
                    $editDataEntry = $this->DataModel->editData('app_id = '.$appID, APP_DATA_TABLE, $editData);
                    if($editDataEntry){
                        redirect('app-view');
                    }
                }
            } else {
                redirect('error');
            }
        } else {
            redirect('logout');
        }
    }

    //Notification Functions
    public function notificationNew(){
        $isLogin = $this->checkAuth();
        if($isLogin == "True"){
            $this->form_validation->set_rules('notification_title', 'Text', 'required');
            $this->form_validation->set_rules('notification_message', 'Text', 'required');
            $this->form_validation->set_rules('notification_url', 'Text', 'required');
            $this->form_validation->set_rules('notification_image', 'Text', 'required');
            
            if ($this->form_validation->run() == FALSE){
                $data['appData'] = $this->DataModel->viewData('app_id '.'DESC', null, APP_DATA_TABLE);
                $data['error'] = "";
                $this->load->view('header');
                $this->load->view('notification/notification_new',$data);
                $this->load->view('footer');
            } else {
                $appData = $this->DataModel->getData('app_id = '.$_POST['app_id'], APP_DATA_TABLE);
                $newData = array(
                    'app_id'=>$this->input->post('app_id'),
                    'notification_title'=>$this->input->post('notification_title'),
                    'notification_message'=>$this->input->post('notification_message'),
                    'notification_url'=>$this->input->post('notification_url'),
                    'notification_image'=>$this->input->post('notification_image'),
                    'app_code'=>$appData->app_code,
                    'app_table'=>$appData->app_table,
                    'app_rsa'=>$appData->app_rsa,
                    'notification_status'=>$this->input->post('notification_status')
                );
                $newDataEntry = $this->DataModel->insertData(APP_NOTIFICATION_TABLE, $newData);
                if($newDataEntry){
                  redirect('notification-view');  
                }
            }
        } else {
            redirect('logout');
        }
    }

    public function notificationView(){
        $isLogin = $this->checkAuth();
        if($isLogin == "True"){

            $data = array();
            //get rows count
            $conditions['returnType'] = 'count';
            $totalRec = $this->DataModel->viewNotificationData($conditions, APP_NOTIFICATION_TABLE);
            
            //pagination config
            $config['base_url']    = site_url('notification-view');
            $config['uri_segment'] = 2;
            $config['total_rows']  = $totalRec;
            $config['per_page']    = 10;
            
            //styling
            $config['num_tag_open'] = '<li class="page-item page-link">';
            $config['num_tag_close'] = '</li>';
            $config['cur_tag_open'] = '<li class="active page-item"><a href="javascript:void(0);" class="page-link" >';
            $config['cur_tag_close'] = '</a></li>';
            $config['next_link'] = '&raquo';
            $config['prev_link'] = '&laquo';
            $config['next_tag_open'] = '<li class="pg-next page-item page-link">';
            $config['next_tag_close'] = '</li>';
            $config['prev_tag_open'] = '<li class="pg-prev page-item page-link">';
            $config['prev_tag_close'] = '</li>';
            $config['first_tag_open'] = '<li class="page-item page-link">';
            $config['first_tag_close'] = '</li>';
            $config['last_tag_open'] = '<li class="page-item page-link">';
            $config['last_tag_close'] = '</li>';
            
            //initialize pagination library
            $this->pagination->initialize($config);
            
            //define offset
            $page = $this->uri->segment(2);
            $offset = !$page?0:$page;
            
            //get rows
            $conditions['returnType'] = '';
            $conditions['start'] = $offset;
            $conditions['limit'] = 10;

            $notification = $this->DataModel->viewNotificationData($conditions, APP_NOTIFICATION_TABLE);
            $data['viewNotification'] = array();
            
            if (is_array($notification) || is_object($notification)){
                foreach($notification as $Row){
                    $dataArray = array();
                    $dataArray['notification_id'] = $Row['notification_id'];
                    $dataArray['app_id'] = $Row['app_id'];
                    $dataArray['notification_title'] = $Row['notification_title'];
                    $dataArray['notification_message'] = $Row['notification_message'];
                    $dataArray['notification_url'] = $Row['notification_url'];
                    $dataArray['notification_image'] = $Row['notification_image'];
                    $dataArray['notification_status'] = $Row['notification_status'];
                    $dataArray['appName'] = $this->DataModel->getappNameData('app_id = '.$dataArray['app_id'], APP_DATA_TABLE);
                    
                    array_push($data['viewNotification'], $dataArray);
                }
            }

            if($data['viewNotification'] != null){
                $this->load->view('header');
                $this->load->view('notification/notification_view',$data);
                $this->load->view('footer');
            } else {
                $data['msg'] = array(
                    'data_title'=>"Notification Database is Empty",
                    'data_description'=>"Please add notification from the below button.",
                    'button_link'=>"notification-new",
                    'button_text'=>"New Notification",
                );
                $this->load->view('header');
                $this->load->view('nodata', $data);
                $this->load->view('footer');
            }
        } else {
            redirect('logout');
        }
    }

    public function NotificationEdit($notificationID = 0){
        $isLogin = $this->checkAuth();
        if($isLogin == "True"){
            $checkEncryption = $this->DataModel->checkEncrypt($notificationID,ENCRYPT_TABLE);
            if($checkEncryption){
                $notificationID = $checkEncryption->enc_number;
            }
           
            if(ctype_digit($notificationID)){
                $data['notificationData'] = $this->DataModel->getData('notification_id = '.$notificationID, APP_NOTIFICATION_TABLE);
                $data['viewApp'] = $this->DataModel->viewData('app_id '.'DESC', null, APP_DATA_TABLE);
                $appID = $data['notificationData']->app_id;
                $data['appData'] = $this->DataModel->getData('app_id = '.$appID, APP_DATA_TABLE);

                if(!empty($data['notificationData'])){
                    $this->load->view('header');
                    $this->load->view('notification/notification_edit',$data);
                    $this->load->view('footer');
                } else {
                    redirect('error');
                }
                if($this->input->post('submit')){
                    $appNotificationData = $this->DataModel->getData('app_id = '.$_POST['app_id'], APP_DATA_TABLE);
                    $editData = array(
                        'app_id'=>$this->input->post('app_id'),
                        'notification_title'=>$this->input->post('notification_title'),
                        'notification_message'=>$this->input->post('notification_message'),
                        'notification_url'=>$this->input->post('notification_url'),
                        'notification_image'=>$this->input->post('notification_image'),
                        'app_code'=>$appNotificationData->app_code,
                        'app_table'=>$appNotificationData->app_table,
                        'app_rsa'=>$appNotificationData->app_rsa,
                        'notification_status'=>$this->input->post('notification_status')
                    );
                    $editDataEntry = $this->DataModel->editData('notification_id = '.$notificationID, APP_NOTIFICATION_TABLE, $editData);
                    if($editDataEntry){
                        redirect('notification-view');
                    }
                }
            } else {
                redirect('error');
            }
        } else {
            redirect('logout');
        }
    }
}
