<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Booking_payment_model extends CI_Model
{


    public function registerBookingPaymentActions(){
        //Send Amout to the OW
        if(!ModulesChecker::isEnabled("digital_wallet")){
            return;
        }

        ActionsManager::register("booking","bookingConfirmed",function ($args){

            $amount = $this->mBookingModel->getTotalBooking($args['id']);

            //get booking
            $booking = $this->mBookingModel->getBooking(intval($args['id']));
            $store = $this->mBookingModel->getStore($booking['store_id']);

            if(empty($store)){
                return [Tags::SUCCESS=>0,Tags::ERRORS=>["err"=>_lang("The store no longer exist!")]];
            }

            //send Order payment to Business owner
            if($booking['payment_status']=="paid"
                OR $booking['payment_status']=="transferBankPaid"){
                $bookingID = "#" . str_pad($args['id'], 6, 0, STR_PAD_LEFT);
                $result = $this->mWalletModel->sendMoneyAdminByID($store['user_id'],$amount['sub_total'],"Booking payment ".$bookingID);
            }

            return [Tags::SUCCESS=>1];
        });

        //Register Refund Action for a canceled Booking
        ActionsManager::register("booking","bookingCanceled",function ($args){

            $booking = $this->mBookingModel->getBooking($args['id']);


            if($booking==NULL){
                return [Tags::SUCCESS=>0];
            }

            if($booking['status'] != -1
                && ($booking['payment_status']=="paid"
                    OR $booking['payment_status']=="transferBankPaid")){

                $invoice = $this->getInvoiceByBooking($booking['id']);


                if($invoice->status!=1 OR $invoice->refunded==1){
                    return [Tags::SUCCESS=>0];
                }


                $commission = 0;
                $amountToRefund = $booking['amount'] - $commission;

                $bookingId = str_pad($booking['id'], 6, 0, STR_PAD_LEFT);

                //get Owner's waller
                $store = $this->mBookingModel->getStore($booking['store_id']);
                $ownerId = $store['user_id'];
                $owner = $this->mUserModel->getUserData($ownerId);

                //get client's wallet
                $clientId= $booking['user_id'];
                $client = $this->mUserModel->getUserData($clientId);

                ;

                //Send Balance to the client
                $result = $this->mWalletModel->verifyAndSendForced($owner['email'], $client['email'], $amountToRefund,'Refund Booking #'.$bookingId);

                $transactionId = $result[Tags::RESULT];

                //update invoice
                $this->markAsRefunded($bookingId,$transactionId);

            }

            return [Tags::SUCCESS=>1];

        });
    }

    public function markAsRefunded($bookingId,$transactionId){

        $invoice = $this->getInvoiceByBooking($bookingId);


        $this->db->where('id',$invoice->id);
        $this->db->update('invoice',array(
            'refunded' => 1
        ));

        $this->db->where('id',$bookingId);
        $this->db->update('booking',array(
           'payment_status' => 'refunded'
        ));

        $this->db->insert('payment_transactions',array(
            'invoice_id' => $invoice->id,
            'transaction_id' => $transactionId,
            'status' => 'refunded',
            'user_id' => $invoice->user_id,
            'updated_at' => date('Y-m-d H:i:s',time()),
            'created_at' => date('Y-m-d H:i:s',time()),
        ));

    }

    public function getInvoiceByBooking($nookingId){

        $this->db->where('module','booking_payment');
        $this->db->where('module_id',$nookingId);
        $invoice = $this->db->get('invoice',1);
        $invoice = $invoice->result();

        if(!isset($invoice[0]))
            return NULL;

        return  $invoice[0];
    }

    public function updateBookingPaymentStatus($inv_object, $status)
    {

        $booking_id = $inv_object->module_id;

        $this->db->where("id", $booking_id);
        $this->db->update("booking", array(
            'payment_status' => $status,
            'amount' => $inv_object->amount,
        ));

    }

    public function getInvoice($module_id)
    {

        $this->db->where('module', "booking_payment");
        $this->db->where('module_id', $module_id);

        $invoice = $this->db->get('invoice', 1);
        $invoice = $invoice->result();

        if (isset($invoice[0]))
            return $invoice[0];

        return NULL;
    }

    private function getInvoiceBooking($user_id, $booking_id){
        $this->db->where('user_id', $user_id);
        $this->db->where('module', "booking_payment");
        $this->db->where('module_id', $booking_id);
        $invoice = $this->db->get('invoice', 1);
        $invoice = $invoice->result();

        return $invoice;
    }

    public function convert_booking_to_invoice($user_id, $booking_id)
    {

        $payable = 0;

        $invoice = $this->getInvoiceBooking($user_id,$booking_id);

        if(isset($invoice[0])) {
            if($invoice[0]->amount > 0)
                $payable = 1;
            return array(Tags::SUCCESS => 1, Tags::RESULT => $invoice[0]->id,"payable"=>$payable);
        }


        $this->db->where('user_id', $user_id);
        $this->db->where('id', $booking_id);
        $booking = $this->db->get('booking', 1);
        $booking = $booking->result_array();


        if (isset($booking[0])) {

            $booking = $booking[0];

            $items = array();
            $amount = 0;

            $cart = json_decode($booking['cart'], JSON_OBJECT_AS_ARRAY);

            foreach ($cart as $item) {

                $callback = NSModuleLinkers::find($item['module'], 'getData');

                if ($callback != NULL) {

                    $params = array(
                        'id' => $item['module_id']
                    );

                    $result = call_user_func($callback, $params);

                    $items[] = array(
                        'item_id' => $item['module_id'],
                        'item_name' => $result['label'],
                        'price' => $item['amount'],
                        'qty' => $item['qty'],
                        'unit' => 'item',
                        'price_per_unit' => $item['amount'],
                    );

                    $amount = $amount + ($item['amount'] * $item['qty']);

                }

            }

            if ($amount == 0)
                return array(Tags::SUCCESS => 1, Tags::RESULT => -1);

            $this->db->where('user_id', $user_id);
            $no = $this->db->count_all_results('invoice');
            $no++;


            $data = array(
                "method" => "",
                "amount" => $amount,
                "no" => $no,
                "module" => "booking_payment",
                "module_id" => $booking['id'],
                "tax_id" => 0,
                "items" => json_encode($items, JSON_FORCE_OBJECT),
                "currency" => PAYMENT_CURRENCY,
                "status" => 0,
                "user_id" => $user_id,
                "transaction_id" => "",
                "updated_at" => date("Y-m-d H:i:s", time()),
                "created_at" => date("Y-m-d H:i:s", time())
            );



            $this->db->insert('invoice', $data);
            $id = $this->db->insert_id();

            return array(Tags::SUCCESS => 1, Tags::RESULT => $id,"payable"=>$payable);
        }


        return array(Tags::SUCCESS => 0);

    }


    public function updateFields()
    {

        if (!$this->db->field_exists('amount', 'booking')) {
            $fields = array(
                'amount' => array('type' => 'DOUBLE', 'default' => 0),
            );
            $this->dbforge->add_column('booking', $fields);
        }

        if (!$this->db->field_exists('payment_status', 'booking')) {
            $fields = array(
                'payment_status' => array('type' => 'VARCHAR(150)', 'default' => 0),
            );
            $this->dbforge->add_column('booking', $fields);
        }

    }

    public function createPayoutsTable()
    {

        $this->load->dbforge();
        $this->dbforge->add_field(array(
            'id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE
            ),

            'method' => array(
                'type' => 'VARCHAR(30)',
                'default' => NULL
            ),

            'amount' => array(
                'type' => 'DOUBLE',
                'default' => NULL
            ),

            'currency' => array(
                'type' => 'VARCHAR(30)',
                'default' => NULL
            ),

            'note' => array(
                'type' => 'TEXT',
                'default' => NULL
            ),

            'status' => array(
                'type' => 'INT',
                'default' => NULL
            ),

            'user_id' => array(
                'type' => 'INT',
                'default' => NULL
            ),

            'transaction_id' => array(
                'type' => 'VARCHAR(60)',
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
        $this->dbforge->create_table('payouts', TRUE, $attributes);

        //==========  Payment_transactions ==========/
    }


}

