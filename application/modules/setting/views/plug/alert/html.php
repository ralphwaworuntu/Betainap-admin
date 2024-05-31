
<div class="modal fade" id="fmodal-default">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title"><b><?=_lang("Confirmation!")?></b></h4>
            </div>
            <div class="modal-body">

                <div class="row">
                    <div style="text-align: center">
                        <h3 class="text-red"><?=Translate::sprint("Are you sure?")?></h3>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?=Translate::sprint("Cancel","Cancel")?></button>
                <button type="button" id="apply_confirm" class="btn btn-flat btn-primary"><?=Translate::sprint("Apply")?></button>
            </div>
        </div>

        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>



<div class="modal fade" id="simple-alert-modal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title"><b><?=_lang("Alert!")?></b></h4>
            </div>
            <div class="modal-body">

                <div class="">
                    <p class="text-red message">
                    </p>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?=Translate::sprint("Cancel")?></button>
                <button type="button" id="DONE" class="btn btn-flat btn-primary"><?=Translate::sprint("DONE")?></button>
            </div>
        </div>

        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>


<script>


    let templateUtils = {

        fieldErrors: {
            resetAll() {
                $('[data-field-error] .field-error').remove();
                $('[data-field-error] input, [data-field-error] textarea').removeClass("error");
            },
            set reset($fieldKey) {
                for (let key in $elements){
                    $('[data-field-error='+$fieldKey+'] .field-error').remove();
                    $('[data-field-error='+$fieldKey+'] input[name='+$fieldKey+'], ' +
                        '[data-field-error='+$fieldKey+'] textarea[name='+$fieldKey+']').removeClass("error");
                }
            },
            set errors($elements) {

                let errorsMessage  = "<ul>";
                for (let key in $elements){

                    $('[data-field-error='+key+'] .field-error').remove();
                    $('[data-field-error='+key+'] input,' +
                        ' [data-field-error='+key+'] textarea,  ' +
                        '[data-field-error='+key+'] select:not(.select2),'+
                        '[data-field-error='+key+'] .select2-container'
                    )
                        .after("<span class=\"field-error\">"+$elements[key]+"</span>");

                    $('[data-field-error='+key+'] input[name='+key+'], ' +
                        '[data-field-error='+key+'] input,' +
                        '[data-field-error='+key+'] textarea[name='+key+'],'+
                        '[data-field-error='+key+'] .select2-container'
                    )
                        .addClass("error").on('keyup',function (){
                        $('[data-field-error='+$(this).attr('name')+'] .field-error, ' +
                            '[data-field-error='+key+'] .field-error').remove();
                        $(this).removeClass('error');
                    });

                    $('[data-field-error='+key+'] select, ' +
                        '[data-field-error='+key+'] .select2'
                    ).addClass("error").on('change',function (){

                        $('[data-field-error='+key+'] .field-error, ' +
                            '[data-field-error='+$(this).attr('name')+'] .field-error').remove();

                        $('[data-field-error='+key+'] .select2-container,' +
                            ' [data-field-error='+$(this).attr('name')+'] .select2-container').removeClass('error');

                    });

                    $('[data-field-error='+key+'] input[type=checkbox]')
                        .addClass("error").on('click',function (){

                        $('[data-field-error='+key+'] .field-error').remove();
                        $(this).removeClass('error');

                    });
                    errorsMessage = errorsMessage+"<li>"+$elements[key]+"</li>";
                }

                errorsMessage = errorsMessage+"</ul>";
                $('form .error-messages').removeClass('hidden').children('p').html(errorsMessage);

            },
        },
    };



    let NSAlertManager = {


        simple_alert: {

            set request(message) {

                let unique = Math.floor((1 + Math.random()) * 0x10000)
                    .toString(16)
                    .substring(1);

                $("#simple-alert-modal").attr("request",unique);

                $("#simple-alert-modal .message").html(message);
                $("#simple-alert-modal[request="+unique+"]").modal("show");

                $("#simple-alert-modal[request="+unique+"] #DONE").on("click",function () {
                    $("#simple-alert-modal").modal("hide");
                    return false;
                });


            },

        },

        alert: {

            set request(callback) {

                let unique = Math.floor((1 + Math.random()) * 0x10000)
                    .toString(16)
                    .substring(1);

                $("#fmodal-default").attr("request",unique);
                $("#fmodal-default[request="+unique+"]").modal("show");
                $("#fmodal-default[request="+unique+"] #apply_confirm").on("click",function () {

                    let selector = $(this);
                    callback(function (status,data,finish) {

                        if(status === "beforeSend"){
                            NSTemplateUIAnimation.button.loading = selector;
                        }else if(status === "error"){
                            NSTemplateUIAnimation.button.default = selector;
                        }else if(status === "success"){
                            NSTemplateUIAnimation.button.success = selector;
                            setTimeout(function () {
                                $("#fmodal-default").modal("hide");
                                NSTemplateUIAnimation.button.default = $("#fmodal-default #apply_confirm");
                                finish("ok");
                            },500);
                        }


                    });

                    $("#fmodal-default").attr("request","");

                    return false;
                });




            },

        },

    };


</script>