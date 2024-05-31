<?php

$events = $data[Tags::RESULT];
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
                <div class="box  box-solid">
                    <div class="box-header">
                        <div class="box-title" style="width : 100%;">
                            <div class=" row ">
                                <div class="pull-left col-md-8">
                                    <b><?= Translate::sprint("Events") ?></b>
                                </div>
                                <div class="pull-right col-md-4">
                                    <a href="<?= admin_url("event/create") ?>">
                                        <button type="button" title="<?= Translate::sprint("Create new store", "") ?>"
                                                class="btn btn-primary btn-sm pull-right"><span
                                                    class="glyphicon glyphicon-plus"></span></button>
                                    </a>
                                    <form method="get"
                                          action="<?php echo empty($status) ? admin_url("event/all_events") : admin_url("event/events"); ?>">
                                        <div class="input-group input-group-sm">
                                            <input class="form-control" size="30" name="search" type="text"
                                                   placeholder="<?= Translate::sprint("Search") ?>"
                                                   value="<?= Text::output(RequestInput::get("search")) ?>">
                                            <span class="input-group-btn">
                                                <button type="submit" class="btn btn-primary btn-flat"><i class="mdi mdi-magnify"></i></button>
                                            </span>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body  table-responsive">
                        <div class="table-responsive">
                            <table id="example2" class="table table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th width="50px"><?= Translate::sprint("Image") ?></th>
                                    <th><?= Translate::sprint("Name") ?></th>
                                    <th><?= Translate::sprint("Owner") ?></th>
                                    <th><?= Translate::sprint("Date") ?></th>
                                    <th><?= Translate::sprint("Status") ?></th>
                                    <?php if (GroupAccess::isGranted('event', MANAGE_EVENTS_PARTICIPANTS)) : ?>
                                        <th><?= Translate::sprint("Participants") ?></th>
                                    <?php endif; ?>
                                    <th><?= Translate::sprint("Price") ?></th>
                                    <th><?= Translate::sprint("Wishlist") ?></th>
                                    <th><?= Translate::sprint("Action") ?></th>
                                </tr>
                                </thead>
                                <tbody>

                                <?php if (!empty($events)) : ?>
                                    <?php foreach ($events as $event): ?>
                                        <?php $token = $this->mUserBrowser->setToken(Text::encrypt($event['id_event'])); ?>
                                        <tr id="offre" class="store_<?= $token ?>" role="row" class="odd">
                                            <td>
                                                <?php
                                                $image = ImageManagerUtils::parseFirstImages($event['images'], ImageManagerUtils::IMAGE_SIZE_200);
                                                ?>

                                                <div class="image-container-70 square"
                                                     style="background-image: url('<?= $image ?>');background-size: auto 100%;
                                                             background-position: center;">
                                                    <img class="direct-chat-img invisible" src="<?= $image ?>"
                                                         alt="Image">
                                                </div>
                                            </td>

                                            <td>
                                                <span style="font-size: 14px"><?= Text::output($event['name']) ?></span>
                                                <?php if ($event['featured'] == 1): ?>
                                                    &nbsp;&nbsp;<span class="badge bg-blue-active"
                                                                      style="font-size: 10px;text-transform: uppercase"><i
                                                                class="mdi mdi-check"></i>&nbsp;<?= Translate::sprint("Featured") ?></span>
                                                <?php endif; ?><br>
                                                <span class="font-size12px"><i class="mdi mdi-map-marker"></i>&nbsp;&nbsp;<?= Text::output($event['store_name']) ?></span>

                                                <?php if($event['cf_id']==0): ?>
                                                <br><small class="pt-2 text-red"><i class="mdi mdi-information"></i> <?=_lang("This event not available for participation")?></small>
                                                <?php elseif($event['cf_id']>0 && !empty($event['cf']) && $event['status'] == 1): ?>
                                                    <br><span class="pt-2 text-green"><i class="mdi mdi-check-circle"></i> <?=_lang("Ready for checkout")?></span>
                                                <?php endif; ?>
                                            </td>

                                            <td>

                                                <a href="<?= empty($status) ? admin_url("store/all_stores?owner_id=" . $event['user_id']) : admin_url("store/stores?owner_id=" . $event['user_id']) ?>"><u><?= ucfirst($this->mUserModel->getUserNameById($event['user_id'])) ?></u></a>
                                                <?php if (GroupAccess::isGranted("user", MANAGE_USERS)): ?>
                                                    &nbsp;&nbsp;<a target="_blank"
                                                                   href="<?= admin_url("user/edit?id=" . $event['user_id']) ?>"><i
                                                                class="mdi mdi-open-in-new"></i></a>
                                                <?php endif; ?>

                                                <?php if (GroupAccess::isGranted("user", MANAGE_USERS)): ?>
                                                    &nbsp;&nbsp;<a data-toggle="tooltip"
                                                                   title="<?= _lang("Shadowing") ?>"
                                                                   href="<?= admin_url("user/shadowing?id=" . $event['user_id']) ?>"><i
                                                                class="mdi mdi-eye-outline"></i></a>
                                                <?php endif; ?>

                                            </td>
                                            <td>
                                                <span style="font-size: 12px;">
                                                <?= MyDateUtils::display($event['date_b']) . " - " . MyDateUtils::display($event['date_e']) ?>
                                                </span>

                                            </td>
                                            <td>
                                                <?php

                                                if ($event['status'] == 0)
                                                    echo '<span class="badge bg-red"><i class="mdi mdi-history"></i> &nbsp;' . Translate::sprint("Disabled") . '&nbsp;&nbsp;</span>';
                                                else if ($event['status'] == 1) {

                                                    $current = date("Y-m-d H:i:s", time());
                                                    $diff_millseconds_start = strtotime($event['date_b']) - strtotime($current);
                                                    $diff_millseconds_end = strtotime($event['date_e']) - strtotime($current);

                                                    if ($diff_millseconds_start > 0) {
                                                        echo '<span class="badge bg-green"><i class="mdi mdi-history"></i> &nbsp;' . Translate::sprint("Published", "") . '&nbsp;&nbsp;</span>';
                                                    } else if ($diff_millseconds_start < 0 && $diff_millseconds_end > 0) {
                                                        echo '<span class="badge bg-green"><i class="mdi mdi-check"></i> &nbsp;' . Translate::sprint("Started", "") . '&nbsp;&nbsp;</span>';
                                                    } else {
                                                        echo '<span class="badge bg-red"><i class="mdi mdi-close"></i> &nbsp;' . Translate::sprint("Finished", "") . '&nbsp;&nbsp;</span>';
                                                    }
                                                }

                                                ?>
                                            </td>

                                            <?php if (GroupAccess::isGranted('event', MANAGE_EVENTS_PARTICIPANTS)) : ?>
                                                <td>
                                                    <?php $participants = $this->mEventModel->countParticipants($event['id_event']); ?>
                                                    <a href="<?= admin_url("event/participants?event_id=" . $event['id_event']) ?>"><i
                                                                class="mdi mdi-account-multiple-outline"></i>&nbsp;<?= $participants ?>
                                                    </a>
                                                </td>
                                            <?php endif; ?>


                                            <td>
                                                <?php if ($event['price'] > 0): ?>
                                                    <strong class="text-red"><?= Currency::parseCurrencyFormat($event['price'], ConfigManager::getValue('DEFAULT_CURRENCY')) ?></strong>
                                                <?php else: ?>
                                                    <strong class="text-red"><?= _lang("Free") ?></strong>
                                                <?php endif; ?>
                                            </td>

                                            <td>
                                            <span data-toggle="tooltip"
                                                  title="<?= $event['wishlist'] ?> peoples have liked this event on bookmark"
                                                  class="badge bg-red">
                                                <i class="mdi mdi-heart"></i>&nbsp;&nbsp;<?= $event['wishlist'] ?>
                                            </span>
                                            </td>
                                            <td>

                                                <?php if ($event['status'] == 1 && GroupAccess::isGranted('event', MANAGE_EVENTS)) : ?>

                                                    <a href="<?= site_url("ajax/event/changeStatus?id=" . $event['id_event']) ?>"
                                                       class="linkAccess" onclick="return false;">
                                                        <button type="button" class="btn btn-sm">
                                                            <i class="color-green text-green fa fa-check"></i>
                                                        </button>
                                                    </a>

                                                <?php elseif ($event['status'] == 0 && GroupAccess::isGranted('event', MANAGE_EVENTS)) : ?>

                                                    <?php if ($event['verified'] == 1): ?>
                                                        <a href="<?= site_url("ajax/event/changeStatus?id=" . $event['id_event']) ?>"
                                                           class="linkAccess" onclick="return false;">
                                                            <button type="button" class="btn btn-sm">
                                                                <i class="color-red text-red fa fa-close"></i>
                                                            </button>

                                                        </a>
                                                    <?php else: ?>

                                                        <?php
                                                        echo ' <a href="' . admin_url("event/verify?status=1&id=" . $event['id_event']) . '&accept=1" class="linkAccess" onclick="return false;" ><button type="button"  data-toggle="tooltip" title="Accept" class="btn btn-sm bg-green" ><i class="text-white mdi mdi-thumb-up" aria-hidden="true"></i></button></a> ';
                                                        echo ' <a href="' . admin_url("event/verify?status=-1&id=" . $event['id_event']) . '&accept=0" class="linkAccess" onclick="return false;"><button type="button"  data-toggle="tooltip" title="Decline" class="btn btn-sm  bg-red" ><i class="text-white fa fa-times" aria-hidden="true"></i></button></a>';
                                                        ?>

                                                    <?php endif; ?>

                                                <?php endif; ?>

                                                <?php if (GroupAccess::isGranted('event', EDIT_EVENT)
                                                    && SessionManager::getData("id_user") == $event['user_id']): ?>
                                                    <a href="<?= admin_url("event/edit?id=" . $event['id_event']) ?>">
                                                        <button type="button" title="detail" class="btn btn-sm"><i
                                                                    class="glyphicon glyphicon-edit"></i></button>
                                                    </a>
                                                <?php elseif (GroupAccess::isGranted('event', MANAGE_EVENTS)): ?>
                                                    <a href="<?= admin_url("event/view?id=" . $event['id_event']) ?>">
                                                        <button type="button" title="detail" class="btn btn-sm"><i
                                                                    class="glyphicon glyphicon-eye-open"></i></button>
                                                    </a>
                                                <?php endif ?>

                                                <?php if ($event['status'] == 1 || GroupAccess::isGranted('event', MANAGE_EVENTS)) : ?>

                                                    <a href="#" data-toggle="modal"
                                                       data-target="#modal-default-<?= md5($event['id_event']) ?>">
                                                        <button type="button" data-toggle="tooltip" title="Delete"
                                                                class="btn btn-sm"><span
                                                                    class="glyphicon glyphicon-trash"></span></button>
                                                    </a>
                                                    <div class="modal fade"
                                                         id="modal-default-<?= md5($event['id_event']) ?>">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <button type="button" class="close"
                                                                            data-dismiss="modal" aria-label="Close">
                                                                        <span aria-hidden="true">&times;</span></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <div class="row">
                                                                        <div style="text-align: center">
                                                                            <p class="text-red"><?= Translate::sprint("Are you sure you want to delete") ?> <?= $event['name'] . " ?" ?></p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button"
                                                                            class="btn btn-default pull-left"
                                                                            data-dismiss="modal"><?= Translate::sprint("Cancel", "Cancel") ?></button>
                                                                    <button type="button" id="_delete"
                                                                            data="<?= ($event['id_event']) ?>"
                                                                            class="btn btn-flat btn-primary"><?= Translate::sprint("Delete", "Delete") ?></button>
                                                                </div>
                                                            </div>

                                                            <!-- /.modal-content -->
                                                        </div>
                                                        <!-- /.modal-dialog -->
                                                    </div>
                                                <?php endif; ?>


                                            </td>


                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7">
                                            <div
                                                    style="text-align: center"><?= Translate::sprint("No data found", "") ?></div>
                                        </td>
                                    </tr>

                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="row">
                            <div class="col-sm-5">
                                <div class="dataTables_info" id="example2_info" role="status" aria-live="polite">

                                </div>

                            </div>
                            <div class="col-sm-7">
                                <div class="dataTables_paginate paging_simple_numbers" id="example2_paginate">

                                    <?php

                                    echo $pagination->links(array(
                                        "search" => RequestInput::get("search"),
                                        "store_id" => intval(RequestInput::get("store_id")),
                                        "status" => intval(RequestInput::get("status"))
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


<?php if (GroupAccess::isGranted('event', DELETE_EVENT)): ?>
    <!-- jQuery 2.1.4 -->
    <script src="<?= adminAssets("plugins/jQuery/jQuery-2.1.4.min.js") ?>"></script>
    <!-- page script -->

    <script>


        $("div #_delete").on('click', function () {

            var selector = $(this);
            var id = $(this).attr("data");

            $.ajax({
                url: "<?=  site_url("ajax/event/delete")?>",
                data: {"id": id},
                dataType: 'json',
                type: 'POST',
                beforeSend: function (xhr) {
                    selector.attr("disabled", true);
                }, error: function (request, status, error) {
                    alert(request.responseText);
                    selector.attr("disabled", false);
                    console.log(request);
                },
                success: function (data, textStatus, jqXHR) {

                    console.log(data);

                    selector.attr("disabled", false);
                    if (data.success === 1) {
                        document.location.reload();
                    } else if (data.success === 0) {
                        var errorMsg = "";
                        for (var key in data.errors) {
                            errorMsg = errorMsg + data.errors[key] + "\n";
                        }
                        if (errorMsg !== "") {
                            alert(errorMsg);
                        }
                    }
                }
            });

            return false;
        });


    </script>
<?php endif; ?>


