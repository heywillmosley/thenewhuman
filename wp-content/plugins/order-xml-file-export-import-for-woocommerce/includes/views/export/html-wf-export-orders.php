
<div class="tool-box export-screen">
    <?php
    $order_statuses = wc_get_order_statuses();
    ?>
    <h3 class="title"><?php _e('Export Orders in XML Format:', 'wf_order_import_export_xml'); ?></h3>
    <h5><?php _e('(For sample format of XML export, <a href="'.admin_url('admin.php?page=wf_woocommerce_order_im_ex_xml&tab=help').'"> Click Here </a>)','wf_order_import_export_xml')?></h5>
    <p><?php _e('Export and download your orders in XML format.', 'wf_order_import_export_xml'); ?></p>
    <form action="<?php echo admin_url('admin.php?page=wf_woocommerce_order_im_ex_xml&action=export'); ?>" method="post">

        <table class="form-table"> 
            <tr>
                <th>
                    <label for="v_order_export_type"><?php _e('Order Export Type', 'wf_order_import_export_xml'); ?></label>
                </th>
                <td>
                    <select id="v_order_export_type" name="order_export_type" data-placeholder="<?php _e('Orders Export Type', 'wf_order_import_export_xml'); ?>">
                        <option value="general"><?php _e("WooCommerce",'wf_order_import_export_xml') ?></option>
                        <option value="stamps"><?php _e("Stamp.Com",'wf_order_import_export_xml') ?></option>
                        <option value="fedex"><?php _e("FedEx",'wf_order_import_export_xml') ?></option>
                        <option value="ups"><?php _e("UPS WorldShip",'wf_order_import_export_xml') ?></option>
                        <option value="endicia"><?php _e("Endicia",'wf_order_import_export_xml') ?></option>
                    </select>
                                                        
                    <p style="font-size: 12px"><?php _e('Orders with these type XML will be exported.', 'wf_order_import_export_xml'); ?></p>
                </td>
            </tr>
            
        

        </table>
        <p class="submit" style="padding-left: 10px;"><input type="submit" class="button button-primary" value="<?php _e('Export Orders', 'wf_order_import_export_xml'); ?>" /></p>
    </form>
</div>