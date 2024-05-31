<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Nexmo_model extends CI_Model
{

    public function send($userId, $phone)
    {

        $phone = str_replace("+","",$phone);

        // Replace these with your actual Nexmo API Key and Secret
        $apiKey = ConfigManager::getValue("OTP_CONFIG_nexmo_API_Key");
        $apiSecret = ConfigManager::getValue("OTP_CONFIG_nexmo_API_Secret");

        // Replace this with the phone number and brand you want to use
        $brand = ConfigManager::getValue("OTP_CONFIG_nexmo_Brand_Name");
        $lang = Translate::getDefaultLangCode() . "-" . Translate::getDefaultLangCode();

        // Set up the Nexmo API URL
        $url = "https://api.nexmo.com/verify/json?lg" . $lang . "&api_key=$apiKey&api_secret=$apiSecret&number=$phone&brand=$brand";

        // Create a cURL handle
        $ch = curl_init($url);

        // Set the cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Execute cURL and store the response
        $response = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
            return array(Tags::SUCCESS => 0, Tags::ERRORS => ["err" => 'cURL error: ' . curl_error($ch)]);
        }

        $response = json_decode($response, JSON_OBJECT_AS_ARRAY);


        if (isset($response['status']) && $response['status'] == 0 && isset($response['request_id'])) {

            //save session
            SessionManager::setValue('opt_verification_nexmo_req_id', $response['request_id']);

            //Clear existing tokens
            TokenSetting::clearAll_Bytype("opt_verification_nexmo_req_id" . $phone);

            //save in the database
            TokenSetting::createToken(9999999912, "opt_verification_nexmo_req_id" . $phone, $response['request_id']);

            //return array(Tags::SUCCESS => 1, Tags::RESULT => $response['request_id']);
            return $response;
        }

        return array(Tags::SUCCESS => 0, Tags::ERRORS => array('Err' => $response['error_text']));
    }


    public function verify($userId, $phone, $optCode)
    {

        $phone = str_replace("+","",$phone);

        //verify the code
        $requestId = SessionManager::getValue('opt_verification_nexmo_req_id');
        if ($requestId == "") {
            //save in the database
            $obj = TokenSetting::getTokensByUserID(9999999912, "opt_verification_nexmo_req_id" . $phone);
            if (isset($obj[0])) {
                $requestId = $obj[0]->content;
            }
        }

        // Replace these with your actual Nexmo API Key and Secret
        $apiKey = ConfigManager::getValue("OTP_CONFIG_nexmo_API_Key");
        $apiSecret = ConfigManager::getValue("OTP_CONFIG_nexmo_API_Secret");

        // Set up the Nexmo API URL
        $url = "https://api.nexmo.com/verify/check/json?api_key=$apiKey&api_secret=$apiSecret&request_id=$requestId&code=$optCode";

        // Create a cURL handle
        $ch = curl_init($url);
        // Set the cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Execute cURL and store the response
        $response = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
            return array(Tags::SUCCESS => 0, Tags::ERRORS => ["err" => 'cURL error: ' . curl_error($ch)]);
        }

        $response = json_decode($response, JSON_OBJECT_AS_ARRAY);

        if (isset($response['status']) && $response['status']==0) {
            return array(Tags::SUCCESS => 1);
        }

        return array(Tags::SUCCESS => 0, Tags::ERRORS => array('Err' => $response['error_text']));
    }

}

