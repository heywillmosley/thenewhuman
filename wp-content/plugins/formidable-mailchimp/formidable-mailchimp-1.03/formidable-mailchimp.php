<?php
/*
Plugin Name: Formidable to MailChimp
Description: Save and update MailChimp contacts from your Formidable forms
Version: 1.03
Plugin URI: http://formidablepro.com/
Author URI: http://strategy11.com
Author: Strategy11
*/

function frm_mlcmp_forms_autoloader($class_name) {
    $path = dirname(__FILE__);

    // Only load Frm classes here
    if ( ! preg_match('/^FrmMlcmp.+$/', $class_name) ) {
        return;
    }

    if ( preg_match('/^.+Helper$/', $class_name) ) {
        $path .= '/helpers/'. $class_name .'.php';
    } else if ( preg_match('/^.+Controller$/', $class_name) ) {
        $path .= '/controllers/'. $class_name .'.php';
    } else {
        $path .= '/models/'. $class_name .'.php';
    }

    if ( file_exists($path) ) {
        include($path);
    }
}

// Add the autoloader
spl_autoload_register('frm_mlcmp_forms_autoloader');

// Load hooks
new FrmMlcmpAppController();
new FrmMlcmpSettingsController();