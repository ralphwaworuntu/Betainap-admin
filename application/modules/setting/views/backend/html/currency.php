<?php

$timezones =  DateTimeZone::listIdentifiers(DateTimeZone::ALL);
$languages =  Translate::getLangsCodes();

$formats = array("X0,000.00","0,000.00X","X 0,000.00","0,000.00 X","0,000.00","X0,000.00 XX","XX0,000.00","0,000.00XX");

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

        <div class="row">

            <div class="col-sm-6">

                <div class="box box-solid">
                    <div class="box-header with-border">
                        <h3 class="box-title"><b> <?php echo Translate::sprint("Add_new_currency","Add new currency"); ?> </b></h3>
                    </div>

                    <div class="box-body">

                            <form id="form" role="form">

                                <div class="form-group">
                                    <label><?=Translate::sprint("Name")?></label>
                                    <input type="text" class="form-control" placeholder="Name ex: US Dollar" id="name_currency">
                                </div>
                                <div class="form-group">
                                    <label><?=Translate::sprint("Code")?></label>
                                    <input type="text" class="form-control" placeholder="Code ex: EUR,USD ..." id="code_currency">
                                </div>

                                <div class="form-group">
                                    <label><?=Translate::sprint("Symbol")?></label>
                                    <input type="text" class="form-control" placeholder="Symbol ex: $,€ ..." id="symbol_currency">
                                </div>
                                <div class="form-group hidden">
                                    <label><?=Translate::sprint("Rate")?></label>
                                    <input type="text" class="form-control"  placeholder="1.0000" id="rate_currency">
                                </div>

                                <div class="form-group">
                                    <label><?=Translate::sprint("Format")?></label>
                                            <select id="CURRENCY_FORMAT" name="format" class="form-control select2 CURRENCY_FORMAT">
                                                <option value='1'>X0,000.00</option>
                                                <option value='2'>0,000.00X</option>
                                                <option value='3'>X 0,000.00</option>
                                                <option value='4'>0,000.00 X</option>
                                                <option value='5'>0,000.00</option>
                                                <option value='6'>X0,000.00 XX</option>
                                                <option value='7'>XX0,000.00</option>
                                                <option value='8'>0,000.00XX</option>
                                            </select>
                                 </div>


                                <div class="form-group">
                                    <label><?= _lang("Format Decimals") ?> <i class="text-danger">*</i></label>
                                    <input class="form-control" id="cfd" type="text"
                                           placeholder="<?= _lang("Format Decimals") ?>" value="2" required="">
                                </div>

                                <div class="form-group">
                                    <label><?= _lang("Decimal Point") ?> <i class="text-danger">*</i></label>
                                    <input class="form-control" id="cdp" type="text"
                                           placeholder="<?= _lang("Decimal Point") ?>" value="," required="">
                                </div>

                                <div class="form-group">
                                    <label><?= _lang("Thousand Separator") ?> <i class="text-danger">*</i></label>
                                    <input class="form-control" id="cts" type="text"
                                           placeholder="<?= _lang("Thousand Separator") ?>" value="." required="">
                                </div>

                                <div class="form-group hidden">
                                    <label><?=Translate::sprint("Rate")?></label>
                                    <input type="text" class="form-control" placeholder="<?=Translate::sprint("Currency rate")?>" id="rate_currency">
                                </div>

                                <div class="form-group">
                                        <button type="button" class="btn  btn-primary" id="addCurrency" > <span class="glyphicon glyphicon-check"></span> <?php echo Translate::sprint("Add","Add"); ?>  </button>
                                </div>

                            </form>

                    </div>

                </div>

                <div class="box box-solid">
                    <div class="box-header with-border">
                        <h3 class="box-title"><b> <?php echo Translate::sprint("Default_currency","Default currency"); ?> </b></h3>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body row">
                        <div class="col-sm-12">
                            <form id="form" role="form">

                                <div class="form-group">
                                    <select id="DEFAULT_CURRENCY" name="DEFAULT_CURRENCY" class="form-control select2 DEFAULT_CURRENCY">
                                        <option value='0'>-- <?php echo Translate::sprint("Currency"); ?></option>
                                    <?php

                                        foreach ($currencies as $key => $c){
                                            if($config['DEFAULT_CURRENCY']==$c['code']){
                                                echo '<option value="'.$c['code'].'" selected>'.$c['name'].', '.$c['code'].'</option>';
                                            }else{
                                                echo '<option value="'.$c['code'].'">'.$c['name'].', '.$c['code'].'</option>';
                                            }

                                        }

                                        ?>
                                    </select>
                                </div>

                            </form>
                        </div>
                    </div>
                    <!-- /.box-body -->

                    <div class="box-footer">
                        <div class="form-group">
                            <button type="button" class="btn  btn-primary" id="btnSave" > <span class="glyphicon glyphicon-check"></span> <?php echo Translate::sprint("Save"); ?> </button>
                        </div>
                    </div>

                </div>


            </div>


            <div class="col-sm-6">

                <div class="box box-solid">
                    <div class="box-header with-border">
                        <h3 class="box-title"><b> <?php echo Translate::sprint("Currencies","Currencies"); ?> </b></h3>
                    </div>

                    <div class="box-body  table-responsive">
                        <div class="col-sm-12">

                            <table id="example2" class="table table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th><?=Translate::sprint("Code")?></th>
                                    <th><?=Translate::sprint("Name")?></th>
                                    <th><?=Translate::sprint("Symbol")?></th>
                                    <th><?=Translate::sprint("Format")?></th>
                                    <!--                                    <th>--><?//=Translate::sprint("Rate")?><!--</th>-->
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                            <?php  foreach ($currencies as $key => $c): ?>
                                    <tr>
                                    <?php
                                        $findex = intval($c['format']);
                                        $findex = $findex-1;
                                        ?>
                                        <td><?=$c['code']?></td>
                                        <td><?=$c['name']?></td>
                                        <td><?=$c['symbol']?></td>
                                        <td><?=$formats[$findex]?></td>
                                        <!--                                    <td>--><?//=$c['rate']?><!--</td>-->
                                        <td align="right">
                                            <a href="#"  id="deleteCurrency" data="<?=$c['code']?>">
                                                <button type="button" class="btn btn-sm"><span class="glyphicon glyphicon-trash"></span></button>
                                            </a>

                                            <a href="#"  data-toggle="modal" data-target="#modal-default-<?=md5($c['code'])?>">
                                                <button type="button" class="btn btn-sm"><span class="glyphicon glyphicon-edit"></span></button>
                                            </a>


                                        </td>

                                    </tr>


                            <?php endforeach;?>

                                </tfoot>
                            </table>

                        </div>
                    </div>

                </div>

            </div>


    </section>

    <div class="modal fade" id="modal-confirm">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">

                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"></h4>
                </div>
                <div class="modal-body">

                    <div class="row">
                        <div style="text-align: center">
                            <h3 class="text-red"><?=Translate::sprint("Are you sure you want to delete it")?> ?</h3>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?=Translate::sprint("Cancel","Cancel")?></button>
                    <button type="button" id="_ok" class="btn btn-flat btn-primary"><?=Translate::sprint("OK")?></button>
                </div>
            </div>

            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
<?php  foreach ($currencies as $key => $c): ?>
    <div class="modal fade" id="modal-default-<?=md5($c['code'])?>">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">

                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?=Translate::sprint("Edit")?> <?=$c['name']?></h4>
                </div>
                <div class="modal-body">

                    <div class="row margin">

                        <form id="form" role="form">

                            <div class="form-group">
                                <label><?=Translate::sprint("Name")?></label>
                                <input type="text" class="form-control" value="<?=$c['name']?>" placeholder="Name ex: US Dollar" id="name_currency_<?=md5($c['code'])?>">
                            </div>
                            <div class="form-group">
                                <label><?=Translate::sprint("Code")?></label>
                                <input type="text" class="form-control" value="<?=$c['code']?>"  placeholder="Code ex: EUR,USD ..." id="code_currency_<?=md5($c['code'])?>" disabled>
                            </div>

                            <div class="form-group">
                                <label><?=Translate::sprint("Symbol")?></label>
                                <input type="text" class="form-control" value="<?=$c['symbol']?>"  placeholder="Symbol ex: $,€ ..." id="symbol_currency_<?=md5($c['code'])?>">
                            </div>

                            <div class="form-group">
                                <label><?= _lang("Format Decimals") ?> <i class="text-danger">*</i></label>
                                <input class="form-control" id="cfd_<?=md5($c['code'])?>" type="text"
                                       placeholder="<?= _lang("Format Decimals") ?>" value="<?=$c['cfd']?>" required="">
                            </div>

                            <div class="form-group">
                                <label><?= _lang("Decimal Point") ?> <i class="text-danger">*</i></label>
                                <input class="form-control" id="cdp_<?=md5($c['code'])?>" type="text"
                                       placeholder="<?= _lang("Format Decimals") ?>" value="<?=$c['cdp']?>" required="">
                            </div>

                            <div class="form-group">
                                <label><?= _lang("Thousand Separator") ?> <i class="text-danger">*</i></label>
                                <input style="width: 200px" class="form-control" id="cts_<?=md5($c['code'])?>" type="text"
                                       placeholder="<?= _lang("Format Decimals") ?>" value="<?=$c['cts']?>" required="">
                            </div>

                            <div class="form-group hidden">
                                <label><?=Translate::sprint("Rate")?></label>
                                <input type="text" class="form-control" value="<?=$c['rate']?>"  placeholder="1.0000" id="rate_currency_<?=md5($c['code'])?>">
                            </div>


                            <div class="form-group">
                                <label><?=Translate::sprint("Format")?></label>

                                <div class="form-group">
                                    <select id="CURRENCY_FORMAT_<?=md5($c['code'])?>" name="format" class="form-control select2 CURRENCY_FORMAT">

                                        <option value='1' <?php if($c['format']==1) echo "selected"?>>X0,000.00</option>
                                        <option value='2' <?php if($c['format']==2) echo "selected"?>>0,000.00X</option>
                                        <option value='3' <?php if($c['format']==3) echo "selected"?>>X 0,000.00</option>
                                        <option value='4' <?php if($c['format']==4) echo "selected"?>>0,000.00 X</option>
                                        <option value='5' <?php if($c['format']==5) echo "selected"?>>0,000.00</option>
                                        <option value='6' <?php if($c['format']==6) echo "selected"?>>X0,000.00 XX</option>
                                        <option value='7' <?php if($c['format']==7) echo "selected"?>>XX0,000.00</option>
                                        <option value='8' <?php if($c['format']==8) echo "selected"?>>0,000.00XX</option>
                                    </select>
                                </div>
                            </div>


                        </form>




                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?=Translate::sprint("Cancel","Cancel")?></button>
                    <button type="button" id="_edit_<?=($c['code'])?>" data="<?=($c['code'])?>" class="btn btn-flat btn-primary"><?=Translate::sprint("Edit")?></button>
                </div>


            </div>

            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>


<?php
    $script = '<script>
        $("div #_edit_'.$c['code'].'").on("click",function () {

            var selector =  $(this);

            var code = $(this).attr("data");

            var symbol_currency = $("#symbol_currency_'.md5($c['code']).'").val();
            var name_currency = $("#name_currency_'.md5($c['code']).'").val();
            var format_currency = $("#CURRENCY_FORMAT_'.md5($c['code']).'").val();
            var rate_currency = $("#rate_currency_'.md5($c['code']).'").val();
            
            
            var cfd = $("#modal-default-'.md5($c['code']).' #cfd_'.md5($c['code']).'").val();
            var cdp = $("#modal-default-'.md5($c['code']).' #cdp_'.md5($c['code']).'").val();
            var cts = $("#modal-default-'.md5($c['code']).' #cts_'.md5($c['code']).'").val();


            var dataSet = {
            
                "symbol_currency":symbol_currency,
                "name_currency":name_currency,
                "code_currency":"'.$c['code'].'",
                "format_currency":format_currency,
                "rate_currency":rate_currency,
                
                "cfd":cfd,
                "cdp":cdp,
                "cts":cts
                
            };



            $.ajax({
                type:"post",
                url:"'.site_url("ajax/setting/editCurrency").'",
                dataType: "json",
                data:dataSet,
                beforeSend: function (xhr) {
                    selector.attr("disabled",true);
                },error: function (request, status, error) {
                    NSAlertManager.simple_alert.request = "'.Translate::sprint("Input invalid").'";
                    selector.attr("disabled",false);

                    console.log(request);

                    $("#modal-default-'.md5($c['code']).'").modal("hide");
                },
                success: function (data, textStatus, jqXHR) {

                    $("#modal-default-'.md5($c['code']).'").modal("hide");
                    selector.attr("disabled",false);

                    if(data.success===1){
                        document.location.reload();
                    }else if(data.success===0){
                        var errorMsg = "";
                        for(var key in data.errors){
                            errorMsg = errorMsg+data.errors[key]+"\n";
                        }
                        if(errorMsg!==""){
                            NSAlertManager.simple_alert.request = errorMsg;
                        }
                    }
                }

            });



            return false;
        });
    </script>';

    AdminTemplateManager::addScript($script);

    ?>
<?php endforeach;?>


</div>



<?php

    $data['config'] = $config;

    $script = $this->load->view('setting/backend/html/scripts/currency-script', $data, TRUE);
    AdminTemplateManager::addScript($script);

?>




