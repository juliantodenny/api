<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	class ModelDoctor extends CI_Model {
		public function getListDoctor() {
			$sql = "select * from t_master_doctor_acuity";
			$query = $this->db->query($sql);
			if ($query->num_rows() > 0) {
				return $query->result();
			} else {
				return false;
			}
		}

        public function getDoctorByID($calendarID) {
			$sql = "select * from t_master_doctor_acuity where doctor_id = '".$calendarID."'";
			$query = $this->db->query($sql);
			if ($query->num_rows() > 0) {
				return $query->result();
			} else {
				return false;
			}
		}
	}
?>
