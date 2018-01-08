<?php
 
class FrmRegAppController{
    public static $min_version = '1.07.02';
    
    public static function load_hooks() {
		add_action( 'plugins_loaded', 'FrmRegAppController::load_lang' );
		add_action( 'after_plugin_row_formidable-registration/formidable-registration.php', 'FrmRegAppController::min_version_notice' );
		add_action( 'admin_init', 'FrmRegAppController::include_updater', 1 );
        //On activation
        register_activation_hook( dirname( __FILE__ ) .'/formidable-registration.php', array( __CLASS__, 'on_frmreg_activation' ) );
        
        // < 2.0 fallback
        add_action('init', array(__CLASS__, 'load_form_settings_hooks') );
        
        // 2.0 hooks
        add_action('frm_before_list_actions', array(__CLASS__, 'migrate_to_2'));
        add_action('frm_registered_form_actions', array(__CLASS__, 'register_actions') );
        add_action('frm_trigger_register_create_action', array(__CLASS__, 'create_user_trigger'), 10, 3);
        add_action('frm_trigger_register_update_action', array(__CLASS__, 'update_user_trigger'), 10, 3);

		// Shortcode builder
		add_filter('frm_popup_shortcodes', array(__CLASS__, 'add_login_shortcode'), 11);
		add_filter('frm_sc_popup_opts', array(__CLASS__, 'login_sc_opts'), 11, 2);
        
        add_action('wp_ajax_frm_add_usermeta_row', array(__CLASS__, '_usermeta_row'));
        add_filter('frm_filter_default_value', array(__CLASS__, 'get_default_value'), 20, 3);
        add_filter('frm_form_options_before_update', array(__CLASS__, 'update_options'), 15, 2);
        
        add_action('frm_entry_form', array(__CLASS__, 'hidden_form_fields'));
        add_filter('frm_validate_field_entry', array(__CLASS__, 'validate'), 20, 3);
        add_action('frm_after_create_entry', array(__CLASS__, 'create_user'), 30, 2);
        
        // User Moderation Functions
        add_filter( 'wp_authenticate_user', array( __CLASS__, 'prevent_pending_login'), 10, 2 );
        add_filter( 'allow_password_reset', array( __CLASS__, 'prevent_password_reset' ),  10, 2 );
        //add_action('frm_payment_paypal_ipn', array( __CLASS__, 'completed_payment' ),  10, 1 );
        add_filter( 'login_message', array( __CLASS__, 'print_login_messages' ), 20 );

        // Ajax Functions
        add_action( 'wp_ajax_resend_activation_link', array( __CLASS__, 'resend_activation_notification' ) );
        add_action( 'wp_ajax_nopriv_resend_activation_link', array( __CLASS__, 'resend_activation_notification' ) );
        add_action( 'wp_ajax_frm_activate_user', array( __CLASS__, 'activate_url' ) );
        add_action( 'wp_ajax_nopriv_frm_activate_user', array( __CLASS__, 'activate_url' ) );

        // Global Settings
        add_action( 'frm_add_settings_section', array( __CLASS__, 'add_settings_section' ) );
        add_filter( 'authenticate', array( __CLASS__, 'check_for_blank_login'),  19, 3 );
        add_action( 'wp_login_failed', array( __CLASS__, 'redirect_to_login_page') );
        //add_filter( 'lostpassword_url', array( __CLASS__, 'set_lostpassword_page' ) );
        add_filter('the_content', array(__CLASS__, 'print_activation_messages'), 21 );

		add_action( 'frm_after_update_entry', 'FrmRegAppController::update_user', 25, 2 );
        add_filter('frm_setup_edit_fields_vars', array(__CLASS__, 'check_updated_user_meta'), 10, 3); 
        
        add_action('show_user_profile', array(__CLASS__, 'show_usermeta'), 200);
        add_action('edit_user_profile', array(__CLASS__, 'show_usermeta'), 200);

        add_action('widgets_init', array(__CLASS__, 'register_widgets') );
        add_filter( 'widget_text', array(__CLASS__, 'widget_text_filter'), 9 );
        
        add_filter('get_avatar', array(__CLASS__, 'get_avatar'), 10, 5 );

        add_filter( 'login_redirect', array(__CLASS__, 'control_login_redirect'), 10, 3 );

        //Add shortcodes
        add_shortcode('frm-login', array(__CLASS__, 'login_form'));
    }
    
    public static function load_lang(){
        load_plugin_textdomain('frmreg', false, 'formidable-registration/languages/' );
    }
    
    public static function min_version_notice(){
        if ( is_callable('FrmAppHelper::min_version_notice') ) {
            FrmAppHelper::min_version_notice(self::$min_version);
            return;
        }
        
        // <= v1.07.09 fallback
        $frm_version = is_callable('FrmAppHelper::plugin_version') ? FrmAppHelper::plugin_version() : 0;
        
        // check if Formidable meets minimum requirements
        if ( version_compare($frm_version, self::$min_version, '>=') ) {
            return;
        }
        
        $wp_list_table = _get_list_table('WP_Plugins_List_Table');
		echo '<tr class="plugin-update-tr active"><th colspan="' . absint( $wp_list_table->get_column_count() ) . '" class="check-column plugin-update colspanchange"><div class="update-message">' .
		__( 'You are running an outdated version of Formidable. This plugin may not work correctly if you do not update Formidable.', 'frmreg' ) .
        '</div></td></tr>';
    }
    
    public static function include_updater(){
		if ( class_exists( 'FrmAddon' ) ) {
			FrmRegUpdate::load_hooks();
		}
    }
    
    /*
    * Add tab in Global Settings
    *
    * Since 1.11
    *
    * @return sections array
    */
    public static function add_settings_section( $sections ) {
        $sections['registration'] = array('class' => 'FrmRegAppController', 'function' => 'route');
        return $sections;
    }

    public static function load_form_settings_hooks() {
        if ( FrmRegAppHelper::is_below_2() ) {
            // load hooks for < v2.0
            add_filter('frm_add_form_settings_section', array(__CLASS__, 'add_registration_options'));
            add_filter('frm_setup_new_form_vars', array(__CLASS__, 'setup_new_vars'));
            add_filter('frm_setup_edit_form_vars', array(__CLASS__, 'setup_edit_vars'));
        }
    }

    /*
    * This function fires whenever add-on is activated
    */
    public static function on_frmreg_activation(){
        //Add new role
        add_role( 'pending', 'Pending', array() );
    }

    public static function register_actions($actions) {
        $actions['register'] = 'FrmRegAction';
        
        return $actions;
    }
    
    public static function add_registration_options($sections){
        $sections['registration'] = array('class' => 'FrmRegAppController', 'function' => 'registration_options');
        return $sections;
    }
    
    public static function registration_options($values){
        if(!class_exists('FrmProFieldsHelper'))
            return;
        
        global $wpdb;
        if ( isset($values['id']) ) {
            $fields = FrmField::getAll($wpdb->prepare("fi.form_id=%d and fi.type not in ('divider', 'html', 'break', 'captcha', 'rte')", $values['id']), ' ORDER BY field_order');
        }
        $echo = true;
        
        include(FrmRegAppHelper::path() .'/views/registration_options.php');
    }
    
    public static function _usermeta_row($meta_name=false, $field_id=''){
        if ( ! $meta_name && isset($_POST['meta_name']) ) {
            $meta_name = $_POST['meta_name'];
        }
        
        if ( isset( $_POST['form_id'] ) ) {
            global $wpdb;
			$fields = FrmField::getAll( $wpdb->prepare( 'fi.form_id=%d', absint( $_POST['form_id'] ) ) . " and fi.type not in ('divider', 'html', 'break', 'captcha')", ' ORDER BY field_order' );
        }

        $echo = false;

        if ( FrmRegAppHelper::is_below_2() ) {
            include(FrmRegAppHelper::path() .'/views/_usermeta_row.php');
        } else {
            $action_control = FrmFormActionsController::get_form_actions( 'register' );
            // Set action ID
            $action_control->_set( sanitize_title( $_POST['action_key'] ) );
            $meta_key = $meta_name;
            include(FrmRegAppHelper::path() .'/views/new_usermeta_row.php');
        }
        die();
    }
    
    // dont select a user in the user ID field when creating from the back-end
    public static function get_default_value($value, $field, $dynamic_default = true) {
        if ( ! $dynamic_default && $field->type == 'user_id' && is_admin() && !defined('DOING_AJAX') && current_user_can('frm_edit_entries') && $_GET['page'] != 'formidable' && FrmProFieldsHelper::field_on_current_page($field) ) {
            
            if ( is_callable('FrmFormActionsHelper::get_action_for_form') ) {
                $actions = FrmFormActionsHelper::get_action_for_form($field->form_id, 'register');
                
                if ( !empty($actions) ) {
                    return '';
                }
            }
            
            // < 2.0 fallback - check the form options
            $form = FrmForm::getOne($field->form_id);
            if ( isset($form->options['registration']) && $form->options['registration'] ) {
                $action_id = self::migrate_to_2($form);
                $value = '';
            }
            
        }
        
        return $value;
    }
    
    public static function setup_new_vars($values){
        $defaults = FrmRegAppHelper::get_default_options();
        foreach ($defaults as $opt => $default){
            $values[$opt] = FrmAppHelper::get_param($opt, $default);
			unset( $default, $opt );
        }
        return $values;
    }
    
    public static function setup_edit_vars($values){   
        $defaults = FrmRegAppHelper::get_default_options();
        foreach ($defaults as $opt => $default){
			if ( ! isset( $values[ $opt ] ) ) {
                $values[$opt] = ($_POST and isset($_POST['options'][$opt])) ? $_POST['options'][$opt] : $default;
			}
            unset( $default, $opt );
        }
        
        return $values;
    }
    
    public static function update_options($options, $values){
        $register = false;
        $avatar_id = '';
        
        // < 2.0 fallback
        if ( isset($values['options']['reg_email']) ) {
            $defaults = FrmRegAppHelper::get_default_options();
            unset($defaults['reg_usermeta']);
            
            foreach($defaults as $opt => $default){
                $options[$opt] = (isset($values['options'][$opt])) ? $values['options'][$opt] : $default;
				unset( $default, $opt );
            }

            $options['reg_usermeta'] = array();
            if(isset($values['options']['reg_usermeta']) and isset($values['options']['reg_usermeta']['meta_name'])){
                foreach($values['options']['reg_usermeta']['meta_name'] as $meta_key => $meta_value){
                    if(!empty($meta_value) and !empty($values['options']['reg_usermeta']['field_id'][$meta_key]))
                        $options['reg_usermeta'][$meta_value] = $values['options']['reg_usermeta']['field_id'][$meta_key];
                }
            }

            unset($defaults);
            
            if ( $options['registration'] ) {
                $register = true;
            }
            
            $avatar_id = isset($values['options']['reg_avatar']) ? (int) $values['options']['reg_avatar'] : '';

        // For 2.0+
        } else if ( isset($values['frm_register_action']) ) {
            $skip = true;
            foreach ( $values['frm_register_action'] as $action_id => $settings ) {
                if ( isset($settings['post_content']) ) {
                    $register = true;
                    $skip = false;
                    $avatar_id = (int) $settings['post_content']['reg_avatar'];
                }
            }
            
            if ( $skip ) {
                // registration settings were never loaded on the page
                return $options;
            }
        }

        // save avatar field id to site option
        global $wpdb;
        $avatar = (array) get_option('frm_avatar');
        
        if ( ( !empty($avatar_id) && !in_array($avatar_id, $avatar) ) || ( empty($avatar_id) && in_array($avatar_id, $avatar) ) ) {
            if ( empty($avatar_id) ) {
                $pos = array_search($avatar_id, $avatar);
                unset($avatar[$pos]);
            } else {
                $avatar[] = $avatar_id;
            }
            update_option('frm_avatar', $avatar);
            
            // reset avatars
            $wpdb->query("DELETE FROM $wpdb->usermeta WHERE meta_key='frm_avatar_id'");
        }
        
        if ( ! $register ) {
            return $options;
        }
        
        //Make sure the form includes a User ID field for correct editing
        $form_id = $values['id'];
        $user_field = FrmField::getAll(array('fi.form_id' => $form_id, 'type' => 'user_id'));
        if ( ! $user_field ) {
            $new_values = FrmFieldsHelper::setup_new_vars('user_id', $form_id);
            $new_values['name'] = __('User ID', 'frmreg');
            FrmField::create($new_values);
            unset($new_values);
        }
        unset($user_field);

        return $options;
    }
    
    public static function hidden_form_fields($form){
        $settings = FrmRegAppHelper::get_registration_settings($form);
        if ( empty($settings) ) {
            return;
        }
        
        if ( isset($settings['reg_username']) && $settings['reg_username'] ) {
            echo '<input type="hidden" name="frm_register[username]" value="'. esc_attr($settings['reg_username']) .'"/>'."\n";
        }
        
        echo '<input type="hidden" name="frm_register[email]" value="'. esc_attr($settings['reg_email']) .'"/>'."\n";
    }
    
	public static function validate( $errors, $field, $value ){
		if ( ! isset ( $_POST['frm_register'] ) ) {
			return $errors;
		}

		$update = ( ( isset($_POST['frm_action'] ) && $_POST['frm_action'] == 'update') || ( isset($_POST['action'] ) && $_POST['action'] == 'update') );

		FrmRegValidate::validate_user_data_fields( $field, $value, $update, $errors );

		return $errors;
    }
    
    public static function create_user_trigger($action, $entry, $form) {
        if ( ! isset($action->v2) ) {
            // 2.0 fallback - prevent extra processing
            remove_action('frm_after_create_entry', array(__CLASS__, 'create_user'), 30, 2);
        }
        
        $settings = $action->post_content;
        
        $user_ID = get_current_user_id();
        if ( $user_ID ) {
            $required_role = apply_filters('frmreg_required_role', 'create_users');
            if ( ( is_admin() && !defined('DOING_AJAX') ) || current_user_can($required_role) ) {
                //don't require the user to edit their own record
                
                $e = (isset($settings['reg_email']) && !empty($settings['reg_email']) && isset($_POST['item_meta'][$settings['reg_email']])) ? $_POST['item_meta'][$settings['reg_email']] : false;
                if ( $user_ID == $entry->user_id || ($e && email_exists($e)) ) {
                    //allow admin users to update their profile when creating an entry
                    self::update_user($entry, $form);
                    return;
                }
            } else {
                //if user is already logged-in, then update the user
                self::update_user($entry, $form);
                return;
            }
            unset($required_role);
        }
        
        if ( ! isset($settings['reg_email']) || ! isset($_POST['item_meta'][$settings['reg_email']]) ) {
            return;
        }
        
        $user_meta = self::_get_usermeta($settings);

        // If email isn't set, don't try to register user
        if ( ! isset( $user_meta['user_email'] ) ) {
            // TODO: Print error message
            return;
        }

        if ( !isset($user_meta['user_pass']) || empty($user_meta['user_pass']) ){
            $user_meta['user_pass'] = wp_generate_password( 12, false );
        }
        
        if ( empty($settings['reg_username']) ) {
            //if the username will be generated from the email
            $parts = explode("@", $user_meta['user_email']);
            $user_meta['user_login'] = $parts[0];
        } else if ( $settings['reg_username'] == '-1' ) {    
            //if the username will be generated from the full email
            $user_meta['user_login'] = $user_meta['user_email'];
        } else {
            $user_meta['user_login'] = $_POST['item_meta'][$settings['reg_username']];
        }
        $user_meta['user_login'] = FrmRegAppHelper::generate_unique_username($user_meta['user_login']);
        
        // Generate display name
        self::_generate_display_name( $user_meta, $settings );

        $new_role = isset($settings['reg_role']) ? $settings['reg_role'] : 'subscriber';
        $user_meta['role'] = apply_filters('frmreg_new_role', $new_role, array('form' => $form));
        unset($new_role);
        
        if ( !function_exists('username_exists') ) {
            require_once(ABSPATH . WPINC . '/registration.php');
        }
            
        $user_meta = apply_filters('frmreg_user_data', $user_meta, array('action' => 'create', 'form' => $form));
        
        $user_id = wp_insert_user($user_meta);
        if ( is_wp_error($user_id) ) {
            wp_die($user_id->get_error_message());
            return;
        }
        
        $user_id = (int) $user_id;
        if ( ! $user_id ) {
            // don't continue if there was no user created
            return;
        }

		FrmRegEntry::update_user_id_for_entry( $form->id, $entry->id, $user_id );

		$_POST['frm_user_id'] = $user_id;
        
        //remove password from database
        if ( isset($settings['reg_password']) && !empty($settings['reg_password']) ) {
            FrmEntryMeta::delete_entry_meta($entry->id, (int) $settings['reg_password']);
        }
        
        //Update usermeta
        self::update_usermeta($settings, $user_id);
        
        //Check if user needs to be moderated
        $moderate = FrmRegAppHelper::moderate_user( $user_id, $user_meta['user_pass'], $settings['reg_moderate'], array( 'future_role' => $settings['reg_role'], 'redirect' => $settings['reg_redirect'], 'entry_id' => $entry->id ) );

        if ( true == $moderate ) {
            return;
        }

        // send new user notifications
        wp_new_user_notification($user_id, ''); // sending a blank password only sends notification to admin
        FrmRegNotification::new_user_notification($user_id, $user_meta['user_pass'], $form, $entry->id, $settings);

        //log user in
        if ( !isset($settings['login']) || $settings['login'] ) {
            self::auto_login($user_meta['user_login'], $user_meta['user_pass']);
        }
    }
    
    /*----------------User Moderation Functions--------------------*/

	/*
    * Prevent "pending" users from logging in
    */
	public static function prevent_pending_login( $user, $password ) {
        //If user has "Pending" role, don't let them in
		if ( in_array( 'pending', (array) $user->roles ) ) {
            $moderate_type = (array) get_user_meta ( $user->ID, 'frmreg_moderate', 1 );

            if ( in_array( 'email', $moderate_type ) ) {
                $resend_link = FrmRegAppHelper::create_ajax_url( array('action' => 'resend_activation_link', 'user_id' => $user->ID ) );
                $params = array( 'frm_message' => 'resend_activation', 'user' => $user->ID );
                self::redirect_to_login_page( $user->user_login, $params );//Call this "do_action" instead?
				return new WP_Error( 'pending', sprintf( __( '<strong>ERROR</strong>: You have not confirmed your e-mail address. %1$sResend activation%2$s?', 'frmreg' ), '<a href="' . esc_url( $resend_link ) . '">', '</a>' ) );
            } else if ( in_array( 'admin', $moderate_type ) ) {
                return new WP_Error( 'pending', __( '<strong>ERROR</strong>: Your registration has not yet been approved.', 'frmreg' ) );
            } else if ( in_array( 'paypal', $moderate_type ) ) {
                return new WP_Error( 'pending', __( '<strong>ERROR</strong>: Your payment has not been completed. You must complete your payment before you may log in.', 'frmreg' ) );
            }
		}
		return $user;
	}

	/*
    * Prevent "pending" users from resetting their password
    */
	public static function prevent_password_reset( $allow, $user_id ) {
		$user = get_user_by( 'id', $user_id );

        if ( in_array( 'pending', (array) $user->roles ) ) {
			return false;
        }
		return $allow;
	}

    /*
    * Checks if user can be activated when activation URL is clicked
    */
	public static function activate_url() {
        //If user is logged-in already, don't try to activate them
        if ( is_user_logged_in() ) {
            $form_settings = FrmRegAppHelper::get_form_settings( get_user_by( 'login', $_GET['login'] ) );
            if ( $form_settings['reg_redirect'] ) {
                $redirect = get_permalink( $form_settings['reg_redirect'] );
            } else {
                $redirect = wp_login_url();
            }
			wp_redirect( esc_url_raw( $redirect ) );
            exit();
        }

        if ( ! isset( $_GET['key'] ) || empty( $_GET['key'] ) || ! is_string( $_GET['key'] ) || !isset( $_GET['login'] ) || empty( $_GET['login'] ) || ! is_string( $_GET['login'] ) ) {
            // Set error parameters
            $params = array( 'frm_message' => 'invalid_key' );

            //Redirect user now
			wp_redirect( esc_url_raw( FrmRegAppHelper::get_login_url( $params ) ) );
            exit();

        } else {
            //Validate key and see if user can be activated
            FrmRegAppHelper::validate_activation_link( $_GET['key'], $_GET['login'] );
        }
        die();
	}

	/**
	* Print activation messages
	*
	* @since 2.11
	*/
	public static function print_activation_messages( $content ) {
		// If frm_message is not set, stop now
        if ( ! isset( $_GET['frm_message'] ) ) {
            return $content;
        }

        // Print activation messages for fully activated user
        if ( $_GET['frm_message'] == 'complete' && isset( $_GET['user'] ) && is_numeric( $_GET['user'] ) ) {
            // Check whether the user has set their own password or if it's auto-generated
            $user = new WP_User( $_GET['user'] );

            $settings = FrmRegAppHelper::get_form_settings( $user->ID );
            if ( $settings === false ) {
                return;
            }

            // If user is automatically logged in
            if ( isset( $_GET['logged_in'] ) && $_GET['logged_in'] ) {
                $message =  __( 'Your account is now active. Enjoy!', 'frmreg' );

            // If user set their own password
            } else if ( isset ( $settings['reg_password'] ) && $settings['reg_password'] ) {
                $message =  __( 'Your account has been activated. You may now log in.', 'frmreg' );

            // If password is automatically generated
            } else {
                $message =  __( 'Your account has been activated. Please check your e-mail for your password.', 'frmreg' );
            }

			$message = apply_filters( 'frmreg_activation_success_msg', $message );

            if ( isset( $message ) ) {
                $class = 'frm_message';
                $style_class = 'with_frm_style';
                if ( is_callable('FrmStylesController::get_form_style_class') ) {
                    $style_class = FrmStylesController::get_form_style_class($style_class, 1);
                }
				$content = '<div class="' . esc_attr( $style_class ) . '"><div class="' . esc_attr( $class ) . '">' . $message . '</div></div>' . $content;
            }
        }

        return $content;
    }

    /*
    * Check if user needs to be activated after payment is completed
    */
    function completed_payment( $args ) {
        if ( !$args['pay_vars']['completed'] ) {
            return; //don't continue if the payment was not completed
        }

        if ( !$args['entry']->user_id || !is_numeric( $args['entry']->user_id ) ) {
            return; //don't continue if not linked to a user
        }

        $user_id = $args['entry']->user_id;

        //Get user
        $user = new WP_User( $user_id );
        if ( !$user ) {
            return; //don't continue if user doesn't exist
        }

        //Get user's current role
        $current_role = $user->roles;

        //Check if user has pending role
        if ( in_array( 'pending', $current_role ) ) {
            //Check if paypal moderation is necessary
            $moderate = (array)get_user_meta( $user_id, 'frmreg_moderate', 1);
            FrmRegAppHelper::maybe_activate_user( 'paypal', $moderate, $user->ID );
        } else {
            return;
        }
    }

    /*----------------Global Settings Functions--------------------*/

    /*
    * Route to different function depending on the URL
    */
    public static function route(){
        $action = isset($_REQUEST['frm_action']) ? 'frm_action' : 'action';
        $action = FrmAppHelper::get_param($action);
        if ( $action == 'process-form' ) {
            return self::process_form();
        } else {
            return self::display_form();
        }
    }

    /*
    * Display form on Global Settings page
    */
    public static function display_form( $errors=array(), $message='' ){
        $frm_reg_settings = new FrmRegSettings();

        require( dirname( __FILE__ ) .'/views/global_reg_settings.php');
    }

    /*
    * Process form on Global Settings page
    */
    public static function process_form(){
        $frm_reg_settings = new FrmRegSettings();

        $errors = array();
        $frm_reg_settings->update($_POST);

        if( empty($errors) ){
            $frm_reg_settings->store();
			$message = __( 'Settings Saved', 'frmreg' );
        }

        self::display_form($errors, $message);
    }

    /**
    * Check for a blank username or password
    * Also redirects when logged-out user visits wp-admin or wp-login.php page
    *
    * @since 1.11
    *
    * @param WP_User|WP_Error|null $user     WP_User or WP_Error object from a previous callback. Default null.
    * @param string                $username Username for authentication.
    * @param string                $password Password for authentication.
    * @return WP_User|WP_Error WP_User on success, WP_Error on failure.
    */
    public static function check_for_blank_login($user, $username, $password) {
        // Return if logging in from pop-up on back-end
        if ( isset( $_GET['interim-login'] ) ) {
            return $user;
        }

        // Return if global login page is NOT selected
        $global_login_page_id = FrmRegAppHelper::global_login_page_id();
        if ( !$global_login_page_id ) {
            return $user;
        }

        // Declare $params array
        $params = array();

        // Redirect after login if necessary
        if ( isset( $_GET['redirect_to'] ) ) {
            $params['redirect_to'] = $_GET['redirect_to'];
        } else if ( isset( $_SERVER['HTTP_REFERER'] ) && strpos( $_SERVER['HTTP_REFERER'], 'redirect_to' ) !== false ) {
            // Add redirect_to parameter to URL
            $params['redirect_to'] = FrmRegAppHelper::get_redirect_to( $_SERVER['HTTP_REFERER'] );
        }

        // Only redirect if username, password, or both are blank. Otherwise, validation will be handled by a different function
        $redirect = false;

        // If both fields are empty, don't print error
        if ( empty( $username ) && empty( $password ) ) {
            $params['frm_message'] = 'login';
            $redirect = true;
        } else if ( empty( $username ) ) {
            $params['frm_message'] = 'blank_login';
            $redirect = true;
        } else if ( empty( $password ) ) {
            $params['frm_message'] = 'blank_pass';
            $redirect = true;
        }

        if ( $redirect ) {
            self::redirect_to_login_page('', $params);
        }
        return $user;
	}

    /*
    * Redirect to custom login page
    * Only redirects if custom login page is set in global settings
    *
    * @since 1.11
    *
    * @param string $username
    * @param array $params
    */
    public static function redirect_to_login_page( $username = '', $params = array() ) {
        // Return if logging in from pop-up on back-end
        if ( isset( $_POST['interim-login'] ) ) {
            return;
        }

        if ( !isset( $params['frm_message'] ) ) {
            $params['frm_message'] = 'invalid_login';
            $params['redirect_to'] = FrmRegAppHelper::get_redirect_to( $_SERVER['HTTP_REFERER'] );
        }

        // If a global login page is set, then redirect to it now
        $global_login_page_id = FrmRegAppHelper::global_login_page_id();
        if ( $global_login_page_id ) {
            //Redirect to login page in settings and add parameters for specific messages
            $redirect = add_query_arg( $params, get_permalink( $global_login_page_id ) );
			wp_redirect( esc_url_raw( $redirect ) );
            exit();
        }
    }

    /*
    * Set custom Lost Password URL
    *
    * Since 1.11
    *
    */
    public static function set_lostpassword_page( $lostpassword_url, $redirect = '' ){
        $frm_reg_settings = new FrmRegSettings();
        $settings = $frm_reg_settings->get_options();
        if ( isset( $settings->lostpass ) && $settings->lostpass ) {
            $lostpassword_url = get_permalink( $settings->lostpass );

            // If redirect is set
        	if ( !empty($redirect) ) {
        		$lostpassword_url = add_query_arg('redirect_to', urlencode($redirect), $lostpassword_url);
            }
        }
		return esc_url_raw( $lostpassword_url );
    }

    public static function create_user($entry_id, $form_id){ //TODO: add wp_noonce
        if ( !isset($_POST['frm_register']) ) {
            return;
        }

        $form = FrmForm::getOne($form_id);
        
        if ( ! isset($form->options['registration']) || ! $form->options['registration'] ) {
            return;
        }
        
        $entry = FrmEntry::getOne($entry_id);
        if ( ! $entry ) {
            return;
        }
        
        $action = array( 'post_content' => $form->options, 'v2' => false );
        
        self::create_user_trigger( (object) $action, $entry, $form );
    }
    
    /*----------------Email Functions--------------------*/

    public static function new_user_notification( $user_id, $plaintext_pass, $form, $entry_id, $settings = false ) {
        _deprecated_function( __FUNCTION__, '1.11.02', 'FrmRegNotification::new_user_notification');
		FrmRegNotification::new_user_notification( $user_id, $plaintext_pass, $form, $entry_id, $settings );
	}
	
	// This function is triggered from payment plugins
	public static function send_paid_user_notification($entry) {
        _deprecated_function( __FUNCTION__, '1.11.02', 'FrmRegNotification::send_paid_user_notification');
        FrmRegNotification::send_paid_user_notification($entry);
	}

    /*
    * Stop Formidable Emails
    */
    public static function stop_the_email( $emails, $values, $form_id, $args = array() ) {
        //TODO: Accommodate for resending notifications

        $emails = array();
        return $emails;
    }

    /*
    * Resend activation link to pending user
    */
	public static function resend_activation_notification() {
        if ( isset( $_GET['user_id'] ) && is_numeric( $_GET['user_id'] ) ) {
            $user_id = $_GET['user_id'];

            // Get user object
    		$user = new WP_User( $user_id );

    		// Send activation e-mail
    		FrmRegNotification::new_user_activation_notification( $user_id, '' );

            // Get login URL
            $login_url = FrmRegAppHelper::get_login_url( array( 'frm_message' => 'activation_sent' ) );

            //Redirect user
			wp_redirect( esc_url_raw( $login_url ) );
            exit();
        }
        die();
    }

    /*
    * Print success message on standard wp-login page when activation link is resent
    * This function will only apply when users do not select a global login/logout page in their global settings
    *
    * Since 1.11
    *
    */
    public static function print_login_messages( $message ) {
        if ( !isset( $_GET['frm_message'] ) || !$_GET['frm_message'] ) {
            return $message;
        }
        if ( $_GET['frm_message'] == 'activation_sent' ) {
            $message = '<p class="message">' . __( 'The activation e-mail has been sent to the e-mail address with which you registered. Please check your email and click on the link provided.', 'frmreg' ) . '</p>';
        } else if ( $_GET['frm_message'] == 'invalid_key' ) {
             $message =  '<div id="login_error">' . __( 'That activation link is invalid.', 'frmreg' ) . '</div>';
        }
        return $message;
    }

	public static function auto_login( $log, $pwd ) {
        if ( is_user_logged_in() ) {
			return;
        }
        
        $_POST['log'] = $log;
        $_POST['pwd'] = $pwd;

		wp_signon();
    }
    
    public static function signon(){
		if ( is_user_logged_in() ) {
            return;
		}

        wp_signon();
	}
    
	public static function update_user_trigger( $action, $entry, $form, $version = 2 ) {
		if ( $version == 2 ) {
			// don't trigger this action to old way
			remove_action( 'frm_after_update_entry', 'FrmRegAppController::update_user', 25, 2 );
		}

        global $user_ID;
		$posted_id = isset( $_POST['frm_user_id'] ) ? absint( $_POST['frm_user_id'] ) : $user_ID;

        $settings = $action->post_content;
        
        if ( $posted_id ) {
            $user_obj = get_userdata( $posted_id );
            $user_meta = $user_obj->to_array();
            if ( function_exists('_get_additional_user_keys') ) {
                foreach ( _get_additional_user_keys( $user_obj ) as $key ) {
                    $user_meta[$key] = get_user_meta( (int)$posted_id, $key, true );
                    unset($key);
                }
            } else {
                //set profile checkboxes to current values
                $profile_meta = array(
                    'rich_editing', 'admin_color', 'show_admin_bar_front',
                    'use_ssl', 'first_name', 'last_name',
                );
                
                foreach ( $profile_meta as $m ) {
                    if ( !isset($user_meta[$m]) ) {
                        $user_meta[$m] = get_user_meta($user_meta['ID'], $m, true);
                    }
                    unset($m);
                }
            }
        } else {
            $user_meta = array();
        }
        
        if ( isset($user_meta['user_pass']) ) {
            unset($user_meta['user_pass']);
        }
        
        $user_meta = self::_get_usermeta($settings, $user_meta);
        
        if ( $posted_id ) {
            $required_role = apply_filters('frmreg_required_role', 'edit_users');
            if ( $user_ID && ($posted_id != $user_ID) ) {
                if ( (is_admin() && !defined('DOING_AJAX')) || current_user_can($required_role) ) {
                    //allow editing if allowed or editing from the admin
                } else {
                    return; //make sure this record is updated by the owner or from the admin
                }
            }
            $user_meta['ID'] = $posted_id;
            unset($required_role);
        }
        
        if ( empty($settings['reg_username']) ) {
            $user_data = get_userdata($posted_id);
            $user_meta['user_login'] = $user_data->user_login;
        } else if ( $settings['reg_username'] == '-1' && isset($user_meta['user_email']) ) {
            $user_meta['user_login'] = $user_meta['user_email'];
        } else {
            $user_meta['user_login'] = $_POST['item_meta'][$settings['reg_username']];
        }

        //If username is empty, fetch current username
        if ( empty( $user_meta['user_login'] ) ) {
            $user_meta['user_login'] = $user_obj->data->user_login;
        }

        // Get display name
        self::_generate_display_name( $user_meta, $settings );
           
        if ( !function_exists('username_exists') ) {
            require_once(ABSPATH . WPINC . '/registration.php');
        }

        $user_meta = apply_filters('frmreg_user_data', $user_meta, array('action' => 'update', 'form' => $form));
        
        if ( ! $user_meta ) {
            return;
        }

        if ( $user_meta['ID'] ) {
            $user_id = wp_update_user($user_meta);
        } else {
            $user_id = wp_insert_user($user_meta);
        }

        if ( !$user_id || !is_numeric($user_id) ) {
            return;
        }

        self::update_usermeta($settings, $user_id);
        
        // check if password was changed
        if ( ! isset($user_meta['user_pass']) ) {
            return;
        }

        //remove password from database
        if ( isset($settings['reg_password']) && ! empty($settings['reg_password']) ) {
            FrmEntryMeta::delete_entry_meta($entry->id, (int) $settings['reg_password']);
        }
    }
    
    public static function update_user($entry_id, $form_id){
        if ( is_object($entry_id) ) {
            $entry = $entry_id;
            $entry_id = $entry->id;
        } else {
            $entry_id = (int) $entry_id;
            $entry = FrmEntry::getOne($entry_id);
        }

        if ( ! $entry ) {
            return;
        }

        if ( is_object( $form_id ) ) {
            $form = $form_id;
            $form_id = $form->id;
        } else {
            $form_id = (int) $form_id;
            $form = FrmForm::getOne($form_id);
        }

        if ( ! $form ) {
            return;
        }

        // Get actions for form if running 2.0+
        if ( is_callable( 'FrmFormActionsHelper::get_action_for_form' ) ) {
            $action = FrmFormActionsHelper::get_action_for_form( $form_id, 'register', 1 );

        } else if ( isset($form->options['registration']) && $form->options['registration'] ){
            $action = array( 'post_content' => $form->options );
        } else {
            $action = array();
        }

        if ( empty( $action ) ) {
            return;
        }

		self::update_user_trigger( (object) $action, $entry, $form, 1 );
    }
    
    /*
    * Fetches the current values from the user profile
    * TODO: Make sure this works with 2.0
    */
    public static function check_updated_user_meta($values, $field, $entry_id=false){
        if ( is_admin() && !defined('DOING_AJAX') && isset($_GET['page']) && 'formidable' == $_GET['page'] ) {
            //make sure this doesn't change settings
            return $values;
        }
        
        global $user_ID;
        
        if ( in_array($field->type, array('data', 'checkbox')) ) {
            return $values;
        }
        
        $settings = FrmRegAppHelper::get_registration_settings($field->form_id);
        if ( ! $settings ) {
            return $values;
        }

		$settings['reg_usermeta'] = FrmRegSettings::format_usermeta_settings( $settings );

		$user_meta = FrmRegSettings::get_usermeta_key_for_field( $field->id, $settings['reg_usermeta'] );
        if ( empty($user_meta) ) {
            return $values;
        }

		// If there is no user ID attached to this entry, do not update the fields automatically
		if ( ! FrmRegAppHelper::get_user_for_entry( $entry_id ) ) {
			return $values;
		}

        $entry = FrmEntry::getOne($entry_id);
        if ( ! $entry || ! $entry->user_id ) {
            return $values;
        }

        $user_data = get_userdata($entry->user_id);
        if ( !isset($_POST['form_id']) && !isset($_POST['item_meta']) && !isset($_POST['item_meta'][$field->id]) ) {
            $new_value = isset($user_data->{$user_meta}) ? $user_data->{$user_meta} : get_user_meta($user_ID, $user_meta);
        
            if ( $new_value ) {
                $values['value'] = $new_value;
            }
            unset($new_value);
        }
        
        return $values;
    }

    /*
    * Show usermeta on profile page
    */
    public static function show_usermeta(){
        global $profileuser, $wpdb;

        $meta_keys = array();

        //If running version prior to Formidable 2.0
        if ( FrmRegAppHelper::is_below_2() ) {
            $form_options = $wpdb->get_col("SELECT options FROM {$wpdb->prefix}frm_forms WHERE is_template=0 AND status='published'");

            foreach($form_options as $opts){
                $opts = maybe_unserialize($opts);
                if ( !isset($opts['reg_usermeta']) || empty($opts['reg_usermeta']) ) {
                    continue;
                }
            
                foreach ( $opts['reg_usermeta'] as $meta_key => $field_id ) {
                    if ( $meta_key != 'user_url' ) {
                        $meta_keys[$meta_key] = $field_id;
                    }
                }
            }
        //If running Formidable 2.0+
        } else {
            // Get registration settings for all forms
            $register_actions = FrmFormActionsHelper::get_action_for_form( 'all', 'register' );
            foreach ( $register_actions as $opts ) {
                if ( !isset( $opts->post_content['reg_usermeta'] ) || empty( $opts->post_content['reg_usermeta'] ) ) {
                    continue;
                }

                foreach ( $opts->post_content['reg_usermeta'] as $usermeta_vars ) {
                    if ( $usermeta_vars['meta_name'] != 'user_url' ) {
                        $meta_keys[$usermeta_vars['meta_name']] = $usermeta_vars['field_id'];
                    }
                }
            }
        }

        //TODO: prevent duplicate user meta from showing
        
        if ( !empty($meta_keys) ) {
            include(FrmRegAppHelper::path() .'/views/show_usermeta.php');
        }
    }
    
    private static function update_usermeta($form_options, $user_ID) {
        if ( isset($form_options['reg_avatar']) && is_numeric($form_options['reg_avatar']) ) {
            // Only update avatar if there is something set
            if ( isset( $_POST['item_meta'][$form_options['reg_avatar']] ) && !empty( $_POST['item_meta'][$form_options['reg_avatar']] ) ) {
                update_user_meta( $user_ID, 'frm_avatar_id', (int) $_POST['item_meta'][$form_options['reg_avatar']] );
            }
        }
        
        if ( !isset($form_options['reg_usermeta']) || empty($form_options['reg_usermeta']) ) {
            return;
        }

        foreach ( $form_options['reg_usermeta'] as $meta_key => $field_id ) {
            //For 2.0+

            if ( ! FrmRegAppHelper::is_below_2() ) {
                $meta_key = $field_id['meta_name'];
                $field_id = $field_id['field_id'];
            }

            $meta_val = isset($_POST['item_meta'][$field_id]) ? $_POST['item_meta'][$field_id] : '';
            if ( $meta_key == 'user_url' ) {
                wp_update_user(array('ID' => $user_ID, 'user_url' => $meta_val));
            } else {
                update_user_meta($user_ID, $meta_key, $meta_val);
            }
            
            unset($meta_val, $meta_key, $field_id);
        }
    }
    
    private static function _get_usermeta($form_options, $user_meta = array()){
        if ( isset( $form_options['reg_email'] ) && !empty( $_POST['item_meta'][$form_options['reg_email']] ) ) {
            $user_meta['user_email'] = sanitize_text_field( $_POST['item_meta'][$form_options['reg_email']] );
        }

        if ( is_numeric($form_options['reg_password']) && !empty($_POST['item_meta'][$form_options['reg_password']] ) ) {
            $user_meta['user_pass'] = $_POST['item_meta'][$form_options['reg_password']];
        }

        foreach ( array('first_name', 'last_name') as $user_field ) {
            if (is_numeric( $form_options['reg_'. $user_field] ) && !empty( $_POST['item_meta'][$form_options['reg_'. $user_field]] ) ) {
                $user_meta[$user_field] = $_POST['item_meta'][$form_options['reg_'. $user_field]];
            }
        }

        // Other cols in wp_users: 'user_url', 'display_name', 'description'
        
        return $user_meta;
    }
    
    private static function _generate_display_name( &$user_meta, $opts ){
        if ( !isset( $opts['reg_display_name'] ) ) {
            return;
        }

		// Get user's first and last name
		$name = array( 'first' => '', 'last' => '' );
		foreach ( $name as $key => $val ) {
			// If first or last name name is a field in the form and it is not blank
			if ( is_numeric( $opts[ 'reg_' . $key . '_name' ] ) && isset( $_POST['item_meta'][ $opts[ 'reg_' . $key . '_name' ] ] ) && $_POST['item_meta'][ $opts[ 'reg_' . $key . '_name' ] ] ) {
				$name[$key] = $_POST['item_meta'][ $opts[ 'reg_' . $key . '_name' ] ];

			// If first/last name is set in user profile
			} else if ( isset( $user_meta[$key . '_name'] ) ) {
				$name[$key] = $user_meta[$key . '_name'];

			// If first/last name is not set anywhere
			} else {
				$name[$key] = '';
			}
			unset( $key, $val );
		}

        // Display name should match username by default
        if ( empty( $opts['reg_display_name'] ) && is_numeric( $opts['reg_username'] ) ) {
            $user_meta['display_name'] = $_POST['item_meta'][$opts['reg_username']];

        // Display name is set as a specific field
        } else if ( is_numeric( $opts['reg_display_name'] ) ) {
            $user_meta['display_name'] = $_POST['item_meta'][$opts['reg_display_name']];

        // Display name is first and last name
        } else if ( $opts['reg_display_name'] == 'display_firstlast' ) {
			$user_meta['display_name'] = $name['first'] . ' ' . $name['last'];

        // Display name is last and first name
        } else if ( $opts['reg_display_name'] == 'display_lastfirst' ) {
            $user_meta['display_name'] = $name['last'] . ' ' . $name['first'];
        }
    }
    
	public static function login_form( $atts ) {
        $defaults = array(
			'form_id' => 'loginform',
			'label_username' => __( 'Username', 'frmreg' ),
			'label_password' => __( 'Password', 'frmreg' ),
			'label_remember' => __( 'Remember Me', 'frmreg' ),
            'label_log_in' => __( 'Login', 'frmreg' ),
			'label_logout' => __( 'Logout', 'frmreg' ),
			'id_username' => 'user_login',
            'id_password' => 'user_pass', 'id_remember' => 'rememberme',
            'id_submit' => 'wp-submit', 'remember' => true,
            'value_username' => NULL, 'value_remember' => false,
			'username_placeholder' => false, 'password_placeholder' => false,
            'slide' => false, 'style' => true, 'layout' => 'v',
			'redirect' => $_SERVER['REQUEST_URI'], 'show_labels' => true,
			'show_messages' => true,
        );
        
        if(isset($atts['slide']) and $atts['slide']){
            $defaults['form_id'] = 'frm-loginform';
            $defaults['label_username'] = $defaults['label_password'] = '';
            $defaults['remember'] = false;
            $defaults['layout'] = 'h';
        }
        
        $atts = shortcode_atts($defaults, $atts);

		if ( is_user_logged_in() ) {
			//don't show the login form if user is already logged in
			return '<a href="' . esc_url( wp_logout_url( get_permalink() ) ) . '" class="frm_logout_link" >'. $atts['label_logout'] . '</a>';
		}

        //If current page has redirect_to parameter set, let this override redirect parameter in shortcode
        $redirect_to = FrmRegAppHelper::get_redirect_to( $_SERVER['REQUEST_URI'] );
        if ( $redirect_to ) {
            $atts['redirect'] = $redirect_to;
        }

        global $frm_vars;
        if(!is_array($frm_vars))
            $frm_vars = array();
        
        if(!isset($frm_vars['reg_login_ids']) or !is_array($frm_vars['reg_login_ids']))
            $frm_vars['reg_login_ids'] = array();
        
        // If styling is not loaded yet, load it now
        if ( ( ! isset($frm_vars['css_loaded']) || ! $frm_vars['css_loaded'] ) && $atts['style'] ) {
            global $frm_settings;
            if ( empty($frm_settings) && is_callable('FrmAppHelper::get_settings') ) {
                 $frm_settings = FrmAppHelper::get_settings();
            }
            if ( $frm_settings->load_style != 'none' ) {
                wp_enqueue_style('formidable');
                $frm_vars['css_loaded'] = true;
            }
        }

		if ( in_array($atts['form_id'], $frm_vars['reg_login_ids'] ) ) {
			$atts['form_id'] .= count( $frm_vars['reg_login_ids'] );
		}
        $frm_vars['reg_login_ids'][] = $atts['form_id'];
        
        $content = '';

        // Print error/success messages
        if ( $atts['show_messages'] ) {
            FrmRegAppHelper::print_messages( $content );
        }

		$frm_version = is_callable('FrmAppHelper::plugin_version') ? FrmAppHelper::plugin_version() : 0;
		if ( version_compare( $frm_version, '2.0.09' ) == '-1' ) {
			self::get_login_form_css( $atts, $content );
		}

		self::get_login_form_js( $atts, $content );

		$atts['echo'] = false;
		if ( $atts['style'] ) {
			$class = 'with_frm_style';
			if ( is_callable('FrmStylesController::get_form_style_class') ) {
				$class = FrmStylesController::get_form_style_class($class, 'default');
			}
			if ( $atts['layout'] == 'h' ) {
				$class .= ' frm_inline_login';
			}
			if ( $atts['show_labels'] == false ) {
				$class .= ' frm_no_labels';
			}
			if ( $atts['slide'] ) {
				$class .= ' frm_slide';
			}
			$content .= '<div class="'. esc_attr( $class ) .' frm_login_form"><div class="frm_form_fields submit auto_width">'."\n";
		}

		if ( $atts['slide'] ) {
			$content .= '<span class="frm-open-login"><a href="#">'. $atts['label_log_in'] .' &rarr;</a></span>';
		}

		$content .= wp_login_form( $atts );

		if ( $atts['style'] ) {
			$content .= '</div></div>';
		}

		return $content;
	}

	private static function get_login_form_css( $atts, &$content ) {
        if ( $atts['slide'] && $atts['style'] ) {
            $content .= '<style type="text/css">';
            if($atts['layout'] == 'h'){
                $content .= '#'. $atts['form_id'].' p{float:left;margin:1px 1px 0;padding:0;}.frm-open-login{float:left;margin-right:15px;}#'. $atts['form_id'].' input[type="text"], #'. $atts['form_id'].' input[type="password"]{width:120px;}';
            }else{
                $content .= '#'. $atts['form_id'].' input[type="text"], #'. $atts['form_id'].' input[type="password"]{width:auto;}';
            }
            $content .= '#'. $atts['form_id'].'{display:none;}#'. $atts['form_id'].' input{padding:1px 5px 2px;vertical-align:top;font-size:13px;} .frm-open-login a{text-decoration:none;font-size:12px;}
</style>'."\n";
        }else if($atts['style']){
            $content .= '<style type="text/css">#'. $atts['form_id'].' input[type="text"], #'. $atts['form_id'].' input[type="password"]';
            if($atts['layout'] == 'h'){
                $content .= '{width:120px;}#'. $atts['form_id'].' p{float:left;margin:1px 1px 0;padding:0;}';
            }else{
                $content .= '{width:auto;}';
            }
            $content .= '</style>'."\n";
        }
	}

	private static function get_login_form_js( $atts, &$content ) {
		if ( ! $atts['username_placeholder'] && ! $atts['password_placeholder'] && ! $atts['slide'] ) {
			return;
		}

        if ( $atts['slide'] ) {
            $content .= '<div style="clear:both"></div>'."\n";
		}

		$content .= '<script type="text/javascript">';
		$content .= 'jQuery(document).ready(function($){';
		if ( $atts['username_placeholder'] ) {
			$content .= "$('#user_login').attr( 'placeholder', '" . $atts['username_placeholder'] . "' );";
		}
		if ( $atts['password_placeholder'] ) {
			$content .= "$('#user_pass').attr( 'placeholder', '" . $atts['password_placeholder'] . "' );";
		}
		if ( $atts['slide'] ) {
			$content .= '$(".frm-open-login a").click(function(){$("#'. $atts['form_id'] .'").toggle(400, "linear");return false;});';
		}

		$content .= '});';
		$content .= '</script>';
	}

    //TODO: Set up lost password form shortcode
    public static function lostpassword_form() {
        $reset = $_GET['reset'];

        $content = '';
        $content .= '
    	<div id="tab3_login" class="tab_content_login" style="display:none;">
			<h3>Lose something?</h3>
			<p>Enter your username or email to reset your password.</p>
			<form method="post" action="<?php echo esc_url( site_url( "wp-login.php?action=lostpassword", "login_post" ) ) ?>" class="wp-user-form">
				<div class="username">
					<label for="user_login" class="hide">' . ('Username or Email') . ': </label>
					<input type="text" name="user_login" value="" size="20" id="user_login" tabindex="1001" />
				</div>
				<div class="login_fields">' .
					do_action('login_form', 'resetpass') .
					'<input type="submit" name="user-submit" value="' . esc_attr( __( 'Reset my password', 'frmreg' ) ) . '" class="user-submit" tabindex="1002" />' .
					($reset == true) ? '<p>A message will be sent to your email address.</p>' : '' .
					'<input type="hidden" name="redirect_to" value="' . esc_attr( $_SERVER['REQUEST_URI'] .  '?reset=true' ) . '" />
					<input type="hidden" name="user-cookie" value="1" />
				</div>
			</form>
		</div>';
        return $content;
    }

    public static function register_widgets() {
        include_once(FrmRegAppHelper::path() .'/widgets/FrmRegLogin.php');
        register_widget('FrmRegLogin');
    }

    //filter login shortcode in text widgets
    public static function widget_text_filter( $content ) {
        if ( is_callable('FrmAppHelper::widget_text_filter_callback') ) {
            $callback = 'FrmAppHelper::widget_text_filter_callback';
        } else if ( is_callable('FrmAppController::widget_text_filter_callback') ) {
            $callback = 'FrmAppController::widget_text_filter_callback';
        } else {
            return $content;
        }
        
    	$regex = '/\[\s*frm-login(\s+)?.*\]/';
    	return preg_replace_callback( $regex, $callback, $content );
    }
    
    public static function get_avatar( $avatar = '', $id_or_email, $size = '96', $default = '', $alt = false ) {
        if ( !class_exists('FrmProFieldsHelper') ) {
            //stop if pro is not installed
            return $avatar;
        }
        
        //change frm_avatar to whatever user meta name you have given the upload field in your registration settings
        $avatar_ids = (array) get_option('frm_avatar');
        if ( empty($avatar_ids) ) {
            // no avatar field has been set
            return $avatar;
        }
        
        $avatar_ids = array_reverse($avatar_ids);
        
        if ( is_numeric($id_or_email) ) {
            $user_id = (int) $id_or_email;
        } else if ( is_string($id_or_email) ) {
            if ( $user = get_user_by('email', $id_or_email ) ) {
                $user_id = $user->ID;
            }
        } else if ( is_object($id_or_email) && !empty($id_or_email->user_id) ) {
            $user_id = (int) $id_or_email->user_id;
        }

        if ( isset($user_id) ) {
            $avatar_id = get_user_meta($user_id, 'frm_avatar_id', true);
            if ( !$avatar_id ) {
                global $wpdb;

                // check each avatar field for an avatar for this user
                foreach ( $avatar_ids as $fid ) {
                    if ( $avatar_id ) {
                        break;
                    } else if ( !is_numeric($fid) ) {
                        continue;
                    }

                    $field = FrmField::getOne($fid);
                    if ( !$field ) {
                        continue;
                    }

                    $entry = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$wpdb->prefix}frm_items WHERE user_id=%d AND form_id=%d ORDER BY created_at DESC LIMIT 1", $user_id, $field->form_id));
                    if ( $entry ) {
                        $avatar_id = FrmProEntryMetaHelper::get_post_or_meta_value($entry, $field);
                    }
                    unset($entry);
                }

                update_user_meta($user_id, 'frm_avatar_id', (int) $avatar_id);
            }
            
            //TODO: get sizes on this site
            if ($size < 150) {
                $temp_size = 'thumbnail';
            } else if ( $size < 250 ) {
                $temp_size = 'medium';
            } else{
                $temp_size = 'full';
            }
            $local_avatars = FrmProFieldsHelper::get_media_from_id($avatar_id, $temp_size);
        }
        
        if ( !isset($local_avatars) || empty($local_avatars) ) {
            if ( !empty($avatar) ) { // if called by filter
                return $avatar;
            }

            remove_filter( 'get_avatar', array(__CLASS__, 'get_avatar') );
            $avatar = get_avatar( $id_or_email, $size, $default );
            add_filter( 'get_avatar', array(__CLASS__, 'get_avatar'), 10, 5 );
            return $avatar;
        }

        if ( !is_numeric($size) ) {
            // ensure valid size
            $size = '96';
        }

        if ( empty($alt) ) {
            $alt = get_the_author_meta( 'display_name', $user_id );
        }

        $author_class = is_author( $user_id ) ? ' current-author' : '' ;
        $avatar = "<img alt='" . esc_attr($alt) . "' src='" . $local_avatars . "' class='avatar avatar-{$size}{$author_class} photo' height='{$size}' width='{$size}' />";

        return $avatar;
    }

    /**
     * Redirect user after successful login.
     *
     * @param string $redirect_to URL to redirect to.
     * @param string $request URL the user is coming from.
     * @param object $user Logged user's data.
     * @return string
     */
    public static function control_login_redirect( $redirect_to, $request, $user ) {
        $check_for_redirect = FrmRegAppHelper::get_redirect_to( $redirect_to );
        if ( $check_for_redirect ) {
            $redirect_to = $check_for_redirect;
        }
        return $redirect_to;
    }

    public static function migrate_to_2($form) {
        if ( ! isset($form->options['registration']) || ! $form->options['registration'] || ! isset($form->options['reg_email']) || empty($form->options['reg_email']) ) {
            return;
        }
        
        if ( FrmRegAppHelper::is_below_2() ) {
            return;
        }
        
        $action_control = FrmFormActionsController::get_form_actions( 'register' );
        $post_id = $action_control->migrate_to_2($form);
        
        return $post_id;
    }

	/**
	* Add Login Form to shortcode builder
	*
	* @since 1.11.04
	*/
	public static function add_login_shortcode($shortcodes) {
		$shortcodes['frm-login'] = array( 'name' => __( 'Login Form', 'formidable' ), 'label' => __( 'Insert a Login Form', 'formidable' ));

		return $shortcodes;
	}

	/**
	* Add login form options to shortcode builder
	*
	* @since 1.11.04
	*/
	public static function login_sc_opts( $opts, $shortcode ) {
		if ( $shortcode != 'frm-login' ) {
			return $opts;
		}

		$opts = array(
			'label_username' => array(
				'val' => __( '', 'formidable' ),
				'label' => __( 'Username Label', 'formidable' ),
				'type' => 'text',
			),
			'label_password' => array(
				'val' => __( '', 'formidable' ),
				'label' => __( 'Password Label', 'formidable' ),
				'type' => 'text',
			),
			'label_remember' => array(
				'val' => __( '', 'formidable' ),
				'label' => __( 'Remember Me Label', 'formidable' ),
				'type' => 'text',
			),
			'label_log_in' => array(
				'val' => __( '', 'formidable' ),
				'label' => __( 'Login Button Label', 'formidable' ),
				'type' => 'text',
			),
			'label_logout' => array(
				'val' => __( '', 'formidable' ),
				'label' => __( 'Logout Label', 'formidable' ),
				'type' => 'text',
			),
			'layout'      => array(
				'val' => '', 'label' => __( 'Display format', 'formidable' ),
				'type' => 'select', 'opts' => array(
					''     => __( 'Standard (vertical)', 'formidable' ),
					'h'   => __( 'Inline (horizontal)', 'formidable' ),
				),
			),
			'slide' => array(
				'val' => 1,
				'label' => __( 'Require a click to show the login form', 'formidable' )
			),
			'remember' => array(
				'val' => 0,
				'label' => __( 'Hide the "Remember Me" checkbox', 'formidable' )
			),
			'show_labels' => array(
				'val' => 0,
				'label' => __( 'Hide the username and password labels', 'formidable' )
			),
			'show_messages' => array(
				'val' => 0,
				'label' => __( 'Hide the login error messages', 'formidable' )
			),
			'username_placeholder' => array(
				'val' => __( '', 'formidable' ),
				'label' => __( 'Username Placeholder', 'formidable' ),
				'type' => 'text',
			),
			'password_placeholder' => array(
				'val' => __( '', 'formidable' ),
				'label' => __( 'Password Placeholder', 'formidable' ),
				'type' => 'text',
			),
			'style' => array(
				'val' => 0,
				'label' => __( 'Do not use Formidable styling', 'formidable' )
			),
		);

		return $opts;
	}

}
