<?php

/**
 * Includes the assets loading and script printing functionality for the Modules
 * page.
 */
class USIN_Module_Assets extends USIN_Assets{

	protected function register_custom_assets(){
		$this->js_assets['usin_modules'] = array('path' => 'js/modules.min.js',
			'deps' => array('usin_angular', 'usin_helpers'));
		$this->js_assets['usin_module_templates'] = array('path' => 'views/modules/templates.js',
			'deps' => array('usin_modules'));
	}

	/**
	 * Loads the required assets on the Modules page/
	 */
	public function enqueue_assets(){
		$this->enqueue_scripts(array('usin_angular', 'usin_helpers', 'usin_modules',
			'usin_module_templates'));

		$this->enqueue_style('usin_main_css');
		
	}


	/**
	 * Prints the initializing JavaScript code on the Modules page.
	 */
	protected function print_inline(){
		$module_options = usin_module_options();

		$options = array(
			'viewsURL' => 'views/modules',
			'ajaxURL' => admin_url( 'admin-ajax.php' ),
			'modules' => $module_options->get_module_options(),
			'nonce' => $this->page->ajax_nonce
		);

		$strings = array(
			'activeModules' => __('Active Modules', 'usin'),
			'inactiveModules' => __('Inactive Modules', 'usin'),
			'settings' => __('Settings', 'usin'),
			'activateModule' => __('Activate Module', 'usin'),
			'deactivateModule' => __('Deactivate Module', 'usin'),
			'freeTrial' => __('Try for free', 'usin'),
			'buy' => __('Buy now', 'usin'),
			'enterLicense' => __('Enter a license key', 'usin'),
			'licenseKey' => __('License key', 'usin'),
			'addLicense' => __('Add license', 'usin'),
			'removeLicense' => __('Remove', 'usin'),
			'refresh' => __('Refresh', 'usin'),
			'licenseActivated' => __('License activated', 'usin'),
			'licenseDeactivated' => __('License deactivated', 'usin'),
			'error' => __('Error', 'usin'),
			'errorRequest' => __('HTTP request error', 'usin'),
			'noActiveModules' => __('No active modules', 'usin'),
			'noInactiveModules' => __('No inactive modules', 'usin'),
			'noModuleLicense' => __('This module requires a license key to be set in the "%s" section', 'usin'),
			'beta' => __('Beta', 'usin')
		);

		$options['strings'] = $strings;
		$options = apply_filters('usin_user_module_options', $options);

		$output = '<script type="text/javascript">var USIN = '.json_encode($options).';</script>';

		echo $output;

	}

}