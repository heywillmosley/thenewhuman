<?php

/**
 * WP_E_addon a class for addons controller.
 *
 * .
 *
 * @version 1.1.4
 * @author Abu shoaib
 */
class WP_E_Addon extends WP_E_Model {

    public function __construct() {
        parent::__construct();

        $this->settings = new WP_E_Setting();
        $this->document = new WP_E_Document();
        // adding action 
    }

    public function esign_update_check($addon_id, $old_version) {


        $esig_license = $this->settings->get_generic("esig_wp_esignature_license_active");
        if (empty($esig_license) || $esig_license == 'invalid') {
            return;
        }
        if (!get_transient('esign-update-list')) {
            return;
        } else {
            //if(!get_transient('esign-update-check'.$addon_id))
            //{
            $plugin_list = json_decode(get_transient('esign-update-list'));
            if (!is_array($plugin_list)) {
                return;
            }
            //delete_transient('esign-message');
            foreach ($plugin_list as $plugin) {
                if ($addon_id != $plugin->addon_id) {
                    continue;
                } else {

                    if (version_compare($old_version, $plugin->new_version, '<')) {

                        //$esign_auto_update =$this->settings->get_generic("esign_auto_update");
                        // if(isset($esign_auto_update) && !empty($esign_auto_update) )
                        //{
                        if (!get_transient('esign-auto-downloads')) {
                            $downloads = array();
                            $downloads[$addon_id] = $plugin->item_name;
                            set_transient('esign-auto-downloads', $downloads, 60 * 60 * 1);
                        } else {
                            $downloads = get_transient('esign-auto-downloads');
                            if (empty($downloads)) {
                                $downloads = array();
                                $downloads[$addon_id] = $plugin->item_name;
                            } elseif (!array_key_exists($addon_id, $downloads)) {
                                $downloads[$addon_id] = $plugin->item_name;
                            }
                            delete_transient('esign-auto-downloads');
                            set_transient('esign-auto-downloads', $downloads, 60 * 60 * 1);
                        }

                        // }
                        // else 
                        //{ 
                        //R$msg = 'WP E-Signature ' . $plugin->item_name . ' ' . $plugin->new_version . ' Updates is available  <a href="?page=esign-addons&esig_action=update&download_url=' . urlencode($plugin->download_link) . '&download_name=' . $plugin->download_name . '" class="esign-addon-update-btn"> Click Here To Update</a>';
                        $msg = sprintf(__('WP E-Signature %s %s Updates is available  <a href="?page=esign-addons&esig_action=update&download_url= urlencode%s &download_name= %s" class="esign-addon-update-btn"> Click Here To Update</a>','esign'),$plugin->item_name,$plugin->new_version,$plugin->download_link,$plugin->download_name);
                        if (!get_transient('esign-message')) {
                            $message = array();
                            $message[$addon_id] = $msg;

                            set_transient('esign-message', json_encode($message), 300);
                        } else {
                            $message = json_decode(get_transient('esign-message'));
                            if (empty($message)) {
                                $message = array();
                                $message[$addon_id] = $msg;
                            } elseif (!property_exists($message, $addon_id)) {
                                $message->$addon_id = $msg;
                            }
                            delete_transient('esign-message');
                            set_transient('esign-message', json_encode($message), 300);
                        }
                        // }

                        set_transient('esign-update-check' . $addon_id, 'checked', 60);
                    } else {

                        if (!get_transient('esign-message')) {
                            continue;
                        }
                        $message = json_decode(get_transient('esign-message'));
                        if (!is_object($message)) {
                            return;
                        }

                        if (property_exists($message, $addon_id)) {

                            unset($message->$addon_id);
                            delete_transient('esign-message');
                            if (isset($message)) {
                                set_transient('esign-message', json_encode($message), 300);
                            }
                        }
                    }
                }
            }
            //}
        }
    }

    public function esig_all_plugin_activation() {
        $array_Plugins = get_plugins();

        if (!empty($array_Plugins)) {
            foreach ($array_Plugins as $plugin_file => $plugin_data) {
                if (is_plugin_inactive($plugin_file)) {
                    $plugin_name = $plugin_data['Name'];

                    if ($plugin_name != "WP E-Signature") {
                        if (preg_match("/WP E-Signature/", $plugin_name)) {
                            $success = $this->esig_addons_enable($plugin_file);
                        }
                    }
                }
            }
        }
    }

    /*
     * addons tab generate method
     *
     * Since 1.1.4
     */

    public function esig_addons_tabs($current = 'all') {

        $tabs = array('all' => 'All', 'enable' => 'Enabled', 'disable' => 'Disabled', 'get-more' => 'Get More');
        echo '<div id="icon-themes" class="icon32"><br></div>';
        echo '<h2 class="nav-tab-wrapper">';
        foreach ($tabs as $tab => $name) {
            $class = ( $tab == $current ) ? ' nav-tab-active esig-tab-active-background' : 'esig-nav-tab-border';
            echo "<a class='nav-tab $class' href='?page=esign-addons&tab=$tab'>$name</a>";
        }
        echo '</h2>';
    }

    public function esig_get_all_addons_list() {
        $all_addons_list = $this->esig_get_premium_addons_list();
        $all_install = array();
        if ($all_addons_list) {

            foreach ($all_addons_list as $addonlist => $addons) {
                if ($addonlist != "esig-price") {
                    if ($addons->addon_name != 'WP E-Signature') {
                        if ($addons->download_access == 'yes') {
                            // set all addon transients    
                            $all_install[$addons->download_name] = $addons->download_link;
                        }
                    }
                }
            }
        }

        return json_encode($all_install);
    }

    /*     * *
     * esig get all addons list. 
     */

    public function esig_get_premium_addons_list() {

        global $wp_version;

        if (!function_exists('WP_E_Sig'))
            return;

        $esig = WP_E_Sig();

        $api_params = array(
            'esig-remote-request' => 'on',
            'esig_action' => 'addons_list',
            'license_key' => trim($esig->setting->get_generic('esig_wp_esignature_license_key')),
            'url' => 'http://www.approveme.me/',
            'author' => 'ApproveMe',
        );

        $request = wp_remote_post('http://www.approveme.me/', array('timeout' => 15, 'sslverify' => false, 'body' => $api_params));

        if (!is_wp_error($request)):
            $request = json_decode(wp_remote_retrieve_body($request));
            return $request;
        else:
            return false;
        endif;
    }

    /*     * *
     * esig get all addons list. 
     */

    public function esig_get_addons_update_list() {

        global $wp_version;

        if (!function_exists('WP_E_Sig'))
            return;

        $esig = WP_E_Sig();

        $api_params = array(
            'esig-remote-request' => 'on',
            'esig_action' => 'update_list',
            'license_key' => trim($esig->setting->get_generic('esig_wp_esignature_license_key')),
            'url' => 'http://www.approveme.me/',
            'author' => 'ApproveMe',
        );

        $request = wp_remote_post('http://www.approveme.me/', array('timeout' => 15, 'sslverify' => false, 'body' => $api_params));

        if (!is_wp_error($request)):
            $request = wp_remote_retrieve_body($request);
            return $request;
        else:
            return false;
        endif;
    }

    /*     * *
     * esig get all addons list. 
     */

    public function esig_get_addons_list() {

        global $wp_version;

        if (!function_exists('WP_E_Sig'))
            return;

        $esig = WP_E_Sig();

        $api_params = array(
            'esig-remote-request' => 'on',
            'esig_action' => 'addons_list_basic',
            'license_key' => trim($esig->setting->get_generic('esig_wp_esignature_license_key')),
            'url' => 'http://www.approveme.me/',
            'author' => 'Approve Me',
        );

        $request = wp_remote_post('http://www.approveme.me/', array('timeout' => 15, 'sslverify' => false, 'body' => $api_params));

        if (!is_wp_error($request)):
            $request = json_decode(wp_remote_retrieve_body($request));

            return $request;
        else:
            return false;
        endif;
    }

    public function esig_addons_installall() {
        set_time_limit(0);
        if (!function_exists('WP_E_Sig'))
            return;

        $esig = WP_E_Sig();

        $all_addons = json_decode(get_transient('esig-all-addons-install'));
        // add onlist is empty then return false . 
        if (!$all_addons) {
            $all_addons = json_decode($this->esig_get_all_addons_list());

            if (!$all_addons) {
                return false;
            }
        }

        // if class not exists add wp updater class 
        if (!class_exists('WP_Upgrader'))
            require ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

        foreach ($all_addons as $download_name => $source) {

            $wp_installer = new WP_Upgrader();

            $result = $wp_installer->run(array(
                'package' => $source,
                'destination' => WP_PLUGIN_DIR,
                'clear_destination' => false, // Do not overwrite files.
                'clear_working' => true,
                'hook_extra' => array(
                    'type' => 'plugin',
                    'action' => 'install',
                )
                    ));

            // activating addon 
            $plugin_root_folder = trim($download_name, ".zip");
            $plugin_file = $this->esig_get_addons_file_path($plugin_root_folder);

            $this->esig_addons_enable($plugin_file);
        }

        return true;
    }

    public function esig_addons_install($source, $download_name) {

        if (!function_exists('WP_E_Sig'))
            return;

        $esig = WP_E_Sig();


        // if class not exists add wp updater class 
        if (!class_exists('WP_Upgrader'))
            require ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        // setting destination to plugin folder .


        $wp_installer = new WP_Upgrader();

        $wp_installer->run(array(
            'package' => $source,
            'destination' => WP_PLUGIN_DIR,
            'clear_destination' => false, // Do not overwrite files.
            'clear_working' => true,
            'hook_extra' => array(
                'type' => 'plugin',
                'action' => 'install',
            )
        ));

        // activating addon. 
        $plugin_root_folder = trim($download_name, ".zip");
        $plugin_file = $this->esig_get_addons_file_path($plugin_root_folder);
        $this->esig_addons_enable($plugin_file);

        return true;
    }

    public function esig_addons_update($source, $download_name) {


        if (!function_exists('WP_E_Sig'))
            return;

        $esig = WP_E_Sig();

        if (!class_exists('WP_Upgrader'))
            require ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        // setting destination to plugin folder .


        $wp_installer = new WP_Upgrader();

        $result = $wp_installer->run(array(
            'package' => $source,
            'destination' => WP_PLUGIN_DIR,
            'clear_destination' => true,
            'clear_working' => true,
            'hook_extra' => array(
                'plugin' => $download_name,
                'type' => 'plugin',
                'action' => 'update',
            ),
                ));

        // if update is failed. 
        if (is_wp_error($result)) {
            if (get_transient('esign-auto-downloads')) {
                delete_transient('esign-auto-downloads');
                delete_transient('esign-update-list');
                set_transient('esign-auto-up-failed', 'yes', 300);

                //$this->settings->set('esign_auto_update','');
            }
        }

        return true;
    }

    public function esig_get_addons_file_path($plugin_root_folder) {
        $plugin_files = '';
        if (!is_dir(WP_PLUGIN_DIR . "/" . $plugin_root_folder)) {
            return;
        }

        if ($handle = opendir(WP_PLUGIN_DIR . "/" . $plugin_root_folder)) {
            while (false !== ($entry = readdir($handle))) {
                if (substr($entry, 0, 1) == '.')
                    continue;
                if (substr($entry, -4) == '.php')
                    $plugin_data = get_plugin_data(WP_PLUGIN_DIR . "/$plugin_root_folder/$entry", false, false);
                if (empty($plugin_data['Name'])) {
                    continue;
                } else {
                    $plugin_files = "$plugin_root_folder/$entry";
                    break;
                }
            }
            closedir($handle);
        } else {
            return false;
        }

        return $plugin_files;
    }

    public function esig_addon_activate($plugin_file) {
        if (!current_user_can('activate_plugins'))
            wp_die(__('You do not have sufficient permissions to activate plugins for this site.'));

        $plugins = FALSE;
        $plugins = get_option('active_plugins'); // get active plugins

        if ($plugins) {
            // plugins to active
            $pugins_to_active = array(
                $plugin_file,
            );

            foreach ($pugins_to_active as $plugin) {
                if (!in_array($plugin, $plugins)) {
                    array_push($plugins, $plugin);
                    update_option('active_plugins', $plugins);
                }
            }
        } // end if $plugins

        return true;
    }

    /*     * *
     *  Deactivating addons 
     *  Since 1.1.4
     */

    public function esig_addons_disable($plugin_file) {
        if (!current_user_can('activate_plugins'))
            wp_die(__('You do not have sufficient permissions to deactivate plugins for this site.'));

        deactivate_plugins($plugin_file);

        return true;
    }

    /*     * *
     *  Deactivating addons 
     *  Since 1.1.4
     */

    public function esig_addons_enable($plugin_file) {
        if (!current_user_can('activate_plugins'))
            wp_die(__('You do not have sufficient permissions to deactivate plugins for this site.'));

        activate_plugins($plugin_file);

        return true;
    }

    /*     * *
     *  Deactivating addons 
     *  Since 1.1.4
     */

    public function esig_addons_delete($plugin_folder) {
        if (!current_user_can('activate_plugins'))
            wp_die(__('You do not have sufficient permissions to deactivate plugins for this site.'));


        $plugins = get_plugin_files($plugin_folder);

        $deleted = delete_plugins($plugins);
        if ($deleted) {
            return true;
        }
    }

    public function get_package_price() {
        global $wp_version;

        if (!function_exists('WP_E_Sig'))
            return;

        $esig = WP_E_Sig();

        $api_params = array(
            'esig-remote-request' => 'on',
            'esig_action' => 'pk_price',
            'url' => 'http://www.approveme.me/',
        );

        $request = wp_remote_post('http://www.approveme.me/', array('timeout' => 15, 'sslverify' => false, 'body' => $api_params));

        if (!is_wp_error($request)):

            $request = json_decode(wp_remote_retrieve_body($request));

            if ($this->settings->get_generic('esig_wp_esignature_license_key') && ($this->settings->get_generic('esig_wp_esignature_license_active')) == 'valid') {
                $license_key = 'yes';
            } else {
                $license_key = 'no';
            }
            foreach ($request as $addonlist => $addons) {
                if ($addonlist == "esig-price") {

                    if (($esig_license_type = $this->settings->get_generic('esig_wp_esignature_license_type') ) != 'Business License') {
                        $buisness_price = is_array($addons) ? $addons[0]->amount : null;
                        $professional_price = is_array($addons) ? $addons[1]->amount : null;
                        $individual_price = is_array($addons) ? $addons[2]->amount : null;


                        if (($esig_license_type = $this->settings->get_generic('esig_wp_esignature_license_type') ) == 'Professional License' && $license_key != 'no') {
                            $price = $buisness_price - $professional_price;
                        } elseif (($esig_license_type = $this->settings->get_generic('esig_wp_esignature_license_type') ) == 'Individual License' && $license_key != 'no') {
                            $price = $buisness_price - $individual_price;
                        } else {
                            $price = $buisness_price;
                        }

                        set_transient('esig-addons-price', $price, 12 * HOUR_IN_SECONDS);

                        return $price;
                    } else {
                        set_transient('esig-addons-price', "buisness", 12 * HOUR_IN_SECONDS);
                        return "buisness";
                    }
                }
            }
        else:
            return false;
        endif;
    }

}
