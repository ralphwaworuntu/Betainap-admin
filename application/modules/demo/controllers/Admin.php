<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


class Admin extends ADMIN_Controller
{

    public function __construct()
    {
        parent::__construct();
        //load models
    }


    public function settings()
    {

        $users_id = ConfigManager::getValue("default_demo_users");
        $users_id = json_decode($users_id,JSON_OBJECT_AS_ARRAY);

        $default_users = array();
        foreach ($users_id as $id){
            $result = $this->mUserModel->userDetail($id);
            if(isset($result[Tags::RESULT][0]))
                $default_users[] = $result[Tags::RESULT][0];
        }

        $user_id = intval(ConfigManager::getValue("default_demo_user"));
        $default_user = array();
        if($user_id > 0){
            $result = $this->mUserModel->userDetail($user_id);
            if(isset($result[Tags::RESULT][0]))
                $default_user[] = $result[Tags::RESULT][0];
        }

        $data = array(
            "default_users" => $default_users,
            "default_user" => $default_user,
        );

        $this->load->view(AdminPanel::TemplatePath."/include/header",$data);
        $this->load->view("demo/settings");
        $this->load->view(AdminPanel::TemplatePath."/include/footer");

    }


}
