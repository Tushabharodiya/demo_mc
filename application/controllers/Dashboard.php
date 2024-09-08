<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {
	function __construct(){
		parent::__construct();
		$this->load->model('DataModel');
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
    
	public function index(){
		$isLogin = $this->checkAuth();
        if($isLogin == "True"){
            if(!empty($this->session->userdata['member_role'])) { 
                if($this->session->userdata['member_role'] == "Administrator" or $this->session->userdata['member_role'] == "Developer") {
                    $data['categoryModsPublishCount'] = $this->DataModel->countData('(category_status="publish")', MCPE_CATEGORY_MODS);
                    $data['categoryModsUnpublishCount'] = $this->DataModel->countData('(category_status="unpublish")', MCPE_CATEGORY_MODS);
                    $data['categoryAddonsPublishCount'] = $this->DataModel->countData('(category_status="publish")', MCPE_CATEGORY_ADDONS);
                    $data['categoryAddonsUnpublishCount'] = $this->DataModel->countData('(category_status="unpublish")', MCPE_CATEGORY_ADDONS);
                    $data['categoryMapsPublishCount'] = $this->DataModel->countData('(category_status="publish")', MCPE_CATEGORY_MAPS);
                    $data['categoryMapsUnpublishCount'] = $this->DataModel->countData('(category_status="unpublish")', MCPE_CATEGORY_MAPS);
                    $data['categorySeedsPublishCount'] = $this->DataModel->countData('(category_status="publish")', MCPE_CATEGORY_SEEDS);
                    $data['categorySeedsUnpublishCount'] = $this->DataModel->countData('(category_status="unpublish")', MCPE_CATEGORY_SEEDS);
                    $data['categoryTexturesPublishCount'] = $this->DataModel->countData('(category_status="publish")', MCPE_CATEGORY_TEXTURES);
                    $data['categoryTexturesUnpublishCount'] = $this->DataModel->countData('(category_status="unpublish")', MCPE_CATEGORY_TEXTURES);
                    $data['categoryShadersPublishCount'] = $this->DataModel->countData('(category_status="publish")', MCPE_CATEGORY_SHADERS);
                    $data['categoryShadersUnpublishCount'] = $this->DataModel->countData('(category_status="unpublish")', MCPE_CATEGORY_SHADERS);
                    $data['categorySkinsPublishCount'] = $this->DataModel->countData('(category_status="publish")', MCPE_CATEGORY_SKIN);
                    $data['categorySkinsUnpublishCount'] = $this->DataModel->countData('(category_status="unpublish")', MCPE_CATEGORY_SKIN);
                    $data['modsPublishCount'] = $this->DataModel->countData('(data_status="publish")', MCPE_MODS);
                    $data['modsUnpublishCount'] = $this->DataModel->countData('(data_status="unpublish")', MCPE_MODS);
                    $data['addonsPublishCount'] = $this->DataModel->countData('(data_status="publish")', MCPE_ADDONS);
                    $data['addonsUnpublishCount'] = $this->DataModel->countData('(data_status="unpublish")', MCPE_ADDONS);
                    $data['mapsPublishCount'] = $this->DataModel->countData('(data_status="publish")', MCPE_MAPS);
                    $data['mapsUnpublishCount'] = $this->DataModel->countData('(data_status="unpublish")', MCPE_MAPS);
                    $data['seedsPublishCount'] = $this->DataModel->countData('(data_status="publish")', MCPE_SEEDS);
                    $data['seedsUnpublishCount'] = $this->DataModel->countData('(data_status="unpublish")', MCPE_SEEDS);
                    $data['texturesPublishCount'] = $this->DataModel->countData('(data_status="publish")', MCPE_TEXTURES);
                    $data['texturesUnpublishCount'] = $this->DataModel->countData('(data_status="unpublish")', MCPE_TEXTURES);
                    $data['shadersPublishCount'] = $this->DataModel->countData('(data_status="publish")', MCPE_SHADERS);
                    $data['shadersUnpublishCount'] = $this->DataModel->countData('(data_status="unpublish")', MCPE_SHADERS);
                    $data['skinsPublishCount'] = $this->DataModel->countData('(data_status="publish")', MCPE_SKIN);
                    $data['skinsUnpublishCount'] = $this->DataModel->countData('(data_status="unpublish")', MCPE_SKIN);
                    $data['searchPublishCount'] = $this->DataModel->countData('(search_status="publish")', MCPE_SEARCH_DATA);
                    $data['searchAddedCount'] = $this->DataModel->countData('(search_status="added")', MCPE_SEARCH_DATA);

					$this->load->view('header');
					$this->load->view('index', $data);
					$this->load->view('footer');
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
}
