<?php

    $banks = $result[Tags::RESULT];

?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <section class="content">
        <div class="row">
            <!-- Message Error -->
            <div class="col-sm-12">
                <?php $this->load->view(AdminPanel::TemplatePath."/include/messages");?>
            </div>
        </div>
        <div class="form-banks">
            <div class="box box-solid">
                <div class="box-header">
                    <div class="box-title">
                        <strong><?=Translate::sprint("Add bank")?></strong>
                    </div>
                </div>
                <!-- /.box-header -->
                <div class="box-body table-responsive">
                    <form>
                        <div class="form-group">
                            <label><?=_lang("Bank Name")?> <span class="text-red">*</span></label>
                            <input name="name" class="form-control" type="text" placeholder="<?=_lang("Enter Bank name")?>" />
                        </div>
                        <div class="form-group">
                            <label><?=_lang("Bank Account / Bank Number")?> <span class="text-red">*</span></label>
                            <input name="account_number" class="form-control" type="text" placeholder="<?=_lang("Enter Bank Account / Bank Number")?>" />
                        </div>
                        <div class="form-group">
                            <label><?=_lang("Bank holder Name / Full name")?> <span class="text-red">*</span></label>
                            <input name="holder_name" class="form-control" type="text" placeholder="<?=_lang("Enter Bank holder Name / Full name")?>" />
                        </div>
                        <div class="form-group">
                            <label><?=_lang("Country")?> <span class="text-red">*</span></label>
                            <input name="country" class="form-control" type="text" placeholder="<?=_lang("Enter Country")?>" />
                        </div>

                    </form>
                </div>
                <div class="box-footer">
                    <button id="addBankBtn" class="btn btn-primary pull-right"><?=_lang("Add")?></button>
                </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box -->
        </div>
    </section>
</div>

<?php

$script = $this->load->view('digital_wallet/backend/html/digitalWallet/scripts/addBank-script',NULL,TRUE);
AdminTemplateManager::addScript($script);

?>



