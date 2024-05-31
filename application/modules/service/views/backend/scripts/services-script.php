<script src="<?= adminAssets("plugins/select2/select2.full.min.js") ?>"></script>

<script>

    $('#storesSelector').select2().on('change',function (){
        let id = $(this).val();
        document.location.href = $('#form-stores-selector').attr('action')+"?store_id="+id;
    });

</script>


