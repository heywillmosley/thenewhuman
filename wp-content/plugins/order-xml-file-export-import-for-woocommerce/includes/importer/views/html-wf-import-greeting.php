<div>

    <p><?php _e('You can import orders (in XML format) in to the shop using any of below methods.', 'wf_order_import_export_xml'); ?></p>



<?php if (!empty($upload_dir['error'])) : ?>

        <div class="error"><p><?php _e('Before you can upload your import file, you will need to fix the following error:', 'wf_order_import_export_xml'); ?></p>

            <p><strong><?php echo $upload_dir['error']; ?></strong></p></div>

    <?php else : ?>

        <form enctype="multipart/form-data" id="import-upload-form" method="POST" action="<?php echo esc_attr(wp_nonce_url($action, 'import-upload')); ?>" name="import_data">

            <table class="form-table">

                <tbody>

                    <tr>

                        <th>
                            <?php _e('XML Type','wf_order_import_export_xml'); ?>
                        </th>

                        <td>
                        
                            <select id="v_order_import_type" name="order_import_type" data-placeholder="<?php _e('Orders Import Type', 'wf_order_import_export_xml'); ?>" onchange="showDiv(this)">
                                <option value="general"><?php _e("WooCommerce",'wf_order_import_export_xml') ?></option>
                                <option value="stamps"><?php _e("Stamps.Com",'wf_order_import_export_xml') ?></option>
                                <option value="fedex"><?php _e("FedEx",'wf_order_import_export_xml') ?></option>
                                <option value="ups"><?php _e("UPS WorldShip",'wf_order_import_export_xml') ?></option>
                                <option value="endicia"><?php _e("Endicia",'wf_order_import_export_xml') ?></option>
                            </select>
                            <div id="add_edit_choice">                           
                                    <?php _e('For existing order,','wf_order_import_export_xml'); ?>
                                &nbsp;
                                <input type="radio" name="order_import_type_decision" value="skip" checked /> <?php _e('Skip','wf_order_import_export_xml'); ?>
                            
                                <input type="radio" name="order_import_type_decision" value="overwrite" /> <?php _e('Overwrite','wf_order_import_export_xml'); ?>
                                &nbsp;
                                <?php _e(' the order.','wf_order_import_export_xml'); ?>
                            </div>
                        </td>
                        
                    </tr>
                    <script type="text/javascript">
                        function showDiv(elem){
                            if(elem.value == 'general')
                              document.getElementById('add_edit_choice').style.display = "block";
                            else
                              document.getElementById('add_edit_choice').style.display = "none";  
                        }
                    </script>
                    <tr>

                        <th>

                            <label for="upload"><?php _e('Select a file from your computer', 'wf_order_import_export_xml'); ?></label>

                        </th>

                        <td>

                            <input type="file" id="upload" name="import" size="25" />

                            <input type="hidden" name="action" value="save" />

                            <input type="hidden" name="max_file_size" value="<?php echo $bytes; ?>" />

                            <small><?php printf(__('Maximum size: %s'), $size); ?></small>

                        </td>

                    </tr>

    
                </tbody>
            </table>
            <p class="submit">
                <input type="submit" class="button button-primary" value="<?php esc_attr_e('Upload file and import'); ?>" />
            </p>
        </form>

<?php endif; ?>

</div>