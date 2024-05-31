<script src="<?=  adminAssets("plugins/select2/select2.full.min.js")?>"></script>
<script>

    $('.select2').select2();

    $(".form-Top-up #select_amount").on('change',function () {

        let val = parseInt($(this).val());

        if(val === -1){
            $('.form-Top-up  .custom_amount').removeClass("hidden");
            $('.form-Top-up  .custom_amount #amount').val("");
        }else{
            $('.form-Top-up  .custom_amount').addClass("hidden");
            $('.form-Top-up  .custom_amount #amount').val($(this).val());
        }

        return false;
    });



    $(".form-Top-up #TopUpBtn").on('click',function () {

        let selector = $(this);

        $.ajax({
            url:"<?=site_url("digital_wallet/ajax/add_balance")?>",
            type:'POST',
            data:{
                'amount': $('.form-Top-up .custom_amount #amount').val()
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

                if(data.success===1){

                    NSTemplateUIAnimation.button.success = selector;
                    document.location.href = data.result;

                }else if(data.success===0){
                    NSTemplateUIAnimation.button.default = selector;
                    var errorMsg = "";
                    for(var key in data.errors){
                        errorMsg = errorMsg+data.errors[key]+"\n";
                    }
                    if(errorMsg!==""){
                        alert(errorMsg);
                    }
                }
            }

        });



        return false;
    });


</script>