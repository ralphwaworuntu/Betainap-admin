<script src="<?= adminAssets("plugins/select2/select2.full.min.js") ?>"></script>
<script>


    $('form #status').select2();

    $("#savePbtn").on('click',function () {

        let selector = $(this);

        $.ajax({
            type: 'post',
            url: "<?=  site_url("ajax/event/saveParticipant")?>",
            dataType: 'json',
            data:{
                'id': $('form #id').val(),
                'event_id': $('form #event_id').val(),
                'status': $('form #status').val(),
                'attachement': $('form #attachement').val(),
            },
            beforeSend: function (xhr) {

                NSTemplateUIAnimation.button.loading = selector;

            }, error: function (request, status, error) {
                NSAlertManager.simple_alert.request = "<?=Translate::sprint("Input invalid")?>";
                console.log(request);

                NSTemplateUIAnimation.button.default = selector;
            },
            success: function (data, textStatus, jqXHR) {

                console.log(data);

                NSTemplateUIAnimation.button.success = selector;

                if (data.success === 1) {
                   history.back();
                } else if (data.success === 0) {
                    var errorMsg = "";
                    for (var key in data.errors) {
                        errorMsg = errorMsg + data.errors[key] + "<br/>";
                    }
                    if (errorMsg !== "") {
                        NSAlertManager.simple_alert.request = errorMsg;
                    }
                }
            }

        });

        return false;
    });


    $('.nsup-fileuploadlabel #delete').on('click',function (){
        $('form #attachement').val("");
    });

    function file_attache_002(data){
       $('form #attachement').val(data.dir);
    }

</script>