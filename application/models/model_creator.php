<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_creator extends CI_Model {

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

    function getChildFolder($id_folder){
        $where=array(
            'id_parent' => $id_folder
        );
        return $this->db->get_where('t_folder', $where)->result();
    }

    function getThisFolder($id_folder){
        $where=array(
            'id_folder' => $id_folder
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

    function getFileWithFileName($fileName){
        $where=array(
            'fileNameNoExtension' => $fileName
        );
        return $this->db->get_where('t_file', $where)->result();
    }

    function getInfoFile($id_file){
        return $this->db->query("SELECT a.id_file, a.fileName,a.fileNameNoDateTime, a.fileNameNoExtension, DATE_FORMAT(a.uploadDate, '%d %M %Y') as uploadDate, TIME_FORMAT(a.uploadTime, '%H:%i') as uploadTime, a.id_parentFile, a.id_uploader, a.expiredDate, a.file_description, a.previledgeAccess, a.revision, b.id_employee, b.first_name, b.last_name, CONCAT(b.first_name,' ', b.last_name) as full_name FROM t_file a left join t_employee b on a.id_uploader = b.id_employee WHERE id_file = ".$id_file)->result();
    }

    function getDownloadFile($id_file){
        return $this->db->query("SELECT a.id_download, a.id_file, a.id_employee, DATE_FORMAT(a.downloadDate, '%d %M %Y') as downloadDate, TIME_FORMAT(a.downloadTime,   '%H:%i') as downloadTime, b.fileNameNoDateTime, CONCAT(c.first_name,' ', c.last_name) as full_name, (SELECT count(id_download) FROM t_download where id_file = ".$id_file.") as totalDownload FROM t_download a join t_file b on a.id_file = b.id_file join t_employee c on a.id_employee = c.id_employee where a.id_file = ".$id_file)->result();
    }

    function setUpdatedToZero($id_file){
        $this->db->query("UPDATE t_file SET updated = 0 WHERE id_file = ".$id_file);
    }

    function deleteFile($id_file){
        $where=array(
            'id_file' => $id_file
        );
        return $this->db->delete('t_file', $where);
    }

    function updateFile($idFolder, $idOldFile){
        $this->db->query("UPDATE t_file SET updated = 1 WHERE id_file = ".$idOldFile);
        $where=array(
            'fileNameNoExtension' => $_SESSION["thisFile"]
        );
        $newFile= $this->db->get_where('t_file', $where)->result();
        foreach ($newFile as $key) {
            $idNewFile = $key->id_file;
        }
        $this->db->query("UPDATE t_file SET id_parentFile = ".$idOldFile." WHERE id_file = ".$idNewFile);
    }

    function uploadFile($id_folder){
        date_default_timezone_set('Asia/Jakarta');
        $file_ext = pathinfo($_FILES["userfile"]["name"],PATHINFO_EXTENSION);
        $new_name = $this->input->post('fileName').date('_dmY-His');
        if ($file_ext == "pdf") {
            $config['file_name']            = $new_name;
            $config['upload_path']          = './storedFile/documents';
            $config['allowed_types']        = 'pdf';
            $config['max_size']             = 15000;
            $this->load->library('upload', $config);   
            $url = "./storedFile/documents/";         
        }else if ($file_ext == "doc" || $file_ext == "docx") {
            $config['file_name']            = $new_name;
            $config['upload_path']          = './storedFile/documents';
            $config['allowed_types']        = 'doc|docx';
            $config['max_size']             = 15000;
            $this->load->library('upload', $config);   
            $url = "./storedFile/documents/";         
        }else if ($file_ext == "xls" || $file_ext == "xlsx") {
            $config['file_name']            = $new_name;
            $config['upload_path']          = './storedFile/documents';
            $config['allowed_types']        = 'xls|xlsx';
            $config['max_size']             = 15000;
            $this->load->library('upload', $config);   
            $url = "./storedFile/documents/";         
        }else if ($file_ext == "ppt" || $file_ext == "pptx") {
            $config['file_name']            = $new_name;
            $config['upload_path']          = './storedFile/documents';
            $config['allowed_types']        = 'ppt|pptx';
            $config['max_size']             = 15000;
            $this->load->library('upload', $config);   
            $url = "./storedFile/documents/";         
        }else if ($file_ext == "png" || $file_ext == "jpg" || $file_ext == "jpeg" || $file_ext == "bmp") {
            echo "string";
            $config['file_name']            = $new_name;
            $config['upload_path']          = './storedFile/images';
            $config['allowed_types']        = 'jpg|jpeg|bmp|png';
            $config['max_size']             = 2000;
            $config['max_width']            = 10240;
            $config['max_height']           = 10240;
            $this->load->library('upload', $config);   
            $url = "./storedFile/images/";         
        }
 
        if (!$this->upload->do_upload('userfile')){
            $error = array('error' => $this->upload->display_errors());
            var_dump($error);
        }else{
            $getFolderData = $this->db->query("SELECT id_directorate , id_department , id_section FROM t_folder WHERE id_folder = ".$id_folder)->result();
            var_dump($getFolderData);
            foreach ($getFolderData as $key) {
                $id_directorate = $key->id_directorate;
                $id_department = $key->id_department;
                $id_section = $key->id_section;
            }
            $fileNameFormated = $this->input->post('fileName').date('_dmY-His');
            $uploadTime =date('H:i:s');
            $uploadDate = date('Y-m-d');
            $data = array(
                'fileName' => $fileNameFormated.".".$file_ext,
                'fileNameNoExtension' => $fileNameFormated,
                'fileNameNoDateTime' => $this->input->post('fileName').".".$file_ext,
                'id_folder' => $id_folder,
                'id_uploader' => $_SESSION["id_employee"],
                'id_parentFile' => 0,
                'id_directorate' => $id_directorate,
                'id_department' =>$id_department,
                'id_section' => $id_section,
                'previledgeAccess' => $this->input->post('access'),
                'updated' => 0,
                'uploadTime' => $uploadTime,
                'uploadDate' => $uploadDate,
                'expiredDate' => $this->input->post('expiredDate'),
                'url' => $url.$new_name.".".$file_ext
            );
            $this->db->insert('t_file', $data);
            $_SESSION["thisFile"] = $fileNameFormated;
        }
    }

    function getComment($idFile){
        return $this->db->query("SELECT DATE_FORMAT(a.UploadDate, '%d %M %Y') as uploadDate, TIME_FORMAT(a.uploadTime, '%H:%i') as uploadTime, a.id_comment as id_comment, a.comment as comment, a.id_employee as id_employee, a.id_file as id_file, a.status as status, CONCAT(b.first_name,' ',b.last_name) as fullname, c.fileNameNoDateTime as file_name, c.fileNameNoExtension as url FROM t_comment a left join t_employee b on a.id_employee = b.id_employee left join t_file c on a.id_file = c.id_file WHERE a.id_file =".$idFile." ORDER BY uploadDate DESC, uploadTime DESC ")->result();
    }

    function request($idFolder){
        $radioButton = $this->input->post('options');
        if ($radioButton == 1) {
            $folderName = $this->input->post('newFolderName');
        } elseif ($radioButton == 2) {
            $folderName = $this->input->post('changeFolderName');
        }elseif ($radioButton == 3) {
            $folderName = " ";
        }

        $data = array(
            'id_employee' =>$_SESSION["id_employee"],
            'id_folder' => $idFolder,
            'request' => $radioButton,
            'folderNewName' => $folderName
        );
        $this->db->insert('t_request', $data);
    }
    function getTotalUnreadComment(){
        $_SESSION["unreadComment"] = $this->db->query("SELECT * FROM t_comment where status = 0 and id_file in (SELECT id_file FROM t_file WHERE id_uploader = '".$_SESSION["id_employee"]."')")->num_rows();
    }

    function getCommentFile(){
        return $this->db->query("SELECT DATE_FORMAT(a.uploadDate, '%d %M %Y') as uploadDate, TIME_FORMAT(a.uploadTime, '%H:%i:%s') as uploadTime, a.id_comment as id_comment, a.comment as comment, a.id_file as id_file, a.status as status, CONCAT(b.first_name,' ',b.last_name) as fullname, c.fileNameNoDateTime as file_name, c.fileNameNoExtension as url, d.folder as folder_name, b.prev as prev FROM t_comment a join t_employee b on a.id_employee = b.id_employee join t_file c on a.id_file = c.id_file join t_folder d on a.id_folder = d.id_folder WHERE a.id_file IN (SELECT e.id_file FROM t_file e WHERE e.id_uploader = '".$_SESSION["id_employee"]."') order by id_file, uploadDate DESC, uploadTime DESC")->result();
    }

    function replyCommentFile($id_file){
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
            'comment' => $this->input->post('comment'),
            'status' => 1
        );
        $this->db->insert('t_comment', $data);
    }
    function deleteCommentByFile($idFile){
        $where=array(
            'id_file' => $idFile
        );
        return $this->db->delete('t_comment', $where);
    }
    function unreadCommentFile($id_file){
        $this->db->query("UPDATE t_comment set status = 1 where id_file = ".$id_file);
    }


}
