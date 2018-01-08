<div class="frm_mlcmp_group_select_<?php echo $group['id'] ?> frm_mlcmp_group_select">
<?php
    foreach ( $group['groups'] as $g ) { ?>
    <div>
        <label class="frm_left_label"><span class="frm_indent_opt"><?php echo esc_html($g['name']) ?></span></label>
        <p class="frm_show_selected_values_<?php echo $group['id']; ?>" class="no_taglist">
        <?php 
            if ( isset($new_field) ) {
                $field_id = $action_control->get_field_name('groups') . '['. $group['id'] .']['. $g['name'] .']';
                $field_name = $field_id;
                if ( isset($list_options['groups'][$group['id']]) && isset($list_options['groups'][$group['id']][$g['name']]) ) {
                    $val = $list_options['groups'][$group['id']][$g['name']];
                } else {
                    $val = '';
                }
                
                include(FrmAppHelper::plugin_path() .'/pro/classes/views/frmpro-fields/field-values.php');
            } else { ?>
            <select style="visibility:hidden;">
                <option value=""> </option>
            </select>
<?php    
            } ?>
        </p>
    </div>
<?php 
        unset($g);
    }
?>
    <div class="clear"></div>
</div>