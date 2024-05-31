<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <section class="content">
        <div class="row">
            <!-- Message Error -->
            <div class="col-sm-12">
                <?php $this->load->view(AdminPanel::TemplatePath . "/include/messages"); ?>
            </div>

        </div>

        <div class="row">

            <form id="form" role="form">

                <div class="col-md-12">

                    <div class="box box-solid">
                        <div class="box-header with-border">
                            <h3 class="box-title"><b><?= Translate::sprint("Edit Event") ?> (<?=$event['name']?>)</b></h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">

                            <form>
                                <?= csrfInput() ?>
                                <div class="steps">

                                    <!-- SmartWizard html -->
                                    <div id="smartwizard">
                                        <ul class="nav nav-progress">
                                            <li class="nav-item">
                                                <a class="nav-link mt-3"
                                                   href="<?= admin_url("event/edit?id=".$event['id_event']."#details") ?>">
                                                    <div class="num">1</div>
                                                    <?= _lang("Details") ?>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link mt-3"
                                                   href="<?= admin_url("event/edit?id=".$event['id_event']."#images") ?>">
                                                    <span class="num">2</span>
                                                    <?= _lang("Images") ?>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link mt-3"
                                                   href="<?= admin_url("event/edit?id=".$event['id_event']."#location") ?>">
                                                    <span class="num">3</span>
                                                    <?= _lang("Address & Location") ?>
                                                </a>
                                            </li>

                                            <li class="nav-item">
                                                <a class="nav-link mt-3"
                                                   href="<?= admin_url("event/edit?id=".$event['id_event']."#options") ?>">
                                                    <span class="num">4</span>
                                                    <?= _lang("Pricing & Booking") ?>
                                                </a>
                                            </li>

                                            <li class="nav-item">
                                                <a class="nav-link pt-5"
                                                   href="<?= admin_url("event/edit?id=".$event['id_event']."#success") ?>">
                                                    <span class="num">5</span>
                                                    <?= _lang("Success") ?>
                                                </a>
                                            </li>
                                        </ul>

                                        <div class="tab-content ">
                                            <div id="details" class="tab-pane" role="tabpanel"
                                                 aria-labelledby="details">
                                                <!-- text input -->
                                                <div class="row">
                                                    <div class="col-sm-12">
                                                        <div class="alert alert-danger alert-dismissible error-messages hidden">
                                                            <button type="button" class="close" data-dismiss="alert"
                                                                    aria-hidden="true">×
                                                            </button>
                                                            <h4><?= _lang("Errors") ?></h4>
                                                            <p></p>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <input type="hidden" id="event_id" value="<?=$event['id_event']?>">
                                                        <div class="form-group" data-field-error="store_id">
                                                            <label><?= Translate::sprint("Store") ?> <sup
                                                                        class="text-red">*</sup></label>
                                                            <select class="form-control select2 store_id" id="store_id">
                                                                <option selected="selected"
                                                                        value="0"><?= Translate::sprint("-- Store", "") ?></option>
                                                                <?php

                                                                if (isset($myStores[Tags::RESULT])) :
                                                                    foreach ($myStores[Tags::RESULT] as $st):
                                                                        echo '<option 
                                                                                    data-address="' . $st['address'] . '" 
                                                                                    data-location-lat="' . $st['latitude'] . '" 
                                                                                    data-location-lng="' . $st['longitude'] . '" 
                                                                                    value="' . $st['id_store'] . '"
                                                                                    '.($event['store_id']==$st['id_store']?"selected":"").'>' . $st['name'] . '</option>';
                                                                    endforeach;
                                                                endif;

                                                                ?>
                                                            </select>
                                                        </div>
                                                        <div class="form-group" data-field-error="name">
                                                            <label><?= Translate::sprint("Name") ?> <sup
                                                                        class="text-red">*</sup></label>
                                                            <input data-field-translator="true" type="text"
                                                                   class="form-control"
                                                                   placeholder="<?= Translate::sprint("Enter") ?> ..."
                                                                   name="name" id="name" value="<?=$event['name']?>">
                                                        </div>
                                                        <div class="form-group" data-field-error="description">
                                                            <label><?= Translate::sprint("Description") ?> :</label>
                                                            <textarea data-field-translator="true" name="description"
                                                                      id="description" class="form-control"
                                                                      style="height: 300px"><?=$event['description']?></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="form-group" data-field-error="date_b">
                                                            <label><?= Translate::sprint("Date Begin") ?> <sup
                                                                        class="text-red">*</sup></label>
                                                            <input class="form-control"
                                                                   data-provide="datepicker"
                                                                   data-default="<?= DateSetting::defaultFormat() ?>"
                                                                   value="<?= date(DateSetting::defaultFormat0(), strtotime($event['date_b'])) ?>"
                                                                   placeholder="<?= DateSetting::defaultFormat() ?>"
                                                                   type="text" name="date_b" id="date_b"/>
                                                        </div>
                                                        <div class="form-group" data-field-error="date_e">
                                                            <label><?= Translate::sprint("Date End") ?> <sup
                                                                        class="text-red">*</sup></label>
                                                            <input class="form-control"
                                                                   data-provide="datepicker" type="text"
                                                                   data-default="<?= DateSetting::defaultFormat() ?>"
                                                                   placeholder="<?= DateSetting::defaultFormat() ?>"
                                                                   value="<?= date(DateSetting::defaultFormat0(), strtotime($event['date_e'])) ?>"
                                                                   name="date_e" id="date_e"/>
                                                        </div>

                                                        <hr/>


                                                        <div class="form-group" data-field-error="tel">
                                                            <label><?= Translate::sprint("Phone Number") ?> </label>
                                                            <div class="row">
                                                                <div class="col-sm-3">
                                                                    <select class="select2" name="telCode" id="telCode">
                                                                        <?php foreach (loadPhoneCodes() as $code): ?>
                                                                            <option value="<?= $code['dial_code'] ?>" <?=$event['country_code']==$code['code']?'selected':''?>><?= $code['dial_code'] ?>
                                                                                (<?= $code['code'] ?>)
                                                                            </option>
                                                                        <?php endforeach; ?>
                                                                    </select>
                                                                </div>
                                                                <div class="col-sm-9 pl-0">
                                                                    <input type="text" class="form-control"
                                                                           placeholder="<?= Translate::sprint("Enter") ?> ..."
                                                                           name="tel" id="tel" value="<?=str_replace(loadPhoneCodeByCode($event['country_code']),'', $event['tel'])?>">
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="form-group" data-field-error="web">
                                                            <label><?= Translate::sprint("Website") ?> </label>
                                                            <input type="text" class="form-control"
                                                                   placeholder="<?= Translate::sprint("Enter") ?> ..."
                                                                   name="web" id="web" value="<?=$event['website']?>">
                                                        </div>
                                                    </div>
                                                </div>


                                            </div>
                                            <div id="images" class="tab-pane" role="tabpanel" aria-labelledby="images">
                                                <div class="row">
                                                    <div class="col-sm-12">
                                                        <div class="form-group required">

                                                            <?php

                                                                $images = $event['images'];

                                                                $upload_plug = $this->uploader->plugin(array(
                                                                    "limit_key" => "aEvFiles",
                                                                    "token_key" => "SzYUjEsS-4555",
                                                                    "limit" => MAX_EVENT_IMAGES,
                                                                    "cache" => $images
                                                                ));

                                                                echo $upload_plug['html'];
                                                                AdminTemplateManager::addScript($upload_plug['script']);

                                                            ?>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="location" class="tab-pane" role="tabpanel"
                                                 aria-labelledby="location">

                                                <?php

                                                $map = LocationPickerManager::plug_pick_location(array(
                                                    'lat'=>$event['lat'],
                                                    'lng'=>$event['lng'],
                                                    'address'=>$event['address'],
                                                    'custom_address'=>$event['address'],
                                                    'city'=> "",
                                                    'country'=> "",
                                                ),array(
                                                    'lat'=>TRUE,
                                                    'lng'=>TRUE,
                                                    'address'=>TRUE,
                                                    'custom_address'=>TRUE,
                                                ));

                                                echo $map['html'];
                                                AdminTemplateManager::addScript($map['script']);
                                                $data['location_fields_id'] = $map['fields_id'];

                                                ?>

                                            </div>

                                            <div id="options" class="tab-pane" role="tabpanel"
                                                 aria-labelledby="options">
                                                <div class="pb-5">
                                                    <div class="row">
                                                        <div class="col-sm-6">

                                                            <div class="form-group" data-field-error="name">
                                                                <label><?= Translate::sprint("Ticket Number") ?> <sup
                                                                            class="text-red">*</sup> (<?=_lang('-1 for unlimited')?>)</label>
                                                                <p><i class="mdi mdi-information"></i> <?=_lang('Enter how many tickets will be booked')?></p>
                                                                <input data-field-translator="true" type="number"
                                                                       class="form-control"
                                                                       placeholder="<?= Translate::sprint("Enter") ?> ..."
                                                                       name="limit" id="limit" value="-1">
                                                            </div>

                                                            <hr/>

                                                            <div class="form-group">
                                                                <label><input type="radio" value="1" name="booking" <?=$event['price']>0?"checked":""?>>&nbsp;&nbsp;<?= Translate::sprint("Enable booking") ?>
                                                                    <p class="text-blue"><i
                                                                                class="mdi mdi-information"></i> <?= _lang("Allow customer to book the event") ?>
                                                                    </p></label>
                                                            </div>

                                                            <div class="event-booking-price  <?=$event['price']==0?"hidden":""?>">
                                                                <?php if (ConfigManager::getValue('EVENT_BOOK_COMMISSION_ENABLED')): ?>
                                                                    <div class="form-group">
                                                                        <label><?= Translate::sprint("Price per ticket") ?>
                                                                            <sup class="text-red">*</sup>
                                                                            (<?= ConfigManager::getValue("DEFAULT_CURRENCY") ?>
                                                                            )</label>
                                                                        <input class="form-control"
                                                                               type="number"
                                                                               placeholder="<?= _lang("Enter price") ?>"
                                                                               name="price_without_commission"
                                                                               id="price_without_commission" value="<?=($event['price']-$event['commission'])?>"/>
                                                                    </div>

                                                                    <input type="hidden"
                                                                           id="commission"
                                                                           value="<?= ConfigManager::getValue('EVENT_BOOK_COMMISSION_VALUE') ?>">

                                                                    <div class="form-group">
                                                                        <label><?= Translate::sprint("Price with commission") ?>
                                                                            <sup class="text-red">*</sup>
                                                                            (<?= ConfigManager::getValue("DEFAULT_CURRENCY") ?>
                                                                            )</label>
                                                                        <input class="form-control"
                                                                               type="number"
                                                                               placeholder="<?= _lang("Enter price") ?>"
                                                                               name="price" id="price" disabled value="<?=$event['price']?>"/>
                                                                    </div>

                                                                <?php else: ?>
                                                                    <div class="form-group">
                                                                        <label><?= Translate::sprint("Price per ticket") ?>
                                                                            <sup class="text-red">*</sup>
                                                                            (<?= ConfigManager::getValue("DEFAULT_CURRENCY") ?>
                                                                            )</label>
                                                                        <input class="form-control"
                                                                               type="number"
                                                                               placeholder="<?= _lang("Enter price") ?>"
                                                                               name="price_without_commission"
                                                                               id="price_without_commission"/>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>

                                                            <div class="form-group">
                                                                <label><input type="radio" value="0" name="booking"  <?=$event['price']==0?"checked":""?>>&nbsp;&nbsp;<?= Translate::sprint("Free") ?>
                                                                    <p class="text-blue"><i
                                                                                class="mdi mdi-information"></i> <?= _lang("Allow customer to participate in the event for free") ?>
                                                                    </p>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div id="success" class="tab-pane" role="tabpanel"
                                                 aria-labelledby="booking">
                                                <div class="text-center pb-5">
                                                    <h3 class="text-green"><i
                                                                class="icon-success mdi mdi-check-circle"></i><br><?= _lang("Success") ?>
                                                    </h3>
                                                    <p> <?= _lang("The event updated successful!") ?></p>
                                                    <a href="<?=admin_url("event/edit?id=".$event['id_event']."")?>"><i class="mdi mdi-plus"></i> <?=_lang("Add new")?></a>
                                                    <a href="<?=admin_url("event/my_events")?>"><i class="mdi mdi-view-list"></i> <?=_lang("Manage events")?></a>
                                                </div>
                                            </div>

                                        </div>

                                    </div>
                            </form>

                        </div>
                    </div>
                </div>

            </form>
    </section>

</div>
<?php


$data['uploader_variable'] = $upload_plug['var'];
$script = $this->load->view('event/backend/html/scripts/editV2-script', $data, TRUE);
AdminTemplateManager::addScript($script);

?>

