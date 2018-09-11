<?php
/**
 * Plugin Name: Admin Custom Login 
 * Version: 2.7.0
 * Description: Customize Your WordPress Login Screen Amazingly
 * Author: Weblizar
 * Author URI: https://weblizar.com/plugins/
 * Plugin URI: https://weblizar.com/plugins/
 * Text Domain: admin-custom-login
 * Domain Path: /languages
 */
 
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
define("WEBLIZAR_NALF_PLUGIN_URL", plugin_dir_url(__FILE__));
define("WEBLIZAR_ACL_PLUGIN_DIR_PATH_FREE", plugin_dir_path(__FILE__));
define("WEBLIZAR_ACL", "admin-custom-login", true);


final class WL_ACL_FREE {
    private static $instance = null;

    private function __construct() {
        $this->initialize_hooks();
    }

    public static function get_instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function initialize_hooks() {
        require_once( 'admin/index.php' );
    }
}
WL_ACL_FREE::get_instance();

/**
 * Admin Custom Login installation script
 */
register_activation_hook( __FILE__, 'ACL_WeblizarDoInstallation' );
function ACL_WeblizarDoInstallation() {
    require_once('installation.php');
}

require_once( WEBLIZAR_ACL_PLUGIN_DIR_PATH_FREE . '/init.php' );
?>