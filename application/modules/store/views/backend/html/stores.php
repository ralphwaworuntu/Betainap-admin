<?php


$stores = $data[Tags::RESULT];
$pagination = $data['pagination'];


?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <!-- Message Error -->
            <div class="col-sm-12">
            <?php $this->load->view(AdminPanel::TemplatePath . "/include/messages"); ?>
            </div>

        </div>

        <div class="row">
            <div class="col-xs-12">
                <div class="box box-solid">
                    <div class="box-header">

                        <div class="box-title" style="width : 100%;">

                            <div class=" row ">
                                <div class="pull-left col-md-8">
                                    <b><?= _lang($h1_title) ?></b>
                                </div>
                                <div class="pull-right col-md-4">
                                    <a href="<?= admin_url("store/create") ?>">
                                        <button type="button" data-toggle="tooltip"
                                                title="<?= _lang("Create new store", "") ?> "
                                                class="btn btn-primary btn-sm pull-right"><span
                                                    class="glyphicon glyphicon-plus"></span></button>
                                    </a>

                                    <form method="get"
                                          action="<?php echo $paginate_url ?? admin_url("store/all_stores"); ?>">

                                        <div class="input-group input-group-sm">
                                            <input class="form-control" size="30" name="search" type="text"
                                                   placeholder="<?= _lang("Search") ?>"
                                                   value="<?= Text::output(RequestInput::get("search")) ?>">
                                            <span class="input-group-btn">
                                                <button type="submit" class="btn btn-primary btn-flat"><i
                                                            class="mdi mdi-magnify"></i></button>
                                        </span>
                                        </div>

                                    </form>

                                </div>
                                <!--  DENY ACCESS TO ROLE "GUEST" -->
                            </div>
                        </div>

                    </div>
                    <!-- /.box-header -->
                    <div class="box-body table-responsive">
                        <table id="example2" class="table table-bordered table-hover">
                            <thead>
                            <tr>
                                <th><?= _lang("Image") ?></th>
                                <th><?= _lang("Name") ?></th>
                                <th><?= _lang("Owner") ?></th>
                                <th><?= _lang("Category") ?></th>
                                <th><?= _lang("Status") ?></th>
                                <th><?= _lang("Reviews") ?> </th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>

                        <?php if (!empty($stores)) { ?>

                            <?php foreach ($stores AS $store) { ?>

                                <?php

                                    $token = $this->mUserBrowser->setToken(Text::encrypt($store['id_store']));

                                    ?>
                                    <tr class="store_<?= $token ?>" role="row" class="odd">

                                        <td>
                                            <?php
                                            $image =  ImageManagerUtils::parseFirstImages($store['images'],ImageManagerUtils::IMAGE_SIZE_200);
                                            ?>

                                            <div class="image-container-70 square"
                                                 style="background-image: url('<?=$image?>');background-size: auto 100%;
                                                         background-position: center;">
                                                <img class="direct-chat-img invisible" src="<?=$image?>" alt="Image">
                                            </div>
                                        </td>

                                        <td>
                                            <span style="font-size: 14px"><?= Text::output($store['name']) ?></span>
                                        <?php if ($store['featured'] == 1): ?>
                                                &nbsp;&nbsp;<span class="badge bg-blue-active"
                                                                  style="font-size: 10px;text-transform: uppercase"><i
                                                            class="mdi mdi-check"></i>&nbsp;<?= _lang("Featured") ?></span>
                                        <?php endif; ?><br>
                                            <i class="mdi mdi-map-marker"></i>&nbsp;&nbsp;
                                            <span style="font-size: 11px"><?= Text::output($store['address']) ?></span>


                                        <?php
                                            if (GroupAccess::isGranted('store', MANAGE_STORES)
                                                && ModulesChecker::isEnabled("cf_manager")){
                                                $category = $this->mCategoryModel->getByCategoryByID($store['category_id']);
                                                if($category['cf_id'] == 0){
                                                    echo "<br><a href='".admin_url("store/cf_categories_edit?id=".$store['category_id'])."'><i class='mdi mdi-alert'></i>&nbsp;"._lang("Link with checkout field")."</a>";
                                                }else{
                                                    $cf = $this->mCFManager->getCF($category['cf_id'] );
                                                    if($cf != NULL)
                                                    echo "<br><span class='text-green'><i class='mdi mdi-attachment'></i>&nbsp;".Translate::sprintf("Linked checkout (%s)",array($cf['label']))."</span>";
                                                }
                                            }
                                            ?>

                                        </td>

                                        <td>
                                            <a href="<?= empty($status) ? admin_url("store/all_stores?owner_id=" . $store['user_id']) : admin_url("store/stores?owner_id=" . $store['user_id']) ?>"><span><?= ucfirst($this->mUserModel->getUserNameById($store['user_id'])) ?></span></a>

                                        <?php if (GroupAccess::isGranted("user", MANAGE_USERS)): ?>
                                                &nbsp;&nbsp;<a target="_blank"
                                                               href="<?= admin_url("user/edit?id=" . $store['user_id']) ?>"><i
                                                            class="mdi mdi-open-in-new"></i></a>
                                        <?php endif; ?>

                                        <?php if (GroupAccess::isGranted("user", MANAGE_USERS)): ?>
                                                &nbsp;&nbsp;<a data-toggle="tooltip" title="<?=_lang("Shadowing")?>" href="<?= admin_url("user/shadowing?id=" . $store['user_id']) ?>"><i
                                                            class="mdi mdi-eye-outline"></i></a>
                                        <?php endif; ?>

                                        </td>
                                        <td>
                                            <a href="<?= empty($status) ? admin_url("store/all_stores?category_id=" . $store['category_id']) : admin_url("store/stores?category_id=" . $store['category_id']) ?>"><span><?= _lang(Text::output($this->mStoreModel->getCatName($store['category_id']))) ?></span></a>
                                        </td>
                                        <td>

                                        <?php

                                            if ($store['status'] == 1) {
                                                echo '<span class="badge bg-green">' . _lang("Enabled") . '</span>';
                                            } else if ($store['status'] == 0) {
                                                echo '<span class="badge bg-red">' . _lang("Disabled") . '</span>';
                                            } else if ($store['status'] == -1) {
                                                echo '<span class="badge bg-red">' . _lang("Blocked") . '</span>';
                                            }

                                            ?>


                                        </td>


                                        <td>
                                            <span class="text-yellow"><?=parseToRatingStars(round($store['votes'], 2), "font-size14px")?> (<?=round($store['votes'], 2)?>)</span><br>
                                            <a href="<?= admin_url("store/reviews?id=" . $store['id_store']) ?>">
                                                <?= _lang("Reviews") ?> (<?=$store['nbr_votes']?>)
                                            </a>
                                        </td>

                                        <!--  DENY ACCESS TO ROLE "GUEST" -->

                                        <td align="right">

                                        <?php

                                            if (GroupAccess::isGranted('store', MANAGE_STORES)) {

                                                if ($store['verified'] == 1) {
                                                    if ($store['status'] == 1) {
                                                        echo ' <a href="' . admin_url("store/status?id=" . $store['id_store']) . '"  class="linkAccess" onclick="return false;"><button type="button" data-toggle="tooltip" title="Enabled" class="btn btn-sm" onclick="return false;"><i style="color:#29910d  !important" class="fa fa-check" aria-hidden="true"></i></button></a>';
                                                    } else if ($store['status'] == 0) {
                                                        echo ' <a href="' . admin_url("store/status?id=" . $store['id_store']) . '"  class="linkAccess" onclick="return false;"><button type="button" data-toggle="tooltip" title="Disabled" class="btn btn-sm"  onclick="return false;"><i style="color:#c60f0f !important" class="fa fa-times" aria-hidden="true"></i></button></a> ';
                                                    }
                                                } else {
                                                    echo ' <a href="' . admin_url("store/verify?id=" . $store['id_store']) . '&accept=1" class="linkAccess" onclick="return false;"><button type="button" data-toggle="tooltip"  title="Confirm" class="btn btn-sm bg-green" ><i class="text-white mdi mdi-thumb-up" aria-hidden="true"></i></button></a> ';
                                                    echo ' <a href="' . admin_url("store/verify?id=" . $store['id_store']) . '&accept=0" class="linkAccess" onclick="return false;"><button type="button" data-toggle="tooltip"  title="Decline" class="btn btn-sm  bg-red" ><i class="text-white fa fa-times" aria-hidden="true"></i></button></a> ';
                                                }

                                            }


                                            ?>


                                        <?php if ($store['user_id'] != $this->mUserBrowser->getData("id_user") && GroupAccess::isGranted('store', EDIT_STORE)) : ?>
                                                <a href="<?= admin_url("store/view?id=" . $store['id_store']) ?>">
                                                    <button type="button" data-toggle="tooltip" title="Detail"
                                                            class="btn btn-sm"><i
                                                                class="fa fa-eye"></i></button>
                                                </a>
                                        <?php elseif (GroupAccess::isGranted('store', EDIT_STORE)) : ?>
                                                <a href="<?= admin_url("store/edit?id=" . $store['id_store']) ?>">
                                                    <button type="button" data-toggle="tooltip" title="<?=_lang("Edit")?>"
                                                            class="btn btn-sm"><span
                                                                class="glyphicon glyphicon-edit"></span></button>
                                                </a>
                                        <?php endif; ?>


                                        <?php if (ModulesChecker::isEnabled("service") && GroupAccess::isGranted('store', EDIT_STORE)): ?>
                                                <a href="<?= $store['user_id']==SessionManager::getData('id_user')?admin_url("store/edit?id=" . $store['id_store']."&tab=service"):admin_url("store/view?id=" . $store['id_store']."&tab=service") ?>">
                                                    <button type="button" data-toggle="tooltip" title="Update"
                                                            class="btn btn-sm"><span
                                                                class="glyphicon glyphicon-list"></span>&nbsp;&nbsp;<?=_lang("Services")?></button>
                                                </a>
                                        <?php endif; ?>


                                        <?php if (GroupAccess::isGranted('store', DELETE_STORE)): ?>
                                                <a href="#" class="delete" data-id="<?= $store['id_store'] ?>">
                                                    <button type="button" class="btn btn-sm"><span
                                                                class="glyphicon glyphicon-trash"></span></button>
                                                </a>
                                        <?php endif; ?>

                                        </td>

                                        <!--  -->


                                    </tr>
                            <?php } ?>


                        <?php } else { ?>
                                <tr>
                                    <td colspan="8" align="center">
                                        <div
                                                style="text-align: center"><?= _lang("No data found", "") ?></div>
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
                                    ), current_url());

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

$script = $this->load->view('store/backend/html/scripts/stores-script', NULL, TRUE);
AdminTemplateManager::addScript($script);

?>
