<script src="<?= adminAssets("plugins/select2/select2.full.min.js") ?>"></script>
<script>

    $("#limit").select2();
    $("#limit").on('change',function () {
        let limit = parseInt($(this).val());
        document.location.href = "<?=current_url()."?event_id=".$event_id?>"+"&limit="+limit;
        return false;
    });

    var checked_participants_guest_list = [];
    var checked_participants_user_list = [];

    $("#check_all").on('click', function () {

        if ($(this).prop('checked')) {
            $(".participant_check").prop('checked', true);
        } else {
            $(".participant_check").prop('checked', false);
        }

        foreachCheck();

    });


    $(".participants .participant_check").on('click', function () {
        foreachCheck();
    });


    function foreachCheck() {

        checked_participants_guest_list = [];
        checked_participants_user_list = [];

        $(".participants .participant_check").each(function (index) {
            if ($(this).is(':checked')) {

                var guest_id = parseInt($(this).attr('data-guest-id'));
                var user_id = parseInt($(this).attr('data-user-id'));
                var data_agreement = parseInt($(this).attr('data-agreement'));

                if (data_agreement === 1) {
                    checked_participants_guest_list.push(guest_id);
                }

                checked_participants_user_list.push(user_id);

            }
        }).promise().done(function () {
            check_participants_trigger();
        });

    }

    function check_participants_trigger() {

        console.log("checked_participants");
        console.log(checked_participants_guest_list);
        console.log(checked_participants_guest_list.length);

        if(checked_participants_guest_list.length>0){
            $('.push_campaign span').text("("+checked_participants_guest_list.length+")");
            $('.push_campaign').removeClass("hidden");
        }else {
            $('.push_campaign').addClass("hidden");
        }

        if(checked_participants_user_list.length>0){
            $('.push_email span').text("("+checked_participants_user_list.length+")");
            $('.push_email').removeClass("hidden");
        }else {
            $('.push_email').addClass("hidden");
        }

        console.log("checked_participants_user_list");
        console.log(checked_participants_user_list);
        console.log(checked_participants_user_list.length);

    }


    $(".push_campaign").on('click',function () {

        if(checked_participants_guest_list.length>0){
            let guests = checked_participants_guest_list.join(",");
            document.location.href = "<?=admin_url("event/push_event_cg")?>?event_id=<?=$event_id?>&guests="+guests;
        }

        return false;
    });


    $(".push_email").on('click',function () {

        let selector = $(this);

        if(checked_participants_user_list.length>0){

            $.ajax({
                type: 'post',
                url: "<?=  site_url("ajax/event/sendReminder")?>",
                dataType: 'json',
                data:{
                    'users': checked_participants_user_list,
                    'event_id':selector.attr('data-id')
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
                        selector.text(data.result);
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

        }

        return false;
    });


    $('.sendTicket').on('click',function () {
        let selector = $(this);
        let id = $(this).attr('data-id');

        $.ajax({
            type: 'post',
            url: "<?=  site_url("ajax/event/sendTicket")?>",
            dataType: 'json',
            data:{
                'id': id,
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



</script>