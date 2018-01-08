<?php
$settings = get_option('woocommerce_' . wf_all_imp_exp_ID . '_settings', null);
//echo '<pre>';print_r($settings);exit;
$sbc_ftp_server = isset($settings['sbc_ftp_server']) ? $settings['sbc_ftp_server'] : '';
$sbc_ftp_user = isset($settings['sbc_ftp_user']) ? $settings['sbc_ftp_user'] : '';
$sbc_ftp_password = isset($settings['sbc_ftp_password']) ? $settings['sbc_ftp_password'] : '';
$sbc_use_ftps = isset($settings['sbc_use_ftps']) ? $settings['sbc_use_ftps'] : '';
$sbc_use_pasv = isset($settings['sbc_use_pasv']) ? $settings['sbc_use_pasv'] : '';
$sbc_enable_ftp_ie = isset($settings['sbc_enable_ftp_ie']) ? $settings['sbc_enable_ftp_ie'] : '';

$cpn_ftp_server = isset($settings['cpn_ftp_server']) ? $settings['cpn_ftp_server'] : '';
$cpn_ftp_user = isset($settings['cpn_ftp_user']) ? $settings['cpn_ftp_user'] : '';
$cpn_ftp_password = isset($settings['cpn_ftp_password']) ? $settings['cpn_ftp_password'] : '';
$cpn_use_ftps = isset($settings['cpn_use_ftps']) ? $settings['cpn_use_ftps'] : '';
$cpn_use_pasv = isset($settings['cpn_use_pasv']) ? $settings['cpn_use_pasv'] : '';
$cpn_enable_ftp_ie = isset($settings['cpn_enable_ftp_ie']) ? $settings['cpn_enable_ftp_ie'] : '';

$ord_ftp_server = isset($settings['ord_ftp_server']) ? $settings['ord_ftp_server'] : '';
$ord_ftp_user = isset($settings['ord_ftp_user']) ? $settings['ord_ftp_user'] : '';
$ord_ftp_password = isset($settings['ord_ftp_password']) ? $settings['ord_ftp_password'] : '';
$ord_use_ftps = isset($settings['ord_use_ftps']) ? $settings['ord_use_ftps'] : '';
$ord_use_pasv = isset($settings['ord_use_pasv']) ? $settings['ord_use_pasv'] : '';
$ord_enable_ftp_ie = isset($settings['ord_enable_ftp_ie']) ? $settings['ord_enable_ftp_ie'] : '';



$sbc_auto_export = isset($settings['sbc_auto_export']) ? $settings['sbc_auto_export'] : 'Disabled';
$sbc_auto_export_start_time = isset($settings['sbc_auto_export_start_time']) ? $settings['sbc_auto_export_start_time'] : '';
$sbc_auto_export_interval = isset($settings['sbc_auto_export_interval']) ? $settings['sbc_auto_export_interval'] : '';

$sbc_auto_import = isset($settings['sbc_auto_import']) ? $settings['sbc_auto_import'] : 'Disabled';
$sbc_auto_import_start_time = isset($settings['sbc_auto_import_start_time']) ? $settings['sbc_auto_import_start_time'] : '';
$sbc_auto_import_interval = isset($settings['sbc_auto_import_interval']) ? $settings['sbc_auto_import_interval'] : '';
$sbc_auto_import_profile = isset($settings['sbc_auto_import_profile']) ? $settings['sbc_auto_import_profile'] : '';
$sbc_auto_import_merge = isset($settings['sbc_auto_import_merge']) ? $settings['sbc_auto_import_merge'] : 0;

wp_localize_script('woocommerce-subscription-order-csv-importer', 'woocommerce_subscription_order_csv_cron_params', array('sbc_enable_ftp_ie' => $sbc_enable_ftp_ie , 'sbc_auto_export' => $sbc_auto_export, 'sbc_auto_import' => $sbc_auto_import));
if ($sbc_scheduled_export_timestamp = wp_next_scheduled('hf_subscription_order_csv_im_ex_auto_export')) {
    $sbc_scheduled_export_desc = sprintf(__('The next export is scheduled on <code>%s</code>', 'wf_order_import_export'), get_date_from_gmt(date('Y-m-d H:i:s', $sbc_scheduled_export_timestamp), wc_date_format() . ' ' . wc_time_format()));
} else {
    $sbc_scheduled_export_desc = __('There is no export scheduled.', 'wf_order_import_export');
}
if ($sbc_scheduled_import_timestamp = wp_next_scheduled('hf_subscription_order_csv_im_ex_auto_import')) {
    $sbc_scheduled_import_desc = sprintf(__('The next import is scheduled on <code>%s</code>', 'wf_order_import_export'), get_date_from_gmt(date('Y-m-d H:i:s', $sbc_scheduled_import_timestamp), wc_date_format() . ' ' . wc_time_format()));
} else {
    $sbc_scheduled_import_desc = __('There is no import scheduled.', 'wf_order_import_export');
}




$cpn_auto_export = isset($settings['cpn_auto_export']) ? $settings['cpn_auto_export'] : 'Disabled';
$cpn_auto_export_start_time = isset($settings['cpn_auto_export_start_time']) ? $settings['cpn_auto_export_start_time'] : '';
$cpn_auto_export_interval = isset($settings['cpn_auto_export_interval']) ? $settings['cpn_auto_export_interval'] : '';

$cpn_auto_import = isset($settings['cpn_auto_import']) ? $settings['cpn_auto_import'] : 'Disabled';
$cpn_auto_import_start_time = isset($settings['cpn_auto_import_start_time']) ? $settings['cpn_auto_import_start_time'] : '';
$cpn_auto_import_interval = isset($settings['cpn_auto_import_interval']) ? $settings['cpn_auto_import_interval'] : '';
$cpn_auto_import_profile = isset($settings['cpn_auto_import_profile']) ? $settings['cpn_auto_import_profile'] : '';
$cpn_auto_import_merge = isset($settings['cpn_auto_import_merge']) ? $settings['cpn_auto_import_merge'] : 0;

wp_localize_script('woocommerce-coupon-csv-importer3', 'woocommerce_coupon_csv_cron_params', array('cpn_enable_ftp_ie' => $cpn_enable_ftp_ie , 'cpn_auto_export' => $cpn_auto_export, 'cpn_auto_import' => $cpn_auto_import));
if ($cpn_scheduled_timestamp = wp_next_scheduled('wf_coupon_csv_im_ex_auto_export_coupons')) {
    $cpn_scheduled_desc = sprintf(__('The next export is scheduled on <code>%s</code>', 'wf_order_import_export'), get_date_from_gmt(date('Y-m-d H:i:s', $cpn_scheduled_timestamp), wc_date_format() . ' ' . wc_time_format()));
} else {
    $cpn_scheduled_desc = __('There is no export scheduled.', 'wf_order_import_export');
}
if ($cpn_scheduled_import_timestamp = wp_next_scheduled('wf_coupon_csv_im_ex_auto_import_coupons')) {
    $cpn_scheduled_import_desc = sprintf(__('The next import is scheduled on <code>%s</code>', 'wf_order_import_export'), get_date_from_gmt(date('Y-m-d H:i:s', $cpn_scheduled_import_timestamp), wc_date_format() . ' ' . wc_time_format()));
} else {
    $cpn_scheduled_import_desc = __('There is no import scheduled.', 'wf_order_import_export');
}





$ord_auto_export = isset($settings['ord_auto_export']) ? $settings['ord_auto_export'] : 'Disabled';
$ord_auto_export_start_time = isset($settings['ord_auto_export_start_time']) ? $settings['ord_auto_export_start_time'] : '';
$ord_auto_export_interval = isset($settings['ord_auto_export_interval']) ? $settings['ord_auto_export_interval'] : '';

$ord_auto_import = isset($settings['ord_auto_import']) ? $settings['ord_auto_import'] : 'Disabled';
$ord_auto_import_start_time = isset($settings['ord_auto_import_start_time']) ? $settings['ord_auto_import_start_time'] : '';
$ord_auto_import_interval = isset($settings['ord_auto_import_interval']) ? $settings['ord_auto_import_interval'] : '';
$ord_auto_import_profile = isset($settings['ord_auto_import_profile']) ? $settings['ord_auto_import_profile'] : '';
$ord_auto_import_merge = isset($settings['ord_auto_import_merge']) ? $settings['ord_auto_import_merge'] : 0;

wp_localize_script('woocommerce-order-csv-importer', 'woocommerce_order_csv_cron_params', array('ord_enable_ftp_ie' => $ord_enable_ftp_ie , 'ord_auto_export' => $ord_auto_export, 'ord_auto_import' => $ord_auto_import));
if ($ord_scheduled_timestamp = wp_next_scheduled('wf_order_csv_im_ex_auto_export_order')) {
    $ord_scheduled_desc = sprintf(__('The next export is scheduled on <code>%s</code>', 'wf_order_import_export'), get_date_from_gmt(date('Y-m-d H:i:s', $ord_scheduled_timestamp), wc_date_format() . ' ' . wc_time_format()));
} else {
    $ord_scheduled_desc = __('There is no export scheduled.', 'wf_order_import_export');
}
if ($ord_scheduled_import_timestamp = wp_next_scheduled('wf_order_csv_im_ex_auto_import_order')) {
    $ord_scheduled_import_desc = sprintf(__('The next import is scheduled on <code>%s</code>', 'wf_order_import_export'), get_date_from_gmt(date('Y-m-d H:i:s', $ord_scheduled_import_timestamp), wc_date_format() . ' ' . wc_time_format()));
} else {
    $ord_scheduled_import_desc = __('There is no import scheduled.', 'wf_order_import_export');
}

$xml_ftp_server             = isset( $settings['xml_ftp_server'] ) ? $settings['xml_ftp_server'] : '';
$xml_ftp_user               = isset( $settings['xml_ftp_user'] ) ? $settings['xml_ftp_user'] : '';
$xml_ftp_password           = isset( $settings['xml_ftp_password'] ) ? $settings['xml_ftp_password'] : '';
$xml_use_ftps               = isset( $settings['xml_use_ftps'] ) ? $settings['xml_use_ftps'] : '';
$xml_use_pasv               = isset( $settings['xml_use_pasv'] ) ? $settings['xml_use_pasv'] : '';
$xml_enable_ftp_ie          = isset( $settings['xml_enable_ftp_ie'] ) ? $settings['xml_enable_ftp_ie'] : '';


$xml_orderxml_auto_export = isset($settings['xml_orderxml_auto_export']) ? $settings['xml_orderxml_auto_export'] : 'Disabled';
$xml_orderxml_auto_export_start_time = isset($settings['xml_orderxml_auto_export_start_time']) ? $settings['xml_orderxml_auto_export_start_time'] : '';
$xml_orderxml_auto_export_interval = isset($settings['xml_orderxml_auto_export_interval']) ? $settings['xml_orderxml_auto_export_interval'] : '';

$xml_orderxml_auto_import = isset($settings['xml_orderxml_auto_import']) ? $settings['xml_orderxml_auto_import'] : 'Disabled';
$xml_orderxml_auto_import_start_time = isset($settings['xml_orderxml_auto_import_start_time']) ? $settings['xml_orderxml_auto_import_start_time'] : '';
$xml_orderxml_auto_import_interval = isset($settings['xml_orderxml_auto_import_interval']) ? $settings['xml_orderxml_auto_import_interval'] : '';

$xml_orderxml_auto_import_merge = isset($settings['xml_orderxml_auto_import_merge']) ? $settings['xml_orderxml_auto_import_merge'] : 0;

wp_localize_script('woocommerce-order-xml-importer', 'woocommerce_order_xml_cron_params', array('xml_enable_ftp_ie' => $xml_enable_ftp_ie , 'xml_orderxml_auto_export' => $xml_orderxml_auto_export, 'xml_orderxml_auto_import' => $xml_orderxml_auto_import));
if ($xml_orderxml_scheduled_timestamp = wp_next_scheduled('wf_order_xml_im_ex_auto_export_orderxml')) {
    $xml_orderxml_scheduled_desc = sprintf(__('The next export is scheduled on <code>%s</code>', 'wf_customer_import_export'), get_date_from_gmt(date('Y-m-d H:i:s', $xml_orderxml_scheduled_timestamp), wc_date_format() . ' ' . wc_time_format()));
} else {
    $xml_orderxml_scheduled_desc = __('There is no export scheduled.', 'wf_customer_import_export');
}
if ($xml_orderxml_scheduled_import_timestamp = wp_next_scheduled('wf_order_xml_im_ex_auto_import_orderxml')) {
    $xml_orderxml_scheduled_import_desc = sprintf(__('The next import is scheduled on <code>%s</code>', 'wf_customer_import_export'), get_date_from_gmt(date('Y-m-d H:i:s', $xml_orderxml_scheduled_import_timestamp), wc_date_format() . ' ' . wc_time_format()));
} else {
    $xml_orderxml_scheduled_import_desc = __('There is no import scheduled.', 'wf_customer_import_export');
}

?>
<div class="tool-box">
    <form action="<?php echo admin_url('admin.php?page=wf_woocommerce_order_im_ex&action=settings'); ?>" method="post">










        <table class="form-table">
            <tr>
                <th>
                    <h3 class="title"><?php _e('FTP Settings for Export Orders', 'wf_order_import_export'); ?></h3>
                </th>
            </tr>
            <tr>
                <th>
                    <label for="ord_enable_ftp_ie"><?php _e('Enable FTP', 'wf_order_import_export'); ?></label>
                </th>
                <td>
                    <input type="checkbox" name="ord_enable_ftp_ie" id="ord_enable_ftp_ie" class="checkbox" <?php checked($ord_enable_ftp_ie, 1); ?> />
                </td>
            </tr>
            <table class="form-table" id="ord_export_section_all">
            <tr>
                <th>
                    <label for="ord_ftp_server"><?php _e('FTP Server Host/IP', 'wf_order_import_export'); ?></label>
                </th>
                <td>
                    <input type="text" name="ord_ftp_server" id="ord_ftp_server" placeholder="<?php _e('XXX.XXX.XXX.XXX', 'wf_order_import_export'); ?>" value="<?php echo $ord_ftp_server; ?>" class="input-text" />
                </td>
            </tr>
            <tr>
                <th>
                    <label for="ord_ftp_user"><?php _e('FTP User Name', 'wf_order_import_export'); ?></label>
                </th>
                <td>
                    <input type="text" name="ord_ftp_user" id="ord_ftp_user" placeholder="" value="<?php echo $ord_ftp_user; ?>" class="input-text" />
                </td>
            </tr>
            <tr>
                <th>
                    <label for="ord_ftp_password"><?php _e('FTP Password', 'wf_order_import_export'); ?></label>
                </th>
                <td>
                    <input type="password" name="ord_ftp_password" id="ord_ftp_password" placeholder="" value="<?php echo $ord_ftp_password; ?>" class="input-text" />
                </td>
            </tr>
            <tr>
                <th>
                    <label for="ord_use_ftps"><?php _e('Use FTPS', 'wf_order_import_export'); ?></label>
                </th>
                <td>
                    <input type="checkbox" name="ord_use_ftps" id="ord_use_ftps" class="checkbox" <?php checked($ord_use_ftps, 1); ?> />
                </td>
            </tr>
            <tr>
                <th>
                    <label for="ord_use_pasv"><?php _e('Enable Passive mode', 'wf_order_import_export'); ?></label>
                </th>
                <td>
                    <input type="checkbox" name="ord_use_pasv" id="ord_use_pasv" class="checkbox" <?php checked($ord_use_pasv, 1); ?> />
                </td>
            </tr>
            <tr>
                <th>
                    <label for="ord_auto_export"><?php _e('Automatically Export Orders', 'wf_order_import_export'); ?></label>
                </th>
                <td>
                    <select class="" style="" id="ord_auto_export" name="ord_auto_export">
                        <option <?php if ($ord_auto_export === 'Disabled') echo 'selected'; ?> value="Disabled"><?php _e('Disabled', 'wf_order_import_export'); ?></option>
                        <option <?php if ($ord_auto_export === 'Enabled') echo 'selected'; ?> value="Enabled"><?php _e('Enabled', 'wf_order_import_export'); ?></option>
                    </select>
                </td>
            </tr>
            <tbody class="ord_export_section">
                <tr>
                    <th>
                        <label for="ord_auto_export_start_time"><?php _e('Export Start Time', 'wf_order_import_export'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="ord_auto_export_start_time" id="ord_auto_export_start_time"  value="<?php echo $ord_auto_export_start_time; ?>"/>
                        <span class="description"><?php echo sprintf(__('Local time is <code>%s</code>.', 'wf_order_import_export'), date_i18n(wc_time_format())) . ' ' . $ord_scheduled_desc; ?></span>
                        <br/>
                        <span class="description"><?php _e('<code>Enter like 6:18pm or 12:27am</code>', 'wf_order_import_export'); ?></span>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="ord_auto_export_interval"><?php _e('Export Interval [ Minutes ]', 'wf_order_import_export'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="ord_auto_export_interval" id="ord_auto_export_interval"  value="<?php echo $ord_auto_export_interval; ?>"  />
                    </td>
                </tr>
            </tbody>





            <tr>
                <th>
                    <label for="ord_auto_import"><?php _e('Automatically Import Orders', 'wf_order_import_export'); ?></label>
                </th>
                <td>
                    <select class="" style="" id="ord_auto_import" name="ord_auto_import">
                        <option <?php if ($ord_auto_import === 'Disabled') echo 'selected'; ?> value="Disabled"><?php _e('Disabled', 'wf_order_import_export'); ?></option>
                        <option <?php if ($ord_auto_import === 'Enabled') echo 'selected'; ?> value="Enabled"><?php _e('Enabled', 'wf_order_import_export'); ?></option>
                    </select>
                </td>
            </tr>
            <tbody class="ord_import_section">
                <tr>
                    <th>
                        <label for="ord_auto_import_start_time"><?php _e('Import Start Time', 'wf_order_import_export'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="ord_auto_import_start_time" id="ord_auto_import_start_time"  value="<?php echo $ord_auto_import_start_time; ?>"/>
                        <span class="description"><?php echo sprintf(__('Local time is <code>%s</code>.', 'wf_order_import_export'), date_i18n(wc_time_format())) . ' ' . $ord_scheduled_import_desc; ?></span>
                        <br/>
                        <span class="description"><?php _e('<code>Enter like 6:18pm or 12:27am</code>', 'wf_order_import_export'); ?></span>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="ord_auto_import_interval"><?php _e('Import Interval [ Minutes ]', 'wf_order_import_export'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="ord_auto_import_interval" id="ord_auto_import_interval"  value="<?php echo $ord_auto_import_interval; ?>"  />
                    </td>
                </tr>



                <tr>
                    <th>
                        <label for="ord_auto_import_merge"><?php _e('Update Orders if exist', 'wf_order_import_export'); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" name="ord_auto_import_merge" id="ord_auto_import_merge"  class="checkbox" <?php checked($ord_auto_import_merge, 1); ?> />
                    </td>
                </tr>

                <?php
                $ord_mapping_from_db = get_option('wf_order_csv_imp_exp_mapping');
                if (!empty($ord_mapping_from_db)) {
                    ?>
                    <tr>
                        <th>
                            <label for="ord_auto_import_profile"><?php _e('Select a mapping file.'); ?></label>
                        </th>
                        <td>
                            <select name="ord_auto_import_profile" id="ord_auto_import_profile">
                                <option value="">--Select--</option>
                                <?php foreach ($ord_mapping_from_db as $key => $value) { ?>
                                    <option value="<?php echo $key; ?>" <?php selected($key, $ord_auto_import_profile); ?>><?php echo $key; ?></option>

                                <?php } ?>
                            </select>
                        </td>
                    </tr>
                <?php } ?>

            </tbody>
        </table>
    </table>





        <table class="form-table">
            <tr>
                <th>
                    <h3 class="title"><?php _e('FTP Settings for Export Subscriptions', 'wf_order_import_export'); ?></h3>
                </th>
            </tr>
            <tr>
                <th>
                    <label for="sbc_enable_ftp_ie"><?php _e('Enable FTP', 'wf_order_import_export'); ?></label>
                </th>
                <td>
                    <input type="checkbox" name="sbc_enable_ftp_ie" id="sbc_enable_ftp_ie" class="checkbox" <?php checked($sbc_enable_ftp_ie, 1); ?> />
                </td>
            </tr>
            <table class="form-table" id="sbc_export_section_all">
            <tr>
                <th>
                    <label for="sbc_ftp_server"><?php _e('FTP Server Host/IP', 'wf_order_import_export'); ?></label>
                </th>
                <td>
                    <input type="text" name="sbc_ftp_server" id="sbc_ftp_server" placeholder="<?php _e('XXX.XXX.XXX.XXX', 'wf_order_import_export'); ?>" value="<?php echo $sbc_ftp_server; ?>" class="input-text" />
                </td>
            </tr>
            <tr>
                <th>
                    <label for="sbc_ftp_user"><?php _e('FTP User Name', 'wf_order_import_export'); ?></label>
                </th>
                <td>
                    <input type="text" name="sbc_ftp_user" id="sbc_ftp_user" value="<?php echo $sbc_ftp_user; ?>" class="input-text" />
                </td>
            </tr>
            <tr>
                <th>
                    <label for="sbc_ftp_password"><?php _e('FTP Password', 'wf_order_import_export'); ?></label>
                </th>
                <td>
                    <input type="password" name="sbc_ftp_password" id="sbc_ftp_password"  value="<?php echo $sbc_ftp_password; ?>" class="input-text" />
                </td>
            </tr>
            <tr>
                <th>
                    <label for="sbc_use_ftps"><?php _e('Use FTPS', 'wf_order_import_export'); ?></label>
                </th>
                <td>
                    <input type="checkbox" name="sbc_use_ftps" id="sbc_use_ftps" class="checkbox" <?php checked($sbc_use_ftps, 1); ?> />
                </td>
            </tr>
            <tr>
                <th>
                    <label for="sbc_use_pasv"><?php _e('Enable Passive mode', 'wf_order_import_export'); ?></label>
                </th>
                <td>
                    <input type="checkbox" name="sbc_use_pasv" id="sbc_use_pasv" class="checkbox" <?php checked($sbc_use_pasv, 1); ?> />
                </td>
            </tr>


            <tr>
                <th>
                    <label for="sbc_auto_export"><?php _e('Automatically Export Subscriptions', 'wf_order_import_export'); ?></label>
                </th>
                <td>
                    <select class="" style="" id="sbc_auto_export" name="sbc_auto_export">
                        <option <?php if ($sbc_auto_export === 'Disabled') echo 'selected'; ?> value="Disabled"><?php _e('Disabled', 'wf_order_import_export'); ?></option>
                        <option <?php if ($sbc_auto_export === 'Enabled') echo 'selected'; ?> value="Enabled"><?php _e('Enabled', 'wf_order_import_export'); ?></option>
                    </select>
                </td>
            </tr>
            <tbody class="sbc_export_section">
                <tr>
                    <th>
                        <label for="sbc_auto_export_start_time"><?php _e('Export Start Time', 'wf_order_import_export'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="sbc_auto_export_start_time" id="sbc_auto_export_start_time"  value="<?php echo $sbc_auto_export_start_time; ?>"/>
                        <span class="description"><?php echo sprintf(__('Local time is <code>%s</code>.', 'wf_order_import_export'), date_i18n(wc_time_format())) . ' ' . $sbc_scheduled_export_desc; ?></span>
                        <br/>
                        <span class="description"><?php _e('<code>Enter like 6:18pm or 12:27am</code>', 'wf_order_import_export'); ?></span>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="sbc_auto_export_interval"><?php _e('Export Interval [ Minutes ]', 'wf_order_import_export'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="sbc_auto_export_interval" id="sbc_auto_export_interval"  value="<?php echo $sbc_auto_export_interval; ?>"  />
                    </td>
                </tr>
            </tbody>





            <tr>
                <th>
                    <label for="sbc_auto_import"><?php _e('Automatically Import Subscriptions', 'wf_order_import_export'); ?></label>
                </th>
                <td>
                    <select class="" style="" id="sbc_auto_import" name="sbc_auto_import">
                        <option <?php if ($sbc_auto_import === 'Disabled') echo 'selected'; ?> value="Disabled"><?php _e('Disabled', 'wf_order_import_export'); ?></option>
                        <option <?php if ($sbc_auto_import === 'Enabled') echo 'selected'; ?> value="Enabled"><?php _e('Enabled', 'wf_order_import_export'); ?></option>
                    </select>
                </td>
            </tr>
            <tbody class="sbc_import_section">
                <tr>
                    <th>
                        <label for="sbc_auto_import_start_time"><?php _e('Import Start Time', 'wf_order_import_export'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="sbc_auto_import_start_time" id="sbc_auto_import_start_time"  value="<?php echo $sbc_auto_import_start_time; ?>"/>
                        <span class="description"><?php echo sprintf(__('Local time is <code>%s</code>.', 'wf_order_import_export'), date_i18n(wc_time_format())) . ' ' . $sbc_scheduled_import_desc; ?></span>
                        <br/>
                        <span class="description"><?php _e('<code>Enter like 6:18pm or 12:27am</code>', 'wf_order_import_export'); ?></span>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="sbc_auto_import_interval"><?php _e('Import Interval [ Minutes ]', 'wf_order_import_export'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="sbc_auto_import_interval" id="sbc_auto_import_interval"  value="<?php echo $sbc_auto_import_interval; ?>"  />
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="sbc_auto_import_merge"><?php _e('Merge Orders if exist', 'wf_order_import_export'); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" name="sbc_auto_import_merge" id="sbc_auto_import_merge"  class="checkbox" <?php checked($sbc_auto_import_merge, 1); ?> />
                    </td>
                </tr>

                <?php
                $sbc_mapping_from_db = get_option('wf_subcription_order_csv_imp_exp_mapping');
                if (!empty($sbc_mapping_from_db)) {
                    ?>
                    <tr>
                        <th>
                            <label for="sbc_auto_import_profile"><?php _e('Select a mapping file.'); ?></label>
                        </th>
                        <td>
                            <select name="sbc_auto_import_profile" id="sbc_auto_import_profile">
                                <option value="">--Select--</option>
                                <?php foreach ($sbc_mapping_from_db as $key => $value) { ?>
                                    <option value="<?php echo $key; ?>" <?php selected($key, $sbc_auto_import_profile); ?>><?php echo $key; ?></option>

                                <?php } ?>
                            </select>
                        </td>
                    </tr>
                <?php } ?>

            </tbody>        


            </table>


        </table>









        <table class="form-table">
            <tr>
                <th>
                    <h3 class="title"><?php _e('FTP Settings for Export Coupons', 'wf_order_import_export'); ?></h3>
                </th>
            </tr>
            <tr>
                <th>
                    <label for="cpn_enable_ftp_ie"><?php _e('Enable FTP', 'wf_order_import_export'); ?></label>
                </th>
                <td>
                    <input type="checkbox" name="cpn_enable_ftp_ie" id="cpn_enable_ftp_ie" class="checkbox" <?php checked($cpn_enable_ftp_ie, 1); ?> />
                </td>
            </tr>
            <table class="form-table" id="cpn_export_section_all">
            <tr>
                <th>
                    <label for="cpn_ftp_server"><?php _e('FTP Server Host/IP', 'wf_order_import_export'); ?></label>
                </th>
                <td>
                    <input type="text" name="cpn_ftp_server" id="cpn_ftp_server" placeholder="<?php _e('XXX.XXX.XXX.XXX', 'wf_order_import_export'); ?>" value="<?php echo $cpn_ftp_server; ?>" class="input-text" />
                </td>
            </tr>
            <tr>
                <th>
                    <label for="cpn_ftp_user"><?php _e('FTP User Name', 'wf_order_import_export'); ?></label>
                </th>
                <td>
                    <input type="text" name="cpn_ftp_user" id="cpn_ftp_user" placeholder="" value="<?php echo $cpn_ftp_user; ?>" class="input-text" />
                </td>
            </tr>
            <tr>
                <th>
                    <label for="cpn_ftp_password"><?php _e('FTP Password', 'wf_order_import_export'); ?></label>
                </th>
                <td>
                    <input type="password" name="cpn_ftp_password" id="cpn_ftp_password" placeholder="" value="<?php echo $cpn_ftp_password; ?>" class="input-text" />
                </td>
            </tr>
            <tr>
                <th>
                    <label for="cpn_use_ftps"><?php _e('Use FTPS', 'wf_order_import_export'); ?></label>
                </th>
                <td>
                    <input type="checkbox" name="cpn_use_ftps" id="cpn_use_ftps" class="checkbox" <?php checked($cpn_use_ftps, 1); ?> />
                </td>
            </tr>
            <tr>
                <th>
                    <label for="cpn_use_pasv"><?php _e('Enable Passive mode', 'wf_order_import_export'); ?></label>
                </th>
                <td>
                    <input type="checkbox" name="cpn_use_pasv" id="cpn_use_pasv" class="checkbox" <?php checked($cpn_use_pasv, 1); ?> />
                </td>
            </tr>
            <tr>
                <th>
                    <label for="cpn_auto_export"><?php _e('Automatically Export Coupons', 'wf_order_import_export'); ?></label>
                </th>
                <td>
                    <select class="" style="" id="cpn_auto_export" name="cpn_auto_export">
                        <option <?php if ($cpn_auto_export === 'Disabled') echo 'selected'; ?> value="Disabled"><?php _e('Disabled', 'wf_order_import_export'); ?></option>
                        <option <?php if ($cpn_auto_export === 'Enabled') echo 'selected'; ?> value="Enabled"><?php _e('Enabled', 'wf_order_import_export'); ?></option>
                    </select>
                </td>
            </tr>
            <tbody class="cpn_export_section">
                <tr>
                    <th>
                        <label for="cpn_auto_export_start_time"><?php _e('Export Start Time', 'wf_order_import_export'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="cpn_auto_export_start_time" id="cpn_auto_export_start_time"  value="<?php echo $cpn_auto_export_start_time; ?>"/>
                        <span class="description"><?php echo sprintf(__('Local time is <code>%s</code>.', 'wf_order_import_export'), date_i18n(wc_time_format())) . ' ' . $cpn_scheduled_desc; ?></span>
                        <br/>
                        <span class="description"><?php _e('<code>Enter like 6:18pm or 12:27am</code>', 'wf_order_import_export'); ?></span>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="cpn_auto_export_interval"><?php _e('Export Interval [ Minutes ]', 'wf_order_import_export'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="cpn_auto_export_interval" id="cpn_auto_export_interval"  value="<?php echo $cpn_auto_export_interval; ?>"  />
                    </td>
                </tr>
            </tbody>





            <tr>
                <th>
                    <label for="cpn_auto_import"><?php _e('Automatically Import Coupons', 'wf_order_import_export'); ?></label>
                </th>
                <td>
                    <select class="" style="" id="cpn_auto_import" name="cpn_auto_import">
                        <option <?php if ($cpn_auto_import === 'Disabled') echo 'selected'; ?> value="Disabled"><?php _e('Disabled', 'wf_order_import_export'); ?></option>
                        <option <?php if ($cpn_auto_import === 'Enabled') echo 'selected'; ?> value="Enabled"><?php _e('Enabled', 'wf_order_import_export'); ?></option>
                    </select>
                </td>
            </tr>
            <tbody class="cpn_import_section">
                <tr>
                    <th>
                        <label for="cpn_auto_import_start_time"><?php _e('Import Start Time', 'wf_order_import_export'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="cpn_auto_import_start_time" id="cpn_auto_import_start_time"  value="<?php echo $cpn_auto_import_start_time; ?>"/>
                        <span class="description"><?php echo sprintf(__('Local time is <code>%s</code>.', 'wf_order_import_export'), date_i18n(wc_time_format())) . ' ' . $cpn_scheduled_import_desc; ?></span>
                        <br/>
                        <span class="description"><?php _e('<code>Enter like 6:18pm or 12:27am</code>', 'wf_order_import_export'); ?></span>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="cpn_auto_import_interval"><?php _e('Import Interval [ Minutes ]', 'wf_order_import_export'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="cpn_auto_import_interval" id="cpn_auto_import_interval"  value="<?php echo $cpn_auto_import_interval; ?>"  />
                    </td>
                </tr>



                <tr>
                    <th>
                        <label for="cpn_auto_import_merge"><?php _e('Merge Coupons if exist', 'wf_order_import_export'); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" name="cpn_auto_import_merge" id="cpn_auto_import_merge"  class="checkbox" <?php checked($cpn_auto_import_merge, 1); ?> />
                    </td>
                </tr>

                <?php
                $cpn_mapping_from_db = get_option('wf_cpn_csv_imp_exp_mapping');
                if (!empty($cpn_mapping_from_db)) {
                    ?>
                    <tr>
                        <th>
                            <label for="cpn_auto_import_profile"><?php _e('Select a mapping file.'); ?></label>
                        </th>
                        <td>
                            <select name="cpn_auto_import_profile" id="cpn_auto_import_profile">
                                <option value="">--Select--</option>
                                <?php foreach ($cpn_mapping_from_db as $key => $value) { ?>
                                    <option value="<?php echo $key; ?>" <?php selected($key, $cpn_auto_import_profile); ?>><?php echo $key; ?></option>

                                <?php } ?>
                            </select>
                        </td>
                    </tr>
                <?php } ?>

            </table>

        </table>

    <table class="form-table">
            <tr>
                <th>
                    <h3 class="title"><?php _e('FTP Settings for Export Order XML', 'wf_order_import_export'); ?></h3>
                </th>
            </tr>
            <tr>
                <th>
                    <label for="xml_enable_ftp_ie"><?php _e( 'Enable FTP', 'wf_order_import_export' ); ?></label>
                </th>
                <td>
                    <input type="checkbox" name="xml_enable_ftp_ie" id="xml_enable_ftp_ie" class="checkbox" <?php checked( $xml_enable_ftp_ie, 1 ); ?> />
                </td>
            </tr>
                        <table class="form-table" id="xml_orderxml_export_section_all">
            <tr>
                <th>
                    <label for="xml_ftp_server"><?php _e( 'FTP Server Host/IP', 'wf_order_import_export' ); ?></label>
                </th>
                <td>
                    <input type="text" name="xml_ftp_server" id="xml_ftp_server" placeholder="<?php _e('XXX.XXX.XXX.XXX', 'wf_order_import_export'); ?>" value="<?php echo $xml_ftp_server; ?>" class="input-text" />
                </td>
            </tr>
            <tr>
                <th>
                    <label for="xml_ftp_user"><?php _e( 'FTP User Name', 'wf_order_import_export' ); ?></label>
                </th>
                <td>
                    <input type="text" name="xml_ftp_user" id="xml_ftp_user" value="<?php echo $xml_ftp_user; ?>" class="input-text" />
                </td>
            </tr>
            <tr>
                <th>
                    <label for="xml_ftp_password"><?php _e( 'FTP Password', 'wf_order_import_export' ); ?></label>
                </th>
                <td>
                    <input type="password" name="xml_ftp_password" id="xml_ftp_password"  value="<?php echo $xml_ftp_password; ?>" class="input-text" />
                </td>
            </tr>
            <tr>
                <th>
                    <label for="xml_use_ftps"><?php _e( 'Use FTPS', 'wf_order_import_export' ); ?></label>
                </th>
                <td>
                    <input type="checkbox" name="xml_use_ftps" id="xml_use_ftps" class="checkbox" <?php checked( $xml_use_ftps, 1 ); ?> />
                </td>
            </tr>
            <tr>
                <th>
                    <label for="xml_use_pasv"><?php _e( 'Enable Passive mode', 'wf_order_import_export' ); ?></label>
                </th>
                <td>
                    <input type="checkbox" name="xml_use_pasv" id="xml_use_pasv" class="checkbox" <?php checked( $xml_use_pasv, 1 ); ?> />
                </td>
            </tr>                        
                   
            <tr>
                <th>
                    <label for="xml_orderxml_auto_export"><?php _e('Automatically Export orders', 'wf_order_import_export'); ?></label>
                </th>
                <td>
                    <select class="" style="" id="xml_orderxml_auto_export" name="xml_orderxml_auto_export">
                        <option <?php if ($xml_orderxml_auto_export === 'Disabled') echo 'selected'; ?> value="Disabled"><?php _e('Disabled', 'wf_order_import_export'); ?></option>
                        <option <?php if ($xml_orderxml_auto_export === 'Enabled') echo 'selected'; ?> value="Enabled"><?php _e('Enabled', 'wf_order_import_export'); ?></option>
                    </select>
                </td>
            </tr>
            <tbody class="xml_orderxml_export_section">
                <tr>
                    <th>
                        <label for="xml_orderxml_auto_export_start_time"><?php _e('Export Start Time', 'wf_order_import_export'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="xml_orderxml_auto_export_start_time" id="xml_orderxml_auto_export_start_time"  value="<?php echo $xml_orderxml_auto_export_start_time; ?>"/>
                        <span class="description"><?php echo sprintf(__('Local time is <code>%s</code>.', 'wf_order_import_export'), date_i18n(wc_time_format())) . ' ' . $xml_orderxml_scheduled_desc; ?></span>
                        <br/>
                        <span class="description"><?php _e('<code>Enter like 6:18pm or 12:27am</code>', 'wf_order_import_export'); ?></span>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="orderxml_auto_export_interval"><?php _e('Export Interval [ Minutes ]', 'wf_order_import_export'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="xml_orderxml_auto_export_interval" id="xml_orderxml_auto_export_interval"  value="<?php echo $xml_orderxml_auto_export_interval; ?>"  />
                    </td>
                </tr>
            </tbody>


            <tr>
                <th>
                    <label for="xml_orderxml_auto_import"><?php _e('Automatically Import Orders', 'wf_order_import_export'); ?></label>
                </th>
                <td>
                    <select class="" style="" id="xml_orderxml_auto_import" name="xml_orderxml_auto_import">
                        <option <?php if ($xml_orderxml_auto_import === 'Disabled') echo 'selected'; ?> value="Disabled"><?php _e('Disabled', 'wf_order_import_export'); ?></option>
                        <option <?php if ($xml_orderxml_auto_import === 'Enabled') echo 'selected'; ?> value="Enabled"><?php _e('Enabled', 'wf_order_import_export'); ?></option>
                    </select>
                </td>
            </tr>
            <tbody class="xml_orderxml_import_section">
                <tr>
                    <th>
                        <label for="xml_orderxml_auto_import_start_time"><?php _e('Import Start Time', 'wf_order_import_export'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="xml_orderxml_auto_import_start_time" id="xml_orderxml_auto_import_start_time"  value="<?php echo $xml_orderxml_auto_import_start_time; ?>"/>
                        <span class="description"><?php echo sprintf(__('Local time is <code>%s</code>.', 'wf_order_import_export'), date_i18n(wc_time_format())) . ' ' . $xml_orderxml_scheduled_import_desc; ?></span>
                        <br/>
                        <span class="description"><?php _e('<code>Enter like 6:18pm or 12:27am</code>', 'wf_order_import_export'); ?></span>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="xml_orderxml_auto_import_interval"><?php _e('Import Interval [ Minutes ]', 'wf_order_import_export'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="xml_orderxml_auto_import_interval" id="xml_orderxml_auto_import_interval"  value="<?php echo $xml_orderxml_auto_import_interval; ?>"  />
                    </td>
                </tr>



                <tr>
                    <th>
                        <label for="xml_orderxml_auto_import_merge"><?php _e('Update Order if Exist', 'wf_order_import_export'); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" name="xml_orderxml_auto_import_merge" id="xml_orderxml_auto_import_merge"  class="checkbox" <?php checked($xml_orderxml_auto_import_merge, 1); ?> />
                    </td>
                </tr>


            </tbody>
                        </table>
       
                        
        </table>




        <p class="submit"><input type="submit" class="button button-primary" value="<?php _e('Save Settings', 'wf_order_import_export'); ?>" /></p>

    </form>
</div>