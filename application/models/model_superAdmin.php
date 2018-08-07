<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_superAdmin extends CI_Model {
    function getAdmin(){
        return $this->db->query("SELECT a.id_employee, a.first_name, a.last_name,a.prev,a.email, a.id_folderAccess,b.section, c.department, d.directorate FROM t_employee a left join t_section b on a.id_section = b.id_section left join t_department c on a.id_department=c.id_department left JOIN t_directorate d on a.id_directorate=d.id_directorate WHERE prev = 3")->result();   
    }

    function updateEmployee($id){
        $previledge = $this->input->post('previledge');
        $folderAccess = $this->input->post('folderAccess');
        
        return $this->db->query("UPDATE t_employee SET prev = ".$previledge.", id_folderAccess = ".$folderAccess." WHERE id_employee = ".$id);   
    }

    function deleteEmployee($id){
        return $this->db->query("DELETE FROM t_employee WHERE id_employee = ". $id);
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

    function addAdmin(){
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
            'id_folderAccess' => 4,
            'email' => $email,
            'phone_number' => $phone_number,
            'password' => $password,
            'prev' => 3,
        );

        $cekid = $this->db->get_where('t_employee',array('id_employee' => $employee_id))->num_rows();
        $cekemail = $this->db->get_where('t_employee',array('email' => $email))->num_rows();
        if ($cekid>0) {
            echo "<script>alert('Id $employee_id Sudah Digunakan');history.go(-1) </script>";
        } elseif ($cekemail>0) {
            echo "<script>alert('Email $email Sudah Digunakan');history.go(-1) </script>";
        } else {
            $this->db->insert('t_employee', $register);
        }
    }
}