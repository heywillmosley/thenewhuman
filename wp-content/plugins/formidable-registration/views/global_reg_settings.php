<h3 class="frm_first_h3">Global Pages</h3>
<table class="form-table">
    <tr class="form-field" valign="top">
        <td width="150px"><label <?php FrmRegAppHelper::maybe_add_tooltip('login_logout'); ?>><?php _e('Login/Logout URL', 'frmreg') ?></label></td>
    	<td>
            <?php FrmAppHelper::wp_pages_dropdown( 'frm_reg_login', $frm_reg_settings->settings->login ) ?>
				
    	</td>
    </tr>
	<tr>
		<td colspan="2">
			<span class="howto"><?php _e('Prevent logged-out users from seeing the wp-admin page. Select a page where logged-out users will be redirected when they try to access the wp-admin page or just leave this option blank.', 'frmreg') ?></span>
		</td>
	</tr>
    
    <tr class="form-field" style="display:none;" valign="top">
        <td><label><?php _e('Lost Password', 'frmreg') ?></label></td>
    	<td>
            <?php FrmAppHelper::wp_pages_dropdown( 'frm_reg_lostpass', $frm_reg_settings->settings->lostpass ) ?>
				
    	</td>
    </tr>
</table>
<script type="text/javascript">
var login_dropdown = document.getElementById( 'frm_reg_login' );
login_dropdown.onchange=function(){
	if ( login_dropdown.value == '' ) {
		return;
	}
	var accept = confirm( 'Please note: You must insert a login form on the selected page.');
	if ( accept == false ) {
		login_dropdown.value = '';
	}
};
</script>