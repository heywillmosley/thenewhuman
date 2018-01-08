<div id="frm_mlcmp_group_select_<?php echo $list_id ?>_<?php echo $group['id'] ?>" class="frm_mlcmp_group_select">
<?php
    foreach ( $group['groups'] as $g ) { ?>
    <div>
        <label class="frm_left_label"><span class="frm_indent_opt"><?php echo esc_html($g['name']) ?></span></label>
        <p class="frm_show_selected_values_<?php echo $list_id .'_'. $group['id']; ?>" class="no_taglist">
        <?php 
            if ( isset($new_field) ) {
                $field_id = (isset($g) && isset($group)) ? "options[mlcmp_list][{$list_id}][groups][{$group['id']}][{$g['name']}]" : "options[mlcmp_list][{$list_id}][hide_opt]";
                $field_name = (isset($g) && isset($group)) ? $field_id : $field_id .'[]';
                if ( isset($list_options['groups'][$group['id']]) && isset($list_options['groups'][$group['id']][$g['name']]) ) {
                    $val = $list_options['groups'][$group['id']][$g['name']];
                } else {
                    $val = '';
                }
                
                $path = method_exists('FrmAppHelper', 'plugin_path') ? FrmAppHelper::plugin_path() : FRM_PATH;
                include($path .'/pro/classes/views/frmpro-fields/field-values.php');
                unset($path);
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