<?php

$total_commission = 0;
$total_amount = 0;

?>

<div class="row">
    <div class="col-sm-12">
        <?php CMS_Display::render("booking.charts"); ?>
    </div>

    <?php if(count($booking)>0): ?>
        <div class="col-sm-6">
            <div class="box box-solid">
                <div class="box-header no-border">
                    <h3 class="box-title"><i class="mdi mdi-shopping"></i>  <?= _lang("Last booking") ?></h3>
                </div>

                <div class="box-body">
                    <table class="table table-hover">
                        <?php foreach ($booking as $reservation): ?>
                            <?php

                            $storeData = $this->mStoreModel->getStoreData($reservation['store_id']);
                            $ownerData = $this->mUserModel->getUserData($storeData['user_id']);
                            $clientData = $this->mUserModel->getUserData($reservation['user_id']);
                            $image = ImageManagerUtils::getFirstImage($storeData['images'], ImageManagerUtils::IMAGE_SIZE_200);

                            ?>

                            <tr role="row" class="odd">

                                <td width="10%" valign="center" align="center">
                                    <div class="image-container-70 square" style="background-image: url('<?=$image?>');background-size: auto 100%;
                                            background-position: center;">
                                        <img class="direct-chat-img invisible" src="<?=$image?>" alt="Image">
                                    </div>
                                </td>
                                <td>

                                    <span style="font-size: 14px">  <b> <?= "#" . str_pad($reservation['id'], 6, 0, STR_PAD_LEFT) ?> </b> </span><br>

                                    <?php
                                    if (isset($reservation['status']) && $reservation['status'] != "") {
                                        $statusParser = explode(";", $reservation['status']);
                                        echo "<strong style='color:" . $statusParser[1] . "'>" . _lang($statusParser[0]) . "</span>";
                                    }
                                    ?>
                                </td>

                                <td>
                                    <?php

                                    $pcode = $reservation['payment_status'];
                                    $payments = Booking_payment::PAYMENT_STATUS;
                                    if (isset($payments[$pcode])) {
                                        echo "<strong  style='color: " . $payments[$pcode]['color'] . "'>" . ucfirst(_lang($payments[$pcode]['label'])) . "</strong>";
                                    } else if ($pcode == "cod_paid") {
                                        echo "<strong class='text-green'>" . _lang("Paid with cash") . "</strong>";
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
                            $userName = $this->mUserModel->getFieldById("name", $reservation['user_id']);

                            if($userName==NULL){
                                $userName = _lang("User not found");
                            }

                            $array = array(
                                'booking_id' => "#" . str_pad($reservation['id'], 6, 0, STR_PAD_LEFT),
                                'client' => ucfirst($userName),
                                'client_phone' => ucfirst(textClear($this->mUserModel->getFieldById("telephone", $reservation['user_id']))),
                                'business_owner' => ucfirst(textClear(isset($store['user_id']) ? $this->mUserModel->getUserNameById($store['user_id']) : "--")),
                                'status' => $statusParser[0],
                                'date' => $reservation['updated_at'],
                            );
                            echo Exim_toolManager::setupRows($array);

                            ?>



                        <?php endforeach;?>
                    </table>

                </div>


            </div>
        </div>
    <?php endif; ?>


    <?php
    $data = $this->mBookingModel->bookingRates(-1);
    ?>


    <?php if($data['total']>0): ?>
        <div class="col-sm-6">
            <div class="box box-solid">
                <div class="box-header no-border">
                    <h3 class="box-title"><i class="mdi mdi-chart-arc"></i>  <?= _lang("Booking Rates") ?></h3>
                </div>

                <div class="box-body">
                    <div class="row">
                        <div class="col-sm-3 hidden-xs">

                        </div>
                        <div class="col-sm-6 col-xs-12">
                            <canvas id="pieChart" style="height:242px"></canvas>
                        </div>
                        <div class="col-sm-3 hidden-xs">

                        </div>
                    </div>
                </div>



                <div class="box-footer no-padding">

                    <ul class="nav nav-pills nav-stacked bookingRates">
                        <li>
                            <a data-id="New_booking" data-label="<?=_lang("New booking")?>" data-value="<?=intval($data['new'])?>">
                                <strong><?=_lang("New booking")?></strong>
                                <strong class="pull-right text-yellow"><?=intval($data['new'])?>%</strong>
                            </a>
                            <a data-id="Confirmed" data-label="<?=_lang("Confirmed")?>" data-value="<?=intval($data['confirmed'])?>">
                                <strong><?=_lang("Confirmed")?></strong>
                                <strong class="pull-right text-green"><?=intval($data['confirmed'])?>%</strong>
                            </a>
                            <a data-id="Canceled" data-label="<?=_lang("Canceled")?>" data-value="<?=intval($data['canceled'])?>">
                                <strong><?=_lang("Canceled")?></strong>
                                <strong class="pull-right text-red"><?=intval($data['canceled'])?>%</strong>
                            </a>
                        </li>
                    </ul>

                </div>

            </div>
        </div>
    <?php endif; ?>


</div>


<?php

$script = $this->load->view('booking/backend/charts/donuts-booking-rates-script',NULL,TRUE);
AdminTemplateManager::addScript($script);

