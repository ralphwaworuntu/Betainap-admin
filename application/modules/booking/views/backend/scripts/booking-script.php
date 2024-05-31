<script>
    $('#bookings-filter-result-tags a[data-name]').on('click',function (){
        let $name = $(this).attr('data-name');
        $('input[name='+$name+']').val('');
        $('select[data-name='+$name+']').html('');
        $('form#form-bookings-filter #_filter').trigger('click');
        return false;
    });
</script>