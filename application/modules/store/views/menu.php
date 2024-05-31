<?php


$uri_m = $this->uri->segment(2);
$uri_parent = $this->uri->segment(3);
$uri_child = $this->uri->segment(4);

?>

<?php if(GroupAccess::isGranted('store')): ?>
    <?php  if (GroupAccess::isGranted('store',MANAGE_STORES)  ) : ?>
    <li class="treeview <?php if ($uri_m == "store") echo "active"; ?>">
        <a href="<?= admin_url("store/all_stores") ?>"><i class="mdi mdi mdi-storefront"></i> &nbsp;
            <span> <?= Translate::sprint("Manage Stores") ?></span>
            <span class="pull-right-container">
                          <i class="fa fa-angle-left pull-right"></i>
            </span>
        </a>
        <ul class="treeview-menu">
            <li class="<?php if ($uri_m == "store" && $uri_parent == "all_stores") echo "active"; ?>">
                    <a href="<?= admin_url("store/all_stores") ?>"><i class="mdi mdi-format-list-bulleted"></i> &nbsp;<span>
                                    <?= Translate::sprint("All_stores") ?></span>
                    <?php
                        $c = $this->mStoreModel->getUnverifiedStoresCount();
                        ?>
                    <?php if($c > 0): ?>
                            <small class="badge pull-right bg-yellow"><?=$c?></small>
                    <?php endif; ?>
                    </a>
                </li>

            <li class="<?php if ($uri_m == "store" && $uri_parent == "my_stores") echo "active"; ?>">
                <a href="<?= admin_url("store/my_stores") ?>"><i class="mdi mdi-format-list-bulleted"></i>
                    &nbsp;<span>
                                    <?= Translate::sprint("My_stores", "") ?></span></a>
            </li>

            <?php if(ModulesChecker::isEnabled("service")): ?>
                <li class="<?php if ($uri_m == "service" && $uri_m == "services") echo "active"; ?>">
                    <a href="<?= admin_url("service/services") ?>"><i class="mdi mdi-cart"></i>
                        &nbsp;<span> <?= Translate::sprint("Services/Menu") ?></span></a>
                </li>
            <?php endif;?>


            <?php if (GroupAccess::isGranted('store',ADD_STORE)) : ?>
            <li class="<?php if ($uri_m == "store" && $uri_parent == "create") echo "active"; ?>">
                <a href="<?= admin_url("store/create") ?>"><i class="mdi mdi-plus-box "></i> &nbsp;<span>
                                    <?= Translate::sprint("Add new", "") ?></span></a>
            </li>
        <?php endif; ?>
            <?php  if (ModulesChecker::isEnabled("cf_manager")
                    && GroupAccess::isGranted('store',MANAGE_STORES)  ) : ?>
                    <li class="<?php if ($uri_m == "store" && $uri_m == "options") echo "active"; ?>">
                        <a href="<?= admin_url("store/cf_categories") ?>"><i class="mdi mdi-cart"></i>
                            &nbsp;<span> <?= Translate::sprint("Checkout Fields") ?></span></a>
                    </li>
            <?php endif; ?>
        </ul>
    </li>
    <?php else: ?>
        <li class="treeview <?php if ($uri_m == "store") echo "active"; ?>">

            <a href="<?= admin_url("store/all_stores") ?>"><i class="mdi mdi mdi-storefront"></i> &nbsp;
                <span> <?= Translate::sprint("Manage Stores") ?></span>
                <span class="pull-right-container">
                          <i class="fa fa-angle-left pull-right"></i>
            </span>
            </a>

            <ul class="treeview-menu">
                <li class="<?php if ($uri_m == "store" && $uri_parent == "my_stores") echo "active"; ?>">
                    <a href="<?= admin_url("store/my_stores") ?>"><i class="mdi mdi-format-list-bulleted"></i>
                        &nbsp;<span>
                                    <?= Translate::sprint("My_stores", "") ?></span></a>
                </li>

                <?php if(ModulesChecker::isEnabled("service")): ?>
                    <li class="<?php if ($uri_m == "service" && $uri_m == "services") echo "active"; ?>">
                        <a href="<?= admin_url("service/services") ?>"><i class="mdi mdi-cart"></i>
                            &nbsp;<span> <?= Translate::sprint("Services") ?></span></a>
                    </li>
                <?php endif;?>


                <?php if (GroupAccess::isGranted('store',ADD_STORE)) : ?>
                    <li class="<?php if ($uri_m == "store" && $uri_parent == "create") echo "active"; ?>">
                        <a href="<?= admin_url("store/create") ?>"><i class="mdi mdi-plus-box "></i> &nbsp;<span>
                                    <?= Translate::sprint("Add new Store", "") ?></span></a>
                    </li>
                <?php endif; ?>
                <?php  if (ModulesChecker::isEnabled("cf_manager")
                    && GroupAccess::isGranted('store',MANAGE_STORES)  ) : ?>
                    <li class="<?php if ($uri_m == "store" && $uri_m == "options") echo "active"; ?>">
                        <a href="<?= admin_url("store/cf_categories") ?>"><i class="mdi mdi-cart"></i>
                            &nbsp;<span> <?= Translate::sprint("Checkout Fields") ?></span></a>
                    </li>
                <?php endif; ?>

            </ul>
        </li>
    <?php endif; ?>
<?php endif; ?>
