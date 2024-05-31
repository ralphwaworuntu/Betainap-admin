<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of event_webservice
 *
 * @author idriss
 */
class Ajax extends AJAX_Controller
{

    public function __construct()
    {
        parent::__construct();

        $this->load->model("event/event_model", "mEventModel");
        $this->load->model("store/store_model", "mStoreModel");
        $this->load->model("user/user_model", "mUserModel");
        $this->load->model("user/user_browser", "mUserBrowser");

    }



    public function saveParticipant(){

        if (!GroupAccess::isGranted('event',MANAGE_EVENTS_PARTICIPANTS))
            redirect("error?page=permission");

        $id = RequestInput::post('id');
        $attachement = RequestInput::post('attachement');
        $status = RequestInput::post('status');
        $eventId = RequestInput::post('event_id');

        $result = $this->mEventModel->saveParticipant(
            $id,
            $eventId,
            $attachement,
            $status,
            SessionManager::getData('id_user')
        );

        echo json_encode($result);return;
    }


    public function sendTicket(){

        if (!GroupAccess::isGranted('event',MANAGE_EVENTS_PARTICIPANTS))
            redirect("error?page=permission");

        $id = RequestInput::post('id');
        $result = $this->mEventModel->sendTicket($id);
        echo json_encode($result);
        return;
    }


    public function sendReminder(){

        if (!GroupAccess::isGranted('event',MANAGE_EVENTS_PARTICIPANTS))
            redirect("error?page=permission");

        $event_id = RequestInput::post('event_id');
        $users = RequestInput::post('users');


        $contacts = $this->mEventModel->reminder_validate_user(
            SessionManager::getData('id_user'),
            $event_id,
            $users
        );

        $result = $this->mEventModel->send_reminders($event_id,$contacts);

        if(!$result){
            echo json_encode(array(Tags::SUCCESS => 0));
            return;
        }

        echo json_encode(array(Tags::SUCCESS => 1,Tags::RESULT=>_lang_f("Reminder sent to (%s) users",[count($contacts)])));
        return;
    }


    public function saveCommissionConfig()
    {

        $this->enableDemoMode();

        if (!GroupAccess::isGranted('event', MANAGE_EVENT_CONFIG_ADMIN)) {
            echo json_encode(array(Tags::SUCCESS => 0, Tags::ERRORS => array(
                "error" => Translate::sprint(Messages::PERMISSION_LIMITED)
            )));
            exit();
        }

        $ORDER_COMMISSION_ENABLED = RequestInput::post("EVENT_BOOK_COMMISSION_ENABLED");
        $ORDER_COMMISSION_VALUE = RequestInput::post("EVENT_BOOK_COMMISSION_VALUE");

        ConfigManager::setValue("EVENT_BOOK_COMMISSION_ENABLED", $ORDER_COMMISSION_ENABLED);
        ConfigManager::setValue("EVENT_BOOK_COMMISSION_VALUE", $ORDER_COMMISSION_VALUE);

        echo json_encode(array(Tags::SUCCESS => 1));
        return;

    }

    public function getEventsAjax()
    {

        $params = array(
            "limit" => 5,
            "store_id" => RequestInput::get('store_id'),
            "search" => RequestInput::get('search'),
            "user_id" => $this->mUserBrowser->getData('id_user'),
            "status" => 1
        );

        if (RequestInput::get('all') == 1)
            unset($params['user_id']);

        if (SessionManager::getData("manager") == 1) {
            unset($params['user_id']);
        }


        $data = $this->mEventModel->getEvents($params);


        $result = array();

        if (isset($data[Tags::RESULT]))
            foreach ($data[Tags::RESULT] as $object) {

                $o = array(
                    'text' => Text::output($object['name']) . ' (' . Text::output($object['store_name']) . ')',
                    'id' => $object['id_event'],

                    'title' => Text::output($object['name']),
                    'description' => strip_tags(Text::output($object['description'])),
                    'image' => ImageManagerUtils::getFirstImage($object['images']),
                );

                if ($object['store_name'] == "") {
                    $o['text'] = Text::output($object['name']);
                }

                if (strlen($o['description']) > 100) {
                    $o['description'] = substr(strip_tags(Text::output($o['description'])), 0, 100) . ' ...';
                }

                $result['results'][] = $o;


            }

        echo json_encode($result, JSON_OBJECT_AS_ARRAY);
        return;
    }

    public function markAsFeatured()
    {

        //check if user have permission
        $this->enableDemoMode();

        if (!GroupAccess::isGranted('event', MANAGE_EVENTS)) {
            echo json_encode(array(Tags::SUCCESS => 0, Tags::ERRORS => array(
                "error" => Translate::sprint(Messages::PERMISSION_LIMITED)
            )));
            exit();
        }

        if ($this->mUserBrowser->isLogged()) {

            $user_id = $this->mUserBrowser->getData("user_id");

            $id = intval(RequestInput::post("id"));
            $featured = intval(RequestInput::post("featured"));


            echo json_encode(
                $this->mEventModel->markAsFeatured(array(
                    "user_id" => $user_id,
                    "id" => $id,
                    "featured" => $featured

                ))
            );
            return;


        }

        echo json_encode(array(Tags::SUCCESS => 0));
    }


    public function create()
    {

        if (!GroupAccess::isGranted('event', ADD_EVENT)) {
            echo json_encode(array(Tags::SUCCESS => 0, Tags::ERRORS => array(
                "error" => Translate::sprint(Messages::PERMISSION_LIMITED)
            )));
            exit();
        }

        echo json_encode($this->mEventModel->create(array(
            "booking" => RequestInput::post("booking"),
            "price" => RequestInput::post("price"),
            "name" => RequestInput::post("name"),
            "address" => RequestInput::post("address"),
            "description" => RequestInput::post("description"),
            "website" => RequestInput::post("website"),
            "lat" => doubleval(RequestInput::post("lat")),
            "lng" => doubleval(RequestInput::post("lng")),
            "tel" => RequestInput::post("tel"),
            "telCode" => RequestInput::post("telCode"),
            "date_b" => RequestInput::post("date_b"),
            "date_e" => RequestInput::post("date_e"),
            "images" => RequestInput::post("images"),
            "store_id" => intval(RequestInput::post("store_id")),
            "user_id" => $this->mUserBrowser->getData("id_user")
        )));
    }


    public function edit()
    {
        if (!GroupAccess::isGranted("event", EDIT_EVENT)) {
            echo json_encode(array(Tags::SUCCESS => 0, Tags::ERRORS => array(
                "error" => Translate::sprint(Messages::PERMISSION_LIMITED)
            )));
            exit();
        }



        echo json_encode($this->mEventModel->edit(array(
            "event_id" => RequestInput::post("event_id"),
            "booking" => RequestInput::post("booking"),
            "price" => RequestInput::post("price"),
            "name" => RequestInput::post("name"),
            "address" => RequestInput::post("address"),
            "description" => RequestInput::post("description"),
            "website" => RequestInput::post("website"),
            "lat" => doubleval(RequestInput::post("lat")),
            "lng" => doubleval(RequestInput::post("lng")),
            "tel" => RequestInput::post("tel"),
            "telCode" => RequestInput::post("telCode"),
            "date_b" => RequestInput::post("date_b"),
            "date_e" => RequestInput::post("date_e"),
            "images" => RequestInput::post("images"),
            "store_id" => intval(RequestInput::post("store_id")),
            "user_id" => $this->mUserBrowser->getData("id_user")
        )));

    }


    public function delete()
    {

        //check if user have permission
        $this->enableDemoMode();

        if (!GroupAccess::isGranted("event", DELETE_EVENT)) {
            echo json_encode(array(Tags::SUCCESS => 0, Tags::ERRORS => array(
                "error" => Translate::sprint(Messages::PERMISSION_LIMITED)
            )));
            exit();
        }

        $id = intval(RequestInput::post("id"));;

        echo json_encode($this->mEventModel->delete(array(
            "id" => $id,
            "user_id" => $this->mUserBrowser->getData("id_user")
        )));


    }


    public function changeStatus()
    {

        //check if user have permission
        $this->enableDemoMode();

        if (!GroupAccess::isGranted('event', MANAGE_EVENTS)) {
            echo json_encode(array(Tags::SUCCESS => 0, Tags::ERRORS => array(
                "error" => Translate::sprint(Messages::PERMISSION_LIMITED)
            )));
            exit();
        }

        $id = intval(RequestInput::get("id"));

        $this->db->select("status");
        $this->db->from("event");
        $this->db->where("id_event", $id);
        $statusUser = $this->db->get();
        $statusUser = $statusUser->result_array();


        if (isset($statusUser[0]) && $statusUser[0]['status'] == 0) {
            $data['status'] = 1;
        } else if (isset($statusUser[0]) && $statusUser[0]['status'] == 1) {
            $data['status'] = 0;
        } else {
            $errors["status"] = Translate::sprint(Messages::STATUS_NOT_FOUND);
        }

        if (isset($data) and empty($errors)) {

            $this->db->where("id_event", $id);
            $this->db->update("event", $data);

            echo json_encode(array(Tags::SUCCESS => 1, "url" => admin_url("event/all_events")));
            return;
        } else {
            echo json_encode(array(Tags::SUCCESS => 0, "errors" => $errors));
            return;
        }

    }


}
