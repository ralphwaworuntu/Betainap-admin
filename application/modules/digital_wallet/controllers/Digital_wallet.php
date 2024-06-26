<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Digital_wallet extends MAIN_Controller implements AdminModuleLoader
{

    public function __construct()
    {
        parent::__construct();
        $this->init('digital_wallet');
    }

    public function onLoad()
    {
        parent::onLoad(); // TODO: Change the autogenerated stub

        define('DIGITAL_WALLET_SEND_RECEIVE', 'digital_wallet_send_receive');

        $this->load->model("digital_wallet/wallet_model", 'mWalletModel');
        $this->load->helper("digital_wallet/wallet");
    }


    public function onCommitted($isEnabled)
    {


        if(!$isEnabled && !ModulesChecker::isEnabled("payment"))
            return;


        //setup payment for waller
        $payment_redirection = site_url("payment/make_payment");
        $payment_callback_success = site_url("payment/wallet_payment_confirm");
        $payment_callback_error = site_url("payment/payment_error");


        $payments = PaymentsProvider::getPayments("default");


        PaymentsProvider::provide("wallet",$payments,
            $payment_redirection,
            $payment_callback_success,
            $payment_callback_error
        );

        PaymentsProvider::excludePayments('wallet',array(PaymentsProvider::WALLET_ID,PaymentsProvider::COD_ID));

        //disable taxes
        TaxManager::disable('wallet');

        //register widget
        CMS_Display::set("widget_digital_wallet","digital_wallet/backend/wallet-widget");



    }


    public function onInstall()
    {

        $this->mWalletModel->createTableWT();
        $this->mWalletModel->createTableBanks();
        $this->mWalletModel->updateFields();

        return TRUE;
    }

    public function onUpgrade()
    {

        $this->mWalletModel->createTableWT();
        $this->mWalletModel->createTableBanks();
        $this->mWalletModel->updateFields();

        GroupAccess::registerActions("digital_wallet", array(
            DIGITAL_WALLET_SEND_RECEIVE
        ));

        return TRUE;
    }

    public function onEnable()
    {

        GroupAccess::registerActions("digital_wallet", array(
            DIGITAL_WALLET_SEND_RECEIVE
        ));


        return TRUE;
    }


    public function onAdminLoaded($module)
    {
        /*
         * return bool / function
         */

        return function (){



        };
    }


    public function manageWalletAuth(){

        $user_id = RequestInput::get("userId");
        $token = RequestInput::get("token");


        $object = TokenSetting::get_by_token($token,"digital_Wallet_Action");

        if($object==NULL){
            echo json_encode(array(Tags::SUCCESS=>0,Tags::ERRORS=>array(
                "error"  => Translate::sprint(Messages::RESTRICT_PERMISSION." #1")
            )));
            exit();
        }

        if(intval($object->content)!=$user_id){
            echo json_encode(array(Tags::SUCCESS=>0,Tags::ERRORS=>array(
                "error"  => Translate::sprint(Messages::RESTRICT_PERMISSION." #2")
            )));
            exit();
        }


        //generate session
        SessionManager::refresh($user_id);


        if (!GroupAccess::isGranted('digital_wallet', DIGITAL_WALLET_SEND_RECEIVE)){
            echo json_encode(array(Tags::SUCCESS=>0,Tags::ERRORS=>array(
                "error"  => Translate::sprint(Messages::RESTRICT_PERMISSION." #3")
            )));
            exit();
        }


        TokenSetting::clear($token);

        //redirect
        $call = RequestInput::get("call");
        redirect(admin_url("digital_wallet/".$call));

    }


}