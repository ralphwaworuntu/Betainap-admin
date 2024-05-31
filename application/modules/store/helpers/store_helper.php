<?php


class LocationManager{

    public static function plug_pick_location($data=array(
        "lat"=>"",
        "lng"=>"",
        "address"=>"",
    ),$config=array(
        "lat"=>TRUE,
        "lng"=>TRUE,
        "address"=>TRUE,
    )){

        $ctx = &get_instance();
        $data['config'] = $config;
        $data['var'] = substr(md5(time()),0,10);
        $html = $ctx->load->view('store/plug/'.WEB_MAP_PICKER.'/html',$data,TRUE);
        $script = $ctx->load->view('store/plug/'.WEB_MAP_PICKER.'/js',$data,TRUE);

        return array(
            'html' => $html,
            'script' =>$script,
            'fields_id' => array(
                "lat"       =>"lat_".$data['var'],
                "lng"       =>"lng_".$data['var'],
                "address"   =>"address_".$data['var'],
            )
        );
    }

}

class StoreHelper{

    public static function get($params=array(), $whereArray=array(), $method = NULL){

        $context = &get_instance();
        $result = $context->mStoreModel->getStores($params,$whereArray,$method);
        return $result;
    }

    public static function getCurrentStore(){

        if(StoreHelper::currentStoreSessionId()==0)
            return NULL;

        $ctx = &get_instance();
        $stores =  $ctx->mStoreModel->userStores(
            SessionManager::getData("id_user"),
            intval(StoreHelper::currentStoreSessionId())
        );

        if(count($stores)>=1){
            return $stores[0];
        }

        return NULL;
    }

    public static function currentStoreSessionId(){
       return SessionManager::getValue("currentStoreSession_".SessionManager::getData('id_user'),0);
    }

    public static function setCurrentStoreSessionId($sid){
         SessionManager::setValue("currentStoreSession_".SessionManager::getData('id_user'),$sid);
    }

    public static function loadStores(){

        $ctx = &get_instance();
        return $ctx->mStoreModel->userStores(
            SessionManager::getData("id_user")
        );
    }

    public static function getImage($store){
        try {

            if (!is_array($store['images']))
                $images = json_decode($store['images'], JSON_OBJECT_AS_ARRAY);
            else
                $images = $store['images'];


            foreach ($images as $k=> $image){
                $images[$k] = _openDir($image);
            }

            if (isset($images[0])) {
                $images = $images[0];
                if (isset($images['100_100']['url'])) {
                    return $images['100_100']['url'];
                } else {
                    return  adminAssets("images/def_logo.png");
                }
            } else {
                return adminAssets("images/def_logo.png");
            }

        } catch (Exception $e) {
           return  adminAssets("images/def_logo.png") ;
        }
    }

}


class StoreManager{


    private static $store_subscriptions = array();

    public static function subscribe($module,$key){

        $context = &get_instance();

        if(!isset(self::$store_subscriptions[$key])){

            //add field if needed
            $context->mStoreModel->add_fk_field($module,$key);

            self::$store_subscriptions[$module] = array(
                "module" => $module,
                "field" => $key,
            );

        }

    }

    public static function getSubscriptions(){
        return self::$store_subscriptions;
    }


}

if( !function_exists("input_get") ){
    function input_get($key){
        $ctx = &get_instance();
        return $ctx->input->get($key);
    }
}


if( !function_exists("printRatingStars") ){
    function parseToRatingStars($rate,$classes=""){
        $html = "";
        for ($i=0;$i<intval($rate);$i++){
            $html .= "<i class='".$classes." mdi mdi-star text-yellow'></i>";
        }

        for ($i=(5-intval($rate));$i>=1;$i--){
            $html .= "<i class='".$classes."  mdi mdi-star-outline  text-yellow'></i>";
        }

        return $html;
    }
}