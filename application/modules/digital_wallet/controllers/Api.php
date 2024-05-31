<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Created by DT Team.
 * AppName: NearbyStores
 */

class Api extends API_Controller  {


    public function __construct(){
        parent::__construct();
    }

    public function getWallet(){

        $auth_user_id = $this->requireAuth();
        $user_id = RequestInput::post("user_id");

        if($user_id != $auth_user_id){
            echo json_encode(array(Tags::SUCCESS=>0,Tags::ERRORS=>array(
                "error"  => Translate::sprint(Messages::RESTRICT_PERMISSION_DEMO)
            )));
            exit();
        }

        $params = array(
            'page' => RequestInput::post('page'),
            'user_id' => RequestInput::post('user_id'),
        );

        $transactionResult = $this->mWalletModel->getWalletTransactions($params);
        $balance = $this->mWalletModel->getBalance($user_id);
        $transactionResult['balance'] = Currency::parseCurrencyFormat($balance,ConfigManager::getValue('DEFAULT_CURRENCY'));

        $token = TokenSetting::generateToken("digital_Wallet_Action",$user_id);

        $transactionResult['withdrawUrl'] = site_url('digital_wallet/manageWalletAuth?call=Mobile_withdraw&token='.$token."&userId=".$user_id);
        $transactionResult['sendMoneyUrl'] = site_url('digital_wallet/manageWalletAuth?call=Mobile_sendDigitalMoney&token='.$token."&userId=".$user_id);
        $transactionResult['topUpUrl'] =  site_url('digital_wallet/manageWalletAuth?call=Mobile_topUp&token='.$token."&userId=".$user_id);

        echo json_encode($transactionResult,JSON_FORCE_OBJECT);

    }


    public function withdraw(){

        $auth_user_id = $this->requireAuth();
        $user_id = RequestInput::post("user_id");

        if($user_id != $auth_user_id){
            echo json_encode(array(Tags::SUCCESS=>0,Tags::ERRORS=>array(
                "error"  => Translate::sprint(Messages::RESTRICT_PERMISSION_DEMO)
            )));
            exit();
        }

        $params = array(
            'user_id' => RequestInput::post('user_id'),
            'to' => RequestInput::post('to'),
        );

    }
}

/* End of file PackmanagerDB.php */