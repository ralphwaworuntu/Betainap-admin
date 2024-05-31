<div class="col-md-12 group-<?=$grp['id']?> group" data-id="<?=$grp['id']?>">
    <table class="table table-responsive">
        <thead>
            <tr>
                <input type="hidden" class="grp-<?=$grp['id']?>-label" value="<?=$grp['label']?>" />
                <th colspan="3">
                    <i class="mdi mdi-menu cursor-pointer"></i>&nbsp;&nbsp;
                    <?=$grp['label']?>: <i><?=$grp['option_type']?></i>
                    &nbsp;&nbsp;&nbsp;
                </th>
                <th>
                    <?=_lang("Price/Option")?>
                </th>
                <th align="right" style="text-align: right">
                    <a href="#" class="btn btn-default update-grp" data-id="<?=$grp['id']?>"><i class="mdi mdi-pencil text-red"></i></a>&nbsp;
                    <a href="#" data-id="<?=$grp['id']?>" class="remove-grp btn btn-default"><i class="mdi mdi-delete text-red"></i></a>&nbsp;
                    <a href="#" data-id="<?=$grp['id']?>" class="add-option btn btn-default"><i class="mdi mdi-plus-box"></i> <?=_lang("Add option")?></a>
                </th>

            </tr>
        </thead>
        <tbody>
            <?php
                $options = $this->mService->laodServices($grp['store_id'],$grp['id']);
                if(!empty($options))
                foreach ($options as $opt){
                    $data['opt'] = $opt;
                    $this->load->view('service/plugV2/options/option_row',$data);
                }
            ?>

        </tbody>
    </table>
</div>
<div class="clearfix"></div>