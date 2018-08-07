<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_admin extends CI_Model {

	function retrieveRequest(){
		return $this->db->query("SELECT a.id_employee, a.first_name, a.last_name,a.prev,a.email, a.id_folderAccess, b.section, c.department, d.directorate FROM t_employee a left join t_section b on a.id_section = b.id_section left join t_department c on a.id_department=c.id_department left JOIN t_directorate d on a.id_directorate=d.id_directorate WHERE a.prev = 0")->result();
	}

    function retrieveFolderRequest(){
        return $this->db->query("SELECT a.id_request,a.id_employee, a.id_folder, a.request,a.folderNewName, b.first_name, b.last_name , c.folder FROM t_request a join t_employee b on a.id_employee = b.id_employee join t_folder c on a.id_folder = c.id_folder")->result();
    }


	function retrieveEmployee(){
		return $this->db->query("SELECT a.id_employee, a.first_name, a.last_name,a.prev,a.email, a.id_folderAccess,b.id_section,b.section,c.id_department, c.department,d.id_directorate, d.directorate FROM t_employee a left join t_section b on a.id_section = b.id_section left join t_department c on a.id_department=c.id_department left JOIN t_directorate d on a.id_directorate=d.id_directorate WHERE prev != 0 && id_employee != ".$_SESSION["id_employee"])->result();	
	}

	function countEmployeeRequest(){
		$where=array(
            'prev' => 0
        );
		$_SESSION["employeeRequest"]=$this->db->get_where('t_employee',$where)->num_rows();
	}

    function countEmployee(){
        $_SESSION["employee"]=$this->db->query("SELECT * FROM t_employee WHERE prev != 0")->num_rows();
    }

    function countFile(){
        $_SESSION["file"]=$this->db->get('t_file')->num_rows();    
    }

    function countFolderRequest(){
        $_SESSION["folderRequest"]=$this->db->get('t_request')->num_rows();    
    }

	function acceptUserRequest($id_employee){
		$previledge = $this->input->post('previledge');
        if ($previledge == "3") {
            //admin
            return $this->db->query("UPDATE t_employee SET prev = ".$previledge.", id_folderAccess = 4 WHERE id_employee = ".$id_employee);
        } elseif ($previledge == "1" || $previledge == "2") {
            //creator or public
            $folderAccess = $this->input->post('folderAccess');
            return $this->db->query("UPDATE t_employee SET prev = ".$previledge." , id_folderAccess = ".$folderAccess." WHERE id_employee = ".$id_employee);
        }
	}

	function declineRequest($id){
		return $this->db->query("DELETE FROM t_employee WHERE id_employee = ". $id);
	}

	function updateEmployee($id){
		$previledge = $this->input->post('previledge');
        $folderAccess = $this->input->post('folderAccess');
        $directorate = $this->input->post('directorate');
        $department = $this->input->post('department');
        $section = $this->input->post('section');
        // echo $folderAccess;
        // echo $directorate."<br>";
        // echo $department."<br>";
        // echo $section."<br>";

        if (($department == null || $department == 0) && ($section == null || $section == 0)) {
            return $this->db->query("UPDATE t_employee SET id_directorate = ".$directorate.", id_department = null, id_section = null, prev = ".$previledge.", id_folderAccess = ".$folderAccess." WHERE id_employee = ".$id);      
        }elseif (($department != null || $department != 0) && ($section == null || $section == 0)) {
            return $this->db->query("UPDATE t_employee SET id_directorate = ".$directorate.", id_department = ".$department.", id_section = null, prev = ".$previledge.", id_folderAccess = ".$folderAccess." WHERE id_employee = ".$id);
        }elseif (($department != null || $department != 0) && ($section != null || $section != 0)) {
            return $this->db->query("UPDATE t_employee SET id_directorate = ".$directorate.", id_department = ".$department.", id_section = ".$section.", prev = ".$previledge.", id_folderAccess = ".$folderAccess." WHERE id_employee = ".$id);
        }
	}

	function deleteEmployee($id){
		return $this->db->query("DELETE FROM t_employee WHERE id_employee = ". $id);
	}

    function get_directorate(){
        $this->db->order_by('id_directorate', 'asc');
        return $this->db->get('t_directorate')->result();
    }

    function count_directorate(){
        return $this->db->get_where('t_directorate')->num_rows();
    }

    function get_department(){
        $this->db->order_by('id_department', 'asc');
        $this->db->join('t_directorate', 't_department.id_directorate = t_directorate.id_directorate');
        return $this->db->get('t_department')->result();
    }

    function get_section(){
        $this->db->order_by('id_section', 'asc');
        $this->db->join('t_department', 't_section.id_department = t_department.id_department');
        return $this->db->get('t_section')->result();
    }

    function addFolder($id_parent, $folderName){
         $data = array(
            'id_parent' => $id_parent,
            'folder' => $folderName
        );
        $this->db->insert('t_folder', $data);   
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

    function changeFolderName($id_folder, $folderNewName){
        return $this->db->query("UPDATE t_folder SET folder = '".$folderNewName."' WHERE id_folder = ".$id_folder);    
    }

    function deleteFolder($id){
        return $this->db->query("DELETE FROM t_folder WHERE id_folder = ". $id);
    }

    function deleteFolderRequest($id_folderRequest){
        return $this->db->query("DELETE FROM t_request WHERE id_request = ". $id_folderRequest);
    }

    function deleteFolderRequestConnection($id_folder){
        return $this->db->query("DELETE FROM t_request WHERE id_folder = ". $id_folder);
    }

    function acceptFolderRequest($id_folderRequest){
        $where=array(
            'id_request' => $id_folderRequest
        );
        $getDetailRequest = $this->db->get_where('t_request', $where)->result();
        foreach ($getDetailRequest as $key) {
            $request = $key->request;
            $folderNewName = $key->folderNewName;
            $id_folder = $key->id_folder;
        }
        if ($request == 1) {
            $this->deleteFolderRequest($id_folderRequest);
            $this->addFolder($id_folder, $folderNewName);
        } elseif ($request == 2) {
            $this->deleteFolderRequest($id_folderRequest);
            $this->changeFolderName($id_folder, $folderNewName);
        } elseif ($request == 3) {
            $this->deleteFolderRequest($id_folderRequest);
            $this->deleteFolderRequestConnection($id_folder);
            $this->deleteFolder($id_folder);
        }
    }
} 