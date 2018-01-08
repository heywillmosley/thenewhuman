<?php
if (!defined('ABSPATH')) {
    exit;
}

class WF_OrderImpExpCsv_Admin_Screen {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('admin_print_styles', array($this, 'admin_scripts'));
        add_action('admin_notices', array($this, 'admin_notices'));

        add_action('admin_footer-edit.php', array($this, 'add_order_bulk_actions'));
        add_action('load-edit.php', array($this, 'process_order_bulk_actions'));
        add_action('woocommerce_admin_order_actions_end', array($this, 'add_order_action'), 10, 2);
        add_filter('woocommerce_admin_order_actions', array($this, 'hf_add_order_action'), 10, 2);
        if (is_admin()) {
            add_action('wp_ajax_wc_order_csv_export_single', array($this, 'process_ajax_export_single_order'));
        }
		$this->csv_documents = array(
			array(
				'id'		=> 'download_to_csv_wf',
				'name'		=> 'Download to CSV'
			),
			/*array(
				'id'		=> 'download_to_csv_wf',
				'name'		=> 'Download to CSV'
			)*/
		);
		$this->xml_documents = array(
			array(
				'id'		=> 'download_to_general_xml_wf',
				'name'		=> 'Download to WooCommerce XML',
				'method'	=> 'general'
			),
			array(
				'id'		=> 'download_to_stamps_xml_wf',
				'name'		=> 'Download to Stamps XML',
				'method'	=> 'stamps'
			),
			array(
				'id'		=> 'download_to_fedex_xml_wf',
				'name'		=> 'Download to FedEx XML',
				'method'	=> 'fedex'
			),
			array(
				'id'		=> 'download_to_ups_xml_wf',
				'name'		=> 'Download to UPS WorldShip XML',
				'method'	=> 'ups'
			),
			array(
				'id'		=> 'download_to_endicia_xml_wf',
				'name'		=> 'Download to Endicia XML',
				'method'	=> 'endicia'
			)
		);
		
    }

    /**
     * Notices in admin
     */
    public function admin_notices() {
        if (!function_exists('mb_detect_encoding')) {
            echo '<div class="error"><p>' . __('Order CSV/XML Import Export requires the function <code>mb_detect_encoding</code> to import and export CSV/XML files. Please ask your hosting provider to enable this function.', 'wf_order_import_export') . '</p></div>';
        }
    }

    /**
     * Admin Menu
     */
    public function admin_menu() {
        $page = add_submenu_page('woocommerce', __('Order Im-Ex', 'wf_order_import_export'), __('Order Im-Ex', 'wf_order_import_export'), apply_filters('woocommerce_csv_order_role', 'manage_woocommerce'), 'wf_woocommerce_order_im_ex', array($this, 'output'));
    }

    /**
     * Admin Scripts
     */
    public function admin_scripts() {
        global $wp_scripts;
        wp_enqueue_script('wc-enhanced-select');

        wp_enqueue_style('woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css');
        wp_enqueue_style('woocommerce-order-csv-importer', plugins_url(basename(plugin_dir_path(WF_OrderImpExpCsv_FILE)) . '/styles/wf-style.css', basename(__FILE__)), '', '1.0.0', 'screen');

        wp_enqueue_script('woocommerce-order-csv-importer', plugins_url(basename(plugin_dir_path(WF_OrderImpExpCsv_FILE)) . '/js/woocommerce-order-csv-importer.js', basename(__FILE__)), array(), '2.0.0', true);
        wp_localize_script('woocommerce-order-csv-importer', 'woocommerce_order_csv_import_params', array('calendar_icon' => plugins_url(basename(plugin_dir_path(WF_OrderImpExpCsv_FILE)) . '/images/calendar.png', basename(__FILE__))));
        wp_localize_script('woocommerce-order-csv-importer', 'woocommerce_order_csv_cron_params', array('ord_enable_ftp_ie' => '','ord_auto_export' => 'Disabled', 'ord_auto_import' => 'Disabled'));
		
        
        wp_enqueue_script('jquery-ui-datepicker');
        
    }

    /**
     * Admin Screen output
     */
    public function output() {
        $tab = 'import';
		$plugin_name = 'ordercsvimportexport';
		include('wf_api_manager/html/html-wf-activation-window.php' );
        if (!empty($_GET['tab'])) {
            if ($_GET['tab'] == 'export') {
                $tab = 'export';
            } else if ($_GET['tab'] == 'settings') {
                $tab = 'settings';
            } else if($_GET['tab'] == 'importxml')
            {
                $tab = 'importxml';
            }else if($_GET['tab'] == 'help'){
                $tab = 'help';
            }
        }
        
        include( 'views/html-wf-admin-screen.php' );
    }

    public function add_order_bulk_actions() {
        global $post_type, $post_status;

        if ($post_type == 'shop_order' && $post_status != 'trash') {
            ?>
            <script type="text/javascript">
                jQuery(document).ready(function ($) {
                    var $downloadToXml = $('<option>').val('download_to_csv_wf').text('<?php _e('Download as CSV', 'wf_order_import_export') ?>');

                    $('select[name^="action"]').append($downloadToXml);
                });
            </script>
            <?php
        }
    }

    /**
     * Order page bulk export action
     * 
     */
    public function process_order_bulk_actions() {
        global $typenow;
        if ($typenow == 'shop_order') {
            // get the action list
            $wp_list_table = _get_list_table('WP_Posts_List_Table');
            $action = $wp_list_table->current_action();
            if (!in_array($action, array('download_to_csv_wf'))) {
                return;
            }
            // security check
            check_admin_referer('bulk-posts');

            if (isset($_REQUEST['post'])) {
                $order_ids = array_map('absint', $_REQUEST['post']);
            }
            if (empty($order_ids)) {
                return;
            }
            // give an unlimited timeout if possible
            @set_time_limit(0);

            if ($action == 'download_to_csv_wf') {
                include_once( 'exporter/class-wf-orderimpexpcsv-exporter.php' );
                WF_OrderImpExpCsv_Exporter::do_export('shop_order', $order_ids);
            }
        }
    }

    /* Function to add option to the end of order details.
	 * 
	 * @ since	2.0.3
	 * @ access	Public
	 * @ params action, order
	 */
    public function hf_add_order_action($actions, $order) {

		$hf_download_csv_options = array(
			array(
				'name'   => '',
				'action' => 'download_to_order_csv_xml',
				'url'    => sprintf( '#%s', (WC()->version < '2.7.0')? $order->id:$order->get_id() )
			),
		);
		return array_merge( $actions, $hf_download_csv_options);

    }

	public function add_order_action($order) 
	{		
		?>
                <div id="hf-download-tooltip-order-actions-<?php if(WC()->version < '2.7.0'){ echo $order->id; } else{echo $order->get_id();} ?>"class="hf-download-tooltip-order-actions " style="display:none;">
			<div class="hf-download-tooltip-content">
				<ul>
					<?php foreach ($this->csv_documents as $id => $value) { ?>
						<li>
							<a class="hf-download-tooltip-content "
							   href="<?php echo wp_nonce_url(admin_url('admin-ajax.php?action=wc_order_csv_export_single&order_id=' . (WC()->version < '2.7.0')? $order->id:$order->get_id()), 'wc_order_csv_export_single'); ?>"
							   target="_blank" >
								<?php echo esc_html(_e($value['name'], 'wf_order_import_export')); ?>
							</a>
						</li>
					<?php } ?>
					<?php foreach ($this->xml_documents as $id => $value) { ?>
						<li>
							<a class="hf-download-tooltip-content "
							   href="<?php echo wp_nonce_url(admin_url('admin-ajax.php?action=wc_order_xml_export_single&method=' . $value['method'] . '&order_id=' . (WC()->version < '2.7.0')? $order->id:$order->get_id()), 'wc_order_xml_export_single'); ?>"
							   target="_blank" >
								<?php echo esc_html(_e($value['name'], 'wf_order_import_export')); ?>
							</a>
						</li>
					<?php } ?>
				</ul>
			</div>
		</div>
		<?php
    }
	
    /**
     * Single order export
     */
    public function process_ajax_export_single_order() {

        if (!is_admin() || !current_user_can('edit_posts')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'wf_order_import_export'));
        }
        if (!check_admin_referer('wc_order_csv_export_single')) {
            wp_die(__('You have taken too long, please go back and try again.', 'wf_order_import_export'));
        }
        $order_id = !empty($_GET['order_id']) ? absint($_GET['order_id']) : '';
        if (!$order_id) {
            die;
        }
        $order_IDS = array(0 => $order_id);
        include_once( 'exporter/class-wf-orderimpexpcsv-exporter.php' );
        WF_OrderImpExpCsv_Exporter::do_export('shop_order', $order_IDS);
        wp_redirect(wp_get_referer());
        exit;
    }

    /**
     * Admin page for importing
     */
    public function admin_import_page() {
        include( 'views/html-wf-getting-started.php' );
        include( 'views/import/html-wf-import-orders.php' );
        $post_columns = include( 'exporter/data/data-wf-post-columns.php' );
        include( 'views/export/html-wf-export-orders.php' );
    }

    /**
     * Admin Page for exporting
     */
    public function admin_export_page() {
        $post_columns = include( 'exporter/data/data-wf-post-columns.php' );
        include( 'views/export/html-wf-export-orders.php' );
    }

    /**
     * Admin Page for settings
     */
    public function admin_settings_page() {
        include( 'views/settings/html-wf-all-settings.php' );
    }

}

new WF_OrderImpExpCsv_Admin_Screen();