

(function ($) {

//"use strict";

    var popup_standard_view_id = 'standard_view_popup'; //Id of the pop-up content
    var popup_edit = 'standard_view_popup_edit';

    $('body').on('click', '.invitations-container a', function (e) {
        
        e.preventDefault();
        $(".af-inner_edit input").removeAttr("readonly");
       
        tb_show("", '#TB_inline?inlineId=' + popup_edit);
    });

    $('#basic_view').click(function () {
        $("#signer_logo").show();
        $("#signer_add").css("display", "block");
        $("#signer_save").css("display", "block");
        $(".af-inner input").removeAttr("readonly");
        tb_show("", '#TB_inline?width=450&height=300&inlineId=' + popup_standard_view_id);
    });


    $('#submit_signer_save').click(function () {

        // duplicate email then can not send email .
        if ($.fn.email_duplicate())
        {
            return false;
        }
        else
        {
            // saving removed any error msg 
            $('.esig-error-box').remove();
        }
        

        var esig_signer_fname = '';
        var esig_signer_email = '';
         var esig_cc_signer_fname = '';
        var esig_cc_signer_email = '';
        
        esig_signer_fname = $("input[name='recipient_fnames\\[\\]']").map(function () {
            return $(this).val();
        }).get();
        
        esig_signer_email = $("input[name='recipient_emails\\[\\]']").map(function () {
            return $(this).val();
        }).get();
        
        esig_cc_signer_fname = $("input[name='cc_recipient_fnames\\[\\]']").map(function () {
            return $(this).val();
        }).get();
        
        esig_cc_signer_email = $("input[name='cc_recipient_emails\\[\\]']").map(function () {
            return $(this).val();
        }).get();
       
        var esig_document_id = $('input[name="document_id"]');
        jQuery.ajax({
            type: "POST",
            url: documentAjax.ajaxurl,
            data: {
                recipient_fnames: esig_signer_fname,
                recipient_emails: esig_signer_email,
                cc_recipient_fnames: esig_cc_signer_fname,
                cc_recipient_emails: esig_cc_signer_email,
                document_id: esig_document_id.val(),
            },
            success: function (data, status, jqXHR) {
                //location.reload();
                $("#document-invitation").html(data);
                tb_remove();
            },
            error: function (xhr, status, error) {
                alert(xhr.responseText);
            }
        });
        return false;
    });

    $("#TB_closeWindowButton").on("click", function (e) {
        //alert('test sdfdf');
    });

    $("#addRecipient").on("click", function (e) {
        e.preventDefault();
        var slv='' ;
        if(esign.is_slv_active()){
           slv ='<span id="second_layer_verification" class="icon-doorkey second-layer" ></span>';
        }
        $("#recipient_emails").append('<div id="signer_main">' +
                '<input type="text" name="recipient_fnames[]" placeholder="Signers Name"  />' +
                '<input type="text" name="recipient_emails[]" placeholder="email@address.com" style="width:213px;"  value="" />'+ slv +'<span id="esig-del-signer" class="deleteIcon"></span></div>').trigger("contentchange");
    });



    $("#addRecipient_view").on("click", function (e) {
        e.preventDefault();
        var slv='' ;
        if(esign.is_slv_active()){
           slv ='<span id="second_layer_verification" class="icon-doorkey second-layer" ></span>';
        }
        $("#recipient_emails").append('<div id="signer_main">' +
                '<input type="text" name="recipient_fnames[]" placeholder="Signers Name"  />' +
                '<input type="text" name="recipient_emails[]" placeholder="email@address.com" style="width:213px;"  value="" />'+ slv +'<span id="esig-del-signer" class="deleteIcon"></span></div>').trigger("contentchange");

    });


    // email validation checking on basic document add view . 

    $.fn.email_duplicate = function () {

        var view_email = $("#recipient_emails input[name='recipient_emails\\[\\]']").map(function () {
            return $(this).val();
        });

        var view_fname = $("#recipient_emails input[name='recipient_fnames\\[\\]']").map(function () {
            return $(this).val();
        });

        var sorted_email = view_email.sort();
        // getting new array 
        var exists = false;
        var blank = false;
        var blank_email = false;
        
        if(view_email.length === 0 || view_fname.length === 0){
              $('.esig-error-box').remove();
              $('.af-inner').append('<span class="esig-error-box">*You must add at least one signer.</span>');
            return true;
        }
        
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
                $('.af-inner').append('<span class="esig-error-box">*You must fill the signer name.</span>');
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
                $('.af-inner').append('<span class="esig-error-box">*You must fill email address.</span>');
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

            $('.af-inner').append('<span class="esig-error-box">*You can not use duplicate email address.</span>');

            return true;
        }
        else
        {
            $('.esig-error-box').remove();
            return false;
        }

    }

    // when view input field focus out 
    $('body').on('focusout', "#recipient_emails input[name='recipient_emails\\[\\]']", function () {

        

        // checkaing if signer only one then hide signer order checkbox 
        $('.esig-error-box').remove();
        $.fn.email_duplicate();


    });

    // view basi signer add submit form . 
    $("#esig-view-form").on("submit", function (e) {

        // validation for same email address . 
        if ($.fn.email_duplicate())
        {
            return false;
        }

        return true;
    });



    $('body').on('click', '#recipient_emails .deleteIcon', function (e) {
       
        $('.esig-error-box').remove();
        // checking if signer only one then hide signer order checkbox 
        
        $(this).parent().remove();

        e.preventDefault();
        $(this).remove();
    });

    $("#esignadvanced").on("click", function (e) {
        e.preventDefault();
        $("#esignadvanced").hide();
        $("#esignadvanced-hide").show();
        $("#advanced-settings").show();
    });

    $("#esignadvanced-hide").on("click", function (e) {
        e.preventDefault();
        $("#esignadvanced-hide").hide();
        $("#esignadvanced").show();
        $("#advanced-settings").hide();
    });


    /* outside append field deled event */
    $('.minus-recipient').on('click', function (e) {
        e.preventDefault();
        $(this).parent().remove();
    });
    // Bindings

    /*
     -- Validation --
     Required fields:
     - Title
     - Document Content
     
     if ( SEND ) :
     - At least one email invite
     */


    // Bind the Submit type before submission
    var submit_type;
    $("#submit_send").on("click", function () {
        submit_type = 'send';
    });

    $("#submit_save").on("click", function () {
        submit_type = 'save';
    });


    function esig_get_tinymce_content() {
        if ($("#wp-document_content-wrap").hasClass("tmce-active")) {
            return tinyMCE.activeEditor.getContent();
        } else {
            return $('#document_content').val();
        }
    }


    // Bind the Submission
    $("#document_form").on("submit", function (e) {

        //e.preventDefault();	
        var valid = true;

        if (this['document_title'].value == "") {
            valid = false;
        }
        else if (!esig_get_tinymce_content()) {

            valid = false;
        }
        
        if (typeof access_role_validation !== 'undefined') {
            
            if(access_role_validation()){
               valid = false;
           }   
        }

        /** 
         Going to skip document content validation for now.. 
         theres a delay with ck editor in adding the content to the dom
         
         if($("#document_content").val() == ""){
         alert("content appears to be empty");
         var doc_content = document.getElementById("document_content").value;
         alert(doc_content);
         }
         */

        // If sending validate that at least one recipient is present
       // var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
        var recips = [];
        $("#document_form input").each(function (index) {

            if (/recipient/.test(this.name)) {
                //alert("recipeints found with value of: " + this.value);
                if (this.value != "") {
                    if (esign.is_valid_email(this.value)) {
                        recips.push(this.value);
                    }
                }
            }
        });

        if (recips.length < 1 && submit_type == 'send') {
            valid = false;
        }

        if (!valid) {
            window.scrollTo(0, 0);

            if ($(".error").html() == undefined) {

                if (!esig_get_tinymce_content())
                {
                    var alertmsg = '<div id="esig-doc-error" class="error"><p>Hey there! It looks like you\'re trying to send a document that does not yet have content. You must first add content in your document in order to send it.</p></div>';
                }
                if (this['document_title'].value == "")
                {
                    var alertmsg = '<div id="esig-doc-error" class="error"><p><strong>Document Error</strong> : All setting fields are required</p></div>';
                }
                $(this).prepend(alertmsg);
            }
            return false;
        } else {

            //showing loader 

            document.getElementById('page-loader-admin').style.display = 'block';
            var overlay = $('<div class="page-loader-overlay"></div>').appendTo('body');
            $(overlay).show();
       
          
            $("#document_action").val(submit_type);

            $(".submit").attr("disabled", true);
            return true;
        }
    });

    $(".cls_tr").on("hover", function () {
        $(this).find(".manage-options").toggle();
    });

    $("#advanced-settings").hide();


    $(".esigactive").click(function () {
        $('.esigactiveinside').toggle(400);
        return false;
    });

    $(".urlredirect").click(function () {
        $('.urlredirectbody').toggle(400);
        return false;
    });

    // error dialog popup 
    $("#esig_show_alert").dialog({
        'dialogClass': 'wp-dialog esig-error-dialog',
        'title': 'Whoah there',
        modal: true,
        buttons: {
            Close: function () {
                $(this).dialog("close");
            }
        }
    });



    // adding E-signature menu active when add document page . 
    if ($('.toplevel_page_esign-docs').hasClass("wp-not-current-submenu")) {
        $('.toplevel_page_esign-docs')
                .removeClass('wp-not-current-submenu')
                .addClass('wp-has-current-submenu')
                .find('li').has('a[href*="admin.php?page=esign-view-document"]')
                .addClass('current');
    }


    //document page tooltip 
    $(".esig-documents-list").tooltip({position: {
            my: "center bottom-30",
            at: "top center",
            using: function (position, feedback) {
                $(this).css(position);
                $("<div>")
                        .addClass("esign-arrow")
                        .addClass(feedback.vertical)
                        .addClass(feedback.horizontal)
                        .appendTo(this);
            }
        }
    });

    // esig document title event change 
    $('#document-title').focus(function () {

        $('#esig-doc-error').remove();
    });

    $('#document_content').focus(function () {

        $('#esig-doc-error').remove();
    });

    $('#tinymce').focus(function () {

        $('#esig-doc-error').remove();
    });


})(jQuery);




