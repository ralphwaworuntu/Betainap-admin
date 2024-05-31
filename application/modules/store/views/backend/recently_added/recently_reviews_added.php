<?php

$reviews = $this->mStoreModel->getReviews(array(
    'limit' => 6
));
$reviews = $reviews['reviews'];


?>

<div class="box box-solid">
    <div class="box-header no-border">
        <h3 class="box-title"><i class="mdi mdi-star-plus-outline"></i>  <?= _lang("Last_reviews") ?></h3>
    </div>

    <div class="box-body">
        <table id="example2" class="table table-hover">
            <?php foreach ($reviews as $review) : ?>

                <?php
                    $user = $this->mUserModel->getUserByGuestId($review->guest_id);
                    if(isset($user[Tags::RESULT][0]['image'])){
                        $image = ImageManagerUtils::parseFirstImages($user[Tags::RESULT][0]['images'], ImageManagerUtils::IMAGE_SIZE_200);
                    }else
                        $image = null;
                ?>

                <tr>
                    <td width="10%" valign="center" align="center">
                        <div class="image-container-40 align-content-center p-image"
                             style="background-image: url('<?= $image ?>');">
                            <?php if($image!=null):?>
                                <img class="direct-chat-img invisible" src="<?=  $image  ?>" alt="user image">
                            <?php else: ?>
                                <strong class="imageAlt"><?=getFirstWords($review->pseudo)?></strong>
                            <?php endif;?>
                        </div>
                        <div class="name text-center pt-2">
                            <b><?= ucfirst(htmlspecialchars($review->pseudo)) ?></b>
                        </div>
                    </td>
                    <td valign="center">
                        <strong class="font-size16px"><?= Text::echo_output(Text::substrwords($review->nameStr, 40)); ?></strong>
                        <br>
                        <small>
                            <?php
                                $rate = ceil($review->rate);
                                for ($i = 1; $i <= $rate; $i++) { ?>
                                    <span class="mdi mdi-star text-yellow font-size20px"></span>
                                    <?php
                                    if ($i == $rate) {
                                        for ($j = $i; $j < 5; $j++) {
                                            echo ' <span class="mdi mdi-star-outline text-yellow  font-size20px"></span>';
                                        }
                                        break;
                                    }
                                }
                            ?>
                        </small>
                        <br>
                        <?= strlen($review->review)>100?htmlspecialchars(Text::substrwords($review->review, 100))." <a href='#'>[...]</a>":htmlspecialchars($review->review) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

    </div>


</div>
