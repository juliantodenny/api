<?php
    defined('BASEPATH') OR exit('No direct script access allowed');

    require APPPATH . '/libraries/REST_Controller.php';
    use Restserver\Libraries\REST_Controller;
    
    class IntPhone extends REST_Controller {
    
        function __construct($config = 'rest') {
            parent::__construct($config);
        }

        public function index_post() {
            $phone = $this->input->post("phone");
            $phone = str_replace(array(" ", "-"), "", $phone);
            $hp_int = str_replace("+", "", $phone);

            $response = [
                "message" => "OK",
                "data" => array([
                    "hp_local" => "",
                    "hp_int" => $hp_int,
                    "type" => "int"]
                )
            ];

            $this->set_response($response, REST_Controller::HTTP_OK);
        }
    
    
        
    }
?>