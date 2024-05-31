<?php

$total_commission = 0;
$total_amount = 0;

?>

<div class="row">
    <div class="col-sm-12">


        <div class="box box-solid">
            <div class="box-header no-border">
                <div class=" row ">
                    <div class="pull-left col-md-8 box-title">
                        <b><?= Translate::sprint("My bookings") ?></b>
                    </div>
                    <div class="pull-right col-md-4"></div>
                </div>
            </div>

            <!-- /.box-header -->
            <div class="box-body table-responsive">
                <div class="list_general">
                    <?php if (!empty($booking)) : ?>
                        <ul>
                            <?php foreach ($booking as $key => $reservation): ?>
                                <?php

                                $storeData = $this->mStoreModel->getStoreData($reservation['store_id']);

                                if(isset($storeData['user_id']))
                                    $ownerData = $this->mUserModel->getUserData($storeData['user_id']);

                                $clientData = $this->mUserModel->getUserData($reservation['user_id']);

                                if(isset($storeData['user_id']))
                                    $image = ImageManagerUtils::getFirstImage($storeData['images'], ImageManagerUtils::IMAGE_SIZE_200);
                                else
                                    $image =  adminAssets("images/def_logo.png");

                                ?>
                                <li>
                                    <figure>
                                        <img src="<?= $image ?? "#" ?>" alt="">
                                    </figure>
                                    <h4>
                                        <?= $reservation['store_name'] ?>
                                        #<?= str_pad($reservation['id'], 7, "0", STR_PAD_LEFT) ?>
                                        (<?=($reservation['booking_type']=="service")?_lang("Service"):_lang("Digital")?>)
                                    </h4>


                                    <div>

                                        <?php
                                            if (isset($reservation['status']) && $reservation['status'] != "") {
                                                $statusParser = explode(";", $reservation['status']);
                                                echo "<strong style='color:" . $statusParser[1] . "'>" . $statusParser[0] . "</strong>";
                                            }
                                        ?>
                                    </div>
                                    <ul class="booking_list">
                                        <li><strong><?= _lang("Booking ID") ?>:</strong>
                                            #<?= $reservation['id'] ?></li>
                                        <li><strong><?= _lang("Booking date") ?>
                                                :</strong> <?= date("d M Y", strtotime($reservation['created_at'])) ?>
                                        </li>
                                        <li><strong><?= _lang("Booking details") ?>:</strong> <a
                                                    class="bookingDetail" data-toggle="collapse" role="button" aria-expanded="false" aria-controls="bookingDetailed-<?= $reservation['id'] ?>"
                                                    href="#bookingDetailed-<?= $reservation['id'] ?>"><?= _lang("Show Details") ?></a>

                                            <div class="collapse booking-detailed" id="bookingDetailed-<?= $reservation['id'] ?>">
                                                <?php
                                                $this->load->view('booking/backend/booking_popup_detail', array(
                                                    'reservation' => $reservation
                                                ));
                                                ?>
                                            </div>

                                        </li>
                                    </ul>

                                    <ul class="buttons">
                                        <li>
                                            <?php if($reservation['booking_type']=="service"): ?>
                                                <a href="<?= admin_url("booking/client_bookings_services#open=".$reservation['id'])?>" class="btn btn-default"><i
                                                            class="fa fa-list"></i> <?= _lang("Detail") ?>
                                                </a>
                                            <?php elseif($reservation['booking_type']=="digital"): ?>
                                                <a href="<?= admin_url("booking/client_bookings_digital#open=".$reservation['id'])?>" class="btn btn-default"><i
                                                            class="fa fa-list"></i> <?= _lang("Detail") ?>
                                                </a>
                                            <?php endif; ?>

                                        </li>
                                    </ul>
                                </li>

                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="result-not-found">
                            <?=_lang("No booking!")?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>

    </div>
</div>
