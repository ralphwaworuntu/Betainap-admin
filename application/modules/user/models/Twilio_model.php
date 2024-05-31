<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Twilio_model extends CI_Model
{


    const CHANNEL="OTP_CONFIG_twilio_";


    public function send($userId, $phone)
    {

        $serviceId = ConfigManager::getValue(Twilio_model::CHANNEL."Service_ID");

        //Create service ID if needed
        if($serviceId==""){
            $result = $this->createServiceId();
            if($result[Tags::SUCCESS]==1){
                ConfigManager::setValue(Twilio_model::CHANNEL."Service_ID",$result[Tags::RESULT]);
            }else{
                return $result;
            }
        }


        $url = "https://verify.twilio.com/v2/Services/".ConfigManager::getValue(Twilio_model::CHANNEL."Service_ID")."/Verifications";

        // Replace these with your actual Twilio Account SID and Auth Token
        $accountSid = ConfigManager::getValue(self::CHANNEL."Account_SID");
        $authToken = ConfigManager::getValue(self::CHANNEL."Auth_Token");

        // Create a cURL handle
        $ch = curl_init($url);

        // Set the cURL options
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "$accountSid:$authToken");
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
            'To' => $phone,
            'Channel' => 'sms'
        )));

        // Execute cURL and store the response
        $response = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
            return array(Tags::SUCCESS => 0, Tags::ERRORS => ["err" => 'cURL error: ' . curl_error($ch)]);
        }

        // Close cURL handle
        curl_close($ch);

        $response = json_decode($response, JSON_OBJECT_AS_ARRAY);

        if(isset($response['sid'])
            && isset($response['status']) && $response['status']=="pending"){
            return array(Tags::SUCCESS => 1);
        }

        return array(Tags::SUCCESS => 0, Tags::ERRORS => array('Err' => 'Error#1'));
    }

    private function createServiceId(){

        // Replace these with your actual Twilio Account SID and Auth Token
        $accountSid = ConfigManager::getValue(self::CHANNEL."Account_SID");
        $authToken = ConfigManager::getValue(self::CHANNEL."Auth_Token");

        // Set up the Twilio API URL for creating a Verify Service
        $url = 'https://verify.twilio.com/v2/Services';

        // Set up the FriendlyName for the Verify Service
        $friendlyName = ConfigManager::getValue("APP_NAE").' Verify Service';

        // Create a cURL handle
        $ch = curl_init($url);

        // Set the cURL options
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "$accountSid:$authToken");
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('FriendlyName' => $friendlyName)));

        // Execute cURL and store the response
        $response = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
            return array(Tags::SUCCESS => 0, Tags::ERRORS => ["err" => 'cURL error: ' . curl_error($ch)]);
        }

        // Close cURL handle
        curl_close($ch);

        $response = json_decode($response, JSON_OBJECT_AS_ARRAY);

        if(isset($response['status']) && $response['status']==200){
            return array(Tags::SUCCESS=>1,Tags::RESULT=>$response['sid']);
        }


        $error = "<b>Twilio error</b>: <br>";

        foreach ($response as $key => $val){
            $error .= ''.$key.": ".$val.'<br>';
        }

        return array(Tags::SUCCESS=>0,Tags::ERRORS=>['err'=>$error]);

    }


    public function verify($userId, $phone, $optCode)
    {
        // Replace these with your actual Twilio Account SID and Auth Token
        $accountSid = ConfigManager::getValue(self::CHANNEL."Account_SID");
        $authToken = ConfigManager::getValue(self::CHANNEL."Auth_Token");

        // Replace this with the Verify Service SID
        $verifyServiceSid = ConfigManager::getValue(self::CHANNEL."Service_ID");



        // Set up the Twilio API URL for checking a verification code
        $url = "https://verify.twilio.com/v2/Services/$verifyServiceSid/VerificationCheck";

        // Create a cURL handle
        $ch = curl_init($url);

        // Set the cURL options
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "$accountSid:$authToken");
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
            'To' => $phone,
            'Code' => $optCode
        )));

        // Execute cURL and store the response
        $response = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
            return array(Tags::SUCCESS => 0, Tags::ERRORS => ["err" => 'cURL error: ' . curl_error($ch)]);
        }

        // Close cURL handle
        curl_close($ch);

        $response = json_decode($response, JSON_OBJECT_AS_ARRAY);

        if(isset($response['sid'])
            && isset($response['status']) && $response['status']=="approved"){
            return array(Tags::SUCCESS => 1);
        }

        return array(Tags::SUCCESS => 0, Tags::ERRORS => array('Err' => 'OTP failed'));
    }

}

