<div class="content-wrapper">
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <!-- Message Error -->
            <div class="col-sm-12">
                <?php $this->load->view(AdminPanel::TemplatePath."/include/messages"); ?>
            </div>
        </div>
        <div class="box box-solid store-service">
            <div class="box-header no-border" style="min-height: 54px;">
                <div class="box-title" style="width : 100%;">
                    <div class="title-header ">
                        <b><?= Translate::sprint("Services/Menu") ?> (<?=$store['name']?>)</b>
                    </div>
                </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body  table-responsive">
                <div class="row">
                    <div class="col-sm-3">
                        <form id="form-stores-selector" action="<?=admin_url("service/services")?>">
                            <select class="select2" id="storesSelector">
                                <option><?=_lang("-- Select")?></option>
                                <?php foreach ($myStores[Tags::RESULT] as $st):?>
                                    <option value="<?=$st['id_store']?>" <?=RequestInput::get('store_id')==$st['id_store']?"selected":""?>><?=$st['name']?></option>
                                <?php endforeach;?>
                            </select>
                        </form>
                    </div>
                    <div class="col-sm-3"></div>
                    <div class="col-sm-6">
                        <div class="pull-right">
                            <button type="button" class="btn btn-primary create-new-grp-service">
                                <i class="mdi mdi-plus"></i>
                                <?=_lang("Add Service/Menu Group")?>
                            </button>
                        </div>
                    </div>

                    <hr class="mt-3 mb-3"/>

                    <div class="col-sm-12 ">
                        <?php
                            $service = $this->service->plugV2(array(
                                'id' => $store['id_store'],
                                'title' => _lang("Services"),
                            ));
                            echo $service['html'];
                            AdminTemplateManager::addScript($service['script']);
                        ?>
                    </div>
                </div>
            </div>
            <!-- /.box-body -->
        </div>
        <!-- /.row -->
    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<?php

$script = $this->load->view('service/backend/scripts/services-script',NULL,TRUE);
AdminTemplateManager::addScript($script);

?>



