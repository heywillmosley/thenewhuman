<?php

class FrmMlcmpAppController{
    public static $min_version = '1.07.05';
    
    function __construct(){
        add_action('admin_init', array(__CLASS__, 'include_updater'), 1);
        add_action('after_plugin_row_formidable-mailchimp/formidable-mailchimp.php', array(__CLASS__, 'min_version_notice'));
        add_action('frm_entry_form', array(__CLASS__, 'hidden_form_fields'), 10, 2);
        
        // 2.0 hooks
        add_action('frm_trigger_malichimp_action', array(__CLASS__, 'trigger_mailchimp'), 10, 3);
        
        // < 2.0 hooks
        add_action('frm_after_create_entry', array(__CLASS__, 'send_to_mailchimp'), 25, 2);
        add_action('frm_after_update_entry', array(__CLASS__, 'send_to_mailchimp'), 25, 2);
        //add_action('frm_before_destroy_entry', array(__CLASS__, 'unsubscribe'));
    }
    
    public static function min_version_notice(){
        $frm_version = is_callable('FrmAppHelper::plugin_version') ? FrmAppHelper::plugin_version() : 0;
        
        // check if Formidable meets minimum requirements
        if ( version_compare($frm_version, self::$min_version, '>=') ) {
            return;
        }
        
        $wp_list_table = _get_list_table('WP_Plugins_List_Table');
        echo '<tr class="plugin-update-tr active"><th colspan="' . $wp_list_table->get_column_count() . '" class="check-column plugin-update colspanchange"><div class="update-message">'.
        __('You are running an outdated version of Formidable. This plugin may not work correctly if you do not update Formidable.', 'formidable') .
        '</div></td></tr>';
    }
    
    public static function path(){
        return dirname(dirname( __FILE__ ));
    }
    
    public static function include_updater(){
        $update = new FrmMlcmpUpdate();
    }
    
    public static function hidden_form_fields($form, $form_action){
        $form->options = maybe_unserialize($form->options);
        if ( !isset($form->options['mailchimp']) || !$form->options['mailchimp'] || !isset($form->options['mlcmp_list']) || !is_array($form->options['mlcmp_list']) ) {
            return;
        }
            
        echo '<input type="hidden" name="frm_mailchimp" value="1"/>'."\n";
        
        if ( $form_action != 'update' ) {
            return;
        }
        
        global $frm_vars, $frm_editing_entry;
        $list = reset($form->options['mlcmp_list']);
        $field_id = $list['fields']['EMAIL'];
        $edit_id = (is_array($frm_vars) && isset($frm_vars['editing_entry'])) ? $frm_vars['editing_entry'] : $frm_editing_entry;
        $frm_entry_meta = new FrmEntryMeta();
        $email = $frm_entry_meta->get_entry_meta((int)$edit_id, $field_id);
        unset($frm_entry_meta);
        echo '<input type="hidden" name="frm_mailchimp_email" value="'. esc_attr($email) .'"/>'."\n";
    }
    
    public static function send_to_mailchimp($entry_id, $form_id){
        if ( !isset($_POST) || !isset($_POST['frm_mailchimp']) ) {
            return;
        }
        
        global $wpdb;
        $form_options = $wpdb->get_var($wpdb->prepare("SELECT options FROM {$wpdb->prefix}frm_forms WHERE id=%d", $form_id));
        $form_options = maybe_unserialize($form_options);
        if ( !isset($form_options['mailchimp']) || !$form_options['mailchimp'] ) {
            return;
        }
        
        $frm_field = new FrmField();
        
        $frm_entry = new FrmEntry();
        $entry = $frm_entry->getOne($entry_id);
        $entry->description = maybe_unserialize($entry->description);
        unset($frm_entry);
        
        foreach ( $form_options['mlcmp_list'] as $list_id => $list_options ) {
            //check conditions
            $subscribe = true;
            if ( isset($list_options['hide_field']) && is_array($list_options['hide_field']) ) {
                //for now we are assuming that if all conditions are met, then the user will be subscribed
                foreach ( $list_options['hide_field'] as $hide_key => $hide_field ) {
                    if(!$subscribe)
                        continue;
                        
                    $observed_value = (isset($_POST['item_meta'][$hide_field])) ? $_POST['item_meta'][$hide_field] : '';
                    
                    if ( $observed_value == '' ) {
                        $subscribe = false;
                    } else if ( class_exists('FrmProFieldsHelper') ) {
                        $subscribe = FrmProFieldsHelper::value_meets_condition($observed_value, $list_options['hide_field_cond'][$hide_key], $list_options['hide_opt'][$hide_key]);
                    }
                }
            }

            if(!$subscribe) //don't subscribe if conditional logic is not met
                continue;
            
            $list_fields = self::decode_call('/lists/merge-vars', array( 'id' => array( $list_id ) ));
            
            $vars = array();
            
            foreach ( $list_options['fields'] as $field_tag => $field_id ) {
                if ( empty($field_id) ) {
                    // don't sent an empty value
                    continue;
                }
                
                $vars[$field_tag] = (isset($_POST['item_meta'][$field_id])) ? $_POST['item_meta'][$field_id] : '';
                if(is_numeric($vars[$field_tag])){
                    $field = $frm_field->getOne($field_id);
                    if($field->type == 'user_id'){
                        $user_data = get_userdata($vars[$field_tag]);
                        if ( $field_tag == 'EMAIL' ) {
                            $vars[$field_tag] = $user_data->user_email;
                        } else if ( $field_tag == 'FNAME' ) {
                            $vars[$field_tag] = $user_data->first_name;
                        } else if ( $field_tag == 'LNAME' ) {
                            $vars[$field_tag] = $user_data->last_name;
                        } else {
                            $vars[$field_tag] = $user_info->user_login;
                        }
                    }else{
                        $vars[$field_tag] = FrmProEntryMetaHelper::display_value($vars[$field_tag], $field, array('type' => $field->type, 'truncate' => false, 'entry_id' => $entry_id)); 
                    }
                }else{
                    global $frmpro_settings;
                    if ( is_string($vars[$field_tag]) && preg_match('/^\d{2}\/\d{2}\/\d{4}$/', trim($vars[$field_tag])) ) {
                	    $vars[$field_tag] = FrmProAppHelper::convert_date($vars[$field_tag], $frmpro_settings->date_format, 'Y-m-d');
                	}
                	
                	$list_field = false;
                	if ( isset($list_fields['data']) ){
                    	foreach ( $list_fields['data'][0]['merge_vars'] as $lf ) {
                    	    if ( $lf['tag'] == $field_tag ) {
                    	        $list_field = $lf;
                    	        continue;
                    	    }
                    	    unset($lf);
                    	}
                	}
                	
                	if($list_field){
                	    if(isset($list_field['dateformat'])){
                	        $list_field['dateformat'] = str_replace('YYYY', 'Y', str_replace('DD', 'd', str_replace('MM', 'm', $list_field['dateformat'])));
                	        $vars[$field_tag] = date($list_field['dateformat'], strtotime($vars[$field_tag]));
                	    }
                	}
                	
                }
                
                if(is_array($vars[$field_tag]))
                    $vars[$field_tag] = implode(', ', $vars[$field_tag]);
            }
            
            unset($list_fields);
            
            if(isset($list_options['groups'])){
                $vars['GROUPINGS'] = array();
                foreach ( $list_options['groups'] as $g_id => $group ) {
                    $selected_grp = (isset($_POST['item_meta'][$group['id']])) ? $_POST['item_meta'][$group['id']] : '';
                    if ( empty($selected_grp) ) {
                        continue;
                    }
                    
                    $grps = array();
                    if ( is_array($selected_grp) ) {
                        foreach($selected_grp as $sel_g){
                            $grps[] = array_search($sel_g, $group);
                        }
                    } else {
                        $grps[] = array_search($selected_grp, $group);
                    }
                    unset($selected_grp);
                    
                    $vars['GROUPINGS'][] = array('id' => $g_id, 'groups' => $grps);
                    unset($g_id, $group);
                }
                
                if ( empty($vars['GROUPINGS']) ) {
                    unset($vars['GROUPINGS']);
                }
            }
            
            if(!isset($vars['EMAIL'])) //no email address is mapped
                return;
            
            $frm_mlcmp_settings = new FrmMlcmpSettings();
        	$email_type = $frm_mlcmp_settings->settings->email_type;
        	unset($frm_mlcmp_settings);
        	
        	$replace_interests = true; // replace or add to groups
            $double_optin = isset($list_options['optin']) ? $list_options['optin'] : false;
            $send_welcome = false;
            
            $update_existing = false;
            $email_field = $vars['EMAIL'];
            if ( isset($_POST['frm_mailchimp_email']) ) { //we are editing the entry
                if ( is_email($_POST['frm_mailchimp_email']) ) {
                    $update_existing = true;
                    $email_field = $_POST['frm_mailchimp_email'];
                } else if ( is_numeric($_POST['frm_mailchimp_email']) && isset($user_data) ) {
                    $frm_field = new FrmField();
                    $f = $frm_field->getOne( (int) $list_options['fields']['EMAIL'] );
                    unset($frm_field);
                    
                    if ( $f && $f->type == 'user_id' ) {
                        if ( (int) $list_options['fields']['EMAIL'] == (int) $_POST['frm_mailchimp_email'] ) {
                            $update_existing = true;
                            $email_field = $user_data->user_email;
                        } else {
                            //user ID field was changed. Allow it?
                        }
                    }
                }
            }            
            
            $email_array = array('email' => $email_field);
            
            // check entry for existing MailChimp id
            if ( is_array($entry->description) && isset($entry->description['mailchimp-leid']) && !empty($entry->description['mailchimp-leid']) && isset($entry->description['mailchimp-leid'][$list_id]) ) {
                $update_existing = true;
                $email_array = array('leid' => $entry->description['mailchimp-leid'][$list_id]);
                $vars['new_email'] = $vars['EMAIL']; // maybe update the email address
            }
            
            // These two filters (frm_mlcmp_update_existing, frm_mlcmp_send_welcome) can be deprecated
            $update_existing = apply_filters('frm_mlcmp_update_existing', $update_existing, compact('list_id', 'email_field', 'vars', 'email_type', 'double_optin', 'replace_interests', 'send_welcome'));
            $send_welcome = apply_filters('frm_mlcmp_send_welcome', $send_welcome, compact('list_id', 'email_field', 'vars', 'email_type', 'double_optin', 'update_existing', 'replace_interests'));
            
            $sending_data = apply_filters('frm_mlcmp_subscribe_data', array(
                'id'        => $list_id,
                'email'     => $email_array,
                'merge_vars' => $vars,
                'email_type' => $email_type,
                'double_optin' => $double_optin,
                'update_existing' => $update_existing,
                'replace_interests' => $replace_interests,
                'send_welcome' => $send_welcome,
            ), $entry );
            
            // Allow the filter to stop submission
            if ( !empty($sending_data) ) {
                $subscribe = self::call('/lists/subscribe', $sending_data);
                $subscribe = json_decode($subscribe, true);
                
                if ( isset($subscribe['status']) && $subscribe['status'] == 'error' ) {
                    // TODO: log the error message
                } else if ( isset($subscribe['leid']) ) {
                    global $wpdb;
                    
                    // save the list user id to the entry for later editing
                    $entry->description = (array) $entry->description;
                    if ( !isset($entry->description['mailchimp-leid']) || !is_array($entry->description['mailchimp-leid']) ) {
                        $entry->description['mailchimp-leid'] = array();
                    }
                    $entry->description['mailchimp-leid'][$list_id] = $subscribe['leid'];
                    
                    $wpdb->update($wpdb->prefix .'frm_items', 
                        array('description' => serialize($entry->description) ),
                        array('id' => $entry_id)
                    );
                }
            }
            
            unset($list_id, $list_options, $vars, $email_field);
        }
    }
    
    public static function unsubscribe($id){
        $frm_entry = new FrmEntry();
        $entry = $frm_entry->getOne($id);
        if(!$entry)
            return;
    }
    
    public static function get_groups($id) {
        $groups = self::decode_call('/lists/interest-groupings', array( 'id' => $id ) );
        if ( $groups && isset( $groups['error'] ) ) {
            $groups = false;
        }
        return $groups;
    }
    
    public static function decode_call($endpoint, $args = array(), $apikey = null ) {
        $res = self::call($endpoint, $args, $apikey);
        return json_decode($res, true);
    }
    
    public static function call($endpoint, $args = array(), $apikey = null ) {
        if( is_null( $apikey ) ) {
            $frm_mlcmp_settings = new FrmMlcmpSettings();
            $apikey = $frm_mlcmp_settings->settings->api_key;
        }
        
        $dc = self::get_datacenter( $apikey );
        $dc = empty( $dc ) ? '' : "{$dc}.";
        $url = "https://{$dc}api.mailchimp.com/2.0{$endpoint}.json";

        $args['apikey'] = $apikey;
        $args = array( 'body' => json_encode( $args ) );
        $res = wp_remote_post( $url, $args );
        $body = wp_remote_retrieve_body( $res );
        
        if ( is_wp_error( $res ) ) {
            $message = __('You had an error communicating with the MailChimp API.', 'formidable') . $res->get_error_message();
            return json_encode( array('error' => $message, 'status' => 'error') );
        }else if ( $body == 'error' || is_wp_error($body) ) {
            $message = __('You had an error communicating with the MailChimp API.', 'formidable');
            return json_encode( array( 'error' => $message, 'status' => 'error' ) );
        }

        return $res['body'];
    }

    public static function get_datacenter($apikey) {
        $dc = explode( '-', $apikey );
        return isset( $dc[1] ) ? $dc[1] : '';
    }

}