<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Msg91_model extends CI_Model
{

    public function send($userId, $phone)
    {

        $phone = str_replace("+","",$phone);

        // Set up the Nexmo API URL
        $url = "https://api.msg91.com/api/sendotp.php";

        // Create a cURL handle
        $ch = curl_init($url);

        //Clear existing tokens
        TokenSetting::clearAll_Bytype("opt_verification_msg91_req_id" . $phone);

        $otp = rand(0000,9999);
        $token = TokenSetting::createToken(999999981, "opt_verification_msg91_req_id" . $phone, $otp);

        // Set the cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
            'authkey' => ConfigManager::getValue("OTP_CONFIG_msg91_Auth_Key"),
            'mobile' => $phone,
            'message' => "You verification code is: ".$otp,
            'sender' =>  ConfigManager::getValue("OTP_CONFIG_msg91_SenderId"),
            'token' => $token,
            'otp' => $otp,
        )));


        // Execute cURL and store the response
        $response = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
            return array(Tags::SUCCESS => 0, Tags::ERRORS => ["err" => 'cURL error: ' . curl_error($ch)]);
        }

        $response = json_decode($response, JSON_OBJECT_AS_ARRAY);

        if(isset($response['type']) && $response['type']=="success"){
            return array(Tags::SUCCESS => 1,Tags::RESULT=>$token);
        }

        return array(Tags::SUCCESS => 0, Tags::ERRORS => array('Err' => $response['message']));
    }


    public function verify($userId, $phone, $optCode)
    {

        $phone = str_replace("+","",$phone);

        //save in the database
        $obj = TokenSetting::getTokensByUserID(999999981, "opt_verification_msg91_req_id" . $phone);

        if (isset($obj[0])) {
            $otp = $obj[0]->content;
        }

        if(!isset($otp))
            return array(Tags::SUCCESS => 0, Tags::ERRORS => array('Err' =>"Invalid otp"));

        if($otp != $optCode){
            return array(Tags::SUCCESS => 0, Tags::ERRORS => array('Err' =>"You entered invalid Code"));
        }

        // Set up the Nexmo API URL
        $url = "https://api.msg91.com/api/verifyRequestOTP.php";

        // Create a cURL handle
        $ch = curl_init($url);

        // Set the cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
            'authkey' => ConfigManager::getValue("OTP_CONFIG_msg91_Auth_Key"),
            'mobile' => $phone,
            'otp' => $optCode,
        )));

        // Execute cURL and store the response
        $response = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
            return array(Tags::SUCCESS => 0, Tags::ERRORS => ["err" => 'cURL error: ' . curl_error($ch)]);
        }

        $response = json_decode($response, JSON_OBJECT_AS_ARRAY);

        if(isset($response['type']) && $response['type']=="success"){
            return array(Tags::SUCCESS => 1,Tags::RESULT=>$response['message']);
        }

        return array(Tags::SUCCESS => 0, Tags::ERRORS => array('Err' => $response['message']));
    }

}

