<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_public extends CI_Model {

    function getIdFolder(){
        if ($_SESSION["folderAccess"] == 1) {
            //section
            $getIdFolder = $this->db->query("SELECT * FROM t_folder WHERE id_directorate = ".$_SESSION["directorate"]." AND id_department = ".$_SESSION["department"]." AND id_section = ".$_SESSION["section"])->result();
        } elseif ($_SESSION["folderAccess"] == 2) {
            //department
            $getIdFolder = $this->db->query("SELECT * FROM t_folder WHERE id_directorate = ".$_SESSION["directorate"]." AND id_department = ".$_SESSION["department"]." AND id_section IS NULL")->result();
        } elseif ($_SESSION["folderAccess"] == 3) {
            //directorate
            $getIdFolder = $this->db->query("SELECT * FROM t_folder WHERE id_directorate = ".$_SESSION["directorate"]." AND id_department IS NULL AND id_section IS NULL")->result();
        } 
        foreach($getIdFolder as $key){
            $id_folder = $key->id_folder;
        }
        return $id_folder;
    }

    function getThisFolder($id_folder){
        $where=array(
            'id_folder' => $id_folder
        );
        return $this->db->get_where('t_folder', $where)->result();   
    }

    function getChildFolder($id_folder){
        $where=array(
            'id_parent' => $id_folder
        );
        return $this->db->get_where('t_folder', $where)->result();
    }
    
    function getFileInFolder($id_folder){
        $where=array(
            'id_folder' => $id_folder,
            'updated' => 0
        );
        return $this->db->get_where('t_file', $where)->result();
    }

    function loadDirectorate(){
        return $this->db->query("SELECT * FROM t_directorate")->result();   
    }

    function loadDepartment(){
        $this->db->join('t_directorate', 't_department.id_directorate = t_directorate.id_directorate');
        return $this->db->get('t_department')->result();
    }

    function loadSection(){
        $this->db->join('t_department', 't_section.id_department = t_department.id_department');
        return $this->db->get('t_section')->result();
    }
    
    function countFile(){
        $_SESSION["totalFile"] = $this->db->get('t_file')->num_rows();
    }

    function searchFile(){
        $input = $this->input->post("input"); 
        $directorate = $this->input->post("directorate");
        $department = $this->input->post("department");
        $section = $this->input->post("section");

        if (empty($directorate)) {
            $directorate = 0;
        }
        if (empty($department)) {
            $department = 0;
        }
        if (empty($section)) {
            $section = 0;
        }
        if ($directorate != 0 && $department != 0 && $section != 0) {
            $getData = $this->db->query("SELECT * FROM t_file WHERE fileNameNoDateTime LIKE '%".$input."%' AND id_directorate = ".$directorate." AND id_department = ".$department." AND id_section = ".$section)->result();
            $getTotalData = $this->db->query("SELECT * FROM t_file WHERE fileNameNoDateTime LIKE '%".$input."%' AND id_directorate = ".$directorate." AND id_department = ".$department." AND id_section = ".$section)->num_rows();
            // var_dump($x);
        }
        if ($directorate == 0 && $department == 0 && $section == 0) {
            $getData = $this->db->query("SELECT * FROM t_file WHERE fileNameNoDateTime LIKE '%".$input."%'")->result();    
            $getTotalData = $this->db->query("SELECT * FROM t_file WHERE fileNameNoDateTime LIKE '%".$input."%'")->num_rows();    
            // var_dump($x);            
        }
        if ($directorate != 0 && $department == 0 && $section == 0) {
            $getData = $this->db->query("SELECT * FROM t_file WHERE fileNameNoDateTime LIKE '%".$input."%' AND id_directorate = ".$directorate)->result();
            $getTotalData = $this->db->query("SELECT * FROM t_file WHERE fileNameNoDateTime LIKE '%".$input."%' AND id_directorate = ".$directorate)->num_rows();
            // var_dump($x);
        }
        if ($directorate != 0 && $department != 0 && $section == 0) {
            $getData = $this->db->query("SELECT * FROM t_file WHERE fileNameNoDateTime LIKE '%".$input."%' AND id_directorate = ".$directorate." AND id_department = ".$department)->result();
            $getTotalData = $this->db->query("SELECT * FROM t_file WHERE fileNameNoDateTime LIKE '%".$input."%' AND id_directorate = ".$directorate." AND id_department = ".$department)->num_rows();
            // var_dump($x);
        }
        $_SESSION["count"] = $getTotalData;
        return $getData;

    }

    function getFileData($fileName){
        $where=array(
            'fileNameNoExtension' => $fileName
        );
        return $this->db->get_where('t_file', $where)->result();
    }

    function getFileDataById($id_file){
        $where=array(
            'id_file' => $id_file
        );
        return $this->db->get_where('t_file', $where)->result();
    }

    function getFileDataByFolder($id_folder){
        $where=array(
            'id_folder' => $id_folder
        );
        return $this->db->get_where('t_file', $where)->result();
    }


    function commentFile($id_file)
    {
        date_default_timezone_set('Asia/Jakarta');
        $uploadTime =date('H:i:s');
        $uploadDate = date('Y-m-d');
        $getData = $this->db->get_where('t_file', array('id_file' => $id_file))->result();
        foreach ($getData as $key) {
            $idFolder = $key->id_folder;
        }
        $data = array(
            'id_file' => $id_file,
            'id_folder' => $idFolder,
            'id_employee' => $_SESSION["id_employee"],
            'uploadDate' => $uploadDate,
            'uploadTime' => $uploadTime,
            'comment' => $this->input->post('comment')
        );
        $this->db->insert('t_comment', $data);   
    }

    function getCommentFile($id_folder){
        return $this->db->query("SELECT a.id_file as id_file, a.comment as comment, DATE_FORMAT(a.uploadDate, '%d %M %Y') as uploadDate, TIME_FORMAT(a.uploadTime, '%H:%i:%s') as uploadTime, c.first_name as first_name, c.last_name as last_name FROM t_comment a JOIN t_file b on a.id_file = b.id_file JOIN t_employee c on a.id_employee = c.id_employee WHERE a.id_folder = ".$id_folder)->result();
    }

    function getComment($idFile){
        return $this->db->query("SELECT DATE_FORMAT(a.UploadDate, '%d %M %Y') as uploadDate, TIME_FORMAT(a.uploadTime, '%H:%i') as uploadTime, a.id_comment as id_comment, a.comment as comment, a.id_employee as id_employee, a.id_file as id_file, a.status as status, CONCAT(b.first_name,' ',b.last_name) as fullname, c.fileNameNoDateTime as file_name, c.fileNameNoExtension as url FROM t_comment a left join t_employee b on a.id_employee = b.id_employee left join t_file c on a.id_file = c.id_file WHERE a.id_file =".$idFile." ORDER BY uploadDate DESC, uploadTime DESC ")->result();
    }

    function getCommentFileAll(){
        return $this->db->query("SELECT a.id_file as id_file, a.comment as comment, DATE_FORMAT(a.uploadDate, '%d %M %Y') as uploadDate, TIME_FORMAT(a.uploadTime, '%H:%i:%s') as uploadTime, c.first_name as first_name, c.last_name as last_name FROM t_comment a JOIN t_file b on a.id_file = b.id_file JOIN t_employee c on a.id_employee = c.id_employee")->result();    
    }

    function getTotalComment($id_folder){
        $where=array(
            'id_folder' => $id_folder
        );
        return $this->db->get_where('t_file', $where)->num_rows();
    }

    function retrieveFolderAccess($id_employee){
        $where=array(
            'id_employee' => $id_employee
        );
        return $this->db->get_where('t_employee', $where)->result();
    }
    function getInfoFile($id_file){
        return $this->db->query("SELECT a.id_file, a.fileName,a.fileNameNoDateTime, a.fileNameNoExtension, DATE_FORMAT(a.uploadDate, '%d %M %Y') as uploadDate, TIME_FORMAT(a.uploadTime, '%H:%i') as uploadTime, a.id_parentFile, a.id_uploader, a.expiredDate, a.file_description, a.previledgeAccess, a.revision, b.id_employee, b.first_name, b.last_name, CONCAT(b.first_name,' ', b.last_name) as full_name FROM t_file a left join t_employee b on a.id_uploader = b.id_employee WHERE id_file = ".$id_file)->result();
    }

    function downloadFile($id_file){
        date_default_timezone_set('Asia/Jakarta');
        $downloadTime =date('H:i:s');
        $downloadDate = date('Y-m-d');
        $data = array(
            'id_file' => $id_file,
            'id_employee' => $_SESSION["id_employee"],
            'downloadDate' => $downloadDate,
            'downloadTime' => $downloadTime
        );
        $this->db->insert('t_download', $data);
    }

    function getDownloadFile($id_file){
        return $this->db->query("SELECT a.id_download, a.id_file, a.id_employee, DATE_FORMAT(a.downloadDate, '%d %M %Y') as downloadDate, TIME_FORMAT(a.downloadTime,   '%H:%i') as downloadTime, b.fileNameNoDateTime, CONCAT(c.first_name,' ', c.last_name) as full_name, (SELECT count(id_download) FROM t_download where id_file = ".$id_file.") as totalDownload FROM t_download a join t_file b on a.id_file = b.id_file join t_employee c on a.id_employee = c.id_employee where a.id_file = ".$id_file)->result();
    }
}