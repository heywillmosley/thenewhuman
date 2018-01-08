<script type="text/javascript">
jQuery(document).ready(function($){
$('#frm_notification_settings').on('change', '.frm_single_mailchimp_settings select[name$="[list_id]"]', frmMlcmpFields);
$('#frm_notification_settings').on('change', 'select.frm_mlcmp_group', frmMlcmpGetFieldGrpValues);
});

function frmMlcmpFields(){
    var form_id = jQuery('input[name="id"]').val();
    var id = jQuery(this).val();
    var key = jQuery(this).closest('.frm_single_mailchimp_settings').data('actionkey');
    var div = jQuery(this).closest('.mlchp_list').find('.frm_mlcmp_fields');
    div.empty().append('<span class="spinner frm_mlcmp_loading_field"></span>');
    jQuery('.frm_mlcmp_loading_field').fadeIn('slow');
    jQuery.ajax({
        type:'POST',url:ajaxurl,
        data:{action:'frm_mlcmp_match_fields', form_id:form_id, list_id:id, action_key:key},
        success:function(html){
            div.replaceWith(html).fadeIn('slow');
        }
    });
}

function frmMlcmpGetFieldGrpValues(){
    var form_id = jQuery('input[name="id"]').val();
    var field_id = jQuery(this).val();
    if(field_id == ''){
        return false;
    }
    var key = jQuery(this).closest('.frm_single_mailchimp_settings').data('actionkey');
    var list_id = jQuery(this).closest('.mlchp_list').find('select[name$="[list_id]"]').val();
    var grp = jQuery(this).closest('.frm_mlcmp_group_box');
    var grp_id = grp.data('gid')
    
    jQuery.ajax({
        type:'POST',url:ajaxurl,
        data:{action:'frm_mlcmp_get_group_values', form_id:form_id, list_id:list_id, field_id:field_id, group_id:grp_id,  action_key:key},
        success:function(html){
            grp.find('.frm_mlcmp_group_select').html(html);
        } 
    });
}
</script>