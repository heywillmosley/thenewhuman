<?php

    class WOO_Date_Update
         {
                
             function __construct()
                {
                    
                    
                }
                
                
             function update_run()
                {
                    
                    global $WOO_MSTORE;
                    
                    //set the start update option to let know others there's an update in procgress
                    add_site_option('mstore_update_wizard_started', 'true');
                    
                    ?>
                    <div class="wrap">
                        <h1>Update</h1>
                        <br />
                    <?php                
                    
                    $options    =   $WOO_MSTORE->functions->get_options();
                    
                    $version        =   isset($options['version'])  ?   $options['version'] :   WOO_MSTORE_VERSION;
                    if(empty($version))
                        $version    =   '1';
            
                    if (version_compare($version, WOO_MSTORE_VERSION, '<')) 
                        {
                            
                            if(version_compare($version, '1.5.1', '<'))
                                {
                                    include_once(   WOO_MSTORE_PATH . '/include/updates/update-1.5.1.php' );
                                    
                                    $options['version'] =   '1.5';
               
                                    //update the options, in case of timeout, to allow later for resume
                                    $WOO_MSTORE->functions->update_options( $options );
                                }
                                
                        }

                    
                    delete_site_option('mstore_update_wizard_started');
                    ?><p><?php _e( 'Update successfully completed.', 'woonet' ); ?></p><?php
                    
                    ?></div><?php
                    
                }
             
         }

?>