
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <section class="content">
        <div class="row">
            <!-- Message Error -->
            <div class="col-sm-12">
                <?php $this->load->view(AdminPanel::TemplatePath."/include/messages"); ?>
            </div>

        </div>

        <div class="row otp-config">
            <div class="col-sm-6">
                <div class="box box-solid">
                    <div class="box-header with-border">
                        <h3 class="box-title"><b><?php echo Translate::sprint("OTP Config"); ?></b></h3>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <form class="form" role="form">

                            <div class="col-sm-12">


                                <div class="form-group">
                                    <label> <?php echo Translate::sprint("OTP"); ?></label>
                                    <select id="OTP_ENABLED" name="OTP_ENABLED"
                                            class="form-control select2 OTP_ENABLED">
                                        <option value="0" <?=ConfigManager::getValue("OTP_ENABLED")==0?"selected":""?>><?=_lang('Disabled')?></option>
                                        <option value="1" <?=ConfigManager::getValue("OTP_ENABLED")==1?"selected":""?>><?=_lang('Enabled')?></option>
                                    </select>
                                </div>

                                <?php if(ConfigManager::getValue("OTP_ENABLED")==1): ?>
                                    <div class="form-group">
                                        <label> <?php echo Translate::sprint("OTP test mode"); ?></label>
                                        <select id="OTP_ENABLED" name="OTP_TEST_ENABLED"
                                                class="form-control select2 OTP_TEST_ENABLED">
                                            <option value="0" <?=ConfigManager::getValue("OTP_TEST_ENABLED")==0?"selected":""?>><?=_lang('Disabled')?></option>
                                            <option value="1" <?=ConfigManager::getValue("OTP_TEST_ENABLED")==1?"selected":""?>><?=_lang('Enabled')?></option>
                                        </select>
                                    </div>
                                <?php endif;?>



                                <?php if(ConfigManager::getValue("OTP_ENABLED")==1 && !ConfigManager::getValue("OTP_TEST_ENABLED")): ?>

                                    <hr/>

                                    <div class="form-group">
                                        <label> <?php echo Translate::sprint("Select OTP method"); ?></label>
                                        <select id="OTP_METHOD" name="OTP_METHOD"
                                                class="form-control select2 OTP_METHOD">
                                            <?php foreach (json_decode(ConfigManager::getValue("OTP_METHODS"),JSON_OBJECT_AS_ARRAY) as $key => $method) : ?>
                                                <option value="<?=$method?>" <?=ConfigManager::getValue("OTP_METHOD")==$method?"selected":""?>><?=$method?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>



                                <div class="otp-config-container">

                                    <?php
                                    $selectedMethodConfig = json_decode(ConfigManager::getValue("OTP_CONFIG"),JSON_OBJECT_AS_ARRAY);
                                    $selectedMethodConfig = $selectedMethodConfig[ConfigManager::getValue('OTP_METHOD')];
                                    ?>

                                    <?php foreach ($selectedMethodConfig as $key => $value) : ?>
                                        <?php if($value=="input"): ?>
                                            <div class="form-group">
                                                <label><?=$key?> <span class="text-red">*</span></label>
                                                <input type="text" class="form-control"
                                                       placeholder="<?= Translate::sprint("Enter") ?> ..."
                                                       name="OTP_CONFIG_<?=ConfigManager::getValue('OTP_METHOD')?>_<?=$key?>"
                                                       id="OTP_CONFIG_<?=ConfigManager::getValue('OTP_METHOD')?>_<?=$key?>"
                                                       value="<?= !ModulesChecker::isEnabled("demo")? ConfigManager::getValue('OTP_CONFIG_'.ConfigManager::getValue('OTP_METHOD').'_'.$key):"**Hidden**" ?>">
                                            </div>
                                        <?php elseif($value=="boolean"): ?>
                                            <div class="form-group">
                                                <label><?=$key?> <span class="text-red">*</span></label>
                                                <select class="form-control select2" name="OTP_CONFIG_<?=ConfigManager::getValue('OTP_METHOD')?>_<?=$key?>"
                                                        id="OTP_CONFIG_<?=ConfigManager::getValue('OTP_METHOD')?>_<?=$key?>">
                                                    <option value="1" <?= ConfigManager::getValue('OTP_CONFIG_'.ConfigManager::getValue('OTP_METHOD').'_'.$key)==1?"selected":"" ?>><?=_lang("Enabled")?></option>
                                                    <option value="0" <?= ConfigManager::getValue('OTP_CONFIG_'.ConfigManager::getValue('OTP_METHOD').'_'.$key)==0?"selected":"" ?>><?=_lang("Disabled")?></option>
                                                </select>
                                            </div>
                                        <?php elseif($value=="text"): ?>

                                            <?php if(ConfigManager::getValue('OTP_METHOD')=="firebase" && $key=="Script_JS"): ?>
                                                <div class="form-group">
                                                    <label><?=_lang("Script")?> (Web Version) <span class="text-red">*</span></label><br>

                                                    <code>
                                                          <?=_lang("&lt;script type=&quot;module&quot;&gt;")?>
                                                    <textarea rows="16" class="form-control"
                                                              placeholder="<?= Translate::sprint("Enter") ?> ..."
                                                              name="OTP_CONFIG_<?=ConfigManager::getValue('OTP_METHOD')?>_<?=$key?>"
                                                              id="OTP_CONFIG_<?=ConfigManager::getValue('OTP_METHOD')?>_<?=$key?>"><?= !ModulesChecker::isEnabled("demo")?ConfigManager::getValue('OTP_CONFIG_'.ConfigManager::getValue('OTP_METHOD').'_'.$key):"**Hidden**" ?></textarea>
                                                    <?=_lang("&lt;/script&gt;")?>
                                                    </code>

                                                </div>


                                                <p class="text-blue"><i class="mdi mdi-information"></i> <?=_lang("Insert script without &lt;script&gt; tag")?></p>

                                            <?php else: ?>
                                                <div class="form-group">
                                                    <label><?=$key?> <span class="text-red">*</span></label>
                                                    <textarea rows="16" class="form-control"
                                                              placeholder="<?= Translate::sprint("Enter") ?> ..."
                                                              name="OTP_CONFIG_<?=ConfigManager::getValue('OTP_METHOD')?>_<?=$key?>"
                                                              id="OTP_CONFIG_<?=ConfigManager::getValue('OTP_METHOD')?>_<?=$key?>"><?= !ModulesChecker::isEnabled("demo")?ConfigManager::getValue('OTP_CONFIG_'.ConfigManager::getValue('OTP_METHOD').'_'.$key):"**Hidden**" ?></textarea>
                                                </div>
                                            <?php endif; ?>

                                        <?php endif; ?>
                                    <?php endforeach; ?>

                                </div>


                                <?php endif;?>



                                <?php if(ConfigManager::getValue("OTP_ENABLED")==1): ?>
                                    <!-- reCAPTCHA -->
                                    <hr/>
                                    <div class="form-group">
                                        <label> <?=_lang("reCAPTCHA")?></label>
                                        <select id="OTP_reCAPTCHA" name="OTP_reCAPTCHA"
                                                class="form-control select2 OTP_reCAPTCHA">
                                            <option value="0" <?=ConfigManager::getValue("OTP_reCAPTCHA")==0?"selected":""?>><?=_lang('Disabled')?></option>
                                            <option value="1" <?=ConfigManager::getValue("OTP_reCAPTCHA")==1?"selected":""?>><?=_lang('Enabled')?></option>
                                        </select>
                                    </div>
                                    <?php if(ConfigManager::getValue("OTP_reCAPTCHA")==1): ?>
                                        <div>
                                            <div class="form-group">
                                                <label><?=_lang("Site key")?> <span class="text-red">*</span></label>
                                                <input type="text" class="form-control"
                                                       placeholder="<?= Translate::sprint("Enter") ?> ..."
                                                       name="OTP_reCAPTCHA_SiteKey"
                                                       id="OTP_reCAPTCHA_SiteKey"
                                                       value="<?= !ModulesChecker::isEnabled("demo")?ConfigManager::getValue('OTP_reCAPTCHA_SiteKey'):"**Hidden**" ?>">
                                            </div>

                                            <div class="form-group">
                                                <label><?=_lang("Secret key")?> <span class="text-red">*</span></label>
                                                <input type="text" class="form-control"
                                                       placeholder="<?= Translate::sprint("Enter") ?> ..."
                                                       name="OTP_reCAPTCHA_SecretKey"
                                                       id="OTP_reCAPTCHA_SecretKey"
                                                       value="<?= !ModulesChecker::isEnabled("demo")?ConfigManager::getValue('OTP_reCAPTCHA_SecretKey'):"**Hidden**" ?>">
                                            </div>
                                            <p class="text-blue"><i class="mdi mdi-information"></i> How to get Site key? <a target="_blank" href="https://www.google.com/recaptcha/about/">Look Here</a></p>

                                        </div>
                                        <hr/>
                                    <?php endif; ?>
                                <?php endif;?>


                            </div>

                        </form>
                    </div>
                    <!-- /.box-body -->
                    <div class="box-footer">
                        <button type="button" class="btn  btn-primary btnSave"><span
                                class="glyphicon glyphicon-check"></span><?php echo Translate::sprint("Save", "Save"); ?>
                        </button>
                    </div>
                </div>

            </div>


            <?php if(ConfigManager::getValue("OTP_ENABLED")==1 &&  ConfigManager::getValue("OTP_METHOD")!="firebase"): ?>
            <div class="col-sm-6 test-otp">
                <div class="box box-solid">
                    <div class="box-header with-border">
                        <h3 class="box-title"><b><?php echo Translate::sprint("Test Sending"); ?></b></h3>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <form class="form" role="form">
                            <div class="col-sm-12">

                                <div class="form-group">
                                    <label><?=_lang("Test Sending SMS with ")?> <?=ConfigManager::getValue("OTP_METHOD")?></label>
                                    <input type="text" class="form-control"
                                           placeholder="<?= Translate::sprint("Enter phone number ex: +11...") ?>"
                                           name="OTP_test_phone"
                                           id="OTP_test_phone"
                                           value="" >
                                </div>


                                <div id="test-otp-send-logs">

                                </div>

                            </div>

                        </form>
                    </div>
                    <!-- /.box-body -->
                    <div class="box-footer">
                        <button type="button" class="btn  btn-primary sendTestOtpBtn"><span
                                    class="glyphicon glyphicon-check"></span><?php echo Translate::sprint("Send Test", "Send Test"); ?>
                        </button>

                    </div>
                </div>
                <div class="box box-solid test-received-code hidden">
                    <div class="box-header with-border">
                        <h3 class="box-title"><b><?php echo Translate::sprint("Code checking"); ?></b></h3>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <form class="form" role="form">
                            <div class="col-sm-12">

                                <div class="form-group test-received-code">
                                    <label><?=_lang("Test OTP")?><span class="text-red">*</span></label>
                                    <input type="text" class="form-control"
                                           placeholder="<?= Translate::sprint("Enter received code") ?> ..."
                                           name="OTP_test_code"
                                           id="OTP_test_code"
                                           value="" >
                                </div>
                                <div id="test-otp-verify-logs">

                                </div>
                            </div>

                        </form>
                    </div>
                    <!-- /.box-body -->
                    <div class="box-footer">

                        <button type="button" class="btn  btn-primary verifyTestOtpBtn"><span
                                    class="glyphicon glyphicon-check"></span><?php echo Translate::sprint("Verify Code", "Verify Code"); ?>
                        </button>
                    </div>
                </div>

            </div>
            <?php endif;?>



        </div>

    </section>

</div>



<?php

$script = $this->load->view('user/backend/html/otp_phone/scripts',[],TRUE);
AdminTemplateManager::addScript($script);

?>

