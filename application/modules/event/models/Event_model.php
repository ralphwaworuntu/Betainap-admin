<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Created by DT Team.
 * AppName: NearbyStores
 */

class Event_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }


    public function getParticipationsProfiles($eventId){

        $this->db->select("user.id_user, user.name, user.images");

        $this->db->where("participants.event_id",$eventId);
        $this->db->join("user","user.id_user=participants.user_id");
        $users = $this->db->get('participants');
        $users = $users->result_array();

        $profiles = [];


        foreach ($users as $user){
            if(is_string($user['images'])){
                $imager = ImageManagerUtils::getFirstImage([$user['images']],ImageManagerUtils::IMAGE_SIZE_100);
            }else{
                $imager = ImageManagerUtils::getFirstImage($user['images'],ImageManagerUtils::IMAGE_SIZE_100);
            }

            $profiles[] = [
                'name' => $user['name'],
                'image' => $imager,
            ];
        }


        return $profiles;
    }

    public function getParticipations($eventId){

        $this->db->where("participants.event_id",$eventId);
        return $this->db->count_all_results('participants');
    }

    public function countNewParticipations($ownerId){

        if (!$this->db->table_exists('participants') )
            return;

        if (isset($ownerId) && $ownerId > 0) {
            $this->db->where("event.user_id", $ownerId);
        }

        $this->db->where("participants.status", 0);
        $this->db->join("event","participants.event_id=event.id_event");

        return $this->db->count_all_results('participants');
    }
    public function sendTicket($pId){

        $this->db->where("participants.id",$pId);
        $participants = $this->db->get('participants',1);
        $participants = $participants->result_array();

        if(!isset($participants[0]))
            return array(Tags::SUCCESS=>0);



        if($participants[0]['attachements']=="")
            return array(Tags::SUCCESS=>0);


        $attachedFile = $this->uploader_model->getFile($participants[0]['attachements']);

        if($attachedFile==NULL)
            return array(Tags::SUCCESS=>0);



        $event = $this->getEvent($participants[0]['event_id']);

        if(!isset($event) && empty($event))
            return array(Tags::SUCCESS=>0);



        $client = $this->mUserModel->getUserData($participants[0]['user_id']);
        $client_email = $client['email'];
        $client_name = $client['name'];


        $logo = ImageManagerUtils::getImage(ConfigManager::getValue('APP_LOGO'),ImageManagerUtils::IMAGE_SIZE_560);

        $messageText = Text::textParser(
            [
                'name' => $client_name,
                'eventName' => $event['name'],
                'appName' => ConfigManager::getValue("APP_NAME"),
                'eventDate' => DateSetting::parse($event['date_b'])." - ".DateSetting::parse($event['date_e']),
                'ticketID' => "#".$participants[0]['id'],
                'bookingID' => "#".str_pad($participants[0]['booking_id'] , 6, 0, STR_PAD_LEFT),
                'logo' => $logo
            ],
            "ticket-template"
        );


        $mailer = new DTMailer();
        $mailer->setRecipient($client_email);

        $mailer->setFrom(ConfigManager::getValue("DEFAULT_EMAIL"));
        $mailer->setFrom_name(ConfigManager::getValue("APP_NAME"));

        $mailer->setReplay_to(ConfigManager::getValue("DEFAULT_EMAIL"));
        $mailer->setReplay_to_name(ConfigManager::getValue("APP_NAME"));

        $fileNameArr = explode("/",$attachedFile['file']);
        $mailer->setAttachments([
            [
                "url"=>($attachedFile['file']),
                "name"=>end($fileNameArr),
            ]
        ]);

        $mailer->setSubject(_lang_f("Your ticket (%s) for (%s)",["#".$participants[0]['id'],$event['name']]));
        $mailer->setMessage($messageText);

        $mailer->send();

        return array(Tags::SUCCESS=>1,Tags::RESULT=>$client_email);
    }

    public function saveParticipant($id,$eventId,$attachement,$status,$ownerId){

        $event = $this->getEvent($eventId);

        if($event['user_id']!=$ownerId){
            return array(Tags::SUCCESS=>0);
        }

        if(!in_array($status,[0,1,-1])){
            return ;
        }

        $this->db->where("participants.id",$id);
        $this->db->update('participants',array(
            'attachements' => $attachement,
            'status' => intval($status),
        ));

        return array(Tags::SUCCESS=>1);
    }

    public function registerBookingEvent(){

        ActionsManager::register('booking','bookingCompleted',function ($args){
            //to do when the payment made
        });

        ActionsManager::register('booking','bookingConfirmed',function ($args){

            $this->db->where('id',$args['id']);
            $this->db->where('booking_type','digital');
            $booking = $this->db->get('booking',1);
            $booking = $booking->result_array();

            if(isset($booking[0])){
                $cart = json_decode($booking[0]['cart'],JSON_OBJECT_AS_ARRAY);
                $cf = json_decode($booking[0]['cf_data'],JSON_OBJECT_AS_ARRAY);
                foreach ($cart as $item){
                    $module = $item['module'];
                    $module_id = $item['module_id'];
                    if($module=="event"){
                        $event = $this->getEvent($module_id);
                        if(!empty($event)){
                            for ($i=0;$i<$item['qty'];$i++){

                                $this->createNewParticipation(array(
                                    'booking_id' => $args['id'],
                                    'event_id' => $module_id,
                                    'user_id' => $booking[0]['user_id'],
                                    'full_name' => $cf['Full name'] ?? "--",
                                    'phone' => $cf['Phone'] ?? "--",
                                ));

                                $this->descreaseEventLimit($module_id);

                            }
                        }

                    }
                }
            }
        });


        ActionsManager::register('booking','bookingCanceled',function ($args){

            $this->db->where('id',$args['id']);
            $this->db->where('booking_type','digital');
            $booking = $this->db->get('booking',1);
            $booking = $booking->result_array();

            if(isset($booking[0])){
                $this->db->where('booking_id',$args['id']);
                $this->db->update('participants',array(
                    'status' => -1
                ));

                $this->db->where('booking_id',$args['id']);
                $participants = $this->db->get('participants');
                $participants = $participants->result();

                foreach ($participants as $participant){
                    $this->increaseEventLimit($participant->event_id);
                }
            }
        });

    }

    public function countParticipants($event_id){
        $this->db->where('event_id',$event_id);
        $this->db->where('status !=',-1);
        return $this->db->count_all_results("participants");
    }

    private function descreaseEventLimit($eventId){

        $this->db->where('id_event',$eventId);
        $event = $this->db->get('event');
        $event = $event->result();


        if(isset($event[0])){
            $this->db->where('id_event',$eventId);
            $this->db->where('limit !=',-1);
            $this->db->update('event',array(
                'limit' => $event[0]->limit - 1
            ));
        }

    }

    private function increaseEventLimit($eventId){

        $this->db->where('id_event',$eventId);
        $event = $this->db->get('event');
        $event = $event->result();


        if(isset($event[0])){
            $this->db->where('id_event',$eventId);
            $this->db->where('limit !=',-1);
            $this->db->update('event',array(
                'limit' => $event[0]->limit + 1
            ));
        }

    }

    private function createNewParticipation($param=array()){

        $param['created_at'] = date("Y-m-d H:i",time());
        $param['updated_at'] = date("Y-m-d H:i",time());
        $this->db->insert('participants',$param);

        if(isset($param['event_id'])){

            $this->db->where('id_event',$param['event_id']);
            $event = $this->db->get('event');
            $event = $event->result_array();

            if($event[0]['limit']!=-1 && $event[0]['limit']){
                $limit = $event[0]['limit']--;
                $this->db->where('id_event',$param['event_id']);
                $this->db->update('event',array(
                    'limit' => $limit
            ));
            }
        }
    }

    public function getAllActiveImages(){
        $result = array();
        $this->db->select('images,id_event');
        $stores = $this->db->get('event');
        $stores = $stores->result();
        foreach ($stores as $store){
            $images = json_decode($store->images,JSON_OBJECT_AS_ARRAY);
            if(count($images)>0){
                foreach ($images as $image){
                    $result[] = $image;
                }
            }
        }
        return $result;
    }

    public function getEvent($event_id=0){

        $events = $this->getEvents(array(
            "event_id" => $event_id,
            "limit" => -1
        ));

        if(isset($events[Tags::RESULT][0]))
            return $events[Tags::RESULT][0];

        return NULL;
    }

    public function send_reminders($event_id,$contacts=array()){

        $event_data = $this->getEvent($event_id);

        $subject = Translate::sprintf("Reminder for upcoming event \"%s\"",array(
            $event_data['name']
        ));

        if(ModulesChecker::isEnabled("webapp")){
            $event_link = webapp_url(slug("detail-event/".$event_data['id_event']));
        }else{
            $event_link = site_url("event/id/" . $event_data['id_event']);
        }

        $event_name = $event_data['name'];
        $event_location = $event_data['address'];
        $event_location_lat = $event_data['lat'];
        $event_location_lng = $event_data['lng'];
        $event_date = DateSetting::parseDateTime($event_data['date_b']);


        $logo_url = ImageManagerUtils::getImage(ConfigManager::getValue('APP_LOGO'),ImageManagerUtils::IMAGE_SIZE_560);

        $sent_users = array();
        foreach ($contacts as $contact){

            $this->db->where('user_id',$contact->id);
            $this->db->where('module',"event");
            $this->db->where('module_id',$event_id);
            $this->db->where('status',1);
            $count = $this->db->count_all_results('bookmarks');

            if($count==1)
                continue;



            $template = Text::textParser(
                [
                    'appName' => ConfigManager::getValue("APP_NAME"),
                    'email' => ConfigManager::getValue("DEFAULT_EMAIL"),
                    'logo' => $logo_url,
                    'name' => $contact->name,
                    'location' => $event_location,
                    'locationOnMap' => "https://www.google.com/maps/search/".(urlencode($event_location))."/@".(urlencode($event_location_lat.",".$event_location_lng)),
                    'url' => $event_link,
                    'eventName' => $event_name,
                    'eventDate' => $event_date,
                ],
                "reminder-template"
            );



            $mail = new DTMailer();
            $mail->setRecipient($contact->email);
            $mail->setFrom(ConfigManager::getValue('DEFAULT_EMAIL'));
            $mail->setFrom_name(ConfigManager::getValue('APP_NAME'));
            $mail->setMessage($template);
            $mail->setReplay_to(ConfigManager::getValue('DEFAULT_EMAIL'));
            $mail->setReplay_to_name(ConfigManager::getValue('APP_NAME'));
            $mail->setType("html");
            $mail->setSubject($subject);
            if ($mail->send()) {
                return FALSE;
            }

        }


        return TRUE;
    }

    public function reminder_validate_user($owner_id, $event_id, $usersId = array())
    {

        $this->db->where('user_id', $owner_id);
        $this->db->where('id_event', $event_id);
        $count = $this->db->count_all_results('event');

        if ($count == 0)
            return array();


        $this->db->select("email,name,id_user");
        $this->db->where_in('id_user',$usersId);
        $user = $this->db->get("user");
        $users = $user->result();

        $contacts = array();

        foreach ($users as $user){
            $contact = new MailContact();
            $contact->name = $user->name;
            $contact->email = $user->email;
            $contact->id = $user->id_user;
            $contacts[] = $contact;
        }

        return $contacts;
    }

    public function reminder_validate_guest($owner_id, $event_id, $guests = array())
    {

        if(empty($guests))
            return array();

        foreach ($guests as $key => $guest) {
            $users[$key] = intval($guest);
        }

        $this->db->where('user_id', $owner_id);
        $this->db->where('id_event', $event_id);
        $count = $this->db->count_all_results('event');

        $new_users_list = array();

        if ($count == 0)
            return array();

        foreach ($guests as $guest_id) {
            $this->db->where('module', "event");
            $this->db->where('module_id', $event_id);
            $this->db->where('guest_id', $guest_id);
            $count = $this->db->count_all_results('bookmarks');

            if($count>0)
                $new_users_list[] = $guest_id;
        }


        return $new_users_list;
    }

    public function custom_campaign_input($args)
    {

        $params = array(
            'limit' => LIMIT_PUSHED_GUESTS_PER_CAMPAIGN,
            'order' => 'last_activity',
        );

        if (isset($args['custom_parameters'])
            && isset($args['custom_parameters']['custom_campaign'])) {

            $campaign_data = (array)$this->session->userdata("campaign");
            $campaign_data = $campaign_data[$args['custom_parameters']['custom_campaign']];

            $params['guests'] = $campaign_data['guests'];
            $params['limit'] = count($campaign_data['guests']);

        }

        $this->load->model("User/mUserModel");
        $data = $this->mUserModel->getGuests($params);

        return $data;
    }

    public function campaign_input($args)
    {

        $params = array(
            'limit' => LIMIT_PUSHED_GUESTS_PER_CAMPAIGN,
            'order' => 'last_activity',
        );

        //get store
        $this->db->select("lat,lng,store_id");
        $this->db->where("id_event", $args['module_id']);
        $this->db->where("user_id", $args['user_id']);
        $obj = $this->db->get($args['module_name'], 1);
        $obj = $obj->result();

        if (count($obj) > 0) {
            $params['__module'] = "store";
            $params['__module_id'] = $obj[0]->store_id;
        }

        //custom parameter for option order by random guest or distance
        if (isset($args['custom_parameters']['getting_option'])
            && $args['custom_parameters']['getting_option'] == 1) {//

        } else if (isset($args['custom_parameters']['getting_option'])
            && $args['custom_parameters']['getting_option'] == 2) { //get guests by distance


            if (count($obj) > 0) {

                $store_id = $obj[0]->store_id;
                $this->db->select("latitude,longitude");
                $this->db->where("id_store", $store_id);
                $obj = $this->db->get("store", 1);
                $obj = $obj->result();

                if (count($obj) > 0) {
                    $params['lat'] = $obj[0]->latitude;
                    $params['lng'] = $obj[0]->longitude;
                }

            }

        } else if (isset($args['custom_parameters']['getting_option'])
            && $args['custom_parameters']['getting_option'] == 3) { //get guest by random and last_activity


        }


        //custom parameter for platforms
        if (isset($args['custom_parameters']['platforms'])
            && !empty($args['custom_parameters']['platforms'])) {

            foreach ($args['custom_parameters']['platforms'] as $key => $value) {
                if ($value == 1) {
                    $params['custom_parameter_platform'][] = $key;
                }
            }

            if (empty($params['custom_parameter_platform']))
                $params['custom_parameter_platform'][] = "unspecified";

        }


        $this->load->model("User/mUserModel");
        $data = $this->mUserModel->getGuests($params, function ($params) {

            if (ModulesChecker::isEnabled("bookmark") && _NOTIFICATION_AGREEMENT_USE) {

                $this->db->select('guest_id');

                $this->db->where("module", $params['__module']);
                $this->db->where("module_id", $params['__module_id']);
                $this->db->where('notification_agreement', 1);
                $this->db->where('guest_id !=', "");
                $guests = $this->db->get('bookmarks');
                $guests = $guests->result_array();

                $ids = array(0);

                foreach ($guests as $g) {
                    $ids[] = $g['guest_id'];
                }

                if (!empty($ids))
                    $this->db->where_in('id', $ids);

            }

            if (isset($params['custom_parameter_platform'])
                && !empty($params['custom_parameter_platform'])) {
                $this->db->where_in('platform', $params['custom_parameter_platform']);
            }

        });


        return $data;
    }

    public function campaign_output($campaign = array())
    {

        $type = $campaign['module_name'];
        $module_id = $campaign['module_id'];

        $this->db->where("id_event", $module_id);
        $this->db->where("status", 1);
        $obj = $this->db->get("event", 1);
        $obj = $obj->result_array();

        if (count($obj) > 0) {

            $data['title'] = Text::output($campaign['name']);
            $data['sub-title'] = Text::output($campaign['text']);
            //$data['sub-title'] = Text::output($obj[0]['name']);
            $data['id'] = $module_id;
            $data['type'] = $type;
            $data['image'] = ImageManagerUtils::getFirstImage($obj[0]['images']);

            $imgJson = json_decode($obj[0]['images'], JSON_OBJECT_AS_ARRAY);
            $data['image_id'] = $imgJson[0];

            return $data;
        }

        return NULL;
    }

    public function getEventsAnalytics($months = array(), $owner_id = 0)
    {

        $analytics = array();

        foreach ($months as $key => $m) {

            $last_month = date("Y-m-t", strtotime($key));
            $start_month = date("Y-m-1", strtotime($key));

            $this->db->where("created_at >=", $start_month);
            $this->db->where("created_at <=", $last_month);


            if ($owner_id > 0)
                $this->db->where('user_id', $owner_id);

            $count = $this->db->count_all_results("event");

            $index = date("m", strtotime($start_month));

            $analytics['months'][$key] = $count;

        }

        if ($owner_id > 0)
            $this->db->where('user_id', $owner_id);

        $analytics['count'] = $this->db->count_all_results("event");

        $analytics['count_label'] = Translate::sprint("Events");
        $analytics['color'] = "#00a65a";
        $analytics['icon_tag'] = "<i class=\"mdi mdi-calendar-month-outline\"></i>";
        $analytics['label'] = "Event";

        if($owner_id==0)
            $analytics['link'] = admin_url("event/all_events");
        else
            $analytics['link'] = admin_url("event/my_events");

        return $analytics;

    }


    public function markAsFeatured($params = array())
    {

        extract($params);


        if (!isset($type) and !isset($id) and !isset($featured))
            return array(Tags::SUCCESS => 0);


        $this->db->where("id_event", $id);
        $this->db->update("event", array(
            "featured" => intval($featured)
        ));

        return array(Tags::SUCCESS => 1);
    }

    public function delete($params = array())
    {

        extract($params);
        $data = array();
        $errors = array();


        if (!isset($id)) {
            $errors["event"] = Translate::sprint(Messages::EVENT_NOT_SPECIFIED);
        } else {
        }


        if (!isset($user_id)) {
            $errors["uid"] = Translate::sprint(Messages::USER_MISS_AUTHENTIFICATION);
        } else {
        }

        if (empty($errors) AND isset($data)) {
            $this->db->where("id_event", $id);

            $eventToDelete = $this->db->get("event", 1);
            $eventToDelete = $eventToDelete->result();
            if (count($eventToDelete) == 0) {
                $errors["event"] = Translate::sprint(Messages::EVENT_NOT_FOUND);
            } else {

                //Delete all images from this event
                if (isset($eventToDelete[0]->images)) {
                    $images = (array)json_decode($eventToDelete[0]->images);
                    foreach ($images AS $k => $v) {
                        _removeDir($v);
                    }
                }

                $this->db->where("id_event", $id);
                $this->db->delete("event");


                //send insert action
                ActionsManager::add_action("event","onDelete",array("id"=>$id));


                return array(Tags::SUCCESS => 1);
            }
        }

        return array(Tags::SUCCESS => 0);
    }


    private function checkEventBookingFields($params = array()){

        if(isset($params['booking']) && $params['booking']==0){
            return [];
        }else if(isset($params['booking']) && $params['booking']==1
            && isset($params['price']) && $params['price']>0){
            return [];
        }else if(isset($params['booking']) && $params['booking']==1
            && isset($params['price']) && intval($params['price'])==0){
            return [_lang("Price invalid, if you want provide free tickets, you can mark it as FREE")];
        }else{
            return [_lang("Event pricing not valid")];
        }

    }


    public function create($params = array())
    {

        $data = array();
        $errors = array();
        extract($params);


        if (isset($user_id) and $user_id > 0) {
            $data["user_id"] = intval($user_id);
        } else {
            $errors["user"] = Translate::sprint(Messages::USER_NOT_LOGGED_IN);
        }

        if (isset($store_id) and $store_id > 0 && isset($user_id) and $user_id > 0) {
            $user_id = intval($user_id);
            $this->db->where("user_id", $user_id);
            $this->db->where("id_store", $store_id);
            $count = $this->db->count_aLL_results("store");
            if ($count == 1) {
                $data['store_id'] = $store_id;
            }
        }

        if(!isset($data['store_id'])){
            $errors["store_id"] = _lang("Store is not valid!");
        }

        if (isset($images))
            $images = json_decode($images);
        else
            $images = array();

        if (!empty($images)) {
            $data['images'] = ImageManagerUtils::check($images);
        }

        if (isset($data["images"]) and empty($data["images"])) {
            $errors['images'] = Translate::sprint("Please upload an image");
        }

        if (isset($name) AND $name != "") {
            $data["name"] = Text::input($name);
        } else {
            $errors['name'] = Translate::sprint(Messages::EVENT_NAME_INVALID);
        }

        if (isset($description) AND $description != "") {
            $data["description"] = Text::input($description, TRUE);
        } else {
            $errors['description'] = Translate::sprint(Messages::EVENT_DESCRIPTION_INVALID);
        }

        if (isset($address) AND $address != "") {
            $data["address"] = trim($address);
        } else {
            $errors['address'] = Translate::sprint(Messages::STORE_ADDRESS_EMPTY);
        }

        if (isset($detail) AND $detail != "") {
            $data["detail"] = Text::input($detail);
        }

        if (isset($tel) && $tel != "" && isset($telCode)) {
            if (preg_match("#^[0-9+]+$#i", $tel) && in_array($telCode,loadPhoneCodesKey())) {

                $data["tel"] = $telCode.$tel;

                $codes = loadPhoneCodes();
                foreach ($codes as $code){
                    if($code['dial_code']==$telCode)
                        $data["country_code"] = $code['code'];
                }

            } else {
                $errors['tel'] = Translate::sprint(Messages::EVENT_PHONE_INVALID);
            }
        }

        if (isset($date_b) AND $date_b != "") {
            if (Text::validateDate($date_b, DateSetting::defaultFormat0())) {
                $data["date_b"] = DateSetting::parseServer($date_b);
            } else {
                $errors["date_e"] = Translate::sprint(messages::EVENT_DATE_BEGIN_INVALID);
            }
        } else {
            $errors["date_b"] = Translate::sprint(messages::EVENT_DATE_BEGIN_INVALID);
        }

        if (isset($date_e) AND $date_e != "") {
            if (Text::validateDate($date_e, DateSetting::defaultFormat0())) {
                $data["date_e"] = DateSetting::parseServer($date_e);
            } else {
                $errors["date_e"] = Translate::sprint(messages::EVENT_DATE_END_INVALID);
            }
        } else {
            $errors["date_e"] = Translate::sprint(messages::EVENT_DATE_END_INVALID);
        }

        if (isset($website) and $website != "") {
            if (filter_var($website, FILTER_VALIDATE_URL)) {
                $pattern = '/^(?:[;\/?:@&=+$,]|(?:[^\W_]|[-_.!~*\()\[\] ])|(?:%[\da-fA-F]{2}))*$/';
                if (preg_match($pattern, $website)) {
                    $data['website'] = Text::input($website);
                } else {
                    $errors['website'] = Translate::sprint(Messages::EVENT_WEBSITE_INVALID);
                }
            } else {
                $errors['website'] = Translate::sprint(Messages::EVENT_WEBSITE_INVALID);
            }
        }

        if ((!isset($lat) && !isset($lng)) OR ($lat == 0 AND $lng == 0)) {
            $errors['location'] = Translate::sprint(Messages::EVENT_POSITION_NOT_FOUND);
        } else {
            $data["lat"] = $lat;
            $data["lng"] = $lng;
        }

        $pricing = $this->checkEventBookingFields($params);
        if(!empty($pricing)){
            $errors['booking'] = $pricing[0];
        }else if($params['booking']==0){
            $data['price'] = 0;
        }else if($params['booking']==1){
            if(ConfigManager::getValue('EVENT_BOOK_COMMISSION_ENABLED')){
                $data['price'] = $params['price'] + ($params['price'] * ConfigManager::getValue('EVENT_BOOK_COMMISSION_VALUE') / 100);
                $data['commission'] = ($params['price'] * ConfigManager::getValue('EVENT_BOOK_COMMISSION_VALUE') / 100);
            }else{
                $data['price'] = $params['price'];
            }
        }

        $cf_id = ConfigManager::getValue("Default_Event_Booking_fields");
        if($cf_id>0){
            $data['cf_id'] = $cf_id;
        }



        if (empty($errors) AND !empty($data)) {
            $nbr_events_monthly = UserSettingSubscribe::getUDBSetting($user_id, KS_NBR_EVENTS_MONTHLY);

            if ($nbr_events_monthly > 0 || $nbr_events_monthly == -1) {
                if(ConfigManager::getValue("ANYTHINGS_APPROVAL")){
                    $data['status'] = 0;
                }else{
                    $data['status'] = 1;
                }

                // current date from the system
                $data['date_created'] = date("Y-m-d H:i:s", time());
                $data['created_at'] = $data["date_created"];
                $this->db->insert("event", $data);

                $event_id = $this->db->insert_id();

                if ($nbr_events_monthly > 0) {
                    $nbr_events_monthly--;
                    UserSettingSubscribe::refreshUSetting($user_id, KS_NBR_EVENTS_MONTHLY, $nbr_events_monthly);
                }


                //manage uri & seo
                $this->createURI($event_id,$data['name']);

                //send insert action
                ActionsManager::add_action("event","onAdd",array("id"=>$event_id));

                return array(Tags::SUCCESS => 1, "url" => admin_url("event/events"));

            } else {
                $errors["events"] = Translate::sprint(Text::_print(Messages::EXCEEDED_MAX_NBR_EVENTS));
            }
        }

        return array(Tags::SUCCESS => 0, Tags::ERRORS => $errors);
    }

    public function edit($params = array())
    {

        $data = array();
        $errors = array();
        extract($params);


        if (isset($event_id) and $event_id > 0) {
            $data["id_event"] = intval($event_id);
        } else {
            $errors["id_event"] = Translate::sprint(Messages::EVENT_NOT_FOUND);
        }

        if (isset($user_id) and $user_id > 0) {
            $data["user_id"] = intval($user_id);
        } else {
            $errors["user"] = Translate::sprint(Messages::USER_NOT_LOGGED_IN);
        }


        if(empty($errors)){
            $this->db->where('user_id',$data["user_id"]);
            $this->db->where('id_event',$data["id_event"]);
            $count = $this->db->count_all_results('event');
            if($count==0){
                $errors['x'] = _lang(Messages::PERMISSION_LIMITED);
            }
        }

        if(!empty($errors)){
            return array(Tags::SUCCESS=>0,Tags::ERRORS=>$errors);
        }

        if (isset($store_id) and $store_id > 0 && isset($user_id) and $user_id > 0) {
            $user_id = intval($user_id);
            $this->db->where("user_id", $user_id);
            $this->db->where("id_store", $store_id);
            $count = $this->db->count_aLL_results("store");
            if ($count == 1) {
                $data['store_id'] = $store_id;
            }
        }

        if(!isset($data['store_id'])){
            $errors["store_id"] = _lang("Store is not valid!");
        }

        if (isset($images))
            $images = json_decode($images);
        else
            $images = array();

        if (!empty($images)) {
            $data['images'] = ImageManagerUtils::check($images);
        }

        if (isset($data["images"]) and empty($data["images"])) {
            $errors['images'] = Translate::sprint("Please upload an image");
        }

        if (isset($name) AND $name != "") {
            $data["name"] = Text::input($name);
        } else {
            $errors['name'] = Translate::sprint(Messages::EVENT_NAME_INVALID);
        }

        if (isset($description) AND $description != "") {
            $data["description"] = Text::input($description, TRUE);
        } else {
            $errors['description'] = Translate::sprint(Messages::EVENT_DESCRIPTION_INVALID);
        }

        if (isset($address) AND $address != "") {
            $data["address"] = trim($address);
        } else {
            $errors['address'] = Translate::sprint(Messages::STORE_ADDRESS_EMPTY);
        }

        if (isset($detail) AND $detail != "") {
            $data["detail"] = Text::input($detail);
        }

        if (isset($tel) && $tel != "" && isset($telCode)) {
            if (preg_match("#^[0-9+]+$#i", $tel) && in_array($telCode,loadPhoneCodesKey())) {

                $data["tel"] = $telCode.$tel;

                $codes = loadPhoneCodes();
                foreach ($codes as $code){
                    if($code['dial_code']==$telCode)
                        $data["country_code"] = $code['code'];
                }

            } else {
                $errors['tel'] = Translate::sprint(Messages::EVENT_PHONE_INVALID);
            }
        }

        if (isset($date_b) AND $date_b != "") {
            if (Text::validateDate($date_b, DateSetting::defaultFormat0())) {
                $data["date_b"] = DateSetting::parseServer($date_b);
            } else {
                $errors["date_e"] = Translate::sprint(messages::EVENT_DATE_BEGIN_INVALID);
            }
        } else {
            $errors["date_b"] = Translate::sprint(messages::EVENT_DATE_BEGIN_INVALID);
        }

        if (isset($date_e) AND $date_e != "") {
            if (Text::validateDate($date_e, DateSetting::defaultFormat0())) {
                $data["date_e"] = DateSetting::parseServer($date_e);
            } else {
                $errors["date_e"] = Translate::sprint(messages::EVENT_DATE_END_INVALID);
            }
        } else {
            $errors["date_e"] = Translate::sprint(messages::EVENT_DATE_END_INVALID);
        }

        if (isset($website) and $website != "") {
            if (filter_var($website, FILTER_VALIDATE_URL)) {
                $pattern = '/^(?:[;\/?:@&=+$,]|(?:[^\W_]|[-_.!~*\()\[\] ])|(?:%[\da-fA-F]{2}))*$/';
                if (preg_match($pattern, $website)) {
                    $data['website'] = Text::input($website);
                } else {
                    $errors['website'] = Translate::sprint(Messages::EVENT_WEBSITE_INVALID);
                }
            } else {
                $errors['website'] = Translate::sprint(Messages::EVENT_WEBSITE_INVALID);
            }
        }

        if ((!isset($lat) && !isset($lng)) OR ($lat == 0 AND $lng == 0)) {
            $errors['location'] = Translate::sprint(Messages::EVENT_POSITION_NOT_FOUND);
        } else {
            $data["lat"] = $lat;
            $data["lng"] = $lng;
        }

        $pricing = $this->checkEventBookingFields($params);
        if(!empty($pricing)){
            $errors['booking'] = $pricing[0];
        }else if($params['booking']==0){
            $data['price'] = 0;
        }else if($params['booking']==1){
            if(ConfigManager::getValue('EVENT_BOOK_COMMISSION_ENABLED')){
                $data['price'] = $params['price'] + ($params['price'] * ConfigManager::getValue('EVENT_BOOK_COMMISSION_VALUE') / 100);
                $data['commission'] = ($params['price'] * ConfigManager::getValue('EVENT_BOOK_COMMISSION_VALUE') / 100);
            }else{
                $data['price'] = $params['price'];
            }
        }

        $cf_id = ConfigManager::getValue("Default_Event_Booking_fields");
        if($cf_id>0){
            $data['cf_id'] = $cf_id;
        }




        if (empty($errors) AND !empty($data)) {
            $nbr_events_monthly = UserSettingSubscribe::getUDBSetting($user_id, KS_NBR_EVENTS_MONTHLY);

            if ($nbr_events_monthly > 0 || $nbr_events_monthly == -1) {
                if(ConfigManager::getValue("ANYTHINGS_APPROVAL")){
                    $data['status'] = 0;
                }else{
                    $data['status'] = 1;
                }

                // current date from the system
                $data['date_created'] = date("Y-m-d H:i:s", time());
                $data['created_at'] = $data["date_created"];

                $this->db->where('id_event',$data['id_event']);
                $this->db->update("event", $data);


                //manage uri & seo
                $this->createURI($event_id,$data['name']);

                //send insert action
                ActionsManager::add_action("event","onUpdated",array("id"=>$event_id));

                return array(Tags::SUCCESS => 1, "url" => admin_url("event/events"));

            } else {
                $errors["events"] = Translate::sprint(Text::_print(Messages::EXCEEDED_MAX_NBR_EVENTS));
            }
        }

        return array(Tags::SUCCESS => 0, Tags::ERRORS => $errors);
    }

    private function createURI($event_id,$title){

        $module = FModuleLoader::getModuleDetail("cms");
        if (version_compare($module['version_name'], '2.0.1') >= 0) {
            $slug = CMSUtils::createSlug("$event_id-".$title,"-");
            $uri = "detail-event/".$event_id;
            CMSUtils::addNewSlug($slug,$uri);
        }
    }

    public function switchTo($old_owner = 0, $new_owner = 0)
    {

        if ($new_owner > 0) {

            $this->db->where("id_user", $new_owner);
            $c = $this->db->count_all_results("user");
            if ($c > 0) {

                $this->db->where("user_id", $old_owner);
                $this->db->update("event", array(
                    "user_id" => $new_owner
                ));

                return TRUE;
            }

        }

        return FALSE;
    }

    public function getEvents($params = array(), $whereArray = array(), $callback = NULL)
    {

        $errors = array();
        $data = array();

        //extract — Importe les variables dans la table des symboles
        extract($params);


        if (!isset($page))
            $page = 1;


        if (!isset($page) OR $page == 0) {
            $page = 1;
        }

        if (!isset($limit)) {
            $limit = 20;
        }


        if ($limit == 0) {
            $limit = 20;
        }

        if($limit===-1){
            $limit = 99999999999;
        }


        if (!empty($whereArray))
            foreach ($whereArray as $key => $value) {
                $this->db->where($key, $value);
            }

        if ($callback != NULL)
            call_user_func($callback, $params);


        if (isset($is_featured) and $is_featured == 1) {
            $this->db->where("event.featured", 1);
        }

        if (isset($status) and $status >= 0) {
            $this->db->where("event.status", $status);
        }


        if (isset($user_id) and $user_id >= 0) {
            $this->db->where("event.user_id", $user_id);
        }


        if (isset($search) and $search != "") {
            $this->db->group_start();
            $this->db->like('event.name', $search);
            $this->db->or_like('event.address', $search);
            $this->db->or_like('event.description', $search);
            $this->db->group_end();
        }


        if (isset($event_ids) && $event_ids != "") {

            if (preg_match("#^([0-9,]+)$#", $event_ids)) {
                $new_ids = explode(",", $event_ids);
                foreach ($new_ids as $key => $id) {
                    $new_ids[$key] = intval($id);
                }

                $this->db->where_in("event.id_event", $new_ids);
            }

        }

        if (isset($store_id) and $store_id > 0) {
            $this->db->where("event.store_id  >=", $store_id);
        }

        if (isset($event_id) and $event_id > 0) {
            $this->db->where("event.id_event  >=", $event_id);
        }

        if (isset($date_end) and $date_end != "") {
            $this->db->where("event.date_e  >=", $date_end);
        }

        if (isset($category_id) and $category_id > 0) {
            $this->db->where("store.category_id", $category_id);
        }

        $calcul_distance = "";

        if (
            isset($longitude)
            AND
            isset($latitude)

        ) {


            $latitude = doubleval($latitude);
            $longitude = doubleval($longitude);

            $calcul_distance = " , IF( event.lat = 0,99999,  (1000 * ( 6371 * acos (
                              cos ( radians(" . $latitude . ") )
                              * cos( radians( event.lat ) )
                              * cos( radians( event.lng ) - radians(" . $longitude . ") )
                              + sin ( radians(" . $latitude . ") )
                              * sin( radians( event.lat ) )
                            )
                          ) ) ) as 'distance'  ";

        }


        $this->db->join("store", "store.id_store=event.store_id", "left outer");
        $count = $this->db->count_all_results("event");


        $pagination = new Pagination();
        $pagination->setCount($count);
        $pagination->setCurrent_page($page);
        $pagination->setPer_page($limit);
        $pagination->calcul();

        if ($count == 0)
            return array(Tags::SUCCESS => 1, "pagination" => $pagination, Tags::COUNT => $count, Tags::RESULT => array());


        if (!empty($whereArray))
            foreach ($whereArray as $key => $value) {
                $this->db->where($key, $value);
            }

        if ($callback != NULL)
            call_user_func($callback, $params);

        //$this->db->where('store.hidden', 0);

        if (isset($is_featured) and $is_featured == 1) {
            $this->db->where("event.featured", 1);
        }

        if (isset($status) and $status >= 0) {
            $this->db->where("event.status", $status);
        }

        if (isset($user_id) and $user_id >= 0) {
            $this->db->where("event.user_id", $user_id);
        }

        if (isset($search) and $search != "") {
            $this->db->group_start();
            $this->db->like('event.name', $search);
            $this->db->or_like('event.address', $search);
            $this->db->or_like('event.description', $search);
            $this->db->group_end();
        }



        if (isset($event_ids) && $event_ids != "") {

            if (preg_match("#^([0-9,]+)$#", $event_ids)) {
                $new_ids = explode(",", $event_ids);

                $this->db->where_in("event.id_event", $new_ids);
            }

        }

        if (isset($store_id) and $store_id > 0) {
            $this->db->where("event.store_id  >=", $store_id);
        }

        if (isset($event_id) and $event_id > 0) {
            $this->db->where("event.id_event", $event_id);
        }

        if (isset($date_end) and $date_end != "") {
            $this->db->where("event.date_e  >=", $date_end);
        }

        if (isset($category_id) and $category_id > 0) {
            $this->db->where("store.category_id", $category_id);
        }

        $this->db->join("store", "store.id_store=event.store_id", "left outer");

        $this->db->select("store.id_store as 'store_id',store.name as 'store_name',event.*" . $calcul_distance, FALSE);
        $this->db->limit($pagination->getPer_page(), $pagination->getFirst_nbr());


        if (isset ($order_by) AND $order_by == "recent") {
            $this->db->order_by("event.id_event", "DESC");
        } else if ($calcul_distance != "" && isset ($order_by) && $order_by == "nearby") {
            $this->db->order_by("distance", "ASC");
        } else if ($calcul_distance != "" && isset ($order_by) && $order_by == "upcoming") {
            $this->db->where("event.date_b >", date('Y-m-d H:i',time()));
            $this->db->order_by("event.date_b", "ASC");
        } else {
            $this->db->order_by("event.id_event", "DESC");
        }


        if (isset($radius) and $radius > 0 && $calcul_distance != "")
            $this->db->having('distance <= ' . intval($radius), NULL, FALSE);


        $this->db->from("event");

        $events = $this->db->get();
        $events = $events->result_array();

        if (count($events) < $limit) {
            $count = count($events);
        }

        $new_events_results = array();

        foreach ($events as $key => $event) {

            $new_events_results[$key] = $event;

            if($this->isSaved("event",$event['id_event']))
                $new_events_results[$key]['saved'] = 1;
            else
                $new_events_results[$key]['saved'] = 0;


            if (isset($event['images'])) {

                $images = (array)json_decode($event['images']);
                $new_events_results[$key]['images'] = array();
                // $new_stores_results[$key]['image'] = $store['images'];
                foreach ($images AS $k => $v) {
                    $imgs = _openDir($v);
                    if (!empty($imgs))
                        $new_events_results[$key]['images'][] = $imgs;
                }

            } else {
                $new_events_results[$key]['images'] = array();
            }


            $new_events_results[$key]['link'] = site_url("event/id/" . $event["id_event"]);

            
            if ($event['cf_id'] > 0)
                $new_events_results[$key]['cf'] = $this->mCFManager->getList0($event['cf_id']);
            else
                $new_events_results[$key]['cf'] = array();


            if ($event['price'] > 0)
                $new_events_results[$key]['priceFormatted'] = Currency::parseCurrencyFormat( $event['price'], ConfigManager::getValue('DEFAULT_CURRENCY'));
            else
                $new_events_results[$key]['priceFormatted'] = Currency::parseCurrencyFormat(0,ConfigManager::getValue('DEFAULT_CURRENCY'));;


            $new_events_results[$key]['wishlist'] = $this->wishlistCounter($event["id_event"]);



        }


        $object = ActionsManager::return_action("event", "func_getEvents", $new_events_results);
        if ($object != NULL)
            $new_events_results = $object;


        if ($calcul_distance != "" && isset ($order_by) && $order_by =="nearby") {
            $new_events_results = $this->re_order_featured_item($new_events_results);
        }

        return array(Tags::SUCCESS => 1, "pagination" => $pagination, Tags::COUNT => $count, Tags::RESULT => $new_events_results);

    }

    private function isSaved($module,$module_id){

        $user_id = Security::decrypt($this->input->get_request_header('Session-User-Id', 0));
        $guest_id = Security::decrypt($this->input->get_request_header('Session-Guest-Id', 0));

        if($user_id > 0){

            $this->db->where("module",$module);
            $this->db->where("module_id",$module_id);
            $this->db->where("user_id",$user_id);

            $c = $this->db->count_all_results("bookmarks");

            if($c>0)
                return TRUE;

        }else  if($guest_id > 0){

            $this->db->where("module",$module);
            $this->db->where("module_id",$module_id);
            $this->db->where("guest_id",$user_id);

            $c = $this->db->count_all_results("bookmarks");

            if($c>0)
                return TRUE;

        }

        return FALSE;
    }


    public function re_order_featured_item($data = array())
    {

        $new_data = array();

        foreach ($data as $key => $value) {
            if ($value['featured'] == 1) {
                $new_data[] = $data[$key];
                unset($data[$key]);
            }
        }


        foreach ($data as $value) {
            $new_data[] = $value;
        }


        return $new_data;
    }

    public function isOwn($user_id,$event_id){

        $this->db->where('user_id',$user_id);
        $this->db->where('id_event',$event_id);

        if($this->db->count_all_results('event')>0){
            return TRUE;
        }

        return FALSE;
    }

    public function getParticipants($params = array(), $whereArray = array(), $callback = NULL)
    {

        //params login password mac_address
        $errors = array();
        $data = array();

        //extract — Importe les variables dans la table des symboles
        extract($params);

        if (!isset($page))
            $page = 1;


        if (!isset($page) OR $page == 0) {
            $page = 1;
        }

        if (!isset($limit)) {
            $limit = 20;
        }

        if ($limit == 0) {
            $limit = 20;
        }

        if (!empty($whereArray))
            foreach ($whereArray as $key => $value) {
                $this->db->where($key, $value);
            }

        if ($callback != NULL)
            call_user_func($callback, $params);


        if (isset($status) and $status >= 0) {
            $this->db->where("event.status", $status);
        }

        if (isset($event_id) && $event_id > 0) {
            $this->db->where("event.id_event", $event_id);
        }

        if (isset($owner_id) && $owner_id > 0) {
            $this->db->where("event.user_id", $owner_id);
        }

        if (isset($id) && $id > 0) {
            $this->db->where("participants.id", $id);
        }

        if (isset($user_id) && $user_id > 0) {
            $this->db->where("participants.user_id", $user_id);
        }

        $this->db->join("event", "event.id_event=participants.event_id", "inner");
        $this->db->join("user", "user.id_user=participants.user_id", "inner");

        $count = $this->db->count_all_results("participants");


        $pagination = new Pagination();
        $pagination->setCount($count);
        $pagination->setCurrent_page($page);
        $pagination->setPer_page($limit);
        $pagination->calcul();



        if (!empty($whereArray))
            foreach ($whereArray as $key => $value) {
                $this->db->where($key, $value);
            }

        if ($callback != NULL)
            call_user_func($callback, $params);


        if (isset($status) and $status >= 0) {
            $this->db->where("event.status", $status);
        }

        if (isset($event_id) && $event_id > 0) {
            $this->db->where("event.id_event", $event_id);
        }

        if (isset($owner_id) && $owner_id > 0) {
            $this->db->where("event.user_id", $owner_id);
        }

        if (isset($id) && $id > 0) {
            $this->db->where("participants.id", $id);
        }

        if (isset($user_id) && $user_id > 0) {
            $this->db->where("participants.user_id", $user_id);
        }


        $this->db->join("event", "event.id_event=participants.event_id", "inner");
        $this->db->join("user", "user.id_user=participants.user_id", "inner");

        $this->db->from("participants");
        $this->db->select("user.name as 'user_name',
        user.email as 'user_email',
        event.name as 'event_name',
        event.images as 'images',
        user.guest_id as 'user_guest_id',
        event.date_b as 'event_date_b',
        event.date_e as 'event_date_e',
        participants.*", FALSE);
        $this->db->limit($pagination->getPer_page(), $pagination->getFirst_nbr());

        $this->db->order_by("id", "desc");
        $participants = $this->db->get();
        $participants = $participants->result_array();


        foreach ($participants as $key => $participant){
            if (isset($participant['images'])) {
                $images = (array)json_decode($participant['images']);
                $participants[$key]['images'] = array();
                foreach ($images AS $k => $v) {
                    $imgs = _openDir($v);
                    if (!empty($imgs))
                        $participants[$key]['images'][] = $imgs;
                }

            } else {
                $participants[$key]['images'] = array();
            }
        }

        return array(Tags::SUCCESS => 1, "pagination" => $pagination, Tags::COUNT => $count, Tags::RESULT => $participants);

    }

    public function updateEvent($params = array())
    {

        $errors = array();
        $data = array();
        extract($params);


        if (isset($user_id) && $user_id > 0) {
            $data["user_id"] = intval($user_id);
        } else {

            $errors["user"] = Translate::sprint(Messages::USER_NOT_LOGGED_IN);

        }

        if (empty($errors) && isset($store_id)) {

            if ($store_id > 0) {
                $this->db->where("user_id", $user_id);
                $this->db->where("id_store", $store_id);
                $count = $this->db->count_aLL_results("store");

                if ($count == 1) {
                    $data['store_id'] = $store_id;
                }
            } else {
                $data['store_id'] = null;

            }

        }


        if (isset($images))
            $images = json_decode($images);
        else
            $images = array();

        if (!empty($images)) {

            $data["images"] = array();
            $i = 0;

            try {
                if (!empty($images)) {
                    foreach ($images as $value) {
                        $data["images"][$i] = $value;
                        $i++;
                    }

                    $data["images"] = json_encode($data["images"], JSON_FORCE_OBJECT);
                }
            } catch (Exception $e) {

            }

        }


        if (isset($id) AND $id > 0) {
            $data["id_event"] = $id;
        } else {
            $errors['name'] = Translate::sprint(Messages::EVENT_NOT_SPECIFIED);
        }


        if (isset($name) AND $name != "") {
            $data["name"] = Text::input($name);
        } else {
            $errors['name'] = Translate::sprint(Messages::EVENT_NAME_EMPTY);
        }

        if (isset($desc) AND $desc != "") {
            $data["description"] = Text::input($desc, TRUE);
        } else {
            $errors['description'] = Translate::sprint(Messages::EVENT_DESCRIPTION_EMPTY);
        }


        if (isset($address) AND $address != "") {
            $data["address"] = $address;
        } else {
            $errors['address'] = Translate::sprint(Messages::EVENT_ADRESSE_EMPTY);
        }


        if (isset($detail) AND $detail != "") {
            $data["detail"] = $detail;
        }

        if (isset($tel) AND $tel != "") {
            if (preg_match("#^[0-9 \-_.\(\)\+]+$#i", $tel)) {
                $data["tel"] = $tel;
            } else {
                $errors['tel'] = Translate::sprint(Messages::EVENT_PHONE_INVALID);
            }


        }else{
            $data['tel'] = NULL;
        }

        if (isset($date_b) AND $date_b != "") {
            if (Text::validateDate($date_b)) {
                $data["date_b"] = date('Y-m-d', strtotime($date_b));
                $data["date_b"] = MyDateUtils::convert($data["date_b"], TimeZoneManager::getTimeZone(), "UTC");

            } else {
                $errors["date_b"] = Translate::sprint(Messages::EVENT_DATE_BEGIN_INVALID);
            }

        } else {
            $errors["date_b"] = Translate::sprint(Messages::EVENT_DATE_BEGIN_INVALID);
        }

        if (isset($date_e) AND $date_e != "") {
            if (Text::validateDate($date_e)) {
                $data["date_e"] = date('Y-m-d', strtotime($date_e));
                $data["date_e"] = MyDateUtils::convert($data["date_e"], TimeZoneManager::getTimeZone(), "UTC");

            } else {
                $errors["date_e"] = Translate::sprint(Messages::EVENT_DATE_END_INVALID);
            }
        } else {
            $errors["date_e"] = Translate::sprint(Messages::EVENT_DATE_END_INVALID);
        }


        if (isset($website) and $website != "") {

            if (filter_var($website, FILTER_VALIDATE_URL)) {
                $pattern = '/^(?:[;\/?:@&=+$,]|(?:[^\W_]|[-_.!~*\()\[\] ])|(?:%[\da-fA-F]{2}))*$/';
                if (preg_match($pattern, $website)) {
                    $data['website'] = Text::input($website);
                } else {
                    $errors['website'] = Translate::sprint(Messages::EVENT_WEBSITE_INVALID);
                }
            } else {
                $errors['website'] = Translate::sprint(Messages::EVENT_WEBSITE_INVALID);
            }

        }


        if ((!isset($lat) && !isset($lng)) OR ($lat == 0 AND $lng == 0)) {
            $errors['location'] = Translate::sprint(Messages::EVENT_POSITION_NOT_FOUND);
        } else {
            $data["lat"] = $lat;
            $data["lng"] = $lng;
        }


        if (empty($errors) AND !empty($data)) {

            $date = date("Y-m-d H:i:s", time());
            $data['updated_at'] = MyDateUtils::convert($date, TimeZoneManager::getTimeZone(), "UTC");

            if(@ModulesChecker::isEnabled('location_picker')){
                $location_detail = LocationPickerManager::getAddressDetail($data['lat'],$data['lng']);
                if(isset($location_detail[0])){
                    if(isset($location_detail[0]['city'])) $data['city'] = $location_detail[0]['city'];
                    if(isset($location_detail[0]['country'] )) $data['country'] = $location_detail[0]['country'];
                    if(isset($location_detail[0]['country_code'])) $data['country_code'] = $location_detail[0]['country_code'];
                }
            }

            $this->db->where("id_event", $data["id_event"]);
            $this->db->update("event", $data);

            return array(Tags::SUCCESS => 1, "url" => admin_url("event/events"));
        } else {
            return array(Tags::SUCCESS => 0, Tags::ERRORS => $errors);
        }


    }


    public function wishlistCounter($id)
    {

        $this->db->where('module_id', $id);
        $this->db->where('module', "event");
        return $this->db->count_all_results('bookmarks');

    }


    public function create_default_checkout_fields()
    {

        $pdc_cf = ConfigManager::getValue("Default_Event_Booking_fields");
        $pdc_cf = intval($pdc_cf);


        if ($pdc_cf == 0) {

            $fields = array(
                0 => array(
                    "type" => "input.text",
                    "label" => "Full name",
                    "required" => 1,
                    "order" => 1,
                    "step" => 1,
                ),
                1 => array(
                    "type" => "input.phone",
                    "label" => "Phone",
                    "required" => 1,
                    "order" => 2,
                    "step" => 1,
                )
            );

            $label = "Default_Event_Booking_fields";

            $result = $this->mCFManager->createCustomFields(array(
                "fields" => $fields,
                "label" => $label,
                "user_id" => SessionManager::getData("id_user"),
            ));

            if ($result[Tags::SUCCESS] == 1) {
                $id = $this->db->insert_id();
                $this->db->where("id", $id);
                $this->db->update("cf_list", array(
                    'editable' => 0
                ));

                ConfigManager::setValue("Default_Event_Booking_fields",$id);
                return $id;
            }


        }

        return 0;
    }


    public function getMyAllEvents($params = array())
    {

        $errors = array();
        $data = array();

        extract($params);

        if (isset($user_id) and $user_id > 0) {

            $events = $this->getEvents(array(
                'order_by'=>"recent",
                'limit'=>-1,
                'user_id'=>intval($user_id),
                'status'=>1,
            ));

            return $events;
        }

        return array(Tags::SUCCESS => 0);
    }

    function hiddenEventsOutOfDate()
    {
        $this->db->select("date_e,id_event");
        $this->db->where("status", 1);
        $events = $this->db->get("event");
        $events = $events->result_array();

        if (count($events) > 0) {
            $currentDate = date("Y-m-d H:i:s", time());
            $currentDate = MyDateUtils::convert($currentDate, TimeZoneManager::getTimeZone(), "UTC", "Y-m-d H:i:s");
            foreach ($events as $value) {
                if (strtotime($value["date_e"]) < strtotime($currentDate)) {
                    $this->db->where("id_event", $value["id_event"]);
                    $this->db->update("event", array(
                        "status" => 0));
                }
            }
            return array(Tags::SUCCESS => 1);
        } else {
            return array(Tags::SUCCESS => 0);
        }
    }



    public function updateFields()
    {

        if (!$this->db->field_exists('cf_id', 'event')) {
            $fields = array(
                'cf_id' => array('type' => 'INT', 'after' => 'verified', 'default' => 0),
            );
            $this->dbforge->add_column('event', $fields);
        }

        if (!$this->db->field_exists('limit', 'event')) {
            $fields = array(
                'limit' => array('type' => 'INT', 'after' => 'verified', 'default' => 0),
            );
            $this->dbforge->add_column('event', $fields);
        }

        if (!$this->db->field_exists('price', 'event')) {
            $fields = array(
                'price' => array('type' => 'DOUBLE', 'after' => 'verified', 'default' => 0),
            );
            $this->dbforge->add_column('event', $fields);
        }

        if (!$this->db->field_exists('commission', 'event')) {
            $fields = array(
                'commission' => array('type' => 'DOUBLE', 'after' => 'verified', 'default' => 0),
            );
            $this->dbforge->add_column('event', $fields);
        }

        if (!$this->db->field_exists('verified', 'event')) {
            $fields = array(
                'verified' => array('type' => 'INT', 'after' => 'tags', 'default' => 0),
            );
            $this->dbforge->add_column('event', $fields);
        }

        if (!$this->db->field_exists('created_at', 'event')) {
            $fields = array(
                'created_at' => array('type' => 'DATETIME', 'after' => 'tags', 'default' => NULL),
            );
            $this->dbforge->add_column('event', $fields);
        }

        if (!$this->db->field_exists('updated_at', 'event')) {
            $fields = array(
                'updated_at' => array('type' => 'DATETIME', 'after' => 'created_at', 'default' => NULL),
            );
            $this->dbforge->add_column('event', $fields);
        }

        if (!$this->db->field_exists('status', 'bookmarks')) {
            $fields = array(
                'status' => array('type' => 'INT', 'default' => 0),
            );
            $this->dbforge->add_column('bookmarks', $fields);
        }

        if (!$this->db->field_exists('name', 'event')) {
            // Define the column definition
            $column_definition = array(
                'name' => array(
                    'type' => 'TEXT',
                    'null' => true,
                    'collation' => 'utf8_general_ci',
                ),
            );
            // Add the 'name' column to the 'event' table
            $this->dbforge->add_column('event', $column_definition);
        }

    }


    public function add_event_country_field()
    {

        if (!$this->db->field_exists('city', 'event')) {
            $fields = array(
                'city' => array('type' => 'VARCHAR(60)', 'after' => 'user_id', 'default' => NULL),
            );
            // modify_column : The usage of this method is identical to add_column(), except it alters an existing column rather than adding a new one.
            $this->dbforge->add_column('event', $fields);
        }

        if (!$this->db->field_exists('country', 'event')) {
            $fields = array(
                'country' => array('type' => 'VARCHAR(60)', 'after' => 'user_id', 'default' => NULL),
            );
            // modify_column : The usage of this method is identical to add_column(), except it alters an existing column rather than adding a new one.
            $this->dbforge->add_column('event', $fields);
        }

        if (!$this->db->field_exists('country_code', 'event')) {
            $fields = array(
                'country_code' => array('type' => 'VARCHAR(3)', 'after' => 'user_id', 'default' => NULL),
            );
            // modify_column : The usage of this method is identical to add_column(), except it alters an existing column rather than adding a new one.
            $this->dbforge->add_column('event', $fields);
        }


    }


    public function createTable()
    {

        if ($this->db->table_exists('participants') )
            return;

        $this->load->dbforge();
        $this->dbforge->add_field(array(
            'id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE
            ),
            'user_id' => array(
                'type' => 'INT',
                'default' => NULL
            ),
            'full_name' => array(
                'type' => 'VARCHAR(250)',
                'default' => NULL
            ),
            'phone' => array(
                'type' => 'VARCHAR(150)',
                'default' => NULL
            ),
            'email' => array(
                'type' => 'VARCHAR(150)',
                'default' => NULL
            ),
            'event_id' => array(
                'type' => 'INT',
                'default' => NULL
            ),
            'attachements' => array(
                'type' => 'TEXT',
                'default' => NULL
            ),
            'status' => array(
                'type' => 'INT',
                'default' => 0
            ),
            'booking_id' => array(
                'type' => 'INT',
                'default' => 0
            ),
            'updated_at' => array(
                'type' => 'DATETIME',
                'default' => NULL
            ),
            'created_at' => array(
                'type' => 'DATETIME',
                'default' => NULL
            ),
        ));

        $attributes = array('ENGINE' => 'InnoDB');
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('participants', TRUE, $attributes);

    }




}