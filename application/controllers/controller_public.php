<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Controller_public extends CI_Controller {
	function __construct(){
		parent::__construct();
		$this->load->model('model_public');
		$this->load->helper(array('form','url','file'));
		$this->load->library('form_validation','upload');
		session_start();
	}
	
	function index()
	{	
		$data = array(
			'directorate' => $this->model_public->loadDirectorate(),
			'department' => $this->model_public->loadDepartment(),
			'section' => $this->model_public->loadSection(),
            'directorate_selected' => '',
            'department_selected' => '',
            'section_selected' => '',
            'comment' => $this->model_public->getCommentFileAll(),  
		);
		if ($_SESSION["folderAccess"] == 4) {
			$data['thisFolder']= $this->model_public->getThisFolder(0);
			$data['childFolder']=$this->model_public->getChildFolder(0);
			$data['file']=$this->model_public->getFileInFolder(0);
			$data['count'] = $this->model_public->getTotalComment(0);
		}elseif ($_SESSION["folderAccess"] == 1 || $_SESSION["folderAccess"] == 2 || $_SESSION["folderAccess"] == 3) {
			$id_folder = $this->model_public->getIdFolder();
			$data['thisFolder'] = $this->model_public->getThisFolder($id_folder);
			$data['childFolder']=$this->model_public->getChildFolder($id_folder);
			$data['file']=$this->model_public->getFileInFolder($id_folder);
			$data['count'] = $this->model_public->getTotalComment($id_folder);
		}
		$this->model_public->countFile();
		$this->load->view('public/home_public.html',$data);
	}

	function openFolder($idFolder){
		$data = array(
			'directorate' => $this->model_public->loadDirectorate(),
			'department' => $this->model_public->loadDepartment(),
			'section' => $this->model_public->loadSection(),
            'directorate_selected' => '',
            'department_selected' => '',
            'section_selected' => '',
            'thisFolder' => $this->model_public->getThisFolder($idFolder),
            'childFolder' => $this->model_public->getChildFolder($idFolder),
            'file' => $this->model_public->getFileInFolder($idFolder),
            'comment' => $this->model_public->getCommentFile($idFolder),
            'count' => $this->model_public->getTotalComment($idFolder)
		);
		$this->load->view('public/home_public.html', $data);
	}

	function searchFile(){
		$data = array(
			'directorate' => $this->model_public->loadDirectorate(),
			'department' => $this->model_public->loadDepartment(),
			'section' => $this->model_public->loadSection(),
            'directorate_selected' => '',
            'department_selected' => '',
            'section_selected' => '',
            'fileSearch' => $this->model_public->searchFile(),
            'comment' => $this->model_public->getCommentFileAll(),
            'count' => $_SESSION["count"]
		);
		$this->load->view('public/home_public.html', $data);
	}

	function logout(){
		session_destroy();
		redirect(base_url());
	}

	function openFile($fileName){
		$fileName = str_replace("%20", " ", $fileName);
		$getFileData = $this->model_public->getFileData($fileName);
		foreach ($getFileData as $key) {
			$FileNameWithExtension = $key->fileName;
			$url = $key->url;
		}
		$url = str_replace(" ", "_", $url);
		$file_name = explode(".", $FileNameWithExtension);
		$file_extension = end($file_name);
		if ($file_extension == "pdf") {
			http_response_code(200);
			header('Content-Length: '.filesize($url));
			header("Content-Type: application/pdf");
			header('Content-Dispotition: attachment; filename="$FileNameWithExtension"');
			readfile($url);
			exit;
		} 
		elseif ($file_extension == "doc" || $file_extension == "docx") {
			http_response_code(200);
			header('Content-Length: '.filesize($url));
			header("Content-Type: application/msword");
			header('Content-Dispotition: attachment; filename="$FileNameWithExtension"');
			readfile($url);
			exit;
		} 
		elseif ($file_extension == "xls" || $file_extension == "xlsx") {
			http_response_code(200);
			header('Content-Length: '.filesize($url));
			header("Content-Type: application/vnd.ms-excel");
			header('Content-Dispotition: attachment; filename="$FileNameWithExtension"');
			readfile($url);
			exit;
		} 
		elseif ($file_extension == "ppt" || $file_extension == "pptx") {
			http_response_code(200);
			header('Content-Length: '.filesize($url));
			header("Content-Type: application/vnd.ms-powerpoint");
			header('Content-Dispotition: attachment; filename="$FileNameWithExtension"');
			readfile($url);
			exit;
		}	
	}

	function downloadFile($id_file){
		$folder = $this->model_public->getFileDataById($id_file);
		foreach ($folder as $key) {
			$idFolder = $key->id_folder;
			$nama_file = $key->fileNameNoDateTime;
			$url = $key->url;
		}
		if(isset($nama_file)){
			$file = str_replace(" ", "_", $url);
			
			if (file_exists($file))
			{
				$this->model_public->downloadFile($id_file);
				header('Content-Description: File Transfer');
				header('Content-Type: application/octet-stream');
				header('Content-Disposition: attachment; filename='.basename($file));
				header('Content-Transfer-Encoding: binary');
				header('Expires: 0');
				header('Cache-Control: private');
				header('Pragma: private');
				header('Content-Length: ' . filesize($file));
				ob_clean();
				flush();
				readfile($file);
				exit;
			} 			
		}
		// redirect('controller_public/openFolder/'.$idFolder);
	}

	function commentFile($id_file){
		$this->model_public->commentFile($id_file);
		$folder = $this->model_public->getFileDataById($id_file);
		foreach ($folder as $key) {
			$idFolder = $key->id_folder;
		}
		redirect('controller_public/openFolder/'.$idFolder);
	}

	function infoFile($idFile){
		$file['selectedFile'] = $this->model_public->getInfoFile($idFile);
		$file['downloadedFile'] = $this->model_public->getDownloadFile($idFile);
		foreach ($file['selectedFile'] as $key) {
			$parentFile = $key->id_parentFile;
		}
		while ($parentFile != 0) {
			$getParentFile = $this->model_public->getInfoFile($parentFile);
			foreach ($getParentFile as $key) {
			   $parentFile = $key->id_parentFile;
			}
			if (!isset($file['parentFile'])) {
				$file['parentFile'] = $getParentFile;
			}
			// else if (isset($file['parentFile'])) {
			// 	array_push($file['parentFile'], $getParentFile);
			// }
		}

		$file['comment'] = $this->model_public->getComment($idFile);
		$this->load->view('public/fileInfo_public.html', $file);
	}
}
