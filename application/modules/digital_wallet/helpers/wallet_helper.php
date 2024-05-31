<?php

class Wallet_helper{

    public static function getBanks($userId){
        $ctx = &get_instance();
        $result = $ctx->mWalletModel->getBanks(array(
            'user_id' => $userId
        ));
        return $result[Tags::RESULT];
    }

}