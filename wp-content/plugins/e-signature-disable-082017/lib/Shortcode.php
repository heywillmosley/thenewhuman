<?php

/**
 * Shortcode Class
 *
 * Provides the Client side signature form shortcode
 * @since 0.1.0
 */
class WP_E_Shortcode {

    public function __construct() {
        $this->view = new WP_E_View();
        $this->invite = new WP_E_Invite;
        $this->document = new WP_E_Document;
        $this->signature = new WP_E_Signature;
        $this->user = new WP_E_User;
        $this->setting = new WP_E_Setting;
        $this->validation = new WP_E_Validation();
        $this->notice = new WP_E_Notice();
        $this->email = new WP_E_Email();
        $this->signer = new WP_E_Signer();
        $this->audit_trail_helper = new WP_E_AuditTrail();
    }

    /**
     * Validate document signature submission
     * @since 1.0
     * @param null
     * @return Boolean
     */
    private function doc_signature_validates() {

        $recipient_fname = trim($_POST['recipient_first_name']);

        $invite_hash = $this->validation->esig_clean($_POST['invite_hash']);
        $checksum = $this->validation->esig_clean($_POST['checksum']);
        $assets_dir = ESIGN_ASSETS_DIR_URI;

        $validity = true; // assume true, only false assertions are made

        $invitation = $this->invite->getInviteBy('invite_hash', $invite_hash);

        // use checksum to ensure doc hasn't changed
        $document = $this->document->getDocument($invitation->document_id);

        // The checksum is calculated by appended the document's content to its id then generating a sha1 checksum from that value
        $doc_checksum = sha1($invitation->document_id . $document->document_content);

        // Enforce a legal name
        if (!$this->validation->esig_valid_string($recipient_fname)) {
            $this->view->setAlert(array("type" => "error", "message" => __("First & Last Name are required", 'esig')));
            $validity = false;
        }

        // if hash isn't here... 
        if (empty($invite_hash)) {
            $this->view->setAlert(array("type" => "error", "message" => sprintf(__("Oh snap! Carnegie, you've stumbled upon a broken URL. We're on the case. Let us know if the problem continues to persist. <p align='center'><img src='%s/images/boss.svg'></p>", 'esig'), $assets_dir)));
            $validity = false;
        }

        // if checksums don't match...
        elseif ($checksum != $doc_checksum) {
            $this->view->setAlert(array("type" => "error", "message" => __("The document has been modified since it was sent to you. Please request a new invitation to sign", 'esig')));
            $valid = false;
        }


        return $validity;
    }

    /**
     * Sign Document Shortcode
     * @since 0.1.0
     */
    public function e_sign_document() {

        $assets_dir = ESIGN_ASSETS_DIR_URI;
        @ini_set( 'memory_limit', '256M' );
        // GET - Display signed or unsigned signature form
        if (!isset($_POST['recipient_signature']) && empty($_POST['recipient_signature']) && !isset($_POST['esignature_in_text']) && empty($_POST['esignature_in_text'])) {


            if ($this->admin_can_view()) {

                return $this->admin_preview();
            }

            $invite = isset($_GET['invite']) ? $this->validation->esig_clean($_GET['invite']) : null;
            $check_sum = isset($_GET['csum']) ? $this->validation->esig_clean($_GET['csum']) : null;

            // URL is expected to pass an invite hash and document checksum
            $invite_hash = isset($invite) ? $invite : null;
            $checksum = isset($check_sum) ? $check_sum : null;
            $document_id = WP_E_Sig()->document->document_id_by_csum($checksum);
            if (class_exists("Esig_Slv_Dashboard")) {

                $access = Esig_Slv_Dashboard::esig_verify_access($invite_hash, $checksum);

                if ($access) {
                    return false;
                }
            }




            if (empty($invite_hash) || empty($checksum)) {

                if (get_transient('esig_current_url')) {

                    $current_url = get_transient('esig_current_url');
                    delete_transient('esig_current_url');

                    wp_redirect($current_url);
                    exit;
                }

                $template_data = array(
                    "message" => sprintf(__("<p align='center' class='esig-404-page-template'><a href='http://www.approveme.me/wp-digital-e-signature/' title='Wordpress Digital E-Signature by Approve Me' target='_blank'><img src='%s/images/logo.png' alt='Sign Documents Online using WordPress E-Signature by Approve Me'></a></p><p align='center' class='esig-404-page-template'>Well this is embarrassing, but we can't seem to locate the document you're looking to sign online.<br>You may want to send an email to the website owner. <br>Thank you for using Wordpress Digital E-Signature By <a href='http://www.approveme.me/wp-digital-e-signature/' title='Free Document Signing by Approve Me'>Approve Me</a></p> <p align='center'><img src='" . $assets_dir . "/images/search.svg' alt='esignature by Approve Me' class='esig-404-search'><br><a class='esig-404-btn' href='http://www.approveme.me/wp-digital-e-signature?404'>Download WP E-Signature!</a></p>", 'esig'), $assets_dir),
                );
                $this->displayDocumentToSign(null, '404', $template_data);
                return; // nothing to do here
            }

            // Grab invitation and recipient from invite hash
            $invitation = $this->invite->getInviteBy('invite_hash', $invite_hash);
            $doc_id = $invitation->document_id;

            if ($this->document->document_exists($doc_id) == 0) {
                $template_data = array(
                    "message" => sprintf(__("<p align='center' class='esig-404-page-template'><a href='http://www.approveme.me/wp-digital-e-signature/' title='Wordpress Digital E-Signature by Approve Me' target='_blank'><img src='%s/images/logo.png' alt='Sign Documents Online using WordPress E-Signature by Approve Me'></a></p><p align='center' class='esig-404-page-template'>Well this is embarrassing, but we can't seem to locate the document you're looking to sign online.<br>You may want to send an email to the website owner. <br>Thank you for using Wordpress Digital E-Signature By <a href='http://www.approveme.me/wp-digital-e-signature/' title='Free Document Signing by Approve Me'>Approve Me</a></p> <p align='center'><img src='" . $assets_dir . "/images/search.svg' alt='esignature by Approve Me' class='esig-404-search'><br><a class='esig-404-btn' href='http://www.approveme.me/wp-digital-e-signature?404'>Download WP E-Signature!</a></p>", 'esig'), $assets_dir),
                );
                $this->displayDocumentToSign(null, '404', $template_data);
                return; // nothing to do here
            }

            $recipient = $this->user->getUserdetails($invitation->user_id, $invitation->document_id);
            $template_data = array(
                "invite_hash" => $invite_hash,
                "checksum" => $checksum,
                "recipient_first_name" => $recipient->first_name,
                "ESIGN_ASSETS_URL" => ESIGN_ASSETS_DIR_URI,
                "recipient_last_name" => $recipient->last_name,
                "recipient_id" => $recipient->user_id,
                "signature_classes" => "unsigned",
                "extra_attr" => "readonly",
            );

            // If the doc has already been signed by this user, add their signature and display read only
            if ($this->signature->userHasSignedDocument($recipient->user_id, $doc_id)) {

                $recipient_signature = stripslashes($this->signature->getDocumentSignature($recipient->user_id, $doc_id));
                // echo '<h1>..'.$recipient_signature."</h1>";
                $template_data["recipient_signature"] = $recipient_signature;
                $template_data["signature_classes"] = 'signed';
                $template_data["viewer_needs_to_sign"] = false;
                $template = "sign-preview";
            } else {
                //if already a transient
                delete_transient('esig_current_url');
                $template_data["viewer_needs_to_sign"] = true;
                $template = "sign-document";
            }

            $this->document->recordView($invitation->document_id, $invitation->user_id, null);

            add_thickbox();
            $this->displayDocumentToSign($invitation->document_id, $template, $template_data);


            // POST - Handle signature submission
        } else {


            // for pdmi bug added this tra
            set_transient('esig_current_url', esc_url($_SERVER['REQUEST_URI']));

            if ($this->doc_signature_validates()) {

                $invitation = $this->invite->getInviteBy('invite_hash', $this->validation->esig_clean($_POST['invite_hash']));

                $doc_id = $invitation->document_id;

                // using the invitation grab the recipient user
                $recipient = $this->user->getUserdetails($invitation->user_id, $invitation->document_id);
                $invite_hash_post = $this->validation->esig_clean($_POST['invite_hash']);

                // User has already signed. Don't let them sign again
                if ($this->signature->userHasSignedDocument($invitation->user_id, $doc_id)) {

                    $template_data = array(
                        "invite_hash" => $invite_hash_post,
                        "recipient_signature" => $recipient_signature,
                        "recipient_first_name" => $recipient->first_name,
                        "recipient_last_name" => $recipient->last_name,
                        "viewer_needs_to_sign" => false,
                        "recipient_id" => '',
                        "message" => __("<p class=\"doc_title\" align=\"center\">You've already signed this document.</h2> <p align='center'></p>", 'esig')
                    );

                    $this->displayDocumentToSign($invitation->document_id, "sign-preview", $template_data);
                    return;
                }

                // validation type signature 
                $esig_signature_type = $this->validation->esig_clean($_POST['esig_signature_type']);

                $esignature_in_text = $this->validation->esig_clean($_POST['esignature_in_text']);

                // adding signature here 
                if (isset($esig_signature_type) && $esig_signature_type == "typed") {

                    $signature_id = $this->signature->add($esignature_in_text, $recipient->user_id, $esig_signature_type);

                    $this->setting->set('esig-signature-type-font' . $recipient->user_id, $_POST['font_type']);
                }

                if (isset($_POST['recipient_signature']) && $_POST['recipient_signature'] != "") {

                    $signature_id = $this->signature->add($_POST['recipient_signature'], $recipient->user_id);
                }


                // save signing device information
                if (wp_is_mobile()) {
                    $this->document->save_sign_device($doc_id, 'mobile');
                }

                // link this signature to this document in the document_signature join table
                $join_id = $this->signature->join($invitation->document_id, $signature_id);


                if (!$join_id) {
                    $this->view->setAlert(array("type" => "error", "message" => __("There was an error attaching the signature to the document", 'esig')));
                    error_log("Shortcode: e_sign_document: An error attaching the signature to the document");
                    return;
                }




                // Update the recipient's first and last name
                if (!empty($_POST['recipient_first_name'])) {
                    $f_name = ESIG_POST('recipient_first_name');
                } else {
                    $f_name = "";
                }
                if (!empty($_POST['recipient_last_name'])) {
                    $l_name = ESIG_POST('recipient_last_name');
                } else {
                    $l_name = "";
                }

                $user_name = $this->user->get_esig_signer_name($recipient->user_id, $doc_id);

                if ($f_name != $user_name) {

                    $this->user->updateField($recipient->user_id, "first_name", trim($f_name));

                    $this->signer->updateField($recipient->user_id, $doc_id, "signer_name", trim($f_name));

                    //$this->user->updateField($recipient->user_id, "last_name", trim($l_name));
                    //$this->setting->set("esign_signed_". $invitation->user_id ."_name_document_id_".$doc_id,$f_name);
                    // saving event
                    $event_text = sprintf(__("Signer name %s was changed to %s by %s IP: %s", 'esig'), $user_name,stripslashes($f_name), $recipient->user_email, esig_get_ip());
                    $this->document->recordEvent($doc_id, 'name_changed', $event_text, null);
                }

                $event_text = sprintf(__("Document signed by %s - %s IP %s", 'esig'), stripslashes($f_name), $recipient->user_email, esig_get_ip());
                $this->document->recordEvent($doc_id, 'document_signed', $event_text);

                $document = $this->document->getDocumentByID($doc_id);

                // Fire post-sign action
                do_action('esig_signature_saved', array(
                    'signature_id' => $signature_id,
                    'recipient' => $recipient,
                    'invitation' => $invitation,
                    'post_fields' => $_POST,
                ));

                $recipient_signature = stripslashes($_POST['recipient_signature']);
                $sender_signature = stripslashes($this->signature->getUserSignature($document->user_id));
                $sender = $this->user->getUserBy('user_id', $document->user_id);


                $success_msg = __("<p class=\"success_title\" align=\"center\"><h2>You're done signing!</h2> <p align='center' class='s_logo'><span class=\"icon-success-check\"></span></p>", "esign");

                $success_msg = apply_filters('esig-success-page-filter', $success_msg, array('document' => $document));

                $template_data = array(
                    "invite_hash" => $invite_hash_post,
                    "recipient_signature" => $recipient_signature,
                    "recipient_first_name" => $recipient->first_name,
                    "recipient_last_name" => $recipient->last_name,
                    "viewer_needs_to_sign" => false,
                    "notify" => 'yes',
                    "message" => sprintf(__($success_msg, 'esig'))
                );

                $template = "sign-preview";
                $this->displayDocumentToSign($document->document_id, $template, $template_data);

                // setting extra transient for pdmi bug
            } else { // ! Submission didn't validate
                // display all errors 
                $this->view->renderAlerts();
            }
        }
    }

    /**
     * Notify Document Owner/Admin via email when a document is signed.
     * @since 1.0.1
     */
    public function notify_owner($document, $recipient, $audit_hash, $attachments = false) {

        $owner = $this->user->getUserByWPID($document->user_id);

        $background_color_bg = apply_filters('esig-invite-button-background-color', '');
        $background_color = !empty($background_color_bg) ? $background_color_bg : '#0083c5';


        $template_data = array(
            'document_title' => $document->document_title,
            'document_id' => $audit_hash,
            'document_checksum' => $document->document_checksum,
            'owner_first_name' => $owner->first_name,
            'owner_last_name' => $owner->last_name,
            'owner_email' => $owner->user_email,
            'signer_name' => $this->user->get_esig_signer_name($recipient->user_id, $document->document_id),
            'signer_email' => $recipient->user_email,
            'view_url' => WP_E_Invite::get_preview_url($document->document_id, $audit_hash),
            'assets_dir' => ESIGN_ASSETS_DIR_URI,
            'background_color' => $background_color,
        );

        // $signed_message = $this->view->renderPartial('document_signed', $template_data, false, 'notifications/admin');

        $subject = sprintf(__("%s - Signed by %s %s", "esig"), $document->document_title, $this->user->get_esig_signer_name($recipient->user_id, $document->document_id), $recipient->user_email);
        // $subject = "{$document->document_title} - Signed by {$recipient->first_name} ({$recipient->user_email})";
        // send Email
       
        $sender = $owner->first_name . " " . $owner->last_name;

        // $sender = apply_filters('esig-sender-name-filter', $sender, $document->user_id);
        // $mailsent = $this->email->esig_mail($sender, $owner->user_email, $owner->user_email, $subject, $signed_message, $attachments);

        $mailsent = WP_E_Sig()->email->send(array(
            'from_name' => $sender, // Use 'posts' to get standard post objects
            'from_email' => $owner->user_email,
            'to_email' => $owner->user_email,
            'subject' => $subject,
            'message_template' => ESIGN_PLUGIN_PATH . DS . 'views' . DS . 'notifications' . DS . 'admin' . DS . 'document_signed.php',
            'template_data' => $template_data,
            'attachments' => $attachments,
            'document' => $document,
        ));

        // fire an action when document admin is notified. 
        do_action('esig_notify_owner_sent', array('document' => $document));

        return $mailsent;
    }

    /**
     * Notify signer via email when they sign a document.
     * @since 1.0.1
     */
    public function notify_signer($document, $recipient, $post, $audit_hash, $attachments = false) {

        $owner = $this->user->getUserByWPID($document->user_id);

        $background_color_bg = apply_filters('esig-invite-button-background-color', '');
        $background_color = !empty($background_color_bg) ? $background_color_bg : '#0083c5';

        $template_data = array(
            'document_title' => $document->document_title,
            'document_id' => $audit_hash,
            'document_checksum' => $document->document_checksum,
            'owner_first_name' => $owner->first_name,
            'owner_last_name' => $owner->last_name,
            'owner_email' => $owner->user_email,
            'signer_name' => $this->user->get_esig_signer_name($recipient->user_id, $document->document_id),
            'signer_email' => $recipient->user_email,
            'view_url' => WP_E_Invite::get_invite_url($post['invite_hash'], $document->document_checksum),
            'assets_dir' => ESIGN_ASSETS_DIR_URI,
            'background_color' => $background_color,
        );


        $subject = sprintf(__('%s has been signed', 'esign'), $document->document_title);

        // send Email
        $sender = $owner->first_name . " " . $owner->last_name;


        $mailsent = WP_E_Sig()->email->send(array(
            'from_name' => $sender, // Use 'posts' to get standard post objects
            'from_email' => $owner->user_email,
            'to_email' => $recipient->user_email,
            'subject' => $subject,
            'message_template' => ESIGN_PLUGIN_PATH . DS . 'views' . DS . 'notifications' . DS . 'document_signed.php',
            'template_data' => $template_data,
            'attachments' => $attachments,
            'document' => $document,
        ));

        return $mailsent;
        // do action when email sent
    }

    /**
     * Displays a page where admins can view their document and see signatures
     *
     */
    public function admin_preview() {


        $doc_id = $this->validation->esig_valid_int($_GET['document_id']);
        
        if (isset($doc_id)) {
            $template_data = array(
                "invite_hash" => '',
                "viewer_needs_to_sign" => '',
                "recipient_id" => '',
            );

            $this->displayDocumentToSign($doc_id, "sign-preview", $template_data);
        }
    }

    /**
     * Necessary callback method for wp_mail_content_type filter
     *
     * @since 0.1.0
     */
    public function set_html_content_type() {
        return 'text/html';
    }

    // Should not be used to display secure information. Just html
    public function get_footer_ajax() {


        $args = array();
        //$template_data=array();

        $document_id = isset($_GET['document_id']) ? $this->validation->esig_valid_int($_GET['document_id']) : $this->validation->esig_valid_int($_GET['document_id']);

        $print_option = $this->print_option_display($document_id);

        if ($print_option == "display")
            $print_button = '<a href="javascript:window.print()" class="agree-button" id="print-agree-button" title="">' . __('Print Document', 'esig') . '</a>';

        $print_button = isset($print_button) ? $print_button : '';
        $mode = isset($_GET['esig_mode']) ? $_GET['esig_mode'] : NULL;
        // Default template data
        $template_data = array(
            'print_button' => $print_button,
            'mode' => $mode
        );


        $template_data = apply_filters('esig-document-footer-data', $template_data, $args);

        $preview = $this->validation->esig_clean($_GET['preview']);
        $invitecode = $this->validation->esig_clean($_GET['inviteCode']);
        // If is admin
        if (isset($preview) && $preview == "1") {

            $this->view->renderPartial('_footer_admin', $template_data, true);

            // If is user
        } else {

            $invite_hash = isset($invitecode) ? $invitecode : null;

            // Grab invitation and recipient from invite hash
            $invitation = $this->invite->getInviteBy('invite_hash', $invite_hash);
            $recipient = $this->user->getUserdetails($invitation->user_id, $invitation->document_id);

            // Viewer signed
            if ($this->user->hasSignedDocument($recipient->user_id, $invitation->document_id)) {

                $this->view->renderPartial('_footer_recipient_signed', $template_data, true);
            }
        }
        die();
    }

    /**
     * Necessary callback method for wp_mail_content_type filter
     *
     * @since 0.1.0
     */
    public function displayDocumentToSign($document_id, $template, $data = array(), $return = false) {

        $recipient_sig_html = "";
        $owner_sig_html = "";
        $audit_hash = "";

        $invite_hash_post = (isset($_POST['invite_hash'])) ? $this->validation->esig_clean($_POST['invite_hash']) : null;
        $invite_get = (isset($_GET['invite'])) ? $this->validation->esig_clean($_GET['invite']) : null;


        if (isset($data['notify']) == 'yes') {

            $document = $this->document->getDocument($document_id);
            $doc_status = $this->document->getSignatureStatus($document_id);

            $invitation = $this->invite->getInviteBy('invite_hash', $invite_hash_post);
            $recipient = $this->user->getUserdetails($invitation->user_id, $document_id);

            // If no more signatures are needed
            if (is_array($doc_status['signatures_needed']) && (count($doc_status['signatures_needed']) == 0)) {

                // Update the document's status to signed

                $this->document->updateStatus($invitation->document_id, "signed");

                $event_text = __("The document has been signed by all parties and is now closed.", 'esig');
                $this->document->recordEvent($document->document_id, 'all_signed', $event_text, null);

                // this action is called when all signing request signed . 
                do_action('esig_all_signature_request_signed', array(
                    'document' => $document,
                    'recipients' => $recipient,
                    'invitations' => $invitation,
                ));
                // getting attachment 
                $attachments = apply_filters('esig_email_attachment', array('document' => $document));
                $audit_hash = $this->auditReport($document_id, $document, true);

                if (is_array($attachments) || empty($attachments)) {

                    $attachments = false;
                }
                // Email all signers

                foreach ($doc_status['invites'] as $invite) {

                    $this->notify_signer($document, $invite, $_POST, $audit_hash, $attachments);
                }


                $this->notify_owner($document, $recipient, $audit_hash, $attachments); // Notify admin
                // Otherwise, if the admin wants to be notified of each signature
            } else if ($document->notify) {
                $audit_hash = $this->auditReport($document_id, $document, true);
                $this->notify_owner($document, $recipient, $audit_hash); // Notify admin
            }
            // do action after sending email 
            do_action('esig_email_sent', array('document' => $document));
        }

        if ($document_id) {

            if (isset($_GET['invite'])) {
                set_transient('esig_invite', $invite_get);
            }

            set_transient('esig_document_id', $document_id);

            $document = $this->document->getDocumentByID($document_id);
            $document_report = $this->auditReport($document_id, $document);

            // Grab sender and sender signature
            if (!empty($document->document_content)) {
                // get shortcoded document content by document id   
                $unfiltered_content = $this->document->esig_do_shortcode($document_id);
            }


            $content = apply_filters('the_content', $unfiltered_content);

            $owner = $this->user->getUserBy('wp_user_id', $document->user_id);

            //Get all other recipient signatures
            $sig_data = $this->document->getSignatureStatus($document_id);

            // Fire e-signature loaded action
            if (count($_POST) > 0)
                do_action('esig_signature_loaded', array('document_id' => $document_id,));

            //If signer is viewing put their box in a different chunk
            foreach ($sig_data['invites'] as $invite) {

                // signed username will be here 
                $user_name = $this->user->get_esig_signer_name($invite->user_id, $document_id);


                $user_data = array(
                    'user_name' => $user_name,
                    'user_id' => $invite->user_id,
                    'signed_doc_id' => $document->document_checksum,
                    'esig_sig_nonce' => $my_nonce = wp_create_nonce($invite->user_id . $document->document_checksum),
                    'input_name' => 'recipient_signatures[]',
                );

                foreach ($sig_data['signatures'] as $signature) {

                    if ($signature->user_id == $invite->user_id) {
                        //$sd = new DateTime($signature->sign_date);
                        $sign_date = $this->document->esig_date_format($signature->sign_date);

                        if ($this->signature->userHasSignedDocument($invite->user_id, $document_id)) {
                            $user_data['signature'] = "yes";
                        }

                        $user_data['output_type'] = $this->signature->getSignature_by_type($signature);

                        $user_data['font_type'] = $this->setting->get_generic('esig-signature-type-font' . $signature->user_id);
                        $user_data['css_classes'] = 'signed';
                        $user_data['by_line'] = 'Signed by';
                        $user_data['sign_date'] = "Signed on: $sign_date";
                    }
                }

                // If this is the viewer's signature box, don't add their sig box here
                if (isset($data['viewer_needs_to_sign']) && $data['viewer_needs_to_sign'] && isset($data['recipient_id']) == $invite->user_id) {
                    // Don't add

                    if ($document->document_type == "normal") {
                        $current_user_invite_hash = isset($invite_get) ? $invite_get : null;
                        if ($invite->invite_hash != $current_user_invite_hash) {
                            $user_data['esig-tooltip'] = 'title="This signature section is assigned to ' . $user_name . '"';
                            if (!$this->user->hasSignedDocument($invite->user_id, $document_id)) {
                                $user_data['esig-awaiting-sig'] = $user_name . "<br>" . "(Awaiting Signature)";
                            }
                            $recipient_sig_html .= $this->view->renderPartial('_signature_display', $user_data);
                        }
                    }
                    // All other signatures
                } else {


                    $current_user_invite_hash = isset($invite_get) ? $invite_get : null;
                    if ($invite->invite_hash != $current_user_invite_hash) {
                        if (!$this->user->hasSignedDocument($invite->user_id, $document_id)) {
                            $user_data['esig-awaiting-sig'] = $user_name . "<br>" . "(Awaiting Signature)";
                        }
                        $user_data['esig-tooltip'] = 'title="This signature section is assigned to ' . $user_name . '"';
                    }

                    $recipient_sig_html .= $this->view->renderPartial('_signature_display', $user_data);
                }
            }



            //$dt = new DateTime($document->date_created);
            $date4sort = $this->document->esig_date_format($document->date_created);

            if (isset($_GET['hash'])) {
                $audit_hash = "Audit Signature ID#" . $_GET['hash'];
            } else {

                if ($this->document->getSignedresult($document->document_id)) {

                    $audit_hash = $this->auditReport($document_id, $document, true);

                    if ($audit_hash != "")
                        $audit_hash = "Audit Signature ID#" . $audit_hash;
                }
            }

            // applying filter for document logo 
            $document_logo = apply_filters('esig_document_logo_filter', '');

            // apply filter for sign document adding extra content. 
            $document_extra_content = '';
            $document_extra_content = apply_filters('esig-sign-document-bottom-content', $document_extra_content, array('document' => $document));

            // Default template data
            $template_data = array(
                "message" => $this->view->renderAlerts(),
                "document_title" => esc_attr(wp_unslash($document->document_title)),
                "document_logo" => $document_logo,
                "document_date" => $date4sort,
                "document_id" => $document->document_checksum,
                "document_content" => $content,
                "action_url" => esc_url($_SERVER["REQUEST_URI"]),
                "sender_first_name" => $owner->first_name,
                "sender_last_name" => $owner->last_name,
                "owner_email" => $owner->user_email,
                "recipient_signatures" => $recipient_sig_html,
                "audit_report" => $document_report,
                "auditsignatureid" => $audit_hash,
                "signer_sign_pad_after" => $document_extra_content,
                'blog_name' => get_bloginfo('name'),
                'blog_url' => get_bloginfo('url'),
            );
        }
        $template_data = isset($template_data) ? $template_data : NULL;
        $document = isset($document) ? $document : NULL;

        $template_data = apply_filters('esig-shortcode-display-owner-signature', $template_data, array('document' => $document));
        // If additional data is sent, append it
        if (!empty($data)) {
            foreach ($data as $field => $datum) {
                $template_data[$field] = $datum;
            }
        }

        // Apply filter
        $template_data = apply_filters('esig-shortcode-display-template-data', $template_data);


        // Render

        if ($return) {
            return $this->view->renderPartial($template, $template_data, false, "documents");
        } else {
            $this->view->render("documents", $template, $template_data, false);
        }



        //exit();
    }

    /*     * *
     *  Audit report used to display document view created report in signed
     *  document footer . 
     *  Since 1.0.0 
     * */

    public function auditReport($id, &$document_data = null, $return_type = false) {
        global $audit_trail_data;
        $audit_trail_data = new stdClass();

        if (!$document_data) {
            $document_data = $this->document->getDocument($id);
        }

        $audittrail = $this->audit_trail_helper->get_audit_trail_timeline($this, $id, $document_data);

        $hash = wp_hash($audittrail->audittrail);

        if ($return_type) {
            $doc_timezone = $this->document->esig_get_document_timezone($document_data->document_id);
            if (empty($doc_timezone)) {
                return $this->document->get_audit_signature_id($id, $document_data);
            } else {
                return $hash;
            }
        } else {
            $document_owner_id = $this->document->get_document_owner_id($id);
            $all_invitations = $this->invite->getInvitations($id);
            $audit_trail_data->users = array();
            foreach ($all_invitations as $invitation) {
                $user = $this->audit_trail_helper->get_signer_user($invitation->user_id, $id);
                $user->security_levels = $this->audit_trail_helper->get_security_levels($id);
                $user->signer_ip = $this->audit_trail_helper->get_signer_ip($user->ID, $id);
                $user->dfc = $this->audit_trail_helper->get_digital_fingerprint_checksum($user->ID, $id);
                $user->dfc_qr_code_image_data = $this->audit_trail_helper->generate_qr_code($user->dfc, 'PDF417,8');
                $user->signature_view = $this->audit_trail_helper->get_signature_view($user->ID, $id);
                $audit_trail_data->users[$invitation->user_id] = $user;
            }

            $audit_trail_data->current_url_qr = $this->audit_trail_helper->get_current_url_qr();
            $audit_trail_data->unique_document_id = $document_data->document_checksum;
            $audit_trail_data->site_url = WP_E_Sig()->document->get_site_url($document_data->document_id);
            $audit_trail_data->document_name = $document_data->document_title;
            $audit_trail_data->timeline = $audittrail->html;
            $audit_trail_data->audit_signature_id = $this->document->getSignedresult($id) ? $hash : false;

            ob_start();
            include ESIGN_PLUGIN_PATH . "/views/documents/audit-trail.php";
            $audit_trail_html = ob_get_contents();
            ob_end_clean();
            return $audit_trail_html;
        }
    }

    /**
     * Checks if we're on an admin preview page
     *
     * @since 1.0.1
     * @return Boolean
     */
    public function admin_can_view() {

        // Editors and above can preview documents
        // TODO: Should authors be able to preview their own docs?
        //current_user_can('edit_pages') &&

        $esig_preview = isset($_GET['esigpreview']) ? $this->validation->esig_clean($_GET['esigpreview']) : NULL;
        
        $allow = apply_filters("can_view_preview_document",false);
        
        if($allow){
            return $allow;
        }

        if (isset($esig_preview) && $esig_preview == "1") {
            if (!is_user_logged_in()) {
                $redirect = home_url() . '/wp-login.php?redirect_to=' . urlencode($_SERVER['REQUEST_URI']);
                wp_redirect($redirect);
                exit;
            } 
            else {
                
                $esigrole = new WP_E_Esigrole();
                $doc_id = $this->validation->esig_valid_int($_GET['document_id']);
                if ($esigrole->user_can_view_document($doc_id)) {
                    return true;
                } else {
                    return false;
                }
            }
        } else {

            return false;
        }
    }

    /*     * *
     * Checks if Document id 
     *
     * @since 1.0.1
     * @return Boolean
     * */

    public function document_id_preview() {

        // Editors and above can preview documents
        // TODO: Should authors be able to preview their own docs?
        $document_id = $this->validation->esig_valid_int($_GET['document_id']);

        if (current_user_can('edit_pages') && isset($document_id)) {

            return $document_id;
        } else {
            return "test";
        }
    }

    /**
     * Checks if print display
     *
     * @since 1.0.1
     * @return string
     */
    public function print_option_display($doc_id) {

        if ($this->setting->get_generic('esig_print_option' . $doc_id)) {
            $print_option = $this->setting->get_generic('esig_print_option' . $doc_id);
        } else {
            $print_option = $this->setting->get_generic('esig_print_option');
        }

        if (empty($print_option))
            $print_option = 2;

        if ($print_option == 0) {
            return $display = "display";
        } elseif ($print_option == 1) {
            if ($this->document->getSignedresult($doc_id))
                return $display = "display";
        }
        elseif ($print_option == 2) {
            return $display = "none";
        } elseif ($print_option == 4) {

            if ($this->document->getStatus($doc_id) == 'awaiting') {
                return $display = "display";
            } else {
                return $display = "none";
            }
        } else {
            return $display = "display";
        }
    }

    /*
     *  E-signature custom footer scripts
     *  Since 1.0.12
     */

    public static function esig_footer_scripts() {

        if (wp_is_mobile()) {
            $esig_mobile = '1';
        } else {
            $esig_mobile = '0';
        }

        $esig_scripts = new WP_E_Esigscripts();

        $document_id = get_transient('esig_document_id');

        $invite = get_transient('esig_invite');

        $device = '';
        if ($document_id) {

            $device = WP_E_Sig()->setting->get_generic($document_id . '-document-sign-using');
        }

        // style 
        echo "<link rel='stylesheet' id='bootstrap-css'  href='" . plugins_url('assets/css/bootstrap/bootstrap.min.css', dirname(__FILE__)) . "' type='text/css' media='all' />";

        echo "<link rel='stylesheet' id='bootstrap-theme-css'  href='" . plugins_url('assets/css/bootstrap/bootstrap-theme.min.css', dirname(__FILE__)) . "' type='text/css' media='all' />";

        if (wp_is_mobile()) {

            echo "<link rel='stylesheet' id='esig-style-css'  href='" . plugins_url('assets/css/style_mobile.css?ver=1.0.9', dirname(__FILE__)) . "' type='text/css' media='screen' />
			<link rel='stylesheet' id='esig-theme-style-css'  href='" . plugins_url('page-template/default/style_mobile.css?ver=3.9.1', dirname(__FILE__)) . "' type='text/css' media='all' />";
        } else {

            echo "<link rel='stylesheet' id='esig-style-css'  href='" . plugins_url('assets/css/style.css?ver=1.0.9', dirname(__FILE__)) . "' type='text/css' media='screen' />
			<link rel='stylesheet' id='esig-theme-style-css'  href='" . plugins_url('page-template/default/styles.css?ver=3.9.1', dirname(__FILE__)) . "' type='text/css' media='all' />";
        }
        echo "<link rel='stylesheet' id='esig-theme-style-print-css'  href='" . plugins_url('page-template/default/print_style.css?ver=1.0.9', dirname(__FILE__)) . "' type='text/css' media='print' />
			<link rel='stylesheet' id='thickbox-css'  href='" . includes_url() . "/js/thickbox/thickbox.css?ver=3.9.1' type='text/css' media='all' />";
        // scripts 
        echo "<script type='text/javascript' src='" . plugins_url("assets/js/jquery.validate.js", dirname(__FILE__)) . "'></script>";
        echo "<script type='text/javascript' src='" . includes_url("js/json2.min.js?ver=2011-02-23", dirname(__FILE__)) . "'></script>";
        echo "<script type='text/javascript' src='" . plugins_url("assets/js/jquery.signaturepad.min.js", dirname(__FILE__)) . "'></script>";

        echo "<script type='text/javascript'>";
        $preview = isset($_GET['esigpreview']) ? $_GET['esigpreview'] : null;
        $mode = isset($_GET['mode']) ? $_GET['mode'] : null;
        echo '/* <![CDATA[ */
				var esigAjax = {"ajaxurl":"' . admin_url() . 'admin-ajax.php?action=wp_e_signature_ajax","preview":"' . $preview . '","document_id":"' . $document_id . '","invite":"' . $invite . '","esig_mobile":"' . $esig_mobile . '","sign_device":"' . $device . '","esig_mode":"' . $mode . '"};
			/* ]]> */ 
			</script>';


        echo "<script type='text/javascript' src='" . plugins_url('assets/js/prefixfree.min.js', dirname(__FILE__)) . "'></script>";

        $esig_scripts->display_ui_scripts(array('core.min', 'widget.min', 'position.min', 'tooltip.min'));

        echo "<script type='text/javascript' src='" . plugins_url('assets/js/tooltip.js?ver=3.9.1', dirname(__FILE__)) . "'></script>";
        echo "<script type='text/javascript' src='" . plugins_url('assets/js/bootstrap/bootstrap.min.js', dirname(__FILE__)) . "'></script>";
        echo "<script type='text/javascript'>";
        echo '/* <![CDATA[ */
				var thickboxL10n = {"next":"Next >","prev":"< Prev","image":"Image","of":"of","close":"Close","noiframes":"This feature requires inline frames. You have iframes disabled or your browser does not support them.","loadingAnimation":"' . site_url() . '/wp-includes/js/thickbox/loadingAnimation.gif"};
				/* ]]> */
			</script>';
        echo "<script type='text/javascript' src='" . plugins_url('assets/js/jquery.smartTab.js', dirname(__FILE__)) . "'></script>";
        echo "<script type='text/javascript' src='" . includes_url('js/thickbox/thickbox.js?ver=3.1-20121105', dirname(__FILE__)) . "'></script>";

        if (wp_is_mobile()) {
            echo "<script type='text/javascript' src='" . plugins_url('assets/js/jquery.mobile-events.js', dirname(__FILE__)) . "'></script>";
            echo "<script type='text/javascript' src='" . plugins_url('assets/js/esig-mobile-common.js', dirname(__FILE__)) . "'></script>";
        }

        echo "<script type='text/javascript' src='" . plugins_url('assets/js/signdoc.js?ver=1.1.4', dirname(__FILE__)) . "'></script>";
        echo "<script type='text/javascript' src='" . plugins_url('assets/js/common.js?ver=1.0.1', dirname(__FILE__)) . "'></script>";
    }

    /*
     *  E-signature custom header scripts
     *  Since 1.0.12
     */

    public static function esig_header_scripts() {
        wp_enqueue_script('jquery-ui-slider');
        $document_id = get_transient('esig_document_id');
        echo "<link rel='stylesheet' id='esig-signaturepad-css'  href='" . plugins_url('assets/css/jquery.signaturepad.css', dirname(__FILE__)) . "' type='text/css' media='screen' />";
        echo "<script type='text/javascript' src='" . includes_url() . "/js/jquery/jquery.js?ver=1.11.2'></script>";
        echo "<script type='text/javascript' src='" . includes_url() . "/js/jquery/jquery-migrate.min.js?ver=1.2.1'></script>";
        echo "<script type='text/javascript' src='" . ESIGN_ASSETS_DIR_URI . "/js/esign.js'></script>";
        echo "<script type='text/javascript' src='" . ESIGN_ASSETS_DIR_URI . "/js/esig-validation.js'></script>";
    }

    /*
     *  E-signature custom header 
     *  Since 1.0.12
     */

    public static function esig_head() {

        self::esig_header_scripts();
        do_action('esig_head');
    }

    /*
     *  E-signature custom footer 
     *  Since 1.0.12
     */

    public static function esig_footer() {
        remove_all_actions('wp_footer');
        self::esig_footer_scripts();
        do_action('esig_footer');
        // delete transient after loading footer
        delete_transient('esig_document_id');
        delete_transient('esig_invite');
    }

}
