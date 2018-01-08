<table class="form-table frm-no-margin">
<tbody>
<tr class="mlchp_list">
    <td>
    <p>
        <?php if ( $lists ) { ?>
        <label class="frm_left_label" style="clear:none;"><?php _e('List', 'frmmlcmp') ?> <span class="frm_required">*</span></label>
        <select name="<?php echo $action_control->get_field_name('list_id') ?>">
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
    include(dirname(__FILE__) .'/_match_fields.php');
} else { ?>
<div class="frm_mlcmp_fields"></div>
<?php    
} ?>

</td>
</tr>
</tbody>
</table>
