(function ($) {


    $("#add-esig-cc").on("click", function (e) {
        e.preventDefault();
         
        $(".cc_recipient_emails_container #cc_recipient_emails").append('<div id="signer_main">' +
                '<input type="text" name="cc_recipient_fnames[]" placeholder="CC Users Name" style="height:35px;" />' +
                '<input type="text" name="cc_recipient_emails[]" placeholder="email@address.com" style="width:213px;height:35px;"  value="" /><span id="esig-del-signer" class="deleteIcon" style="position:absolute;left:400px;"></div>').trigger("contentchange");
       
    });
    
    $("#cc_users_edit").on("click", function (e) {
        e.preventDefault();

        $("#cc_recipient_emails").append('<div id="signer_main" style="position:relative; left:6px;">' +
                '<input type="text" name="cc_recipient_fnames[]" placeholder="CC Users Name" style="height:35px;" />' +
                '<input type="text" name="cc_recipient_emails[]" placeholder="email@address.com" style="width:214px;height:35px;"  value="" /><span id="esig-del-signer" class="deleteIcon" style="position:absolute;left:400px;"></div>').trigger("contentchange");
    });


    $("#add_cc_temp").on("click", function (e) {
        e.preventDefault();
         
        $(".invitations-container .cc_recipient_emails").append('<div id="cc-signer_main" class="cc-invitation-email">' +
                '<input type="text" name="cc_recipient_fnames[]" placeholder="CC Users Name" style="height:35px;" />' +
                '<input type="text" name="cc_recipient_emails[]" placeholder="email@address.com" style="width:213px;height:35px;"  value="" /><span id="esig-del-signer" class="deleteIcon" style="position:absolute;left:400px;"></div>').trigger("contentchange");
       
    });

 
    $('#submit_send').click(function () {


        // validation for same email address . 
        if ($.fn.cc_users_email_duplicate())
        {
           return false;
        }
        else
        {
            // saving removed any error msg 
            $('.esig-error-box').remove();
        }

        var esig_cc_recipient_fnames = '';
        var esig_cc_recipient_emails = '';

        esig_cc_recipient_fnames = $("input[name='cc_recipient_fnames\\[\\]']").map(function () {
            return $(this).val();
        }).get();
        esig_cc_recipient_emails = $("input[name='cc_recipient_emails\\[\\]']").map(function () {
            return $(this).val();
        }).get();

        var esig_document_id = $('input[name="document_id"]');

        var data = {
            'cc_recipient_fnames': esig_cc_recipient_fnames,
            'cc_recipient_emails': esig_cc_recipient_emails,
            'document_id': esig_document_id.val(),
        };

        $.post(esigAjax.ajaxurl + "?action=esig_cc_user_information", data).done(function (response) {


        });
       //return false;

    });
    
    // temp cc users 
      $("#standard_view_popup_bottom #submit_insert").on("click", function (e) {
   // $('#template_insert').click(function () {


        // validation for same email address . 
        if ($.fn.cc_users_email_duplicate())
        {
           return false;
        }
        else
        {
            // saving removed any error msg 
            $('.esig-error-box').remove();
        }

        var esig_cc_recipient_fnames = '';
        var esig_cc_recipient_emails = '';

        esig_cc_recipient_fnames = $(".invitations-container input[name='cc_recipient_fnames\\[\\]']").map(function () {
            return $(this).val();
        }).get();
        esig_cc_recipient_emails = $(".invitations-container input[name='cc_recipient_emails\\[\\]']").map(function () {
            return $(this).val();
        }).get();

        var esig_document_id = $('input[name="document_id"]');

        var data = {
            'cc_recipient_fnames': esig_cc_recipient_fnames,
            'cc_recipient_emails': esig_cc_recipient_emails,
            'document_id': esig_document_id.val(),
        };

        $.post(esigAjax.ajaxurl + "?action=esig_cc_user_information", data).done(function (response) {


        });
       //return false;

    });
    
    
    


    // email validation checking on basic document add view . 

    $.fn.cc_users_email_duplicate = function () {

        var view_email = $("#cc_recipient_emails input[name='cc_recipient_emails\\[\\]']").map(function () {
            return $(this).val();
        }).get();

        var view_fname = $("#cc_recipient_emails input[name='cc_recipient_fnames\\[\\]']").map(function () {
            return $(this).val();
        }).get();

        var sorted_email = view_email.sort();

        // getting new array 
        var exists = false;
        var blank = false;
        var blank_email = false;
        // if blank signer name is input 
        for (var i = 0; i < view_fname.length; i++) {

            if (view_fname[i] == undefined || view_fname[i] == '')
            {

                blank = true;
            }

            var re = /<(.*)>/
            if (re.test(view_fname[i]))
            {
                blank = true;
            }

            if (blank)
            {

                $('.esig-error-box').remove();
                $('#error').append('<span class="esig-error-box">*You must fill the CC user name.</span>');
                return true;
            }
        }
        // if blank email address is input 
        for (var i = 0; i < view_email.length; i++) {

            if (view_email[i] == undefined || view_email[i] == '')
            {

                blank_email = true;
            }

           
            if (!esign.is_valid_email(view_email[i]))
            {
                blank_email = true;
            }
            if (blank_email)
            {
                // remove previous error msg 
                $('.esig-error-box').remove();
                // add new error msg 
                $('#error').append('<span class="esig-error-box">*You must fill email address.</span>');
                return true;
            }
        }


        for (var i = 0; i < view_email.length - 1; i++) {

            if (sorted_email[i + 1].toLowerCase() == sorted_email[i].toLowerCase())
            {
                exists = true;
            }
        }

        if (exists)
        {

            $('.esig-error-box').remove();

            $('#error').append('<span class="esig-error-box"> *You can not use duplicate email address.</span>');

            return true;
        }
        else
        {
            
            $('.esig-error-box').remove();
            return false;
        }

    }

    $('body').on('click', '#cc_recipient_emails .deleteIcon', function (e) {

        // checking if signer only one then hide signer order checkbox 

        $(this).parent().remove();

        e.preventDefault();
        
        $(this).remove();
    });
    
    
  


})(jQuery);
