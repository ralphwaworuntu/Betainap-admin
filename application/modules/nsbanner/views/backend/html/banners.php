<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <!-- Message Error -->
            <div class="col-sm-12">
            <?php $this->load->view(AdminPanel::TemplatePath."/include/messages"); ?>
            </div>

        </div>

        <div class="row">
            <div class="col-xs-12">
                <div class="box box-solid">
                    <div class="box-header">

                        <div class="box-title" style="width : 100%;">

                            <div class=" row ">
                                <div class="pull-left col-md-8">
                                    <b><?= Translate::sprint("Sliders") ?></b>
                                </div>
                                <div class="pull-right col-md-4">
                                    <a href="<?= admin_url("nsbanner/add") ?>">
                                        <button type="button" data-toggle="tooltip"
                                                title="<?= Translate::sprint("Add new slider", "") ?> "
                                                class="btn btn-primary btn-sm pull-right"><span
                                                    class="glyphicon glyphicon-plus"></span></button>
                                    </a>


                                </div>
                                <!--  DENY ACCESS TO ROLE "GUEST" -->
                            </div>
                        </div>

                    </div>
                    <!-- /.box-header -->
                    <div class="box-body table-responsive banners">
                        <table id="example2" class="table table-bordered table-hover">
                            <thead>
                            <tr>
                                <th width="20%"><?= Translate::sprint("Image") ?></th>
                                <th width="25%"><?= Translate::sprint("Detail") ?></th>
                                <th width="10%"><?= Translate::sprint("Module") ?></th>
                                <th width="10%"><?= Translate::sprint("Content") ?></th>
                                <th width="10%"><?= Translate::sprint("Status") ?></th>
                                <th width="25%"><?= Translate::sprint("Action") ?></th>
                            </tr>
                            </thead>
                            <tbody>

                        <?php if (!empty($banners)) { ?>

                            <?php foreach ($banners as $banner) : ?>
                                    <tr>
                                        <td>
                                        <?php

                                            try {

                                                $images = ImageManagerUtils::getValidImages($banner['image']);


                                                if (isset($images[0])) {
                                                    echo ImageManagerUtils::imageHTML($images[0]);
                                                } else {
                                                    echo '<img src="' . adminAssets("images/def_logo.png") . '" alt="Product Image">';
                                                }

                                            } catch (Exception $e) {
                                                $e->getMessage();
                                                echo '<img src="' . adminAssets("images/def_logo.png") . '"alt="Product Image">';
                                            }

                                            ?>
                                        </td>
                                        <td>

                                            <?php if(trim($banner['title'])=="" && trim($banner['description'])==""): ?>
                                                <?=_lang("No content")?>
                                            <?php else: ?>
                                                <b><?= $banner['title'] ?></b><br>
                                                <?= $banner['description'] ?>
                                            <?php endif;?>

                                        </td>
                                        <td>
                                            <span class="badge bg-blue"><?= $banner['module'] ?></span>
                                        </td>
                                        <td>
                                            <?= $banner['module_id'] ?>
                                        </td>
                                        <td>
                                        <?php
                                            if ($banner['status'] == 1) {
                                                echo "<span class='badge bg-green'>" . Translate::sprint("Enabled") . "</span>";
                                            } else {
                                                echo "<span class='badge bg-red'>" . Translate::sprint("Disabled") . "</span>";
                                            }
                                            ?>
                                        </td>
                                        <td align="right">


                                        <?php if ($banner['status']==0): ?>
                                                <a href="<?= site_url("ajax/nsbanner/enable?id=" . $banner['id']) ?>">
                                                    <button type="button" data-toggle="tooltip" title="Disable"
                                                            class="btn btn-sm bg-green">
                                                        <i class="mdi mdi-close text-green"></i>&nbsp;&nbsp;<?= Translate::sprint("Enable") ?>
                                                    </button>
                                                </a>
                                        <?php else: ?>
                                                <a href="<?= site_url("ajax/nsbanner/disable?id=" . $banner['id']) ?>">
                                                    <button type="button" data-toggle="tooltip" title="Disable"
                                                            class="btn btn-sm bg-red">
                                                        <i class="mdi mdi-close text-red"></i>&nbsp;&nbsp;<?= Translate::sprint("Disable") ?>
                                                    </button>
                                                </a>
                                        <?php endif; ?>

                                            <a href="<?= admin_url("nsbanner/edit?id=" . $banner['id']) ?>">
                                                <button type="button" data-toggle="tooltip" title="Edit"
                                                        class="btn btn-sm">
                                                    <i class="mdi mdi-pencil"></i>
                                                </button>
                                            </a>


                                            <a data-id="<?=$banner['id']?>" href="#" class="delete">
                                                <button type="button" data-toggle="tooltip" title="Delete"
                                                        class="btn btn-sm">
                                                    <i class="mdi mdi-delete"></i>
                                                </button>
                                            </a>

                                        </td>
                                    </tr>
                            <?php endforeach; ?>

                        <?php } else { ?>
                                <tr>
                                    <td colspan="8" align="center">
                                        <div
                                                style="text-align: center"><?= Translate::sprint("No data found", "") ?></div>
                                    </td>
                                </tr>

                        <?php } ?>
                            </tbody>
                        </table>

                        <div class="row">
                            <div class="col-sm-5">
                                <div class="dataTables_info" id="example2_info" role="status" aria-live="polite">

                                </div>

                            </div>
                            <div class="col-sm-7">
                                <div class="dataTables_paginate paging_simple_numbers" id="example2_paginate">

                                <?php

                                    echo $pagination->links(array(
                                        "status" => intval(RequestInput::get("status")),
                                        "search" => RequestInput::get("search"),
                                        "category_id" => intval(RequestInput::get("category_id")),
                                        "owner_id" => intval(RequestInput::get("owner_id")),
                                    ), empty($status) ? admin_url("store/all_stores") : admin_url("store/stores"));

                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->


                <!-- /.box -->
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->
    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<?php

$script = $this->load->view('nsbanner/backend/scripts/list-script', NULL, TRUE);
AdminTemplateManager::addScript($script);

?>
