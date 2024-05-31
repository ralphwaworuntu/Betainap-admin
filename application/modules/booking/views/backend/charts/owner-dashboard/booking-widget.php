<?php

$ownerResult = $this->mBookingModel->getWidgetData();


?>
<div class="row dashboard">
    <div class="col-sm-12">
        <!-- LINE CHART -->
        <div class="box box-solid reservation-dashboard">
            <div class="box-header">
                <h3 class="box-title"><b><?=_lang("Booking")?></b></h3>
                <div class="box-tools pull-right">
                   <select class="select2 dashboard-dropdown" data-label="<?=_lang("All booking(s)")?>">
                <?php foreach ($ownerResult as $key => $value): ?>
                       <option value="<?=$key?>"><?=_lang("_filter_dashboard_".$key)?></option>
                <?php endforeach; ?>
                   </select>
                </div>
            </div>
            <div class="box-body chart-responsive">
                <div class="chart" id="line-chart" style="height: 290px;"></div>
            </div>
            <!-- /.box-body -->
            <div class="overlay">
                <i class="fa fa-refresh fa-spin"></i>
            </div>
        </div>
        <!-- /.box -->
    </div>

</div>


<dashboard-analytics data-module="booking" dashboard-analytics-status="all" class="hidden">
<?php foreach ($ownerResult as $key => $value): ?>
        <item data-id="<?=$key?>" data-key="<?=$key?>" data-label="<?=_lang($key)?>"><?=json_encode($value['all'])?></item>
<?php endforeach; ?>
</dashboard-analytics>

<dashboard-analytics data-module="booking" dashboard-analytics-status="pending" class="hidden">
<?php foreach ($ownerResult as $key => $value): ?>
        <item data-id="<?=$key?>" data-key="<?=$key?>" data-label="<?=_lang($key)?>"><?=json_encode($value['pending'])?></item>
<?php endforeach; ?>
</dashboard-analytics>

<dashboard-analytics data-module="booking" dashboard-analytics-status="canceled" class="hidden">
<?php foreach ($ownerResult as $key => $value): ?>
        <item data-id="<?=$key?>" data-key="<?=$key?>" data-label="<?=_lang($key)?>"><?=json_encode($value['canceled'])?></item>
<?php endforeach; ?>
</dashboard-analytics>

<dashboard-analytics data-module="booking" dashboard-analytics-status="confirmed" class="hidden">
<?php foreach ($ownerResult as $key => $value): ?>
        <item data-id="<?=$key?>" data-key="<?=$key?>" data-label="<?=_lang($key)?>"><?=json_encode($value['confirmed'])?></item>
<?php endforeach; ?>
</dashboard-analytics>

<?php

$script = $this->load->view('booking/backend/charts/owner-dashboard/owner-dashboard-script',NULL,TRUE);
AdminTemplateManager::addScript($script);


