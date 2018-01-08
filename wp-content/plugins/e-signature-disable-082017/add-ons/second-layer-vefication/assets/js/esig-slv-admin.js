(function ($) {

    // $('#tabs-access-code').smartTab({autoProgress: false, stopOnFocus: true, transitionEffect: 'fade in'});

    $("#access_cssmenu a").click(function (event) {
        event.preventDefault();
        
        // $(this).parent().siblings().removeClass("current");
        $("#access_cssmenu a").removeClass("current");
        $(this).addClass("current");
        var tab = $(this).attr("href");
        $(".tab-content").not(tab).css("display", "none");
        $(tab).fadeIn();
    });


    $('body').on('click', '#recipient_emails .second-layer', function () {

        // select default none in dialog. 
        if(!$("#access_cssmenu a").hasClass("current")){
            $("#esig-access-none").addClass("current");
        }
        
        
        var email_address = $(this).parent().find('input[name="recipient_emails\\[\\]"]').val();
        if (esig_validation.is_email(email_address)) {

            // passing email address to dialog 
            $("#esig-slv-email-address").val(email_address);
            //show dialog
            $('#esig_access_code').val("");
            $("#esig-access-code-verification").dialog({
                dialogClass: 'esig-access-dialog',
                height: 400,
                width: 495,
                modal: true,
            });

        } else {
            $('.esig-error-box').remove();
            $('.af-inner').append('<span class="esig-error-box">*You must fill e-mail address field.</span>');
        }

    });
    
    $('body').on('click', '#recipient_emails .edit-second-layer', function () {

        // select default none in dialog. 
        $("#esig-access-none").addClass("current");
        var email_address = $(this).parent().find('input[name="recipient_emails\\[\\]"]').val();
        if (esig_validation.is_email(email_address)) {

            // passing email address to dialog 
            $("#esig-slv-email-address").val(email_address);
            //show dialog
            $("#esig-access-code-verification").dialog({
                dialogClass: 'esig-access-dialog',
                height: 400,
                width: 500,
                modal: true,
            });

        } else {
            $('.esig-error-box').remove();
            $('.af-inner').append('<span class="esig-error-box">*You must fill e-mail address field.</span>');
        }

    });




    $("#submit_send_layer").click(function (e) {

        e.preventDefault();
        var email_address = $("#esig-slv-email-address").val();
        var access_security_code = $("#access_code").find('input[name="esig_access_code"]').val(); //get the value..
        slv_meta_save(email_address, access_security_code);

        $("#esig-access-code-verification").dialog("close");


    });

    ////cancel second layer verification
    $("#submit_send_cancel").click(function () {

        $("#esig-access-code-verification").dialog("close");

    });

    ///second layer for temp
    $('body').on('click', '#standard_view_popup_bottom .second-layer', function () {


        // select default none in dialog. 
        if(!$("#access_cssmenu a").hasClass("current")){
            $("#esig-access-none").addClass("current");
        }
        
        var email_address = $('#standard_view_popup_bottom').parent().find('input[name="recipient_emails\\[\\]"]').val();
        if (esig_validation.is_email(email_address)) {

            // passing email address to dialog 
            $("#esig-slv-email-address").val(email_address);
            $('#esig_access_code').val("");
            //show dialog
            $("#esig-access-code-verification").dialog({
                dialogClass: 'esig-access-dialog',
                height: 400,
                width: 495,
                modal: true,
            });

        } else {
            $('.esig-error-box').remove();
            $('.af-inner').append('<span class="esig-error-box">*You must fill e-mail address field.</span>');
        }

    });



    $("#access_code_login").click(function (e) {
        e.preventDefault();
       var access_security_code = $("#access_code").find('input[name="esig_access_code"]').val(); //get the value..


        $.post(esigAjax.ajaxurl + "?action=esig_access_code_verification", {access_code: access_security_code}).done(function (data) {
            // alert("Data Loaded: " + data);
            if (data == "success") {
                $("#esig-access-code-verification").dialog("close");
            } else {

                alert('dfbhdchfb');
            }
        });
    });


    // for sad document 
    // Show or hide the stand alone console when the box is checked.
    $('input[name="esig_second_layer_verification"]').on('change', function () {
        if ($('input[name="esig_second_layer_verification"]').attr('checked')) {
            //show dialog
             // passing email address to dialog 
            $("#esig-slv-email-address").val('stand-alone');
            
            $("#esig-access-code-verification").dialog({
                dialogClass: 'esig-access-dialog',
                height: 400,
                width: 510,
                modal: true,
            });
           
        } 
    });



})(jQuery);

/**
 *  slv meta saving. 
 */
function slv_meta_save(email, access_code) {

    var slv_settings = JSON.parse(esign.getCookie("slv-settings"));
    if (slv_settings) {
        slv_settings[email] = access_code;
        esign.setCookie("esig-slv-settings", JSON.stringify(slv_settings), 12 * 60 * 60);
    } else {

        var slv_settings = {};
        slv_settings[email] = access_code;
        esign.setCookie("esig-slv-settings", JSON.stringify(slv_settings), 12 * 60 * 60);
    }
}
