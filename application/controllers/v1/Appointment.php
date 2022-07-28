<?php
    defined('BASEPATH') OR exit('No direct script access allowed');
    date_default_timezone_set("Asia/Jakarta");
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
    
    class Appointment extends REST_Controller {
    
        function __construct($config = 'rest') {
            parent::__construct($config);
        }

        public function index_post() {
            $date = date("Y-m-d");
            $time = date("H:i:s");

            $checkToken = $this->authtoken();
            
            if ($checkToken['status'] == "200" || $checkToken['status'] == 200) {
                $dataPost = json_decode(file_get_contents('php://input'));

                $rsid = $dataPost->rsid;
                $appointmentID = $dataPost->appointmentID;
                $calendarID = $dataPost->calendarID;
                $firstName = $dataPost->firstName;
                $lastName = $dataPost->lastName;
                $phone = $dataPost->phone;
                $email = $dataPost->email;
                $datetime = $dataPost->datetime;
                $notes = $dataPost->notes;
    
                $fullName = $firstName." ".$lastName;
                $exp_datetime = explode("T", $datetime);
                $tanggal = $exp_datetime[0];
                $jam = substr($exp_datetime[1], 0, -8);
    
                if ($tanggal == "") {
                    $this->set_response([
                        "status" => 500,
                        "message" => "Date can't be empty"
                    ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
                    return;
                } else if ($fullName == "") {
                    $this->set_response([
                        "status" => 500,
                        "message" => "Fullname can't be empty"
                    ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
                    return;
                } else if ($rsid == "") {
                    $this->set_response([
                        "status" => 500,
                        "message" => "RSID can't be empty"
                    ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
                    return;
                } else {
                    $dataPhoneNumber = $this->correctPhoneNumber($phone);
                    $phone = $dataPhoneNumber["phone"];
    
                    if ($phone == "") {
                        $this->set_response([
                            "status" => 500,
                            "message" => "Phone number can't be empty"
                        ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
                        return;
                    } else {
                        if ($dataPhoneNumber["message"] == "OK") {
                            $checkDoctorPID = $this->ModelDoctor->getDoctorByID($calendarID);
                            if ($checkDoctorPID !== false) {
                                foreach ($checkDoctorPID as $row) {
                                    $calendarID = $row->doctor_id;
                                    $doctorName = $row->doctor_name;
                                    $doctorPID = $row->doctor_pid;
                                }
                            } else {
                                $calendarID = "";
                                $doctorName = "";
                                $doctorPID = "";
                            }
        
                            if ($doctorPID == "" || $doctorPID == NULL || !$doctorPID) {
                                $this->set_response([
                                    "status" => 404,
                                    "message" => "Doctor PID not found"
                                ], REST_Controller::HTTP_NOT_FOUND);
                                return;
                            } else {
                                $datePost = $tanggal."T".$jam.":00";

                                $dataDSID = $this->getDSID($doctorPID, $datePost);
                                $dataDSID = json_decode($dataDSID);

                                if (isset($dataDSID->{'error'})) {
                                    $this->set_response([
                                        "status" => 404,
                                        "message" => "DSID not found"
                                    ], REST_Controller::HTTP_NOT_FOUND);
                                    return;
                                } else {
                                    foreach ($dataDSID->{'jadwal'} as $row) {
                                        $dsid = $row->{'dsid'};
                                    }
                                }

                                $postArr = array(
                                    "tanggal" => $tanggal,
                                    "pid" => $doctorPID,
                                    "dsid" => $dsid,
                                    "name" => $fullName,
                                    "hp" => $phone,
                                    "rsid" => "42",
                                    "time" => $jam,
                                    "notes" => $notes
                                );
                                $postArr = json_encode($postArr);
        
                                $curl = curl_init();
                                curl_setopt_array($curl, array(
                                    CURLOPT_URL => 'https://dev-api-webservice.teramobile.app/api/v1/appointmentstep1',
                                    CURLOPT_RETURNTRANSFER => true,
                                    CURLOPT_ENCODING => '',
                                    CURLOPT_MAXREDIRS => 10,
                                    CURLOPT_TIMEOUT => 0,
                                    CURLOPT_FOLLOWLOCATION => true,
                                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                    CURLOPT_CUSTOMREQUEST => 'POST',
                                    CURLOPT_POSTFIELDS => $postArr,
                                    CURLOPT_HTTPHEADER => array(
                                        'Content-Type: application/json',
                                        'Accept: application/json',
                                        'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIzIiwianRpIjoiNzRjMGRiOGIwNDhmMjIwYWEzYjM0NjBmMTNiZjlmZmRlODI5NGEzMGNiNTk4ODRkOGU5ZjU4ZDY4MjQzMWMxYjE0NTYyOTYwNGYyNGE4YTAiLCJpYXQiOjE2NTc2MjAxOTAsIm5iZiI6MTY1NzYyMDE5MCwiZXhwIjoxNjg5MTU2MTkwLCJzdWIiOiIxNiIsInNjb3BlcyI6W119.q6-H8730EWXrBwsbvUXJOm9eeOszGjY4PauYXHrqdMEHmetLiW425l6tSJ_s8qLk4NS6YDQNXP3cXFc33EruJQ'
                                    ),
                                ));
        
                                $response = curl_exec($curl);
                                curl_close($curl);
        
                                $response = json_decode($response);
                                $peid_tera = $response->{'data'}->{'peid'};
                                $kode_booking_tera = $response->{'data'}->{'kode_booking'};
                                $nama_tera = $response->{'data'}->{'nama'};
                                $doctor_id_tera = $response->{'data'}->{'doctor_id'};
                                $nama_dokter_tera = $response->{'data'}->{'nama_dokter'};
                                $did_tera = $response->{'data'}->{'did'};
                                $poli_tera = $response->{'data'}->{'poli'};
                                $tgl_janji_tera = $response->{'data'}->{'tgl_janji'};
                                $rsid_tera = $response->{'data'}->{'rsid'};
                                $nohp_tera = $response->{'data'}->{'nohp'};
                                $queue_tera = $response->{'data'}->{'queue'};
        
                                if ($response->{'message'} == "OK") {
                                    $saveAcuityData = $this->ModelAppointment->saveAcuityData($appointmentID, $rsid, $doctorPID, $fullName, $phone, $email, $notes, $datetime);
                                    if ($saveAcuityData == true) {
                                        $saveTeraData = $this->ModelAppointment->saveTeraData($appointmentID, $peid_tera, $kode_booking_tera, $nama_tera, $doctor_id_tera, $nama_dokter_tera, $did_tera, $poli_tera, $tgl_janji_tera, $rsid_tera, $nohp_tera, $queue_tera);
                                    
                                        if ($saveTeraData == true) {
                                            $this->set_response([
                                                "status" => 200,
                                                "message" => "OK"
                                            ], REST_Controller::HTTP_OK);
                                            return;
                                        } else {
                                            $this->set_response([
                                                "status" => 404,
                                                "message" => "Failed save data to tera table"
                                            ], REST_Controller::HTTP_NOT_FOUND);
                                            return;
                                        }
                                    } else {
                                        $this->set_response([
                                            "status" => 404,
                                            "message" => "Failed save data to acuity table"
                                        ], REST_Controller::HTTP_NOT_FOUND);
                                        return;
                                    }
                                }
                            }
                        } else {
                            $this->set_response([
                                "status" => 404,
                                "message" => "Failed change format phone number"
                            ], REST_Controller::HTTP_NOT_FOUND);
                            return;
                        }
                    }
    
                    
                }
            } else {
                $this->set_response($checkToken, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
                return;
            }
        }
    
        public function correctPhoneNumber($phone) {

            $urlLocalPhone = "http://127.0.0.1/api/v1/localphone";
            $phone = array(
                "phone" => $phone
            );
        
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $urlLocalPhone,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $phone,
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer {{access_token}}'
                ),
            ));

            $response = curl_exec($curl);
            curl_close($curl);

            foreach (json_decode($response, true) as $row) {
                $message = $row["message"];

                foreach ($row["data"] as $val) {
                    $phone = $val["hp_local"];
                    $type = $val["type"];
                }
            }

            return $data = array(
                "message" => $message,
                "phone" => $phone,
                "type" => $type
            );
        }

        public function getDSID($doctorPID, $datePost) {
            $postDSID = array(
                "pid" => $doctorPID, 
                "tanggal" => $datePost
            );
            $postDSID = json_encode($postDSID);

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://dev-api-webservice.teramobile.app/api/v1/schedulebyPidDate',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $postDSID,
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIzIiwianRpIjoiZDY4NGRkMjAwYjAzOThkYmNjNTcxYmM5Y2QzMDI3N2YwMDM4NTEwNWZiNDY2MTFhMTc4MjQ3MzgyNWI3N2YwZWI1ZTZlMDEzZTgzN2Q0MjgiLCJpYXQiOjE2NTgyOTk0MzMsIm5iZiI6MTY1ODI5OTQzMywiZXhwIjoxNjg5ODM1NDMzLCJzdWIiOiIxNiIsInNjb3BlcyI6W119.UF3JjP-DAhlTZszx3EipIaeR201yNpmNCLom0WpQwOQCJnfkNTLlTnd_SdB0Yyur1gJX75cl4G1_Ns7H99BA1g'
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            return $response;
        }

        public function checkDoctorPID($calendarID) {

            $urlListDoctor = "http://127.0.0.1/api/v1/doctor";

            $calendarID = array(
                "calendarID" => $calendarID
            );

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $urlListDoctor,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $calendarID,
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer {{access_token}}'
                ),
            ));

            $response = curl_exec($curl);
            curl_close($curl);

            return $response;
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