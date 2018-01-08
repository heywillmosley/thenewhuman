<?php

if (!defined('ABSPATH')) {
    exit;
}

class wf_allImpExpCsv_Settings {

    /**
     * Order Exporter Tool
     */
    public static function save_settings() {
        global $wpdb;
        
        $sbc_ftp_server = !empty($_POST['sbc_ftp_server']) ? $_POST['sbc_ftp_server'] : '';
        $sbc_ftp_user = !empty($_POST['sbc_ftp_user']) ? $_POST['sbc_ftp_user'] : '';
        $sbc_ftp_password = !empty($_POST['sbc_ftp_password']) ? $_POST['sbc_ftp_password'] : '';
        $sbc_use_ftps = !empty($_POST['sbc_use_ftps']) ? true : false;
        $sbc_use_pasv = !empty($_POST['sbc_use_pasv']) ? true : false;
        $sbc_enable_ftp_ie = !empty($_POST['sbc_enable_ftp_ie']) ? true : false;


        $cpn_ftp_server = !empty($_POST['cpn_ftp_server']) ? $_POST['cpn_ftp_server'] : '';
        $cpn_ftp_user = !empty($_POST['cpn_ftp_user']) ? $_POST['cpn_ftp_user'] : '';
        $cpn_ftp_password = !empty($_POST['cpn_ftp_password']) ? $_POST['cpn_ftp_password'] : '';
        $cpn_use_ftps = !empty($_POST['cpn_use_ftps']) ? true : false;
        $cpn_use_pasv = !empty($_POST['cpn_use_pasv']) ? true : false;
        $cpn_enable_ftp_ie = !empty($_POST['cpn_enable_ftp_ie']) ? true : false;


        $ord_ftp_server = !empty($_POST['ord_ftp_server']) ? $_POST['ord_ftp_server'] : '';
        $ord_ftp_user = !empty($_POST['ord_ftp_user']) ? $_POST['ord_ftp_user'] : '';
        $ord_ftp_password = !empty($_POST['ord_ftp_password']) ? $_POST['ord_ftp_password'] : '';
        $ord_use_ftps = !empty($_POST['ord_use_ftps']) ? true : false;
        $ord_use_pasv = !empty($_POST['ord_use_pasv']) ? true : false;
        $ord_enable_ftp_ie = !empty($_POST['ord_enable_ftp_ie']) ? true : false;


        $sbc_auto_export = !empty($_POST['sbc_auto_export']) ? $_POST['sbc_auto_export'] : 'Disabled';
        $sbc_auto_export_start_time = !empty($_POST['sbc_auto_export_start_time']) ? $_POST['sbc_auto_export_start_time'] : '';
        $sbc_auto_export_interval = !empty($_POST['sbc_auto_export_interval']) ? $_POST['sbc_auto_export_interval'] : '';

        $sbc_auto_import = !empty($_POST['sbc_auto_import']) ? $_POST['sbc_auto_import'] : 'Disabled';
        $sbc_auto_import_start_time = !empty($_POST['sbc_auto_import_start_time']) ? $_POST['sbc_auto_import_start_time'] : '';
        $sbc_auto_import_interval = !empty($_POST['sbc_auto_import_interval']) ? $_POST['sbc_auto_import_interval'] : '';
        $sbc_auto_import_profile = !empty($_POST['sbc_auto_import_profile']) ? $_POST['sbc_auto_import_profile'] : '';
        $sbc_auto_import_merge = !empty($_POST['sbc_auto_import_merge']) ? true : false;


        $xml_ftp_server = !empty($_POST['xml_ftp_server']) ? $_POST['xml_ftp_server'] : '';
        $xml_ftp_user = !empty($_POST['xml_ftp_user']) ? $_POST['xml_ftp_user'] : '';
        $xml_ftp_password = !empty($_POST['xml_ftp_password']) ? $_POST['xml_ftp_password'] : '';
        $xml_use_ftps = !empty($_POST['xml_use_ftps']) ? true : false;
        $xml_use_pasv = !empty($_POST['xml_use_pasv']) ? true : false;
        $xml_enable_ftp_ie = !empty($_POST['xml_enable_ftp_ie']) ? true : false;

        $cpn_auto_export = !empty($_POST['cpn_auto_export']) ? $_POST['cpn_auto_export'] : 'Disabled';
        $cpn_auto_export_start_time = !empty($_POST['cpn_auto_export_start_time']) ? $_POST['cpn_auto_export_start_time'] : '';
        $cpn_auto_export_interval = !empty($_POST['cpn_auto_export_interval']) ? $_POST['cpn_auto_export_interval'] : '';

        $cpn_auto_import = !empty($_POST['cpn_auto_import']) ? $_POST['cpn_auto_import'] : 'Disabled';
        $cpn_auto_import_start_time = !empty($_POST['cpn_auto_import_start_time']) ? $_POST['cpn_auto_import_start_time'] : '';
        $cpn_auto_import_interval = !empty($_POST['cpn_auto_import_interval']) ? $_POST['cpn_auto_import_interval'] : '';
        $cpn_auto_import_profile = !empty($_POST['cpn_auto_import_profile']) ? $_POST['cpn_auto_import_profile'] : '';
        $cpn_auto_import_merge = !empty($_POST['cpn_auto_import_merge']) ? true : false;




        $ord_auto_export = !empty($_POST['ord_auto_export']) ? $_POST['ord_auto_export'] : 'Disabled';
        $ord_auto_export_start_time = !empty($_POST['ord_auto_export_start_time']) ? $_POST['ord_auto_export_start_time'] : '';
        $ord_auto_export_interval = !empty($_POST['ord_auto_export_interval']) ? $_POST['ord_auto_export_interval'] : '';


        $xml_orderxml_auto_export = !empty($_POST['xml_orderxml_auto_export']) ? $_POST['xml_orderxml_auto_export'] : 'Disabled';
        $xml_orderxml_auto_export_start_time = !empty($_POST['xml_orderxml_auto_export_start_time']) ? $_POST['xml_orderxml_auto_export_start_time'] : '';
        $xml_orderxml_auto_export_interval = !empty($_POST['xml_orderxml_auto_export_interval']) ? $_POST['xml_orderxml_auto_export_interval'] : '';

        $xml_orderxml_auto_import = !empty($_POST['xml_orderxml_auto_import']) ? $_POST['xml_orderxml_auto_import'] : 'Disabled';
        $xml_orderxml_auto_import_start_time = !empty($_POST['xml_orderxml_auto_import_start_time']) ? $_POST['xml_orderxml_auto_import_start_time'] : '';
        $xml_orderxml_auto_import_interval = !empty($_POST['xml_orderxml_auto_import_interval']) ? $_POST['xml_orderxml_auto_import_interval'] : '';
        
        $xml_orderxml_auto_import_merge = !empty($_POST['xml_orderxml_auto_import_merge']) ? true : false;


        $ord_auto_import = !empty($_POST['ord_auto_import']) ? $_POST['ord_auto_import'] : 'Disabled';
        $ord_auto_import_start_time = !empty($_POST['ord_auto_import_start_time']) ? $_POST['ord_auto_import_start_time'] : '';
        $ord_auto_import_interval = !empty($_POST['ord_auto_import_interval']) ? $_POST['ord_auto_import_interval'] : '';
        $ord_auto_import_profile = !empty($_POST['ord_auto_import_profile']) ? $_POST['ord_auto_import_profile'] : '';
        $ord_auto_import_merge = !empty($_POST['ord_auto_import_merge']) ? true : false;

        $settings = array();
        $settings['sbc_ftp_server'] = $sbc_ftp_server;
        $settings['sbc_ftp_user'] = $sbc_ftp_user;
        $settings['sbc_ftp_password'] = $sbc_ftp_password;
        $settings['sbc_use_ftps'] = $sbc_use_ftps;
        $settings['sbc_use_pasv'] = $sbc_use_pasv;
        $settings['sbc_enable_ftp_ie'] = $sbc_enable_ftp_ie;

        $settings['sbc_auto_export'] = $sbc_auto_export;
        $settings['sbc_auto_export_start_time'] = $sbc_auto_export_start_time;
        $settings['sbc_auto_export_interval'] = $sbc_auto_export_interval;

        $settings['sbc_auto_import'] = $sbc_auto_import;
        $settings['sbc_auto_import_start_time'] = $sbc_auto_import_start_time;
        $settings['sbc_auto_import_interval'] = $sbc_auto_import_interval;
        $settings['sbc_auto_import_profile'] = $sbc_auto_import_profile;
        $settings['sbc_auto_import_merge'] = $sbc_auto_import_merge;



        $settings['cpn_ftp_server'] = $cpn_ftp_server;
        $settings['cpn_ftp_user'] = $cpn_ftp_user;
        $settings['cpn_ftp_password'] = $cpn_ftp_password;
        $settings['cpn_use_ftps'] = $cpn_use_ftps;
        $settings['cpn_use_pasv'] = $cpn_use_pasv;
        $settings['cpn_enable_ftp_ie'] = $cpn_enable_ftp_ie;

        $settings['cpn_auto_export'] = $cpn_auto_export;
        $settings['cpn_auto_export_start_time'] = $cpn_auto_export_start_time;
        $settings['cpn_auto_export_interval'] = $cpn_auto_export_interval;

        $settings['cpn_auto_import'] = $cpn_auto_import;
        $settings['cpn_auto_import_start_time'] = $cpn_auto_import_start_time;
        $settings['cpn_auto_import_interval'] = $cpn_auto_import_interval;
        $settings['cpn_auto_import_profile'] = $cpn_auto_import_profile;
        $settings['cpn_auto_import_merge'] = $cpn_auto_import_merge;



        $settings['ord_ftp_server'] = $ord_ftp_server;
        $settings['ord_ftp_user'] = $ord_ftp_user;
        $settings['ord_ftp_password'] = $ord_ftp_password;
        $settings['ord_use_ftps'] = $ord_use_ftps;
        $settings['ord_use_pasv'] = $ord_use_pasv;
        $settings['ord_enable_ftp_ie'] = $ord_enable_ftp_ie;

        $settings['ord_auto_export'] = $ord_auto_export;
        $settings['ord_auto_export_start_time'] = $ord_auto_export_start_time;
        $settings['ord_auto_export_interval'] = $ord_auto_export_interval;

        $settings['ord_auto_import'] = $ord_auto_import;
        $settings['ord_auto_import_start_time'] = $ord_auto_import_start_time;
        $settings['ord_auto_import_interval'] = $ord_auto_import_interval;
        $settings['ord_auto_import_profile'] = $ord_auto_import_profile;
        $settings['ord_auto_import_merge'] = $ord_auto_import_merge;

        $settings['xml_ftp_server'] = $xml_ftp_server;
        $settings['xml_ftp_user'] = $xml_ftp_user;
        $settings['xml_ftp_password'] = $xml_ftp_password;
        $settings['xml_use_ftps'] = $xml_use_ftps;
        $settings['xml_use_pasv'] = $xml_use_pasv;
        $settings['xml_enable_ftp_ie'] = $xml_enable_ftp_ie;

        $settings['xml_orderxml_auto_export'] = $xml_orderxml_auto_export;
        $settings['xml_orderxml_auto_export_start_time'] = $xml_orderxml_auto_export_start_time;
        $settings['xml_orderxml_auto_export_interval'] = $xml_orderxml_auto_export_interval;

        $settings['xml_orderxml_auto_import'] = $xml_orderxml_auto_import;
        $settings['xml_orderxml_auto_import_start_time'] = $xml_orderxml_auto_import_start_time;
        $settings['xml_orderxml_auto_import_interval'] = $xml_orderxml_auto_import_interval;
        
        $settings['xml_orderxml_auto_import_merge'] = $xml_orderxml_auto_import_merge;





        $settings_db = get_option('woocommerce_' . wf_all_imp_exp_ID . '_settings', null);

        $sbc_orig_export_start_inverval = '';
        if (isset($settings_db['sbc_auto_export_start_time']) && isset($settings_db['sbc_auto_export_interval'])) {
            $sbc_orig_export_start_inverval = $settings_db['sbc_auto_export_start_time'] . $settings_db['sbc_auto_export_interval'];
        }

        $sbc_orig_import_start_inverval = '';
        if (isset($settings_db['sbc_auto_import_start_time']) && isset($settings_db['sbc_auto_import_interval'])) {
            $sbc_orig_import_start_inverval = $settings_db['sbc_auto_import_start_time'] . $settings_db['sbc_auto_import_interval'];
        }


        $cpn_orig_export_start_inverval = '';
        if (isset($settings_db['cpn_auto_export_start_time']) && isset($settings_db['cpn_auto_export_interval'])) {
            $cpn_orig_export_start_inverval = $settings_db['cpn_auto_export_start_time'] . $settings_db['cpn_auto_export_interval'];
        }

        $cpn_orig_import_start_inverval = '';
        if (isset($settings_db['cpn_auto_import_start_time']) && isset($settings_db['cpn_auto_import_interval'])) {
            $cpn_orig_import_start_inverval = $settings_db['cpn_auto_import_start_time'] . $settings_db['cpn_auto_import_interval'];
        }


        $ord_orig_export_start_inverval = '';
        if (isset($settings_db['ord_auto_export_start_time']) && isset($settings_db['ord_auto_export_interval'])) {
            $ord_orig_export_start_inverval = $settings_db['ord_auto_export_start_time'] . $settings_db['ord_auto_export_interval'];
        }

        $ord_orig_import_start_inverval = '';
        if (isset($settings_db['ord_auto_import_start_time']) && isset($settings_db['ord_auto_import_interval'])) {
            $ord_orig_import_start_inverval = $settings_db['ord_auto_import_start_time'] . $settings_db['ord_auto_import_interval'];
        }

        $xml_orderxml_orig_export_start_inverval = '';
        if (isset($settings_db['xml_orderxml_auto_export_start_time']) && isset($settings_db['xml_orderxml_auto_export_interval'])) {
            $xml_orderxml_orig_export_start_inverval = $settings_db['xml_orderxml_auto_export_start_time'] . $settings_db['xml_orderxml_auto_export_interval'];
        }

        $xml_orderxml_orig_import_start_inverval = '';
        if (isset($settings_db['xml_orderxml_auto_import_start_time']) && isset($settings_db['xml_orderxml_auto_import_interval'])) {
            $xml_orderxml_orig_import_start_inverval = $settings_db['xml_orderxml_auto_import_start_time'] . $settings_db['xml_orderxml_auto_import_interval'];
        }






        update_option('woocommerce_' . wf_all_imp_exp_ID . '_settings', $settings);



        // clear scheduled export event in case export interval was changed
        if ($sbc_orig_export_start_inverval !== $settings['sbc_auto_export_start_time'] . $settings['sbc_auto_export_interval']) {
            // note this resets the next scheduled execution time to the time options were saved + the interval
            wp_clear_scheduled_hook('hf_subscription_order_csv_im_ex_auto_export');
        }

        // clear scheduled import event in case import interval was changed
        if ($sbc_orig_import_start_inverval !== $settings['sbc_auto_import_start_time'] . $settings['sbc_auto_import_interval']) {
            // note this resets the next scheduled execution time to the time options were saved + the interval
            wp_clear_scheduled_hook('hf_subscription_order_csv_im_ex_auto_import');
        }



        if ($cpn_orig_export_start_inverval !== $settings['cpn_auto_export_start_time'] . $settings['cpn_auto_export_interval']) {
            wp_clear_scheduled_hook('wf_coupon_csv_im_ex_auto_export_coupons');
        }

        if ($cpn_orig_import_start_inverval !== $settings['cpn_auto_import_start_time'] . $settings['cpn_auto_import_interval']) {
            wp_clear_scheduled_hook('wf_coupon_csv_im_ex_auto_import_coupons');
        }



        if ($ord_orig_export_start_inverval !== $settings['ord_auto_export_start_time'] . $settings['ord_auto_export_interval']) {
            wp_clear_scheduled_hook('wf_order_csv_im_ex_auto_export_order');
        }

        if ($ord_orig_import_start_inverval !== $settings['ord_auto_import_start_time'] . $settings['ord_auto_import_interval']) {
            wp_clear_scheduled_hook('wf_order_csv_im_ex_auto_import_order');
        }


        if ($xml_orderxml_orig_export_start_inverval !== $settings['xml_orderxml_auto_export_start_time'] . $settings['xml_orderxml_auto_export_interval']) {
            wp_clear_scheduled_hook('wf_order_xml_im_ex_auto_export_orderxml');
        }

        if ($xml_orderxml_orig_import_start_inverval !== $settings['xml_orderxml_auto_import_start_time'] . $settings['xml_orderxml_auto_import_interval']) {
            wp_clear_scheduled_hook('wf_order_xml_im_ex_auto_import_orderxml');
        }


        wp_redirect(admin_url('/admin.php?page=' . WF_WOOCOMMERCE_ORDER_IM_EX . '&tab=settings'));
        exit;
    }

}
