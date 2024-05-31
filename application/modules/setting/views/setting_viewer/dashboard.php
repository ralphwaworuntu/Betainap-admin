<?php

$timezones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
$languages = Translate::getLangsCodes();

?>

<div class="box-body dashboard-block">
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label><?= Translate::sprint("App name", "") ?> <sup
                            class="text-red">*</sup> </label>
                <input type="text" class="form-control" required="required"
                       placeholder="<?= Translate::sprint("Enter") ?> ..." name="APP_NAME"
                       id="APP_NAME" value="<?= $config['APP_NAME'] ?>">
            </div>


            <div class="form-group required">

                <label><?= _lang("Login/Registration Logo") ?> <sup class="text-red">*</sup> </label>

            <?php

                if (!is_array(ConfigManager::getValue("APP_LOGO")))
                    $images2 = json_decode(ConfigManager::getValue("APP_LOGO"), JSON_OBJECT_AS_ARRAY);
                if (preg_match('#^([a-zA-Z0-9]+)$#', ConfigManager::getValue("APP_LOGO"))) {
                    $images2 = array(ConfigManager::getValue("APP_LOGO") => ConfigManager::getValue("APP_LOGO"));
                }

                $imagesData2 = array();

                if (count($images2) > 0) {
                    foreach ($images2 as $key => $value)
                        $imagesData2 = _openDir($value);
                    if (!empty($imagesData2))
                        $imagesData2 = array($imagesData2);
                }

                ?>


            <?php

                $upload_plug2 = $this->uploader->plugin(array(
                    "limit_key" => "aUvFiles",
                    "token_key" => "SzsYUjEsS-4555",
                    "limit" => 1,
                    "cache" => $imagesData2
                ));

                echo $upload_plug2['html'];
                AdminTemplateManager::addScript($upload_plug2['script']);

                ?>
            </div>
            <div class="form-group">
                <label><?php echo Translate::sprint("Dashboard analytics"); ?>
                    <sup>*</sup></label>
                <input type="text" class="form-control"
                       placeholder="<?= Translate::sprint("Enter") ?> ..."
                       name="DASHBOARD_ANALYTICS" id="DASHBOARD_ANALYTICS"
                       value="<?= $config['DASHBOARD_ANALYTICS'] ?>">
            </div>
            <div class="form-group">
                <label><?php echo Translate::sprint("Dashboard Color"); ?>   </label>
                <input type="text" class="form-control colorpicker1"
                       placeholder="<?= Translate::sprint("Enter") ?> ..."
                       name="DASHBOARD_COLOR" id="DASHBOARD_COLOR"
                       value="<?= $config['DASHBOARD_COLOR'] ?>">
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group hidden">
                <label><?php echo Translate::sprint("Enable default front-end"); ?>   </label>
                <select id="ENABLE_FRONT_END" name="ENABLE_FRONT_END"
                        class="form-control select2 ENABLE_FRONT_END">
                <?php
                    if ($config['ENABLE_FRONT_END']) {
                        echo '<option value="true" selected>true</option>';
                        echo '<option value="false" >false</option>';
                    } else {
                        echo '<option value="true"  >true</option>';
                        echo '<option value="false"  selected>false</option>';
                    }
                    ?>
                </select>

            </div>
            <div class="form-group hidden">
                <div class="row">
                    <div class="col-sm-6">
                        <label><?php echo Translate::sprint("Upload limitation"); ?>
                            <sup>*</sup>
                            <span style="color: grey;font-size: 11px;"><?= Translate::sprint("Number uploaded images per stores & events") ?></span></label>

                        <input type="text" class="form-control"
                               placeholder="<?= Translate::sprint("Enter") ?> ..."
                               name="IMAGES_LIMITATION" id="IMAGES_LIMITATION"
                               value="<?= $config['IMAGES_LIMITATION'] ?>">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label><?php echo Translate::sprint("Default_language"); ?>
                    <sup>*</sup></label>
                <select id="DEFAULT_LANG" name="DEFAULT_LANG"
                        class="form-control select2 DEFAULT_LANG">
                    <option value='0'><?=_lang("-- Languages")?></option>
                <?php

                    foreach ($languages as $key => $lng) {
                        if ($config['DEFAULT_LANG']
                            == $key) {
                            echo '<option value="' . $key . '" selected>' . $lng['name'] . '</option>';
                        } else {
                            echo '<option value="' . $key . '">' . $lng['name'] . '</option>';
                        }

                    }

                    ?>
                </select>

            </div>
            <div class="form-group">
                <label><?php echo Translate::sprint("Number items per page", ""); ?>
                    <sup>*</sup></label>
                <input type="text" class="form-control"
                       placeholder="<?= Translate::sprint("Enter") ?> ..."
                       name="NO_OF_ITEMS_PER_PAGE" id="NO_OF_ITEMS_PER_PAGE"
                       value="<?= $config['NO_OF_ITEMS_PER_PAGE'] ?>">
            </div>

            <div class="form-group">
                <label><?php echo Translate::sprint("Date format"); ?></label>
                <select class="select2 form-control" id="SCHEMA_DATE" name="SCHEMA_DATE">
                    <option value="yyyy-mm-dd" <?= $config['SCHEMA_DATE']=="yyyy-mm-dd"?"selected":"" ?>>yyyy-mm-dd</option>
                    <option value="dd-mm-yyyy" <?= $config['SCHEMA_DATE']=="dd-mm-yyyy"?"selected":"" ?>>dd-mm-yyyy</option>
                    <option value="dd/mm/yyyy" <?= $config['SCHEMA_DATE']=="dd/mm/yyyy"?"selected":"" ?>>dd/mm/yyyy</option>
                    <option value="yyyy/mm/dd" <?= $config['SCHEMA_DATE']=="yyyy/mm/dd"?"selected":"" ?>>yyyy/mm/dd</option>
                    <option value="yyyy/mm/dd" <?= $config['SCHEMA_DATE']=="mm/dd/yyyy"?"selected":"" ?>>mm/dd/yyyy</option>
                </select>
            </div>

            <div class="form-group">
                <label> <?php echo Translate::sprint("Date format"); ?></label>
                <select id="DATE_FORMAT" name="DATE_FORMAT"
                        class="form-control select2 DATE_FORMAT">
                    <option value="24" <?=ConfigManager::getValue("DATE_FORMAT")=="24"?"selected":""?>>24H format</option>
                    <option value="12" <?=ConfigManager::getValue("DATE_FORMAT")=="12"?"selected":""?>>12H format</option>
                </select>
            </div>


        </div>
    </div>
</div>

<div class="box-footer">
    <div class="pull-right">
        <button type="button" class="btn  btn-primary btnSaveDashboardConfig"><span
                    class="glyphicon glyphicon-check"></span>&nbsp;<?php echo Translate::sprint("Save", "Save"); ?>
        </button>
    </div>
</div>


<?php


$data['config'] = $config;
$data['uploader_variable2'] = $upload_plug2['var'];

$script = $this->load->view('setting/setting_viewer/scripts/dashboard-script', $data, TRUE);
AdminTemplateManager::addScript($script);

?>
