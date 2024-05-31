<div class="row">
    <div class="col-sm-12 mt-5 mb-5 pb-4">
        <div class="row justify-content-center">
            <div class="col-sm-12">
                <div class="maintenance-img">
                    <img src="<?=adminAssets()?>/images/we-are-open-bg.png" alt="" class="img-fluid mx-auto d-block">
                </div>

                <div class="text-center mt-5">
                    <h3><?=Translate::sprintf("Open a new store on %s and begin selling your services.",[ConfigManager::getValue("APP_NAME")])?></h3>
                    <div class=" mt-4">
                        <a class="btn btn-info" href="<?=admin_url("store/create")?>"><i class="mdi mdi-plus font-size-24 d-block"></i>&nbsp;&nbsp;<?=_lang('Create new Store')?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
