/*global ajaxurl, inlineEditPost, inlineEditL10n */
jQuery(function( $ ) {
	$( '#the-list' ).on( 'click', '.editinline', function() {

        var post_id = $( this ).closest( 'tr' ).attr( 'id' );

        post_id = post_id.replace( 'post-', '' );
        var blog_id =   '';
        if(post_id.indexOf('_') !== -1)
            {
                var parts   =   post_id.split("_");
                blog_id     =   parts[0];
                post_id     =   parts[1];
            }
        
        inlineEditPost.revert();
        
        if(jQuery( '#woocommerce_multistore_inline_' + post_id ).length < 1)
            {
                jQuery('#woocommerce-multistore-fields').hide();
                return;   
            }
        
        jQuery('#woocommerce-multistore-fields').show();
        
     		
		var $wc_inline_data = $( '#woocommerce_multistore_inline_' + post_id );
        
        jQuery($wc_inline_data).find('.data_block').each(function() {
            
            var data_blog_id    =   jQuery(this).attr('data-blog-id');
            
            var publish_to              =   jQuery(this).find('.publish_to').text();
            var child_inheir            =   jQuery(this).find('.child_inheir').text();
            var stock_synchronize       =   jQuery(this).find('.stock_synchronize').text();
            
            if(publish_to != '')
                {
                    $( 'input[name="_woonet_publish_to_' +  data_blog_id +'"]', '.inline-edit-row' ).attr( 'checked', 'checked' );
                    $( 'input[name="_woonet_publish_to_' +  data_blog_id +'"]', '.inline-edit-row' ).attr( 'data-default-value', 'yes' );
                }
                else
                $( 'input[name="_woonet_publish_to_' +  data_blog_id +'"]', '.inline-edit-row' ).attr( 'checked', false );
            
            if(child_inheir != '')
                $( 'input[name="_woonet_publish_to_' +  data_blog_id +'_child_inheir"]', '.inline-edit-row' ).attr( 'checked', 'checked' );
                else
                $( 'input[name="_woonet_publish_to_' +  data_blog_id +'_child_inheir"]', '.inline-edit-row' ).attr( 'checked', false );
                
            if(stock_synchronize != '')
                $( 'input[name="_woonet_' +  data_blog_id +'_child_stock_synchronize"]', '.inline-edit-row' ).attr( 'checked', 'checked' );
                else
                $( 'input[name="_woonet_' +  data_blog_id +'_child_stock_synchronize"]', '.inline-edit-row' ).attr( 'checked', false );
                
        })
        
        
        jQuery('.inline-edit-row input[type="checkbox"]._woonet_publish_to').change(function() {
                        if(jQuery(this).is(":checked")) {
                            jQuery(this).closest('label').find('.warning').slideUp();
                        }
                        else {                            
                            if(jQuery(this).attr('data-default-value')   !=  '')
                                jQuery(this).closest('label').find('.warning').slideDown();
                        }
                    })
       
	});
    
    
    jQuery('#doaction').click(function(e){
        
            jQuery('#bulk-edit #woocommerce-multistore-fields').show();
            setTimeout(function() {
                ms_inlineEditPost.after_setBulk();    
            }, 100);
       
       });
    
    
    jQuery('#ms_doaction').click(function(e){
   
        $( 'input.text', '.inline-edit-row' ).val( '' );
        $( '#woocommerce-fields' ).find( 'select' ).prop( 'selectedIndex', 0 );
        $( '#woocommerce-fields-bulk' ).find( '.inline-edit-group .change-input' ).hide();

        
        var n = $(this).attr('id').substr(5);
            if ( 'edit' === jQuery( 'select[name="' + n + '"]' ).val() ) {
                e.preventDefault();
                ms_inlineEditPost.setBulk();
            } else if ( jQuery('form#posts-filter tr.inline-editor').length > 0 ) {
                inlineEditPost.revert();
            }
        });
    
    //custom inline update
    jQuery( '.ms-save', '#inline-edit' ).click( function() {
            return ms_inlineEditPost.inlinSave(this);
        });
        
        
    $( '#ms_bulk_update' ).on( 'click', function() {
        return ms_inlineEditPost.bulkSave(this);   
    });    
    
            	
});


    
    var ms_inlineEditPost   =   {
        
        inlinSave :  function(id) {
                var params, fields, page = jQuery('.post_status_page').val() || '';
                                
                var id = jQuery(id).closest('tr').attr('id'),
                    parts = id.split('-');
                
                post_id =   parts[parts.length - 1];
                id  =   post_id;
                
                var data_ms_id  = jQuery('#the-list').find('tr#post-' + id).attr('data-ms-id');
                var blog_id =   '';
                if(data_ms_id.indexOf('_') !== -1)
                    {
                        var parts   =   data_ms_id.split("_");
                        blog_id     =   parts[0];
                        post_id     =   parts[1];
                    } 
                
                
                jQuery( 'table.widefat .spinner' ).addClass( 'is-active' );

                params = {
                    action: 'woosl-inline-save',
                    post_ID: id,
                    blog_id :   blog_id
                };

                fields = jQuery('#edit-'+id).find(':input').serialize();
                params = fields + '&' + jQuery.param(params);

                // make ajax request
                jQuery.post( ajaxurl, params,
                    function(r) {
                        jQuery( 'table.widefat .spinner' ).removeClass( 'is-active' );
                        jQuery( '.ac_results' ).hide();

                        if (r) {
                            if ( -1 !== r.indexOf( '<tr' ) ) {
                                jQuery(inlineEditPost.what+id).siblings('tr.hidden').addBack().remove();
                                jQuery('#edit-'+id).before(r).remove();
                                jQuery(inlineEditPost.what+id).hide().fadeIn();
                            } else {
                                r = r.replace( /<.[^<>]*?>/g, '' );
                                jQuery('#edit-'+id+' .inline-edit-save .error').html(r).show();
                            }
                        } else {
                            jQuery('#edit-'+id+' .inline-edit-save .error').html(inlineEditL10n.error).show();
                        }
                    },
                'html');
                return false;
        
        },
        after_setBulk   :   function()
        {
            jQuery( 'tbody th.check-column input[type="checkbox"]' ).each( function() {
                if ( jQuery(this).prop('checked') ) {
                    if(jQuery(this).closest('tr').hasClass('ms-child-product'))
                        {
                            jQuery('#bulk-edit #woocommerce-multistore-fields').hide();   
                        }

                }
            });   
            
        },
        setBulk : function ()
        {
            if(jQuery( '#the-list .check-column input[type="checkbox"]:checked' ).length < 1)
                return false;
            
            var te = '', c = true;
            inlineEditPost.revert();

            jQuery( '#bulk-edit td' ).attr( 'colspan', jQuery( 'th:visible, td:visible', '.widefat:first thead' ).length );
            // Insert the editor at the top of the table with an empty row above to maintain zebra striping.
            jQuery('table.widefat tbody').prepend( jQuery('#bulk-edit') ).prepend('<tr class="hidden"></tr>');
            jQuery('#bulk-edit').addClass('inline-editor').show();
            
            //make sure all checkboces are not checked
            jQuery('#bulk-edit input:checkbox').attr('checked', false);

            jQuery( 'tbody th.check-column input[type="checkbox"]' ).each( function() {
                if ( jQuery(this).prop('checked') ) {
                    c = false;
                    var data_ms_id = jQuery(this).val(), theTitle;
                    
                    var parts   =   data_ms_id.split("_");
                    blog_id     =   parts[0];
                    id     =   parts[1];                   
                    
                    theTitle = jQuery('#inline_'+id+' .post_title').html() || inlineEditL10n.notitle;
                    te += '<div id="ttle'+id+'" class="post" data-id="'+ id +'" data-blog-id="' + blog_id + '"><a id="_'+id+'" class="ntdelbutton" title="'+inlineEditL10n.ntdeltitle+'">X</a>'+theTitle+'</div>';
                }
            });

            if ( c ) {
                return this.revert();
            }

            jQuery('#bulk-titles').html(te);
            jQuery('#bulk-titles a').click(function(){
                var id = jQuery(this).attr('id').substr(1);

                jQuery('table.widefat input[value="' + id + '"]').prop('checked', false);
                jQuery('#ttle'+id).remove();
            });

      
            jQuery('html, body').animate( { scrollTop: 0 }, 'fast' );   
            
        },
        
        bulkSave : function() {
        
            var ids =   [];
            var blog_ids  =   [];
            
            jQuery('#bulk-edit #bulk-titles > .post').each(function() {
                
                var id          =   jQuery(this).attr('data-id');
                var blog_id     =   jQuery(this).attr('data-blog-id');
                
                ids.push(id);
                blog_ids.push(blog_id);
            })
            
            params = {
                    action: 'woosl-bulk-edit-save',
                    'ids' :   ids,
                    'blog_ids'    :   blog_ids
                };

                fields = jQuery('#bulk-edit').find(':input').serialize();
                params = fields + '&' + jQuery.param(params);

                // make ajax request
                jQuery.post( ajaxurl, params,
                    function(r) {
                        
                        //page refresh
                        location.reload();
                    },
                'html');
                
            return false;
        } 
        
    }



