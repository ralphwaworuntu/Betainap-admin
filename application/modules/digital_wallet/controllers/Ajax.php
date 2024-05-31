<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Created by DT Team.
 * AppName: NearbyStores
 */

class Ajax extends AJAX_Controller  {


    public function __construct(){
        parent::__construct();
    }


    public function add_balance(){

        $amount = doubleval(RequestInput::post("amount"));
        $amounts =  $this->mWalletModel->getTopUp();

        if(is_numeric($amount) && $amounts[0]<=$amount){
            //create invoice
            $result = $this->mWalletModel->create_invoice(SessionManager::getData("id_user"),doubleval($amount));

            if($result[Tags::SUCCESS]==1){


                if(SessionManager::getValue("Mobile_Auth_Management")){
                    SessionManager::setValue("Payment_ForcedCallback","digital_wallet/Mobile_sendDigitalMoney");
                }else{
                    SessionManager::setValue("Payment_ForcedCallback","digital_wallet/sendDigitalMoney");
                }

                $url  = site_url("payment/make_payment?id=".$result[Tags::RESULT]);

                echo json_encode(array(Tags::SUCCESS=>1,Tags::RESULT=>$url));
                return;
            }

        }

        echo json_encode(array(Tags::SUCCESS=>0,Tags::ERRORS=>array("err"=>"Amount invalid"))); return;
    }

    public function requestWithdrawal(){

        if (!GroupAccess::isGranted('digital_wallet', DIGITAL_WALLET_SEND_RECEIVE)){
            echo json_encode(array(Tags::SUCCESS=>0,Tags::ERRORS=>array(
                "error"  => Translate::sprint(Messages::RESTRICT_PERMISSION_DEMO)
            )));
            exit();
        }

        $user_id = SessionManager::getData("id_user");
        $amount = doubleval(RequestInput::post('amount'));
        $bank = intval(RequestInput::post('bank'));

        $result = $this->mWalletModel->requestWithdrawalFromWallet(
            $user_id,
            $bank,
            $amount,
            "digital_wallet"
        );


        if(SessionManager::getValue("Mobile_Auth_Management")){
            $result['callback'] = admin_url("digital_wallet/Mobile_sendDigitalMoney");
        }else{
            $result['callback'] = admin_url("digital_wallet/sendDigitalMoney");
        }



        echo json_encode($result);
    }

    public function deleteBank(){

        if (!GroupAccess::isGranted('digital_wallet', DIGITAL_WALLET_SEND_RECEIVE)){
            echo json_encode(array(Tags::SUCCESS=>0,Tags::ERRORS=>array(
                "error"  => Translate::sprint(Messages::RESTRICT_PERMISSION_DEMO)
            )));
            exit();
        }

        echo json_encode($this->mWalletModel->deleteBank(
            SessionManager::getData('id_user'),
            RequestInput::get("id"),
        ));return;
    }

    public function verifyAndCreateWalletTransaction(){

        if(!SessionManager::isLogged()
            && !GroupAccess::isGranted('digital_wallet', DIGITAL_WALLET_SEND_RECEIVE)){
            echo json_encode(array(Tags::SUCCESS=>0,Tags::ERRORS=>array(
                "error"  => Translate::sprint(Messages::RESTRICT_PERMISSION_DEMO)
            )));
            exit();
        }

        $SendAsadmin = (RequestInput::post("SendAsadmin"));
        $token = (RequestInput::post("token"));
        $receiver = (RequestInput::post("email"));
        $amount = (RequestInput::post("amount"));
        $sessToken = $this->mUserBrowser->getToken("WgTh_ABbnl");

        if($token != $sessToken){
            echo json_encode(array(Tags::SUCCESS=>0,Tags::ERRORS=>array(
                "error"  => Translate::sprint(Messages::RESTRICT_PERMISSION_DEMO)
            )));
            exit();
        }


        if($SendAsadmin==true && SessionManager::getData("manager")==1){

            try {

                $result = $this->mWalletModel->sendMoneyAdmin($receiver,$amount);

                if($result[Tags::SUCCESS]==1){
                    $this->mUserBrowser->cleanToken("WgTh_ABbnl");
                }
                echo json_encode($result);return;
            }catch (Exception $e){
                echo json_encode(array(
                    Tags::SUCCESS=>0,
                    Tags::ERRORS=>array("err"=>_lang($e->getMessage()))
                ));return;
            }

        }

        $sender = SessionManager::getData("email");

        try {
            $result = $this->mWalletModel->verifyAndSend($sender,$receiver,$amount);

            if($result[Tags::SUCCESS]==1){
                $this->mUserBrowser->cleanToken("WgTh_ABbnl");
            }

            echo json_encode($result);return;
        }catch (Exception $e){
            echo json_encode(array(
                Tags::SUCCESS=>0,
                Tags::ERRORS=>array("err"=>_lang($e->getMessage()))
            ));return;
        }




    }


    public function editBank(){

        if(!SessionManager::isLogged()
            && !GroupAccess::isGranted('digital_wallet', DIGITAL_WALLET_SEND_RECEIVE)){
            echo json_encode(array(Tags::SUCCESS=>0,Tags::ERRORS=>array(
                "error"  => Translate::sprint(Messages::RESTRICT_PERMISSION_DEMO)
            )));
            exit();
        }

        $token = (RequestInput::post("token"));
        $sessToken = $this->mUserBrowser->getToken("WgTh_ABbnl");


        if($token != $sessToken){
            echo json_encode(array(Tags::SUCCESS=>0,Tags::ERRORS=>array(
                "error"  => Translate::sprint(Messages::RESTRICT_PERMISSION_DEMO)
            )));
            exit();
        }

        $result = $this->mWalletModel->editBank(
            array(
                "id"=>(RequestInput::post("id")),
                "name"=>(RequestInput::post("name")),
                "account_number"=>(RequestInput::post("account_number")),
                "country"=>(RequestInput::post("country")),
                "holder_name"=>(RequestInput::post("holder_name")),
                "user_id"=>SessionManager::getData('id_user'),
            )
        );

        if(SessionManager::getValue("Mobile_Auth_Management")){
            $result['callback'] = admin_url('digital_wallet/Mobile_manageBanks');
        }else{
            $result['callback'] = admin_url('digital_wallet/manageBanks');
        }

        echo json_encode($result);return;

    }

    public function addBank(){

        if(!SessionManager::isLogged()
            && !GroupAccess::isGranted('digital_wallet', DIGITAL_WALLET_SEND_RECEIVE)){
            echo json_encode(array(Tags::SUCCESS=>0,Tags::ERRORS=>array(
                "error"  => Translate::sprint(Messages::RESTRICT_PERMISSION_DEMO)
            )));
            exit();
        }

        $token = (RequestInput::post("token"));
        $sessToken = $this->mUserBrowser->getToken("WgTh_ABbnl");


        if($token != $sessToken){
            echo json_encode(array(Tags::SUCCESS=>0,Tags::ERRORS=>array(
                "error"  => Translate::sprint(Messages::RESTRICT_PERMISSION_DEMO)
            )));
            exit();
        }

        $result = $this->mWalletModel->addBank(
            array(
                "name"=>(RequestInput::post("name")),
                "account_number"=>(RequestInput::post("account_number")),
                "country"=>(RequestInput::post("country")),
                "holder_name"=>(RequestInput::post("holder_name")),
                "user_id"=>SessionManager::getData('id_user'),
            )
        );


        if(SessionManager::getValue("Mobile_Auth_Management")){
            $result['callback'] = admin_url('digital_wallet/Mobile_manageBanks');
        }else{
            $result['callback'] = admin_url('digital_wallet/manageBanks');
        }

        if( SessionManager::getValue("callback") !=""){
            $result['callback'] = SessionManager::getValue("callback");
        }

        echo json_encode($result);return;

    }

}

/* End of file PackmanagerDB.php */