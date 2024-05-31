<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Created by DT Team.
 * AppName: NearbyStores
 */

class Setting extends MAIN_Controller {

    public function __construct(){
        parent::__construct();

        /////// register module ///////
        $this->init("setting");

    }

    public function onLoad()
    {

        define('CHANGE_APP_SETTING','change_app_setting');
        define('MANAGE_CURRENCIES','manage_currencies');

        //load model
        $this->load->helper("setting/settings");
        $this->load->helper("setting/currency");

        $this->load->model("setting/setting_model","mSettingModel");
        $this->load->model("setting/config_model","mConfigModel");
        $this->load->model("setting/currency_model","mCurrencyModel");

        $this->upgrade_alert();


    }


    public function onCommitted($isEnabled)
    {
        if(!$isEnabled)
            return;


        //Email and SMTP
        ConfigManager::setValue("DEFAULT_EMAIL","contact@domain.com",TRUE);
        ConfigManager::setValue("SMTP_HOST","",TRUE);
        ConfigManager::setValue("SMTP_PORT",465,TRUE);
        ConfigManager::setValue("SMTP_USER","",TRUE);
        ConfigManager::setValue("SMTP_PASS","",TRUE);
        ConfigManager::setValue("SMTP_PROTOCOL","mail",TRUE);
        $domain = explode("@",ConfigManager::getValue("DEFAULT_EMAIL"));
        ConfigManager::setValue("REPORT_EMAIL","report@".$domain[1],TRUE);
        ConfigManager::setValue("NOREPLY_EMAIL","no-reply@".$domain[1],TRUE);
        ConfigManager::setValue("DATE_FORMAT","24",TRUE);


        ConfigManager::setValue("SCHEMA_DATE","yyyy-mm-dd",TRUE);
        ConfigManager::setValue("SCHEMA_DATETIME","yyyy-mm-dd hh:ii",TRUE);
        ConfigManager::setValue("SCHEMA_TIME","hh:ii",TRUE);


        //Currency
        ConfigManager::setValue("DEFAULT_CURRENCY","USD",TRUE);

        //Register MAin menu
        AdminTemplateManager::registerMenu(
            'setting',
            "setting/menu",
            5
        );

        //Register Setting MAin menu
        AdminTemplateManager::registerMenuSetting(
            'setting',
            "setting/menu_setting",
            1
        );

        //Register timezone list in the header
        CMS_Display::set("dropdown_v1","setting/plug/header/timezone_list",NULL);

        //Cronjob execution message
        $ex1cron = RequestInput::get('ex1cron');
        if($ex1cron=="executed"){
            NotesManager::addNew(
                TM_Note::newInstance("setting",
                    "<div class=\"callout callout-success\"><h4>The cronjob was executed successful!</h4></div>"
                )
            );
        }



        $alert_script = $this->load->view("setting/plug/alert/html",NULL,TRUE);
        AdminTemplateManager::addScript($alert_script);


        //check version and alert
        if(_APP_VERSION!=APP_VERSION){
            $update_script = $this->load->view("setting/plug/update/html",NULL,TRUE);
            AdminTemplateManager::addScript($update_script);
        }


        //register setting component
        $this->registerSetting();

    }


    private function registerSetting(){

        //register component for setting viewer
        SettingViewer::register("setting","setting/setting_viewer/dashboard",array(
            'title' => _lang("Dashboard config"),
            'active' => TRUE
        ));

    }

    public function onLoaded()
    {
        parent::onLoaded(); // TODO: Change the autogenerated stub


        $this->registerModuleActions();

    }

    private function registerModuleActions(){

        GroupAccess::registerActions("setting",array(
            CHANGE_APP_SETTING,
            MANAGE_CURRENCIES,
        ));

    }

	public function index()
	{

	}

	public function setupMomentJS(){
        $jslib_moment = AdminTemplateManager::assets('setting','plugins/moment-timezone/moment.js');
        $jslib_moment_tz = AdminTemplateManager::assets('setting','plugins/moment-timezone/moment-timezone.min.js');
        AdminTemplateManager::addScriptLibs($jslib_moment);
        AdminTemplateManager::addScriptLibs($jslib_moment_tz);
    }

	public function cron_exe(){

        $modules = FModuleLoader::loadCoreModules();

        if(!empty($modules)){

            foreach ($modules as $module){

                if(ModulesChecker::isRegistred($module)){
                    $class = $this->{$module};
                    if(method_exists($class,'cron')){
                        $result = url_get_content(site_url($module.'/cron'));
                        echo "Execute => ".$module." cron <br>";
                        echo $result.'<br><br>';
                    }
                }

            }

        }

    }

    public function cron(){


    }


    public function language()
    {
        $lang = RequestInput::get("lang");

        if($lang!=""){

            Translate::changeSessionLang($lang);

            if($this->mUserBrowser->isLogged()){
                $this->db->where('user_id',$this->mUserBrowser->getData('id_user'));
                $this->db->update('user_subscribe_setting',array(
                    'user_language' => $lang
                ));
            }

            redirect(admin_url());
        }

    }


    public function messagesToTranslate(){

        echo "You need navigate in the website to save all messages<br><br>";

        if(isset($_SESSION['toTranslate'])){
            foreach ($_SESSION['toTranslate'] as $key => $item) {
                echo $key.": $item<br>";
            }
        }

    }


    public function enableModules(){

        //enable modules
        $modules = FModuleLoader::loadCoreModules();
        foreach ($modules as $module){
            $this->db->where('module_name',$module);
            $this->db->update('modules',array(
                '_enabled' => 1
            ));
        }

    }

    public function init_settings(){

        $data = RequestInput::post("data");
        $data = base64_decode($data);
        $data = json_decode($data,JSON_OBJECT_AS_ARRAY);

        foreach ($data as $k => $value){
            ConfigManager::setValue($k,$value);
        }
    }


    public function onEnable()
    {
        $this->registerModuleActions();
        return parent::onEnable();
    }

    public function onInstall()
    {

        $this->mSettingModel->updateSetting();
        $this->mSettingModel->updateFields();
        $this->mConfigModel->createConfigTable();
        $this->mCurrencyModel->updateFields();
        $this->mCurrencyModel->initCurrencies();

        return TRUE;
    }

    public function onUpgrade()
    {
        $this->mSettingModel->updateSetting();
        $this->mSettingModel->updateFields();
        $this->mConfigModel->createConfigTable();
        $this->mCurrencyModel->updateFields();
        $this->mCurrencyModel->initCurrencies();

        $this->registerModuleActions();

        return TRUE;
    }

    private function upgrade_alert(){
        if(defined("_APP_VERSION") && defined("APP_VERSION")){
            if(_APP_VERSION!=APP_VERSION){
                $script = $this->load->view('setting/plug/upgrade_alert/script',NULL,TRUE);
                AdminTemplateManager::addScript($script);
            }
        }
    }

}

/* End of file SettingDB.php */