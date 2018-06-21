<table class="form-table frm-no-margin">
<tbody>
<tr class="mlchp_list">
    <td>
    <p>
        <?php if ( $lists && isset( $lists['lists'] ) ) { ?>
        <label class="frm_left_label" style="clear:none;"><?php esc_html_e( 'List', 'frmmlcmp' ) ?> <span class="frm_required">*</span></label>
        <select name="<?php echo esc_attr( $action_control->get_field_name('list_id') ) ?>">
            <option value=""><?php esc_html_e( '&mdash; Select &mdash;' ); ?></option>
            <?php foreach ( $lists['lists'] as $list ) { ?>
            <option value="<?php echo esc_attr( $list['id'] ) ?>" <?php selected( $list_id, $list['id'] ) ?>>
				<?php echo FrmAppHelper::truncate( $list['name'], 40 ) ?>
			</option>
            <?php } ?>
        </select>
        <?php } else {
            esc_html_e( 'No MailChimp mailing lists found', 'frmmlcmp' );
			if ( isset( $lists['error'] ) ) {
				echo '<br/>'. $lists['error'];
			}
        } ?>
    </p>
<div class="clear"></div><?php
        include( FrmMlcmpAppHelper::plugin_path() .'/views/action-settings/address_action_setting.php');
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
