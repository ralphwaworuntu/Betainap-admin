<?php

$offer = $offer[Tags::RESULT][0];
$adminAccess = "";
if ($offer['user_id'] != $this->mUserBrowser->getData("id_user")) {
    $adminAccess = "disabled";
}


?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <!-- Message Error -->
            <div class="col-sm-12">
            <?php $this->load->view(AdminPanel::TemplatePath."/include/messages"); ?>
            </div>
        </div>

        <div class="row" id="form">
            <div class="col-sm-6">
                <div class="box box-solid">
                    <div class="box-header">

                        <div class="box-title">
                            <b><?= Translate::sprint("Edit Offer", "") ?></b>
                        </div>

                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <div class="row">
                            <div class="col-sm-12">

                                <div class="form-group">
                                    <label><?= Translate::sprint("Store") ?></label>
                                    <select id="selectStore" class="form-control select2 selectStore" style="width: 100%;">
                                        <option value="0">
                                            <?= Translate::sprint("Select store", "") ?></option>
                                        <?php
                                        if (isset($myStores[Tags::RESULT])) {
                                            foreach ($myStores[Tags::RESULT] as $st) {
                                                echo '<option 
                                                    value="' . $st['id_store'] . '" 
                                                    data-adr="' . $st['address'] . '" 
                                                    data-lat="' . $st['latitude'] . '" 
                                                    data-lng="' . $st['longitude'] . '" 
                                                    data-value="' . $st['id_store'] . '">' . $st['name'] . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label><?= Translate::sprint("Name", "") ?></label>
                                    <input data-field-translator="true" type="text" class="form-control" name="name" id="name"
                                           placeholder="Ex: black friday" value="<?= $offer['name'] ?>">
                                </div>

                                <div class="form-group">
                                    <label><?= Translate::sprint("Description", "") ?></label>
                                    <textarea data-field-translator="true" name="editable-textarea" class="form-control" rows="7" id="editable-textarea"
                                              placeholder="<?= Translate::sprint("Enter") ?> ..."><?= $offer['description'] ?></textarea>
                                </div>


                            </div>
                        </div>
                    </div>

                    <div class="box box-solid">
                        <div class="box-header">

                            <div class="box-title">
                                <b><?= Translate::sprint("Images", "") ?></b>
                            </div>

                        </div>


                        <div class="box-body">
                            <!-- text input -->
                            <div class="form-group required">

                            <?php

                                $images = $offer['images'];
                                if ($images != "" AND !is_array($images)) {
                                    $images = json_decode($images);
                                }

                                ?>

                            <?php

                                $upload_plug = $this->uploader->plugin(array(
                                    "limit_key"     => "aOhFiles",
                                    "token_key"     => "SzYjEsS-4555",
                                    "limit"         => MAX_OFFER_IMAGES,
                                    "cache"         => $images,
                                ));

                                echo $upload_plug['html'];
                                AdminTemplateManager::addScript($upload_plug['script']);

                                ?>

                            </div>

                        </div>
                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->
            </div>

        <?php if (GroupAccess::isGranted('offer',MANAGE_OFFERS)): ?>
                <div class="col-md-6">

                    <div class="box box-solid">
                        <div class="box-header with-border">
                            <h3 class="box-title"><b>
                                    <?= Translate::sprint("Featured Options") ?></b></h3>
                        </div>

                        <div class="box-body">

                        <?php

                            $checked0 = "";
                            if (intval($offer['featured']) == 0)
                                $checked0 = " checked='checked'";

                            $checked = "";
                            if (intval($offer['featured']) == 1)
                                $checked = " checked='checked'";

                            ?>

                            <div class="form-group">
                                <label style="cursor: pointer;">
                                    <input name="featured" type="radio" id="featured_item0" <?= $checked0 ?>>&nbsp;&nbsp;
                                    <?= Translate::sprint("Disabled Featured") ?>
                                </label><br>
                                <label style="cursor: pointer;">
                                    <input name="featured" type="radio" id="featured_item1" <?= $checked ?>>&nbsp;&nbsp;
                                    <?= Translate::sprint("Make it as featured") ?>
                                </label>
                            </div>


                        </div>

                    </div>

                </div>
        <?php endif; ?>

            <div class="col-sm-6">

                <div class="row">

                    <div class="col-sm-12">
                        <div class="box box-solid">
                            <div class="box-header">

                                <div class="box-title">
                                    <b><?= Translate::sprint("Pricing & Offer value") ?></b>
                                    <span style="color: grey;font-size: 11px;">(  <?php echo Translate::sprint("For promotion offers, leave this field as the default ", ""); ?>)</span>
                                </div>

                            </div>

                            <div class="box-body">
                                <!-- text input -->
                                <div class="pricing">

                                    <div class="form-group">
                                        <select id="value_type" class="select2">
                                            <option value="0">-- <?= Translate::sprint('Select Type') ?></option>
                                            <option value="1"><?= Translate::sprint('Price') ?></option>
                                            <option value="2"><?= Translate::sprint('Percent') ?></option>
                                        </select>
                                    </div>

                                <?php

                                    $currency = $this->mCurrencyModel->getCurrency(DEFAULT_CURRENCY);

                                    ?>

                                    <div class="form-group form-price hidden">
                                        <div class="row">
                                            <div class="col-sm-12 no-margin">
                                                <label><?= Translate::sprint("Offer price") ?> <?=DEFAULT_CURRENCY?>, <?=$currency['symbol']?></label>
                                                <div class="form-group">
                                                    <input type="number" class="form-control" id="priceInput"
                                                           placeholder="<?= Translate::sprint("Enter price of your offer") ?>" value="<?php if($offer['value_type']=="price") echo $offer['offer_value']?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group form-percent hidden">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <label><?= Translate::sprint("Offer percent", "") ?> </label>
                                                <div class="form-group">
                                                    <input  <?= $adminAccess ?>  type="number" class="form-control" id="percentInput"
                                                           placeholder="Exemple : -50 %"  value="<?php if($offer['value_type']=="percent") echo $offer['offer_value']?>">
                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>
                    </div>

                <?php
                    if(ModulesChecker::isEnabled("qrcoupon")
                        && GroupAccess::isGranted('qrcoupon',GRP_MANAGE_QRCOUPONS_KEY)){
                            $result =  $this->qrcoupon->setup_widget_coupon_config(array(
                                    "offer_id" => $offer['id_offer']
                            ));

                            echo $result['html'];
                            AdminTemplateManager::addScript($result['script']);
                        }
                    ?>

                    <div class="col-sm-12">
                        <div class="box box-solid">
                            <div class="box-header">
                                <div class="box-title">
                                    <b><?= Translate::sprint("Deal Option") ?></b>
                                </div>
                            </div>
                            <div class="box-body">
                                <div class="form-group">
                                    <label><input type="checkbox" id="make_as_deal"/>&nbsp;&nbsp;<?=_lang("Make as a deal")?></label>
                                </div>

                                <div class="form-group deal-data hidden">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label> <?= Translate::sprint("Date Begin") ?>  </label>
                                            <div class="input-group">
                                                <div class="input-group-addon">
                                                    <i class="mdi mdi-calendar"></i>
                                                </div>
                                            <?php

                                                $date_start = "";
                                                if ($offer['date_start'] != "")
                                                    $date_start = date("Y-m-d", strtotime($offer['date_start']));

                                                ?>
                                                <input  <?= $adminAccess ?> class="form-control" data-provide="datepicker"
                                                                            placeholder="YYYY-MM-DD" type="text" name="date_b"
                                                                            id="date_b"
                                                                            data-format="yyyy-mm-dd"
                                                                            value="<?= $date_start?>"/>
                                            </div>

                                        </div>
                                        <div class="col-md-6">
                                            <label><?= Translate::sprint("Date End") ?> </label>

                                            <div class="input-group">
                                                <div class="input-group-addon">
                                                    <i class="mdi mdi-calendar"></i>
                                                </div>

                                            <?php

                                                $date_end = "";
                                                if ($offer['date_end'] != "")
                                                    $date_end = date("Y-m-d", strtotime($offer['date_end']));

                                                ?>
                                                <input <?= $adminAccess ?> class="form-control"
                                                                           data-provide="datepicker" type="text"
                                                                           placeholder="YYYY-MM-DD"
                                                                           data-format="yyyy-mm-dd"
                                                                           name="date_e" id="date_e"
                                                                           value="<?= $date_end ?>"/>


                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                        <?php if(!ModulesChecker::isEnabled("nsorder")): ?>
                                <div class="box-footer">
                                    <button <?= $adminAccess ?>  type="button" class="btn  btn-primary" id="btnSave"><span
                                                class="glyphicon glyphicon-check"></span>
                                        <?= Translate::sprint("Save Changes", "") ?> </button>
                                </div>
                        <?php endif; ?>
                        </div>
                    </div>


                <?php if(ModulesChecker::isEnabled("nsorder")): ?>
                    <div class="col-sm-12">
                        <div class="box box-solid">
                            <div class="box-header">
                                <div class="box-title">
                                    <b><?= Translate::sprint("Order Option") ?></b>
                                </div>
                            </div>
                            <div class="box-body">

                                <div class="form-group">
                                    <label><input type="checkbox" id="enable_order"/>&nbsp;&nbsp;<?=_lang("Enable order option")?></label>
                                </div>

                                <div class="order-customization hidden">
                                <?php if(GroupAccess::isGranted("cf_manager")): ?>

                                        <div class="form-group">
                                            <select id="cf_id" class="select2">
                                                <option value="0">-- <?= Translate::sprint('Select Checkout Fields') ?></option>
                                            <?php foreach ($cf_list as $cf): ?>
                                                    <option value="<?=$cf['id']?>" <?=$offer['cf_id']==$cf['id']?"selected":""?>><?=$cf['label']?></option>
                                            <?php endforeach; ?>
                                            </select>
                                        </div>


                                <?php else: ?>
                                        <p class="text-blue"><i class="mdi mdi-information-outline"></i>&nbsp;&nbsp;<?=_lang("The order option will request information from the client, order form will be customized depend on store's category and provided by the admin")?></p>
                                <?php endif; ?>

                                    <div class="form-group">
                                        <label><?=_lang("Button Template")?></label>
                                        <select id="button_template" class="select2">
                                            <option value="0">-- <?= Translate::sprint('Select Template') ?></option>
                                        <?php foreach ($this->mOfferModel->button_templates as $key => $tmp): ?>
                                                <option value="<?=$key?>" <?=$key==$offer['order_button']?"selected":""?>><?=_lang($tmp)?></option>
                                        <?php endforeach; ?>
                                        </select>

                                        <div class="input-group hidden">
                                            <input class="form-control" type="text" id="custom-button-text" placeholder="<?=_lang("Enter...")?>">
                                            <div class="input-group-addon cursor-pointer text-blue" id="open-oml"><i class="mdi mdi-translate"></i></div>
                                        </div>
                                    </div>


                                </div>


                            </div>

                            <div class="box-footer">
                                <button <?= $adminAccess ?>  type="button" class="btn  btn-primary" id="btnSave"><span
                                            class="glyphicon glyphicon-check"></span>
                                    <?= Translate::sprint("Save Changes", "") ?> </button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                </div>


            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->
    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->
<?php


$data['offer'] = $offer;
$data['uploader_variable'] = $upload_plug['var'];

$script = $this->load->view('offer/backend/html/scripts/edit-script',$data,TRUE);
AdminTemplateManager::addScript($script);


$data0 = array();
$html = $this->load->view('offer/backend/html/modal-order-multi-language',$data0,TRUE);
AdminTemplateManager::addHtml($html);

?>



