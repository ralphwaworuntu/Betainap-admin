<script src="<?= adminAssets("plugins/datepicker/bootstrap-datepicker.js") ?>"></script>

<script>


    // Select2
    $(".select2").select2();

    $.fn.datepicker.defaults.format = "yyyy-mm-dd";
    $('.datepicker').datepicker({

    });

    $('#select_owner').select2({

        ajax: {
            url: "<?=site_url("ajax/user/getOwners")?>",
            dataType: "json",
            data: function (params) {

                var query = {
                    q: params.term,
                };

                // Query parameters will be ?search=[term]&type=public
                return query;
            },
            processResults: function (data) {
                // Tranforms the top-level key of the response object from 'items' to 'results'
                console.log(data);
                return {
                    results: data
                };
            },
            results: function (data, page) {
                console.log(data);

                return {results: data};
            }
        }
    });



    $("form#form-bookings-filter [data-dismiss]").on('click',function () {
        $('#modal-default-filter').modal('hide');
        return false;
    });

    $('#bookings-filter-result-tags a[data-name]').on('click',function (){
        let $name = $(this).attr('data-name');
        $('input[name='+$name+']').val('');
        $('select[data-name='+$name+']').html('');
        $('form#form-bookings-filter #_filter').trigger('click');
        return false;
    });

    $("form#form-bookings-filter #_filter").on('click', function () {

        $( "form#form-bookings-filter [data-name]").each(function( index ) {

            let $dataId = $(this).attr('data-name');
            let value = $(this).val();

            console.log($dataId);
            console.log(value);
            console.log('input[name='+$dataId+']');

            if(value!==undefined && value !== null){
                $('form#form-bookings-filter input[name='+$dataId+']').val(value.join());
            }
        }).promise().done( function(){
            $('#modal-default-filter').modal('hide');
            $("form#form-bookings-filter").submit();
        } );
        return false;
    });


</script>