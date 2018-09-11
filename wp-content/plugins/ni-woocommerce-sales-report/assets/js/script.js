// JavaScript Document
jQuery(function($){
	//alert(ajaxurl);

   $("._datepicker").datepicker({ dateFormat: 'yy-mm-dd' });
  //  var currentDate = new Date();

    //$("._datepicker").datepicker("setDate", currentDate);
    $("._datepicker").datepicker("option", "showAnim", "blind");

    $("._datepicker").datepicker({
        changeMonth: true,
        changeYear: true,

    });

	$( "#frmOrderItem" ).submit(function( event ) {
			$(".ajax_content").html("Please wait..");
		$.ajax({
			url:ni_sales_report_ajax_object.ni_sales_report_ajaxurl,
			data: $(this).serialize(),
			success:function(data) {
				$(".ajax_content").html(data);
			},
			error: function(errorThrown){
				console.log(errorThrown);
				//alert("e");
			}
		}); 
		return false; 
	});
	
	
	$("#frmOrderItem").trigger("submit");
	
	$("#select_order").change(function(){
	  //alert("The text has been changed.");
	 // $("#frmOrderItem").trigger("submit");
	});
});