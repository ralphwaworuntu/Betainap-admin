<script>

    $(".form-banks #addBankBtn").on('click',function () {

        let selector = $(this);

        $.ajax({
            url:"<?=site_url("digital_wallet/ajax/addBank")?>",
            type:'POST',
            data:{
                'name': $('.form-banks input[name=name]').val(),
                'account_number': $('.form-banks input[name=account_number]').val(),
                'country': $('.form-banks input[name=country]').val(),
                'holder_name': $('.form-banks input[name=holder_name]').val(),
                'token': '<?=$this->mUserBrowser->setToken("WgTh_ABbnl")?>',
            },
            dataType: 'json',
            beforeSend: function (xhr) {
                NSTemplateUIAnimation.button.loading = selector;
            },error: function (request, status, error) {
                NSTemplateUIAnimation.button.default = selector;
                alert(request.responseText);
                console.log(request);
            },
            success: function (data, textStatus, jqXHR) {

                console.log(data);
                if(data.success===1){
                    NSTemplateUIAnimation.button.success = selector;
                    document.location.href = data.callback;
                }else if(data.success===0){
                    NSTemplateUIAnimation.button.default = selector;
                    var errorMsg = "";
                    for(var key in data.errors){
                        errorMsg = errorMsg+data.errors[key]+"<br>";
                    }

                    if(errorMsg!==""){
                        NSAlertManager.simple_alert.request = errorMsg;
                    }
                }
            }

        });



        return false;
    });


</script>