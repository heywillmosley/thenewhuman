    <table class="form-table">
        <tr class="form-field" valign="top">
            <td width="200px"><label><?php _e('API Key', 'formidable') ?></label></td>
        	<td>
                <input type="text" name="frm_mlcmp_api_key" id="frm_mlcmp_api_key" value="<?php echo $frm_mlcmp_settings->settings->api_key ?>" class="frm_long_input" />
                <span class="frm_icon_font frm_mlcmp_resp"></span>
        	</td>
        </tr>
        
    </table>

<script type="text/javascript">
jQuery(document).ready(function($){
$('#frm_mlcmp_api_key').change(frmMlcmpCheckKey);
});

function frmMlcmpCheckKey( ) {
    var apikey = jQuery(this).val();
    if ( apikey == '' ) {
        jQuery('.frm_mlcmp_resp').html('');
        return;
    }

    jQuery.ajax({
        type:'POST',url:ajaxurl,dataType:'json',
        data:{action: 'frm_mlcmp_check_apikey', apikey: apikey, wpnonce: '<?php echo wp_create_nonce("frm_mlcmp") ?>'}, 
        success:function(res) {
            if ( 'error' in res ) {
                jQuery('.frm_mlcmp_resp').html( res.error ).addClass('frm_invalid_icon').removeClass('frm_valid_icon');
            } else {
                jQuery('.frm_mlcmp_resp').html( res.msg ).addClass('frm_valid_icon').removeClass('frm_invalid_icon');
            }
        }
    });
}
</script>