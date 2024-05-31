<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Otp_model extends CI_Model
{

    private  $method = "";
    private  $config = array();

    public function setup()
    {

        $this->method = ConfigManager::getValue('OTP_METHOD');
        $config = json_decode(ConfigManager::getValue('OTP_METHODS'),JSON_OBJECT_AS_ARRAY);
        $this->config = isset($config[$this->method])?$config[$this->method]:[];


        @$this->load->model('user/'.ucfirst($this->method).'_model','OTP_VerifyModel');
    }


    public function sendCodePhoneValidity($phone){ //send code

        $this->db->where('telephone',$phone);
        $this->db->where('hidden',0);
        $this->db->where('status !=' ,-1);
        $count = $this->db->count_all_results('user');

        if($count>0){
            return  array(Tags::SUCCESS=>0,Tags::ERRORS=>array("err"=>_lang("This phone number already linked with an account!")));
        }

        //check limit
        if(ConfigManager::getValue("OTP_TEST_ENABLED")){
            $opt_limit = 1;
        }else{
            $opt_limit =  intval(SessionManager::getValue( date('Y-m-d H',time()).'_'.$phone ,1));
        }

        if($opt_limit>=5){
            return array(Tags::SUCCESS=>0,Tags::ERRORS=>array('Err'=>'You have exceeded the limit of requests. try after 1 hour'));
        }

        $result = $this->OTP_VerifyModel->send(null,$phone);
        if($result[Tags::SUCCESS]==1){
            $opt_limit++;
            SessionManager::setValue(  date('Y-m-d H',time()).'_'.$phone   ,$opt_limit);
            $result['message'] = Translate::sprintf("You still have %s attempt",array( 5 - $opt_limit ));
        }


        return $result;
    }


    public function send($userId,$phone){ //send code


        if(trim($phone)=="" or strlen($phone)<8){
            return  array(Tags::SUCCESS=>0,Tags::ERRORS=>array("err"=>_lang("Your phone field is empty!")));
        }

        $this->db->where('telephone',$phone);
        $this->db->where('hidden',0);
        $this->db->where('phoneVerified',1);
        $this->db->where('status !=' ,-1);
        $count = $this->db->count_all_results('user');

        if($count==0){
            return  array(Tags::SUCCESS=>-1,Tags::ERRORS=>array("err"=>_lang("There is no user linked with this phone number, try to create new account")));
        }

        //check limit
        $opt_limit =  intval(SessionManager::getValue( date('Y-m-d H',time()).'_'.$phone ,1));

        if($opt_limit>=5){
            return array(Tags::SUCCESS=>0,Tags::ERRORS=>array('Err'=>'You have exceeded the limit of requests. try after 1 hour'));
        }


        $result = $this->OTP_VerifyModel->send($userId,$phone);
        if($result[Tags::SUCCESS]==1){
            $opt_limit++;
            SessionManager::setValue(  date('Y-m-d H',time()).'_'.$phone   ,$opt_limit);
            $result['message'] = Translate::sprintf("You still have %s attempt",array( 5 - $opt_limit ));
        }

        return $result;
    }

    public function checkPhoneValidity($phone,$optCode){ //verify the code
        return $this->OTP_VerifyModel->verify(null,$phone,$optCode);
    }


    public function verify($userId,$phone,$optCode){ //verify the code


        $result = $this->OTP_VerifyModel->verify($userId,$phone,$optCode);

        //skip if test mode enabled
        if(ConfigManager::getValue("OTP_TEST_ENABLED")){
            $result = array(Tags::SUCCESS=>1);
        }

        //display if there errors
        if($result[Tags::SUCCESS]==0){
            return $result;
        }

        //check user linking with phone number

        if($result[Tags::SUCCESS]==1 && ($userId==0 OR  $userId==null)){

            $this->db->where('telephone',$phone);
            $this->db->where('hidden',0);
            $this->db->where('status !=',-1);
            $user = $this->db->get('user',1);
            $user = $user->result_array();

            //update statis
            if(isset($user[0])){
                $result['userId'] = isset($user[0]['id_user']);
                $this->db->where('id_user',$user[0]['id_user']);
                $this->db->where('telephone',$phone);
                $this->db->update('user',array(
                    'phoneVerified' => 1
                ));
            }

        }else if($result[Tags::SUCCESS]==1){
            SessionManager::setValue('opt_limit_'.$phone,1);
        }


        $result = array(Tags::SUCCESS=>1);


        if($result[Tags::SUCCESS]==1){


            $this->db->where('telephone',$phone);
            $this->db->where('status !=',-1);
            $this->db->where('hidden',0);
            $user = $this->db->get('user',1);
            $user = $user->result_array();

            if(isset($user[0]['id_user'])){
                $user = $this->mUserModel->syncUser(array(
                    'user_id' => $user[0]['id_user']
                ));
                if(isset($user[Tags::RESULT]))
                    $result[Tags::RESULT] = $user[Tags::RESULT];
            }

        }

        return $result;
    }


}

