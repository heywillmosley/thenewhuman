<?php

class WP_E_General extends WP_E_Model {

    public function __construct() {
        parent::__construct();

        $this->settings = new WP_E_Setting();
    }

    /**
     * misc setting to remove all data when plugins files deleted. 
     *
     *
     */
    public function misc_settings() {
        if (isset($_POST['esign_remove_all_data'])) {
            $remove_value = "1";
        } else {
            $remove_value = "";
        }

        // setting auto save and preview option 
        if (isset($_POST['esign_auto_save_data'])) {
            $preview_option = "1";
        } else {
            $preview_option = "";
        }

        if (isset($_POST['esign_auto_update'])) {
            $esign_auto_update = "1";
        } else {
            $esign_auto_update = "";
        }

        $this->settings->set("esign_remove_all_data", $remove_value);
        $this->settings->set("esign_auto_save_data", $preview_option);

        $this->settings->set("esign_auto_update", $esign_auto_update);

        if (isset($_POST['esig_print_option']))
            $this->settings->set("esig_print_option", $_POST['esig_print_option']);
    }

    /**
     * Checking if any extension installed .
     * Since 1.0.1 
     * return void
     * */
    public function checking_extension() {

        $array_Plugins = get_plugins();

        if (!empty($array_Plugins)) {
            foreach ($array_Plugins as $plugin_file => $plugin_data) {
                if (is_plugin_active($plugin_file)) {
                    $plugin_name = $plugin_data['Name'];

                    // if($plugin_name!="WP E-Signature")
                    // {  
                    if (preg_match("/WP E-Signature/", $plugin_name)) {
                        if ($plugin_name != "WP E-Signature") {
                            $this->item_plugshortname = str_replace("WP E-Signature ", "", "$plugin_name");
                        } else {
                            $this->item_plugshortname = $plugin_name;
                        }
                        $this->item_pluginname = 'esig_' . preg_replace('/[^a-zA-Z0-9_\s]/', '', str_replace(' ', '_', strtolower($this->item_plugshortname)));

                        if (!$this->settings->get_generic($this->item_pluginname . '_license_active'))
                            $this->settings->set($this->item_pluginname . '_license_active', 'invalid');


                        if (isset($_GET['page']) && $_GET['page'] == 'esign-licenses-general') {
                            $cssclass = 'nav-tab-active';
                        } else {
                            $cssclass = '';
                        }

                        $Licenses = '<a class="nav-tab  ' . $cssclass . '" href="?page=esign-licenses-general">' . __('Licenses', 'esig') . '</a>';
                    }
                    // }
                }
            }
        } else {
            return;
        }

        return $Licenses;
    }

    /**
     *  creating license form 
     *   Since 1.0.1
     *  @deprecated since version  1.3.1
     * */
    public function making_license_form() {

        $array_Plugins = get_plugins();
        $html = '';
        if (!empty($array_Plugins)) {
            foreach ($array_Plugins as $plugin_file => $plugin_data) {
                if (is_plugin_active($plugin_file)) {
                    $plugin_name = $plugin_data['Name'];


                    if ($plugin_name == "WP E-Signature") {

                        if ($plugin_name != "WP E-Signature") {
                            $this->item_plugshortname = str_replace("WP E-Signature ", "", "$plugin_name");
                        } else {
                            $this->item_plugshortname = $plugin_name;
                        }

                        $this->item_pluginname = 'esig_' . preg_replace('/[^a-zA-Z0-9_\s]/', '', str_replace(' ', '_', strtolower($this->item_plugshortname)));

                        $this->license_active = trim($this->settings->get_generic($this->item_pluginname . '_license_active'));


                        if ($this->license_active == "valid") {
                            $this->license_key = trim($this->settings->get_generic($this->item_pluginname . '_license_key'));
                        } else {
                            $this->license_key = null;
                        }

                        if (!empty($this->license_key)) {
                            $this->output_key = $this->license_key;
                        } else {
                            $this->output_key = '';
                        }

                        $esig_license_type = $this->settings->get_generic($this->item_pluginname . '_license_type');

                        // display license kye last four digit.
                        if (!empty($this->output_key)) {
                            $license_key = str_repeat('*', (strlen($this->output_key) - 4)) . substr($this->output_key, -4, 4);
                            $input_readonly = isset($license_key) ? 'readonly' : "";
                        } else {
                            $license_key = "";
                            $input_readonly = "";
                        }

                        $html .='<tr class="esig-settings-wrap">
								<th><label for="license_key" id="license_key_label">' . $plugin_name . ' License Key <span class="description"> (required)</span></label></th>
								<td><input type="text" name="' . $this->item_pluginname . '_license_key' . '" id="first_name" value="' . $license_key . '" class="regular-text" ' . $input_readonly . ' />';
                        if ($this->license_active == "valid") {

                            $html .='<input type="submit" class="button-appme button" name="' . $this->item_pluginname . '_license_key_deactivate' . '" value="Deactivate License">';
                        }
                        if ($this->license_active == "invalid") {
                            $html .='<input type="submit" class="button-appme button" name="' . $this->item_pluginname . '_license_key_activate' . '" value="Activate License">';
                        }
                        $html .= '</td>
								</tr>';
                        // getting license expire date 
                        $esig_license_expire = $this->settings->get_generic($this->item_pluginname . '_license_expires');

                        if (!empty($license_key)) {
                            if ($this->settings->esig_license_expired()) {
                                $html .='<tr><td colspan="3">' . __('Your e-signature license is expired.', 'esign') . '  </td></tr>';
                            } else {

                                if (isset($esig_license_expire) && !empty($esig_license_expire)) {
                                    $html .= sprintf(__('<tr><td colspan="3">Your e-signature license will expire on %s </td></tr>', 'esign'), $esig_license_expire);
                                }
                            } // expire else end here 
                        }
                    }
                }
            }
        } else {
            return;
        }

        return $html;
    }

    /**
     *   E-signature extension license checking . 
     *   Since 1.0.1 
     *
     *
     * */
    public function license_checking($license, $name) {

        // Data to send to the API
        $api_params = array(
            'edd_action' => 'check_license',
            'license' => $license,
            'item_name' => urlencode($name)
        );

        // Call the API
        $response = wp_remote_get(
                esc_url_raw(add_query_arg($api_params, 'http://www.approveme.me/')), array(
            'timeout' => 15,
            'body' => $api_params,
            'sslverify' => false
                )
        );

        // Make sure there are no errors
        if (is_wp_error($response))
            return;

        // Decode license data
        $license_data = json_decode(wp_remote_retrieve_body($response));

        return $license_data->license;
    }

    /**
     *   E-signature checking requirement . 
     *   Since 1.0.10
     * */
    public function esig_requirement() {

        $msg = '';
        if (!function_exists('mcrypt_create_iv')) {
            $msg .=__('Hey There! WP eSignature requires MCrypt to be installed on your server in order to work properly. MCrypt is often installed on most web hosts by default. For some reason your current hosting provider does not have MCrypt installed. Please contact your hosting provider and request they install MCrypt on your server so you can save a ton of time and money by signing documents using WordPress.-<a href="http://php.net/manual/en/mcrypt.requirements.php" target="_blank">Install Now</a>', 'esig');
        }
        if (get_bloginfo('version') < 3.6) {

            $msg .=__('<strong>Wordpress Update Required:</strong> Your wordpress installation is currently out of date . Wp E-signature requires version 3.6 or greater to work properly.<a href="http://wordpress.org">Update Now</a>', 'esig');
        }

        $msg .= apply_filters('esig-system-requirement', $msg);

        return $msg;
    }

    public static function is_auto_save_enabled() {

        if (!WP_E_Sig()->setting->exists("esign_auto_save_data")) {
            
            return true;
        } elseif (WP_E_Sig()->setting->get_generic("esign_auto_save_data")) {
            return true;
        } else {
            return false;
        }
    }

}
