<?php
/**
 * WordPress Importer class for managing the import process of a CSV file
 *
 * @package WordPress
 * @subpackage Importer
 */
if (!class_exists('WP_Importer'))
    return;

class wf_subcription_orderImpExpCsv_Order_Import extends WP_Importer {

    var $id;
    var $file_url;
    var $delimiter;
    var $profile;
    var $merge_empty_cells;
    var $processed_terms = array();
    var $processed_posts = array();
    var $merged = 0;
    var $skipped = 0;
    var $imported = 0;
    var $errored = 0;
    // Results
    var $import_results = array();

    /**
     * Constructor
     */
    public function __construct() {

        $this->log = new WC_Logger();
        $this->import_page = 'woocommerce_wf_subscription_order_csv';
        $this->file_url_import_enabled = apply_filters('woocommerce_csv_product_file_url_import_enabled', true);
    }

    public static $membership_plans = null;
    public static $all_virtual = true;
    public static $user_meta_fields = array(
        '_billing_first_name', // Billing Address Info
        '_billing_last_name',
        '_billing_company',
        '_billing_address_1',
        '_billing_address_2',
        '_billing_city',
        '_billing_state',
        '_billing_postcode',
        '_billing_country',
        '_billing_email',
        '_billing_phone',
        '_shipping_first_name', // Shipping Address Info
        '_shipping_last_name',
        '_shipping_company',
        '_shipping_address_1',
        '_shipping_address_2',
        '_shipping_city',
        '_shipping_state',
        '_shipping_postcode',
        '_shipping_country',
    );

    /**
     * Registered callback function for the WordPress Importer
     *
     * Manages the three separate stages of the CSV import process
     */
    public function dispatch() {
        global $woocommerce, $wpdb;

        if (!empty($_POST['delimiter'])) {
            $this->delimiter = stripslashes(trim($_POST['delimiter']));
        } else if (!empty($_GET['delimiter'])) {
            $this->delimiter = stripslashes(trim($_GET['delimiter']));
        }

        if (!$this->delimiter)
            $this->delimiter = ',';

        if (!empty($_POST['profile'])) {
            $this->profile = stripslashes(trim($_POST['profile']));
        } else if (!empty($_GET['profile'])) {
            $this->profile = stripslashes(trim($_GET['profile']));
        }
        if (!$this->profile)
            $this->profile = '';

        if (!empty($_POST['merge_empty_cells']) || !empty($_GET['merge_empty_cells'])) {
            $this->merge_empty_cells = 1;
        } else {
            $this->merge_empty_cells = 0;
        }

        $step = empty($_GET['step']) ? 0 : (int) $_GET['step'];

        switch ($step) {
            case 0 :
                $this->header();
                $this->greet();
                break;
            case 1 :
                $this->header();

                check_admin_referer('import-upload');

                if (!empty($_GET['file_url']))
                    $this->file_url = esc_attr($_GET['file_url']);
                if (!empty($_GET['file_id']))
                    $this->id = $_GET['file_id'];

                if (!empty($_GET['clearmapping']) || $this->handle_upload())
                    $this->import_options();
                else
                    _e('Error with handle_upload!', 'wf_order_import_export');
                break;
            case 2 :
                $this->header();

                check_admin_referer('import-woocommerce');

                $this->id = (int) $_POST['import_id'];

                if ($this->file_url_import_enabled)
                    $this->file_url = esc_attr($_POST['import_url']);

                if ($this->id)
                    $file = get_attached_file($this->id);
                else if ($this->file_url_import_enabled)
                    $file = ABSPATH . $this->file_url;

                $file = str_replace("\\", "/", $file);

                if ($file) {
                    ?>
                    <table id="import-progress" class="widefat_importer widefat">
                        <thead>
                            <tr>
                                <th class="status">&nbsp;</th>
                                <th class="row"><?php _e('Row', 'wf_order_import_export'); ?></th>
                                <th><?php _e('OrderID', 'wf_order_import_export'); ?></th>
                                <th><?php _e('Order Status', 'wf_order_import_export'); ?></th>
                                <th class="reason"><?php _e('Status Msg', 'wf_order_import_export'); ?></th>
                            </tr>
                        </thead>
                        <tfoot>         <tr class="importer-loading">
                                                <td colspan="5"></td>         </tr>
                        </tfoot>
                                        <tbody></tbody>
                    </table>
                                        <script type="text/javascript">     jQuery(document).ready(function($) {

                                        if (! window.console) { window.console = function(){}; }

                                        var processed_terms = [];
                        var processed_posts = [];
                        var i = 1;
                        var done_count = 0;
                                                function import_rows(start_pos, end_pos) {

                                                var data = {     action:     'woocommerce_csv_subscription_order_import_request',
                                                file:       '<?php echo addslashes($file); ?>',
                                                mapping:    '<?php echo json_encode($_POST['map_from']); ?>',
                                profile:    '<?php echo $this->profile; ?>',
                                                eval_field: '<?php echo stripslashes(json_encode(($_POST['eval_field']), JSON_HEX_APOS)) ?>',
                                                delimiter:  '<?php echo $this->delimiter; ?>',
                                        merge_empty_cells: '<?php echo $this->merge_empty_cells; ?>',
                                        start_pos:  start_pos,
                                end_pos:    end_pos,
                                        };
                        data.eval_field = $.parseJSON(data.eval_field);
                                                return $.ajax({     url:        '<?php echo add_query_arg(array('import_page' => $this->import_page, 'step' => '3', 'merge' => !empty($_GET['merge']) ? '1' : '0'), admin_url('admin-ajax.php')); ?>',
                                                data:       data,
                                type:       'POST',
                                                success:    function(response) {
                                                if (response) {
                                try {
                                                // Get the valid JSON only from the returned string
                                                        if (response.indexOf("<!--WC_START-->") >= 0)
                                                response = response.split("<!--WC_START-->")[1]; // Strip off before after WC_START 
                                                        if (response.indexOf("<!--WC_END-->") >= 0)
                                        response = response.split("<!--WC_END-->")[0]; // Strip off anything after WC_END

                    // Parse
                                                var results = $.parseJSON(response);
                                                if (results.error) {

                                                $('#import-progress tbody').append('<tr id="row-' + i + '" class="error"><td class="status" colspan="5">' + results.error + '</td></tr>');
                                                                            i++;
                                                                            } else if (results.import_results && $(results.import_results).size() > 0) {

                                                                            $.each(results.processed_terms, function(index, value) {
                                                                            processed_terms.push(value);
                                                                            });
                                                                            $.each(results.processed_posts, function(index, value) {
                                                                            processed_posts.push(value);
                                                                            });
                                                                            $(results.import_results).each(function(index, row) {
                                                                            $('#import-progress tbody').append('<tr id="row-' + i + '" class="' + row['status'] + '"><td><mark class="result" title="' + row['status'] + '">' + row['status'] + '</mark></td><td class="row">' + i + '</td><td>' + row['order_number'] + '</td><td>' + row['post_id'] + ' - ' + row['post_title'] + '</td><td class="reason">' + row['reason'] + '</td></tr>');
                                                                                                        i++;
                                                                                                        });
                                                                                                        }

                                                                                                        } catch (err) {}

                                                                                                        } else {
                                                                                                        $('#import-progress tbody').append('<tr class="error"><td class="status" colspan="5">' +      '<?php _e('AJAX Error', 'wf_order_import_export'); ?>' + '</td></tr>');
                                                                                                                                    }

                                                                                                                                    var w = $(window);
                                                                                                                                    var row = $("#row-" + (i - 1));
                                                                                                                                    if (row.length) {
                                                                                                                                    w.scrollTop(row.offset().top - (w.height() / 2));
                                                                                                                                    }

                                                                                done_count++;
                                                                                                    $('body').trigger('woocommerce_csv_subscription_order_import_request_complete');
                                                                                                    }
                       });
                                                                                                        }

                                                                                                        var rows = [];
                    <?php
                    $limit = apply_filters('woocommerce_csv_import_limit_per_request', 10);
                    $enc = mb_detect_encoding($file, 'UTF-8, ISO-8859-1', true);
                    if ($enc)
                        setlocale(LC_ALL, 'en_US.' . $enc);
                    @ini_set('auto_detect_line_endings', true);

                    $count = 0;
                    $previous_position = 0;
                    $position = 0;
                    $import_count = 0;

// Get CSV positions
                    if (( $handle = fopen($file, "r") ) !== FALSE) {

                        while (( $postmeta = fgetcsv($handle, 0, $this->delimiter) ) !== FALSE) {
                            $count++;

                            if ($count >= $limit) {
                                $previous_position = $position;
                                $position = ftell($handle);
                                $count = 0;
                                $import_count ++;

// Import rows between $previous_position $position
                                ?>rows.push([ <?php echo $previous_position; ?>, <?php echo $position; ?> ]); <?php
                            }
                        }

// Remainder
                        if ($count > 0) {
                            ?>rows.push( [ <?php echo $position; ?>, '' ] ); <?php
                            $import_count ++;
                        }

                        fclose($handle);
                    }
                    ?>

                    var data = rows.shift();
                    var regen_count = 0;
                    import_rows( data[0], data[1] );

                    $('body').on( 'woocommerce_csv_subscription_order_import_request_complete', function() {
                    if ( done_count == <?php echo $import_count; ?> ) {

                    import_done();
                    } else {
                    // Call next request
                    data = rows.shift();
                    import_rows( data[0], data[1] );
                    }
                    } );

                    function import_done() {
                    var data = {
                    action: 'woocommerce_csv_subscription_order_import_request',
                    file: '<?php echo $file; ?>',
                    processed_terms: processed_terms,
                    processed_posts: processed_posts,
                                                                    };

                    $.ajax({
                    url: '<?php echo add_query_arg(array('import_page' => $this->import_page, 'step' => '4', 'merge' => !empty($_GET['merge']) ? 1 : 0), admin_url('admin-ajax.php')); ?>',
                    data:       data,
                    type:       'POST',
                    success:    function( response ) {
                    console.log( response );
                    $('#import-progress tbody').append( '<tr class="complete"><td colspan="5">' + response + '</td></tr>' );
                    $('.importer-loading').hide();
                    }
                    });
                    }
                    });
                    </script>
                    <?php
                } else {
                    echo '<p class="error">' . __('Error finding uploaded file!', 'wf_order_import_export') . '</p>';
                }
                break;
            case 3 :
                // Check access - cannot use nonce here as it will expire after multiple requests
                if (!current_user_can('manage_woocommerce'))
                    die();

                add_filter('http_request_timeout', array($this, 'bump_request_timeout'));

                if (function_exists('gc_enable'))
                    gc_enable();

                @set_time_limit(0);
                @ob_flush();
                @flush();
                $wpdb->hide_errors();

                $file = stripslashes($_POST['file']);
                $mapping = json_decode(stripslashes($_POST['mapping']), true);
                $profile = isset($_POST['profile']) ? $_POST['profile'] : '';
                $eval_field = $_POST['eval_field'];
                $start_pos = isset($_POST['start_pos']) ? absint($_POST['start_pos']) : 0;
                $end_pos = isset($_POST['end_pos']) ? absint($_POST['end_pos']) : '';

                if ($profile !== '') {
                    $profile_array = get_option('wf_subcription_order_csv_imp_exp_mapping');
                    $profile_array[$profile] = array($mapping, $eval_field);
                    update_option('wf_subcription_order_csv_imp_exp_mapping', $profile_array);
                }

                $position = $this->import_start($file, $mapping, $start_pos, $end_pos, $eval_field);
                $this->import();
                $this->import_end();

                $results = array();
                $results['import_results'] = $this->import_results;
                $results['processed_terms'] = $this->processed_terms;
                $results['processed_posts'] = $this->processed_posts;

                echo "<!--WC_START-->";
                echo json_encode($results);
                echo "<!--WC_END-->";
                exit;
                break;
            case 4 :
                // Check access - cannot use nonce here as it will expire after multiple requests
                if (!current_user_can('manage_woocommerce'))
                    die();

                add_filter('http_request_timeout', array($this, 'bump_request_timeout'));

                if (function_exists('gc_enable'))
                    gc_enable();

                @set_time_limit(0);
                @ob_flush();
                @flush();
                $wpdb->hide_errors();

                $this->processed_terms = isset($_POST['processed_terms']) ? $_POST['processed_terms'] : array();
                $this->processed_posts = isset($_POST['processed_posts']) ? $_POST['processed_posts'] : array();

                _e('Step 1...', 'wf_order_import_export') . ' ';

                wp_defer_term_counting(true);
                wp_defer_comment_counting(true);

                _e('Step 2...', 'wf_order_import_export') . ' ';

                echo 'Step 3...' . ' '; // Easter egg

                _e('Finalizing...', 'wf_order_import_export') . ' ';

                // SUCCESS
                _e('Finished. Import complete.', 'wf_order_import_export');

                $this->import_end();
                exit;
                break;
        }

        $this->footer();
    }

    /**
     * format_data_from_csv
     */
    public function format_data_from_csv($data, $enc) {
        return ( $enc == 'UTF-8' ) ? $data : utf8_encode($data);
    }

    /**
     * Display pre-import options
     */
    public function import_options() {
        $j = 0;

        if ($this->id)
            $file = get_attached_file($this->id);
        else if ($this->file_url_import_enabled)
            $file = ABSPATH . $this->file_url;
        else
            return;

        // Set locale
        $enc = mb_detect_encoding($file, 'UTF-8, ISO-8859-1', true);
        if ($enc)
            setlocale(LC_ALL, 'en_US.' . $enc);
        @ini_set('auto_detect_line_endings', true);

        // Get headers
        if (( $handle = fopen($file, "r") ) !== FALSE) {

            $row = $raw_headers = array();
            $header = fgetcsv($handle, 0, $this->delimiter);

            while (( $postmeta = fgetcsv($handle, 0, $this->delimiter) ) !== FALSE) {
                foreach ($header as $key => $heading) {
                    if (!$heading)
                        continue;
                    $s_heading = $heading;
                    $row[$s_heading] = ( isset($postmeta[$key]) ) ? $this->format_data_from_csv($postmeta[$key], $enc) : '';
                    $raw_headers[$s_heading] = $heading;
                }
                break;
            }
            fclose($handle);
        }

        $mapping_from_db = get_option('wf_subcription_order_csv_imp_exp_mapping');

        if ($this->profile !== '' && !empty($_GET['clearmapping'])) {
            unset($mapping_from_db[$this->profile]);
            update_option('wf_subcription_order_csv_imp_exp_mapping', $mapping_from_db);
            $this->profile = '';
        }
        if ($this->profile !== '')
            $mapping_from_db = $mapping_from_db[$this->profile];

        $saved_mapping = null;
        $saved_evaluation = null;
        if ($mapping_from_db && is_array($mapping_from_db) && count($mapping_from_db) == 2 && empty($_GET['clearmapping'])) {
            $reset_action = 'admin.php?clearmapping=1&amp;profile=' . $this->profile . '&amp;import=' . $this->import_page . '&amp;step=1&amp;merge=' . (!empty($_GET['merge']) ? 1 : 0 ) . '&amp;file_url=' . $this->file_url . '&amp;delimiter=' . $this->delimiter . '&amp;merge_empty_cells=' . $this->merge_empty_cells . '&amp;file_id=' . $this->id . '';
            $reset_action = esc_attr(wp_nonce_url($reset_action, 'import-upload'));
            echo '<h3>' . __('Columns are pre-selected using the Mapping file: "<b style="color:gray">' . $this->profile . '</b>".  <a href="' . $reset_action . '"> Delete</a> this mapping file.', 'wf_order_import_export') . '</h3>';
            $saved_mapping = $mapping_from_db[0];
            $saved_evaluation = $mapping_from_db[1];
        }

        $merge = (!empty($_GET['merge']) && $_GET['merge']) ? 1 : 0;

        include( 'views-subscription/html-wf-import-options.php' );
    }

    /**
     * The main controller for the actual import stage.
     */
    public function import() {
        global $woocommerce, $wpdb;

        wp_suspend_cache_invalidation(true);
        $this->log->add('hf-subscription-csv-import', '---');
        $this->log->add('hf-subscription-csv-import', __('Processing orders.', 'wf_order_import_export'));
        $merging = 1;
        $record_offset = 0;
        foreach ($this->parsed_data as $key => &$item) {
            $order = $this->parser->parse_subscription_orders($item, $this->raw_headers, $merging, $record_offset);
            if (!is_wp_error($order))
                $this->process_subscription_orders($order['shop_subscription']);
            else
                $this->add_import_result('failed', $order->get_error_message(), 'Not parsed', json_encode($item), '-');

            unset($item, $order);
            $i++;
        }
        $this->log->add('hf-subscription-csv-import', __('Finished processing Orders.', 'wf_order_import_export'));
        wp_suspend_cache_invalidation(false);
    }

    /**
     * Parses the CSV file and prepares us for the task of processing parsed data
     *
     * @param string $file Path to the CSV file for importing
     */
    public function import_start($file, $mapping, $start_pos, $end_pos, $eval_field) {

	$memory    = size_format( (WC()->version < '2.7.0')?woocommerce_let_to_num( ini_get( 'memory_limit' ) ):wc_let_to_num( ini_get( 'memory_limit' ) )  );
	$wp_memory = size_format( (WC()->version < '2.7.0')? woocommerce_let_to_num( WP_MEMORY_LIMIT ) : wc_let_to_num( WP_MEMORY_LIMIT ) );

        $this->log->add('hf-subscription-csv-import', '---[ New Import ] PHP Memory: ' . $memory . ', WP Memory: ' . $wp_memory);
        $this->log->add('hf-subscription-csv-import', __('Parsing subscription CSV.', 'wf_order_import_export'));

        $this->parser = new WF_CSV_Subscription_Parser('shop_subscription');

        list( $this->parsed_data, $this->raw_headers, $position ) = $this->parser->parse_data($file, $this->delimiter, $mapping, $start_pos, $end_pos, $eval_field);

        $this->log->add('hf-subscription-csv-import', __('Finished parsing subscriptionss CSV.', 'wf_order_import_export'));

        unset($import_data);

        wp_defer_term_counting(true);
        wp_defer_comment_counting(true);

        return $position;
    }

    /**
     * Performs post-import cleanup of files and the cache
     */
    public function import_end() {

        //wp_cache_flush(); Stops output in some hosting environments
        foreach (get_taxonomies() as $tax) {
            delete_option("{$tax}_children");
            _get_term_hierarchy($tax);
        }

        wp_defer_term_counting(false);
        wp_defer_comment_counting(false);

        do_action('import_end');
    }

    /**
     * Handles the CSV upload and initial parsing of the file to prepare for
     * displaying author import options
     *
     * @return bool False if error uploading or invalid file, true otherwise
     */
    public function handle_upload() {
        if ($this->handle_ftp()) {
            return true;
        }
        if (empty($_POST['file_url'])) {

            $file = wp_import_handle_upload();

            if (isset($file['error'])) {
                echo '<p><strong>' . __('Sorry, there has been an error.', 'wf_order_import_export') . '</strong><br />';
                echo esc_html($file['error']) . '</p>';
                return false;
            }

            $this->id = (int) $file['id'];
            return true;
        } else {

            if (file_exists(ABSPATH . $_POST['file_url'])) {

                $this->file_url = esc_attr($_POST['file_url']);
                return true;
            } else {

                echo '<p><strong>' . __('Sorry, there has been an error.', 'wf_order_import_export') . '</strong></p>';
                return false;
            }
        }

        return false;
    }

    public function subscription_order_exists($orderID) {
        global $wpdb;
        $query = "SELECT ID FROM $wpdb->posts WHERE post_type = 'shop_subscription' AND post_status IN ( 'wc-pending-cancel','wc-expired','wc-switched','wc-cancelled','wc-on-hold','wc-active','wc-pending')";
        $args = array();
        $posts_are_exist = $wpdb->get_col($wpdb->prepare($query, $args));
        if ($posts_are_exist) {
            foreach ($posts_are_exist as $exist_id) {
                $found = false;
                if ($exist_id == $orderID) {
                    $found = TRUE;
                }
                if ($found)
                    return TRUE;
            }
        } else {
            return FALSE;
        }
    }

    /**
     * Create new posts based on import information
     */
    private function process_subscription_orders($data) {

        global $wpdb;
        $this->imported = $this->merged = 0;
        $merging = (!empty($_GET['merge'])) ? 1 : 0;
        $add_memberships = ( isset($_POST['add_memberships']) ) ? $_POST['add_memberships'] : 'no';
        // plan a dry run
        //$dry_run = isset( $_POST['dry_run'] ) && $_POST['dry_run'] ? true : false;
        $this->log->add('hf-subscription-csv-import', __('Process start..', 'wf_order_import_export'));
        $this->log->add('hf-subscription-csv-import', __('Processing subscriptions...', 'wf_order_import_export'));
        

        $email_customer = false; // set this as settings for choosing weather to mail details for newly created customers.
        $meta_array = array();
        foreach ( $data['post_meta'] as $meta ) {
                    $meta_array[$meta['key']] = $meta['value'];
        }
        $user_id = $this->hf_check_customer($meta_array, $email_customer);
        

        if (is_wp_error($user_id)) {
            $this->log->add('hf-subscription-csv-import' , sprintf(__($user_id->get_error_message(), 'wf_order_import_export')));
            $this->add_import_result('skipped', __($user_id->get_error_message(), 'wf_order_import_export'), $data['subscription_id'], $data['subscription_id'], $data['subscription_id']);
            $skipped++;
            unset($data);
            return;
        } elseif (empty($user_id)) {
            $this->log->add('hf-subscription-csv-import' , sprintf(__('An error occurred with the customer information provided.', 'wf_order_import_export')));
            $this->add_import_result('skipped', __('An error occurred with the customer information provided.', 'wf_order_import_export'), $data['subscription_id'], $data['subscription_id'], $data['subscription_id']);
            $skipped++;
            unset($data);
            return;
        }



        if (!$dry_run) {
            //check whether download permissions need to be granted
            $add_download_permissions = false;


            // Check if post exists when importing
            $new_added = false;
            $is_order_exist = $this->subscription_order_exists($data['subscription_id']);

            if (!$merging && $is_order_exist) {
                $usr_msg = 'Order already exists.';
                $this->add_import_result('skipped', __($usr_msg, 'wf_order_import_export'), $data['subscription_id'], $data['subscription_id'], $data['subscription_id']);
                $this->log->add('hf-subscription-csv-import', sprintf(__('> &#8220;%s&#8221;' . $usr_msg, 'wf_order_import_export'), esc_html($data['subscription_id'])), true);
                unset($data);
                return;
            } else {
                if ($is_order_exist) {
                    $order_id = $data['subscription_id'];
                    $query = "SELECT post_parent FROM $wpdb->posts WHERE ID = $order_id AND post_type = 'shop_subscription' AND post_status IN ( 'wc-pending-cancel','wc-expired','wc-switched','wc-cancelled','wc-on-hold','wc-active','wc-pending')";
                    $args = array();
                    $post_parent = $wpdb->get_col($wpdb->prepare($query, $args));
                    $subscription = $this->hf_create_subscription(array(
                        'ID' => $data['subscription_id'],
                        'customer_id' => $user_id,
                        'order_id' => $post_parent[0],
                        'import_id' => $data['subscription_id'], //Suggest import to keep the given ID
                        'start_date' => $data['dates_to_update']['start'],
                        'billing_interval' => (!empty($data['billing_interval']) ) ? $data['billing_interval'] : 1,
                        'billing_period' => (!empty($data['billing_period']) ) ? $data['billing_period'] : '',
                        'created_via' => 'importer',
                        'customer_note' => (!empty($data['customer_note']) ) ? $data['customer_note'] : '',
                        'currency' => (!empty($data['order_currency']) ) ? $data['order_currency'] : '',
                            ), $data, $post_parent[0]
                    );
                    $new_added = false;
                    if (is_wp_error($subscription)) {
                        $this->errored++;
                        $new_added = false;
                        //$this->add_import_result('failed', __($order_id->get_error_message() , 'wf_order_import_export'), $post['order_number'], $order_data['post_title'], $post['order_number']);
                        $this->log->add('hf-subscription-csv-import' ,sprintf(__('> Error inserting %s: %s', 'wf_order_import_export'), $post['order_number'], $order_id->get_error_message()), true);
                    }
                } else {
                    $subscription = $this->hf_create_subscription(array(
                        'customer_id' => $user_id,
                        'order_id' => $data['post_parent'],
                        'import_id' => $data['subscription_id'], //Suggest import to keep the given ID
                        'start_date' => $data['dates_to_update']['start'],
                        'billing_interval' => (!empty($data['billing_interval']) ) ? $data['billing_interval'] : 1,
                        'billing_period' => (!empty($data['billing_period']) ) ? $data['billing_period'] : '',
                        'created_via' => 'importer',
                        'customer_note' => (!empty($data['customer_note']) ) ? $data['customer_note'] : '',
                        'currency' => (!empty($data['order_currency']) ) ? $data['order_currency'] : '',
                            )
                    );
                    $new_added = true;
                    if (is_wp_error($subscription)) {
                        $this->errored++;
                        $new_added = false;

                        $this->add_import_result('skipped', __('Error inserting', 'wf_order_import_export'), $data['subscription_id'], $data['subscription_id'], $data['subscription_id']);
                        $this->log->add('hf-subscription-csv-import', sprintf(__($subscription->get_error_message(), 'wf_order_import_export'), esc_html($data['subscription_id'])), true);
                        unset($data);
                        return;

                        //$this->add_import_result('failed', __($order_id->get_error_message() , 'wf_order_import_export'), $post['order_number'], $order_data['post_title'], $post['order_number']);
                        //$this->log->add(sprintf(__('> Error inserting %s: %s', 'wf_order_import_export'), $data['subscription_id'], $subscription->get_error_message()), true);
                    }
                }
            }
            // empty update to bump up the post_modified date to today's date (otherwise it would match the post_date, which isn't quite right)
            //wp_update_post( array( 'ID' => $order_id ) );
            // handle special meta fields
            //update_post_meta( $order_id, '_order_key',          apply_filters( 'woocommerce_generate_order_key', uniqid( 'order_' ) ) );
            //update_post_meta( $order_id, '_order_currency',     get_woocommerce_currency() );  // TODO: fine to use store default?
            //update_post_meta( $order_id, '_prices_include_tax', get_option( 'woocommerce_prices_include_tax' ) );
            // add order postmeta
            foreach ($data['post_meta'] as $meta_data) {
                update_post_meta($subscription->id, $meta_data['key'], $meta_data['value']);
            }

            $subscription->update_dates($data['dates_to_update']);
            $subscription->update_status($data['subscription_status']);

            if ($data['manualy_set']) {
                $subscription->update_manual();
                $result['warning'][] = esc_html__('No payment method was given in CSV and so the subscription has been set to manual renewal.', 'wf_order_import_export');
                //log warning
            } elseif (!$subscription->has_status($this->hf_get_subscription_ended_statuses())) { // don't bother trying to set payment meta on a subscription that won't ever renew
                $warning = array_merge($result['warning'], self::set_payment_meta($subscription, $data));
                $this->log->add('hf-subscription-csv-import', sprintf(__($warning, 'wf_order_import_export'), $data['subscription_id']), true);
                //log warning
            }

            if (!empty($data['order_notes'])) {
                $order_notes = explode(';', $data['order_notes']);

                foreach ($order_notes as $order_note) {
                    $subscription->add_order_note($order_note);
                }
            }


            if (!empty($data['coupon_items'])) {
                self::add_coupons($subscription, $data);
            }

            $chosen_tax_rate_id = 0;
            if (!empty($data['tax_items'])) {
                $chosen_tax_rate_id = self::add_taxes($subscription, $data);
            }

            if (!empty($data['order_items'])) {
                if (is_numeric($data['order_items'])) {
                    $product_id = absint($data['order_items']);
                    $result['items'] = self::add_product($data, $subscription, array('product_id' => $product_id), $chosen_tax_rate_id);

                    if ($add_memberships) {
                        self::maybe_add_memberships($user_id, $subscription->id, $product_id);
                    }
                } else {
                    $order_items = explode(';', $data['order_items']);

                    if (!empty($order_items)) {
                        foreach ($order_items as $order_item) {
                            $item_data = array();

                            foreach (explode('|', $order_item) as $item) {
                                list( $name, $value ) = explode(':', $item);
                                $item_data[trim($name)] = trim($value);
                            }

                            $result['items'] .= self::add_product($data, $subscription, $item_data, $chosen_tax_rate_id) . '<br/>';

                            if ($add_memberships) {
                                self::maybe_add_memberships($user_id, $subscription->id, $item_data['product_id']);
                            }
                        }
                    }
                }
            }


            // only show the following warnings on the import when the subscription requires shipping
            if (!self::$all_virtual) {
                if (!empty($missing_shipping_addresses)) {
                    $result['warning'][] = esc_html__('The following shipping address fields have been left empty: ' . rtrim(implode(', ', $missing_shipping_addresses), ',') . '. ', 'wf_order_import_export');
                }

                if (!empty($missing_billing_addresses)) {
                    $result['warning'][] = esc_html__('The following billing address fields have been left empty: ' . rtrim(implode(', ', $missing_billing_addresses), ',') . '. ', 'wf_order_import_export');
                }

                if (empty($shipping_method)) {
                    $result['warning'][] = esc_html__('Shipping method and title for the subscription have been left as empty. ', 'wf_order_import_export');
                }
            }



            // handle order items
        } // ! dry run

        if($subscription->id){
            $this->processed_posts[$subscription->id] = $subscription->id;
            $data['subscription_id'] = $subscription->id;
        }
        if(!empty($data['subscription_id'])){
            $this->processed_posts[$data['subscription_id']] = $data['subscription_id'];
        }

        if (!empty($data['subscription_id']) && !empty($data['payment_method']) && $data['payment_method'] != 'manual') {
            update_post_meta($data['subscription_id'], '_requires_manual_renewal', 'false');
        }
        
        if ($merging && !$new_added)
            $out_msg = 'Order Successfully updated.';
        else
            $out_msg = 'Order Imported Successfully.';

        $this->add_import_result('imported', __($out_msg, 'wf_order_import_export'), $data['subscription_id'], $result['items'], $data['subscription_id']);
        $this->log->add('hf-subscription-csv-import', sprintf(__('> &#8220;%s&#8221;' . $usr_msg, 'wf_order_import_export'), esc_html($data['subscription_id'])), true);
        $this->imported++;
        $this->log->add('hf-subscription-csv-import' , sprintf(__('> Finished importing order %s', 'wf_order_import_export'), $dry_run ? "" : $data['subscription_id'] ));


        $this->log->add('hf-subscription-csv-import' , __('Finished processing orders.', 'wf_order_import_export'));

        unset($data);
    }

    /**
     * Log a row's import status
     */
    protected function add_import_result($status, $reason, $post_id = '', $post_title = '', $order_number = '') {
        $this->import_results[] = array(
            'post_title' => $post_title,
            'post_id' => $post_id,
            'order_number' => $order_number,
            'status' => $status,
            'reason' => $reason
        );
    }

    /**
     * Decide what the maximum file size for downloaded attachments is.
     * Default is 0 (unlimited), can be filtered via import_attachment_size_limit
     *
     * @return int Maximum attachment file size to import
     */
    public function max_attachment_size() {
        return apply_filters('import_attachment_size_limit', 0);
    }

    //handle FTP section
    private function handle_ftp() {
        $enable_ftp_ie = !empty($_POST['enable_ftp_ie']) ? true : false;
        if ($enable_ftp_ie == false)
            return false;

        $ftp_server = !empty($_POST['ftp_server']) ? $_POST['ftp_server'] : '';
        $ftp_server_path = !empty($_POST['ftp_server_path']) ? $_POST['ftp_server_path'] : '';
        $ftp_user = !empty($_POST['ftp_user']) ? $_POST['ftp_user'] : '';
        $ftp_password = !empty($_POST['ftp_password']) ? $_POST['ftp_password'] : '';
        $use_ftps = !empty($_POST['use_ftps']) ? true : false;
        $use_pasv = !empty($_POST['use_pasv']) ? true : false;


        $settings = array();
        $settings['ftp_server'] = $ftp_server;
        $settings['ftp_user'] = $ftp_user;
        $settings['ftp_password'] = $ftp_password;
        $settings['use_ftps'] = $use_ftps;
        $settings['use_pasv'] = $use_pasv;
        $settings['enable_ftp_ie'] = $enable_ftp_ie;
        $settings['ftp_server_path'] = $ftp_server_path;


        $local_file = 'wp-content/plugins/order-import-export-for-woocommerce/temp-import.csv';
        $server_file = $ftp_server_path;

        update_option('hf_subscription_order_importer_ftp', $settings);

        $ftp_conn = $use_ftps ? ftp_ssl_connect($ftp_server) : ftp_connect($ftp_server);
        $error_message = "";
        $success = false;
        if ($ftp_conn == false) {
            $error_message = "There is connection problem\n";
        }

        if (empty($error_message)) {
            if (ftp_login($ftp_conn, $ftp_user, $ftp_password) == false) {
                $error_message = "Not able to login \n";
            }
        }
        
        if($use_pasv) ftp_pasv($ftp_conn, TRUE);
        if (empty($error_message)) {

            if (ftp_get($ftp_conn, ABSPATH . $local_file, $server_file, FTP_BINARY)) {
                $error_message = "";
                $success = true;
            } else {
                $error_message = "There was a problem\n";
            }
        }

        ftp_close($ftp_conn);
        if ($success) {
            $this->file_url = $local_file;
        } else {
            die($error_message);
        }
        return true;
    }

    // Display import page title
    public function header() {
        echo '<div class="wrap"><div class="icon32" id="icon-woocommerce-importer"><br></div>';
        echo '<h2>' . ( empty($_GET['merge']) ? __('Import', 'wf_order_import_export') : __('Merge Orders', 'wf_order_import_export') ) . '</h2>';
    }

    // Close div.wrap
    public function footer() {
        echo '</div>';
    }

    /**
     * Display introductory text and file upload form
     */
    public function greet() {
        $action = 'admin.php?import=woocommerce_wf_subscription_order_csv&amp;step=1&amp;merge=' . (!empty($_GET['merge']) ? 1 : 0 );
        $bytes = apply_filters('import_upload_size_limit', wp_max_upload_size());
        $size = size_format($bytes);
        $upload_dir = wp_upload_dir();
        $ftp_settings = get_option('hf_subscription_order_importer_ftp');
        include( 'views/html-wf-import-greeting.php' );
    }

    /**
     * Added to http_request_timeout filter to force timeout at 60 seconds during import
     * @return int 60
     */
    public function bump_request_timeout($val) {
        return 60;
    }

    public function hf_check_customer($data, $email_customer = false) {
        $customer_email = (!empty($data['_customer_email']) ) ? $data['_customer_email'] : '';
        $username = (!empty($data['_customer_username']) ) ? $data['_customer_username'] : '';
        $customer_id = (!empty($data['_customer_id']) ) ? $data['_customer_id'] : '';

        if (!empty($data['_customer_password'])) {
            $password = $data['_customer_password'];
            $password_generated = false;
        } else {
            $password = wp_generate_password(12, true);
            $password_generated = true;
        }

        $found_customer = false;

        if (!empty($customer_email)) {

            if (is_email($customer_email) && false !== email_exists($customer_email)) {
                $found_customer = email_exists($customer_email);
            } elseif (!empty($username) && false !== username_exists($username)) {
                $found_customer = username_exists($username);
            } elseif (is_email($customer_email)) {



                // Not in test mode, create a user account for this email
                if (empty($username)) {

                    $maybe_username = explode('@', $customer_email);
                    $maybe_username = sanitize_user($maybe_username[0]);
                    $counter = 1;
                    $username = $maybe_username;

                    while (username_exists($username)) {
                        $username = $maybe_username . $counter;
                        $counter++;
                    }
                }

                $found_customer = wp_create_user($username, $password, $customer_email);

                if (!is_wp_error($found_customer)) {

                    // update user meta data
                    foreach (self::$user_meta_fields as $key) {
                        switch ($key) {
                            case '_billing_email':
                                // user billing email if set in csv otherwise use the user's account email
                                $meta_value = (!empty($data[$key]) ) ? $data[$key] : $customer_email;
                                $key = substr($key, 1);
                                update_user_meta($found_customer, $key, $meta_value);
                                break;

                            case '_billing_first_name':
                                $meta_value = (!empty($data[$key]) ) ? $data[$key] : $username;
                                $key = substr($key, 1);
                                update_user_meta($found_customer, $key, $meta_value);
                                update_user_meta($found_customer, 'first_name', $meta_value);
                                break;

                            case '_billing_last_name':
                                $meta_value = (!empty($data[$key]) ) ? $data[$key] : '';
                                $key = substr($key, 1);
                                update_user_meta($found_customer, $key, $meta_value);
                                update_user_meta($found_customer, 'last_name', $meta_value);
                                break;

                            case '_shipping_first_name':
                            case '_shipping_last_name':
                            case '_shipping_address_1':
                            case '_shipping_address_2':
                            case '_shipping_city':
                            case '_shipping_postcode':
                            case '_shipping_state':
                            case '_shipping_country':
                                // Set the shipping address fields to match the billing fields if not specified in CSV
                                $meta_value = (!empty($data[$key]) ) ? $data[$key] : '';

                                if (empty($meta_value)) {
                                    $n_key = str_replace('shipping', 'billing', $key);
                                    $meta_value = (!empty($data[$n_key]) ) ? $data[$n_key] : '';
                                }
                                $key = substr($key, 1);
                                update_user_meta($found_customer, $key, $meta_value);
                                break;

                            default:
                                $meta_value = (!empty($data[$key]) ) ? $data[$key] : '';
                                $key = substr($key, 1);
                                update_user_meta($found_customer, $key, $meta_value);
                        }
                    }

                    $this->hf_make_user_active($found_customer);

                    // send user registration email if admin as chosen to do so
                    if ($email_customer && function_exists('wp_new_user_notification')) {

                        $previous_option = get_option('woocommerce_registration_generate_password');

                        // force the option value so that the password will appear in the email
                        update_option('woocommerce_registration_generate_password', 'yes');

                        do_action('woocommerce_created_customer', $found_customer, array('user_pass' => $password), true);

                        update_option('woocommerce_registration_generate_password', $previous_option);
                    }
                }
            }
        } else {
           
                $found_customer = new WP_Error('hf_invalid_customer', sprintf(__('User could not be created without Email.', 'wf_order_import_export'), $customer_id));
           
        }

        return $found_customer;
    }

    public function hf_make_user_active($user_id) {
        $this->hf_update_users_role($user_id, 'default_subscriber_role');
    }

    /**
     * Update a user's role to a special subscription's role
     * @param int $user_id The ID of a user
     * @param string $role_new The special name assigned to the role by Subscriptions,
     * one of 'default_subscriber_role', 'default_inactive_role' or 'default_cancelled_role'
     * @return WP_User The user with the new role.
     * @since 2.0
     */
    public function hf_update_users_role($user_id, $role_new) {

        $user = new WP_User($user_id);

        // Never change an admin's role to avoid locking out admins testing the plugin
        if (!empty($user->roles) && in_array('administrator', $user->roles)) {
            return;
        }

        // Allow plugins to prevent Subscriptions from handling roles
        if (!apply_filters('woocommerce_subscriptions_update_users_role', true, $user, $role_new)) {
            return;
        }

        $roles = $this->hf_get_new_user_role_names($role_new);

        $role_new = $roles['new'];
        $role_old = $roles['old'];

        if (!empty($role_old)) {
            $user->remove_role($role_old);
        }

        $user->add_role($role_new);

        do_action('woocommerce_subscriptions_updated_users_role', $role_new, $user, $role_old);
        return $user;
    }

    /**
     * Gets default new and old role names if the new role is 'default_subscriber_role'. Otherwise returns role_new and an
     * empty string.
     *
     * @param $role_new string the new role of the user
     * @return array with keys 'old' and 'new'.
     */
    public function hf_get_new_user_role_names($role_new) {
        $default_subscriber_role = get_option(WC_Subscriptions_Admin::$option_prefix . '_subscriber_role');
        $default_cancelled_role = get_option(WC_Subscriptions_Admin::$option_prefix . '_cancelled_role');
        $role_old = '';

        if ('default_subscriber_role' == $role_new) {
            $role_old = $default_cancelled_role;
            $role_new = $default_subscriber_role;
        } elseif (in_array($role_new, array('default_inactive_role', 'default_cancelled_role'))) {
            $role_old = $default_subscriber_role;
            $role_new = $default_cancelled_role;
        }

        return array(
            'new' => $role_new,
            'old' => $role_old,
        );
    }

    /**
     * Create a new subscription
     *
     * Returns a new WC_Subscription object on success which can then be used to add additional data.
     *
     * @return WC_Subscription | WP_Error A WC_Subscription on success or WP_Error object on failure
     * @since  2.0
     */
    function hf_create_subscription($args = array(), $subscription_exist = false, $data = array()) {

        $order = ( isset($args['order_id']) ) ? wc_get_order($args['order_id']) : null;

        if (!empty($order) && isset($order->post->post_date)) {
            $default_start_date = ( '0000-00-00 00:00:00' != $order->post->post_date_gmt ) ? $order->post->post_date_gmt : get_gmt_from_date($order->post->post_date);
        } else {
            $default_start_date = current_time('mysql', true);
        }

        $default_args = array(
            'status' => '',
            'order_id' => 0,
            'customer_note' => null,
            'customer_id' => (!empty($order) ) ? $order->get_user_id() : null,
            'start_date' => $default_start_date,
            'created_via' => (!empty($order) ) ? $order->created_via : '',
            'order_version' => (!empty($order) ) ? $order->order_version : WC_VERSION,
            'currency' => (!empty($order) ) ? $order->order_currency : get_woocommerce_currency(),
            'prices_include_tax' => (!empty($order) ) ? ( ( $order->prices_include_tax ) ? 'yes' : 'no' ) : get_option('woocommerce_prices_include_tax'), // we don't use wc_prices_include_tax() here because WC doesn't use it in wc_create_order(), not 100% sure why it doesn't also check the taxes are enabled, but there could forseeably be a reason
        );

        $args = wp_parse_args($args, $default_args);
        $subscription_data = array();

        // validate the start_date field
        if (!is_string($args['start_date']) || false === $this->hf_is_datetime_mysql_format($args['start_date'])) {
            return new WP_Error('woocommerce_subscription_invalid_start_date_format', _x('Invalid date. The date must be a string and of the format: "Y-m-d H:i:s".', 'Error message while creating a subscription', 'woocommerce-subscriptions'));
        } else if (strtotime($args['start_date']) > current_time('timestamp', true)) {
            return new WP_Error('woocommerce_subscription_invalid_start_date', _x('Subscription start date must be before current day.', 'Error message while creating a subscription', 'woocommerce-subscriptions'));
        }

        // check customer id is set
        if (empty($args['customer_id']) || !is_numeric($args['customer_id']) || $args['customer_id'] <= 0) {
            return new WP_Error('woocommerce_subscription_invalid_customer_id', _x('Invalid subscription customer_id.', 'Error message while creating a subscription', 'woocommerce-subscriptions'));
        }

        // check the billing period
        if (empty($args['billing_period']) || !in_array(strtolower($args['billing_period']), array_keys($this->hf_get_subscription_period_strings()))) {
            return new WP_Error('woocommerce_subscription_invalid_billing_period', __('Invalid subscription billing period given.', 'woocommerce-subscriptions'));
        }

        // check the billing interval
        if (empty($args['billing_interval']) || !is_numeric($args['billing_interval']) || absint($args['billing_interval']) <= 0) {
            return new WP_Error('woocommerce_subscription_invalid_billing_interval', __('Invalid subscription billing interval given. Must be an integer greater than 0.', 'woocommerce-subscriptions'));
        }
        $subscription_data['import_id'] = $args['import_id'];
        $subscription_data['customer_id'] = $args['customer_id']; // handle here perfectly-need discuss
        $subscription_data['post_type'] = 'shop_subscription';
        $subscription_data['post_status'] = 'wc-' . apply_filters('woocommerce_default_subscription_status', 'pending');
        $subscription_data['ping_status'] = 'closed';
        $subscription_data['post_author'] = 1;
        $subscription_data['post_password'] = uniqid('order_');
        // translators: Order date parsed by strftime
        $post_title_date = strftime(_x('%b %d, %Y @ %I:%M %p', 'Used in subscription post title. "Subscription renewal order - <this>"', 'woocommerce-subscriptions'));
        // translators: placeholder is order date parsed by strftime
        $subscription_data['post_title'] = sprintf(_x('Subscription &ndash; %s', 'The post title for the new subscription', 'woocommerce-subscriptions'), $post_title_date);
        $subscription_data['post_date_gmt'] = $args['start_date'];
        $subscription_data['post_date'] = get_date_from_gmt($args['start_date']);

        if ($args['order_id'] > 0) {
            $subscription_data['post_parent'] = absint($args['order_id']);
        }

        if (!is_null($args['customer_note']) && !empty($args['customer_note'])) {
            $subscription_data['post_excerpt'] = $args['customer_note'];
        }


        if ($args['status']) {
            if (!in_array('wc-' . $args['status'], array_keys($this->hf_get_subscription_statuses()))) {
                return new WP_Error('woocommerce_invalid_subscription_status', __('Invalid subscription status given.', 'woocommerce-subscriptions'));
            }
            $subscription_data['post_status'] = 'wc-' . $args['status'];
        }
        if ($subscription_exist) {
            $subscription_data['ID'] = $args['ID'];
            $subscription_data['import_id'] = $args['import_id'];
            $subscription_data['post_type'] = 'shop_subscription';
            $subscription_data['post_status'] = $data['subscription_status'];
            $subscription_data['ping_status'] = 'closed';
            $subscription_data['post_author'] = $subscription_data['customer_id'];
            $subscription_data['post_password'] = uniqid('order_');
            // translators: Order date parsed by strftime
            $post_title_date = strftime(_x('%b %d, %Y @ %I:%M %p', 'Used in subscription post title. "Subscription renewal order - <this>"', 'woocommerce-subscriptions'));
            // translators: placeholder is order date parsed by strftime
            $subscription_data['post_title'] = sprintf(_x('Subscription &ndash; %s', 'The post title for the new subscription', 'woocommerce-subscriptions'), $post_title_date);
            $subscription_data['post_date_gmt'] = $args['start_date'];
            $subscription_data['post_date'] = get_date_from_gmt($args['start_date']);
            $subscription_id = wp_update_post(apply_filters('woocommerce_update_subscription_data', $subscription_data, $args), true);
        } else {
            $subscription_id = wp_insert_post(apply_filters('woocommerce_new_subscription_data', $subscription_data, $args), true);
        }
        if (is_wp_error($subscription_id)) {
            return $subscription_id;
        }

        // Default order meta data.
        update_post_meta($subscription_id, '_order_key', 'wc_' . apply_filters('woocommerce_generate_order_key', uniqid('order_')));
        update_post_meta($subscription_id, '_order_currency', $args['currency']);
        update_post_meta($subscription_id, '_prices_include_tax', $args['prices_include_tax']);
        update_post_meta($subscription_id, '_created_via', sanitize_text_field($args['created_via']));

        // add/update the billing
        update_post_meta($subscription_id, '_billing_period', $args['billing_period']);
        update_post_meta($subscription_id, '_billing_interval', absint($args['billing_interval']));

        update_post_meta($subscription_id, '_customer_user', $args['customer_id']);
        update_post_meta($subscription_id, '_order_version', $args['order_version']);

        return new WC_Subscription($subscription_id);
    }

    /**
     * Return an array statuses used to describe when a subscriptions has been marked as ending or has ended.
     *
     * @return array
     * @since 2.0
     */
    public function hf_get_subscription_ended_statuses() {
        return apply_filters('hf_subscription_ended_statuses', array('cancelled', 'trash', 'expired', 'switched', 'pending-cancel'));
    }

    /**
     * Set the payment method meta on the imported subscription or on user meta
     * @param WC_Subscription $subscription
     * @param array $data Current line from the CSV
     */
    public static function set_payment_meta($subscription, $data) {
        $warnings = array();
        $payment_gateways = WC()->payment_gateways->get_available_payment_gateways();
        $payment_method = $subscription->payment_method;

        if (!empty($payment_method)) {
            $payment_method_table = apply_filters('woocommerce_subscription_payment_meta', array(), $subscription);
            $payment_gateway = ( isset($payment_gateways[$payment_method]) ) ? $payment_gateways[$payment_method] : '';

            if (!empty($payment_gateway) && isset($payment_method_table[$payment_gateway->id])) {
                $payment_post_meta = $payment_user_meta = array();

                if (!empty($data['payment_method_post_meta'])) {
                    foreach (explode('|', $data['payment_method_post_meta']) as $meta) {
                        list( $name, $value ) = explode(':', $meta);
                        $payment_post_meta[trim($name)] = trim($value);
                    }
                }

                if (!empty($data['payment_method_user_meta'])) {
                    foreach (explode('|', $data['payment_method_user_meta']) as $meta) {
                        list( $name, $value ) = explode(':', $meta);
                        $payment_user_meta[trim($name)] = trim($value);
                    }
                }

                $payment_method_data = $payment_method_table[$payment_gateway->id];
                $meta_set = false;

                foreach ($payment_method_data as $meta_table => &$meta) {
                    if (!is_array($meta)) {
                        continue;
                    }

                    foreach ($meta as $meta_key => &$meta_data) {
                        switch ($meta_table) {
                            case 'post_meta':
                            case 'postmeta':
                                $value = (!empty($payment_post_meta[$meta_key]) ) ? $payment_post_meta[$meta_key] : '';
                                break;
                            case 'user_meta':
                            case 'usermeta':
                                $value = (!empty($payment_user_meta[$meta_key]) ) ? $payment_user_meta[$meta_key] : '';
                                break;
                            default :
                                $value = '';
                        }

                        if (!empty($value)) {
                            $meta_data['value'] = $value;
                            $meta_set = true;
                        }
                    }
                }

                if ($meta_set) {
                    $subscription->set_payment_method($payment_gateway, $payment_method_data);
                } else {
                    $warnings[] = sprintf(esc_html__('No payment meta was set for your %s subscription (%s). The next renewal is going to fail if you leave this.', 'wf_order_import_export'), $payment_method, $subscription->id);
                }
            } else {
                if ('paypal' == $payment_method) {
                    $warnings[] = sprintf(esc_html__('Could not set payment method as PayPal, defaulted to manual renewals. Either PayPal was not enabled or your PayPal account does not have Reference Transaction setup. Learn more about enabling Reference Transactions %shere%s.', 'wf_order_import_export'), '<a href="https://support.woothemes.com/hc/en-us/articles/205151193-PayPal-Reference-Transactions-for-Subscriptions">', '</a>');
                } else {
                    $warnings[] = sprintf(esc_html__('The payment method "%s" is either not enabled or does not support the new features of Subscriptions 2.0 and can not be properly attached to your subscription. This subscription has been set to manual renewals.', 'wf_order_import_export'), $payment_method);
                }
                $subscription->update_manual();
            }
        }
        return $warnings;
    }

    /**
     * Add membership plans to imported subscriptions if applicable
     *
     * @since 1.0
     * @param int $user_id
     * @param int $subscription_id
     * @param int $product_id
     */
    public static function maybe_add_memberships($user_id, $subscription_id, $product_id) {

        if (function_exists('wc_memberships_get_membership_plans')) {

            if (!self::$membership_plans) {
                self::$membership_plans = wc_memberships_get_membership_plans();
            }

            foreach (self::$membership_plans as $plan) {
                if ($plan->has_product($product_id)) {
                    $plan->grant_access_from_purchase($user_id, $product_id, $subscription_id);
                }
            }
        }
    }

    /**
     * Adds the line item to the subscription
     *
     * @since 1.0
     * @param WC_Subscription $subscription
     * @param array $data
     * @param int $chosen_tax_rate_id
     * @return string
     */
    public static function add_product($details, $subscription, $data, $chosen_tax_rate_id) {
        $item_args = array();
        $item_args['qty'] = isset($data['quantity']) ? $data['quantity'] : 1;

        if (!isset($data['product_id'])) {
            throw new Exception(__('The product_id is missing from CSV.', 'wf_order_import_export'));
        }

        $_product = wc_get_product($data['product_id']);

        if (!$_product) {


            $line_item_name = (!empty($data['name']) ) ? $data['name'] : __('Unknown Product', 'wf_order_import_export');
            $product_string = $line_item_name;

            foreach (array('total', 'tax', 'subtotal', 'subtotal_tax') as $line_item_data) {

                switch ($line_item_data) {
                    case 'total' :
                        $default = $data['total'];
                        break;
                    case 'subtotal' :
                        $default = (!empty($data['total']) ) ? $data['total'] : 0;
                        break;
                    default :
                        $default = 0;
                }
                $item_args['totals'][$line_item_data] = (!empty($data[$line_item_data]) ) ? $data[$line_item_data] : $default;
            }


            if (!empty($item_args['totals']['tax']) && !empty($chosen_tax_rate_id)) {
                $item_args['totals']['tax_data']['total'] = array($chosen_tax_rate_id => $item_args['totals']['tax']);
                $item_args['totals']['tax_data']['subtotal'] = array($chosen_tax_rate_id => $item_args['totals']['tax']);
            }



            $postdata = array(
                'post_date' => '',
                'post_date_gmt' => '',
                'post_title' => $product_string,
                'post_status' => 'publish',
                'post_type' => 'product',
            );


            $post_id = wp_insert_post($postdata, true);
            //wp_set_post_terms( $post_id, 6, 'subscription', false );
            wp_set_object_terms( $post_id, 'subscription', 'product_type' );
            $_product = new WC_Product($post_id);


            $item_id = $subscription->add_product($_product, $item_args['qty'], $item_args);

            // Set the name used in the CSV if it's different to the product's current title (which is what WC_Abstract_Order::add_product() uses)
            if (!empty($data['name']) && $_product->get_title() != $data['name']) {
                wc_update_order_item($item_id, array('order_item_name' => $data['name']));
            }

            // Add any meta data for the line item
            if (!empty($data['meta'])) {
                foreach (explode('+', $data['meta']) as $meta) {
                    $meta = explode('=', $meta);
                    wc_update_order_item_meta($item_id, $meta[0], $meta[1]);
                }
            }

            if (!$item_id) {
                throw new Exception(__('An unexpected error occurred when trying to add product "%s" to your subscription. The error was caught and no subscription for this row will be created. Please fix up the data from your CSV and try again.', 'wf_order_import_export'));
            }

            if (!empty($details['download_permissions']) && ( 'true' == $details['download_permissions'] || 1 == (int) $details['download_permissions'] )) {
                self::save_download_permissions($subscription, $_product, $item_args['qty']);
            }
            //throw new Exception(sprintf(__('No product or variation in your store matches the product ID #%s.', 'wf_order_import_export'), $data['product_id']));
        } else {

            $line_item_name = (!empty($data['name']) ) ? $data['name'] : $_product->get_title();
            $product_string = sprintf('<a href="%s">%s</a>', get_edit_post_link($_product->id), $line_item_name);

            foreach (array('total', 'tax', 'subtotal', 'subtotal_tax') as $line_item_data) {

                switch ($line_item_data) {
                    case 'total' :
                        $default = WC_Subscriptions_Product::get_price($data['product_id']);
                        break;
                    case 'subtotal' :
                        $default = (!empty($data['total']) ) ? $data['total'] : WC_Subscriptions_Product::get_price($data['product_id']);
                        break;
                    default :
                        $default = 0;
                }
                $item_args['totals'][$line_item_data] = (!empty($data[$line_item_data]) ) ? $data[$line_item_data] : $default;
            }

            // Add this site's variation meta data if no line item meta data was specified in the CSV
            if (empty($data['meta']) && $_product->variation_data) {
                $item_args['variation'] = array();

                foreach ($_product->variation_data as $attribute => $variation) {
                    $item_args['variation'][$attribute] = $variation;
                }
                $product_string .= ' [#' . $data['product_id'] . ']';
            }

            if (self::$all_virtual && !$_product->is_virtual()) {
                self::$all_virtual = false;
            }

            if (!empty($item_args['totals']['tax']) && !empty($chosen_tax_rate_id)) {
                $item_args['totals']['tax_data']['total'] = array($chosen_tax_rate_id => $item_args['totals']['tax']);
                $item_args['totals']['tax_data']['subtotal'] = array($chosen_tax_rate_id => $item_args['totals']['tax']);
            }


            $item_id = $subscription->add_product($_product, $item_args['qty'], $item_args);

            // Set the name used in the CSV if it's different to the product's current title (which is what WC_Abstract_Order::add_product() uses)
            if (!empty($data['name']) && $_product->get_title() != $data['name']) {
                wc_update_order_item($item_id, array('order_item_name' => $data['name']));
            }

            // Add any meta data for the line item
            if (!empty($data['meta'])) {
                foreach (explode('+', $data['meta']) as $meta) {
                    $meta = explode('=', $meta);
                    wc_update_order_item_meta($item_id, $meta[0], $meta[1]);
                }
            }

            if (!$item_id) {
                throw new Exception(__('An unexpected error occurred when trying to add product "%s" to your subscription. The error was caught and no subscription for this row will be created. Please fix up the data from your CSV and try again.', 'wf_order_import_export'));
            }

            if (!empty($details['download_permissions']) && ( 'true' == $details['download_permissions'] || 1 == (int) $details['download_permissions'] )) {
                self::save_download_permissions($subscription, $_product, $item_args['qty']);
            }
        }
        return $product_string;
    }

    /**
     * Save download permission to the subscription.
     *
     * @since 1.0
     * @param WC_Subscription $subscription
     * @param WC_Product $product
     * @param int $quantity
     */
    public static function save_download_permissions($subscription, $product, $quantity = 1) {

        if ($product && $product->exists() && $product->is_downloadable()) {
            $downloads = $product->get_files();
            $product_id = isset($product->variation_id) ? $product->variation_id : $product->id;

            foreach (array_keys($downloads) as $download_id) {
                wc_downloadable_file_permission($download_id, $product_id, $subscription, $quantity);
            }
        }
    }

    /**
     * Add coupon line item to the subscription. The discount amount used is based on priority list.
     *
     * @since 1.0
     * @param WC_Subscription $subscription
     * @param array $data
     */
    public static function add_coupons($subscription, $data) {

        $coupon_items = explode(';', $data['coupon_items']);

        if (!empty($coupon_items)) {
            foreach ($coupon_items as $coupon_item) {
                $coupon_data = array();

                foreach (explode('|', $coupon_item) as $item) {
                    list( $name, $value ) = explode(':', $item);
                    $coupon_data[trim($name)] = trim($value);
                }

                $coupon_code = isset($coupon_data['code']) ? $coupon_data['code'] : '';
                $coupon = new WC_Coupon($coupon_code);

                if (!$coupon) {
                    throw new Exception(sprintf(esc_html__('Could not find coupon with code "%s" in your store.', 'wf_order_import_export'), $coupon_code));
                } elseif (isset($coupon_data['amount'])) {
                    $discount_amount = floatval($coupon_data['amount']);
                } else {
                    $discount_amount = $coupon->discount_amount;
                }


                $coupon_id = $subscription->add_coupon($coupon_code, $discount_amount);

                if (!$coupon_id) {
                    throw new Exception(sprintf(esc_html__('Coupon "%s" could not be added to subscription.', 'wf_order_import_export'), $coupon_code));
                }
            }
        }
    }

    /**
     * PHP on Windows does not have strptime function. Therefore this is what we're using to check
     * whether the given time is of a specific format.
     * @param  string $time the mysql time string
     * @return boolean      true if it matches our mysql pattern of YYYY-MM-DD HH:MM:SS
     */
    public function hf_is_datetime_mysql_format($time) {
        if (!is_string($time)) {
            return false;
        }

        if (function_exists('strptime')) {
            $valid_time = $match = ( false !== strptime($time, '%Y-%m-%d %H:%M:%S') ) ? true : false;
        } else {
            // parses for the pattern of YYYY-MM-DD HH:MM:SS, but won't check whether it's a valid timedate
            $match = preg_match('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $time);

            // parses time, returns false for invalid dates
            $valid_time = strtotime($time);
        }

        // magic number -2209078800 is strtotime( '1900-01-00 00:00:00' ). Needed to achieve parity with strptime
        return ( $match && false !== $valid_time && -2209078800 <= $valid_time ) ? true : false;
    }

    /**
     * Return translated associative array of all possible subscription periods.
     * @param int (optional) An interval in the range 1-6
     * @param string (optional) One of day, week, month or year. If empty, all subscription ranges are returned.
     */
    public function hf_get_subscription_period_strings($number = 1, $period = '') {

        $translated_periods = apply_filters('woocommerce_subscription_periods', array(
            // translators: placeholder is number of days. (e.g. "Bill this every day / 4 days")
            'day' => sprintf(_nx('day', '%s days', $number, 'Subscription billing period.', 'woocommerce-subscriptions'), $number),
            // translators: placeholder is number of weeks. (e.g. "Bill this every week / 4 weeks")
            'week' => sprintf(_nx('week', '%s weeks', $number, 'Subscription billing period.', 'woocommerce-subscriptions'), $number),
            // translators: placeholder is number of months. (e.g. "Bill this every month / 4 months")
            'month' => sprintf(_nx('month', '%s months', $number, 'Subscription billing period.', 'woocommerce-subscriptions'), $number),
            // translators: placeholder is number of years. (e.g. "Bill this every year / 4 years")
            'year' => sprintf(_nx('year', '%s years', $number, 'Subscription billing period.', 'woocommerce-subscriptions'), $number),
                )
        );

        return (!empty($period) ) ? $translated_periods[$period] : $translated_periods;
    }

    /**
     * Return an array of subscription status types, similar to @see wc_get_order_statuses()
     * @return array
     */
    public function hf_get_subscription_statuses() {

        $subscription_statuses = array(
            'wc-pending' => _x('Pending', 'Subscription status', 'woocommerce-subscriptions'),
            'wc-active' => _x('Active', 'Subscription status', 'woocommerce-subscriptions'),
            'wc-on-hold' => _x('On hold', 'Subscription status', 'woocommerce-subscriptions'),
            'wc-cancelled' => _x('Cancelled', 'Subscription status', 'woocommerce-subscriptions'),
            'wc-switched' => _x('Switched', 'Subscription status', 'woocommerce-subscriptions'),
            'wc-expired' => _x('Expired', 'Subscription status', 'woocommerce-subscriptions'),
            'wc-pending-cancel' => _x('Pending Cancellation', 'Subscription status', 'woocommerce-subscriptions'),
        );

        return apply_filters('hf_subscription_statuses', $subscription_statuses);
    }

    /**
     * Import tax lines
     * @param WC_Subscription $subscription
     * @param array $data
     */
    public static function add_taxes($subscription, $data) {
        global $wpdb;

        $tax_items = explode(';', $data['tax_items']);
        $chosen_tax_rate_id = 0;

        if (!empty($tax_items)) {
            foreach ($tax_items as $tax_item) {
                $tax_data = array();

                if (false !== strpos($tax_item, ':')) {
                    foreach (explode('|', $tax_item) as $item) {
                        list( $name, $value ) = explode(':', $item);
                        $tax_data[trim($name)] = trim($value);
                    }
                } elseif (1 == count($tax_items)) {
                    if (is_numeric($tax_item)) {
                        $tax_data['id'] = $tax_item;
                    } else {
                        $tax_data['code'] = $tax_item;
                    }
                }

                if (!empty($tax_data['id'])) {
                    $tax_rate = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}woocommerce_tax_rates WHERE tax_rate_id = %s", $tax_data['id']));
                } elseif (!empty($tax_data['code'])) {
                    $tax_rate = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}woocommerce_tax_rates WHERE tax_rate_name = %s ORDER BY tax_rate_priority LIMIT 1", $tax_data['code']));
                } else {
                    $result['warning'][] = esc_html__(sprintf('Missing tax code or ID from column: %s', $data['tax_items']), 'wf_order_import_export');
                }

                if (!empty($tax_rate)) {

                    $tax_rate = array_pop($tax_rate);
                    $tax_id = $subscription->add_tax($tax_rate->tax_rate_id, (!empty($data['order_shipping_tax']) ) ? $data['order_shipping_tax'] : 0, (!empty($data['order_tax']) ) ? $data['order_tax'] : 0 );

                    if (!$tax_id) {
                        $result['warning'][] = esc_html__('Tax line item could not properly be added to this subscription. Please review this subscription.', 'wf_order_import_export');
                    } else {
                        $chosen_tax_rate_id = $tax_rate->tax_rate_id;
                    }
                } else {
                    $result['warning'][] = esc_html__(sprintf('The tax code "%s" could not be found in your store.', $tax_data['code']), 'wf_order_import_export');
                }
            }
        }

        return $chosen_tax_rate_id;
    }

}
