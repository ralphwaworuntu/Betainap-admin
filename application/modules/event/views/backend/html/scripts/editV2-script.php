<script src="https://cdn.jsdelivr.net/npm/smartwizard@6/dist/js/jquery.smartWizard.min.js"></script>
<script src="<?= adminAssets("plugins/datepicker/bootstrap-datepicker.js") ?>"></script>

<script>
    let datepickerSelector = $('input[data-provide=datepicker]');
    $.fn.datepicker.defaults.format = datepickerSelector.attr('data-default');
    datepickerSelector.datepicker({
        startDate: '-3d'
    });
</script>

<script>

    $('form .select2').select2();

    let smartwizard = $('#smartwizard');

    smartwizard.smartWizard({
        selected: 0, // Initial selected step, 0 = first step
        theme: 'dots', // theme for the wizard, related css need to include for other than default theme
        justified: true, // Nav menu justification. true/false
        autoAdjustHeight: false, // Automatically adjust content height
        backButtonSupport: true, // Enable the back button support
        enableUrlHash: true, // Enable selection of the step based on url hash
        transition: {
            animation: 'none', // Animation effect on navigation, none|fade|slideHorizontal|slideVertical|slideSwing|css(Animation CSS class also need to specify)
            speed: '400', // Animation speed. Not used if animation is 'css'
            easing: '', // Animation easing. Not supported without a jQuery easing plugin. Not used if animation is 'css'
            prefixCss: '', // Only used if animation is 'css'. Animation CSS prefix
            fwdShowCss: '', // Only used if animation is 'css'. Step show Animation CSS on forward direction
            fwdHideCss: '', // Only used if animation is 'css'. Step hide Animation CSS on forward direction
            bckShowCss: '', // Only used if animation is 'css'. Step show Animation CSS on backward direction
            bckHideCss: '', // Only used if animation is 'css'. Step hide Animation CSS on backward direction
        },
        toolbar: {
            position: 'bottom', // none|top|bottom|both
            showNextButton: true, // show/hide a Next button
            showPreviousButton: true, // show/hide a Previous button
            extraHtml: "<?=SessionManager::getData("id_user")!=$event['user_id']?'<p class=\"text-red\">You don\'t have permission to edit the content, the only owner can do this.</p>':''?>" // Extra html to show on toolbar
        },
        anchor: {
            enableNavigation: true, // Enable/Disable anchor navigation
            enableNavigationAlways: false, // Activates all anchors clickable always
            enableDoneState: true, // Add done state on visited steps
            markPreviousStepsAsDone: true, // When a step selected by url hash, all previous steps are marked done
            unDoneOnBackNavigation: false, // While navigate back, done state will be cleared
            enableDoneStateNavigation: true // Enable/Disable the done state navigation
        },
        keyboard: {
            keyNavigation: true, // Enable/Disable keyboard navigation(left and right keys are used if enabled)
            keyLeft: [37], // Left key code
            keyRight: [39] // Right key code
        },
        lang: { // Language variables for button
            next: '<?=_lang("Next")?>',
            previous: '<?=_lang("Previous")?>'
        },
        disabledSteps: [], // Array Steps disabled
        errorSteps: [], // Array Steps error
        warningSteps: [], // Array Steps warning
        hiddenSteps: [], // Hidden steps
        getContent: provideContent,
        style: { // CSS Class settings
            mainCss: 'sw',
            navCss: 'nav',
            navLinkCss: 'nav-link',
            contentCss: 'tab-content',
            contentPanelCss: 'tab-pane',
            themePrefixCss: 'sw-theme-',
            anchorDefaultCss: 'default',
            anchorDoneCss: 'done',
            anchorActiveCss: 'active',
            anchorDisabledCss: 'disabled',
            anchorHiddenCss: 'hidden',
            anchorErrorCss: 'error',
            anchorWarningCss: 'warning',
            justifiedCss: 'sw-justified',
            btnCss: 'btn-primary',
            btnNextCss: 'sw-btn-next',
            btnPrevCss: 'sw-btn-prev',
            loaderCss: 'sw-loading',
            progressCss: 'progress',
            progressBarCss: 'progress-bar',
            toolbarCss: 'toolbar box-footer',
            toolbarPrefixCss: 'toolbar-',
        }
    });


    // Function to fetch the ajax content
    function provideContent(idx, stepDirection, stepPosition, selStep, callback) {

        if (idx===4) {

            $.ajax({
                url: "<?=  site_url("ajax/event/edit")?>",
                data: {
                    'event_id':      $("form #event_id").val(),
                    'booking':      $("form input[name=booking]:checked").val(),
                    'price':        $("form #price_without_commission").val(),
                    'store_id':     $("form #store_id").val(),
                    'name':         $("form #name").val(),
                    'description':  $("form #description").val(),
                    'website':      $("form #website").val(),
                    'date_b':       $("form #date_b").val(),
                    'date_e':       $("form #date_e").val(),
                    'address':      $("#form #<?=$location_fields_id['address']?>").val(),
                    'lat':          $("#form #<?=$location_fields_id['lat']?>").val(),
                    'lng':          $("#form #<?=$location_fields_id['lng']?>").val(),
                    'tel':          $("form #tel").val(),
                    'telCode':          $("form #telCode").val(),
                    'images':       JSON.stringify(<?=$uploader_variable?>)
                },
                dataType: 'json',
                type: 'POST',
                beforeSend: function (xhr) {

                    $('#smartwizard').smartWizard("loader", "show");

                }, error: function (request, status, error) {

                    smartwizard.smartWizard("loader", "hide");
                    console.log(request);

                },
                success: function (data, textStatus, jqXHR) {

                    console.log(data);
                    // Hide the loader
                    smartwizard.smartWizard("loader", "hide");

                    if (data.success === 1) {
                        callback();
                    } else if (data.success === 0) {
                        smartwizard.smartWizard("goToStep", 0, true);
                        templateUtils.fieldErrors.errors = data.errors;
                    }

                }
            });

        }else{
            callback();
        }

    }


    $('#options input[type=radio]').on('click',function (){
        let val = parseInt($(this).val());
        if(val===1){
            $('#options .event-booking-price').removeClass('hidden');
        }else{
            $('#options .event-booking-price').addClass('hidden');
        }
    });

    $("#price_without_commission").on('keyup',function () {
        let commission  = parseFloat($("#commission").val()) / 100;
        let price  = parseFloat($(this).val());
        let calculated  = (price * commission)+price;
        $("#price").val( calculated );
        return false;
    });

</script>

<style>
    :root {
        --sw-border-color:  #eeeeee;
        --sw-toolbar-btn-color:  #ffffff;
        --sw-toolbar-btn-background-color:  <?=ConfigManager::getValue("DASHBOARD_COLOR")?>;
        --sw-anchor-default-primary-color:  #f8f9fa;
        --sw-anchor-default-secondary-color:  #b0b0b1;
        --sw-anchor-active-primary-color:  <?=ConfigManager::getValue("DASHBOARD_COLOR")?>;
        --sw-anchor-active-secondary-color:  #ffffff;
        --sw-anchor-done-primary-color:  <?=ConfigManager::getValue("DASHBOARD_COLOR")?>;
        --sw-anchor-done-secondary-color:  #fefefe;
        --sw-anchor-disabled-primary-color:  #f8f9fa;
        --sw-anchor-disabled-secondary-color:  #dbe0e5;
        --sw-anchor-error-primary-color:  #dc3545;
        --sw-anchor-error-secondary-color:  #ffffff;
        --sw-anchor-warning-primary-color:  #ffc107;
        --sw-anchor-warning-secondary-color:  #ffffff;
        --sw-progress-color:  <?=ConfigManager::getValue("DASHBOARD_COLOR")?>;
        --sw-progress-background-color:  #f8f9fa;
        --sw-loader-color:  <?=ConfigManager::getValue("DASHBOARD_COLOR")?>;
        --sw-loader-background-color:  #f8f9fa;
        --sw-loader-background-wrapper-color:  rgba(255, 255, 255, 0.7);
        --primary-color: <?=ConfigManager::getValue("DASHBOARD_COLOR")?>;
    }
    <style/>