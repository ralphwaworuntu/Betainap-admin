<div class="content-wrapper">
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <!-- Message Error -->
            <div class="col-sm-12">
                <?php $this->load->view(AdminPanel::TemplatePath."/include/messages"); ?>
            </div>
        </div>
        <div class="box box-solid">
            <div class="box-header no-border" style="min-height: 54px;">
                <div class="box-title" style="width : 100%;">
                    <div class="title-header ">
                        <b><?= Translate::sprint("Services/Menu") ?>)</b>
                    </div>
                </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body  table-responsive">
                <div class="row">
                    <div class="col-sm-6">
                       <form id="form-stores-selector" action="<?=admin_url("service/services")?>">
                           <select class="select2" id="storesSelector">
                               <option><?=_lang("-- Select")?></option>
                               <?php foreach ($myStores[Tags::RESULT] as $store):?>
                               <option value="<?=$store['id_store']?>"><?=$store['name']?></option>
                               <?php endforeach;?>
                           </select>
                       </form>
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

