<div class="tool-box">
    <?php
    $order_statuses = wc_get_order_statuses();
    ?>
    <h3 class="title"><?php _e('Export Orders in CSV/XML Format:', 'wf_order_import_export'); ?></h3>
    <p><?php _e('Export and download your orders in CSV/XML format. This file can be used to import orders back into your Woocommerce shop.', 'wf_order_import_export'); ?></p>
    <form  method="post">

        <table class="form-table">
            <tr>
                <th>
                    <label for="v_order_status"><?php _e('Order Statuses', 'wf_order_import_export'); ?></label>
                </th>
                <td>
                    <select id="v_order_status" name="order_status[]" data-placeholder="<?php _e('All Orders', 'wf_order_import_export'); ?>" class="wc-enhanced-select" multiple="multiple">
                        <?php
                        foreach ($order_statuses as $key => $column) {
                            echo '<option value="' . $key . '">' . $column . '</option>';
                        }
                        ?>
                    </select>
                                                        
                    <p style="font-size: 12px"><?php _e('Orders with these status will be exported.', 'wf_order_import_export'); ?></p>
                </td>
            </tr>  
            <tr>
                <th>
                    <label for="v_offset"><?php _e('Offset', 'wf_order_import_export'); ?></label>
                </th>
                <td>
                    <input type="text" name="offset" id="v_offset" placeholder="<?php _e('0', 'wf_order_import_export'); ?>" class="input-text" />
                    <p style="font-size: 12px"><?php _e('The number of orders to skip before returning.', 'wf_order_import_export'); ?></p>
                </td>
            </tr>            
            <tr>
                <th>
                    <label for="v_limit"><?php _e('Limit', 'wf_order_import_export'); ?></label>
                </th>
                <td>
                    <input type="text" name="limit" id="v_limit" placeholder="<?php _e('Unlimited', 'wf_order_import_export'); ?>" class="input-text" />
                    <p style="font-size: 12px"><?php _e('The number of orders to return.', 'wf_order_import_export'); ?></p>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="v_start_date"><?php _e('Start Date', 'wf_order_import_export'); ?></label>
                </th>
                <td>
                    <input type="text" name="start_date"  id="v_start_date" />
                    <p>Format: <code>YYYY-MM-DD.</code></p>         
                </td>
            </tr>
            <tr>
                <th>
                    <label for="v_end_date"><?php _e('End Date', 'wf_order_import_export'); ?></label>
                </th>
                <td>
                    <input type="text" name="end_date"  id="v_end_date" />
                    <p>Format: <code>YYYY-MM-DD.</code></p>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="v_delimiter"><?php _e('Delimiter', 'wf_order_import_export'); ?></label>
                </th>
                <td>
                    <input type="text" name="delimiter" id="v_delimiter" placeholder="<?php _e(',', 'wf_order_import_export'); ?>" class="input-text" />
                    <p style="font-size: 12px"><?php _e('Column seperator for exported file', 'wf_order_import_export'); ?></p>
                </td>
            </tr>
            
            
            
            
            <tr>
                <th>
                    <label for="v_columns"><?php _e('Columns', 'wf_order_import_export'); ?></label>
                </th>
            <table id="datagrid">
                <!-- select all boxes -->
                  <tr>
                      <td style="padding: 10px;">
                          <a href="#" id="selectall" onclick="return false;" >Select all</a> &nbsp;/&nbsp;
                          <a href="#" id="unselectall" onclick="return false;">Unselect all</a>
                      </td>
                  </tr>
                <th style="text-align: left;">
                    <label for="v_columns"><?php _e('Column', 'wf_order_import_export'); ?></label>
                </th>
                <th style="text-align: left;">
                    <label for="v_columns_name"><?php _e('Column Name', 'wf_order_import_export'); ?></label>
                </th>
                <?php 
                ?>
                <?php foreach ($post_columns as $pkey => $pcolumn) {
                            
                         ?>
            <tr>
                <td>
                    <input name= "columns[<?php echo $pkey; ?>]" type="checkbox" value="<?php echo $pkey; ?>" checked>
                    <label for="columns[<?php echo $pkey; ?>]"><?php _e($pcolumn, 'wf_order_import_export'); ?></label>
                </td>
                <td>
                     <input type="text" name="columns_name[<?php echo $pkey; ?>]"  value="<?php echo $pkey; ?>" class="input-text" />
                </td>
            </tr>
                <?php } ?>
                
            </table><br/>
            </tr>
            

        </table>
        <p class="submit"><input type="submit" class="button button-primary" value="<?php _e('Export Orders (CSV)', 'wf_order_import_export'); ?>" formaction="<?php echo admin_url('admin.php?page=wf_woocommerce_order_im_ex&action=export'); ?>" />
        <input type="submit" class="button button-primary" value="<?php _e('Export Orders (XML)', 'wf_order_import_export'); ?>" formaction="<?php echo admin_url('admin.php?page=wf_woocommerce_order_im_ex&action=export&xml=1'); ?>"/></p>
    </form>
</div>