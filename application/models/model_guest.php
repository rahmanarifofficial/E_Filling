<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_guest extends CI_Model {

	function register(){
        $first_name = $this->input->post('first_name');
        $last_name = $this->input->post('last_name');
        $employee_id = $this->input->post('employee_id');
        $directorate = $this->input->post('directorate');
        $department = $this->input->post('department');
        $section = $this->input->post('section');
        $email = $this->input->post('email');
        $phone_number = $this->input->post('phone_number');
        $pass = $this->input->post('password');
        $password = md5($pass);
        $prev = 0;

        if (empty($department)) {
            $department = null;
        }

        if (empty($section)) {
            $section = null;
        }
        
        $register = array(
            'first_name' => $first_name,
            'last_name' => $last_name,
            'id_employee' => $employee_id,
            'id_directorate' => $directorate,
            'id_department' => $department,
            'id_section' => $section,
            'email' => $email,
            'phone_number' => $phone_number,
            'password' => $password,
            'prev' => $prev,
        );

        $cekid = $this->db->get_where('t_employee',array('id_employee' => $employee_id))->num_rows();
        $cekemail = $this->db->get_where('t_employee',array('email' => $email))->num_rows();
        if ($cekid>0) {
            echo "<script>alert('Id $employee_id Sudah Digunakan');history.go(-1) </script>";
        } elseif ($cekemail>0) {
            echo "<script>alert('Email $email Sudah Digunakan');history.go(-1) </script>";
        } else {
            $this->db->insert('t_employee', $register);
            redirect('controller_guest/toLogin');
        }
	}

    function login(){
        $employeeID = $this->input->post('employeeID');
        $pass = $this->input->post('password');
        $password = md5($pass);
        $where=array(
            'id_employee' => $employeeID,
            'password' => $password
        );

        $check=$this->db->get_where('t_employee',$where)->num_rows();
        if($check == 1){
            $retrieveData = $this->db->query("SELECT * FROM t_employee WHERE id_employee='$employeeID'")->result();
            foreach ($retrieveData as $key) {
                $prev = $key->prev;
                $id_employee = $key->id_employee;
                $first_name = $key->first_name;
                $last_name = $key->last_name;
                $id_directorate = $key->id_directorate;
                $id_department = $key->id_department;
                $id_section = $key->id_section;
                $folderAccess = $key->id_folderAccess;
            }
            $_SESSION["id_employee"] = $id_employee;
            $_SESSION["prev"] = $prev;
            $_SESSION["fullname"] = $first_name." ".$last_name;
            $_SESSION["directorate"] = $id_directorate;
            $_SESSION["department"] = $id_department;
            $_SESSION["section"] = $id_section;
            $_SESSION["idFolder"]= NULL;
            $_SESSION["folderAccess"] = $folderAccess;
            
            if($prev == 1){
                redirect('controller_public/index','refresh');
            }else if($prev == 2){
                redirect('controller_creator/index','refresh');
            }else if($prev == 3){
                redirect('controller_admin/index','refresh');
            }else if($prev == 4){
                redirect('controller_superAdmin/index','refresh');
            }else if($prev == 0){
                session_destroy();
                //Have not Approved
                redirect('controller_guest/toLogin','refresh');
            } else{
                session_destroy();
                //Error or Have not Approved
                redirect('controller_guest/toLogin','refresh');
            }
        }else{
            redirect('controller_guest/toLogin');
        }
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
}