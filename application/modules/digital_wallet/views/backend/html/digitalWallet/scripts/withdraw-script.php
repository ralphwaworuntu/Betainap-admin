<script src="<?=  adminAssets("plugins/select2/select2.full.min.js")?>"></script>
<script>

    $('.select2').select2();
    $(".form-Withdraw #WithdrawBtn").on('click',function () {

        let selector = $(this);

        $.ajax({
            url:"<?=site_url("digital_wallet/ajax/requestWithdrawal")?>",
            type:'POST',
            data:{
                'amount': $('.form-Withdraw input[name=amount]').val(),
                'bank': $('.form-Withdraw select[name=bank]').val(),
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

                NSTemplateUIAnimation.button.default = selector;
                if(data.success===1){

                    $('.msgSuccess').removeClass('hidden');
                    $('.form-Withdraw input[name=amount]').val(0);

                    setTimeout(function (){
                        document.location.href = data.callback;
                    },3000);
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