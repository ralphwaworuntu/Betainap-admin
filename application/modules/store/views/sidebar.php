<?php
$currentStore = StoreHelper::getCurrentStore();
$stores = StoreHelper::loadStores();
?>

<?php if(!empty($stores)): ?>
    <div class="dropdown dropdown-selector show">
        <?php if($currentStore!=NULL): ?>
            <a class="btn btn-secondary dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <div class="image-container-20 margin-right pull-left square" style="background-image: url('<?=StoreHelper::getImage($currentStore)?>');background-size: auto 100%;
                    background-position: center;">
                    <img class="direct-chat-img invisible" src="views/backend/admin-v2/assets/images/profile_placeholder.png" alt="Message User Image">
                </div>
                <?=$currentStore['name']?>
            </a>
        <?php else: ?>
            <a class="btn btn-secondary dropdown-toggle" href="#"  role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="mdi mdi-view-list"></i>&nbsp;&nbsp;<?=_lang("All stores")?>
            </a>
        <?php endif; ?>
        <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
            <?php foreach (StoreHelper::loadStores() as $store): ?>
                <a class="dropdown-item" href="<?=admin_url("store/current")?>?id=<?=$store['id_store']?>&callback=<?=base64_encode(current_url())?>">
                    <div class="image-container-20 margin-right square pull-left" style="background-image: url('<?=StoreHelper::getImage($store)?>');background-size: auto 100%;
                        background-position: center;">
                        <img class="direct-chat-img invisible" src="<?=StoreHelper::getImage($store)?>" alt="<?=$store['name']?>">
                    </div>
                    <?=$store['name']?>
                </a>
            <?php endforeach; ?>
            <a class="dropdown-item" href="<?=admin_url("store/current")?>?id=0&callback=<?=base64_encode(current_url())?>">
                <i class="mdi mdi-view-list"></i>&nbsp;&nbsp;<?=_lang("All stores")?>
            </a>
            <a class="dropdown-item" href="<?=admin_url("store/create")?>">
                <i class="mdi mdi-plus"></i>&nbsp;&nbsp;<?=_lang("Add new Store")?>
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="dropdown dropdown-selector show">
        <a class="btn btn-secondary dropdown-toggle" href="<?=admin_url("store/create")?>">
            <i class="mdi mdi-plus"></i>&nbsp;&nbsp;<?=_lang("Add new Store")?>
        </a>
    </div>
<?php endif; ?>