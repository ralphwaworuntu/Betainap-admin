<?php

$participants = $data[Tags::RESULT];

$pagination = $data['pagination'];

?>
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

                <div class="box  box-solid">
                    <div class="box-header" style="min-height: 54px;">
                        <div class="box-title" style="width : 100%;">
                            <div class="title-header ">
                                <b><?= Translate::sprint("My tickets") ?></b>
                                <div class="pull-right">
                                    <button class="btn btn-flat bg-blue push_email hidden"><i class="mdi mdi-email-variant"></i>&nbsp;&nbsp;<?=Translate::sprintf("Remind <span id=\"estimated_users\">%s</span> user(s) via email",array(0))?></button>
                                    <button class="btn btn-flat bg-orange push_campaign hidden"><i class="mdi mdi-bullseye"></i>&nbsp;&nbsp;<?=Translate::sprintf("Push Campaign to <span id=\"estimated_guests\">%s</span> user(s)",array(0))?></button>
                                </div>
                            </div>

                        </div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body  table-responsive">
                        <div class="table-responsive participants">
                            <table id="example2" class="table table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th><strong>#<?= Translate::sprint("ID") ?></strong></th>
                                    <th><strong>#<?= Translate::sprint("Booking ID") ?></strong></th>
                                    <th><?= Translate::sprint("User") ?></th>
                                    <th><?= Translate::sprint("Label") ?></th>
                                    <th><?= Translate::sprint("Date") ?></th>
                                    <th><?= Translate::sprint("Status") ?></th>
                                    <th>
                                        <?php
                                        $limit = intval(RequestInput::get('limit'));
                                        ?>
                                        <select class="select2" id="limit">
                                            <option value="100" <?=$limit==100?"selected":""?>>100</option>
                                            <option value="200" <?=$limit==200?"selected":""?>>200</option>
                                            <option value="300" <?=$limit==300?"selected":""?>>300</option>
                                            <option value="400" <?=$limit==400?"selected":""?>>400</option>
                                            <option value="500" <?=$limit==500?"selected":""?>>500</option>
                                            <option value="600" <?=$limit==600?"selected":""?>>600</option>
                                        </select>
                                    </th>
                                </tr>
                                </thead>
                                <tbody>

                                <?php if (!empty($participants)) : ?>
                                    <?php foreach ($participants AS $participant) : ?>
                                        <tr>
                                            <?php
                                            $attachedFile = $this->uploader_model->getFile($participant['attachements']);
                                            ?>
                                            <td><strong>#<?=$participant['id']?></strong></td>
                                            <td><strong><?= "#" . str_pad($participant['booking_id'], 6, 0, STR_PAD_LEFT) ?> </b></strong></td>
                                            <td><b><?=$participant['user_name']?></b><br>
                                                <?=hideEmailAddress($participant['user_email'])?>
                                            </td>
                                            <td>
                                                <strong><?=$participant['event_name']?></strong><br>
                                                <?=DateSetting::parse($participant['event_date_b'])?> - <?=DateSetting::parse($participant['event_date_e'])?>
                                            </td>
                                            <td><?=DateSetting::parse($participant['created_at'])?></td>
                                            <td>
                                                <?php if($participant['status']==0): ?>
                                                    <span class="badge bg-yellow"><?=_lang("New")?></span>
                                                <?php elseif($participant['status']==1): ?>
                                                    <span class="badge bg-success"><?=_lang("Confirmed")?></span>
                                                <?php elseif($participant['status']==-1): ?>
                                                    <span class="badge bg-danger"><?=_lang("Canceled")?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if($attachedFile!=NULL):?>
                                                    &nbsp;<a target="_blank" href="<?=base_url($attachedFile['file'])?>"><i class="mdi mdi-download"></i> <?=_lang("Download ticket")?></a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php  else: ?>
                                    <tr>
                                        <td colspan="7">
                                            <div style="text-align: center"><?= Translate::sprint("No tickets found") ?></div>
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
                                        "event_id" => intval(RequestInput::get("event_id")),
                                        "limit" => intval(RequestInput::get("limit")),
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

$data['event_id'] = intval(RequestInput::get('event_id'));

$script = $this->load->view('event/backend/html/scripts/participants-script', $data, TRUE);
AdminTemplateManager::addScript($script);

?>
