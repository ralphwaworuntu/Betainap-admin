
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <section class="content">
        <div class="row">
            <!-- Message Error -->
            <div class="col-sm-12">
                <?php $this->load->view(AdminPanel::TemplatePath."/include/messages");?>
            </div>
        </div>
        <div class="form-Top-up">
            <div class="box box-solid">
                <div class="box-header">
                    <div class="box-title">
                        <strong><?=Translate::sprint("Add balance to your wallet")?></strong>
                    </div>
                </div>
                <!-- /.box-header -->
                <div class="box-body table-responsive">
                    <form>
                        <div class="form-group">
                            <select id="select_amount" class="select2">
                                <?php
                                $amounts = $this->mWalletModel->getTopUp();

                                ?>
                                <?php foreach ($amounts as $a): ?>
                                    <option value="<?=$a?>"><?=Currency::parseCurrencyFormat($a,ConfigManager::getValue("DEFAULT_CURRENCY"))?></option>
                                <?php endforeach; ?>
                                <option value="-1"><?=_lang("Custom amount")?></option>
                            </select>
                        </div>

                        <div class="form-group custom_amount hidden">
                            <input type="number" id="amount" class="form-control" value="<?=$amounts[0]?>" placeholder="<?=_lang("Enter amount")?>" />
                        </div>

                    </form>
                </div>
                <div class="box-footer">
                    <button id="TopUpBtn" class="btn btn-primary pull-right"><?=_lang("Top-up")?></button>
                </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box -->
        </div>
    </section>
</div>

<?php

$script = $this->load->view('digital_wallet/backend/html/digitalWallet/scripts/top-up-script',NULL,TRUE);
AdminTemplateManager::addScript($script);

?>







