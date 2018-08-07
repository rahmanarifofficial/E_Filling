<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Controller_guest extends CI_Controller {
	function __construct(){
		parent::__construct();
		$this->load->model('model_guest');
		$this->load->helper(array('form','url'));
		$this->load->library('form_validation');
		session_start();
	}
	
	function index()
	{
		$this->load->view('guest/welcome.html');
	}

	function toLogin()
	{
		$this->load->view('guest/login.html');
	}

	function toRegister(){
		$data = array(
			'directorate' => $this->model_guest->loadDirectorate(),
			'department' => $this->model_guest->loadDepartment(),
			'section' => $this->model_guest->loadSection()
		);
		$this->load->view('guest/register.html',$data);
	}

	function register(){
		$this->model_guest->register();
	}

	function login(){
		$this->model_guest->login();
	}
}