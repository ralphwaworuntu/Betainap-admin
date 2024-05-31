<?php

$uri_m = $this->uri->segment(2);
$uri_parent = $this->uri->segment(3);
$uri_child = $this->uri->segment(4);


    if(GroupAccess::isGranted('booking',GRP_MANAGE_BOOKING_CONFIG)){


        $servicesCountAdmin = $this->mBookingModel->countPending(FALSE,"service");
        $servicesCountAdmin = isset($servicesCountAdmin[Tags::COUNT]) ? $servicesCountAdmin[Tags::COUNT] : 0;

        $digitalCountAdmin = $this->mBookingModel->countPending(FALSE,"digital");
        $digitalCountAdmin = isset($digitalCountAdmin[Tags::COUNT]) ? $digitalCountAdmin[Tags::COUNT] : 0;

    }else{

        $servicesCountAdmin = $this->mBookingModel->countPending(TRUE,"service");
        $servicesCountAdmin = isset($servicesCountAdmin[Tags::COUNT]) ? $servicesCountAdmin[Tags::COUNT] : 0;

        $digitalCountAdmin = $this->mBookingModel->countPending(TRUE,"digital");
        $digitalCountAdmin = isset($digitalCountAdmin[Tags::COUNT]) ? $digitalCountAdmin[Tags::COUNT] : 0;


    }



$all = $servicesCountAdmin + $digitalCountAdmin;


?>


<?php if (ModulesChecker::isEnabled("booking"))
    if (GroupAccess::isGranted('booking', GRP_MANAGE_BOOKING) && GroupAccess::isGranted('booking', GRP_MANAGE_BOOKING_CONFIG)) :

        ?>

        <li class="treeview <?php if ($uri_m == "booking") echo "active"; ?>">
            <a href="<?= admin_url("booking/all_bookings") ?>"><i class="mdi  mdi-calendar-clock"></i>
                &nbsp;<span> <?= Translate::sprint("Booking") ?></span>
                <?php if (($all) > 0): ?>
                    <small class="badge pull-right bg-yellow"><?= ($all) ?></small>
                <?php endif; ?>
            </a>

            <ul class="treeview-menu">

                <?php if (GroupAccess::isGranted('booking', GRP_MANAGE_BOOKING_CONFIG)): ?>
                    <li class="<?php if ($uri_m == "booking" && $uri_parent == "all_booking") echo "active"; ?>">
                        <a href="<?= admin_url("booking/all_bookings") ?>"><i class="mdi mdi-cart-outline"></i>
                            &nbsp;<?= Translate::sprint("All") ?>
                            <?php if ($all > 0): ?>
                                <small class="badge pull-right bg-yellow"><?= $all ?></small>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if (GroupAccess::isGranted('booking', GRP_MANAGE_BOOKING_CONFIG)): ?>
                    <li class="<?php if ($uri_m == "booking" && $uri_parent == "all_booking") echo "active"; ?>">
                        <a href="<?= admin_url("booking/all_bookings_service") ?>"><i class="mdi mdi-toolbox-outline"></i>
                            &nbsp;<?= Translate::sprint("Services") ?>
                            <?php if ($servicesCountAdmin > 0): ?>
                                <small class="badge pull-right bg-yellow"><?= $servicesCountAdmin ?></small>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if (GroupAccess::isGranted('booking', GRP_MANAGE_BOOKING_CONFIG)): ?>
                    <li class="<?php if ($uri_m == "booking" && $uri_parent == "all_booking") echo "active"; ?>">
                        <a href="<?= admin_url("booking/all_digital") ?>"><i class="mdi mdi-ticket-outline"></i>
                            &nbsp;<?= Translate::sprint("Digital") ?>
                            <?php if ($digitalCountAdmin > 0): ?>
                                <small class="badge pull-right bg-yellow"><?= $digitalCountAdmin ?></small>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>

        </li>


    <?php elseif (GroupAccess::isGranted('booking', GRP_MANAGE_BOOKING)): ?>
        <li class="treeview <?php if ($uri_m == "booking") echo "active"; ?>">
            <a href="<?= admin_url("booking/all_booking") ?>"><i class="mdi  mdi-calendar-clock"></i>
                &nbsp;<span> <?= Translate::sprint("Booking") ?></span>
                <?php if (($all) > 0): ?>
                    <small class="badge pull-right bg-yellow"><?= ($all) ?></small>
                <?php endif; ?>
            </a>
            <ul class="treeview-menu">
                <li class="<?php if ($uri_m == "booking" && $uri_parent == "all_booking") echo "active"; ?>">
                    <a href="<?= admin_url("booking/my_reservations") ?>"><i class="mdi mdi-toolbox-outline"></i>
                        &nbsp;<?= Translate::sprint("Services") ?>
                        <?php if ($servicesCountAdmin > 0): ?>
                            <small class="badge pull-right bg-yellow"><?= $servicesCountAdmin ?></small>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="<?php if ($uri_m == "booking" && $uri_parent == "all_booking") echo "active"; ?>">
                    <a href="<?= admin_url("booking/my_digital") ?>"><i class="mdi mdi-ticket-outline"></i>
                        &nbsp;<?= Translate::sprint("Digital") ?>
                        <?php if ($digitalCountAdmin > 0): ?>
                            <small class="badge pull-right bg-yellow"><?= $digitalCountAdmin ?></small>
                        <?php endif; ?>
                    </a>
                </li>
            </ul>
        </li>
    <?php endif; ?>
