<script src="<?= adminAssets("plugins/select2/select2.full.min.js") ?>"></script>
<script>

    $('.otp-config .select2').select2();

    $('.OTP_METHOD').on('change',function (){
        $('.otp-config-container').addClass('hidden');
    });

    $(".otp-config .btnSave").on('click', function () {

        var selector = $(this);

        var dataSet = {
            "OTP_ENABLED": $("#OTP_ENABLED").val(),
            "OTP_METHOD": $("#OTP_METHODS").val(),
        };


        $.ajax({
            url: "<?=  site_url("ajax/setting/saveAppConfig")?>",
            data: dataSet,
            dataType: 'json',
            type: 'POST',
            beforeSend: function (xhr) {
                selector.attr("disabled", true);
            }, error: function (request, status, error) {
                NSAlertManager.simple_alert.request = "<?=Translate::sprint("Input invalid")?>";
                selector.attr("disabled", false);
                console.log(request.responseText);
            },
            success: function (data, textStatus, jqXHR) {

                console.log(data);
                selector.attr("disabled", false);
                if (data.success === 1) {
                    handleSaveOtherFields();
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




    function handleSaveOtherFields(){

        let selector = $(this);
        let errors = {};
        let dataSet = {};

        $( ".otp-config .form-control" ).each(function( index ) {

            var $this = $(this);
            if ($this.is("textarea")) {

                let val = $this.val();

                if(val === "")
                    val = $this.text();

                if(val !== ""){
                    dataSet[$this.attr('name')] =  val;
                }else{
                    if($this.is("[required]"))
                        errors[$this.attr('name')] = "empty field!";
                }

            }else{
                let val  = $this.val();
                if(val !== ""){
                    dataSet[$this.attr('name')] =  val;
                }else{
                    if($this.is("[required]"))
                        errors[$this.attr('name')] = "empty field!";
                }
            }
        }).promise().done( function(){
            console.log(dataSet);
            console.log(errors);
            if (Object.keys(errors).length === 0){
                saveConfigData(dataSet,selector);
            }else{
                $('.otp-config .errors').removeClass("hidden");
            }
        } );
    }


    function saveConfigData(dataSet,selector) {

        $.ajax({
            url: "<?=  site_url("ajax/setting/saveAppConfig")?>",
            data: dataSet,
            dataType: 'json',
            type: 'POST',
            beforeSend: function (xhr) {
                NSTemplateUIAnimation.button.loading = selector;
            }, error: function (request, status, error) {
                NSAlertManager.simple_alert.request = "<?=Translate::sprint("Input invalid")?>";
                console.log(request.responseText);
                NSTemplateUIAnimation.button.default = selector;
            },
            success: function (data, textStatus, jqXHR) {

                NSTemplateUIAnimation.button.default = selector;

                console.log(data);

                if (data.success === 1) {
                    document.location.reload();
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


    $('.test-otp .sendTestOtpBtn').on('click',function (){
        let selector = $(this);
        $.ajax({
            url: "<?=  site_url("ajax/user/otpTestSend")?>",
            data: {
                'phone': $('.test-otp #OTP_test_phone').val()
            },
            dataType: 'json',
            type: 'POST',
            beforeSend: function (xhr) {
                NSTemplateUIAnimation.button.loading = selector;
            }, error: function (request, status, error) {
                NSAlertManager.simple_alert.request = "<?=Translate::sprint("Input invalid")?>";
                console.log(request.responseText);
                NSTemplateUIAnimation.button.default = selector;
            },
            success: function (data, textStatus, jqXHR) {
                NSTemplateUIAnimation.button.default = selector;
                console.log(data);
                if (data.success === 1) {
                    $('#test-otp-send-logs').html("<div class='alert' style='background-color: #bbedc1'>"+JSON.stringify(data)+"</div>");
                    $('.test-received-code').removeClass('hidden');
                } else if (data.success === 0) {
                    var errorMsg = "";
                    for (var key in data.errors) {
                        errorMsg = errorMsg + data.errors[key] + "<br/>";
                    }
                    if (errorMsg !== "") {
                       $('#test-otp-send-logs').html("<div class='alert' style='background-color: #f4c8c3'>"+errorMsg+"</div>");
                    }
                }
            }
        });

        return false;
    });


    $('.test-otp .verifyTestOtpBtn').on('click',function (){
        let selector = $(this);
        $.ajax({
            url: "<?=  site_url("ajax/user/otpTestVerify")?>",
            data: {
                'phone': $('.test-otp #OTP_test_phone').val(),
                'code': $('.test-otp #OTP_test_code').val(),
            },
            dataType: 'json',
            type: 'POST',
            beforeSend: function (xhr) {
                NSTemplateUIAnimation.button.loading = selector;
            }, error: function (request, status, error) {
                NSAlertManager.simple_alert.request = "<?=Translate::sprint("Input invalid")?>";
                console.log(request.responseText);
                NSTemplateUIAnimation.button.default = selector;
            },
            success: function (data, textStatus, jqXHR) {
                NSTemplateUIAnimation.button.default = selector;
                console.log(data);
                if (data.success === 1) {
                    $('#test-otp-verify-logs').html("<div class='alert' style='background-color: #bbedc1'>"+JSON.stringify(data)+"</div>");
                } else if (data.success === 0) {
                    var errorMsg = "";
                    for (var key in data.errors) {
                        errorMsg = errorMsg + data.errors[key] + "<br/>";
                    }
                    if (errorMsg !== "") {
                        $('#test-otp-verify-logs').html("<div class='alert' style='background-color: #f4c8c3'>"+errorMsg+"</div>");
                    }
                }
            }
        });

        return false;
    });


</script>
