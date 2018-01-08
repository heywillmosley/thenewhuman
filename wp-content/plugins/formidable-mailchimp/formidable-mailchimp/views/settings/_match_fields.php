<div class="frm_mlcmp_fields frm_mlcmp_fields_<?php echo $list_id ?>" data-lid="<?php echo $list_id ?>">
<?php
if(!isset($list_options['optin']))
    $list_options['optin'] = 0;
?>

<?php foreach($list_fields['data'][0]['merge_vars'] as $list_field){ ?>
<p><label class="frm_left_label"><?php echo $list_field['name']; ?> 
    <?php
    if ( $list_field['req'] ) {
        ?><span class="frm_required">*</span><?php
    } ?>
    </label>
    
    <select name="options[mlcmp_list][<?php echo $list_id ?>][fields][<?php echo $list_field['tag'] ?>]">
        <option value=""><?php _e( '&mdash; Select &mdash;' ); ?></option>
        <?php foreach($form_fields as $form_field){ 
                if ( $list_field['field_type'] == 'email' && !in_array($form_field->type, array('email', 'hidden', 'user_id')) ) {
                    continue;
                }
                
                $selected = (isset($list_options['fields'][$list_field['tag'] ]) && $list_options['fields'][$list_field['tag']] == $form_field->id) ? ' selected="selected"' : '';
            ?>
        <option value="<?php echo $form_field->id ?>" <?php echo $selected ?>><?php echo FrmAppHelper::truncate($form_field->name, 40) ?></option>
        <?php } ?>
    </select>
</p>
<?php } ?>
<?php

if($groups){
foreach($groups as $group){ 
    if(!isset($group['id']))
        continue;
?>
<div class="frm_mlcmp_group_box" data-gid="<?php echo $group['id'] ?>">
    <label class="frm_left_label"><?php echo esc_html($group['name']); ?></label>
    <select name="options[mlcmp_list][<?php echo $list_id ?>][groups][<?php echo $group['id'] ?>][id]" class="frm_mlcmp_group">
            <option value=""><?php _e( '&mdash; Select &mdash;' ); ?></option>
            <?php 
            foreach ( $form_fields as $form_field ) {
                if(!in_array($form_field->type, array('hidden', 'select', 'radio', 'checkbox', 'data')))
                    continue;
                
                if ( (isset($list_options['groups'][$group['id']]) && $list_options['groups'][$group['id']]['id'] == $form_field->id) ) {
                    $selected = ' selected="selected"';
                    $new_field = $form_field;
                }else{
                    $selected = '';
                }
                
            ?>
            <option value="<?php echo $form_field->id ?>" <?php echo $selected ?>><?php echo FrmAppHelper::truncate($form_field->name, 40) ?></option>
            <?php } ?>
    </select>
    <?php
    include('_group_values.php');
        
    if ( isset($new_field) ) {
        unset($new_field);
    }
        
    ?>
</div>
<?php }
} ?>

<p><label class="frm_left_label"><?php _e('Opt In', 'formidable') ?></label>
    <select name="options[mlcmp_list][<?php echo $list_id ?>][optin]" id="mlcmp_optin_<?php echo $list_id ?>">
        <option value="0"><?php _e('Single', 'formidable') ?></option>
        <option value="1" <?php selected($list_options['optin'], 1); ?>><?php _e('Double', 'formidable') ?></option>
    </select> 
</p>

<div class="frm_add_remove">
    <p class="frm_add_logic_link">
        <a class="frm_add_mlcmp_logic frm_add_logic_link" data-emailkey="mlcmp_<?php echo $list_id ?>" <?php echo (!isset($list_options['hide_field']) || empty($list_options['hide_field'])) ? '' : 'style="display:none"'; ?>><?php _e('Use Conditional Logic', 'formidable') ?></a>
    </p>
<div class="frm_logic_rows" id="frm_mlcmp_logic_rows_<?php echo $list_id ?>" <?php if(!isset($list_options['hide_field']) || empty($list_options['hide_field'])){ echo 'style="display:none;"'; } ?>>
    <h4><?php _e('Conditional Logic', 'formidable') ?></h4>
    <div class="frm_mlcmp_logic_rows">
        <div id="frm_mlcmp_logic_row_<?php echo $list_id ?>">
<?php 
if ( isset($list_options['hide_field']) && !empty($list_options['hide_field']) ) {
    foreach ( (array) $list_options['hide_field'] as $meta_name => $hide_field ) {
        FrmMlcmpAppHelper::include_logic_row($meta_name, (isset($values) && isset($values['id'])) ? $values['id'] : $form_id, $list_id, $list_options);
        unset($meta_name, $hide_field);
    }
}
?>
        </div>
    </div>
</div>
</div>
</div>