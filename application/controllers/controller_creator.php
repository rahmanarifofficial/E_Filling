<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Controller_creator extends CI_Controller {
	function __construct(){
		parent::__construct();
		$this->load->model('model_creator');
		$this->load->helper(array('form','url','file'));
		$this->load->library('form_validation','upload');
		session_start();
	}
	
	function index()
	{
		$this->model_creator->getTotalUnreadComment();
		$this->load->view('creator/home_creator.html');
	}

	function sidebar($destination){
		if ($destination == "home") {
			$this->load->view('creator/home_creator.html');
		} else if ($destination == "data") {
			if ($_SESSION["folderAccess"] == 4) {
				redirect('controller_creator/openFolder/0');
			}elseif ($_SESSION["folderAccess"] == 1 || $_SESSION["folderAccess"] == 2 || $_SESSION["folderAccess"] == 3) {
				$id_folder = $this->model_creator->getIdFolder();
				redirect('controller_creator/openFolder/'.$id_folder);				
			}
		} else if ($destination == "notes") {
			$this->model_creator->getTotalUnreadComment();
			$data['comment'] = $this->model_creator->getCommentFile();
			$this->load->view('creator/notes_creator.html', $data);
		} else if ($destination == "logout") {
			session_destroy();
			redirect(base_url());
		} 
	}

	function openFolder($id_folder){
		$folder['childFolder']=$this->model_creator->getChildFolder($id_folder);
		$folder['thisFolder']= $this->model_creator->getThisFolder($id_folder);
		$folder['file']=$this->model_creator->getFileInFolder($id_folder);
		$this->load->view('creator/data_creator.html', $folder);
	}

	function uploadFile($id_folder){
		$this->model_creator->uploadFile($id_folder);
        redirect('controller_creator/openFolder/'.$id_folder);
	}

	function openFile($fileName){
		$fileName = str_replace("%20", " ", $fileName);
		$getFileData = $this->model_creator->getFileWithFileName($fileName);
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

	function deleteFile($fileName){
		echo $fileName;
		$fileName = str_replace("%20", " ", $fileName);
		$getFileData = $this->model_creator->getFileWithFileName($fileName);

		foreach ($getFileData as $key) {
			$fileName = $key->fileName;
			$idFolder = $key->id_folder;
			$path = $key->url;
			$idFile = $key->id_file;
			$id_parentFile = $key->id_parentFile;
		}
		if ($id_parentFile != 0) {
			$this->model_creator->setUpdatedToZero($id_parentFile);
		}
		unlink($path);
		$this->model_creator->deleteFile($idFile);
        redirect('controller_creator/openFolder/'.$idFolder);
	}

	function updateFile($idFolder, $idOldFile){
		$this->model_creator->uploadFile($idFolder);
		$this->model_creator->updateFile($idFolder,$idOldFile);
		$_SESSION["thisFile"]=NULL;
        redirect('controller_creator/openFolder/'.$idFolder);
	}

	function infoFile($idFile){
		$file['selectedFile'] = $this->model_creator->getInfoFile($idFile);
		$file['downloadedFile'] = $this->model_creator->getDownloadFile($idFile);
		foreach ($file['selectedFile'] as $key) {
			$parentFile = $key->id_parentFile;
		}
		while ($parentFile != 0) {
			$getParentFile = $this->model_creator->getInfoFile($parentFile);
			foreach ($getParentFile as $key) {
			   $parentFile = $key->id_parentFile;
			}
			if (!isset($file['parentFile'])) {
				$file['parentFile'] = $getParentFile;
			}
			// else if (isset($file['parentFile'])) {
			// 	$x = $getParentFile;
			// 	array_push($file['parentFile'], $x);
			// }
		}
		// echo "<pre>";
		// var_dump($file['parentFile']);
		// echo "</pre>";
		$file['comment'] = $this->model_creator->getComment($idFile);
		$this->load->view('creator/fileInfo_creator.html', $file);
	}

	function request($idFolder){
		$this->model_creator->request($idFolder);
        redirect('controller_creator/openFolder/'.$idFolder);
	}
	function replyCommentFile($id_file){
		$this->model_creator->replyCommentFile($id_file);
		$this->model_creator->unreadCommentFile($id_file);
		redirect('controller_creator/sidebar/notes');
	}

	function deleteCommentFile($id_file){
		$this->model_creator->deleteCommentByFile($id_file);
		redirect('controller_creator/sidebar/notes');
	}

	function unreadCommentFile($id_file){
		$this->model_creator->unreadCommentFile($id_file);
		redirect('controller_creator/sidebar/notes');	
	}
}
