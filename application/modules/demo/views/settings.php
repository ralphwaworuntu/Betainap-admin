
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content">
            <div class="row">
                <!-- Message Error -->
                <div class="col-sm-12">
                <?php $this->load->view(AdminPanel::TemplatePath."/include/messages"); ?>
                </div>

            </div>

            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title"><b> <?=Translate::sprint("Demo Settings")?> </b></h3>
                </div>

                <form>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Default demo user</label>
                                    <select class="select2 form-control" id="select_default_demo_user">
                                    <?php foreach ($default_user as $user): ?>
                                            <option value="<?=$user->id?>" selected><?=$user->name?></option>
                                    <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Demo users</label>
                                    <select class="select2 form-control" id="select_default_demo_users" multiple="multiple">
                                    <?php foreach ($default_users as $user): ?>
                                            <option value="<?=$user->id?>" selected><?=$user->name?></option>
                                    <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <div class="form-group">
                            <button type="button" class="btn  btn-primary" id="btnSave" > <span class="glyphicon glyphicon-check"></span> <?=_lang("Save"); ?> </button>
                        </div>
                    </div>
                </form>

            </div>
        </section>

    </div>

<?php

    $script = $this->load->view('demo/script', NULL, TRUE);
    AdminTemplateManager::addScript($script);

    ?>

