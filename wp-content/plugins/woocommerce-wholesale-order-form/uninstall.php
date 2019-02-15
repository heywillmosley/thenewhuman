<?php
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) exit();

if ( get_option( "wwof_settings_help_clean_plugin_options_on_uninstall" ) == 'yes' ) {
  
  global $wpdb;

  // DELETES WWOF SETTINGS
  $wpdb->query(
      "DELETE FROM $wpdb->options
       WHERE option_name LIKE 'wwof_%'
      "
    );

}