<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Controller_admin extends CI_Controller {
	function __construct(){
		parent::__construct();
		$this->load->model('model_admin');
		$this->load->helper(array('form','url'));
		$this->load->library('form_validation');
		session_start();
	}
	
	function index()
	{			
		$this->model_admin->countEmployeeRequest();
		$this->model_admin->countEmployee();
		$this->model_admin->countFolderRequest();
		$this->model_admin->countFile();
		$this->load->view('admin/home_admin.html');
	}

	function logout(){
		session_destroy();
		redirect(base_url());
	}

	function sidebar($destination){
		if ($destination == "home") {
			$this->load->view('admin/home_admin.html');
		} else if ($destination == "announcement") {
			$this->load->view('admin/announcement_admin.html');
		} else if ($destination == "request") {
			$this->model_admin->countEmployeeRequest();
			$data['request']=$this->model_admin->retrieveRequest();
			$this->load->view('admin/request_admin.html',$data);
		} else if ($destination == "list") {
			$data['directorate'] = $this->model_admin->get_directorate();
	        $data['department'] = $this->model_admin->get_department();
	        $data['section'] = $this->model_admin->get_section();
	        $data['employee']=$this->model_admin->retrieveEmployee();
			$this->load->view('admin/list_admin.html', $data);
		} else if ($destination == "folderManagement") {
			redirect('controller_admin/openFolder/0');		
		} else if ($destination == "fileAddRemove") {
			$this->load->view('admin/fileAddRemove_admin.html');
		} else if ($destination == "folderRequest") {
			$this->model_admin->countFolderRequest();
			$data['requestFolder']=$this->model_admin->retrieveFolderRequest();
			var_dump($data['requestFolder']);
			$this->load->view('admin/folderRequest_admin.html',$data);
		} else if ($destination == "logout") {
			session_destroy();
			redirect(base_url());
		} 
	}

	function acceptUserRequest($id_employee){
		$this->model_admin->acceptUserRequest($id_employee);
		redirect('controller_admin/sidebar/request');
	}

	function declineRequest($id){
		$this->model_admin->declineRequest($id);
		redirect('controller_admin/sidebar/request');	
	}

	function updateEmployee($id){
		$this->model_admin->updateEmployee($id);
		redirect('controller_admin/sidebar/list');	
	}

	function deleteEmployee($id){
		$this->model_admin->deleteEmployee($id);
		redirect('controller_admin/sidebar/list');			
	}

	function addFolder($id_parent){
		$folderName = $this->input->post('folderName');
		$this->model_admin->addFolder($id_parent, $folderName);
		redirect('controller_admin/openFolder/'.$id_parent);
	}

	function openFolder($id_folder){
		$folder['childFolder']=$this->model_admin->getChildFolder($id_folder);
		$folder['thisFolder']= $this->model_admin->getThisFolder($id_folder);
		$this->load->view('admin/folderManagement_admin.html', $folder);
	}

	function deleteFolder($id_folder){
		$this->model_admin->deleteFolder($id_folder);
		redirect('controller_admin/openFolder/0');
	}

	function changeNameFolder($id_folder){
		$folderNewName = $this->input->post('folderName');
		$this->model_admin->changeFolderName($id_folder, $folderNewName);
		redirect('controller_admin/openFolder/'.$id_folder);
	}

	function deleteFolderRequest($idFolderRequest){
		$this->model_admin->deleteFolderRequest($idFolderRequest);
		redirect('controller_admin/sidebar/folderRequest');	
	}

	function acceptFolderRequest($id_folderRequest){
		$this->model_admin->acceptFolderRequest($id_folderRequest);
		redirect('controller_admin/sidebar/folderRequest');	
	}
}