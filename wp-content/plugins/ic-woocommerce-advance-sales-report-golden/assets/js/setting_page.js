jQuery(document).ready(function($) {	
	var custom_uploader;
	var upload_this = null;
	
	$('a.ic_upload_button').click(function(e) {
		upload_this = $(this);
		e.preventDefault();
		//If the uploader object has already been created, reopen the dialog
		if (custom_uploader) {
			custom_uploader.open();
			return;
		}
		//Extend the wp.media object
		custom_uploader = wp.media.frames.file_frame = wp.media({
			title: 'Choose File',
			//frame: 'post',
			button: {
				text: 'Choose File'
			},
			multiple: false
		});
		//When a file is selected, grab the URL and set it as the text field's value
		custom_uploader.on('select', function() {
			attachment = custom_uploader.state().get('selection').first().toJSON();
			upload_this.parent().find('input[type=text].upload_field').val(attachment.url);
		});
		//Open the uploader dialog
		custom_uploader.open();
	});
	
	$('.clear_textbox').click(function(){
		$(this).parent().find('input[type=text]').val('');
	});
	
	jQuery( "#default_date_rage_start_date" ).datepicker({
		dateFormat : 'yy-mm-dd',		
		changeMonth: true,
		changeYear: true,
		maxDate:0,
		onClose: function( selectedDate ) {
			$( "#default_date_rage_end_date" ).datepicker( "option", "minDate", selectedDate );
		}
	});							
	
	jQuery( "#default_date_rage_end_date" ).datepicker({
		dateFormat : 'yy-mm-dd',
		changeMonth: true,
		changeYear: true,
		///maxDate: 0,
		onClose: function( selectedDate ) {
			$( "#default_date_rage_start_date" ).datepicker( "option", "maxDate", selectedDate );
		}
	}); 
	
	
	$(".ic_close_button").click(function(){
		$('.ic_close_button').hide();
		$("#ic_please_wait").hide();
		$("#ic_please_wait").removeClass('ic_please_wait');
		
		ajax_on_processing = false;
		
	});
	
	var ajax_on_processing = false;
	
	$('#email_report_actions_order_status_mail').click(function(){
		
		if(ajax_on_processing) return false;
		
		var action_data = {
            "action"			: ic_ajax_object.ic_ajax_action,
            "do_action_type"	: "order_status_email"
        }
		
		$('.ic_close_button').hide();
		$(".ic_please_wait_msg").html("Please Wait");
		$("#ic_please_wait").fadeIn();
		$("#ic_please_wait").addClass('ic_please_wait');
		
		ajax_on_processing = true;
		
		$.ajax({
			type: "POST",
			url: ic_ajax_object.ajaxurl,
			data:  action_data,
			success:function(data) {								
				$('.ic_close_button').fadeIn();
				$(".ic_please_wait_msg").html(data);				
				$(".email_report_actions_order_status_mail").attr('disabled',false).removeClass('disabled');
			},
			error: function(jqxhr, textStatus, error ){
				alert(jqxhr + "  - " + textStatus + "  - " + error + " --- " + jqxhr.responseText);
				$("#ic_please_wait").hide();
				$("#ic_please_wait").removeClass('ic_please_wait');
				$(".email_report_actions_order_status_mail").attr('disabled',false).removeClass('disabled');
				
				ajax_on_processing = false;
			}
		});
		
		return '';
	});
	
	$("#graph_setting_action_reset").click(function(event) {
		var r = confirm("Do you want to reset Graph Settings? \nPress \"OK\" for reset. \nUpon reset please click \"Save Changes\" button.");
		if (r == true) {
			$("#graph_height").val(300);
			$("#tick_angle").val(0);
			$("#tick_font_size").val(9);
			$("#tick_char_length").val(15);
			$("#tick_char_suffix").val("...");
			$("#graph_setting_action_reset").hide();
		}
	});
});