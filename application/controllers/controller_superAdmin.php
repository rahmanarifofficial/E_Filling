<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Controller_superAdmin extends CI_Controller {
	function __construct(){
		parent::__construct();
		$this->load->model('model_superAdmin');
		$this->load->helper(array('form','url'));
		$this->load->library('form_validation');
		session_start();
	}
	
	function index()
	{
		$data = array(
			'directorate' => $this->model_superAdmin->loadDirectorate(),
			'department' => $this->model_superAdmin->loadDepartment(),
			'section' => $this->model_superAdmin->loadSection(),
			'admin' => $this->model_superAdmin->getAdmin()
		);
		$this->load->view('superAdmin/list_superAdmin.html',$data);
	}

	function updateEmployee($id){
		$this->model_superAdmin->updateEmployee($id);
		redirect('controller_superAdmin/index');		
		
	}

	function deleteEmployee($id){
		$this->model_superAdmin->deleteEmployee($id);
		redirect('controller_superAdmin/index');		
	}

	function addAdmin(){
		$this->model_superAdmin->addAdmin();
		redirect('controller_superAdmin/index');		
	}
}