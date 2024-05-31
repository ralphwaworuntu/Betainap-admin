<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Firebase_model extends CI_Model
{
    public function __construct()
    {
        $this->setupFirebaseOTP();
    }

    private function setupFirebaseOTP(){

        $otp = ConfigManager::getValue("OTP_METHOD");

        if($otp!="firebase"){
            return;
        }


    }


    public function verify($userId, $phone, $optCode)
    {



        if(RequestInput::post("token")!=""){

            $result = $this->getTokenOTP(RequestInput::post("token"));


            if(isset($result["error"])){
                return array(
                    Tags::SUCCESS=>0,
                    Tags::ERRORS=>[json_encode($result["error"])]
                );
            }

            $result = $this->getUserDataOTP($result['id_token']);

            if(isset($result['users'][0]['phoneNumber'])){
                return array(
                    Tags::SUCCESS=>1,
                    Tags::RESULT=>$result['users'][0]['phoneNumber']
                );
            }

        }else if(RequestInput::post("idToken")!=""){
            $result = $this->getUserDataOTP(RequestInput::post("idToken"));

            if(isset($result["error"])){
                return array(
                    Tags::SUCCESS=>0,
                    Tags::ERRORS=>[json_encode($result["error"])]
                );
            }

            if(isset($result['users'][0]['phoneNumber'])){
                return array(
                    Tags::SUCCESS=>1,
                    Tags::RESULT=>$result['users'][0]['phoneNumber']
                );
            }
        }

        return array(
            Tags::SUCCESS=>0
        );
    }


    private function getUserDataOTP($idToken){

        // Replace [API_KEY] and [FIREBASE_ID_TOKEN] with your actual values
        $apiKey = ConfigManager::getValue("OTP_CONFIG_firebase_apiKey");

        // Set the API URL
        $url = 'https://identitytoolkit.googleapis.com/v1/accounts:lookup?key=' . $apiKey;

        // Set the request data
        $data = array(
            'idToken' => $idToken
        );

        // Convert the data to JSON format
        $jsonData = json_encode($data);

        // Initialize cURL session
        $ch = curl_init($url);

        // Set cURL options
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

        // Execute cURL and store the response
        $response = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
            echo 'cURL error: ' . curl_error($ch);
        }

        // Close cURL handle
        curl_close($ch);

        // Output the response
        return json_decode($response,JSON_OBJECT_AS_ARRAY);
    }


    private function getTokenOTP($token){

        // Replace [API_KEY] and [REFRESH_TOKEN] with your actual values
        $apiKey = ConfigManager::getValue("OTP_CONFIG_firebase_apiKey");
        $refreshToken = $token;

        // Set the API URL
        $url = 'https://securetoken.googleapis.com/v1/token?key=' . $apiKey;

        // Create POST data
        $data = array(
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken
        );

        // Initialize cURL session
        $ch = curl_init($url);

        // Set cURL options
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        // Execute cURL and store the response
        $response = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
            echo 'cURL error: ' . curl_error($ch);
        }

        // Close cURL handle
        curl_close($ch);

        return json_decode($response,JSON_OBJECT_AS_ARRAY);

    }

}

