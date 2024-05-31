<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


class Admin extends ADMIN_Controller
{

    public function __construct()
    {
        parent::__construct();
        SessionManager::setValue("Mobile_Auth_Management",false);

    }


    public function Mobile_sendDigitalMoney()
    {

        if (!GroupAccess::isGranted('digital_wallet', DIGITAL_WALLET_SEND_RECEIVE))
            redirect("error?page=permission");


        SessionManager::setValue("Mobile_Auth_Management",true);


        $data['title'] = Translate::sprint("Transactions");

        $params = array(
            'page' => RequestInput::get('page'),
            'user_id' => SessionManager::getData("id_user"),
        );

        $data['result'] = $this->mWalletModel->getWalletTransactions($params);

        $this->load->view(AdminPanel::TemplatePath."/include/header-no-auth", $data);
        $this->load->view("digital_wallet/backend/html/digitalWallet/home");
        $this->load->view(AdminPanel::TemplatePath."/include/footer");

    }


    public function Mobile_withdraw(){

        if (!GroupAccess::isGranted('digital_wallet'))
            redirect("error?page=permission");

        SessionManager::setValue("Mobile_Auth_Management",true);

        $data['title'] = Translate::sprint("Withdraw");

        $this->load->view(AdminPanel::TemplatePath."/include/header-no-auth", $data);
        $this->load->view("digital_wallet/backend/html/digitalWallet/withdraw");
        $this->load->view(AdminPanel::TemplatePath."/include/footer");

    }

    public function Mobile_manageBanks(){

        if (!GroupAccess::isGranted('digital_wallet'))
            redirect("error?page=permission");

        SessionManager::setValue("Mobile_Auth_Management",true);

        $data['title'] = Translate::sprint("Manage Banks");

        $params = array(
            'page' => RequestInput::get('page'),
            'user_id' => SessionManager::getData('id_user'),
        );


        $callback = RequestInput::get('callback');
        if($callback!="")
            SessionManager::setValue("callback",$callback);

        $data['result'] = $this->mWalletModel->getBanks($params);

        $this->load->view(AdminPanel::TemplatePath."/include/header-no-auth", $data);
        $this->load->view("digital_wallet/backend/html/digitalWallet/banks");
        $this->load->view(AdminPanel::TemplatePath."/include/footer");


    }

    public function Mobile_addBank(){

        if (!GroupAccess::isGranted('digital_wallet'))
            redirect("error?page=permission");

        SessionManager::setValue("Mobile_Auth_Management",true);

        $data['title'] = Translate::sprint("Transactions");

        $params = array(
            'page' => RequestInput::get('page'),
        );

        $data['result'] = $this->mWalletModel->getBanks($params);

        $this->load->view(AdminPanel::TemplatePath."/include/header-no-auth", $data);
        $this->load->view("digital_wallet/backend/html/digitalWallet/addBank");
        $this->load->view(AdminPanel::TemplatePath."/include/footer");

    }

    public function Mobile_topUp(){

        if (!GroupAccess::isGranted('digital_wallet'))
            redirect("error?page=permission");

        SessionManager::setValue("Mobile_Auth_Management",true);

        $data['title'] = Translate::sprint("Top-up");

        $this->load->view(AdminPanel::TemplatePath."/include/header-no-auth", $data);
        $this->load->view("digital_wallet/backend/html/digitalWallet/top-up");
        $this->load->view(AdminPanel::TemplatePath."/include/footer");

    }

    public function topUp(){

        if (!GroupAccess::isGranted('digital_wallet'))
            redirect("error?page=permission");

        SessionManager::setValue("Mobile_Auth_Management",false);

        $data['title'] = Translate::sprint("Top-up");

        $this->load->view(AdminPanel::TemplatePath."/include/header", $data);
        $this->load->view("digital_wallet/backend/html/digitalWallet/top-up");
        $this->load->view(AdminPanel::TemplatePath."/include/footer");

    }

    public function withdraw(){

        if (!GroupAccess::isGranted('digital_wallet'))
            redirect("error?page=permission");

        SessionManager::setValue("Mobile_Auth_Management",false);

        $data['title'] = Translate::sprint("Transactions");

        $this->load->view(AdminPanel::TemplatePath."/include/header", $data);
        $this->load->view("digital_wallet/backend/html/digitalWallet/withdraw");
        $this->load->view(AdminPanel::TemplatePath."/include/footer");

    }

    public function manageBanks(){

        if (!GroupAccess::isGranted('digital_wallet'))
            redirect("error?page=permission");

        SessionManager::setValue("Mobile_Auth_Management",false);

        $data['title'] = Translate::sprint("Transactions");

        $params = array(
            'page' => RequestInput::get('page'),
            'user_id' => SessionManager::getData("id_user"),
        );

        $data['result'] = $this->mWalletModel->getBanks($params);
        SessionManager::setValue("callback","");

        $this->load->view(AdminPanel::TemplatePath."/include/header", $data);
        $this->load->view("digital_wallet/backend/html/digitalWallet/banks");
        $this->load->view(AdminPanel::TemplatePath."/include/footer");

    }

    public function addBank(){

        if (!GroupAccess::isGranted('digital_wallet'))
            redirect("error?page=permission");

        SessionManager::setValue("Mobile_Auth_Management",false);

        $data['title'] = Translate::sprint("Transactions");

        $params = array(
            'page' => RequestInput::get('page'),
        );

        $data['result'] = $this->mWalletModel->getBanks($params);

        $this->load->view(AdminPanel::TemplatePath."/include/header", $data);
        $this->load->view("digital_wallet/backend/html/digitalWallet/addBank");
        $this->load->view(AdminPanel::TemplatePath."/include/footer");


    }


    public function Mobile_editBank(){

        if (!GroupAccess::isGranted('digital_wallet'))
            redirect("error?page=permission");

        SessionManager::setValue("Mobile_Auth_Management",false);

        $data['title'] = Translate::sprint("Transactions");

        $params = array(
            'page' => RequestInput::get('page'),
            'id' => RequestInput::get('id'),
            'user_id' => SessionManager::getData("id_user"),
        );

        $data = $this->mWalletModel->getBanks($params);


        if(!isset($data[Tags::RESULT][0]))
            redirect("error404");

        $data['bank'] = $data[Tags::RESULT][0];

        $this->load->view(AdminPanel::TemplatePath."/include/header", $data);
        $this->load->view("digital_wallet/backend/html/digitalWallet/editBank");
        $this->load->view(AdminPanel::TemplatePath."/include/footer");


    }

    public function editBank(){

        if (!GroupAccess::isGranted('digital_wallet'))
            redirect("error?page=permission");

        SessionManager::setValue("Mobile_Auth_Management",false);

        $data['title'] = Translate::sprint("Transactions");

        $params = array(
            'page' => RequestInput::get('page'),
            'id' => RequestInput::get('id'),
            'user_id' => SessionManager::getData("id_user"),
        );

        $data = $this->mWalletModel->getBanks($params);


        if(!isset($data[Tags::RESULT][0]))
            redirect("error404");

        $data['bank'] = $data[Tags::RESULT][0];


        $this->load->view(AdminPanel::TemplatePath."/include/header", $data);
        $this->load->view("digital_wallet/backend/html/digitalWallet/editBank");
        $this->load->view(AdminPanel::TemplatePath."/include/footer");


    }


    public function manageWallet()
    {

        if (!GroupAccess::isGranted('digital_wallet'))
            redirect("error?page=permission");

        $data['title'] = Translate::sprint("Transactions");

        $params = array(
            'page' => RequestInput::get('page'),
        );

        if(SessionManager::getData("manager")==0){
            $params['user_id']  = SessionManager::getData("id_user");
        }

        $data['result'] = $this->mWalletModel->getWalletTransactions($params);

        $this->load->view(AdminPanel::TemplatePath."/include/header", $data);
        $this->load->view("digital_wallet/backend/html/digitalWallet/home");
        $this->load->view(AdminPanel::TemplatePath."/include/footer");

    }



    public function sendDigitalMoney()
    {

        if (!GroupAccess::isGranted('digital_wallet', DIGITAL_WALLET_SEND_RECEIVE))
            redirect("error?page=permission");


        $data['title'] = Translate::sprint("Transactions");

        $params = array(
            'page' => RequestInput::get('page'),
        );

        if(SessionManager::getData("manager")!=GroupAccess::ADMIN_ACCESS){
            $params['user_id'] = SessionManager::getData("id_user");
        }

        $data['result'] = $this->mWalletModel->getWalletTransactions($params);

        $this->load->view(AdminPanel::TemplatePath."/include/header", $data);
        $this->load->view("digital_wallet/backend/html/digitalWallet/home");
        $this->load->view(AdminPanel::TemplatePath."/include/footer");

    }



}
