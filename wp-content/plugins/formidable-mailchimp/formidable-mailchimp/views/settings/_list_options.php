<tr class="hide_mailchimp mlchp_list mlchp_list_<?php echo $list_id ?>" <?php echo $hide_mailchimp; ?>>
    <td>
    <a class="frm_mlcmp_remove alignright frm_email_actions feature-filter" id="remove_list_<?php echo $list_id ?>" href="javascript:void(0)"><img src="<?php echo method_exists('FrmAppHelper', 'plugin_url') ? FrmAppHelper::plugin_url() : FRM_URL; ?>/images/trash.png" alt="<?php _e('Remove', 'formidable') ?>" title="<?php _e('Remove', 'formidable') ?>" /></a>
    <p>
        <?php if ( $lists ) { ?>
        <label class="frm_left_label" style="clear:none;"><?php _e('List to Subscribe', 'frmmlcmp') ?> <span class="frm_required">*</span></label>
        <select name="mlcmp_list[]" id="select_list_<?php echo $list_id ?>">
            <option value=""><?php _e( '&mdash; Select &mdash;' ); ?></option>
            <?php foreach($lists['data'] as $list){ ?>
            <option value="<?php echo $list['id'] ?>" <?php selected($list_id, $list['id']) ?>><?php echo FrmAppHelper::truncate($list['name'], 40) ?></option>
            <?php } ?>
        </select>
        <?php } else {
            _e('No MailChimp mailing lists found', 'formidable');
        } ?>
    </p>
<div class="clear"></div>

<?php 
if ( isset($list_fields) && $list_fields ) {
    include(FrmMlcmpAppController::path() .'/views/settings/_match_fields.php');
} else { ?>
<div class="frm_mlcmp_fields"></div>
<?php    
} ?>

</td>
</tr>
