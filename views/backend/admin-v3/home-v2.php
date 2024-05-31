
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <!-- Message Error -->
            <div class="col-sm-12">
                <?php $this->load->view(AdminPanel::TemplatePath."/include/messages"); ?>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <h4 class="title"><?=_lang("Dashboard")?><br><small><i class="mdi mdi-chart-arc"></i> <?=_lang('Overview')?></small></h4>
                <div class="cards">
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="small-box profile-box">
                                <div class="image-container-70 img margin-right pull-left <?=SessionManager::getData("images")!=""?"no-background":""?>" style="background-image: url('<?=SessionManager::getUserImage()?>');background-size: auto 100%;
                                                background-position: center;">
                                    <img class="direct-chat-img invisible" src="<?=SessionManager::getUserImage()?>" alt="Message User Image">
                                </div>
                                <div class="inner">
                                    <h4><?=_lang("Hello!")?> <?=(explode(" ",SessionManager::getData("name")))[0]?></h4>
                                    <p>
                                        <?php if(SessionManager::getData("manager")==GroupAccess::ADMIN_ACCESS):?>
                                        <span class="font-size12px">
                                            <i class="mdi mdi-account-outline"></i>&nbsp;<?=_lang('Admin')?>
                                        </span>
                                        <?php elseif(SessionManager::getData("manager")==GroupAccess::OWNER_ACCESS): ?>
                                        <span class="font-size12px">
                                              <i class="mdi mdi-account-outline"></i>&nbsp;<?=_lang("Business owner")?>
                                        </span>
                                        <?php elseif(SessionManager::getData("manager")==GroupAccess::CLIENT_ACCESS): ?>
                                            <span class="font-size12px">
                                              <i class="mdi mdi-account-outline"></i>&nbsp;<?=_lang("Client")?>
                                        </span>
                                        <?php endif; ?>
                                        &nbsp;&nbsp;<a href="<?=admin_url("user/profile")?>" class="text-black"><i class="mdi mdi-pencil"></i></a>
                                    </p>
                                </div>
                            </div>
                            <?php CMS_Display::render('widget_digital_wallet'); ?>
                        </div>
                        <div class="col-sm-8 col-xl-8 col-md-8">
                            <?php CMS_Display::render('widget_cards'); ?>
                            <?php CMS_Display::render('widget_booking'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php CMS_Display::render('widget_overview_charts'); ?>


        <div class="row">
            <div class="col">
                <?php CMS_Display::render("store.recentlyStoreAdded"); ?>
            </div>
            <div class="col">
                <?php CMS_Display::render("store.recentlyReviews"); ?>
            </div>
        </div>


        <?php CMS_Display::render("store.recentlyReviewsStoreOwner"); ?>

        <?php CMS_Display::render("booking.newPendingBooking"); ?>


    </section><!-- /.content -->
</div><!-- /.content-wrapper -->