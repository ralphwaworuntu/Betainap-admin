<div class="box box-solid form-wallet">
    <div class="box-header">
        <div class="box-title"  style="width : 100%;">
            <strong><?=Translate::sprint("Wallet")?></strong>
        </div>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-sm-6">
                <form>
                    <div class="form-group">
                        <label><?=_lang("Enter client email")?></label>
                        <input name="email" class="form-control" type="email" placeholder="<?=_lang("Enter email")?>" />
                    </div>
                    <div class="form-group">
                        <label><?=_lang("Amount")?> (<?=ConfigManager::getValue("DEFAULT_CURRENCY")?>)</label>
                        <input name="amount" class="form-control" type="number" placeholder="<?=_lang("Enter amount")?>" />
                    </div>

                    <?php if(SessionManager::getData("manager")==1): ?>
                        <div class="form-group">
                            <label><input name="SendAsadmin"  type="checkbox" checked/> <?=_lang("Send as admin")?> (<?=_lang("Send money without release it from the wallet")?>)</label>
                        </div>
                    <?php endif; ?>


                    <div class="form-group">
                        <button id="verifyAndSendBtn" class="btn btn-primary"><?=_lang("Send")?></button>
                    </div>
                </form>
            </div>

            <div class="col-sm-6">
                <div class="box box-solid">
                    <div class="box-header">
                        <div class="box-title"  style="width : 100%;">
                            <div class="row">
                                <div class="pull-left col-md-8">
                                    <b><?=Translate::sprint("Balance")?></b>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="box-body box-title">
                        <strong><?=_lang("Remaining account credits")?></strong>
                        <div class="box-title">
                            <?php
                            $balance = $this->mWalletModel->getBalance(SessionManager::getData('id_user'))
                            ?>
                            <b class="font-size20px <?=$balance==0?"text-red":""?>">
                                <?php
                                echo Currency::parseCurrencyFormat(
                                    $balance,
                                    ConfigManager::getValue("DEFAULT_CURRENCY")
                                )
                                ?>
                            </b>

                            <?php if(SessionManager::getValue("Mobile_Auth_Management",false)): ?>
                            &nbsp;&nbsp;<a href="<?=admin_url("digital_wallet/Mobile_topUp")?>"><i class="mdi mdi-plus"></i><?=_lang("Top-up")?></a>
                            <?php else: ?>
                                &nbsp;&nbsp;<a href="<?=admin_url("digital_wallet/topUp")?>"><i class="mdi mdi-plus"></i><?=_lang("Top-up")?></a>
                            <?php endif; ?>


                            &nbsp;&nbsp;
                            <?php if( SessionManager::getValue("Mobile_Auth_Management",false)): ?>
                                <a href="<?=admin_url("digital_wallet/Mobile_withdraw")?>"><i class="mdi mdi-cash-fast"></i> <?=_lang("Withdraw")?></a>
                            <?php else: ?>
                                <a href="<?=admin_url("digital_wallet/withdraw")?>"><i class="mdi mdi-cash-fast"></i> <?=_lang("Withdraw")?></a>
                            <?php endif; ?>

                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>


<?php

$script = $this->load->view('digital_wallet/backend/html/digitalWallet/scripts/form-script',NULL,TRUE);
AdminTemplateManager::addScript($script);

?>
