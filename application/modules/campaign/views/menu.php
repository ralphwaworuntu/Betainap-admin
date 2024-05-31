<?php

$uri_m = $this->uri->segment(2);
$uri_parent = $this->uri->segment(3);
$uri_child = $this->uri->segment(4);


?>
<?php if (GroupAccess::isGranted('campaign', MANAGE_CAMPAIGNS)) : ?>

    <li class="treeview <?php if ($uri_m == "campaign") echo "active"; ?>">
        <a href="<?= admin_url("campaign/campaigns") ?>"><i class="mdi mdi-bullseye"></i> &nbsp;&nbsp;
            <span><?= Translate::sprint("Campaigns") ?></span>
            <span class="pull-right-container">
                      <i class="fa fa-angle-left pull-right"></i>
                    </span>
        </a>


    <?php

        $this->load->model("campaign/campaign_model");
        $nbrCampaigns = 0;
        $nbrCampaigns = $this->campaign_model->getPendingCampaigns();

        ?>
        <ul class="treeview-menu">
            <li class="<?php if ($uri_m == "campaign" && $uri_parent == "campaigns") echo "active"; ?>">
                <a href="<?= admin_url("campaign/campaigns") ?>"><i class="mdi mdi-format-list-bulleted"></i>
                    &nbsp;<span>
                             <?= Translate::sprint("Campaigns", "") ?></span>
                <?php if ($nbrCampaigns > 0): ?>
                        <span class="pull-right-container">
                                  <small class="badge pull-right bg-yellow"><?= $nbrCampaigns ?></small>
                                </span>
                <?php endif; ?>
                </a>
            </li>

            <li class="<?php if ($uri_m == "campaign" && $uri_parent == "create") echo "active"; ?>">
                <a href="<?= admin_url("campaign/create") ?>"><i class="mdi mdi-bullseye"></i> &nbsp;<span>
                    <?= Translate::sprint("Create new") ?>
                </span>
                </a>
            </li>


        </ul>
    </li>
<?php elseif (GroupAccess::isGranted('campaign')): ?>




    <li class="treeview <?php if ($uri_m == "campaign") echo "active"; ?>">
        <a href="<?= admin_url("campaign/campaigns") ?>"><i class="mdi mdi-bullseye"></i> &nbsp;&nbsp;
            <span><?= Translate::sprint("Campaigns") ?></span>
            <span class="pull-right-container">
                      <i class="fa fa-angle-left pull-right"></i>
                    </span>
        </a>
        <ul class="treeview-menu">
            <li class="<?php if ($uri_m == "campaign" && $uri_parent == "campaigns") echo "active"; ?>">
                <a href="<?= admin_url("campaign/campaigns") ?>"><i class="mdi mdi-bullseye"></i> &nbsp;<span>
                    <?= Translate::sprint("Campaigns") ?>
                </span>
                </a>
            </li>
            <li class="<?php if ($uri_m == "campaign" && $uri_parent == "create") echo "active"; ?>">
                <a href="<?= admin_url("campaign/create") ?>"><i class="mdi mdi-bullseye"></i> &nbsp;<span>
                    <?= Translate::sprint("Create new") ?>
                </span>
                </a>
            </li>
        </ul>
    </li>

<?php endif; ?>
