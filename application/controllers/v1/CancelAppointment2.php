<?php
    defined('BASEPATH') OR exit('No direct script access allowed');
    require APPPATH . '/libraries/JWT.php';
    require APPPATH . '/libraries/Key.php';
    require APPPATH . '/libraries/ExpiredException.php';
    require APPPATH . '/libraries/BeforeValidException.php';
    require APPPATH . '/libraries/SignatureInvalidException.php';
    require APPPATH . '/libraries/JWK.php';

    require APPPATH . '/libraries/REST_Controller.php';
    use Restserver\Libraries\REST_Controller;
    use \Firebase\JWT\JWT;
    use Firebase\JWT\Key;
    use \Firebase\JWT\ExpiredException;
    
    class CancelAppointment2 extends REST_Controller {
    
        function __construct($config = 'rest') {
            parent::__construct($config);
        }

        public function index_post() {
            $checkToken = $this->authtoken();
            
            if ($checkToken['status'] == "200" || $checkToken['status'] == 200) {
                $dataPost = json_decode(file_get_contents('php://input'));
                $kodeBooking = $dataPost->kodeBooking;

                $checkKodeBooking = $this->ModelAppointment->checkKodeBooking($kodeBooking);
                if ($checkKodeBooking !== false) {
                    foreach ($checkKodeBooking as $row) {
                        $appointmentID = $row->appointment_id;
                        $rsid = $row->rsid;
                    }

                    $deleteAppointment = $this->ModelAppointment->deleteAppointment($appointmentID, $rsid);
                    if ($deleteAppointment == true) {
                        if ($rsid == "42") {
                            $rsid = "crt";
                        }
        
                        $response = array(
                            "status" => 200,
                            "message" => "success",
                            "data" => array(
                                "appointmentID" => $appointmentID,
                                "rsid" => $rsid
                            ) 
                        );
                        $this->set_response($response, REST_Controller::HTTP_OK);
                    } else {
                        $response = array(
                            "status" => 500,
                            "message" => "Failed to delete appointment"
                        );
                        $this->set_response($response, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
                    }
                } else {
                    $response = array(
                        "status" => 404,
                        "message" => "Appoinment ID not found",
                        "data" => array() 
                    );
                    $this->set_response($response, REST_Controller::HTTP_NOT_FOUND);
                    return;
                }
            } else {
                $this->set_response($checkToken, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
                return;
            }
        }

        public function authtoken(){
            $secret_key = "2212336221"; 
            $token = null; 
            $headers = getallheaders();
            if (array_key_exists('Authorization', $headers)) {
                $arr = explode(" ", $headers['Authorization']); 
                $token = $arr[1];   
                if ($token){
                    try {
                        $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));
                        if ($decoded){
                            $response = array(
                                "status" => 200,
                                "message" => "OK"
                            );
                            return $response;
                        }
                    } catch (\Exception $e) {
                        $response = array(
                            "status" => 500,
                            "message" => "Not authorization"
                        );
                        return $response;
                    }
                }   
            } else {
                $response = array(
                    "status" => 500,
                    "message" => "Not authorization"
                );
                return $response;
            }
        }
    }
?>