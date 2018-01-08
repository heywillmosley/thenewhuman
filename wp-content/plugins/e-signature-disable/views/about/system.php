<div class="esig-updated e-sign-alert">

<img src="<?php echo $data['home_url']; ?>/wp-content/plugins/e-signature/assets/images/wp-e-sign-logo.svg" width="100">

	<p><?php _e('Please include this information when requesting support:', 'esig'); ?> </p>
	<p class="submit"><a href="#" class="button-primary esig-debug-report"><?php _e('Get System Report', 'esig'); ?></a></p>
	<div id="debug-report" style="display:none;"><textarea readonly rows="20" cols="100"></textarea></div>
</div>

<table class="esign_status_table widefat" cellspacing="0">

	<thead>
		<tr>
			<th colspan="2"><?php _e('Environment', 'esig'); ?></th>
		</tr>
	</thead>

	<tbody>
		<tr>
			<td><?php _e('Home URL:', 'esig'); ?></td>
			<td><?php echo $data['home_url']; ?></td>
		</tr>
		<tr>
			<td><?php _e('Site URL:', 'esig'); ?></td>
			<td><?php echo $data['site_url']; ?></td>
		</tr>
		<tr>
			<td><?php _e('E-sign Version:', 'esig'); ?></td>
			<td><?php echo $data['e-sign_version']; ?></td>
		</tr>
		<tr>
			<td><?php _e('E-sign Database Version:', 'esig'); ?></td>
			<td><?php echo $data['e-sign_database_version']; ?></td>
		</tr>
		<tr>
			<td><?php _e('WP Version:', 'esig'); ?></td>
			<td><?php echo $data['wp_version']; ?></td>
		</tr>
		<tr>
			<td><?php _e('WP Multisite Enabled:', 'esig'); ?></td>
			<td><?php echo $data['wp_multisite_enabled']; ?></td>
		</tr>
		<tr>
			<td><?php _e('Web Server Info:', 'esig'); ?></td>
			<td><?php echo $data['web_server_info']; ?></td>
		</tr>
		<tr>
			<td><?php _e('PHP Version:', 'esig'); ?></td>
			<td><?php echo $data['php_version']; ?></td>
		</tr>
		<tr>
			<td><?php _e('MySQL Version:', 'esig'); ?></td>
			<td>
				<?php echo $data['mysql_version']; ?>		</td>
		</tr>
		<tr>
			<td><?php _e('PHP Required Extension:', 'esig'); ?></td>
			<td>
				<?php _e('MCrypt :', 'esig'); ?> <?php echo $data['mcrypt_extension']; ?>		</td>
		</tr>
		<tr>
			<td><?php _e('WP Memory Limit:', 'esig'); ?></td>
			<td><?php echo $data['wp_memory_limit']; ?></td>
		</tr>
		<tr>
			<td><?php _e('WP Debug Mode:', 'esig'); ?></td>
			<td><mark class="yes"><?php echo $data['wp_debug_mode']; ?></mark></td>
		</tr>
		<tr>
			<td><?php _e('WP Language:', 'esig'); ?></td>
			<td><?php echo $data['wp_language']; ?></td>
		</tr>
		<tr>
			<td><?php _e('WP Max Upload Size:', 'esig'); ?></td>
			<td><?php echo $data['wp_max_upload_size']; ?></td>
		</tr>
					<tr>
				<td><?php _e('PHP Post Max Size:', 'esig'); ?></td>
				<td><?php echo $data['php_post_max_size']; ?></td>
			</tr>
			<tr>
				<td><?php _e('PHP Time Limit:', 'esig'); ?></td>
				<td><?php echo $data['php_time_limit']; ?></td>
			</tr>
			<tr>
				<td><?php _e('PHP Max Input Vars:', 'esig'); ?></td>
				<td><?php echo $data['php_max_input_vars']; ?></td>
			</tr>
			<tr>
				<td><?php _e('SUHOSIN Installed:', 'esig'); ?></td>
				<td><?php echo $data['suhosin_installed']; ?></td>
			</tr>
				<tr>
			<td><?php _e('E-sign Logging:', 'esig'); ?></td>
			<td><mark class="yes"><?php echo $data['e-sign_logging']; ?></mark></td>
		</tr>
		<tr>
			<td><?php _e('Default Timezone:', 'esig'); ?></td>
			<td><mark class="yes"><?php echo $data['default_timezone']; ?></mark>			</td>
		</tr>
						<tr>
					<td><?php _e('fsockopen/cURL:', 'esig'); ?></td>
					<td>
						<mark class="yes">
							<?php echo $data['fsockopen_curl']; ?>					</mark>
					</td>
				</tr>
								<tr>
					<td><?php _e('SOAP Client:', 'esig'); ?></td>
					<td>
						<mark class="yes">
							<?php echo $data['soap_client']; ?>						</mark>
					</td>
				</tr>
				<tr>
					<td><?php _e('WP Connect Beta Server:', 'esig'); ?></td>
					<td>
						
							<?php echo $data['wp_remote_post']; ?> 
					</td>
				</tr>
					</tbody>

	<thead>
		<tr>
			<th colspan="2"><?php _e('Locale', 'esig'); ?></th>
		</tr>
	</thead>

	<tbody>
		<tr><td><?php _e('decimal_point:', 'esig'); ?></td><td><?php echo $data['decimal_point']; ?></td></tr>
		<tr><td><?php _e('thousands_sep:', 'esig'); ?></td><td><?php echo $data['thousands_sep']; ?></td></tr>
		<tr><td><?php _e('mon_decimal_point:', 'esig'); ?></td><td><?php echo $data['mon_decimal_point']; ?></td></tr>
		<tr><td><?php _e('mon_thousands_sep:', 'esig'); ?></td><td><?php echo $data['mon_thousands_sep']; ?></td></tr>	</tbody>

	<thead>
		<tr>
			<th colspan="2"><?php _e('Plugins', 'esig'); ?></th>
		</tr>
	</thead>

	<tbody>
		<tr>
			<td><?php _e('Installed Plugins:', 'esig'); ?></td>
			<td><?php echo $data['installed_plugins']; ?></td>
		</tr>
	</tbody>

	<thead>
		<tr>
			<th colspan="2"><?php _e('Settings', 'esig'); ?></th>
		</tr>
	</thead>

	<tbody>
		<tr>
			<td><?php _e('Force SSL:', 'esig'); ?></td>
			<td><mark class="no"><?php echo $data['force_ssl']; ?></mark></td>
		</tr>
	</tbody>

	<thead>
		<tr>
			<th colspan="2"><?php _e('E-sign Pages', 'esig'); ?></th>
		</tr>
	</thead>

	<tbody>
		 <?php echo $data['esign_pages']; ?>
	</tbody>

	

	<thead>
		<tr>
			<th colspan="2"><?php _e('Theme', 'esig'); ?></th>
		</tr>
	</thead>

        	<tbody>
            <tr>
                <td><?php _e('Theme Name:', 'esig'); ?></td>
                <td><?php echo $data['theme_name']; ?></td>
            </tr>
            <tr>
                <td><?php _e('Theme Version:', 'esig'); ?></td>
                <td><?php echo $data['theme_version']; ?></td>
            </tr>
            <tr>
                <td><?php _e('Author URL:', 'esig'); ?></td>
                <td><?php echo $data['author_url']; ?></td>
            </tr>
	</tbody>


</table>