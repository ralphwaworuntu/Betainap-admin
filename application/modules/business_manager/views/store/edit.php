<?php

$categories = $categories[Tags::RESULT];
$store = $dataStores[Tags::RESULT][0];


?>


    <div id="app" class="framework7-root">
        <div id="create-business" class="page-content no-padding-top" data-item="">
            <!--<a href="#" class="link back close-button">
                <i class="mdi mdi-close"></i>
            </a>-->
            <div class="block">
                <div class="form-container">

                    <div class="list no-hairlines custom-form">

                        <div class="step-1-form">

                            <h1 class="create-title"><?= _lang("Business Location") ?></h1>

                        <?php

                            $map = LocationPickerManager::plug_pick_location(array(
                                'lat' => $store['latitude'],
                                'lng' => $store['longitude'],
                                'address' => $store['address'],
                                'custom_address' => $store['address'],
                                'city' => "",
                                'country' => "",

                                'size_height' => "300px",
                                'size_width' => "100%",

                            ), array(
                                'lat' => FALSE,
                                'lng' => FALSE,
                                'address' => TRUE,
                                'custom_address' => TRUE,
                            ), array(
                                'address' => _lang("Enter address") . " (*)",
                            ));

                            echo $map['html'];
                            AdminTemplateManager::addScript($map['script']);
                            $data['location_fields_id'] = $map['fields_id'];

                            ?>

                            <div class="row">
                                <div class="col-50">
                                    <a class="big-button button" id="cancel"> <i
                                                class="mdi <?=Translate::getDir()=="rtl"?"mdi-arrow-right":"mdi-arrow-left"?>"></i>&nbsp;<?= _lang("Cancel") ?></a>
                                </div>
                                <div class="col-50">
                                    <a class="big-button button button-fill link"
                                       id="go-step-2"><?= _lang("Continue") ?> <i
                                                class="mdi <?=Translate::getDir()=="rtl"?"mdi-arrow-left":"mdi-arrow-right"?>"></i></a>
                                </div>
                            </div>
                        </div>

                        <div class="step-2-form hidden">

                            <h1 class="create-title"><?= _lang("Business Detail") ?></h1>

                            <ul>
                                <li class="item-content item-input">
                                    <div class="item-inner">
                                        <div class="item-input-wrap">
                                        <?php
                                            $cat_value = "";
                                                if (!empty($categories)) {
                                                    foreach ($categories AS $cat) {
                                                        if ($cat['id_category'] == $store['category_id']) {
                                                            $cat_value = $cat['name'];
                                                        }
                                                    }
                                                }
                                            ?>

                                            <input id="autocomplete-categories" type="text"
                                                   placeholder="<?= _lang("Business Category") ?> (*)" class="" value="<?=$cat_value?>">
                                            <span class="input-clear-button"></span>
                                            <input type="hidden" id="category" value="<?=$store['category_id']?>">
                                        </div>
                                    </div>
                                </li>
                                <li class="item-content item-input">
                                    <div class="item-inner">
                                        <div class="item-input-wrap">
                                            <input type="text" id="name" placeholder="<?= _lang("Business Name") ?> (*)"
                                                   class="" value="<?=$store['name']?>">
                                            <span class="input-clear-button"></span>
                                        </div>
                                    </div>
                                </li>
                                <li class="item-content item-input">
                                    <div class="item-inner">
                                        <div class="item-input-wrap">
                                            <textarea class="resizable" id="description"
                                                      placeholder="<?= _lang("Description") ?> (*)"><?=$store['detail']?></textarea>
                                            <span class="input-clear-button"></span>
                                        </div>
                                    </div>
                                </li>
                                <li class="item-content item-input">
                                    <div class="item-inner">
                                        <div class="item-input-wrap">
                                            <input type="text" id="phone"
                                                   placeholder="<?= _lang("Phone Number Ex: +1 000-000-000") ?>"
                                                   class="" value="<?=$store['telephone']?>">
                                            <span class="input-clear-button"></span>
                                        </div>
                                    </div>
                                </li>

                                <li class="item-content item-input">
                                    <div class="item-inner">
                                        <div class="item-input-wrap">
                                            <input type="text" id="video_url" placeholder="<?=_lang("Youtube URL")?>" class=""
                                                   value="<?=$store['video_url']?>">
                                            <span class="input-clear-button"></span>
                                        </div>
                                    </div>
                                </li>

                            </ul>
                            <div class="row">
                                <div class="col-50">
                                    <a class="big-button button" id="back-step-1"> <i
                                                class="mdi <?=Translate::getDir()=="rtl"?"mdi-arrow-right":"mdi-arrow-left"?>"></i>&nbsp;<?= _lang("Back") ?></a>
                                </div>
                                <div class="col-50">
                                    <a class="big-button button button-fill link"
                                       id="go-step-3"><?= _lang("Continue") ?> <i
                                                class="mdi <?=Translate::getDir()=="rtl"?"mdi-arrow-left":"mdi-arrow-right"?>"></i></a>
                                </div>
                            </div>
                        </div>

                        <div class="step-3-form hidden">

                            <h1 class="create-title"><?= _lang("Business Photos") ?></h1>

                            <div style="width: 100%;display: block;clear: both">
                                <h3><i class="mdi mdi-camera"></i>&nbsp;<?= _lang("Upload Photos") ?></h3>
                            <?php

                                $images = $store['images'];
                                if ($images != "" AND !is_array($images)) {
                                    $images = json_decode($images);
                                }

                                $upload_plug = $this->uploader->plugin(array(
                                    "limit_key" => "publishFiles",
                                    "token_key" => "SzYjES-4555",
                                    "limit" => MAX_STORE_IMAGES,
                                    "cache" => $images
                                ));

                                echo $upload_plug['html'];
                                AdminTemplateManager::addScript($upload_plug['script']);
                                $data['uploader_variable'] = $upload_plug['var'];

                                ?>

                            </div>


                            <div style="width: 100%;display: block;clear: both">
                            <?php


                                if (ModulesChecker::isRegistred("gallery") and isset($gallery[Tags::RESULT])) {
                                    //load view
                                    $gallery_variable = $this->mGalleryModel->setup("store-gallery", $gallery[Tags::RESULT], $store['user_id']);
                                    $data['gallery_variable'] = $gallery_variable;
                                }

                                ?>
                                <br><br>

                            </div>
                            <div style="width: 100%;display: block;clear: both">
                                <div class="row">
                                    <div class="col-50">
                                        <a class="big-button button" id="back-step-2"> <i
                                                    class="mdi <?=Translate::getDir()=="rtl"?"mdi-arrow-right":"mdi-arrow-left"?>"></i>&nbsp;<?= _lang("Back") ?></a>
                                    </div>
                                    <div class="col-50">
                                        <a class="big-button button button-fill link"
                                           id="go-step-4"><?= _lang("Continue") ?> <i
                                                    class="mdi <?=Translate::getDir()=="rtl"?"mdi-arrow-left":"mdi-arrow-right"?>"></i></a>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="step-4-form hidden">

                            <h1 class="create-title"><?=_lang("Business options")?></h1>



                            <div>
                                <label class="item-checkbox item-content" style=" background: none;">
                                    <input name="canChat" type="checkbox" id="canChat" <?php if (isset($store["canChat"]) and $store["canChat"] == 1) echo "checked"; ?>/>
                                    <i class="icon icon-checkbox"></i>
                                    <div class="item-inner">
                                        <div class="item-title"><?=_lang("Enable chat")?></div>
                                    </div>
                                </label>
                            </div>


                        <?php if(ModulesChecker::isEnabled("booking")): ?>
                                <div>
                                    <label class="item-checkbox item-content" style=" background: none;">
                                        <input name="book" type="checkbox" id="book" <?php if (isset($store["book"]) and $store["book"] == 1) echo "checked"; ?>/>
                                        <i class="icon icon-checkbox"></i>
                                        <div class="item-inner">
                                            <div class="item-title"><?=_lang("Enable booking")?></div>
                                        </div>
                                    </label>
                                </div>
                        <?php endif; ?>


                            <div>
                                <label class="item-checkbox item-content" style=" background: none;">
                                    <input type="checkbox" id="opening_time"/>
                                    <i class="icon icon-checkbox"></i>
                                    <div class="item-inner">
                                        <div class="item-title"><?=_lang("Enable Opening time")?></div>
                                    </div>
                                </label>
                            </div>

                            <div id="op-form" class="hidden">
                            <?php if(OPENING_TIME_ENABLED): ?>
                                <?php
                                    $days = array(
                                        'monday',
                                        'tuesday',
                                        'wednesday',
                                        'thursday',
                                        'friday',
                                        'saturday',
                                        'sunday',
                                    );
                                    ?>

                                <?php foreach ($days as $key => $day): ?>

                                        <label><?=ucfirst(Translate::sprint($day))?></label>

                                        <label class="item-checkbox item-content op-list">
                                            <input type="checkbox" data-key="<?=$day?>" class="_checked_d_<?=$key?>" id="_checked_d_<?=$day?>" />
                                            <i class="icon icon-checkbox"></i>
                                            <div class="item-inner" style="padding-top: 0px;padding-bottom: 0px;">
                                                <div class="row">
                                                    <div class="col-50">
                                                        <input placeholder="<?=Translate::sprint("Opening time")?>" type="text" class="form-control date-picker _o_d_<?=$key?>" id="_o_d_<?=$day?>" disabled/>
                                                    </div>
                                                    <div class="col-50">
                                                        <input placeholder="<?=Translate::sprint("Closing time")?>" type="text" class="form-control date-picker _c_d_<?=$key?>" id="_c_d_<?=$day?>" disabled/>
                                                    </div>
                                                </div>
                                            </div>
                                        </label>

                                <?php endforeach; ?>

                                <?php

                                    $data['times'] = $this->mStoreModel->getOpeningTimes($store['id_store']);
                                    $data['days'] = $days;

                                    $ot_script = $this->load->view('business_manager/store/scripts/edit-opening-time-script',$data,TRUE);
                                    AdminTemplateManager::addScript($ot_script);

                                    ?>

                            <?php endif; ?>
                            </div>

                            <div class="row">
                                <div class="col-50">
                                    <a class="big-button button" id="back-step-3"> <i
                                                class="mdi <?=Translate::getDir()=="rtl"?"mdi-arrow-right":"mdi-arrow-left"?>"></i>&nbsp;<?= _lang("Back") ?></a>
                                </div>
                                <div class="col-50">
                                    <a class="big-button button button-fill link" id="save-business"> <i
                                                class="mdi mdi-check"></i>&nbsp;<?= _lang("Save") ?></a>
                                </div>
                            </div>
                        </div>


                    </div>

                </div>
            </div>
        </div>
    </div>


<?php

$data['store_id'] = $store['id_store'];
$script = $this->load->view('business_manager/store/scripts/edit-script', $data, TRUE);
AdminTemplateManager::addScript($script);


?>