<?php

/**
 * License handler for E-Signature
 *
 * This class should simplify the process of adding license information
 * to new ESIG extensions.
 *
 * @version 1.1
 */
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

if (!class_exists('ESIG_License')) :

    /**
     * ESIG_License Class
     */
    class ESIG_License {

        private $file;
        private $license;
        private $item_name;
        private $item_shortname;
        private $version;
        private $author;
        private $api_url = 'http://www.approveme.me/';

        /**
         * Class constructor
         *
         * @global  array $edd_options
         * @param string  $_file
         * @param string  $_item_name
         * @param string  $_version
         * @param string  $_author
         * @param string  $_optname
         * @param string  $_api_url
         */
        function __construct($_file, $_item_name, $_version, $_author, $_optname = null, $_api_url = null) {

            $this->file = $_file;
            $this->item_name = $_item_name;
            $this->item_shortname = 'esig_' . preg_replace('/[^a-zA-Z0-9_\s]/', '', str_replace(' ', '_', strtolower($this->item_name)));
            $this->version = $_version;
            //$this->license        = trim( $esig->setting->get_generic($this->item_shortname . '_license_key'));
            $this->author = $_author;
            $this->api_url = is_null($_api_url) ? $this->api_url : $_api_url;
            // Setup hooks
            $this->includes();
            $this->hooks();
            $this->auto_updater();
        }

        /**
         * Include the updater class
         *
         * @access  private
         * @return  void
         */
        private function includes() {

            if (!class_exists('ESIG_Plugin_Updater'))
                require_once 'WP_E_Plugin_Updater.php';
        }

        /**
         * Setup hooks
         *
         * @access  private
         * @return  void
         */
        private function hooks() {
            // Register settings
            add_filter('esig_settings_licenses', array($this, 'settings'), 1);

            // Activate license key on settings save
            add_action('admin_init', array($this, 'activate_license'));

            // Deactivate license key
            add_action('admin_init', array($this, 'deactivate_license'));
        }

        /**
         * Auto updater
         *
         * @access  private
         * @global  array $edd_options
         * @return  void
         */
        private function auto_updater() {

            // Setup the updater
            $esig_updater = new ESIG_Plugin_Updater(
                    $this->api_url, $this->file, array(
                'version' => $this->version,
                'license' => $this->license,
                'item_name' => $this->item_name,
                'item_shortname' => $this->item_shortname,
                'author' => $this->author
                    )
            );
        }

        /**
         * Add license field to settings
         *
         * @access  public
         * @param array   $settings
         * @return  array
         */
        public function settings($settings) {
            $esig_license_settings = array(
                array(
                    'id' => $this->item_shortname . '_license_key',
                    'name' => sprintf(__('%1$s License Key', 'esig'), $this->item_name),
                    'desc' => '',
                    'type' => 'license_key',
                    'options' => array('is_valid_license_option' => $this->item_shortname . '_license_active'),
                    'size' => 'regular'
                )
            );

            return array_merge($settings, $esig_license_settings);
        }

        /**
         * Activate the license key
         *
         * @access  public
         * @return  void
         */
        public function activate_license() {

            if (!function_exists('WP_E_Sig'))
                return;

            $esig = WP_E_Sig();
            //$esig_options = $esig->setting;

            if (!isset($_POST[$this->item_shortname . '_license_key_activate']))
                return;

            if (!isset($_POST[$this->item_shortname . '_license_key']))
                return;

            //if ('valid' == $esig->setting->get_generic($this->item_shortname . '_license_active'))
                       // return;

            $license = sanitize_text_field($_POST[$this->item_shortname . '_license_key']);
            if ($license)
                $esig->setting->set($this->item_shortname . '_license_key', $_POST[$this->item_shortname . '_license_key']);

            // Data to send to the API
            $api_params = array(
                'edd_action' => 'activate_license',
                'license' => $license,
                'item_name' => urlencode($this->item_name),
                'url' => site_url('', 'http')
            );

            $response = wp_remote_post(
                    $this->api_url, array(
                'timeout' => 15,
                'body' => $api_params,
                'sslverify' => false
                    )
            );

            if (!$response) {
                // Call the API
                $response = wp_remote_get(
                        esc_url_raw(add_query_arg($api_params, $this->api_url)), array(
                    'timeout' => 15,
                    'body' => $api_params,
                    'sslverify' => true
                        )
                );
            }
            // Make sure there are no errors
            if (is_wp_error($response))
                return;

            // Decode license data
            $license_data = json_decode(wp_remote_retrieve_body($response));


            if ($license_data->license == "valid") {
                $esig->setting->set_generic($this->item_shortname . '_license_active', $license_data->license);
                $esig->setting->set_generic($this->item_shortname . '_license_key', $license);
                $esig->setting->set_generic($this->item_shortname . '_license_expires', $license_data->expires);
                $esig->setting->set_generic($this->item_shortname . '_customer_email', $license_data->customer_email);
                $esig->setting->set_generic($this->item_shortname . '_license_type', $license_data->license_type);
                $esig->setting->set_generic($this->item_shortname . '_license_name', $license_data->item_name);
                WP_E_Sig()->notice->set('e-sign-red-alert license', Esign_licenses::esig_super_admin() . ' Your license key has been Activated');
            } else {

                WP_E_Sig()->notice->set('e-sign-red-alert license', Esign_licenses::esig_super_admin() . ' It looks like the license you entered no longer exists.  Please <a href="https://www.approveme.me/support">contact support</a> or <a href="https://www.approveme.me/#pricingPlans">purchase a new license here</a>');
            }

            //add_option('esig_license_msg',$license_data->license) ; 
        }

        /**
         * Deactivate the license key
         *
         * @access  public
         * @return  void
         */
        public function deactivate_license() {

            if (!function_exists('WP_E_Sig'))
                return;

            $esig = WP_E_Sig();



            if (!isset($_POST[$this->item_shortname . '_license_key']))
                return;



            // Run on deactivate button press
            if (isset($_POST[$this->item_shortname . '_license_key_deactivate'])) {


                // Data to send to the API
                $api_params = array(
                    'edd_action' => 'deactivate_license',
                    'license' => Esign_licenses::get_license_key(),
                    'item_name' => urlencode($this->item_name),
                    'url' => site_url('', 'http')
                );

                // Call the API
                $response = wp_remote_get(
                        esc_url_raw(add_query_arg($api_params, $this->api_url)), array(
                    'timeout' => 15,
                    'sslverify' => false
                        )
                );

                if (!$response) {
                    $response = wp_remote_post(
                            $this->api_url, array(
                        'timeout' => 15,
                        'body' => $api_params,
                        'sslverify' => false
                            )
                    );
                }
                // Make sure there are no errors
                if (is_wp_error($response))
                    return;

                // Decode the license data
                $license_data = json_decode(wp_remote_retrieve_body($response));


                if ($license_data->license == 'deactivated') {
                    $esig->setting->delete($this->item_shortname . '_license_active');
                    $esig->setting->delete($this->item_shortname . '_license_key');
                    $esig->setting->delete($this->item_shortname . '_customer_email');
                    $esig->setting->delete($this->item_shortname . '_license_type');
                    $esig->setting->delete($this->item_shortname . '_license_name');

                    // add_option('esig_license_msg', $license_data->license);
                    WP_E_Sig()->notice->set('e-sign-red-alert license', Esign_licenses::esig_super_admin() . ' Your license key has been Deactivated.');
                } else {
                    WP_E_Sig()->notice->set('e-sign-red-alert license', Esign_licenses::esig_super_admin() . ' It looks like the license you entered no longer exists.  Please <a href="https://www.approveme.me/support">contact support</a>');
                }
            }
        }

    }

    

    

endif; // end class_exists check


