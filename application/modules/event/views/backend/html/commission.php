

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

    <section class="content commission">

        <div class="row">
            <!-- Message Error -->
            <div class="col-sm-12">
                <?php $this->load->view(AdminPanel::TemplatePath."/include/messages"); ?>
            </div>

        </div>

        <div class="row">
            <div class="col-sm-12">

                <div class="box box-solid">
                    <div class="box-header with-bbooking">
                        <h3 class="box-title"><b><?= Translate::sprint("Manage Commission") ?></b></h3>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <div class="row">
                            <div class="col-sm-6">

                                <div class="form-group">
                                    <label><?php echo Translate::sprint("Commission enabled"); ?></label>
                                    <select id="EVENT_BOOK_COMMISSION_ENABLED" name="EVENT_BOOK_COMMISSION_ENABLED"
                                            class="form-control select2 EVENT_BOOK_COMMISSION_ENABLED">
                                        <?php

                                        if (ConfigManager::getValue('EVENT_BOOK_COMMISSION_ENABLED')==TRUE) {
                                            echo '<option value="1" selected>Enabled</option>';
                                            echo '<option value="0" >Disabled</option>';
                                        } else {
                                            echo '<option value="1"  >Enabled</option>';
                                            echo '<option value="0"  selected>Disabled</option>';
                                        }

                                        ?>
                                    </select>
                                </div>


                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label><?php echo Translate::sprint("Commission value"); ?> (%)</label>&nbsp;&nbsp;
                                    <input type="number" min="0" max="100" class="form-control"
                                           placeholder="<?= Translate::sprint("Enter percent") ?> E.g 10, 20...." name="EVENT_BOOK_COMMISSION_VALUE"
                                           id="EVENT_BOOK_COMMISSION_VALUE" value="<?= ConfigManager::getValue('EVENT_BOOK_COMMISSION_VALUE') ?>" <?=ConfigManager::getValue('EVENT_BOOK_COMMISSION_ENABLED')==FALSE?"disabled":""?>/>
                                </div>
                            </div>
                            <div class="col-sm-12">

                                <div class="callout callout-info">
                                    <p>
                                        <strong>Payment Process Explanation</strong><br/><br/>
                                        <strong>Commission Calculation:</strong> <br/>The commission charged to vendors is calculated based on a percentage of the total of booking value. It is computed as the product of the commission rate and the subtotal of the booking. Mathematically, Platform Commission = (Commission Rate / 100) x Subtotal.<br/><br/>
                                        <strong>Order Payment:</strong><br/>  The payment to the vendor is calculated by subtracting the platform commission from the subtotal of the booking. This ensures that the vendor receives the appropriate amount for their products. Order Payment = Subtotal - Platform Commission.<br/><br/>
                                        <strong>Vendor Payout:</strong><br/> Once the vendor marks an booking as delivered, the payment is made to their digital wallet. This ensures a seamless and convenient transfer of funds to the vendor's account.<br/>
                                    </p>

                                </div>


                            </div>
                        </div>
                    </div>
                    <!-- /.box-body -->
                    <div class="box-footer">
                        <button type="button" class="btn  btn-primary btnSave"><span
                                class="glyphicon glyphicon-check"></span><?php echo Translate::sprint("Save", "Save"); ?>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </section>

</div>


<?php


$script = $this->load->view('event/backend/html/scripts/commission-scripts', NULL, TRUE);
AdminTemplateManager::addScript($script);

?>




