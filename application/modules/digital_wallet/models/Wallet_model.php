<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Created by DT Team.
 * AppName: NearbyStores
 */

class Wallet_model extends CI_Model
{

    const AMOUNTS = array();

    public function __construct()
    {
        parent::__construct();
    }

    public function getBank($id){
        $this->db->where('id',$id);
        $bank = $this->db->get('wallet_banks',1);
        $bank = $bank->result_array();
        return $bank[0]??NULL;
    }

    public function requestWithdrawalFromWallet($user_id,$bankAccountId, $amount, $module)
    {

        $this->db->where('user_id',$user_id);
        $this->db->where('id',$bankAccountId);
        $count = $this->db->count_all_results('wallet_banks');



        if($count==0)
            return array(Tags::SUCCESS=>0,Tags::ERRORS=>["err"=>_lang("You bank is not valid")]);


        $sender = $this->mUserModel->getUserData($user_id);
        $admin = $this->mUserModel->getAdmin();

        //release from wallet
        try {
            $bank = $this->getBank($bankAccountId);
            $result = $this->mWalletModel->verifyAndSend($sender['email'], $admin['email'], $amount,"Credit withdrawn to ".$bank['name']  );
        } catch (Exception $e) {
            return array(Tags::SUCCESS => 0, Tags::ERRORS => array('err' => $e->getMessage()));
        }

        if ($result[Tags::SUCCESS] == 0)
            return $result;

        $params = array(
            'method' => "bank",
            'info' => "bank_id=".$bankAccountId,
            'note' => "Wallet_withdrawal:" . $result[Tags::RESULT],
            'user_id' => $user_id,
            'amount' => $amount,
            'currency' => ConfigManager::getValue("DEFAULT_CURRENCY"),
            'status' => "request",
            'module' => $module,
        );

        //add payout
        $result = $this->mPayoutModel->addPayout($params);


        if($result[Tags::SUCCESS]==1){
            $this->sendWithdrawaleNotification($user_id,$result[Tags::RESULT]);
        }


        return $result;
    }

    public function deleteBank($userId,$Id){

        $this->db->where('user_id',$userId);
        $this->db->where('id',$Id);
        $this->db->delete('wallet_banks');

        return array(Tags::SUCCESS=>1);
    }

    public function editBank($params=array()){


        $errors = array();

        if(isset($params['id']) && $params['id']==""){
            $errors[] = _lang('id empty');
        }

        if(isset($params['name']) && $params['name']==""){
            $errors[] = _lang('Name empty');
        }

        if(isset($params['account_number']) && $params['account_number']==""){
            $errors[] = _lang('account_number empty');
        }

        if(isset($params['country']) && $params['country']==""){
            $errors[] = _lang('country empty');
        }

        if(isset($params['holder_name']) && $params['holder_name']==""){
            $errors[] = _lang('holder_name empty');
        }

        if(!empty($errors))
            return array(Tags::SUCCESS=>0,Tags::ERRORS=>$errors);

        $this->db->where('id',$params['id']);
        $this->db->update("wallet_banks",$params);

        return array(Tags::SUCCESS=>1);
    }

    public function addBank($params=array()){


        $errors = array();

        if(isset($params['name']) && $params['name']==""){
            $errors[] = _lang('Name empty');
        }

        if(isset($params['account_number']) && $params['account_number']==""){
            $errors[] = _lang('account_number empty');
        }

        if(isset($params['country']) && $params['country']==""){
            $errors[] = _lang('country empty');
        }

        if(isset($params['holder_name']) && $params['holder_name']==""){
            $errors[] = _lang('holder_name empty');
        }

        if(!empty($errors))
            return array(Tags::SUCCESS=>0,Tags::ERRORS=>$errors);

        $this->db->insert("wallet_banks",$params);

        return array(Tags::SUCCESS=>1);
    }

    public function getTopUp()
    {
        $val = ConfigManager::getValue("WALLET_TOP_UP_AMOUNTS");
        $val = explode(",", $val);
        return $val;
    }

    public function autoRenew($user_id)
    {

        $balance = $this->getBalance($user_id);

        $invoice = $this->mPaymentModel->getInvoice_by_user_id($user_id, 0);

        if ($invoice == NULL)
            return FALSE;

        if ($balance >= $invoice->amount) {//release balance

            $result = $this->releaseBalanceTransaction(
                SessionManager::getData("id_user"),
                $invoice->amount
            );

            if (!$result)
                return FALSE;


            $key = md5("abc-key" . $invoice->id);
            $result = $this->mPaymentModel->updateInvoice(
                $invoice->id,
                "wallet",
                "wallet:" . $result,
                $key,
                FALSE
            );

            if ($result)
                return TRUE;

        }

        return FALSE;
    }

    public function create_invoice($user_id, $amount)
    {

        $items = array();

        $items[] = array(
            'item_id' => $user_id,
            'item_name' => "Add balance of %s",
            'price' => $amount,
            'qty' => 1,
            'unit' => 'item',
            'price_per_unit' => $amount,
        );

        if ($amount == 0)
            return array(Tags::SUCCESS => 0);

        $this->db->where('user_id', $user_id);
        $no = $this->db->count_all_results('invoice');
        $no++;

        $data = array(
            "method" => "",
            "amount" => $amount,
            "no" => $no,
            "module" => "wallet",
            "module_id" => $user_id,
            "tax_id" => 0,
            "items" => json_encode($items, JSON_FORCE_OBJECT),
            "currency" => PAYMENT_CURRENCY,
            "status" => 0,
            "user_id" => $user_id,
            "transaction_id" => "",
            "created_at" => date("Y-m-d H:i:s", time())
        );


        $this->db->where('module', 'wallet');
        $this->db->where('user_id', $user_id);
        $invoice = $this->db->get('invoice', 1);
        $invoice = $invoice->result();

        if (!isset($invoice[0])) {

            $data['created_at'] = date("Y-m-d H:i:s", time());
            $data['updated_at'] = date("Y-m-d H:i:s", time());

            $this->db->insert('invoice', $data);
            $id = $this->db->insert_id();

        } else {
            $this->db->where('id', $invoice[0]->id);
            $this->db->update('invoice', $data);
            $id = $invoice[0]->id;
        }


        return array(Tags::SUCCESS => 1, Tags::RESULT => $id);

    }

    public function sendMoneyAdminByID($toUID, $amount, $note="--")
    {

        $receiverUserData = $this->mUserModel->getUserData($toUID);
        if ($receiverUserData == NULL)
            throw new Exception("Receiver undefined");

        //add money to receiver wallet
        $wallet_id = $this->add_Balance($receiverUserData['id_user'], $amount);

        $transactionId = time() . "-" . date("dmy");
        $this->createWalletTransaction($transactionId, $receiverUserData['id_user'], "receive", $amount, $note);


        //send notifications
        @$this->sendTransactionNotificationAdmin(
            $receiverUserData['id_user'],
            $amount." ".ConfigManager::getValue("DEFAULT_CURRENCY"),
            $transactionId
        );

        return array(Tags::SUCCESS => 1);
    }

    public function sendMoneyAdmin($to, $amount, $note="--")
    {

        $receiverUserData = $this->mUserModel->findUserByEmail($to);
        if ($receiverUserData == NULL)
            throw new Exception("Receiver undefined");

        //add money to receiver wallet
        $wallet_id = $this->add_Balance($receiverUserData['id_user'], $amount);

        $transactionId = time() . "-" . date("dmy");
        $this->createWalletTransaction($transactionId, $receiverUserData['id_user'], "receive", $amount, $note);


        //send notifications
        @$this->sendTransactionNotificationAdmin(
            $receiverUserData['id_user'],
            $amount." ".ConfigManager::getValue("DEFAULT_CURRENCY"),
            $transactionId
        );

        return array(Tags::SUCCESS => 1);
    }

    public function verifyAndSend($from, $to, $amount,$note="--")
    {

        $senderUserData = $this->mUserModel->findUserByEmail($from);
        if ($senderUserData == NULL)
            throw new Exception("Sender undefined");


        $receiverUserData = $this->mUserModel->findUserByEmail($to);
        if ($receiverUserData == NULL)
            throw new Exception("Receiver undefined");

        if ($receiverUserData['status'] == -1)
            throw new Exception("Receiver is disabled");


        if ($receiverUserData['confirmed'] == 0)
            throw new Exception("Receiver didn't verified his email");


        //release money from sender wallet
        $released = $this->releaseBalance($senderUserData['id_user'], $amount);

        if (!$released)
            throw new Exception("Insufficient funds. Please use the 'Top-up' feature to add more funds.");

        //add money to receiver wallet
        $wallet_id = $this->add_Balance($receiverUserData['id_user'], $amount);

        $transactionId = time() . "-" . date("dmy");

        //register new transaction
        $this->createWalletTransaction($transactionId, $senderUserData['id_user'], "send", $amount, $note);
        $this->createWalletTransaction($transactionId, $receiverUserData['id_user'], "receive", $amount, $note);


        //send notifications
        $this->sendTransactionNotification(
            $receiverUserData['id_user'],
            $senderUserData['id_user'],
            $amount." ".ConfigManager::getValue("DEFAULT_CURRENCY"),
            $transactionId
        );

        return array(Tags::SUCCESS => 1,Tags::RESULT=>$transactionId);
    }

    public function verifyAndSendForced($from, $to, $amount,$note="--")
    {

        $senderUserData = $this->mUserModel->findUserByEmail($from);
        if ($senderUserData == NULL)
            throw new Exception("Sender undefined");


        $receiverUserData = $this->mUserModel->findUserByEmail($to);
        if ($receiverUserData == NULL)
            throw new Exception("Receiver undefined");

        if ($receiverUserData['status'] == -1)
            throw new Exception("Receiver is disabled");


        if ($receiverUserData['confirmed'] == 0)
            throw new Exception("Receiver didn't verified his email");


        //release money from sender wallet
        $released = $this->releaseBalanceForced($senderUserData['id_user'], $amount);

        //add money to receiver wallet
        $wallet_id = $this->add_Balance($receiverUserData['id_user'], $amount);

        $transactionId = time() . "-" . date("dmy");

        //register new transaction
        $this->createWalletTransaction($transactionId, $senderUserData['id_user'], "send", $amount, $note);
        $this->createWalletTransaction($transactionId, $receiverUserData['id_user'], "receive", $amount, $note);


        //send notifications
        $this->sendTransactionNotification(
            $receiverUserData['id_user'],
            $senderUserData['id_user'],
            $amount." ".ConfigManager::getValue("DEFAULT_CURRENCY"),
            $transactionId
        );

        return array(Tags::SUCCESS => 1,Tags::RESULT=>$transactionId);
    }


    private function createWalletTransaction($no, $user_id, $operation, $amount,$note="--")
    {


        $this->db->insert('wallet_transaction', array(
            'amount' => $amount,
            'currency' => ConfigManager::getValue("DEFAULT_CURRENCY"),
            'no' => $no,
            'operation' => $operation,
            'note' => $note,
            'user_id' => $user_id,
            'created_at' => date("Y-m-d H:i:s", time()),
            'updated_at' => date("Y-m-d H:i:s", time()),
        ));

        return TRUE;

    }

    public function getSenderByTranId($tranID)
    {

        $this->db->select('user_id');
        $this->db->where('no', $tranID);
        $this->db->where('operation', 'send');
        $object = $this->db->get('wallet_transaction');
        $object = $object->result_array();

        if (!isset($object[0]['user_id']))
            return NULL;

        $sender = $this->mUserModel->getUserData($object[0]['user_id']);

        if ($sender == NULL)
            return NULL;

        return $sender;
    }


    public function getBanks($params = array())
    {
        if(isset($params['user_id']) && $params['user_id']>0){
            $this->db->where('user_id',intval($params['user_id']));
        }

        if(isset($params['id']) && $params['id']>0){
            $this->db->where('id',intval($params['id']));
        }

        $this->db->order_by("created_at desc, id desc");
        $this->db->from("wallet_banks");
        $result = $this->db->get();
        $result = $result->result_array();

        return array(Tags::SUCCESS=>1,Tags::RESULT=>$result);
    }


    public function getWalletTransactions($params = array())
    {

        if (!isset($params['page'])) {
            $page = 1;
        } else {
            $page = intval($params['page']);
        }

        if (!isset($params['limit'])) {
            $limit = 100;
        } else {
            $page = intval($params['limit']);
        }

        if (isset($params['user_id']) && $params['user_id'] > 0)
            $this->db->where('user_id', intval($params['user_id']));

        $count = $this->db->count_all_results("wallet_transaction");

        $pagination = new Pagination();
        $pagination->setCount($count);
        $pagination->setCurrent_page($page);
        $pagination->setPer_page($limit);
        $pagination->calcul();

        if (isset($params['user_id']) && $params['user_id'] > 0)
            $this->db->where('user_id', intval($params['user_id']));

        $this->db->order_by("created_at desc, id desc");
        $this->db->from("wallet_transaction");
        $this->db->limit($pagination->getPer_page(), $pagination->getFirst_nbr());
        $wallet_transactions = $this->db->get();
        $wallet_transactions = $wallet_transactions->result_array();

        foreach ($wallet_transactions as $key => $val) {
            $user = $this->mUserModel->getUserData($val['user_id']);

            $wallet_transactions[$key]['client'] = array(
                'username' => $user['username'],
                'email' => $user['email'],
                'name' => $user['name'],
            );

            $senderTransaction = $this->getSenderTransaction($val['no'], $val['operation']);
            if($senderTransaction!=NULL){
                $from = $this->mUserModel->getUserData($senderTransaction['user_id']);
                $wallet_transactions[$key]['from'] = array(
                    'username' => $from['username'],
                    'email' => $from['email'],
                    'name' => $from['name'],
                );
            }else{
                $wallet_transactions[$key]['from'] = array(
                    'username' => "--",
                    'email' => "--",
                    'name' => "--",
                );
            }

            $wallet_transactions[$key]['amount_v'] = Currency::parseCurrencyFormat($val['amount'],$val['currency']);


        }

        return array(
            Tags::SUCCESS => 1,
            Tags::RESULT => $wallet_transactions,
            Tags::COUNT => $count,
            Tags::PAGINATION => $pagination
        );

    }

    private function getSenderTransaction($no,$operation="send"){

        $this->db->where('no',$no);

        if($operation=="send")
            $this->db->where('operation',"receive");
        else
            $this->db->where('operation',"send");

        $wallet_transaction = $this->db->get('wallet_transaction',1);
        $wallet_transaction = $wallet_transaction->result_array();

        if(isset($wallet_transaction[0])){
            return $wallet_transaction[0];
        }

        return  NULL;
    }


    public function sendTransactionNotificationAdmin($receiverId, $amount, $transactionId)
    {

        $receiverData = $this->mUserModel->getUserData($receiverId);

        if ($receiverData == NULL)
            return;

        $appLogo = _openDir(ConfigManager::getValue('APP_LOGO'));
        $imageUrl = "";
        if (!empty($appLogo)) {
            $imageUrl = $appLogo['200_200']['url'];
        }

        $msg = "You received a new payment of ";
        $msg = Translate::sprint($msg).$amount;

        $body = $msg . "\n\nTo see all the transaction details, log in to your account.\n\nTransactionID: %s\n";
        $body = Translate::sprintf($body, array($transactionId));

        $messageText = Text::textParserHTML(array(
            "name" => $receiverData['name'],
            "imageUrl" => $imageUrl,
            "email" => ConfigManager::getValue('DEFAULT_EMAIL'),
            "appName" => strtolower(ConfigManager::getValue('APP_NAME')),
            "body" => nl2br($body),
        ), $this->load->view("mailing/templates/default.html", NULL, TRUE));


        $mail = new DTMailer();
        $mail->setRecipient($receiverData['email']);
        $mail->setFrom(ConfigManager::getValue('DEFAULT_EMAIL'));
        $mail->setFrom_name(ConfigManager::getValue('APP_NAME'));
        $mail->setMessage($messageText);
        $mail->setReplay_to(ConfigManager::getValue('DEFAULT_EMAIL'));
        $mail->setReplay_to_name(ConfigManager::getValue('APP_NAME'));
        $mail->setType("html");
        $mail->setSubject($msg);
        if ($mail->send()) {
            return FALSE;
        }

    }


    public function sendWithdrawaleNotification($fromUserId, $payoutId)
    {

        $receiverData = $this->mUserModel->getUserData($fromUserId);

        if ($receiverData == NULL)
            return;

        $appLogo = _openDir(ConfigManager::getValue('APP_LOGO'));
        $imageUrl = "";
        if (!empty($appLogo)) {
            $imageUrl = $appLogo['200_200']['url'];
        }

        $msg = "New payout requested from %s #".$payoutId;
        $msg = Translate::sprintf($msg,[$receiverData['username']]);

        $body = $msg . "";

        $messageText = Text::textParserHTML(array(
            "name" => "Admin",
            "imageUrl" => $imageUrl,
            "email" => ConfigManager::getValue('DEFAULT_EMAIL'),
            "appName" => strtolower(ConfigManager::getValue('APP_NAME')),
            "body" => nl2br($body),
        ), $this->load->view("mailing/templates/default.html", NULL, TRUE));


        $mail = new DTMailer();
        $mail->setRecipient(ConfigManager::getValue('DEFAULT_EMAIL'));
        $mail->setFrom(ConfigManager::getValue('DEFAULT_EMAIL'));
        $mail->setFrom_name(ConfigManager::getValue('APP_NAME'));
        $mail->setMessage($messageText);
        $mail->setReplay_to(ConfigManager::getValue('DEFAULT_EMAIL'));
        $mail->setReplay_to_name(ConfigManager::getValue('APP_NAME'));
        $mail->setType("html");
        $mail->setSubject($msg);
        if ($mail->send()) {
            return FALSE;
        }

    }

    public function sendTransactionNotification($receiverId, $senderId, $amount, $transactionId,$note="")
    {

        $receiverData = $this->mUserModel->getUserData($receiverId);
        $senderData = $this->mUserModel->getUserData($senderId);

        if ($receiverData == NULL && $senderData == NULL)
            return;

        $appLogo = _openDir(ConfigManager::getValue('APP_LOGO'));
        $imageUrl = "";
        if (!empty($appLogo)) {
            $imageUrl = $appLogo['200_200']['url'];
        }

        $msg = "You received a payment: %s";
        $msg = Translate::sprintf($msg, array($amount));


        $body = $msg . "\nTransactionID: %s\n\n<i>To see all the transaction details, log in to your account.</i>";
        $body = Translate::sprintf($body, array($transactionId));

        $messageText = Text::textParserHTML(array(
            "name" => $receiverData['name'],
            "imageUrl" => $imageUrl,
            "email" => ConfigManager::getValue('DEFAULT_EMAIL'),
            "appName" => strtolower(ConfigManager::getValue('APP_NAME')),
            "body" => nl2br($body),
        ), $this->load->view("mailing/templates/default.html", NULL, TRUE));


        $mail = new DTMailer();
        $mail->setRecipient($receiverData['email']);
        $mail->setFrom(ConfigManager::getValue('DEFAULT_EMAIL'));
        $mail->setFrom_name(ConfigManager::getValue('APP_NAME'));
        $mail->setMessage($messageText);
        $mail->setReplay_to(ConfigManager::getValue('DEFAULT_EMAIL'));
        $mail->setReplay_to_name(ConfigManager::getValue('APP_NAME'));
        $mail->setType("html");
        $mail->setSubject($msg);
        if ($mail->send()) {
            return FALSE;
        }

    }

    public function getBalance($user_id)
    {

        $this->db->where('user_id', $user_id);
        $wallet = $this->db->get('wallet', 1);
        $wallet = $wallet->result();

        if (isset($wallet[0])) {
            return $wallet[0]->balance;
        }

        return 0;
    }

    public function releaseBalance($user_id, $amount)
    {

        $this->db->where('user_id', $user_id);
        $this->db->where('balance >=', $amount);
        $wallet = $this->db->get('wallet', 1);
        $wallet = $wallet->result();


        if (isset($wallet[0])) {

            $this->db->where('id', $wallet[0]->id);
            $this->db->update('wallet', array(
                'balance' => $wallet[0]->balance - $amount,
                'updated_at' => date("Y-m-d H:i:s", time()),
            ));
            return TRUE;
        }

        return FALSE;
    }

    public function releaseBalanceForced($user_id, $amount)
    {

        $this->db->where('user_id', $user_id);
        $wallet = $this->db->get('wallet', 1);
        $wallet = $wallet->result();


        if (isset($wallet[0])) {

            $this->db->where('id', $wallet[0]->id);
            $this->db->update('wallet', array(
                'balance' => $wallet[0]->balance - $amount,
                'updated_at' => date("Y-m-d H:i:s", time()),
            ));
            return TRUE;
        }

        return FALSE;
    }
    public function hasBalance($user_id, $amount)
    {

        $this->db->where('user_id', $user_id);
        $this->db->where('balance >=', $amount);
        $count = $this->db->count_all_results("wallet");

        if($count>0)
            return  TRUE;

        return FALSE;
    }

    public function releaseBalanceTransaction($user_id, $amount,$note="--")
    {

        $released = $this->releaseBalance($user_id, $amount);

        if (!$released)
            return $released;

        $transactionId =  date("dmy")."-" .time() ;
        //register new transaction
        $this->createWalletTransaction($transactionId, $user_id, "send", $amount, $note);

        return $transactionId;
    }


    public function add_Balance($user_id, $amount)
    {

        $this->db->where('user_id', $user_id);
        $wallet = $this->db->get('wallet', 1);
        $wallet = $wallet->result();

        if (isset($wallet[0])) {

            $this->db->where('id', $wallet[0]->id);
            $this->db->update('wallet', array(
                'balance' => $wallet[0]->balance + $amount,
                'updated_at' => date("Y-m-d H:i:s", time()),
            ));

            return $wallet[0]->id;
        }

        $this->db->insert('wallet', array(
            'balance' => $amount,
            'user_id' => $user_id,
            'currency' => ConfigManager::getValue("DEFAULT_CURRENCY"),
            'created_at' => date("Y-m-d H:i:s", time()),
            'updated_at' => date("Y-m-d H:i:s", time()),
        ));

        return $this->db->insert_id();
    }

    public function add_BalanceTransaction($user_id, $amount)
    {

        $this->add_Balance($user_id, $amount);

        $transactionId = time() . "-" . date("dmy");
        //register new transaction
        $this->createWalletTransaction($transactionId, $user_id, "top-up", $amount);


    }

    public function updateFields()
    {

        if (!$this->db->field_exists('info', 'payouts')) {
            $fields = array(
                'info' => array('type' => 'TEXT', 'after' => 'method', 'default' => NULL),
            );
            // modify_column : The usage of this method is identical to add_column(), except it alters an existing column rather than adding a new one.
            $this->dbforge->add_column('payouts', $fields);
        }


        if (!$this->db->field_exists('note', 'wallet_transaction')) {
            $fields = array(
                'note' => array('type' => 'TEXT', 'after' => 'operation', 'default' => NULL),
            );
            // modify_column : The usage of this method is identical to add_column(), except it alters an existing column rather than adding a new one.
            $this->dbforge->add_column('wallet_transaction', $fields);
        }

    }

    public function createTables()
    {

        $this->load->dbforge();
        $this->dbforge->add_field(array(
            'id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE
            ),
            'balance' => array(
                'type' => 'DOUBLE',
                'default' => NULL
            ),
            'currency' => array(
                'type' => 'VARCHAR(10)',
                'default' => NULL
            ),
            'user_id' => array(
                'type' => 'INT',
                'default' => NULL
            ),
            'updated_at' => array(
                'type' => 'DATETIME'
            ),
            'created_at' => array(
                'type' => 'DATETIME'
            ),
        ));

        $attributes = array('ENGINE' => 'InnoDB');
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('wallet', TRUE, $attributes);

    }

    public function createTableWT()
    {

        $this->load->dbforge();
        $this->dbforge->add_field(array(
            'id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE
            ),
            'amount' => array(
                'type' => 'DOUBLE',
                'default' => NULL
            ),
            'currency' => array(
                'type' => 'VARCHAR(10)',
                'default' => NULL
            ),
            'no' => array(
                'type' => 'TEXT',
                'default' => NULL
            ),
            'operation' => array(
                'type' => 'VARCHAR(30)',
                'default' => NULL
            ),
            'user_id' => array(
                'type' => 'INT',
                'default' => NULL
            ),
            'updated_at' => array(
                'type' => 'DATETIME'
            ),
            'created_at' => array(
                'type' => 'DATETIME'
            ),
        ));

        $attributes = array('ENGINE' => 'InnoDB');
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('wallet_transaction', TRUE, $attributes);


    }

    public function createTableBanks()
    {

        $this->load->dbforge();
        $this->dbforge->add_field(array(
            'id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE
            ),
            'name' => array(
                'type' => 'VARCHAR(250)',
                'default' => NULL
            ),
            'account_number' => array(
                'type' => 'TEXT',
                'default' => NULL
            ),
            'holder_name' => array(
                'type' => 'VARCHAR(250)',
                'default' => NULL
            ),
            'country' => array(
                'type' => 'VARCHAR(250)',
                'default' => NULL
            ),
            'user_id' => array(
                'type' => 'INT',
                'default' => NULL
            ),
            'updated_at' => array(
                'type' => 'DATETIME'
            ),
            'created_at' => array(
                'type' => 'DATETIME'
            ),
        ));

        $attributes = array('ENGINE' => 'InnoDB');
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('wallet_banks', TRUE, $attributes);


    }


}