<?php
$reservations = $data[Tags::RESULT];
$pagination = $data['pagination'];
$this->load->model("user/user_model", "mUserModel");
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
                    <div class="box-header" style="width : 100%;">
                        <div class="row">
                            <div class="pull-left col-md-8 box-title">
                                <b><?= Translate::sprint("Booking Management") ?></b></div>
                            <div class="pull-right col-sm-4">
                                <div class="pull-right">
                                    <a class="btn btn-outline bg-primary" href="#" data-toggle="modal"
                                       data-toggle="tooltip"
                                       data-target="#modal-default-filter">
                                        <i class="mdi mdi-filter"></i>&nbsp;<?= _lang('Filter bookings') ?>
                                    </a>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- /.box-header -->
                    <div class="box-body table-responsive">

                        <?php if (
                            RequestInput::get('owner') != "" or
                            RequestInput::get('client') != "" or
                            RequestInput::get('store') != "" or
                            RequestInput::get('date_start') != "" or
                            RequestInput::get('date_end') != "" or
                            RequestInput::get('booking_status') != "" or
                            RequestInput::get('payment_status') != ""
                        ): ?>
                            <div id="bookings-filter-result-tags" class="margin-bottom">
                                <?= _lang("Filter") ?>:
                                <?php if (RequestInput::get('store') != ""): ?>
                                    <span class="badge badge-filter"><a href="#" data-name="product"><i
                                                    class="mdi mdi-close"></i></a>&nbsp;&nbsp;<?= _lang('Store') ?>: <?= RequestInput::get('store') ?></span>
                                <?php endif; ?>
                                <?php if (RequestInput::get('client') != ""): ?>
                                    <span class="badge badge-filter"><a href="#" data-name="client"><i
                                                    class="mdi mdi-close"></i></a>&nbsp;&nbsp;<?= _lang('Client') ?>: <?= RequestInput::get('client'); ?></span>
                                <?php endif; ?>
                                <?php if (RequestInput::get('owner') > 0): ?>

                                    <?php
                                    $sc = parseUrlParam('owner');
                                    ?>
                                    <span class="badge badge-filter"><a href="#" data-name="owner"><i
                                                    class="mdi mdi-close"></i></a>&nbsp;&nbsp;<?= _lang('Owner') ?>: <?= implode(' | ', $sc) ?></span>
                                <?php endif; ?>
                                <?php if (RequestInput::get('date_start') != ""): ?>
                                    <span class="badge badge-filter"><a href="#" data-name="date_start"><i
                                                    class="mdi mdi-close"></i></a>&nbsp;&nbsp;<?= _lang('Date start') ?>: <?= RequestInput::get('date_start') ?></span>
                                <?php endif; ?>
                                <?php if (RequestInput::get('date_end') != ""): ?>
                                    <span class="badge badge-filter"><a href="#" data-name="date_end"><i
                                                    class="mdi mdi-close"></i></a>&nbsp;&nbsp;<?= _lang('Date end') ?>: <?= RequestInput::get('date_end') ?></span>
                                <?php endif; ?>

                                <?php if (RequestInput::get('booking_status') != ""): ?>
                                    <?php
                                    $sc = parseUrlParam('booking_status');
                                    ?>
                                    <span class="badge badge-filter"><a href="#" data-name="booking_status"><i
                                                    class="mdi mdi-close"></i></a>&nbsp;&nbsp;<?= _lang('Order status') ?>: <?= implode(' | ', BookingHelper::convertInToStatus($sc)) ?></span>
                                <?php endif; ?>
                                <?php if (RequestInput::get('payment_status') != ""): ?>
                                    <?php
                                    $sc = parseUrlParam('payment_status');
                                    ?>
                                    <span class="badge badge-filter"><a href="#" data-name="payment_status"><i
                                                    class="mdi mdi-close"></i></a>&nbsp;&nbsp;<?= _lang('Payment') ?>: <?= implode(' | ', BookingHelper::convertInTopPaymentStatus($sc)) ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <table id="" class="table table-bordered table-hover">
                            <thead>
                            <tr>
                                <th><?= Translate::sprint("Order ID") ?></th>
                                <th><?= Translate::sprint("Type") ?></th>
                                <th><?= Translate::sprint("Client") ?></th>
                                <th><?= Translate::sprint("Business/Owner") ?></th>
                                <th><?= Translate::sprint("Status") ?></th>
                                <th><?= Translate::sprint("Payment") ?></th>
                                <th><?= Translate::sprint("Subtotal") ?></th>
                                <th><?= Translate::sprint("Date") ?></th>
                                <th>

                                    <?php

                                    $export_plugin = $this->exim_tool->plugin_export(array(
                                        'module' => 'bookings'
                                    ));

                                    echo $export_plugin['html'];
                                    AdminTemplateManager::addScript($export_plugin['script']);

                                    ?>

                                </th>
                            </tr>
                            </thead>
                            <tbody id="list">

                            <?php

                            $total_commission = 0;
                            $total_amount = 0;

                            ?>
                            <?php if (!empty($reservations)) : ?>

                                <?php foreach ($reservations as $key => $reservation): ?>

                                    <?php
                                    $token = $this->mUserBrowser->setToken(Text::encrypt($reservation['id']));
                                    ?>

                                    <tr class="store_<?= $token ?>" role="row" class="odd">

                                        <td>
                                            <span style="font-size: 14px">  <b> <?= "#" . str_pad($reservation['id'], 6, 0, STR_PAD_LEFT) ?> </b> </span>
                                        </td>
                                        <td>
                                            <?php if($reservation['booking_type']=="digital"):?>
                                            <span class="text-green"><?=ucfirst($reservation['booking_type'])?></span>
                                            <?php else: ?>
                                              <span class="text-blue"><?=ucfirst($reservation['booking_type'])?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?= ucfirst(textClear($this->mUserModel->getFieldById("name", $reservation['user_id']))) ?></strong>
                                            <?php if (GroupAccess::isGranted("user", MANAGE_USERS)): ?>
                                                &nbsp;&nbsp;<a target="_blank"
                                                               href="<?= admin_url("user/edit?id=" . $reservation['user_id']) ?>"><i
                                                            class="mdi mdi-open-in-new"></i></a>
                                            <?php endif; ?>


                                            <br/>
                                            <span><?= hideEmailAddress(textClear($this->mUserModel->getFieldById("email", $reservation['user_id']))) ?></span>
s

                                        </td>

                                        <td>
                                            <?php $store = $this->mBookingModel->getStore($reservation['store_id']); ?>
                                            <?php if (!empty($store)): ?>
                                                <?= $store['name'] ?>
                                                <br/>
                                                <a target="_blank"
                                                   href="<?= admin_url("user/edit?id=" . $store['user_id']) ?>"><?= ucfirst($this->mUserModel->getUserNameById($store['user_id'])) ?>
                                                    <i class="mdi mdi-open-in-new"></i>
                                                </a>
                                            <?php else: ?>
                                                <i class="text-red mdi mdi-close"></i> <?= _lang("Removed Store") ?> (#<?= $reservation['store_id'] ?>)
                                            <?php endif; ?>
                                        </td>

                                        <td>

                                            <?php

                                            if (isset($reservation['status']) && $reservation['status'] != "") {
                                                $statusParser = explode(";", $reservation['status']);
                                                echo "<span class=badge style='background:" . $statusParser[1] . "'>" . _lang($statusParser[0]) . "</span>";
                                            }
                                            ?>
                                        </td>


                                        <td>
                                            <?php

                                                $pcode = $reservation['payment_status'];
                                                $payments = Booking_payment::PAYMENT_STATUS;
                                                if (isset($payments[$pcode])) {
                                                    echo "<span class='badge' style='background-color: " . $payments[$pcode]['color'] . "'>" . ucfirst(_lang($payments[$pcode]['label'])) . "</span>";
                                                } else if ($pcode == "cod_paid") {
                                                    echo "<span class='badge bg-green'>" . _lang("Paid with cash") . "</span>";
                                                } else if ($pcode == "refunded") {
                                                    echo "<span class='badge bg-red'>" . _lang("Refunded") . "</span>";
                                                }

                                            ?>
                                        </td>


                                        <td>

                                            <?php

                                            $cart = json_decode($reservation['cart'], JSON_OBJECT_AS_ARRAY);
                                            $sub_total = 0;
                                            $currency = DEFAULT_CURRENCY;

                                            $commission = 0;


                                            foreach ($cart as $item) {

                                                if (empty($item))
                                                    continue;

                                                $callback = NSModuleLinkers::find($item['module'], 'getData');

                                                if ($callback != NULL) {

                                                    $params = array(
                                                        'id' => $item['module_id']
                                                    );

                                                    $result = call_user_func($callback, $params);

                                                }

                                                $sub_total = $sub_total + ($item['amount'] * $item['qty']);
                                                $total_amount = $total_amount + $sub_total;
                                            }

                                            if (defined('DEFAULT_TAX') and DEFAULT_TAX > 0) {

                                                $percent = 0;
                                                $tax = $this->mTaxModel->getTax(DEFAULT_TAX);
                                                if ($tax != NULL) {
                                                    $percent = $tax['value'];

                                                }

                                            }

                                            echo "<b>" . Currency::parseCurrencyFormat($sub_total, $currency) . "</b>";


                                            ?>

                                        </td>

                                        <td>
                                            <span> <?=DateSetting::parseDateTime($reservation['created_at'])?></span>
                                        </td>

                                        <td align="right">

                                            <a class="btn btn-default" data-toggle="tooltip"
                                               href="<?= admin_url("booking/view?id=" . $reservation['id']) ?>"
                                               title="<?= Translate::sprint("Edit") ?>">
                                                <i class="fa fa-pencil"></i>
                                            </a>

                                        </td>

                                    </tr>


                                    <?php


                                    $store = $this->mBookingModel->getStore($reservation['store_id']);
                                    $statusParser = explode(";", $reservation['status']);


                                    $array = array(
                                        'booking_id' => "#" . str_pad($reservation['id'], 6, 0, STR_PAD_LEFT),
                                        'client' => ucfirst($this->mUserModel->getFieldById("name", $reservation['user_id'])),
                                        'client_phone' => ucfirst(textClear($this->mUserModel->getFieldById("telephone", $reservation['user_id']))),
                                        'business_owner' => ucfirst(textClear(isset($store['user_id']) ? $this->mUserModel->getUserNameById($store['user_id']) : "--")),
                                        'status' => $statusParser[0],
                                        'date' => $reservation['updated_at'],
                                    );
                                    echo Exim_toolManager::setupRows($array);

                                    ?>

                                <?php endforeach; ?>

                            <?php else: ?>
                                <tr>
                                    <td colspan="7" align="center">
                                        <div style="text-align: center"><?= Translate::sprint("No data found", "") ?></div>
                                    </td>
                                </tr>

                            <?php endif; ?>


                            </tbody>
                        </table>


                        <div class="row">
                            <div class="col-sm-12">
                                <div class="dataTables_paginate paging_simple_numbers" id="example2_paginate">

                                    <?php
                                    echo $pagination->links(array(
                                        "status" => intval(RequestInput::get("status")),
                                        "search" => RequestInput::get("search"),
                                        "owner_id" => intval(RequestInput::get("owner_id")),
                                    ), $pagination_url);

                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.box-body -->
            </div>
        </div>
</div>


<?php

$html = $this->load->view('booking/backend/filter-modal',NULL,TRUE);
AdminTemplateManager::addHtml($html);

$script = $this->load->view('booking/backend/scripts/booking-script', NULL, TRUE);
AdminTemplateManager::addScript($script);

?>
