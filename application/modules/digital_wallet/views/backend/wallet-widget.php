<?php if(GroupAccess::isGranted("digital_wallet",DIGITAL_WALLET_SEND_RECEIVE)): ?>
    <div class="small-box bg-primary wallet-box">
        <div class="inner">
            <span><?=_lang("Your balance")?></span>
            <h3><?=Currency::parseCurrencyFormat($this->mWalletModel->getBalance(SessionManager::getData("id_user")), ConfigManager::getValue("DEFAULT_CURRENCY"))?></h3>
            <a href="<?=admin_url("digital_wallet/topUp")?>" class="text-color-white text-white"><i class="mdi mdi-plus"></i> <?=_lang("Top-up")?></a>
            &nbsp;&nbsp;
            <a href="<?=admin_url("digital_wallet/manageWallet")?>" class="text-color-white text-white"><i class="mdi mdi-currency-usd"></i> <?=_lang("Send Money")?></a>
            &nbsp;&nbsp;
            <a href="<?=admin_url("digital_wallet/withdraw")?>" class="text-color-white text-white"><i class="mdi mdi-cash-fast"></i> <?=_lang("Withdraw")?></a>
        </div>
        <div class="icon"><i class="mdi mdi-wallet"></i></div>
    </div>
<?php endif;?>
