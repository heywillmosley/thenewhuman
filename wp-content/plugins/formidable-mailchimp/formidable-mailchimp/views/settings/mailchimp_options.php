<table class="form-table">
    <tbody>
    <tr>
    <td><label for="mailchimp"><input type="checkbox" name="options[mailchimp]" id="mailchimp" value="1" <?php checked($values['mailchimp'], 1); ?> /> <?php _e('Add users who submit this form to a Mailchimp mailing list', 'formidable') ?></label></td>
    </tr>
<?php
        if(!empty($values['mlcmp_list'])){
            $hide_mailchimp = ($values['mailchimp']) ? '' : 'style="display:none;"';
            foreach((array)$values['mlcmp_list'] as $list_id => $list_options){
                if(!is_array($list_options))
                    continue;

                $list_fields = FrmMlcmpAppController::decode_call('/lists/merge-vars', array( 'id' => array( $list_id ) ) );
                $groups = FrmMlcmpAppController::get_groups($list_id);
                include(FrmMlcmpAppController::path() .'/views/settings/_list_options.php');
                unset($groups);
                unset($list_fields);
            }
        }
        
        ?>
    </tbody>
</table>
<p id="mlcmp_add_button" class="hide_mailchimp" style="margin-left:10px;<?php echo $values['mailchimp'] ? '' : 'display:none;'; ?>">
    <a href="javascript:void(0)" class="button-secondary frm_mlcmp_add_list frm_add_logic_link">+ <?php _e('Add List', 'formidable') ?></a></p>
</p>

<style type="text/css">
.themeRoller .mailchimp_settings{color:#333;display:block !important;}
.mlchp_list > td{border-top:1px solid #DFDFDF;}
table .mlchp_list:nth-child(2) > td{border:none;}
</style>
<script type="text/javascript">
jQuery(document).ready(function($){
frm_form_id=<?php echo $values['id'] ?>;
$('#mailchimp_settings').on('change', 'select[name="mlcmp_list[]"]', frmMlcmpFields);
$('#mailchimp_settings').on('change', '.frm_mlcmp_group', frmMlcmpGetFieldGrpValues);
$('#mailchimp_settings').on('click', '.frm_mlcmp_remove', frmMlcmpRemoveList);
$('#mailchimp_settings').on('click', '.frm_add_mlcmp_logic', frmMlcmpAddLogicRow);
$('#mailchimp_settings').on('click', '.frm_mlcmp_add_list', frmMlcmpAddList);
$('input#mailchimp').click(function(){
    frm_show_div('hide_mailchimp',this.checked,1,'.');
    if(this.checked) frmMlcmpAddList();
    else $('.frm_mlcmp_remove').click();
});
});

function frmMlcmpFields(){
    var id=jQuery(this).val();
    var htmlid=jQuery(this).attr('id').replace('select_list_', '');
    var div=jQuery(this).closest('.mlchp_list').find('.frm_mlcmp_fields');
    div.empty().append('<img class="frm_mlcmp_loading_field" src="'+ frm_js.images_url +'/wpspin_light.gif" alt="'+ frm_js.loading +'" style="display:none;"/>');
    jQuery('.frm_mlcmp_loading_field').fadeIn('slow');
    jQuery.ajax({
        type:"POST",url:ajaxurl,
        data:"action=frm_mlcmp_match_fields&form_id="+frm_form_id+"&list_id="+id,
        success:function(html){
            div.replaceWith(html).fadeIn('slow');
        }
    });
}

function frmMlcmpAddList(){
    var len=jQuery('.mlchp_list').length+1;
    jQuery('#mailchimp_settings .form-table tbody').append('<tr class="frm_mlcmp_loading_list"><td><img src="'+ frm_js.images_url +'/wpspin_light.gif" alt="'+ frm_js.loading +'" style="display:none;"/></td></tr>');
    jQuery('.frm_mlcmp_loading_list img').fadeIn('slow');
    jQuery.ajax({
        type:"POST",url:ajaxurl,
        data:"action=frm_mlcmp_add_list&list_id="+len,
        success:function(html){jQuery('.frm_mlcmp_loading_list').replaceWith(html);jQuery('.mailchimp_settings').fadeIn('slow');}
    });
}

function frmMlcmpRemoveList(){
    var id=jQuery(this).attr('id').replace('remove_list_', '');
    jQuery('.mlchp_list_'+id+',#frm_mlcmp_fields_'+id+',.frm_mlcmp_fields_'+id).fadeOut(1000, function(){
        jQuery('.mlchp_list_'+id+',#frm_mlcmp_fields_'+id+',.frm_mlcmp_fields_'+id).replaceWith('');
    });
}

function frmMlcmpAddLogicRow(){
    var id=jQuery(this).closest('.frm_mlcmp_fields').data('lid');
if(jQuery('#frm_mlcmp_logic_row_'+id+' .frm_logic_row_mailchimp').length){
	var len=1+parseInt(jQuery('#frm_mlcmp_logic_row_'+id+' .frm_logic_row_mailchimp:last').attr('id').replace('frm_mlcmp_logic_'+id+'_', ''));
}else{
    var len=0;
}
jQuery.ajax({
    type:"POST",url:ajaxurl,
    data:"action=frm_mlcmp_add_logic_row&form_id="+frm_form_id+"&list_id="+id+"&meta_name="+len,
    success:function(html){
        jQuery('.frm_mlcmp_fields_'+id+' .frm_add_logic_link').hide();
        jQuery('#frm_mlcmp_logic_rows_'+id).show();
        jQuery('#frm_mlcmp_logic_row_'+id).append(html);
    }
});
    return false;
}

function frmMlcmpGetFieldGrpValues(){
    var field_id = jQuery(this).val();
    if(field_id == ''){
        return false;
    }
    var list_id = jQuery(this).closest('.frm_mlcmp_fields').data('lid');
    var grp = jQuery(this).closest('.frm_mlcmp_group_box').data('gid');
    jQuery.ajax({
        type:"POST",url:ajaxurl,
        data:"action=frm_mlcmp_get_group_values&form_id="+frm_form_id+"&list_id="+list_id+"&field_id="+field_id+'&group_id='+grp,
        success:function(html){
            jQuery('#frm_mlcmp_group_select_'+list_id+'_'+grp).html(html);
        } 
    });
}
</script>