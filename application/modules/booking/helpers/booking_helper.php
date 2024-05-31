<?php


class BookingHelper{

    public static function optionsBuilderString($options){

        $string = "";

        if(!is_array($options))
            $options = json_decode($options);

        if(!empty($options))
            $string = "<br />";

        foreach ($options as $grp_label => $option){
            $string .= '&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;'.$grp_label.'<br/>';
        }

        return $string;
    }

    public static function convertInToStatus($data){
        $ctx = &get_instance();
        $list = [
            [
                "id" => 0,
                "label" => "Pending"
            ],
            [
                "id" => 1,
                "label" => "Confirmed"
            ],
            [
                "id" => -1,
                "label" => "Canceled"
            ],
        ];
        if(is_array($data)){
            foreach ($list as $k1 => $val){
                foreach ($data as $k2 => $input){
                    if($val['id']==$input){
                        $data[$k2] = $val['label'] ;
                    }
                }
            }
        }else{
            foreach ($list as $k1 => $val){
                if($val['id']==$val){
                    return $val['label'] ;
                }
            }
        }
        return $data;
    }

    public static function convertInTopPaymentStatus($data){
        $ctx = &get_instance();
        $list = Booking_payment::PAYMENT_STATUS;
        if(is_array($data)){
            foreach ($list as $k1 => $val){
                foreach ($data as $k2 => $input){
                    if($k1==$input){
                        $data[$k2] = _lang($val['label'] );
                    }
                }
            }
        }else{
            foreach ($list as $k1 => $val){
                if($val['id']==$val){
                    return $val['label'] ;
                }
            }
        }
        return $data;
    }

}