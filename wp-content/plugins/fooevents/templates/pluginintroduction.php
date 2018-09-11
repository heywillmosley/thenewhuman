<?php $Config = new FooEvents_Config(); 
if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
    require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
}
?>
<div class='woocommerce-events-help'>
    
    <h1>Welcome to FooEvents for WooCommerce</h1>

    <p> 
        <a href="https://www.fooevents.com/documentation/" target="new">Documentation</a> | 
        <a href="https://www.fooevents.com/frequently-asked-questions/" target="new">Frequently Asked Questions</a> | 
        <a href="https://www.fooevents.com/submit-ticket/" target="new">Submit a Support Query</a>
    </p>

    <h3 class="woocommerce-events-intro">Thank you for purchasing FooEvents for WooCommerce!</h3>  

    FooEvents works great out of the box without any custom configuration, however, if you would like to configure FooEvents based on your specific event requirements, you can find out more about how to do this by visiting our <a href="https://www.fooevents.com/documentation/" target="new">help documentation</a>.

    <div class="clear"></div> 

    <div class="woocommerce-events-infobox">
        <h3>Next Steps</h3>
        <ol>
            <li><a href="https://www.fooevents.com/documentation/global-settings/" target="new">Configure the FooEvents global settings (optional)</a></li> 
            <li><strong><a href="https://www.fooevents.com/documentation/setup-event-product/" target="new">Setup your first event</a></strong></li> 
            <li><a href="https://www.fooevents.com/ticket-themes/" target="new">Customize your email tickets using Ticket Themes</a></li>
            <li><a href="https://www.fooevents.com/apps/" target="new">Install the free FooEvents Check-ins Apps</a></li> 
            <li><a href="https://www.fooevents.com/foosales-integration/" target="new">Sell tickets at your event with FooSales</a></li>
        </ol>
    </div>

    <div class="woocommerce-events-infobox">
        <h3>Helpful Resources</h3>
        <ul>
            <li><a href="https://www.fooevents.com/2018/03/07/create-different-ticket-types-fooevents-using-woocommerce-variations-attributes/" target="new">How to create different ticket types in FooEvents using WooCommerce variations and attributes</a></li>
            <li><a href="https://www.fooevents.com/2018/02/28/how-to-create-reoccurring-events-using-fooevents-multi-day-plugin/" target="new">How to create reoccurring events using FooEvents Multi-day plugin</a></li>
            <li><a href="https://www.fooevents.com/2018/04/05/get-creative-with-fooevents-ticket-themes/" target="new">Get creative with FooEvents Ticket Themes</a></li>
            <li><a href="https://www.fooevents.com/speed-up-your-woocommerce-website/" target="new">Speed up your WooCommerce Website</a></li>
            <li><a href="http://demo.fooevents.com/" target="new">FooEvents Demo</a>
        </ul> 
    </div>

    <div class="clear"></div> 

    <h3>FooEvents Extensions</h3>

    <p>The following extensions add various advanced features to the FooEvents for WooCommerce plugin. They can be purchased separately or as part of our popular <a href="https://www.fooevents.com/pricing/" target="new">bundles</a>. If you would like to upgrade to a bundle, please <a href="https://www.fooevents.com/submit-ticket/" target="new">contact us</a> and we will gladly assist.</p>

    <div class="woocommerce-events-extensions">

        <?php if ( fooevents_check_plugin_active('fooevents_custom_attendee_fields/fooevents-custom-attendee-fields.php') || is_plugin_active_for_network('fooevents_custom_attendee_fields/fooevents-custom-attendee-fields.php')) { $installed=true; } else { $installed=false; } ?>
        
        <div class="woocommerce-events-extension <?php if($installed==false) { echo 'not-installed'; } ?>">    

            <a href="https://www.fooevents.com/product/fooevents-custom-attendee-fields/" target="_BLANK"><img src="https://www.fooevents.com/wp-content/uploads/2017/07/fooevents_product_covers_custom_attendee_fields-512x512.png" alt="FooEvents Custom Attendee Fields" /></a>
            <h3><a href="https://www.fooevents.com/product/fooevents-custom-attendee-fields/" target="_BLANK">FooEvents Custom Attendee Fields</a></h3>
            <p>Capture customized attendee fields at checkout and tailor FooEvents according to your unique event requirements.</p>
            <strong>Status:</strong> 

            <?php
            if ($this->is_plugin_active('fooevents_custom_attendee_fields/fooevents-custom-attendee-fields.php') || is_plugin_active_for_network('fooevents_custom_attendee_fields/fooevents-custom-attendee-fields.php')) {
                echo "<span class='install-status installed'>Installed</span>| <a href='https://www.fooevents.com/fooevents-custom-attendee-fields/' target='new'>Plugin Details</a>"; 
            } else {
                if( file_exists(ABSPATH . 'wp-content/plugins/fooevents_custom_attendee_fields/fooevents-custom-attendee-fields.php')) { 
                    echo "<span class='install-status notinstalled'>Deactivated</span>"; 
                } else { 
                    echo "<span class='install-status notinstalled'>Not Installed</span>| <a href='https://www.fooevents.com/fooevents-custom-attendee-fields/' target='new'>Get Plugin</a>"; 
                }  
            } 
            ?> 
            | <a href="https://www.fooevents.com/documentation/category/fooevents-custom-attendee-fields/">Documentation</a> 
            <div class="clear"></div>   
            
        </div>
        
        <?php if ($this->is_plugin_active('fooevents_multi_day/fooevents-multi-day.php') || is_plugin_active_for_network('fooevents_multi_day/fooevents-multi-day.php')) { $installed=true; } else { $installed=false; } ?>
        
        <div class="woocommerce-events-extension <?php if($installed==false) { echo 'not-installed'; } ?>">    
        
            <a href="https://www.fooevents.com/fooevents-multi-day/" target="_BLANK"><img src="https://www.fooevents.com/wp-content/uploads/2017/07/fooevents_product_covers_multiday-512x512.png" alt="FooEvents Custom Attendee Fields" /></a>
            <h3><a href="https://www.fooevents.com/fooevents-multi-day/" target="_BLANK">FooEvents Multi-day</a></h3>
            <p>The FooEvents Multi-day plugin adds support for events that run over multiple days such as concerts, conferences and exhibitions.</p>
            <strong>Status:</strong> 
        
            <?php
            if ($this->is_plugin_active('fooevents_multi_day/fooevents-multi-day.php') || is_plugin_active_for_network('fooevents_multi_day/fooevents-multi-day.php')) {
                echo "<span class='install-status installed'>Installed</span>| <a href='https://www.fooevents.com/fooevents-multi-day/' target='new'>Plugin Details</a>"; 
            } else {
                if( file_exists(ABSPATH . 'wp-content/plugins/fooevents_multi_day/fooevents-multi-day.php')) { 
                    echo "<span class='install-status notinstalled'>Deactivated</span>"; 
                } else { 
                    echo "<span class='install-status notinstalled'>Not Installed</span>| <a href='https://www.fooevents.com/fooevents-multi-day/' target='new'>Get Plugin</a>"; 
                }  
            } 
            ?> 
            | <a href="https://www.fooevents.com/documentation/category/fooevents-multi-day/">Documentation</a> 
            <div class="clear"></div>        
            
        </div>
        
        <?php if ( fooevents_check_plugin_active('fooevents_pdf_tickets/fooevents-pdf-tickets.php') || is_plugin_active_for_network('fooevents_pdf_tickets/fooevents-pdf-tickets.php')) { $installed=true; } else { $installed=false; } ?>
        
        <div class="woocommerce-events-extension <?php if($installed==false) { echo 'not-installed'; } ?>">
            
            <a href="https://www.fooevents.com/product/fooevents-pdf-tickets/" target="_BLANK"><img src="https://www.fooevents.com/wp-content/uploads/2017/07/fooevents_product_covers_pdf-tickets-512x512.png" alt="FooEvents PDF Tickets Plugin" /></a>
            <h3><a href="https://www.fooevents.com/product/fooevents-pdf-tickets/" target="_BLANK">FooEvents PDF Tickets Plugin</a></h3>
            <p>The FooEvents PDF Tickets plugin attaches event tickets as PDF files to the email that is sent to the attendee or ticket purchaser.</p>
            <strong>Status:</strong> 
            
            <?php
            if ( fooevents_check_plugin_active('fooevents_pdf_tickets/fooevents-pdf-tickets.php') || is_plugin_active_for_network('fooevents_pdf_tickets/fooevents-pdf-tickets.php')) {
                echo "<span class='install-status installed'>Installed</span> | <a href='https://www.fooevents.com/fooevents-pdf-tickets/' target='new'>Plugin Details</a>";
            } else {
                if( file_exists(ABSPATH . 'wp-content/plugins/fooevents_pdf_tickets/fooevents-pdf-tickets.php')) { 
                    echo "<span class='install-status notinstalled'>Deactivated</span>"; 
                } else { 
                    echo "<span class='install-status notinstalled'>Not Installed</span>| <a href='https://www.fooevents.com/fooevents-pdf-tickets/' target='new'>Get Plugin</a>"; 
                }  
            } 
            ?> | <a href="https://www.fooevents.com/documentation/category/fooevents-pdf-tickets/">Documentation</a> 
            <div class="clear"></div>   

        </div>

        <?php if ( fooevents_check_plugin_active('fooevents_calendar/fooevents-calendar.php') || is_plugin_active_for_network('fooevents_calendar/fooevents-calendar.php')) { $installed=true; } else { $installed=false; } ?>
        
        <div class="woocommerce-events-extension <?php if($installed==false) { echo 'not-installed'; } ?>">
            
            <a href="https://www.fooevents.com/product/fooevents-calendar/" target="_BLANK"><img src="https://www.fooevents.com/wp-content/uploads/2017/07/fooevents_product_covers_calendar-512x512.png" alt="FooEvents Calendar" /></a>
            <h3><a href="https://www.fooevents.com/product/fooevents-calendar/" target="_BLANK">FooEvents Calendar</a></h3>
            <p>The FooEvents Calendar plugin makes it possible to display event lists and calendars using shortcodes and widgets.</p>
            <strong>Status:</strong> 
            
            <?php
            if ( fooevents_check_plugin_active('fooevents_calendar/fooevents-calendar.php') || is_plugin_active_for_network('fooevents_calendar/fooevents-calendar.php')) {
                echo "<span class='install-status installed'>Installed</span> | <a href='https://www.fooevents.com/fooevents-calendar/' target='new'>Plugin Details</a>";
            } else {
                if( file_exists(ABSPATH . 'wp-content/plugins/fooevents_calendar/fooevents-calendar.php')) { 
                    echo "<span class='install-status notinstalled'>Deactivated</span>"; 
                } else { 
                echo "<span class='install-status notinstalled'>Not Installed</span> | <a href='https://www.fooevents.com/fooevents-calendar/' target='new'>Get Plugin</a>";
                }  
            }
            ?> 
            | <a href="https://www.fooevents.com/documentation/category/fooevents-calendar/">Documentation</a> 
            <div class="clear"></div> 

        </div>

        <?php if ( fooevents_check_plugin_active('fooevents_express_check_in/fooevents-express-check_in.php') || is_plugin_active_for_network('fooevents_express_check_in/fooevents-express-check_in.php')) { $installed=true; } else { $installed=false; } ?>
        
        <div class="woocommerce-events-extension <?php if($installed==false) { echo 'not-installed'; } ?>">
            
            <a href="https://www.fooevents.com/product/fooevents-express-check-in/" target="_BLANK"><img src="https://www.fooevents.com/wp-content/uploads/2017/07/fooevents_product_covers_express_checkins-512x512.png" alt="FooEvents Express Check-in" /></a>
            <h3><a href="https://www.fooevents.com/product/fooevents-express-check-in/" target="_BLANK">FooEvents Express Check-in</a></h3>
            <p>The FooEvents Express Check-in plugin ensures that checking in attendees at your event is fast and effortless.</p>
            <strong>Status:</strong> 
            <?php
            if ( fooevents_check_plugin_active('fooevents_express_check_in/fooevents-express-check_in.php') || is_plugin_active_for_network('fooevents_express_check_in/fooevents-express-check_in.php')) {
                echo "<span class='install-status installed'>Installed</span> | <a href='https://www.fooevents.com/fooevents-express-check-in/' target='new'>Plugin Details</a>";
            } else {
              if( file_exists(ABSPATH . 'wp-content/plugins/fooevents_express_check_in/fooevents-express-check_in.php')) { 
                    echo "<span class='install-status notinstalled'>Deactivated</span>"; 
                } else { 
                echo "<span class='install-status notinstalled'>Not Installed</span> | <a href='https://www.fooevents.com/fooevents-express-check-in/' target='new'>Get Plugin</a>";
                }              
            }
            ?> 
            | <a href="https://www.fooevents.com/documentation/category/fooevents-express-check-in/">Documentation</a> 
            <div class="clear"></div>  

        </div>

        <div class="clear"></div>
        
    </div>

</div>