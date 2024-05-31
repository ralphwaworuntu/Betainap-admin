<?php

$uri_m = $this->uri->segment(2);
$uri_parent = $this->uri->segment(3);
$uri_child = $this->uri->segment(4);


?>

<?php if (ModulesChecker::isEnabled("booking") && ModulesChecker::isEnabled("event")) : ?>
    <li>
        <a href="<?= admin_url("event/my_tickets") ?>"><i class="mdi mdi-ticket-outline"></i>
            &nbsp;<span><?=_lang("My tickets") ?></span></a>
    </li>
<?php endif; ?>


