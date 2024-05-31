<?php

$lastMessage = Translate::sprint("Me").": ";

$sender = json_decode($data['sender'],JSON_OBJECT_AS_ARRAY);


if(!isset($sender['result'])){
   $this->load->view("messenger/backend/html/empty_discussion_view",NULL);
   return;
}


$sender = $sender[Tags::RESULT];
$sender = $sender[0];

$image = "";

if(isset($sender['images'][0]['200_200']['url'])){
    $image = $sender['images'][0]['200_200']['url'];
}

$user_id = $this->mUserBrowser->getData("id_user");

$messages = json_decode($data['messages'],JSON_OBJECT_AS_ARRAY);
$messages = $messages[Tags::RESULT];

$nbrMessageNotSeen = 0;
foreach ($messages as $value){
    if($value['status']<0 && $value['sender_id']!=$user_id){
        $nbrMessageNotSeen++;
    }

    if($value['sender_id']!=$user_id)
        $lastMessage = "";
    else
        $lastMessage = Translate::sprint("Me").": ";

}





?>
<tr <?php if($nbrMessageNotSeen>0){ echo "class='active'";} ?>>
    <td>

        <?php if($image!=""):?>
        <div class="image-container-50 pull-left"  style="background-image: url('<?=$image?>');">
            <img class="direct-chat-img invisible" src="<?=$image?>" alt="Message User Image" >
        </div>
        <?php else: ?>
        <div class="image-container-50 p-image pull-left"
             style="background-size: auto 100%;
                     background-position: center;">
            <strong class="imageAlt"><?=getFirstWords($sender['name'])?></strong>
        </div>
        <?php endif;?>


        <div class="discussion-content <?=isset($sender['name'])?"clickable":""?>">
            <strong style="text-transform: uppercase"><?=isset($sender['name'])? ucfirst($sender['name']): "<i class='mdi mdi-link-off'></i> "._lang("User not found")?></strong>
        <?php

            if(isset($sender['typeAuth']))
                echo '<span class="pull-right">'.Translate::sprint($sender['typeAuth']).'</span>';

            ?>
            <div class="discussion-content-message" <?php if(isset($sender['hash_id'])): ?> onclick="redirect('<?= isset($sender['hash_id'])?admin_url("messenger/messages?u=".$sender['hash_id']):"#"?>')" <?php endif;?> >
            <?php

                if(count($messages)>0){
                    echo "<p style='width: 90%'>".$lastMessage." ".Text::echo_output($messages[0]['content'])."<span class=\"paragraph-end\"></span>
                        </p>";
                }

                ?>

            <?php if($nbrMessageNotSeen>0): ?>
                    <span class="badge bg-red"><?=$nbrMessageNotSeen?></span>
            <?php endif; ?>
            </div>
        <?php if(GroupAccess::isGranted('')): ?>
                <span onclick="removeDiscussion(<?=$data['id_discussion']?>)"  data-toggle="tooltip" title="" data-original-title="<?=Translate::sprint("Delete")?>" style="float: right" href=""><i class="mdi mdi-delete"></i></span>
        <?php endif;?>


        </div>

    <?php if($this->mUserBrowser->getData("typeAuth")=="admin"): ?>
            <div class="modal fade" id="modal-default-<?=$data['id_discussion']?>">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title"></h4>
                        </div>
                        <div class="modal-body">

                            <div class="row">

                                <div style="text-align: center">
                                    <h3 class="text-red"><?=Translate::sprint("Are you sure?")?></h3>
                                </div>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="button" id="_delete_discussion"  class="btn btn-flat btn-primary pull-right"><?=Translate::sprint("Yes")?></button>
                            <button type="button" class="btn btn-flat btn-default pull-right" data-dismiss="modal"><?=Translate::sprint("No")?></button>
                        </div>
                    </div>

                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>
    <?php endif; ?>
    </td>

</tr>



<script>

<?php if(GroupAccess::isGranted("messenger",MANAGE_MESSAGES)): ?>
    function removeDiscussion(id) {

        $("#modal-default-"+id).modal("show");

        $("#modal-default-"+id+" #_delete_discussion").on('click',function () {

            var selector = $(this);
            $.ajax({
                url:"<?=  site_url("ajax/messenger/delete_discussion")?>",
                data:{
                    id:id
                },
                dataType: 'json',
                type: 'POST',
                beforeSend: function (xhr) {

                    selector.attr("disabled",true);

                },error: function (request, status, error) {
                    console.log(request);
                    $("#modal-default-"+id).modal("hide");

                    selector.attr("disabled",false);
                },
                success: function (data, textStatus, jqXHR) {

                    selector.attr("disabled",false);
                    $("#modal-default-"+id).modal("hide");

                    if(data.success===1){

                        document.location.reload();

                    }
                }
            });


            return false;
        });

    }
<?php endif; ?>


</script>

<script>
    function redirect(url) {
        document.location.href = url;
    }
</script>