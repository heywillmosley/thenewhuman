<?php
/**
 * invitationsController
 * @since 1.0.1
 * @author Micah Blu
 */
	class WP_E_invitationsController extends WP_E_appController {

	public function __construct(){
		parent::__construct();

		include_once ESIGN_PLUGIN_PATH . DS . "models" . DS . "Invite.php";
		$this->model = new WP_E_Invite();
		$this->mail = new WP_E_Email();
		
	}

	public function calling_class(){
		return get_class();
	}

	/**
	 * Stores invitation and optionally emails the invite
	 *
	 * @since 0.1.0
	 * @param Array $invitation
	 * @return Boolean 
	 */
	public function saveThenSend($invitation, $document){

		// Save first, catch invitation id
		$invitation_id = $this->save($invitation);
                
                /* record event */
                

		// Prepare invitation message
		$invite_template = file_get_contents(ESIGN_PLUGIN_PATH . DS . 'views' . DS . 'invitations' . DS . 'invite.php');

		$pageID = $this->setting->get_generic('default_display_page');

		$invite_hash = $this->model->getInviteHash($invitation_id);

		$invitationURL = esc_url(add_query_arg(array('invite'=>$invite_hash, 'csum'=>$invitation['document_checksum']), get_permalink($pageID)));
		// adding required filter 
		$esig_logo="default";
        $esig_logo = apply_filters('esig_invitation_logo_filter',$esig_logo);
       
        if($esig_logo=="default"){
        	
             $esig_logo = sprintf( __( '<a href="http://wpesign.com/?utm_source=wpesignplugin&utm_medium=signer-invite-email&utm_campaign=header-logo" target="_blank"><img src="%s/images/logo.png" title="Wp E-signature"></a> ', 'esig'), ESIGN_ASSETS_DIR_URI )  ; 
        }
        
        $esig_header_tagline='default';
        
        $esig_header_tagline = apply_filters('esig_invitation_header_tagline_filter',$esig_header_tagline);
        
        if($esig_header_tagline == 'default'){
        	
             $esig_header_tagline = __( 'Sign Legally Binding Documents using a WordPress website', 'esig')  ; 
        }
        $esig_footer_head ='default';
        $esig_footer_head = apply_filters('esig_invitation_footer_head_filter',$esig_footer_head);
        if($esig_footer_head == 'default'){
             $esig_footer_head = __( 'What is WP E-Signature?', 'esig')  ; 
        }
        $esig_footer_text='default';
        $esig_footer_text = apply_filters('esig_invitation_footer_text_filter',$esig_footer_text);
        if($esig_footer_text == 'default'){
            $esig_footer_text = __( 'WP E-Signature by Approve Me is the
                                fastest way to sign and send documents
                                using WordPress. Save a tree (and a
                                stamp).  Instead of printing, signing
                                and uploading your contract, the
                                document signing process is completed
                                using your WordPress website. You have
                                full control over your data - it never
                                leaves your server. <br>
                                <b>No monthly fees</b> - <b>Easy to use
                                  WordPress plugin.</b><a style="color:#368bc6;text-decoration:none" href="http://wpesign.com/?utm_source=wpesignplugin&utm_medium=signer-invite-email&utm_campaign=learn-more" target="_blank"> Learn more</a> ', 'esig');
        }
        
        $admin_user = $this->user->getUserByWPID($document->user_id);
        
         $sender= $admin_user->first_name . " " .  $admin_user->last_name ;
        
        $sender =apply_filters('esig-sender-name-filter',$sender,$document->user_id);
        
		$template_data = array(
            'esig_logo'=> $esig_logo , 
            'esig_header_tagline'=>$esig_header_tagline,
            'esig_footer_head'=>$esig_footer_head,
            'esig_footer_text'=>$esig_footer_text,
			'user_email' => $admin_user->user_email,
			'user_full_name' => $sender,
			'recipient_name' => stripslashes_deep(trim($invitation['recipient_name'])),
			'document_title' => $invitation['document_title'],
			'document_checksum' => $document->document_checksum,
			'invite_url' => $invitationURL,
			'assets_dir' => ESIGN_ASSETS_DIR_URI,
		);

		$invite_message = $this->view->renderPartial('invite', $template_data, false, 'invitations');
                $subject = sprintf(__("%s - Signature requested by %s", 'esig'), $invitation['document_title'], $sender);
		
		
		$mailsent=$this->mail->esig_mail($sender,$admin_user->user_email,$invitation['recipient_email'], $subject, $invite_message);
		// send Email
	
		// Record event: Document sent
		$this->model->recordSent($invitation_id);
		$doc_model=new WP_E_Document();
		$doc_model->esig_event_timezone($document->document_id,$invitation_id);
                
                // record event when document sent for sign\
                 if($mailsent){
                     WP_E_Invite::invite_sent_record($invitation['recipient_name'],$invitation['recipient_email'],$document->document_id);
                 }
                 
		return $mailsent;
	}

	public function save($invitation){

		// Add hash to inivitation array, then insert record
		$hash = time() . rand(0,1000);
		//shuffle($hash);
		$hash = sha1($hash);

		$invitation['hash'] = $hash;

		return $this->model->insert($invitation);

	}

}
