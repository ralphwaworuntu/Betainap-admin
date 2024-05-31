<?php


$banks = $result[Tags::RESULT];


?>



<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <section class="content">
        <div class="row">
            <!-- Message Error -->
            <div class="col-sm-12">
                <?php $this->load->view(AdminPanel::TemplatePath."/include/messages");?>
            </div>
        </div>
        <div class=" banks">
            <div class="box box-solid form-banks">

                <div class="box-header">
                    <div class="box-title">
                        <strong><?=Translate::sprint("Banks")?></strong>
                    </div>

                    <div class="pull-right">
                        <?php if(SessionManager::getValue("Mobile_Auth_Management",false)): ?>
                            <a href="<?=admin_url("digital_wallet/Mobile_addBank")?>">
                                <button type="button" data-toggle="tooltip" title="" class="btn btn-primary btn-sm pull-right" data-original-title="<?=_lang("Add new bank")?>"><span class="glyphicon glyphicon-plus"></span></button>
                            </a>
                        <?php else: ?>
                            <a href="<?=admin_url("digital_wallet/addBank")?>">
                                <button type="button" data-toggle="tooltip" title="" class="btn btn-primary btn-sm pull-right" data-original-title="<?=_lang("Add new bank")?>"><span class="glyphicon glyphicon-plus"></span></button>
                            </a>
                        <?php endif; ?>

                    </div>
                </div>
                <!-- /.box-header -->
                <div class="box-body table-responsive">
                    <table id="example2" class="table table-bordered table-hover">
                        <thead>
                        <tr>
                            <!--    <th>ID</th>-->
                            <th width="20%"><?=Translate::sprint("Bank Name","")?></th>
                            <th width="20%"><?=Translate::sprint("Bank Account / Bank Number")?></th>
                            <th width="20%"><?=Translate::sprint("Bank holder Name")?></th>
                            <th width="20%"><?=Translate::sprint("Country")?></th>
                            <th width="20%"><?=Translate::sprint("Action","")?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php  if(count($banks)>0): ?>

                            <?php foreach ($banks as $value): ?>
                                <tr>
                                    <td><?=$value['name']?></td>
                                    <td><?=$value['account_number']?></td>
                                    <td><?=$value['holder_name']?></td>
                                    <td><?=$value['country']?></td>
                                    <td>

                                        <?php if(SessionManager::getValue("Mobile_Auth_Management",false)): ?>
                                            <a class="btn btn-default" href="<?=admin_url("digital_wallet/Mobile_editBank?id=".$value['id'])?>"><i class="mdi mdi-pencil"></i></a>
                                        <?php else: ?>
                                            <a class="btn btn-default" href="<?=admin_url("digital_wallet/editBank?id=".$value['id'])?>"><i class="mdi mdi-pencil"></i></a>
                                        <?php endif; ?>


                                        &nbsp;&nbsp;
                                        <a class="btn btn-default linkAccess" href="<?=site_url("ajax/digital_wallet/deleteBank?id=".$value['id'])?>"><i class="mdi mdi-delete"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                        <?php else: ?>
                            <tr>
                                <td colspan="5" align="center"><?=_lang("No bank add")?></td>
                            </tr>
                        <?php endif; ?>

                        </tbody>
                    </table>

                    <div class="row">
                        <div class="col-sm-12 pull-right">
                            <div class="dataTables_paginate paging_simple_numbers" id="example2_paginate">

                                <?php


                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box -->
        </div>
    </section>
</div>

