<?php
$image = _openDir($opt['image']);

$imageSrc =  ImageManagerUtils::parseFirstImages([$image],ImageManagerUtils::IMAGE_SIZE_200);
?>
<tr class="opt-<?=$opt['id']?> opt bg-white" data-id="<?=$opt['id']?>">
    <td style="width: 2%;">
        <i class="mdi mdi-menu text-gray cursor-pointer"></i>
    </td>
    <td style="width: 8%;">
        <div class="image-container-70 square"
             style="background-image: url('<?=$imageSrc?>');background-size: auto 100%;
                     background-position: center;">
            <img class="direct-chat-img invisible" src="<?=$imageSrc?>" alt="Image">
        </div>
    </td>
    <td style="width: 50%;">
        <strong><?=$opt['label']?></strong><br>
        <span><?=($opt['description']!=null && strlen($opt['description'])>100) ?substr(Text::input($opt['description']),0,100).'...': Text::input($opt['description']??"")?></span>
    </td>
    <td style="width: 10%;">
        <?php if($opt['value']>0): ?>
        <strong class="text-red"><?=$opt['value']==0?"":Currency::parseCurrencyFormat($opt['value'],ConfigManager::getValue("DEFAULT_CURRENCY"))?></strong>
        <?php else: ?>
        --
        <?php endif; ?>
    </td>
    <td align="right" style="width: 30%">

        <input type="hidden" class="opt-<?=$opt['id']?>-title" value="<?=$opt['label']?>" />
        <input type="hidden" class="opt-<?=$opt['id']?>-description" value="<?=$opt['description']?>" />
        <input type="hidden" class="opt-<?=$opt['id']?>-value" value="<?=$opt['value']?>" />

        <?php if(!empty($image) && isset($image['300_300'])): ?>
        <input type="hidden" class="opt-<?=$opt['id']?>-image" value="<?=$image['name']?>" />
        <input type="hidden" class="opt-<?=$opt['id']?>-imageUrl" value="<?=$image['300_300']['url']?>" />
        <?php else: ?>
            <input type="hidden" class="opt-<?=$opt['id']?>-image" value="" />
            <input type="hidden" class="opt-<?=$opt['id']?>-imageUrl" value="" />
        <?php endif;?>

        <a href="#" class="btn btn-default update-opt" data-id="<?=$opt['id']?>"><i class="mdi mdi-pencil"></i></a>
        <a href="#" class="btn btn-default remove-opt" data-id="<?=$opt['id']?>"><i class="mdi mdi-delete"></i></a>
    </td>
</tr>