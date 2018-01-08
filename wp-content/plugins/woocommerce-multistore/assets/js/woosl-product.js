

    jQuery(document).ready(function()
        {
            
            jQuery('#woonet_toggle_all_sites').change(function() {
                if(jQuery(this).is(":checked")) {
                    
                    jQuery('#woonet_data input[type="checkbox"]._woonet_publish_to').each(function() {
                        if(jQuery(this).prop('disabled')    ==  false)
                            {
                                jQuery(this).attr('checked', 'checked');
                                woonet_publsih_to_site_checkbox(jQuery(this));
                            }    
                    })
     
                }
                else {
                    jQuery('#woonet_data input[type="checkbox"]._woonet_publish_to').each(function() {
                        if(jQuery(this).prop('disabled')    ==  false)
                            {
                                jQuery(this).attr('checked', false);
                                woonet_publsih_to_site_checkbox(jQuery(this));
                            }    
                    })     
                }
                    
            });
            
            
            jQuery('#woonet_toggle_child_product_inherit_updates').change(function() {
                if(jQuery(this).is(":checked")) {
                    
                    jQuery('#woonet_data input[type="checkbox"]._woonet_publish_to_child_inheir').each(function() {
                        if(jQuery(this).prop('disabled')    ==  false)
                            {
                                jQuery(this).attr('checked', 'checked');
                            }    
                    })
     
                }
                else {
                    jQuery('#woonet_data input[type="checkbox"]._woonet_publish_to_child_inheir').each(function() {
                        if(jQuery(this).prop('disabled')    ==  false)
                            {
                                jQuery(this).attr('checked', false);
                            }    
                    })     
                }
                    
            });
            
            
            
            jQuery('#woonet_data input[type="checkbox"]._woonet_publish_to').change(function() {
                        woonet_publsih_to_site_checkbox(jQuery(this));
                    })

    });
    
    
    function woonet_publsih_to_site_checkbox(element)
        {
            
            var group_id    =   jQuery(element).closest('p.form-field').attr('data-group-id');
            
            if(jQuery(element).is(":checked")) {
                    jQuery('#woonet_data').find('.form-field.group_' + group_id).slideDown();
                    jQuery(element).closest('p.form-field').find('.description .warning').slideUp();
                }
                else {
                    jQuery('#woonet_data').find('.form-field.group_' + group_id).slideUp();
                    
                    if(jQuery(element).attr('data-default-value')   !=  '')
                        jQuery(element).closest('p.form-field').find('.description .warning').slideDown();
                }   
            
        }