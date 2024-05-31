<?php


class SecurityUtils{
    
    var $jsonBody;
    
    public function __construct($data) {
        $this->jsonBody=$data;
        $this->jsonBody  =  json_decode($this->jsonBody,TRUE);
    }

    public function getInput($key){
        
        if(isset($this->jsonBody[$key])){
            return urldecode($this->jsonBody[$key]);
        }
        
        return "";
    }

    public function getInputs(){
        return $this->jsonBody;
    }
    
}


class Encryption {
    
    var $skey 	= ""; // you can change it
    var $skeyIV        = "";
    
    
    function getSkey() {
        return $this->skey;
    }

    function getSkeyIV() {
        return $this->skeyIV;
    }

    function setSkey($skey) {
        $this->skey = $skey;
    }

    function setSkeyIV($skeyIV) {
        $this->skeyIV = $skeyIV;
    }


    function encrypt($str, $isBinary = false)
    {


         if($str=="")
             return $str;

        $iv = $this->skeyIV;
        $str = $isBinary ? $str : utf8_decode($str);

        $td = mcrypt_module_open('rijndael-128', ' ', 'cbc', $iv);


        mcrypt_generic_init($td, $this->skey, $iv);
        $encrypted = mcrypt_generic($td, $str);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);



        return $isBinary ? $encrypted : bin2hex($encrypted);
    }
    /**
     * @param string $code
     * @param bool $isBinary whether to decrypt as binary or not. Default is: false
     * @return string Decrypted data
     */
    function decrypt($code, $isBinary = false)
    {

        if($code=="")
             return $code;

        $code = $isBinary ? $code : $this->hex2bin($code);
        $iv = $this->skeyIV;
        $td = mcrypt_module_open('rijndael-128', ' ', 'cbc', $iv);
        mcrypt_generic_init($td, $this->skey, $iv);
        $decrypted = mdecrypt_generic($td, $code);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        return $isBinary ? trim($decrypted) : utf8_encode(trim($decrypted));
    }


    protected function hex2bin($hexdata)
    {
        $bindata = '';
        for ($i = 0; $i < strlen($hexdata); $i += 2) {
            $bindata .= chr(hexdec(substr($hexdata, $i, 2)));
        }
        return $bindata;
    }
    

//   
//    
//    public function encrypt($str=""){
//        
//        $iv_size = mcrypt_get_iv_size(MCRYPT_CAST_256, MCRYPT_MODE_CBC);
//        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
//
//       $encrypted = openssl_encrypt($str, "AES-256-CBC", $this->skey, 0, $iv);
//       $encrypted = $iv.$encrypted;
//
//       return $encrypted;
//    }
//    
//    
//    public function decrypt($str=""){
//        
//       $iv_size = mcrypt_get_iv_size(MCRYPT_CAST_256, MCRYPT_MODE_CBC);
//       $iv = substr($str, 0, $iv_size);
//
//       $decrypted = openssl_decrypt(substr($str, $iv_size), "AES-256-CBC", $this->skey, 0, $iv);
//       return $decrypted;
//    }
    
    
}

class Security{

    public static function cryptPassword($str=''){
        return sha1(sha1(md5(md5($str))));
    }


    public static function decrypt($str=""){

        return $str;
    }

    public static function cryptToken($str=""){
        return md5(sha1($str));
    }

    public static function encrypt($str=""){

        return $str;
    }

    //127.0.0.1
    //198.168.168.1
    public static function checkMacAddress($str=""){

//        if($str!=""){
//            if(preg_match("/^([a-fA-F0-9]{2}[:|\-|\.]?){6}$/i", $str)
//                OR preg_match("/^([a-fA-F0-9]{1}[:|\-|\.]?){6}$/i", $str)){
//                return TRUE;
//            }
//        }

        return TRUE;
    }


    public static function checkIpAddress($str=""){

        if($str!=""){
            if(preg_match("/^([0-9]{3}[:|\-|\.]?){4}$/i", $str)){

                return TRUE;
            }
        }

        return FALSE;
    }

    public static function checkToken($str=""){

        if($str!=""){
            if(preg_match("/^[a-z0-9]+$/i", $str)){

                return TRUE;
            }
        }

        return FALSE;
    }

}


if(!function_exists("hideEmailAddress")){
    function hideEmailAddress($email)
    {
        if(filter_var($email, FILTER_VALIDATE_EMAIL))
        {
            list($first, $last) = explode('@', $email);
            $first = str_replace(substr($first, '3'), str_repeat('*', strlen($first)-3), $first);
            $last = explode('.', $last);
            $last_domain = str_replace(substr($last['0'], '1'), str_repeat('*', strlen($last['0'])-1), $last['0']);
            $hideEmailAddress = $first.'@'.$last_domain.'.'.$last['1'];
            return $hideEmailAddress;

        }
    }
}


if(!function_exists("csrfData")){
    function csrfData(){
        $ctx = &get_instance();
            return array(
                'name' => $ctx->security->get_csrf_token_name(),
                'hash' => $ctx->security->get_csrf_hash()
            );
    }
}

if(!function_exists("csrfInput")){
    function csrfInput(){
        $ctx = &get_instance();
        return '<input type="hidden" name="'.$ctx->security->get_csrf_token_name().'" 
        value="'.$ctx->security->get_csrf_hash().'" />';
    }
}




