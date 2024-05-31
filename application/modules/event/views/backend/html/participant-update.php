<?php

$participants = $data[Tags::RESULT];

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
                                <b><?= Translate::sprint("Update") ?></b>
                            </div>

                        </div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body  table-responsive">
                        <div class="table-responsive participants">
                            <form>
                                <table id="example2" class="table table-bordered table-hover">
                                    <tbody>
                                    <?php if (!empty($participants)) : ?>
                                        <?php foreach ($participants AS $participant) : ?>
                                            <input type="hidden" id="id" value="<?=$participant['id']?>">
                                            <input type="hidden" id="event_id" value="<?=$participant['event_id']?>">
                                            <tr>
                                                <td style="width: 30%"><label><?=_lang("Booking ID")?></label></td>
                                                <td><?= "#" . str_pad($participant['booking_id'], 6, 0, STR_PAD_LEFT) ?></td>
                                            </tr>
                                            <tr>
                                                <td><label><?=_lang("Event")?></label></td>
                                                <td>
                                                    <strong><?=$participant['event_name']?></strong><br>
                                                    #<?=$participant['event_id']?>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td><label><?=_lang("Client name")?></label></td>
                                                <td><?=$participant['user_name']?></td>
                                            </tr>

                                            <tr>
                                                <td><label><?=_lang("Date")?></label></td>
                                                <td>
                                                    <?=DateSetting::parse($participant['event_date_b'])?> - <?=DateSetting::parse($participant['event_date_e'])?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><label><?=_lang("Status")?></label></td>
                                                <td>
                                                    <select class="select2" name="status" id="status">
                                                        <option value="0" <?=($participant['status']==0)?"selected":""?>><?=_lang("Waiting verification")?></option>
                                                        <option value="1" <?=($participant['status']==1)?"selected":""?>><?=_lang("Confirmed")?></option>
                                                        <option value="-1" <?=($participant['status']==-1)?"selected":""?>><?=_lang("Canceled")?></option>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <label><?=_lang("Attachment")?></label>
                                                    <p class="text-blue"><?=_lang("Upload digital ticket (PDF,Image)")?></p>
                                                </td>
                                                <td>

                                                    <?php
                                                        $attachedFile = $this->uploader_model->getFile($participant['attachements']);
                                                        if($attachedFile!=NULL){
                                                            $e = explode("/",$attachedFile['file']);
                                                            echo "<div class='pt-3 pl-3'><a target='_blank' href='".base_url($attachedFile['file'])."'>"._lang("Download attached file")." (".end(  $e ).")"."</a></div><hr/>";

                                                        }
                                                    ?>
                                                    <?php

                                                    $upload_plug = $this->uploader->plug_files_uploader(array(
                                                        "limit_key"     => "publishFiles",
                                                        "token_key"     => "SzYjES-4555",
                                                        "limit"         => 1,
                                                        "types"         => array(
                                                            "application/pdf",
                                                            "image/jpg",
                                                            "image/jpeg",
                                                            "image/pjpeg",
                                                            "image/png",
                                                            "image/gif",
                                                            "image/bmp",
                                                            "image/tiff",
                                                            "image/webp",
                                                            "image/svg+xml",
                                                        ),
                                                        "template_html"         => "event/plug_file_uploader/html",
                                                        "template_script"       => "event/plug_file_uploader/script",
                                                        "template_style"        => "event/plug_file_uploader/style",
                                                        "script_trigger_callback"        => "file_attache_002",
                                                    ));

                                                    echo $upload_plug['html'];
                                                    AdminTemplateManager::addScript($upload_plug['script']);

                                                    ?>
                                                    <input type="hidden" id="attachement">
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php  else: ?>
                                        <tr>
                                            <td colspan="7">
                                                <div style="text-align: center"><?= Translate::sprint("No participant found") ?></div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                    </tbody>
                                </table>
                            </form>

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
                    <div class="box-footer">
                        <button class="pull-right btn btn-primary" id="savePbtn"><?=_lang("Save")?></button>
                        <?php
                            $attachedFile = $this->uploader_model->getFile($participant['attachements']);
                        ?>
                        <?php if($attachedFile!=NULL):?>
                            <button class="pull-right btn btn-default bg-blue" id="sendTicket"><?=_lang("Send ticket")?></button>
                        <?php endif; ?>
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
$script = $this->load->view('event/backend/html/scripts/participant-update-script', $data, TRUE);
AdminTemplateManager::addScript($script);

?>



