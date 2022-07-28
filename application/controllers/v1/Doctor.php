<?php
    defined('BASEPATH') OR exit('No direct script access allowed');

    require APPPATH . '/libraries/REST_Controller.php';
    use Restserver\Libraries\REST_Controller;
    
    class Doctor extends REST_Controller {
    
        function __construct($config = 'rest') {
            parent::__construct($config);
        }

        public function index_post() {
            $calendarID = $this->input->post("calendarID");
            $getDoctorByID = $this->ModelDoctor->getDoctorByID($calendarID);

            $doctor = NULL;
            if (!empty($getDoctorByID)) {
                foreach (json_decode($getDoctorByID, true) as $row) {
                    $doctor = $row;
                }
                
                $response = array(
                    "status" => 200,
                    "message" => "OK",
                    "data" => array(
                        "doctorID" => $doctor["doctor_id"],
                        "doctorName" => $doctor["doctor_name"],
                        "doctorEmail" => $doctor["doctor_email"],
                        "doctorReplyTo" => $doctor["doctor_reply_to"],
                        "doctorDescription" => $doctor["doctor_description"],
                        "doctorLocation" => $doctor["doctor_location"],
                        "doctorTimezone" => $doctor["doctor_timezone"],
                        "doctorPID" => $doctor["doctor_pid"]
                    )
                );
                $this->set_response($response, REST_Controller::HTTP_OK);
                return;
            } else {
                $this->set_response([
                    "status" => 404,
                    "message" => "Doctor could not be found"
                ], REST_Controller::HTTP_NOT_FOUND);
                return;
            }
        }

        public function index_get() {
            $listDoctor = $this->ModelDoctor->getListDoctor();
            $listDoctor = json_encode($listDoctor);
    
            $doctor_id = $this->get('calendarID');
    
            if ($doctor_id === NULL) {
                if ($listDoctor) {
                    $this->response($listDoctor, REST_Controller::HTTP_OK);
                } else {
                    $this->response([
                        'status' => FALSE,
                        'message' => 'No doctors were found'
                    ], REST_Controller::HTTP_NOT_FOUND);
                }
            } else {
                $doctor_id = (int) $doctor_id;
    
                if ($doctor_id <= 0) {
                    $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST);
                }
    
                $doctor = NULL;
    
                if (!empty($listDoctor)) {
                    foreach (json_decode($listDoctor, true) as $row) {
                        if ($row['doctor_id'] == $doctor_id) {
                            $doctor = $row;
                        }
                    }
                }
    
                if (!empty($doctor)) {
                    if ($doctor['doctor_pid'] == "") {
                        $this->set_response([
                            'status' => 404,
                            'message' => 'Doctor PID is empty'
                        ], REST_Controller::HTTP_NOT_FOUND);
                    } else {
                        $this->set_response($doctor, REST_Controller::HTTP_OK);
                    }
                } else {
                    $this->set_response([
                        'status' => FALSE,
                        'message' => 'Doctor could not be found'
                    ], REST_Controller::HTTP_NOT_FOUND);
                }
            }
        } 
    }
?>