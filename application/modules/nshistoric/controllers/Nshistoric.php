<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Nshistoric extends MAIN_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->init('nshistoric');
    }

    public function changeStatus(){

        $this->load->model("nshistoric/notification_history_model","mHistoric");


        $id = intval(RequestInput::get("id"));
        $status = intval(RequestInput::get("status"));

        //id & status

        $data =  $this->mHistoric->changeStatus($id,$status);

        if($status == 1){

            $notification = $this->mHistoric->getNotification($id);
            if($notification != NULL){
                ActionsManager::add_action('nshistoric','read_notification',$notification);
            }

        }


        echo json_encode($data);return;

    }


    public function onLoad()
    {
        parent::onLoad(); // TODO: Change the autogenerated stub

        $this->load->model('nshistoric/notification_history_model');
        $this->load->helper('nshistoric/nshistoric');

    }

    public function onCommitted($isEnabled)
    {

        if(!$isEnabled)
            return;

        //handle store deleted action
        ActionsManager::register("store","onDelete",function ($args){
            if(isset($args['id'])){
                $this->db->where("module","store");
                $this->db->where("module_id",$args['id']);
                $this->db->delete("nsh_notifications");
            }
        });

        //handle event deleted action
        ActionsManager::register("event","onDelete",function ($args){
            if(isset($args['id'])){
                $this->db->where("module","event");
                $this->db->where("module_id",$args['id']);
                $this->db->delete("nsh_notifications");
            }
        });

        //handle offer deleted action
        ActionsManager::register("offer","onDelete",function ($args){
            if(isset($args['id'])){
                $this->db->where("module","offer");
                $this->db->where("module_id",$args['id']);
                $this->db->delete("nsh_notifications");
            }
        });

        //handle user deleted action
        ActionsManager::register("user","onDelete",function ($args){
            if(isset($args['id'])){
                $this->db->where("auth_type","user");
                $this->db->where("auth_id",$args['id']);
                $this->db->delete("nsh_notifications");
            }
        });


        //handle guest deleted action
        ActionsManager::register("guest","onDelete",function ($args){
            if(isset($args['id'])){
                $this->db->where("auth_type","guest");
                $this->db->where("auth_id",$args['id']);
                $this->db->delete("nsh_notifications");
            }
        });
    }

    public function onInstall()
    {
        parent::onInstall(); // TODO: Change the autogenerated stub
        $this->notification_history_model->createTable();

        return TRUE;
    }


    public function onUpgrade()
    {
        parent::onUpgrade(); // TODO: Change the autogenerated stub
        $this->notification_history_model->createTable();


        return TRUE;
    }

    public function onEnable()
    {
        return TRUE;
    }

    public function onUninstall()
    {
        parent::onUninstall(); // TODO: Change the autogenerated stub


        return TRUE;
    }


    public function cron()
    {

        $this->notification_history_model->clear_removed_cache();

    }


}