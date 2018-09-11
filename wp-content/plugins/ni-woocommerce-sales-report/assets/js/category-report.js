// JavaScript Document
jQuery(function($){
	$( "#frmCategoryReport" ).submit(function( event ) {
		event.preventDefault();
		
		$.ajax({
			//url: ajaxurl,
			url:ni_sales_report_ajax_object.ni_sales_report_ajaxurl,
			//data:$("#frmOrderItem").serialize(),
			//data: "{action:'my_action',sub_action:'order_item',select_order:'"+ $("#select_order").val() +"'}",
			data: $(this).serialize(),
			success:function(data) {
				// This outputs the result of the ajax request
				//console.log(data);
				//alert(data);
				//alert(JSON.stringify(data));
				$(".ajax_content").html(data);
			},
			error: function(errorThrown){
				console.log(errorThrown);
				//alert("e");
			}
		
		});
		
	});
	$("#frmCategoryReport").trigger("submit");
});