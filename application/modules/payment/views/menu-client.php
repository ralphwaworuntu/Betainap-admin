<?php

$uri_m = $this->uri->segment(2);
$uri_parent = $this->uri->segment(3);
$uri_child = $this->uri->segment(4);


$unpaid = $this->mPaymentModel->getUnpaidInvoicesCount();

?>


<li class="treeview <?php if ($uri_m == "payment") echo "active"; ?>">
    <a href="<?= admin_url("payment/billing") ?>"><i class="mdi mdi-receipt"></i> &nbsp;
        <span><?= _lang("Payment") ?> </span>
        <span class="pull-right-container">
                      <i class="fa fa-angle-left pull-right"></i>
                    </span>
    </a>

    <ul class="treeview-menu">

        <li class="treeview <?php if ($uri_m == "payment" and $uri_parent=="billing") echo "active"; ?>">
            <a href="<?= admin_url("payment/billing") ?>"><i class="mdi mdi-receipt"></i> &nbsp;
                <span><?= _lang("Billing") ?> </span>
                <?php if ($unpaid > 0): ?>
                    <span class="pull-right-container">
                                      <small class="badge pull-right bg-yellow"><?= $unpaid ?></small>
                                    </span>
                <?php endif; ?>
            </a>
        </li>

        <?php if (GroupAccess::isGranted('digital_wallet', DIGITAL_WALLET_SEND_RECEIVE)): ?>
            <li class="<?php if ($uri_parent == "sendDigitalMoney") echo "active"; ?>">
                <a href="<?= admin_url("digital_wallet/sendDigitalMoney") ?>"><i class="mdi mdi-wallet"></i>
                    &nbsp;<span> <?= _lang("My Wallet") ?></span></a>
            </li>
        <?php endif; ?>

    </ul>
</li>
