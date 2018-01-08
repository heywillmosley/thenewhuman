<?php

class ESIG_CC_Admin extends Cc_Settings {

    /**
     * Initialize the plugin by loading admin scripts & styles and adding a
     * settings page and menu.
     * @since     0.1
     */
    public static function Init() {

        add_action('admin_enqueue_scripts', array(__CLASS__, 'queueScripts'));
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_admin_styles'));

        add_filter("esig_cc_users", array(__CLASS__, "cc_users"), 10, 1);
        add_filter("esig_cc_edit_users", array(__CLASS__, "cc_users_edit"), 10, 1);
        
        add_filter("esig_cc_users_content", array(__CLASS__, "cc_users_content_edit"), 10, 2);
        
        add_filter("esig_cc_users_temp", array(__CLASS__, "cc_users_content_edit_temp"), 10, 2);
        
        add_action('wp_ajax_esig_cc_user_information', array(__CLASS__, 'esig_cc_user_information_ajax'));
        add_action('wp_ajax_nopriv_esig_cc_user_information', array(__CLASS__, 'esig_cc_user_information_ajax'));
        
        add_action('esig_reciepent_edit', array(__CLASS__, 'esig_reciepent_cc_edit'), 10, 1);

        add_action('esig_document_after_save', array(__CLASS__, 'sending_mail_cc_users'), 10, 1);
        
         add_action('esig_template_save', array(__CLASS__, 'esig_template_save'), 10, 1);
        
        add_action('esig_template_basic_document_create', array(__CLASS__, 'template_basic_doc_create'), 10, 1);
    }
    
     public static function esig_template_save($args){
            $document_id = $args['document_id'];
            $template_id = $args['template_id'];
           
           
                if(self::is_cc_enabled($document_id)){
                    WP_E_Sig()->meta->add($template_id,  self::USER_INFO_META_KEY,  json_encode(self::get_cc_information($document_id)));
                }
           
    }
    
    
    public static function template_basic_doc_create($args){
            $document_id = $args['document_id'];
            $template_id = $args['template_id'];
            $doc_type = $args['document_type'];
            
            if($doc_type == "basic"){
                
                if(self::is_cc_enabled($template_id)){
                    WP_E_Sig()->meta->add($document_id,  self::USER_INFO_META_KEY, json_encode(self::get_cc_information($template_id)));
                }
            }
    }


    public static function esig_reciepent_cc_edit($args){
         $document_id = $args['document_id']; 
         $POST=$args['post'];
         
         if(count($POST['cc_recipient_emails'])>0){
             self::prepare_cc_user_information($document_id, $POST);
         }
         else {
             self::delete_cc_information($document_id);
         }
    }

    public static function sending_mail_cc_users($args) {

        $document_id = $args['document']->document_id;
        
        $signers = self::get_cc_information($document_id, false);

        $doc = WP_E_Sig()->document->getDocument($document_id);

        //global $cc_users ;
        $cc_users = new stdClass();

        $cc_users->doc = $doc;
        $cc_users->owner_email = self::get_owner_email($doc->user_id);
        $cc_users->owner_name = self::get_owner_name($doc->user_id);
        $cc_users->organization_name = self::get_organization_name($doc->user_id);
        $cc_users->signers = WP_E_Sig()->signer->get_all_signers($document_id);
        //$cc_users = array();
        foreach ($signers as $user_info) {


            $cc_users->user_info= $user_info;

            $subject = __("You have been cc'd on ", "esig") . $doc->document_title;

            $email_temp = WP_E_Sig()->view->renderPartial('', $cc_users, false, '', ESIGN_CC_PATH . '/views/cc-email-template.php');

            WP_E_Sig()->email->esig_mail($cc_users->owner_name, $cc_users->owner_email, $user_info->email_address, $subject, $email_temp);
            
            self::cc_record_event($document_id,$cc_users->owner_name,$cc_users->owner_email, $user_info->first_name, $user_info->email_address);
        }
    }
    
    public static function cc_record_event($document_id,$sender_name,$sender_email,$cc_name,$cc_email){
        
             $event_text = sprintf(__("%s - %s added by %s - %s as a CC'd Recipient Ip: %s", 'esig'), esig_unslash($cc_name), $cc_email,  esig_unslash($sender_name),$sender_email, esig_get_ip());
             WP_E_Sig()->document->recordEvent($document_id, 'document_signed', $event_text);
    }

    public static function esig_cc_user_information_ajax() {

        $document_id = isset($_POST['document_id']) ? $_POST['document_id'] : null;
        
        self::prepare_cc_user_information($document_id, $_POST);

        die();
    }

    public static function cc_users($protected_documents) {

        $protected_documents = '<div  class="cc_recipient_emails_container">
                                        <div class="esig-cc-signer-container">
                                           <a href="#" class="add-esig-cc" id="add-esig-cc">' . __('+ CC', 'esig-cc') . '</a>
                                        </div>
                                        <div id="error"></div>
                                        <div id="cc_recipient_emails" class="af-inner">
                                        </div>
                                </div>
                                ';


        return $protected_documents;
    }

    public static function cc_users_edit($protected_documents) {

        $document_id = (isset($_GET['document_id'])) ? $_GET['document_id'] : null;

        $cc_edit_users = self::get_cc_information($document_id, false);
        $protected_documents .= '<div class="esig-cc-container">

                                            <a href="#" id="add-esig-cc">' . __('+ CC', 'esig-cc') . '</a>
                                        </div>
                                        <div id="error"></div>';
        if (is_array($cc_edit_users) && count($cc_edit_users)>0) {
            
            foreach ($cc_edit_users as $user_info) {

                $fnames = $user_info->first_name;
                $emails = $user_info->email_address;


               

                $protected_documents .= '<div class="cc_recipient_emails_container">
                                        <div id="cc_recipient_emails" class="af-inner">
                                        <div id="signer_main" class="cc-invitation-email">
                                            <input type="text" class="cc_recipient_fnames" name="cc_recipient_fnames[]" placeholder="' . __('CC Users Name', 'esig-cc') . '"  value="' . $fnames . '"/>
                                            <input type="text" class="cc_recipient_emails" name="cc_recipient_emails[]" placeholder="' . __('email@address.com', 'esig-cc') . '"  value="' . $emails . '" style="width:212px;" /> <span id="esig-del-signer" class="deleteIcon" style="position:absolute;left:400px;"></span><br>
                                        </div>
                                        </div>
                                    </div>';
            }
        }else {
           
             $protected_documents .= '<div class="cc_recipient_emails_container">
                                        <div id="cc_recipient_emails" class="af-inner">
                                        
                                        </div>
                                    </div>';
        }


        return $protected_documents;
    }
    
    public static function cc_users_content_edit_temp($protected_documents,$document_id=null) {

        $document_id = (isset($_GET['document_id'])) ? $_GET['document_id'] :$document_id;

        //$cc_edit_users = self::get_cc_information($document_id, false);
         $protected_documents .= '<div class="esig-cc-container">
                                           <a href="#" id="add_cc_temp">' . __('+ CC', 'esig-cc') . '</a>
                                        </div>
                                       ';
       
               
                $protected_documents .= '<div id="cc_recipient_emails" class="cc_recipient_emails">
                    
                                        

                                    </div>';
               
          


        return $protected_documents;
    }
    
    public static function cc_users_content_edit($protected_documents,$document_id=null) {

        $document_id = (isset($_GET['document_id'])) ? $_GET['document_id'] :$document_id;

        $cc_edit_users = self::get_cc_information($document_id, false);
         $protected_documents .= '<div class="esig-cc-container">
                                           <a href="#" id="add_cc">' . __('+ CC', 'esig-cc') . '</a>
                                        </div>
                                       ';
        if (is_array($cc_edit_users)) {
            foreach ($cc_edit_users as $user_info) {

                $fnames = $user_info->first_name;
                $emails = $user_info->email_address;


               
                $protected_documents .= '<div id="cc_recipient_emails12">
                    
                                        <div id="cc-signer_main" class="cc-invitation-email">

                                            <input type="text" class="cc_recipient_fnames" name="cc_recipient_fnames_final[]" placeholder="' . __('CC Users Name', 'esig-cc') . '"  value="' . $fnames . '" readonly />
                                            <input type="text" class="recipient-email-input" name="cc_recipient_emails_final[]" placeholder="' . __('email@address.com', 'esig-cc') . '"  value="' . $emails . '" readonly style="width:212px;" /> <br>


                                        </div>

                                    </div>';
               
            }
        } 


        return $protected_documents;
    }

    public static function enqueue_admin_styles() {

        $screen = get_current_screen();
        $admin_screens = array(
            'admin_page_esign-add-document',
            'admin_page_esign-edit-document',
            'e-signature_page_esign-view-document'
        );

        if (in_array($screen->id, $admin_screens)) {
            wp_enqueue_style('esig-cc-users-admin-styles', ESIGN_CC_URL . '/assets/css/esig_cc.css', array(), ESIGN_CC_VERSION);
        }
    }

    public static function queueScripts() {

        $screen = get_current_screen();

        $admin_screens = array(
            'admin_page_esign-add-document',
            'admin_page_esign-edit-document',
            'e-signature_page_esign-view-document'
        );

        if (in_array($screen->id, $admin_screens)) {

            wp_enqueue_script('jquery');
            wp_enqueue_script('esig-cc-users', ESIGN_CC_URL . '/assets/js/esig-cc.js', false, ESIGN_CC_VERSION, true);
        }
    }

}
