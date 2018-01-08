jQuery(document).ready(function(){
	wf_fed_ex_load_packing_method_options();
	jQuery('.packing_method').change(function(){
		wf_fed_ex_load_packing_method_options();
	});
});

function wf_fed_ex_load_packing_method_options(){
	pack_method	=	jQuery('.packing_method').val();
	jQuery('#packing_options').hide();
	jQuery('.weight_based_option').closest('tr').hide();
	switch(pack_method){
		case 'per_item':
		default:
			break;
			
		case 'box_packing':
			jQuery('#packing_options').show();
			break;
			
		case 'weight_based':
			jQuery('.weight_based_option').closest('tr').show();
			break;
	}
}