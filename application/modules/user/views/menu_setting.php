<?php

$uri_m = $this->uri->segment(2);
$uri_parent = $this->uri->segment(3);
$uri_child = $this->uri->segment(4);

?>

<?php if(GroupAccess::isGranted('user',USER_SETTING)): ?>

    <li class="<?php if ($uri_parent == "userSetting") echo "active"; ?>">
        <a href="<?= admin_url("user/userSetting") ?>"><i class="mdi mdi-account-cog"></i>
            &nbsp;<span> <?= Translate::sprint("User Settings") ?></span></a>
    </li>

    <li class="<?php if ($uri_parent == "userOtp") echo "active"; ?>">
        <a href="<?= admin_url("user/userOtp") ?>"><i class="mdi mdi-phone"></i>
            &nbsp;<span> <?= Translate::sprint("Phone OTP") ?></span></a>
    </li>
<?php endif; ?>
