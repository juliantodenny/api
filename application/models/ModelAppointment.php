<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	class ModelAppointment extends CI_Model {
		public function saveAcuityData($appointmentID, $rsid, $doctorPID, $fullName, $phone, $email, $notes, $datetime) {
			$sql = "insert into t_appointment_acuity (appointment_id, rs_id, calendar_id, fullname, phone, email, notes, datetime) 
                    values ('".$appointmentID."', '".$rsid."', '".$doctorPID."', '".$fullName."', '".$phone."', '".$email."', 
                    '".$notes."', '".$datetime."')";
			$query = $this->db->query($sql);
			if ($query) {
				return true;
			} else {
				return false;
			}
		}

        public function saveTeraData($appointmentID, $peid_tera, $kode_booking_tera, $nama_tera, $doctor_id_tera, $nama_dokter_tera, $did_tera, $poli_tera, $tgl_janji_tera, $rsid_tera, $nohp_tera, $queue_tera) {
			$sql = "insert into t_appointment_tera (appointment_id, peid, kode_booking, nama, doctor_id, nama_dokter, did, poli, 
                    tgl_janji, rsid, nohp, queue, appointment_status) values ('".$appointmentID."', '".$peid_tera."', 
                    '".$kode_booking_tera."', '".$nama_tera."', '".$doctor_id_tera."', '".$nama_dokter_tera."', '".$did_tera."', 
                    '".$poli_tera."', '".$tgl_janji_tera."',  '".$rsid_tera."',  '".$nohp_tera."',  '".$queue_tera."', '0')";
			$query = $this->db->query($sql);
			if ($query) {
				return true;
			} else {
				return false;
			}
		}

        public function getKodeBooking($appointmentID) {
            $sql = "select kode_booking from t_appointment_tera where appointment_id = '".$appointmentID."'";
            $query = $this->db->query($sql);
            if ($query->num_rows() > 0) {
                return $query->result();
            } else {
                return false;
            }
        }

        public function getPeid($appointmentID) {
            $sql = "select peid from t_appointment_tera where appointment_id = '".$appointmentID."'";
            $query = $this->db->query($sql);
            if ($query->num_rows() > 0) {
                return $query->result();
            } else {
                return false;
            }
        }

        public function getAppointmentID($appointmentID) {
            $sql = "select appointment_id from t_appointment_tera where appointment_id = '".$appointmentID."'";
            $query = $this->db->query($sql);
            if ($query->num_rows() > 0) {
                return $query->result();
            } else {
                return false;
            }
        }

        public function updateStatusApp($appointmentID) {
            $sql = "update t_appointment_tera set appointment_status = '1' where appointment_id = '".$appointmentID."'";
            $query = $this->db->query($sql);
			if ($query) {
				return true;
			} else {
				return false;
			}
        }

        public function saveAdditionalInfo($appointmentID, $RM, $data) {
            $data = json_decode($data);
			$sql = "insert into t_additional_information_appointment (appointment_id, asuransi, gender, alamat, birthplace, 
                    birthdate, noktp, status, agama, warganegara, kelurahan, kecamatan, kabupaten, provinsi, golongandarah, 
                    nama_keluarga, hubungan_keluarga, hp_keluarga, alamat_keluarga, kecamatan_keluarga, kabupaten_keluarga, 
                    provinsi_keluarga, mr_number) values ('".$appointmentID."', '".$data->{'asuransi'}."', '".$data->{'sex'}."', 
                    '".$data->{'alamat'}."', '".$data->{'birthplace'}."', '".$data->{'birthdate'}."', '".$data->{'noktp'}."', 
                    '".$data->{'status'}."', '".$data->{'agama'}."',  '".$data->{'warganegara'}."',  '".$data->{'kelurahan'}."',  
                    '".$data->{'kecamatan'}."', '".$data->{'kabupaten'}."', '".$data->{'provinsi'}."', 
                    '".$data->{'golongandarah'}."', '".$data->{'nama_keluarga'}."', '".$data->{'hubungan_keluarga'}."', 
                    '".$data->{'hp_keluarga'}."', '".$data->{'alamat_keluarga'}."', '".$data->{'kecamatan_keluarga'}."', 
                    '".$data->{'kabupaten_keluarga'}."', '".$data->{'provinsi_keluarga'}."', '".$RM."')";
			$query = $this->db->query($sql);
			if ($query) {
				return true;
			} else {
				return false;
			}
		}

        public function checkKodeBooking($kodeBooking) {
            $sql = "select appointment_id, rsid from t_appointment_tera where kode_booking = '".$kodeBooking."'";
            $query = $this->db->query($sql);
            if ($query->num_rows() > 0) {
                return $query->result();
            } else {
                return false;
            }
        }

        public function deleteAppointment($appointmentID, $rsid) {
            $sql = "update t_appointment_tera set appointment_status = '2' where appointment_id = '".$appointmentID."' 
                    and rsid = '".$rsid."'";
            $query = $this->db->query($sql);
            if ($query) {
				return true;
			} else {
				return false;
			}
        }
	}
?>
