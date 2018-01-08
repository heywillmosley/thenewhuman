

    var woosl_setup = {
        sites   :   '',
        sites_current_key    :   -1,
        
        current_site_data           :   {},
        current_site_process_list   :   {},
        current_site_process_list_page  :   '',
        
        process_batch_items_count   :   50,
        
        start_process    :  function(sites_using_woocommerce) {
            
            woosl_setup.sites  =   sites_using_woocommerce;    
            woosl_setup.process_sites();
        },
        
        process_sites   :   function() {
            
            if(woosl_setup.sites.length < 1)
                {
                    woosl_setup.process_log('There are no sites to process');
                    woosl_setup.process_completed();
                    return;
                }
            
            if( woosl_setup.sites_current_key  < 0)
                woosl_setup.sites_current_key  =   0;
                else
                woosl_setup.sites_current_key++;
                
            //check if the key exists
            if(typeof woosl_setup.sites[ woosl_setup.sites_current_key  ] === 'undefined') 
                {
                    woosl_setup.process_log('Updating complete.');
                    woosl_setup.process_completed();
                    return;
                }
            
            woosl_setup.process_site();
        
        },
        
        process_site    :   function() {
            
            woosl_setup.current_site_data = woosl_setup.sites[ woosl_setup.sites_current_key  ];
            
            woosl_setup.process_log('Retrieving list for ' + woosl_setup.current_site_data.blogname);
            woosl_setup.show_progress_bar();
            woosl_setup.update_progress_bar(1);
                        
            woosl_setup.get_site_process_list();    
            
        },
        
        get_site_process_list   :   function() {
            
            var queryString = { "action": "woosl_setup_get_process_list", "site_id" : woosl_setup.current_site_data.id};
            
            //send the data through ajax
            jQuery.ajax({
              type: 'POST',
              url: wc_setup_params.ajax_url,
              data: queryString,
              cache: false,
              dataType: "json",
              success: woosl_setup.ajax_get_site_process_list,
              error: function(html){

                  }
            });   
        },
        
        ajax_get_site_process_list : function(response) {
            if(response.status  ==  'completed')
                {
                    woosl_setup.current_site_process_list  =   response.data;
                    
                    woosl_setup.current_site_process_list_page  =   -1;
                    woosl_setup.update_progress_bar(10);
                    
                    woosl_setup.process_list();    
                }
        },
        
        process_list    :   function() {
            
            woosl_setup.process_log('Processing list for ' + woosl_setup.current_site_data.blogname);
            
            woosl_setup.current_site_process_list_page++;
            
            var start_at    =   woosl_setup.current_site_process_list_page  *   woosl_setup.process_batch_items_count;
            
            var current_bath    =   woosl_setup.current_site_process_list.slice(start_at, start_at + woosl_setup.process_batch_items_count);
            
            if(current_bath.length < 1)
                {
                    woosl_setup.update_progress_bar(100);
                    setTimeout( function() { woosl_setup.process_sites(); } , 1000);
                }
                
            var queryString = { "action": "woosl_setup_process_batch", "site_id" : woosl_setup.current_site_data.id, "batch"    :   current_bath};
            //send the data through ajax
            jQuery.ajax({
              type: 'POST',
              url: wc_setup_params.ajax_url,
              data: queryString,
              cache: false,
              dataType: "json",
              success: woosl_setup.ajax_process_list,
              error: function(html){

                  }
            });
            
        },
        
        ajax_process_list :  function(response) {
            
            //update the progress bar
            var total_pages = Math.ceil(woosl_setup.current_site_process_list.length / woosl_setup.process_batch_items_count);
            
            var process = parseInt ( (woosl_setup.current_site_process_list_page + 1 ) * 90 /  total_pages )  + 10;
            woosl_setup.update_progress_bar(process);
            
            woosl_setup.process_list();    
            
        },
        
        
        process_log : function(log) {
         
            jQuery('#process_log').html(log);  
            
        },
        
        process_completed   :   function() {
            
            woosl_setup.hide_progress_bar();
            jQuery('form .wc-setup-actions').show();    
        },
        
        show_progress_bar   :   function() {
            
            jQuery( "#progressbar" ).show();    
            
        },
        hide_progress_bar   :   function() {
        
            jQuery( "#progressbar" ).hide();    
            
        },        
        update_progress_bar :   function(progress) {
            jQuery( "#progressbar" ).progressbar({
                              value: progress
                            });
        }    
        
    }
    