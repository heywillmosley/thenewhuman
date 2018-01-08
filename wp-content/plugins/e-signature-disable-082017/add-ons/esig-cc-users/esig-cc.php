<?php
/**
 * @package   	      WP E-Signature CC Users
 * @contributors      Kevin Michael Gray (Approve Me), Abu Shoaib (Approve Me),Arafat Rahman (Approve Me)
 * @wordpress-plugin
 * Plugin Name:       WP E-Signature - CC Users
 * Plugin URI:        http://approveme.me/wp-digital-e-signature
 * Description:       This feature let's you easily CC (or carbon copy) recipients on a Document without requiring their legal signature.  Access is given to the cc'd user at the time the document is closed.
 * Version:           1.0.0
 * Author:            Approve Me
 * Author URI:        http://approveme.me/
 * Documentation:     http://www.approveme.me/wp-digital-signature-plugin-docs/article/cc-someone-document-wp-esignature/
 */

 // If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// define constants 
define('ESIGN_CC_VERSION','1.0.0');
define('ESIGN_CC_PATH',  dirname(__FILE__));
define('ESIGN_CC_URL',  plugins_url("",__FILE__));

// Load up after WP E-Signature is loaded 
add_action('wp_esignature_loaded', 'esig_load_cc_addons' );

function esig_load_cc_addons() {
    
        if(class_exists("Cc_settings")){
            return ;
        }
    
	require_once( 'includes/esig-cc-settings.php' );
        
        // load admin stuff here 
        if(is_admin()){
            
            require_once ('includes/esig-cc-admin.php');
            ESIG_CC_Admin::Init();
        } 
        else {
            require_once 'includes/esig-cc-mails.php';
            Cc_mails::Init();
        }
}


