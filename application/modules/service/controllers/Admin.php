<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Admin extends ADMIN_Controller {


    public function download(){



        if (!GroupAccess::isGranted('store',ADD_STORE))
            redirect("error?page=permission");

        $store_id = intval(RequestInput::get("id"));

        $result = $this->mStoreModel->getStores(array(
            "limit"     => 1,
            "owner_id" => SessionManager::getData('id_user'),
            "store_id"   => $store_id>0?$store_id:-1,
        ));

        if(!isset($result[Tags::RESULT][0]) OR $store_id==-1){
            redirect("error404");
            return;
        }


        require 'vendor/autoload.php';

        $dompdf = new \Dompdf\Dompdf(array('enable_remote' => true));
        $dompdf->loadHtml(
            $this->load->view('service/pdf/templateV1',['store'=>$result[Tags::RESULT][0]],TRUE)
        );

        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dompdf->stream("pdf_filename_".rand(10,1000).".pdf", array("Attachment" => true));

    }


    public function services(){

        if (!GroupAccess::isGranted('store',ADD_STORE))
            redirect("error?page=permission");

        $store_id = intval(RequestInput::get("store_id"));

        $result = $this->mStoreModel->getStores(array(
            "limit"     => 1,
            "owner_id" => SessionManager::getData('id_user'),
            "store_id"   => $store_id>0?$store_id:-1,
        ));


        $data["myStores"] = $this->mStoreModel->getMyAllStores(array(
            "user_id"   => $this->mUserBrowser->getData("id_user")
        ));


        if(!isset($result[Tags::RESULT][0]) OR $store_id==-1){
            $this->load->view(AdminPanel::TemplatePath."/include/header",$data);
            $this->load->view("service/backend/services-stores");
            $this->load->view(AdminPanel::TemplatePath."/include/footer");
            return;
        }

        $data['store'] = $result[Tags::RESULT][0];
        $this->load->view(AdminPanel::TemplatePath."/include/header",$data);
        $this->load->view("service/backend/services");
        $this->load->view(AdminPanel::TemplatePath."/include/footer");

    }


}

/* End of file EventDB.php */