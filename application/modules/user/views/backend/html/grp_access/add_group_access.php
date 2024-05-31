<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <section class="content">

        <div class="row">
            <!-- Message Error -->
            <div class="col-sm-12">
            <?php $this->load->view(AdminPanel::TemplatePath."/include/messages"); ?>
            </div>

        </div>


        <div class="row">
            <div class="col-sm-12">
                <div class="box box-solid">
                    <div class="box-header with-border">
                        <h3 class="box-title"><b> <?= Translate::sprint("Group Accesses") ?>  </b></h3>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <table class="table table-responsive">
                            <tr>
                                <th><?= Translate::sprint("Name") ?></th>
                                <th width="60%"></th>
                                <th></th>
                            </tr>
                        <?php if (count($group_accesses) > 0):
                                foreach ($group_accesses as $grp): ?>
                                    <tr>
                                        <td><?= $grp['name'] ?></td>
                                        <td>
                                            <?php if($grp['manager']==1): ?>
                                                <span class="badge bg-green badge-success"><?=GroupAccess::ACCESSES[$grp['manager']]?></span>
                                            <?php elseif($grp['manager']==2): ?>
                                                <span class="badge bg-blue badge-success"><?=GroupAccess::ACCESSES[$grp['manager']]?></span>
                                            <?php elseif($grp['manager']==3): ?>
                                                <span class="badge bg-red badge-success"><?=GroupAccess::ACCESSES[$grp['manager']]?></span>
                                            <?php endif; ?>

                                        </td>
                                        <td align="right">
                                        <?php if ($grp['editable'] == 1 OR ENVIRONMENT == "development"): ?>
                                                <a class="font-size16px" data-toggle="tooltip" title="<?=_lang("Access to the group will be removed only if no account is linked")?>"
                                                   href="<?= admin_url("user/delete_group_access") ?>?id=<?= $grp['id'] ?>"><i
                                                            class="mdi mdi-delete"></i></a>
                                                <a class="font-size16px"
                                                   href="<?= admin_url("user/edit_group_access") ?>?id=<?= $grp['id'] ?>"><i
                                                            class="mdi mdi-square-edit-outline"></i></a>
                                        <?php else: ?>
                                                <a class="font-size16px text-gray"><i class="mdi mdi-delete"></i></a>
                                                <a class="font-size16px text-gray"><i
                                                            class="mdi mdi-square-edit-outline"></i></a>
                                        <?php endif; ?>
                                        </td>
                                    </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="2"><?= Translate::sprint("No Group Added") ?></td>
                                </tr>
                        <?php endif; ?>

                        </table>
                    </div>

                </div>

            </div>
            <div class="col-sm-12">

                <div class="box box-solid">
                    <div class="box-header with-border">
                        <h3 class="box-title"><b> <?= Translate::sprint("Add New Group Access", "") ?>  </b></h3>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <form class="items" id="form" role="form">

                            <div class="col-sm-12">
                                <table class="table table-responsive">
                                    <tr>
                                        <th width="20%"><?= Translate::sprint("Module Name") ?></th>
                                        <th width="80%"><?= Translate::sprint("Options") ?></th>
                                    </tr>
                                <?php if (count($actions) > 0): ?>
                                    <?php foreach ($actions as $key => $action): ?>
                                            <tr class="item_<?= $key ?>">
                                                <td>
                                                    <label id="module_action_<?= $key ?>" data-key="<?= $key ?>">
                                                        <input type="checkbox" class="minimal">
                                                        &nbsp;&nbsp;<strong><?= Translate::sprint($key) ?></strong>
                                                        &nbsp;&nbsp;
                                                    </label>
                                                </td>
                                                <td>
                                                <?php foreach ($action as $value): ?>
                                                        <label id="<?= $key ?>_<?= $value ?>"
                                                               class="option options_<?= $key ?> option_<?= $key ?> option_<?= $value ?>">
                                                            <input type="checkbox" class="minimal">
                                                            &nbsp;&nbsp;<strong><?= strtoupper(Translate::sprint("grpac_" . $value)) ?></strong>
                                                            &nbsp;&nbsp;
                                                        </label>
                                                <?php endforeach; ?>
                                                </td>
                                            </tr>
                                    <?php endforeach; ?>

                                <?php else: ?>
                                        <tr>
                                            <td colspan="2"><?= Translate::sprint('No Action Added') ?></td>
                                        </tr>
                                <?php endif; ?>
                                </table>

                                <div class="form-group">
                                    <label><?= Translate::sprint("Name") ?></label>
                                    <input class="form-control" id="name"
                                           placeholder="<?= Translate::sprint("Enter name") ?>">
                                </div>

                                <div class="form-group">
                                    <label><?= Translate::sprint("Type") ?></label>
                                    <select class="select2" name="manager" id="manager">
                                        <?php foreach (GroupAccess::ACCESSES as $k => $val):?>
                                            <option value="<?=$k?>"><?=$val?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                            </div>

                        </form>
                    </div>
                    <!-- /.box-body -->
                    <div class="box-footer">
                        <button class="btn btn-primary"
                                id="add_grp_access"><?= Translate::sprint('Add Group Access') ?></button>
                    </div>
                </div>

            </div>
        </div>
    </section>

</div>


<?php

$data['actions'] = $actions;
$script = $this->load->view("user/backend/html/grp_access/scripts/add_group_access_script", $data, TRUE);
AdminTemplateManager::addScript($script);

?>