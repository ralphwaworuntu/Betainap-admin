<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Nsbanner extends MAIN_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->init('nsbanner');


    }


    public function onLoad()
    {
        parent::onLoad(); // TODO: Change the autogenerated stub

        define("NS_BANNER_GRP_ACTION_ADD", 'add');
        define("NS_BANNER_GRP_ACTION_EDIT", 'edit');
        define("NS_BANNER_GRP_ACTION_DELETE", 'delete');


        $this->load->model('nsbanner/nsbanner_model');
        $this->load->helper('nsbanner/nsbanner');

    }

    public function onCommitted($isEnabled)
    {
        parent::onCommitted($isEnabled); // TODO: Change the autogenerated stub


        if(!$isEnabled)
            return;

        AdminTemplateManager::registerMenu(
            'nsbanner',
            "nsbanner/menu",
            13
        );

        ConfigManager::setValue('NS_BANNER_MODULE_IS_ENABLED_KEY',TRUE);

        //register upload clear folder
        $this->onClearUploadFolder();
    }


    private function onClearUploadFolder()
    {
        ActionsManager::register("uploader","onClearFolder",function(){
            //get all active images
            return $this->nsbanner_model->getAllActiveImages();
        });
    }

    public function onEnable()
    {
        GroupAccess::registerActions('nsbanner', array(
            NS_BANNER_GRP_ACTION_ADD,
            NS_BANNER_GRP_ACTION_EDIT,
            NS_BANNER_GRP_ACTION_DELETE
        ));

        return TRUE;
    }

    public function onInstall()
    {
        parent::onInstall(); // TODO: Change the autogenerated stub
        $this->nsbanner_model->createTable();

        return TRUE;
    }


    public function onUpgrade()
    {
        parent::onUpgrade(); // TODO: Change the autogenerated stub
        $this->nsbanner_model->createTable();

        GroupAccess::registerActions('nsbanner', array(
            NS_BANNER_GRP_ACTION_ADD,
            NS_BANNER_GRP_ACTION_EDIT,
            NS_BANNER_GRP_ACTION_DELETE
        ));

        return TRUE;
    }

    public function onUninstall()
    {



        return TRUE;
    }


}