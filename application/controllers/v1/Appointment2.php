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
    
    class Appointment2 extends REST_Controller {
    
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

                if ($dataPost->P_Kelamin == "Male" || $dataPost->P_Kelamin == "Laki-laki") {
                    $P_Kelamin = "M";
                } else {
                    $P_Kelamin = "F";
                }

                if (isset($dataPost->RM) && $dataPost->RM !== "") {
                    $dash = "-";
                    if (preg_match("/$dash/i", $dataPost->RM)) {
                        $RM = $dataPost->RM;
                    } else {
                        if (strlen($dataPost->RM) == "1" || strlen($dataPost->RM) == 1) {
                            $RM = "01-0000000".$dataPost->RM;
                        } else if (strlen($dataPost->RM) == "2" || strlen($dataPost->RM) == 2) {
                            $RM = "01-000000".$dataPost->RM;
                        } else if (strlen($dataPost->RM) == "3" || strlen($dataPost->RM) == 3) {
                            $RM = "01-00000".$dataPost->RM;
                        } else if (strlen($dataPost->RM) == "4" || strlen($dataPost->RM) == 4) {
                            $RM = "01-0000".$dataPost->RM;
                        } else if (strlen($dataPost->RM) == "5" || strlen($dataPost->RM) == 5) {
                            $RM = "01-000".$dataPost->RM;
                        } else if (strlen($dataPost->RM) == "6" || strlen($dataPost->RM) == 6) {
                            $RM = "01-00".$dataPost->RM;
                        } else if (strlen($dataPost->RM) == "7" || strlen($dataPost->RM) == 7) {
                            $RM = "01-0".$dataPost->RM;
                        } else {
                            $RM = "01-".$dataPost->RM;
                        }
                    }
                } else {
                    $RM = "";
                }

                if ($dataPost->P_Status_Nikah == "Belum Menikah" || $dataPost->P_Status_Nikah == "Lajang") {
                    $P_Status_Nikah = "1";
                } else if ($dataPost->P_Status_Nikah == "Sudah Menikah" || $dataPost->P_Status_Nikah == "Menikah") {
                    $P_Status_Nikah = "2";
                } else {
                    $P_Status_Nikah = "3";
                }

                if ($dataPost->P_Agama == "Islam") {
                    $P_Agama = "1";
                } else if ($dataPost->P_Agama == "Katolik" || $dataPost->P_Agama == "Kristen Katolik" || $dataPost->P_Agama == " Katholik") {
                    $P_Agama = "3";
                } else if ($dataPost->P_Agama == "Protestan" || $dataPost->P_Agama == "Kristen Protestan" || $dataPost->P_Agama == "Kristen") {
                    $P_Agama = "2";
                } else if ($dataPost->P_Agama == "Buddha" || $dataPost->P_Agama == "Budha") {
                    $P_Agama = "4";
                } else if ($dataPost->P_Agama == "Hindu") {
                    $P_Agama = "5";
                } else if ($dataPost->P_Agama == "Konghucu") {
                    $P_Agama = "6";
                } else if ($dataPost->P_Agama == "Kepercayaan") {
                    $P_Agama = "7";
                } else {
                    $P_Agama = "8";
                }

                if ($dataPost->P_Kewarganegaraan == "Indonesia" || $dataPost->P_Kewarganegaraan == "Warga Negara Indonesia" || $dataPost->P_Kewarganegaraan == "WNI") {
                    $P_Kewarganegaraan = "1";
                } else {
                    $P_Kewarganegaraan = "2";
                }

                if ($dataPost->B_Metode_Pembayaran == "Asuransi") {
                    $B_Metode_Pembayaran = "1";
                } else {
                    $B_Metode_Pembayaran = "2";
                }

                $appointmentID = $dataPost->appointmentID;
                $P_Tanggal_Lahir = $dataPost->P_Tanggal_Lahir;
    
                $getAppointmentID = $this->ModelAppointment->getAppointmentID($appointmentID);
                if ($getAppointmentID !== false) {
                    foreach ($getAppointmentID as $row) {
                        $getAppointmentID_db = $row->appointment_id;
                    }
                } else {
                    $getAppointmentID_db = "";
                }
    
                $getKodeBooking = $this->ModelAppointment->getKodeBooking($appointmentID);
                if ($getKodeBooking !== false) {
                    foreach ($getKodeBooking as $row) {
                        $kode_booking = $row->kode_booking;
                    }
                } else {
                    $kode_booking = "";
                }
    
                $getPeid = $this->ModelAppointment->getPeid($appointmentID);
                if ($getPeid !== false) {
                    foreach ($getPeid as $row) {
                        $peid = $row->peid;
                    }
                } else {
                    $peid = "";
                }
    
                if ($getAppointmentID_db == "") {
                    $this->set_response([
                        "status" => 404,
                        "message" => "Appointment ID not found"
                    ], REST_Controller::HTTP_NOT_FOUND);
                    return;
                } else if ($kode_booking == "") {
                    $this->set_response([
                        "status" => 404,
                        "message" => "Kode booking empty"
                    ], REST_Controller::HTTP_NOT_FOUND);
                    return;
                } else if ($peid == "") {
                    $this->set_response([
                        "status" => 404,
                        "message" => "PEID empty"
                    ], REST_Controller::HTTP_NOT_FOUND);
                    return;
                } else {
                    if (isset($RM) && $RM !== "") {
                        $checkRMPatient = $this->checkRM($rsid, $RM, $P_Tanggal_Lahir);
                        $checkRMPatient = json_decode($checkRMPatient);
    
                        $dataPost = array(
                            "rsid" => $rsid,
                            "peid" => $peid,
                            "asuransi" => $B_Metode_Pembayaran, 
                            "sex" => $P_Kelamin, 
                            "alamat" => $dataPost->A_Alamat, 
                            "birthplace" => $dataPost->P_Tempat_Lahir, 
                            "birthdate" => date("Y-m-d", strtotime($P_Tanggal_Lahir)), 
                            "noktp" => $dataPost->P_No_Identitas, 
                            "status" => $P_Status_Nikah, 
                            "agama" => $P_Agama, 
                            "warganegara" => $P_Kewarganegaraan, 
                            "kelurahan" => $dataPost->A_Kelurahan, 
                            "kecamatan" => $dataPost->A_Kecamatan, 
                            "kabupaten" => $dataPost->A_Kota, 
                            "provinsi" => "", 
                            "golongandarah" => "", 
                            "nama_keluarga" => $dataPost->D_Kontak, 
                            "hubungan_keluarga" => $dataPost->D_Hubungan, 
                            "hp_keluarga" => $dataPost->D_HP, 
                            "alamat_keluarga" => $dataPost->D_Alamat, 
                            "kecamatan_keluarga" => $dataPost->D_Kecamatan, 
                            "kabupaten_keluarga" => $dataPost->D_Kota, 
                            "provinsi_keluarga" => "" 
                        );
                        $dataPost = json_encode($dataPost);
        
                        $curl = curl_init();
                        curl_setopt_array($curl, array(
                            CURLOPT_URL => 'https://dev-api-webservice.teramobile.app/api/v1/appointmentstep2',
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
                                'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIzIiwianRpIjoiMWZjMjAxYzZlZWFhZTA1MWU4MjYwYmYxZDAxOGQyNmQzYWI5ZjRhMDZjMzA5NWJkNTU0OTg0NDhhMjFkOTZmMDgyYzY3NTExZTJhZGFkZDgiLCJpYXQiOjE2NTgxMjAzODcsIm5iZiI6MTY1ODEyMDM4NywiZXhwIjoxNjg5NjU2Mzg3LCJzdWIiOiIxNiIsInNjb3BlcyI6W119.jiqB8DHpXpuJtzkbvBQCiDMuc5wiC1sQUUXxHnK7yLHoaOmDe5Vg8RfWuLWBsrjEpjz6566up3J6FsSjjZCb6A'
                            ),
                        ));
        
                        $response = curl_exec($curl);
                        curl_close($curl);
                        $response = json_decode($response);

                        if ($response->{'message'} == "OK") {
                            $saveAddInfo = $this->ModelAppointment->saveAdditionalInfo($appointmentID, $RM, $dataPost);
                            if ($saveAddInfo == true) {
                                $this->set_response([
                                    "status" => 200,
                                    "message" => "Success save additional information to table"
                                ], REST_Controller::HTTP_OK);
                                return;
                            } else {
                                $this->set_response([
                                    "status" => 404,
                                    "message" => "Failed save additional infomation to table"
                                ], REST_Controller::HTTP_NOT_FOUND);
                                return;
                            }
                        } else {
                            $this->set_response([
                                "status" => 404,
                                "message" => "Failed post additional infomation"
                            ], REST_Controller::HTTP_NOT_FOUND);
                            return;
                        }
                    } else {
                        $dataPost = array(
                            "rsid" => $rsid,
                            "peid" => $peid,
                            "asuransi" => $B_Metode_Pembayaran, 
                            "sex" => $P_Kelamin, 
                            "alamat" => $A_Alamat, 
                            "birthplace" => $P_Tempat_Lahir, 
                            "birthdate" => date("Y-m-d", strtotime($P_Tanggal_Lahir)), 
                            "noktp" => $P_No_Identitas, 
                            "status" => $P_Status_Nikah, 
                            "agama" => $P_Agama, 
                            "warganegara" => $P_Kewarganegaraan, 
                            "kelurahan" => $A_Kelurahan, 
                            "kecamatan" => $A_Kecamatan, 
                            "kabupaten" => $A_Kota, 
                            "provinsi" => "", 
                            "golongandarah" => "", 
                            "nama_keluarga" => $D_Kontak, 
                            "hubungan_keluarga" => $D_Hubungan, 
                            "hp_keluarga" => $D_HP, 
                            "alamat_keluarga" => $D_Alamat, 
                            "kecamatan_keluarga" => $D_Kecamatan, 
                            "kabupaten_keluarga" => $D_Kota, 
                            "provinsi_keluarga" => "" 
                        );
                        $dataPost = json_encode($dataPost);
        
                        $curl = curl_init();
                        curl_setopt_array($curl, array(
                            CURLOPT_URL => 'https://dev-api-webservice.teramobile.app/api/v1/appointmentstep2',
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
                                'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIzIiwianRpIjoiMWZjMjAxYzZlZWFhZTA1MWU4MjYwYmYxZDAxOGQyNmQzYWI5ZjRhMDZjMzA5NWJkNTU0OTg0NDhhMjFkOTZmMDgyYzY3NTExZTJhZGFkZDgiLCJpYXQiOjE2NTgxMjAzODcsIm5iZiI6MTY1ODEyMDM4NywiZXhwIjoxNjg5NjU2Mzg3LCJzdWIiOiIxNiIsInNjb3BlcyI6W119.jiqB8DHpXpuJtzkbvBQCiDMuc5wiC1sQUUXxHnK7yLHoaOmDe5Vg8RfWuLWBsrjEpjz6566up3J6FsSjjZCb6A'
                            ),
                        ));
        
                        $response = curl_exec($curl);
                        curl_close($curl);
                        $response = json_decode($response);
    
                        if ($response->{'message'} == "OK") {
                            $saveAddInfo = $this->ModelAppointment->saveAdditionalInfo($appointmentID, $RM, $dataPost);
                            if ($saveAddInfo == true) {
                                $this->set_response([
                                    "status" => 200,
                                    "message" => "Success save additional information to table"
                                ], REST_Controller::HTTP_OK);
                                return;
                            } else {
                                $this->set_response([
                                    "status" => 404,
                                    "message" => "Failed save additional infomation to table"
                                ], REST_Controller::HTTP_NOT_FOUND);
                                return;
                            }
                        } else {
                            $this->set_response([
                                "status" => 404,
                                "message" => "Failed post additional infomation"
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
    
        public function checkRM($rsid, $RM, $P_Tanggal_Lahir) {
            $curl = curl_init();
            
            $dataPost = array(
                "requestid" => "verifikasi_rm", 
                "group" => true, 
                "rsid" => $rsid,
                "data" => array(
                    "pid" => $RM,
                    "birth_date" => $P_Tanggal_Lahir
                )
            );
            $dataPost = json_encode($dataPost);

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://dev-api-webservice.teramobile.app/api/v1/passing',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS =>$dataPost,
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIzIiwianRpIjoiZGMxZjc5NTY1ODIzYTMzMWE4N2E2MjkwMzA0NmE0YmQ1OTcwYzQ1YTM2ZGIwZDQzMDUzYTU0ZTYwNmEzM2ZlYTVmMjNkNmU3MTY3YmMxYTYiLCJpYXQiOjE2NTgxMjc2MTEsIm5iZiI6MTY1ODEyNzYxMSwiZXhwIjoxNjg5NjYzNjExLCJzdWIiOiIxNiIsInNjb3BlcyI6W119.CSusCi4rCycKzEgySXVk4ArRuP-7UExJoPUc6O-UpvR04-ITZrgnvIJ0g8Dz3U8JyngSvVVwmgtdH2DF6ryP1g'
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