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
    
    class CancelAppointment extends REST_Controller {
    
        function __construct($config = 'rest') {
            parent::__construct($config);
        }

        public function index_post() {
            $checkToken = $this->authtoken();
            
            if ($checkToken['status'] == "200" || $checkToken['status'] == 200) {
                $dataPost = json_decode(file_get_contents('php://input'));

                if ($dataPost->rsid == "crt") {
                    $rsid = "42";
                }

                $appointmentID = $dataPost->appointmentID;

                $getKodeBooking = $this->ModelAppointment->getKodeBooking($appointmentID);
                if ($getKodeBooking !== false) {
                    foreach ($getKodeBooking as $row) {
                        $kode_booking = $row->kode_booking;
                    }
                } else {
                    $kode_booking = "";
                }

                if ($kode_booking == "") {
                    $this->set_response([
                        "status" => 404,
                        "message" => "Kode booking empty"
                    ], REST_Controller::HTTP_NOT_FOUND);
                    return;
                } else {
                    $postArr = array(
                        "group" => "true",
                        "rsid" => $rsid,
                        "kode_booking" => $kode_booking 
                    );
                    $dataPost = json_encode($postArr);

                    $curl = curl_init();
                    curl_setopt_array($curl, array(
                        CURLOPT_URL => 'https://dev-api-webservice.teramobile.app/api/v1/cancelappointment_bybookcode',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',
                        CURLOPT_POSTFIELDS => $dataPost,
                        CURLOPT_HTTPHEADER => array(
                            'Content-Type: application/json',
                            'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIzIiwianRpIjoiNzRjMGRiOGIwNDhmMjIwYWEzYjM0NjBmMTNiZjlmZmRlODI5NGEzMGNiNTk4ODRkOGU5ZjU4ZDY4MjQzMWMxYjE0NTYyOTYwNGYyNGE4YTAiLCJpYXQiOjE2NTc2MjAxOTAsIm5iZiI6MTY1NzYyMDE5MCwiZXhwIjoxNjg5MTU2MTkwLCJzdWIiOiIxNiIsInNjb3BlcyI6W119.q6-H8730EWXrBwsbvUXJOm9eeOszGjY4PauYXHrqdMEHmetLiW425l6tSJ_s8qLk4NS6YDQNXP3cXFc33EruJQ'
                        ),
                    ));

                    $response = curl_exec($curl);
                    curl_close($curl);

                    $response = json_decode($response);
                    if ($response->{'message'} == "Ok") {
                        $updateStatusApp = $this->ModelAppointment->updateStatusApp($appointmentID);
                        if ($updateStatusApp == true) {
                            $this->set_response([
                                "status" => 200,
                                "message" => $response->{'message'}
                            ], REST_Controller::HTTP_OK);
                            return;
                        } else {
                            $this->set_response([
                                "status" => 404,
                                "message" => "Failed to update status appointment"
                            ], REST_Controller::HTTP_NOT_FOUND);
                            return;
                        }
                    } else {
                        $this->set_response([
                            "status" => 404,
                            "message" => $response->{'message'}
                        ], REST_Controller::HTTP_NOT_FOUND);
                        return;
                    }
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