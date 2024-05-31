<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class User_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model("appcore/bundle", "mBundle");
    }

    public function resetUserSettingSubscribe($userId){


        $user_package = array();
        $user_subscribe_fields = UserSettingSubscribe::load();
        $app_config = $this->mConfigModel->getParams();

        foreach ($user_subscribe_fields as $field){

            if($field['_display']==0)
                continue;

            $key_field_name = $field['field_name'];
            $key_field_config = $field['config_key'];

            if(isset($app_config[$key_field_config])){
                $user_package[$key_field_name] = UserSettingSubscribe::parseToType($app_config[$key_field_config],$field['field_type']);
            }

        }

        $package_json = json_encode($user_package,JSON_FORCE_OBJECT);
        $user_package['user_settings_package'] = $package_json;

        $user_package['will_expired'] = date("Y-m-d H:i:s", time());
        $user_package['created_at'] = date("Y-m-d H:i:s", time());
        $user_package['created_at'] = MyDateUtils::convert($user_package['created_at'], TimeZoneManager::getTimeZone(), "UTC");


        if(isset($user_language) and $user_language!="")
            $user_package['user_language'] = $user_language;

        if(isset($user_timezone) and $user_timezone!="")
            $user_package['user_timezone'] = $user_timezone;


        $user_package['user_id'] = $userId;
        $this->db->where('user_id',$userId);
        $count = $this->db->count_all_results('user_subscribe_setting');


        if($count > 0){
            $this->db->where('user_id',$userId);
            $this->db->update("user_subscribe_setting", $user_package);
        }else{
            $this->db->insert("user_subscribe_setting", $user_package);
        }

        return TRUE;

    }

    public function findUserByToken($token,$type="BusinessLogged"){

        $object = TokenSetting::get_by_token($token,$type);

        if($object == NULL)
            return array(Tags::SUCCESS=>0);

        $user = $this->getUserData($object->uid);

        if($user == NULL)
            return array(Tags::SUCCESS=>0);

        $user['token'] = $token;

        return array(Tags::SUCCESS=>1,Tags::RESULT=>array($user));

    }

    public function loadLastUserActions($user_id){

        $loadedActions = array(
            "user" => $this->syncUser($user_id)
        );

        $loadedActions = ActionsManager::return_action("user", "funcLoadLastActions",$loadedActions);
        return array(Tags::SUCCESS=>1,Tags::RESULT=>$loadedActions);

    }

    /*
     * Generate unique qr code for each user
     */

    public function generateUniqueQRCode($user_id){

        $errors = array();

        $this->db->where('id_user',$user_id);
        $count = $this->db->count_all_results("user");

        if($count == 0)
            $errors[] = _lang("User not found!");


        if(empty($errors)){
            $token = TokenUserManager::generate($user_id);
            return array(Tags::SUCCESS=>1,Tags::RESULT=>$token);
        }


        return array(Tags::SUCCESS=>0,Tags::ERRORS=>array($errors));
    }

    /*
     * Get user verification with token
     */

    public function tokenValid($user_id,$token){

        $errors = array();

        $this->db->where('id_user',$user_id);
        $this->db->where('status',1);
        $count = $this->db->count_all_results("user");

        if($count == 0)
            $errors[] = _lang("User not found!");


        $result = TokenUserManager::isValidTokenAndUser($token,$user_id);

        if($result != NULL){

            $user = $this->syncUser(array("
                user_id"=>$user_id
            ));

            if(!empty($user))
                return array(Tags::SUCCESS=>1,Tags::RESULT=>$user);
            else
                $errors[] = _lang("User not found!");
        }

        return array(Tags::SUCCESS=>0,Tags::ERRORS=>array($errors));
    }

    public function getAdmin(){

        $this->db->order_by("id_user","asc");
        $user = $this->db->get("user",1);
        $user = $user->result_array();

        if(isset($user[0])){
            return $user[0];
        }

        return NULL;
    }

    public function findUserByEmail($email){

        $this->db->where("email",$email);
        $user = $this->db->get("user",1);
        $user = $user->result_array();

        if(isset($user[0])){
            return $user[0];
        }

        return NULL;
    }

    public function getAllActiveImages(){

        $result = array();

        $this->db->select('images,id_user');
        $users = $this->db->get('user');
        $users = $users->result();
        foreach ($users as $user){

            if(preg_match("#^([0-9]+)$#i",$user->images)){
                $images = array($user->images=>$user->images);
            }else{
                $images = json_decode($user->images,JSON_OBJECT_AS_ARRAY);
            }

            if(is_array($images) && count($images)>0){
                foreach ($images as $image){
                    $result[] = $image;
                }
            }

        }

        return $result;
    }

    public function delete($user_id){


        $this->db->where("id_user", $user_id);
        $this->db->update("user",array(
            'hidden' => 1
        ));

        //remove linked tokens
        TokenSetting::clearAll_byUserID($user_id);

        return TRUE;
    }

    public function updateAccountPassword($params = array())
    {

        $data = array();
        $errors = array();

        extract($params);


        if (isset($user_id) && $user_id > 0) {
            $data['id_user'] = intval($user_id);
        }else{
            $errors['id_user'] = Translate::sprint("User ID is not valid");
        }

        if (isset($username) && $username != "") {
            if (preg_match("#^[a-zA-Z0-9\-_." . REGEX_FR . "]+$#i", $username)) {
                $data['username'] = Text::input($username);
            } else {
                $errors['username'] = Translate::sprint(Messages::USER_NAME_INVALID);
            }
        } else {
            $errors['username'] = Translate::sprint(Messages::USER_NAME_EMPTY);
        }

        if (isset($current_password) && $current_password != "") {
            if (strlen($current_password) >= 6) {
                $data['password'] = Security::cryptPassword($current_password);
            } else {
                $errors['password'] = Translate::sprint(Messages::PASSWORD_FORMAT);
            }
        }else{
            $errors['password'] = Translate::sprint(Messages::USER_PASSWORD_EMPTY);
        }



        if(empty($errors)){

            print_r($data);
            $this->db->where($data);
            $count = $this->db->count_all_results("user");

            if($count == 0){
                $errors['password'] = Translate::sprint(Messages::LOGIN_PASSWORD_NOT_VALID).json_encode($data);
            }
        }


        if(empty($errors)){

            if (isset($new_password) && $new_password != "") {
                if (strlen($new_password) < 6) {
                    $errors['password'] = Translate::sprint(Messages::PASSWORD_FORMAT);
                }
            }else{
                $errors['password'] = Translate::sprint(Messages::USER_PASSWORD_EMPTY);
            }



            if (isset($confirm_password) && $confirm_password != "") {
                if (strlen($confirm_password) < 6) {
                    $errors['password'] = Translate::sprint(Messages::PASSWORD_FORMAT);
                }
            }else{
                $errors['password'] = Translate::sprint(Messages::USER_PASSWORD_EMPTY);
            }
        }



        if(empty($errors)){
            if($new_password != $confirm_password){
                $errors['password'] = Translate::sprint("Your passwords don't match");
            }else{
                $__password = Security::cryptPassword($new_password);
            }
        }



        if(empty($errors)){

            $this->db->where($data);

            $this->db->update('user',array(
                "password" =>  $__password
            ));

            $this->db->where("user.id_user", $data['id_user']);
            $this->db->from('user');
            $userData = $this->db->get();

            $userData = $userData->result_array();

            $this->load->model("appcore/bundle");
            $userData = $this->bundle->prepareData($userData);


            return array(Tags::SUCCESS => 1, Tags::RESULT => $userData);

        }

        return array(Tags::SUCCESS => 0, Tags::ERRORS=> $errors);
    }

    public function createDefaultAdmin($login,$password,$email,$name,$timezone="UTC"){

        $c = $this->db->count_all_results("user");

        if($c == 0){

            $grp = $this->generateDefaultGrpAcc();

            $this->db->insert("user",array(
                "name"  => trim($name),
                "username"=> $login,
                "manager"=> 1,
                "email" => $email,
                "password"=> Security::cryptPassword($password),
                "status"=> 1,
                "confirmed"=> 1,
                "grp_access_id" => $grp->id
            ));

            $user_id = $this->db->insert_id();

            $package =array(
                "user_id"   =>$user_id,
            );


            $fields = UserSettingSubscribe::load();

            foreach ($fields as $field){
                $package[ $field['field_name'] ] = $field['field_default_value'];
            }

            //$package['package'] = json_encode($package);

            $pkg = array();

            foreach ($fields as $field){
                if($field['_display'] == 1)
                    $pkg[ $field['field_name'] ] = $field['field_default_value'];
            }

            $package['user_settings_package'] = json_encode($pkg,JSON_FORCE_OBJECT);

            $this->db->insert("user_subscribe_setting",$package);

            $appLogo = _openDir(APP_LOGO);
            $imageUrl = "";
            if(!empty($appLogo)){
                $imageUrl = $appLogo['200_200']['url'];
            }

            //send mail verification
            $messageText = Text::textParser(array(
                "name" =>trim($name),
                "appName" =>trim(APP_NAME),
                "imageUrl"  => $imageUrl,
                "login"  => $login,
                "password"  => $password,
                "panelUrl"  => admin_url(""),
            ),"setupsuccess");


            $mail = new DTMailer();
            $mail->setRecipient($email);
            $mail->setFrom(DEFAULT_EMAIL);
            $mail->setFrom_name(APP_NAME);
            $mail->setMessage($messageText);
            $mail->setReplay_to(DEFAULT_EMAIL);
            $mail->setReplay_to_name(APP_NAME);
            $mail->setType("html");
            $mail->setSubject(Translate::sprint("Setup Successful!"));
            $mail->send();

            @$this->mConfigModel->save("TIME_ZONE",$timezone);



        }

        $this->db->where('manager',1);
        $users = $this->db->get('user',1);
        $users = $users->result();


        if(isset($users[0])){
            return $users[0];
        }

        return NULL;

    }

    public function generateDefaultGrpAcc(){
        return $this->mGroupAccessModel->generate_group_access('SuperAdmin');
    }



    public function getGuestIDByUserId($id)
    {

        if(intval($id)==0)
            return array();

        $this->db->select("guest_id");
        $this->db->from("user");
        $this->db->where("id_user",$id);

        $guest_data = $this->db->get();
        $guest_data = $guest_data->result();

        if(isset($guest_data[0]))
            return $guest_data[0]->guest_id;
        else
            return 0;

    }

    public function getGuestData($id)
    {

        if(intval($id)==0)
            return array();

        $this->db->select("guest.*");
        $this->db->from("guest");
        $this->db->where("id",$id);

        $guest_data = $this->db->get();
        $guest_data = $guest_data->result_array();

        if(isset($guest_data[0]))
            return $guest_data[0];
        else
            return array();

    }

    public function getUserData($id)
    {

        $this->load->model("bundle/bundle");

        $this->db->select("user.*,user_subscribe_setting.*");
        $this->db->from("user");
        $this->db->join("user_subscribe_setting", "user_subscribe_setting.user_id=user.id_user", "left outer");
        $this->db->where("id_user",$id);
        $this->db->select("user.*");

        $user_data = $this->db->get();
        $user_data = $user_data->result_array();
        $user_data = $this->bundle->prepareData($user_data);

        if(isset($user_data[0]))
             return $user_data[0];
        else
            return array();

    }


    public function getUserDataByUsername($username)
    {

        $this->db->where("username",$username);
        $user_data = $this->db->get('user');
        $user_data = $user_data->result_array();

        if(isset($user_data[0]))
            return $user_data[0];
        else
            return NULL;

    }

    public function getUserDataHashId($hashId)
    {
        $this->db->where("hash_id",$hashId);
        $user_data = $this->db->get('user');
        $user_data = $user_data->result_array();

        if(isset($user_data[0]))
            return $user_data[0];
        else
            return NULL;

    }


    public function getUserByGuestId($guest_id)
    {

        $this->db->select("id_user");
        $this->db->where("guest_id", $guest_id);
        $u = $this->db->get("user", 1);
        $u = $u->result_array();

        if (count($u) > 0) {

            return $this->syncUser(array(
                "user_id" => $u[0]['id_user']
            ));

        }

        return NULL;
    }

    public function getFCM($user_id)
    {

        $this->db->select("guest_id");
        $this->db->where("user_id", $user_id);
        $guests = $this->db->get("user_guest");
        $guests = $guests->result_array();

        $fcmList = array();
        foreach ($guests as $ug){
            $this->db->select("fcm_id,platform");
            $this->db->where("id", $ug['guest_id']);
            $fcm = $this->db->get("guest");
            $fcm = $fcm->result_array();
            if (count($fcm) > 0) {
                $fcmList[] = array(
                    "fcm"       => $fcm[0]["fcm_id"],
                    "platform"  => $fcm[0]["platform"],
                );
            }
        }


        return $fcmList;
    }

    public function updatePhotosProfile($params = array())
    {

        $data = array();
        $errors = array();

        extract($params);

        if (isset($image) && $image != "") {
            $data['images'] = json_encode(array($image), JSON_FORCE_OBJECT);
        }

        if (isset($user_id) and $user_id > 0 and isset($data['images'])) {

            $this->db->where("id_user", $user_id);
            $this->db->update("user", $data);

            $this->db->where("user.id_user", $user_id);
            $this->db->from('user');
            $userData = $this->db->get();

            $userData = $userData->result_array();

            $this->load->model("bundle/bundle");
            $userData = $this->bundle->prepareData($userData);

            return (array(Tags::SUCCESS => 1, Tags::RESULT => $userData));
        }

        return (array(Tags::SUCCESS => 0));

    }


    public function getUserNameById($uid)
    {
        $this->db->select("username");
        $this->db->where("id_user", $uid);
        $user = $this->db->get("user", 1);
        $user = $user->result();
        if (count($user) > 0)
            return $user[0]->username;

        return;
    }

    public function getFieldById($field , $uid)
    {
        $this->db->select($field);
        $this->db->where("id_user", $uid);
        $this->db->join("user_subscribe_setting", "user_subscribe_setting.user_id=user.id_user");
        $this->db->select("user.*, user_subscribe_setting.*");
        $user = $this->db->get("user", 1);
        $user = $user->result();
        if (count($user) > 0)
            return $user[0]->{$field};

        return;
    }

    public function getUserIDByUsername($username)
    {

        $this->db->select("id_user");
        $this->db->where("username", $username);
        $user = $this->db->get("user", 1);
        $user = $user->result();
        if (count($user) > 0)
            return $user[0]->id_user;

        return 0;
    }




    public function getCurrentLimits($uid)
    {

        $this->db->where("user_id", $uid);
        $user = $this->db->get("user_subscribe_setting", 1);
        $user = $user->result();


    }


    public function refreshPackage($uid)
    {


        if(ModulesChecker::isEnabled("pack")){
            $this->load->model("pack/pack_model");
            $this->mPack->refreshPackage($uid);
        }else{
            $this->db->select("updated_at,package");
            $this->db->where("user_id", $uid);
            $user = $this->db->get("user_subscribe_setting", 1);
            $user = $user->result();

            if (count($user) > 0) {
                $date = $user[0]->updated_at;
                $date = date("Y-m", strtotime($date));
                $currentdate = date('Y-m', time());

                if ($currentdate != $date) {
                    $pack = $user[0]->package;
                    $pack = json_decode($pack, JSON_OBJECT_AS_ARRAY);
                    $pack['updated_at'] = date("Y-m-d", time());

                    $this->db->where("user_id", $uid);
                    $this->db->update("user_subscribe_setting", $pack);
                }
            }
        }

        throw new Exception('refreshPackage called');
        exit();

    }

    public function mailVerification($token)
    {

        if (Text::tokenIsValid($token)) {

            $this->db->where("id", $token);
            $this->db->where("type", "confirm");
            $v = $this->db->get("token", 1);
            $v = $v->result();

            if (count($v) > 0) {

                $this->db->where("id_user", $v[0]->uid);
                $this->db->update("user", array(
                    "confirmed" => 1
                ));

                $this->db->where("id", $token);
                $this->db->delete("token");


                return $v[0]->uid;

            }

        }


        return FALSE;
    }

    public function resetPassword($params = array())
    {

        extract($params);
        $errors = array();
        $data = array();

        if (isset($token) and isset($password) and isset($confirm)) {

            if (Text::tokenIsValid($token)) {

                if (strlen($password) >= 6) {

                    if (Text::compareToStrings($password, $confirm)) {

                        $this->db->where("id", $token);
                        $this->db->where("type", "newpassword");
                        $v = $this->db->get("token", 1);
                        $v = $v->result();

                        if (count($v) > 0) {

                            $this->db->where("id_user", $v[0]->uid);
                            $this->db->update("user", array(
                                "password" => Security::cryptPassword($password)
                            ));


                            $this->db->where("id", $token);
                            $this->db->delete("token");

                            return array(Tags::SUCCESS => 1);

                        } else {

                            $errors['token'] = Translate::sprint(Messages::TOKEN_NOT_VALID);
                        }

                    } else {
                        $errors['password'] = Translate::sprint(Messages::USER_PASSWORD_INVALID);
                    }
                } else {
                    $errors['password'] = Translate::sprint(Messages::PASSWORD_FORMAT);
                }

            } else {
                $errors['token'] = Translate::sprint(Messages::TOKEN_NOT_VALID);
            }


        } else {
            $errors['reset'] = Translate::sprint(Messages::RESET_ERROR);
        }


        return array(Tags::SUCCESS => 0, Tags::ERRORS => $errors);
    }

    public function sendNewPassword($params = array(),$callback="")
    {

        //params login password mac_address
        $errors = array();
        $data = array();

        //extract — Importe les variables dans la table des symboles
        extract($params);

        if (isset($login) AND $login != "") {

            if (Text::checkEmailFields($login)) {

                $data["email"] = strtolower(trim($login));

            } else if (Text::checkUsernameValidate($login)) {

                $data["username"] = Text::input($login);

            } else {

                $errors['login'] = Translate::sprint(Messages::USER_LOGIN_INVALID);
            }

        } else {
            $errors['login'] = Translate::sprint(Messages::USER_NAME_EMPTY);
        }


        $this->db->where($data);
        $user = $this->db->get("user");
        $user = $user->result();


        if (count($user) == 0) {
            $errors['login'] = Translate::sprint(Messages::LOGIN_NOT_EXIST_OR_LIMIT_EXCEEDED);
        }

        if (empty($errors)) {

            //generate new random password Q23qo5
            $token = md5(time() . rand(0, 999));

            $this->db->insert('token', array(
                "id" => $token,
                "uid" => $user[0]->id_user,
                "type" => "newpassword",
                "created_at" => date("Y-m-d", time())
            ));


            $appLogo = _openDir(APP_LOGO);
            $imageUrl = "";
            if (!empty($appLogo)) {
                $imageUrl = $appLogo['200_200']['url'];
            }

            if($callback != ""){
                $callback = $callback."?recover=$token";
            }else{
                $callback = site_url("user/rpassword?recover=$token");
            }

            $messageText = Text::textParser(array(
                "name" => $user[0]->name,
                "url" => $callback,
                "imageUrl" => $imageUrl,
                "email" => DEFAULT_EMAIL,
                "appName" => APP_NAME,
            ), "passwordforgot");

            $messageText = ($messageText);

            $mail = new DTMailer();
            $mail->setRecipient($user[0]->email);
            $mail->setFrom(DEFAULT_EMAIL);
            $mail->setFrom_name(APP_NAME);
            $mail->setMessage($messageText);
            $mail->setReplay_to(NOREPLY_EMAIL);
            $mail->setReplay_to_name(APP_NAME);
            $mail->setType("html");
            $mail->setSubject(_lang("New Password"));


            if($mail->send())
                return array(Tags::SUCCESS => 1);
            else
                return array(Tags::SUCCESS => 0);


        }


        return array(Tags::SUCCESS => 0, Tags::ERRORS => $errors);
    }


    public function refreshPosition($params = array())
    {

        $data = array();
        $errors = array();
        extract($params);

        if (!isset($guest_id) OR $guest_id == 0) {
            $errors[] = _lang("Invalid guest_id");
        }

        if(!empty($errors)){
            return (array(Tags::SUCCESS=>0,Tags::ERRORS=>$errors));
        }


        $this->db->where("id", $guest_id);
        $count = $this->db->count_all_results("guest");

        if($count == 0){
            return (array(Tags::SUCCESS=>0,Tags::ERRORS=>array("err","guest not found")));;
        }

        if (isset($lng) AND isset($lat) AND $lat != 0 AND $lng != 0) {
            $data["lat"] = $lat;
            $data["lng"] = $lng;
        }

        $this->db->where("id", $guest_id);
        $this->db->update("guest", $data);


        if(isset($params['userId']) && $params['userId'] > 0){
            $this->manageGuest($guest_id,intval($params['userId']));
        }

        return array(Tags::SUCCESS => 1);

    }

    public function createNewGuest($params = array())
    {

        extract($params);
        $errors = array();
        $data = array();


        if (isset($fcm_id) AND $fcm_id != "") {
            $data['fcm_id'] = $fcm_id;
        }

        if (isset($sender_id) AND $sender_id != "") {
            $data['sender_id'] = $sender_id;

        } else {
            $errors['error'] = Translate::sprint(Messages::ERROR_SENDER_ID);
        }


        if (isset($lat) AND $lat != "") {
            $data['lat'] = $lat;
        }

        if (isset($lng) AND $lng != "") {
            $data['lng'] = $lng;
        }


        if (isset($platform) AND $platform != "") {
            $data['platform'] = $platform;
        }


        if (empty($errors)) {


            $this->db->where("sender_id", $sender_id);
            $guest = $this->db->get("guest", 1);
            $guest = $guest->result();

            $data['last_activity'] = date("Y-m-d", time());


            if (count($guest) == 0) {

                $this->db->insert("guest", $data);
                $id = $this->db->insert_id();

            } else {

                $this->db->where("id", $guest[0]->id);
                $this->db->update("guest", $data);
                $id = $guest[0]->id;

            }

            $this->db->where("id", $id);
            $guest = $this->db->get("guest", 1);
            $guest = $guest->result_array();

            return array(Tags::SUCCESS => 1, Tags::RESULT => $guest);
        }

        return json_encode(array(Tags::SUCCESS => 0, Tags::ERRORS => $errors));
    }


    public function generateToken($params = array())
    {
        $errors = array();
        $data = array();

        //extract — Importe les variables dans la table des symboles
        extract($params);

        if (
        (isset($mac_adr) and Security::checkMacAddress($mac_adr))
            /* AND
             (isset($ip_adr) and Security::checkIpAddress($ip_adr)) */
        ) {


            $mac_adr = trim($mac_adr);
            //GENERATE NEW TOKEN TO USING APPLICATION
            $token = md5($mac_adr . "_" . APP_KEY . "_" . time());

            /* $this->db->where("_device_id",trim($mac_adr));
             $this->db->delete("token");*/
            if(!isset($ip_adr))
                $ip_adr = "000.00.00.00";

            $this->db->insert("token", array(
                "_id" => $token,
                "_device_id" => $mac_adr,
                "_ip_adr" => $ip_adr
            ));


            $data = $this->db->get("option");
            $data = $data->result_array();


            return array("success" => 1, "token" => $token, "data" => $data);

        }

        /*if(isset($token) and $token!="" and preg_match("#^[a-z0-9]+$#i", $token)){

        }*/

        return array("success" => 0, "error" => "");
    }


    public function checkUser($params = array())
    {

        $errors = array();
        $data = array();

        //extract — Importe les variables dans la table des symboles
        extract($params);


        if (isset($user_id) and $user_id > 0) {
            $this->db->where("id_user", $user_id);
            $count = $this->db->count_all_results("user");

            if ($count == 1) {
                return array(Tags::SUCCESS => 1);
            }
        }

        return array(Tags::SUCCESS => 0);
    }

    public function forceSignInById($user_id){
        return $this->signIn(array(),$user_id);
    }

    public function signIn($params = array(),$forced_user_id=0)
    {

        //params login password mac_address
        $errors = array();
        $data = array();

        //extract — Importe les variables dans la table des symboles
        extract($params);

        if (isset($login) AND $login != "") {

            if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
                $data["user.email"] = strtolower($login);
            } else if (preg_match("#^[a-zA-Z0-9\-_." . REGEX_FR . "]+$#i", $login)) {
                $data["user.username"] = strtolower(Text::input($login));
            } else {
                $errors['login'] = Translate::sprint(Messages::USER_LOGIN_INVALID);
            }

        } else {
            $errors['login'] = Translate::sprint(Messages::USER_LOGIN_EMPTY);
        }


        if (isset($password) AND $password != "") {
            $data["user.password"] = Security::cryptPassword($password);
        } else {
            $errors['user.password'] = Translate::sprint(Messages::USER_PASSWORD_EMPTY);
        }


        //force signin by an ID

        if($forced_user_id>0){
            $errors = array();
            $data['id_user'] = $forced_user_id;
        }


        if (empty($errors)) {


            //Gel all users detail and subscribe setting infos
            $this->db->select('user.*,user.status as user_status, user_subscribe_setting.*');
            $this->db->where($data);
            $this->db->where("hidden",0);
            $this->db->join('user_subscribe_setting','user_subscribe_setting.user_id=user.id_user',"left");

            $user = $this->db->get("user",1);


            if ($user->num_rows() == 0) {
                return array(Tags::SUCCESS => 0, "errors" => array("err"=>Translate::sprint(Messages::LOGIN_PASSWORD_NOT_VALID)));
            }

            $user_data = $user->result_array();

            if ($user_data[0]['user_status'] <= -1) {
                $errors['connect'] = Translate::sprint(Messages::USER_DISABLED_ACCOUNT);
                return array(Tags::SUCCESS => 0, "errors" => $errors);
            }

            if (isset($lat) AND isset($lng))
                $this->changeLocation($lat, $lng, $user_data[0]['id_user']);


            //update guest and user
            if (isset($guest_id) and $guest_id > 0) {
                $this->manageGuest($guest_id, $user_data[0]['id_user']);
            }


            $user_setting = array();

            if(isset($user_language) and $user_language!="")
                $user_setting['user_language'] = $user_language;

            if(isset($user_timezone) and $user_timezone!="")
                $user_setting['user_timezone'] = $user_timezone;


            if(!empty($user_setting)){
                $this->db->where('user_id',$user_data[0]['id_user']);
                $this->db->update('user_subscribe_setting',$user_setting);
            }

            $this->load->model("appcore/bundle");
            $user_data = $this->bundle->prepareData($user_data);

            $token = TokenSetting::createToken($user_data[0]['id_user'],"logged");
            $user_data[0]['token'] = $token;

            ActionsManager::add_action("user","userConnected",$user_data[0]['id_user']);

            return array(Tags::SUCCESS => 1, Tags::RESULT => $user_data);

        }

        return array(Tags::SUCCESS => 0, "errors" => $errors);
    }

    public function manageGuest($guest_id, $user_id){

        //check if someone else was used the guest
        $this->db->where('guest_id',$guest_id);
        $this->db->delete('user_guest');


        //insert new one
        $this->db->insert("user_guest",array(
            'user_id' => $user_id,
            'guest_id' => $guest_id,
            'created_at' => date("Y-m-d H:i:s",time()),
            'updated_at' => date("Y-m-d H:i:s",time()),
        ));


        $this->db->where("guest_id", intval($guest_id));
        $this->db->update("user", array(
            "guest_id" => 0
        ));

        $this->db->where("id_user", $user_id);
        $this->db->update("user", array(
            "guest_id" => intval($guest_id)
        ));


    }


    public function signUp($params = array(), $fieldsRequirement = array())
    {

        $data = array();
        $errors = array();

        extract($params);

        if (isset($phone) and $phone != "") {
            if (Text::checkPhoneFields($phone)) {
                $data['telephone'] = $phone;
            }else{
                $errors['telephone'] = Translate::sprint(Messages::EVENT_PHONE_INVALID);
            }
        }

        if (isset($username) && $username != "") {
            if (preg_match("#^[a-zA-Z0-9\-_." . REGEX_FR . "]+$#i", $username) && strlen($username) > 3) {
                $data['username'] = strtolower(/*Text::input*/($username));
            } else {
                $errors['username'] = Translate::sprint(Messages::USERNAME_ERROR_NO_VALIDE);
            }
        } else {
            $errors['username'] = Translate::sprint(Messages::USERNAME_ERROR_EMPTY);
        }

        if (isset($email) && $email != "") {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $data['email'] = strtolower($email);
            } else {
                $errors['email'] = Translate::sprint(Messages::USER_EMAIL_NOT_FALID);;
            }
        } else {
            $errors['email'] = Translate::sprint(Messages::USER_EMAIL_EMPTY);;
        }


        if (isset($phone) && $phone != "") {
            if (Text::checkPhoneFields($phone)) {
                $data['telephone'] = $phone;
            } else {
                $errors['phone'] = Translate::sprint(Messages::EVENT_PHONE_INVALID).$phone;
            }
        }


        if (isset($name) && $name != "") {

            if (Text::checkNameCompleteFields($name) OR Text::checkNameFields($name)) {
                $data['name'] = /*Text::input*/($name);
            } else {
                $errors['name'] = Translate::sprint(Messages::NAME_INVALID);
            }

        } else {
            if (!empty($fieldsRequirement) and in_array("name", $fieldsRequirement))
                $errors['name'] = Translate::sprint(Messages::NAME_FILED_EMPTY);
        }

        if (isset($image) && $image != "") {
            $data['images'] = json_encode(array($image), JSON_FORCE_OBJECT);
        }

        if (isset($password) && $password != "") {

            if (strlen($password) < 6) {
                $errors['password'] = Translate::sprint(Messages::PASSWORD_FORMAT);
            } else {
                $data['password'] = Security::cryptPassword($password);
            }
        } else {
            $errors['password'] = Translate::sprint(Messages::USER_PASSWORD_EMPTY);
        }



        if (empty($errors)) {

            $this->db->where("hidden",0);
            $this->db->where("username", $data['username']);
            $count = $this->db->count_all_results("user");

            if ($count == 1) {
                $errors['username'] = Translate::sprint(Messages::USER_NAME_EXIST);
            }

            $this->db->where("hidden",0);
            $this->db->where("email", $data['email']);
            $count = $this->db->count_all_results("user");

            if ($count == 1) {
                $errors['email'] = Translate::sprint(Messages::EMAIL_ALREADY_EXIST);
            }

        }


        if(empty($errors) && isset($data['telephone']) && ConfigManager::getValue("OTP_ENABLED")==1){
            $this->db->where("telephone", $data['telephone']);
            $this->db->where("hidden", 0);
            $count = $this->db->count_all_results("user");
            if ($count > 0) {
                $errors['telephone'] = Translate::sprint("Telephone already used");
            }
        }

        if(empty($errors) && isset($data['telephone'])){
            $this->db->where("hidden",0);
            $this->db->where("telephone", $data['telephone']);
            $count = $this->db->count_all_results("user");
            if ($count == 1) {
                $errors['telephone'] = Translate::sprint("Phone number already used");
            }
        }


        if (empty($errors) AND !empty($data)) {

            // TODO:IMAGE PAR DEFAULT
            $data['created_at'] = date("Y-m-d H:i:s", time());
            $data['created_at'] = MyDateUtils::convert($data['created_at'], TimeZoneManager::getTimeZone(), "UTC");
            $data['updated_at'] = $data['created_at'];
            $data['confirmed'] = 0;

            if (isset($typeAuth) and $typeAuth>0){
                $grp = $this->mGroupAccessModel->getGroupAccess($typeAuth);
                if($grp!=NULL){
                    $data['typeAuth'] = $grp['name'];
                    $data['grp_access_id'] = $grp['id'];
                }
            }

            $data['dateLogin'] = date("Y-m-d H:i", time());
            $data['dateLogin'] = MyDateUtils::convert($data['dateLogin'], TimeZoneManager::getTimeZone(), "UTC");

            $this->db->insert('user', $data);
            $user_id = $this->db->insert_id();


            $this->db->where('id_user',$user_id);
            $this->db->update('user',array(
                'hash_id' => md5($user_id.$data['username'])
            ));

            /*
             * SET UP USER SUBSCRIBE SETTINGS
             */

            $this->resetUserSettingSubscribe($user_id);

            ///////// END USER SUBSCRIBE SET UP ///////////

            $this->db->select("user.*,user_subscribe_setting.*");
            $this->db->from("user");
            $this->db->join("user_subscribe_setting", "user_subscribe_setting.user_id=user.id_user", "INNER");
            $this->db->where("id_user", $user_id);
            $this->db->select("user.*");

            $user_data = $this->db->get();
            $user_data = $user_data->result_array();


            //update user location
            if (isset($lat) AND isset($lng))
                $this->changeLocation($lat, $lng, $user_id);


            //update guest ID with FCM
            if (isset($guest_id) and $guest_id > 0) {
                $this->manageGuest($guest_id,$user_data[0]['id_user']);
            }

            $this->load->model("appcore/bundle");
            $user_data = $this->bundle->prepareData($user_data);


            //send mail confirmation
            $this->userMailConfirmation($user_data[0]);

            $token = TokenSetting::createToken($user_data[0]['id_user'],"logged");
            $user_data[0]['token'] = $token;

            ActionsManager::add_action("user","userConnected",$user_data[0]['id_user']);

            return array(Tags::SUCCESS => 1, Tags::RESULT => $user_data);

        } else {
            return array(Tags::SUCCESS => 0, Tags::ERRORS => $errors);
        }


    }

    public function resendMailConfirmation($user_id){

        $this->db->where('id_user',$user_id);
        $usr = $this->db->get('user',1);
        $usr = $usr->result_array();

        @$this->userMailConfirmation($usr[0]);

    }

    public function userMailConfirmation($user_data){

        if (!ConfigManager::getValue('EMAIL_VERIFICATION')) {
            return ;
        }


        $this->db->where('id_user',$user_data['id_user']);
        $this->db->update('user',array(
            'confirmed' => 0
        ));

        //generate new random password Q23qo5
        $token = md5(time() . rand(0, 999));
        $created_at = MyDateUtils::convert(date("Y-m-d", time()), TimeZoneManager::getTimeZone(), "UTC", "Y-m-d");


        $this->db->insert('token', array(
            "id" => $token,
            "uid" => $user_data['id_user'],
            "type" => "confirm",
            "created_at" => $created_at
        ));

        $appLogo = _openDir(ConfigManager::getValue("APP_LOGO"));
        $imageUrl = "";
        if (!empty($appLogo)) {
            $imageUrl = $appLogo['560_560']['url'];
        }

        //send mail verification
        $messageText = Text::textParser(array(
            "name" => $user_data['name'],
            "url" => site_url("user/userConfirm?id=$token"),
            "imageUrl" => $imageUrl,
            "email" => DEFAULT_EMAIL,
            "appName" => strtolower(APP_NAME),
        ), "emailconfirm");


        $mail = new DTMailer();
        $mail->setRecipient($user_data['email']);
        $mail->setFrom(DEFAULT_EMAIL);
        $mail->setFrom_name(APP_NAME);
        $mail->setMessage($messageText);
        $mail->setReplay_to(NOREPLY_EMAIL);
        $mail->setReplay_to_name(APP_NAME);
        $mail->setType("html");
        $mail->setSubject(Translate::sprint("Mail verification"));
        if(@$mail->send()){
            return TRUE;
        }



    }


    public function syncUser($params = array())
    {

        $errors = array();
        $data = array();

        extract($params);


        if ((isset($user_id) and $user_id > 0) OR (isset($username) and $username != "")) {

            $this->db->select("user.*");
            if ((isset($user_id) and $user_id > 0))
                $this->db->where("id_user", $user_id);
            else
                $this->db->where("username", $username);

            $this->db->from("user");

            $user = $this->db->get();
            $user = $user->result_array();

            $users = $this->mBundle->prepareData($user);


            if (!empty($user)) {


                $token = TokenSetting::get_by_uid($users[0]['id_user'],"logged");

                if($token!=NULL){
                    $users[0]['token'] = $token->id;
                }else{
                    $token = TokenSetting::createToken($users[0]['id_user'],"logged");
                    $users[0]['token'] = $token;
                }

                return array(Tags::SUCCESS => 1, Tags::RESULT => $users);

            } else {
                return array(Tags::SUCCESS => 0);
            }

        }

        return array(Tags::SUCCESS => 0, Tags::ERRORS => $errors);
    }

    public function updateAccount($params = array())
    {

        $data = array();
        $errors = array();

        extract($params);


        if (isset($username) && $username != "" && isset($oldUsername) and $oldUsername != "") {
            if (preg_match("#^[a-zA-Z0-9\-_." . REGEX_FR . "]+$#i", $username)) {
                $data['username'] = Text::input($username);
            } else {
                $errors['username'] = Translate::sprint(Messages::USER_NAME_INVALID);
            }
        } else {
            $errors['username'] = Translate::sprint(Messages::USER_NAME_EMPTY);
        }

        if (isset($email) && $email != "") {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $data['email'] = $email;
            } else {
                $errors['email'] = Translate::sprint(Messages::USER_NAME_INVALID);
            }
        }

        if (isset($phone) && $phone != "") {
            if (Text::checkPhoneFields($phone)) {
                $data['telephone'] = $phone;
            } else {
                $errors['phone'] = Translate::sprint(Messages::STORE_PHONE_INVALID);
            }
        } else {
            // $errors['phone'] = "Phone field is empty!";
        }

        if (isset($name) && $name != "") {
            $data['name'] = ucfirst(strtolower($name));
        } else {
            $errors['name'] = Translate::sprint(Messages::NAME_FILED_EMPTY);
        }

        if (isset($image) && $image != "") {
            $data['images'] = json_encode(array($image), JSON_FORCE_OBJECT);
        } else {

        }

        if (empty($errors))
            if (isset($password) && $password != "") {
                if (strlen($password) < 6) {
                    $errors['password'] = Translate::sprint(Messages::PASSWORD_FORMAT);
                } else if (isset($user_id) AND $user_id > 0) {
                    $data['password'] = Security::cryptPassword($password);
                }

            }

        if (empty($errors)) {

            if ($username != $oldUsername) {
                $this->db->where("id_user !=", $user_id);
                $this->db->where("username", $username);
                $count = $this->db->count_all_results("user");

                if ($count > 0) {
                    $errors['username'] = Translate::sprint(Messages::USER_NAME_EXIST);
                } else {
                    $data['username'] = $username;
                }
            }

            $this->db->where("email", $data['email']);
            $this->db->where("id_user !=", $user_id);
            $count = $this->db->count_all_results("user");

            if ($count == 1) {
                $errors['email'] = Translate::sprint(Messages::EMAIL_ALREADY_EXIST);
            }

            if(isset($data['telephone']) && ConfigManager::getValue("OTP_ENABLED")==1){
                $this->db->where("telephone", $data['telephone']);
                $this->db->where("hidden", 0);
                $this->db->where("id_user !=", $user_id);
                $count = $this->db->count_all_results("user");
                if ($count > 0) {
                    $errors['telephone'] = Translate::sprint("Telephone already used");
                }
            }

        }

        if (empty($errors) AND !empty($data)) {


            $date = date("Y-m-d H:i:s", time());
            $data['updated_at'] = MyDateUtils::convert($date, TimeZoneManager::getTimeZone(), "UTC");


            $this->db->where("id_user", $user_id);
            $this->db->where("username", $oldUsername);
            $this->db->update("user", $data);


            $this->db->where("user.id_user", $user_id);
            $this->db->from('user');

            $userData = $this->db->get();

            $userData = $userData->result_array();

            $this->load->model("appcore/bundle");
            $userData = $this->bundle->prepareData($userData);


            //update guest ID with FCM
            if (isset($guest_id) and $guest_id > 0) {
                $this->db->where("id_user", $userData[0]['id_user']);
                $this->db->update("user", array(
                    "guest_id" => intval($guest_id)
                ));
            }


            $token = TokenSetting::createToken($userData[0]['id_user'],"logged");
            $userData[0]['token'] = $token;


            return (array(Tags::SUCCESS => 1, Tags::RESULT => $userData));

        } else {

            return (array(Tags::SUCCESS => 0, Tags::ERRORS => $errors));

        }
    }


    public function updatePhone($params = array())
    {

        $data = array();
        $errors = array();

        extract($params);

        if (isset($phone) && $phone != "") {
            if (Text::checkPhoneFields($phone)) {
                $data['telephone'] = $phone;
                $data['phone_verified'] = 1;
            } else {
                $errors['phone'] = Translate::sprint(Messages::STORE_PHONE_INVALID);
            }
        } else {
             $errors['phone'] = "Phone field is empty!";
        }


        if (isset($user_id) && $user_id > 0) {

        } else {
            $errors['user_id'] = "User ID is missing!";
        }


        if (empty($errors) AND !empty($data)) {

            $date = date("Y-m-d H:i:s", time());
            $data['updated_at'] = MyDateUtils::convert($date, TimeZoneManager::getTimeZone(), "UTC");

            $this->db->where("id_user", $user_id);
            $this->db->update("user", $data);


            return (array(Tags::SUCCESS => 1));

        } else {

            return (array(Tags::SUCCESS => 0, Tags::ERRORS => $errors));

        }
    }


    public function checkUserConnection($params = array())
    {
        $errors = array();
        $data = array();
        extract($params);


        if (
            isset($userid) AND $userid > 0
            AND
            isset($username) AND Text::checkUsernameValidate($username)
        ) {
            $data['username'] = $username;
            $data['id_user'] = $userid;
        }else{
            $errors['username'] = Translate::sprint("Username is not valid!");
        }


        if (empty($errors)) {

            $this->db->where("user.hidden", 1);
            $this->db->where("username", $data['username']);
            $this->db->where("id_user", $data['id_user']);
            $count = $this->db->count_all_results("user");

            if($count == 1){
                $errors['user'] = Translate::sprint("User is not exists or disabled ".$this->db->last_query());
            }
        }


        if(empty($errors)){

            $this->db->select("user.*");
            $this->db->where("username", $data['username']);
            $this->db->where("id_user", $data['id_user']);
            $this->db->where("status >=", 0);

            $users = $this->db->get("user",1);
            $users = $users->result_array();

            if (count($users) > 0) {

                $new_users_results = $users;
                $this->load->model("appcore/bundle");
                $users = $this->bundle->prepareData($new_users_results);

                $token = TokenSetting::get_by_uid($users[0]['id_user'],"logged");

                if($token!=NULL)
                    $users[0]['token'] = $token->id;

                return array(Tags::SUCCESS => 1,
                    //"senderId" => $users[0]["senderid"],
                    "userId" => $users[0]["id_user"],
                    "username" => $users[0]["username"],
                    Tags::RESULT=> $users
                );

            } else {
                return array(Tags::SUCCESS => -1);
            }


        }

        return array(Tags::SUCCESS => 0,Tags::ERRORS=>$errors);
    }



    public function getUsers($params = array(), $callback = NULL, $callback0 = NULL)
    {

        $data = array();
        $errors = array();
        extract($params);

        if (!isset($page)) {
            $page = 1;
        }

        if (isset($page) and $page == 0) {
            $page = 1;
        }

        if (isset($limit) AND $limit == 0) {
            $limit = 20;
        } else if ($limit > 0) {

        } else if ($limit == -1) {
            $limit = 100000000;
        }


        $calcul_distance = "";

        $this->load->model("appcore/bundle");
        if (isset($user_id))
            $blockedIs = $this->bundle->getBlockedId($user_id);
        else
            $blockedIs = array();


        if ($callback != NULL)
            call_user_func($callback, $params);


        $this->db->where("user.hidden", 0);


        if (isset($uid) && $uid>0)
            $this->db->where("id_user", intval($uid));

        if (isset($user_id))
            $this->db->where("id_user !=", intval($user_id));


        if (isset($search) and $search != "") {
            $search = htmlentities($search, ENT_QUOTES, ENCODING);
            $this->db->where("(name like '%$search%' OR email like '%$search%' OR username like '%$search%' )", NULL, FALSE);
        }


        if (!isset($is_super)){

            if(isset($uid) and $uid>0){
                //
            }else{
                $this->db->where("grp_access_id", DEFAULT_USER_MOBILE_GRPAC);
            }

        }else {
            $this->db->where("manager !=", 1);
        }


        if (!empty($blockedIs)) {
            $this->db->where(" id_user NOT IN " . $this->bundle->inArrayClauseWhere($blockedIs), NULL, FALSE);
        }


        if (
            isset($lng)
            AND
            isset($lat)

        ) {

            $longitude = doubleval($lng);
            $latitude = doubleval($lat);


            $calcul_distance = " , IF( user.lat = 0,99999,  (1000 * ( 6371 * acos (
                              cos ( radians(" . $latitude . ") )
                              * cos( radians( user.lat ) )
                              * cos( radians( user.lng ) - radians(" . $longitude . ") )
                              + sin ( radians(" . $latitude . ") )
                              * sin( radians( user.lat ) )
                            )
                          ) ) ) as 'distance'  ";


        }


        $this->db->from("user");
        $count = $this->db->count_all_results();


        $pagination = new Pagination();
        $pagination->setCount($count);
        $pagination->setCurrent_page($page);
        $pagination->setPer_page($limit);
        $pagination->calcul();

        if ($count == 0)
            return array(Tags::SUCCESS => 1, "pagination" => $pagination, Tags::COUNT => $count, Tags::RESULT => array());


        if ($callback != NULL)
            call_user_func($callback, $params);


        $this->db->where("user.hidden", 0);


        if (!empty($blockedIs)) {
            $this->db->where(" id_user NOT IN " . $this->bundle->inArrayClauseWhere($blockedIs), NULL, FALSE);
        }

        if (isset($search) and $search != "") {
            $search = htmlentities($search, ENT_QUOTES, ENCODING);
            $this->db->where("(name like '%$search%' OR email like '%$search%' OR username like '%$search%' )", NULL, FALSE);
        }


        if (!isset($is_super)){

            if(isset($uid) and $uid>0){
                //
            }else{
                $this->db->where("grp_access_id", ConfigManager::getValue('DEFAULT_USER_MOBILE_GRPAC'));
            }

        }else {
            $this->db->where("manager !=", 1);
        }


        $this->db->select("user.*" . $calcul_distance, FALSE);

        if (isset($uid) && $uid>0)
            $this->db->where("id_user", intval($uid));

        if (isset($user_id))
            $this->db->where("id_user !=", intval($user_id));


        $this->db->from("user");

        if ($calcul_distance != "")
            $this->db->order_by("distance", "ASC");
        else
            $this->db->order_by("id_user", "DESC");


        if ($callback0 != NULL)
            call_user_func($callback0, $params);


        $this->db->limit($pagination->getPer_page(), $pagination->getFirst_nbr());
        $users = $this->db->get();
        $users = $users->result_array();


        $new_users_results = $users;

        if (!isset($is_super))
            foreach ($users as $key => $user) {

                $new_users_results[$key] = $user;

                if ($this->bundle->isBlocked($user_id, $user['id_user'])) {
                    $new_users_results[$key]['blocked'] = true;
                } else {
                    $new_users_results[$key]['blocked'] = false;
                }

            }

        $this->load->model("appcore/bundle");
        $new_users_results = $this->bundle->prepareData($new_users_results);

        $object = ActionsManager::return_action("user","func_getUsers",$new_users_results);
        if($object != NULL)
            $new_users_results = $object;


        return array(Tags::SUCCESS => 1, "pagination" => $pagination, Tags::COUNT => $count, Tags::RESULT => $new_users_results);

    }


    public function getGuests($params = array(), $callback = NULL)
    {

        $data = array();
        $errors = array();
        extract($params);


        if (isset($limit) AND $limit == 0) {
            $limit = 20;
        } else if ($limit > 0) {

        } else if ($limit == -1) {
            $limit = 100000000;
        }

        if ($callback != NULL)
            call_user_func($callback, $params);

        $calcul_distance = "";

        if (
            isset($lng)
            AND
            isset($lat)

        ) {

            $longitude = doubleval($lng);
            $latitude = doubleval($lat);


            $calcul_distance = " , IF( guest.lat = 0,99999,  (1000 * ( 6371 * acos (
                              cos ( radians(" . $latitude . ") )
                              * cos( radians( guest.lat ) )
                              * cos( radians( guest.lng ) - radians(" . $longitude . ") )
                              + sin ( radians(" . $latitude . ") )
                              * sin( radians( guest.lat ) )
                            )
                          ) ) ) as 'distance'  ";


        }

        if(isset($params['guests']) && !empty($params['guests'])){
            $this->db->where_in("id",$params['guests']);
        }


        if ($calcul_distance != "")
            $this->db->select("guest.*" . $calcul_distance, FALSE);
        $this->db->from("guest");

        if($calcul_distance != ""){
            $this->db->order_by("distance ASC,last_activity desc");
        }else{
            $this->db->order_by("last_activity","DESC");
        }


        $this->db->limit($limit);
        $guests = $this->db->get();
        $guests = $guests->result_array();


        return array(Tags::SUCCESS => 1, Tags::RESULT => $guests);

    }


    public function changeUserStatus($params = array())
    {
        $data = array();
        $errors = array();
        extract($params);

        if (isset($user_id) AND $user_id > 0 AND isset($status) AND $status >= 0) {

            $data = array(
                "is_online" => $status,
            );

            if (isset($lng) AND isset($lat) AND $lat != 0 AND $lng != 0) {
                $data["lat"] = $lat;
                $data["lng"] = $lng;
            }

            $this->db->where("id_user", $user_id);
            $this->db->update("user", $data);

        }

        return array(Tags::SUCCESS => 1);
    }


    private function changeLocation($lat, $lng, $user_id)
    {

        $this->db->where("id_user", $user_id);
        $this->db->update("user", array(
            "lat" => $lat,
            "lng" => $lng,
        ));

    }

    public function getUsersAnalytics($months = array(),$owner_id=0){

        $analytics = array();

        foreach ($months as $key => $m) {

            $last_month = date("Y-m-t",strtotime($key));
            $start_month = date("Y-m-1",strtotime($key));


            $this->db->where("created_at >=", $start_month);
            $this->db->where("created_at <=", $last_month);

            if ($owner_id>0) {
              //  $this->db->where('id_user',$owner_id);
            }

            $count = $this->db->count_all_results("user");
            $analytics['months'][$key] = $count;

        }


        if ($owner_id>0) {
            $this->db->where('id_user',$owner_id);
        }

        $analytics['count'] = $this->db->count_all_results("user");
        $analytics['count_label'] = Translate::sprint("Users");
        $analytics['color'] = "#f39c12";
        $analytics['icon_tag'] = "<i class=\"mdi mdi-account-circle-outline\"></i>";
        $analytics['label'] = "User";


        return $analytics;

    }


    public function userDetail($id)
    {

        if (isset($id) AND $id > 0) {
            $this->db->join("user_subscribe_setting", "user_subscribe_setting.user_id=user.id_user","left outer");
            $this->db->from("user");
            $this->db->where("user.id_user", $id);
            $this->db->limit(1);
            $user = $this->db->get();
            $user = $user->result();

            return array("success" => 1, Tags::RESULT => $user);
        }


        return array(Tags::SUCCESS => 0);
    }

    public function access($id)
    {

        $this->db->select("status");
        $this->db->from("user");
        $this->db->where("id_user", $id);
        $statusUser = $this->db->get()->row()->status;

        if (intval($statusUser) == -1) {
            $data['status'] = 1;
        } else if (intval($statusUser) == 1 || intval($statusUser) == 0) {
            $data['status'] = -1;
        } else {
            $errors["status"] = Translate::sprint(Messages::STATUS_NOT_FOUND);
        }

        if (isset($data) AND empty($errors)) {

            $this->db->where("id_user", $id);
            $this->db->update("user", $data);

            return array(Tags::SUCCESS => 1, "url" => admin_url("user/users"));

        } else {
            return array(Tags::SUCCESS => 0, Tags::ERRORS => $errors);

        }

    }


    public function edit($params = array())
    {


        $errors = array();
        $data = array();
        extract($params);


        if (isset($image) and $image != "") {
            $data["images"] = json_encode($image, JSON_FORCE_OBJECT);
            $image = json_decode($data["images"], JSON_OBJECT_AS_ARRAY);
            foreach ($image as $img) {
                $data["images"] = $img;
                break;
            }
        }else
        {
            $data["images"] = json_decode("",JSON_OBJECT_AS_ARRAY);
        }


        if (isset($username) and $username != "") {

            if (Text::checkUsernameValidate($username)) {
                $data['username'] = $username;
            }

        } else {
            $errors['username'] = Translate::sprint(Messages::USER_NAME_EMPTY);
        }


        if (isset($phone) and $phone != "") {

            if (Text::checkPhoneFields($phone)) {
                $data['telephone'] = $phone;
            }else{
                 $errors['username'] = Translate::sprint(Messages::EVENT_PHONE_INVALID);
            }

        } else {
           // $errors['username'] = Translate::sprint(Messages::EVENT_PHONE_INVALID);
        }

        if (isset($email) AND $email != "") {

            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {

                $data["email"] = $email;

            } else {
                $errors['email'] = Translate::sprint(Messages::USER_EMAIL_NOT_FALID);
            }

        } else {
            $errors['email'] = Translate::sprint(Messages::USER_EMAIL_EMPTY);
        }

        if (isset($name) and $name != "") {

            $name = trim($name);
            if (Text::checkNameCompleteFields($name) OR Text::checkNameFields($name)) {
                $data['name'] = Text::input($name);
            } else {
                $errors['name'] = Translate::sprint(Messages::NAME_INVALID);
            }

        } else {
            $errors['name'] = Translate::sprint(Messages::USER_NAME_EMPTY);
        }

        if (isset($password) and $password != "") {

            if (!isset($confirm) || $confirm == "") {
                $errors['confirm'] = Translate::sprint(Messages::USER_CONFIRMED_PASSWORD);
            }

            if (!isset($password) || $password == "") {
                $errors['password'] = Translate::sprint(Messages::USER_PASSWORD_EMPTY);
            }

        }


        if(!isset($self_edit) && isset($typeAuth) and $typeAuth>0){
            $grp = $this->mGroupAccessModel->getGroupAccess($typeAuth);
            if($grp!=NULL){
                $data['typeAuth'] = $grp['name'];
                $data['grp_access_id'] = $grp['id'];
            }
        }

        if (empty($errors) and isset($id_user) AND $id_user > 0) {

            /***********************************VALIDTE PASSWORD *****************/
            if ($password != "")
                if ($confirm == $password) {
                    $data["password"] = Security::cryptPassword($password);
                } else {
                    $errors["password"] = Translate::sprint(Messages::USER_CONFIRMED_PASSWORD);
                }
            /***********************************VALIDTE PASSWORD *****************/


            $this->db->where("id_user", $id_user);
            $count = $this->db->count_all_results("user");
            if ($count == 0) {
                $errors['user_id'] = Translate::sprint(Messages::USER_NOT_FOUND);
            }

        } else {

            if($id_user == 0){
                $errors['user_id'] = Translate::sprint(Messages::USER_NOT_FOUND);
            }

        }

        if (empty($errors) AND !empty($data)) {

            $this->db->where("hidden", 0);
            $this->db->where("username", $username);
            $this->db->where("id_user !=", $id_user);
            $count = $this->db->count_all_results("user");

            if ($count == 1) {
                $errors['user_id'] = Translate::sprint(Messages::USER_NAME_EXIST);
            }


            $this->db->where("hidden", 0);
            $this->db->where("email", $email);
            $this->db->where("id_user !=", $id_user);
            $count = $this->db->count_all_results("user");

            if ($count == 1) {
                $errors['user_id'] = Translate::sprint(Messages::EMAIL_ALREADY_EXIST);
            }


        }


        if (empty($errors) AND !empty($data)) {

            $this->db->where("id_user", $id_user);
            $this->db->update("user", $data);


            //add settings
            if(isset($user_settings)
                AND !empty($user_settings)
                AND !ModulesChecker::isEnabled("pack")){

                $user_package = array();
                $user_subscribe_fields = UserSettingSubscribe::load();
                foreach ($user_subscribe_fields as $field){

                    if($field['_display']==0)
                        continue;

                    $key = $field['field_name'];
                    if(isset($user_settings[$key])){
                        if($field['field_type']==UserSettingSubscribeTypes::BOOLEAN){

                            if($user_settings[$key] == "true"){
                                $user_package[$key] = true;
                            }elseif($user_settings[$key] == "false"){
                                $user_package[$key] = false;
                            }else
                                $user_package[$key] = UserSettingSubscribe::parseToType($user_settings[$key],$field['field_type']);

                        }else{
                            $user_package[$key] = UserSettingSubscribe::parseToType($user_settings[$key],$field['field_type']);
                        }

                    }


                }


                if(!empty($user_package)){

                    $package_json = json_encode($user_package,JSON_FORCE_OBJECT);
                    $user_package['user_settings_package'] = $package_json;

                    $user_package['updated_at'] = date("Y-m-d", time());
                    $user_package['updated_at'] = MyDateUtils::convert($user_package['updated_at'], TimeZoneManager::getTimeZone(), "UTC");

                    $this->db->where('user_id',$id_user);
                    $c = $this->db->count_all_results('user_subscribe_setting');

                    if($c==1){
                        $this->db->where('user_id',$id_user);
                        $this->db->update("user_subscribe_setting", $user_package);
                    }else{
                        error_log("An error occurrence, when saving user setting");
                        throw new  Exception("An error occurrence, when saving user setting");
                    }

                }



            }

            $this->db->where("user.id_user", $id_user);

            $this->db->select("user.*,user_subscribe_setting.*");
            $this->db->from("user");
            $this->db->join("user_subscribe_setting", "user_subscribe_setting.user_id=user.id_user", "INNER");

            $userData = $this->db->get();
            $userData = $userData->result_array();


            return array(Tags::SUCCESS => 1,Tags::RESULT=>$userData, "url" => admin_url("user/edit"));
        } else {


            return array(Tags::SUCCESS => 0, Tags::ERRORS => $errors);
        }

    }


    public function blockUser($params=array()){

        $errors= array();
        $data = array();

        extract($params);


        if(isset($user_id) and $user_id>0){
            $data['user_id'] = intval($user_id);
        }else{
            $errors['user_id'] = Translate::sprint(Messages::USER_NOT_FOUND);;
        }


        if(isset($blocked_id) and $blocked_id>0){
            $data['blocked_id'] = intval($blocked_id);
        }else{
            $errors['blocked_id'] = Translate::sprint(Messages::USER_NOT_FOUND);
        }

        if(empty($errors)){

            if(isset($state) AND $state==TRUE){

                $this->db->where($data);
                $count = $this->db->count_all_results("block");

                if($count==0){
                    $data['created_at'] = date("Y-m-d H:i:s",time());
                    $this->db->insert("block",$data);

                    return array(Tags::SUCCESS=>1);
                }

            }else{

                $this->db->where($data);
                $this->db->delete("block");
                return array(Tags::SUCCESS=>1);
            }

        }

        return array(Tags::SUCCESS=>0,  Tags::ERRORS=>$errors);

    }


    public function create($params=array())
    {

        $errors  = array();
        $data = array();
        extract($params);


        if (isset($image) and $image != "") {
            $data["images"] = json_encode($image, JSON_FORCE_OBJECT);
            $image = json_decode($data["images"], JSON_OBJECT_AS_ARRAY);
            foreach ($image as $img) {
                $data["images"] = $img;
                break;
            }
        }

        if (isset($phone) and $phone != "") {

            if (Text::checkPhoneFields($phone)) {
                $data['telephone'] = $phone;
            }else{
                $errors['telephone'] = Translate::sprint(Messages::EVENT_PHONE_INVALID);
            }

        } else {
            // $errors['username'] = Translate::sprint(Messages::EVENT_PHONE_INVALID);
        }

        if (isset($typeAuth) and $typeAuth>0){
            $grp = $this->mGroupAccessModel->getGroupAccess($typeAuth);
            if($grp!=NULL){
                $data['typeAuth'] = $grp['name'];
                $data['grp_access_id'] = $grp['id'];
            }else{
                $errors['typeAuth'] = Translate::sprint("You've selected incorrect user type");
            }
        }else{
            $errors['typeAuth'] = Translate::sprint("Please select specific user type");
        }

        if (isset($push_campaign_auto) and $push_campaign_auto == "on") $push_campaign_auto = 1;
        else  $push_campaign_auto = 0;


        if (isset($email) AND $email != "") {

            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {

                $data["email"] = $email;

            } else {
                $errors['email'] = Translate::sprint(Messages::USER_EMAIL_NOT_FALID);
            }

        } else {
            $errors['email'] = Translate::sprint(Messages::USER_EMAIL_EMPTY);
        }

        if (isset($username) and $username != "") {

            $regex = "#^[a-zA-Z0-9 \-_." . REGEX_FR . "]+$#i";
            if (preg_match($regex, $username)) {
                $data['username'] = $username;

            } else {
                $errors['username'] = Translate::sprint(messages::USER_NAME_INVALID);
            }

        } else {
            $errors['username'] = Translate::sprint(messages::USER_NAME_EMPTY);
        }

        if (isset($name) and $name != "") {

            if (Text::checkNameCompleteFields($name)) {
                $data['name'] = Text::input($name);
            } else {
                $errors['name'] = Translate::sprint(Messages::NAME_INVALID);
            }

        } else {
            $errors['name'] = Translate::sprint(messages::USER_NAME_EMPTY);
        }


        if (isset($confirm) and $confirm == "" || !isset($confirm)) {
            $errors['confirm'] = Translate::sprint(messages::USER_CONFIRMED_PASSWORD);
        }

        if (isset($password) and $password == "" || !isset($password)) {
            $errors['password'] = Translate::sprint(messages::USER_PASSWORD_EMPTY);
        }

        if (empty($errors) and $confirm == $password) {

            $data["password"] = Security::cryptPassword($password);

        } else {
            $errors["password"] = Translate::sprint(messages::USER_CONFIRMED_PASSWORD);
        }

        if (isset($tel) AND $tel != "") {
            if (preg_match("#^[0-9 \-_.\(\)\+]+$#i", $tel)) {
                $data["telephone"] = $tel;
            } else {
                $errors['tel'] = Translate::sprint(Messages::USER_PHONE_EMPTY);
            }


        }

        $data['created_at'] = date("Y-m-d H:i:s", time());
        $data['created_at'] = MyDateUtils::convert($data['created_at'], TimeZoneManager::getTimeZone(), "UTC");


        if (empty($errors) AND !empty($data)) {


            $this->db->where("username", $username);
            $this->db->or_where("email", $data["email"]);
            $count = $this->db->count_all_results("user");

            if ($count > 0) {
                return array(Tags::SUCCESS => 0, Tags::ERRORS => array("err"=>Messages::USER_LOGIN_EMAIL_EXIST));
            }

            $data["status"] = 1;
            $data["confirmed"] = 1;

            $this->db->insert("user", $data);
            $user_id = $this->db->insert_id();


            if ($user_id == 0) {
                return (array(Tags::SUCCESS => 0, Tags::ERRORS => Translate::sprint(Messages::USER_NOT_CREATED)));
            } else {

                if(isset($user_settings)
                    AND !empty($user_settings)
                    AND !ModulesChecker::isEnabled("pack")){

                    $user_package = array();
                    $user_subscribe_fields = UserSettingSubscribe::load();
                    foreach ($user_subscribe_fields as $field){

                        if($field['_display']==0)
                            continue;

                        $key = $field['field_name'];
                        if(isset($user_settings[$key])){
                            $user_package[$key] = UserSettingSubscribe::parseToType($user_settings[$key],$field['field_type']);
                        }

                    }

                    $package_json = json_encode($user_package,JSON_FORCE_OBJECT);
                    $user_package['user_settings_package'] = $package_json;

                    $user_package['created_at'] = date("Y-m-d H:i:s", time());
                    $user_package['created_at'] = MyDateUtils::convert($user_package['created_at'], TimeZoneManager::getTimeZone(), "UTC");
                    $user_package['user_id'] = $user_id;

                    $this->db->insert("user_subscribe_setting", $user_package);

                }else if (!ModulesChecker::isEnabled("pack")){//execute it when pack module is enabled

                    $user_package = array();
                    $user_subscribe_fields = UserSettingSubscribe::load();
                    foreach ($user_subscribe_fields as $field){

                        if($field['_display']==0)
                            continue;

                        $key = $field['field_name'];
                        $value = $field['field_default_value'];
                        $user_package[$key] = $value;

                    }

                    $package_json = json_encode($user_package,JSON_FORCE_OBJECT);
                    $user_package['user_settings_package'] = $package_json;

                    $user_package['created_at'] = date("Y-m-d H:i:s", time());
                    $user_package['created_at'] = MyDateUtils::convert($user_package['created_at'], TimeZoneManager::getTimeZone(), "UTC");
                    $user_package['user_id'] = $user_id;

                    $this->db->insert("user_subscribe_setting", $user_package);

                }

            }

            $this->db->where("id_user", $user_id);
            $users = $this->db->get("user");
            $users = $users->result_array();
            $user = $users[0];

            $this->db->where('id_user',$user_id);
            $this->db->update('user',array(
                'hash_id' => md5($user_id.$users[0]['username'])
            ));

            return (array(Tags::SUCCESS => 1,Tags::RESULT=> $user, "url" => admin_url("user/users")));
        } else {
            return (array(Tags::SUCCESS => 0, Tags::ERRORS => $errors));
        }


    }


    public function removeInvalidGuest($guest_id)
    {

        $this->db->where("id", $guest_id);
        $this->db->delete("guest");

        $this->db->where("guest_id", $guest_id);
        $this->db->update("user", array(
            'guest_id' => 0
        ));

        ActionsManager::add_action('guest', 'onDelete', array('id' => $guest_id));
    }



    public function getOwners($params=array())
    {

        extract($params);

        $name = Text::input(RequestInput::get("q"));

        $this->db->where("(username LIKE '%" . trim($name) . "%' OR email LIKE '%" . trim($name) . "%')", NULL, FALSE);

        $users = $this->db->get("user", 10);
        $users = $users->result();

        $json = array();
        foreach ($users as $obj) {

            $m = "";
            if (isset($user_id) and $user_id == $obj->id_user) {
                $m = Translate::sprint("Me") . " - ";
            }

            $json[] = array(
                "text" => $m . $obj->name . ", @" . $obj->username , "id" => $obj->id_user,
            );
        }


        return $json;
    }


    function addFields(){

        if (!$this->db->field_exists('platform', 'guest'))
        {
            $fields = array(
                'platform'       => array('type' => 'VARCHAR(30)', 'after' => 'lng','default' => ""),
            );
            // modify_column : The usage of this method is identical to add_column(), except it alters an existing column rather than adding a new one.
            $this->dbforge->add_column('guest', $fields);
        }

    }


    public function generateHashIdForEachUser(){

        $this->db->select('id_user, username');
        $this->db->where('hash_id','');
        $users = $this->db->get('user');
        $users = $users->result();

        foreach ($users as $val){
            $this->db->where('id_user',$val->id_user);
            $this->db->update('user',array(
                'hash_id' => md5($val->id_user.$val->username)
            ));
        }

    }

    public function updateFields(){



        if (!$this->db->field_exists('phoneVerified', 'user'))
        {
            $fields = array(
                'phoneVerified'  => array('type' => 'INT', 'default' => 0),
            );
            // modify_column : The usage of this method is identical to add_column(), except it alters an existing column rather than adding a new one.
            $this->dbforge->add_column('user', $fields);
        }

        if (!$this->db->field_exists('hash_id', 'user'))
        {
            $fields = array(
                'hash_id'  => array('type' => 'VARCHAR(32)', 'default' => ""),
            );
            // modify_column : The usage of this method is identical to add_column(), except it alters an existing column rather than adding a new one.
            $this->dbforge->add_column('user', $fields);
        }

        if (!$this->db->field_exists('hidden', 'user'))
        {
            $fields = array(
                'hidden'  => array('type' => 'INT', 'default' => 0),
            );
            // modify_column : The usage of this method is identical to add_column(), except it alters an existing column rather than adding a new one.
            $this->dbforge->add_column('user', $fields);
        }


        if (!$this->db->field_exists('phone_verified', 'user'))
        {
            $fields = array(
                'phone_verified'  => array('type' => 'INT', 'default' => 0),
            );
            // modify_column : The usage of this method is identical to add_column(), except it alters an existing column rather than adding a new one.
            $this->dbforge->add_column('user', $fields);
        }

        if (!$this->db->field_exists('updated_at', 'user'))
        {
            $fields = array(
                'updated_at'  => array('type' => 'DATETIME', 'after' => 'guest_id','default' => NULL),
            );
            // modify_column : The usage of this method is identical to add_column(), except it alters an existing column rather than adding a new one.
            $this->dbforge->add_column('user', $fields);
        }

        if (!$this->db->field_exists('created_at', 'user'))
        {
            $fields = array(
                'created_at'  => array('type' => 'DATETIME', 'after' => 'guest_id','default' => NULL),
            );
            // modify_column : The usage of this method is identical to add_column(), except it alters an existing column rather than adding a new one.
            $this->dbforge->add_column('user', $fields);
        }

        if ($this->db->field_exists('blacklist', 'user'))
            $this->dbforge->drop_column('user', 'blacklist');

        if ($this->db->field_exists('job', 'user'))
            $this->dbforge->drop_column('user', 'job');

        if ($this->db->field_exists('date_created', 'user'))
            $this->dbforge->drop_column('user', 'date_created');

        if ($this->db->field_exists('is_online', 'user'))
            $this->dbforge->drop_column('user', 'is_online');

        if ($this->db->field_exists('senderid', 'user'))
            $this->dbforge->drop_column('user', 'senderid');


        if ($this->db->table_exists('setting') ){
            $this->dbforge->rename_table('setting', 'user_subscribe_setting');
            $this->dbforge->drop_table('setting',TRUE);
        }


        if (!$this->db->field_exists('created_at', 'user_subscribe_setting'))
        {
            $fields = array(
                'created_at'  => array('type' => 'DATETIME', 'after' => 'last_updated','default' => NULL),
            );
            // modify_column : The usage of this method is identical to add_column(), except it alters an existing column rather than adding a new one.
            $this->dbforge->add_column('user_subscribe_setting', $fields);
        }


        if (!$this->db->field_exists('updated_at', 'user_subscribe_setting'))
        {
            $fields = array(
                'updated_at'  => array('type' => 'DATETIME', 'after' => 'last_updated','default' => NULL),
            );
            // modify_column : The usage of this method is identical to add_column(), except it alters an existing column rather than adding a new one.
            $this->dbforge->add_column('user_subscribe_setting', $fields);
        }



        $this->alter_table_user_timezone();
    }


    public function save_user_subscribe_setting($key,$value){

        $user_id = $this->mUserBrowser->getData('id_user');

        $this->db->where('user_id',$user_id);
        $this->db->update('user_subscribe_setting',array(
            $key=>$value
        ));

    }

    public function alter_table_user_timezone(){
        if($this->db->field_exists('user_timezone', 'user_subscribe_setting')){
            $this->db->query('ALTER TABLE `user_subscribe_setting` CHANGE `user_timezone` `user_timezone` VARCHAR( 60 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT \'UTC\';');
        }
    }


    public function createTable(){


        $this->load->dbforge();

        $fields = array(
            'id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE
            ),

            'user_id' => array(
                'type' => 'INT',
                'default' => NULL
            ),

            'guest_id' => array(
                'type' => 'INT',
                'default' => NULL
            ),

            'updated_at' => array(
                'type' => 'DATETIME'
            ),
            'created_at' => array(
                'type' => 'DATETIME'
            ),
        );

        $this->dbforge->add_field($fields);
        $attributes = array('ENGINE' => 'InnoDB');
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('user_guest', TRUE, $attributes);



    }



}

