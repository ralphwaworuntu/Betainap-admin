<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Created by DT Team.
 * AppName: NearbyStores
 */

class Ajax extends AJAX_Controller {

    public function __construct(){
        parent::__construct();
        //load model
        $this->load->model("gallery/gallery_model");
        $this->load->model("user/user_model");
        $this->load->model("user/user_browser");

    }



}

/* End of file UploaderDB.php */