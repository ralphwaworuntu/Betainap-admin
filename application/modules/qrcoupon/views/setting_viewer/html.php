<?php

$params = array();

?>

<div class="box-body offer-block">
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label><?php echo Translate::sprint("Coupon characters limit"); ?> <sup>*</sup></label>
                <input type="number" class="form-control"
                       placeholder="<?= Translate::sprint("Enter") ?> ..."
                       name="OFFER_COUPON_LIMIT"
                       id="OFFER_COUPON_LIMIT" value="<?= ConfigManager::getValue("OFFER_COUPON_LIMIT") ?>">
            </div>
        </div>
    </div>
</div>

<div class="box-footer">
    <div class="pull-right">
        <button type="button" class="btn  btn-primary btnSaveStoreConfig"><span
                    class="glyphicon glyphicon-check"></span>&nbsp;<?php echo Translate::sprint("Save", "Save"); ?>
        </button>
    </div>
</div>


<?php


$script = $this->load->view('qrcoupon/setting_viewer/scripts/script', $params, TRUE);
AdminTemplateManager::addScript($script);

?>

