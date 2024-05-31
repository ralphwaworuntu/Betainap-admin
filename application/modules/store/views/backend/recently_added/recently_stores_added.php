<?php if (!empty($stores)) : ?>
    <div class="box box-solid">
        <div class="box-header no-border">
            <h3 class="box-title"><i class="mdi mdi-storefront-plus"></i> <?= _lang("Recently_Added") ?></h3>
        </div>
        <div class="box-body">
            <table class="table table-hover">
                <?php foreach ($stores as $store) : ?>
                    <!-- /.item -->
                    <tr>
                        <td style="width: 70px">
                            <div class="product-img margin-right">
                                <div class="image-container-70 square" style="background-image: url('<?= ImageManagerUtils::getFirstImage($store['images'], ImageManagerUtils::IMAGE_SIZE_200)?>');background-size: auto 100%;background-position: center;">
                                    <img class="direct-chat-img invisible" src="<?= ImageManagerUtils::getFirstImage($store['images'], ImageManagerUtils::IMAGE_SIZE_200)?>" alt="Image">
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="product-info">
                                <strong class="font-size16px"><?= (ucfirst(Text::echo_output($store['category_name']))) ?></strong><br/>
                                <span class="text-yellow"><?=parseToRatingStars(round($store['votes'], 2), "font-size16px ")?> (<?=round($store['votes'], 2)?>)</span><br>
                                <span class="pt-2 pm-1"><?=$store['category_name']?></span><br/>
                                <span class="product-description">
                                    <?= Text::echo_output($store['address']) ?>
                                </span>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <!-- /.box-body -->
        <?php if (count($stores) > 4) { ?>
            <div class="box-footer text-center">
                <a href="<?= admin_url("store/all_stores") ?>"
                   class="uppercase"><?= Translate::sprint("view more") ?> </a>
            </div>
        <?php } ?>
        <!-- /.box-footer -->
    </div>
<?php endif; ?>