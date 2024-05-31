
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <section class="content">
        <div class="row">
            <!-- Message Error -->
            <div class="col-sm-12">
                <?php $this->load->view(AdminPanel::TemplatePath."/include/messages");?>
            </div>
        </div>
        <div class="form-Withdraw">
            <div class="box box-solid">
                <div class="box-header">
                    <div class="box-title">
                        <strong><?=Translate::sprint("Withdraw to your bank")?></strong>
                    </div>
                </div>
                <!-- /.box-header -->
                <div class="box-body table-responsive">

                    <div class="callout callout-success msgSuccess hidden">
                        <h4><?=_lang("Success")?></h4>
                        <p><?=_lang("Your money is requested for withdrawal to bank account")?></p>
                    </div>
                    <form>
                        <div class="form-group">
                            <label><?= _lang("Amount to be withdrawn") ?></label>
                            <input max="<?=$this->mWalletModel->getBalance(SessionManager::getData("id_user"))?>" class="form-control" name="amount" id="amount" value="<?=$this->mWalletModel->getBalance(SessionManager::getData("id_user"))?>" type="number" placeholder="<?=_lang('Enter amount')?>" />
                            <input type="hidden" id="user_id" value="<?=SessionManager::getData("id_user")?>"/>
                        </div>
                        <div class="form-group">
                            <label><?= _lang("To bank") ?></label>
                            <select class="form-control select2" name="bank">
                                <?php foreach (Wallet_helper::getBanks(SessionManager::getData("id_user")) as $bank): ?>
                                    <option value="<?=$bank['id']?>"><?=$bank['name']?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <?php if( SessionManager::getValue("Mobile_Auth_Management",false)): ?>
                                <a class="btn  btn-default" href="<?=admin_url("digital_wallet/Mobile_manageBanks?callback=".current_url())?>"><i class="mdi mdi-bank"></i> <?=_lang("Manage your banks")?></a>
                            <?php else: ?>
                                <a class="btn btn-default" href="<?=admin_url("digital_wallet/manageBanks?callback=".current_url())?>"><i class="mdi mdi-bank"></i>  <?=_lang("Manage your banks")?></a>
                            <?php endif; ?>

                        </div>
                    </form>
                </div>
                <div class="box-footer">
                    <button id="WithdrawBtn" class="btn btn-primary pull-right"><?=_lang("Withdraw")?></button>
                </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box -->
        </div>
    </section>
</div>

<?php

$script = $this->load->view('digital_wallet/backend/html/digitalWallet/scripts/withdraw-script',NULL,TRUE);
AdminTemplateManager::addScript($script);

?>



