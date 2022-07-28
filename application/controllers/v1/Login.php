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
    
    class Login extends REST_Controller {
    
        function __construct($config = 'rest') {
            parent::__construct($config);
        }

        function configToken(){
            $cnf['exp'] = 3600; //milisecond
            $cnf['secretkey'] = '2212336221';
            return $cnf;        
        }

        public function getToken_post(){               
            $exp = time() + 3600;
            $token = array(
                "iss" => 'apprestservice',
                "aud" => 'pengguna',
                "iat" => time(),
                "nbf" => time() + 10,
                "exp" => $exp,
                "data" => array(
                    "username" => "cihoscorp",
                    "password" => "Cihos123!"
                )
            );       
        
            $jwt = JWT::encode($token, $this->configToken()['secretkey'], 'HS256');
            $output = [
                    'status' => 200,
                    'message' => 'OK',
                    "token" => $jwt,                
                    "expireAt" => $token['exp']
                ];      
            $data = array(
                'status' => '200', 
                'message' => 'OK', 
                'data' => array(
                    'token' => $jwt, 
                    'exp' => $exp
                )
            );
            $this->response($data, 200 );       
        }

        public function authtoken_post(){
            $secret_key = $this->configToken()['secretkey']; 
            $token = null; 
            $authHeader = $this->input->request_headers()['Authorization'];  
            $arr = explode(" ", $authHeader); 
            $token = $arr[1];   
            if ($token){
                try{
                    $decoded = JWT::decode($token, new Key($this->configToken()['secretkey'], 'HS256'));
                    if ($decoded){
                        echo 'benar';
                    }
                } catch (\Exception $e) {
                    $result = array('pesan'=>'Token tidak sesuai');
                    echo 'salah';
                }
            }       
        }
        
    }
?>