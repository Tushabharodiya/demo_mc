<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require 'vendor/autoload.php';
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use Aws\CommandPool;

class Android extends CI_Controller {
    function __construct(){
		parent::__construct();
		$this->load->model('DataModel');
		$this->load->library('pagination');

		if ($this->session->userdata('auth_key') != AUTH_KEY){ 
            redirect('login');
        }
	}
	
	//S3Bucket Config
    public function getconfig() {
        $s3Client = new S3Client([
            'version' => 'latest',
            'region'  => S3_REGION,
            'credentials' => [
                'key'    => S3_KEY,
                'secret' => S3_SECRET
            ]            
        ]);
        return $s3Client;
    }
    
    public function uniqueKey(){
        date_default_timezone_set("Asia/Kolkata");
        $characters = '0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < 4; $i++) 
        {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        $uniqueKey =  $randomString.''.strtolower(date('dmYhis'));
        return $uniqueKey;
    }
    
    public function newBucketObject($objectName, $objectCode, $objectTemp, $objectPath){
        $isLogin = $this->checkAuth();
        if(!empty($this->session->userdata['member_role'])) { 
            date_default_timezone_set("Asia/Kolkata");
            $s3Client = $this->getconfig();
            $extObject = explode(".", $objectName);
            $newObject = end($extObject);
            $objectName = $objectCode.'.'.$newObject;
            $result = $s3Client->putObject([
                'Bucket' => BUCKET_NAME,
                'Key'    => $objectPath.$objectName,
                'SourceFile' => $objectTemp,
                'ACL'    => 'public-read', 
            ]);
            return $result->get('ObjectURL');
        } else {
            redirect('logout');
        }
    }
    
    function copyBucketObject($objectName, $objectCode, $objectTemp, $objectPath){
        $isLogin = $this->checkAuth();
        if($isLogin == "True"){ 
            date_default_timezone_set("Asia/Kolkata");
            
            $extObject = explode(".", $objectName);
            $newObject = end($extObject);
            $objectName = $objectCode;
            
            $sourceBucket = BUCKET_NAME;
            $sourceKeyname = $objectPath.$objectName;
            $targetBucket = BUCKET_NAME_TWO;
            
            $s3Client = $this->getconfig();
            $s3Client->copyObject([
                'Bucket'     => $targetBucket,
                'Key'        => "{$sourceKeyname}",
                'CopySource' => "{$sourceBucket}/{$sourceKeyname}",
            ]);
            $batch = array();
            for ($i = 1; $i <= 1; $i++) {
                $batch[] = $s3Client->getCommand('CopyObject', [
                    'Bucket'     => $targetBucket,
                    'Key'        => "{$sourceKeyname}",
                    'CopySource' => "{$sourceBucket}/{$sourceKeyname}",
                ]);
            }
            $results = CommandPool::batch($s3Client, $batch);
            foreach($results as $result) {
                return $result->get('ObjectURL');
            }
        } else {
            redirect('logout');
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
    
    // Bucket Copy Function
    public function copyData($type){
        if($type == 'mods'){
            $table = MCPE_MODS;
        } else if($type == 'addons'){
            $table = MCPE_ADDONS;
        } else if($type == 'maps'){
            $table = MCPE_MAPS;
        } else if($type == 'seeds'){
            $table = MCPE_SEEDS;
        } else if($type == 'textures'){
            $table = MCPE_TEXTURES;
        } else if($type == 'shaders'){
            $table = MCPE_SHADERS;
        } else {
            $table = '';
        }
        
        $viewData = $this->DataModel->viewCopyData('unique_id '.'DESC', 'is_copy = "False"', 5, $table);

        foreach($viewData as $viewRow){
            $imageKey = $viewRow->data_image;
            $newImageKey = basename($imageKey);
            $imageName = $imageKey;
            $imageCode = $newImageKey;
            $imageTemp = $imageKey;
            $imagePath = DATA_IMAGE;
            $imageResponse = $this->copyBucketObject($imageName, $imageCode, $imageTemp, $imagePath);
            
            $bundleKey = $viewRow->data_bundle;
            $newBundleKey = basename($bundleKey);
            $bundleName = $bundleKey;
            $bundleCode = $newBundleKey;
            $bundleTemp = $bundleKey;
            $bundlePath = DATA_BUNDLE;
            $bundleResponse = $this->copyBucketObject($bundleName, $bundleCode, $bundleTemp, $bundlePath);
            
            $editData = array(
                'is_copy'=>'True'
            );
            $editDataEntry = $this->DataModel->editData('unique_id = '.$viewRow->unique_id, $table, $editData);
        }
        if($type == 'mods'){
            redirect('mods-view');
        } else if($type == 'addons'){
            redirect('addons-view');
        } else if($type == 'maps'){
            redirect('maps-view');
        } else if($type == 'seeds'){
            redirect('seeds-view');
        } else if($type == 'textures'){
            redirect('textures-view');
        } else if($type == 'shaders'){
            redirect('shaders-view');
        } else {
            redirect('dashboard');
        }
    }

    //Category Mods Functions
    public function categoryModsNew(){
        $isLogin = $this->checkAuth();
        if($isLogin == "True"){
            if(!empty($this->session->userdata['member_role'])) { 
                if($this->session->userdata['member_role'] == "Administrator" or $this->session->userdata['member_role'] == "Developer") {
                    $this->form_validation->set_rules('category_name', 'Text', 'required');
                    
                    if ($this->form_validation->run() == FALSE){
                        $data['error'] = "";
                        $this->load->view('header');
                        $this->load->view('android/category_mods_new',$data);
                        $this->load->view('footer');
                    } else {
                        $newData = array(
                            'category_name'=>$this->input->post('category_name'),
                            'category_status'=>$this->input->post('category_status'),
                        );
                        $newDataEntry = $this->DataModel->insertData(MCPE_CATEGORY_MODS, $newData);
                        if($newDataEntry){
                          redirect('category-mods-view');  
                        }
                    }
                } else {
                    redirect('error');
                }
            } else {
                redirect('error');
            }
        } else {
            redirect('logout');
        }
    }

    public function categoryModsView(){
        $isLogin = $this->checkAuth();
        if($isLogin == "True"){
            if(!empty($this->session->userdata['member_role'])) { 
                if($this->session->userdata['member_role'] == "Administrator" or $this->session->userdata['member_role'] == "Developer") {
                   
                    $data = array();
                    //get rows count
                    $conditions['returnType'] = 'count';
                    $totalRec = $this->DataModel->viewCategoryModsData($conditions, MCPE_CATEGORY_MODS);
                    
                    //pagination config
                    $config['base_url']    = site_url('category-mods-view');
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

                    $categoryMods = $this->DataModel->viewCategoryModsData($conditions, MCPE_CATEGORY_MODS);
                    $data['viewCategoryMods'] = array();
                    
                    if (is_array($categoryMods) || is_object($categoryMods)){
                        foreach($categoryMods as $Row){
                            $dataArray = array();
                            $dataArray['category_id'] = $Row['category_id'];
                            $dataArray['category_name'] = $Row['category_name'];
                            $dataArray['category_status'] = $Row['category_status'];
                            
                            array_push($data['viewCategoryMods'], $dataArray);
                        }
                    }

                    if($data['viewCategoryMods'] != null){
                        $this->load->view('header');
                        $this->load->view('android/category_mods_view',$data);
                        $this->load->view('footer');
                    } else {
                        $data['msg'] = array(
                            'data_title'=>"Category Mods Database is Empty",
                            'data_description'=>"Please add category mods from the below button.",
                            'button_link'=>"category-mods-new",
                            'button_text'=>"New Category Mods",
                        );
                        $this->load->view('header');
                        $this->load->view('nodata', $data);
                        $this->load->view('footer');
                    }
                } else {
                    redirect('error');
                }
            } else {
                redirect('error');
            }
        } else {
            redirect('logout');
        }
    }

    public function categoryModsEdit($categoryID = 0){
        $isLogin = $this->checkAuth();
        if($isLogin == "True"){
            if(!empty($this->session->userdata['member_role'])) { 
                if($this->session->userdata['member_role'] == "Administrator" or $this->session->userdata['member_role'] == "Developer") {
                    $checkEncryption = $this->DataModel->checkEncrypt($categoryID,ENCRYPT_TABLE);
                    if($checkEncryption){
                        $categoryID = $checkEncryption->enc_number;
                    }
                   
                    if(ctype_digit($categoryID)){
                        $data['categoryModsData'] = $this->DataModel->getData('category_id = '.$categoryID, MCPE_CATEGORY_MODS);
                        if(!empty($data['categoryModsData'])){
                            $this->load->view('header');
                            $this->load->view('android/category_mods_edit',$data);
                            $this->load->view('footer');
                        } else {
                            redirect('error');
                        }
                        if($this->input->post('submit')){
                            $editData = array(
                                'category_name'=>$this->input->post('category_name'),
                                'category_status'=>$this->input->post('category_status'),
                            );
                            $editDataEntry = $this->DataModel->editData('category_id = '.$categoryID, MCPE_CATEGORY_MODS, $editData);
                            if($editDataEntry){
                                redirect('category-mods-view');
                            }
                        }
                    } else {
                        redirect('error');
                    }
                } else {
                    redirect('error');
                }
            } else {
                redirect('error');
            }
        } else {
            redirect('logout');
        }
    }

    //Category Addons Functions
    public function categoryAddonsNew(){
        $isLogin = $this->checkAuth();
        if($isLogin == "True"){
            if(!empty($this->session->userdata['member_role'])) { 
                if($this->session->userdata['member_role'] == "Administrator" or $this->session->userdata['member_role'] == "Developer") {
                    $this->form_validation->set_rules('category_name', 'Text', 'required');
                    
                    if ($this->form_validation->run() == FALSE){
                        $data['error'] = "";
                        $this->load->view('header');
                        $this->load->view('android/category_addons_new',$data);
                        $this->load->view('footer');
                    }else{
                        $newData = array(
                            'category_name'=>$this->input->post('category_name'),
                            'category_status'=>$this->input->post('category_status'),
                        );
                        $newDataEntry = $this->DataModel->insertData(MCPE_CATEGORY_ADDONS, $newData);
                        if($newDataEntry){
                          redirect('category-addons-view');  
                        }
                    }
                } else {
                    redirect('error');
                }
            } else {
                redirect('error');
            }
        } else {
            redirect('logout');
        }
    }

    public function categoryAddonsView(){
        $isLogin = $this->checkAuth();
        if($isLogin == "True"){
            if(!empty($this->session->userdata['member_role'])) { 
                if($this->session->userdata['member_role'] == "Administrator" or $this->session->userdata['member_role'] == "Developer") {
                   
                    $data = array();
                    //get rows count
                    $conditions['returnType'] = 'count';
                    $totalRec = $this->DataModel->viewCategoryAddonsData($conditions, MCPE_CATEGORY_ADDONS);
                    
                    //pagination config
                    $config['base_url']    = site_url('category-addons-view');
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

                    $categoryAddons = $this->DataModel->viewCategoryAddonsData($conditions, MCPE_CATEGORY_ADDONS);
                    $data['viewCategoryAddons'] = array();
                    
                    if (is_array($categoryAddons) || is_object($categoryAddons)){
                        foreach($categoryAddons as $Row){
                            $dataArray = array();
                            $dataArray['category_id'] = $Row['category_id'];
                            $dataArray['category_name'] = $Row['category_name'];
                            $dataArray['category_status'] = $Row['category_status'];
                            
                            array_push($data['viewCategoryAddons'], $dataArray);
                        }
                    }

                    if($data['viewCategoryAddons'] != null){
                        $this->load->view('header');
                        $this->load->view('android/category_addons_view',$data);
                        $this->load->view('footer');
                    } else {
                        $data['msg'] = array(
                            'data_title'=>"Category Addons Database is Empty",
                            'data_description'=>"Please add category addons from the below button.",
                            'button_link'=>"category-addons-new",
                            'button_text'=>"New Category Addons",
                        );
                        $this->load->view('header');
                        $this->load->view('nodata', $data);
                        $this->load->view('footer');
                    }
                } else {
                    redirect('error');
                }
            } else {
                redirect('error');
            }
        } else {
            redirect('logout');
        }
    }

    public function categoryAddonsEdit($categoryID = 0){
        $isLogin = $this->checkAuth();
        if($isLogin == "True"){
            if(!empty($this->session->userdata['member_role'])) { 
                if($this->session->userdata['member_role'] == "Administrator" or $this->session->userdata['member_role'] == "Developer") {
                    $checkEncryption = $this->DataModel->checkEncrypt($categoryID,ENCRYPT_TABLE);
                    if($checkEncryption){
                        $categoryID = $checkEncryption->enc_number;
                    }
                   
                    if(ctype_digit($categoryID)){
                        $data['categoryAddonsData'] = $this->DataModel->getData('category_id = '.$categoryID, MCPE_CATEGORY_ADDONS);
                        if(!empty($data['categoryAddonsData'])){
                            $this->load->view('header');
                            $this->load->view('android/category_addons_edit',$data);
                            $this->load->view('footer');
                        } else {
                            redirect('error');
                        }
                        if($this->input->post('submit')){
                            $editData = array(
                                'category_name'=>$this->input->post('category_name'),
                                'category_status'=>$this->input->post('category_status'),
                            );
                            $editDataEntry = $this->DataModel->editData('category_id = '.$categoryID, MCPE_CATEGORY_ADDONS, $editData);
                            if($editDataEntry){
                                redirect('category-addons-view');
                            }
                        }
                    } else {
                        redirect('error');
                    }
                } else {
                    redirect('error');
                }
            } else {
                redirect('error');
            }
        } else {
            redirect('logout');
        }
    }

    //Category Maps Functions
    public function categoryMapsNew(){
        $isLogin = $this->checkAuth();
        if($isLogin == "True"){
            if(!empty($this->session->userdata['member_role'])) { 
                if($this->session->userdata['member_role'] == "Administrator" or $this->session->userdata['member_role'] == "Developer") {
                    $this->form_validation->set_rules('category_name', 'Text', 'required');
                    
                    if ($this->form_validation->run() == FALSE){
                        $data['error'] = "";
                        $this->load->view('header');
                        $this->load->view('android/category_maps_new',$data);
                        $this->load->view('footer');
                    }else{
                        $newData = array(
                            'category_name'=>$this->input->post('category_name'),
                            'category_status'=>$this->input->post('category_status'),
                        );
                        $newDataEntry = $this->DataModel->insertData(MCPE_CATEGORY_MAPS, $newData);
                        if($newDataEntry){
                          redirect('category-maps-view');  
                        }
                    }
                } else {
                    redirect('error');
                }
            } else {
                redirect('error');
            }
        } else {
            redirect('logout');
        }
    }

    public function categoryMapsView(){
        $isLogin = $this->checkAuth();
        if($isLogin == "True"){
            if(!empty($this->session->userdata['member_role'])) { 
                if($this->session->userdata['member_role'] == "Administrator" or $this->session->userdata['member_role'] == "Developer") {
                   
                    $data = array();
                    //get rows count
                    $conditions['returnType'] = 'count';
                    $totalRec = $this->DataModel->viewCategoryMapsData($conditions, MCPE_CATEGORY_MAPS);
                    
                    //pagination config
                    $config['base_url']    = site_url('category-maps-view');
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

                    $categoryMaps = $this->DataModel->viewCategoryMapsData($conditions, MCPE_CATEGORY_MAPS);
                    $data['viewCategoryMaps'] = array();
                    
                    if (is_array($categoryMaps) || is_object($categoryMaps)){
                        foreach($categoryMaps as $Row){
                            $dataArray = array();
                            $dataArray['category_id'] = $Row['category_id'];
                            $dataArray['category_name'] = $Row['category_name'];
                            $dataArray['category_status'] = $Row['category_status'];
                            
                            array_push($data['viewCategoryMaps'], $dataArray);
                        }
                    }

                    if($data['viewCategoryMaps'] != null){
                        $this->load->view('header');
                        $this->load->view('android/category_maps_view',$data);
                        $this->load->view('footer');
                    } else {
                        $data['msg'] = array(
                            'data_title'=>"Category Maps Database is Empty",
                            'data_description'=>"Please add category maps from the below button.",
                            'button_link'=>"category-maps-new",
                            'button_text'=>"New Category Maps",
                        );
                        $this->load->view('header');
                        $this->load->view('nodata', $data);
                        $this->load->view('footer');
                    }
                } else {
                    redirect('error');
                }
            } else {
                redirect('error');
            }
        } else {
            redirect('logout');
        }
    }

    public function categoryMapsEdit($categoryID = 0){
        $isLogin = $this->checkAuth();
        if($isLogin == "True"){
            if(!empty($this->session->userdata['member_role'])) { 
                if($this->session->userdata['member_role'] == "Administrator" or $this->session->userdata['member_role'] == "Developer") {
                    $checkEncryption = $this->DataModel->checkEncrypt($categoryID,ENCRYPT_TABLE);
                    if($checkEncryption){
                        $categoryID = $checkEncryption->enc_number;
                    }
                   
                    if(ctype_digit($categoryID)){
                        $data['categoryMapsData'] = $this->DataModel->getData('category_id = '.$categoryID, MCPE_CATEGORY_MAPS);
                        if(!empty($data['categoryMapsData'])){
                            $this->load->view('header');
                            $this->load->view('android/category_maps_edit',$data);
                            $this->load->view('footer');
                        } else {
                            redirect('error');
                        }
                        if($this->input->post('submit')){
                            $editData = array(
                                'category_name'=>$this->input->post('category_name'),
                                'category_status'=>$this->input->post('category_status'),
                            );
                            $editDataEntry = $this->DataModel->editData('category_id = '.$categoryID, MCPE_CATEGORY_MAPS, $editData);
                            if($editDataEntry){
                                redirect('category-maps-view');
                            }
                        }
                    } else {
                        redirect('error');
                    }
                } else {
                    redirect('error');
                }
            } else {
                redirect('error');
            }
        } else {
            redirect('logout');
        }
    }

    //Category Seeds Functions
    public function categorySeedsNew(){
        $isLogin = $this->checkAuth();
        if($isLogin == "True"){
            if(!empty($this->session->userdata['member_role'])) { 
                if($this->session->userdata['member_role'] == "Administrator" or $this->session->userdata['member_role'] == "Developer") {
                    $this->form_validation->set_rules('category_name', 'Text', 'required');
                    
                    if ($this->form_validation->run() == FALSE){
                        $data['error'] = "";
                        $this->load->view('header');
                        $this->load->view('android/category_seeds_new',$data);
                        $this->load->view('footer');
                    }else{
                        $newData = array(
                            'category_name'=>$this->input->post('category_name'),
                            'category_status'=>$this->input->post('category_status'),
                        );
                        $newDataEntry = $this->DataModel->insertData(MCPE_CATEGORY_SEEDS, $newData);
                        if($newDataEntry){
                          redirect('category-seeds-view');  
                        }
                    }
                } else {
                    redirect('error');
                }
            } else {
                redirect('error');
            }
        } else {
            redirect('logout');
        }
    }

    public function categorySeedsView(){
        $isLogin = $this->checkAuth();
        if($isLogin == "True"){
            if(!empty($this->session->userdata['member_role'])) { 
                if($this->session->userdata['member_role'] == "Administrator" or $this->session->userdata['member_role'] == "Developer") {
                   
                    $data = array();
                    //get rows count
                    $conditions['returnType'] = 'count';
                    $totalRec = $this->DataModel->viewCategorySeedsData($conditions, MCPE_CATEGORY_SEEDS);
                    
                    //pagination config
                    $config['base_url']    = site_url('category-seeds-view');
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

                    $categorySeeds = $this->DataModel->viewCategorySeedsData($conditions, MCPE_CATEGORY_SEEDS);
                    $data['viewCategorySeeds'] = array();
                    
                    if (is_array($categorySeeds) || is_object($categorySeeds)){
                        foreach($categorySeeds as $Row){
                            $dataArray = array();
                            $dataArray['category_id'] = $Row['category_id'];
                            $dataArray['category_name'] = $Row['category_name'];
                            $dataArray['category_status'] = $Row['category_status'];
                            
                            array_push($data['viewCategorySeeds'], $dataArray);
                        }
                    }

                    if($data['viewCategorySeeds'] != null){
                        $this->load->view('header');
                        $this->load->view('android/category_seeds_view',$data);
                        $this->load->view('footer');
                    } else {
                        $data['msg'] = array(
                            'data_title'=>"Category Seeds Database is Empty",
                            'data_description'=>"Please add category seeds from the below button.",
                            'button_link'=>"category-seeds-new",
                            'button_text'=>"New Category Seeds",
                        );
                        $this->load->view('header');
                        $this->load->view('nodata', $data);
                        $this->load->view('footer');
                    }
                } else {
                    redirect('error');
                }
            } else {
                redirect('error');
            }
        } else {
            redirect('logout');
        }
    }

    public function categorySeedsEdit($categoryID = 0){
        $isLogin = $this->checkAuth();
        if($isLogin == "True"){
            if(!empty($this->session->userdata['member_role'])) { 
                if($this->session->userdata['member_role'] == "Administrator" or $this->session->userdata['member_role'] == "Developer") {
                    $checkEncryption = $this->DataModel->checkEncrypt($categoryID,ENCRYPT_TABLE);
                    if($checkEncryption){
                        $categoryID = $checkEncryption->enc_number;
                    }
                   
                    if(ctype_digit($categoryID)){
                        $data['categorySeedsData'] = $this->DataModel->getData('category_id = '.$categoryID, MCPE_CATEGORY_SEEDS);
                        if(!empty($data['categorySeedsData'])){
                            $this->load->view('header');
                            $this->load->view('android/category_seeds_edit',$data);
                            $this->load->view('footer');
                        } else {
                            redirect('error');
                        }
                        if($this->input->post('submit')){
                            $editData = array(
                                'category_name'=>$this->input->post('category_name'),
                                'category_status'=>$this->input->post('category_status'),
                            );
                            $editDataEntry = $this->DataModel->editData('category_id = '.$categoryID, MCPE_CATEGORY_SEEDS, $editData);
                            if($editDataEntry){
                                redirect('category-seeds-view');
                            }
                        }
                    } else {
                        redirect('error');
                    }
                } else {
                    redirect('error');
                }
            } else {
                redirect('error');
            }
        } else {
            redirect('logout');
        }
    }

    //Category Textures Functions
    public function categoryTexturesNew(){
        $isLogin = $this->checkAuth();
        if($isLogin == "True"){
            if(!empty($this->session->userdata['member_role'])) { 
                if($this->session->userdata['member_role'] == "Administrator" or $this->session->userdata['member_role'] == "Developer") {
                    $this->form_validation->set_rules('category_name', 'Text', 'required');
                    
                    if ($this->form_validation->run() == FALSE){
                        $data['error'] = "";
                        $this->load->view('header');
                        $this->load->view('android/category_textures_new',$data);
                        $this->load->view('footer');
                    }else{
                        $newData = array(
                            'category_name'=>$this->input->post('category_name'),
                            'category_status'=>$this->input->post('category_status'),
                        );
                        $newDataEntry = $this->DataModel->insertData(MCPE_CATEGORY_TEXTURES, $newData);
                        if($newDataEntry){
                          redirect('category-textures-view');  
                        }
                    }
                } else {
                    redirect('error');
                }
            } else {
                redirect('error');
            }
        } else {
            redirect('logout');
        }
    }

    public function categoryTexturesView(){
        $isLogin = $this->checkAuth();
        if($isLogin == "True"){
            if(!empty($this->session->userdata['member_role'])) { 
                if($this->session->userdata['member_role'] == "Administrator" or $this->session->userdata['member_role'] == "Developer") {
                   
                    $data = array();
                    //get rows count
                    $conditions['returnType'] = 'count';
                    $totalRec = $this->DataModel->viewCategoryTexturesData($conditions, MCPE_CATEGORY_TEXTURES);
                    
                    //pagination config
                    $config['base_url']    = site_url('category-textures-view');
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

                    $categoryTextures = $this->DataModel->viewCategoryTexturesData($conditions, MCPE_CATEGORY_TEXTURES);
                    $data['viewCategoryTextures'] = array();
                    
                    if (is_array($categoryTextures) || is_object($categoryTextures)){
                        foreach($categoryTextures as $Row){
                            $dataArray = array();
                            $dataArray['category_id'] = $Row['category_id'];
                            $dataArray['category_name'] = $Row['category_name'];
                            $dataArray['category_status'] = $Row['category_status'];
                            
                            array_push($data['viewCategoryTextures'], $dataArray);
                        }
                    }

                    if($data['viewCategoryTextures'] != null){
                        $this->load->view('header');
                        $this->load->view('android/category_textures_view',$data);
                        $this->load->view('footer');
                    } else {
                        $data['msg'] = array(
                            'data_title'=>"Category Textures Database is Empty",
                            'data_description'=>"Please add category textures from the below button.",
                            'button_link'=>"category-textures-new",
                            'button_text'=>"New Category Textures",
                        );
                        $this->load->view('header');
                        $this->load->view('nodata', $data);
                        $this->load->view('footer');
                    }
                } else {
                    redirect('error');
                }
            } else {
                redirect('error');
            }
        } else {
            redirect('logout');
        }
    }

    public function categoryTexturesEdit($categoryID = 0){
        $isLogin = $this->checkAuth();
        if($isLogin == "True"){
            if(!empty($this->session->userdata['member_role'])) { 
                if($this->session->userdata['member_role'] == "Administrator" or $this->session->userdata['member_role'] == "Developer") {
                    $checkEncryption = $this->DataModel->checkEncrypt($categoryID,ENCRYPT_TABLE);
                    if($checkEncryption){
                        $categoryID = $checkEncryption->enc_number;
                    }
                   
                    if(ctype_digit($categoryID)){
                        $data['categoryTexturesData'] = $this->DataModel->getData('category_id = '.$categoryID, MCPE_CATEGORY_TEXTURES);
                        if(!empty($data['categoryTexturesData'])){
                            $this->load->view('header');
                            $this->load->view('android/category_textures_edit',$data);
                            $this->load->view('footer');
                        } else {
                            redirect('error');
                        }
                        if($this->input->post('submit')){
                            $editData = array(
                                'category_name'=>$this->input->post('category_name'),
                                'category_status'=>$this->input->post('category_status'),
                            );
                            $editDataEntry = $this->DataModel->editData('category_id = '.$categoryID, MCPE_CATEGORY_TEXTURES, $editData);
                            if($editDataEntry){
                                redirect('category-textures-view');
                            }
                        }
                    } else {
                        redirect('error');
                    }
                } else {
                    redirect('error');
                }
            } else {
                redirect('error');
            }
        } else {
            redirect('logout');
        }
    }

    //Category Shaders Functions
    public function categoryShadersNew(){
        $isLogin = $this->checkAuth();
        if($isLogin == "True"){
            if(!empty($this->session->userdata['member_role'])) { 
                if($this->session->userdata['member_role'] == "Administrator" or $this->session->userdata['member_role'] == "Developer") {
                    $this->form_validation->set_rules('category_name', 'Text', 'required');
                    
                    if ($this->form_validation->run() == FALSE){
                        $data['error'] = "";
                        $this->load->view('header');
                        $this->load->view('android/category_shaders_new',$data);
                        $this->load->view('footer');
                    }else{
                        $newData = array(
                            'category_name'=>$this->input->post('category_name'),
                            'category_status'=>$this->input->post('category_status'),
                        );
                        $newDataEntry = $this->DataModel->insertData(MCPE_CATEGORY_SHADERS, $newData);
                        if($newDataEntry){
                          redirect('category-shaders-view');  
                        }
                    }
                } else {
                    redirect('error');
                }
            } else {
                redirect('error');
            }
        } else {
            redirect('logout');
        }
    }

    public function categoryShadersView(){
        $isLogin = $this->checkAuth();
        if($isLogin == "True"){
            if(!empty($this->session->userdata['member_role'])) { 
                if($this->session->userdata['member_role'] == "Administrator" or $this->session->userdata['member_role'] == "Developer") {
                   
                    $data = array();
                    //get rows count
                    $conditions['returnType'] = 'count';
                    $totalRec = $this->DataModel->viewCategoryShadersData($conditions, MCPE_CATEGORY_SHADERS);
                    
                    //pagination config
                    $config['base_url']    = site_url('category-shaders-view');
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

                    $categoryShaders = $this->DataModel->viewCategoryShadersData($conditions, MCPE_CATEGORY_SHADERS);
                    $data['viewCategoryShaders'] = array();
                    
                    if (is_array($categoryShaders) || is_object($categoryShaders)){
                        foreach($categoryShaders as $Row){
                            $dataArray = array();
                            $dataArray['category_id'] = $Row['category_id'];
                            $dataArray['category_name'] = $Row['category_name'];
                            $dataArray['category_status'] = $Row['category_status'];
                            
                            array_push($data['viewCategoryShaders'], $dataArray);
                        }
                    }

                    if($data['viewCategoryShaders'] != null){
                        $this->load->view('header');
                        $this->load->view('android/category_shaders_view',$data);
                        $this->load->view('footer');
                    } else {
                        $data['msg'] = array(
                            'data_title'=>"Category Shaders Database is Empty",
                            'data_description'=>"Please add category shaders from the below button.",
                            'button_link'=>"category-shaders-new",
                            'button_text'=>"New Category Shaders",
                        );
                        $this->load->view('header');
                        $this->load->view('nodata', $data);
                        $this->load->view('footer');
                    }
                } else {
                    redirect('error');
                }
            } else {
                redirect('error');
            }
        } else {
            redirect('logout');
        }
    }

    public function categoryShadersEdit($categoryID = 0){
        $isLogin = $this->checkAuth();
        if($isLogin == "True"){
            if(!empty($this->session->userdata['member_role'])) { 
                if($this->session->userdata['member_role'] == "Administrator" or $this->session->userdata['member_role'] == "Developer") {
                    $checkEncryption = $this->DataModel->checkEncrypt($categoryID,ENCRYPT_TABLE);
                    if($checkEncryption){
                        $categoryID = $checkEncryption->enc_number;
                    }
                   
                    if(ctype_digit($categoryID)){
                        $data['categoryShadersData'] = $this->DataModel->getData('category_id = '.$categoryID, MCPE_CATEGORY_SHADERS);
                        if(!empty($data['categoryShadersData'])){
                            $this->load->view('header');
                            $this->load->view('android/category_shaders_edit',$data);
                            $this->load->view('footer');
                        } else {
                            redirect('error');
                        }
                        if($this->input->post('submit')){
                            $editData = array(
                                'category_name'=>$this->input->post('category_name'),
                                'category_status'=>$this->input->post('category_status'),
                            );
                            $editDataEntry = $this->DataModel->editData('category_id = '.$categoryID, MCPE_CATEGORY_SHADERS, $editData);
                            if($editDataEntry){
                                redirect('category-shaders-view');
                            }
                        }
                    } else {
                        redirect('error');
                    }
                } else {
                    redirect('error');
                }
            } else {
                redirect('error');
            }
        } else {
            redirect('logout');
        }
    }

    //Category Skin Functions
    public function categorySkinNew(){
        $isLogin = $this->checkAuth();
        if($isLogin == "True"){
            if(!empty($this->session->userdata['member_role'])) { 
                if($this->session->userdata['member_role'] == "Administrator" or $this->session->userdata['member_role'] == "Developer") {
                    $this->form_validation->set_rules('category_name', 'Text', 'required');
                    
                    if ($this->form_validation->run() == FALSE){
                        $data['error'] = "";
                        $this->load->view('header');
                        $this->load->view('android/category_skin_new',$data);
                        $this->load->view('footer');
                    }else{
                        $newData = array(
                            'category_name'=>$this->input->post('category_name'),
                            'category_status'=>$this->input->post('category_status'),
                        );
                        $newDataEntry = $this->DataModel->insertData(MCPE_CATEGORY_SKIN, $newData);
                        if($newDataEntry){
                          redirect('category-skin-view');  
                        }
                    }
                } else {
                    redirect('error');
                }
            } else {
                redirect('error');
            }
        } else {
            redirect('logout');
        }
    }

    public function categorySkinView(){
        $isLogin = $this->checkAuth();
        if($isLogin == "True"){
            if(!empty($this->session->userdata['member_role'])) { 
                if($this->session->userdata['member_role'] == "Administrator" or $this->session->userdata['member_role'] == "Developer") {
                   
                    $data = array();
                    //get rows count
                    $conditions['returnType'] = 'count';
                    $totalRec = $this->DataModel->viewCategorySkinData($conditions, MCPE_CATEGORY_SKIN);
                    
                    //pagination config
                    $config['base_url']    = site_url('category-skin-view');
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

                    $categorySkin = $this->DataModel->viewCategorySkinData($conditions, MCPE_CATEGORY_SKIN);
                    $data['viewCategorySkin'] = array();
                    
                    if (is_array($categorySkin) || is_object($categorySkin)){
                        foreach($categorySkin as $Row){
                            $dataArray = array();
                            $dataArray['category_id'] = $Row['category_id'];
                            $dataArray['category_name'] = $Row['category_name'];
                            $dataArray['category_status'] = $Row['category_status'];
                            
                            array_push($data['viewCategorySkin'], $dataArray);
                        }
                    }

                    if($data['viewCategorySkin'] != null){
                        $this->load->view('header');
                        $this->load->view('android/category_skin_view',$data);
                        $this->load->view('footer');
                    } else {
                        $data['msg'] = array(
                            'data_title'=>"Category Skin Database is Empty",
                            'data_description'=>"Please add category skin from the below button.",
                            'button_link'=>"category-skin-new",
                            'button_text'=>"New Category Skin",
                        );
                        $this->load->view('header');
                        $this->load->view('nodata', $data);
                        $this->load->view('footer');
                    }
                } else {
                    redirect('error');
                }
            } else {
                redirect('error');
            }
        } else {
            redirect('logout');
        }
    }

    public function categorySkinEdit($categoryID = 0){
        $isLogin = $this->checkAuth();
        if($isLogin == "True"){
            if(!empty($this->session->userdata['member_role'])) { 
                if($this->session->userdata['member_role'] == "Administrator" or $this->session->userdata['member_role'] == "Developer") {
                    $checkEncryption = $this->DataModel->checkEncrypt($categoryID,ENCRYPT_TABLE);
                    if($checkEncryption){
                        $categoryID = $checkEncryption->enc_number;
                    }
                   
                    if(ctype_digit($categoryID)){
                        $data['categorySkinData'] = $this->DataModel->getData('category_id = '.$categoryID, MCPE_CATEGORY_SKIN);
                        if(!empty($data['categorySkinData'])){
                            $this->load->view('header');
                            $this->load->view('android/category_skin_edit',$data);
                            $this->load->view('footer');
                        } else {
                            redirect('error');
                        }
                        if($this->input->post('submit')){
                            $editData = array(
                                'category_name'=>$this->input->post('category_name'),
                                'category_status'=>$this->input->post('category_status'),
                            );
                            $editDataEntry = $this->DataModel->editData('category_id = '.$categoryID, MCPE_CATEGORY_SKIN, $editData);
                            if($editDataEntry){
                                redirect('category-skin-view');
                            }
                        }
                    } else {
                        redirect('error');
                    }
                } else {
                    redirect('error');
                }
            } else {
                redirect('error');
            }
        } else {
            redirect('logout');
        }
    }

    //Mods Functions
    public function modsNew(){
        $isLogin = $this->checkAuth();
        if($isLogin == "True"){
            if(!empty($this->session->userdata['member_role'])) { 
                if($this->session->userdata['member_role'] == "Administrator" or $this->session->userdata['member_role'] == "Developer") {
                    date_default_timezone_set("Asia/Kolkata");
                    $s3Client = $this->getconfig();
                    $uniqueCode = $this->uniqueKey();
                    
                    $this->form_validation->set_rules('category_id', 'Text', 'required');
                    $this->form_validation->set_rules('data_name', 'Text', 'required');
                    $this->form_validation->set_rules('data_description', 'Text', 'required');
                    $this->form_validation->set_rules('data_support_version', 'Text', 'required');
                    $this->form_validation->set_rules('data_price', 'Text', 'required');
                    $this->form_validation->set_rules('data_size', 'Text', 'required');
                    $this->form_validation->set_rules('data_view', 'Text', 'required');
                    $this->form_validation->set_rules('data_download', 'Text', 'required');

                    if (empty($_FILES['data_image']['name'])){
                        $this->form_validation->set_rules('data_image', 'Data Image', 'required');
                    }
                    $this->form_validation->set_error_delimiters('','');

                    if (empty($_FILES['data_bundle']['name'])){
                        $this->form_validation->set_rules('data_bundle', 'Data Bundle', 'required');
                    }
                    $this->form_validation->set_error_delimiters('','');
                    
                    if ($this->form_validation->run() == FALSE){
                        $data['categoryModsData'] = $this->DataModel->viewData('category_id '.'DESC', null, MCPE_CATEGORY_MODS);
                        $data['error'] = "";
                        $this->load->view('header');
                        $this->load->view('android/mods_new',$data);
                        $this->load->view('footer');
                    } else {

                        $imageName = $_FILES['data_image']['name'];
                        $imageTemp = $_FILES['data_image']['tmp_name'];
                        $imagePath = DATA_IMAGE;
                        $imageResponse = $this->newBucketObject($imageName, $uniqueCode, $imageTemp, $imagePath);

                        $bundleName = $_FILES['data_bundle']['name'];
                        $bundleTemp = $_FILES['data_bundle']['tmp_name'];
                        $bundlePath = DATA_BUNDLE;
                        $bundleResponse = $this->newBucketObject($bundleName, $uniqueCode, $bundleTemp, $bundlePath);
                        
                        $newData = array(
                            'category_id'=>$this->input->post('category_id'),
                            'data_name'=>$this->input->post('data_name'),
                            'data_description'=>$this->input->post('data_description'),
                            'data_image'=>$imageResponse,
                            'data_bundle'=>$bundleResponse,
                            'data_support_version'=>$this->input->post('data_support_version'),
                            'data_price'=>$this->input->post('data_price'),
                            'data_size'=>$this->input->post('data_size'),
                            'data_view'=>$this->input->post('data_view'),
                            'data_download'=>$this->input->post('data_download'),
                            'is_copy'=>'False',
                            'data_status'=>$this->input->post('data_status'),
                        );
                        $newDataEntry = $this->DataModel->insertData(MCPE_MODS, $newData);
                        if($newDataEntry){
                          redirect('mods-view');  
                        }
                    }
                } else {
                    redirect('error');
                }
            } else {
                redirect('error');
            }
        } else {
            redirect('logout');
        }
    }

    public function modsView(){
        $isLogin = $this->checkAuth();
        if($isLogin == "True"){
            if(!empty($this->session->userdata['member_role'])) { 
                if($this->session->userdata['member_role'] == "Administrator" or $this->session->userdata['member_role'] == "Developer") {
                    if(isset($_POST['reset'])){
                        $this->session->unset_userdata('session_mods_view');
                    }
                    if(isset($_POST['search'])){
                        $searchModsView = $this->input->post('search_mods_view');
                        $this->session->set_userdata('session_mods_view',$searchModsView);
                    }
                    $sessionModsView =  $this->session->userdata('session_mods_view');
                   
                    $data = array();
                    //get rows count
                    $conditions['search_mods_view'] = $sessionModsView;
                    $conditions['returnType'] = 'count';
                    $totalRec = $this->DataModel->viewModsData($conditions, MCPE_MODS);
                    
                    //pagination config
                    $config['base_url']    = site_url('mods-view');
                    $config['uri_segment'] = 2;
                    $config['total_rows']  = $totalRec;
                    $config['per_page']    = 20;
                    
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
                    $conditions['limit'] = 20;

                    $mods = $this->DataModel->viewModsData($conditions, MCPE_MODS);
                    $data['viewMods'] = array();
                    
                    if(is_array($mods) || is_object($mods)){
                        foreach($mods as $Row){
                            $dataArray = array();
                            $dataArray['unique_id'] = $Row['unique_id'];
                            $dataArray['category_id'] = $Row['category_id'];
                            $dataArray['data_name'] = $Row['data_name'];
                            $dataArray['data_description'] = $Row['data_description'];
                            $dataArray['data_image'] = $Row['data_image'];
                            $dataArray['data_bundle'] = $Row['data_bundle'];
                            $dataArray['data_support_version'] = $Row['data_support_version'];
                            $dataArray['data_price'] = $Row['data_price'];
                            $dataArray['data_size'] = $Row['data_size'];
                            $dataArray['data_view'] = $Row['data_view'];
                            $dataArray['data_download'] = $Row['data_download'];
                            $dataArray['is_copy'] = $Row['is_copy'];
                            $dataArray['data_status'] = $Row['data_status'];
                            $dataArray['category_name'] = $this->DataModel->getCategoryNameData('category_id = '.$dataArray['category_id'], MCPE_CATEGORY_MODS);
                            array_push($data['viewMods'], $dataArray);
                        }
                    }

                    if($data['viewMods'] != null){
                        $this->load->view('header');
                        $this->load->view('android/mods_view',$data);
                        $this->load->view('footer');
                    } else {
                        $this->session->unset_userdata('session_mods_view');   
                        $data['msg'] = array(
                            'data_title'=>"Mods Database is Empty",
                            'data_description'=>"Please add mods & redirect mods from the below button.",
                            'button_link'=>"mods-new",
                            'button_text'=>"New Mods",
                            'button_link1'=>'mods-view',
                            'button_text1'=>"View Mods",
                        );
                        $this->load->view('header');
                        $this->load->view('nodata', $data);
                        $this->load->view('footer');
                    }
                } else {
                    redirect('error');
                }
            } else {
                redirect('error');
            }
        } else {
            redirect('logout');
        }
    }
    
    public function modsEdit($uniqueID = 0){
        $isLogin = $this->checkAuth();
        if($isLogin == "True"){
            if(!empty($this->session->userdata['member_role'])) { 
                if($this->session->userdata['member_role'] == "Administrator" or $this->session->userdata['member_role'] == "Developer") {
                    $checkEncryption = $this->DataModel->checkEncrypt($uniqueID,ENCRYPT_TABLE);
                    if($checkEncryption){
                        $uniqueID = $checkEncryption->enc_number;
                    }
                   
                    if(ctype_digit($uniqueID)){
                        $data['modsData'] = $this->DataModel->getData('unique_id = '.$uniqueID, MCPE_MODS);
                        $data['viewCategoryMods'] = $this->DataModel->viewData('category_id '.'DESC', null, MCPE_CATEGORY_MODS);
                        $categoryID = $data['modsData']->category_id;
                        $data['categoryModsData'] = $this->DataModel->getData('category_id = '.$categoryID, MCPE_CATEGORY_MODS);
                        if(!empty($data['modsData'])){
                            $this->load->view('header');
                            $this->load->view('android/mods_edit',$data);
                            $this->load->view('footer');
                        } else {
                            redirect('error');
                        }
                        if($this->input->post('submit')){
                            date_default_timezone_set("Asia/Kolkata");
                            $s3Client = $this->getconfig();
                            $uniqueCode = $this->uniqueKey();
                            
                            $imageKey = $data['modsData']->data_image;
                            $newImageKey = basename($imageKey);
                    
                            $bundleKey = $data['modsData']->data_bundle;
                            $newBundleKey = basename($bundleKey);
                            
                            if (!empty($_FILES['data_image']['name']) and !empty($_FILES['data_bundle']['name'])){
                                $deleteImage = $s3Client->deleteObject([
                                        'Bucket' => BUCKET_NAME,
                                        'Key'    => DATA_IMAGE.$newImageKey,
                                    ]);
                                $deleteBundle = $s3Client->deleteObject([
                                        'Bucket' => BUCKET_NAME,
                                        'Key'    => DATA_BUNDLE.$newBundleKey,
                                    ]);
                                
                                $imageName = $_FILES['data_image']['name'];
                                $imageTemp = $_FILES['data_image']['tmp_name'];
                                $imagePath = DATA_IMAGE;
                                $imageResponse = $this->newBucketObject($imageName, $uniqueCode, $imageTemp, $imagePath);

                                $bundleName = $_FILES['data_bundle']['name'];
                                $bundleTemp = $_FILES['data_bundle']['tmp_name'];
                                $bundlePath = DATA_BUNDLE;
                                $bundleResponse = $this->newBucketObject($bundleName, $uniqueCode, $bundleTemp, $bundlePath);

                                $editData = array(
                                    'category_id'=>$this->input->post('category_id'),
                                    'data_name'=>$this->input->post('data_name'),
                                    'data_description'=>$this->input->post('data_description'),
                                    'data_image'=>$imageResponse,
                                    'data_bundle'=>$bundleResponse,
                                    'data_support_version'=>$this->input->post('data_support_version'),
                                    'data_price'=>$this->input->post('data_price'),
                                    'data_size'=>$this->input->post('data_size'),
                                    'data_view'=>$this->input->post('data_view'),
                                    'data_download'=>$this->input->post('data_download'),
                                    'data_status'=>$this->input->post('data_status'),
                                );
                            } else if(!empty($_FILES['data_image']['name'])) {
                                $deleteImage = $s3Client->deleteObject([
                                    'Bucket' => BUCKET_NAME,
                                    'Key'    => DATA_IMAGE.$newImageKey,
                                ]);
                    
                                $imageName = $_FILES['data_image']['name'];
                                $imageTemp = $_FILES['data_image']['tmp_name'];
                                $imagePath = DATA_IMAGE;
                                $imageResponse = $this->newBucketObject($imageName, $uniqueCode, $imageTemp, $imagePath);
                    
                                $editData = array(
                                    'category_id'=>$this->input->post('category_id'),
                                    'data_name'=>$this->input->post('data_name'),
                                    'data_description'=>$this->input->post('data_description'),
                                    'data_image'=>$imageResponse,
                                    'data_support_version'=>$this->input->post('data_support_version'),
                                    'data_price'=>$this->input->post('data_price'),
                                    'data_size'=>$this->input->post('data_size'),
                                    'data_view'=>$this->input->post('data_view'),
                                    'data_download'=>$this->input->post('data_download'),
                                    'data_status'=>$this->input->post('data_status'),
                                );
                            } else if(!empty($_FILES['data_bundle']['name'])) {
                                $deleteBundle = $s3Client->deleteObject([
                                    'Bucket' => BUCKET_NAME,
                                    'Key'    => DATA_BUNDLE.$newBundleKey,
                                ]);
                                
                                $bundleName = $_FILES['data_bundle']['name'];
                                $bundleTemp = $_FILES['data_bundle']['tmp_name'];
                                $bundlePath = DATA_BUNDLE;
                                $bundleResponse = $this->newBucketObject($bundleName, $uniqueCode, $bundleTemp, $bundlePath);
                                
                                $editData = array(
                                    'category_id'=>$this->input->post('category_id'),
                                    'data_name'=>$this->input->post('data_name'),
                                    'data_description'=>$this->input->post('data_description'),
                                    'data_bundle'=>$bundleResponse,
                                    'data_support_version'=>$this->input->post('data_support_version'),
                                    'data_price'=>$this->input->post('data_price'),
                                    'data_size'=>$this->input->post('data_size'),
                                    'data_view'=>$this->input->post('data_view'),
                                    'data_download'=>$this->input->post('data_download'),
                                    'data_status'=>$this->input->post('data_status'),
                                );
                            } else {
                                $editData = array(
                                    'category_id'=>$this->input->post('category_id'),
                                    'data_name'=>$this->input->post('data_name'),
                                    'data_description'=>$this->input->post('data_description'),
                                    'data_support_version'=>$this->input->post('data_support_version'),
                                    'data_price'=>$this->input->post('data_price'),
                                    'data_size'=>$this->input->post('data_size'),
                                    'data_view'=>$this->input->post('data_view'),
                                    'data_download'=>$this->input->post('data_download'),
                                    'data_status'=>$this->input->post('data_status'),
                                );
                            }
                            $editDataEntry = $this->DataModel->editData('unique_id = '.$uniqueID, MCPE_MODS, $editData);
                            if($editDataEntry){
                                redirect('mods-view');
                            }
                        }
                    } else {
                        redirect('error');
                    }
                } else {
                    redirect('error');
                }
            } else {
                redirect('error');
            }
        } else {
            redirect('logout');
        }
    }

    //Addons Functions
    public function addonsNew(){
        $isLogin = $this->checkAuth();
        if($isLogin == "True"){
            if(!empty($this->session->userdata['member_role'])) { 
                if($this->session->userdata['member_role'] == "Administrator" or $this->session->userdata['member_role'] == "Developer") {
                    date_default_timezone_set("Asia/Kolkata");
                    $s3Client = $this->getconfig();
                    $uniqueCode = $this->uniqueKey();
                    $this->form_validation->set_rules('category_id', 'Text', 'required');
                    $this->form_validation->set_rules('data_name', 'Text', 'required');
                    $this->form_validation->set_rules('data_description', 'Text', 'required');
                    $this->form_validation->set_rules('data_support_version', 'Text', 'required');
                    $this->form_validation->set_rules('data_price', 'Text', 'required');
                    $this->form_validation->set_rules('data_size', 'Text', 'required');
                    $this->form_validation->set_rules('data_view', 'Text', 'required');
                    $this->form_validation->set_rules('data_download', 'Text', 'required');

                    if (empty($_FILES['data_image']['name'])){
                        $this->form_validation->set_rules('data_image', 'Data Image', 'required');
                    }
                    $this->form_validation->set_error_delimiters('','');

                    if (empty($_FILES['data_bundle']['name'])){
                        $this->form_validation->set_rules('data_bundle', 'Data Bundle', 'required');
                    }
                    $this->form_validation->set_error_delimiters('','');
                    
                    if ($this->form_validation->run() == FALSE){
                        $data['categoryAddonsData'] = $this->DataModel->viewData('category_id '.'DESC', null, MCPE_CATEGORY_ADDONS);
                        $data['error'] = "";
                        $this->load->view('header');
                        $this->load->view('android/addons_new',$data);
                        $this->load->view('footer');
                    }else{

                        $imageName = $_FILES['data_image']['name'];
                        $imageTemp = $_FILES['data_image']['tmp_name'];
                        $imagePath = DATA_IMAGE;
                        $imageResponse = $this->newBucketObject($imageName, $uniqueCode, $imageTemp, $imagePath);

                        $bundleName = $_FILES['data_bundle']['name'];
                        $bundleTemp = $_FILES['data_bundle']['tmp_name'];
                        $bundlePath = DATA_BUNDLE;
                        $bundleResponse = $this->newBucketObject($bundleName, $uniqueCode, $bundleTemp, $bundlePath);
                        
                        $newData = array(
                            'category_id'=>$this->input->post('category_id'),
                            'data_name'=>$this->input->post('data_name'),
                            'data_description'=>$this->input->post('data_description'),
                            'data_image'=>$imageResponse,
                            'data_bundle'=>$bundleResponse,
                            'data_support_version'=>$this->input->post('data_support_version'),
                            'data_price'=>$this->input->post('data_price'),
                            'data_size'=>$this->input->post('data_size'),
                            'data_view'=>$this->input->post('data_view'),
                            'data_download'=>$this->input->post('data_download'),
                            'is_copy'=>'False',
                            'data_status'=>$this->input->post('data_status'),
                        );
                        $newDataEntry = $this->DataModel->insertData(MCPE_ADDONS, $newData);
                        if($newDataEntry){
                          redirect('addons-view');  
                        }
                    }
                } else {
                    redirect('error');
                }
            } else {
                redirect('error');
            }
        } else {
            redirect('logout');
        }
    }

    public function addonsView(){
        $isLogin = $this->checkAuth();
        if($isLogin == "True"){
            if(!empty($this->session->userdata['member_role'])) { 
                if($this->session->userdata['member_role'] == "Administrator" or $this->session->userdata['member_role'] == "Developer") {

                    if(isset($_POST['reset'])){
                        $this->session->unset_userdata('session_addons_view');
                    }
                    if(isset($_POST['search'])){
                        $searchAddonsView = $this->input->post('search_addons_view');
                        $this->session->set_userdata('session_addons_view',$searchAddonsView);
                    }
                    $sessionAddonsView =  $this->session->userdata('session_addons_view');
                   
                    $data = array();
                    //get rows count
                    $conditions['search_addons_view'] = $sessionAddonsView;
                    $conditions['returnType'] = 'count';
                    $totalRec = $this->DataModel->viewAddonsData($conditions, MCPE_ADDONS);
                    
                    //pagination config
                    $config['base_url']    = site_url('addons-view');
                    $config['uri_segment'] = 2;
                    $config['total_rows']  = $totalRec;
                    $config['per_page']    = 20;
                    
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
                    $conditions['limit'] = 20;

                    $addons = $this->DataModel->viewAddonsData($conditions, MCPE_ADDONS);
                    $data['viewAddons'] = array();
                    
                    if (is_array($addons) || is_object($addons)){
                        foreach($addons as $Row){
                            $dataArray = array();
                            $dataArray['unique_id'] = $Row['unique_id'];
                            $dataArray['category_id'] = $Row['category_id'];
                            $dataArray['data_name'] = $Row['data_name'];
                            $dataArray['data_description'] = $Row['data_description'];
                            $dataArray['data_image'] = $Row['data_image'];
                            $dataArray['data_bundle'] = $Row['data_bundle'];
                            $dataArray['data_support_version'] = $Row['data_support_version'];
                            $dataArray['data_price'] = $Row['data_price'];
                            $dataArray['data_size'] = $Row['data_size'];
                            $dataArray['data_view'] = $Row['data_view'];
                            $dataArray['data_download'] = $Row['data_download'];
                            $dataArray['is_copy'] = $Row['is_copy'];
                            $dataArray['data_status'] = $Row['data_status'];
                            $dataArray['category_name'] = $this->DataModel->getCategoryNameData('category_id = '.$dataArray['category_id'], MCPE_CATEGORY_ADDONS);
                            array_push($data['viewAddons'], $dataArray);
                        }
                    }

                    if($data['viewAddons'] != null){
                        $this->load->view('header');
                        $this->load->view('android/addons_view',$data);
                        $this->load->view('footer');
                    } else {
                        $this->session->unset_userdata('session_addons_view'); 
                        $data['msg'] = array(
                            'data_title'=>"Addons Database is Empty",
                            'data_description'=>"Please add addons & redirect addons from the below button.",
                            'button_link'=>"addons-new",
                            'button_text'=>"New Addons",
                            'button_link1'=>'addons-view',
                            'button_text1'=>"View Addons",
                        );
                        $this->load->view('header');
                        $this->load->view('nodata', $data);
                        $this->load->view('footer');
                    }
                } else {
                    redirect('error');
                }
            } else {
                redirect('error');
            }
        } else {
            redirect('logout');
        }
    }

    public function addonsEdit($uniqueID = 0){
        $isLogin = $this->checkAuth();
        if($isLogin == "True"){
            if(!empty($this->session->userdata['member_role'])) { 
                if($this->session->userdata['member_role'] == "Administrator" or $this->session->userdata['member_role'] == "Developer") {
                    $checkEncryption = $this->DataModel->checkEncrypt($uniqueID,ENCRYPT_TABLE);
                    if($checkEncryption){
                        $uniqueID = $checkEncryption->enc_number;
                    }
                   
                    if(ctype_digit($uniqueID)){
                        $data['addonsData'] = $this->DataModel->getData('unique_id = '.$uniqueID, MCPE_ADDONS);
                        $data['viewCategoryAddons'] = $this->DataModel->viewData('category_id '.'DESC', null, MCPE_CATEGORY_ADDONS);
                        $categoryID = $data['addonsData']->category_id;
                        $data['categoryAddonsData'] = $this->DataModel->getData('category_id = '.$categoryID, MCPE_CATEGORY_ADDONS);
                        if(!empty($data['addonsData'])){
                            $this->load->view('header');
                            $this->load->view('android/addons_edit',$data);
                            $this->load->view('footer');
                        } else {
                            redirect('error');
                        }
                        if($this->input->post('submit')){
                            date_default_timezone_set("Asia/Kolkata");
                            $s3Client = $this->getconfig();
                            $uniqueCode = $this->uniqueKey();
                            
                            $imageKey = $data['addonsData']->data_image;
                            $newImageKey = basename($imageKey);
                    
                            $bundleKey = $data['addonsData']->data_bundle;
                            $newBundleKey = basename($bundleKey);
            
                            if (!empty($_FILES['data_image']['name']) and !empty($_FILES['data_bundle']['name'])){
                                $deleteImage = $s3Client->deleteObject([
                                        'Bucket' => BUCKET_NAME,
                                        'Key'    => DATA_IMAGE.$newImageKey,
                                    ]);
                                $deleteBundle = $s3Client->deleteObject([
                                        'Bucket' => BUCKET_NAME,
                                        'Key'    => DATA_BUNDLE.$newBundleKey,
                                    ]);
                                
                                $imageName = $_FILES['data_image']['name'];
                                $imageTemp = $_FILES['data_image']['tmp_name'];
                                $imagePath = DATA_IMAGE;
                                $imageResponse = $this->newBucketObject($imageName, $uniqueCode, $imageTemp, $imagePath);

                                $bundleName = $_FILES['data_bundle']['name'];
                                $bundleTemp = $_FILES['data_bundle']['tmp_name'];
                                $bundlePath = DATA_BUNDLE;
                                $bundleResponse = $this->newBucketObject($bundleName, $uniqueCode, $bundleTemp, $bundlePath);

                                $editData = array(
                                    'category_id'=>$this->input->post('category_id'),
                                    'data_name'=>$this->input->post('data_name'),
                                    'data_description'=>$this->input->post('data_description'),
                                    'data_image'=>$imageResponse,
                                    'data_bundle'=>$bundleResponse,
                                    'data_support_version'=>$this->input->post('data_support_version'),
                                    'data_price'=>$this->input->post('data_price'),
                                    'data_size'=>$this->input->post('data_size'),
                                    'data_view'=>$this->input->post('data_view'),
                                    'data_download'=>$this->input->post('data_download'),
                                    'data_status'=>$this->input->post('data_status'),
                                );
                            } else if(!empty($_FILES['data_image']['name'])) {
                                $deleteImage = $s3Client->deleteObject([
                                            'Bucket' => BUCKET_NAME,
                                            'Key'    => DATA_IMAGE.$newImageKey,
                                        ]);
                    
                                $imageName = $_FILES['data_image']['name'];
                                $imageTemp = $_FILES['data_image']['tmp_name'];
                                $imagePath = DATA_IMAGE;
                                $imageResponse = $this->newBucketObject($imageName, $uniqueCode, $imageTemp, $imagePath);
                    
                                $editData = array(
                                    'category_id'=>$this->input->post('category_id'),
                                    'data_name'=>$this->input->post('data_name'),
                                    'data_description'=>$this->input->post('data_description'),
                                    'data_image'=>$imageResponse,
                                    'data_support_version'=>$this->input->post('data_support_version'),
                                    'data_price'=>$this->input->post('data_price'),
                                    'data_size'=>$this->input->post('data_size'),
                                    'data_view'=>$this->input->post('data_view'),
                                    'data_download'=>$this->input->post('data_download'),
                                    'data_status'=>$this->input->post('data_status'),
                                );
                            } else if(!empty($_FILES['data_bundle']['name'])) {
                                $deleteBundle = $s3Client->deleteObject([
                                        'Bucket' => BUCKET_NAME,
                                        'Key'    => DATA_BUNDLE.$newBundleKey,
                                    ]);
                                
                                $bundleName = $_FILES['data_bundle']['name'];
                                $bundleTemp = $_FILES['data_bundle']['tmp_name'];
                                $bundlePath = DATA_BUNDLE;
                                $bundleResponse = $this->newBucketObject($bundleName, $uniqueCode, $bundleTemp, $bundlePath);
                                
                                $editData = array(
                                    'category_id'=>$this->input->post('category_id'),
                                    'data_name'=>$this->input->post('data_name'),
                                    'data_description'=>$this->input->post('data_description'),
                                    'data_bundle'=>$bundleResponse,
                                    'data_support_version'=>$this->input->post('data_support_version'),
                                    'data_price'=>$this->input->post('data_price'),
                                    'data_size'=>$this->input->post('data_size'),
                                    'data_view'=>$this->input->post('data_view'),
                                    'data_download'=>$this->input->post('data_download'),
                                    'data_status'=>$this->input->post('data_status'),
                                );
                            } else {
                                $editData = array(
                                    'category_id'=>$this->input->post('category_id'),
                                    'data_name'=>$this->input->post('data_name'),
                                    'data_description'=>$this->input->post('data_description'),
                                    'data_support_version'=>$this->input->post('data_support_version'),
                                    'data_price'=>$this->input->post('data_price'),
                                    'data_size'=>$this->input->post('data_size'),
                                    'data_view'=>$this->input->post('data_view'),
                                    'data_download'=>$this->input->post('data_download'),
                                    'data_status'=>$this->input->post('data_status'),
                                );
                            }
                            $editDataEntry = $this->DataModel->editData('unique_id = '.$uniqueID, MCPE_ADDONS, $editData);
                            if($editDataEntry){
                                redirect('addons-view');
                            }
                        }
                    } else {
                        redirect('error');
                    }
                } else {
                    redirect('error');
                }
            } else {
                redirect('error');
            }
        } else {
            redirect('logout');
        }
    }

    //Maps Functions
    public function mapsNew(){
        $isLogin = $this->checkAuth();
        if($isLogin == "True"){
            if(!empty($this->session->userdata['member_role'])) { 
                if($this->session->userdata['member_role'] == "Administrator" or $this->session->userdata['member_role'] == "Developer") {
                    date_default_timezone_set("Asia/Kolkata");
                    $s3Client = $this->getconfig();
                    $uniqueCode = $this->uniqueKey();
                    
                    $this->form_validation->set_rules('category_id', 'Text', 'required');
                    $this->form_validation->set_rules('data_name', 'Text', 'required');
                    $this->form_validation->set_rules('data_description', 'Text', 'required');
                    $this->form_validation->set_rules('data_support_version', 'Text', 'required');
                    $this->form_validation->set_rules('data_price', 'Text', 'required');
                    $this->form_validation->set_rules('data_size', 'Text', 'required');
                    $this->form_validation->set_rules('data_view', 'Text', 'required');
                    $this->form_validation->set_rules('data_download', 'Text', 'required');

                    if (empty($_FILES['data_image']['name'])){
                        $this->form_validation->set_rules('data_image', 'Document', 'required');
                    }
                    if (empty($_FILES['data_bundle']['name'])){
                        $this->form_validation->set_rules('data_bundle', 'Document', 'required');
                    }
                    $this->form_validation->set_error_delimiters('','');
                    
                    if ($this->form_validation->run() == FALSE){
                        $data['error'] = "";
                        $data['categoryMapsData'] = $this->DataModel->viewData('category_id '.'DESC', null, MCPE_CATEGORY_MAPS);
                        $this->load->view('header');
                        $this->load->view('android/maps_new',$data);
                        $this->load->view('footer');
                    } else {
                        $imageName = $_FILES['data_image']['name'];
                        $imageTemp = $_FILES['data_image']['tmp_name'];
                        $imagePath = DATA_IMAGE;
                        $imageResponse = $this->newBucketObject($imageName, $uniqueCode, $imageTemp, $imagePath);

                        $bundleName = $_FILES['data_bundle']['name'];
                        $bundleTemp = $_FILES['data_bundle']['tmp_name'];
                        $bundlePath = DATA_BUNDLE;
                        $bundleResponse = $this->newBucketObject($bundleName, $uniqueCode, $bundleTemp, $bundlePath);
                        
                        $newData = array(
                            'category_id'=>$this->input->post('category_id'),
                            'data_name'=>$this->input->post('data_name'),
                            'data_description'=>$this->input->post('data_description'),
                            'data_image'=>$imageResponse,
                            'data_bundle'=>$bundleResponse,
                            'data_support_version'=>$this->input->post('data_support_version'),
                            'data_price'=>$this->input->post('data_price'),
                            'data_size'=>$this->input->post('data_size'),
                            'data_view'=>$this->input->post('data_view'),
                            'data_download'=>$this->input->post('data_download'),
                            'is_copy'=>'False',
                            'data_status'=>$this->input->post('data_status'),
                        );
                        $newDataEntry = $this->DataModel->insertData(MCPE_MAPS, $newData);
                        if($newDataEntry){
                          redirect('maps-view');  
                        }
                    }
                } else {
                    redirect('error');
                }
            } else {
                redirect('error');
            }
        } else {
            redirect('logout');
        }
    }

    public function mapsView(){
        $isLogin = $this->checkAuth();
        if($isLogin == "True"){
            if(!empty($this->session->userdata['member_role'])) { 
                if($this->session->userdata['member_role'] == "Administrator" or $this->session->userdata['member_role'] == "Developer") {

                    if(isset($_POST['reset'])){
                        $this->session->unset_userdata('session_maps_view');
                    }
                    if(isset($_POST['search'])){
                        $searchMapsView = $this->input->post('search_maps_view');
                        $this->session->set_userdata('session_maps_view',$searchMapsView);
                    }
                    $sessionMapsView =  $this->session->userdata('session_maps_view');
                   
                    $data = array();
                    //get rows count
                    $conditions['search_maps_view'] = $sessionMapsView;
                    $conditions['returnType'] = 'count';
                    $totalRec = $this->DataModel->viewMapsData($conditions, MCPE_MAPS);
                    
                    //pagination config
                    $config['base_url']    = site_url('maps-view');
                    $config['uri_segment'] = 2;
                    $config['total_rows']  = $totalRec;
                    $config['per_page']    = 20;
                    
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
                    $conditions['limit'] = 20;

                    $maps = $this->DataModel->viewMapsData($conditions, MCPE_MAPS);
                    $data['viewMaps'] = array();
                    
                    if (is_array($maps) || is_object($maps)){
                        foreach($maps as $Row){
                            $dataArray = array();
                            $dataArray['unique_id'] = $Row['unique_id'];
                            $dataArray['category_id'] = $Row['category_id'];
                            $dataArray['data_name'] = $Row['data_name'];
                            $dataArray['data_description'] = $Row['data_description'];
                            $dataArray['data_image'] = $Row['data_image'];
                            $dataArray['data_bundle'] = $Row['data_bundle'];
                            $dataArray['data_support_version'] = $Row['data_support_version'];
                            $dataArray['data_price'] = $Row['data_price'];
                            $dataArray['data_size'] = $Row['data_size'];
                            $dataArray['data_view'] = $Row['data_view'];
                            $dataArray['data_download'] = $Row['data_download'];
                            $dataArray['is_copy'] = $Row['is_copy'];
                            $dataArray['data_status'] = $Row['data_status'];
                            $dataArray['category_name'] = $this->DataModel->getCategoryNameData('category_id = '.$dataArray['category_id'], MCPE_CATEGORY_MAPS);
                            array_push($data['viewMaps'], $dataArray);
                        }
                    }

                    if($data['viewMaps'] != null){
                        $this->load->view('header');
                        $this->load->view('android/maps_view',$data);
                        $this->load->view('footer');
                    } else {
                        $this->session->unset_userdata('session_maps_view');   
                        $data['msg'] = array(
                            'data_title'=>"Maps Database is Empty",
                            'data_description'=>"Please add maps & redirect maps from the below button.",
                            'button_link'=>"maps-new",
                            'button_text'=>"New Maps",
                            'button_link1'=>'maps-view',
                            'button_text1'=>"View Maps",
                        );
                        $this->load->view('header');
                        $this->load->view('nodata', $data);
                        $this->load->view('footer');
                    }
                } else {
                    redirect('error');
                }
            } else {
                redirect('error');
            }
        } else {
            redirect('logout');
        }
    }

    public function mapsEdit($uniqueID = 0){
        $isLogin = $this->checkAuth();
        if($isLogin == "True"){
            if(!empty($this->session->userdata['member_role'])) { 
                if($this->session->userdata['member_role'] == "Administrator" or $this->session->userdata['member_role'] == "Developer") {
                    $checkEncryption = $this->DataModel->checkEncrypt($uniqueID,ENCRYPT_TABLE);
                    if($checkEncryption){
                        $uniqueID = $checkEncryption->enc_number;
                    }
                   
                    if(ctype_digit($uniqueID)){
                        $data['mapsData'] = $this->DataModel->getData('unique_id = '.$uniqueID, MCPE_MAPS);
                        $data['viewCategoryMaps'] = $this->DataModel->viewData('category_id '.'DESC', null, MCPE_CATEGORY_MAPS);
                        $categoryID = $data['mapsData']->category_id;
                        $data['categoryMapsData'] = $this->DataModel->getData('category_id = '.$categoryID, MCPE_CATEGORY_MAPS);
                        if(!empty($data['mapsData'])){
                            $this->load->view('header');
                            $this->load->view('android/maps_edit',$data);
                            $this->load->view('footer');
                        } else {
                            redirect('error');
                        }
                        if($this->input->post('submit')){
                            date_default_timezone_set("Asia/Kolkata");
                            $s3Client = $this->getconfig();
                            $uniqueCode = $this->uniqueKey();
                            
                            $imageKey = $data['mapsData']->data_image;
                            $newImageKey = basename($imageKey);
                    
                            $bundleKey = $data['mapsData']->data_bundle;
                            $newBundleKey = basename($bundleKey);

                            if (!empty($_FILES['data_image']['name']) and !empty($_FILES['data_bundle']['name'])){
                                $deleteImage = $s3Client->deleteObject([
                                        'Bucket' => BUCKET_NAME,
                                        'Key'    => DATA_IMAGE.$newImageKey,
                                    ]);
                                $deleteBundle = $s3Client->deleteObject([
                                        'Bucket' => BUCKET_NAME,
                                        'Key'    => DATA_BUNDLE.$newBundleKey,
                                    ]);
                                
                                $imageName = $_FILES['data_image']['name'];
                                $imageTemp = $_FILES['data_image']['tmp_name'];
                                $imagePath = DATA_IMAGE;
                                $imageResponse = $this->newBucketObject($imageName, $uniqueCode, $imageTemp, $imagePath);

                                $bundleName = $_FILES['data_bundle']['name'];
                                $bundleTemp = $_FILES['data_bundle']['tmp_name'];
                                $bundlePath = DATA_BUNDLE;
                                $bundleResponse = $this->newBucketObject($bundleName, $uniqueCode, $bundleTemp, $bundlePath);

                                $editData = array(
                                    'category_id'=>$this->input->post('category_id'),
                                    'data_name'=>$this->input->post('data_name'),
                                    'data_description'=>$this->input->post('data_description'),
                                    'data_image'=>$imageResponse,
                                    'data_bundle'=>$bundleResponse,
                                    'data_support_version'=>$this->input->post('data_support_version'),
                                    'data_price'=>$this->input->post('data_price'),
                                    'data_size'=>$this->input->post('data_size'),
                                    'data_view'=>$this->input->post('data_view'),
                                    'data_download'=>$this->input->post('data_download'),
                                    'data_status'=>$this->input->post('data_status'),
                                );
                            } else if(!empty($_FILES['data_image']['name'])) {
                                $deleteImage = $s3Client->deleteObject([
                                            'Bucket' => BUCKET_NAME,
                                            'Key'    => DATA_IMAGE.$newImageKey,
                                        ]);
                    
                                $imageName = $_FILES['data_image']['name'];
                                $imageTemp = $_FILES['data_image']['tmp_name'];
                                $imagePath = DATA_IMAGE;
                                $imageResponse = $this->newBucketObject($imageName, $uniqueCode, $imageTemp, $imagePath);
                    
                                $editData = array(
                                    'category_id'=>$this->input->post('category_id'),
                                    'data_name'=>$this->input->post('data_name'),
                                    'data_description'=>$this->input->post('data_description'),
                                    'data_image'=>$imageResponse,
                                    'data_support_version'=>$this->input->post('data_support_version'),
                                    'data_price'=>$this->input->post('data_price'),
                                    'data_size'=>$this->input->post('data_size'),
                                    'data_view'=>$this->input->post('data_view'),
                                    'data_download'=>$this->input->post('data_download'),
                                    'data_status'=>$this->input->post('data_status'),
                                );
                            } else if(!empty($_FILES['data_bundle']['name'])) {
                                $deleteBundle = $s3Client->deleteObject([
                                        'Bucket' => BUCKET_NAME,
                                        'Key'    => DATA_BUNDLE.$newBundleKey,
                                    ]);
                                
                                $bundleName = $_FILES['data_bundle']['name'];
                                $bundleTemp = $_FILES['data_bundle']['tmp_name'];
                                $bundlePath = DATA_BUNDLE;
                                $bundleResponse = $this->newBucketObject($bundleName, $uniqueCode, $bundleTemp, $bundlePath);
                                
                                $editData = array(
                                    'category_id'=>$this->input->post('category_id'),
                                    'data_name'=>$this->input->post('data_name'),
                                    'data_description'=>$this->input->post('data_description'),
                                    'data_bundle'=>$bundleResponse,
                                    'data_support_version'=>$this->input->post('data_support_version'),
                                    'data_price'=>$this->input->post('data_price'),
                                    'data_size'=>$this->input->post('data_size'),
                                    'data_view'=>$this->input->post('data_view'),
                                    'data_download'=>$this->input->post('data_download'),
                                    'data_status'=>$this->input->post('data_status'),
                                );
                            } else {
                                $editData = array(
                                    'category_id'=>$this->input->post('category_id'),
                                    'data_name'=>$this->input->post('data_name'),
                                    'data_description'=>$this->input->post('data_description'),
                                    'data_support_version'=>$this->input->post('data_support_version'),
                                    'data_price'=>$this->input->post('data_price'),
                                    'data_size'=>$this->input->post('data_size'),
                                    'data_view'=>$this->input->post('data_view'),
                                    'data_download'=>$this->input->post('data_download'),
                                    'data_status'=>$this->input->post('data_status'),
                                );
                            }
                            $editDataEntry = $this->DataModel->editData('unique_id = '.$uniqueID, MCPE_MAPS, $editData);
                            if($editDataEntry){
                                redirect('maps-view');
                            }
                        }
                    } else {
                        redirect('error');
                    }
                } else {
                    redirect('error');
                }
            } else {
                redirect('error');
            }
        } else {
            redirect('logout');
        }
    }

    //Seeds Functions
    public function seedsNew(){
        $isLogin = $this->checkAuth();
        if($isLogin == "True"){
            if(!empty($this->session->userdata['member_role'])) { 
                if($this->session->userdata['member_role'] == "Administrator" or $this->session->userdata['member_role'] == "Developer") {
                    date_default_timezone_set("Asia/Kolkata");
                    $s3Client = $this->getconfig();
                    $uniqueCode = $this->uniqueKey();
                    $this->form_validation->set_rules('category_id', 'Text', 'required');
                    $this->form_validation->set_rules('data_name', 'Text', 'required');
                    $this->form_validation->set_rules('data_description', 'Text', 'required');
                    $this->form_validation->set_rules('data_support_version', 'Text', 'required');
                    $this->form_validation->set_rules('data_price', 'Text', 'required');
                    $this->form_validation->set_rules('data_size', 'Text', 'required');
                    $this->form_validation->set_rules('data_view', 'Text', 'required');
                    $this->form_validation->set_rules('data_download', 'Text', 'required');

                    if (empty($_FILES['data_image']['name'])){
                        $this->form_validation->set_rules('data_image', 'Data Image', 'required');
                    }
                    $this->form_validation->set_error_delimiters('','');

                    if (empty($_FILES['data_bundle']['name'])){
                        $this->form_validation->set_rules('data_bundle', 'Data Bundle', 'required');
                    }
                    $this->form_validation->set_error_delimiters('','');
                    
                    if ($this->form_validation->run() == FALSE){
                        $data['categorySeedsData'] = $this->DataModel->viewData('category_id '.'DESC', null, MCPE_CATEGORY_SEEDS);
                        $data['error'] = "";
                        $this->load->view('header');
                        $this->load->view('android/seeds_new',$data);
                        $this->load->view('footer');
                    }else{

                        $imageName = $_FILES['data_image']['name'];
                        $imageTemp = $_FILES['data_image']['tmp_name'];
                        $imagePath = DATA_IMAGE;
                        $imageResponse = $this->newBucketObject($imageName, $uniqueCode, $imageTemp, $imagePath);

                        $bundleName = $_FILES['data_bundle']['name'];
                        $bundleTemp = $_FILES['data_bundle']['tmp_name'];
                        $bundlePath = DATA_BUNDLE;
                        $bundleResponse = $this->newBucketObject($bundleName, $uniqueCode, $bundleTemp, $bundlePath);
                        
                        $newData = array(
                            'category_id'=>$this->input->post('category_id'),
                            'data_name'=>$this->input->post('data_name'),
                            'data_description'=>$this->input->post('data_description'),
                            'data_image'=>$imageResponse,
                            'data_bundle'=>$bundleResponse,
                            'data_support_version'=>$this->input->post('data_support_version'),
                            'data_price'=>$this->input->post('data_price'),
                            'data_size'=>$this->input->post('data_size'),
                            'data_view'=>$this->input->post('data_view'),
                            'data_download'=>$this->input->post('data_download'),
                            'is_copy'=>'False',
                            'data_status'=>$this->input->post('data_status'),
                        );
                        $newDataEntry = $this->DataModel->insertData(MCPE_SEEDS, $newData);
                        if($newDataEntry){
                          redirect('seeds-view');  
                        }
                    }
                } else {
                    redirect('error');
                }
            } else {
                redirect('error');
            }
        } else {
            redirect('logout');
        }
    }

    public function seedsView(){
        $isLogin = $this->checkAuth();
        if($isLogin == "True"){
            if(!empty($this->session->userdata['member_role'])) { 
                if($this->session->userdata['member_role'] == "Administrator" or $this->session->userdata['member_role'] == "Developer") {

                    if(isset($_POST['reset'])){
                        $this->session->unset_userdata('session_seeds_view');
                    }
                    if(isset($_POST['search'])){
                        $searchSeedsView = $this->input->post('search_seeds_view');
                        $this->session->set_userdata('session_seeds_view',$searchSeedsView);
                    }
                    $sessionSeedsView =  $this->session->userdata('session_seeds_view');
                   
                    $data = array();
                    //get rows count
                    $conditions['search_seeds_view'] = $sessionSeedsView;
                    $conditions['returnType'] = 'count';
                    $totalRec = $this->DataModel->viewSeedsData($conditions, MCPE_SEEDS);
                    
                    //pagination config
                    $config['base_url']    = site_url('seeds-view');
                    $config['uri_segment'] = 2;
                    $config['total_rows']  = $totalRec;
                    $config['per_page']    = 20;
                    
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
                    $conditions['limit'] = 20;

                    $seeds = $this->DataModel->viewSeedsData($conditions, MCPE_SEEDS);
                    $data['viewSeeds'] = array();
                    
                    if (is_array($seeds) || is_object($seeds)){
                        foreach($seeds as $Row){
                            $dataArray = array();
                            $dataArray['unique_id'] = $Row['unique_id'];
                            $dataArray['category_id'] = $Row['category_id'];
                            $dataArray['data_name'] = $Row['data_name'];
                            $dataArray['data_description'] = $Row['data_description'];
                            $dataArray['data_image'] = $Row['data_image'];
                            $dataArray['data_bundle'] = $Row['data_bundle'];
                            $dataArray['data_support_version'] = $Row['data_support_version'];
                            $dataArray['data_price'] = $Row['data_price'];
                            $dataArray['data_size'] = $Row['data_size'];
                            $dataArray['data_view'] = $Row['data_view'];
                            $dataArray['data_download'] = $Row['data_download'];
                            $dataArray['is_copy'] = $Row['is_copy'];
                            $dataArray['data_status'] = $Row['data_status'];
                            $dataArray['category_name'] = $this->DataModel->getCategoryNameData('category_id = '.$dataArray['category_id'], MCPE_CATEGORY_SEEDS);
                            array_push($data['viewSeeds'], $dataArray);
                        }
                    }

                    if($data['viewSeeds'] != null){
                        $this->load->view('header');
                        $this->load->view('android/seeds_view',$data);
                        $this->load->view('footer');
                    } else {
                        $this->session->unset_userdata('session_seeds_view');   
                        $data['msg'] = array(
                            'data_title'=>"Seeds Database is Empty",
                            'data_description'=>"Please add seeds & redirect seeds from the below button.",
                            'button_link'=>"seeds-new",
                            'button_text'=>"New Seeds",
                            'button_link1'=>'seeds-view',
                            'button_text1'=>"View Seeds",
                        );
                        $this->load->view('header');
                        $this->load->view('nodata', $data);
                        $this->load->view('footer');
                    }
                } else {
                    redirect('error');
                }
            } else {
                redirect('error');
            }
        } else {
            redirect('logout');
        }
    }

    public function seedsEdit($uniqueID = 0){
        $isLogin = $this->checkAuth();
        if($isLogin == "True"){
            if(!empty($this->session->userdata['member_role'])) { 
                if($this->session->userdata['member_role'] == "Administrator" or $this->session->userdata['member_role'] == "Developer") {
                    $checkEncryption = $this->DataModel->checkEncrypt($uniqueID,ENCRYPT_TABLE);
                    if($checkEncryption){
                        $uniqueID = $checkEncryption->enc_number;
                    }
                   
                    if(ctype_digit($uniqueID)){
                        $data['seedsData'] = $this->DataModel->getData('unique_id = '.$uniqueID, MCPE_SEEDS);
                        $data['viewCategorySeeds'] = $this->DataModel->viewData('category_id '.'DESC', null, MCPE_CATEGORY_SEEDS);
                        $categoryID = $data['seedsData']->category_id;
                        $data['categorySeedsData'] = $this->DataModel->getData('category_id = '.$categoryID, MCPE_CATEGORY_SEEDS);
                        if(!empty($data['seedsData'])){
                            $this->load->view('header');
                            $this->load->view('android/seeds_edit',$data);
                            $this->load->view('footer');
                        } else {
                            redirect('error');
                        }
                        if($this->input->post('submit')){
                            date_default_timezone_set("Asia/Kolkata");
                            $s3Client = $this->getconfig();
                            $uniqueCode = $this->uniqueKey();
                            
                            $imageKey = $data['seedsData']->data_image;
                            $newImageKey = basename($imageKey);
                    
                            $bundleKey = $data['seedsData']->data_bundle;
                            $newBundleKey = basename($bundleKey);

                            if (!empty($_FILES['data_image']['name']) and !empty($_FILES['data_bundle']['name'])){
                                $deleteImage = $s3Client->deleteObject([
                                        'Bucket' => BUCKET_NAME,
                                        'Key'    => DATA_IMAGE.$newImageKey,
                                    ]);
                                $deleteBundle = $s3Client->deleteObject([
                                        'Bucket' => BUCKET_NAME,
                                        'Key'    => DATA_BUNDLE.$newBundleKey,
                                    ]);
                                
                                $imageName = $_FILES['data_image']['name'];
                                $imageTemp = $_FILES['data_image']['tmp_name'];
                                $imagePath = DATA_IMAGE;
                                $imageResponse = $this->newBucketObject($imageName, $uniqueCode, $imageTemp, $imagePath);

                                $bundleName = $_FILES['data_bundle']['name'];
                                $bundleTemp = $_FILES['data_bundle']['tmp_name'];
                                $bundlePath = DATA_BUNDLE;
                                $bundleResponse = $this->newBucketObject($bundleName, $uniqueCode, $bundleTemp, $bundlePath);

                                $editData = array(
                                    'category_id'=>$this->input->post('category_id'),
                                    'data_name'=>$this->input->post('data_name'),
                                    'data_description'=>$this->input->post('data_description'),
                                    'data_image'=>$imageResponse,
                                    'data_bundle'=>$bundleResponse,
                                    'data_support_version'=>$this->input->post('data_support_version'),
                                    'data_price'=>$this->input->post('data_price'),
                                    'data_size'=>$this->input->post('data_size'),
                                    'data_view'=>$this->input->post('data_view'),
                                    'data_download'=>$this->input->post('data_download'),
                                    'data_status'=>$this->input->post('data_status'),
                                );
                            } else if(!empty($_FILES['data_image']['name'])) {
                                $deleteImage = $s3Client->deleteObject([
                                            'Bucket' => BUCKET_NAME,
                                            'Key'    => DATA_IMAGE.$newImageKey,
                                        ]);
                    
                                $imageName = $_FILES['data_image']['name'];
                                $imageTemp = $_FILES['data_image']['tmp_name'];
                                $imagePath = DATA_IMAGE;
                                $imageResponse = $this->newBucketObject($imageName, $uniqueCode, $imageTemp, $imagePath);
                    
                                $editData = array(
                                    'category_id'=>$this->input->post('category_id'),
                                    'data_name'=>$this->input->post('data_name'),
                                    'data_description'=>$this->input->post('data_description'),
                                    'data_image'=>$imageResponse,
                                    'data_support_version'=>$this->input->post('data_support_version'),
                                    'data_price'=>$this->input->post('data_price'),
                                    'data_size'=>$this->input->post('data_size'),
                                    'data_view'=>$this->input->post('data_view'),
                                    'data_download'=>$this->input->post('data_download'),
                                    'data_status'=>$this->input->post('data_status'),
                                );
                            } else if(!empty($_FILES['data_bundle']['name'])) {
                                $deleteBundle = $s3Client->deleteObject([
                                        'Bucket' => BUCKET_NAME,
                                        'Key'    => DATA_BUNDLE.$newBundleKey,
                                    ]);
                                
                                $bundleName = $_FILES['data_bundle']['name'];
                                $bundleTemp = $_FILES['data_bundle']['tmp_name'];
                                $bundlePath = DATA_BUNDLE;
                                $bundleResponse = $this->newBucketObject($bundleName, $uniqueCode, $bundleTemp, $bundlePath);
                                
                                $editData = array(
                                    'category_id'=>$this->input->post('category_id'),
                                    'data_name'=>$this->input->post('data_name'),
                                    'data_description'=>$this->input->post('data_description'),
                                    'data_bundle'=>$bundleResponse,
                                    'data_support_version'=>$this->input->post('data_support_version'),
                                    'data_price'=>$this->input->post('data_price'),
                                    'data_size'=>$this->input->post('data_size'),
                                    'data_view'=>$this->input->post('data_view'),
                                    'data_download'=>$this->input->post('data_download'),
                                    'data_status'=>$this->input->post('data_status'),
                                );
                            } else {
                                $editData = array(
                                    'category_id'=>$this->input->post('category_id'),
                                    'data_name'=>$this->input->post('data_name'),
                                    'data_description'=>$this->input->post('data_description'),
                                    'data_support_version'=>$this->input->post('data_support_version'),
                                    'data_price'=>$this->input->post('data_price'),
                                    'data_size'=>$this->input->post('data_size'),
                                    'data_view'=>$this->input->post('data_view'),
                                    'data_download'=>$this->input->post('data_download'),
                                    'data_status'=>$this->input->post('data_status'),
                                );
                            }
                            $editDataEntry = $this->DataModel->editData('unique_id = '.$uniqueID, MCPE_SEEDS, $editData);
                            if($editDataEntry){
                                redirect('seeds-view');
                            }
                        }
                    } else {
                        redirect('error');
                    }
                } else {
                    redirect('error');
                }
            } else {
                redirect('error');
            }
        } else {
            redirect('logout');
        }
    }

    //Textures Functions
    public function texturesNew(){
        $isLogin = $this->checkAuth();
        if($isLogin == "True"){
            if(!empty($this->session->userdata['member_role'])) { 
                if($this->session->userdata['member_role'] == "Administrator" or $this->session->userdata['member_role'] == "Developer") {
                    date_default_timezone_set("Asia/Kolkata");
                    $s3Client = $this->getconfig();
                    $uniqueCode = $this->uniqueKey();
                    $this->form_validation->set_rules('category_id', 'Text', 'required');
                    $this->form_validation->set_rules('data_name', 'Text', 'required');
                    $this->form_validation->set_rules('data_description', 'Text', 'required');
                    $this->form_validation->set_rules('data_support_version', 'Text', 'required');
                    $this->form_validation->set_rules('data_price', 'Text', 'required');
                    $this->form_validation->set_rules('data_size', 'Text', 'required');
                    $this->form_validation->set_rules('data_view', 'Text', 'required');
                    $this->form_validation->set_rules('data_download', 'Text', 'required');

                    if (empty($_FILES['data_image']['name'])){
                        $this->form_validation->set_rules('data_image', 'Data Image', 'required');
                    }
                    $this->form_validation->set_error_delimiters('','');

                    if (empty($_FILES['data_bundle']['name'])){
                        $this->form_validation->set_rules('data_bundle', 'Data Bundle', 'required');
                    }
                    $this->form_validation->set_error_delimiters('','');
                    
                    if ($this->form_validation->run() == FALSE){
                        $data['categoryTexturesData'] = $this->DataModel->viewData('category_id '.'DESC', null, MCPE_CATEGORY_TEXTURES);
                        $data['error'] = "";
                        $this->load->view('header');
                        $this->load->view('android/textures_new',$data);
                        $this->load->view('footer');
                    }else{

                        $imageName = $_FILES['data_image']['name'];
                        $imageTemp = $_FILES['data_image']['tmp_name'];
                        $imagePath = DATA_IMAGE;
                        $imageResponse = $this->newBucketObject($imageName, $uniqueCode, $imageTemp, $imagePath);

                        $bundleName = $_FILES['data_bundle']['name'];
                        $bundleTemp = $_FILES['data_bundle']['tmp_name'];
                        $bundlePath = DATA_BUNDLE;
                        $bundleResponse = $this->newBucketObject($bundleName, $uniqueCode, $bundleTemp, $bundlePath);
                        
                        $newData = array(
                            'category_id'=>$this->input->post('category_id'),
                            'data_name'=>$this->input->post('data_name'),
                            'data_description'=>$this->input->post('data_description'),
                            'data_image'=>$imageResponse,
                            'data_bundle'=>$bundleResponse,
                            'data_support_version'=>$this->input->post('data_support_version'),
                            'data_price'=>$this->input->post('data_price'),
                            'data_size'=>$this->input->post('data_size'),
                            'data_view'=>$this->input->post('data_view'),
                            'data_download'=>$this->input->post('data_download'),
                            'is_copy'=>'False',
                            'data_status'=>$this->input->post('data_status'),
                        );
                        $newDataEntry = $this->DataModel->insertData(MCPE_TEXTURES, $newData);
                        if($newDataEntry){
                          redirect('textures-view');  
                        }
                    }
                } else {
                    redirect('error');
                }
            } else {
                redirect('error');
            }
        } else {
            redirect('logout');
        }
    }

    public function texturesView(){
        $isLogin = $this->checkAuth();
        if($isLogin == "True"){
            if(!empty($this->session->userdata['member_role'])) { 
                if($this->session->userdata['member_role'] == "Administrator" or $this->session->userdata['member_role'] == "Developer") {

                    if(isset($_POST['reset'])){
                        $this->session->unset_userdata('session_textures_view');
                    }
                    if(isset($_POST['search'])){
                        $searchTexturesView = $this->input->post('search_textures_view');
                        $this->session->set_userdata('session_textures_view',$searchTexturesView);
                    }
                    $sessionTexturesView =  $this->session->userdata('session_textures_view');
                   
                    $data = array();
                    //get rows count
                    $conditions['search_textures_view'] = $sessionTexturesView;
                    $conditions['returnType'] = 'count';
                    $totalRec = $this->DataModel->viewTexturesData($conditions, MCPE_TEXTURES);
                    
                    //pagination config
                    $config['base_url']    = site_url('textures-view');
                    $config['uri_segment'] = 2;
                    $config['total_rows']  = $totalRec;
                    $config['per_page']    = 20;
                    
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
                    $conditions['limit'] = 20;

                    $textures = $this->DataModel->viewTexturesData($conditions, MCPE_TEXTURES);
                    $data['viewTextures'] = array();
                    
                    if (is_array($textures) || is_object($textures)){
                        foreach($textures as $Row){
                            $dataArray = array();
                            $dataArray['unique_id'] = $Row['unique_id'];
                            $dataArray['category_id'] = $Row['category_id'];
                            $dataArray['data_name'] = $Row['data_name'];
                            $dataArray['data_description'] = $Row['data_description'];
                            $dataArray['data_image'] = $Row['data_image'];
                            $dataArray['data_bundle'] = $Row['data_bundle'];
                            $dataArray['data_support_version'] = $Row['data_support_version'];
                            $dataArray['data_price'] = $Row['data_price'];
                            $dataArray['data_size'] = $Row['data_size'];
                            $dataArray['data_view'] = $Row['data_view'];
                            $dataArray['data_download'] = $Row['data_download'];
                            $dataArray['is_copy'] = $Row['is_copy'];
                            $dataArray['data_status'] = $Row['data_status'];
                            $dataArray['category_name'] = $this->DataModel->getCategoryNameData('category_id = '.$dataArray['category_id'], MCPE_CATEGORY_TEXTURES);
                            array_push($data['viewTextures'], $dataArray);
                        }
                    }

                    if($data['viewTextures'] != null){
                        $this->load->view('header');
                        $this->load->view('android/textures_view',$data);
                        $this->load->view('footer');
                    } else {
                        $this->session->unset_userdata('session_textures_view');  
                        $data['msg'] = array(
                            'data_title'=>"Textures Database is Empty",
                            'data_description'=>"Please add textures & redirect textures from the below button.",
                            'button_link'=>"textures-new",
                            'button_text'=>"New Textures",
                            'button_link1'=>'textures-view',
                            'button_text1'=>"View Textures",
                        );
                        $this->load->view('header');
                        $this->load->view('nodata', $data);
                        $this->load->view('footer');
                    }
                } else {
                    redirect('error');
                }
            } else {
                redirect('error');
            }
        } else {
            redirect('logout');
        }
    }

    public function texturesEdit($uniqueID = 0){
        $isLogin = $this->checkAuth();
        if($isLogin == "True"){
            if(!empty($this->session->userdata['member_role'])) { 
                if($this->session->userdata['member_role'] == "Administrator" or $this->session->userdata['member_role'] == "Developer") {
                    $checkEncryption = $this->DataModel->checkEncrypt($uniqueID,ENCRYPT_TABLE);
                    if($checkEncryption){
                        $uniqueID = $checkEncryption->enc_number;
                    }
                   
                    if(ctype_digit($uniqueID)){
                        $data['texturesData'] = $this->DataModel->getData('unique_id = '.$uniqueID, MCPE_TEXTURES);
                        $data['viewCategoryTextures'] = $this->DataModel->viewData('category_id '.'DESC', null, MCPE_CATEGORY_TEXTURES);
                        $categoryID = $data['texturesData']->category_id;
                        $data['categoryTexturesData'] = $this->DataModel->getData('category_id = '.$categoryID, MCPE_CATEGORY_TEXTURES);
                        if(!empty($data['texturesData'])){
                            $this->load->view('header');
                            $this->load->view('android/textures_edit',$data);
                            $this->load->view('footer');
                        } else {
                            redirect('error');
                        }
                        if($this->input->post('submit')){
                            date_default_timezone_set("Asia/Kolkata");
                            $s3Client = $this->getconfig();
                            $uniqueCode = $this->uniqueKey();
                            
                            $imageKey = $data['texturesData']->data_image;
                            $newImageKey = basename($imageKey);
                    
                            $bundleKey = $data['texturesData']->data_bundle;
                            $newBundleKey = basename($bundleKey);

                            if (!empty($_FILES['data_image']['name']) and !empty($_FILES['data_bundle']['name'])){
                                $deleteImage = $s3Client->deleteObject([
                                        'Bucket' => BUCKET_NAME,
                                        'Key'    => DATA_IMAGE.$newImageKey,
                                    ]);
                                $deleteBundle = $s3Client->deleteObject([
                                        'Bucket' => BUCKET_NAME,
                                        'Key'    => DATA_BUNDLE.$newBundleKey,
                                    ]);
                                
                                $imageName = $_FILES['data_image']['name'];
                                $imageTemp = $_FILES['data_image']['tmp_name'];
                                $imagePath = DATA_IMAGE;
                                $imageResponse = $this->newBucketObject($imageName, $uniqueCode, $imageTemp, $imagePath);

                                $bundleName = $_FILES['data_bundle']['name'];
                                $bundleTemp = $_FILES['data_bundle']['tmp_name'];
                                $bundlePath = DATA_BUNDLE;
                                $bundleResponse = $this->newBucketObject($bundleName, $uniqueCode, $bundleTemp, $bundlePath);

                                $editData = array(
                                    'category_id'=>$this->input->post('category_id'),
                                    'data_name'=>$this->input->post('data_name'),
                                    'data_description'=>$this->input->post('data_description'),
                                    'data_image'=>$imageResponse,
                                    'data_bundle'=>$bundleResponse,
                                    'data_support_version'=>$this->input->post('data_support_version'),
                                    'data_price'=>$this->input->post('data_price'),
                                    'data_size'=>$this->input->post('data_size'),
                                    'data_view'=>$this->input->post('data_view'),
                                    'data_download'=>$this->input->post('data_download'),
                                    'data_status'=>$this->input->post('data_status'),
                                );
                            } else if(!empty($_FILES['data_image']['name'])) {
                                $deleteImage = $s3Client->deleteObject([
                                            'Bucket' => BUCKET_NAME,
                                            'Key'    => DATA_IMAGE.$newImageKey,
                                        ]);
                    
                                $imageName = $_FILES['data_image']['name'];
                                $imageTemp = $_FILES['data_image']['tmp_name'];
                                $imagePath = DATA_IMAGE;
                                $imageResponse = $this->newBucketObject($imageName, $uniqueCode, $imageTemp, $imagePath);
                    
                                $editData = array(
                                    'category_id'=>$this->input->post('category_id'),
                                    'data_name'=>$this->input->post('data_name'),
                                    'data_description'=>$this->input->post('data_description'),
                                    'data_image'=>$imageResponse,
                                    'data_support_version'=>$this->input->post('data_support_version'),
                                    'data_price'=>$this->input->post('data_price'),
                                    'data_size'=>$this->input->post('data_size'),
                                    'data_view'=>$this->input->post('data_view'),
                                    'data_download'=>$this->input->post('data_download'),
                                    'data_status'=>$this->input->post('data_status'),
                                );
                            } else if(!empty($_FILES['data_bundle']['name'])) {
                                $deleteBundle = $s3Client->deleteObject([
                                        'Bucket' => BUCKET_NAME,
                                        'Key'    => DATA_BUNDLE.$newBundleKey,
                                    ]);
                                
                                $bundleName = $_FILES['data_bundle']['name'];
                                $bundleTemp = $_FILES['data_bundle']['tmp_name'];
                                $bundlePath = DATA_BUNDLE;
                                $bundleResponse = $this->newBucketObject($bundleName, $uniqueCode, $bundleTemp, $bundlePath);
                                
                                $editData = array(
                                    'category_id'=>$this->input->post('category_id'),
                                    'data_name'=>$this->input->post('data_name'),
                                    'data_description'=>$this->input->post('data_description'),
                                    'data_bundle'=>$bundleResponse,
                                    'data_support_version'=>$this->input->post('data_support_version'),
                                    'data_price'=>$this->input->post('data_price'),
                                    'data_size'=>$this->input->post('data_size'),
                                    'data_view'=>$this->input->post('data_view'),
                                    'data_download'=>$this->input->post('data_download'),
                                    'data_status'=>$this->input->post('data_status'),
                                );
                            } else {
                                $editData = array(
                                    'category_id'=>$this->input->post('category_id'),
                                    'data_name'=>$this->input->post('data_name'),
                                    'data_description'=>$this->input->post('data_description'),
                                    'data_support_version'=>$this->input->post('data_support_version'),
                                    'data_price'=>$this->input->post('data_price'),
                                    'data_size'=>$this->input->post('data_size'),
                                    'data_view'=>$this->input->post('data_view'),
                                    'data_download'=>$this->input->post('data_download'),
                                    'data_status'=>$this->input->post('data_status'),
                                );
                            }
                            $editDataEntry = $this->DataModel->editData('unique_id = '.$uniqueID, MCPE_TEXTURES, $editData);
                            if($editDataEntry){
                                redirect('textures-view');
                            }
                        }
                    } else {
                        redirect('error');
                    }
                } else {
                    redirect('error');
                }
            } else {
                redirect('error');
            }
        } else {
            redirect('logout');
        }
    }

    //Shaders Functions
    public function shadersNew(){
        $isLogin = $this->checkAuth();
        if($isLogin == "True"){
            if(!empty($this->session->userdata['member_role'])) { 
                if($this->session->userdata['member_role'] == "Administrator" or $this->session->userdata['member_role'] == "Developer") {
                    date_default_timezone_set("Asia/Kolkata");
                    $s3Client = $this->getconfig();
                    $uniqueCode = $this->uniqueKey();
                    $this->form_validation->set_rules('category_id', 'Text', 'required');
                    $this->form_validation->set_rules('data_name', 'Text', 'required');
                    $this->form_validation->set_rules('data_description', 'Text', 'required');
                    $this->form_validation->set_rules('data_support_version', 'Text', 'required');
                    $this->form_validation->set_rules('data_price', 'Text', 'required');
                    $this->form_validation->set_rules('data_size', 'Text', 'required');
                    $this->form_validation->set_rules('data_view', 'Text', 'required');
                    $this->form_validation->set_rules('data_download', 'Text', 'required');

                    if (empty($_FILES['data_image']['name'])){
                        $this->form_validation->set_rules('data_image', 'Data Image', 'required');
                    }
                    $this->form_validation->set_error_delimiters('','');

                    if (empty($_FILES['data_bundle']['name'])){
                        $this->form_validation->set_rules('data_bundle', 'Data Bundle', 'required');
                    }
                    $this->form_validation->set_error_delimiters('','');
                    
                    if ($this->form_validation->run() == FALSE){
                        $data['categoryShadersData'] = $this->DataModel->viewData('category_id '.'DESC', null, MCPE_CATEGORY_SHADERS);
                        $data['error'] = "";
                        $this->load->view('header');
                        $this->load->view('android/shaders_new',$data);
                        $this->load->view('footer');
                    }else{

                        $imageName = $_FILES['data_image']['name'];
                        $imageTemp = $_FILES['data_image']['tmp_name'];
                        $imagePath = DATA_IMAGE;
                        $imageResponse = $this->newBucketObject($imageName, $uniqueCode, $imageTemp, $imagePath);

                        $bundleName = $_FILES['data_bundle']['name'];
                        $bundleTemp = $_FILES['data_bundle']['tmp_name'];
                        $bundlePath = DATA_BUNDLE;
                        $bundleResponse = $this->newBucketObject($bundleName, $uniqueCode, $bundleTemp, $bundlePath);
                        
                        $newData = array(
                            'category_id'=>$this->input->post('category_id'),
                            'data_name'=>$this->input->post('data_name'),
                            'data_description'=>$this->input->post('data_description'),
                            'data_image'=>$imageResponse,
                            'data_bundle'=>$bundleResponse,
                            'data_support_version'=>$this->input->post('data_support_version'),
                            'data_price'=>$this->input->post('data_price'),
                            'data_size'=>$this->input->post('data_size'),
                            'data_view'=>$this->input->post('data_view'),
                            'data_download'=>$this->input->post('data_download'),
                            'is_copy'=>'False',
                            'data_status'=>$this->input->post('data_status'),
                        );
                        $newDataEntry = $this->DataModel->insertData(MCPE_SHADERS, $newData);
                        if($newDataEntry){
                          redirect('shaders-view');  
                        }
                    }
                } else {
                    redirect('error');
                }
            } else {
                redirect('error');
            }
        } else {
            redirect('logout');
        }
    }

    public function shadersView(){
        $isLogin = $this->checkAuth();
        if($isLogin == "True"){
            if(!empty($this->session->userdata['member_role'])) { 
                if($this->session->userdata['member_role'] == "Administrator" or $this->session->userdata['member_role'] == "Developer") {

                    if(isset($_POST['reset'])){
                        $this->session->unset_userdata('session_shaders_view');
                    }
                    if(isset($_POST['search'])){
                        $searchShadersView = $this->input->post('search_shaders_view');
                        $this->session->set_userdata('session_shaders_view',$searchShadersView);
                    }
                    $sessionShadersView =  $this->session->userdata('session_shaders_view');

                   
                    $data = array();
                    //get rows count
                    $conditions['search_shaders_view'] = $sessionShadersView;
                    $conditions['returnType'] = 'count';
                    $totalRec = $this->DataModel->viewShadersData($conditions, MCPE_SHADERS);
                    
                    //pagination config
                    $config['base_url']    = site_url('shaders-view');
                    $config['uri_segment'] = 2;
                    $config['total_rows']  = $totalRec;
                    $config['per_page']    = 20;
                    
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
                    $conditions['limit'] = 20;

                    $shaders = $this->DataModel->viewShadersData($conditions, MCPE_SHADERS);
                    $data['viewShaders'] = array();
                    
                    if (is_array($shaders) || is_object($shaders)){
                        foreach($shaders as $Row){
                            $dataArray = array();
                            $dataArray['unique_id'] = $Row['unique_id'];
                            $dataArray['category_id'] = $Row['category_id'];
                            $dataArray['data_name'] = $Row['data_name'];
                            $dataArray['data_description'] = $Row['data_description'];
                            $dataArray['data_image'] = $Row['data_image'];
                            $dataArray['data_bundle'] = $Row['data_bundle'];
                            $dataArray['data_support_version'] = $Row['data_support_version'];
                            $dataArray['data_price'] = $Row['data_price'];
                            $dataArray['data_size'] = $Row['data_size'];
                            $dataArray['data_view'] = $Row['data_view'];
                            $dataArray['data_download'] = $Row['data_download'];
                            $dataArray['is_copy'] = $Row['is_copy'];
                            $dataArray['data_status'] = $Row['data_status'];
                            $dataArray['category_name'] = $this->DataModel->getCategoryNameData('category_id = '.$dataArray['category_id'], MCPE_CATEGORY_SHADERS);
                            array_push($data['viewShaders'], $dataArray);
                        }
                    }

                    if($data['viewShaders'] != null){
                        $this->load->view('header');
                        $this->load->view('android/shaders_view',$data);
                        $this->load->view('footer');
                    } else {
                        $this->session->unset_userdata('session_shaders_view');
                        $data['msg'] = array(
                            'data_title'=>"Shaders Database is Empty",
                            'data_description'=>"Please add shaders & redirect shaders from the below button.",
                            'button_link'=>"shaders-new",
                            'button_text'=>"New Shaders",
                            'button_link1'=>'shaders-view',
                            'button_text1'=>"View Shaders",
                        );
                        $this->load->view('header');
                        $this->load->view('nodata', $data);
                        $this->load->view('footer');
                    }
                } else {
                    redirect('error');
                }
            } else {
                redirect('error');
            }
        } else {
            redirect('logout');
        }
    }

    public function shadersEdit($uniqueID = 0){
        $isLogin = $this->checkAuth();
        if($isLogin == "True"){
            if(!empty($this->session->userdata['member_role'])) { 
                if($this->session->userdata['member_role'] == "Administrator" or $this->session->userdata['member_role'] == "Developer") {
                    $checkEncryption = $this->DataModel->checkEncrypt($uniqueID,ENCRYPT_TABLE);
                    if($checkEncryption){
                        $uniqueID = $checkEncryption->enc_number;
                    }
                   
                    if(ctype_digit($uniqueID)){
                        $data['shadersData'] = $this->DataModel->getData('unique_id = '.$uniqueID, MCPE_SHADERS);
                        $data['viewCategoryShaders'] = $this->DataModel->viewData('category_id '.'DESC', null, MCPE_CATEGORY_SHADERS);
                        $categoryID = $data['shadersData']->category_id;
                        $data['categoryShadersData'] = $this->DataModel->getData('category_id = '.$categoryID, MCPE_CATEGORY_SHADERS);
                        if(!empty($data['shadersData'])){
                            $this->load->view('header');
                            $this->load->view('android/shaders_edit',$data);
                            $this->load->view('footer');
                        } else {
                            redirect('error');
                        }
                        if($this->input->post('submit')){
                            date_default_timezone_set("Asia/Kolkata");
                            $s3Client = $this->getconfig();
                            $uniqueCode = $this->uniqueKey();
                            
                            $imageKey = $data['shadersData']->data_image;
                            $newImageKey = basename($imageKey);
                    
                            $bundleKey = $data['shadersData']->data_bundle;
                            $newBundleKey = basename($bundleKey);

                            if (!empty($_FILES['data_image']['name']) and !empty($_FILES['data_bundle']['name'])){
                                $deleteImage = $s3Client->deleteObject([
                                        'Bucket' => BUCKET_NAME,
                                        'Key'    => DATA_IMAGE.$newImageKey,
                                    ]);
                                $deleteBundle = $s3Client->deleteObject([
                                        'Bucket' => BUCKET_NAME,
                                        'Key'    => DATA_BUNDLE.$newBundleKey,
                                    ]);
                                
                                $imageName = $_FILES['data_image']['name'];
                                $imageTemp = $_FILES['data_image']['tmp_name'];
                                $imagePath = DATA_IMAGE;
                                $imageResponse = $this->newBucketObject($imageName, $uniqueCode, $imageTemp, $imagePath);

                                $bundleName = $_FILES['data_bundle']['name'];
                                $bundleTemp = $_FILES['data_bundle']['tmp_name'];
                                $bundlePath = DATA_BUNDLE;
                                $bundleResponse = $this->newBucketObject($bundleName, $uniqueCode, $bundleTemp, $bundlePath);

                                $editData = array(
                                    'category_id'=>$this->input->post('category_id'),
                                    'data_name'=>$this->input->post('data_name'),
                                    'data_description'=>$this->input->post('data_description'),
                                    'data_image'=>$imageResponse,
                                    'data_bundle'=>$bundleResponse,
                                    'data_support_version'=>$this->input->post('data_support_version'),
                                    'data_price'=>$this->input->post('data_price'),
                                    'data_size'=>$this->input->post('data_size'),
                                    'data_view'=>$this->input->post('data_view'),
                                    'data_download'=>$this->input->post('data_download'),
                                    'data_status'=>$this->input->post('data_status'),
                                );
                            } else if(!empty($_FILES['data_image']['name'])) {
                                $deleteImage = $s3Client->deleteObject([
                                            'Bucket' => BUCKET_NAME,
                                            'Key'    => DATA_IMAGE.$newImageKey,
                                        ]);
                    
                                $imageName = $_FILES['data_image']['name'];
                                $imageTemp = $_FILES['data_image']['tmp_name'];
                                $imagePath = DATA_IMAGE;
                                $imageResponse = $this->newBucketObject($imageName, $uniqueCode, $imageTemp, $imagePath);
                    
                                $editData = array(
                                    'category_id'=>$this->input->post('category_id'),
                                    'data_name'=>$this->input->post('data_name'),
                                    'data_description'=>$this->input->post('data_description'),
                                    'data_image'=>$imageResponse,
                                    'data_support_version'=>$this->input->post('data_support_version'),
                                    'data_price'=>$this->input->post('data_price'),
                                    'data_size'=>$this->input->post('data_size'),
                                    'data_view'=>$this->input->post('data_view'),
                                    'data_download'=>$this->input->post('data_download'),
                                    'data_status'=>$this->input->post('data_status'),
                                );
                            } else if(!empty($_FILES['data_bundle']['name'])) {
                                $deleteBundle = $s3Client->deleteObject([
                                        'Bucket' => BUCKET_NAME,
                                        'Key'    => DATA_BUNDLE.$newBundleKey,
                                    ]);
                                
                                $bundleName = $_FILES['data_bundle']['name'];
                                $bundleTemp = $_FILES['data_bundle']['tmp_name'];
                                $bundlePath = DATA_BUNDLE;
                                $bundleResponse = $this->newBucketObject($bundleName, $uniqueCode, $bundleTemp, $bundlePath);
                                
                                $editData = array(
                                    'category_id'=>$this->input->post('category_id'),
                                    'data_name'=>$this->input->post('data_name'),
                                    'data_description'=>$this->input->post('data_description'),
                                    'data_bundle'=>$bundleResponse,
                                    'data_support_version'=>$this->input->post('data_support_version'),
                                    'data_price'=>$this->input->post('data_price'),
                                    'data_size'=>$this->input->post('data_size'),
                                    'data_view'=>$this->input->post('data_view'),
                                    'data_download'=>$this->input->post('data_download'),
                                    'data_status'=>$this->input->post('data_status'),
                                );
                            } else {
                                $editData = array(
                                    'category_id'=>$this->input->post('category_id'),
                                    'data_name'=>$this->input->post('data_name'),
                                    'data_description'=>$this->input->post('data_description'),
                                    'data_support_version'=>$this->input->post('data_support_version'),
                                    'data_price'=>$this->input->post('data_price'),
                                    'data_size'=>$this->input->post('data_size'),
                                    'data_view'=>$this->input->post('data_view'),
                                    'data_download'=>$this->input->post('data_download'),
                                    'data_status'=>$this->input->post('data_status'),
                                );
                            }
                            $editDataEntry = $this->DataModel->editData('unique_id = '.$uniqueID, MCPE_SHADERS, $editData);
                            if($editDataEntry){
                                redirect('shaders-view');
                            }
                        }
                    } else {
                        redirect('error');
                    }
                } else {
                    redirect('error');
                }
            } else {
                redirect('error');
            }
        } else {
            redirect('logout');
        }
    }

    //Skin Functions
    public function skinNew(){
        $isLogin = $this->checkAuth();
        if($isLogin == "True"){
            if(!empty($this->session->userdata['member_role'])) { 
                if($this->session->userdata['member_role'] == "Administrator" or $this->session->userdata['member_role'] == "Developer") {
                    date_default_timezone_set("Asia/Kolkata");
                    $s3Client = $this->getconfig();
                    $uniqueCode = $this->uniqueKey();
                    $this->form_validation->set_rules('data_name', 'Text', 'required');
                    $this->form_validation->set_rules('data_description', 'Text', 'required');
                    $this->form_validation->set_rules('data_support_version', 'Text', 'required');
                    $this->form_validation->set_rules('data_price', 'Text', 'required');
                    $this->form_validation->set_rules('data_size', 'Text', 'required');
                    $this->form_validation->set_rules('data_view', 'Text', 'required');
                    $this->form_validation->set_rules('data_download', 'Text', 'required');

                    if (empty($_FILES['data_image']['name'])){
                        $this->form_validation->set_rules('data_image', 'Data Image', 'required');
                    }
                    $this->form_validation->set_error_delimiters('','');

                    if (empty($_FILES['data_bundle']['name'])){
                        $this->form_validation->set_rules('data_bundle', 'Data Bundle', 'required');
                    }
                    $this->form_validation->set_error_delimiters('','');
                    
                    if ($this->form_validation->run() == FALSE){
                        $data['categorySkinData'] = $this->DataModel->viewData('category_id '.'DESC', null, MCPE_CATEGORY_SKIN);
                        $data['error'] = "";
                        $this->load->view('header');
                        $this->load->view('android/skin_new',$data);
                        $this->load->view('footer');
                    } else {
                        $imageName = $_FILES['data_image']['name'];
                        $imageTemp = $_FILES['data_image']['tmp_name'];
                        $imagePath = DATA_IMAGE;
                        $imageResponse = $this->newBucketObject($imageName, $uniqueCode, $imageTemp, $imagePath);

                        $bundleName = $_FILES['data_bundle']['name'];
                        $bundleTemp = $_FILES['data_bundle']['tmp_name'];
                        $bundlePath = DATA_BUNDLE;
                        $bundleResponse = $this->newBucketObject($bundleName, $uniqueCode, $bundleTemp, $bundlePath);
                        
                        $newData = array(
                            'category_id'=>$this->input->post('category_id'),
                            'data_name'=>$this->input->post('data_name'),
                            'data_description'=>$this->input->post('data_description'),
                            'data_image'=>$imageResponse,
                            'data_bundle'=>$bundleResponse,
                            'data_support_version'=>$this->input->post('data_support_version'),
                            'data_price'=>$this->input->post('data_price'),
                            'data_size'=>$this->input->post('data_size'),
                            'data_view'=>$this->input->post('data_view'),
                            'data_download'=>$this->input->post('data_download'),
                            'data_status'=>$this->input->post('data_status'),
                        );
                        $newDataEntry = $this->DataModel->insertData(MCPE_SKIN, $newData);
                        if($newDataEntry){
                          redirect('skin-view');  
                        }
                    }
                } else {
                    redirect('error');
                }
            } else {
                redirect('error');
            }
        } else {
            redirect('logout');
        }
    }

    public function skinView(){
        $isLogin = $this->checkAuth();
        if($isLogin == "True"){
            if(!empty($this->session->userdata['member_role'])) { 
                if($this->session->userdata['member_role'] == "Administrator" or $this->session->userdata['member_role'] == "Developer") {

                    if(isset($_POST['reset'])){
                        $this->session->unset_userdata('session_skin_view');
                    }
                    if(isset($_POST['search'])){
                        $searchSkinView = $this->input->post('search_skin_view');
                        $this->session->set_userdata('session_skin_view',$searchSkinView);
                    }
                    $sessionSkinView =  $this->session->userdata('session_skin_view');
                   
                    $data = array();
                    //get rows count
                    $conditions['search_skin_view'] = $sessionSkinView;
                    $conditions['returnType'] = 'count';
                    $totalRec = $this->DataModel->viewSkinData($conditions, MCPE_SKIN);
                    
                    //pagination config
                    $config['base_url']    = site_url('skin-view');
                    $config['uri_segment'] = 2;
                    $config['total_rows']  = $totalRec;
                    $config['per_page']    = 20;
                    
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
                    $conditions['limit'] = 20;

                    $skin = $this->DataModel->viewSkinData($conditions, MCPE_SKIN);
                    $data['viewSkin'] = array();
                    
                    if (is_array($skin) || is_object($skin)){
                        foreach($skin as $Row){
                            $dataArray = array();
                            $dataArray['unique_id'] = $Row['unique_id'];
                            $dataArray['category_id'] = $Row['category_id'];
                            $dataArray['data_name'] = $Row['data_name'];
                            $dataArray['data_description'] = $Row['data_description'];
                            $dataArray['data_image'] = $Row['data_image'];
                            $dataArray['data_bundle'] = $Row['data_bundle'];
                            $dataArray['data_support_version'] = $Row['data_support_version'];
                            $dataArray['data_price'] = $Row['data_price'];
                            $dataArray['data_size'] = $Row['data_size'];
                            $dataArray['data_view'] = $Row['data_view'];
                            $dataArray['data_download'] = $Row['data_download'];
                            $dataArray['data_status'] = $Row['data_status'];
                            $dataArray['category_name'] = $this->DataModel->getCategoryNameData('category_id = '.$dataArray['category_id'], MCPE_CATEGORY_SKIN);

                            array_push($data['viewSkin'], $dataArray);
                        }
                    }

                    if($data['viewSkin'] != null){
                        $this->load->view('header');
                        $this->load->view('android/skin_view',$data);
                        $this->load->view('footer');
                    } else {
                        $this->session->unset_userdata('session_skin_view');
                        $data['msg'] = array(
                            'data_title'=>"Skin Database is Empty",
                            'data_description'=>"Please add skin & redirect skin from the below button.",
                            'button_link'=>"skin-new",
                            'button_text'=>"New Skin",
                            'button_link1'=>'skin-view',
                            'button_text1'=>"View Skin",
                        );
                        $this->load->view('header');
                        $this->load->view('nodata', $data);
                        $this->load->view('footer');
                    }
                } else {
                    redirect('error');
                }
            } else {
                redirect('error');
            }
        } else {
            redirect('logout');
        }
    }

    public function skinEdit($uniqueID = 0){
        $isLogin = $this->checkAuth();
        if($isLogin == "True"){
            if(!empty($this->session->userdata['member_role'])) { 
                if($this->session->userdata['member_role'] == "Administrator" or $this->session->userdata['member_role'] == "Developer") {
                    $checkEncryption = $this->DataModel->checkEncrypt($uniqueID,ENCRYPT_TABLE);
                    if($checkEncryption){
                        $uniqueID = $checkEncryption->enc_number;
                    }
                   
                    if(ctype_digit($uniqueID)){
                        $data['skinData'] = $this->DataModel->getData('unique_id = '.$uniqueID, MCPE_SKIN);
                        $data['categorySkinData'] = $this->DataModel->viewData('category_id '.'DESC', null, MCPE_CATEGORY_SKIN);
                        if(!empty($data['skinData'])){
                            $this->load->view('header');
                            $this->load->view('android/skin_edit',$data);
                            $this->load->view('footer');
                        } else {
                            redirect('error');
                        }
                        if($this->input->post('submit')){
                            date_default_timezone_set("Asia/Kolkata");
                            $s3Client = $this->getconfig();
                            $uniqueCode = $this->uniqueKey();
                            
                            $imageKey = $data['skinData']->data_image;
                            $newImageKey = basename($imageKey);
                    
                            $bundleKey = $data['skinData']->data_bundle;
                            $newBundleKey = basename($bundleKey);

                            if (!empty($_FILES['data_image']['name']) and !empty($_FILES['data_bundle']['name'])){
                                $deleteImage = $s3Client->deleteObject([
                                        'Bucket' => BUCKET_NAME,
                                        'Key'    => DATA_IMAGE.$newImageKey,
                                    ]);
                                $deleteBundle = $s3Client->deleteObject([
                                        'Bucket' => BUCKET_NAME,
                                        'Key'    => DATA_BUNDLE.$newBundleKey,
                                    ]);
                                
                                $imageName = $_FILES['data_image']['name'];
                                $imageTemp = $_FILES['data_image']['tmp_name'];
                                $imagePath = DATA_IMAGE;
                                $imageResponse = $this->newBucketObject($imageName, $uniqueCode, $imageTemp, $imagePath);

                                $bundleName = $_FILES['data_bundle']['name'];
                                $bundleTemp = $_FILES['data_bundle']['tmp_name'];
                                $bundlePath = DATA_BUNDLE;
                                $bundleResponse = $this->newBucketObject($bundleName, $uniqueCode, $bundleTemp, $bundlePath);

                                $editData = array(
                                    'category_id'=>$this->input->post('category_id'),
                                    'data_name'=>$this->input->post('data_name'),
                                    'data_description'=>$this->input->post('data_description'),
                                    'data_image'=>$imageResponse,
                                    'data_bundle'=>$bundleResponse,
                                    'data_support_version'=>$this->input->post('data_support_version'),
                                    'data_price'=>$this->input->post('data_price'),
                                    'data_size'=>$this->input->post('data_size'),
                                    'data_view'=>$this->input->post('data_view'),
                                    'data_download'=>$this->input->post('data_download'),
                                    'data_status'=>$this->input->post('data_status'),
                                );
                            } else if(!empty($_FILES['data_image']['name'])) {
                                $deleteImage = $s3Client->deleteObject([
                                            'Bucket' => BUCKET_NAME,
                                            'Key'    => DATA_IMAGE.$newImageKey,
                                        ]);
                    
                                $imageName = $_FILES['data_image']['name'];
                                $imageTemp = $_FILES['data_image']['tmp_name'];
                                $imagePath = DATA_IMAGE;
                                $imageResponse = $this->newBucketObject($imageName, $uniqueCode, $imageTemp, $imagePath);
                    
                                $editData = array(
                                    'category_id'=>$this->input->post('category_id'),
                                    'data_name'=>$this->input->post('data_name'),
                                    'data_description'=>$this->input->post('data_description'),
                                    'data_image'=>$imageResponse,
                                    'data_support_version'=>$this->input->post('data_support_version'),
                                    'data_price'=>$this->input->post('data_price'),
                                    'data_size'=>$this->input->post('data_size'),
                                    'data_view'=>$this->input->post('data_view'),
                                    'data_download'=>$this->input->post('data_download'),
                                    'data_status'=>$this->input->post('data_status'),
                                );
                            } else if(!empty($_FILES['data_bundle']['name'])) {
                                $deleteBundle = $s3Client->deleteObject([
                                        'Bucket' => BUCKET_NAME,
                                        'Key'    => DATA_BUNDLE.$newBundleKey,
                                    ]);
                                
                                $bundleName = $_FILES['data_bundle']['name'];
                                $bundleTemp = $_FILES['data_bundle']['tmp_name'];
                                $bundlePath = DATA_BUNDLE;
                                $bundleResponse = $this->newBucketObject($bundleName, $uniqueCode, $bundleTemp, $bundlePath);
                                
                                $editData = array(
                                    'category_id'=>$this->input->post('category_id'),
                                    'data_name'=>$this->input->post('data_name'),
                                    'data_description'=>$this->input->post('data_description'),
                                    'data_bundle'=>$bundleResponse,
                                    'data_support_version'=>$this->input->post('data_support_version'),
                                    'data_price'=>$this->input->post('data_price'),
                                    'data_size'=>$this->input->post('data_size'),
                                    'data_view'=>$this->input->post('data_view'),
                                    'data_download'=>$this->input->post('data_download'),
                                    'data_status'=>$this->input->post('data_status'),
                                );
                            } else {
                                $editData = array(
                                    'category_id'=>$this->input->post('category_id'),
                                    'data_name'=>$this->input->post('data_name'),
                                    'data_description'=>$this->input->post('data_description'),
                                    'data_support_version'=>$this->input->post('data_support_version'),
                                    'data_price'=>$this->input->post('data_price'),
                                    'data_size'=>$this->input->post('data_size'),
                                    'data_view'=>$this->input->post('data_view'),
                                    'data_download'=>$this->input->post('data_download'),
                                    'data_status'=>$this->input->post('data_status'),
                                );
                            }
                            $editDataEntry = $this->DataModel->editData('unique_id = '.$uniqueID, MCPE_SKIN, $editData);
                            if($editDataEntry){
                                redirect('skin-view');
                            }
                        }
                    } else {
                        redirect('error');
                    }
                } else {
                    redirect('error');
                }
            } else {
                redirect('error');
            }
        } else {
            redirect('logout');
        }
    }

    //Search Functions
    public function searchView(){
        $isLogin = $this->checkAuth();
        if($isLogin == "True"){
            if(!empty($this->session->userdata['member_role'])) { 
                if($this->session->userdata['member_role'] == "Administrator" or $this->session->userdata['member_role'] == "Developer") {
                   
                    $data = array();
                    //get rows count
                    $conditions['returnType'] = 'count';
                    $totalRec = $this->DataModel->viewSearchData($conditions, MCPE_SEARCH_DATA);
                    
                    //pagination config
                    $config['base_url']    = site_url('search-view');
                    $config['uri_segment'] = 2;
                    $config['total_rows']  = $totalRec;
                    $config['per_page']    = 50;
                    
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
                    $conditions['limit'] = 50;

                    $search = $this->DataModel->viewSearchData($conditions, MCPE_SEARCH_DATA);
                    $data['viewSearch'] = array();
                    
                    if (is_array($search) || is_object($search)){
                        foreach($search as $Row){
                            $dataArray = array();
                            $dataArray['search_id'] = $Row['search_id'];
                            $dataArray['search_category'] = $Row['search_category'];
                            $dataArray['search_query'] = $Row['search_query'];
                            $dataArray['search_time'] = $Row['search_time'];
                            $dataArray['search_status'] = $Row['search_status'];
                            
                            array_push($data['viewSearch'], $dataArray);
                        }
                    }

                    if($data['viewSearch'] != null){
                        $this->load->view('header');
                        $this->load->view('android/search_view',$data);
                        $this->load->view('footer');
                    } else {
                        $data['msg'] = array(
                            'data_title'=>"Search Database is Empty",
                            'data_description'=>"Please search added data from the below button.",
                            'button_link'=>"search-added-view",
                            'button_text'=>"View Search Added",
                        );
                        $this->load->view('header');
                        $this->load->view('nodata', $data);
                        $this->load->view('footer');
                    }
                } else {
                    redirect('error');
                }
            } else {
                redirect('error');
            }
        } else {
            redirect('logout');
        }
    }

    public function searchAddedView(){
        $isLogin = $this->checkAuth();
        if($isLogin == "True"){
            if(!empty($this->session->userdata['member_role'])) { 
                if($this->session->userdata['member_role'] == "Administrator" or $this->session->userdata['member_role'] == "Developer") {
                   
                    $data = array();
                    //get rows count
                    $conditions['returnType'] = 'count';
                    $totalRec = $this->DataModel->viewSearchAddedData($conditions, MCPE_SEARCH_DATA);
                    
                    //pagination config
                    $config['base_url']    = site_url('search-added-view');
                    $config['uri_segment'] = 2;
                    $config['total_rows']  = $totalRec;
                    $config['per_page']    = 20;
                    
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
                    $conditions['limit'] = 20;

                    $searchAdded = $this->DataModel->viewSearchAddedData($conditions, MCPE_SEARCH_DATA);
                    $data['viewSearchAdded'] = array();
                    
                    if (is_array($searchAdded) || is_object($searchAdded)){
                        foreach($searchAdded as $Row){
                            $dataArray = array();
                            $dataArray['search_id'] = $Row['search_id'];
                            $dataArray['search_category'] = $Row['search_category'];
                            $dataArray['search_query'] = $Row['search_query'];
                            $dataArray['search_time'] = $Row['search_time'];
                            $dataArray['search_status'] = $Row['search_status'];
                            
                            array_push($data['viewSearchAdded'], $dataArray);
                        }
                    }

                    if($data['viewSearchAdded'] != null){
                        $this->load->view('header');
                        $this->load->view('android/search_added_view',$data);
                        $this->load->view('footer');
                    } else {
                        $data['msg'] = array(
                            'data_title'=>"Search Added Database is Empty",
                            'data_description'=>"Please search data from the below button.",
                            'button_link'=>"search-view",
                            'button_text'=>"View Search",
                        );
                        $this->load->view('header');
                        $this->load->view('nodata', $data);
                        $this->load->view('footer');
                    }
                } else {
                    redirect('error');
                }
            } else {
                redirect('error');
            }
        } else {
            redirect('logout');
        }
    }

    public function searchEdit($searchID = 0){
        $isLogin = $this->checkAuth();
        if($isLogin == "True"){
            if(!empty($this->session->userdata['member_role'])) { 
                if($this->session->userdata['member_role'] == "Administrator" or $this->session->userdata['member_role'] == "Developer") {
                    $checkEncryption = $this->DataModel->checkEncrypt($searchID,ENCRYPT_TABLE);
                    if($checkEncryption){
                        $searchID = $checkEncryption->enc_number;
                    }
                   
                    if(ctype_digit($searchID)){
                        $editData = array(
                            'search_status'=>"added",
                        );
                        $editDataEntry = $this->DataModel->editData('search_id = '.$searchID, MCPE_SEARCH_DATA, $editData);
                        if($editDataEntry){
                            redirect('search-view');
                        }
                    }
                } else {
                    redirect('error');
                }
            } else {
                    redirect('error');
                }
        } else {
            redirect('logout');
        }
    }
    
    public function searchDelete($searchID = 0){
        $isLogin = $this->checkAuth();
        if($isLogin == "True"){
            if(!empty($this->session->userdata['member_role'])) { 
                if($this->session->userdata['member_role'] == "Administrator" or $this->session->userdata['member_role'] == "Developer") {
                    $checkEncryption = $this->DataModel->checkEncrypt($searchID,ENCRYPT_TABLE);
                    if($checkEncryption){
                        $searchID = $checkEncryption->enc_number;
                    }
                   
                    if(ctype_digit($searchID)){
                        $deleteData = $this->DataModel->deleteData('search_id = '.$searchID, MCPE_SEARCH_DATA);
                        if($deleteData){
                            redirect('search-view');
                        }
                    }
                } else {
                    redirect('error');
                }
            } else {
                    redirect('error');
                }
        } else {
            redirect('logout');
        }
	}
	
	public function notificationView(){
        $data = array();
        //get rows count
        $conditions['returnType'] = 'count';
        $totalRec = $this->DataModel->viewTokenData($conditions, "ztoken_ebmm");
        
        //pagination config
        $config['base_url']    = site_url('category-mods-view');
        $config['uri_segment'] = 2;
        $config['total_rows']  = $totalRec;
        $config['per_page']    = 10;
        
        //initialize pagination library
        $this->pagination->initialize($config);
        
        //define offset
        $page = $this->uri->segment(2);
        $offset = !$page?0:$page;
        
        //get rows
        $conditions['returnType'] = '';
        $conditions['start'] = $offset;
        $conditions['limit'] = 10;

        $categoryMods = $this->DataModel->viewTokenData($conditions, "ztoken_ebmm");
        $data['viewCategoryMods'] = array();
        
        if (is_array($categoryMods) || is_object($categoryMods)){
            foreach($categoryMods as $Row){
                $dataArray = array();
                $dataArray['category_id'] = $Row['category_id'];
                $dataArray['category_name'] = $Row['category_name'];
                $dataArray['category_status'] = $Row['category_status'];
                
                array_push($data['viewCategoryMods'], $dataArray);
            }
        }

        if($data['viewCategoryMods'] != null){
            $this->load->view('header');
            $this->load->view('android/category_mods_view',$data);
            $this->load->view('footer');
        } else {
            $data['msg'] = array(
                'data_title'=>"Category Mods Database is Empty",
                'data_description'=>"Please add category mods from the below button.",
                'button_link'=>"category-mods-new",
                'button_text'=>"New Category Mods",
            );
            $this->load->view('header');
            $this->load->view('nodata', $data);
            $this->load->view('footer');
        }
    }
}



