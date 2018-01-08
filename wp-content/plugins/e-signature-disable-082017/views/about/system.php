

<?php
$active_theme = wp_get_theme();

$super_admin_wp_data = get_userdata(WP_E_Sig()->user->esig_get_super_admin_id());

$plugins = get_plugins();
$active_plugins = get_option('active_plugins', array());

$license_result = Esign_licenses::check_license();



?>


<img src="<?php echo ESIGN_ASSETS_DIR_URI; ?>/images/logo.svg">

<p><?php _e('Please include this information when requesting support:', 'esig'); ?> </p>
<form action="" method="POST">
    <p class="submit">
        <a href="#" onclick="copyToClipboard()" id="esig-copy-clipboard" class="button-primary esig-debug-report"><?php _e('Copy To Clipboard', 'esig'); ?></a>
        <button type="submit" value="download-system-info" class="button-primary esig-debug-report"><?php _e('Download System Status', 'esig'); ?></button>
    </p>

    <textarea readonly id="esig-system-info-textarea" name="esig_system_info">
            
### Begin System Status Report ###

        <?php do_action('edd_system_info_before'); ?>

===== Site Info =====

SITE_URL:                <?= site_url() . "\n"; ?>
HOME_URL:                <?= home_url() . "\n"; ?>
Multisite:               <?= is_multisite() ? 'Yes' . "\n" : 'No' . "\n"; ?>



===== Hosting Provider =====

HOST :                   <?= $data['hosting_info'] . "\n"; ?>


===== WordPress Configuration =====

Version:                <?= get_bloginfo('version') . "\n"; ?>
Language:               <?= ( defined('WPLANG') && WPLANG ? WPLANG : 'en_US' ) . "\n"; ?>
Timezone:               <?= date_default_timezone_get() . "\n"; ?>
Permalink Structure:    <?= ( get_option('permalink_structure') ? get_option('permalink_structure') : 'Default' ) . "\n"; ?>
Active Theme:           <?= $active_theme->Name . "\n"; ?>
Theme Version:          <?= $active_theme->Version . "\n"; ?>
Author Url:             <?= $active_theme->{'Author URI'} . "\n"; ?>
Remote Post:            <?= $data['remote_post'] . "\n"; ?>
WP_DEBUG:               <?= ( defined('WP_DEBUG') ? WP_DEBUG ? 'Enabled' : 'Disabled' : 'Not set' ) . "\n"; ?>
WP_DEBUG_LOG:           <?= ( WP_DEBUG_LOG ? 'Enabled' : 'Disabled' ) . "\n"; ?>
Memory Limit:           <?= WP_MEMORY_LIMIT . "- We recommend setting memory to at least 64MB \n\t\t\t See: http://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP" . "\n"; ?>


===== WP E-Signature Configuration =====

Version:                <?= esig_plugin_name_get_version() . "\n"; ?>
DB Version:             <?= get_option("esig_db_version") . "\n"; ?>
License :               <?= $license_result->license . "\n";?>
License Type:           <?= $license_result->license_type . "\n"; ?>
Super Admin:            <?= $super_admin_wp_data->user_login . "(WP Username)" . "\n"; ?>
Timezone:               <?= WP_E_Sig()->setting->get_generic('esig_timezone_string') . "\n"; ?>
Force SSL:              <?= (WP_E_Sig()->setting->get_generic('force_ssl_enabled') ? "Yes" : "NO") . "\n\n"; ?>
Active Add-ons:         <?php
        foreach (Esig_Addons::get_addons_setting() as $addon_file) {
            list($folder_name, $file) = explode("/", $addon_file);

            $addon_data = Esig_Addons::get_addon_data(Esig_Addons::get_installed_directory($folder_name) . $addon_file);
            if($addon_data){
                echo $addon_data['Name'] . "\n\t\t\t";
            }    
        }
        ?>

E-signature Pages:       <?= $data['esign_pages'] . "\n"; ?>


===== WordPress Active Plugins =====
                      
    <?php
        foreach ($plugins as $plugin_path => $plugin) {
            if (!in_array($plugin_path, $active_plugins))
                continue;

            echo "\t\t\t" . $plugin['Name'] . ': ' . $plugin['Version'] . "\n";
        }
        ?>

===== WordPress Inactive Plugins =====
    
    <?php
        foreach ($plugins as $plugin_path => $plugin) {
            if (in_array($plugin_path, $active_plugins))
                continue;

            echo "\t\t\t" . $plugin['Name'] . ': ' . $plugin['Version'] . "\n";
        }
        ?>

    <?php
        if (is_multisite()) {
            // WordPress Multisite active plugins
            echo "\n" . '-- Network Active Plugins' . "\n\n";

            $plugins = wp_get_active_network_plugins();
            $active_plugins = get_site_option('active_sitewide_plugins', array());

            foreach ($plugins as $plugin_path) {
                $plugin_base = plugin_basename($plugin_path);

                if (!array_key_exists($plugin_base, $active_plugins))
                    continue;

                $plugin = get_plugin_data($plugin_path);
                echo "\t\t\t" . $plugin['Name'] . ': ' . $plugin['Version'] . "\n";
            }
        }
        ?>

===== Webserver Configuration =====

PHP Version:            <?= PHP_VERSION . "\n"; ?>
MySQL Version:          <?php
        global $wpdb;
        echo $wpdb->db_version() . "\n";
        ?>
Webserver Info:         <?= $_SERVER['SERVER_SOFTWARE'] . "\n"; ?>
Port:                   <?= $_SERVER['SERVER_PORT'] . "\n"; ?>
Document Root:          <?= $_SERVER['DOCUMENT_ROOT'] . "\n\n";?>


===== PHP Configuration =====

Safe Mode:              <?= ( ini_get('safe_mode') ? 'Enabled' : 'Disabled' . "\n" ); ?>
Memory Limit:           <?= ini_get('memory_limit') . "\n"; ?>
Upload Max Size:        <?= ini_get('upload_max_filesize') . "\n"; ?>
Post Max Size:          <?= ini_get('post_max_size') . "\n"; ?>
Upload Max Filesize:    <?= ini_get('upload_max_filesize') . "\n"; ?>
Time Limit:             <?= ini_get('max_execution_time') . "\n"; ?>
Max Input Vars:         <?= ini_get('max_input_vars') . "\n"; ?>
Display Errors:         <?= ( ini_get('display_errors') ? 'On (' . ini_get('display_errors') . ')' : 'N/A' ) . "\n"; ?>


===== PHP Extensions =====

cURL:                    <?= ( function_exists('curl_init') ? 'Supported' : 'Not Supported' ) . "\n"; ?>
fsockopen:               <?= ( function_exists('fsockopen') ? 'Supported' : 'Not Supported' ) . "\n"; ?>
SOAP Client:             <?= ( class_exists('SoapClient') ? 'Installed' : 'Not Installed' ) . "\n"; ?>
Suhosin:                 <?= ( extension_loaded('suhosin') ? 'Installed' : 'Not Installed' ) . "\n"; ?>
MCrypt:                  <?= ( function_exists('mcrypt_create_iv') ? 'Supported' : 'Not Supported' ) . "\n"; ?>


===== Session Configuration =====
	
Session:                <?= ( isset($_SESSION) ? 'Enabled' : 'Disabled' ) . "\n"; ?>
<?php
        if (isset($_SESSION)) {
            echo 'Session Name:           ' . esc_html(ini_get('session.name')) . "\n";
            echo 'Cookie Path:            ' . esc_html(ini_get('session.cookie_path')) . "\n";
            echo 'Save Path:              ' . esc_html(ini_get('session.save_path')) . "\n";
            echo 'Use Cookies:            ' . ( ini_get('session.use_cookies') ? 'On' : 'Off' ) . "\n";
            echo 'Use Only Cookies:       ' . ( ini_get('session.use_only_cookies') ? 'On' : 'Off' ) . "\n";
        }
        ?>




### End System Status Report ###

    </textarea>


</form>


<script type="text/javascript">
    function copyToClipboard() {

        //var text = document.getElementById('#esig-system-info-textarea').innerHTML;
        var copyTextarea = document.querySelector('#esig-system-info-textarea');
        copyTextarea.select();

        try {
            var successful = document.execCommand('copy');
           // var msg = successful ? 'successful' : 'unsuccessful';
            //console.log('Copying text command was ' + msg);
            document.getElementById("esig-copy-clipboard").innerHTML = 'Copied Successfully';
            //alert();
        } catch (err) {
            alert("Unable to copy");
        }
    }
</script>

