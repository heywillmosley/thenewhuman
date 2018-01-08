<?php

class WP_E_Common extends WP_E_Model {

    public function __construct() {
        parent::__construct();

        $this->settings = new WP_E_Setting();
        $this->document = new WP_E_Document();
        // adding action 
    }

    public function esig_document_search_form() {
        
        $document_status= isset( $_GET['document_status'] ) ?$_GET['document_status'] : "awaiting";
        
       // $query_args = add_query_arg( array("page"=>"esign-docs","document_status"=>$document_status),"admin.php");

        $html = '<form id="esig_document_search_form"  name="esig_document_search_form" action="" method="get"> ';

        $html .= apply_filters('esig_documents_search_filter', '');

        $esig_document_search = isset($_GET['esig_document_search']) ? $_GET['esig_document_search'] : null;
        
        $html .='<input type="hidden" name="document_status" value="'. $document_status .'">';

        $html .='<input type="hidden" name="page" value="esign-docs"><input type="text" class="esig_document_search" name="esig_document_search" style="min-width:250px;" placeholder="Document title or Signer name" value="' . $esig_document_search . '">
		
		<input type="submit" name="esig_search" class="button-primary" value="Search">
		</form>';

        return $html;
    }

    /*     * *
     * adddmin admin user from to set admin role . 
     * Since 1.0.13 
     * */

    public function esig_user_admin_dialog() {
        // previewing admin user settings dialog if doc super admin false . 
        if (ESIGN_DOC_SUPERADMIN_USERID === FALSE) {
            wp_enqueue_script('jquery-ui-dialog');
            add_thickbox();
            _e("<div id='esig_show_dialog' style='display:none;'>
					<div class='esig-show-dialog-content'>
					<h3>Setup your E-signature Admin</h3>
					<p>Select User:<select name='esig_admin_user' id='esig_admin_user'>
					", 'esig');

            $blogusers = get_users("role=administrator");

            foreach ($blogusers as $buser) {
                echo '<option value="' . $buser->ID . '">' . $buser->display_name . ' </option>';
            }

            echo "	</select></p>
					</div>
				</div>";
        }
    }

    /*     * *
     * Saving administrator from settings 
     * Since 1.0.13 
     * 
     * */

    public function esig_save_administrator() {

        $wpipd = get_current_user_id();

        if (count($_POST) > 0) {
            if (isset($_POST['esig_admin_user_id'])) {

                $admin_user_id = $_POST['esig_admin_user_id'];
                //getting settings class 
                $this->settings->delete_generic('esig_superadmin_user');
                $this->settings->set('esig_superadmin_user', $admin_user_id);
                // getting admin environment .php

                if ($wpipd != $admin_user_id) {
                    wp_redirect("admin.php?page=esign-docs");
                    exit;
                }
            }
        }

        $admin_user_id = $this->settings->get_generic('esig_superadmin_user');
        $html = '';
        if ($wpipd == $admin_user_id || $admin_user_id == null) {

            $html = "<select name='esig_admin_user_id' class='esig-select2' style='width:288px;'>
					";
            $blogusers = get_users("role=administrator");
            foreach ($blogusers as $buser) {
                if ($buser->ID == $wpipd) {
                    $selected = "selected ";
                } else {
                    $selected = " ";
                }
                $html .='<option value="' . $buser->ID . '" data-used="' . $wpipd . '" ' . $selected . '>' . $buser->user_login . ' </option>';
            }
            $html .="</select>";
            return $html;
        } else {
            // not super admin return plain text 
            $user_info = get_userdata($admin_user_id);
            $html .='<b>' . $user_info->display_name . '</b>';
            return $html;
        }

        // checking esig settngs table for already defined super admin . 				  
    }

    /**
     * Activate the license key
     *
     * @access  public
     * @return  void
     */
    public function esig_get_terms_conditions() {
        if (!function_exists('WP_E_Sig'))
            return;

        $esig = WP_E_Sig();

        $connected = @fsockopen("www.approveme.me", 80);

        if (!$connected) {
            if (file_exists(ESIGN_PLUGIN_PATH . "/assets/images/esign-ament.txt")) {
                $esig = new WP_E_Signature();
                return $esig->decrypt(ENCRYPTION_KEY, file_get_contents(ESIGN_PLUGIN_PATH . "/assets/images/esign-ament.txt"));
            }
        }


        $api_url = 'https://www.approveme.me';
        // Data to send to the API
        $api_params = array(
            'esig_action_terms' => 'esig_get_terms',
        );


        // Call the API
        $response = wp_remote_get(
                esc_url_raw(add_query_arg($api_params, $api_url)), array(
            'timeout' => 15,
            'body' => $api_params,
            'sslverify' => false
                )
        );

        // Make sure there are no errors
        if (is_wp_error($response))
            return;

        // Decode license data
        $condition_data = json_decode(wp_remote_retrieve_body($response));

        return $condition_data->terms_content;
    }

    /*     * *
     * Return bult action form element
     * @Since 1.1.3
     */

    public function esig_bulk_action_form() {

        $screen = get_current_screen();
        $current_screen = $screen->id;

        $admin_screens = array(
            'toplevel_page_esign-docs',
        );



        $html = '';
        if (in_array($screen->id, $admin_screens)) {
            $html .='<select name="esig_bulk_option" id="bulk-action-selector-top">
            <option value="-1" selected="selected">Bulk Actions</option>';
            if (isset($_GET['document_status']) && $_GET['document_status'] == "trash") {
                //R
                $html .=__('<option value="restore">Restore Again</option>','esign');
                $html .=__('<option value="del_permanent">Delete Permanently</option>','esign');
            } else {
                $html .=__('<option value="trash">Move to Trash</option>','esign');
            }

            $html .=' </select><input type="submit" name="esigndocsubmit" id="esig-action" class="button action" value="Apply"  />';
        }
        return $html;
    }

    public function esig_latest_version() {

        global $wp_version;

        if (!function_exists('WP_E_Sig'))
            return;

        $esig = WP_E_Sig();

        $api_params = array(
            'edd_action' => 'get_version',
            'license' => trim($esig->setting->get_generic('esig_wp_esignature_license_key')),
            'name' => 'WP E-Signature',
            'slug' => basename(ESIGN_PLUGIN_PATH, '/e-signature.php'),
            'author' => 'Approve Me'
        );

        $request = wp_remote_post('http://www.approveme.me/', array('timeout' => 15, 'sslverify' => false, 'body' => $api_params));

        if (!is_wp_error($request)):
            $request = json_decode(wp_remote_retrieve_body($request));
            if ($request)
                $request->sections = maybe_unserialize($request->sections);

            $esig->setting->set('esig_new_update_version', $request->new_version);

            return $request->new_version;
        else:
            return false;
        endif;
    }

    /*     * *
     *  report bug form 
     *  @Since 1.1.3
     */

    public function esig_report_bug_form() {

        $assets_dir = ESIGN_ASSETS_DIR_URI;

        $user = new WP_E_User();

        $admin_user = $user->getUserByWPID(get_current_user_id());
        if ($admin_user) {
            $first_name = $admin_user->first_name;
            $user_email = $admin_user->user_email;
        } else {
            $first_name = '';
            $user_email = '';
        }
        $newabout = new WP_E_aboutsController();
        $view = new WP_E_View();
        $systeminfo_data = $newabout->systeminfo(true);
        $sytem_template = ESIGN_PLUGIN_PATH . "/views/about/systeminfo.php";
        $system_result = $view->renderPartial('', $systeminfo_data, false, '', $sytem_template);
        // report bug form part 1
        //R
        $report_bug_html = '<div id="report_bug_loading" style="display:none;"><br><br>'.__('<h1>Loading...</h1>','esign').'</div>
                 <div id="report-bug-step1" class="esign-form-panel" style="display:none;">
			
				<span class="invitations-container">	

				<div align="center"><img src="' . $assets_dir . '/images/logo.png" width="200px" height="45px" alt="Sign Documents using WP E-Signature" width="100%" style="text-align:center;"></div>
					<h2 class="esign-form-header">' . __('What Would You Like To Do?', 'esig') . '</h2>

				</span>';
        $setting = new WP_E_Setting();
        $license_valid = $setting->get_generic('esig_wp_esignature_license_active');

        if ($license_valid == "valid") {

            //R
            $report_bug_html .= __('<p id="report-bug-radio-button" style="margin-left:35%;">
    				<input type="radio" name="esig_report_bug_type" value="bug"> Report a Bug</br>
                        <input type="radio" name="esig_report_bug_type" value="ticket"> Open a Support Ticket</br>
                        <input type="radio" name="esig_report_bug_type" value="idea"> Submit an Idea!</br>
    				</p>','esign');
            $report_bug_html .= '<p id="report_bug_button" align="center">
    					<a href="#" id="esig_report_bug_upload" class="button-primary esig-button-large">' . __('Next Step', 'esig') . '</a>	
    				</p></div> 
    				';
        } else {

            $report_bug_html .= '<p id="report-bug-radio-button">  ';
            if ($admin_user) {
                $report_bug_html .= $admin_user->first_name;
            }
            //R
            $report_bug_html .=__( ' it looks You do not have WP e-Signature valid license .  Please purchase a valid license first. <a href="http://www.approveme.me/#pricingPlans/" target="_blank">Buy Now</a></p></div>','esign');
        }


        // report bug step two start here 
        $report_bug_html .= '<div id="report-bug-step-bug" class="esign-form-panel" style="display:none;">
			
                     <form action="//approve.activehosted.com/proc.php" method="post" name="_form_281" id="_form_281" accept-charset="utf-8" enctype="multipart/form-data">
				
                     <input type="hidden" name="f" value="281">
                      <input type="hidden" name="s" value="">
                      <input type="hidden" name="c" value="0">
                      <input type="hidden" name="m" value="0">
                      <input type="hidden" name="act" value="sub">
                      <input type="hidden" name="nlbox[]" value="21">
                      <input type="hidden" name="fullname" placeholder="' . $first_name . '"  value="' . $first_name . '">
         	
          
                        <input type="hidden" name="email" placeholder="' . $user_email . '" value="' . $user_email . '">
                    
                    	
                        <textarea name="field[14]" rows="5" style="display:none;clear:both" cols="40" placeholder="System Info"> ' . str_replace('<br>', "\n", strip_tags($system_result, '<br>')) . ' </textarea>
                              
                        <span class="invitations-container">	
            
            				<div align="center"><img src="' . $assets_dir . '/images/logo.png" width="200px" height="45px" alt="Sign Documents using WP E-Signature" width="100%" style="text-align:center;"></div>
            					
            				</span>
                            
            				<p id="report-bug-form">
            					
           
                         <label>Plugin Name</label>
                         <select name="field[15]" style="width:500px;" tabindex="9" multiple  data-placeholder="Choose a Plugin name..." class="chosen-select">
                         <option value=""></option>';

        $array_Plugins = get_plugins();

        if (!empty($array_Plugins)) {
            foreach ($array_Plugins as $plugin_file => $plugin_data) {
                if (is_plugin_active($plugin_file)) {
                    $plugin_name = $plugin_data['Name'];

                    // if($plugin_name=="WP E-Signature")
                    // {  
                    if (preg_match("/WP E-Signature/", $plugin_name)) {
                        $report_bug_html .= '<option value="' . $plugin_name . '">' . $plugin_name . '</option>';
                    }
                    // }
                }
            }
        }



        $report_bug_html .= ' </select>   <br><br>
                    <label>Name of bug</label>
                    <input type="text" name="field[15]" value="" ><br>
                  	<label>Action (What action did you take?)</label>
                    <textarea name="field[16]" rows="5" cols="40"></textarea><br>
        			<label>Expectation: What did you expect?</label>
                    <textarea name="field[17]" rows="5" cols="40"></textarea><br>
        			<label>Result: What was the actual result?</label>
                    <textarea name="field[18]" rows="5" cols="40"></textarea><br>
        			<label>Outcome: What outcome do you want?</label>
                    <textarea name="field[19]" rows="5" cols="40"></textarea><br>
                    
                     
        				</p>
                        
        			 <label style="color:red;">
                        <input type="checkbox" name="field[20][]" value="I understand this is NOT a support ticket but rather a bug submission, therefore it will not be handled like a support ticket and i will not receive a response." >
                        I understand this is NOT a support ticket but rather a bug submission, therefore it will not be handled like a support ticket and i will not receive a response.
                      </label>
                      
                      <p id="report_bug_button" align="center">
        					<a href="#" id="esig_report_bug_submit" class="button-primary esig-button-large">' . __('Submit Form', 'esig') . '</a>	
        				</p>
                        </form>
        				</div> 
        				';
        //R
        $report_bug_html .= '<div id="report-bug-step-ticket" class="esign-form-panel" style="display:none;">
        			    
                            
                        <span class="invitations-container">	
        
        				<div align="center"><img src="' . $assets_dir . '/images/logo.png" width="200px" height="45px" alt="Sign Documents using WP E-Signature" width="100%" style="text-align:center;"></div>
        					
        				</span>
                        
        				<p id="report-bug-form">
        				
        				Hi ' . $first_name . ', so we can better serve you we ask that you login to your account and submit a support ticket at www.approveme.me/support 
        				
        				
        				<p align="center"><a target="_blank" href="https://www.approveme.me/wp-digital-e-signature-document-support" class="button-primary esig-button-large">'.__('Open a Ticket','eisgn').' </a></p><br><p align="center">'.__('<em>Bug requests will <strong>not</strong> be treated like a support ticket</em>','esign').'</p></div>';
        //R
        $report_bug_html .= '<div id="report-bug-step-idea" class="esign-form-panel" style="display:none;">
        			      <span class="invitations-container">	
        
        				<div align="center"><img src="' . $assets_dir . '/images/logo.png" width="200px" height="45px" alt="Sign Documents using WP E-Signature" width="100%" style="text-align:center;"></div>
        					
        				</span>
                        
        				<p id="report-bug-form">
                        
                        Hi ' . $first_name . ', we love user feedback! If you have an idea for WP E-Signature feel free to shout it out using our user voice page.<br>
                         </p><p align="center"><a target="_blank" href="http://approveme.uservoice.com/forums/243780-general" class="button-primary esig-button-large">'.__('Submit an Idea','esign' ).'</a></p>
        				</div> 
        				';

        echo $report_bug_html;
    }

    public function expired_popup() {

        if (!$this->settings->esig_license_expired()) {
            return false;
        }

        $license_key = $this->settings->get_generic('esig_wp_esignature_license_key');
        $html = ' <script type="text/javascript">
			      var e= jQuery.noConflict();
				  e(document).ready(function () { 
						e( "#esig-error-dialog" ).dialog({
								  dialogClass:"esig-dialog",
								  height: 500,
							      width: 600,
							      modal: true,
		   			 	}); 
		   			e("div#esig-error-dialog").on("dialogclose", function(event) {
     e("#esig-error-dialog").dialog("close");
 });   
				  }); 
			    </script>';

        $html .='<div id="esig-error-dialog"  class="esig-dialog-header" style="display:none">
				<div class="esig-alert">
				<span class="icon-esig-alert"></span>
				</div>';
        $license_check = get_transient('esig-license-check');
        if (empty($license_key)) {
            //R
            $html .='<p>'.__('To complicate the situation, it also looks like you do not have a license key (which means your site cannot communicate with ApproveMe and receive critical updates, downloads etc)... you will need to have a valid license in order to install this add-on.','esign').'</p>
			<p align="center"> <a href="admin.php?page=esign-licenses-general" id="esig-primary-dgr-btn">'.__('Put License Key','esig').'</a> </p>';
        } 
        elseif($license_check == "disabled"){
             $html .='<h3 align="center">'.__('Urgent: Expired or Refunded License Key','esign').'</h3>
		 		<p>'.__('WP E-signature requires a valid license for critical security updates and support.</p>  <p><strong>Your signers, website and documents could be at risk.</strong></p> <p>To avoid any issues for you or your signers please renew your license immediately.','esign').'</p>
		 		<p align="center"> <a href="https://www.approveme.me/checkout/?edd_license_key=' . $license_key . '&download_id=2660" id="esig-primary-dgr-btn">'.__('Purchase Again','esig').'</a> </p>';
        }
        else {
            //R
            $html .='<h3 align="center">'.__('Urgent: You Have an Expired License','esign').'</h3>
		 		<p>'.__('WP E-signature requires a valid license for critical security updates and support.</p>  <p><strong>Your signers, website and documents could be at risk.</strong></p> <p>To avoid any issues for you or your signers please renew your license immediately.','esign').'</p>
		 		<p align="center"> <a href="https://www.approveme.me/checkout/?edd_license_key=' . $license_key . '&download_id=2660" id="esig-primary-dgr-btn">'.__('Renew My License Key','esig').'</a> </p>';
        }
        $html .='</div>';

        return $html;
    }

    /*     * *
     *  Esign checking update
     *  @since 1.1.6
     * 
     * */

    public function esign_check_update() {
        if (!get_transient('esign-update-list')) {
            $addons = new WP_E_Addon();
            $update_list = $addons->esig_get_addons_update_list();
            set_transient('esign-update-list', $update_list, 60 * 60 * 12);
        }
    }

    /*     * *
     *  Making update list
     *  @since 1.1.6
     * 
     * */

    public function making_update_list() {

        $array_Plugins = get_plugins();

        $update_list = array();

        $plugin_info = array();

        if (!empty($array_Plugins)) {
            foreach ($array_Plugins as $plugin_file => $plugin_data) {
                if (is_plugin_active($plugin_file)) {
                    $plugin_name = $plugin_data['Name'];


                    if (preg_match("/WP E-Signature/", $plugin_name)) {

                        if ($plugin_name != "WP E-Signature") {
                            $this->item_plugshortname = str_replace("WP E-Signature - ", "", "$plugin_name");

                            $plugin_version = $plugin_data['Version'];
                            $plugin_info['item_name'] = $this->item_plugshortname;
                            $plugin_info['version'] = $plugin_version;
                            $update_list[] = $plugin_info;
                        }
                    }
                }
                // foreach end here
            }

            if (!get_transient('esign-local-update-list')) {
                set_transient('esign-local-update-list', json_encode($update_list), 60 * 60 * 12);
            }
        }
    }

    /**
     * Returns the timezone string for a site, even if it's set to a UTC offset
     *
     * Adapted from http://www.php.net/manual/en/function.timezone-name-from-abbr.php#89155
     *
     * @return string valid PHP timezone string
     */
    public function wp_get_timezone_string() {

        // if site timezone string exists, return it
        if ($timezone = get_option('timezone_string'))
            return $timezone;


        // get UTC offset, if it isn't set then return UTC
        if ($utc_offset = get_option('gmt_offset', 0))
            return $utc_offset;

        $utc_offset *= 3600;



        //print_r(timezone_abbreviations_list() );
        //exit ; 
        // last try, guess timezone string manually
        $is_dst = date('I');

        foreach (timezone_abbreviations_list() as $abbr) {

            foreach ($abbr as $city) {
                if ($city['offset'] == $utc_offset)
                    return $city['timezone_id'];
            }
        }

        // fallback to UTC
        return 'UTC';
    }

    public function esig_get_timezone() {

        if ($utc_offset = get_option('gmt_offset', 0))
            return $utc_offset;

        $timezone_string = $this->wp_get_timezone_string();

        try {

            $dt = new DateTime(null, new DateTimeZone($timezone_string));

            $offset = $dt->getOffset() / 3600; // 11

            if ($offset < 0) {
                return $offset;
            } else {
                return $offset;
            }
        } catch (Exception $e) {
            echo $e->getMessage() . '<br />';
        }
    }

    /**
     *  Esig set timezone 
     *  @Since 1.2.5
     */
    public function esig_set_timezone() {
        // submitted 
        if (count($_POST) > 0) {
            
            if (!empty($_POST['esig_timezone_string']) && preg_match('/^UTC[+-]/', $_POST['esig_timezone_string'])) {
                $_POST['esig_gmt_offset'] = $_POST['esig_timezone_string'];
                $_POST['esig_gmt_offset'] = preg_replace('/UTC\+?/', '', $_POST['esig_gmt_offset']);
                $_POST['esig_timezone_string'] = '';
                $this->settings->set_generic('esig_gmt_offset', $_POST['esig_gmt_offset']);
            }
            // saving timezone settings to database 
            $this->settings->set_generic('esig_timezone_string', $_POST['esig_timezone_string']);
            // saving offset 
        }

        $tzstring = $this->settings->get_generic('esig_timezone_string');

        $current_offset = $this->settings->get_generic('esig_gmt_offset');

        if (empty($tzstring)) { // Create a UTC+- zone if no timezone string exists
            $check_zone_info = false;
            if (0 == $current_offset)
                $tzstring = 'UTC+0';
            elseif ($current_offset < 0)
                $tzstring = 'UTC' . $current_offset;
            else
                $tzstring = 'UTC+' . $current_offset;
        }
        
        return $this->esig_timezone_choice($tzstring);
    }

    /**
     *  Set document timezone 
     *  @since 1.2.5
     *  @return void 
     */
    public function set_document_timezone($document_id) {
        $tzstring = $this->settings->get_generic('esig_timezone_string');

        $current_offset = $this->settings->get_generic('esig_gmt_offset');

        if (empty($tzstring)) { // Create a UTC+- zone if no timezone string exists
            $check_zone_info = false;
            if (0 == $current_offset)
                $tzstring = 'UTC+0';
            elseif ($current_offset < 0)
                $tzstring = 'UTC' . $current_offset;
            else
                $tzstring = 'UTC+' . $current_offset;
        }
        // save document timezone 
        //$this->settings->set('esig-timezone-document-'.$document_id,$tzstring);
        $meta = new WP_E_Meta();
        $meta->add($document_id, "esig-timezone-document", $tzstring);
    }

    /**
     * Gives a nicely-formatted list of timezone strings.
     *
     * @since 2.9.0
     *
     * @param string $selected_zone Selected timezone.
     * @return string
     */
    final function esig_timezone_choice($selected_zone) {
        static $mo_loaded = false;

        $continents = array('Africa', 'America', 'Antarctica', 'Arctic', 'Asia', 'Atlantic', 'Australia', 'Europe', 'Indian', 'Pacific');

        // Load translations for continents and cities
        if (!$mo_loaded) {
            $locale = get_locale();
            $mofile = WP_LANG_DIR . '/continents-cities-' . $locale . '.mo';
            load_textdomain('continents-cities', $mofile);
            $mo_loaded = true;
        }

        $zonen = array();
        foreach (timezone_identifiers_list() as $zone) {
            $zone = explode('/', $zone);
            if (!in_array($zone[0], $continents)) {
                continue;
            }

            // This determines what gets set and translated - we don't translate Etc/* strings here, they are done later
            $exists = array(
                0 => ( isset($zone[0]) && $zone[0] ),
                1 => ( isset($zone[1]) && $zone[1] ),
                2 => ( isset($zone[2]) && $zone[2] ),
            );
            $exists[3] = ( $exists[0] && 'Etc' !== $zone[0] );
            $exists[4] = ( $exists[1] && $exists[3] );
            $exists[5] = ( $exists[2] && $exists[3] );

            $zonen[] = array(
                'continent' => ( $exists[0] ? $zone[0] : '' ),
                'city' => ( $exists[1] ? $zone[1] : '' ),
                'subcity' => ( $exists[2] ? $zone[2] : '' ),
                't_continent' => ( $exists[3] ? translate(str_replace('_', ' ', $zone[0]), 'continents-cities') : '' ),
                't_city' => ( $exists[4] ? translate(str_replace('_', ' ', $zone[1]), 'continents-cities') : '' ),
                't_subcity' => ( $exists[5] ? translate(str_replace('_', ' ', $zone[2]), 'continents-cities') : '' )
            );
        }
        usort($zonen, '_wp_timezone_choice_usort_callback');

        $structure = array();

        if (empty($selected_zone)) {
            $structure[] = '<option selected="selected" value="">' . __('Select a city') . '</option>';
        }

        foreach ($zonen as $key => $zone) {
            // Build value in an array to join later
            $value = array($zone['continent']);

            if (empty($zone['city'])) {
                // It's at the continent level (generally won't happen)
                $display = $zone['t_continent'];
            } else {
                // It's inside a continent group
                // Continent optgroup
                if (!isset($zonen[$key - 1]) || $zonen[$key - 1]['continent'] !== $zone['continent']) {
                    $label = $zone['t_continent'];
                    $structure[] = '<optgroup label="' . esc_attr($label) . '">';
                }

                // Add the city to the value
                $value[] = $zone['city'];

                $display = $zone['t_city'];
                if (!empty($zone['subcity'])) {
                    // Add the subcity to the value
                    $value[] = $zone['subcity'];
                    $display .= ' - ' . $zone['t_subcity'];
                }
            }

            // Build the value
            $value = join('/', $value);
            $selected = '';
            if ($value === $selected_zone) {
                $selected = 'selected="selected" ';
            }
            $structure[] = '<option ' . $selected . 'value="' . esc_attr($value) . '">' . esc_html($display) . "</option>";

            // Close continent optgroup
            if (!empty($zone['city']) && (!isset($zonen[$key + 1]) || (isset($zonen[$key + 1]) && $zonen[$key + 1]['continent'] !== $zone['continent']) )) {
                $structure[] = '</optgroup>';
            }
        }



        return join("\n", $structure);
    }

}
