<?php

$uri_m = $this->uri->segment(2);
$uri_parent = $this->uri->segment(3);
$uri_child = $this->uri->segment(4);

$countClientPending1 = $this->mBookingModel->countClientPending("service");
$countClientPending2 = $this->mBookingModel->countClientPending("digital");
$countClientPending =  $countClientPending1+$countClientPending2;
?>

<?php if (ModulesChecker::isEnabled("booking")
    && version_compare(ModulesChecker::getField("cms", "version_name"), '2.0.2', '>=')): ?>
    <li class=" <?php if ($uri_parent == "client_bookings_digital" or $uri_parent == "client_bookings_services" ) echo "active"; ?>">
        <a href="<?= admin_url("booking/client_bookings") ?>"><i class="mdi  mdi-calendar-clock"></i>
            &nbsp;<span> <?= Translate::sprint("My bookings") ?></span>

            <?php if ($countClientPending > 0): ?>
                <small class="badge pull-right bg-yellow"><?= $countClientPending ?></small>
            <?php endif; ?>
        </a>

        <ul class="treeview-menu">

            <li class="<?php if ($uri_m == "booking" && $uri_parent == "client_bookings_services") echo "active"; ?>">
                <a href="<?= admin_url("booking/client_bookings_services") ?>"><i class="mdi mdi-calendar"></i>
                    &nbsp;<?= Translate::sprint("Services") ?>

                    <?php if ($countClientPending1 > 0): ?>
                        <small class="badge pull-right bg-yellow"><?= $countClientPending1 ?></small>
                    <?php endif; ?>
                </a>
            </li>

            <li class="<?php if ($uri_m == "booking" && $uri_parent == "client_bookings_digital") echo "active"; ?>">
                <a href="<?= admin_url("booking/client_bookings_digital") ?>"><i class="mdi mdi-ticket-outline"></i>
                    &nbsp;<?= Translate::sprint("Digital") ?>

                    <?php if ($countClientPending2 > 0): ?>
                        <small class="badge pull-right bg-yellow"><?= $countClientPending2 ?></small>
                    <?php endif; ?>
                </a>
            </li>
        </ul>
    </li>
<?php endif; ?>