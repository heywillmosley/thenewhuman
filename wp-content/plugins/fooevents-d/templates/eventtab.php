<h2><?php _e('Event Details', 'woocommerce-events'); ?></h2>

<?php if($multiDayEvent === true) :?>
    
    <?php if($WooCommerceEventsMultiDayType != 'select' && !empty($WooCommerceEventsDate)) :?>

        <?php if(!empty($WooCommerceEventsDate)) :?>

            <p><b><?php _e('Start date:', 'woocommerce-events'); ?> </b> <?php echo $WooCommerceEventsDate; ?></p>

        <?php endif; ?>

        <?php if(!empty($WooCommerceEventsEndDate)) :?>

            <p><b><?php _e('End date:', 'woocommerce-events'); ?> </b> <?php echo $WooCommerceEventsEndDate; ?></p>

        <?php endif; ?>
    <?php elseif($WooCommerceEventsMultiDayType == "select" && !empty($WooCommerceEventsSelectDate)) :?>
        
        <?php $x = 1; ?>    
        <?php foreach($WooCommerceEventsSelectDate as $date) :?>
            
            <p><b><?php printf(__('%s %d: ', 'woocommerce-events'), $dayTerm, $x) ?> </b> <?php echo $date; ?></p>
            
            <?php $x++; ?>
            
        <?php endforeach; ?>    
            
    <?php endif; ?>
            
<?php else :?>

    <?php if(!empty($WooCommerceEventsDate)) :?>
        
    <p><b><?php _e('Date:', 'woocommerce-events'); ?> </b> <?php echo $WooCommerceEventsDate; ?></p>
    
    <?php endif; ?>

<?php endif; ?>
<?php if(!empty($WooCommerceEventsHour) && !empty($WooCommerceEventsMinutes) && $WooCommerceEventsHour != '00') :?>

    <p><b><?php _e('Start time:', 'woocommerce-events'); ?> </b> <?php echo $WooCommerceEventsHour.':'.$WooCommerceEventsMinutes; ?> <?php echo (!empty($WooCommerceEventsPeriod))? $WooCommerceEventsPeriod : '' ?></p>

<?php endif; ?>
    
<?php if(!empty($WooCommerceEventsHourEnd) && !empty($WooCommerceEventsMinutesEnd) && $WooCommerceEventsHourEnd != '00') :?>

    <p><b><?php _e('End time:', 'woocommerce-events'); ?> </b> <?php echo $WooCommerceEventsHourEnd.':'.$WooCommerceEventsMinutesEnd; ?> <?php echo (!empty($WooCommerceEventsEndPeriod))? $WooCommerceEventsEndPeriod : '' ?></p>

<?php endif; ?>

<?php if(!empty($WooCommerceEventsLocation)) :?>
    
    <p><b><?php _e('Venue:', 'woocommerce-events'); ?> </b> <?php echo html_entity_decode($WooCommerceEventsLocation); ?></p>
    
<?php endif; ?>
    
<?php if(!empty($WooCommerceEventsGPS)) :?>
    
    <p><b><?php _e('Coordinates:', 'woocommerce-events'); ?> </b> <?php echo $WooCommerceEventsGPS; ?></p>
    
<?php endif; ?>

<?php if(!empty($WooCommerceEventsDirections)) :?>
    
    <p><b><?php _e('Directions:', 'woocommerce-events'); ?> </b> <?php echo html_entity_decode($WooCommerceEventsDirections); ?></p>
    
<?php endif; ?>
    
<?php if(!empty($WooCommerceEventsSupportContact)) :?>
    
    <p><b><?php _e('Phone:', 'woocommerce-events'); ?> </b> <?php echo html_entity_decode($WooCommerceEventsSupportContact); ?></p>
    
<?php endif; ?>
    
<?php if(!empty($WooCommerceEventsEmail)) :?>
    
    <p><b><?php _e('Email:', 'woocommerce-events'); ?> </b> <?php echo html_entity_decode($WooCommerceEventsEmail); ?></p>
    
<?php endif; ?>